# SSOT — Remotive Media theme

This is the canonical reference for facts about this theme, the brand it
implements, and the legal entity behind it. If any other document (readme.txt,
readme.md, a slide deck, a client-facing brief) ever conflicts with what's
written here, **this file is correct and the other document is out of date.**

Last verified: theme v1.79.1.

## Legal entity

Sourced from ACRA business profile (Singapore), dated 01 Aug 2024.

| Field | Value |
|---|---|
| Registered name | REMOTIVE MEDIA ASIA PTE. LTD. |
| UEN | 202404376G |
| Incorporation date | 31 Jan 2024 |
| Company type | Private company limited by shares |
| Status | Live company |
| Registered office | 11 North Buona Vista Drive, #08-09, The Metropolis, Singapore 138589 |

This is the address used in the site footer. Do not revert it to any
previously-used placeholder city — Kuala Lumpur was used in early drafts
of this theme and is **not** the registered address.

## Domains

| Domain | Use |
|---|---|
| `remotivemedia.asia` | This website. `Theme URI` in `style.css` points here. |
| `remotivemedia.com` | Email domain (`hello@remotivemedia.com`) — legacy/parallel domain, still in active use for contact addresses at time of writing. |
| `menj.blog` | Theme author's (MENJ) personal site. `Author URI` in `style.css`. |

If these domains diverge further in the future (e.g. email migrates to
`@remotivemedia.asia`), update this table first, then propagate to
`parts/footer.html` and `readme.txt`.

## Design tokens

Two modes, both defined in `theme.json` (dark, the baseline) and
`assets/css/remotive.css` (light, an override — see `readme.md` for the
mechanism). Source of truth for each hex value:

### Dark mode — "Registration" (CMYK direction, default)

Aligned to `remotive-reporting` (the skill governing every client-facing
deck/report) as of v1.9.0, per explicit request. Was previously its own
separate palette sampled from an early concept deck, not the actual
reporting brand — see `docs/changelog.md` v1.9.0 for the full before/after.

| Token | Value | Source |
|---|---|---|
| `paper` | `#1a1a2e` | `remotive-reporting` §1.1, "Background (primary dark)" |
| `paper-2` | `#0d1117` | `remotive-reporting` §1.1, "Background (deep navy alt)" |
| `card` | `#252542` | Derived — lifted navy; the reporting skill doesn't define a "card" surface |
| `ink` | `#ffffff` | `remotive-reporting` §1.1, "Text on dark" — was cream before; the skill bans cream/beige backgrounds and specifies pure white for dark-bg text |
| `cyan` | `#00aeef` | `remotive-reporting` §1.1, Accent 2 |
| `cyan-dark` | `#00aeef` | Same as base — already clears 4.5:1 on navy, no lightening needed |
| `magenta` | `#ec008c` | `remotive-reporting` §1.1, Accent 1 |
| `magenta-dark` | `#ff0198` | Lightened variant for small text-on-navy use — base is 4.02:1 on navy (under the 4.5:1 minimum), lightened clears 4.64:1 |
| `accent-3` | `#39b54a` | `remotive-reporting` §1.1, Accent 3 (green). **Not** named "yellow" or "green" — see note below. |
| `accent-3-dark` | `#39b54a` | Same as base — already clears 4.5:1 on navy |
| `contrast-bg` | `#1a1a2e` | Fixed navy — **not** overridden in light mode. Used by the CTA band and every solid button, so those stay visually consistent regardless of site-wide mode. |
| `contrast-text` | `#ffffff` | Fixed white — the text pairing for `contrast-bg`. |
| Display font | Archivo | **Diverges from the skill's Arial Black** — Arial Black is largely absent on non-Windows systems by default; Archivo (self-hosted, renders identically everywhere) is the close cousin used instead. Documented divergence. |
| Pull-quote serif | Newsreader | **Also diverges** — the skill specifies no serif anywhere. Scoped to pull-quotes/drop-caps only, never a title, which is technically compliant with the skill's specific title rule even if not its zero-serif spirit. Kept deliberately. |

**Why `accent-3`, not `green`:** light mode's third accent is genuinely
yellow (`#f2f216`, from the *different* brand source — `remotive_brand_5.json`),
while dark mode's is genuinely green (`#39b54a`, from `remotive-reporting`).
Since both modes share one CSS variable name, naming it `green` would mean
light mode stores a yellow color inside a variable literally called green
— the same class of bug that broke the CTA section during the v1.2.0
`ink`/`paper` rework (see that gotcha further down this file).

### Light mode — Remotive Media Asia brand system

Source: `remotive_brand_5.json`, the brand's official PPTX/document design
spec (colours, fonts, logo usage rules for slide decks and documents).

| Token | Value | Brand JSON field |
|---|---|---|
| `paper` | `#f7f4ec` | Cream, **not** the brand JSON's literal `primary: #ffffff` — overridden per explicit request to keep the site's cream ground in light mode too |
| `card` | `#ffffff` | — |
| `paper-2` | `#f0f8ff` | brand JSON `tables.row_alt_background` |
| `ink` | `#1e1e1e` | brand JSON `colors.secondary` / `text` |
| `cyan` | `#00a2ff` | brand JSON `colors.accent_1` |
| `cyan-dark` | `#00308f` | brand JSON `colors.accent_2` |
| `magenta` | `#ff449f` | brand JSON `colors.accent_5` |
| `magenta-dark` | `#cd360b` | Darkened from brand JSON `colors.accent_6` (`#f2410f`) — the literal brand value failed WCAG 1.4.3 as text (3.45:1 against the cream background, needs 4.5:1). Same hue, darkened until it cleared 4.5:1. See `docs/changelog.md` v1.7.0. |
| `accent-3` (yellow in this mode) | `#f2f216` | brand JSON `colors.accent_3` |
| `accent-3-dark` (green in this mode) | `#008110` | Darkened from brand JSON `colors.accent_4` (`#00aa15`) — the literal brand value failed even the 3:1 large-text minimum (2.83:1). Same hue, darkened until it cleared 4.5:1. |
| Font (all roles) | Saira | brand JSON `fonts.heading/subheading/body/caption` — brand spec uses one typeface throughout, no serif accent. **Scoped exception, v1.66.5:** footer column headings (`.rm-footer__heading` in `remotive.css`) use Space Grotesk as a small accent; everything else on the site stays Saira, matching the brand JSON. (v1.66.4 briefly swapped the whole site to Space Grotesk; reverted the same session once it became clear only the footer headings were meant to change.) |

**If the brand JSON (`remotive_brand_5.json`) is ever updated** — new accent
colours, a font change — light mode's tokens above must be updated to match,
and this table updated in the same commit/session so it doesn't drift.


**Corner radii (v1.93.0).** The design is rounded, not square: `--rm-r-sm`
10px (form fields), `--rm-r` 16px (cards, images), `--rm-r-lg` 24px (large
panels, the landing-page form cards) and `--rm-r-pill` 999px (buttons and
chips), defined at the end of `assets/css/remotive.css`. Full-bleed bands stay
edge to edge. CTAs carry no underline.

**Slugs are roles, and the colours are configurable (v1.95.0).** `ink` is the
text colour and `paper` the page background in whichever mode is showing; the
`-dark` variants are the stronger accent used for text and buttons, and are
only darker than the plain colour in light mode (in dark mode `cyan-dark` equals
`cyan`). Theme Options → Colours sets all twelve per mode and checks contrast;
only values that differ from the tables above are written to the site. The
defaults exist in three places that must change together: the dark values in
`theme.json`, the light overrides in `assets/css/remotive.css` and
`assets/css/critical.css`, and `remotive_colour_tokens()` in `inc/options/colours.php`.
The block editor's palette (`theme.json`) does not follow the Colours tab.

## Admin-configurable values (as of v1.79.1)

Contact email, the three footer address lines, the seven social URLs, the
CTA form's submission URL, the default colour mode, enquiry retention
period, branded login, the twelve country-ticker settings (countries,
visibility, colours, size, weight, tracking, padding, speed, direction),
scroll motion, graceful error handling, maintenance mode and the Pexels API key (a write-only secret, never printed back) are **no longer hardcoded** — they're stored
in the `remotive_theme_options` WordPress option, editable at
Appearance → Theme Options, with defaults matching the values in this
document.

**Tabs added since v1.79.1.** Colours (v1.95.0, the palette per mode with a
contrast check; see `readme.md`), Integrations (v1.91.0, holds the Pexels API
key) and a maintenance-mode switch under Site behaviour (v1.88.0). The ticker
default now lists six markets (Singapore, Malaysia, Thailand, Vietnam, Hong
Kong, China); a site that has saved its own ticker list keeps it.

**Where to find them (restructured v1.79.1).** Theme Options has four tabs:
**Homepage** (with Hero, Section headings, Numbers and Team as groups
within it), **Business details**, **Country ticker**, and **Site
behaviour**. Before v1.79.1 there were seven; four of them all edited the
homepage, so changing one page meant moving between four tabs. Two were
also renamed to match their contents — Display became Site behaviour
(it holds lead retention, branded login and error handling), and Contact &
Socials became Business details (it also holds the legal name and UEN).
The field set was 57 at v1.79.1 and is 94 stored settings as of v1.96.0: the
twenty-four colours and a reset switch (Colours, v1.95.0), maintenance mode
(v1.90.0), the Pexels API key and its remove switch (Integrations, v1.91.0),
and other additions recorded in `docs/changelog.md`. The Pexels key is a write-only
secret: stored in the options table, never printed back into a page, never
exposed through REST, and not used by the front end.

**Ticker colour defaults are mode-specific.** `ticker_bg` (`#1a1a2e`) and
`ticker_color` (`#f7f4ec`) describe the light-mode band — navy on cream.
They are emitted as inline CSS only when changed from these defaults,
because emitting them always overrode the stylesheet's mode-aware rule and
made the ticker invisible in dark mode. An untouched install inherits the
inverting rule; a deliberate choice is honoured in both modes.

That means **this file describes the shipped defaults, not
necessarily the live values** once someone edits them in wp-admin. If the
two ever disagree, wp-admin is correct for what's actually live; this file
stays correct for what the entity's real legal/contact facts are. Update
both when the underlying fact changes (e.g. the registered address).

## Logo assets

| File | Source | Use |
|---|---|---|
| `assets/images/remotive-mark.png` | Cropped from `Remotive_logo_landscape.png` (tight bounding box, alpha-transparent) | Nav, footer, hero watermark |
| `assets/images/remotive-lockup-square.jpg` | `remotivemedia_logo.jpg` (white background, square) | Favicon / Site Icon |
| `assets/images/remotive-logo-112/168` `.png` and `.avif` | Derived from the mark | Header (1x, 2x, 3x) and the ad landing pages. The artwork's wordmark is dark, so in dark mode it sits on a white plate (header since v1.92.0, landing pages since v1.90.0) |

Both are bundled in the theme and sideloaded into the media library on
first activation (see `readme.md` → Logo bootstrap).

## Deviations from this document

Recorded here so a future maintainer does not "fix" a deliberate choice
back to the spec.

| Item | Spec | Shipped | Why |
|---|---|---|---|
| Body typeface | Saira (`remotive_brand_5.json`) | Saira site-wide; Space Grotesk scoped to `.rm-footer__heading` only | Accent contrast in the footer; documented deviation, brand JSON unchanged |
| CTA band colour | Single navy `contrast-bg` everywhere | Navy, plus magenta and cyan variants on six pages | Visual variance across a long scroll; see `docs/changelog.md` v1.67.3–v1.67.4 |
| CTA band text on coloured variants | White | Light mode: ink (`#1e1e1e`). Dark mode: near-black (`#14141f`) | White fails WCAG on the real palette values: 2.76:1 on light `--cyan` (`#00a2ff`), 3.19:1 on light `--magenta` (`#ff449f`); ink gives 6.04:1 and 5.22:1. In dark mode the bands are the *bright* `#00aeef` and `#ff0198` (the `-dark` tokens are not dark there), where white was 2.53:1 and 3.68:1; near-black gives about 7:1 and 4.9:1 (v1.92.0) |
| Corners | Not specified (the "Registration" direction is sharp-edged) | Rounded: 10px fields, 16px cards and images, pill buttons and chips (v1.93.0) | Requested design change; full-bleed bands stay edge to edge |
| CTA links and buttons | Not specified | No underline in any state; the animated underline under text-link CTAs was removed (v1.93.0) | Requested design change; the arrow, uppercase bold type and hover colour carry the affordance |
| Landing-page photography | Not specified | Pexels photos, self-hosted, credited in `docs/image-credits.md` (v1.91.0) | Not Remotive's own work, so it is listed separately in `docs/resources.md` |
| Markets named on the landing pages | `areaServed` in structured data is Singapore and Malaysia | Six: Singapore, Malaysia, Thailand, Vietnam, Hong Kong, China (v1.91.0) | The landing pages and the country ticker default name six markets; the schema `areaServed` has not been widened. Decide whether it should be |

**Palette values that have caught people out.** `--magenta-dark` is
`#cd360b` — an orange-red, not a darker magenta. `--accent-3` is `#f2f216`
(yellow) while `--accent-3-dark` is `#008110` (green). Neither pair is a
tint of the other. Always read the resolved values from
`assets/css/critical.css` before calculating contrast; do not assume from
the token name.

## Versioning & file naming

- **Repository root:** only `style.css`, `theme.json`, `functions.php`,
  `screenshot.png`, `readme.txt` and `readme.md`. Every other document lives in
  `docs/` (moved there in v1.97.0, so those paths in the document map are
  `docs/…`).
- Semver (`MAJOR.MINOR.PATCH`), tracked in `style.css`'s `Version:` field
  and mirrored in `docs/changelog.md`.
- Release zip naming convention: `[theme-name]-[version].zip`, all
  lowercase, theme name matching the `Theme Name:` header with spaces
  replaced by hyphens — e.g. `remotive-media-1.2.0.zip`.
- Two other numbers move independently of the theme version: the migration
  schema, `REMOTIVE_SETUP_SCHEMA` in `inc/setup/site-setup.php` (1.92.0 as of
  v1.96.0), which only changes when an already-set-up site needs a migration,
  and the rewrite-rule version, the `remotive_lp_rewrite_v` option (2), which
  only changes when the landing-page URL rules change.
- Every pull request runs `.github/workflows/ci.yml` (PHP syntax on 7.4 and
  8.3, JavaScript syntax, valid `theme.json`, and `tests/check-landing-copy.php`).
- The zip's top-level folder is `remotive` (matches `wp-content/themes/remotive`),
  independent of the zip filename or the `Theme Name:` display string.

## Security audit record

### Log-driven hardening (v1.68.1, 2026-08-31)

A second pass driven by the site's own access, SSL and FTP logs for
Aug 2026 rather than by a code review. What the logs actually showed:

| Observation | Volume | Response |
|---|---|---|
| `wp-login.php` brute force from `103.138.189.98` | 271 hits | Login rate limit in `inc/core/security.php`; **IP block still required at firewall — a theme cannot do this** |
| 500s on `/wp-admin/install.php` from one Cloudflare IPv6 | 91 | Endpoint redirected to home on `admin_init` |
| `setup-config.php` returning 200 to external IPs | 2 IPs | Same redirect |
| `xmlrpc.php` probes | 7 hits, 5 IPs | XML-RPC disabled entirely; server-level deny also recommended |
| Failed auth responses (401) | 2,205 | Rate limit; login-limiting plugin still advised |
| `c99` webshell probes | 3, all 404 | Nothing present; noted only |
| FTP activity | 8,848 transfers, 1 IP, `remotive` account | Site build; no anomaly |

Outstanding server-level actions, unchanged by any theme release:

- Block `103.138.189.98` at firewall or `.htaccess`
- Deny `xmlrpc.php` at server level (stops PHP executing at all)
- Deny `wp-admin/install.php` at server level
- `WP_DEBUG_DISPLAY` false and `display_errors` off in production

### Lead forms and landing pages (v1.90.0 – v1.96.0)

Recorded as findings and fixes, so a later maintainer knows what was decided.

| Finding | Decision |
|---|---|
| Landing and homepage forms embed a nonce that lives 12 to 24 hours; a cached page older than that rejected a real lead as "link expired" | Fixed in v1.94.0 without weakening the check: `assets/js/remotive.js` fetches current nonces from the uncacheable `remotive_form_nonces` endpoint on page load, and a form submitted before it returns waits for it. The server-rendered nonce stays as the no-script fallback. The endpoint returns nothing secret (a logged-out nonce is the same for everyone and authorises only its own form). Removing the nonce check was considered and rejected |
| The rate limit (3 per IP per ten minutes) blocks real visitors behind carrier-grade NAT | Per-form limit (`rate_limit`); landing forms allow 10. The spam-trap check now runs before the limit so bot traffic cannot use up the quota. The counter is a read-then-write transient and can let one extra enquiry through under a simultaneous burst; accepted, it cannot block a real one |
| `maybe_unserialize()` on custom fields a contributor can edit (`schema-markup.php`) | Removed in v1.94.0; a still-serialised value is read with no classes allowed |
| Maintenance mode could lock out the owner | It exempts users who can edit posts, wp-admin, cron, AJAX, feeds and REST, and never runs on `wp-login.php`; it returns 503 with `Retry-After` so search engines keep the indexed pages |
| Pexels API key in the settings | Write-only: never printed back, blank keeps the saved key, a switch removes it; not exposed through REST; the front end does not use it. It was pasted into chat once, so rotating it is advisable |
| Colours tab input | Hex only (`sanitize_hex_color`), stored as lowercase `#rrggbb`; anything else, including CSS injection attempts, falls back to the default |
| Landing pages are public and cached | `noindex, nofollow` by meta, `X-Robots-Tag` and Rank Math; excluded from site search and the core sitemap; never blocked in `robots.txt` (crawlers must fetch the page to see the noindex, and Google Ads must fetch it to review the ad) |

### Original code audit

| Field | Value |
|---|---|
| Current version | 1.8.0 |
| Previous version | 1.7.0 |
| Audit date | 2026-08-26 |
| Release type | Minor (new settings/defaults — the CTA form's default handler and `cta_form_action`'s default value both changed; see `docs/changelog.md`) |
| Scope reviewed | `functions.php`, `inc/options/theme-options.php`, `inc/forms/cta-form-handler.php`, and every enqueued JS file. No `$wpdb`/SQL, file uploads, REST routes, or custom authentication exist anywhere in this theme, so those categories had no surface to review. |

**Security summary**

| | Count |
|---|---|
| Confirmed findings | 3 |
| Fixed (implemented) | 3 |
| Proposed (not implemented) | 1 |
| False positives | 0 |
| Residual risks | 2 |

**Confirmed findings, all fixed**

1. **Missing escape-on-output for admin-configurable values.**
   `remotive_replace_theme_option_tokens()` sanitized on save
   (`esc_url_raw`/`sanitize_email`/`sanitize_text_field`) but injected
   the stored value directly into rendered HTML via `strtr()` with no
   re-escaping. Fixed: `esc_url()` added for URL fields, `esc_html()`
   for text fields, at the point of output — independent of whatever the
   save-time sanitizer does. No live exploit confirmed (the affected
   fields all require `edit_theme_options` capability to set); fixed as
   defense-in-depth regardless, per "sanitize on input, escape on
   output" being two separate, independent steps.
2. **No CRLF header-injection guard on the CTA form's `Reply-To`
   header.** `sanitize_email()`/`is_email()` should already prevent this
   (neither permits control characters in a valid address), but the code
   didn't make that guarantee explicit or independent of their internal
   behavior. Fixed: an explicit `\r`/`\n` rejection added before the
   value reaches `wp_mail()`.
3. **No rate limiting on the CTA form's public POST endpoint.** Fixed:
   a transient-based throttle (3 submissions per IP per 10 minutes).
   Documented as a real but imperfect mitigation — `REMOTE_ADDR` can be
   spoofed or shared behind a proxy — not a guarantee.

**Proposed, not implemented**

- CAPTCHA (hCaptcha/Turnstile) or WAF-level rate limiting for the CTA
  form, for full bot resistance beyond what the nonce/honeypot/rate-limit
  combination provides. Not implemented because vendor selection is a
  product/UX decision, not something to make unilaterally in theme code.

**Residual risks**

1. A scripted client that loads the real page first (for a valid nonce)
   can still submit the CTA form repeatedly, just throttled to the rate
   limit. Only a CAPTCHA closes this fully — see "Proposed" above.
2. `register_setting()` doesn't specify an explicit capability, so the
   Settings API's save-path check (`options.php`, core) defaults to
   `manage_options` while this theme's own page-access check uses
   `edit_theme_options`. Identical in a stock install; would only
   diverge on a site with customized role capabilities. Not fixed
   without a live WordPress install to verify the correct mechanism
   against.

**Test status:** every fix verified against actual computed output —
PHP brace/paren balance checked for every changed file, block-template
tag balance re-verified, the honeypot's invisibility confirmed via
`getBoundingClientRect()` (not just visual inspection), the ticker
pause/resume behavior confirmed via a real scroll-triggered
`IntersectionObserver` test, the `.pot` file's syntax verified
line-by-line after a bug in the first extraction attempt produced
garbled comments (caught before packaging, not after).

**Release readiness:** version bumped (1.8.0, all four locations:
`style.css`, `readme.txt` Stable tag, `readme.txt` "Latest version"
line, and this file) · changelog updated · upgrade notice added ·
readme.md and readme.txt updated · no sensitive exploit details in any
public-facing doc · package contains no VCS artifacts, backups, or debug
files (re-verified this pass, not just carried over from the last check).

## Structured data (v1.26.0)

`inc/content/schema-markup.php` publishes the entity facts in this file as
schema.org JSON-LD: `legalName` is the ACRA registered name above
(title-cased), the address is read from Theme Options (shipped defaults
= the registered office above), `areaServed` is Singapore and Malaysia
per the SEO content strategy section. If the legal entity facts change,
update this file, the Theme Options defaults, and the `legalName`
constant in `inc/content/schema-markup.php` together. As of v1.27.0 the schema
also publishes the ACRA incorporation date (2024-01-31) as
`foundingDate` and the UEN (202404376G) as an `identifier` — both from
the Legal entity table above — and types the organization
ProfessionalService over the registered office. The full
Google-feature coverage matrix lives in readme.md.

The ad landing pages (v1.91.0) publish a separate `FAQPage` JSON-LD block,
generated from the same items as the visible FAQ so the two cannot drift, in
the language of the URL (`inLanguage`). The pages are `noindex`, so search
engines ignore the markup; it is correct and ready if a page is ever made
indexable. They also declare `hreflang` alternates (`en`, `ms`, `zh-Hans`,
`zh-Hant`, `x-default`) and a self-referencing canonical per language.

## Rank Math meta coverage (v1.66.2)

**Listed case studies (v1.98.0).** Six are listed, in one uncategorised grid, in
the footer and on the home page; `remotive_listed_case_studies()` in
`inc/setup/classic-menus.php` is the single list for the menu. The other eight case-study
pages stay published at their URLs but nothing links to them.

Every page and post the theme ships or creates carries a Rank Math
title, description and focus keyword, written via
`rank_math_title` / `rank_math_description` / `rank_math_focus_keyword`
postmeta, all within Rank Math's 50-character title and 130-character
description limits:

- The 23 seeded blog posts and case studies: set by `content-seed.php`
  from the `rm_title`/`rm_desc`/`rm_kw` keys in `content-seed-data.php`.
  As of v1.66.2, all 23 also pass Rank Math's full on-page checklist:
  a real, non-generic focus keyword present in the title, description,
  body content and a subheading; 600+ words; at least one internal and
  one external link. No two items share a focus keyword.
- The 9 auto-created core pages (Home, Services, Case Studies, About,
  Team, Contact, Insights, FAQ, Privacy, Terms): set by
  `remotive_run_site_setup()` in `inc/setup/site-setup.php` from the same
  three keys, added to `remotive_required_pages()`. Privacy and Terms
  carry no focus keyword by design. These pages have title/description/
  keyword meta but are not scored against Rank Math's on-page content
  checklist (word count, keyword density, subheadings): their
  `post_content` is intentionally empty, per the self-contained-
  template convention below, so there is no post content for the
  checklist to analyse. Reaching a 90+ Rank Math score there would
  require duplicating template copy into `post_content`, which the
  project has deliberately not done; see `docs/changelog.md` v1.66.2 for
  the tradeoff.
- The two market landing pages were removed in v1.79.3, so they are not
  covered here. The same on-page-checklist limitation as the core
  pages applies once they are.

- The ad landing pages (`seo-audit`, `google-ads-management`,
  `paid-social-advertising`, `audit-requested`) deliberately carry **no** Rank
  Math title, description or keyword: they are `noindex, nofollow`, so there is
  nothing to score. The theme also forces Rank Math's robots output to the same
  answer (`inc/landing/landing-pages.php`).

Both site-setup paths fill in only meta keys that are currently
empty, never overwriting a value already written by hand.

## Ad landing pages (v1.90.0 – v1.96.0)

Canonical facts for the paid and social landing pages. Code: `inc/landing/landing-pages.php`
(routing, rendering, form, SEO rules) and `inc/landing/landing-copy.php` (the words).

| Service | English URL | Page slug |
|---|---|---|
| SEO | `/seo-audit/` | `seo-audit` |
| Google Ads | `/google-ads-management/` | `google-ads-management` |
| Paid social | `/paid-social-advertising/` | `paid-social-advertising` |
| Confirmation (conversion URL) | `/audit-requested/` | `audit-requested` |

- **Languages**, each on its own path: English (no prefix), Bahasa Melayu
  `/ms/`, Simplified Chinese `/zh-cn/` (`zh-Hans`), Traditional Chinese
  `/zh-tw/` (`zh-Hant`). The server renders only the language of the URL. There
  is no `?lang=` parameter and no script-based switching. The Malay and Chinese
  copy is a first draft until a native speaker has reviewed it.
- **Markets named:** Singapore, Malaysia, Thailand, Vietnam, Hong Kong, China
  (six). See the deviations table for how this relates to structured data.
- **Funnel:** form above the fold, how it works, six market photo tiles, a
  second form, an FAQ. No site navigation. The form records the service, the
  visitor's website and the campaign parameters (`utm_*`, `gclid`, `fbclid`,
  `ttclid`) with the enquiry, then redirects to `/audit-requested/` in the
  same language.
- **Events** (for a tag manager): `remotive_lp_view` and
  `remotive_lp_form_start` on the landing pages; `remotive_lead` on the
  confirmation page with `form`, `service` and `language`.
- **Rights:** the photography is from Pexels (credited in
  `docs/image-credits.md`, rights in `docs/resources.md`); the logo is Remotive's own.
- **Requirements:** pretty permalinks (the language paths are rewrite rules).
  Keep the pages out of `robots.txt`.

## Document map

| File | Audience | Purpose |
|---|---|---|
| `readme.txt` | General / WP admin users | Plain-language description, install steps, FAQ |
| `readme.md` | Developers, hosting, sysadmins | Architecture, file map, implementation gotchas |
| `docs/ssot.md` | Anyone maintaining brand/entity accuracy | This file — canonical facts |
| `docs/upgrading.md` | Future maintainers, MENJ | Roadmap, planned work, not-yet-built ideas |
| `docs/changelog.md` | Everyone | Version history |
| `docs/resources.md` | Legal, anyone redistributing/auditing the theme | Consolidated license/copyright for the theme and every bundled font/image |
| `docs/image-credits.md` | Legal, editors | Photographer and Pexels photo for every landing-page image |
| `tests/check-landing-copy.php` | Developers, CI | Fails if any landing-page text is missing one of its four languages |
| `.github/workflows/ci.yml` | Developers | The checks every pull request runs |

## PHP module layout (v1.100.0)

`inc/` holds the theme's PHP, in six folders. `functions.php` requires each file
by path, error handler first.

| Folder | Files |
|---|---|
| `inc/core/` | `error-handler`, `security`, `accessibility`, `avif`, `branded-login` |
| `inc/options/` | `theme-options`, `colours`, `maintenance-mode` |
| `inc/setup/` | `site-setup`, `classic-menus`, `content-seed`, `content-seed-data` |
| `inc/forms/` | `lead-form-handler`, `cta-form-handler`, `about-form-handler`, `contact-form-handler`, `leads`, `akismet`, `thank-you` |
| `inc/landing/` | `landing-pages`, `landing-copy` |
| `inc/content/` | `schema-markup`, `webmcp`, `feature-grids`, `stats-band` |

A new module goes in the folder that matches its job and gets one `require`
line in `functions.php`. Older entries in `docs/changelog.md` and the security
record name the files by their earlier flat paths.

## SEO content strategy (v1.12.0)

Built against `remotivemedia-keyword-selection.xlsx` (90 keywords: 30
Singapore, 30 Malaysia, 30 generic — provided by the client, competitor-
validated keyword research, not third-party content requiring
attribution). Selection rule per the file's own "Read me" sheet:
commercial/transactional keywords throughout, informational keywords
kept only above 500 monthly search volume.

**Decision reversed in v1.79.3: the two market landing pages were
removed.** `page-seo-singapore.html` and `page-digital-marketing-malaysia.html`
each covered their market's full commercial keyword cluster on one page.
Both templates, their auto-creation entries, their schema and the fifteen
internal links pointing at them are gone. The reasoning that produced them
still holds for anything built later — one consolidated page per market
rather than one page per keyword, since near-duplicate pages targeting the
same intent cannibalise each other and amount to a doorway-page pattern —
but the market pages themselves are no longer part of the shipped content.
Market context now lives in the service pages and the blog posts.

**Decision: genuinely informational clusters became blog posts, not
pages.** Four posts (delivered separately as `remotive-blog-seed-content.xml`,
not bundled in the theme zip — content and theme code are kept
separate): SEO cost in Singapore, SEO pricing in Malaysia, "is your
website SEO-friendly," and SEO vs SEM. See that file's own README for
the full keyword-to-post mapping and volumes.

**Documented, not silently ignored: the web-design keyword gap.** Six
Malaysia keywords (`web design malaysia`, `website design malaysia`,
`ecommerce website design malaysia`, `web design kl`, `web design company
malaysia`, `website design company kuala lumpur` — combined volume over
9,000) are untargeted anywhere. Remotive doesn't offer web design as a
service; the Malaysia landing page says so explicitly in its own FAQ.
Revisit only if that service offering changes — tracked in
`docs/upgrading.md`.
