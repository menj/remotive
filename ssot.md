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
reporting brand — see `changelog.md` v1.9.0 for the full before/after.

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
| `magenta-dark` | `#cd360b` | Darkened from brand JSON `colors.accent_6` (`#f2410f`) — the literal brand value failed WCAG 1.4.3 as text (3.45:1 against the cream background, needs 4.5:1). Same hue, darkened until it cleared 4.5:1. See `changelog.md` v1.7.0. |
| `yellow` | `#f2f216` | brand JSON `colors.accent_3` |
| `yellow-dark` | `#008110` | Darkened from brand JSON `colors.accent_4` (`#00aa15`) — the literal brand value failed even the 3:1 large-text minimum (2.83:1). Same hue, darkened until it cleared 4.5:1. |
| Font (all roles) | Saira | brand JSON `fonts.heading/subheading/body/caption` — brand spec uses one typeface throughout, no serif accent. **Scoped exception, v1.66.5:** footer column headings (`.rm-footer__heading` in `remotive.css`) use Space Grotesk as a small accent; everything else on the site stays Saira, matching the brand JSON. (v1.66.4 briefly swapped the whole site to Space Grotesk; reverted the same session once it became clear only the footer headings were meant to change.) |

**If the brand JSON (`remotive_brand_5.json`) is ever updated** — new accent
colours, a font change — light mode's tokens above must be updated to match,
and this table updated in the same commit/session so it doesn't drift.

## Admin-configurable values (as of v1.79.1)

Contact email, the three footer address lines, the seven social URLs, the
CTA form's submission URL, the default colour mode, enquiry retention
period, branded login, the twelve country-ticker settings (countries,
visibility, colours, size, weight, tracking, padding, speed, direction),
scroll motion, graceful error handling, maintenance mode and the Pexels API key (a write-only secret, never printed back) are **no longer hardcoded** — they're stored
in the `remotive_theme_options` WordPress option, editable at
Appearance → Theme Options, with defaults matching the values in this
document.

**Tabs added since v1.79.1.** Integrations (v1.91.0, holds the Pexels API
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
The field set is unchanged at 57.

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

Both are bundled in the theme and sideloaded into the media library on
first activation (see `readme.md` → Logo bootstrap).

## Deviations from this document

Recorded here so a future maintainer does not "fix" a deliberate choice
back to the spec.

| Item | Spec | Shipped | Why |
|---|---|---|---|
| Body typeface | Saira (`remotive_brand_5.json`) | Saira site-wide; Space Grotesk scoped to `.rm-footer__heading` only | Accent contrast in the footer; documented deviation, brand JSON unchanged |
| CTA band colour | Single navy `contrast-bg` everywhere | Navy, plus magenta and cyan variants on six pages | Visual variance across a long scroll; see `changelog.md` v1.67.3–v1.67.4 |
| CTA band text on coloured variants | White | Ink (`#1e1e1e`) | White fails WCAG on the real palette values: 2.76:1 on `--cyan` (`#00a2ff`), 3.19:1 on `--magenta` (`#ff449f`). Ink gives 6.04:1 and 5.22:1 |

**Palette values that have caught people out.** `--magenta-dark` is
`#cd360b` — an orange-red, not a darker magenta. `--accent-3` is `#f2f216`
(yellow) while `--accent-3-dark` is `#008110` (green). Neither pair is a
tint of the other. Always read the resolved values from
`assets/css/critical.css` before calculating contrast; do not assume from
the token name.

## Versioning & file naming

- Semver (`MAJOR.MINOR.PATCH`), tracked in `style.css`'s `Version:` field
  and mirrored in `changelog.md`.
- Release zip naming convention: `[theme-name]-[version].zip`, all
  lowercase, theme name matching the `Theme Name:` header with spaces
  replaced by hyphens — e.g. `remotive-media-1.2.0.zip`.
- The zip's top-level folder is `remotive` (matches `wp-content/themes/remotive`),
  independent of the zip filename or the `Theme Name:` display string.

## Security audit record

### Log-driven hardening (v1.68.1, 2026-08-31)

A second pass driven by the site's own access, SSL and FTP logs for
Aug 2026 rather than by a code review. What the logs actually showed:

| Observation | Volume | Response |
|---|---|---|
| `wp-login.php` brute force from `103.138.189.98` | 271 hits | Login rate limit in `inc/security.php`; **IP block still required at firewall — a theme cannot do this** |
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

### Original code audit

| Field | Value |
|---|---|
| Current version | 1.8.0 |
| Previous version | 1.7.0 |
| Audit date | 2026-08-26 |
| Release type | Minor (new settings/defaults — the CTA form's default handler and `cta_form_action`'s default value both changed; see `changelog.md`) |
| Scope reviewed | `functions.php`, `inc/theme-options.php`, `inc/cta-form-handler.php`, and every enqueued JS file. No `$wpdb`/SQL, file uploads, REST routes, or custom authentication exist anywhere in this theme, so those categories had no surface to review. |

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

`inc/schema-markup.php` publishes the entity facts in this file as
schema.org JSON-LD: `legalName` is the ACRA registered name above
(title-cased), the address is read from Theme Options (shipped defaults
= the registered office above), `areaServed` is Singapore and Malaysia
per the SEO content strategy section. If the legal entity facts change,
update this file, the Theme Options defaults, and the `legalName`
constant in `inc/schema-markup.php` together. As of v1.27.0 the schema
also publishes the ACRA incorporation date (2024-01-31) as
`foundingDate` and the UEN (202404376G) as an `identifier` — both from
the Legal entity table above — and types the organization
ProfessionalService over the registered office. The full
Google-feature coverage matrix lives in readme.md.

## Rank Math meta coverage (v1.66.2)

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
  `remotive_run_site_setup()` in `inc/site-setup.php` from the same
  three keys, added to `remotive_required_pages()`. Privacy and Terms
  carry no focus keyword by design. These pages have title/description/
  keyword meta but are not scored against Rank Math's on-page content
  checklist (word count, keyword density, subheadings): their
  `post_content` is intentionally empty, per the self-contained-
  template convention below, so there is no post content for the
  checklist to analyse. Reaching a 90+ Rank Math score there would
  require duplicating template copy into `post_content`, which the
  project has deliberately not done; see `changelog.md` v1.66.2 for
  the tradeoff.
- The two market landing pages were removed in v1.79.3, so they are not
  covered here. The same on-page-checklist limitation as the core
  pages applies once they are.

Both site-setup paths fill in only meta keys that are currently
empty, never overwriting a value already written by hand.

## Document map

| File | Audience | Purpose |
|---|---|---|
| `readme.txt` | General / WP admin users | Plain-language description, install steps, FAQ |
| `readme.md` | Developers, hosting, sysadmins | Architecture, file map, implementation gotchas |
| `ssot.md` | Anyone maintaining brand/entity accuracy | This file — canonical facts |
| `upgrading.md` | Future maintainers, MENJ | Roadmap, planned work, not-yet-built ideas |
| `changelog.md` | Everyone | Version history |
| `resources.md` | Legal, anyone redistributing/auditing the theme | Consolidated license/copyright for the theme and every bundled font/image |

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
`upgrading.md`.
