# SSOT, Remotive Media theme

This is the canonical reference for facts about this theme, the brand it
implements, and the legal entity behind it. If any other document (readme.txt,
readme.md, a slide deck, a client-facing brief) ever conflicts with what is
written here, **this file is correct and the other document is out of date.**

Last verified: theme v1.120.3.

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
previously-used placeholder city, Kuala Lumpur was used in early drafts
of this theme and is **not** the registered address.

## Domains

| Domain | Use |
|---|---|
| `remotivemedia.asia` | This website. |
| `github.com/menj/remotive` | The theme's repository. `Theme URI` in `style.css` points here. |
| `hello@remotivemedia.asia` | The contact address. Default of the `contact_email` option; migration 1.103.1 replaces a saved `hello@remotivemedia.com`. |
| `menj.blog` | Theme author's (MENJ) personal site. `Author URI` in `style.css`. |

If these domains diverge further in the future (e.g. the contact address moves), update this table first, then propagate to
`parts/footer.html` and `readme.txt`.

## Design tokens

Two modes, both defined in `theme.json` (dark, the CSS baseline) and
`assets/css/remotive.css` (light, an override, see `readme.md` for the
mechanism). **Light is the shipped default a first-time visitor sees since
v1.120.3** (`default_theme`, Theme Options → Site behaviour → Default colour
mode); dark was the default up to v1.120.2, and migration 1.120.3
moves a site still on `dark` to `light` once, leaving `system` alone. The CSS
baseline and the shipped default are different things: the stylesheet is still
written dark-first. Source of truth for each hex value:

### Dark mode, "Registration" (CMYK direction, the CSS baseline)

Aligned to `remotive-reporting` (the skill governing every client-facing
deck/report) as of v1.9.0, per explicit request. Was previously its own
separate palette sampled from an early concept deck, not the actual
reporting brand, see `docs/changelog.md` v1.9.0 for the full before/after.

| Token | Value | Source |
|---|---|---|
| `paper` | `#1a1a2e` | `remotive-reporting` §1.1, "Background (primary dark)" |
| `paper-2` | `#0d1117` | `remotive-reporting` §1.1, "Background (deep navy alt)" |
| `card` | `#252542` | Derived, lifted navy; the reporting skill does not define a "card" surface |
| `ink` | `#ffffff` | `remotive-reporting` §1.1, "Text on dark", was cream before; the skill bans cream/beige backgrounds and specifies pure white for dark-bg text |
| `cyan` | `#00aeef` | `remotive-reporting` §1.1, Accent 2 |
| `cyan-dark` | `#00aeef` | Same as base, already clears 4.5:1 on navy, no lightening needed |
| `magenta` | `#ec008c` | `remotive-reporting` §1.1, Accent 1 |
| `magenta-dark` | `#ff0198` | Lightened variant for small text-on-navy use, base is 4.02:1 on navy (under the 4.5:1 minimum), lightened clears 4.64:1 |
| `accent-3` | `#39b54a` | `remotive-reporting` §1.1, Accent 3 (green). **Not** named "yellow" or "green", see note below. |
| `accent-3-dark` | `#39b54a` | Same as base, already clears 4.5:1 on navy |
| `contrast-bg` | `#1a1a2e` | Fixed navy, **not** overridden in light mode. Used by the CTA band and every solid button, so those stay visually consistent regardless of site-wide mode. |
| `contrast-text` | `#ffffff` | Fixed white, the text pairing for `contrast-bg`. |
| Display font | Archivo | **Diverges from the skill's Arial Black**, Arial Black is largely absent on non-Windows systems by default; Archivo (self-hosted, renders identically everywhere) is the close cousin used instead. Documented divergence. |
| Pull-quote serif | Newsreader | **Also diverges**, the skill specifies no serif anywhere. Scoped to pull-quotes/drop-caps only, never a title, which is technically compliant with the skill's specific title rule even if not its zero-serif spirit. Kept deliberately. |

**Why `accent-3`, not `green`:** light mode's third accent is
yellow (`#f2f216`, from the *different* brand source, `remotive_brand_5.json`),
while dark mode's is green (`#39b54a`, from `remotive-reporting`).
Since both modes share one CSS variable name, naming it `green` would mean
light mode stores a yellow color inside a variable literally called green, the same class of bug that broke the CTA section during the v1.2.0
`ink`/`paper` rework (see that gotcha further down this file).

### Light mode, Remotive Media Asia brand system (the default since v1.120.3)

Source: `remotive_brand_5.json`, the brand's official PPTX/document design
spec (colours, fonts, logo usage rules for slide decks and documents).

| Token | Value | Brand JSON field |
|---|---|---|
| `paper` | `#f7f4ec` | Cream, **not** the brand JSON's literal `primary: #ffffff`, overridden per explicit request to keep the site's cream ground in light mode too |
| `card` | `#ffffff` |, |
| `paper-2` | `#f0f8ff` | brand JSON `tables.row_alt_background` |
| `ink` | `#1e1e1e` | brand JSON `colors.secondary` / `text` |
| `cyan` | `#00a2ff` | brand JSON `colors.accent_1` |
| `cyan-dark` | `#00308f` | brand JSON `colors.accent_2` |
| `magenta` | `#ff449f` | brand JSON `colors.accent_5` |
| `magenta-dark` | `#cd360b` | Darkened from brand JSON `colors.accent_6` (`#f2410f`), the literal brand value failed WCAG 1.4.3 as text (3.45:1 against the cream background, needs 4.5:1). Same hue, darkened until it cleared 4.5:1. See `docs/changelog.md` v1.7.0. |
| `accent-3` (yellow in this mode) | `#f2f216` | brand JSON `colors.accent_3` |
| `accent-3-dark` (green in this mode) | `#008110` | Darkened from brand JSON `colors.accent_4` (`#00aa15`), the literal brand value failed even the 3:1 large-text minimum (2.83:1). Same hue, darkened until it cleared 4.5:1. |
| Font (all roles) | Saira | brand JSON `fonts.heading/subheading/body/caption`, brand spec uses one typeface throughout, no serif accent. **Scoped exception, v1.66.5:** footer column headings (`.rm-footer__heading` in `remotive.css`) use Space Grotesk as a small accent; everything else on the site stays Saira, matching the brand JSON. (v1.66.4 briefly swapped the whole site to Space Grotesk; reverted the same session once it became clear only the footer headings were meant to change.) |

**If the brand JSON (`remotive_brand_5.json`) is ever updated**, new accent
colours, a font change, light mode's tokens above must be updated to match,
and this table updated in the same commit/session so it does not drift.


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

## Admin-configurable values (as of v1.120.3)

Everything below is stored in the `remotive_theme_options` WordPress option,
editable at Appearance → Theme Options, with defaults matching the values in
this document. `remotive_theme_option_defaults()` holds 165 stored settings:
75 base settings (`inc/options/theme-options.php`), 25 colours
(`inc/options/colours.php`) and 65 one-page layout settings
(`inc/options/agency-layout.php`). Every key is declared, defaulted and
sanitised by name in `remotive_sanitize_theme_options()`.

| Tab | Settings | Since |
|---|---|---|
| **Homepage** | Hero (eyebrow, two headline lines, highlighted words, sub-heading, two button labels, reassurance line), the section headings and descriptions, the closing call to action, the three figures with their case-study links, the team roster (name, role, portrait slug, bio, leadership flag) | v1.79.1 (groups) |
| **Agency layout** | `agency_mode` (off), `agency_hide_pages` (on), and the words and show/hide switch of every section of the one-page layout: opening, services, how we work, signs, questions, team (portrait size), closing form | v1.116.0, v1.118.0 |
| **Business details** | `contact_email`, three address lines, `legal_name`, `legal_uen`, seven social URLs | v1.3.0, renamed v1.79.1 |
| **Call-to-Action** | `cta_form_action`; blank or `#` falls back to the built-in handler at save and at render | v1.3.0, fallback v1.119.9 and v1.120.0 |
| **Country ticker** | Countries (six markets by default: Singapore, Malaysia, Thailand, Vietnam, Hong Kong, China; a site that saved its own list keeps it), visibility, background, text and separator colours, size, weight, letter spacing, padding, speed, direction | v1.66.8 |
| **Languages** | `lang_ms`, `lang_zh_hans`, `lang_zh_hant`, `lang_nav`, `lang_footer`, all on | v1.106.0 |
| **Colours** | Twelve palette roles per mode and a reset switch, with a contrast check; only values that differ from the defaults are written to the page | v1.95.0 |
| **Integrations** | `pexels_api_key` (write-only: never printed back, never exposed through REST, not used by the front end) and its remove switch | v1.91.0 |
| **Site behaviour** | `lead_retention_months` (24), `branded_login` (off) and `branded_login_message`, `maintenance_mode` (off), `graceful_errors` (on), `block_app_passwords` (on, v1.120.1; an options array saved by an older version counts as on), `motion_effects` (on), `default_theme` (`light` since v1.120.3, `dark` before; `system` follows the device) | renamed from Display v1.79.1 |

Two card panels sit beside the tabs, each with its own form: **Site setup**
(shipped content with per-item restore, menus, site pages) and **Enquiries**
(the stored enquiries, the spam folder, CSV export). Edits to translations are
not theme options; they live in the language layer's own tables
(`wp_rm_i18n*`) under Tools → Translations.

**History.** Before v1.79.1 there were seven tabs; four of them edited the
homepage and were merged into one. Display became Site behaviour and Contact &
Socials became Business details. The field set was 57 at v1.79.1, 94 at
v1.96.0 and is 165 at v1.120.3.

**Ticker colour defaults are mode-specific.** `ticker_bg` (`#1a1a2e`) and
`ticker_color` (`#f7f4ec`) describe the light-mode band, navy on cream.
They are emitted as inline CSS only when changed from these defaults,
because emitting them always overrode the stylesheet's mode-aware rule and
made the ticker invisible in dark mode. An untouched install inherits the
inverting rule; a deliberate choice is honoured in both modes.

That means **this file describes the shipped defaults, not
necessarily the live values** once someone edits them in wp-admin. If the
two ever disagree, wp-admin is correct for what is actually live; this file
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
`#cd360b`, an orange-red, not a darker magenta. `--accent-3` is `#f2f216`
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
  replaced by hyphens, e.g. `remotive-media-1.2.0.zip`.
- Two other numbers move independently of the theme version: the migration
  schema, `REMOTIVE_SETUP_SCHEMA` in `inc/setup/site-setup.php` (`1.120.3` as of
  v1.120.3), which only changes when an already-set-up site needs a migration,
  and the rewrite-rule version, the `remotive_lp_rewrite_v` option (`3`), which
  only changes when the landing-page URL rules change.
- Every pull request runs `.github/workflows/ci.yml`: PHP syntax on 7.4 and
  8.3, JavaScript syntax, valid `theme.json`, every `tests/check-*.php` and
  every `tests/test-*.php` (the list is in `readme.md` → "Tests and CI").
- `tests/check-versions.php` fails when `style.css`, `readme.txt` (Stable tag
  and "Latest version") and the newest `docs/changelog.md` entry disagree.

### Constants, option rows and filters the theme adds

Named here so a mu-plugin or a host can find them without reading the code.

| Name | Kind | Purpose |
|---|---|---|
| `REMOTIVE_SETUP_SCHEMA` | PHP constant, `inc/setup/site-setup.php` | The migration schema version a site is brought up to. |
| `REMOTIVE_SETUP_FLAG` | option row `remotive_site_setup_done` | The schema version the site has reached. |
| `REMOTIVE_SETUP_LOCK` | option row `remotive_setup_lock` (v1.120.0) | The migration lock: `0` when free, otherwise the taking request's timestamp token. Taken by one compare-and-swap update on the row (`remotive_setup_lock_acquire()`), released only by the request holding that token (`remotive_setup_lock_release()`), treated as abandoned after ten minutes. Two overlapping admin loads cannot both run `remotive_run_pending_migrations()`. |
| `REMOTIVE_ERROR_HANDLER` | PHP constant, defined by `inc/core/error-handler.php` (v1.120.0) | Set when the theme's fatal page is active. `drop-ins/php-error.php` returns at once when it is defined, so WordPress's own fatal handler (which stays registered, and keeps recovery mode working) prints nothing over the theme's page. |
| `REMOTIVE_SHOW_ERRORS` | `wp-config.php` constant | Shows the full error detail without an admin session; for a lockout, remove afterwards. |
| `remotive_use_custom_error_page` | filter | `__return_false` hands the fatal page back to WordPress and the drop-in. |
| `wp_is_application_passwords_available` | WordPress filter the theme hooks (v1.120.1) | Returns false while `block_app_passwords` is on, which it is by default. |
| `remotive_trusted_proxy_ranges` | filter | Address ranges whose forwarded-address header is believed (Cloudflare's by default). |
| `remotive_i18n_available`, `remotive_i18n_live_pages`, `remotive_i18n_translates_request` | filters | Which pages exist in which language, and which requests the translator skips. |
| `remotive_schema_plugin_managed_types`, `remotive_schema_suppressed_types`, `remotive_schema_graph`, `remotive_schema_logo_license` | filters | Structured data coexistence with SEO plugins, see `readme.md`. |
| `remotive_preconnect_google_accounts` | filter | Forces the Google Sign-In preconnect hint on. |
| `remotive_lp_rewrite_v` | option row | Rewrite-rule set version, see above. |
- The zip's top-level folder is `remotive` (matches `wp-content/themes/remotive`),
  independent of the zip filename or the `Theme Name:` display string.

## Security audit record

### Log-driven hardening (v1.68.1, 2026-08-31)

A second pass driven by the site's own access, SSL and FTP logs for
Aug 2026 rather than by a code review. What the logs actually showed:

| Observation | Volume | Response |
|---|---|---|
| `wp-login.php` brute force from `103.138.189.98` | 271 hits | Login rate limit in `inc/core/security.php`; **IP block still required at firewall, a theme cannot do this** |
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

### Full-theme audit (v1.103.0, 2026-10-02)

Scope: every PHP module under `inc/`, `functions.php`, the front-end scripts and
the shipped drop-ins, at v1.102.0. Method: attack-surface inventory, then static
review by pattern and by reading each state-changing path. Evidence for each
finding is the code; fixes are covered by `tests/test-security.php`,
`tests/test-landing.php` and `tests/check-security-patterns.php`. No live site
was tested and no plugin other than the Rank Math 1.0.279 source was run.

Attack surface: five public form endpoints (`admin-post.php` actions `cta`,
`about`, `contact`, `lp`, with nopriv), one public AJAX nonce endpoint
(`remotive_form_nonces`), one public REST route (`remotive/v1/search`, GET),
four admin-post actions behind capability and nonce checks (site setup, restore
shipped item, spam/ham, CSV export), the Theme Options page
(`edit_theme_options`, Settings API), and query strings read on the landing and
confirmation pages (`service`, `from`, campaign fields). No file upload, no
`$wpdb` call, no outbound HTTP request, no custom XML-RPC method, no shortcode,
no `wp_redirect`, no `unserialize` without `allowed_classes => false`.

| ID | Severity | Status | Finding | Roles |
|---|---|---|---|---|
| WPSEC-001 | medium | fixed 1.103.0 | JSON-LD printed without `JSON_HEX_TAG`: a closing script tag in a title or excerpt breaks out of the block | contributor, author |
| WPSEC-002 | medium | fixed 1.103.0 | Public search endpoint returned excerpts of password-protected posts and listed unlisted pages | anonymous |
| WPSEC-003 | medium | fixed 1.103.0 | Username discovery: encoded `rest_route`, author redirect vs 404 difference, distinct login errors, users sitemap | anonymous |
| WPSEC-004 | low | fixed 1.103.0 | No length limit on enquiry name and message; array values caused PHP errors on public endpoints | anonymous |
| WPSEC-005 | medium | fixed 1.103.0 | Login lockout and form quotas keyed on the connecting address, which is Cloudflare's (inferred from the Cloudflare addresses in the v1.68.1 server logs): one bucket for everyone, so five failed sign-ins by anyone could lock out the administrator | anonymous |
| WPSEC-006 | informational | accepted | `?from=lp` on the confirmation page fires a conversion event without a submission. Documented design (the page must work as a destination); the event carries no data | anonymous |

Checked and not a vulnerability (false positives): CSV export formula injection
(`remotive_csv_safe()` already neutralises leading `=`, `+`, `-`, `@`, tab and
space forms); admin enquiry list output (every field escaped); nonce on public
forms (present, with the fresh-nonce endpoint for cached pages, which discloses
nothing secret); `open-remotive-page` in `assets/js/webmcp.js` (same-origin
HTTP(S) only); `innerHTML` in `assets/js/remotive.js` (a constant string);
`copy()` of the drop-ins (fixed source and destination, skips a file that is not
ours); `unlink()` of retired files (realpath-confined to the theme folder);
hero preload varying on `Accept` (`Vary: Accept` is sent); the Pexels key
(never printed back to the page, never in the repository).

Proposed, not code (owner in brackets):

- Content-Security-Policy and HSTS at the edge; the theme sends the other
  headers but a CSP needs the real list of third-party hosts [Cloudflare/server].
- Deny `xmlrpc.php` and `wp-admin/install.php` at the server (unchanged from
  v1.68.1) [hosting].
- Rate-limit `wp-login.php` and `admin-post.php` at Cloudflare as well; the
  theme's counter is per address and per ten minutes [Cloudflare].
- Two-factor authentication for administrators; the theme adds no login flow
  of its own [WordPress admin, security plugin]. The application-password
  review proposed here was overtaken by v1.120.1, which blocks application
  passwords by default (see the second review below).
- WordPress's lost-password screen still says whether an account exists; a
  site-wide change belongs to a security plugin [WordPress admin].
- Rotate the Pexels key shared in chat before saving it in Theme Options
  [site owner].
- Run WPScan against staging and a dependency check on the JavaScript
  packages if any are added (none ship now) [developer].

Residual risk: a scripted client that loads a page for a nonce and submits at the
rate limit is still possible without a CAPTCHA (a product decision recorded since
v1.67); contributors can still enter HTML only as WordPress allows them.

Release readiness for 1.103.0: version bumped in `style.css` and `readme.txt`;
changelog, upgrading, readme updated; tests run (`php tests/test-*.php`,
`check-security-patterns`, `check-modules`, `check-versions`, `check-parent`);
residual risk reviewed above.

### Second review of the codebase (v1.119.9 to v1.120.1, 2026-10-14 to 2026-10-25)

Two passes over every module, the scripts and the templates, with the
findings fixed release by release (`docs/changelog.md` has the full list).
The security-relevant ones:

| ID | Severity | Status | Finding | Roles |
|---|---|---|---|---|
| WPSEC-007 | medium | fixed 1.120.0 | `?author=1` still revealed the login name: the theme's redirect shared `template_redirect` priority 10 with `redirect_canonical()`, which fired first and sent the visitor to `/author/<login>/`. The redirect now runs at priority 0 | anonymous |
| WPSEC-008 | medium | fixed 1.120.0 | The REST users block was stepped around with `?_embed=author`: the embedded request skipped `rest_authentication_errors`. The users routes are refused on `rest_pre_dispatch` as well, and only the path and the `rest_route` argument count as a route, so a search that merely mentions the users path is served | anonymous |
| WPSEC-009 | low | fixed 1.120.1 | Application passwords were open to anyone who had guessed a login (the lockout does not cover REST logins). Blocked by default through `wp_is_application_passwords_available`, with a switch under Site behaviour | anonymous |
| WPSEC-010 | low | fixed 1.119.9 | The honeypot let a value of `0` through (`empty()`); a mistyped address used up the form rate limit before the email was checked | anonymous |
| WPSEC-011 | low | fixed 1.119.10 | An expired nonce ended the request on WordPress's "link has expired" page (`check_admin_referer()`); `remotive_form_nonce_valid()` sends the visitor back to the form | anonymous |
| WPSEC-012 | low | fixed 1.120.0 | Two overlapping admin loads could run the setup migrations and seed the pages twice; the migrations now run under `REMOTIVE_SETUP_LOCK` | administrator |
| WPSEC-013 | informational | fixed 1.119.9, 1.120.0 | Enquiry text lost backslashes and gained entities on save (`wp_insert_post()` and `update_post_meta()` expect slashed data; kses ran for public visitors). `remotive_insert_lead_post()` slashes and pauses kses for the one insert; the meta is slashed | anonymous |
| WPSEC-014 | informational | fixed 1.120.0 | A fatal printed two pages (the drop-in and the theme's page). The drop-in steps aside on `REMOTIVE_ERROR_HANDLER`; the `wp_fatal_error_handler_enabled` filter, which cannot work from a theme, is gone, and recovery mode works | anonymous |

Tests: `tests/test-security.php`, `tests/test-forms.php`, `tests/test-setup.php`,
`tests/test-landing.php` and `tests/test-agency.php` carry a check for each.

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
| Landing pages are public and cached | `noindex, nofollow` by default and controllable in Rank Math (`rank_math_robots`; tag, header and sitemap follow it, v1.110.0); excluded from site search, page lists, menus, the REST listing and the sitemaps, and a click from any page of this site is turned back to the home page (`Referer`, `Sec-Fetch-Site`); exclude the landing slugs from any page cache; never blocked in `robots.txt` (crawlers must fetch the page to see the noindex, and Google Ads must fetch it to review the ad) |

### Original code audit

| Field | Value |
|---|---|
| Current version | 1.8.0 |
| Previous version | 1.7.0 |
| Audit date | 2026-08-26 |
| Release type | Minor (new settings/defaults, the CTA form's default handler and `cta_form_action`'s default value both changed; see `docs/changelog.md`) |
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
   for text fields, at the point of output, independent of whatever the
   save-time sanitizer does. No live exploit confirmed (the affected
   fields all require `edit_theme_options` capability to set); fixed as
   defense-in-depth regardless, per "sanitize on input, escape on
   output" being two separate, independent steps.
2. **No CRLF header-injection guard on the CTA form's `Reply-To`
   header.** `sanitize_email()`/`is_email()` should already prevent this
   (neither permits control characters in a valid address), but the code
   did not make that guarantee explicit or independent of their internal
   behavior. Fixed: an explicit `\r`/`\n` rejection added before the
   value reaches `wp_mail()`.
3. **No rate limiting on the CTA form's public POST endpoint.** Fixed:
   a transient-based throttle (3 submissions per IP per 10 minutes).
   Documented as a real but imperfect mitigation, `REMOTE_ADDR` can be
   spoofed or shared behind a proxy, not a guarantee.

**Proposed, not implemented**

- CAPTCHA (hCaptcha/Turnstile) or WAF-level rate limiting for the CTA
  form, for full bot resistance beyond what the nonce/honeypot/rate-limit
  combination provides. Not implemented because vendor selection is a
  product/UX decision, not something to make unilaterally in theme code.

**Residual risks**

1. A scripted client that loads the real page first (for a valid nonce)
   can still submit the CTA form repeatedly, just throttled to the rate
   limit. Only a CAPTCHA closes this fully, see "Proposed" above.
2. `register_setting()` does not specify an explicit capability, so the
   Settings API's save-path check (`options.php`, core) defaults to
   `manage_options` while this theme's own page-access check uses
   `edit_theme_options`. Identical in a stock install; would only
   diverge on a site with customized role capabilities. Not fixed
   without a live WordPress install to verify the correct mechanism
   against.

**Test status:** every fix verified against actual computed output, PHP brace/paren balance checked for every changed file, block-template
tag balance re-verified, the honeypot's invisibility confirmed via
`getBoundingClientRect()` (checked by measurement as well as by eye), the ticker
pause/resume behavior confirmed via a real scroll-triggered
`IntersectionObserver` test, the `.pot` file's syntax verified
line-by-line after a bug in the first extraction attempt produced
garbled comments (caught before packaging, not after).

**Release readiness:** version bumped (1.8.0, all four locations:
`style.css`, `readme.txt` Stable tag, `readme.txt` "Latest version"
line, and this file) · changelog updated · upgrade notice added ·
readme.md and readme.txt updated · no sensitive exploit details in any
public-facing doc · package contains no VCS artifacts, backups, or debug
files (re-verified this pass instead of carried over from the last check).

## Structured data (v1.26.0)

`inc/content/schema-markup.php` publishes the entity facts in this file as
schema.org JSON-LD: `legalName` is the ACRA registered name above
(title-cased), the address is read from Theme Options (shipped defaults
= the registered office above), `areaServed` is Singapore and Malaysia
per the SEO content strategy section. If the legal entity facts change,
update this file, the Theme Options defaults, and the `legalName`
constant in `inc/content/schema-markup.php` together. As of v1.27.0 the schema
also publishes the ACRA incorporation date (2024-01-31) as
`foundingDate` and the UEN (202404376G) as an `identifier`, both from
the Legal entity table above, and types the organization
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
  covered here.

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
  `/ms/`, Simplified Chinese `/zh-hans/` (`zh-Hans`), Traditional Chinese
  `/zh-hant/` (`zh-Hant`); the `/zh-cn/` and `/zh-tw/` paths of v1.90.0 to
  v1.103.x redirect (v1.104.0). The server renders only the language of the
  URL. There is no `?lang=` parameter and no script-based switching. The Malay
  and Chinese copy is a first draft until a native speaker has reviewed it.
- **Form fields:** name, email, website, message, and since v1.119.0 an
  optional "How did you hear about us?" select with eight listed answers
  (`remotive_lp_source_options()`), recorded in English in the enquiry.
- **Confirmation page:** the handler checks `/audit-requested/` exists and is
  published before redirecting (`remotive_lp_thanks_url()`, v1.120.0); without
  it the visitor gets the shared thank-you page or the landing page's own
  message.
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
| `docs/ssot.md` | Anyone maintaining brand/entity accuracy | This file, canonical facts |
| `docs/upgrading.md` | Future maintainers, MENJ | Roadmap, planned work, not-yet-built ideas |
| `docs/changelog.md` | Everyone | Version history |
| `docs/resources.md` | Legal, anyone redistributing/auditing the theme | Consolidated license/copyright for the theme and every bundled font/image |
| `docs/image-credits.md` | Legal, editors | Photographer and Pexels photo for every landing-page image |
| `docs/walkthrough.md` | Site owner, MENJ | The page-by-page sheet for the walkthrough meeting, and the switches that exist |
| `docs/accessibility.md` | Developers, auditors | WCAG 2.2 AA conformance notes: tested, fixed, exempt, open |
| `docs/cache-headers.md`, `docs/htaccess-cache.txt` | Hosting | The Cache-Control rules to paste at the server |
| `docs/homepage-v1.80-brief.md` | Editors | The homepage copy brief and its two open items |
| `docs/legal-privacy-policy.md`, `docs/legal-terms-of-service.md` | Legal | Drafts with CONFIRM markers, not page content yet |
| `drop-ins/README.md` | Hosting, developers | What the three drop-ins do and how they install |
| `error-pages/htaccess-errors.txt` | Hosting | The `ErrorDocument` lines for the static 500 and 503 pages |
| `languages/README.txt` | Translators | Notes on the `.pot` file |
| `tests/check-*.php`, `tests/test-*.php` | Developers, CI | Eleven checks and twelve unit tests, one line each in `readme.md` → "Tests and CI" |
| `.github/workflows/ci.yml` | Developers | The checks every pull request runs |

## The brief for the site (as of 2026-10-04)

From Gordan, on the site: it is "a total mess"; to start, "1 simple, clean, well
designed page that explains what we do, in English, Malay and Simplified
Chinese, that we can point people in Asia to. Not lots of pages, not content all
over the place"; other things can be built as it goes; turn the site off until a
walkthrough has agreed the changes. Every change should be checked against this
first. In practice: the one page is a landing page (they carry their own copy in
every language); since 1.105.0 the main pages are also live in all four
languages with a header switcher, at the site owner's direction, which goes
beyond one page, so the walkthrough decides what stays
(`remotive_i18n_default_live_pages()`); the landing pages are noindex and for
paid traffic; the site is switched off with Maintenance mode during review. Traditional Chinese is also supported because the language layer
and landing pages provide it; it is not part of Gordan's list.

## Malay spelling (ms-MY, from v1.107.0)

The Malay text follows the Dewan Bahasa dan Pustaka's *Pedoman Umum Ejaan Bahasa
Melayu* (Sistem Ejaan Rumi, the 65-page copy supplied by the site owner; it is
not stored in the repository), and the 2010 edition (Dewan Bahasa dan Pustaka, Brunei), also supplied. Where the editions differ, the newer 2010 edition is used; Brunei-specific titles and `awda` are not applied. What is applied and checked by
`tests/check-ejaan.php`:

| Pedoman | Rule applied |
|---|---|
| Penulisan unsur serapan | Loan words are adapted by the Pedoman's table (-ity → -iti, -tion → -si, -ization → -isasi, c/q/x/ph/th/y changes, -ics → -ik, -cy → -si). Already followed throughout. |
| Kata depan, partikel, kata ganti | di/ke/dari apart (kepada, daripada together); -lah/-kah/-tah and -nya/-ku/-mu attached; pun apart except the Pedoman's list; per apart. |
| Angka | `ke-2`, `50-an` with a hyphen; amounts as `250 juta`, `745 ribu`, not 745k; `US$`, `RM`, `S$` for money; `%` or `peratus`; decimal point and comma thousands for quantities. |
| Tanda koma | No comma before an anak ayat that follows its main clause; a comma before tetapi and melainkan; a comma after a sentence-opening connective. |
| Tanda titik | No full stop at the end of a title, a figure caption or a table caption. |
| Tanda pisah | The en dash with a space each side (2010 edition; it replaces the unspaced em dash of the first edition), also between numbers; titles are separated with ` \| `. |
| Tanda petik | Curly double quotes “ ”, never straight. |
| Tanda koma (senarai) | A comma before the final `dan` of a list of three or more items (2010 edition). |
| Tatabahasa (from v1.109.2) | *Tatabahasa Dewan*, 3rd ed. (DBP, 2008): `adalah` is not used as a general copula (`ialah` before a noun, otherwise left out); never `paling` or `sekali` with `ter-`; `daripada ... kepada` for changes of state and `dari` for place or time of origin; `kepada` for people and abstract targets, `ke` for places; no doubled noun after `semua`, `para`, `beberapa`; `bahawa` kept before a reported clause; loan-word verbs keep their first letter (`menskalakan`, `mengklik`, `mengkomitkan`); compounds as the book writes them. Checked by `tests/check-ejaan.php` where a script can; the rest was read through by hand. |
| Huruf besar | Months and weekdays, languages and peoples (bahasa Melayu, orang Inggeris), places and official names with a capital. |
| Tarikh | Day, month name, year: `2 Oktober 2026`. |

Not enforced: foreign terms in italics (the language layer swaps text, not
markup, so quoted search phrases and terms such as retainer and sprint stay as
written), and wording.

## PHP module layout (v1.100.0)

`inc/` holds the theme's PHP, in six folders. `functions.php` requires each file
by path, error handler first.

| Folder | Files |
|---|---|
| `inc/core/` | `error-handler`, `security`, `accessibility`, `avif`, `branded-login`, `rank-math`, `not-found` (v1.118.0), `ai-discovery-files` |
| `inc/options/` | `theme-options`, `colours`, `agency-layout` (v1.116.0), `maintenance-mode` |
| `inc/setup/` | `site-setup`, `classic-menus`, `content-seed`, `content-seed-data` |
| `inc/forms/` | `lead-form-handler`, `cta-form-handler`, `about-form-handler`, `contact-form-handler`, `leads`, `akismet`, `thank-you` |
| `inc/landing/` | `landing-pages`, `landing-copy`, `agency-page` (v1.116.0) |
| `inc/i18n/` | `i18n`, `i18n-admin`, and the dictionaries `ms`, `zh-hans`, `zh-hant` (loaded by name). On/off per language: Theme Options → Languages |
| `inc/content/` | `schema-markup`, `schema-elements` (v1.118.0), `schema-extended` (v1.120.5), `webmcp`, `feature-grids`, `stats-band`, `post-image-fallback`, `article` |

`tests/check-modules.php` fails when a file under `inc/` is not required, so
the table and `functions.php` cannot drift apart unnoticed.

A new module goes in the folder that matches its job and gets one `require`
line in `functions.php`. Older entries in `docs/changelog.md` and the security
record name the files by their earlier flat paths.

## SEO content strategy (v1.12.0)

Built against `remotivemedia-keyword-selection.xlsx` (90 keywords: 30
Singapore, 30 Malaysia, 30 generic, provided by the client, competitor-
validated keyword research, not third-party content requiring
attribution). Selection rule per the file's own "Read me" sheet:
commercial/transactional keywords throughout, informational keywords
kept only above 500 monthly search volume.

**Decision reversed in v1.79.3: the two market landing pages were
removed.** `page-seo-singapore.html` and `page-digital-marketing-malaysia.html`
each covered their market's full commercial keyword cluster on one page.
Both templates, their auto-creation entries, their schema and the fifteen
internal links pointing at them are gone. The reasoning that produced them
still holds for anything built later, one consolidated page per market
rather than one page per keyword, since near-duplicate pages targeting the
same intent cannibalise each other and amount to a doorway-page pattern, but the market pages themselves are no longer part of the shipped content.
Market context now lives in the service pages and the blog posts.

**Decision: informational clusters became blog posts, not
pages.** Four posts (delivered separately as `remotive-blog-seed-content.xml`,
not bundled in the theme zip, content and theme code are kept
separate): SEO cost in Singapore, SEO pricing in Malaysia, "is your
website SEO-friendly," and SEO vs SEM. See that file's own README for
the full keyword-to-post mapping and volumes.

**Documented, not silently ignored: the web-design keyword gap.** Six
Malaysia keywords (`web design malaysia`, `website design malaysia`,
`ecommerce website design malaysia`, `web design kl`, `web design company
malaysia`, `website design company kuala lumpur`, combined volume over
9,000) are untargeted anywhere. Remotive does not offer web design as a
service; the Malaysia landing page says so explicitly in its own FAQ.
Revisit only if that service offering changes, tracked in
`docs/upgrading.md`.
