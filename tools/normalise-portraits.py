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

Run from /home/claude. Sources and per-person framing live in PEOPLE below.
"""

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

UPLOADS = '/mnt/user-data/uploads/'
OUT = 'remotive/assets/team/'

# Each entry is a generous starting crop: centre x, top, and height, all as
# fractions of the source image. It only has to contain the subject with room
# to spare; the measured geometry decides the final frame.
PEOPLE = {
    'gordan':     ('1704438368189.jpg', 0.40, 0.00, 1.00),
    'gordan-alt': ('Untitled_design_-_2023-02-02T154126_752.webp', 0.52, 0.00, 1.00),
    'elfie':      ('DSC07027.JPG', 0.46, 0.22, 0.60),
    'elfie-alt':  ('DSC07025.JPG', 0.52, 0.18, 0.60),
    'alif':       ('DSC07036.JPG', 0.47, 0.48, 0.52),
    'alif-alt':   ('DSC07035.JPG', 0.49, 0.48, 0.52),
    'jazlan':     ('DSC07029.JPG', 0.50, 0.36, 0.52),
    # DSC07031 puts the shop sign beside him and DSC07033 leaves the face
    # undetectable behind sunglasses and a raised chin, so the crop had to be
    # inferred and came out leaning and pushed to one edge.
    # Chin raised, hands clasped, standing square to the camera like his
    # base frame. The window sits left of the subject on purpose: further
    # right and the shop sign enters the frame, which the cut-out then
    # treats as part of him.
    'jazlan-alt': ('DSC07030.JPG', 0.44, 0.30, 0.46),
    'nabil':      ('DSC07018.JPG', 0.47, 0.28, 0.60),
        # Kept tight: a taller window pulls in the shop sign beside him, which
    # the cut-out then treats as the subject.
    'nabil-alt':  ('DSC07017.JPG', 0.44, 0.30, 0.42),
}

# Rotation applied before anything else, to level the eye line where the
# source leans. Degrees counter-clockwise.
ROTATE = {'elfie': 7, 'elfie-alt': 5}

# Frames the general rule cannot serve, handled explicitly rather than by
# bending the rule for everyone.
#
# gordan-alt's only source is a low-resolution landscape headshot with
# nothing below the chest. Framed to the set's face size it floats with a
# cut edge under the shoulders, so it is scaled to fill the tile instead
# and its face reads larger than the rest. 'nudge' moves the subject left
# as a fraction of the frame, because filling the tile puts him off centre.
SPECIAL = {
    'gordan-alt': {'mode': 'fill', 'nudge': 0.10},
}


def load(name):
    return ImageOps.exif_transpose(Image.open(UPLOADS + name)).convert('RGB')


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

    head_top, head_cx, head_w = measure(cut)

    # Scale from the face where one is found, and from head width only as a
    # fallback, calibrated by the ratio the faces themselves establish.
    face = find_face(cut)
    person = slug.split('-')[0]
    special = SPECIAL.get(slug)

    if special and special.get('mode') == 'fill':
        rows_v = np.where((np.array(cut)[:, :, 3] > 30).sum(axis=1) > 3)[0]
        scale = (TILE_H * 0.999) / (rows_v.max() - rows_v.min())
        cols_h = np.where((np.array(cut)[:, :, 3] > 30).sum(axis=0) > 3)[0]
        win_w = TILE_W / scale
        x0 = (cols_h.min() + cols_h.max()) / 2 - win_w / 2 + win_w * special.get('nudge', 0)
        canvas = Image.new('RGBA', (int(round(win_w)), int(round(TILE_H / scale))), (0, 0, 0, 0))
        canvas.paste(cut, (int(round(-x0)), int(round(-rows_v.min()))))

        return canvas.resize((TILE_W, TILE_H), Image.LANCZOS)

    if face:
        fx, fy, fw, fh = face
        scale = (TILE_H * FACE_H_FRAC) / fh
        anchor_x = fx + fw / 2
        anchor_y = fy + 0.42 * fh          # eye line within a face box
        anchor_frac = EYE_Y_FRAC
        RATIO[person] = fh / head_w        # this person's face-to-head ratio
    elif person in RATIO:
        # Sunglasses and a raised chin defeat the cascade, which is exactly
        # the sort of frame a hover state uses. The same person's other
        # photograph gives the ratio between their head width and their face
        # height, so the face can be sized without being seen.
        implied_face = head_w * RATIO[person]
        scale = (TILE_H * FACE_H_FRAC) / implied_face
        anchor_x = head_cx
        anchor_y = head_top + implied_face * 0.62
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

    return tile


RATIO = {}

if __name__ == '__main__':
    SESSION = new_session('u2net')

    for slug, (source, cx, top, h) in sorted(PEOPLE.items(), key=lambda kv: ('-alt' in kv[0], kv[0])):
        out = build(slug, source, cx, top, h)
        out.save(OUT + slug + '.avif', quality=70)

        a = np.array(out)[:, :, 3]
        cols = np.where((a > 30).sum(axis=0) > 3)[0]
        rows = np.where((a > 30).sum(axis=1) > 3)[0]
        face = find_face(out)
        fh = face[3] / TILE_H * 100 if face else 0
        clipped = 'CLIPPED' if cols.min() < 4 or cols.max() > TILE_W - 4 else 'clear'
        print(f'{slug:11} faceH={fh:5.1f}%  sides={cols.min()/TILE_W*100:4.1f}%..'
              f'{cols.max()/TILE_W*100:5.1f}%  bottom={rows.max()/TILE_H*100:5.1f}%  {clipped}')
