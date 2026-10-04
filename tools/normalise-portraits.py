#!/usr/bin/env python3
"""
Normalise team portraits so every tile shares the same head geometry.

Cropping these by eye produced a grid where face heights ranged from 300 to
570 pixels and eye lines sat anywhere from 32% to 51% down the frame, which
reads as each person standing at a different distance. This script measures
each subject and computes the crop, rather than the other way round.

Two measurements drive it, both taken from the cut-out alpha mask so they do
not depend on a face detector agreeing with itself across turned heads:

  head top    the first row containing subject pixels
  head width  the widest run of subject pixels in the head band, taken as a
              robust percentile so stray hair strands do not set the scale

The crop is then chosen so that, in the finished 4:5 tile:

  head width  equals HEAD_W_FRAC of the tile width
  head top    sits HEAD_TOP_FRAC down from the top edge
  head centre sits on the tile's vertical centre line

Usage (run from anywhere; paths resolve from this file's location):

  normalise-portraits.py --replace SLUG PHOTO   build one portrait, replace the
                                                old one and delete what it
                                                leaves behind
  normalise-portraits.py --replace SLUG PHOTO --extend
                                                as above, for a photo that stops
                                                at the chest in a plain shirt
  normalise-portraits.py --check                report face size and framing
                                                for every shipped portrait
  normalise-portraits.py                        rebuild everyone in PEOPLE from
                                                their sources

PHOTO is a path, or a file name inside the sources folder (PORTRAIT_SOURCES,
default /mnt/user-data/uploads/). One portrait per person: there is no
alternate/rollover frame any more, so --replace also deletes any leftover
<slug>-alt.avif, and the theme deletes those on the server by itself
(remotive_retired_files() in inc/site-setup.php).
"""

import argparse
import os
import sys

import cv2
import numpy as np
from PIL import Image, ImageOps
from rembg import remove, new_session

try:  # AVIF support is a plugin rather than part of Pillow itself.
    import pillow_avif  # noqa: F401
except ImportError:
    pass

TILE_W, TILE_H = 800, 1000
# Faces, not heads. Normalising head width looked right in isolation and
# wrong in the grid: hair, hats and a bald crown all change head width
# without changing how big the person reads, so face heights ended up
# ranging from 26% to 52% of the tile. The face box is what the eye
# compares.
FACE_H_FRAC = 0.25    # face height as a fraction of tile height
EYE_Y_FRAC = 0.34     # where the eye line sits down the tile
SIDE_MARGIN = 0.02    # clear space each side, so shoulders are not cut off
HEAD_TOP_FRAC = 0.10  # fallback framing when no face is found
BAND_PX = 230         # fixed head-measuring band, in tile pixels
# A source that stops at the chest cannot fill the tile at the set's face
# size: the cut-out ends mid-tile and the straight edge floats over the
# gradient, which reads as unfinished. Two measures, in order. First scale
# the person up until the cut reaches the bottom edge, but never past
# MAX_BOOST times the standard size, so one face does not dwarf the rest.
# Whatever gap remains is closed by feathering the cut edge into the tile
# over FEATHER_FRAC of its height, rather than leaving a hard line.
MAX_BOOST = 1.30
FLOAT_BELOW = 0.94    # subject bottom above this fraction of the tile = floating
FEATHER_FRAC = 0.10

FACE_CASCADE = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
PROFILE_CASCADE = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_profileface.xml')


def find_face(img):
    """Face box as (x, y, w, h), or None.

    Frontal first, then profile, then profile on a mirrored copy, since the
    profile cascade only recognises one direction.
    """
    rgb = np.array(img.convert('RGB'))
    gray = cv2.cvtColor(rgb, cv2.COLOR_RGB2GRAY)

    for detector, flip in ((FACE_CASCADE, False), (PROFILE_CASCADE, False), (PROFILE_CASCADE, True)):
        source = cv2.flip(gray, 1) if flip else gray
        found = detector.detectMultiScale(source, 1.1, 5, minSize=(60, 60))

        if len(found):
            x, y, w, h = max(found, key=lambda f: f[2] * f[3])

            if flip:
                x = gray.shape[1] - (x + w)

            return int(x), int(y), int(w), int(h)

    return None

HERE = os.path.dirname(os.path.abspath(__file__))
UPLOADS = os.environ.get('PORTRAIT_SOURCES', '/mnt/user-data/uploads/')
OUT = os.path.join(HERE, '..', 'assets', 'team') + os.sep

# Each entry is a generous starting crop: centre x, top, and height, all as
# fractions of the source image. It only has to contain the subject with room
# to spare; the measured geometry decides the final frame.
PEOPLE = {
    'gordan': ('1704438368189.jpg', 0.40, 0.00, 1.00),
    'elfie':  ('mohd-elfie-nieshaem-juferi.jpg', 0.50, 0.00, 1.00),
    'alif':   ('DSC07036.JPG', 0.47, 0.48, 0.52),
    'jazlan': ('DSC07029.JPG', 0.50, 0.36, 0.52),
    'nabil':  ('DSC07018.JPG', 0.47, 0.28, 0.60),
    'ally':   ('ally-foo.jpg', 0.50, 0.00, 1.00),
    'jay':    ('Jay.png', 0.50, 0.00, 1.00),
    'louie':  ('Louie.png', 0.50, 0.00, 1.00),
    'freya':  ('Freya.png', 0.52, 0.00, 1.00),
}

# Rotation applied before anything else, to level the eye line where the
# source leans. Degrees counter-clockwise.
ROTATE = {}

# People whose photo stops short of the tile's bottom edge AND ends in plain
# fabric: the garment is continued straight down instead of being feathered
# out. Opt-in on purpose. Mirroring is right for an unpatterned shirt and wrong
# for anything else (skin, straps, a logo, a visible hem), which is why this
# is a list and not automatic. `--extend` adds a slug for one run.
EXTEND = {'jay'}
# Close-ups that cannot be shrunk to the set's face size without the torso ending
# in straight edges inside the tile: filled from the photograph instead (see
# cover_tile()). Ally's is a close selfie; the standard framing left a small
# bust floating mid-tile. Elfie's is a tight chest-up shot whose sleeves are cut
# by the photo's own edges: the standard framing kept the face at the set's size
# but showed those cuts as straight vertical edges on both sides of the shirt.
COVER = {'ally', 'elfie'}
EXTEND_MAX_FRAC = 0.16   # never invent more than this fraction of the tile's height

def load(name):
    path = name if os.path.isabs(name) or os.path.exists(name) else os.path.join(UPLOADS, name)
    return ImageOps.exif_transpose(Image.open(path)).convert('RGB')


def rough_crop(im, cx, top, h):
    W, H = im.size
    hh = int(h * H)
    ww = int(hh * 0.8)
    x0 = int(cx * W - ww / 2)
    y0 = int(top * H)
    return im.crop((max(0, x0), max(0, y0), min(W, x0 + ww), min(H, y0 + hh)))


def measure(cut, band_px=None):
    """Head top, head centre x and head width, in pixels of the cut-out.

    band_px fixes how far below the crown the head is measured. Deriving it
    from the subject's height instead, as a first attempt did, makes the
    measurement disagree with itself: a crop that includes more torso has a
    taller band, which reaches into the shoulders and changes the answer.
    A fixed band measures the same part of the person every time.
    """
    alpha = np.array(cut)[:, :, 3]
    rows = np.where((alpha > 30).sum(axis=1) > 3)[0]
    top = int(rows.min())

    if band_px is None:
        band_px = max(1, int((rows.max() - top) * 0.30))

    band = alpha[top:top + band_px, :] > 30
    widths = band.sum(axis=1)
    head_w = float(np.percentile(widths[widths > 0], 92))

    cols = np.where(band.any(axis=0))[0]
    head_cx = float((cols.min() + cols.max()) / 2)

    return top, head_cx, head_w


def build(slug, source, cx, top, h):
    im = load(source)

    if slug in ROTATE:
        im = im.rotate(ROTATE[slug], resample=Image.BICUBIC, fillcolor=(128, 128, 128))

    rough = rough_crop(im, cx, top, h)
    # Alpha matting rather than a plain mask. A white shirt against a pale
    # studio wall gives the plain mask almost no contrast to work with, and
    # it leaves a grey line tracing the silhouette that reads as a border
    # around the tile. Matting resolves the edge from the image itself.
    cut = remove(
        rough,
        session=SESSION,
        alpha_matting=True,
        alpha_matting_foreground_threshold=250,
        alpha_matting_background_threshold=15,
        alpha_matting_erode_size=12,
    )

    if slug in COVER:
        return cover_tile(cut)

    head_top, head_cx, head_w = measure(cut)

    # Scale from the face where one is found, and from head width only as a
    # fallback, calibrated by the ratio the faces themselves establish.
    face = find_face(cut)
    if face:
        fx, fy, fw, fh = face
        scale = (TILE_H * FACE_H_FRAC) / fh
        anchor_x = fx + fw / 2
        anchor_y = fy + 0.42 * fh          # eye line within a face box
        anchor_frac = EYE_Y_FRAC
    else:
        scale = (TILE_W * 0.46) / head_w
        anchor_x = head_cx
        anchor_y = head_top
        anchor_frac = HEAD_TOP_FRAC

    # No shoulder may be cut off by the frame. If the subject would be wider
    # than the tile allows, the scale is reduced until it fits, which is a
    # visible compromise on face size and the right one: a clipped shoulder
    # reads as a mistake, a slightly smaller face does not.
    alpha = np.array(cut)[:, :, 3]
    cols = np.where((alpha > 30).sum(axis=0) > 3)[0]
    subject_w = cols.max() - cols.min()
    fit_scale = (TILE_W * (1 - 2 * SIDE_MARGIN)) / subject_w

    if fit_scale < scale:
        scale = fit_scale

    # Short source: grow the person until the cut reaches the bottom edge,
    # within MAX_BOOST. Shoulders still win over face size, as above.
    rows_all = np.where((alpha > 30).sum(axis=1) > 3)[0]
    bottom_frac = anchor_frac + (rows_all.max() - anchor_y) * scale / TILE_H

    if bottom_frac < FLOAT_BELOW:
        needed = (1 - anchor_frac) * TILE_H / max(1, rows_all.max() - anchor_y)
        scale = min(scale * MAX_BOOST, max(scale, needed), fit_scale)

    # Two or three passes: render at the current scale, measure the result
    # against the fixed band, and correct. It converges immediately because
    # the only error is the difference between the rough cut's band and the
    # tile's.
    for _ in range(1):
        win_w = TILE_W / scale
        win_h = TILE_H / scale
        x0 = anchor_x - win_w / 2
        y0 = anchor_y - (TILE_H * anchor_frac) / scale

        # Horizontal placement. Centring on the face is right when the body
        # fills the frame, and wrong when it does not: a turned pose leaves
        # the subject sitting to one side with dead space opposite. Where
        # the whole person fits with room to spare, the person is centred
        # instead of the face.
        left, right = cols.min(), cols.max()

        if (right - left) * scale < TILE_W * 0.92:
            x0 = (left + right) / 2 - win_w / 2
        else:
            x0 = min(x0, left - win_w * SIDE_MARGIN)
            x0 = max(x0, right + win_w * SIDE_MARGIN - win_w)

        # Anything outside the cut-out is transparent, which is correct: the
        # tile gradient shows through rather than a hard edge.
        canvas = Image.new('RGBA', (int(round(win_w)), int(round(win_h))), (0, 0, 0, 0))
        canvas.paste(cut, (int(round(-x0)), int(round(-y0))))
        tile = canvas.resize((TILE_W, TILE_H), Image.LANCZOS)

    return extend_bottom(tile) if slug in EXTEND else feather_bottom(tile)


def cover_tile(cut):
    """Fill the tile from the photograph itself, like CSS `background-size: cover`.

    For a tight head-and-chest shot. Shrinking one to the set's face size
    leaves the torso ending in straight vertical and horizontal edges where the
    photograph did, floating inside the tile; nothing can honestly continue a
    shoulder that was never captured. So the largest window of the tile's 4:5
    shape that fits INSIDE the photo is used (no empty margins, nothing to
    hide), placed so the crown sits HEAD_TOP_FRAC below the top edge. The price
    is honest and visible: the face reads larger than the rest of the set.
    """
    W, H = cut.size
    ww = min(W, int(H * 0.8))
    wh = int(round(ww / 0.8))

    alpha = np.array(cut)[:, :, 3]
    rows = np.where((alpha > 30).sum(axis=1) > 3)[0]
    head_top = int(rows.min())

    face = find_face(cut)
    cx = face[0] + face[2] / 2 if face else measure(cut)[1]

    x0 = int(round(min(max(cx - ww / 2, 0), W - ww)))
    y0 = int(round(min(max(head_top - HEAD_TOP_FRAC * wh, 0), H - wh)))

    return cut.crop((x0, y0, x0 + ww, y0 + wh)).resize((TILE_W, TILE_H), Image.LANCZOS)


def extend_bottom(tile):
    """Continue a plain garment straight down to the tile's bottom edge.

    A source that stops at the chest leaves the cut-out ending mid-tile. For a
    plain shirt the honest, natural fix is to carry the fabric on, which is
    what the rest of the garment does in life. The real pixels just above the
    join are mirrored downward, so the weave and folds continue without a
    seam (a flat colour fill draws a visible line: it has no grain), while the
    silhouette's sides continue straight down from the last solid row.

    Returns the tile untouched if it already reaches the bottom, and falls back
    to feathering if the gap is larger than EXTEND_MAX_FRAC, because mirroring
    further than that starts reaching up past the chest into the neckline.
    """
    arr = np.array(tile)
    alpha = arr[:, :, 3]
    rows = np.where((alpha > 30).sum(axis=1) > 3)[0]
    bottom = int(rows.max())
    need = TILE_H - (bottom + 1)

    if need <= 0:
        return tile

    if need > TILE_H * EXTEND_MAX_FRAC:
        return feather_bottom(tile)

    solid = bottom - 3                      # last row that is reliably solid, not the soft matte edge
    mask = alpha[solid] > 128               # the silhouette continues straight down from here
    width = arr.shape[1]
    out = arr.copy()

    for i in range(need):
        src = solid - 2 - i                 # mirror the real fabric above the join
        row = arr[src].copy()
        valid = arr[src, :, 3] > 128

        # Where the mirrored row is background but the silhouette continues,
        # borrow the nearest real fabric pixel in that row.
        if not valid.all() and valid.any():
            idx = np.where(valid)[0]
            nearest = idx[np.abs(np.arange(width)[:, None] - idx[None, :]).argmin(axis=1)]
            row[~valid] = row[nearest[~valid]]

        row[:, 3] = np.where(mask, 255, 0)
        out[bottom + 1 + i] = row

    # The matte's soft edge just above the join would show as a faint line.
    for y in range(solid + 1, bottom + 1):
        out[y, :, 3] = np.where(mask, 255, 0)
        out[y, :, :3] = np.where(mask[:, None], out[y, :, :3], 0)

    return Image.fromarray(out, 'RGBA')


def feather_bottom(tile):
    """Dissolve a cut edge that stops short of the tile's bottom.

    Only acts on a floating edge; a subject that runs off the bottom of the
    tile is returned untouched, so the normal portraits are byte-for-byte
    what they were.
    """
    arr = np.array(tile)
    rows = np.where((arr[:, :, 3] > 30).sum(axis=1) > 3)[0]
    bottom = int(rows.max())

    if bottom >= TILE_H * 0.985:
        return tile

    span = int(TILE_H * FEATHER_FRAC)
    y0 = max(0, bottom - span)
    ramp = np.linspace(1.0, 0.0, bottom - y0 + 1) ** 1.5   # ease, not a straight line
    alpha = arr[:, :, 3].astype(np.float32)
    alpha[y0:bottom + 1, :] *= ramp[:, None]
    alpha[bottom + 1:, :] = 0
    arr[:, :, 3] = alpha.astype(np.uint8)

    return Image.fromarray(arr, 'RGBA')


def report(slug, tile):
    """One line of geometry, and whether any shoulder is clipped."""
    a = np.array(tile)[:, :, 3]
    cols = np.where((a > 30).sum(axis=0) > 3)[0]
    rows = np.where((a > 30).sum(axis=1) > 3)[0]
    face = find_face(tile)
    fh = face[3] / TILE_H * 100 if face else 0
    clipped = 'CLIPPED' if cols.min() < 4 or cols.max() > TILE_W - 4 else 'clear'
    print(f'{slug:11} faceH={fh:5.1f}%  sides={cols.min()/TILE_W*100:4.1f}%..'
          f'{cols.max()/TILE_W*100:5.1f}%  bottom={rows.max()/TILE_H*100:5.1f}%  {clipped}')
    return fh


def save(slug, tile):
    """Write <slug>.avif, replacing any previous portrait, and delete leftovers.

    Overwriting is the deletion of the old photograph. Anything else that
    belongs to the same person and is no longer used goes too: the retired
    rollover frame, and stale files of the same slug in another format.
    """
    os.makedirs(OUT, exist_ok=True)
    target = os.path.join(OUT, slug + '.avif')
    replaced = os.path.exists(target)
    tile.save(target, quality=70)
    print(('replaced ' if replaced else 'created  ') + os.path.relpath(target, HERE))

    for leftover in (slug + '-alt.avif', slug + '.png', slug + '.jpg', slug + '.jpeg', slug + '.webp'):
        path = os.path.join(OUT, leftover)

        if os.path.exists(path):
            os.remove(path)
            print('deleted  ' + os.path.relpath(path, HERE))


def check():
    for name in sorted(f for f in os.listdir(OUT) if f.endswith('.avif')):
        report(name[:-5], Image.open(os.path.join(OUT, name)).convert('RGBA'))


if __name__ == '__main__':
    ap = argparse.ArgumentParser(description='Build team portraits to one shared framing.')
    ap.add_argument('--replace', nargs=2, metavar=('SLUG', 'PHOTO'), help='rebuild one portrait from a new photo')
    ap.add_argument('--check', action='store_true', help='report geometry of the shipped portraits and exit')
    ap.add_argument('--cx', type=float, default=None, help='--replace: rough crop centre x, fraction of the photo')
    ap.add_argument('--top', type=float, default=None, help='--replace: rough crop top, fraction of the photo')
    ap.add_argument('--h', type=float, default=None, help='--replace: rough crop height, fraction of the photo')
    ap.add_argument('--cover', action='store_true', help='--replace: fill the tile from the photo itself, for a close-up that cannot be shrunk to the set\'s face size')
    ap.add_argument('--extend', action='store_true', help='--replace: continue a plain garment down to the bottom edge instead of feathering it')
    ap.add_argument('--rotate', type=float, default=0.0, help='--replace: level a leaning eye line (degrees CCW)')
    args = ap.parse_args()

    if args.check:
        check()
        sys.exit(0)

    SESSION = new_session('u2net')

    if args.replace:
        slug, photo = args.replace
        slug = ''.join(c for c in slug.lower() if c.isalnum() or c == '-')
        # Framing, rotation and the EXTEND / COVER choices describe one
        # specific photograph. They carry over only when this is that same
        # file being rebuilt; a NEW photo of the same person must not inherit
        # them (a rotation that levelled a leaning pose would tilt a straight
        # one). Explicit flags always win.
        known = PEOPLE.get(slug)
        same = bool(known) and os.path.basename(photo) == known[0]

        if not same:
            ROTATE.pop(slug, None)
            EXTEND.discard(slug)
            COVER.discard(slug)

        if args.rotate:
            ROTATE[slug] = args.rotate

        if args.extend:
            EXTEND.add(slug)

        if args.cover:
            COVER.add(slug)

        cx = known[1] if same and args.cx is None else (0.50 if args.cx is None else args.cx)
        top = known[2] if same and args.top is None else (0.00 if args.top is None else args.top)
        h = known[3] if same and args.h is None else (1.00 if args.h is None else args.h)
        tile = build(slug, photo, cx, top, h)
        save(slug, tile)
        report(slug, tile)
        sys.exit(0)

    for slug, (source, cx, top, h) in sorted(PEOPLE.items()):
        path = source if os.path.isabs(source) else os.path.join(UPLOADS, source)

        if not os.path.exists(path):
            print(f'skipped  {slug}: source {source} not found in {UPLOADS}')
            continue

        tile = build(slug, source, cx, top, h)
        save(slug, tile)
        report(slug, tile)
