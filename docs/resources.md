# Resources — copyright & licensing

Consolidated license/copyright record for this theme and everything
bundled inside it, per this theme's compliance ruleset (which targets
modern WordPress standards, not WordPress.org directory submission rules —
so bundled non-GPL assets like the company logo are acceptable here as
long as they're disclosed, which is what this file does).

## The theme itself

Remotive Media — Copyright © 2026 MENJ (https://menj.blog), built for
Remotive Media Asia Pte. Ltd.

Licensed under the GNU General Public License v2.0 or later
(https://www.gnu.org/licenses/gpl-2.0.html), same as its parent theme,
Twenty Twenty-Five (bundled with WordPress core, GPL-2.0-or-later).

## Bundled fonts

All three are Google Fonts, redistributed here as self-hosted `.woff2`
files (see `readme.md` → "Fonts" for why: no Google Fonts CDN calls at
runtime, for GDPR compliance). All are licensed under the SIL Open Font
License 1.1 (https://openfontlicense.org/), which explicitly permits
bundling and redistribution, including inside a commercial theme, with
attribution.

| Font | Files | License | Copyright / source |
|---|---|---|---|
| Archivo | `assets/fonts/archivo/*.woff2` (weights 400, 500, 600, 700, 900) | SIL OFL 1.1 | © The Archivo Project Authors. Source: https://github.com/Omnibus-Type/Archivo |
| Newsreader | `assets/fonts/newsreader/*.woff2` (400 italic, 500 normal, 500 italic) | SIL OFL 1.1 | © The Newsreader Project Authors. Source: https://github.com/productiontype/Newsreader |
| Saira | `assets/fonts/saira/*.woff2` (weights 400, 600, 700, 900, and 500 italic; the 500 normal face was removed in 1.44.4 as nothing requested it) | SIL OFL 1.1 | © The Saira Project Authors (Jóse Ramiro Peña Rodríguez / Huerta Tipográfica). Source: https://github.com/Omnibus-Type/Saira. The site's typeface throughout, per the client's brand-guideline JSON. |
| Space Grotesk | `assets/fonts/space-grotesk/*.woff2` (weights 400, 500, 600, 700) | SIL OFL 1.1 | © 2020 The Space Grotesk Project Authors (Florian Karsten). Source: https://github.com/floriankarsten/space-grotesk. Used only for the footer column headings (`.rm-footer__heading`) as a scoped accent since v1.66.5; not the site's general typeface. |

Font files were built as static `.woff2` via the `@fontsource` npm
distribution of each family (itself just a packaging of the same
upstream OFL-licensed sources) — no font file has been modified from its
upstream release.

## Bundled images

| File | Rights holder | Notes |
|---|---|---|
| `assets/images/remotive-mark.png` and `.avif` | Remotive Media Asia Pte. Ltd. | Company logo mark, hero watermark and site logo source. **Proprietary, not GPL, not open-licensed.** Bundled because it is this site's own brand asset rather than a redistributable library; not for reuse outside this theme or entity. The AVIF is an encode of the same file, served when the request accepts it. |
| `assets/images/remotive-logo-56/112/168.png` and `.avif` | Remotive Media Asia Pte. Ltd. | Header logo at 1x, 2x and 3x, derived from the mark above. Same proprietary status. |
| `assets/images/remotive-logo-admin.png` and `.avif` | Remotive Media Asia Pte. Ltd. | Logo shown on the Theme Options screen. Same proprietary status. |
| `assets/images/remotive-logo-landscape.png` | Remotive Media Asia Pte. Ltd. | Landscape lockup. Same proprietary status. |
| `assets/images/remotive-lockup-square.jpg` | Remotive Media Asia Pte. Ltd. | Company logo lockup, square, used as the favicon and site icon source. Same proprietary status. |
| `assets/images/landing/*.avif` and `*.jpg` | Photographers via Pexels (see `docs/image-credits.md`) | Photography on the ad landing pages: six city skylines and one hero per service. Used under the [Pexels License](https://www.pexels.com/license/) (free for commercial use, no attribution required, credited anyway). Resized and re-encoded; do not imply the photographers endorse the business. **Not** Remotive's own work and not covered by the theme's licence. |
| `assets/seed-images/*.jpg` and `*.avif` | Photographers via Pexels (see `docs/image-credits.md`) | Photographs for the seven shipped Insights articles (v1.111.0; before that, gradient graphics generated for this theme). Used under the [Pexels License](https://www.pexels.com/license/). |
| `assets/images/case-studies/*.avif` and `*.jpg` | Photographers via Pexels (see `docs/image-credits.md`) | The six photos on the Case Studies page cards (v1.110.2). Pexels License. |
| `assets/fonts/kagnue/kagnue-regular.woff2` | Kagnue Serif, used under the site owner's licence | A subset (Basic Latin, dashes, quotes, arrow) of the Kagnue Regular display serif for the big figures only (v1.109.0). **Not open-licensed:** keep the licence on file; do not redistribute the theme with this file to others without checking its terms. |
| `assets/team/<slug>.avif` | Remotive Media Asia Pte. Ltd. (photographs of the named individuals) | Team portraits, one per person, cut out and re-encoded from photography supplied by the company. **Proprietary, and additionally personal likenesses:** each depicts a named person, so redistribution or reuse needs that person's consent as well as the company's. Not covered by the theme's GPL licence. |

## Theme's own code and styles

All PHP, CSS and JavaScript in this theme is original work by Re:Motive Media
Asia Pte. Ltd. and carries the theme's GPL-2.0-or-later licence, including
`assets/css/login.css` (v1.53.0), `assets/js/remotive.js` (v1.54.0, the
concatenation of the former toggle, navigation-fallback and ticker scripts)
and `tools/normalise-portraits.py` (v1.55.0). No third-party library is
bundled.

## Derived assets

Several bundled files are generated from the originals above rather than
supplied separately, so their rights follow the source file:

- The AVIF companions are re-encodes of the corresponding PNG or JPEG.
  Both formats ship; the original is always the fallback.
- The logo renders at 56, 112 and 168px are downscales of the mark.
- The team portraits are crops of supplied photography with the background
  removed.

No third-party tool output, stock imagery or AI-generated imagery is
bundled in the theme.

## Bundled icons

| File | License | Source | Attribution |
|---|---|---|---|
| `assets/icons/instagram.svg` | CC0 1.0 (public domain) | [Simple Icons](https://simpleicons.org) | Not required |
| `assets/icons/tiktok.svg` | CC0 1.0 (public domain) | [Simple Icons](https://simpleicons.org) | Not required |
| `assets/icons/linkedin.svg` | CC BY 4.0 | [Font Awesome Free](https://fontawesome.com) | **Required** — see below |

The LinkedIn icon is the one asset in this theme requiring a visible
credit, not just internal documentation of the license. CC BY 4.0 asks
for attribution wherever the work is used, which for a live website means
somewhere a visitor can actually see it — not just in a file inside the
theme package. That credit lives in `parts/footer.html` and
`templates/page-contact.html` (`.rm-footer__credit`, a small "Icons by
Font Awesome" line next to the copyright notice, linking to
fontawesome.com), rendered on every page that includes the footer. If
the LinkedIn icon is ever swapped for a different source, remove that
credit line too — it shouldn't stay if the thing it's crediting is gone.

The icons themselves are inlined directly as `<svg>` markup inside the
Custom HTML blocks that use them (`fill="currentColor"`, so they follow
the surrounding text colour automatically across light/dark mode)
rather than loaded as external files via `<img>` — the source `.svg`
files in `assets/icons/` are kept for reference/reuse, not actually
enqueued or requested by the browser.

This is the standard, expected split for a theme's bundled assets: the
*code* (PHP, CSS, JS, block templates) is GPL, matching WordPress's own
licensing; a company's own logo is not required to be GPL and commonly
isn't — WordPress.org's own theme-review guidelines make this same
distinction for directory-submitted themes, and this theme isn't
distributed there anyway (see `docs/ssot.md` — it's built exclusively for
`remotivemedia.asia`).

## No other bundled third-party code

No JavaScript libraries, icon fonts, or other third-party code are
bundled in this theme. `assets/js/theme-toggle.js`, `assets/js/admin-theme-options.js`,
`assets/js/lightbox.js`, `assets/js/parallax.js`, and `assets/js/webmcp.js`
are original, theme-specific code (see `docs/changelog.md` for authorship/version
history). WebMCP is used as a browser API when available; no WebMCP package,
polyfill, or specification code is bundled.
The performance and paper-delivery styles in `assets/css/critical.css` and
`assets/css/print.css` are also original theme code; v1.44.1 introduces no new
font, image, library, polyfill, or other third-party asset.
Dashicons, used for the admin page's tab icons, ships with WordPress core
itself (`wp_enqueue_style('dashicons')`) — nothing extra bundled for it.

## A note on design inspiration (v1.10.0)

The blog archive/single templates, the About page's lightbox gallery, and
its parallax hero were built after reviewing three uploaded reference
templates — two from HTML5 UP ("Phantom," "Zerofour"; both Creative
Commons Attribution 3.0) and one unlicensed rock-climbing-gym template of
unknown copyright status. **None of their code, CSS, JavaScript, images,
or content was copied into this theme.** The interaction *patterns* they
demonstrate — a sidebar blog layout, a lightbox-enabled image gallery, a
parallax hero — are generic, uncopyrightable UX conventions; this theme's
implementations of them (`inc/forms/lead-form-handler.php`,
`assets/js/lightbox.js`, `assets/js/parallax.js`, `templates/index.html`,
`templates/page-about.html`) are original code written for this project,
using this theme's own design system throughout. This was a deliberate
choice, not an oversight: CC BY 3.0 requires visible attribution if the
actual work is reused, which is awkward on a polished client site and
avoidable entirely by writing fresh code instead of adapting licensed
code — and the rock-climbing template's unknown license made it
unsuitable to touch at all, code or content. No attribution is required
or included for any of the three, since nothing of theirs was
distributed.
