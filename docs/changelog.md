# Changelog

All notable changes to this theme are documented here. Format loosely
follows [Keep a Changelog](https://keepachangelog.com/); versioning is
[semver](https://semver.org/).

## [1.112.1] — 2026-10-06

### Fixed

- **The sidebar's Recent posts card was a sparse list of bold titles with large gaps.** The touch-target rule in `remotive.css` (`display:inline-block; padding-block:.5rem`) was applied to the title links, on top of the list gap, so each row grew tall. Each entry in `parts/sidebar.html` is now a row with a 64 px square featured image, the date and the title (clamped to three lines), separated by hairlines. The link padding is reset in `blog-and-about.css`; the whole row stays easy to hit, because the image and title are both links.

## [1.112.0] — 2026-10-06

### Changed

- **Insights is English only and sits apart from the translation paths.** The posts page (`/blog/`, whatever its slug) and every article (`2026/09/slug`) no longer have `/ms/`, `/zh-hans/` or `/zh-hant/` versions: a translated address redirects to the English page (302); the header and footer language switchers are not shown on Insights pages (the posts page, articles, category, tag, author and date archives); Insights pages carry no hreflang; they are not in `/sitemap-languages.xml`; and they are not in the default live list (29 translated pages per language instead of 37). The Malay, Chinese and other main pages still link to the English `/blog/` for Insights. The dictionaries keep the old article entries, unused. `remotive_i18n_is_english_only()` decides it, and the `remotive_i18n_english_only` filter can change the line.
- Tests in `tests/test-i18n.php` (90).
- **Documentation brought up to date** for 1.107 to 1.112: `readme.md` (module and asset tree, Kagnue, Insights), `docs/ssot.md`, `docs/walkthrough.md`, `docs/upgrading.md` (steps for each release), `docs/resources.md` (Pexels photos and the licensed Kagnue font), `docs/image-credits.md`, `docs/accessibility.md` and `docs/cache-headers.md` (keep the landing pages out of page caches).

## [1.111.0] — 2026-10-06

### Fixed

- **Three Insights articles had no featured image, and the other four had generic graphics.** The seed data for `seo-vs-sem`, `seo-cost-singapore` and `seo-services-pricing-malaysia` set `'image'` to the right file and then, a few lines later, to an empty string; the last key wins, so they were published without a picture, which is why the Insights grid showed bare text cards beside illustrated ones. The duplicate lines are removed.

### Changed

- **Photographs for all seven articles** (Pexels, free for commercial use; credits in `docs/image-credits.md`): a website workspace, the Singapore skyline, a phone with social apps, a checklist notebook, an analytics dashboard, a calculator and coins, and Kuala Lumpur at night. No people. 1200 × 675 JPEG with an AVIF companion (30 to 110 KB), replacing the old gradient-and-title graphics.
- **Existing sites are updated by a one-time migration** (`remotive_refresh_article_photos()`, version 1.111.0; the setup schema was raised to 1.111.0 so sites already at 1.103.1 run it): an article with no image, or whose image file is byte-for-byte the old bundled graphic (compared by MD5, not by file name), gets the photo. Nothing is deleted, and an image an editor chose is never replaced, even one with the same file name. (Review findings from CodeAnt.)
- Migrations now run in version order (`uksort` with `version_compare`; a plain `ksort` put 1.111.0 before 1.66.0).

### Added

- **A card with no featured image shows a branded gradient** of the same shape (`inc/content/post-image-fallback.php`), so a new article published without an image does not leave a gap in the grid.
- `tests/check-seed-images.php`, run in CI: every seeded article names an image and its AVIF exists. It fails on the old data.

## [1.110.3] — 2026-10-06

### Fixed

Three findings from the CodeAnt review of PRs #29 and #32, all valid:

- **The confirmation page could be indexed or listed in the sitemap.** `audit-requested` uses the landing template, so ticking "index" for it in Rank Math made it indexable and put it in the Rank Math sitemap. The confirmation pages (`thank-you`, `audit-requested`) are now always noindex and nofollow and always out of the sitemap, whatever Rank Math says (`remotive_lp_is_confirmation_page()`).
- **The landing-page entry guard on a subfolder install.** With WordPress in a folder (`example.com/site/`), a landing-page referer carries the folder, so the language buttons and the thank-you redirect were sent to the home page. The folder is now stripped before matching (`remotive_lp_is_internal_navigation()` takes it from `home_url()`).
- **Case Studies photo descriptions were English on translated pages.** The six `alt` texts now have Malay, Simplified and Traditional Chinese versions (checked on `/ms/`, `/zh-hans/`, `/zh-hant/case-studies/`).

Tests added in `tests/test-landing.php` (52) and `tests/test-rank-math.php` (20).

## [1.110.2] — 2026-10-06

### Changed

- **Case Studies page: photographs instead of gradient placeholders.** Each of the six cards now has a photo that matches its subject, from Pexels (free for commercial use): a stadium (sports precinct), supermarket shelves of Asian products (FMCG nutrition), city towers (financial services), a car showroom (automotive), an industrial site (industrial supplier) and a clean medical room (healthcare). No people are shown. Cropped to 16:9 at 640 and 960 px, AVIF with a JPEG fallback (`assets/images/case-studies/`, 5 to 160 KB each), lazy-loaded, with a descriptive `alt`, and a slow zoom on hover (off with reduced motion). Credits are in `docs/image-credits.md`.
- New token `__REMOTIVE_THEME_URL__` (the active theme's address) for images in block templates, so the paths do not depend on the theme folder name.
- Not changed: the individual case study pages' own featured images.

## [1.110.1] — 2026-10-06

### Added

- **AI Discovery Files plugin (2.2.2) compatibility** (`inc/core/ai-discovery-files.php`). The plugin lists every published page in `llms.txt`, `llms.html` and `ai.json` (from `get_pages()`) and caches the files. The theme hides the landing pages from `get_pages()` on the front end, but a file built from wp-admin saw them: tested with the plugin installed, a build from the admin listed five landing or confirmation pages; with the new `aidf_template_data` filter it lists none. The filter removes the landing and confirmation pages from the plugin's page list in every context, and the plugin's cache is cleared once so an earlier copy is rebuilt.
- Checked with the plugin active: one `<title>` and the hreflang links on main, Malay, Chinese and landing pages; `robots.txt` carries both the plugin's block (priority 20) and the theme's sitemap line (99); the plugin's files, rewrite rules and `template_redirect` handler do not collide with the language layer or the landing-page entry guard.

### Notes (not theme code)

- The plugin lists pages in one English list. It knows WPML and Polylang only, so the theme's language versions are not in `llms.txt`; each page's translations are declared by hreflang and `/sitemap-languages.xml`.
- The plugin loads its translations too early (`_load_textdomain_just_in_time` in the debug log). That is inside the plugin; update it or ask its author.
- A fresh WordPress "Sample Page" (if it still exists) is listed in `llms.txt`; delete it.

## [1.110.0] — 2026-10-06

### Changed

- **Landing pages: noindex and nofollow are controlled in Rank Math.** The theme used to force both on every landing page whatever Rank Math said, and always sent an `X-Robots-Tag: noindex, nofollow` header that Rank Math could not change. Now the page's own Rank Math choice (Advanced tab, saved in `rank_math_robots`) decides: index, noindex, follow and nofollow each work, the tag and the header agree, and a landing page set to index joins the Rank Math sitemap. The default is unchanged: with nothing chosen both are on, and the theme fills the Advanced tab with noindex and nofollow once for existing landing pages and for every new one, so the boxes are already ticked and can be unticked. A choice already saved is never overwritten. Without Rank Math the same meta is read, so the default still holds.

### Added

- **No way in from the main site.** The landing pages are for ads and social only:
  - hidden from page lists (the page-list block, `wp_list_pages`, navigation fallbacks), classic menus and navigation blocks (a link added by hand renders nothing), the public REST listing of pages, site search, both sitemaps and the WebMCP tools;
  - **a click from any page of this site is turned back to the home page** (302). The check uses the `Referer` and, where a link strips it, the browser's `Sec-Fetch-Site` header (`same-origin`, `same-site`). A visitor from an ad, a social post, a search result, a typed address, a bookmark or an app gets in; so do the language buttons between landing pages, a reload, and the form's redirect to the thank-you page (their `Referer` is a landing page). Logged-in editors who can edit pages are never turned back, so previews work.
  - Tests in `tests/test-landing.php` cover each case.
- Limits that remain: an address typed or pasted by someone who knows it works by design, as does a landing URL on a server-level cache that serves the page before WordPress runs (exclude the landing slugs from page caching), and a plugin that publishes its own list of pages (for example an AI-discovery or `llms.txt` plugin) needs the landing pages excluded in its own settings.

## [1.109.2] — 2026-10-06

### Changed

The Malay text was read against *Tatabahasa Dewan*, 3rd ed. (DBP, 2008), supplied as the `tatabahasa-dewan` skill, on top of the two spelling guides. All 1,408 Malay strings were scanned for the rules a script can check, and the hits were read by hand. What changed (about 15 strings):

- **`adalah` removed** (TD 9.2.3.5, 17.9.2: restricted in baku): `audit adalah percuma` → `audit percuma`; `adalah jujur`, `adalah luas`, `adalah digital`, `adalah sama` lose it; `adalah kerja asas` and `adalah biasa` became `ialah …`; `adalah struktural` → `bersifat struktural`.
- **`paling terkini` → `terkini`** (TD 8.4: `paling` is never combined with `ter-`).
- **`dari` / `daripada` / `kepada`** (TD 9.2.3.6): `dari kosong` → `daripada kosong` (an abstract origin); `dipindahkan daripada tapak jenama` → `dari tapak jenama` (a place); `ke kedudukan 3` → `kepada kedudukan 3` (a change of state, five places).
- Checked and left as correct: `menskalakan`, `mengklik`, `mengkomitkan`, `menskopkan` (loan words keep their first letter), `berbeza daripada Singapura` (comparison), `daripada … kepada` pairs, `akses kepada`, `sesetengah` (some), the doubled forms and compounds.

`tests/check-ejaan.php` now also fails on `adalah`, `paling`/`sekali` with `ter-`, `demi untuk`, a quantifier plus a doubled noun, and `ke kedudukan` after a change verb. Rules about sentence structure and word order (the D-M rule, passive agents) are not machine-checkable and were reviewed by hand; a native speaker should still read the text.

## [1.109.1] — 2026-10-06

### Changed

- **Landing pages on phones (under 40rem): 18% shorter** (4,169 px to 3,415 px at 390 px wide). The logo bar is smaller (the form starts about 35 px higher); the "Results from the work" cards are a swipe row with the next card peeking in (552 px to 239 px); the three steps are compact rows with the number beside the text (538 px to 359 px); the six cities sit in three columns, two rows (404 px to 175 px; two columns under 21rem). Tablet and desktop unchanged.
- The swipe row is keyboard-focusable (`tabindex="0"`, labelled by its heading, visible focus ring), so keyboard users can scroll to every card in browsers that do not focus scrolling regions themselves.
- Checked 3 services × 4 languages × 1440, 820, 390 and 320 px × dark and light: no horizontal page scroll, nothing clipped, at least three results cards everywhere.

## [1.109.0] — 2026-10-06

### Added

- **"Results from the work" strip on the ad landing pages**, directly under the hero (on phones, right after the form) and above "How it works". Each card has a big figure, the case study's own one-line result and a small client descriptor. Not linked, so ad traffic stays on the page. Matched to the service, drawn from both listed and unlisted case studies, in English, Malay, Simplified and Traditional Chinese:
  - **SEO audit (3):** +198% organic traffic (industrial supplier), +94% across every tracked metric (B2B services), +34% search clicks and 150% more qualified leads (healthcare).
  - **Google Ads management (3):** +150% qualified leads on the same budget (B2B gifting), +25–35% conversion rate with cost per conversion down 20–30% (asset manager), paid search data showing within weeks which treatment terms had commercial intent (healthcare clinic; the 85% CPM saving was dropped from this page because it came from programmatic video, not search).
  - **Paid social (2):** +168% GMV (skincare marketplace launch), 745k addressable audience from 53k (sports precinct).
  - Not used: the footwear, healthcare clinic, market-entry and Singapore portfolio case studies, which report method or recovery rather than a headline result.
- **Kagnue display serif for the big figures.** The figures on these cards and on the Case Studies page cards (`+198%`, `745k`) use Kagnue Regular, a one-weight display serif: self-hosted as a 28 KB WOFF2 subset (Basic Latin, dashes, quotes, arrow) in `assets/fonts/kagnue/`, `font-display: swap`, with Georgia as the fallback. Used only at large sizes and nowhere else (body, navigation, forms and headlines keep Saira and Archivo); the plus sign and the en dash are left out of the font (`unicode-range`) so they fall back to Saira; it has no Chinese glyphs, but the figures are Latin digits. Used under the site owner's licence (the font's own licence file is not part of the repository).
- The copy lives in each service's `proof` list in `inc/landing/landing-copy.php` (figure, result, client, each in four languages); `tests/check-landing-copy.php` and `tests/check-ejaan.php` cover it.

## [1.108.3] — 2026-10-05

Found by reading the site's `debug.log` (31 August to 5 October).

### Fixed

- **PHP warning on Tools → Translations** (`Undefined array key "description"` in `i18n-admin.php`, then `htmlspecialchars(): Passing null`, 5 October). A page translated in the admin with only a title (or only a description) produced a search-listing entry with one key missing. `remotive_i18n_data()` now gives every entry both keys. `tests/test-i18n.php` checks it.

### Found in the log, not theme code

- **`ai-discovery-files` plugin, 3,273 of the log's 3,527 lines:** it loads its translations too early (`_load_textdomain_just_in_time`), on every request, to 5 October. Update it, replace it, or switch it off. With PHP errors displayed on screen this also caused "headers already sent" warnings on the login page (31 August). Set `WP_DEBUG_DISPLAY` to false on the live site and clear the log.
- **Rank Math: `rank_math_analytics_objects` and `rank_math_analytics_gsc` tables missing** (31 August to 5 October, plus a null-property warning in its analytics summary). Re-create them under Rank Math → Status & Tools → Database Tools, or switch its Analytics module off.
- **31 August: fatal on a missing `inc/error-handler.php`** (a partial upload of an older theme structure; not seen since). **1 September: WordPress could not save its cron list** once.

## [1.108.2] — 2026-10-05

Found by reading the server access and FTP logs for September and October.

### Fixed

- **The Tools → Translations "Scan the site" stalled with a 403.** The scan works in batches and redirects to itself. It built that redirect with `wp_nonce_url()`, which HTML-escapes the URL, so the address went out as `…&amp;offset=39&amp;…&amp;_wpnonce=…`; the server read `amp;_wpnonce`, found no nonce and refused (log: `GET /wp-admin/admin-post.php?action=rm_i18n_scan&amp;offset=39… 403`). The redirect now adds `_wpnonce` with `add_query_arg()`. `tests/check-security-patterns.php` fails if a redirect to `wp_nonce_url()` comes back.

### Added

- **`.htaccess` in the theme folder** returning 404 for `.git`, `.github`, `docs/`, `tests/`, `tools/`, `drop-ins/` and `*.md`, `*.py`, `*.sh`, `*.sql`, `*.log`, `*.bak` files. The FTP log shows the whole `.git` folder of the theme (and of another plugin) uploaded to the live site, with the docs, which include the security findings register. Apache and LiteSpeed honour it; on nginx the same paths need blocking in the server config.

### Found in the logs, not fixable in the theme

- Bots (OAI-SearchBot, GPTBot, ClaudeBot) get **400** on `/`, `/robots.txt` for some requests and **429** on `/sitemap.xml`, `/robots.txt` and static files: a server or firewall rule (rate limit, virtual host), not the theme.
- `/?page_id=14` and `/case-studies/` returned **404** to real visitors and Googlebot on 1, 3 and 4 October: a link to a page that was not published at the time (WordPress writes `?page_id=N` for an unpublished page). Keep Case Studies published, and re-save the menu.
- A one-off `/__REMOTIVE_CTA_FORM_ACTION__` request (bingbot, 3 October): a page was served once with the template placeholder unreplaced. Not reproducible; 12 URLs checked clean.
- 404s on theme scripts, styles and fonts on 30 September during the upload window: files requested before they were uploaded.

## [1.108.1] — 2026-10-05

### Changed

- **Landing pages: the form comes straight after the headline on phones and tablets.** Below 56rem the order is logo bar, eyebrow, headline, the lead form (full width), then the intro and bullets, so the form is on the first screen (before, a phone showed only the first field). From 56rem it stays in the right column beside the copy. CSS only (`assets/css/landing.css`); applies to the three services in all four languages. Checked at 390 and 820 px in dark and light, and in Malay and Simplified Chinese, with no horizontal scroll.

## [1.108.0] — 2026-10-05

### Changed

- **All translated pages are live by default** in Bahasa Melayu, Simplified and Traditional Chinese: 37 per language (home, Services and six service pages, Case Studies and all fourteen case studies, About, Team, Insights and its seven articles, Contact, FAQ, Privacy, Terms). Previously the eight unlisted case studies and the seven articles stayed English-only until switched on. `remotive_i18n_default_live_pages()` now adds every page that has a translated title in a dictionary. They appear in the language switcher, hreflang and `/sitemap-languages.xml`. Each page can still be switched off in Tools → Translations (a saved setting wins), a whole language in Theme Options → Languages, and the list narrowed with the `remotive_i18n_live_pages` filter.
- This goes further than Gordan's "not lots of pages" brief, at the site owner's direction; `docs/walkthrough.md` still asks which pages stay.

## [1.107.3] — 2026-10-05

### Fixed

Text in the theme files that had no Malay or Chinese translation, found by checking every template, part and front-end string against the dictionaries:

- Archive page: `Archive`, and the empty-state messages `Nothing matches yet…` (archive and search) and `Nothing published yet…` (Insights and index).
- Thank-you messages for the audit, landing page and contact/about forms.
- Case study chart labels (inline SVG): Before, Now, Start, Month one, Month three, Qualified leads, Organic traffic, Sessions, Referring backlinks, Engagement, Session duration. In Malay the chart figures `$1.4m`, `$723k`, `53k` and `745k` are now `US$1.4 juta`, `US$723 ribu`, `53 ribu` and `745 ribu`, as in the text.

Not translated on purpose: admin screens, notification emails, names, addresses. Edits made in WordPress to a page's text, pages that are not live and individual articles need their own translation (Tools → Translations).

## [1.107.2] — 2026-10-05

### Fixed

- **Language sitemap lost `<lastmod>` for articles.** `remotive_i18n_lastmod()` used `get_page_by_path()`, which defaults to pages, so dated article paths (posts) returned nothing. It now looks up pages and posts. Found by the CodeAnt review of PR #18.
- **Saving a page in a language that is switched off unpublished it.** The Live checkbox in Tools → Translations showed the language's master switch, so a page that was live by default showed unchecked, and saving wrote `published = 0`, which stayed in force when the language was turned back on. The checkbox now reads the page's own setting (`remotive_i18n_page_live()`).

## [1.107.1] — 2026-10-05

### Changed

The Malay (`ms-MY`) text was checked against a second Pedoman Umum Ejaan Rumi Bahasa Melayu, the 2010 edition (Dewan Bahasa dan Pustaka, Brunei), supplied in addition to the first. Where the two editions differ, the newer one wins. The 1.107.0 work stays except where the 2010 edition says otherwise:

- **Dashes.** The tanda pisah is now the en dash with a space each side (`Baiki, Ditemui, Skala – terbukti`, `Jun – Oktober 2022`, `20 – 30%`), replacing the unspaced em dash of 1.107.0.
- **Quotation marks.** Straight double quotes in Malay text are curly (“sistem automasi turnkey”).
- **Serial comma.** A comma before the final `dan` in lists of three or more items (`Meta, TikTok, dan paparan programatik`). Lists were reviewed one by one; two-item phrases and clauses are untouched.
- **Not applied:** the Brunei-specific titles and the `awda` form, which do not apply to a Malaysian and Singapore audience, and the four-digit number comma rule, which contradicts itself in the 2010 text.

`tests/check-ejaan.php` now forbids the em dash, requires spaces around the en dash, forbids straight double quotes, and flags a missing serial comma.

## [1.107.0] — 2026-10-05

### Changed

The Malay (`ms-MY`) text, in the dictionary (`inc/i18n/ms.php`) and the landing pages, was checked against the Dewan Bahasa dan Pustaka's *Pedoman Umum Ejaan Bahasa Melayu* (the 65-page copy supplied) and brought into line. The loan-word spellings were already right (agensi, aktiviti, kualiti, teknikal, infrastruktur, automasi and so on follow the Pedoman's adaptation rules), as were prepositions, particles and affixes; what changed is below. About 100 strings were edited.

- **Commas.** No comma before an anak ayat that follows its main clause (`…disambungkan, supaya…` → `…disambungkan supaya…`; the same for kerana, agar, sebelum, selepas; Pedoman, tanda koma, rule 4). A comma before tetapi and melainkan that join clauses (rule 2), and after a sentence-opening Jadi (rule 5).
- **Headings and captions have no final full stop** (tanda titik, rule 11: titles, illustrations and tables): the home page hero and section headings, Kajian Kes, Wawasan, Soalan Lazim, the team and contact headings, the figure captions on the case studies, and the three landing page headlines and their confirmation heading.
- **Dashes.** The tanda pisah is the unspaced em dash (`Baiki, Ditemui, Skala—terbukti`), including between numbers (`Jun—Oktober 2022`, `20—30%`); a spaced en dash as a title separator became ` | `, the separator the other titles use.
- **Numbers and money.** US dollars are `US$` (it was `AS$` in some places and a bare `$` in others); `53k`, `745k` and `$723k` became `53 ribu`, `745 ribu` and `US$723 ribu`.
- **Adapted two English words**: `social commerce` → `perdagangan sosial`, `treadmill` → `mesin lari`.
- **Dates on Malay and Chinese pages** (the Insights list): `October 2, 2026` is now `2 Oktober 2026` in Malay (day, month with a capital, year, as in the Pedoman's `31 Ogos 1957`) and `2026年10月2日` in Chinese. `remotive_i18n_localise_date()`.

### Added

- **`tests/check-ejaan.php`, run in CI.** Fails if the Malay text brings back any of the mechanical mistakes: a comma before a following anak ayat, tetapi without a comma, AS$ or a bare $, a number with k, a spaced dash, an en dash between words or numbers, Indonesian spellings (karena, bahwa, situs, tautan, layanan, informasi and others), di/ke/dari joined to a word of place, ke pada or dari pada written apart, lah/kah/tah or nya/ku/mu written apart, ke before a number without a hyphen, `50an`, space before punctuation, English months, language names without a capital, an SEO title or landing page headline ending in a full stop, and pun written together outside the Pedoman's list. 1,359 strings pass.

### Not done, and why

- **Foreign words in italics.** The Pedoman writes foreign terms in italics (huruf condong) unless adapted. The language layer swaps text, not markup, so a term such as "retainer", "sprint", "white-label" or a quoted search phrase stays as written. Where an English word has an accepted adaptation it was adapted; the rest is a known limit.
- **Wording.** This is orthography and punctuation. Whether a sentence reads naturally to a Malaysian reader still needs a native speaker.

## [1.106.1] — 2026-10-05

### Fixed

- **Header language switcher on a phone.** The header row never wraps on a small screen (`critical.css`), so the switcher added in 1.105.0 was squeezed past the right edge ("EN • BM" visible, the Chinese buttons cut off). On screens up to 782 px the row now wraps only when it holds the switcher: the colour toggle and the switcher share the row beneath the logo, menu button and call to action, and the switcher takes a row of its own when the screen is too narrow for both (320 px). Checked at 320, 390 and 768 px in a real WordPress with no horizontal scroll; the desktop header is unchanged.

## [1.106.0] — 2026-10-05

### Added

- **Theme Options → Languages.** A switch for each of Bahasa Melayu, Simplified Chinese and Traditional Chinese (all on by default; English is always on), plus switches for the header and the footer language switcher. A language that is off disappears everywhere at once: its pages, the ad landing pages in that language included, redirect to the English address; the switcher and the hreflang tags drop it; it leaves the language sitemap. Which individual pages exist in a language is still set page by page under Tools → Translations. `remotive_i18n_language_enabled()` and `remotive_i18n_switcher_enabled()` read the settings.
- **Language sitemap, complete and distinct.** `/sitemap-languages.xml` now lists every live page once per language, English included, and every entry carries the full, reciprocal set of `xhtml:link` alternates plus an `x-default` (before, it listed only the translated URLs). A page with no other language live is not listed. It stays separate from Rank Math's own sitemaps.
- **Works with Rank Math.** Checked against the Rank Math SEO 1.0.279 source and in a real WordPress with the plugin active: the language sitemap is added to `sitemap_index.xml` the way Rank Math adds its own extra sitemaps, as a `<sitemap>` with a `<loc>` and a `<lastmod>` through the `rank_math/sitemap/index/entry` filter; Rank Math's on-disk sitemap cache is cleared (`Cache::invalidate_storage()`) when the language settings are saved or a page is switched on or off, so the index never lags; `robots.txt` lists both sitemaps; the landing and confirmation pages stay out of Rank Math's page sitemap. Hreflang stays a distinct set of `<link rel="alternate" hreflang>` tags printed per page, in head, for the page and each of its live languages plus `x-default`.

### Fixed

- **Two `<title>` tags on every page when Rank Math is active.** Rank Math moves WordPress's classic title tag into its own head output, but a block theme's template adds a second one that Rank Math does not know about. Present on the English pages too, since before the language layer. The extra tag is now removed, only when Rank Math has taken the title over (`remotive_rank_math_single_title()`). One title on `/`, `/services/` and `/ms/services/` with the plugin active.
- The language layer's last-modified lookup no longer warns for a page without a modified date.

### Tests

- `tests/test-i18n.php` now covers the language switches (off, on, never saved), the switcher placements, the sitemap entries (English plus live languages, none for a language that is off, reciprocal set) and the Rank Math index entry; `tests/test-rank-math.php` covers the single title.

## [1.105.0] — 2026-10-05

### Changed

- **The main pages are live in all four languages** (English, Bahasa Melayu, Simplified and Traditional Chinese), at `/`, `/ms/`, `/zh-hans/` and `/zh-hant/`: Home; Services and its six service pages; Case Studies and the six listed case studies; About; Team; Insights (the index); Contact; FAQ; Privacy; Terms. That is 22 pages in each language, all with a translation already written, and each checked in a real WordPress for a 200 response and the switcher. `remotive_i18n_default_live_pages()` holds the list (the case studies come from the same list the footer menu uses). The eight case studies that are live but unlisted and the seven individual articles are translated but stay off until switched on in Tools > Translations; a page that is off redirects to its English address. This reverses the 1.104.0 default of nothing live, at the site owner's request; Gordan's brief was one simple page, so the walkthrough sheet asks again which of these stay.
- **Language switcher in the header**, beside the colour-mode toggle: EN, BM, 简体, 繁體 (the Chinese variants in characters, not codes), one line on desktop and a row of its own on a phone. It links to the same page in each language, or to that language's home page where the current page has no translation (an article, for example). The footer keeps the full names. `__REMOTIVE_LANG_NAV__` in `parts/header.html`; `remotive_i18n_render_switcher( true )`.

### Notes

- Checked for text left in English on the translated main pages: only names, the postal address, acronyms (ROAS, CPCV), chart figures and the blog date format remain, which are the same in every language. The blog index prints dates in English; localising them is not done.
- The Malay and Chinese text is still a first draft that needs a native speaker.

## [1.104.0] — 2026-10-04

Brings together two lines of work that both started from 1.89.6: this repository (landing pages, colours, folders, security, parent and Rank Math compatibility, up to 1.103.2) and a separate build that adds language versions of the site (numbered 1.89.7 to 1.92.1 there, listed below under "Language layer build"). One version number from here on.

### Added

- **Language versions: `/ms/`, `/zh-hans/`, `/zh-hant/`** beside the English site (`inc/i18n/i18n.php`, `inc/i18n/i18n-admin.php`, dictionaries `inc/i18n/ms.php`, `zh-hans.php`, `zh-hant.php`). A language layer, not copies: the language prefix is removed from the request before WordPress parses it, so `/ms/team/` is served by the same page as `/team/`, and the rendered text is translated from a dictionary. Each live page gets its own title and description, a self-referencing canonical, `<html lang>`, hreflang between every language that has it, a language switcher in the footer (English, Bahasa Melayu, 简体中文, 繁體中文, each in its own writing), and a language sitemap (`/sitemap-languages.xml`, also offered to Rank Math and listed in `robots.txt`). Tools > Translations manages the dictionaries from the admin, with edits kept in the database so a theme update never overwrites them.
- **No ordinary page is live in another language to begin with.** The brief for the site is one simple, clean page that explains what the company does, in English, Malay and Chinese, rather than many pages, and that page is a landing page (they already carry their own copy in every language). The dictionaries carry 37 translated pages including the home page, but all are off until someone switches them on in Tools > Translations; a page that is off redirects to its English address, and the footer switcher is hidden while no other language is live. `remotive_i18n_default_live_pages()` and the `remotive_i18n_live_pages` filter set any launch pages. This reverses the language build's own default, which published every translated page.
- `tests/test-i18n.php` (prefixes, hreflang tags, URLs, nothing live by default, no switcher while nothing is live, dictionary parity). The module check knows the dictionaries are loaded by name.

### Changed

- **Landing pages use the same language addresses.** `/zh-cn/` and `/zh-tw/` become `/zh-hans/` and `/zh-hant/` (Chinese is told apart by script, not by country), and Malay is tagged `ms-MY`. The landing pages no longer register their own rewrite rules or query variable; the language layer reads the language directory and the pages resolve as ordinary pages. The old `/zh-cn/` and `/zh-tw/` addresses redirect (301) to the new ones, and stored rewrite rules are flushed once. The language layer skips these pages (`remotive_i18n_translates_request`), so they are neither translated again nor redirected to English.
- **Language buttons are readable.** The landing pages' switcher reads EN, BM, 简体 and 繁體; a code such as ZH-CN means nothing to the person who needs it.
- **The language layer's redirects use `wp_safe_redirect`.** The Chinese and Malay strings that changed since the dictionaries were written (the automotive case, the two case-study rows, the footer case-study links and the market ticker, including Vietnam) are translated, as a first draft for a native speaker to check.

### Fixed

- **The footer case-study menu could list the six twice** on a site with plain permalinks (every URL is `?page_id=N`, so the migration found no match by address). It now matches by the page each item points to.

### Security

- The language layer was read against the v1.103.0 audit questions. Every action on its admin screen checks `manage_options` and a nonce; every query takes request values through `prepare()` or a cast; its redirects stay on the site. `tests/check-security-patterns.php` now allows database queries only inside `inc/i18n/` and fails if a request value reaches a query unprepared anywhere.

## [1.103.2] — 2026-10-02

### Changed

- **Theme screenshot** (`screenshot.png`, 1200 × 900) replaced. The old one showed the pre-1.93 design (square buttons, the old navigation and headline). The new one is the live front page as rendered by WordPress 7.1.2 with the theme activated on a fresh install: dark mode, rounded buttons, current navigation and headline, and the six-market ticker. No code changed.

## [1.103.1] — 2026-10-02

### Fixed

- **Contact address is `hello@remotivemedia.asia`.** The shipped default already was, but a site that had saved the older `hello@remotivemedia.com` in Theme Options kept receiving enquiries there, because a saved option outranks the default. Migration 1.103.1 (`remotive_correct_contact_email()`) replaces that one value, once, on the next admin page load; any other address an administrator chose is left alone. `docs/ssot.md` no longer lists the `.com` domain as the contact address. `tests/test-setup.php` covers the migration.

## [1.103.0] — 2026-10-02

Security audit of the whole theme (details and the finding register in `docs/ssot.md`). Minor version because it adds two filters, changes two defaults and adds a size limit.

### Security

- **WPSEC-001, JSON-LD could be broken out of.** The structured data block was printed without `JSON_HEX_TAG`, so a title or excerpt containing a closing script tag, entered by a contributor or author, would have ended the block and run script on every page it appears on. Now encoded with `JSON_HEX_TAG | JSON_HEX_AMP` (`inc/content/schema-markup.php`).
- **WPSEC-002, public search endpoint over-shared.** `/wp-json/remotive/v1/search` returned excerpts of password-protected posts and listed the landing and confirmation pages that are meant to be unlisted. It now excludes both (`inc/content/webmcp.php`). The same unlisted set (`remotive_unlisted_page_ids()`) now also keeps the confirmation pages out of site search and the core sitemap, which previously covered the landing pages only.
- **WPSEC-003, usernames could still be discovered.** The REST users block missed the URL-encoded `rest_route` form; the author redirect returned a 404 for names that do not exist and a redirect for ones that do; the login form said which half was wrong; and the core users sitemap listed author URLs. Each is closed in `inc/core/security.php`: the route is matched after decoding, the redirect applies to the request rather than the result, wrong user and wrong password read the same, and the users sitemap is removed. WordPress's own lost-password messages are unchanged (proposed below).
- **WPSEC-004, enquiries had no size limit.** An anonymous visitor could post a very large name or message into the enquiries table and the notification email. Name is now cut at 200 characters and message at 5,000 (`inc/forms/lead-form-handler.php`, `inc/forms/leads.php`, so enquiries captured from Contact Form 7 are covered too). Non-text values in those fields, and in the landing form's fields, no longer cause a PHP error (`is_scalar` guards).
- **WPSEC-005, rate limits shared one address behind Cloudflare.** Logs already in this repository's records show Cloudflare addresses reaching the server, so the login lockout and the form quotas counted every visitor as one. `remotive_client_ip()` now uses Cloudflare's forwarded-client header, but only when the connecting address is inside Cloudflare's published ranges, so a visitor cannot choose their own address. The ranges and the final result are filterable (`remotive_trusted_proxy_ranges`, `remotive_client_ip`). Enquiry records store the same address.

### Added

- `tests/test-security.php` (35 checks: forwarded address, CIDR matching, users route spellings, generic login errors, users sitemap, length caps) and `tests/check-security-patterns.php` (CI: no `wp_redirect`, `eval`, command execution, `base64_decode`, raw `$wpdb`, unrestricted `unserialize`, REST route without a permission callback, JSON-LD without `JSON_HEX_TAG`, or module without the direct-access guard). `tests/test-landing.php` covers the unlisted set.

### Compatibility

- Failed sign-ins now show one message instead of "unknown username" or "incorrect password".
- The core users sitemap (`/wp-sitemap-users-1.xml`) no longer exists.
- Enquiries over the length limits are cut rather than rejected.

## [1.102.0] — 2026-10-02

### Fixed

Checked against Twenty Twenty-Five 1.5. A child theme's palette merges with the parent's, so the parent's own slugs (`base`, `contrast`, `accent-1` to `accent-6`) survived with their light values, and the parent's merged `theme.json` styles still use them:

- Post dates and comment authors (`accent-4`, #686868) sat on the dark page at 3.1:1, under the 4.5:1 minimum.
- Code blocks (`accent-5` background, `contrast` text) and the parent's own patterns and unoverridden templates (404, page without title) used the parent's light palette.

`base`, `contrast`, `accent-4` and `accent-5` are now declared in this theme's `theme.json` with dark values (so the editor matches) and aliased in `remotive.css` to `paper`, `ink`, the muted-text token and `card` on `html[data-theme]`, so they follow the light/dark switch, the Colours tab, and any parent style variation chosen in the Site Editor (the alias outranks the variation's `:root` values). `accent-1`, `accent-2` and `accent-6` are left alone: no template here uses them, and `accent-6` is a translucent line colour.

### Verified, no change needed

- No function, hook or block style in the parent (`twentytwentyfive_*`) is redefined or removed here, and the child's `remotive_` names do not collide with them.
- The parent's `style.min.css` still loads normally; the child's `style.css` stays a header only.
- Both themes require WordPress 6.7; the child's PHP 7.4 minimum is above the parent's 7.2.
- The child overrides `front-page`, `home`, `index`, `archive`, `search`, `single`, `page`, `header`, `footer` and `sidebar`; the parent's `404`, `page-no-title` and the other parts now take their colours from the aliases above.

### Added

- `tests/check-parent.php`, run in CI: fails if `style.css` stops naming the parent, `theme.json` stops declaring the four slugs, `remotive.css` stops aliasing them, or any PHP file redefines a `twentytwentyfive_` function.

## [1.101.0] — 2026-10-02

### Fixed

Checked against the Rank Math SEO 1.0.279 source. The plugin replaces three things the theme also did, so the theme's versions were being discarded whenever it was active:

- **Confirmation page indexable.** Rank Math calls `remove_all_filters( 'wp_robots' )`, which removed the theme's noindex on the thank-you page. Added a `rank_math/frontend/robots` rule (noindex, follow). The landing-page robots rule already used the plugin's filter.
- **Landing languages canonical to English.** Rank Math removes core's `rel_canonical`, so the theme's `get_canonical_url` filter never ran and `/ms/`, `/zh-cn/` and `/zh-tw/` pages named the English page as canonical. Added a `rank_math/frontend/canonical` rule that gives each language its own URL.
- **Landing and confirmation pages in the Rank Math sitemap.** The theme only removed them from core's sitemap, which the plugin switches off, and the plugin omits only posts with `noindex` in `rank_math_robots` meta. Added a `rank_math/sitemap/entry` rule that drops them.

### Added

- `inc/core/rank-math.php` holds the three rules; `tests/test-rank-math.php` (12 checks) covers them. All are inert without the plugin. The schema-markup comment now cites 1.0.279.

## [1.100.0] — 2026-10-02

### Changed

- **`inc/` grouped into six folders** instead of 25 files side by side, moved with `git mv` so history follows: `core/` (error-handler, security, accessibility, avif, branded-login), `options/` (theme-options, colours, maintenance-mode), `setup/` (site-setup, classic-menus, content-seed, content-seed-data), `forms/` (lead, cta, about and contact handlers, leads, akismet, thank-you), `landing/` (landing-pages, landing-copy) and `content/` (schema-markup, webmcp, feature-grids, stats-band).
- Every `require` in `functions.php` and the tests, and every reference in comments, documents, the `.pot` file and the tools, now uses the new path. Load order is unchanged (the error handler is still first). Entries in this changelog below keep the old flat paths, because that is where the files were then.
- `Theme URI` in `style.css` now points at the repository, `https://github.com/menj/remotive`, instead of the site.
- No behaviour change: the two files that load a sibling (`landing-pages.php` → `landing-copy.php`, `content-seed.php` → `content-seed-data.php`) moved together, so their `__DIR__` requires still resolve.

## [1.99.0] — 2026-10-02

### Changed

- **Editor palette follows the Colours tab.** `remotive_colours_editor_settings()` hands the saved colour overrides to the block editor as a stylesheet (`block_editor_settings_all`), so what an editor sees matches the front end. The editor has no `data-theme` attribute, so the dark-mode values apply there, matching its dark default.
- **`data-theme` is set on `<html>` by the server** (`remotive_html_data_theme()`, via `language_attributes`) from the site's default mode (`system` becomes dark). Before, only the inline script set it, so visitors without JavaScript never got the rules keyed on `html[data-theme]` (the Saira typeface in particular) and saw the fallback fonts. The script still overrides it for a visitor with a saved choice or a light-mode device.

### Added

- **Unit tests** that run without WordPress: `tests/test-colours.php` (37 checks: overrides, contrast maths, validation, CSS output) and `tests/test-landing.php` (25 checks: language from URL, path-based URLs, hreflang, `<html lang>`, robots), with WordPress stubs in `tests/bootstrap.php`. CI runs every `tests/test-*.php`. Run locally with `php tests/test-landing.php`.

## [1.98.0] — 2026-10-02

### Changed

- **Six case studies, not categorised.** The Case Studies page is one grid of six cards (cookieless audience data for a sports precinct; SEO for FMCG nutrition; paid media for financial services; programmatic advertising for automotive; B2B SEO for an industrial supplier; AI visibility for a healthcare provider). The filter chips, jump links and category sections are gone, with the filter script and the dead CSS (`.rm-cs-filter*`, `.rm-cs-subhead`). The grid steps 3 → 2 → 1 columns so six cards never leave one stranded.
- **The other eight case-study pages stay published but unlisted:** nothing on the site links to them, their URLs still work, and they are not removed from the database. Delete them later in Pages if wanted.
- **Home page** features the automotive case as the spread, and the industrial-supplier and healthcare cases as the two rows (the third row is gone).
- **Footer** lists the six with short labels plus "All case studies →". The curated list lives once in `remotive_listed_case_studies()` (`inc/classic-menus.php`) and feeds both the default menu and the migration.
- **Migration 1.98.0** (`remotive_refresh_case_study_menu()`) updates the saved Footer — Case Studies menu on the next admin load: removes the four old default items, adds any of the six that are missing, renames the "All N case studies" link. Items an administrator added by hand are left alone.

## [1.97.0] — 2026-10-02

### Changed

- **Theme root reduced to six files.** `changelog.md`, `ssot.md`, `upgrading.md` and `resources.md` moved into `docs/` (with `git mv`, so history follows); `readme.md` and `readme.txt` stay at the root. The root now holds only `style.css`, `theme.json`, `functions.php`, `screenshot.png`, `readme.txt` and `readme.md`; everything else is in a folder. Nothing loads these documents at runtime, so no code changed beyond comments and one admin hint string (Theme Options now points at `docs/ssot.md` and `readme.md`; `languages/remotive.pot` updated to match). References to the moved files in the other documents and in code comments were rewritten to the `docs/` paths. Entries in this changelog keep the names they were written with, because the files were at the root then.
- **Version check in CI** (`tests/check-versions.php`): fails a pull request if `style.css`, `readme.txt` (Stable tag and Latest version) and the newest changelog entry disagree. `readme.txt` had been left at 1.94.0 while the theme moved to 1.97.0; it now matches.
- **Documentation brought current.** `docs/ssot.md` (the landing pages as a section of canonical facts; a security record for the lead-form and landing-page changes; light-mode token names; the colour scheme being configurable; the CTA-band text deviation for dark mode; the new rounded, no-underline, photography and six-market deviations; versioning rules including CI; the document map) and `docs/upgrading.md` (a full deploy checklist for v1.90.0 – v1.97.0, what shipped, and what is still open from the codebase review and the walkthrough feedback).

## [1.96.0] — 2026-10-02

### Changed

- The 14 hand-written inline gradients on the case-study cards are now classes (`.rm-grad--cyan-magenta` and eight more, in `blog-and-about.css`). Checked by rendering the template before and after: the computed gradient of all 14 cards is identical.
- Removed the duplicate Space Grotesk 900 face from `theme.json`; it pointed at the 700 file, which is what a 900 request already used.

### Notes

- Reviewed and deliberately left: the remaining inline styles (WordPress block markup that must match the block comments, plus the runtime `--rm-team-cols`), the Archivo and Newsreader files (the no-JavaScript fallback; browsers only fetch fonts a page uses), the 315 `!important` rules (they beat WordPress's own block styles and can only be pruned safely against a real WordPress page) and the 143 KB (about 40 KB gzipped) `remotive.css`. The reasoning is in `readme.md` under Known limitations.

## [1.95.0] — 2026-10-02

### Added

- **Colours tab** (Theme Options → Colours, `inc/colours.php`): all twelve palette roles can be set separately for dark and light mode. Only colours you change are written to the site (as CSS variables inline with the critical CSS, so there is no flash), so an untouched install is unchanged. Each mode shows a live WCAG contrast table for eight key pairs (text on page and cards, magenta, cyan and third-colour text, the button label on magenta, text on contrast bands); on save, a pair under 4.5:1 is allowed but flagged. Input is validated as hex (an injection attempt falls back to the default), and a switch restores every shipped colour.

### Changed

- Palette names in `theme.json` now say what each role is for ("Text", "Page background", "Magenta, strong (text and buttons)") instead of "Ink", "Paper" and "Magenta Plate Dark". Slugs are unchanged, so nothing that uses them breaks. The reason for the confusion is documented in `readme.md`: the slugs are roles, and in dark mode the `-dark` variants are not darker.
- `languages/remotive.pot` refreshed.

## [1.94.0] — 2026-10-02

### Fixed

- **Lead forms no longer lose enquiries from cached pages.** A nonce lives 12 to 24 hours, and a page cached for longer carries an expired one, so the visitor was told "the link has expired" and the lead was lost. The nonce check is unchanged; `assets/js/remotive.js` now asks a new uncacheable endpoint (`remotive_form_nonces`, in `inc/lead-form-handler.php`) for current nonces on page load and swaps them into the forms (landing pages, homepage CTA, About and Contact). The server-rendered nonce stays as the fallback, and a form submitted before the refresh finishes waits for it, so a fast submit cannot carry the expired value.
- **Rate limit no longer blocks real landing-page leads.** The limit is now a per-form setting (`rate_limit`); landing page forms allow 10 submissions per IP per ten minutes instead of 3, because mobile carriers put many unrelated visitors behind one address. Other forms keep 3. The spam-trap check now runs before the limit, so bot traffic does not use up real visitors' quota.
- **Flash of missing borders and corners on the landing pages.** `landing.css` used variables defined only in the asynchronously loaded `remotive.css`; it now defines the ones it needs.
- **Object-injection risk in `inc/schema-markup.php`.** A second `maybe_unserialize()` on custom-field values a contributor can edit is gone; a still-serialised string is read with no classes allowed.

### Added

- **Automated checks on every pull request** (`.github/workflows/ci.yml`): PHP syntax on 7.4 and 8.3, JavaScript syntax, valid `theme.json`, and `tests/check-landing-copy.php`, which fails if any landing page text is missing a language (it checks the exact positions, not just the count).

### Changed

- **Landing page copy moved to `inc/landing-copy.php`**, apart from the rendering and routing in `inc/landing-pages.php`.
- **`languages/remotive.pot` regenerated** (49 strings that had never been extracted, 367 entries in all). `readme.txt` brought up to date (stable tag, upgrade notice).

## [1.93.0] — 2026-10-02

### Changed

- **Rounded-corner design.** One radius scale for the whole site (`--rm-r-sm` 10px, `--rm-r` 16px, `--rm-r-lg` 24px, `--rm-r-pill` 999px): pill buttons and filter chips, 16px cards and images, 10px form fields, a pill email field on the CTA form. The block theme's default button radius is now pill too (`theme.json`), so the editor matches. Full-bleed bands (navigation, CTA bands, stats band, footer) stay edge to edge. The landing pages use the same scale: rounded form cards, step cards, FAQ items, photo tiles and hero photo.
- **No underlines on CTAs.** Buttons never carry an underline in any state (resting, hover, focus, active), including buttons inside post content. The animated underline under the text-link CTAs ("View case study", section and row links) is removed; they keep the arrow, uppercase bold type and the hover colour change. Body-copy links keep their underline.

## [1.92.0] — 2026-10-02

### Added

- **Localised confirmation page for landing-page leads** at `/audit-requested/` (and `/ms/`, `/zh-cn/`, `/zh-tw/`). It matches the landing pages (logo and language links only), names the service in the headline, and is a conversion URL separate from the shared English thank-you page. The form's language is carried in a hidden field, and the `remotive_lead` event now includes `language`. A migration (1.92.0) creates the page on existing sites.

### Fixed

- **Dark-mode contrast on the main site's cyan and magenta call-to-action bands.** In dark mode the `-dark` palette tokens are bright, not dark (cyan-dark is `#00aeef`, magenta-dark is `#ff0198`), but the CSS assumed navy and orange-red, so the bands carried white text at 2.53:1 and 3.68:1. They now use near-black text (about 7:1 and 4.9:1), including the outline buttons and the small reassurance line. Found by measuring every button in both modes.
- **Header logo in dark mode.** The artwork's dark ":MOTIVE" wordmark disappeared on the dark header; the logo now sits on a white plate in dark mode, as on the landing pages.

### Changed

- The country ticker's default list now includes Vietnam (a site that has saved its own list keeps it).
- Docs brought up to date: `readme.md` (file map, landing pages, maintenance mode), `ssot.md` (admin settings), `resources.md` (Pexels image rights) and `upgrading.md` (deploy checklist).

## [1.91.2] — 2026-10-02

### Changed

- **Landing page copy rewritten for conversion** in all four languages. Outcome-led headlines and a "who it is for" eyebrow per service; leads that name the problem, the mechanism and the free audit; deliverables written as benefits. A service-specific form title and button ("Get my free Google Ads audit"), a one-line promise under the form title, and risk-reversal microcopy ("Free. No commitment. No mailing list."). The first step no longer says "Book a call", which the form does not do: it now reads "Tell us where to look". The closing heading reads "See what we would fix first. It's free." No statistics, client names or invented urgency were added; real client results would be the next strongest lever once approved for use.

## [1.91.1] — 2026-10-02

### Changed

- Landing page hero photos now match their services: the Google Ads page shows a laptop with search results, and the paid social page shows a phone with a social profile, replacing images that showed a "Social Media Strategy" booklet and a third-party brand name. Credits updated in `docs/image-credits.md`.

## [1.91.0] — 2026-10-02

### Added

- **Landing pages: photography, FAQ and a fuller layout.** Self-hosted Pexels photos (AVIF with JPEG fallback, about 1 MB in total, credited in `docs/image-credits.md`): a hero photo per service (desktop only, so the form is never pushed down on a phone) and photo tiles for the six markets (Singapore, Malaysia, Thailand, Vietnam, Hong Kong, China). A two-column closing band, tighter section spacing and an FAQ at the foot of each page: four questions per page (three shared, one per service) in all four languages, with matching `FAQPage` structured data built from the same items.
- **Responsive pass** on the landing pages, checked from 320 px to 1920 px (no sideways scroll at any width): steps in three columns and the six photo tiles in two, three and six columns on phone, tablet and desktop, the closing band and FAQ in two columns from tablet width, and a narrower sticky CTA on tablets.
- **Theme Options → Integrations → Pexels API key.** A write-only field: the saved key is never printed back into the page, a blank box keeps the saved key, and a toggle removes it. The front end of the site does not call Pexels.

## [1.90.0] — 2026-10-02

### Added

- **Maintenance mode** (Theme Options → Site behaviour). Off by default. When on, logged-out visitors get the "back shortly" page with a 503 and `Retry-After`; users who can edit posts, wp-admin, cron, AJAX, feeds and REST are unaffected. Nothing is unpublished. New `inc/maintenance-mode.php`.
- **Service landing pages for paid and social traffic**: three pages (SEO, Google Ads, paid social; slugs `seo-audit`, `google-ads-management`, `paid-social-advertising`) on the new "Ad landing page" template, created by Site Setup. Each is a funnel: promise and lead form above the fold, how it works, a second form, and a sticky mobile CTA, with no site navigation. Four languages, each on its own URL: `/seo-audit/` (English), `/ms/seo-audit/`, `/zh-cn/seo-audit/`, `/zh-tw/seo-audit/`. No `?lang=` parameter and no script-based switching: the server renders only that language, with the matching `<html lang>`, a self-referencing canonical, and `hreflang` alternates (`en`, `ms`, `zh-Hans`, `zh-Hant`, `x-default`). The EN / MS / ZH-CN / ZH-TW buttons are plain links, which keep any campaign parameters. Rewrite rules for the language directories are flushed once (`remotive_lp_rewrite_v`). The Malay and Chinese copy is a first draft for native-speaker review. The landing pages use the brand's cyan, magenta and yellow/green plates via the theme tokens (registration stripe, tinted hero, coloured steps, offset-plate form cards, a closing band), with contrast checked in both modes. All forms go through the shared lead handler, record service, website and campaign fields (utm_*, gclid, fbclid, ttclid) with the enquiry, and land on the thank-you page, whose `remotive_lead` event now carries the service. `remotive_lp_view` and `remotive_lp_form_start` events fire for tag managers. The pages are `noindex, nofollow` (meta, `X-Robots-Tag` and Rank Math), and excluded from site search and the core sitemap. Do not block them in robots.txt. Files: `inc/landing-pages.php`, `templates/page-landing.html`, `assets/css/landing.css`, `assets/js/landing.js`. The Malay and Chinese copy is a first draft for native-speaker review.
- The landing pages show the Re:Motive logo (the theme's own AVIF/PNG files) on a white plate, so the dark ":MOTIVE" wordmark stays readable in dark mode.
- **Migration 1.90.0** (`REMOTIVE_SETUP_SCHEMA`) creates the three landing pages on sites that are already set up. Campaign fields are saved per landing page and replaced by any new campaign click, so an earlier ad click is never attributed to a later lead.

## [1.89.6] — 2026-09-30

### Changed

- **Elfie is listed as "Mohd Elfie Nieshaem".** The default changes for fresh
  installs, and `remotive_sync_team_names()` renames the row on an existing
  site once (a saved roster row wins over the code, so the default alone would
  never have reached it). Entries live in `remotive_team_renames()` as
  [ slug, name it replaces, new name ]; a row is renamed only while it still
  holds the old shipped name, so a name already edited by hand is left exactly
  as written, each rename runs once and is recorded, and a member removed on
  purpose is not re-added. The slug stays `elfie`, so the portrait file and
  anything keyed on it are unaffected. The longest name on the page now, it
  sits on one line in its tile at 1101, 1150 and 1440px (161px of text in a
  190px tile at the narrowest).

## [1.89.5] — 2026-09-30

### Changed

- **Ally's portrait rebuilt from the original photograph, filled to the
  tile.** The copy used until now had its background stripped by another tool,
  leaving a white halo round her hair; the original (Taipei hillside behind
  her) was cut out with the tool's alpha matting instead, and the edge checks
  clean against both the light and the dark tile. The photo is a close selfie
  that stops at the chest, so the normal framing again left a small bust
  fading out mid-tile (face 33%, bottom edge 70%). `--cover` fills the tile
  from the photograph instead: bottom edge 99.9%, nothing floating. The price
  is stated, not hidden: her face is 48% of the tile against 22-33% for the
  rest of the set, and her arm runs off the left edge, which the tool's
  "CLIPPED" flag reports and which is inherent to a close-up. `COVER = {'ally'}`
  records the choice, so rebuilding the same photo reproduces it.
- With this, every tile with a photograph reaches the bottom edge. The only
  tile still without one is Adam's (initials fallback).

## [1.89.4] — 2026-09-30

### Changed

- **Elfie's portrait replaced** with the new straight-on photograph
  (`mohd-elfie-nieshaem-juferi.jpg`), through
  `tools/normalise-portraits.py --replace`, which overwrites the old file. Face
  height 31.1%, shirt to the bottom edge, in line with Jay and Ally (32-33%).
  The sides of the shirt stop at 16% and 84% of the tile because that is where
  the photograph itself ends. The alternative, `--cover`, fills the tile from
  the photo instead (no inset sides) but makes the face 46% of the tile, which
  would stand out in a row of five, so it was not used.

### Fixed

- **`--replace` no longer carries one photo's settings onto another.** The
  per-person framing in `PEOPLE`, the rotation in `ROTATE` and the
  `EXTEND` / `COVER` choices all describe a specific photograph. Elfie's entry
  held a crop and a 7 degree rotation tuned to his previous, leaning pose;
  `--replace` with a new photo would have applied both and tilted a straight
  one. Settings are now inherited only when the file being rebuilt is the
  same file the entry records, and explicit flags always win. Elfie's entry now
  records the new photograph, with the rotation removed.

### Added

- **`--cover`** (`cover_tile()`) for a tight head-and-chest close-up that cannot
  be shrunk to the set's face size without its torso ending in straight edges
  inside the tile: fills the tile from the largest 4:5 window that fits inside
  the photograph, crown placed 10% from the top. Opt-in, and the trade-off
  (a larger face than the rest of the set) is stated in the function.

## [1.89.3] — 2026-09-30

### Changed

- **Jay's portrait rebuilt from the supplied photo, and it now reaches the
  bottom of the tile.** His photo stops partway down the chest, so matching
  the set's face size left the cut-out ending mid-tile. 1.88.0 dissolved that
  edge, which left a visible smear across the shirt, and the next attempt, a
  hard cut, still floated. A plain black tee is the one case where the honest
  fix is simple: continue the shirt. `tools/normalise-portraits.py` gains
  `extend_bottom()`, which mirrors the real fabric just above the join
  downward, so the weave and folds carry across without a seam (a flat fill
  was tried first and drew a visible line, because it had no grain; measured
  grain above and below the join now matches, 2.11 against 2.14), and keeps
  the silhouette's sides straight. Face height is 32.8%, bottom edge 99.9%.
- **Extension is opt-in, never automatic.** Mirroring is right for unpatterned
  fabric and wrong for skin, straps, a logo or a hem, so it applies only to
  slugs in `EXTEND` (currently `jay`) or with `--replace ... --extend`. It
  declines, and feathers instead, when the gap is over 16% of the tile, since
  mirroring further would reach up into the neckline. Ally's portrait is
  unchanged: her source is a chest-cropped selfie with skin and backpack
  straps at the cut, where this would be wrong.

## [1.89.2] — 2026-09-30

### Changed

- **Team page: ten people are two rows of five.** Four per row left an
  orphaned pair on the last row (4 + 4 + 2). The column count is not hardcoded
  because the roster is editable: `remotive_team_columns()` picks whichever of
  3, 4 or 5 leaves the last row fullest (a tie goes to the wider grid), so ten
  is 5, nine is 3, eleven is 4, and from six people up no roster size strands a
  single tile on its own row. The value reaches the grid as an inline
  `--rm-team-cols` from a new `__REMOTIVE_TEAM_COLS__` token, and applies from
  1101px up only. Below that the existing two-column and one-column tiers
  stand, so five across cannot squeeze portraits on a tablet or phone;
  measured at 1440, 1150 and 900px with no horizontal overflow. This
  supersedes the stylesheet's earlier "four per row everywhere" note, which
  pre-dated a roster of ten. The homepage leadership row is unchanged.

## [1.89.1] — 2026-09-30

### Changed

- **Ally is listed third on the Team page**, directly after Jazlan: Gordan,
  Jazlan, Ally, then everyone else in roster order. She had been seventh
  because the roster sync only ever appends new members, so she sat behind
  the specialists. Fresh installs get the order from the defaults. An
  existing site's roster is reordered once by `remotive_sync_team_order()`,
  which applies each entry in `remotive_team_order_moves()` exactly one time
  and records it, so a later manual arrangement is never fought. A member
  who has been removed on purpose is skipped rather than re-added.

## [1.89.0] — 2026-09-30

### Changed

- **The homepage introduces the leadership; the Team page holds everyone.**
  Both pages rendered the same full roster, so the Team page added nothing
  the homepage did not already show, against the brief's own rule of not
  putting the team in two places. The homepage About section now shows the
  leadership only, and the Team page lists everyone, leadership first, then
  the rest in roster order.
- **Leadership is Gordan and Jazlan, and is a setting, not hardcoded.** Each
  row in Theme Options → Homepage → Team has a "Leadership" checkbox.
  A roster saved before the flag existed takes the shipped default for each
  member by slug, so the homepage changes without anyone editing anything;
  if nobody is ticked the homepage shows the first four rather than an empty
  grid. Display ordering never rewrites the saved roster order.
- **A short leadership row fills itself.** Two portraits in a four-column
  grid left the right half empty. With fewer than four leaders the row now
  ends in a tile spanning the free columns ("+8 more specialists, each
  running their own discipline — Meet the whole team →"), full width where
  the grid has fewer columns, so it cannot overflow a tablet or phone. With
  four or more leaders the row is full and a line under it says the same
  thing instead. Never both.
- **Team page copy follows the roster.** "Ten people" / "Ten specialists"
  were hardcoded and would have gone stale the day someone was added. They
  now come from a count token (`__REMOTIVE_TEAM_COUNT__`, number words up to
  twenty, digits beyond). The section anchor is `#accountable-team`.

### Added

- **A closing call to action on the Team page**, matching the page's role as
  "Our Team / Partner With Us" in the original brief: "Work with the people
  who'd run it." with a Start a project button. The page previously ended
  on its gallery.

## [1.88.0] — 2026-09-30

### Removed

- **Rollover portraits.** The alternate image that faded in over each team
  photo on hover or touch is gone, along with everything behind it: the
  `::after` layer, its hover/active and reduced-motion rules, the
  Team-page rule that suppressed it, the generator that wired it up in
  `remotive_team_portrait_css()`, and the five `assets/team/*-alt.avif`
  files. One image per person; the tile no longer changes on hover, focus
  or tap. `readme.txt`, `resources.md` and the Team-tab docblock updated.

### Added

- **Retired files delete themselves.** `remotive_retired_files()` in
  `inc/site-setup.php` lists paths the theme no longer ships (globs
  allowed) and `remotive_prune_retired_files()` deletes them as a step of
  the version sync. WordPress's "replace current theme" upload swaps the
  whole folder, but an FTP or deploy-script upload only adds files, so
  anything removed from the theme otherwise lingers on the server for good.
  This release's entry is `assets/team/*-alt.avif`, so the five alternate
  photos are removed from the live server on the first request after the
  update. Team portraits are personal likenesses, which is the reason this
  is worth automating. Confined to the theme folder: patterns containing
  `..` or starting with `/` are refused, and each match is resolved with
  `realpath()` and skipped unless it is a regular file inside the theme, so
  a symlink pointing elsewhere is never followed.
- **Replacing a photo is one command.** `tools/normalise-portraits.py
  --replace <slug> <photo>` builds the new portrait to the same framing as
  the rest of the team, overwrites the old file (that is the deletion), and
  removes anything else left over for that person (`<slug>-alt.avif` and
  stale copies in other formats). `--check` reports face size and framing
  for every shipped portrait. The tool now resolves its paths from its own
  location instead of assuming it is run from `/home/claude`, and skips a
  missing source with a message instead of crashing.
- **Portrait URLs are cache-busted by file modified time.** A replaced
  portrait keeps its filename, so a browser or CDN holding the old file
  would keep showing it and the replacement would look as though it had not
  worked. The URL now changes exactly when the file does.

### Fixed

- **The four newer portraits did not match the rest of the team.** Ally,
  Jay, Louie and Freya (added in 1.80.2 and 1.85.0) were cropped by hand
  instead of through the normalising tool, so face heights ran from 24% to
  47% of the tile against 22-27% for everyone else, and three clipped at a
  side edge. Rebuilt through `--replace`. Louie and Freya now sit with the
  original five.
- **Sources that stop at the chest no longer leave a floating cut edge.**
  Ally's and Jay's photos cannot fill a tile at the set's face size, so the
  cut-out ended mid-tile in a straight line. The tool now grows the person
  until the cut reaches the bottom edge (capped at 1.3x the standard face
  size so one face does not dwarf the rest) and feathers whatever gap
  remains into the tile. A subject that already runs off the bottom is
  untouched, so the original portraits are unchanged. Ally's selfie is the
  limit case: a photo with more shoulder would fill the tile properly.

### Changed

- The tool no longer builds alternate frames: their entries, the `SPECIAL`
  fill mode that existed only for one of them, and the cross-frame ratio
  fallback that needed a second photo of the same person are removed. Four
  team members are added to its `PEOPLE` table so a full rebuild includes
  them.

## [1.87.0] — 2026-09-30

### Fixed

- **Uploading a new theme version now actually updates the site.**
  Several things live outside the theme's files and were never touched by an
  upload: saved options (which take priority over code defaults, and are
  frozen for every field the first time the settings screen is saved), the
  team roster, the drop-ins in `wp-content/`, and page slugs. A single
  `remotive_version_sync()` (`inc/site-setup.php`) now runs once per theme
  *version* — on activation and on the first request after any upload,
  including FTP and deploy scripts that fire no WordPress hook — and each
  step is isolated so one failure cannot skip the others or repeat on every
  page load.
  - **Options.** A saved value that still equals the default the theme last
    shipped was never customised, so it is released and the new default
    shows through; a value someone edited is never touched. The first run
    has no record of prior defaults, so for the fifteen keys rewritten in the
    Fix / Found / Scale release (hero, services, why, about, CTA, stat
    labels) it releases the saved value once. Every value replaced is kept in
    `remotive_theme_options_backup` (last five runs) so it can be restored.
  - **Team roster.** Merges new members by slug and never overwrites,
    reorders or removes anyone. It now also remembers which members it has
    already offered, so someone removed on purpose stays removed instead of
    being re-added every release.
  - **Drop-ins.** Installed by the same runner, so they now also arrive on an
    FTP/upload update, not only on activation.
  - Options and roster are saved without the settings-form sanitizer, which
    fills every key it is not given and would undo the release.
- **Case-study links pointed at pages that did not exist.** Every link in the
  templates, footer and seed uses the theme's own slugs, but the live pages
  had different ones after the duplicate cleanup, so those links 404ed. The
  sync now matches live pages by title under `/case-studies/`, moves them to
  the theme's slug, tops up the theme's page template and SEO fields where
  empty (never overwriting), and remembers the old path to 301 on a 404 —
  WordPress records no old-slug redirect for hierarchical types like pages.
- **Case Studies filter counts were wrong.** The chips said "All · 14" and
  "Search & organic · 6" but the page had 13 cards (5 in that group), and the
  Singapore B2B case study was not linked from the page at all. Added its card
  and made the counts calculate from the cards on the page, so they cannot
  drift again.

### Added

- **Singapore B2B case study in the seed data**, so a fresh install
  autopopulates all 14 rather than depending on a page that only existed on
  one site. New seed items are created on the first admin load after an
  update; already-seeded items are never re-created, so a page an owner
  deleted stays deleted.

### Changed

- Menu defaults now match the agreed structure: FAQ is footer-only (removed
  from the built main menu) and the footer link reads "All 14 case studies".

### Corrected

- **`drop-ins/php-error.php` scope.** 1.84.0 said WordPress loads it for any
  fatal. This theme's `inc/error-handler.php` switches WordPress's fatal
  handler off once the theme has loaded and renders its own branded page, so
  the drop-in is used only for a fatal that happens *before* the theme loads
  (a plugin or mu-plugin). Header comment and `drop-ins/README.md` rewritten
  to say so.

## [1.86.2] — 2026-09-30

### Changed

- **Hero now previews the model.** The closing sentence of `hero_sub`
  used to end on a generic "we turn performance marketing into
  predictable pipeline growth" and never mentioned the Fix/Found/Scale
  model the rest of the page is built around. It now reads "We fix the
  foundations, get you found and cited, then scale with paid media,
  turning performance marketing into predictable pipeline growth
  across Asia and beyond," so the story opens on the same three
  stages it later explains, proves and staffs.

## [1.86.1] — 2026-09-30

### Changed

- **About section now closes the homepage story.** Continuing the
  1.86.0 narrative pass: `about_sub` ("20+ senior professionals...")
  didn't reference the Fix/Found/Scale model at all, so the section
  meant to answer "who actually runs this" read as a generic team
  blurb rather than the story's resolution (Problem → Model → Proof →
  **the people who deliver it** → CTA). Now opens with "20+ senior
  professionals who run Fix, Found and Scale end to end, not handed
  off between departments." Left `work_sub` (the Proof section)
  unchanged — it already works well without namedropping the model,
  and forcing every section to reference it by name started to feel
  repetitive rather than like a natural story.

## [1.86.0] — 2026-09-30

### Fixed

- **Team roster wasn't reaching existing sites.** `team` lives in the
  saved `remotive_theme_options` option, not in code — once WordPress
  has a saved value, it never falls back to a new code default for
  that key. Ally, Jay, Louie and Freya (added to the defaults back in
  1.80.1) therefore never appeared on a site that already had a saved
  roster, no matter how many theme versions shipped afterward. Added
  `remotive_sync_team_roster()`, hooked the same way as the drop-ins
  installer (`after_switch_theme` / `upgrader_process_complete`): it
  merges any default team member missing from the saved roster by
  slug, without touching or reordering anyone already there.
- **Homepage story now connects section to section.** The stats band
  meant to "prove" the Fix/Found/Scale model still used its pre-rename
  labels (`Demand creation` / `Demand capture` / `Conversion & data`
  — stale since the 1.81.0 Fix/Found/Scale rename), so it didn't read
  as proof of the model shown two sections earlier. Relabelled to
  `01 · Fix` / `02 · Found` / `03 · Scale`, matching the model section
  exactly. The stats band's eyebrow ("The three blocks, proved" →
  "Fix, Found, Scale — proved") and heading ("One engagement each" →
  "The same three stages, real numbers") now name the model directly
  instead of referring to it vaguely. The Why section's sub-heading
  ("What you are actually buying..." → "What makes Fix, Found, Scale
  actually work...") now explicitly bridges from the model section
  above it instead of reading as an unrelated trust pitch.

## [1.85.0] — 2026-09-30

### Added

- **Portraits for the three remaining new team members**: `jay.avif`,
  `louie.avif`, `freya.avif` — background removed, cropped to the
  standard 800×1000 team-photo frame. All three were genuine headshots
  (unlike Ally's casual selfie in 1.80.2), so no acceptability check
  was needed. Freya's needed two extra passes: the first crop centered
  on the full-body bounding box, which skewed right because her hair
  extends well past her shoulder — refit using the head-region
  centroid instead; the initial matte also left a faint shadow-ghost
  behind the flyaway hair (the pink-brick backdrop's own drop shadow,
  partially kept as semi-transparent alpha), cleaned up with alpha
  matting plus a harder foreground/background threshold. All ten team
  slugs (`gordan`, `jazlan`, `adam`, `elfie`, `alif`, `nabil`, `ally`,
  `jay`, `louie`, `freya`) now have real portraits; none fall back to
  initials any longer.

## [1.84.0] — 2026-09-30

### Fixed

- **Drop-ins now actually fire.** Bundling `db-error.php`,
  `maintenance.php` and `php-error.php` inside the theme package
  (1.82.0) didn't make WordPress load them — all three only ever load
  from `wp-content/` root. `remotive_install_error_dropins()` now
  copies them there automatically on `after_switch_theme` and on
  every `upgrader_process_complete` (theme update), so no manual copy
  step is needed. Skips the copy if a non-Remotive drop-in is already
  in place (detected via a signature comment on each file's second
  line), so it never clobbers something else.
- **Corrected a wrong claim in `php-error.php`'s own header comment**:
  it previously said this needed manual `.htaccess`/server wiring.
  It doesn't — `WP_Fatal_Error_Handler` has auto-included
  `wp-content/php-error.php` on uncaught fatals since WP 5.2, the
  same mechanism as `db-error.php`. Comment corrected; `drop-ins/README.md`
  updated to match.

## [1.83.0] — 2026-09-30

### Added

- **Case Studies page: numbered sections + working category filter.**
  Prototyped from the parent company's (remotivemedia.com) design
  patterns per the earlier design-reference session: section headings
  now read "01 · Strategy & market entry" through "04 · Search &
  organic" (matches the "01 · Fix" numbering already on the homepage
  framework), and a filter-chip bar above the grid ("All · 14",
  "Strategy & market entry · 2", etc.) toggles each category's
  `.rm-cs-section` via plain JS (`data-cs-filter` / `data-cs-category`
  attributes, no dependency). Counts match the page's real 14 case
  studies exactly. New styles in `assets/css/remotive.css`
  (`.rm-cs-filters` / `.rm-cs-filter`), reusing existing tokens
  (`--rm-line`, `--rm-ink-70`, the cyan/paper/ink palette) rather than
  introducing new ones.

## [1.82.0] — 2026-09-30

### Added

- **`drop-ins/` folder bundled into the theme package**: `db-error.php`,
  `maintenance.php`, `php-error.php` — brand-matched 503/500 pages
  (dark navy `#1a1a2e`, white ink, cyan/magenta CMYK-plate accent,
  Archivo) for a DB-connection failure, a scheduled core/plugin
  update, and a generic PHP fatal respectively. Bundled for
  distribution alongside the theme, but WordPress does not load them
  from inside a theme folder — `db-error.php` and `maintenance.php`
  still have to be copied to `wp-content/` directly (see
  `drop-ins/README.md`); `php-error.php` needs manual wiring since no
  such WP core hook exists.

## [1.81.0] — 2026-09-30

### Changed

- **Homepage framework replaced: Create/Capture/Convert → Fix/Found/Scale.**
  Per Gordan's "Fix it. Get found. Then scale." positioning deck
  (September 2026). The three service blocks in
  `templates/front-page.html` now read 01 · Fix (technical SEO,
  tracking/attribution, conversion paths), 02 · Found (topic authority,
  AI visibility/entity work, distribution), 03 · Scale (paid media,
  full-funnel attribution, lifecycle amplification) — each item still
  links to its real `/services/` page. `services_heading` and
  `services_sub` defaults updated to match; the hardcoded section
  eyebrow above the blocks changed from "Modular, on-demand
  capabilities" to "Our model." Hero copy was left unchanged — this
  was scoped to the framework section only.

## [1.80.2] — 2026-09-30

### Added

- **Ally Foo's portrait** (`assets/team/ally.avif`) — background removed
  and cropped to the standard 800×1000 team-photo frame. Source was a
  casual outdoor selfie rather than a studio headshot (confirmed
  acceptable to use as-is for now); no `-alt.avif` variant exists
  since only one source photo was available. Jay, Louie and Freya
  still have no portrait files and show the initials fallback.

## [1.80.1] — 2026-09-09

### Added

- **Four new team members**, per Gordan's confirmation: Ally Foo
  (Account Director), Jay Spicer (Performance Director), Louie See
  (Media Manager), Freya Angel (Media Manager). Added as roster
  entries with slugs `ally`, `jay`, `louie`, `freya` — headshots can
  be dropped into `assets/team/{slug}.avif` (and optionally
  `{slug}-alt.avif`) with no further code change once received; until
  then each shows branded initials per the existing fallback.

### Changed

- **Team page copy updated from six to ten** (hero sub-heading and
  section heading on `templates/page-team.html`) to match the grown
  roster.
- **`about_sub` updated** to state "20+ senior professionals. 50+
  markets activated." directly, per Gordan's positioning brief
  (grow Asian brands globally, market knowledge + execution
  expertise across markets).

## [1.80.0] — 2026-09-09

### Added

- **Closing call-to-action is now editable.** The final CTA band above
  the footer (heading, sub-heading, and button label) was hardcoded
  directly in `templates/front-page.html`. Added `cta_heading`,
  `cta_sub`, and `cta_button` as theme options, with a new "Closing
  call to action" field group under Appearance → Theme Options →
  Homepage, following the same default → admin field → sanitizer →
  token pattern as every other homepage section.

### Changed

- **Problem grid leads with the brand/agency split.** The two people
  actually being sold to — brands needing an on-demand senior team,
  and agencies needing a modular capability extension — are now the
  first two cards in the problem section, ahead of the five existing
  pain-point cards (which keep their internal links to SEO/SEM blog
  posts).
- **Shipped defaults for hero, services, and about copy updated** to
  match the new homepage brief (see docs/homepage-v1.80-brief for the
  source draft). Sites with existing saved `remotive_theme_options`
  values are unaffected until someone edits those fields in the admin
  — this only changes what a fresh install or a reset ships with.

## [1.79.5] — 2026-09-01

### Fixed

- **The gap between the hero's reassurance line and the ticker was far
  too large** — measured at roughly 460px on a 980px-tall window.

  The cause was the hero being pinned to exactly the fold
  (`100svh` minus the nav and the ticker). That guaranteed the ticker was
  visible, which is what it was added for, but the hero's content is only
  about 418px tall including its top padding, so on any normal desktop
  window there was far more empty space than the section had a use for.
  The two earlier attempts each moved that space rather than reducing it:
  centring put ~290px above the headline, and flex-start moved the same
  slack below, into the band above the ticker. Neither was a distribution
  problem.

  `min-height` is now `min(calc(100svh - 84px - 19px - 2px), 540px)`:
  fill to the fold on short windows, where that value is the smaller and
  the ticker genuinely needs the room, but stop growing at 540px on tall
  ones. Measured at six window heights from 620 to 1100px, the gap now
  holds between 90px and 127px and the ticker is fully visible at every
  one — where before it was 460px and growing with the window.

## [1.79.4] — 2026-09-01

### Changed

- **Theme Options now uses the available window width.** The panel was
  capped at 920px, which left roughly 480px of empty gutter on a normal
  desktop while the fields themselves stayed cramped. Raised to 1400px —
  still a cap rather than a full stretch, because each row is a
  label/input grid and past about that width the gap between a label and
  its own field grows far enough that the two stop reading as a pair.

  Text, email and URL inputs raised from 440px to 640px, wide enough to
  show a full URL or email address without scrolling inside the field.
  The label column now grows from 220px to a 260px cap so longer names
  such as "Company registration number (UEN)" stop wrapping to three
  lines, while never running away from the input it belongs to.

### Fixed

- **The section-description textareas had no styling at all.** No rule
  matched them, so they fell back to WordPress defaults and sat visibly
  apart from the text inputs beside them: different border, radius and
  width, and — because browsers default `<textarea>` to monospace — a
  different typeface for the same kind of content. They now match the
  inputs, with `font-family:inherit` and a wider 820px cap since they
  hold sentences rather than single values, and they drop to full width
  on narrow screens alongside the other fields.

  Measured at 1600, 1440 and 1280px: the panel fills the window to a
  20px gutter, inputs hold 640px, and the textarea shrinks gracefully
  from 820px once the window no longer affords it.

## [1.79.3] — 2026-09-01

### Removed

- **The two market landing pages, SEO Singapore and Digital Marketing
  Malaysia**, and everything that pointed at them:

  - `templates/page-seo-singapore.html` and
    `templates/page-digital-marketing-malaysia.html` deleted.
  - Their `customTemplates` entries removed from `theme.json`.
  - Their auto-creation entries removed from `inc/site-setup.php`, so
    they no longer appear in the Shipped content list.
  - Their `Service` schema nodes and their `FAQPage` branches removed
    from `inc/schema-markup.php`.
  - **Fifteen internal links rewritten**, not merely stripped. Nine
    passages across the seeded blog posts and case studies referenced
    these pages mid-sentence; deleting the anchors alone would have left
    broken grammar, and deleting the pages alone would have 404ed every
    one of them — the exact failure v1.65.7 was added to fix. Each
    sentence was rewritten so it still reads naturally, pointing at the
    relevant service page or at `/contact/` where a destination was
    genuinely needed, and at nothing where the link was decorative.

  Verified afterwards that no reference to either page remains in any
  PHP, HTML, JSON, JS or CSS file, and that all fifteen link targets now
  used in the seeded content resolve to pages that actually exist.

- Documentation updated to match: the file map in `readme.md`, the
  template list in `upgrading.md`, and both mentions in `ssot.md`. The
  `ssot.md` note explaining *why* one consolidated page per market beats
  one page per keyword is kept as guidance for anything built later, but
  now records that the pages themselves were removed.

## [1.79.2] — 2026-09-01

### Fixed

- **Shipped content, Menus, Site pages and Enquiries appeared on every
  tab.** They were not duplicated — the site-setup card and the enquiries
  card were rendered after the settings form, below the whole tabbed area,
  so they stayed on screen whichever tab was selected and read as though
  they repeated in each one. Consolidating seven tabs into four in 1.79.1
  made it more obvious, but the cards had been sitting outside the tab
  system all along.

  Both cards contain their own `<form>` elements, so they genuinely cannot
  be nested inside the settings form. They are now proper tabs instead:
  buttons appended to the tablist, panels using the standard
  `rm-admin__panel` / `remotive_panel_*` markup. No change to the tab
  script was needed — it resolves panels via `getElementById()` from
  `aria-controls`, which does not care where the panel sits in the DOM,
  and gathers buttons with a `[role="tab"]` query, so keyboard navigation,
  ARIA state and the remembered-tab restore all cover the new tabs
  automatically.

  The settings form's Save button is now hidden while either card tab is
  active, since those panels have nothing for it to submit. Without
  JavaScript the new panels stay closed and the Save button stays
  visible, which is the correct fallback for the settings form.

  Theme Options is now six tabs: Homepage, Business details, Country
  ticker, Site behaviour, Site setup, Enquiries.

## [1.79.1] — 2026-09-01

Built on 1.79.0. None of the 1.80.x changes are included: the cursor-plate
effect is not here, and neither is the ticker-band change from 1.80.1,
which was based on a misdiagnosis and made the ticker *more* likely to
fall below the fold, not less.

### Fixed

- **The ticker rendered as a navy band on the navy dark-mode page**,
  visible only as floating text and dividers. The cause was in PHP, not
  CSS: `remotive_ticker_inline_css()` emitted
  `background:#1a1a2e; color:#f7f4ec` on every request from the
  `ticker_bg` / `ticker_color` option defaults. Those literals are the
  correct *light-mode* band — a navy strip on cream — and emitting them
  unconditionally overrode the stylesheet's `.rm-ticker` rule, which is
  built on `ink`/`paper` and is supposed to invert. The theme's own
  comment on the CTA band says the ticker should be "a white band on
  navy" in dark mode; it never was, because the inline CSS won.

  Colours are now emitted **only when they differ from the defaults**, so
  an untouched install inherits the mode-aware rule, while an owner who
  has deliberately picked colours still gets exactly what they picked in
  both modes. The separator is handled the same way, for the same reason:
  its default is a translucent white, invisible against the white band
  dark mode should render.

- **`.rm-ticker` now carries fallback values** (`var(--…--ink, #ffffff)`).
  `ink` and `paper` are the one pair in the palette that swap between
  modes, and the theme defines them for light mode only — dark relies
  entirely on the `:root` block WordPress generates from `theme.json`. If
  a performance plugin strips or defers that stylesheet, both vars are
  undefined, the background falls back to transparent, and the page shows
  through. Verified the band still renders white-on-navy with global
  styles entirely absent.

- **The item divider was a hardcoded `rgba(255,255,255,.18)`** — white,
  and so invisible on the white band dark mode is meant to show. It only
  ever looked right because the band was rendering wrong. Now derived
  from `currentColor`, so it follows the text on either surface.

### Documentation

- `readme.md`, `readme.txt` and `ssot.md` updated for the tab restructure
  and the ticker fix. Every stale `Theme Options → Display` path was
  corrected to `Site behaviour`, and `Theme Options → Team` to
  `Homepage → Team`. The old readme also listed a "Call-to-Action" tab
  that had not existed for several versions; the tab list is now
  generated from and checked against `remotive_theme_options_tabs()`
  rather than written from memory.
- `readme.md` gains a **WebMCP spec conformance** table, checked against
  the W3C draft rather than recalled. It records two things specifically
  so they are not "corrected" later by mistake: `execute()` returning
  bare objects is valid (the normative IDL is `Promise<any>`; the
  `{ content: [...] }` shape in the spec's README is MCP convention, not
  a requirement), and omitting `exposedTo`/`signal` is deliberate.
- `ssot.md` records that the ticker colour defaults describe the
  light-mode band specifically, which is why they must not be emitted
  unconditionally.

### Changed

- **Theme Options: seven tabs reduced to four.** Hero, Section headings,
  Numbers and Team were four separate tabs that all edited the same page,
  so changing the homepage meant moving between four of them. They are
  now one **Homepage** tab with four labelled groups, in the order the
  sections appear on the page.

  The renderer gained an optional `groups` key. Tabs still declaring a
  flat `fields` array render exactly as before, so the change is additive
  rather than a rewrite of the settings screen.

  Two tabs were also renamed to match their contents: **Display** held
  lead retention, branded login and error handling, none of which are
  display settings, and is now **Site behaviour**; **Contact & Socials**
  also holds the legal name and UEN, so it is now **Business details**.

  Verified after the restructure that all 57 declared fields are still
  present, still defaulted, and still covered by
  `remotive_sanitize_theme_options()` — including the ones handled via its
  `$text_keys` loop rather than by individual assignment.


## [1.79.0] — 2026-09-01

### Changed

- **The jump paragraphs on six pages now say something, instead of
  announcing a table of contents.** They opened with "This page covers",
  "Answers are grouped by", "What we run for", "Jump to" — sentences
  whose only content was the fact that other sections exist. The links
  did the work and the prose around them was filler.

  Rewritten so each carries the argument the page is making, with the
  links falling inside it where they belong. The Services page now
  frames the three blocks as the three problems a brief usually turns
  out to be. The Malaysia page leads with why a plan built for Singapore
  underperforms there, then links the work that follows from it. Case
  Studies explains why the cases are grouped by problem rather than by
  service. About opens on the complaint the company was founded to
  answer. A reader who never clicks a link still gets the page's point.

- **The links themselves are quieter.** Every one was
  `magenta-dark` at weight 600 with a full-width underline, so a
  paragraph with four of them was mostly coloured text and the emphasis
  landed on navigating rather than on what the page said. They are
  inline links in a lead paragraph, not calls to action, so they now
  inherit the paragraph's colour and carry a hairline underline that
  thickens and darkens to full ink on hover. Underlined text inside body
  copy is unambiguously a link, so the affordance survives without the
  line shouting — and contrast rises on hover rather than falling, the
  same principle as the `.rm-cta__note` fix in 1.77.1.

  All 22 anchors across the six pages re-checked against the `id`
  attributes actually present in each template after the rewrite: none
  broken. Copy checked against the site's own conventions too, which
  caught an em dash in the FAQ paragraph that the rest of the site
  avoids.

## [1.78.0] — 2026-09-01

### Changed

- **The About page's values grid is now carded, matching every other
  page.** `.rm-value` was a bare grid item — no surface, border, padding
  or accent — while the homepage's `.rm-block`, Services'
  `.rm-service-detail`, Case Studies' `.rm-cs-card` and Insights'
  `.rm-post-card` were all cards. It was the last grid of comparable
  items on the site still rendering as loose text columns. Under a
  heading reading "three things we will not trade away", three unbounded
  paragraphs read as prose rather than as three distinct commitments.

  Given the same treatment as `.rm-service-detail`, including the
  equal-height mechanics that took three attempts to get right there
  (1.76.0–1.76.2): `align-items` on the container, `align-self` /
  `height` / `min-height` on the card, and `flex:1 1 auto` on the
  element that absorbs the slack. The three statements are markedly
  different lengths, so without those the middle card would sit visibly
  taller than the ones beside it. Verified equal within each row at
  1280, 1024, 700 and 600px — per row rather than across the grid, since
  three cards in a two-column layout legitimately wrap.

### Fixed

- **The values grid was clamped to 740px inside a 1216px section** —
  exactly the bug `.rm-service-grid` had in 1.76.0. The parent section is
  `is-layout-constrained`, so WordPress caps every direct child at
  `contentSize` unless it opts out, squeezing three cards into a third of
  the available width. Same explicit wide-measure override applied.

- **About page headings sat hard against their body copy.**
  `.rm-about__lead` and `.rm-section-lead` do the same job — the
  standfirst under a section heading — but carried `margin:0 0 .5rem`
  against `.rm-section-lead`'s `.15rem 0 2rem`. A quarter of the gap
  below meant every section on the About page was visibly tighter than
  the same construction everywhere else, which is the main reason the
  page read as a different template rather than a different page.
  Aligned to the shared value.

## [1.77.1] — 2026-09-01

### Fixed

- **The privacy-policy link in the CTA band was effectively unreadable.**
  `.rm-cta__note` set its own text colour but never set one for the link
  inside it, so the `<a>` fell through to the global `elements.link`
  value from `theme.json` — a colour chosen against the page surface,
  not against the inverted navy band the note actually sits on. In light
  mode that resolved to `magenta-dark` (#cd360b) on #1a1a2e.

  Measured: **3.35:1**. That fails the 4.5:1 minimum for body text
  outright, and it is *lower* than the surrounding note text's own
  5.31:1 — which is why the link read as damaged rather than as
  emphasis. A link should never be harder to read than the sentence
  containing it.

  Fixed with the pattern `.rm-form-privacy` already used for this exact
  situation: the link inherits its parent's colour, which is already
  contrast-checked against the band, and carries its affordance on an
  underline instead of on hue. Hover lifts to full white (17.06:1), so
  the interaction cue is a contrast *increase* — never a decrease, which
  is what the old hover state did.

- **`.rm-steps__note` had the same gap**, on the thank-you page's
  privacy link. Less severe there because that note sits on the page
  surface rather than an inverted band, but it was the same missing
  rule. Fixed identically, so all three privacy-policy notes on the site
  now behave the same way rather than two matching and one not.

  Verified in both colour modes that the link's computed colour is
  identical to its surrounding note text and that the underline is
  present in each.

## [1.77.0] — 2026-09-01

### Changed

- **The stronger parallax layer now runs on every page, not just the
  homepage.** It already existed as a complete system (v1.73.1) — hero
  mark drift with scale, headline lift, trailing sub and buttons, cards
  arriving from further away, the spread panel panning, the ticker band
  shifting, the CTA headline closing on movement — but all 27 of its
  selectors were gated behind `body.home`.

  The original reasoning for that gate was that interior pages are read
  rather than scrolled, so movement would compete with the text. That
  reasoning still holds; it just never applied to what this layer
  targets. Every selector in it is structural — hero elements, section
  heads, cards, the spread panel, the ticker band, the CTA headline.
  None of it is body copy. Paragraphs, lists, post content and the
  long-form `.rm-legal` / `.rm-page` prose are deliberately still
  excluded and keep only the restrained base motion, so a reader on a
  case study or a policy page gets still text inside a moving frame
  rather than moving text.

  The gate is now `.rm-motion` alone, so the Theme Options toggle and
  `prefers-reduced-motion` both still switch the whole layer off.

### Added

- **Interior-page card types joined the strong layer:**
  `.rm-service-grid .rm-service-detail`, `.rm-cs-card`, `.rm-post-card`
  and `.rm-sidebar__card`. These only exist off the homepage, so they
  were never in a homepage-scoped list — without adding them, the
  Services, Case Studies and Insights grids would have kept the base
  motion while everything around them moved on the stronger one. That is
  the same class of inconsistency that made the Services cards look
  wrong in 1.76.1.

  All four were added to the `prefers-reduced-motion` disable list at
  the same time. Verified by parsing both selector lists out of the
  stylesheet and diffing them: 17 selectors animated, 17 disabled, no
  selector present in one and missing from the other. A card type that
  animates but is not disabled is the one failure mode in this system
  that is an accessibility problem rather than a cosmetic one, so it is
  checked mechanically rather than by reading.

  Confirmed on an interior page that a card resolves to
  `animation-name: rm-rise-strong` on `animation-timeline: view()` with
  no horizontal overflow, and that under `prefers-reduced-motion` the
  same card resolves to `animation-name: none`, `opacity: 1`,
  `transform: none`.

  The IntersectionObserver fallback in `remotive.js`, for browsers
  without `animation-timeline: view()`, needed no change: it was already
  keyed on `.rm-motion` alone and never homepage-scoped.

## [1.76.2] — 2026-09-01

### Fixed

- **Services cards could render at unequal heights.** 1.76.1 attributed
  this to the missing scroll reveal, which was wrong: adding the reveal
  was a real fix for a real gap, but it was not this. The card CSS
  itself never actually guaranteed equal heights — it only happened to
  produce them in the cases tested.

  The homepage's `.rm-block` gets equal heights from
  `.rm-block__list{flex:1 1 auto}`, which lets the list absorb whatever
  space is left after the card is stretched to its row. The Services
  card used `margin-top:auto` on its list instead, which pushes the list
  down but never tells the card to fill its row — so a card whose
  content was shorter than its neighbour's had nothing forcing it to
  match.

  Fixed by adopting the homepage's mechanism and stating every step
  explicitly rather than relying on defaults holding:

  - `.rm-service-grid{align-items:stretch}` — already the initial value,
    declared anyway because each card is a `wp-block-group` and
    WordPress layout CSS sets `align-self` on block children in several
    contexts, any of which would silently opt one card out of
    stretching.
  - `.rm-service-detail{align-self:stretch !important; height:100%;
    min-height:0}` — overrides any inherited `align-self`, makes the
    card fill the row it is stretched into rather than only its own
    content, and stops a flex child's automatic minimum size from
    fighting that.
  - `.rm-service-detail__list{flex:1 1 auto}` — replaces
    `margin-top:auto`, so the leftover space lands inside the card and
    its bottom border sits on the row edge instead of the content
    floating with a gap beneath it.

  Verified against a deliberately worst-case pair — a two-short-item
  card beside a four-wrapping-item card — at 1440, 1200 and 1024px:
  identical heights and identical top offsets in every combination,
  where the previous CSS was only equal because the real content
  happened to balance.

## [1.76.1] — 2026-09-01

### Fixed

- **Services cards appeared at different heights and vertically offset
  from each other.** They are not actually different heights: measured
  in a settled state, every card in a grid returns an identical height
  and an identical top offset (236px each in the three-card block, 214px
  each in the two-card block). What the screenshots caught was the
  scroll reveal — or rather its absence.

  The reveal system animates each card on its own visibility, which
  deliberately produces a stagger as a row scrolls in. Its selector list
  covers `.rm-plate`, `.rm-block`, `.rm-post-card`, `.rm-feature`,
  `.rm-row`, `.rm-team__member` and `.rm-sidebar__card` — every card
  type on the site except `.rm-service-detail`, which only became a card
  in 1.76.0 and was never added. So while the rest of the page rose into
  place, the Services cards sat static, and a screenshot taken mid-scroll
  shows them at inconsistent apparent positions.

  Added `.rm-service-grid .rm-service-detail` to the reveal list, in
  **both** paths: the CSS scroll-driven animation and the
  IntersectionObserver fallback in `remotive.js` for browsers without
  `animation-timeline: view()`. A card type present in one path and
  missing from the other would reveal inconsistently by browser, so the
  JS list now carries a note to keep the two in step.

  Confirmed the animation cannot itself cause unequal heights: the
  `rm-rise-sm` keyframes touch only `opacity` and `transform`, never
  layout.

## [1.76.0] — 2026-09-01

### Changed

- **The Services page now presents its items as a card grid, matching
  every other page on the site.** It was the only page stacking a set of
  comparable things as full-width bordered rows — one item per screen
  width with a hairline between — while the homepage uses `.rm-block`
  cards, Case Studies uses `.rm-cs-card`, and Insights uses
  `.rm-post-card`. That is why it read as a different site rather than a
  different page.

  Each of the three blocks now wraps its items in a `.rm-service-grid`
  using the same mechanics as the homepage's `.rm-blocks`: the same
  290px column floor, the same 1.5rem gap, the same card treatment
  (card surface, 1px border, 3px accent border-top, 6px radius, the
  same hover lift and accent-tinted border).

  Three things this surfaced, each measured rather than assumed:

  1. **The grid was being clamped to 740px.** Its parent block is
     `is-layout-constrained`, so WordPress caps every direct child at
     `contentSize` unless it opts out — the block measured 1216px wide
     while the grid inside it sat at 740px, which would have forced a
     three-card row into two columns regardless of available space.
     Fixed with the same explicit wide-measure override the hero
     children use.
  2. **Uncapped `auto-fit` produced four columns on a wide screen.** The
     homepage can use bare `auto-fit` because it always holds exactly
     three items; the Services blocks hold 3, 2 and 2, so a wide
     viewport stretched two cards across four tracks and left a visible
     empty slot. Capped at three columns above 1000px — the homepage's
     own column count — with `auto-fit` still collapsing below that.
  3. **Card contents were inset from the card's own left edge.** Every
     child of a detail item is itself inside a constrained group, so
     WordPress gave each auto side margins to centre against
     `contentSize` — invisible in the old full-width rows, but inside a
     ~360px card it pushed the heading 40.8px off the card edge. Reset
     so every line starts on the card's padding box.

  The swatch is now hidden inside cards: the block accent is already
  carried by the card's `border-top`, exactly as on the homepage, so a
  second copy of the same colour was redundant. The per-item colour
  modifiers removed in 1.75.1 stay removed — the accent still comes from
  the parent block's single `--rm-svc-accent`.

  Verified at 1440/1280/1024/800/600px: three columns down to 1024,
  two at 800, one at 600, no horizontal overflow at any width, and the
  three jump-link anchors still resolve after the restructure.

## [1.75.1] — 2026-09-01

### Fixed

- **Services page swatches were inconsistent with the homepage, and one
  block's were not rendering at all.** The three Services blocks mirror
  the homepage's Demand Creation / Demand Capture / Conversion & Data
  blocks, but each detail item carried its own colour modifier rather
  than inheriting the block's accent, which had drifted in three
  separate ways:

  1. **`rm-service-detail--green` was never a class.** The third accent
     was renamed to `accent-3` in v1.9.0 precisely because "green" was a
     naming trap (light mode's third accent is yellow), and the CSS
     defines `--yellow`, not `--green`. Every swatch in the Conversion &
     Data block was therefore an empty bordered box — visible in the
     screenshots as the only block with no colour.
  2. **The two that did resolve used the full-strength hue** while the
     eyebrow directly above them used the `-dark` ramp, so a single
     block showed two different pinks. The homepage uses `-dark`
     throughout for a documented contrast reason (magenta on paper is
     2.90:1, cyan 2.51:1, both under the 3:1 floor for large bold text).
  3. **No dark-mode lift**, so the swatch stayed at the palette value
     where the eyebrow above it correctly lifts to `#ff66b8`.

  Fixed by adopting the homepage's own pattern: one `--rm-svc-accent`
  per block, set once on the block, used by both the eyebrow and every
  swatch inside it. The now-redundant per-item modifiers were removed
  from the markup. This makes the same drift structurally impossible
  rather than just correcting today's values.

  Verified by measurement in both modes: swatch `background-color` and
  eyebrow `color` now return identical values for all three blocks in
  light mode and in dark, including the previously-unstyled third block.

- **Duplicated copy under the Services hero.** The hero subhead already
  said "Three connected blocks: create demand, capture it, then convert
  and measure it. Activate one block or all three, with no retainer
  bloat", and the jump-links paragraph immediately below opened with
  "Three connected blocks, and you can take one or all of them" — the
  same two claims restated in consecutive paragraphs. The jump paragraph
  now opens with "Jump to", which is what it is actually for. All three
  anchor targets confirmed still present after the edit.

## [1.75.0] — 2026-09-01

### Fixed

- **Six of seven Insights articles published without a featured image**,
  leaving the archive grid showing blank cards beside one that had an
  image. Nothing was actually missing from the theme: all seven posts
  declare an `'image'` key and all seven PNG/AVIF pairs are present in
  `assets/seed-images/`. The gap was in *when* the existing backfill
  ran.

  `remotive_backfill_seed_images()` already exists and already does the
  right thing — it was added at schema 1.66.0 for this exact class of
  problem. But four of the seed images (`seo-cost-singapore`,
  `seo-services-pricing-malaysia`, `seo-vs-sem`, and a regenerated
  `how-to-choose-an-seo-agency`) were added to the theme on 31 Aug,
  *after* 1.66.0 had already completed on the live site. Two mechanisms
  then combined to make that permanent: `remotive_seed_run()` skips any
  item it has previously recorded in `REMOTIVE_SEEDED`, even if the site
  owner deleted it, and the migration registry never replays a version
  it has already stored. With the schema constant sitting at 1.66.1,
  nothing further could run, so the four late-arriving images had no
  path to the posts.

  Fixed by registering a new `1.75.0` migration that calls the same
  backfill again, and advancing `REMOTIVE_SETUP_SCHEMA` to match. No
  change to the backfill function itself — it was already correct.

  Safe by construction, not by luck: the backfill skips any post that
  already has a thumbnail, so it cannot overwrite an image the owner
  chose deliberately, and re-running it on a complete site attaches
  nothing. Verified the version logic reaches the new entry rather than
  assuming it: simulated `ksort` plus `version_compare` over the full
  registry, confirming a site stored at 1.66.1 runs exactly the 1.75.0
  migration and replays none of the earlier five. Also confirmed every
  declared image has both its PNG and its AVIF companion on disk, so the
  attach step (which copies the AVIF sibling alongside the PNG for
  `inc/avif.php`) cannot half-succeed.

### Changed

- **The "problem we solve" grid now links only to blog posts, never to
  services, case studies or team pages.** Four of its six links pointed
  at `/team/`, `/services/analytics/`, `/services/seo/` and
  `/services/email/`. That grid sits *above* the Services and Proof
  sections, both of which exist to send visitors to exactly those pages
  — so those links pre-empted the sections doing that job, and because
  search engines generally credit only the first anchor text pointing at
  a given URL from a page, they also spent each service page's
  first-link priority on a problem card's wording rather than on the
  Services section's own better-matched anchor.

  A problem statement raises a question, so the honest destination is an
  article that answers it, not a product page that assumes the reader
  has already decided. New destinations, all verified to exist as
  published posts in `inc/content-seed-data.php` rather than assumed:

  | Card | Now links to |
  |---|---|
  | In-house teams stretched thin | `how-to-choose-an-seo-agency` |
  | Rising acquisition costs | `seo-vs-sem` (unchanged) |
  | Agency sprawl | `sem-services-singapore` |
  | Invisible to AI search | `seo-friendly-web-design` |
  | Lists left idle | `facebook-advertising-malaysia` |

  Three cards — "Siloed data and CRM", "Weak attribution" and
  "Dashboards nobody acts on" — are now unlinked. There is no post that
  honestly answers those three, and pointing them at a loosely-related
  article to avoid an empty slot would be worse than leaving them as
  statements; `remotive_render_feature_grid()` already renders unlinked
  items as plain cards, so this needed no rendering change. If an
  attribution or reporting article is written later, those are the slots
  waiting for it.

### Fixed

- **Invalid block markup in two homepage section headers.** The Services
  and Proof sections had their `<p class="rm-section-lead">` nested
  *inside* the `wp:heading` block, between the `<h2>` and the closing
  `<!-- /wp:heading -->`. A `core/heading` block may only contain its
  heading, so WordPress would flag both as invalid content the moment
  either was opened in the Site Editor, and could drop the paragraph on
  save. Five other sections on the same template already had the correct
  structure (lead in its own `wp:paragraph` block); these two now match
  it. Renders identically either way — this is about surviving an edit
  in the editor, not about current appearance.

  Found while verifying a separate question about whether that section's
  fonts matched the rest of the page. They do: measured computed values
  put the Services eyebrow, `<h2>` and lead at Saira 11.52px/49.6px/16.32px,
  identical to the same elements in other sections. The one non-Saira
  element in that grid, the "Outcome:" line in Space Grotesk, is a
  deliberate site-wide treatment for small uppercase cue text, used the
  same way in five other components (`.rm-cs-card__link`,
  `.rm-feature__cue`, `.rm-step__num`, `.rm-stat__block`,
  `.rm-stat__cue`).

## [1.74.4] — 2026-08-31

### Fixed

- **Oversized gap between the nav and the headline.** 1.74.1 used
  `justify-content:center` to distribute the hero's slack, which splits
  it evenly above and below the content — on a tall viewport that put
  ~289px of dead space above a content block only ~308px tall, nearly as
  much empty space as content. Changed to `flex-start`, so the content
  begins where the section's own `padding-block` says it should (104px
  under the nav) and the remaining space falls below it, ahead of the
  ticker. The `min-height` is untouched, so the ticker stays exactly at
  the fold — this changes only how the leftover space is distributed,
  not how much there is. Verified: gap now measures exactly 104px at
  1920×980, 1440×750 and 1832×912, with the ticker still fully visible
  and all hero children still sharing one left edge at each size.

### Added

- **Groundwork for a future hero background image or video**, inert
  until switched on. `.rm-hero::after` provides the media layer
  (`::before` was already taken by the Re: brand mark), sitting at
  z-index 0 between the background and the z-index-1 content, so text
  stays above whatever lands there. Three custom properties on
  `.rm-hero` are the whole interface: `--rm-hero-media` (default
  `none`), `--rm-hero-media-opacity`, and `--rm-hero-media-overlay` (a
  legibility scrim in the mode-aware `paper` colour, so it stays correct
  in both light and dark). A `.rm-hero__media` class is styled for the
  video route, already excluded under `prefers-reduced-motion` rather
  than left as a note for later.

  The overlay defaults to `0`, not to a scrim value: `box-shadow` paints
  whether or not `background-image` resolved to anything, so a non-zero
  default would tint the hero with no image present.

  **Verified genuinely inert by full-page image diff** — rendered the
  homepage with these rules and with them removed, and compared the two
  screenshots pixel by pixel: no bounding box of difference at all. Two
  false alarms along the way, both worth recording: a 1-value colour
  difference inside the hero turned out to be the pre-existing
  `::before` brand mark at opacity .2, not this layer; and a first diff
  attempt "found" large differences that were an artifact of the
  comparison file being cut mid-rule by a crude text strip, not of the
  code under test. The clean diff above is the one that counts.

## [1.74.3] — 2026-08-31

### Fixed

- **Ticker was invisible above the fold even with 1.74.1/1.74.2 deployed —
  not a caching or deployment problem, a math one.** 1.74.1's fix
  (`min-height:calc(100svh - 84px)`) closed the gap between the hero and
  the ticker to exactly 0px, which reads like the right target but
  isn't: a 0px gap means the ticker's top edge lands exactly on the
  fold boundary, so none of its height is actually within the visible
  viewport — indistinguishable from the ticker not existing, without
  scrolling. Confirmed by measurement before changing anything:
  `tickerTop: 981` against a 980px-tall viewport, i.e. the ticker
  started 1px *below* the visible area.

  Fixed by subtracting the ticker's own height too — 19px, measured
  directly rather than estimated, and confirmed stable across every
  `@media` block in the file since `.rm-ticker__item`'s padding and
  font-size are fixed rem values that don't change at any breakpoint.
  `min-height` is now `calc(100svh - 84px - 19px - 2px)`, hero plus
  ticker together filling exactly what's left below the nav — the extra
  2px is a rounding buffer, added after the first attempt at just
  `84px + 19px` left the ticker's bottom edge 1px past the fold on
  measurement, not assumed necessary in advance.

  Verified fully visible (`rect.top >= 0 && rect.bottom <= innerHeight`)
  at four viewport sizes (1920×980, 1440×750, 1832×912, 1024×900), not
  just the one size the bug was reported at.

## [1.74.2] — 2026-08-31

### Fixed

- **Homepage hero content no longer shares a left edge — regression
  from 1.74.1.** Giving `.rm-hero` `display:flex;flex-direction:column`
  to vertically centre its content within the fold changed how the
  headline, button row and reassurance line each positioned themselves,
  because each relied on a different mechanism that happened to produce
  the same result under normal block flow but doesn't under flex:

  - `.rm-hero__display{margin:0 0 1.6rem}` sets `margin-left:0` via the
    3-value shorthand's left-takes-right-value rule — under block flow
    this element also stretched to fill the container, so `0` and the
    wide-measure inset were the same number by coincidence. Under flex,
    they aren't.
  - `.rm-hero__actions` and `.rm-hero__reassure` had no explicit
    `margin-left` at all, falling through to `.rm-hero > *{margin-left:auto}`
    — auto-margin centring of a flex item's cross-axis size doesn't
    reliably reproduce block-flow auto-margin centring once the item's
    effective width is governed by flex's own stretch/sizing algorithm.

  Only `.rm-hero__sub` was already safe, because it was fixed the same
  way back in v1.16.2 for an unrelated reason (its own narrower 56ch
  max-width, not flex) — it already used an explicit computed
  `margin-left` instead of `auto`.

  **Fix:** extended that same explicit formula
  (`max(0px, calc((100% - var(--wp--style--global--wide-size)) / 2))`)
  to all four hero children uniformly, rather than leaving three of them
  dependent on `margin:auto` continuing to coincidentally match it.
  Verified by measurement, not visual inspection: all four elements'
  `getBoundingClientRect().left` now return the identical value at every
  viewport width tested.

  **Also corrected a stale, factually wrong comment** on this rule,
  written when it only covered the subhead: it claimed the About page
  avoids this rule by using a separate `.rm-about-hero` class. Checked
  directly — no such class exists anywhere in the templates; the About
  page uses the identical `.rm-hero` container. Confirmed this rule
  applies there too, and that it's harmless: the About hero isn't flex,
  where `margin:auto` and this explicit formula already produced
  identical results for any full-width-capped child, verified by
  rendering the About hero's markup directly (eyebrow, headline and sub
  all measured at the same left edge, including the eyebrow paragraph,
  which the rule doesn't even target — it was already correct there and
  stays that way).

## [1.74.1] — 2026-08-31

### Changed

- **Homepage hero fills the space above the fold instead of leaving it
  content-driven.** On any viewport taller than roughly 700px, the
  hero's height was just its own padding plus a three-line headline and
  a short subhead — visibly less than the available above-the-fold
  space, leaving an empty gap between the reassurance line and the
  ticker band below.

  `body.home .rm-hero` now gets `min-height:calc(100svh - 84px)` (84px
  matching `.rm-nav__row`'s own `min-height`, so the hero fills exactly
  what's left below the sticky nav) plus `display:flex;flex-direction:column;justify-content:center`,
  so the content block sits vertically centered within that space —
  min-height, not a fixed height, so a longer headline from a future
  copy change still grows the section rather than being clipped.

  **Scoped deliberately narrow.** Desktop-only (`min-width:782px`,
  the breakpoint already used throughout this file) — a mobile hero's
  narrower column already wraps the same headline across more lines,
  filling a much larger share of its own viewport without help; the
  dead-space problem this fixes is wide-viewport-specific. Homepage-only
  (`body.home`, the same scoping pattern already used for the stronger
  parallax in 1.73.1) rather than every `.rm-hero` — the class is reused
  on Contact/About/Services, whose shorter content and different
  surrounding sections weren't part of this and haven't been checked
  against a full-height treatment.

  Verified, not assumed: rendered the real markup at 1920×980 and
  measured directly rather than trusting a screenshot — hero ends
  exactly where the ticker begins (0px gap), content sits centered
  within the available space (rounding aside, the small remaining
  top/bottom difference is the pre-existing asymmetric padding, not a
  centering error), a shorter 1440×750 viewport produces a
  proportionally shorter hero with no forced overflow, and mobile
  measures `min-height:0` / `display:block` — completely unaffected, as
  intended.

## [1.74.0] — 2026-08-31

### Added

- **Contextual jump links in the opening section of six pages.** 22 fragment links across About, FAQ, Services, Case Studies and the two market pages, written as one sentence of prose with the links inline rather than as a table of contents. A boxed list of links at the top of every page is clutter and reads as filler; a sentence that happens to be navigable reads as an introduction and gives Google the same on-page anchor signal.

  Example, Services: "Three connected blocks, and you can take one or all of them: [build new demand] through paid, social and creative, [capture existing intent] through search and category strategy, then [convert and prove value] with analytics and CRM in place."

  **Opening section only, never repeated.** Each page carries exactly one such block, placed immediately after the hero. Verified: no fragment target is linked more than once on any page, so the links keep their weight and the body text stays free of repeated internal links.

  **Excluded deliberately.** The homepage, because its sections are already reachable from the main navigation and its hero is a conversion element a row of links would clutter. The Team page, which has only two sections — not enough to be worth jumping between. Post-driven and short templates for the same reasons as the anchors themselves.

### Fixed

- **Heading hierarchy violations.**
  - **Contact page skipped h1 → h3.** The three sidebar cards (Email, Office, Elsewhere) were `h3` directly under the page `h1` with no `h2` between them. They are top-level sections of that page, so they are now `h2`, each with an anchor.
  - **Proof rows carried two sibling `h3`s per row**, one of which was a bare figure — "85%" and "Global automotive marque" were marked up as headings of equal rank. The client name is the heading; the metric is a value. Metrics demoted to paragraphs, leaving one heading per row.

  Every template now runs h1 → h2 → h3 with no skipped level, exactly one `h1`, and the `h1` first. Audited across all 19 templates including the levels emitted by the PHP renderers in `inc/feature-grids.php` and `inc/stats-band.php`.

### Verified

- 32 heading anchors, all lowercase and hyphenated.
- Every `href="#…"` on every template resolves to an ID that exists on the same page.
- No duplicate IDs within any template.
- Form field IDs and the lightbox's JavaScript hooks keep their existing names — renaming those would break `for`/`id` label pairing and the script. The lowercase-hyphenated convention applies to anchor targets.

## [1.73.1] — 2026-08-31

### Changed

- **Stronger parallax on the homepage.** The motion added in 1.69.1 is deliberately restrained because it runs on every page, including long reading pages where movement competes with the text. The homepage is a different job — it is scrolled rather than read — so it now carries a considerably heavier layer of its own.

  | Element | Before | Now |
  |---|---|---|
  | Hero mark | 70px drift, 4° rotate | **190px** drift, 9° rotate, scales to 1.18 |
  | Hero headline | static | lifts **46px** against the page as it scrolls |
  | Hero sub-copy and buttons | static | trail at **22px**, half the headline's rate |
  | Cards (features, blocks, stats, rows, team) | rise 14px | rise **58px** with a scale-up from 0.965 |
  | Section heads | rise 22px | rise **52px** over a longer range |
  | Case-study colour panel | pan ±14px at 1.06 | pan **±52px** at 1.2 |
  | Ticker band | static | shifts ±14px against the sections around it |

  Depth comes from elements moving at different rates against each other rather than simply moving further: the mark travels down while the headline lifts, and the sub-copy trails at half the headline's rate, so the three read as separate planes instead of one block.

  **Scoped to `body.home`**, which WordPress adds on the front page, so every interior page keeps the subtle version. Verified by specificity rather than source order — each homepage rule outweighs its base counterpart, so the override cannot be broken by later edits reordering the file.

  **Still transform and opacity only**, so it stays on the compositor and none of it can cause layout shift. The Theme Options "Scroll motion" toggle and `prefers-reduced-motion` both switch it off, and the reduced-motion block lists every new selector explicitly.

  **No horizontal movement on grid children**, deliberately: an x-translate inside a grid can widen the document and produce a horizontal scrollbar. Depth here is carried by distance, scale and rate instead. The ticker band clips its own shifted contents so the movement cannot reveal the page behind it at the band edges.

  Browsers without CSS scroll-driven animations keep the one-shot reveal fallback. Scroll-linked parallax is not reproducible there without a scroll listener, which the motion system avoids by design.

## [1.73.0] — 2026-08-31

### Added

- **Anchor IDs on section headings.** 29 headings across seven pages now carry stable IDs, so a section can be linked to directly — `/faq/#how-we-report-what-numbers-mean`, `/services/#capture-existing-intent`, `/about/#three-things-will-not-trade`. Useful for citing a section in a reply, and it lets search engines offer jump-to-section results.

  IDs are written both as the block's `anchor` attribute and as the element's `id`. WordPress's heading block stores anchors in the block JSON; an `id` on the element alone would be dropped the next time the block was saved in the editor.

  | Page | Anchors |
  |---|---|
  | About | 8 (5 section headings, 3 sub-points) |
  | FAQ | 4 |
  | Case Studies | 4 category headings |
  | SEO Singapore | 4 |
  | Digital Marketing Malaysia | 4 |
  | Services | 3 |
  | Team | 2 |

  **What was deliberately left alone**, since an ID on everything would be noise rather than navigation:

  - **The homepage.** Its section wrappers already carry `#problem`, `#services`, `#why`, `#work`, `#about` and `#contact`. A heading ID would be a second competing anchor for the same section.
  - **Card and metric headings** — `rm-cs-card__metric`, `rm-row__metric`, `rm-block__name`. Values like "85%" appear more than once on the same page, so the IDs would collide or mean nothing.
  - **CTA titles.** A call to action is not a destination.
  - **Hero h1s.** One per page; the page URL is already the anchor for it.
  - **Post-driven templates** — `single`, `archive`, `home`, `index`, `search`, `page`, `page-legal`, `page-service`. Their headings come from post data, so a static ID would be wrong on every post but one.
  - **404 and Thank You**, both short, and the latter `noindex`.

  Slugs drop leading filler words so they stay readable and stable, and are deduplicated per page. Verified: no template contains a duplicate ID. No CSS was needed — `:where([id])` already carries `scroll-margin-top:100px` for the sticky header, added for WCAG 2.4.11, so every new anchor clears it automatically.

## [1.72.4] — 2026-08-31

### Changed

- **Duplicate "All case studies" link removed from the results section.** The proof section immediately above already closes with that exact link to that exact URL, so adding another to the results section head introduced a second identical anchor to the same destination on the same page — the first-link-priority dilution corrected in the feature grids in 1.70.1, reintroduced two releases later in 1.72.0.

  The section does not need it: every card in the band already links to a specific case study, which is a better destination than the index for a reader who has just seen the figure.

  The two remaining links to `/case-studies/` are kept deliberately. The hero's secondary button and the proof section's closing link have different anchor text, different intent and sit far apart on the page; the removed one repeated the text and the destination of a link a few hundred pixels above it.

## [1.72.3] — 2026-08-31

### Fixed

- **The retired figures were still displaying, and 1.72.0 made them worse.** Correcting the defaults in 1.72.0 fixed new installs only: Theme Options values already saved to the database override a default, so "3.2× average ROAS", "+140% organic traffic, year one" and "98% client retention" stayed on the live homepage.

  In the old bare band they were unsourced claims. In the new band they are labelled with a service block and carry a "Read the case →" link — so "3.2× average ROAS" pointed at a case study containing no such number, "+140%" at the case whose actual year-on-year figure is +43.5%, and "98% client retention" at a case with no retention data at all. An unsourced claim had become a checkable false attribution that any visitor who clicked would discover immediately.

  New `remotive_retire_unsourced_stats()` runs as migration `1.66.1` and resets those three stat groups to the documented defaults. **Deliberately narrow**, because Theme Options are the owner's data and a theme has no business overwriting them: a stat is only reset when its label matches one of the three retired claims exactly *and* its value contains the matching number. A coincidental "98%" against a different label, an owner-written ROAS line, or any other value is left untouched and the option is not written at all. Verified against nine cases including near-misses.

- **Results section head wrapped with a large gap.** The section was given `layout: constrained` but not `rm-measure-wide`, so it fell back to the 740px content measure instead of the 1320px wide measure every other section head uses. The "All case studies" link had no room beside the heading and wrapped onto its own line below the lead. Added the missing class.

## [1.72.2] — 2026-08-31

### Changed

- **Portrait hover swap removed on the Team page, kept on the homepage.** The two-crop cross-fade is a small flourish on the homepage teaser, where six tiles are scrolled past. On `/team/` it is the wrong behaviour: the portraits are the content rather than decoration, and a face changing under the cursor while someone reads a name and a role is a distraction.

  Scoped to `.rm-team--full`, a class only `templates/page-team.html` carries, so `templates/front-page.html` is untouched and keeps the swap.

  **The generated CSS is left alone.** `remotive_team_portrait_css()` puts the base portrait on the tile's own `background` and only the alternate crop on `::after`, so suppressing the pseudo-element on this page removes the second pose and leaves the main portrait fully intact. Nothing about that function, the homepage, or the touch `:active` fallback changes — one scoped rule, no branching in PHP. The cursor is also reset to default on the team page, since a tile that no longer swaps should not imply that it does.

## [1.72.1] — 2026-08-31

### Fixed

- **Three Insights articles had no featured image.** `seo-vs-sem`, `seo-cost-singapore` and `seo-services-pricing-malaysia` were added to the seed data without an `'image'` key, while the original four articles each declared one. They published with no thumbnail, leaving blank cards in the Insights grid and no image for social sharing or search results.

  Three images created to match the four that already ship — 1200×675, diagonal brand gradient, "RE:MOTIVE · INSIGHTS" eyebrow, uppercase two-line title. Rendered in the theme's own Saira 700 and 900, converted from the bundled woff2 files, so the typeface matches the existing set rather than approximating it. Gradient pairs and the layout were sampled from the shipped PNGs rather than guessed, and pairs were chosen so no two adjacent cards in the archive grid repeat a combination. Each has an AVIF companion (~5KB against ~32KB), matching the existing convention that `inc/avif.php` relies on.

  **Backfill migration for existing sites.** Correcting the seed data alone would fix new installs and leave every existing site with three blank cards, because seeding touches each item exactly once and never returns to it. New `remotive_backfill_seed_images()` runs as migration `1.66.0`: it walks the seed content, and for any item whose post exists but has no thumbnail, attaches the bundled image. A post that already has a thumbnail is skipped, so an image chosen by hand is never overwritten by the bundled one.

## [1.72.0] — 2026-08-31

### Fixed

- **Results band: unsubstantiated figures replaced, section given context.** The band carried three numbers — "3.2× average ROAS", "+140% organic traffic, year one", "98% client retention" — with no eyebrow, heading, basis or link. It was the only section on the homepage without a head, which is why it read as orphaned, and none of the three survived a check against the thirteen case studies the theme ships:

  | Claim | What the case studies show |
  |---|---|
  | 3.2× average ROAS | Exactly one engagement reports ROAS, "above 400%", and that case's own honest-read section states platform-reported ROAS flatters. One figure is not an average, and 3.2× appears nowhere. |
  | +140% organic, year one | The only genuine year-on-year organic figure in the body of work is **+43.5%**. The larger percentages (+198%, +94%) are month-two and month-four movements, and their own cases flag them as large percentages on small absolute numbers — "17 visitors to 33". The claim is contradicted, not merely unsupported. |
  | 98% client retention | Retention is not mentioned in any case study. Not derivable from anything in the theme. |

  Unsourced aggregates sitting directly beneath case studies that state their own measurement limits was the single inconsistency most damaging to the rest of the page — the site's whole argument is honest measurement.

  **Replaced with one documented figure per service block**, each from a single named engagement and each linking to the case study where its limits are set out:

  | Block | Figure | Case study |
  |---|---|---|
  | Demand creation | 745k addressable audience, from 53k | `cookieless-audience-sports` |
  | Demand capture | +43.5% organic search, year on year | `seo-fmcg-malaysia-singapore` |
  | Conversion & data | +25–35% conversion rate, four APAC markets | `paid-media-financial-services` |

  **That mapping is the context the section was missing.** One figure per block, in the same order as "What we do", so the band reads as that section proved rather than three numbers with no parent. Each card names its block, and the section lead states plainly that these are single engagements rather than averages across clients.

  None of the three duplicates an engagement already shown in the proof rows or featured spread above, so the band adds evidence rather than repeating it.

  **Structural changes.** New `inc/stats-band.php` renders the band through a `__REMOTIVE_STATS__` token. Four Theme Options keys per stat (`block`, `value`, `label`, `case`) replace the previous two, all sanitised. Case studies are resolved with `get_page_by_path( 'case-studies/{slug}' )` rather than a built URL — a hardcoded path assumes a permalink structure, which is what had seeded content 404ing before 1.66.6. A stat whose case study is missing or unpublished renders without a link rather than emitting one that 404s, and the hover state is scoped to linked cards only.

## [1.71.1] — 2026-08-31

### Changed

- **Reply-time promise changed from one business day to three.** Twelve instances across nine files, so the commitment is consistent wherever a visitor meets it — there is no worse version of this than promising one day in one place and three in another.

  | File | Where it appears |
  |---|---|
  | `templates/page-contact.html` | Contact page hero |
  | `templates/page-about.html` | About page form lead |
  | `templates/page-faq.html` | FAQ closing section |
  | `templates/page-thank-you.html` | Step 2 heading |
  | `parts/sidebar.html` | Blog sidebar note |
  | `inc/theme-options.php` | `hero_reassure` default |
  | `inc/site-setup.php` | Contact and Thank You meta descriptions (×2) |
  | `inc/thank-you.php` | All three source-varied confirmation lines |
  | `assets/js/lead-form-status.js` | Inline success message |

  Note that `hero_reassure` is a Theme Options field: the default is updated here, but a site that has saved a value keeps it. Check Appearance → Theme Options → Homepage hero if the homepage still shows the old wording.

  Translation template regenerated — three of the changed strings are translatable.

## [1.71.0] — 2026-08-31

### Added

- **Thank-you page.** All three native forms now redirect to a real `/thank-you/` page on success instead of returning to the form with a query parameter.

  **Why this was worth doing.** The previous confirmation was `?remotive_cta=success` on the page the visitor was already on, read by `assets/js/lead-form-status.js` and then stripped with `history.replaceState()`. That is not a conversion destination: GA4, Google Ads and Meta all key on a URL, and whether a tag saw the parameter at all was a race with script order. For an agency whose own pitch is honest measurement, its highest-value action was the one thing on the site that could not be reliably measured. It was also thin as an experience — a button promising a free audit returned one line of text.

  **The page.** A confirmation heading, three numbered steps answering "what happens now" (who reads it, when the reply comes, what the audit involves), a note that nothing was added to a mailing list, and three recent articles so the page continues rather than dead-ends.

  **Source-aware copy.** The form key travels as `?from=`, so one page serves all three forms and varies its opening line. The homepage CTA collects only an email address, so promising a reply to "what you sent" would be wrong there; it says what will actually arrive instead.

  **Conversion signal, no vendor lock-in.** A `remotive_lead` event is pushed to `dataLayer` with the originating form, plus a matching `remotive:lead` `CustomEvent` for anything listening without a tag manager. `dataLayer` is created if absent — the documented GTM pattern, costing nothing when no container is installed. No third-party script is loaded and no identifier is sent. Fires only when a known `from` value is present, so a direct visit or refresh does not report a conversion.

  **`noindex, follow`.** The page is thin by design and only meaningful right after a submission; indexed, it could be landed on cold where "thank you" makes no sense. `noindex` via `wp_robots` rather than a robots.txt block, so it stays crawlable for ad platforms verifying the conversion URL resolves. `follow` is kept so its outbound links still pass value.

  **Fails safe.** If the page is missing, unpublished or renamed, the handler falls back to the previous inline confirmation. A deleted page must never swallow a lead that has already been stored and emailed.

  Provisioned by migration `1.65.9`, so existing sites get the page on their next admin load rather than needing a manual step.

## [1.70.6] — 2026-08-31

### Changed

- **UEN no longer shown by default.** The `legal_uen` option added in 1.70.5 defaulted to the ACRA number, so it displayed unless cleared. It now defaults to empty and displays nothing. The field remains under Theme Options → Contact for anyone who wants it; entering a value adds it in brackets after the company name. The footer reads `© 2026 Remotive Media Asia. All rights reserved.` followed by the legal links.

  The conditional that appends the UEN also owns the space before it, so an empty field leaves no stray whitespace before the full stop.

## [1.70.5] — 2026-08-31

### Fixed

- **Footer copyright line was inadequate on three counts.** It read `© 2026 Re:Motive Media. All rights reserved.` as literal text in `parts/footer.html`.

  - **The year was hardcoded.** It would have gone stale on 1 January and stayed wrong until somebody noticed. Now generated with `date_i18n( 'Y' )` — the locale-aware variant, since not every locale renders the year the same way.
  - **The registered name and UEN were absent.** Singapore's Companies Act section 144 requires a company's registered name and registration number on its business communications and publications, and a website footer is conventionally where that is satisfied. The line carried the brand mark instead. Two new Theme Options fields under Contact: **Registered company name** (default `Remotive Media Asia`) and **Company registration number (UEN)** (default `202404376G`). Clearing the UEN omits it entirely, since the requirement does not apply to every entity that might run this theme.
  - **Privacy and Terms were not in the bottom bar.** They appear in the footer's Company column, but the bottom bar is where visitors and regulators look, and under the PDPA a privacy policy has to be readily accessible. Now rendered there from the real pages via `get_page_by_path()`, so a renamed, unpublished or missing page drops its link rather than producing a 404.

  Renders as `© 2026 Remotive Media Asia (UEN 202404376G). All rights reserved.` followed by the two legal links, which wrap beneath the copyright on narrow screens rather than colliding with the social row.

  The legal name is deliberately distinct from the brand: `Re:Motive Media` remains correct everywhere else on the site, and the copyright line is the one place the registered entity belongs.

## [1.70.4] — 2026-08-31

### Changed

- **The whole featured spread is now the click target.** 1.70.3 gave the spread a link, but only the small "Read the full case →" text at the bottom was clickable — the gradient panel, the +168% figure and the "Premium skincare" label were all inert on a card whose entire purpose is to open one case study. The Case Studies page already treats each card as a single `<a>`; the homepage spread now behaves the same way.

  **Implemented as a stretched link rather than an anchor wrapper.** The spread is a `wp:columns` block, so wrapping it in an `<a>` would break block nesting and the editor would fight it on every save. Instead the existing link's pseudo-element is absolutely positioned across the whole card. The document still contains exactly one anchor with one accessible name, so nothing changes for screen readers or for link-signal purposes.

  **Pseudo-element collision avoided.** `.rm-spread__link` also carries `.rm-section-link`, whose `::after` is the animated underline added in 1.69.5. The overlay therefore uses `::before` — reusing `::after` would have silently replaced the underline with an invisible overlay and stripped the link's affordance while appearing to work.

  **Feedback comes from the image side too.** The gradient scales gently on hover so the card responds wherever the cursor lands, with the label layered above the scaling element. `:focus-within` gives keyboard users the same card state a mouse user sees, and the scale is disabled under `prefers-reduced-motion`.

  **Trade-off, stated rather than glossed:** a stretched link makes the body paragraph unselectable, since the overlay sits above it. Accepted here because the card exists to be opened and the same paragraph appears on the case study page itself, where it selects normally. This is also why the pattern is not extended to the three proof rows below — they carry no prose worth selecting and are already fully served by their own links.

## [1.70.3] — 2026-08-31

### Fixed

- **Homepage proof rows pointed at the index, not the case.** All three "View case study" links went to `/case-studies/` rather than the case whose figure they quote. The link text promises a specific case and delivered a list the visitor then had to search — with thirteen case studies published, that is real friction on the section that exists to prove the claims. Each now points at its own page, matched on the metric it cites:

  | Row | Destination |
  |---|---|
  | 85% — Global automotive marque | `programmatic-advertising-automotive` |
  | +150% — B2B corporate gifting | `paid-search-b2b-gifting` |
  | +86% — Industrial automation B2B | `technical-seo-industrial-automation` |

- **The featured spread had no link at all.** The +168% skincare launch is the largest and most prominent case study on the homepage, and it was a dead end — the three smaller rows beneath it were clickable while the headline piece was not. Added a "Read the full case →" link to `marketplace-launch-skincare`, using the shared action-link treatment.

  All four slugs verified against the seeded case-study pages, so none can 404. Swept the remaining templates for other generic `/case-studies/` links presented as specific ones; none remain.

## [1.70.2] — 2026-08-31

### Fixed

- **Ten invisible links in the "What we do" section.** Every capability name — "Performance Media", "Paid Search", "Analytics Infrastructure" and seven others — was an `<a>`, but `.rm-block__list strong` set them to ink and no rule ever styled the link. Ten links on the page's main capability section were indistinguishable from the plain bold text beside them. Same class of fault as the unstyled `.rm-cs-card__link` fixed in 1.69.5. Linked names now carry the block's accent colour, an underline that draws on hover, and a small arrow so the link is identifiable before hover rather than relying on colour alone (WCAG 1.4.1). The underline is a background gradient rather than a pseudo-element because these sit inside flowing text where an absolutely positioned rule would not follow a wrapped line.

- **Top rule and heading were different hues.** `.rm-block--create` drew its rule in `--magenta` (`#ff449f`, pink) while its heading used `--magenta-dark` (`#cd360b`, orange-red) — not a tint pair. Same mismatch on the capture block (`#00a2ff` against `#00308f`). Only the data block matched, because it happened to use the same token for both. Introduced a per-block `--rm-block-accent` custom property; the rule, icon and outcome line all read from it. The heading's value wins because it is the one constrained by contrast, and the reasoning for those `-dark` choices is already documented above them.

- **Ten links across six destinations.** Paid Media appeared three times, SEO and Email twice each — the same first-link-priority dilution corrected in the feature grids in 1.70.1. Reduced to six links, one per service page, keeping the strongest anchor for each: "SEO & GEO / AIO / AEO" over "Category Strategy", "Lifecycle & Engagement" over "CRM Integration". The four repeats stay as plain text, so nothing looks clickable that is not.

- **Keyword-stuffed description rewritten.** "Own performance marketing, digital marketing and SEO across Singapore and Malaysia" read as a keyword list rather than a description, and its length was what made the middle card ragged. Now "Owning the categories buyers search in Singapore and Malaysia."

### Changed

- **Block icons.** One per block, in the same inline-SVG style as the feature grids so the three card systems on the homepage read as one language: an outward burst for Demand Creation (demand made, not harvested), a magnet for Demand Capture (intent that already exists), a rising series with a check for Conversion & Data. Tinted from the block accent.

- **Outcome line promoted** from a small bold footnote to a Space Grotesk uppercase label in the block's accent colour.

- **Cards lift on hover.** The card itself is deliberately *not* a link — each holds several distinct destinations, so a card-level target would compete with the links inside it. The lift is affordance for those links, not a target of its own. This is the reason the feature grids can be fully clickable and these cannot.

## [1.70.1] — 2026-08-31

### Changed

- **Feature grid links reduced from 16 to 7.** The 1.70.0 grids linked every point, which produced 16 links across only 8 destinations — `/services/analytics/` alone appeared four times.

  **Why that was worse than untidy.** Search engines generally apply first-link priority: where a page links the same URL more than once, only the first anchor text is credited. Three of those four Analytics links therefore passed no additional signal while still diluting the page's link profile. For a reader, clicking two different cards and landing on the same page twice reads as padding.

  **The split is now by intent.** A problem statement raises a question a link can answer — "Invisible to AI search" wants "so what do you do about it". A reassurance statement does not; nobody reads "Commercial accountability" and needs somewhere to click. So the Problem grid keeps 6 links across 8 points, and the Why grid keeps 1 ("Proof before promises" → Case Studies) across its 8. Every destination is now unique.

  **Two now point at articles rather than service pages**, where the article is the better answer: "Rising acquisition costs" → *SEO vs SEM: Which One First, and When*, and "Agency sprawl" → *How to Choose an SEO Agency in Singapore or Malaysia*.

  Posts are referenced by slug and resolved through `get_permalink()` at render time, not by a hardcoded `/blog/{slug}/` path — that assumption is what had seeded content 404ing before v1.66.6, and it breaks under any other permalink structure or a subdirectory install. A slug that resolves to nothing drops the link rather than emitting one that 404s.

  **Unlinked cards no longer look clickable.** They render as `<div>` rather than `<a>`, with no cue, no hover lift, no shadow, a default cursor, and the accent rule fixed at its resting width. Hover and focus affordances are scoped to `.rm-feature--linked`.

## [1.70.0] — 2026-08-31

### Changed

- **Homepage feature grids rebuilt.** "The problem we solve" and "Why Re:Motive" were sixteen bare `<div>`s of heading-plus-paragraph inside a `wp:html` block — no icons, no links, no colour, and type set smaller than the case-study copy beside them. Every point was also a dead end: a visitor reading "Invisible to AI search" and wanting to know what is done about it had nowhere to go.

  - **Each point now links to the page that answers it**, not to a generic index. "Lists left idle" goes to Email, "Rising acquisition costs" to Paid Media, "Weak attribution" to Analytics, "In-house teams stretched thin" to the team page. The whole card is the target rather than a small link inside it, so the hit area matches what the hover state implies.
  - **Sixteen inline SVG icons**, simple geometric strokes on a 24-unit grid rather than a generic icon set. Inline rather than a font or sprite: no extra request, and `currentColor` means one path serves every accent colour instead of needing a copy per colour. `aria-hidden` and `focusable="false"` — the icon repeats the heading beside it, and without the latter some assistive tech adds sixteen empty tab stops to the homepage.
  - **Type up.** Headings 1.05rem → 1.15rem, body 0.9rem → 0.98rem at line-height 1.6.
  - **Accent colour rotates** through the four plate colours, driven by a `--rm-feature-i` custom property set inline by PHP rather than sixteen hand-written `nth-child` rules. Colour lands on the icon and the top rule only; headings stay ink, so legibility never depends on the accent.
  - **Idle pulse.** A slow low-amplitude breath on each icon, staggered off the same index so the grid never pulses in unison — that reads as a loading spinner. 7s cycle with most of it at rest. Transform and opacity only, so it stays on the compositor. Pauses on hover, because competing motion under the cursor is noise. Gated on the `rm-motion` body class and disabled entirely under `prefers-reduced-motion`.

  **Architecture.** Moved out of the template into `inc/feature-grids.php` and rendered through `__REMOTIVE_PROBLEMS__` / `__REMOTIVE_WHYS__` tokens, the same pattern the ticker and social icons use — sixteen inline SVGs pasted into a block template cannot be maintained, and each point's destination is a maintenance concern rather than editorial content. This required making the token map filterable: new `remotive_theme_option_tokens` filter in `remotive_replace_theme_option_tokens()`. Values added through it are spliced into rendered output as-is, so a filter that adds one owns its escaping — documented at the filter.

  The old `.rm-problem` / `.rm-why` selectors in the scroll-motion CSS and the JS reveal fallback are updated to `.rm-feature`.

## [1.69.5] — 2026-08-31

### Changed

- **Action links given a distinct treatment.** "See how we fix this", "All services", "More about us", "Meet the team" and "View case study" read as plain bold body text — nothing marked them as clickable until the cursor was already on them.

  Three inconsistent treatments existed. `.rm-section-link` changed colour and tracking on hover; `.rm-row__link` only changed colour when its parent row was hovered; `.rm-cs-card__link` had **no styling at all** beyond a touch-target padding rule, which is why "View case study" rendered as ordinary text. All three are unified.

  - **Space Grotesk instead of Saira.** The face is already self-hosted for the footer column headings, so this adds no request. Its wider apertures and squarer terminals separate an action from the surrounding Saira body copy at a glance, which is the entire job.
  - **`magenta-dark` (`#cd360b`) at rest**, not only on hover. Measured 4.63:1 on the cream page background and 5.09:1 on white cards — clears WCAG AA on both surfaces these links appear against.
  - **A real underline**, drawn as a pseudo-element rather than `text-decoration` so it can animate and so the arrow stays clear of it. Sits at 40% width at rest, fills to 100% on hover via `transform: scaleX()`, which keeps it on the compositor.
  - **The arrow moves independently.** It was previously part of the text node and could not be animated separately. New `remotive_split_link_arrow()` filter on `render_block` wraps a trailing → in `<span class="rm-link-arrow" aria-hidden="true">`, so it slides right on hover. Marked `aria-hidden` because the link text already states the destination and a screen reader announcing "right arrow" after each one is noise; the accessible name is unchanged. The filter is idempotent, skips blocks without one of the three classes after a single `strpos`, and leaves arrows in unrelated content alone.

  Colour is not the only signal — weight, typeface and the underline all carry it (WCAG 1.4.1). `:focus-visible` gets the same treatment as hover, since the focus ring alone did not communicate the affordance. Links inside coloured CTA bands inherit the band's text colour instead of the accent, which would otherwise collide with the background. Transitions are disabled under `prefers-reduced-motion`.

## [1.69.4] — 2026-08-31

### Fixed

- **Internal production note removed from the Case Studies page.** The closing note read "Card artwork is placeholder gradient pending photography" — an internal to-do visible to every visitor, telling prospective clients the site was unfinished. Same class of leak as the "Placeholder tiles" note removed from the team page in 1.66.6 (RM-018). Removed that sentence only; the two preceding sentences stay, since the anonymisation disclosure and the pointer to each case's measurement limits are proper visitor-facing content on a case studies page. Swept the templates for other internal notes — none remain, the only other `placeholder` matches being legitimate search-input attributes on `404.html` and `search.html`.

## [1.69.3] — 2026-08-31

### Changed

- **Documentation brought current.** `readme.md`, `readme.txt`, `ssot.md`, `upgrading.md` and the translation template had drifted roughly three days behind the code — none of the ticker, CTA, contact form, security, error handling or scroll motion work from v1.66.6 onward appeared in any of them. No code changes in this release.

  - **`readme.md`** — file map gains `inc/security.php` and `inc/error-handler.php`, and records that the latter is required first in `functions.php` so it is registered before any other include can fail. New Security subsections covering the hardening module (with the observation that prompted each measure) and the error handler. Two implementation traps documented explicitly because they cost real time: `is_admin()` returns true on `wp-login.php`, and the `authenticate` filter runs on every page load rather than only on login submissions — returning a `WP_Error` there unconditionally fatals the site. Theme Options section now covers the Country ticker tab and the two Display toggles.

  - **`readme.txt`** — description rewritten to mention the ticker, motion, error handling and hardening. Four new FAQ entries: editing the country list, turning off scroll motion, what happens on a PHP error (including why a printed notice breaks redirects, and the `REMOTIVE_SHOW_ERRORS` escape hatch), and what the hardening does and does not cover. Upgrade notices added for every release from 1.66.8.

  - **`ssot.md`** — configurable-values list updated from the v1.3.0 state to v1.69.2. New "Deviations from this document" section recording the Space Grotesk footer scoping, the CTA colour variants, and the ink-not-white text on coloured bands, each with its reason — so a future maintainer does not revert a deliberate choice. Palette values that mislead are called out: `--magenta-dark` is `#cd360b`, an orange-red, and `--accent-3` / `--accent-3-dark` are yellow and green rather than a tint pair. Security audit record gains the log-driven pass with observed volumes and the server-level actions no theme release can perform.

  - **`upgrading.md`** — shipped items moved off the roadmap with their reasoning retained; outstanding server-level actions from the log review listed in one place.

  - **`languages/remotive.pot`** — regenerated from source: **265 strings, up from 35**, each with real `file:line` references. The previous hand-maintained file was missing roughly seven eighths of the theme's translatable strings, well predating this session. `languages/README.txt` now says to regenerate rather than hand-edit, and how.

## [1.69.2] — 2026-08-31

### Added

- **Non-fatal PHP diagnostics kept off the page.** 1.69.0 handled fatals; this handles everything that does not stop execution — notices, warnings, deprecation messages. A `set_error_handler()` claims them before PHP can print them, so they never reach the page for anyone.

  **Why this is more than cosmetic.** A printed notice is output. Once any output has been sent, no `header()` call can succeed for the remainder of the request, so every subsequent cookie, redirect and cache header fails with "Cannot modify header information". A single early notice from one plugin therefore cascades into a page of warnings and can break redirects and logins outright. Intercepting the notice removes the cascade at its source instead of suppressing the symptoms one at a time.

  **Nothing is lost.** Each diagnostic is written to the PHP error log with a `[Remotive Notice]` / `[Remotive Warning]` / `[Remotive Deprecated]` prefix. Suppression moves the message; it does not discard it.

  **What administrators see.** A dismissible summary in the admin notice area, and a collapsed panel in the footer on the front end, listing type, message, file relative to the WordPress root, line and occurrence count. Visitors see nothing.

  **Deduplication and caps.** Entries are keyed on file, line and message, so a notice inside a loop firing two hundred times is recorded once with a count. Capped at 50 distinct entries per request — beyond that suppression continues but recording stops, so a pathologically noisy plugin cannot exhaust memory through the mechanism meant to contain it.

  **Respects existing tooling.** The previously registered error handler is captured and still called, so Query Monitor and similar debugging plugins — which install their handlers during plugin load, before a theme's `functions.php` runs — continue to collect normally. Guarded against recursion, and a previous handler that throws cannot take the page down.

  **Respects `@` and `error_reporting()`.** Suppressed expressions and anything outside the current error-reporting mask are handed straight back to PHP untouched.

  **Controls.** New "Graceful error handling" toggle under Theme Options → Display, on by default. The option is read directly from the option row rather than through `remotive_get_theme_option()`, because `inc/error-handler.php` is required before every other include so that it is already active when they load.

  **Limit worth stating plainly.** Errors raised before the theme loads at all — during WordPress core or plugin bootstrap — happen before any theme code exists and are outside the reach of this or any other theme. Those still require `WP_DEBUG_DISPLAY` false and `display_errors` off in `wp-config.php`.

## [1.69.1] — 2026-08-31

### Added

- **Subtle scroll motion across the theme.** Section headings, cards, stats and CTA titles fade and lift into place as they scroll into view; the hero mark drifts and rotates slightly behind the content; the case-study colour panel pans against its own frame. Distances are small (14–22px) and ranges short — the page should feel alive when scrolled rather than the motion being noticed.

  **Two implementations, one behaviour.** Where the browser supports CSS scroll-driven animations (`animation-timeline`, Chromium 115+, Safari 26+) the effects are pure CSS and run entirely on the compositor thread — no scroll listener exists, so the number of animated elements cannot cause scroll jank. Where it does not, an `IntersectionObserver` adds a class once per element and a CSS transition finishes the job; each element is unobserved after revealing, so work is bounded by element count rather than scroll length. Modern browsers exit the JavaScript block immediately and pay nothing for it.

  **No layout impact.** Only `opacity` and `transform` are animated. Both are compositor properties — neither triggers layout or paint, and a transform does not move anything else on the page, so none of this can contribute to CLS. The performance work from earlier releases is unaffected.

  **Cannot hide content.** The `.rm-reveal` class that hides an element before it animates is added by JavaScript, never by a template. If the script fails or JavaScript is disabled, nothing is ever hidden — the page simply renders without motion.

  **Controls.** New "Scroll motion" toggle under Theme Options → Display, on by default. Everything is scoped to an `rm-motion` body class added only when the toggle is on, so switching it off means no rule matches and no observer is created rather than the effects being overridden. Visitors with `prefers-reduced-motion: reduce` set at OS level get no motion in either implementation regardless of the setting, and the JavaScript path checks the media query before doing any work at all. The class is not added in the admin, so block editor previews are unaffected.

## [1.69.0] — 2026-08-31

### Added

- **`inc/error-handler.php` — branded error page with administrator-only detail.** Replaces WordPress's generic "There has been a critical error on this website" with a branded page that shows the actual fault to people entitled to see it.

  **Who sees what.** Administrators (`manage_options`) get the exception class, message, file path relative to the WordPress root, line number, and full stack trace where one is available. Everyone else gets a branded apology with no technical content. This split is deliberate: a stack trace names absolute server paths, function names and plugin files, and showing that publicly would undo the version fingerprinting removal added in 1.68.1.

  **Reference IDs.** Every error gets a short stable hash of its file, line and message. It appears on both the public and admin views and is written to the PHP error log alongside the full detail, so a visitor can quote it in an email and you can find the exact log entry without them ever seeing the internals. The same fault always produces the same ID, so repeat reports are recognisably one problem.

  **Two handlers, different coverage.** `set_exception_handler()` catches uncaught exceptions and yields a real stack trace. `register_shutdown_function()` catches fatals that are not exceptions — `E_ERROR`, `E_PARSE`, undefined function calls, memory exhaustion — where only file, line and message exist. Warnings and notices are deliberately not intercepted: they do not halt execution, and replacing a working page with an error screen over a notice would be worse than the notice.

  **Safe inside a broken site.** The handler is required first in `functions.php`, before any other include, so it is already registered if a later `require` fatals. Every WordPress function it calls is guarded with `function_exists()` and falls back to a PHP equivalent — `esc_html()` → `htmlspecialchars()`, `esc_url()` → `filter_var()`, `status_header()` → raw `header()`. A fatal occurring before `wp-includes/formatting.php` loads therefore still renders the page rather than producing a blank screen. Page CSS is inlined and brand colours hardcoded, because linking the theme stylesheet would risk a second fatal if the theme is what failed. Output buffers are flushed before rendering so the error page never appends to a half-built document.

  **Contexts left alone.** AJAX, REST, cron, XML-RPC and WP-CLI requests pass through untouched — a JSON client receiving an HTML error page gets a parse error instead of a diagnosable failure.

  **Trade-off.** Taking over from `WP_Fatal_Error_Handler` also disables WordPress recovery mode emails. Hand control back with `add_filter( 'remotive_use_custom_error_page', '__return_false' );`.

  **Locked-out access.** If the error prevents you logging in, define `REMOTIVE_SHOW_ERRORS` as `true` in `wp-config.php` to see full detail without an admin session. Remove it once fixed.

## [1.68.5] — 2026-08-31

### Fixed

- **Fatal error on admin load: `Call to undefined function remotive_seed_data()`.** The real cause of the critical error, introduced in 1.66.6 and misdiagnosed across 1.68.2–1.68.4 as a security-module problem. `remotive_resolve_seed_post_links()` (added for RM-012/013) called `remotive_seed_data()`, which does not exist — the seed provider is named `remotive_seed_content()`, used correctly in the three other places that call it. The fatal fired via `admin_init` → `remotive_auto_setup_on_admin()` → `remotive_maybe_auto_setup()` → `remotive_seed_run()` → `remotive_resolve_seed_post_links()`, so it hit on every admin page load including the dashboard immediately after login. Corrected to `remotive_seed_content()`.

- **`inc/security.php` re-enabled.** It was never the cause. The module was disabled in 1.68.4 as a diagnostic step; with the actual fatal fixed it is active again, including the guards added in 1.68.2 and 1.68.3 (which remain correct hardening regardless).

### Notes

- The three prior version bumps chasing this (1.68.2, 1.68.3, 1.68.4) changed `security.php` and then disabled it, none of which addressed the real fault. Those hardening guards are kept because they are correct on their own merits, but they were not what was breaking login.
- Once the site loads normally, remove the duplicate `define( 'WP_DEBUG', ... )` from `wp-config.php` line 94 — it was emitting a PHP warning on every request.

## [1.68.4] — 2026-08-31

### Changed

- **`inc/security.php` disabled pending diagnosis.** Two attempted fixes (1.68.2, 1.68.3) did not resolve the critical error on login. Rather than continue guessing at the cause, the module is commented out in `functions.php` so the site is usable. The file remains in the theme unchanged; re-enabling is a one-line edit once the actual fatal is identified from the PHP error log. All other 1.68.x changes (the `/work/` → `/case-studies/` slug migration, CTA layout, pagination) are unaffected and remain active.

## [1.68.3] — 2026-08-31

### Fixed

- **Critical error on login — second fix.** v1.68.2 fixed the `authenticate` filter but two other hooks in `security.php` were also unsafe during the login flow:

  - `remotive_block_install_endpoints()` on `admin_init` was matching against `SCRIPT_NAME` and calling `wp_safe_redirect()` + `exit`. `is_admin()` returns true on `wp-login.php`, so the function was executing on every login page load. The `SCRIPT_NAME` check was intended to match `install.php` and `setup-config.php` only, but string matching on `basename()` is fragile. Switched to matching `SCRIPT_FILENAME` (the real filesystem path) against exact basenames `'install.php'` and `'setup-config.php'` — `wp-login.php` is a different basename and is never matched.

  - `remotive_block_user_enumeration_rest()` was calling `is_user_logged_in()` without first checking whether the result parameter was already set by a prior filter. This can cause issues during early REST bootstrap. Added a `null !== $result` early-return to respect previous filter results.

  - The whole `security.php` was also temporarily commented out in `functions.php` during diagnosis in v1.68.2 — it is re-enabled in this release with all three hooks properly guarded.

## [1.68.2] — 2026-08-31

### Fixed

- **Critical error on login caused by `remotive_block_locked_login`.** The `authenticate` filter runs on every page load where WordPress validates a logged-in cookie (via `determine_current_user`), not only on actual login form submissions. The previous implementation checked the failure counter and potentially returned `WP_Error` during cookie validation on normal page requests — any user who had previously triggered the rate limit from their IP would get a fatal error on every page, not just the login page. Fixed by adding two guards: the function now returns early if either `$username` or `$password` is empty (cookie auth passes empty strings for both), and returns early if the request method is not POST. This limits the rate-limit check strictly to explicit credential submissions.

## [1.68.1] — 2026-08-31

### Added

- **`inc/security.php` — dedicated security hardening module.** Six measures drawn directly from patterns in the site's first-week server logs. Each is a named function with its own hook so any of them can be removed individually without editing the file.

  **1. Login rate limiting** (`wp_login_failed` / `authenticate` / `wp_login`). After 5 failed login attempts from the same IP within 10 minutes, further attempts from that IP return a generic error without reaching the database. Resets on successful login. Mirrors the contact-form rate-limit pattern already in `inc/lead-form-handler.php`. The log showed 271 wp-login.php hits from a single IP (`103.138.189.98`) within the observation window.

  **2. XML-RPC disabled** (`xmlrpc_enabled` filter → `__return_false`). Pingback methods removed from the methods list. `X-Pingback` header removed from all responses. The log showed 7 xmlrpc.php probe attempts from 5 distinct IPs in the first week. This theme uses no XML-RPC functionality.

  **3. Install/setup endpoints redirected** (`admin_init`). Once WordPress is installed, `/wp-admin/install.php` and `/wp-admin/setup-config.php` serve no legitimate purpose and were producing 91 × 500 errors and 200 responses to external IPs respectively. Both now redirect to home with a 301.

  **4. Version fingerprinting removed** (`wp_head` generator removed; `the_generator` filter; `style_loader_src` / `script_loader_src` filters strip `?ver=` query strings from non-timestamp version parameters). Raises the cost of targeted CVE scanning. Timestamp-based version strings (filemtime cache-busting) are preserved; only WordPress-version strings are stripped.

  **5. REST API user enumeration blocked** (`rest_authentication_errors`). Unauthenticated requests to `/wp-json/wp/v2/users` return 401. Author archive pages and `?author=N` URLs redirect to home to close the same gap via the URL layer. The rest of the REST API remains open — the WebMCP integration (`inc/webmcp.php`) needs it.

  **6. Security headers** (`wp_headers` filter). Added to all front-end responses: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` disabling camera/microphone/geolocation/payment. Content-Security-Policy intentionally omitted — requires site-specific tuning and will break the block editor if set incorrectly.

  **What the theme cannot do** (requires server/firewall/plugin): block specific IPs, rate-limit at TCP level, protect `wp-login.php` at the server layer. `103.138.189.98` (active brute-force) should be blocked at the firewall or `.htaccess` level directly.

## [1.68.0] — 2026-08-31

### Fixed

- **Case Studies page slug migration: `/work/` → `/case-studies/`.** The required-pages registry has used the `case-studies` slug throughout, but the theme's setup routine only creates pages, never renames them. Any site activated before the slug was standardised had the page in the database at `/work/` — all internal links and footer menus pointing at `/case-studies/` were quietly 404ing. Added `remotive_migrate_work_slug()` as a versioned migration (schema `1.65.8`) that runs once on the next admin page load: if a page exists at `/work/` and no page exists at `/case-studies/`, it renames the slug via `wp_update_post()` and flushes rewrite rules. Child pages (individual case study entries parented to the renamed page) update automatically since their parent ID is unchanged. Does nothing on fresh installs or sites already at the correct slug.

## [1.67.9] — 2026-08-31

### Fixed

- **Empty pagination block leaving a visible gap below the post grid.** `wp:query-pagination` renders its container element even when all posts fit on a single page — previous/next/numbers slots are empty but the block itself still occupies height and shows two faint divider lines. Added `display:none` on `.rm-pagination:not(:has(a))` and `.wp-block-query-pagination:not(:has(a))` so the block collapses when it contains no navigable links. `:has()` progressive enhancement — older browsers see the empty block.

## [1.67.8] — 2026-08-31

### Fixed

- **Centred CTA layout not applying; outline button invisible on cyan band.** The `.rm-cta--centred` rules used single-class selectors (`.rm-cta--centred`) which have the same specificity as `.rm-cta`. The base grid rules on `.rm-cta` were winning the cascade over the centred overrides, leaving all button-only bands still using the two-column form-grid layout. Rewrote all centred rules with doubled selectors (`.rm-cta.rm-cta--centred`) which outweigh the single-class base rules. Also fixed the outline button on the cyan band: `color:inherit` resolved to the ink token but the inherited text colour from the band was ink-dark, which is near-invisible on cyan; changed to explicit `#fff` on the navy band and `rgba(30,30,30,.55)` on coloured bands where ink text is appropriate.

## [1.67.7] — 2026-08-31

### Fixed

- **Section head links floating at inconsistent vertical positions.** `.rm-section-head` used `align-items:flex-end` — the link aligned to the bottom of the flex row, which meant it landed at the baseline of whatever text was in the left column. A two-line description pushed it down further than a one-line description. Every section looked different because each had a different amount of text. Changed to `align-items:flex-start` so the link consistently aligns with the top of the section (level with the eyebrow), predictable across every section regardless of how long the description runs. Also removed the `border-bottom:2px solid magenta` underline styling on the link (replaced with a colour-change and letter-spacing nudge on hover instead), which read as a decorative element competing with the content rather than a navigation signal.

## [1.67.6] — 2026-08-31

### Changed

- **CTA band button-only layout redesigned.** The base `.rm-cta` grid was built around the homepage email-capture form: headline hard-left in one column, form stacked in a fixed right column. Button-only bands (service pages, case studies, market pages, blog index, single post, archive) were using the same two-column grid with no form filling the right side — leaving a hard-left headline and an isolated button marooned in the corner. Added `.rm-cta--centred` modifier to all button-only bands: single centred column, headline at full width with a larger size cap (`clamp(2.4rem,5.5vw,4.4rem)`, up from `3.4rem` max), reassure line and buttons centred below it. Fill button on the standard navy band now uses magenta (matching the primary action colour site-wide) rather than the ivory paper colour that blended into the band. Outline button gets a white semi-transparent border. Both buttons styled consistently at `.9rem`, `700` weight, uppercase, `0.06em` tracking. Colour variants (magenta, cyan bands) override the fill button to ink-on-band-colour as before. Homepage band is unchanged — it uses the two-column grid with the email form and gets no `rm-cta--centred` class.

## [1.67.5] — 2026-08-31

### Fixed

- **Service page hero subtitle too wide.** On pages using `wp:post-excerpt` (the six service pages), WordPress wraps the excerpt in `<div class="wp-block-post-excerpt"><p>…</p></div>`. The `rm-hero__sub` class landed on the outer div, but the `max-width:44ch` rule only targeted that element directly — the inner `<p>` was unconstrained and stretched to the full container width, making the subtitle run much wider than on static-content pages where the class sits directly on a `<p>`. Added `.rm-hero__sub p` to both the base rule (`44ch`) and the homepage wide-layout override (`56ch`) so the constraint applies in both cases.

## [1.67.4] — 2026-08-31

### Fixed

- **CTA band colour variants failed contrast.** The 1.67.3 CSS was written against an assumed token value (`#00aeef` for cyan, `#ec008c` for magenta) rather than the actual resolved values in this theme's palette (`#00a2ff` cyan, `#ff449f` magenta). The actual values are significantly lighter, making white text on both colours fail WCAG 4.5:1 (white on `#00a2ff` = 2.76:1; white on `#ff449f` = 3.19:1). The fill button used `var(--paper)` (ivory `#f7f4ec`) as background on cyan — `paper on cyan` = 2.51:1, which is exactly what made the button label invisible. Fixed by measuring all combinations against the real hex values before writing any CSS:
  - **Cyan band (`#00a2ff`)**: switched to ink text (`#1e1e1e`) at 6.04:1 ✓, ink fill button with white label (16.67:1 ✓), ink outline button border. Dark mode uses `cyan-dark` (`#00308f`) with white text at 11.54:1 ✓.
  - **Magenta band (`#ff449f`)**: switched to ink text at 5.22:1 ✓, same ink fill button pattern. Dark mode uses `magenta-dark` (`#cd360b`, orange-red) with white text at 5.09:1 ✓.
  - **Reassure line on coloured bands**: was `rgba(30,30,30,.65)` which blends to ~`#757570` and lands at ~1.45–1.68:1 on the band colours — invisible. Changed to `var(--wp--preset--color--ink)` directly (full opacity ink) for full 6.04:1 / 5.22:1 contrast.

## [1.67.3] — 2026-08-31

### Changed

- **CTA band colour variants.** Added `rm-cta--magenta` and `rm-cta--cyan` modifier classes, each with full light/dark-mode handling and adjusted fill/outline button colours so nothing disappears against its own band surface.

  | Page | Variant | Reasoning |
  |---|---|---|
  | Services index | Magenta | "Not sure where to start?" — invitation tone, magenta matches the energy |
  | SEO Singapore | Magenta | Market landing page, needs urgency |
  | Digital Marketing Malaysia | Magenta | Parallel to Singapore, consistent treatment |
  | Case Studies index | Cyan | "Want to be the next one?" — aspirational, distinct from services |
  | Insights index | Cyan | Cooler tone suits a content audience |
  | Archive pages | Cyan | Identical context to Insights index |
  | Homepage email-capture band | Navy (unchanged) | Form/button contrast is carefully tuned, changing risks breaking it |
  | Each service page | Navy (unchanged) | Six pages should feel consistent with each other |
  | Single post | Navy (unchanged) | Natural continuation after reading, not a gear change |
  | FAQ | Navy (unchanged) | Cautious-researcher audience, no need to shout |

  Contrast ratios verified before shipping: magenta band — white text 4.61:1 ✓, navy fill button 5.29:1 ✓; cyan band — navy text 4.87:1 ✓, navy fill button 4.87:1 ✓. Dark mode: both variants stay on their own colour (magenta-dark / cyan-dark) rather than inverting, since both read equally well against the dark page surface and inverting a coloured band would undermine the variance.

## [1.67.2] — 2026-08-31

### Fixed

- **CF7 submissions not Akismet-checked at capture time.** The `remotive_capture_cf7_submission()` function called `remotive_store_lead()` without passing `is_spam`, so every Contact Form 7 submission was stored with `post_status = 'publish'` regardless of spam verdict — they would never appear in the spam folder and the manual spam/ham actions on the leads card had no cause to act on them. Added `remotive_akismet_is_spam()` call in the CF7 capture path before `remotive_store_lead()`, passing the extracted name, email and message. CF7 runs its own Akismet check before firing `wpcf7_mail_sent` (its built-in integration), so a submission reaching this hook has already passed CF7's filter; our check runs independently so the `is_spam` flag is set correctly in the stored record.

### Changed

- **Akismet check payload enriched with IP, user agent and HTTP referrer.** The initial `comment_check` call was sending name, email, message and permalink, but not the submitting IP address, browser string, or HTTP referrer. These are standard Akismet fields (`user_ip`, `user_agent`, `referrer`) that the service uses heavily for reputation scoring. Adding them improves detection accuracy across all three native forms, and matters most for the homepage CTA form which only collects an email address — with no name or message to score against, IP and user agent carry proportionally more weight.

## [1.67.1] — 2026-08-31

### Changed

- **About page form brought up to the same visual standard as the Contact page form.** The backend tracking was already fully independent — separate `action` value (`remotive_about_submit`), separate nonce (`remotive_about_nonce`), separate honeypot field name (`remotive_about_website`), separate rate-limit bucket (`remotive_rl_about_*`), and separate `source` stored against the lead record (`about form` vs `contact form`). The `data-lead-status="remotive_about"` attribute on the status div means the success/error message only fires when `?remotive_about=success/error` is in the URL — it can't be triggered by a Contact page submission and vice versa. None of that changed. What changed: the form's HTML class updated from `rm-about-form` (no CSS styling whatsoever) to `rm-contact-form` (full styled treatment added in 1.66.9), so both forms now share one CSS definition and render identically — labelled fields, bordered inputs, styled submit button, privacy line inline with the button, honeypot gap fixed. The `action`, nonce token, honeypot field names, and `data-lead-status` are all unchanged, so every tracking and security mechanism remains exactly as it was.

## [1.67.0] — 2026-08-31

### Added

- **CTA band on Insights index (`home.html`).** The blog listing page had no bottom-of-page CTA — visitors who scrolled through all the articles hit the footer with no prompt. Added the standard `rm-cta` band with headline "Read enough. Let's talk.", the standard audit reassurance line, a filled primary button ("Start a project →") and an outline secondary button ("See what we do" → `/services/`). The secondary button gives readers who aren't ready to contact a useful next step rather than a dead end.

- **CTA band on single post (`single.html`).** Individual article pages had no CTA at all — the post ended with the tag list and "All insights →" back-link, then straight to footer. Added the `rm-cta` band with headline "Want this working for your brand?", the standard audit reassurance line, a filled primary ("Start a project →") and an outline secondary ("See our work" → `/case-studies/`). The secondary button is intentionally different from the index — a reader who just finished an article is a more qualified lead, so pointing at case studies is a more contextually appropriate next step than the generic services overview.

- **CTA band on archive pages (`archive.html`).** Category and tag archive pages (e.g. `/category/seo/`) were also missing a CTA. Added the same band as the Insights index — "Read enough. Let's talk." — since the context is identical: a reader browsing a filtered article list.

### Fixed

- **FAQ primary button missing `is-style-fill`.** The "Get in touch →" button in the FAQ's closing section used the default (unstyled) button class, rendering as a plain browser button while every other primary button on the site uses the filled ink-background style. Added `is-style-fill` to match.

## [1.66.9] — 2026-08-31

### Changed

- **Contact page layout and form restyle.** Complete overhaul of `templates/page-contact.html` and the associated CSS, fixing gaps and adding proper field styling:
  - **Hero tightened.** Contact page now uses `.rm-hero--contact` with `padding-block: clamp(2rem,4vw,3.5rem)` instead of the full homepage hero padding, so the form appears above the fold without scrolling on most screens.
  - **Column split widened.** Changed from 60/40 to 65/35 — form gets more room, sidebar sits comfortably without feeling squeezed.
  - **Honeypot gap fixed.** `.rm-cta__honeypot` was occupying visible space on both the Contact and About forms. Added `display:none !important` scoped to both form classes so the div is invisible and takes no space.
  - **Form renamed.** Contact page form class changed from `rm-about-form` (shared with the About page) to `rm-contact-form` for independent CSS control. The handler is unchanged — it keys on the hidden `action` POST field, not the form class. About page form and its handler are untouched.
  - **Labels.** Now styled to the site's eyebrow convention: `0.75rem`, `700` weight, uppercase, `0.07em` letter-spacing, muted ink colour. Required field marked with a magenta asterisk (`aria-hidden`).
  - **Inputs and textarea.** Properly styled: `1.5px` border using the `--rm-line` token, `6px` border-radius, `0.75rem 1rem` padding, smooth `:focus` ring (`box-shadow` using ink at 12% opacity) that avoids the browser default outline. Textarea locked to `resize:vertical` only. Dark mode inverts the background and adjusts the border and focus ring.
  - **Submit button.** Replaces the browser-default button with a styled control matching the site's filled button: ink background, paper text, magenta on hover, uppercase, `0.06em` tracking. Mobile: full-width, 44px min-height touch target.
  - **Form footer.** Button and privacy line sit side by side on wide screens (`flex-wrap`), stack on narrow. Privacy line is now visually subordinate — `0.78rem`, muted colour, no top margin competing with the button.
  - **Sidebar cards.** Now have `1px border`, `8px border-radius`, and `1.25rem 1.4rem` padding. Gap between cards tightened to `1rem`. Headings (`EMAIL`, `OFFICE`, `ELSEWHERE`) styled as small eyebrows (`0.72rem`, uppercase, muted) rather than unstyled h3s.
  - **Empty Elsewhere card hidden.** When no social profiles are configured, `__REMOTIVE_SOCIAL_LINKS__` renders nothing and the card showed a blank white box. Added a `:has()` rule on `.rm-sidebar__card--social` that hides the card when its social container is empty. Progressive enhancement — the card shows on browsers without `:has()` support.

## [1.66.8] — 2026-08-31

### Added

- **Dedicated "Country ticker" tab in Theme Options.** All ticker settings now live in their own tab (icon: `dashicons-arrow-right-alt`) rather than buried in Homepage hero. Twelve options, all properly sanitized and immediately applied via an inline `<style>` block generated by `remotive_ticker_css()` and attached to `remotive-style` via `wp_add_inline_style`:

  | Setting | Type | Range / accepted values |
  |---|---|---|
  | Show ticker | Toggle | On/Off — hides the entire band (`.rm-ticker-band { display:none }`) |
  | Countries | Text | Comma-separated, ≥2 for scroll to loop |
  | Background colour | Colour picker | Hex — default: `#1a1a2e` (Charcoal) |
  | Text colour | Colour picker | Hex — default: `#f7f4ec` (Ivory) |
  | Separator colour | Text | Hex or `rgba()` — default: `rgba(255,255,255,0.18)` |
  | Font size (rem) | Text | 0.5–2.5 — default: `0.85` |
  | Font weight | Select | 400/500/600/700/800/900 — default: `700` |
  | Letter spacing (em) | Text | 0.00–0.50 — default: `0.10` |
  | Vertical padding (rem) | Text | 0.2–4.0 — default: `0.85` |
  | Horizontal padding (rem) | Text | 0.5–8.0 — default: `2.4` |
  | Scroll speed (seconds) | Text | 4–120 — default: `26` |
  | Scroll direction | Select | Left / Right — default: Left |

  The animation duration scales proportionally with the country count so perceived speed stays consistent when items are added or removed (reference: 26s at 5 countries). Direction reverses by swapping the keyframe `translateX` target and the flex direction on `.rm-ticker__track`, leaving the IntersectionObserver pause logic untouched. The `ticker_countries` field and its `remotive_render_ticker()` token (`__REMOTIVE_TICKER__`) are moved from the Homepage hero tab into this new tab. No template changes required; `front-page.html` still uses the same token.

## [1.66.7] — 2026-08-31

### Added

- **Country ticker editable from Theme Options.** The scrolling markets strip under the hero was hardcoded in `templates/front-page.html` (5 countries, duplicated 4× for the seamless loop). It is now a `ticker_countries` option under the Homepage hero tab — a comma-separated plain-text field. Add a country, remove one, rename one, reorder them: save, and the ticker rebuilds immediately on the next request. The four-repetition duplication required by the CSS scroll animation is handled by `remotive_render_ticker()` automatically, so the field just takes a clean comma-separated list with no manual duplication. Minimum two entries required for the loop; fewer than two hides the band rather than rendering a broken half-loop. Shipping default: `Singapore, Malaysia, Thailand, Hong Kong, China` — matches what was hardcoded before.

## [1.66.6] — 2026-08-31

### Security

- **RM-003: Dedicated lead post-type capabilities.** `remotive_lead` previously used `capability_type: 'post'`, meaning Editors could reach `edit.php?post_type=remotive_lead` directly and read, export, or delete every enquiry. Replaced with a full dedicated capability set (`edit_remotive_leads`, `read_remotive_lead`, `delete_remotive_leads`, etc.). Administrators get these granted via `remotive_grant_lead_caps()` on activation and defensively on `admin_init`. Action handlers (export, spam, leads card) now check `edit_remotive_leads` instead of `edit_theme_options`.
- **RM-005: CSV formula injection protection.** New `remotive_csv_safe()` helper prefixes an apostrophe onto any value that begins with `=`, `+`, `-`, or `@` (after leading whitespace). Applied to Name, Email, Message, and Source on export. The stored value in the database is unchanged; the safe prefix is applied only at export time.

### Fixed

- **RM-002: Privacy draft corrected to match actual data processing.** The draft stated "a submission is emailed to us and is not stored in this website's database" — factually wrong. The theme stores name, email address, message, page source, IP address, user agent, mail delivery status, spam status, and submission date. The "What happens to a form submission" section now accurately describes all stored fields, the scheduled retention purge, Akismet processing, and CF7 capture. The "What we collect" section updated to reference this. Privacy notice links added to the homepage CTA and Contact page forms (the About page already had one). A notice on any form now says submissions are stored, not email-only.
- **RM-004: Retention schedule self-healing.** The daily purge was scheduled only on `after_switch_theme`, so a site upgraded while the theme stayed active never got a purge event. Added an `admin_init` defensive re-check using the existing `wp_next_scheduled()` guard (no duplicate events possible).
- **RM-006: Footer navigation — three real registered locations.** The footer's three navigation columns (Services, Case Studies, Company) now each have their own registered menu location (`footer_services`, `footer_cases`, `footer_company`). The old `footer_sitemap` location is retired; `remotive_migrate_footer_sitemap_location()` on `admin_init` copies any existing assignment to `footer_company` once, then removes the orphaned key. `remotive_nav_location_for_class()` rewired to match by explicit class (`rm-footer__links`, `rm-footer__links--cases`, `rm-footer__links--company`), not by render-order counter. Case Studies now gets its own real menu an administrator can edit; previously it was permanently excluded from menu management.
- **RM-007: Theme Options description fields now render.** Five description fields (`problem_sub`, `services_sub`, `why_sub`, `work_sub`, `about_sub`) were incorrectly nested inside their sibling heading field's array definition due to a brace-alignment error introduced when the fields were first added. They appeared in the defaults and in the token map, but never in the admin UI. Restructured as proper top-level siblings of the heading fields. Also fixed the field renderer: `type: 'textarea'` silently fell through to a single-line `<input type="text">` because `remotive_render_text_field()` had no textarea branch. Added the correct `<textarea rows="3">` path with `esc_textarea()`.
- **RM-008: Default colour mode option now actually reaches the front end.** `wp_add_inline_script()` was attached to `'remotive-theme-toggle'`, a handle that does not exist (a leftover from before the front-end scripts were consolidated into `'remotive-front'`). The inline script never printed, so `window.remotiveThemeOptions` was undefined and `remotive.js` always fell back to `'dark'` regardless of the Theme Options setting. Fixed by attaching to the real handle.
- **RM-009: Contact settings genuinely global.** Three hardcoded instances of the email address and postal address remained: the blog sidebar (`parts/sidebar.html`), the Contact page email link, and the Contact page address block (`templates/page-contact.html`). All three replaced with `__REMOTIVE_CONTACT_EMAIL__` and `__REMOTIVE_ADDRESS_LINE_*__` tokens. The sidebar email is now also wrapped in a `mailto:` link, matching the footer. Footer and schema markup were already using tokens.
- **RM-010: Unconfigured social profiles hidden.** All seven social icons were previously rendered unconditionally with `#` as the href when no URL was configured — directly contradicting the Theme Options UI which says "blank hides the icon". Replaced 14 static anchor blocks (7 in `parts/footer.html`, 7 in `templates/page-contact.html`) with a single `__REMOTIVE_SOCIAL_LINKS__` token each. New `remotive_render_social_links()` helper applies the same empty/`#`/invalid-URL skip logic already in `remotive_schema_same_as()`. Schema output was already correct.
- **RM-015: Setup no longer overwrites Reading settings unconditionally.** `remotive_run_site_setup()` previously called `update_option('show_on_front',...)` on every run, including via the manual repair action. A site that had deliberately configured a different static homepage would have it silently overwritten. Now only sets Reading options when `show_on_front` is `'posts'` or `page_on_front` is `0` (fresh-install defaults). An explicitly configured static front page is left alone.
- **RM-016: Undefined `$org_ref` in `remotive_schema_faq_page()`.** A Service block copied from `remotive_schema_services()` into `remotive_schema_faq_page()` referenced `$org_ref` and `$services`, neither of which was in scope. This produced PHP warnings on every page using the FAQ, SEO-Singapore, or Malaysia templates. The orphaned block removed; Service generation remains only in `remotive_schema_services()` where the variables are defined. (Source of the `$org_ref` warnings flooding the production error log.)
- **RM-017: Accept-header logo variation now uses `<picture>` markup.** The header logo previously selected AVIF or PNG server-side from the request's `Accept` header and baked the chosen URL into the HTML, varying the document by Accept. `Vary: Accept` was only added on the front page, leaving every other page (all of which also carry the header logo) unprotected in shared caches. Replaced with a `<picture>` element — `<source type="image/avif">` + `<img src="…png">` fallback — so format selection is handled entirely by the browser. The document is now identical across Accept values. `Vary: Accept` on the front page retained only for the hero mark CSS background (a genuine server-side negotiation that hasn't moved to `<picture>` yet).
- **RM-018: Public "Placeholder tiles" note removed from Team page.** The note was visible to all visitors. Removed. Team members without a portrait pair now show branded initials on the gradient background (computed from `name` in the team options) rather than the literal word "Photo". Neither placeholder text nor fabricated photography is shown.

### Changed

- **RM-012/RM-013: Internal cross-links resolved from real permalinks.** New `remotive_resolve_seed_post_links()` runs after the seed loop. It builds a map of every seeded blog post slug → `get_permalink()` result, then rewrites any `/blog/{slug}/` occurrence in seeded post content to the actual permalink. Works with any WordPress permalink structure and subdirectory installs. Hardcoded paths remain in templates where they can't be token-resolved at runtime; those will be documented as a known limitation until a runtime token approach replaces them.
- **RM-014: Versioned, idempotent migrations.** `remotive_site_setup_done` now stores a migration schema version string (`REMOTIVE_SETUP_SCHEMA = '1.65.7'`) rather than just the theme version. `remotive_maybe_auto_setup()` uses `version_compare()` to decide which entries in `remotive_migration_registry()` still need to run, advances the stored version after each one, and is a no-op on sites already at the current schema. Existing sites whose stored value is a truthy non-version string (e.g. an old theme version) get the full 1.65.7 migration on their next admin load.

## [1.66.5] — 2026-08-30

### Changed

- **Reverted 1.66.4's site-wide typeface swap.** Space Grotesk was
  meant only for the footer column headings, not the whole site.
  Saira is restored as the general typeface across both light and
  dark modes, matching the client's brand-guideline JSON
  (`remotive_brand_5.json`) again:
  - `theme.json`: re-added the Saira font family entry alongside
    Space Grotesk (both now registered; each used where intended).
  - Deleted Saira font files restored (`assets/fonts/saira/*.woff2`,
    5 files) via the same `@fontsource/saira` npm package that
    produced the originals, so the bytes match what shipped before
    1.66.4.
  - `critical.css` and `remotive.css`: `--wp--preset--font-family--archivo`
    and `--newsreader` point back to Saira, with the original,
    unmodified metric-matched CLS fallback (`size-adjust:107.1%`,
    `ascent-override:113.5%`, `descent-override:43.9%`) restored
    rather than recalculated, since these are Saira's real values
    from before 1.66.4 touched them.
  - `functions.php`: font preload hints point back to the four Saira
    weights (400/600/700/900) needed above the fold.
  - `login.css` and the colour-toggle comment in `remotive.js`:
    reverted to Saira.
  - **`.rm-footer__heading` now explicitly requests Space Grotesk**,
    falling back to Saira if Space Grotesk somehow fails to load
    (`font-family:'Space Grotesk','Saira','Saira Fallback',system-ui,sans-serif`),
    rather than inheriting whatever the page-wide variable resolves
    to. This is the only element on the site set to Space Grotesk.
    Given it's four short one-line labels below the fold, no separate
    metric-matched fallback was built for it — the layout-shift risk
    Saira's system exists to prevent is negligible at this scale.
  - `readme.txt`, `readme.md`, `resources.md`, `ssot.md` updated to
    describe Saira as the site typeface again, Space Grotesk as a
    named, scoped footer accent. `ssot.md`'s brand-facts table keeps
    a note that 1.66.4 briefly deviated further than intended, so the
    record isn't silently rewritten as if it never happened.

## [1.66.4] — 2026-08-30

### Changed

- **Footer column heading font-size increased** from `.78rem` to
  `.92rem` (`.rm-footer__heading`), with letter-spacing eased slightly
  (`.12em` to `.1em`) so the wider glyphs at the larger size don't
  read as over-tracked.
- **Brand typeface changed from Saira to Space Grotesk** across both
  light and dark modes, requested for a bolder, more distinctive,
  geometric feel. Space Grotesk (SIL OFL, via `@fontsource/space-grotesk`)
  bundled as self-hosted woff2 at weights 400/500/600/700 in
  `assets/fonts/space-grotesk/`; theme.json's font family entry
  updated to match. The typeface has no true 900 (Black) cut, so
  weight-900 requests are aliased to the same 700 file via an explicit
  `@font-face` src rather than left to the browser's synthetic-bold
  algorithm, which tends to distort geometric sans faces.
  - **Metric-matched CLS fallback recalculated from real font data.**
    This theme sizes a local system-font fallback to match the real
    font's proportions before it loads, so the swap from fallback to
    webfont is a repaint, not a reflow. Reused `fontTools` to extract
    Space Grotesk's actual OS/2 ascent/descent and compute a
    frequency-weighted average character width (English letter
    frequency, measured against Liberation Sans as the Arial-metric
    reference) rather than estimate a `size-adjust` value: 113.3%,
    versus Saira's 107.1%. Verified the method first by reproducing
    Saira's already-known value (106.9% computed vs 107.1% shipped,
    close enough to trust) before trusting it for Space Grotesk.
    Updated in both `critical.css` (first-paint inline block) and
    `remotive.css`.
  - Font preload hints in `functions.php` updated to the three real
    Space Grotesk files needed above the fold (400, 600, 700; 900
    aliases to the 700 file already preloaded, so needs no separate
    entry), replacing the four Saira preloads.
  - Old Saira font files deleted (5 woff2 files) and its theme.json
    entry removed, rather than left bundled and unused.
  - `login.css`'s font-family reference updated too, though the login
    screen was never actually enqueuing the webfont there in the
    first place — it was already falling through to `system-ui`
    regardless of which name was written first in the stack.
- **Documented, not silently overwritten: this deviates from the
  client's own brand-guideline JSON** (`remotive_brand_5.json`), which
  still specifies Saira as the single typeface across all roles.
  `ssot.md`'s brand facts table now notes the deviation explicitly
  rather than just updating the value, so a future reader doesn't
  mistake this for a revision to the client's actual brand guidelines.
  `readme.txt`'s FAQ and feature descriptions updated to describe
  Space Grotesk as current state while keeping the historical record
  of Saira's role intact where it was already discussing history
  (e.g. the 1.41.2 dark/light font-parity fix).

## [1.66.3] — 2026-08-30

### Fixed

- **Footer's Case Studies and Company columns both rendered the
  Services list once any classic menu was assigned to a footer
  location.** `remotive_nav_location_for_class()` told the footer's
  navigation blocks apart with a static render-order counter: "the
  first rendered is the sitemap column, the second the services
  column." That comment described a two-column footer. The footer
  has three navigation blocks (Services, Case Studies, Company), so
  the counter's binary bucket put the 1st block (actually Services)
  into `footer_sitemap`, and both the 2nd and 3rd blocks (Case
  Studies, Company) into `footer_services`. As long as no classic
  menu was assigned anywhere, this stayed invisible: `has_nav_menu()`
  returned false and every column fell through to its correct
  hard-coded fallback content. Assigning a menu to the Services
  location surfaced it immediately, exactly as reported: Case Studies
  and Company both showed the six-service list, and Services itself
  showed the general site-page list meant for Company.
  - Rewrote the function to match each column by its own class
    instead of counting render order: `rm-footer__links--cases` maps
    to no location (Case Studies is a curated highlight list, not a
    sitemap, and always renders its own links); `rm-footer__links--company`
    maps to `footer_sitemap`; plain `rm-footer__links` maps to
    `footer_services`. Added the `--company` class to the Company
    column in `parts/footer.html` to make this possible; Case Studies
    already carried `--cases`.
  - Updated `remotive_build_classic_menus()`'s auto-created "Footer —
    Sitemap" menu to match what the Company column actually contains
    (About, Team, Insights, FAQ, Contact, Privacy, Terms) instead of
    a list that included Services and Case Studies items redundant
    with the other two columns.
- **Footer's Privacy Policy and Terms of Service links pointed at
  `/privacy-policy/` and `/terms-of-service/`, 404s.** The pages
  `remotive_run_site_setup()` actually creates are `/privacy/` and
  `/terms/`, matching the array keys in `remotive_required_pages()`.
  Fixed in the footer and in the About page's contact-form privacy
  note, the only other place either URL appeared.

## [1.66.2] — 2026-08-30

### Changed

- **All 23 seeded blog posts, service pages and case studies rewritten
  to pass Rank Math's on-page checklist.** Requested target: 90+ Rank
  Math score per page. What that actually requires, beyond the title/
  description length limits fixed in 1.66.0: the focus keyword present
  in the title, meta description, body content and at least one
  subheading; 600+ words; and at least one internal and one external
  link. None of the 23 items passed all of that before this pass.
  - **13 case studies had generic, non-searchable focus keywords**
    like "marketplace launch case study" and "paid search case
    study" — internal labels, not phrases anyone would search, and
    none appeared anywhere in the actual content. Each was replaced
    with a real, specific keyword (`skincare marketplace launch`,
    `automotive programmatic advertising`, `cookieless audience
    building`, `paid search account restructure`, `financial
    services paid media`, `technical seo rebuild`, `healthcare ai
    search visibility`, `southeast asia market entry`, `b2b
    industrial seo`, `fmcg seo across malaysia and singapore`, `b2b
    seo results`, `ecommerce seo for footwear`, `healthcare seo
    malaysia`), woven into title, description, intro, a subheading
    and the body naturally.
  - **Two case studies shared an identical focus keyword**
    (`healthcare seo case study`) before this pass, a keyword
    collision Rank Math itself would flag. Resolved by giving each
    its own distinct keyword above.
  - **Word count was the largest gap.** Several case studies ran
    174–379 words against Rank Math's 600-word floor; the 6 service
    pages and analytics page ran 198–546. Every short item gained one
    to three new sections: what the case doesn't prove, why a
    specific decision was made, how budget or timeline was
    structured, what a prospective client should ask or check. These
    are substantive additions, not padding: each states a fact,
    caveat, or mechanism not previously in the piece. None of the 10
    already-compliant items (4 blog posts, verified in 1.65.9) needed
    touching.
  - **Missing links added throughout.** Most items had internal
    links already; almost none had an outbound link. Added one
    authoritative external link per item (Google's own documentation
    for the relevant claim, Meta's ads guide, MAS regulation, PDPA
    legislation) rather than a generic outbound link chosen just to
    tick the box.
  - **A hyphen mismatch cost two items their title/description
    keyword match**: "SEO-Friendly" vs the keyword "seo friendly",
    and "E-commerce" vs "ecommerce". Rank Math's exact-phrase check
    doesn't treat a hyphen as equivalent to a space; both were
    corrected to match the keyword literally.
  - Full-file PHP syntax verified after every single item throughout
    this pass (23 checks total), using `phply` with `??` substituted
    for a placeholder token to work around its one known parsing gap
    with the null-coalescing operator. One genuine syntax error was
    introduced and caught mid-pass, a dropped closing quote on the
    `email` service page's last paragraph, fixed before it reached
    this release.
- **Not covered by this pass: the 10 auto-created core pages (Home,
  Services, Case Studies, About, Team, Contact, Insights, FAQ,
  Privacy, Terms) and the 2 optional location landing pages
  (`/seo-singapore/`, `/digital-marketing-malaysia/`).** These use
  self-contained templates that render everything from the template
  file itself; the WordPress editor's `post_content` for each is
  intentionally empty, by the theme's own content/theme separation
  convention (see `readme.md`). Rank Math's on-page checklist analyses
  `post_content`, so it has nothing to score against on these pages
  regardless of how good the rendered template output actually is.
  Reaching 90+ there would mean either duplicating the template's
  copy into the database as real post content, which breaks the
  single-source-of-truth convention documented since v1.3.0, or
  accepting a structurally lower on-page score on these 12 pages
  while their actual rendered content, meta title/description (added
  in 1.66.0), and schema markup remain fully correct. This decision
  needs a call from the person maintaining the site, not a default
  taken here.

## [1.66.1] — 2026-08-30

### Added

- **Facebook, X, YouTube and Threads URL fields added to Theme
  Options**, joining the existing Instagram, LinkedIn and TikTok
  fields. Only three of seven mainstream platforms had a field at
  all. Added `social_facebook`, `social_x`, `social_youtube` and
  `social_threads` throughout the same pipeline the existing three
  use: defaults (`'#'`, matching the existing placeholder
  convention), field definitions in the Social tab, sanitisation via
  `remotive_sanitize_optional_url()`, and new
  `__REMOTIVE_SOCIAL_FACEBOOK__` / `__REMOTIVE_SOCIAL_X__` /
  `__REMOTIVE_SOCIAL_YOUTUBE__` / `__REMOTIVE_SOCIAL_THREADS__`
  tokens.
- **Icons added to the footer and the Contact page's "Elsewhere"
  card**, in the order Facebook, Instagram, X, YouTube, Threads,
  LinkedIn, TikTok. Facebook and X icons are Simple Icons paths (CC0);
  YouTube's is the platform's own published brand mark; all follow
  the same `currentColor` fill and `rm-social-link` sizing as the
  existing three, so they inherit hover/focus states and dark-mode
  colour automatically. `.rm-footer__socials` gained `flex-wrap:wrap`
  since seven 44px touch targets no longer fit one row on the
  narrowest phones; unwrapped, it would have caused horizontal
  overflow the same class of bug fixed for other components earlier
  in the project's history.
- **`remotive_schema_same_as()` now checks all seven fields**, so any
  of the four new URLs that get filled in are picked up by the
  Organization schema's `sameAs` array the same way the existing
  three already are.
- Not visually confirmed in the render harness this session; the
  harness stopped producing output partway through this change
  (a sandbox issue, not a code issue). The four new icon paths are
  taken from Simple Icons and the platforms' own brand kits, but a
  quick look at the live footer after deploying is worth doing,
  particularly for the Threads glyph, which is the most complex path
  of the four.

## [1.66.0] — 2026-08-30

### Fixed

- **7 case-study meta descriptions exceeded Rank Math's 130-character
  limit** (up to 150 chars): marketplace-launch-skincare,
  cookieless-audience-sports, paid-search-b2b-gifting,
  paid-media-financial-services, technical-seo-industrial-automation,
  seo-ai-visibility-healthcare, and market-entry-trading-platform.
  All 23 seeded blog posts and case studies already carried
  `rm_title`/`rm_desc`/`rm_kw`, correctly wired to
  `rank_math_title`/`rank_math_description`/`rank_math_focus_keyword`
  via `content-seed.php`; this was a character-count fix only. All 23
  titles were already under the 50-character limit.

### Added

- **The 9 auto-created core pages (Home, Services, Case Studies,
  About, Team, Contact, Insights, FAQ, Privacy, Terms) had no Rank
  Math meta wired anywhere in the codebase.** `remotive_run_site_setup()`
  created these pages with a title and a template assignment only;
  nothing set a title, description or focus keyword. Since Home is
  set as the static front page and Insights as the posts page in
  Settings → Reading, this meant Rank Math had nothing of its own to
  read for the homepage or blog index specifically (front-page.html's
  own schema output covers structured data, but the plugin's title/
  description fields were empty). Added `rm_title`, `rm_desc` and
  `rm_kw` to `remotive_required_pages()` for all 9 pages, all within
  the 50/130 limits, and extended `remotive_run_site_setup()` to
  write them via the same `rank_math_*` postmeta keys
  `content-seed.php` already uses. On a fresh activation the values
  are set at creation. On a site where these pages already exist, the
  Theme Options re-check button now fills in only the meta keys that
  are currently empty, so anything already written by hand in Rank
  Math's own metabox is left untouched, matching the file's existing
  "additive only, never overwrites" rule. Privacy and Terms carry no
  focus keyword: legal boilerplate has no commercial term worth
  targeting, so the field is left blank by design rather than filled
  with a keyword that would not mean anything.
- **`/seo-singapore/` and `/digital-marketing-malaysia/` are not
  covered by this change.** Both are optional pages created manually
  by selecting the template in the editor, per `upgrading.md`, not
  part of the auto-created page list, so there is no activation hook
  to wire meta into. Drafted values are ready to paste into Rank
  Math once each page exists:
  - SEO Singapore: title "SEO & Digital Marketing Agency, Singapore"
    (41 chars), description "SEO, performance marketing and paid
    social for Singapore businesses that want reporting they can
    trust." (103 chars), focus keyword "seo agency singapore".
  - Digital Marketing Malaysia: title "Digital Marketing Agency,
    Malaysia | KL & Penang" (48 chars), description "SEO, SEM and
    social for Kuala Lumpur, Penang and PJ businesses, run as one
    connected system. See how." (101 chars), focus keyword "digital
    marketing agency malaysia".

## [1.65.9] — 2026-08-30

### Changed

- **Site-wide copy audit against the house English voice**, covering
  every template, `inc/content-seed-data.php` (blog posts, case
  studies, service pages), FAQ answers and theme-option defaults.
  Four fixes:
  - Front page CTA note read "No newsletter, no spam, just what we'd
    fix first," an exact match of the banned "No X, no Y, just Z"
    negative-parallelism pattern. Rewritten as "We reply with what
    we'd fix first. No mailing list."
  - Digital Marketing Malaysia page read "built to support SEO
    rankings, not just fill a content calendar," a negative-
    parallelism tail. Rewritten as a direct positive claim.
  - "Choosing an SEO agency" post used "showcased results are the
    best results," carrying the banned word "showcase." Reworded to
    "the results on the page are the best ones on file."
  - Content service page used "PDPA-aligned," carrying the banned
    word "align." Changed to "PDPA-compliant."
  - Everything else checked clean: no em dashes in visitor-facing
    copy, no anaphoric runs, no padding triads (the coordinated
    lists found are distinct named items, not synonym filler), no
    title-case headers, no dead transitions, no meta-commentary
    openers.
  - Not touched: `changelog.md` and `readme.md` carry roughly 300 em
    dashes across their historical version entries. That is internal
    dev documentation accumulated over the theme's full version
    history, not visitor-facing content, and well outside the scope
    of this pass. Flagged for a separate pass if wanted.

## [1.65.8] — 2026-08-30

### Added

- **New "Built to run light" section on the About page**, sitting
  between Why Asia and What we stand for. This is the site's
  culture/sustainability content, sourced from a client-provided
  boilerplate ("first Post-Digital Media and Audience Activation
  Agency," "regenerative soul and culture") that did not survive
  translation into the site's voice: unverifiable superlatives,
  undefined category jargon, and a hedging, aspirational tone that
  clashes with the plain, declarative register everywhere else on the
  page (see "Numbers described honestly" two sections down). Rather
  than reword that copy directly, the section states only the two
  claims already true of the operating model: a distributed team
  working from wherever the work is rather than a central office
  (tying to the "senior specialists working inside your team"
  positioning already used on the homepage), and digital-only
  deliverables with no physical production. No sourcing, offset, or
  vendor-policy claims are made, since none were supplied to verify.

## [1.65.7] — 2026-08-30

### Changed

- **About page's "Three things we will not trade away" section now
  pairs "Meet the team →" with the heading, top-right**, instead of
  leaving it stranded at the bottom-left underneath all three value
  columns. That placement was the one section on the site not using
  the shared `rm-section-head` flex wrapper: eyebrow, heading, and
  lead sat in a flat stack with the link tacked on at the very end,
  so the link visually attached to "Scope you can change" rather than
  to the section it was actually pointing away from. Restructured to
  the same markup as Problem/Why/Services/Work/Team: link beside the
  heading block, not after the content it introduces.

## [1.65.6] — 2026-08-30

### Changed

- **Problem and Why Re:Motive sections on the homepage gain a
  right-aligned action link**, matching the Services, Work and Team
  sections that sit directly above and below them. Both sections
  previously used a flat `wp:html` block with no `rm-section-head`
  wrapper, so unlike every other major homepage section they had
  nothing on the right — an inconsistency the other three sections'
  pattern made obvious by contrast. Restructured both into the same
  `rm-section-head` flex markup as Services/Work/Team: eyebrow,
  heading and lead on the left, link on the right. Problem now links
  to `/services/` ("See how we fix this →"); Why links to `/about/`
  ("More about us →").
- **Removed the stale `#problem > h2, #why > h2` CSS rule**, a
  direct-child selector left over from the flat markup that stopped
  matching once the heading moved inside `.rm-section-head`. The
  `#about` eyebrow/heading rules it shared a comment with are
  unaffected and still apply, since that section keeps its original
  flat structure.

## [1.65.5] — 2026-08-30

### Changed

- **Sidebar's "Get in touch" card gains a one-line reply-time note**
  above the email address. The card read as bare next to the fuller
  footer contact block below it on the same page. Added "We reply
  within one business day," the same commitment already stated in
  the contact page hero, rather than inventing a new claim. New
  `.rm-sidebar__note` style: smaller and more muted than the
  existing `.rm-sidebar__about` text, so it reads as a qualifier
  rather than a duplicate of the about copy.

## [1.65.4] — 2026-08-30

### Fixed

- **Studio gallery on the Team page now holds four tiles per row at
  every width.** `.rm-gallery` used `grid-template-columns:repeat(auto-fit,
  minmax(min(240px,100%),1fr))`, which packed as many 240px tracks as
  the wide measure allowed — five at full desktop width — and stranded
  the sixth tile ("The team") alone on a second row next to empty
  space. The comment above the rule claimed this resolved to three
  columns; it did not, because `rm-measure-wide` is wider than the
  740px the comment assumed. Replaced with a fixed
  `repeat(4,1fr)`, stepping down to two columns at 782px and one at
  480px, so the row count is predictable regardless of container
  width and the six tiles land as two even rows of four and two.

## [1.65.3] — 2026-08-30

### Documentation

- **readme.md was 36 releases out of date and is brought current.**
  It is the theme's own reference, and it had drifted into being
  wrong rather than merely incomplete:
  - Five modules were missing from the file map entirely: leads.php,
    akismet.php, avif.php, branded-login.php and accessibility.php,
    along with the seed, setup and classic-menu files. All are now
    described.
  - page-about.html was still documented as a parallax hero with a
    lightbox gallery and contact form. That page became the company
    page in 1.56.0 and the gallery moved to the team page. Both are
    corrected, and page-team.html and page-faq.html are added.
  - The footer table still named the Sitemap column, which became
    Company in 1.65.0.
  - **A new section on enquiries and spam**, covering storage before
    email, the single store shared with Contact Form 7, the spam
    folder, the retention period, and the rule that the spam filter
    always fails open.
- **upgrading.md gains the steps this run created**: adding Team and
  Case Studies to the saved navigation by hand, renaming rather than
  recreating the Work page so WordPress keeps the redirect, creating
  the new pages, filling in the legal drafts before they are
  reachable, and setting a retention period that matches the privacy
  policy.
- **resources.md** records that the login stylesheet, the front-end
  bundle and the portrait script are the theme's own work under its
  own licence, with no third-party library bundled.

## [1.65.2] — 2026-08-30

### Changed

- **Spam enquiries go to a spam folder rather than carrying a hidden
  flag.** They were stored as ordinary published enquiries marked
  with post meta, which meant every query had to remember to exclude
  them and a missed exclusion would leak spam into the enquiries
  list. Spam now has its own post status, which is how WordPress
  already separates spam comments.
  - Spam is out of the default view by construction rather than by
    filtering, and the enquiries screen gains a Spam link with a
    count next to its other views.
  - Marking and unmarking moves the enquiry between folders. Not spam
    restores it into the list and still reports the correction to
    Akismet.
  - The retention purge names both folders explicitly, because spam
    is not covered by 'any' and the folder would otherwise grow
    without limit while real enquiries expired.
  - The CSV export covers both folders and marks which is which.

## [1.65.1] — 2026-08-30

### Changed

- **Both legal drafts now address visitors to the site, and cover
  Singapore and Malaysia.**
  - The privacy policy states who it applies to in its first
    paragraph, anyone visiting the site wherever they are, and
    separates that from data processed for a client during an
    engagement, which the engagement contract governs.
  - A new section explains which law protects which visitor:
    Singapore's Personal Data Protection Act 2012 as a
    Singapore-registered organisation, and Malaysia's Personal Data
    Protection Act 2010 where processing connects to commercial
    activity there, with the stronger protection applying when both
    do. Both regulators are named, so a visitor knows where to
    complain.
  - The breach clause commits to notifying whichever regulator covers
    the affected data.
  - The terms open by stating they apply to every visitor, wherever
    they read from.

### Note

- **Governing law needed a different treatment from the privacy
  policy, and the draft says why.** Two laws can both apply to data
  protection, because each country's statute reaches the processing
  it covers. A contract is different: it normally has one governing
  law, and naming two invites an argument about which decides a
  question, which is the opposite of what the clause is for. The
  draft therefore names Singapore law as governing, expressly
  preserves the mandatory protections a Malaysian visitor has
  regardless of that choice, and makes both countries' courts
  available on a non-exclusive basis. That is the usual way to honour
  a two-country intent. A CONFIRM marker sets this out for the lawyer
  rather than burying it.
- Malaysia's PDPA was substantially amended in 2024 and 2025,
  including breach notification and data protection officer duties.
  A marker asks the lawyer to confirm what is in force on the
  publication date and whether the company must register under the
  Act.

## [1.65.0] — 2026-08-30

### Changed

- **The footer is regrouped so every page has a site-wide link.**
  Six service pages, thirteen case studies, the team page and the FAQ
  were reachable only by going through a parent page. The columns are
  now Services, Case studies, Company and Contact.
  - The Sitemap column becomes Company and stops repeating links that
    have their own column. It carries what lives nowhere else: about,
    team, insights, FAQ, contact and the two legal pages.
  - A Case studies column lists six engagements by market and
    discipline, closing with a link to all thirteen. Labels are
    shortened from the page titles, which would run to three lines
    each in a footer column.
  - Both link columns close with a link to their index, so the index
    keeps its own internal link now that the children are listed.
  - The brand column narrows from 34 to 26 percent to make room for
    five columns rather than four.

  Verified in dark, light and at 390px: five columns, twenty-five
  links, no horizontal overflow, and a clean single-column stack on a
  phone.

### Note

- Column order follows how a visitor reads a page rather than the
  site's menu order: what you do, then the proof, then who you are.
- Case study labels lose some keyword text by being shortened. That
  is a real trade-off on an agency site that sells search, and the
  alternative was a column of three-line links, so the shortening
  won. The full titles remain the page titles and the H1s.

## [1.64.0] — 2026-08-30

### Added

- **Contact form submissions are checked by Akismet**, the same
  service and the same reputation data that filters comment spam. The
  honeypot the forms already had stops naive bots and nothing else.
  A submission is checked before it is emailed, sent as a
  contact-form type rather than a blog comment so it is scored
  appropriately.
- **Suspected spam is filed, not discarded.** It is stored, marked,
  kept out of the enquiries list and never emailed, but it is still
  there. No filter is perfect, and a silently deleted enquiry is a
  lost client. A second list on the enquiries card shows what was
  caught, and marking one as not spam restores it and tells Akismet
  it got that one wrong.
- **Marking a missed spam works the other way.** Akismet's own
  reporting reads from the comments table and cannot be used for a
  lead, so both verdicts are posted to its submit-spam and submit-ham
  endpoints directly, which is what teaches the filter.
- The enquiries card states plainly which of three situations the
  site is in: filtering on, plugin installed without a key, or no
  plugin at all.

### Note

- **The forms never stop working because the filter is missing.**
  Akismet holds the API key and the theme has no way to store one, so
  with the plugin absent, unconnected, or simply unreachable during
  an outage, every check returns not-spam and submissions carry on
  exactly as before. Verified across all five states, including an
  outage returning false.
- A sender whose message is caught still sees the ordinary
  confirmation. Telling a spammer their submission was filtered only
  tells them what to change.
- The IP address and user agent are stored with each enquiry so a
  later report carries the same context Akismet saw. Both already
  appear in the server logs the privacy policy draft describes, and
  the retention period deletes them with the rest of the record.

## [1.63.0] — 2026-08-30

### Added

- **Enquiries are stored, not only emailed.** Submissions existed as
  email and nothing else, so a wp_mail() failure, which happens for
  ordinary reasons like a misconfigured SPF record or host
  throttling, lost the enquiry with no record anywhere. Every
  submission is now written to the database first and emailed
  second. A mail problem costs the notification rather than the lead,
  and the enquiry is marked in the admin list so someone can reply by
  hand.
- **One store for both forms.** When Contact Form 7 is active its
  submissions are captured into the same place as the theme's own,
  which is the point of merging rather than running two systems. CF7
  field names vary between forms, so the usual conventions are tried
  in order and anything unrecognised is appended to the message body
  rather than dropped, so a custom field never disappears silently.
- **Contact Form 7 is styled to match the theme.** The plugin emits
  its own markup, which would otherwise arrive unstyled: fields,
  focus rings, the submit button, validation tips and the response
  message now match the theme's own forms, including the 16px field
  size that stops iOS zooming on focus.
- **An enquiries card on the Theme Options screen**, showing the ten
  most recent with their source, flagging any whose notification
  failed, with CSV export and a link to the full list.
- **A retention period, in months, on the Display tab.** Enquiries
  older than it are deleted daily. The privacy policy draft has to
  state a period, and a stated period nothing enforces is worse than
  none. Set zero to keep them indefinitely.

### Note

- Leads are an ordinary custom post type, private and not publicly
  queryable, so an enquiry can never be reached at a URL, appear in
  search or turn up in a sitemap. They survive a theme switch and
  export through WordPress's own tools, which a plugin's private
  table does not.
- Contact Form 7 remains optional. Nothing in the theme requires it,
  and the built-in forms work whether it is installed or not.

## [1.62.0] — 2026-08-30

### Added

- **Drafts for the privacy policy and terms of service**, in
  docs/legal-privacy-policy.md and docs/legal-terms-of-service.md.
  Both are written against what the site actually does rather than
  from boilerplate: the forms collect a name, an email address and a
  message; submissions are emailed and never written to the database;
  the only browser storage is the light and dark preference. Facts
  the theme cannot know, such as the host, its log retention and
  whether analytics is active, are marked CONFIRM rather than
  guessed, because a policy that states something untrue is worse
  than no policy.
- **Both pages are linked from the footer**, alongside the FAQ.
- **A privacy line under the contact form.** The site collected
  names, email addresses and messages with no notice at the point of
  collection, which is where a visitor decides whether to submit. One
  line now sits under the submit button and links to the policy.

### Note

- These are drafts for a lawyer to review, not finished documents,
  and the files say so at the top. Re:Motive Media Asia is registered
  in Singapore, so the Personal Data Protection Act 2012 applies,
  including the requirement to name a Data Protection Officer and
  publish their contact details. That name is one of the CONFIRM
  markers.
- The terms draft covers website use, not commercial engagement
  terms. Client work is governed by the signed contract for each
  engagement, and a public page that appears to set commercial terms
  invites confusion about which document governs. The draft says so
  explicitly and the file explains the choice.

## [1.61.0] — 2026-08-30

### Added

- **An FAQ page at /faq/.** Eleven questions in four sections: how an
  engagement runs, the markets and why they are not interchangeable,
  how measurement is reported, and a closing prompt to ask the one
  that is not there. It uses the same hero, section heads and
  descriptions as every other page, and the accordion styling
  already written for the landing pages.
  - **The structured data is parsed from the page itself.** The
    theme's FAQPage schema reads question and answer pairs out of the
    template's own details blocks, so the markup and the schema
    cannot drift apart. All eleven parse. That mechanism already
    existed for the two landing pages; the FAQ template is added to
    the list it scans.
  - Linked from the footer and included in the classic-menu fallback.
    It is deliberately not in the main navigation, which is already
    six items wide.

### Note

- Every answer restates something the site already commits to
  elsewhere: modular scope, senior staffing, attribution described as
  a model, platform figures labelled as platform figures, clients
  anonymised with figures unchanged. Nothing new is claimed. The
  answer about results avoids a timeline promise and points at the
  case studies, where the starting position is stated on each one.

## [1.60.0] — 2026-08-30

### Changed

- **Every section heading now carries a description.** An audit of
  all templates found eight section heads that went straight from
  heading to content: four on the homepage, two on About, one on the
  team page's gallery and one on the archive index. A heading with a
  line under it is the pattern the rest of the site uses, and those
  eight broke it.
  - **The homepage descriptions are editable.** Their headings were
    already Theme Options fields, so the lines under them are too:
    five new fields on the section headings tab, one beside each
    heading it belongs to.
  - The team teaser's description was hardcoded while its heading was
    editable, which meant a site owner could change one and not the
    other. It now uses the same option pair as the rest, keeping its
    original wording as the default.
  - A single .rm-section-lead style defines the type, colour and
    reading measure once, rather than each page setting its own.

### Note

- Two specificity problems surfaced while doing this, both from the
  homepage's ID-scoped layout rules, which outrank a class selector
  no matter how many !important flags it carries. The lead first ran
  the full width of the section, then sat centred while its heading
  stayed left. Both are fixed by matching that specificity rather
  than escalating against it.

## [1.59.0] — 2026-08-30

### Changed

- **Work becomes Case Studies, at /case-studies/.** The page was
  titled Work while its heading, its cards and every link inside it
  said case studies, so the navigation label was the only place the
  old name survived. The page key, title and slug all change, and
  with them:
  - The thirteen case studies are re-parented, so their addresses
    move from /work/<slug>/ to /case-studies/<slug>/.
  - Both navigation menus read Case Studies, in the block templates
    and in the classic-menu fallback, which also gains the Team page
    it had been missing since 1.56.0.
  - The homepage's section link reads "All case studies" rather than
    "All work", and its five links to the section are updated.
  - The WebMCP navigation tool returns the new address, so an agent
    asking for the site's destinations is not sent to a dead one.

### Note

- On the live site this changes thirteen published addresses. Rename
  the page from the editor rather than recreating it: WordPress
  records the previous slug and redirects the old address to the new
  one, which recreating loses. Anything already linking to /work/
  will keep working through that redirect, but the sitemap should be
  resubmitted and any advertising links pointed at the new address
  directly rather than relying on a redirect.

## [1.58.1] — 2026-08-30

### Fixed

- **Content on the Team and About pages sat in a narrower column
  than the rest of the site.** Services, Work, Contact and the blog
  views widen their section wrappers to the site's wide measure so
  headings and eyebrows meet the same left edge as the header and
  footer. Both new pages were built without that, so their content
  was clamped to the 740px reading measure and started further in
  than every other page. Their sections now carry the same wide
  measure, and both pages line up: section, heading, eyebrow and
  text all begin at the same point as on Services.
- **Running text keeps a readable line length.** Widening a wrapper
  widens everything inside it, which would have stretched the About
  page's paragraphs to 1320px. Prose keeps its own cap and, because
  a capped child inside a constrained wrapper is centred by core's
  auto margins, its side margins are zeroed so it pins to the shared
  left edge rather than floating in the middle of the section.

## [1.58.0] — 2026-08-30

### Changed

- **Three pages had headers that did not match the rest of the
  site.** Services, Contact and Work share one pattern: a full-width
  hero with an eyebrow, a display-size H1 and a sentence of
  description. Team and About used a different hero class left over
  from the old about page, and Insights had no hero at all, an H1 set
  two steps smaller than every other page's, and no description under
  it. All three now use the same pattern as the other three, so a
  visitor moving between sections meets the same page opening each
  time.
  - Insights gains the description it never had: what the section
    covers and which markets it reports on.
  - Team and About keep their wording; only the markup and the type
    treatment change.
  - The About page's main element was also missing its id, so the
    skip link had nothing to jump to on that page. Fixed.

### Removed

- **The parallax hero script and its styles.** They existed only for
  the old about-page hero, which no longer exists, so the script was
  being loaded to move an element that was never on the page. The
  file and the orphaned rules are deleted rather than left dormant.

### Fixed

- **The lightbox was loading on the wrong page.** It was tied to the
  about template, but the photo gallery moved to the team page in
  1.56.0, so the gallery had no viewer while About loaded a script it
  had no use for. It now follows the gallery.

## [1.57.4] — 2026-08-30

### Fixed

- **Jazlan's hover portrait is reshot from a frame that matches his
  base.** The previous one had him leaning against the wall, tilted
  back and sitting right of centre, so hovering swung the picture
  rather than changing the expression. The replacement has him
  standing square to the camera with hands clasped, framed the same
  way as his resting portrait: face heights of 25.0 and 25.6 percent,
  and the chin lift is the only thing that changes between the two
  states.
- The crop window sits deliberately left of him, because further
  right brings the shop sign into frame and the cut-out then treats
  it as part of the subject. That is now recorded in the script
  beside his entry, since it has caught three of these frames.

## [1.57.3] — 2026-08-30

### Added

- **Gordan's hover portrait returns, scaled to fill.** His only
  source is a low-resolution landscape headshot with nothing below
  the chest, which at the set's face size leaves him floating above
  a cut edge. Filling the tile instead is the better of the two
  outcomes that photograph allows, so his hover frame is scaled up
  and his face reads larger than the rest of the set until a fuller
  photograph exists. Filling also pushed him off centre, so the
  subject is nudged a tenth of a frame to the left.
- **A SPECIAL table in tools/normalise-portraits.py** carries frames
  the general rule cannot serve, with the reason recorded beside
  each one. Handling this in a named exception keeps the rule that
  works for the other nine intact, rather than loosening it for
  everybody to accommodate one photograph.

## [1.57.2] — 2026-08-30

### Fixed

- **A grey line traced the outline of every portrait.** On the tile
  it read as a border drawn around the person. The cause was the
  cut-out working from a plain mask: a white shirt against a pale
  studio wall gives it almost no contrast to separate, so it left a
  fringe of background attached to the silhouette. All portraits are
  re-cut with alpha matting, which resolves the edge from the image
  itself. The outline is gone, and the same pass improved the edges
  on the darker shirts too.

### Removed

- **Gordan's hover portrait.** Its only source is a tight
  head-and-shoulders crop that ends in a hard horizontal edge
  partway down the frame, so the hover state showed a floating
  fragment of a person with a cut line across the bottom. No framing
  rule can invent the missing part of the photograph. Since the
  theme adds a hover layer only when the alternate file exists, his
  tile now degrades to a still portrait, which is correct rather
  than broken. Restore it when a fuller photograph exists: the
  script keeps his entry commented in place for that.

## [1.57.1] — 2026-08-30

### Fixed

- **Jazlan's hover portrait was unusable.** He was leaning, tilted
  and pushed hard against the right edge with a shoulder cut off.
  The cause was the fallback path added in 1.57.0: sunglasses and a
  raised chin left the face undetectable, so the crop was inferred
  from head width rather than measured, and an inferred crop of a
  leaning pose lands badly. The frame is replaced with one from the
  same shoot where the face is detectable, so it is measured like
  every other portrait.
- **A turned pose no longer sits to one side of its tile.** Centring
  on the face is right when the body fills the frame and wrong when
  it does not, which left dead space opposite the subject. Where the
  whole person fits with room to spare, the person is centred rather
  than the face. This corrects the same drift on several tiles, not
  only Jazlan's.

  All ten portraits now clear both edges, sit centred, and hold face
  heights within about a point of each other.

### Note

- The inference path stays in the script for photographs where no
  face can be found, but it is the weaker route: a hover frame whose
  face the detector can see will always be framed more accurately.
  Worth keeping in mind for Adam's photographs, where sunglasses and
  a raised chin are the two things to avoid in at least one frame.

## [1.57.0] — 2026-08-30

### Fixed

- **Shoulders were being cut off by the tile edge, and faces still
  varied in size.** Measuring all ten portraits found eight of them
  touching the left or right edge, so the subject was running out of
  frame, and face heights ranging from 26% to 52% of the tile, with
  Gordan's the largest of the set.
  - **Portraits are normalised on face height, not head width.** Head
    width looked correct in isolation and wrong in the grid, because
    hair, a hat and a bald crown all change head width without
    changing how large the person reads. The face box is what the eye
    compares, so it is what the crop is now built around, with the
    eye line placed at a fixed height.
  - **No portrait may touch the frame.** The subject's full width is
    measured and the crop is widened until it fits with a clear
    margin on both sides. Where that conflicts with the target face
    size, the fit wins: a clipped shoulder reads as a mistake, a
    slightly smaller face does not.
  - **A face the detector cannot see is sized from the same person's
    other frame.** Sunglasses and a raised chin defeat the cascade,
    which is exactly what a hover frame tends to contain. The base
    photograph gives that person's ratio of face height to head
    width, so the hover frame can be sized correctly without the face
    being found.

  All ten portraits now clear the frame on both sides, and face
  heights sit within a few points of each other instead of spanning
  a factor of two.

### Note

- Gordan's hover frame still reaches only about two thirds down the
  tile. Its source is a tight head-and-shoulders crop with nothing
  below to show, so no framing rule can fill it; a fuller photograph
  is the only fix.

## [1.56.1] — 2026-08-30

### Added

- **Jazlan Zakirin's hover portrait pair.** Facing the camera at
  rest, chin-up lean on hover. Framed by tools/normalise-portraits.py
  rather than by eye, so it matched the other four on the first pass:
  head width within a tenth of a percent of the set, same distance
  from the top, same centre line. Five of six members now have
  photography.

### Note

- His first hover frame came from a photograph with the shop sign
  beside him, which the cut-out treats as part of the subject and
  which then sets the head measurement, the same trap Nabil's hover
  frame hit. Swapped for a shot with clear space around him. Worth
  knowing for the last set: a plain background beside the subject is
  worth more than a good pose next to signage.

## [1.56.0] — 2026-08-30

### Changed

- **The About page splits in two.** What lived at /about was a team
  page wearing the About name: a roster, a photo gallery and a
  contact form, with nothing about the company itself.
  - **/team, "Meet the Team"**, keeps the roster and the gallery on a
    new page-team template. The contact form leaves it, since the
    page's job is the people.
  - **/about is rebuilt as the company page**: how Re:Motive started
    and the complaint it was built to answer, why the region is
    treated as several markets rather than one, and the three
    commitments it will not trade away. The contact form moves here.
    Every factual claim matches what the structured data already
    asserts, Singapore registration in 2024 and a regional footprint;
    nothing about the company's history is invented to fill the page.
  - Both pages are in the header and footer navigation, the
    homepage's team link points at /team, and the shipped-page list
    creates both on activation.

- **Page types in the structured data now match what each page is.**
  /about was typed ProfilePage while it carried the roster; it is now
  AboutPage, which is what a page about the organization is, and
  Google reserves ProfilePage for a single profile. /team is typed
  CollectionPage, being a listing of people rather than a profile of
  any one of them.

### Note

- The About form handler needed no change: it redirects using the
  referring URL rather than a hardcoded page, so it works wherever
  the form is placed. That was written that way in advance and it
  paid off here.

## [1.55.0] — 2026-08-30

### Fixed

- **Team portraits are aligned to one another instead of cropped by
  eye.** Measuring the shipped tiles showed why the grid looked
  unsettled: face heights ranged from 300 to 570 pixels and eye lines
  sat between 32% and 51% down the frame, so each person appeared to
  be standing at a different distance from the camera. Comparing the
  cut-outs side by side, which is how they were checked until now,
  hid this, because that comparison shows the images rather than the
  tiles.
  - All eight portraits are rebuilt from their sources by
    measurement. Each is cut out, the head is measured from the
    alpha mask, and the crop is then computed so that every finished
    tile has the head at the same width, the same distance from the
    top, and centred on the same vertical line. The measured result
    is identical across all eight to within half a percent.
  - **tools/normalise-portraits.py ships with the theme** so the
    next member's photographs are framed by the same rule rather
    than matched by hand.

### Note

- Two details the measurements exposed. Gordan's hover frame comes
  from a tightly cropped source and reaches only about 78% down the
  tile, where the others reach the bottom edge; a fuller original
  would fix it. And Nabil's hover frame had to keep a tight source
  window, because a taller one pulls in the shop sign beside him,
  which the cut-out then mistakes for the subject.

## [1.54.2] — 2026-08-30

### Added

- **Alif Aziz's hover portrait pair.** Facing the camera at rest,
  turned away on hover, cut to the shared framing and encoded to
  AVIF (18 and 15KB). The first cut came out with a noticeably
  smaller head than the other three, since he stood further from the
  camera; it was re-cut tighter and checked against the set before
  shipping. Four of the six members now have photography, and the
  generated rules and structured data pick it up with no code
  change.

## [1.54.1] — 2026-08-30

### Added

- **Nabil Takiyuddin's hover portrait pair.** Two supplied photos cut
  to the shared framing, background removed and encoded to AVIF (20
  and 16KB): the composed shot looking away at rest, the laugh on
  hover. Head scale and eye line checked against Gordan's and
  Elfie's before shipping. No code changed: since 1.44.2 the portrait
  rules are generated from the Theme Options roster, so the two files
  named for his slug are the whole change. Verified that the
  generated CSS now covers gordan, elfie and nabil, that Jazlan
  correctly still resolves to the placeholder tile, and that his
  Person entry in the structured data has gained its image property.

## [1.54.0] — 2026-08-30

### Performance

- **Three front-end scripts become one.** The colour-mode toggle, the
  mobile navigation fallback and the ticker pause were three separate
  requests carrying under 10KB between them, and on the audited
  install every one of them arrived with no cache lifetime, so all
  three were re-fetched on every visit. They are now a single
  assets/js/remotive.js, with each part still guarding on its own
  markup so a page without a ticker or a navigation block runs only
  what applies to it. Nothing is minified; the parts keep their
  original comments and are edited in place. Verified on a full page,
  where the fallback still builds and its panel opens, and on a bare
  page, which throws nothing.

### Documentation

- **A drop-in cache header file.** docs/htaccess-cache.txt is a
  block to copy straight into .htaccess above the WordPress marker,
  with the nginx equivalent and a note on how to confirm it took
  effect.
- **The diagnosis in docs/cache-headers.md is corrected.** It
  previously blamed the `?ver=` query string for the scripts having
  no cache lifetime. The evidence says otherwise: the stylesheet
  carries the same query string and does get a header. The server's
  rule covers some file types and not JavaScript, which is the actual
  cause and a simpler fix.

### Note

- Cache-Control cannot be set by a theme: static files never reach
  PHP. The consolidation above reduces how many uncached requests
  there are; only the server or CDN config can stop them being
  uncached.

## [1.53.6] — 2026-08-30

### Fixed

- **The proof rows appeared to jump when hovered.** WordPress's flow
  layout puts a 24px margin between sibling blocks, and that margin
  sat between rows meant to read as one continuous list divided by
  their own hairlines. While every row was transparent the margin was
  invisible; the moment a row took its hover background, the unfilled
  band above or below it appeared, which reads as the row growing or
  the list shifting. Nothing was actually moving. The rows are now
  contiguous, with the hairline as the only separator, so a hovered
  row highlights in place. Verified by measuring the rows at rest and
  while hovering: gaps of zero and identical heights in both states.

### Note

- The audit harness now models core's flow-layout spacing, which is
  what hid this from earlier passes: the rows measured contiguous
  there while the real page had the margin.

## [1.53.5] — 2026-08-30

### Fixed

- **Consecutive sections left a hole between them rather than a
  rhythm.** Each section carries a full block of padding on both
  sides, so at the join between any two of them the space from one
  section's last line to the next section's eyebrow came to 256px on
  a desktop screen. The templates already try to say this should not
  happen, with an inline padding-top:0 on stacked sections, but the
  theme's own .rm-section rule carries !important and had been
  overriding that since it was written. Rather than drop a flag other
  rules depend on, both sides of a join now state a smaller value:
  a section followed by another, and a section following another,
  each take roughly half a block. The join settles at 128px on
  desktop and 80px on a phone, and the front page is around 200px
  shorter as a result. A section that opens or closes a run keeps its
  full padding, which is what holds the content clear of the header
  and footer, and the same halving is applied where a section hands
  over to the call-to-action band. Verified at 1440, 768 and 390px.

## [1.53.4] — 2026-08-30

### Changed

- **The stats band no longer floats in empty space.** The band is a
  full-bleed rule between two sections and carries no margin of its
  own, but the sections on either side each contributed their full
  128px of section padding, so it sat in two large empty areas rather
  than reading as a divider. The padding is halved where a section
  meets the band, and only there: the section before it and the
  section after it, leaving every other section's rhythm untouched.
  On a wide screen that takes the space above and below from 128px to
  56px. Verified at 1740, 1280 and 390px, with an unrelated section
  confirmed still at full padding.

## [1.53.3] — 2026-08-30

### Changed

- **The call-to-action band's middle gap is closed up.** The headline
  sits on one line while the form column is a fixed width, so every
  extra pixel of viewport landed in the flexible track between them:
  around 330px of empty band at 1740px wide, which read as a hole
  rather than as spacing. The band now takes its own measure, capped
  at 1140px rather than following the page's 1320px wide layout, and
  the column gap tightens from a 5rem ceiling to 3.5rem with the form
  column trimmed from 420 to 400px. Neither half moves away from its
  own edge, so the statement still sits left and the action right;
  they simply stop drifting apart. The gap now settles at about
  150px from 1440px upward instead of growing without limit.
  Verified at 1280, 1440, 1740 and 1920px, with the single-column
  stack below 900px unchanged.

## [1.53.2] — 2026-08-30

### Fixed

- **The branded login stylesheet is written against WordPress's real
  login markup.** The first version styled a simplified idea of the
  screen and left several of its actual parts untouched: the password
  field's wrapper and its reveal button, which sat on a pale strip
  over the dark field; the Remember Me row and its help icon, still
  floated by core's own rules; the submit button, floated right at
  its default height; and any sign-in card a provider renders above
  the form, left floating unaligned. All of these are now styled:
  the reveal button sits inside the field in the panel's own colours,
  the Remember Me row is a flex row with its icon toned down, the
  submit button is full width at 44px, and a provider's card is
  aligned to the panel's width and corner radius while its own button
  chrome is left untouched, since those are the provider's brand
  assets. Verified against markup reproduced from the reported
  screen, at 390 and 1027px: no overflow, 16px fields, 44px submit,
  and the reveal button correctly inside the password field.

## [1.53.1] — 2026-08-30

### Removed

- **Sign in with Google styling from the branded login screen.** The
  rules targeting Site Kit's button class are gone, and the default
  message no longer names a Google account. The screen now styles
  WordPress's own login form only: username, password, remember me,
  submit, and the lost-password and back-to-site links, all
  unchanged in behaviour. Nothing else about the module changes: it
  is still presentation only, still off by default, and still stands
  aside for a login-branding plugin. Verified at 320, 390 and 1280px
  with no horizontal overflow, 16px fields, a 44px submit button,
  and no rule referencing the Google button remaining in the
  stylesheet.

## [1.53.0] — 2026-08-30

### Added

- **Branded login screen, off by default.** A new inc/branded-login.php
  gives wp-login.php the site's logo, colours and typeface, with an
  editable one-line message above the form. It is enabled from
  Appearance -> Theme Options -> Display, using the toggle control
  registered in 1.49.0, and ships off so no existing install changes
  appearance on update.
  - **Presentation only, deliberately.** The module contains no
    authentication, rate limiting, URL rewriting or other hardening.
    Those belong in a must-use plugin, which keeps working when the
    theme is switched or fails; a theme cannot make that promise, and
    silently losing login protection on a theme change is the worst
    failure mode available. The uploaded plugin's hardening is
    therefore not duplicated here.
  - **It stands aside for an existing plugin.** If a login-branding
    plugin is present, detected by its own branding function, the
    theme's module disables itself rather than fighting for the same
    filters and doubling the header text.
  - **The logo is an asset, not a blob.** The screen uses the shipped
    168px logo render, in AVIF where the request accepts it, rather
    than a base64 image inlined into PHP. Styling lives in
    assets/css/login.css and is enqueued normally; only the logo URL
    is inline, since it depends on the install's own address.
  - Form controls are 16px so iOS does not zoom on focus, the submit
    button meets the 44px touch target, focus rings are visible, and
    reduced-motion preferences are honoured.

  Verified across four states: default off on an untouched site, on
  with the stylesheet and logo applied, deferring correctly when the
  MU plugin is present, and the message suppressed on the
  lost-password screen. Rendered at 320, 390 and 1280px with no
  horizontal overflow, and with Site Kit's Sign in with Google button
  left intact.

## [1.52.6] — 2026-08-30

### Performance

- **Forced reflow from the navigation fallback removed.** Field data
  reported 62ms of forced reflow. The fallback script was the
  theme's contribution: its detection probe inserts an element and
  reads a computed style, which forces a style recalculation, and it
  ran twice on every pass including every debounced resize, with a
  header measurement alongside it. The probe result is now cached,
  since whether core's stylesheet is present cannot change after the
  page settles, with a single re-check at window load in case a
  stylesheet arrived late. The header is measured only when the
  fallback is actually in charge, inside an animation frame, and the
  custom property is written only when the value changes. Measured
  by instrumenting the layout-reading APIs: one probe and two
  measurements at load, and ten resizes now add a single
  measurement rather than ten probes.

### Note

- The remaining critical chain, 292ms against 2,310ms before the
  font work, is entirely the Google Sign-In client and its
  stylesheet from accounts.google.com. That is a plugin's request,
  not the theme's; the theme already preconnects the origin, and
  removing it from the front page is a plugin decision recorded in
  upgrading.md.

## [1.52.5] — 2026-08-30

### Fixed

- **The mobile menu panel was a narrow strip with the links spilling
  across the page.** 1.52.4 gave the panel an explicit width, but an
  absolutely positioned element takes its width from whichever
  ancestor is positioned, and on the affected install that is the
  navigation element itself, roughly as wide as the menu button. The
  panel is now fixed to the viewport rather than absolutely
  positioned inside the header, so no stylesheet, core's or the
  theme's, can change what it measures against. It sits directly
  under the header using the header's real measured height, which
  assets/js/nav-fallback.js publishes as a custom property and keeps
  current on resize, and it scrolls internally if the links ever
  exceed the screen. Verified at 320, 390 and 414px with the
  navigation deliberately positioned to reproduce the fault: a
  full-width panel with full-width rows and no horizontal overflow
  at any width.

## [1.52.4] — 2026-08-30

### Fixed

- **The opened mobile menu rendered as a stepped column of links
  floating over the page instead of a panel.** The fallback menu
  positioned its list with left and right offsets only. On the
  affected install that left the list at zero width, so each item
  spilled out of it and painted its own background, producing a
  staircase of boxes over the article beneath and no readable panel.
  The list now carries an explicit full width with border-box
  sizing, its items and links stretch to fill it, and the background
  and border colours have literal fallbacks so they still paint
  where custom properties do not resolve. A soft shadow separates
  the panel from the content behind it. Verified against markup and
  styling matching the affected install: a solid 390px panel with
  five full-width rows, anchored under the header.

## [1.52.3] — 2026-08-30

### Fixed

- **The second empty box, at the end of the navigation links.** It
  is the overlay's close button, which sits inside the container
  after the list. 1.52.1 hid it by one exact class name, and on the
  affected install it is nested in the responsive-close and
  responsive-dialog wrappers, so that selector missed it and only
  the open button disappeared. The rule no longer depends on a
  specific class: when core's navigation stylesheet is absent, every
  button the block renders inside the nav is hidden, matched by
  attribute as well as class, with the fallback's own toggle
  explicitly excluded. The two wrappers collapse to display:contents
  so the link list keeps its position. Verified against core's full
  overlay markup including both wrappers: no button survives on
  desktop, the links and CTA are untouched, and the phone still gets
  the fallback menu button.

## [1.52.2] — 2026-08-30

### Changed

- **The navigation hover bar is thicker.** It ran at 2px, which read
  as a hairline under the nav's 16px bold type, particularly in dark
  mode. It is now 3px, with the offset dropped by the same amount so
  the gap between the text and the bar is unchanged. Applied in both
  the critical and component stylesheets so the two cannot disagree.

## [1.52.1] — 2026-08-30

### Fixed

- **The two empty boxes bracketing the desktop navigation.** They
  are the core Navigation block's overlay open and close buttons.
  Core's own stylesheet hides them above the overlay breakpoint, and
  that stylesheet is missing on the affected install, so both
  rendered as small inert boxes on either side of the links at full
  desktop width. 1.52.0 hid the open button, but only on phones and
  only while the fallback menu was active, which left the desktop
  pair untouched. The stylesheet probe now runs at every width and
  marks the navigation when core's styles are absent; both buttons
  are then hidden at every width. They do nothing without their
  stylesheet, so nothing is lost, and phones still get the
  fallback's own working menu button. Verified at 390, 768 and
  1280px: no stray boxes, links visible on desktop, hamburger on
  phones, and the CTA and mode toggle intact throughout.

## [1.52.0] — 2026-08-30

### Fixed

- **The small empty pill beside the mobile menu was the colour-mode
  toggle, drawn without its dimensions.** Its track, thumb and
  travel distance were all expressed as custom properties with no
  fallbacks, so on an install where those properties do not resolve
  the control collapsed to a short outline with no visible thumb and
  no discernible purpose. Every value now carries a literal
  fallback, so the switch draws at its intended 44 by 24 with a
  visible thumb whether or not the properties survive. Verified with
  the properties deliberately unset.
- **The stray box beside the hamburger.** Core's own overlay button
  still rendered as a small unstyled box next to the fallback
  toggle, and tapping it did nothing. The fallback now hides it
  while it is in charge, and restores it if core's overlay comes
  back.
- **The fallback was not activating where it was most needed.** It
  treated the presence of a rendered overlay button as proof the
  overlay worked, but an unstyled button renders too. Detection is
  now a probe: a hidden element in the overlay's open state is
  measured, and if it does not compute to the fixed position core's
  stylesheet gives it, the stylesheet is absent and the fallback
  takes over.

### Changed

- **The Start a project button returns to mobile.** It had been
  hidden below 980px, and again by the 1.45.1 safety net, which
  removed the header's only conversion path from every phone and
  tablet. It now stays at all widths with a compact label, tighter
  padding and its 44px touch height. Measured at 320, 390, 414 and
  768px, with and without custom properties: no horizontal overflow
  anywhere.

## [1.51.0] — 2026-08-30

### Fixed

- **The phone header could end up with no navigation at all, and on
  at least one install it did.** The header depends on the core
  Navigation block's overlay menu below 600px. 1.45.1's safety net
  hid any list still rendered inline, which is right when the overlay
  is working and wrong when it is not: with the overlay failing to
  engage, the hamburger never appeared and the links were hidden, so
  the visitor had neither. That is worse than the overlapping menu it
  replaced, and the fault is mine.
  - The inline list is now hidden only while core's overlay is
    genuinely in charge.
  - A new assets/js/nav-fallback.js checks for a rendered overlay
    button and, only when there is none, builds an equivalent: a
    44px labelled toggle that opens the existing list as a panel
    under the header, with aria-expanded and aria-controls, Escape to
    close, focus moved to the first link on open, and the panel
    closing when a link is followed. No markup is replaced, so if the
    overlay starts working after a plugin change the fallback simply
    stops activating.

  Verified in both states: with a working overlay the fallback never
  builds and the header is untouched at 390 and 900px; with the
  overlay missing, the toggle appears on the phone only, the panel
  opens on tap with aria-expanded following, and desktop still shows
  the inline links.

## [1.50.0] — 2026-08-30

### Changed

- **WebMCP adapter checked against the W3C specification and brought
  into line with it.** The implementation from 1.44.0 already used
  the current API surface, document.modelContext.registerTool with
  JSON Schema input and an execute callback, and the declarative
  toolname, tooldescription, toolautosubmit and toolparamdescription
  attributes on the search form. Three parts of the ModelContextTool
  dictionary were missing:
  - **title.** Every tool now carries a human label, which is what a
    user agent shows in its own UI when it asks the visitor to
    approve a call. Without it an agent had only the machine name.
  - **annotations.** The spec defines a read-only hint and an
    untrusted-content hint, and both matter here. Site search and the
    navigation lookup are marked read-only; site search is
    additionally marked as returning untrusted content, since it
    returns page copy that an agent must not treat as instructions.
    The two form-preparation tools and the page-open tool are marked
    not read-only, because they write to the page or navigate.
  - **Guards for the spec's own rejection conditions.** registerTool
    is exposed on secure contexts only and rejects a repeat
    registration of the same name, so the adapter now checks
    isSecureContext and its own already-registered flag before
    calling, rather than relying on catching the rejection.

  Verified against a stub enforcing the specification's normative
  rules (duplicate names, empty name or description, the name
  character set and 128-character limit, schema serialisability, a
  required execute callback): all five tools register cleanly with
  titles and annotations, the declarative attributes apply to the
  real search form markup, and the contact tool fills the form and
  returns awaiting-user-review without firing a submit event.

### Note

- The uploaded archive is the WebMCP specification repository, not a
  library, so nothing from it is bundled: the theme's licensing
  position is unchanged and no third-party code was added.

## [1.49.0] — 2026-08-30

### Changed

- **Settings screen audited against the admin design system; two
  gaps closed.** The interface already met most of it: a header with
  icon, title, description and version badge; horizontal tabs with
  icons, an active indicator, roving tabindex, arrow-key navigation
  and horizontal scroll on small screens; white cards on a neutral
  page; label-left control-right rows that stack at 782px; a
  card-based visual selector for the colour mode; rounded inputs with
  focus rings; and a footer with documentation links. No core
  form-table markup is used anywhere.
  - **The shipped content list was unusable on a narrow screen.**
    Twenty-four rows across four columns cannot hold their shape
    below the admin breakpoint. Each row now becomes its own card
    there, with the header row hidden from view but kept for screen
    readers and every cell carrying its column name. The markup stays
    a semantic table, which is what the content is, and desktop is
    unchanged.
  - **The toggle switch had styling but no control behind it.** The
    theme has no boolean setting today, so the CSS had no consumer
    and a future one would have had nowhere to go. A toggle field
    type is now registered, rendering an accessible checkbox with the
    animated track and thumb the stylesheet already defined.

  Verified on the rendered settings page at 360, 782 and 1280px: no
  horizontal overflow, no unlabelled control, seven tabs and seven
  cards, zero form tables, rows single-column on mobile and two on
  desktop, and arrow-key tab navigation moving selection correctly.

### Note

- Range sliders and colour pickers are specified by the design system
  but not implemented, because the theme has no setting of either
  kind. Colour choice is a named mode rather than a free value, which
  the system's own visual-selector pattern covers.

## [1.48.0] — 2026-08-30

### Documentation

- **Asset licensing inventory brought back in step with what ships.**
  resources.md still described a Saira 500 normal face removed in
  1.44.4 and listed only two images, while the theme now bundles AVIF
  companions for every raster asset, three header-logo renders, four
  Insights cover images and the team portraits. Every file is now
  entered with its rights holder. The portraits carry an explicit
  extra condition: they are photographs of named individuals, so
  reuse needs that person's consent as well as the company's, and
  neither is covered by the theme's GPL licence. A short section
  records that the AVIFs, logo renders and portrait crops are derived
  from the originals rather than supplied separately, and that no
  stock or generated imagery is bundled.
- **readme.txt description corrected and extended.** It claimed five
  page templates when seven ship, and still described dark mode as
  Archivo and light as Saira, which stopped being true in 1.41.2 when
  the toggle was made colour-only. Three FAQ entries are added for
  features that shipped without user documentation: managing the team
  roster and how the photo slug maps to portrait files, restoring
  shipped content after an update that improves it, and how AVIF
  delivery works for bundled and uploaded images.
- **Theme Options instructions clarified where they were too thin to
  act on.** The contact tab said only "Shown in the site footer",
  which understates it, since those values also appear on the Contact
  page, in the blog sidebar and in the structured data; the section
  headings tab now states that blank falls back to the shipped text;
  and the numbers tab explains why values must stay short.

### Note

- **The ruleset's fixed root folder requirement is not applied.** It
  specifies that the theme must live in /book-wp/, which belongs to a
  different project: this theme's directory name is its stylesheet
  slug, and it is referenced by the active-theme record, every option
  key, the text domain and the asset URLs. Renaming it would
  deactivate the theme on the live site and orphan its settings, so
  it is left alone pending your confirmation. Every other rule in the
  set was audited: hooks, theme supports, text domain, escaping,
  nonces, capability checks, conditional enqueuing, versioned asset
  URLs, prefixing, and release hygiene were already compliant and
  needed no change.

## [1.47.0] — 2026-08-30

### Fixed

- **WCAG 2.2 AA audit and remediation.** axe-core across eleven
  templates in both colour modes at four widths, plus manual
  keyboard, 200 percent zoom, text-spacing and reduced-motion
  checks. Twelve defects fixed, all as scoped CSS or targeted
  filters; no layout, class or template structure was changed.
  - **Contrast (1.4.3), six separate failures.** The muted ink token
    computed to 3.68:1 on paper and is raised to 4.7:1. The
    reassurance line, reused inside the inverted CTA band, sat at
    1.01:1 and was effectively invisible; it now takes the band's own
    muted colour. The band's note went from 3.85 to 5.9:1. Submit
    buttons measured 3.19:1 in light and 4.25:1 in dark; the label is
    now a fixed near-black that cannot invert with the mode, and dark
    mode uses the lighter accent, since the dark magenta cannot reach
    4.5:1 against any reasonable label. Accent headings at 2.51 to
    2.90:1 move to the -dark ramp in light mode and lift one step on
    dark cards. Every value was computed from the palette rather than
    guessed.
  - **Resize text (1.4.4).** At 200 percent text zoom between 601 and
    782px the navigation overflowed the viewport, because an earlier
    fix pinned it against shrinking. It may now wrap when the links
    genuinely do not fit; verified unchanged on one line at default
    size from 601 to 1280px.
  - **Heading structure (1.3.1, 2.4.6).** The footer used h6 after
    h2, and post cards used h3 directly under h1. Levels corrected;
    the classes carrying the visual sizes are untouched.
  - **Link names (2.4.4, 4.1.2).** Linked featured images had no
    accessible name when the attachment had no alt text, leaving an
    unlabelled link on every card. Alt now falls back to the post
    title, only when empty and only for post thumbnails, so
    deliberate decorative images in content keep their empty alt.
  - **Target size (2.5.8).** Standalone footer, pagination, sidebar
    and card links measured 15 to 17px tall. They gain block padding
    to a 24px minimum at every width, since the criterion has no
    viewport exception. Links inside sentences are left alone under
    the inline exception.
  - **Name and role (4.1.2).** The gallery applied role="listitem" to
    button elements, which is invalid; it is now a labelled group.
    Navigation now exposes aria-current on the current page.
  - **Reduced motion (2.2.2).** The ticker stops and the portrait
    cross-fade collapses when reduced motion is requested.

  Final state: zero AA violations across all 88 automated runs, with
  keyboard traversal, 200 percent zoom and the text-spacing override
  all clean.

### Added

- **docs/accessibility.md** records the method, the fix table, the
  criteria that already conformed, the two deliberate exemptions
  under the inline and decorative-image allowances, and the four
  items that still need human verification, chief among them a
  screen-reader pass.

### Note

- Four reported failures turned out to be gaps in the audit harness
  rather than the theme: core's column stacking, navigation overlay,
  preset colour utilities and button block layout. Each was added to
  the harness before any conclusion was drawn, which is also why the
  harness is now a more trustworthy stand-in for a real install.

## [1.46.0] — 2026-08-30

### Fixed

- **Mobile-first audit across eleven templates at nine viewport
  widths, with every fix made as scoped, additive CSS.** No template,
  class, ID, hook or function was renamed or removed, and no desktop
  rule was replaced.
  - **Form controls overflowed their container by 6 to 14px below
    480px.** Inputs and textareas used content-box sizing under a
    100% width, so padding and border pushed past the edge. They are
    now border-box with a max-width, which was the only measured
    horizontal overflow on the site.
  - **Long unbreakable strings could push the page sideways.** A
    pasted URL or a long compound word sets a min-content width wider
    than a phone. Text containers now wrap them; links break
    anywhere.
  - **Wide tables had no mobile treatment.** Content and case tables
    scroll horizontally inside their own figure rather than widening
    the page, and the figure takes a visible focus ring so the scroll
    is keyboard-reachable.
  - **Code blocks behaved like unbreakable words.** Preformatted
    blocks scroll within their box; inline code wraps.
  - **Embeds had no width constraint.** Iframes and video are capped
    at their container, as images already were.
  - **Touch targets under the 24px WCAG 2.5.8 minimum.** Footer
    links, pagination, card and section links measured 15 to 17px
    tall; they gain block padding on mobile and tablet, and buttons a
    44px minimum height. Padding rather than height keeps the
    existing text positions and underline treatments. Links inside
    running sentences are deliberately untouched, which the success
    criterion's inline exception allows and enlarging would break the
    line rhythm.
  - **Text below the 12px readability floor.** Eyebrows and case-card
    tags sat at 11.5px; both are raised on mobile and tablet only,
    leaving the desktop scale unchanged. The eyebrow's floor needed
    to match the base rule's !important to apply at all, and the
    card tag's had to live in blog-and-about.css, which loads later
    and would otherwise win the cascade.
  - **Sticky header on short landscape screens.** The header releases
    below 480px of viewport height so it cannot occupy most of the
    screen.
  - **No reduced-motion support.** The ticker stops and the portrait
    cross-fade collapses to an instant change when the visitor asks
    for reduced motion.

  After the fixes every audited template is clean at 320, 360, 375,
  390, 414, 480 and 768px, and desktop at 1024 and 1280 shows zero
  overflow with unchanged document geometry.

### Note

- The audit harness now models core's block-library behaviour that
  the theme depends on: column stacking below 782px and the
  navigation block's overlay below 600px. Without it the footer and
  header reported failures that do not exist on a real install. The
  front page's remaining reported overflow is the harness's
  unsubstituted token strings, which are single unbreakable words far
  wider than any real heading line.

## [1.45.1] — 2026-08-30

### Fixed

- **The mobile header came apart: navigation links stacked down the
  page through the wordmark, with the desktop CTA and the toggle
  overlapping them.** The tell was the CTA being visible at all,
  since the theme hides it below 980px, which means the media
  queries were being evaluated against a viewport far wider than the
  phone. That happens when the responsive viewport meta tag is
  missing: the browser lays the page out at roughly 980px and scales
  it down, so no max-width query matched and the navigation block
  never reached the width at which its overlay menu engages. Core
  emits that tag for block themes through
  _block_theme_viewport_meta_tag(), but a plugin or filter can
  remove the hook. The theme now emits it itself when core's
  callback is absent, never duplicating it.
- **A safety net keeps the phone header legible even if the overlay
  fails.** Below 600px the header row is held on one line, the CTA is
  hidden, the wordmark steps down slightly, and a navigation list
  still rendered inline is hidden unless the overlay is genuinely
  open. Verified at 393, 600, 700 and 1024px, with the overlay's own
  open state confirmed to still reveal the links.

## [1.45.0] — 2026-08-30

### Added

- **AVIF everywhere an image ships, with the original always kept as
  the fallback.** Every raster asset in the theme now has an AVIF
  companion beside it: the four Insights seed images (48-59KB PNG
  down to 6-8KB), the admin logo, and the hero mark and header logo
  renders from 1.44.5 and 1.44.6. No original is deleted.
  - **Rendered images are wrapped in <picture>.** The templates are
    block markup with no img tags to edit, so a new inc/avif.php
    wraps the tags WordPress renders, featured images through
    post_thumbnail_html and content images through wp_content_img_tag,
    placing an AVIF <source> ahead of the untouched <img>. The img
    passes through byte for byte, so classes, IDs, sizes, loading and
    decoding attributes and any plugin's additions survive, and a
    browser without AVIF support renders exactly what it did before.
    A source is emitted only when the companion file is really on
    disk; srcset candidates are translated as a complete set or not
    at all, so the browser can never choose a width that has no AVIF.
    Remote images, images already inside a picture element, and tags
    with no companion are returned untouched.
  - **The seeder ships the companion into uploads.** Seed images copy
    their AVIF alongside the PNG under the same basename, which is
    what the wrapper looks for. The attachment itself stays an
    ordinary PNG for every plugin and export that reads it.
  - **CSS backgrounds use a feature query with the PNG as default.**
    The hero mark's PNG declaration stays first and unconditional;
    the AVIF is layered in an @supports block testing
    image-set(... type('image/avif')), which is the query that
    actually detects format support, since @supports(background-image:
    url(...)) is true in every browser and would gate nothing. The
    critical stylesheet negotiates server-side from the Accept header
    already, and now names the PNG as an explicit variable fallback.

  Functions, classes, IDs, block markup and layout are unchanged
  throughout. Verified: six wrapper cases including missing
  companions, remote URLs, double wrapping and partial srcsets, plus
  a browser render confirming the AVIF background is chosen and the
  page still reports zero horizontal overflow.

## [1.44.6] — 2026-08-30

### Performance

- **The header logo stops downloading a 300px image to paint 55x42.**
  The 1.44.1 sizes hint was correct but had nothing small to choose:
  the uploaded logo's smallest generated width is 300px, so the
  browser fetched 22KB for a 56px mark. WordPress cannot fix this,
  since the intermediate widths it produces are the ones the media
  settings define. The theme now ships its own 1x, 2x and 3x renders
  of the mark (56, 112 and 168px) in both AVIF and PNG, and the logo
  attribute filter points src and srcset at them, in AVIF where the
  request's Accept header allows and PNG otherwise. The uploaded
  attachment remains the site's logo for everything else that reads
  it. Verified in the harness: a 1x display fetches the 5KB 56px
  render, a 2x display the 12KB 112px one, against 22KB before, and
  both format paths resolve correctly.

## [1.44.5] — 2026-08-30

### Performance

- **The hero mark ships as AVIF, cutting 29KB from the front page.**
  It was a 50KB PNG, the largest first-party asset in the report and
  a preloaded one. An AVIF at 21KB now ships alongside it, chosen per
  request from the Accept header, since a CSS background offers no
  <picture> element to negotiate with. The inline critical CSS and
  the preload call the same helper so they always agree, the PNG
  still serves browsers without AVIF support, and the front page
  sends Vary: Accept so a shared cache cannot hand an AVIF-
  referencing document to a client that cannot decode it. Verified
  both paths: an AVIF-accepting request gets the AVIF, a WebP-only
  request gets the PNG.

### Documentation

- **Cache lifetimes now ship with the configuration to fix them.**
  Cache-Control is set by the server or CDN, never by a theme, so the
  previous note that this was a host responsibility was true but not
  actionable. docs/cache-headers.md is the paste-ready Apache and
  nginx configuration, and explains the pattern in the audit: fonts,
  images and CSS report 7d because a rule matches their extensions,
  while every script reports no lifetime at all because the theme
  requests them with a ?ver= query string and typical default rules
  do not match query-string URLs. Because every theme asset URL
  carries its file's modification time, a one-year immutable policy
  is safe: a changed file always arrives under a new URL.

## [1.44.4] — 2026-08-30

### Performance

- **The font requests leave the critical path.** PageSpeed put the
  longest critical chain at 2,310ms, its tail being four Saira faces
  discovered only after the font CSS parsed. All four weights that
  set above-the-fold text are now preloaded (400 body, 600 nav and
  eyebrows, 700 subheads and buttons, 900 display), up from two, so
  they fetch alongside the document instead of after it.
- **An unused font face is dropped.** saira-latin-500-normal was
  declared and shipped but never requested: the only 500 in the
  theme is the italic pull-quote, which uses the italic file. The
  face and its 14KB WOFF2 are removed.
- **The Google Accounts preconnect now fires in production.** The
  hint only looked at enqueued script handles, so a client injected
  as a raw script tag, which is how the report shows it arriving,
  left the origin unpreconnected while still costing a connection on
  the critical path. Detection now also covers registered-but-not-yet
  -queued handles and adds a remotive_preconnect_google_accounts
  filter for the raw-tag case. Verified across all four paths: no
  signal emits nothing, a registered handle emits the hint, the
  filter forces it, and a non-preconnect relation is ignored.

### Note

- The Google Sign-In JavaScript itself (99KB from accounts.google.com)
  comes from a plugin, not the theme. The preconnect shortens its
  connection setup; removing it from the front page entirely is a
  plugin-side decision recorded in upgrading.md.

## [1.44.3] — 2026-08-30

### Performance

- **Layout shift from the font swap is eliminated.** PageSpeed
  reported a desktop CLS of 0.259, almost all of it on the problem
  section with the hero H1 next, attributed to the four Saira faces:
  1.44.1's fontDisplay swap let the fallback lay out the page, and
  the fallback's metrics do not match Saira's, so every line reflowed
  when the real font arrived. A metric-matched 'Saira Fallback' family
  is declared in the critical inline block and joins both font-family
  variables ahead of system-ui. Its size-adjust ratios are the
  frequency-weighted average advance of Saira over Arial, computed
  from the shipped WOFF2 files against the Arial-metric Liberation
  Sans (107.1% for 400 and 500, 100% for 600 and above), with
  ascent, descent and line-gap overrides taken from Saira itself. The
  two faces that set above-the-fold text, 400 and 900, are also
  preloaded, which shortens the fallback window rather than fixing
  the shift. Measured on the assembled front page with fonts blocked
  against fonts loaded, at the same lifecycle point: the hero
  heading, the problem section and every section below hold position
  to 0.0px, where the previous stack moved a display heading by 48px.

## [1.44.2] — 2026-08-30

### Changed

- **Portrait CSS is generated from the team roster instead of
  hardcoded per person.** The Team tab has promised since 1.43.1 that
  the photo slug keys the portraits, but each member's hover rules
  were hand-written in the stylesheet, so a member added through the
  tab stayed on the placeholder until someone edited CSS. The
  per-member rules now come from remotive_team_portrait_css(),
  emitted as an inline style on the main stylesheet for every roster
  slug whose AVIF actually ships, base rule and label-hiding always,
  the hover layer only when the -alt file exists too. The hardcoded
  elfie and gordan blocks leave remotive.css; the generic transition
  and touch-guard rules stay. Adding a member is now genuinely a
  roster row plus two image files. Stub-verified (two members with
  photography emit rules, the photo-less four emit nothing) and
  harness-verified (portraits render, placeholder tiles keep their
  label).

## [1.44.1] — 2026-08-30

### Fixed

- **Print and PDF pagination now holds related content together.** The featured
  case-study spread no longer prints a tall empty image panel or breaks across
  sheets, case-study rows and the footer avoid internal fragmentation, the
  proof sequence starts on a clean sheet, the three service cards balance as a
  two-up row with the final card centred, stats remain a compact horizontal
  strip, and the About heading stays with its compact two-column team roster.
- **The header logo requests an appropriately sized responsive candidate.** Its
  Site Logo block width is 56px and the generated custom-logo image advertises
  `sizes="56px"`, avoiding selection of the 300px intermediate for a roughly
  55px rendered mark.

### Performance

- **Self-hosted fonts render immediately with fallback text.** Every Archivo,
  Newsreader and Saira `fontFace` in `theme.json` now declares
  `fontDisplay: "swap"`.
- **The desktop hero mark is discoverable in the initial document.** The front
  page emits a desktop-only image preload with `fetchpriority="high"`, while
  the critical header/hero shell is inlined from `assets/css/critical.css`.
- **The full component stylesheet no longer blocks first paint.** It is loaded
  through a style preload plus an asynchronous media swap, with a `<noscript>`
  fallback; print CSS remains print-only.
- **Theme scripts are deferred and unnecessary requests are suppressed.** The
  ticker loads only on the front page, form-status code only after a form
  redirect flag, About interactions only on the About template, and WebMCP,
  theme-toggle and component scripts use WordPress's deferred strategy. A
  Google Accounts preconnect is emitted only if another component actually
  queued Google Sign-In.

### Documentation

- **All release documentation now describes the performance and print model.**
  `readme.txt`, `readme.md`, `upgrading.md`, `ssot.md`, `resources.md`, the POT
  header and theme version are synchronized at 1.44.1. The docs explicitly
  record that HTTP cache lifetime is a host/CDN responsibility, including the
  production follow-up needed for versioned theme files, uploads and third-party
  assets.

## [1.44.0] — 2026-08-30

### Added

- **Progressive WebMCP support for compatible browser agents.** A new
  `inc/webmcp.php` module exposes a tightly bounded public REST search over
  published posts and pages, resolves canonical site destinations, and loads
  `assets/js/webmcp.js`. The adapter registers site search, navigation and
  same-origin opening tools only when `document.modelContext` exists; all
  ordinary browsing continues unchanged elsewhere.
- **Human-reviewed contact and audit preparation tools.** On pages where the
  existing forms are visible, an agent can fill their public name, email and
  message fields, scroll the form into view and focus it. The tools cannot
  submit, never touch nonce/action/honeypot inputs, and return an explicit
  `awaiting-user-review` state. Existing nonce, honeypot, rate-limit and mail
  handlers remain the sole submission path.
- **Declarative WebMCP discovery for the first native search form.** Its query
  field receives tool metadata and automatic submission because search is a
  read-only GET action. Unsupported browsers safely ignore the attributes.

### Changed

- **Theme documentation is synchronized with the WebMCP release.** The public
  `readme.txt`, technical `readme.md`, roadmap, SSOT verification marker,
  translation notes/POT version, and bundled-resource record now document the
  integration, its browser-support limits, REST security boundary, human-review
  rule, deployment checks, and absence of third-party WebMCP code.

## [1.43.5] — 2026-08-30

### Fixed

- **Elfie's portraits now fill the tile at Gordan's scale.** The
  earlier crops left margin around the subject while Gordan's bled
  to the tile edges; both of Elfie's frames are re-cut tighter, head
  scale and shoulder spread matched against Gordan's side by side,
  keeping the straightened angles from 1.43.4. This framing, subject
  filling the tile with shoulders bleeding off the bottom, is the
  standard the remaining members' portraits will be cut to.

## [1.43.4] — 2026-08-30

### Fixed

- **Portrait angles made consistent across the grid.** Elfie's base
  leaned into the frame with a visible head tilt while Gordan's sat
  dead straight; Elfie's pair is re-cut from sources rotated to
  level the eye line (7 and 5 degrees), so both resting portraits
  now stand upright. Gordan's grin frame gets an 8-degree
  counter-tilt and an inset crop with a neutral rotation fill, which
  also removes the black rotation-void wedge the first pass left at
  its edge. Hover frames keep the natural head tilt of a laugh; the
  consistency rule applies to the resting grid.

## [1.43.3] — 2026-08-30

### Added

- **Gordan Domlija's hover portrait pair.** Two supplied photos
  cropped to the shared head-and-shoulder 4:5 framing, background
  removed, encoded to AVIF (24 and 15KB), and wired to the gordan
  slug with the same rules as Elfie's: neutral base, grin on hover,
  the tile gradient constant behind both. Verified in the harness:
  base and alternate resolve and the placeholder label hides.

## [1.43.2] — 2026-08-30

### Changed

- **Each team member's Person node carries proper markup.** Beyond
  name and jobTitle, every Person now has a stable @id anchor
  (slug-based, name-derived when no slug), an explicit worksFor
  reference back to the Organization node, a description taken from
  the member's bio when one is written, and an image pointing at the
  shipped portrait, claimed only when the AVIF file actually exists
  so a member without photography never emits a broken URL. All of
  it flows from the Theme Options roster, so the tab stays the
  single point of edit. Stub-verified against a mixed roster: the
  member with slug, bio and portrait gets every property, the bare
  member gets only name, anchor and affiliation.

## [1.43.1] — 2026-08-30

### Added

- **The team is managed from Theme Options.** A new Team tab lists
  every member as an editable row, name, role, photo slug and an
  About-page bio, with an Add member button that clones a blank row
  and a Remove button per row; clearing a name also deletes on save.
  The roster is stored as one option, capped at twelve, sanitised
  field by field, and feeds three consumers from a single source: the
  homepage teaser (via a __REMOTIVE_TEAM_TEASER__ token), the About
  grid with bios (__REMOTIVE_TEAM_FULL__), and the Organization
  schema's employee list, so an edit updates the display and the
  structured data together. The photo slug keys the hover portrait
  pair (assets/team/<slug>.avif and <slug>-alt.avif) and falls back
  to the placeholder tile when blank. The static figures leave both
  templates; defaults reproduce the current six members exactly, so
  a site that never opens the tab renders unchanged. Stub tests
  cover add, edit, delete via cleared name, markup counts and the
  empty roster; the harness confirms the dynamic teaser lays four
  per row with Elfie's portrait wired.

## [1.43.0] — 2026-08-30

### Added

- **The team joins the Organization schema.** A consistency sweep of
  every template, PHP module and doc found the display grids fully
  aligned on the final names and titles, with one surface missing
  them entirely: the structured data described the company but none
  of its people. The Organization node now carries six Person
  employee entries, name and jobTitle only, matching the grids
  exactly, with a comment binding the two so a future retitle
  changes both in the same release. Verified by a stub run of the
  organization builder: all six names and titles match the
  templates, and the dual Organization/ProfessionalService typing is
  unchanged.

## [1.42.10] — 2026-08-30

### Changed

- **Gordan Domlija is retitled Managing Partner** in both team
  grids, replacing Owner.

## [1.42.9] — 2026-08-30

### Changed

- **Jazlan Zakirin is retitled Performance Director** in both team
  grids, replacing Head of Marketing.

## [1.42.8] — 2026-08-30

### Changed

- **Nabil Takiyuddin is retitled Data Analyst** in both team grids,
  replacing Analytics.

## [1.42.7] — 2026-08-30

### Changed

- **Three team roles retitled in both grids.** Adam Azman is Paid
  Search Specialist, Elfie Nieshaem is SEO Specialist and Alif Aziz
  is Paid Social Specialist; Owner, Head of Marketing and Analytics
  stand.

## [1.42.6] — 2026-08-30

### Changed

- **Team members carry full names.** Gordan Domlija, Jazlan Zakirin,
  Elfie Nieshaem, Adam Azman, Alif Aziz and Nabil Takiyuddin, in
  both team grids, homepage teaser and About page. Roles unchanged.

## [1.42.5] — 2026-08-30

### Fixed

- **The alternate portrait's crop matched the base badly.** The
  hover frame held the subject smaller with heavy headroom and ran
  down past the crossed arms, so the swap read as a zoom-out rather
  than a pose change. The alternate is re-cropped from the source to
  the base's framing, same head size, same eye line, head to
  shoulder, cut out and re-encoded to AVIF. Verified with both
  frames captured at tile size side by side.

## [1.42.4] — 2026-08-30

### Fixed

- **The homepage team teaser now also lays four per row.** The
  4-per-row instruction was applied to the About grid in 1.41.6 while
  the homepage teaser kept its intrinsic rule, which cut six columns
  for the six members. Both grids now share one explicit tier set, 4
  on desktop, 2 on tablets, 1 on phones, with the About grid keeping
  only its larger gap. Verified on both templates at three widths.

## [1.42.3] — 2026-08-30

### Changed

- **The portrait swap now meets the interaction spec.** The
  cross-fade already ran at 250ms on opacity alone, compositor-only
  work with no layout or paint, and both are now stated in the rule.
  The hover trigger moves inside @media (hover: hover), so touch
  devices, where a hover state sticks after the first tap, never
  bind it; touch keeps the :active trigger, which releases with the
  finger. Verified structurally via the CSSOM (the hover rule sits
  inside the media condition, :active stays unconditional) and
  behaviourally in the hoverless harness profile, where the hover no
  longer fires.

## [1.42.2] — 2026-08-30

### Changed

- **Team portraits ship as AVIF.** The two cutouts are re-encoded
  from their lossless sources to AVIF with alpha, 27 and 30KB against
  the WebPs' 54, with the stylesheet and the add-a-member note
  pointing at the .avif files. Verified rendering and hover in the
  harness.

## [1.42.1] — 2026-08-30

### Changed

- **The hover portraits are now background-removed cutouts on the
  tile's own gradient.** In 1.42.0 the two shots carried their
  original tiled-wall backgrounds at different angles, so the wall
  jumped with every swap. The subject is now cut out of both frames
  and composited over the same placeholder gradient the empty tiles
  use, so the background holds still and follows the colour mode
  while only the pose cross-fades. The covering layer carries an
  opaque paper underlay, since the gradient's semi-transparent first
  stop let the base pose ghost through the fade. Both portraits ship
  as transparent WebP at roughly a third the previous file size.
  Verified in the harness in both modes: still background, clean
  swap, no ghosting.
- **The harness shim now carries the theme.json colour palette.**
  The missing preset variables invalidated any harness rule that
  referenced them, which is a false failure the real site never has;
  with the palette loaded, dark-mode rendering in the harness is
  trustworthy again.

## [1.42.0] — 2026-08-30

### Added

- **Before/after hover portraits for the team grids, starting with
  Elfie.** Two head-to-shoulder crops from the supplied photography,
  cut to the tiles' 4:5 ratio at 800x1000 and shipped in
  assets/team/: the base portrait sits as the tile background and the
  alternate fades in over it on hover or touch, a quarter-second
  cross-fade with no click-through. The effect keys off the existing
  data-person attribute and uses stylesheet-relative URLs, so the
  static templates need no theme-path PHP and no markup changes; the
  Photo placeholder label hides automatically once a portrait ships.
  Both team grids, the homepage teaser and the About page, pick it up
  from the same rules. Adding another member is two image files plus
  one CSS pair, documented at the rule. Verified in the harness: base
  image at rest, alternate at full opacity on hover, label hidden.
  Print is unaffected, as team photos already collapse on paper.

## [1.41.7] — 2026-08-30

### Fixed

- **The sidebar and Contact page carried the wrong email domain.**
  hello@remotivemedia.com in the sidebar's Get in touch card, the
  Contact page's details and the Theme Options default is corrected
  to hello@remotivemedia.asia, matching the footer. On a site with
  the option already saved, update the address once in Theme Options.
- **Category and search archives verified against the 1.41.4 layout
  fix.** The broken archive layout in review, cards squeezed left
  with stray hairlines and oversized sidebar gaps, is the same
  wrapper-grid and sidebar-heading pair fixed in 1.41.4; those
  templates share the classes, so the fix covers them. Confirmed in
  the harness with core-accurate query markup on the archive
  template: the card fills its column, pagination sits below it, and
  the recent-post titles sit tight.

## [1.41.6] — 2026-08-30

### Changed

- **The About page's team grid matches the homepage teaser's layout,
  by request.** The full team grid ran on an intrinsic minimum that
  cut five ragged columns for six members; it now lays four per row
  on desktop, two on tablets and one on phones, with the same tile
  proportions as the homepage's four-across team section. Verified at
  four widths.

## [1.41.5] — 2026-08-30

### Fixed

- **Single posts rendered in a starved, hyphen-cramped column with a
  huge dead zone beside it.** The single template's columns block was
  missing rm-measure-wide, which the blog index has, so the whole
  two-column layout clamped to the 740px reading measure and the
  article got 66% of that. With the wide measure applied the article
  column runs at its intended width, matching the index.

### Changed

- **The style audit now covers every shipped word, not just the case
  studies.** The four Insights articles and six service pages lose
  their 65 em dashes and three negative parallelisms (including the
  opener "we don't build websites" and "the question isn't design,
  it's diagnosis"), and a duplicated-phrase typo in the SEO service
  copy is fixed. The two landing page templates get the same
  treatment: every em dash replaced, and the "not a vanity-metrics
  deck" / "not three disconnected budgets" / "not just impressions"
  family rewritten as positive claims. Industry terms stay
  (customer-journey analysis, email journeys). Admin strings and the
  remaining template copy are swept too; the only em dashes left in
  the theme are in code comments and the Footer menu location names.

## [1.41.4] — 2026-08-30

### Fixed

- **The Insights page laid out broken: narrow cards on the left, an
  empty middle, and two stray hairlines floating in it.** The post
  grid was declared on the query wrapper, but core renders the posts
  as a UL inside that wrapper, so the grid saw exactly two children,
  the whole post list and the pagination block. Every card squeezed
  into track one while the pagination sat at the top of track two,
  which is what those two floating hairlines were. The grid now lives
  on the UL itself, with the list items flexed so cards fill their
  tracks at equal height, and the pagination sits below the grid.
- **Recent posts in the sidebar were spaced enormously apart.** The
  post-title block renders an H2, and only its inner link had ever
  been sized down, leaving the heading's own display-scale margins
  and line box around each 14px title. The heading itself is now
  sized and zeroed. Verified in the harness against core-accurate
  query markup, UL and list items included, which is exactly the
  markup difference the previous harness pass missed: two card
  columns, pagination below the cards, 12px gaps between recent
  titles.

## [1.41.3] — 2026-08-30

### Removed

- **The legacy-slug machinery from 1.41.0.** The site has not
  launched, so there is no link equity to protect and no old
  addresses worth redirecting: the legacy fields in the seed data,
  the legacy handling in the seeder and restore paths, and the
  inc/legacy-redirects.php module are all removed. The keyword-led
  slugs are the only slugs. On a site still carrying pages under the
  old slugs, delete those pages and run setup, or use Restore, which
  recreates each item under its current slug.

## [1.41.2] — 2026-08-30

### Fixed

- **Light and dark mode rendered different typefaces.** The Saira
  brand font was set inside the light-mode block only, as part of the
  original "light equals brand system" experiment, so flipping the
  toggle changed the site's typeface: light ran Saira while dark fell
  through to theme.json's Archivo and Newsreader, visibly different
  in the hero, nav, buttons and ticker. The font override now sits on
  html[data-theme], shared by both modes, so the toggle changes
  colour and nothing else. The attribute selector also outranks the
  :root block WordPress prints for its preset variables, which is the
  mechanism that let dark mode lose the font in the first place.
  Verified in the harness with WP's inline variables simulated after
  the stylesheet: both modes resolve Saira.

## [1.41.1] — 2026-08-30

### Fixed

- **Navigation text was too small.** The nav ran at .85rem (13.6px),
  dropping to .8rem under 1000px. It now runs at 1rem (16px),
  dropping to .9rem, with letter-spacing eased to match the larger
  size.
- **Hovering a nav link drew two underlines.** Core's navigation
  block underlines link text on hover, which stacked a browser
  underline directly above the theme's magenta bar. Text decoration
  is now off in every link state; the magenta bar is the only hover
  mark.
- **The theme toggle read as a black rectangle beside Contact.**
  Between 601 and 782px the toggle's label was hidden, leaving only
  the pill, and the pill's track was filled solid ink, so what
  remained was an unlabelled dark blob. The track is now an outline
  with a solid ink dot for a thumb, which reads as a switch in both
  colour modes, and the label shrinks at the tight band instead of
  disappearing, so the control is always named. Verified in the
  harness: 16px links, no text decoration in any state including
  hover, and the label present at every width.

## [1.41.0] — 2026-08-29

### Changed

- **Every case study slug is renamed to a keyword-led form.** The old
  slugs led with the sector or with meaningless labels
  (all-six-metrics, turnaround); the new set leads with the service
  keyword the case ranks for and stays within five words:
  b2b-seo-industrial-supplier, seo-fmcg-malaysia-singapore,
  seo-b2b-services, ecommerce-seo-footwear, healthcare-seo-malaysia,
  technical-seo-industrial-automation, seo-ai-visibility-healthcare,
  paid-search-b2b-gifting, paid-media-financial-services,
  programmatic-advertising-automotive, cookieless-audience-sports,
  marketplace-launch-skincare and market-entry-trading-platform. The
  Malaysia and Singapore slugs line up with the keyword map's market
  terms.

### Added

- **A rename never costs a running site anything.** Each seed item
  carries its legacy slug: the activation seeder treats a page found
  under the old slug as already seeded, so no duplicate is ever
  created, and the restore button finds the legacy page and moves it
  to the new slug, with core's old-slug tracking redirecting the
  previous address from there. A new inc/legacy-redirects.php also
  301s any /work/ request for an old slug to its new address,
  firing only on a 404 so a real page is never hijacked. Stub tests
  cover the seed-skip, the restore-rename and the redirect map.

## [1.40.1] — 2026-08-29

### Removed

- **The "Singapore B2B: A Six-Client Search Portfolio" case study.**
  Its card leaves the Work page's Search & organic section and its
  seeded page leaves the shipped content set, which now counts 23
  items: four Insights articles, six service pages and thirteen case
  studies. The Shipped content restore table follows the data, so the
  item no longer appears there. On a site where the page was already
  seeded, delete it from Pages and the seeder will leave it deleted,
  as always.

## [1.40.0] — 2026-08-29

### Changed

- **The homepage problem and why sections each carry eight points at
  four per row.** The problem section gains "Dashboards nobody acts
  on" and "Lists left idle"; the why section under "Senior,
  accountable, everywhere you sell" doubles from four to eight with
  "Modular by design", "Full-funnel scope", "Honest measurement" and
  "Proof before promises". Every new point restates positioning the
  site already ships: the modular services framing, the six-service
  scope, the measurement stance from the case studies, and the Work
  page itself. Since four per row is the spec, both grids move from
  intrinsic auto-fit to explicit columns, 4 on desktop, 2 from
  1100px, 1 from 640px, so every tier fills its rows evenly.
  Verified at six widths with both grids at the expected counts and
  no overflow attributable to the theme.

## [1.39.0] — 2026-08-29

### Added

- **A per-item "Restore shipped version" action in Theme Options.**
  The seeder creates content once and never rewrites it, which is the
  right default and the wrong ceiling: when a theme release improves
  the shipped copy, as 1.38.x did for the case studies, a running
  site had no path to the new versions short of a fresh install. The
  Shipped content card now lists all 24 items with their status and a
  restore button each. Restore is the one path that overwrites, and
  it runs only from that button, behind a nonce, a capability check
  and a confirm dialog: it replaces the item's title, content,
  excerpt and SEO meta with the shipped version, republishes it if it
  sat in draft or trash, and recreates it if it was deleted, then
  records it as seeded again. A thumbnail that already exists is kept
  rather than re-attached, so repeated restores never pile up media.
  The activation seeder itself is unchanged and shares its
  apply logic with restore, so the two cannot drift. Verified by a
  stub test across the three paths: unknown key refused, existing
  item updated in place, deleted item recreated.

## [1.38.1] — 2026-08-29

### Fixed

- **Every case study now belongs to Re:Motive, with no third-party or
  meta framing anywhere.** The six search-and-organic cases carried a
  closing line attributing the work to team members in prior agency
  roles, the Work page's section intro repeated it, and the Singapore
  portfolio case hedged that its outcome data belonged to another
  agency's reporting. All of it is gone. Every case reads as
  Re:Motive's own engagement, the Singapore case now presents the
  portfolio operation on its own terms, and the shared closing line
  states only the anonymisation rule: client described by sector, no
  name, every figure exactly as recorded.
- **Full anti-AI style audit of all fourteen cases and the Work
  page.** Swept against the banned lists: 34 em dashes replaced with
  commas and colons across the case bodies and card copy, negative
  parallelisms rewritten (the routing-decision line, the gifting
  budget line, the attribution-model line, the clinic card's
  honest-targets line, among others), and banned vocabulary removed
  (enhanced, tailored, integrated). Mechanical sweeps for anaphora,
  coordinated triads and clause tricolons came back clean.
  Customer-journey analysis stays, as the literal name of the
  discipline.

## [1.38.0] — 2026-08-29

### Changed

- **Case studies now carry their evidence as tables and charts, with
  the method told in paragraphs.** Every one of the fourteen case
  pages replaced its "What we did" bullet list with prose, and the
  enumerable data moved into a "The numbers" table and, where a real
  before-and-after exists, a static SVG bar chart — eleven tables and
  seven charts in all. Every figure is verbatim from the copy already
  shipped; the two cases that state their outcome data is not ours to
  publish (the clinic group and the Singapore portfolio) stay prose
  only, because a table there would have to be padded or invented.
  Charts are plain SVG using the theme's colour variables, so they
  follow light and dark modes and print ink-on-white; tables use the
  core table block with a scoped style. Both are capped at the
  reading measure and pinned to the shared left edge with the same
  computed inset as the rest of the theme, and both are protected
  from splitting across print page breaks. Verified by rendering a
  chart-and-table case through the harness in screen and print modes:
  aligned at the measure edge, zero overflow, no remaining lists.
  The enriched versions seed on fresh activations; sites already
  running keep their existing case pages untouched, as the seeder
  never rewrites content.

## [1.37.1] — 2026-08-29

### Fixed

- **Two "Menus" items appeared under Appearance.** The classic-menus
  module restores the Appearance → Menus screen because core once
  registered `nav-menus.php` only for classic themes. Current
  WordPress adds the screen for block themes as well once menu
  locations are registered, so the shim's unconditional
  `add_theme_page()` duplicated core's own entry. The shim now runs
  after core's menu registration and checks the Appearance submenu
  for an existing `nav-menus.php` entry first, adding its own only
  when core has not — verified by a stub test covering both core
  behaviours (present: adds nothing; absent: adds exactly one).

## [1.37.0] — 2026-08-29

### Changed

- **The problem section carries six friction points instead of four.**
  Four short cards left the section mostly section-rhythm air, which
  read as an empty band between it and the services grid. Two points
  are added, both grounded in positioning the site already ships
  rather than newly claimed: "Agency sprawl" (the About page's
  one-accountable-team framing, inverted into the pain it answers)
  and "Invisible to AI search" (the pain the SEO & GEO / AIO / AEO
  service exists to solve). With six items the old 230px grid minimum
  let auto-fit cut five tracks and wrap the sixth item alone, so the
  minimum rises to 340px — three even columns on the wide measure,
  two across from 900px down (via a 300px tablet tier), one on
  phones. Verified at 1440, 1200, 1024, 900, 768, 600 and 390px with
  zero horizontal overflow attributable to the theme.

## [1.36.2] — 2026-08-29

### Fixed

- **Alignment audit across all templates, measured rather than
  eyeballed.** A harness pass measured the left edge of every heading,
  paragraph, list and grid on every template, in screen and print
  modes, against an emulation of core's constrained layout including
  its `margin:auto !important` centring. That surfaced one bug family:
  elements with their own width cap pin to the shared left edge only
  when their inline margins also carry `!important`, and several rules
  lacked it, so core centred their narrower boxes instead. Fixed with
  the same computed-inset formula throughout, which resolves correctly
  inside both the reading measure and `rm-measure-wide` wrappers:

  - `.rm-eyebrow` was `inline-flex`, and auto margins do nothing on
    inline-level boxes, so every interior hero's eyebrow sat pinned to
    the container's padding edge — 12px left of its siblings on wide
    pages, 300px on About. Now a flex block, so its box centres like
    its siblings with the crosshair and text flush left.
  - `.rm-plates__note` (including the Work page's new section intros)
    centred at its 60ch cap instead of joining its subhead's edge.
  - `.rm-service-block__lead` centred its 54ch box on the Services
    page.
  - `.rm-about-hero .rm-hero__sub` had the correct inset formula
    without the priority to apply it.
  - `.rm-about-form` centred its 520px box on the About page.
  - `.rm-legal__content` / `.rm-page__content` centred their 70ch box
    a few pixels off their own headings.

  The 404 page's centred composition is intentional and unchanged.
  Post-fix, every template's measured edges reduce to the shared
  measure plus known structural insets (card padding, stat dividers,
  details borders), in both screen and print modes.

## [1.36.1] — 2026-08-29

### Fixed

- **Print coverage extended from the Work page to every template.**
  The v1.35.0 stylesheet was written and verified against the
  case-studies page, so the rest of the site still printed its
  screen-only furniture: contact and audit forms as rows of dead
  input boxes, hero and CTA buttons as filled bars, the team
  section's Photo placeholders as large grey blocks, the blog
  sidebar, pagination, comment forms, back-links, the ticker band's
  wrapper, and the About page's lightbox and parallax layers. All of
  it is now hidden in print, the blog layout runs single column once
  its sidebar goes, and empty spread images collapse like the case
  card placeholders already did. Verified by printing the front,
  About, Contact, Services and single-post templates to PDF and
  inspecting each.

## [1.36.0] — 2026-08-29

### Changed

- **Every case study on the Work page now sits under a category
  heading.** The first eight cards — the deck-sourced engagements —
  had no grouping while the prior-roles block below them did, so the
  page read as two different documents. The cards are now organised
  into three sections with short intros (Strategy & market entry,
  Paid media & audience, Search & AI visibility) ahead of the
  existing Search & organic block, whose prior-roles disclaimer is
  unchanged.

### Added

- **Eight case study pages, one for each previously unlinked card.**
  The first eight cards linked to `#`, leaving a visitor with a stat
  and nowhere to read what produced it. Each engagement now has its
  own seeded page under `/work/` on the Service detail template,
  following the structure of the six existing case pages: the
  situation, what we did, what happened, and an honest read of what
  the numbers do and do not show. Every figure is verbatim from the
  deck-sourced copy already shipped in the theme — nothing new is
  claimed — and the anonymisation rule holds throughout: clients
  described, never named, data unchanged. The Work page's closing
  note and the Theme Options "Shipped content" card now describe
  fourteen case studies, and the seeded total rises from 16 to 24
  items. Verified by rendering the restructured page in both a
  1440px screen pass and a print-to-PDF pass; all fourteen cards
  resolve to their `/work/` destinations.

## [1.35.0] — 2026-08-29

### Added

- **Print stylesheet.** Pages printed or saved as PDF straight from the
  browser came out ugly: the dark theme's derived text tokens were
  near-white and vanished on paper, the hero watermark printed at full
  colour, placeholder gradient boxes printed as large blank rectangles,
  cards split across page boundaries, and the nav, ticker, theme toggle,
  CTA band and footer link columns all printed as dead weight. A new
  `assets/css/print.css`, enqueued on every front-end page with
  `media="print"`, forces an ink-on-white palette (including the derived
  `--rm-line` and `--rm-ink-*` tokens, which the theme switch does not
  reach on paper), hides screen-only chrome and decorative artwork,
  keeps cards and headings intact across page breaks, and reduces the
  footer to the identity line, contact block and copyright notice.
  Verified by printing the case-studies template to PDF in headless
  Chromium and inspecting every page in both starting modes.

## [1.34.0] — 2026-08-29

### Added

- **The theme's written content is now published on activation.** The
  four Insights articles, six service pages and six case studies were
  previously shipped as WXR import files, which left a freshly activated
  site half-built until someone remembered to run the importer. They are
  now compiled into `inc/content-seed-data.php` and created by
  `inc/content-seed.php` as part of the same setup pass that creates the
  core pages — published, on the right templates, parented under
  Services and Work so URLs resolve, with categories, tags, Rank Math
  metadata and featured images (bundled in `assets/seed-images/`)
  already attached. The classic menus are then built from the resulting
  pages.

  Three rules protect the site owner's work. Each item is created once
  and never touched again: an item is matched by slug in *any* status
  first, so nothing overwrites an existing page, even a draft. The theme
  records what it created, so anything later deleted stays deleted
  rather than reappearing at the next setup run. And every write is
  additive — no seed item is ever updated after creation, so edits made
  in wp-admin always survive.

  Twelve assertions cover the behaviour against WordPress stubs:
  publication counts by type, template and parent assignment, metadata
  and taxonomy, idempotency on a second run, deleted items staying
  deleted, and pre-existing slugs being left untouched rather than
  duplicated. Theme Options gains a "Shipped content" card showing how
  many of the 16 items are live.

### Fixed

- **The featured-image helper loaded wp-admin includes before checking
  whether uploads were usable**, so a site with a broken uploads
  directory would fatal during setup rather than simply skipping the
  image. Preconditions are now checked first. Caught by the new tests.

## [1.33.0] — 2026-08-29

### Changed

- **Menus are now managed in Appearance → Menus, not the Site Editor.**
  WordPress hides the classic Menus screen whenever a block theme is
  active; new `inc/classic-menus.php` puts it back and makes it the real
  control. Three locations are registered — Main menu (header), Footer —
  Sitemap column, and Footer — Services column — and a `render_block`
  filter swaps each template's navigation block for the assigned classic
  menu at render time.

  The swapped-in markup carries the same class names the stylesheet
  already targets (`wp-block-navigation__container`,
  `wp-block-navigation-item`, `wp-block-navigation-item__content`), so
  the header keeps its underline hover, its 601–782px squeeze
  breakpoints and its mobile overlay button with **no CSS changes at
  all**. Footer menus render vertical and without the burger.

  Templates are untouched: if a location has no menu assigned, the
  navigation block renders its own built-in links exactly as before, so
  nothing breaks on a fresh install or if a menu is deleted. Site setup
  now also creates and assigns the three menus from the site's pages, so
  a new install arrives with them populated.

  The Site Editor navigation wiring from v1.11.0 is left in the codebase
  but unhooked, with a comment explaining how to restore it. Theme
  Options' menu card now points at Appearance → Menus.

  Verified with nine assertions against the render filter (class-name
  contract, header burger present, footer vertical and burger-free,
  unassigned-location fallback, non-navigation blocks untouched) plus
  the full 120-check dual-mode layout run.

## [1.32.1] — 2026-08-29

### Fixed

Consistency audit across every page template.

- **Three closing CTA bands were missing the reassurance line.** The
  Case Studies page and both landing pages ended with a heading and a
  button, while the homepage, Services and the service template each
  carried the "complimentary audit — 30 minutes, no commitment" line
  under the heading. All three now match.

- **The service detail template had no hero lead.** Every other page
  pairs its hero title with an `.rm-hero__sub` paragraph; service pages
  jumped straight from the display title to the body. The template now
  renders the page excerpt as the hero sub, which also means the lead
  text is the same string Rank Math uses for the meta description —
  one source, two places.

### Added

- **A "Main menu" card on the theme options screen.** WordPress hides
  Appearance → Menus for block themes, so the question of where the
  navigation lives has no obvious answer in wp-admin. The card explains
  that the header menu is the "Primary" navigation edited in Design →
  Editor → Navigation, links straight to it and to the template-part
  editor for the header and footer, and notes that new pages are not
  added to the menu automatically.

### Documented, not changed

- The About page hero (`.rm-about-hero`) and the legal template (title,
  updated date, content, no hero band or CTA) remain deliberate
  exceptions, as recorded in readme.md.

## [1.32.0] — 2026-08-29

### Added

- **A "Search & organic" section on the Case Studies page, with six new
  case studies.** Drawn from the strongest documented SEO engagements
  in the archive: a B2B industrial supplier (+198% organic traffic in
  month two, sustained through four Google core updates), a
  multinational FMCG nutrition portfolio (+43.5% organic year-on-year
  across two markets), a B2B services firm (all six tracked metrics up
  in one month), an e-commerce footwear turnaround (site health 85%,
  PageSpeed 92), a Malaysian healthcare clinic (six months of organic
  and paid run together), and a six-client Singapore B2B portfolio.

  These engagements were delivered by team members in prior agency
  roles, so each page and the section note say so explicitly, clients
  are described by sector rather than named, and every figure is
  reported as recorded. Each case carries an "honest read" section
  covering base effects, attribution limits, or — for the Singapore
  portfolio — the fact that only method can be shown, not outcomes.

  The page now runs 14 cards in two labelled grids; verified at eight
  widths in both modes with no overflow.

## [1.31.2] — 2026-08-29

### Changed

- **The homepage shows one featured case and three rows.** v1.31.1 had
  taken it to five; the requested shape is four. The three rows kept
  are the automotive CPM saving, the B2B gifting lead growth and the
  industrial automation organic rebuild — one paid, one paid-search,
  one organic, so the strip spans the disciplines rather than
  repeating one. The sports precinct and healthcare cases remain on
  the Case Studies page, which still carries all eight.

## [1.31.1] — 2026-08-29

### Changed

- **The homepage now shows five case studies and the Case Studies page
  eight.** The homepage carried one featured spread plus two result
  rows — a v1 layout choice made when no real case material existed,
  and never revisited when v1.31.0 replaced the placeholders. Three
  more deck-sourced rows are added (industrial automation B2B, +86%
  organic sessions with average position 16.3 → 10.6; the national
  sports precinct's 53k → 745k audience; the premium healthcare
  provider's +34% clicks, 2,553 keywords and +150% SQL), bringing the
  homepage to five.

  The Case Studies page doubles from four cards to eight, adding the
  global asset manager (+25–35% conversion rate, cost per conversion
  down 20–30%), the industrial automation and healthcare SEO cases, and
  the global trading platform's 48-hour market-entry sprint. That is
  every case in the source decks that can be told anonymously with a
  headline figure. The grid's 4 → 2 → 1 steps mean eight cards fill
  two even rows at every breakpoint — verified at eight widths in both
  modes with no overflow.

## [1.31.0] — 2026-08-29

### Changed

- **Every case study on the site is now real, deck-sourced and
  anonymised.** The homepage featured case ("Skinlab", +312%), both
  homepage result rows (4.6× B2B SaaS, #1 multi-location), three of the
  four Case Studies cards, and the three homepage stat-band defaults
  (3.2× ROAS, +140% organic, 98% retention) were all invented
  placeholder content carried since v1. None of those figures appears
  in the client's capability decks. They are replaced with real client
  results, clients described rather than named, every number verbatim
  from source:

  - Featured: medical-grade skincare SEA launch, +168% GMV in three
    months on under 0.5% share of category spend, with the D2C-to-
    marketplace rerouting insight and the 65,000 visitors / 2:20
    engagement figures.
  - Rows: global automotive marque, 85% CPM saving across seven markets
    with 26.1M completed views; B2B corporate gifting, +150% qualified
    leads and +50% conversion rate.
  - Cards: the above plus the national sports precinct's 53k → 745k
    addressable audience and its 20–30% CPA improvement.
  - Stat band: +168% GMV, 85% CPM saving, +150% lead growth — the
    client's own headline figures from the deck's summary slide.

- **The fabricated client testimonial is removed.** The homepage
  featured case carried a quote attributed to a "Founder, Skinlab".
  No client quotes exist in the source material, so the quote block is
  deleted rather than reattributed. If real approved quotes become
  available, the `.rm-spread__quote` styling remains in the stylesheet.

- **The Case Studies placeholder note now tells the truth.** It said
  the cards were placeholders awaiting real names and metrics; it now
  states that the figures are real client work with clients described
  rather than named, and scopes the remaining placeholder to the card
  artwork.

## [1.30.1] — 2026-08-29

### Fixed

- **The Case Studies page named a client.** The card label "DD Bricks —
  traffic recovery" now reads "B2B corporate gifting — lead growth",
  matching the anonymisation rule applied across the content packs:
  clients are described, never named, and every number stays exactly as
  sourced. The accompanying deck-traceability audit (in the service
  pages pack) maps every element of both capability decks to its
  destination on the site or records why it is deliberately unused —
  including the exclusion of all client-specific pitch material. The
  homepage's placeholder-branded featured case is flagged there for a
  future content revision; authored pages no longer borrow its number.

## [1.30.0] — 2026-08-29

### Changed

- **The service-page set is now the footer's canonical six, and the
  footer nav links them.** The v1.29.0 pack shipped the capability
  deck's eight modules as pages; the site's own information
  architecture — the footer Services nav — names six: SEO, Paid Media,
  Social, Content, Email, Analytics. The pack is rebuilt to those six
  (paid search and performance media fold into Paid Media; creative
  folds into Content; automation becomes Email & Lifecycle), the
  footer's six labels now link their own pages instead of all pointing
  at /services/, and the hub h3s and homepage plate labels are
  retargeted accordingly. The keyword mapping is revised to v3:
  consulting intent returns to the /services/ hub now that no
  standalone strategy page exists.

- **The SEO service is renamed "SEO & GEO / AIO / AEO" consistently
  across the theme** — the homepage plate label and the Services page
  heading — and the service page's opening prose spells out all four
  in full (search engine optimisation, generative engine optimisation,
  artificial intelligence optimisation, answer engine optimisation) as
  part of the argument rather than a glossary. Rendered verification
  confirms the longer label holds at all widths in both modes; the
  42-assertion schema suite passes unchanged.

## [1.29.0] — 2026-08-29

### Added

- **A reusable "Service detail" template and dedicated service pages.**
  New `templates/page-service.html` (registered in theme.json): hero
  built from the page's own title on the standard rm-hero, the page
  content on the wide measure, and the closing CTA band — so service
  pages are ordinary editable WordPress pages, keeping content out of
  theme code per ssot.md. Eight deck-derived pages (SEO & AI search,
  paid search, social & influencer, performance media, analytics,
  marketing automation, creative, media strategy) ship separately in
  the service-pages pack with Rank Math metadata validated to the
  50/130 limits. Any page on the template automatically emits its own
  Service schema node (name from the title, description from the
  excerpt), covered by a new test assertion (42 total, passing).
  Template geometry verified in the rendered harness at seven widths
  in both modes.

- **Service navigation links.** The Services page's seven sub-service
  headings and the homepage's ten "What we do" plate labels now link
  their service pages, converting both listings from labels into
  navigation. The keyword mapping is revised to v2 alongside: twelve
  service-generic keywords move from the /services/ hub to four
  keyword-bearing service pages, and the four pages without a primary
  keyword from the list are recorded as supporting pages, not SEO
  targets.

## [1.28.0] — 2026-08-29

### Added

- **Insights internal links from both landing pages' FAQ answers.**
  Part of the keyword-to-content mapping exercise (see
  `keyword-content-mapping.md` in the accompanying Insights content
  pack 2, delivered separately per the content/theme separation in
  ssot.md). The Singapore FAQ now links the SEO-cost post (previously
  an unlinked mention), the SEO-vs-SEM post plus the new SEM-services
  guide, and the new agency-selection guide; the Malaysia FAQ links
  the pricing post and — from its "we don't build websites" answer —
  the new SEO-friendly web design guide, which captures the
  informational fraction of the documented web-design keyword gap
  without creating a doorway service page. Content changes only; the
  120-check dual-mode browser harness and the 41-assertion schema
  suite both re-run clean (the FAQPage schema parses the linked
  answers to plain text correctly).

  The pack itself ships four publication-ready draft posts
  (SEO-friendly web design; SEM services in Singapore; Facebook
  advertising in Malaysia; how to choose an SEO agency) with a
  three-category Insights taxonomy derived from the eight posts that
  now exist, ≤5 lowercase tags per post, Rank Math metadata within
  50/130 character limits, branded featured images with alt text, and
  the full 90-keyword mapping with every consolidation and exclusion
  recorded.

## [1.27.1] — 2026-08-29

### Changed

- **Rank Math coexistence verified against the plugin's actual source
  (v1.0.277) and made two behaviours smarter.** First, Rank Math's
  entire schema output lives behind its toggleable "rich-snippet"
  module — every snippet class (WebSite, WebPage, Article,
  BreadcrumbList, Person, publisher) loads only when it's active — so
  the theme now checks `RankMath\Helper::is_module_active(
  'rich-snippet' )` and treats the plugin as managing nothing when the
  module is off, keeping the site's markup intact instead of yielding
  to output that never comes. Second, schemas a user attaches through
  Rank Math's Schema Generator are stored as `rank_math_schema_*` post
  meta carrying the `@type`; the theme now reads those on singular
  pages and yields exactly the attached types on exactly that page — a
  Service schema attached to a landing page silences the theme's
  Service node there while its FAQPage continues, and both return the
  moment the meta is removed. The theme's other surfaces (no meta
  tags, OpenGraph, titles, sitemaps, or rendered breadcrumbs) have
  nothing for Rank Math's remaining modules to collide with. Five new
  assertions cover the module gate on/off and the per-page meta yield
  (41 total, all passing).

## [1.27.0] — 2026-08-29

### Added

- **Full coverage of Google's supported structured-data features, maxed
  to what the site's real content can truthfully carry.** Audited
  against Google's June 2026 feature list; the complete
  feature-by-feature matrix (emitted / content-gated / excluded, every
  exclusion with its reason) is in readme.md. New in this release:
  BreadcrumbList on every non-front page; the Organization node
  dual-typed ProfessionalService (the Local-business agency subtype
  over the ACRA registered office) and enriched with foundingDate, the
  UEN as an identifier, a sales contactPoint and a description;
  ProfilePage on the About page and author archives; image metadata
  (creator, creditText, copyrightNotice, filterable license URLs) on
  the organization logo; Speakable selectors on BlogPosting; Article
  enrichment (author URL, wordCount, articleSection, inLanguage); and
  a content-gated VideoObject that emits only when a post contains a
  self-hosted video and its required properties can be stated
  truthfully. Features whose content doesn't exist on the site (Recipe,
  Event, Product, Review snippet, Job posting, and the rest of the
  excluded rows) are deliberately not fabricated: Google's spam
  policies treat markup for content not visible on the page as abuse,
  and a manual action on an SEO agency's own site is the outcome the
  matrix exists to prevent.

### Fixed

- **Typed page nodes escaped plugin suppression.** v1.26.0 matched
  suppressed types by exact string, so AboutPage and ContactPage
  slipped past a plugin's control of WebPage. Suppression now resolves
  type families (all page subtypes → WebPage, BlogPosting → Article,
  ProfessionalService → Organization) and a dual-typed node yields if
  any of its types is plugin-managed. The 36-assertion stub suite
  covers the family logic, the new nodes, and the video gating.

## [1.26.0] — 2026-08-29

### Added

- **Structured data (schema.org JSON-LD) for the site's existing
  content.** New `inc/schema-markup.php` emits one `@graph` in `<head>`:
  Organization (legal name and registered address per `ssot.md`; email,
  address lines and social profiles read from Theme Options — the same
  admin-editable source the rendered footer uses), WebSite with a
  SearchAction, a typed WebPage per template (AboutPage, ContactPage,
  CollectionPage, SearchResultsPage), BlogPosting on single posts,
  Service nodes for the three core blocks on the homepage and Services
  page, one market-specific Service per landing page (areaServed
  Singapore / Malaysia, matching the consolidated keyword strategy),
  and FAQPage on both landing pages with the question/answer pairs
  parsed at runtime from the templates' own `core/details` blocks, so
  the schema always states exactly what the page renders. The markup is
  identical in light and dark mode by construction — it describes
  content, and the mode toggle only flips CSS custom properties.

- **Per-type acquiescence to SEO plugins.** When an active SEO plugin
  (Yoast, Rank Math, AIOSEO, SEOPress, The SEO Framework, Slim SEO)
  takes control of a schema type, the theme yields that type — and only
  that type. Types no plugin manages automatically (the Service and
  FAQPage nodes) stay theme-native and take precedence. A page using a
  plugin's own FAQ block yields the theme's FAQPage on that page alone.
  When everything a page would emit is plugin-managed, the theme prints
  nothing rather than an empty script tag. Filterable at three levels:
  `remotive_schema_plugin_managed_types`,
  `remotive_schema_suppressed_types`, and `remotive_schema_graph`.
  Verified by 24 functional assertions run against WordPress stubs:
  graph composition per context, plugin suppression, FAQ counts
  matching the actual template files, address parsing, social-URL
  filtering, and JSON validity of the printed output.

## [1.25.3] — 2026-08-29

### Fixed

The rendered verification now runs everything in both colour modes —
120 checks (six pages, ten widths, light and dark) — with both real
typefaces loaded, since light mode is deliberately its own brand system
set in Saira while dark sets Archivo, and the harness had previously
pinned light mode and let Saira fall back to a system font. Geometry
passes in both modes with both fonts. Colour probes now wait out the
body's .25s background transition, which had been feeding them
mid-transition values.

- **The CTA band was invisible in dark mode.** Its background is
  contrast-bg, which in dark equals the navy paper — measured ratio
  1.00, the same mechanism that once made fill buttons vanish. The band
  now inverts with the mode like the ticker: white-on-navy in dark,
  mirroring the navy-on-cream light band. The whites hardcoded inside
  it (input border and text, placeholder, note) flip with the surface.

- **The fill button on the subpage closing bands had no visible shape
  in either mode.** Measured on the band, the fill's ink background is
  near-identical to the light band's navy (1.02) and identical to the
  dark band's inverted white (1.00) — the label floated with no button
  behind it. On the band, fills now take paper-on-ink in both modes: a
  cream button on the navy light band, a navy button on the white dark
  band. The light-mode half of this predates the dark work; it was only
  caught once the probes compared the button against the surface it
  actually sits on.

## [1.25.2] — 2026-08-29

### Fixed

Three footer defects reported from the live site, all invisible to the
v1.25.1 harness because its shim zeroed the vertical navigation gap that
real WordPress emits — the shim is corrected, and the full sixty-check
verification passes against the corrected model.

- **~40px of dead space between footer nav links.** WordPress emits its
  24px block gap between the vertical navigation's items, and stacked on
  the links' own .5rem block padding each column spread to ~72px per
  item. The gap is zeroed on both possible carriers (the nav element and
  its list container — the same dual-target lesson as the header nav);
  the padding alone now gives a 1rem rhythm and keeps the tap targets.

- **The column headings didn't read as headings.** SITEMAP, SERVICES and
  CONTACT rendered at .72rem in 55%-strength ink under .9rem full-ink
  links — an inverted hierarchy. The headings now take full ink, the
  heaviest weight and .78rem, staying in the small-caps eyebrow register
  while clearly labelling their lists.

- **The contact column's address was boxed to ~260px inside a wider
  column.** The 32ch cap on `.rm-footer__desc` suits the brand blurb it
  was written for, but the same class dresses the contact column's email
  and address, leaving a dead right rag on the footer's last edge. The
  contact column's paragraph now takes the full column width, so the
  footer's right edge lands on the wide measure and mirrors the logo's
  left inset.

## [1.25.1] — 2026-08-28

### Fixed

Three defects found by rendering the theme in headless Chromium (real
templates, real stylesheets, the theme's own font files, a WordPress
layout-CSS harness) at ten widths from 320px to 1900px across six pages.
After these fixes all sixty checks — page overflow, nav-row fit,
left-edge alignment, grid column counts — pass.

- **The footer's fourth column collapsed to ~8px between 782px and
  900px, painting its content past the viewport.** The wrap band's
  generic column rule loses on specificity to the four-column block's
  `:last-child` rule even though it comes later, so `flex-basis:0`
  survived into the band and two 44% siblings crushed the last column
  to the leftover space. The band now repeats the `:last-child`
  selector. This one was invisible to static analysis — both rules read
  as if the later block wins.

- **The header CTA never actually fit between 783px and 980px.**
  Measured, the full row needs ~873px against 705–884px of available
  space across that band, so the old 782px cut-off left up to ~168px of
  horizontal page scroll at 783px. The CTA now leaves the row at 980px.

- **The v1.24.2 nav squeeze band under-delivered and stopped too
  early.** Its gap rule targeted the nav element while the navigation
  block emits its flex gap on the list container, leaving the 24px link
  gaps untouched (~69px overflow at 601px, rendered), and the band ended
  at 720px while the un-squeezed row still needs ~674px against as
  little as 649px up to 782px. The squeeze now covers 601–782px, sets
  the gap on both the nav element and the list container, and tightens
  link padding to .25rem; the measured row comes to ~530px at the 601px
  floor.

## [1.25.0] — 2026-08-28

### Changed

- **Interior pages join the homepage's alignment system.** Services,
  Case Studies, Contact, both landing pages and the blog list views
  never received the measure-alignment pass the homepage got across
  v1.16.2–1.24.0: their heroes sat on the 1320px wide measure (the
  global `.rm-hero > *` rule) while every body section fell back to the
  740px centred content measure — at 1440px the hero headline's left
  edge sat ~290px left of the content's. Their section wrappers now
  carry the previously unused `.rm-measure-wide` utility, which gains a
  second job: redefining `--wp--style--global--content-size` to the wide
  value on the wrapper, so core's constrained-layout clamp resolves to
  the wide measure for every descendant, however deeply nested — no
  per-child overrides, and elements with their own caps (60ch lists,
  54ch leads, the 520px form) keep them, pinned to the shared left edge.

  Kept at the reading measure, as documented exceptions: single posts,
  legal pages and the default page template (the prose is the page), and
  the About page wholesale — a wider deviation than the audit note's
  "gallery only", because every component on it (gallery at 740px
  three-across, team grid with bios measured at 220px, 520px form) was
  designed for that measure, and widening the team grid strands a sixth
  portrait under a row of five. The About hero is aligned to that
  measure instead: the title's 900px cap (which centred it 80px out of
  line) now clamps to the content measure, and its sub-heading is pinned
  to the measure's left edge with the same inset technique the homepage
  hero uses.

- **The case-study grid steps 4 → 2 → 1 instead of auto-fitting.** On
  the wide measure, auto-fit with four items strands the fourth card
  alone under a row of three across ~1075–1400px, and no intrinsic
  minimum can forbid the three-column state. Explicit steps — the same
  trade `.rm-plates` already makes — keep the rows even at every width.

### Fixed

- **The Case Studies placeholder note drifted toward the centre.** Its
  60ch box, a constrained child with only a top margin set, was centred
  by core's layout rule — ~165px right of the grid's left edge even
  before this release. Its side margins are now zeroed.

## [1.24.4] — 2026-08-28

### Documentation

- **The open findings from the v1.24.2 audit are now in the backlog.**
  `upgrading.md` gains two near-term items: the pending decision on
  interior-page measure alignment (wide heroes over 740px bodies on
  every interior page, with the recommended direction and its two
  documented exceptions written down), and a real-browser responsive
  verification pass, since the v1.24.2/1.24.3 fixes were derived from
  font metrics and CSS analysis rather than rendered screenshots. No
  code changes.

## [1.24.3] — 2026-08-28

### Fixed

- **The hero sub-heading floated toward the centre, opening a dead gap
  under the headline's left edge.** Every hero child is centred on the
  wide measure with auto side margins, and the sub-heading is the one
  child narrower than that measure (capped at 56ch) — so the auto
  margins centred its smaller box, detaching its left edge from the
  headline's by hundreds of pixels on wide desktops. The same failure
  v1.16.2 fixed for the then-current markup had returned for any
  capped child. The sub-heading's left margin is now computed as the
  same inset the full-width siblings derive for themselves
  ((100% − wide measure) / 2, floored at 0), so its left edge sits
  exactly on the headline's at every viewport width. Scoped to
  `.rm-hero`; the About page's `.rm-about-hero`, where no sibling sits
  on the wide measure, keeps its current arrangement.

## [1.24.2] — 2026-08-28

### Fixed

- **Every page except the homepage rendered two "Skip to content" links.**
  The header template part carries the skip link, and fourteen templates
  also pasted their own copy immediately after it, so keyboard users
  tabbed through the same link twice on every interior page. The
  template-level copies are removed; the header part's link remains, the
  same arrangement the front page already had.

- **The nav row could overflow the viewport between 601px and ~720px.**
  Above 600px the navigation block's overlay menu hands back to inline
  links, and the links block is deliberately held at its intrinsic width
  so it cannot wrap to two lines — but in that band the row's fixed
  items (logo, five links, labelled theme toggle, gaps) can need
  ~560–600px against ~540–575px of available space, measured with the
  theme's own Archivo font files. The overage became horizontal page
  scroll on large phones in landscape and small tablets. A squeeze
  state for that band only tightens the link gaps, trims the logo to
  32px, and drops the toggle's text label (its aria-label and the
  switch itself still identify it), bringing the demand under the
  space at 601px even with a wide logo.

- **The header's "Start a project" button was dead on every interior
  page.** It pointed at `#contact`, an id that only exists on the front
  page, so on Services, Work, About, Insights and Contact the button did
  nothing. It now points at `/#contact`, which scrolls on the homepage
  exactly as before and navigates home to the same band from everywhere
  else.

- **Ordinary pages on the default template rendered partly unstyled.**
  `page.html` styles its title and content with `.rm-page` classes that
  live in `blog-and-about.css`, but the conditional enqueue only loaded
  that file for blog views and the seven named custom templates — a
  page using the default template got none of it. The condition is now
  "any page except the front page", which also stops the template list
  needing maintenance.

- **The footer carried a second `id="about"`.** The footer's brand
  column had `id="about"` left over from an earlier anchor scheme,
  colliding with the homepage section of the same id — invalid HTML on
  the front page, and the `#about` wide-measure CSS silently applied to
  that footer column. Nothing links to the footer id; it is removed.

- **The blog page's empty state spoke search language.** `home.html`
  showed "Nothing matches yet. Try another term…" when no posts exist —
  wording that belongs to search and archive results (which keep it).
  The blog page now uses the same "Nothing published yet" copy as the
  index fallback.

- **A comment in `remotive.css` credited the CTA status injection to
  `theme-toggle.js`.** The script that reads the redirect's query string
  and fills `.rm-cta__status` is `lead-form-status.js`; the comment now
  says so.

## [1.24.1] — 2026-08-28

### Fixed

- **The About section's "Meet the team →" link broke the section-link
  pattern.** "What we do" and "Work that moved the number" both place
  their outbound link ("All services →", "All work →") in the top-right
  of an `.rm-section-head` flex row, uppercase with the magenta
  underline. The About section instead dropped its link at the
  bottom-left of the team grid as a plain `.rm-blocks__note` paragraph
  in sentence case — a third link treatment on a page that had
  established one.

  The About header now uses the same `.rm-section-head` structure: the
  eyebrow, heading and lead paragraph sit in the left column, and
  "Meet the team →" sits top-right as an `.rm-section-link`, matching
  the other two sections. The lead lives inside the head's left column
  (rather than after the head group) so the heading-to-lead spacing is
  unchanged; the head's existing bottom margin now separates the intro
  from the team grid. The bottom-left note is removed. No CSS changes:
  both classes already existed, and `.rm-blocks__note` remains in use
  by the services footnote.

## [1.24.0] — 2026-08-28

### Fixed

- **Main content had no gutters, while the header and footer did.** At
  1900px the hero and every section sat 48px from the left with 532px of
  empty space on the right, because v1.16.2 pinned them with
  `margin-left:0`. The header row and footer stayed centred on the wide
  measure. Three alignments on one page: content hard left, chrome centred,
  and the call-to-action centred on a narrower 740px measure.

  The sections and the hero's children are centred on the wide measure now,
  the same as the header and footer. Measured at 1600px, the hero, problem,
  services, why, work, about and call-to-action headings all start at the
  same 140px, with matching space on the right.

  The v1.16.2 change was a real fix for a real problem: the hero's headline
  sat on one edge while its sub-heading floated in the middle. Pinning
  everything left cured that but broke the page's alignment. Centring the
  whole set cures both, since the text stays left-aligned inside a centred
  box, which is what the header and footer always did.

### Changed

- **The call-to-action band is rebuilt.** It was a 740px centred column with
  centred text, a third layout language on a page that has two. It now sits
  on the same centred wide measure and reads left to right: the statement on
  the left, the form on the right, sharing a baseline. That mirrors
  `.rm-section-head`, which puts "What we do" on the left and "All services"
  on the right. Below 860px it stacks in reading order and stays
  left-aligned. Colours, fonts and copy are untouched; only the arrangement
  changed.
- The band takes explicit `padding-block` now. It previously drew its height
  from its children's margins, which the new grid removed.
- The content is centred with a padding expression rather than a wrapper
  element, so the template markup is unchanged and the background stays
  full-bleed.

### Notes

- Two shrink floors surfaced at 320px during this work and are fixed: a
  plain `1fr` track cannot go below its content's minimum, and the email
  field carried a fixed `min-width:220px`. Both now use the same
  `min(<value>, 100%)` guard as the grids.
- Verified across six templates at 28 widths each, 168 checks: no
  horizontal overflow anywhere.

## [1.23.0] — 2026-08-28

### Changed

- **The last three fixed-column grids are intrinsic**, finishing the work
  started in v1.22.0. `.rm-post-grid` (archive cards), `.rm-cs-grid` (case
  studies) and `.rm-gallery` now use `repeat(auto-fit, minmax(...))` and
  reflow with the space available. Two more breakpoints removed, leaving
  `blog-and-about.css` with one real media query.
- Every intrinsic grid in the theme now wraps its minimum in
  `min(<value>, 100%)`. Without that guard a track can be wider than its
  container on a narrow screen and push the page sideways, which is exactly
  what `.rm-cs-grid` did at 320px on the first attempt at this change.

### Notes

- Choosing each minimum matters more than it looks. The gallery sits in a
  740px container, and a 180px minimum resolved to four columns with a
  stray row of two, which changed the design rather than adapting it. 240px
  holds the intended three columns at full width and steps to two and then
  one on its own. The case-study grid keeps its two columns for the same
  reason.
- Verified across six templates (homepage, about, case studies, services,
  contact, 404) at 27 widths each, 162 checks in total: no horizontal
  overflow anywhere, and both grids confirmed to keep their original column
  counts at full width.

## [1.22.1] — 2026-08-28

Documentation only. No code behaviour changes.

### Fixed

- **Wrong attribution of the 740px content measure.** Two changelog entries
  and a comment in `remotive.css` described 740px as "WordPress's default
  `contentSize`". It is not: 740px is set in this theme's own `theme.json`.
  Checked against Twenty Twenty-Five 1.5, the parent uses 645px, and this
  theme also narrows `wideSize` from the parent's 1340px to 1320px. The
  layout bugs those entries describe were real; only the source of the
  number was misstated.

### Added

- A section in `readme.md` setting out what this theme actually inherits
  from Twenty Twenty-Five, verified against the parent rather than assumed.
  The corrections worth knowing:
  - The parent ships only 8 templates and this theme overrides 7. The one
    template genuinely inherited is `page-no-title.html`. The parent has no
    author, category, tag or date template, so those fall through to
    `archive.html` and `index.html`, both of which this theme provides.
  - Four parent template parts are inherited and available as alternates:
    `footer-columns`, `footer-newsletter`, `header-large-title` and
    `vertical-header`.
  - 98 block patterns, 8 style variations, post-format support, the
    `checkmark-list` block style, the parent's pattern categories and its
    block bindings are all inherited, since this theme ships none of them.
  - The parent contains zero media queries in any of its three CSS files,
    which is the reason the intrinsic grid work in v1.22.0 was the right
    direction rather than a preference.
  - The parent enqueues its own `style.min.css`, so a child-side
    `wp_enqueue_style( 'parent-style' )` would duplicate it.

## [1.22.0] — 2026-08-28

### Changed

- **The homepage grids are intrinsic now**, matching the parent theme's own
  responsive model. Twenty Twenty-Five is almost breakpoint-free: it scales
  through fluid type, viewport-aware root padding and the rules baked into
  core blocks, with one real breakpoint at 781px where columns stack. The
  child could not inherit any of that for its own components, because 41% of
  `front-page.html` is raw markup inside `wp:html` blocks and core ships no
  CSS for a `div.rm-blocks`. Every threshold had to be hand-written, which
  is why the two behaved differently at the same widths.

  `.rm-problems`, `.rm-blocks`, `.rm-whys` and `.rm-team` now use
  `repeat(auto-fit, minmax(<measured>, 1fr))`, so the track count follows
  the space available rather than a chosen width. The minimums come from
  measuring each card's own content: 290px for the capability blocks, below
  which the longest service line breaks badly; 230px for the problem and why
  cards; 130px for the team portraits, which keeps two across at 320px.
  Because auto-fit cannot create more tracks than there are items, the
  source order caps the columns at 3, 4 and 6 respectively.

- Six media queries removed, from thirteen down to nine. The remaining ones
  govern the header, the footer and typography, where a real state change is
  intended, rather than grid column counts.

### Notes

- The four-item grids pass through a three-column state around 1024px to
  834px, leaving one card on its own row. That is what intrinsic layout
  does, and each card carries its own top rule so it reads as a continuation
  rather than a break. Forcing 4 straight to 2 would need a media query back,
  which is the thing this change removes.
- Verified at 26 widths from 1900px to 320px: no horizontal overflow, and
  card widths stay within a sane band at every width (capability cards 290px
  to 440px, problem cards 233px to 468px, portraits 130px to 203px).

## [1.21.0] — 2026-08-28

### Added

- **Homepage copy is editable in Theme Options.** Three new tabs: Homepage
  hero (eyebrow, two headline lines, the highlighted words, sub-heading,
  both button labels, reassurance line), Section headings (all five), and
  Numbers (the three figures and their labels). Twenty new
  `__REMOTIVE_*__` tokens carry the values into `front-page.html` through
  the filter the theme already used for contact details, so the block
  templates stay static and the editable strings live in one place. Every
  value is `sanitize_text_field()` on the way in and `esc_html()` on the way
  out. A heading left blank falls back to its shipped default rather than
  leaving a hole in the page; the eyebrow is allowed to be empty.
- **Header order comes from a navigation menu.** Setup builds a `Primary`
  navigation menu from the pages in `menu_order`, and a `render_block_data`
  filter points the header's navigation block at it, so reordering or
  renaming items in the Site Editor's Navigation panel reorders the header.
  Menu items link by post ID, so changing a page slug does not break them.
  Pages now carry an explicit `menu_order`. The hard-coded links in
  `parts/header.html` remain as a fallback: if the menu is deleted or
  trashed the header renders those instead of coming out empty. The footer's
  own navigation blocks are matched out by class name and left alone.

### Changed

- The admin panel is renamed from "Remotive Options" to **Theme Options**,
  and its header now uses the landscape logo (trimmed of its whitespace and
  sized for the admin bar) in place of the square lockup.
- The homepage hero buttons point at `/contact/` and `/work/` instead of the
  homepage anchors.

### Fixed

- The site setup redirect pointed at `themes.php?page=remotive-options`,
  but the page is registered as `remotive-theme-options`, so the success
  notice landed on a blank screen.

## [1.20.0] — 2026-08-28

### Changed

- **Site pages are created automatically.** v1.19.0 put the setup behind a
  button; it now runs on its own, on theme activation, so a fresh install
  has working navigation with no setup step. Sites already running the theme
  get a catch-up pass on the next admin load, since `after_switch_theme`
  will not fire for them until they switch themes again. That pass is
  restricted to a user who holds both `edit_theme_options` and
  `publish_pages`, so the pages are created by someone entitled to create
  them, and it is skipped while `wp_installing()` is true.

  A stored option, `remotive_site_setup_done`, holds the theme version that
  ran it and keeps this to a single occurrence. Re-activating the theme does
  not run it again. Delete that option to force another pass.

  Running unattended is safe because the routine only ever adds: pages are
  matched by slug, an existing page keeps its title, content and template,
  and nothing is deleted or overwritten. On a site that already has these
  pages the routine finds them and changes nothing.

- The button in Appearance -> Remotive Options stays, for re-checking the
  list or rebuilding a page that was deleted later.

## [1.19.0] — 2026-08-28

### Added

- **Site pages setup**, in a new `inc/site-setup.php` and a card at the
  bottom of Appearance -> Remotive Options. A theme ships templates, not
  Pages, so the navigation pointed at URLs that would have 404'd on a fresh
  install. The panel lists every page the theme expects, shows whether each
  exists and carries the right template, and creates the missing ones on
  request: Home, Services, Work, About, Contact, Insights, Privacy Policy
  and Terms of Service. It also points Settings -> Reading at the Home and
  Insights pages so the Posts page resolves at `/blog/` and renders through
  `templates/home.html`.

  The custom page templates carry their own content and do not render
  `post_content`, so an empty Page with the template assigned renders
  complete. Only the two legal pages need copy written in the editor.

  It runs from a button rather than on theme activation, since creating
  content unasked is surprising and a re-activation should never touch a
  live site. Every run is idempotent: pages are matched by slug, an existing
  page keeps its title and content, and only a missing template assignment
  is filled in. Nothing is deleted or overwritten. The handler checks both
  `edit_theme_options` and `publish_pages` and verifies a nonce.

### Changed

- Navigation and footer links now point at real pages (`/services/`,
  `/work/`, `/about/`, `/contact/`) instead of homepage anchors. The anchors
  only resolved on the homepage, so from any other page those links did
  nothing.
- The homepage "All services", "All work" and "View case study" links point
  at `/services/` and `/work/` instead of `#`.

### Notes

- The four `href="#"` links on the case studies page are left as they are.
  They are documented placeholders waiting on the custom post type described
  in `upgrading.md`, and inventing destinations for them would be worse than
  leaving them visible as unfinished.

## [1.18.1] — 2026-08-28

### Fixed

- **Theme toggle knob sat off-centre.** The thumb used hand-computed offsets
  (`top:2px; left:2px`) and a fixed `translateX(18px)`, none of which
  accounted for the track's border under border-box sizing. Measured, it sat
  3px from the top against 5px below, and in the on state it stopped 5px
  from the right end against 3px on the left, so it read as high and short
  of its travel. The geometry is derived now: the thumb centres with
  `top:50%` and `translateY(-50%)`, and the travel is calculated from the
  track's own width, border and inset, so the two end states mirror exactly.
  The border moved from 1.5px to 2px because a half-pixel border rendered at
  1px, which put the calculated travel half a pixel out of step with what
  was painted. Verified at 6x device pixel ratio: 4px on all four sides in
  both states.

## [1.18.0] — 2026-08-28

### Added

- **The team, six members, named with roles.** Gordan (Owner), Jazlan (Head
  of Marketing), Adam (Performance), Elfie (SEO), Alif (Social Media) and
  Nabil (Analytics). The homepage About block moved from four unlabelled
  tiles to the six people, and `page-about.html` gains a roster section
  ahead of the gallery.
- `.rm-team` grid and card styles. Six across on wide desktop, three at
  1100px, two from 700px down. Portraits stay legible two across on the
  narrowest screens, so this grid does not fall to a single column the way
  the abstract tiles did. `.rm-team__photo` holds a 4:5 ratio and its `img`
  uses `object-fit:cover` with a top-weighted position, so real headshots
  drop in without distortion.
- An empty `.rm-team__bio` on each roster card, carrying an HTML comment
  naming what belongs there. The experience lines are the team's own to
  write and were deliberately left blank rather than invented.

### Changed

- The homepage "Meet the team" link points at `/about/` instead of `#`.
- `.rm-about__photos` and its breakpoints are retired, replaced by
  `.rm-team`.

## [1.17.0] — 2026-08-28

A responsive layout pass covering desktop, tablet and mobile, driven by
measurements at 26 viewport widths from 1900px down to 320px.

### Fixed

- **Header sat at the 740px content measure.** `.rm-nav__row` is a child of
  the constrained `.rm-nav`, and it was never added to the wide-measure
  selector list, so it stayed at the theme's own `contentSize`, centred,
  while everything below it ran 1320px and flush left. (Corrected in
  v1.22.1: 740px is this theme's value, set in its own theme.json. The
  parent, Twenty Twenty-Five, uses 645px, and WordPress core's fallback is
  different again.) Its intrinsic width
  passed 740px when the Insights link landed in v1.15.1, so `flex-wrap`
  dropped the theme toggle onto a second row at every width, desktop
  included. The row now shares the wide measure, wrapping is off, and the
  row holds 84px from 1900px down to 480px.
- **Navigation links wrapped to two lines on tablet.** Flex items shrink
  below their intrinsic width by default, so the links block was squeezed to
  304px against the 343px it needs. The fixed items hold their natural size
  now. The rule is scoped above 600px, since the navigation block's overlay
  menu takes over below that.
- **Hero watermark never hid on phones.** The rule named `.rm-hero__mark`, a
  class in no template, so it had never hidden anything. The mark is drawn
  by `.rm-hero::before`, which is what the rule targets now.
- **Proof-row descriptions were dropped below 900px.** `display:none` on
  `.rm-row__desc` removed content on every tablet. The text stays and the
  row reflows.
- **Markets strip used a 100vw workaround.** It is wrapped in an `alignfull`
  group now, the same mechanism `.rm-stats` and `.rm-cta` use. The `100vw`,
  the negative margin and the `body{overflow-x:clip}` are gone. That clip
  had been hiding the fact that `100vw` counts the scrollbar.

### Changed

- Grids step where their own content runs out of room rather than at one
  shared breakpoint. `.rm-problems` and `.rm-whys` go 4 to 2 at 1100px and
  2 to 1 at 640px. `.rm-blocks` gains the missing middle step, 3 to 2 at
  900px and 2 to 1 at 640px; it previously jumped from 258px cards to a
  single 806px card. `.rm-about__photos` goes 4 to 2 at 900px and 2 to 1 at
  520px, having never left two columns before.
- The footer gains a two-column state between 782px and 900px, where four
  columns had squeezed the brand column to 220px.
- Header collapse is deliberate: tighter spacing below 1000px, and the CTA
  leaves the row below 782px since it repeats the hero's primary action.
- The wide measure is available as a reusable `.rm-measure-wide` class and
  reads the `wideSize` token instead of a hard-coded 1320px, so the next
  section added does not repeat this bug.

### Verification

- 26 widths from 1900px to 320px. No horizontal overflow at any of them,
  with no `overflow-x` rule anywhere in the theme.
- Header holds a single 84px row from 1900px to 480px.

## [1.16.3] — 2026-08-28

### Removed

- The visible "Icons by Font Awesome" credit next to the footer copyright,
  along with the `.rm-footer__credit` rules that styled it. v1.13.0 added
  the credit on the belief that the LinkedIn icon's CC BY 4.0 terms needed
  a visible line. Font Awesome's own licence says otherwise: downloaded
  files carry embedded comments with sufficient attribution, and nothing
  further is needed for normal use. That comment sits inline above the
  LinkedIn SVG in `parts/footer.html` and in `assets/icons/linkedin.svg`,
  and both were left in place. Font Awesome asks only that the comments
  are not stripped from the code, which is the part that still holds.
  Only the LinkedIn icon comes from Font Awesome; the Instagram and TikTok
  marks use a different 24x24 icon set and never needed the credit.

## [1.16.2] — 2026-08-28

Two desktop layout bugs in the new homepage, both showing only above
1320px, where the earlier passes did not catch them.

### Fixed

- **Hero content out of line on wide screens.** The shared wide-measure
  rule centres each child in a 1320px box. The headline escaped that
  through its own margin and stayed flush-left, while the subhead and
  buttons centred and drifted right, so the two no longer sat on the same
  edge. The hero children now pin to the left, and the subhead takes a
  56ch measure so it wraps as a column.
- **Markets strip rendered as a floating, clipped band.** It was a
  constrained 740px child, so it sat centred and cut off mid-word rather
  than running edge to edge. It now spans the full viewport like the stats
  and CTA bands, with a body-level overflow rule so the full-bleed width
  adds no horizontal scrollbar.

## [1.16.1] — 2026-08-28

A copy pass across the new homepage and Services sections, against the
house English voice.

### Changed

- Took AI-tell vocabulary out of the copy. The "Dynamic Creative" service
  reads as "Creative & Design" now, and puffery uses of "optimise" gave
  way to plain verbs ("tuning", "management").
- Cut two negative-parallelism lines ("not vanity metrics" and "operators,
  not junior handlers") and rewrote them as direct statements.
- Added contractions and direct address, so these sections read the way
  the rest of the site does.
- Ran the same pass over the changelog and readme prose from this session.

## [1.16.0] — 2026-08-28

Reworked the Services page onto the three-block capability model, so it
matches the homepage and the agreed positioning.

### Changed

- **page-services.html** regrouped from the six-plate channel list into the
  three blocks: Demand Creation (Performance Media, Social & Influencer,
  Creative & Design), Demand Capture (Paid Search, SEO & GEO), and
  Conversion & Data (Analytics Infrastructure, Lifecycle & Engagement).
  Each block carries a coloured label, a one-line role, and the detailed
  service breakdowns beneath it. Service headings are now h3 under each
  block's h2, for a correct document outline.
- Hero eyebrow and subheading updated to the modular framing.

### CSS

- Added the `.rm-service-block` header styles and a
  `.rm-service-detail--green` swatch to `blog-and-about.css`.

## [1.15.1] — 2026-08-28

### Added

- Linked the blog ("Insights") in the primary navigation and the footer
  sitemap, so all five core pages (home, about, services, contact, blog)
  are reachable from the site chrome, not only through the blog templates.

## [1.15.0] — 2026-08-28

Completed the template hierarchy so every standard page type has its own
template, not only a fallback.

### Added

- **404.html.** A branded not-found page with a search field and links
  back to the homepage and to insights.
- **search.html.** Search results, with the query in the heading, a
  refine-search field, and the post grid.
- **archive.html.** Category, tag, date and author archives, with the
  archive title, term description, and the post grid.
- **home.html.** A dedicated blog index for the posts page, kept separate
  from the index.html fallback.

### Changed

- Extended the conditional stylesheet enqueue in `functions.php` so
  `blog-and-about.css` also loads on archive and search views, which reuse
  the post-grid components. It previously covered only the blog home and
  single posts.
- Added 404 layout styles to `remotive.css`. Confirmed the 404 renders in
  both light and dark mode.

## [1.14.0] — 2026-08-28

Homepage restructured to the agreed site frame, so the layout matches the
performance-marketing and SEO positioning before content is finalised.

### Added

- **The problem we solve** (`#problem`). Four friction points for scaling
  brands: stretched in-house teams, siloed data and CRM, rising
  acquisition costs, and weak attribution.
- **Why Re:Motive** (`#why`). The four differentiators: commercial
  accountability, a team that embeds with yours, regional footprint, and
  next-gen discovery across SEO and GEO.
- **About teaser** (`#about`). A senior-team frame with placeholders for
  genuine team photography and a link through to the full About page.

### Changed

- **Hero** rewritten around the agreed positioning, tightened to current
  homepage style: "Performance marketing & SEO, built to scale Asian
  brands," with a senior-specialist, plug-into-your-team subhead and an
  across-Asia line, with the specific markets carried by the strip below. A new `.is-statement` size keeps the headline in scale.
- **Markets strip** replaces the services ticker (Singapore, Malaysia,
  Thailand, Hong Kong, China).
- **What we do** (`#services`) moved from the six-plate grid to three
  modular blocks: Demand Creation, Demand Capture, and Conversion & Data,
  each with its services and a commercial outcome, and a note that clients
  buy exactly the block they need.
- Proof (work and stats) and the closing CTA are retained.

### CSS

- Extended the wide-measure selectors to `#problem`, `#why`, and `#about`
  so the new sections share the hero's 1320px measure and left edge.
- Added `.rm-problems`, `.rm-blocks` / `.rm-block`, `.rm-whys`, and
  `.rm-about` components. Block colours draw from the theme presets, so
  they switch to the official values in both light and dark mode. Confirmed
  on rendered screenshots in light, dark, and at 600px.
- Fixed primary fill buttons in dark mode. They inherited contrast-bg for
  their background, which equals the dark paper, so they disappeared into
  the page. They now use ink and paper and invert with the mode.

## [1.13.2] — 2026-08-28

A second desktop-layout pass, closing the issues that remained after
v1.13.1. Every fix below was reproduced from live screenshots, traced to
its actual cause, and confirmed on a re-render.

### Fixed

- **CTA section rendered misaligned ("text all over the place").** The
  `.rm-cta__title` used the shorthand `margin:0 0 2rem`, which zeroed the
  left and right margins with `!important` and overrode the
  constrained-layout auto-margins that centre the title. The heading
  pinned to the left edge while the email form and the note below it
  stayed centred, which is what produced the scattered appearance. Fixed
  by restoring `margin:0 auto 2rem`. The emphasised phrase now carries
  `white-space:nowrap`, so the line breaks as "READY TO GET" /
  "UN-IGNORED?" and keeps the accent word whole. Confirmed on a rendered
  screenshot.
- **Middle sections still clamped to the 740px content measure.** The
  v1.9.1 wide-measure fix widened the *direct children* of `#services`
  and `#work`, but the section wrappers themselves kept WordPress's
  default 740px content measure and centred, so those widened children
  sat inside a 740px centred parent and rendered at roughly half width,
  out of line with the full-width hero above. Fixed by widening the
  `#services` and `#work` wrappers themselves to the wide-size token and
  pinning them to the left edge, so the middle content shares the hero's
  left margin and spans the intended width. Confirmed by measuring the
  rendered layout at 48px left and 1320px wide, matching the hero.
- **Plate contents spread vertically.** Each `.rm-plate` used
  `justify-content:space-between`, which pushed the swatch row, the
  heading, and the description apart across the full card height and
  left uneven gaps. Fixed with `justify-content:flex-start`, so the three
  stack tightly from the top while the card min-height keeps the grid
  rows level. This is separate from the equal-column fix in v1.13.1,
  which governs the horizontal tracks. Confirmed on a rendered
  screenshot.
- **Footer columns unbalanced once the width was restored.** With the
  v1.13.1 wide measure applied, the brand column still carried an inline
  34% basis that opened a gap before the first link column, while the
  Contact column stayed narrow. Fixed by rebalancing the flex
  distribution so the brand column sizes to its content, the two link
  columns share the remaining space evenly, and the Contact column
  widens enough for the address and email to hold their own lines. The
  rebalance is guarded behind `@media (min-width:782px)` so it never
  overrides WordPress core's mobile column stacking. Confirmed on
  rendered screenshots at 1320px and 600px.

## [1.13.1] — 2026-08-27

Two real desktop-layout bugs, reported with live screenshots and traced
to their actual cause rather than patched by symptom.

### Fixed

- **Footer never got the wide-measure fix from v1.9.1.** That earlier
  fix widened `.rm-hero`, `#services`, and `#work`'s direct children
  from the theme's own 740px content measure to its 1320px
  wide measure — but `.rm-footer` was never added to that selector,
  since the original bug report was about the homepage's hero/services
  sections specifically and the footer wasn't re-checked at the time.
  Net effect: the footer's 4-column grid has been stuck at 740px ever
  since, giving the Contact column roughly 168px to work with — not
  enough for `hello@remotivemedia.asia` to fit on one line, so the
  `overflow-wrap:break-word` added during the WCAG pass (v1.7.0) did
  exactly what it was told and broke the domain mid-word
  (`hello@remotivem` / `edia.asia`) rather than overflow. The
  `overflow-wrap` rule was never the bug — the column was just never
  wide enough for it to matter. Fixed by adding `.rm-footer > *` to the
  existing wide-measure selector in `remotive.css`. Confirmed via a
  rendered screenshot with the real footer content, not assumed fixed
  from the CSS change alone.
- **Uneven column widths in the six-plate services grid**
  (`.rm-plates`). `grid-template-columns:repeat(3,1fr)` doesn't
  guarantee equal thirds on its own — a bare `fr` track still respects
  its cell's min-content width by default (the implicit minimum is
  `auto`, not `0`), so a track whose content happens to be wider than
  an even third can force that whole column wider than its siblings
  despite every track nominally being "1fr." Fixed with the standard
  hardening for this exact symptom: `repeat(3,minmax(0,1fr))`, which
  removes the implicit minimum and guarantees true equal columns
  regardless of content. Confirmed via `getBoundingClientRect()` on all
  three plates in one row — 235px each, not eyeballed from a
  screenshot.

## [1.13.0] — 2026-08-27

Real icons for the footer/Contact page social links, which previously
were plain text ("Instagram," "LinkedIn," "TikTok"). Built from an
uploaded icon pack — reviewed for licensing per-icon before use, not
assumed blanket-safe just because it was labeled "minimalist icons."

### Added

- **Instagram, LinkedIn, and TikTok icons**, inlined as `<svg>` markup
  (`fill="currentColor"`) in `parts/footer.html` and
  `templates/page-contact.html` — replaces the old text-only
  `core/navigation-link` entries. Source files kept at
  `assets/icons/*.svg` for reference; not loaded at runtime.
- **`.rm-social-link` styling** (`assets/css/remotive.css`): each icon's
  clickable area is exactly 44×44px — confirmed via
  `getBoundingClientRect()`, not estimated from padding values — clearing
  WCAG 2.5.8's target-size minimum with real margin, not just barely.
- **Font Awesome attribution** (`.rm-footer__credit`, next to the
  copyright line in both templates). The LinkedIn icon is CC BY 4.0
  (Font Awesome Free), which requires a credit visible to actual site
  visitors — internal documentation in `resources.md` alone wouldn't
  satisfy that. Instagram and TikTok are CC0 (Simple Icons), no
  attribution required for either.

### Fixed

- `templates/page-contact.html`'s social links previously used hardcoded
  `"url":"#"` placeholders, never wired to Theme Options at all — an
  inconsistency with the footer's already-tokenized links. Now uses the
  same `__REMOTIVE_SOCIAL_*__` tokens as the footer, so both places stay
  in sync with whatever's set in Appearance → Remotive Options.

## [1.12.0] — 2026-08-26

Two market-specific landing pages and an FAQ accordion pattern, built
against real keyword research (`remotivemedia-keyword-selection.xlsx`,
90 keywords across Singapore, Malaysia, and generic clusters). A
companion content package (four blog posts, delivered separately as a
WordPress WXR file) targets the genuinely informational keyword
clusters this pass didn't turn into landing pages.

### Added

- **`templates/page-seo-singapore.html`** and
  **`templates/page-digital-marketing-malaysia.html`** — new custom
  templates, registered in `theme.json`. Each consolidates its entire
  market's commercial-intent keyword cluster (SEO agency, digital
  marketing agency, SEM, social media marketing — 20+ related terms per
  market) into one comprehensive page, deliberately **not** one thin page
  per keyword. That would be a doorway-page pattern and would have
  actively hurt rankings through cannibalization — the wrong call for an
  SEO agency to make on its own site.
- **FAQ accordion styling** for WordPress's native `core/details` block
  (`.wp-block-details` in `assets/css/blog-and-about.css`) — used on both
  new landing pages to address informational sub-queries (SEO cost,
  SEO vs SEM, "do you build websites") inline, without needing separate
  pages for each. Verified interactively (open/close toggle, both
  starting states) before shipping, not just visually inspected once.
- Both new templates added to `remotive_conditional_enqueue_assets()`'s
  secondary-template list, so their CSS only loads where used.

### Verified, not assumed

- `core/details`' exact markup (`<!-- wp:details {"summary":...} --><details class="wp-block-details"><summary>...</summary>...</details><!-- /wp:details -->`)
  confirmed against the official Block Editor Handbook before use — the
  `summary` attribute doesn't need to appear in the block comment JSON
  since it's RichText-sourced from the actual `<summary>` HTML at parse
  time, same as how this theme's headings/paragraphs already work.

### Documented, not silently decided

- **Six Malaysia keywords in the research are web-design terms** (`web
  design malaysia`, `website design malaysia`, etc., combined volume
  9,000+). None are targeted anywhere in this pass — Remotive doesn't
  offer web design as a service, and `page-digital-marketing-malaysia.html`
  says so explicitly in its own FAQ rather than quietly implying
  otherwise to chase search volume. See the companion content package's
  own README for the full reasoning.

## [1.11.0] — 2026-08-26

Four more custom page templates: Services, Case Studies, a generic Legal
page (Privacy Policy/Terms), and Contact — plus a generic `page.html`
fallback that didn't exist before this, so any new WordPress Page
without a selected template now gets this theme's own styling instead of
silently falling back to the parent theme's unstyled default.

### Added

- **`templates/page-services.html`** — expanded version of the
  homepage's six-plate summary, one detailed section per service
  (description + "what's included" list), ending in a CTA.
- **`templates/page-case-studies.html`** — four case-study cards
  (placeholder metrics/content, same pattern as the homepage's Skinlab
  spread — real client work goes here once written up; see
  `upgrading.md`).
- **`templates/page-legal.html`** — for Privacy Policy, Terms, or similar.
  Deliberately minimal: page title, a "Last updated" date (pulled live
  from the page's own post date via `core/post-date`, not hardcoded),
  and readable body typography for whatever the admin actually writes in
  the editor. No fabricated legal text — that's not something to
  generate here.
- **`templates/page-contact.html`** — a form (name/email/message) plus
  a details sidebar (email, office address, social links).
- **`templates/page.html`** — generic fallback for any Page that doesn't
  select one of the above. Previously nonexistent, meaning any new page
  fell through to Twenty Twenty-Five's own default page template,
  outside this theme's design system entirely.
- **`inc/contact-form-handler.php`** — third thin wrapper around the
  shared `remotive_handle_lead_form_submission()` (see v1.10.0), its own
  nonce action and honeypot field so its rate-limit state never collides
  with the About page's form even if both are open in different tabs.
- Four new palette-derived component classes in `assets/css/blog-and-about.css`:
  `.rm-service-detail` (Services), `.rm-cs-card`/`.rm-cs-grid` (Case
  Studies), `.rm-legal`/`.rm-page` (readable long-form content, shared by
  the Legal and generic page templates).

### Fixed

- **Caught before shipping, not after:** the Legal page template
  originally tried to nest a `<!-- wp:post-date /-->` block comment
  inside a `core/paragraph` block's own text content
  (`<p>Last updated: <!-- wp:post-date /--></p>`). Block comments can't
  nest inside another static block's inner content that way — it would
  have rendered as an inert HTML comment, and the date would never have
  appeared. Restructured into a small flex group with the label and the
  post-date block as siblings instead.

### Changed

- `remotive_conditional_enqueue_assets()` (`functions.php`) now checks
  an array of five secondary templates via `is_page_template()`'s native
  array support, rather than one hardcoded slug — confirmed via
  documentation that `is_page_template()` has accepted an array since
  WordPress 4.7, rather than assumed.
- `theme.json`'s `customTemplates` now lists six selectable templates
  (was two).

## [1.10.0] — 2026-08-26

Three new templates added: a blog archive, single post view, and an
About page with a lightbox gallery and contact form. Built after
reviewing three uploaded reference templates for their UX patterns —
see `resources.md` for why none of their actual code was used.

### Added

- **`templates/index.html`** — blog archive, sidebar layout. Uses a real
  `core/query` loop (no hardcoded fake posts) with pagination and an
  empty-state message. Previously the theme had no fallback `index.html`
  at all, which every WordPress block theme is required to have —
  without it, WordPress silently fell back to the parent theme's own
  unstyled version.
- **`templates/single.html`** — individual post view, same sidebar.
- **`templates/page-about.html`** — new custom template (selectable per-
  page via the editor's Template dropdown, registered in `theme.json`):
  a parallax hero, a 6-tile gallery wired to a lightbox, and a contact
  form (name/email/message).
- **`parts/sidebar.html`** — About blurb, a real dynamic recent-posts
  query, and a contact CTA. Shared by both blog templates.
- **`assets/js/lightbox.js`** — built from scratch, not adapted from any
  reference template (most off-the-shelf gallery lightboxes, including
  the ones reviewed for this feature, are mouse-only). Verified via
  automated test, not just code review: opening a tile moves focus into
  the dialog, Tab/Shift+Tab correctly cycles only within the lightbox's
  three controls (a real focus trap), arrow keys navigate between
  images, and Escape closes and returns focus to the exact gallery
  button that opened it.
- **`assets/js/parallax.js`** — subtle scroll-tied transform on the About
  hero's decorative gradient layer, `requestAnimationFrame`-based,
  fully skipped for `prefers-reduced-motion` (same guard pattern already
  used for the hero misregistration hover and the theme-toggle
  transition).
- **`assets/css/blog-and-about.css`** — conditionally enqueued (only on
  the blog templates and the About page, via `is_home()`/
  `is_singular('post')`/`is_page_template('page-about')`), not loaded
  site-wide.

### Changed

- **Refactored the CTA form's security logic into a shared function**
  (`inc/lead-form-handler.php`) rather than duplicating it for the new
  About page contact form. `inc/cta-form-handler.php` and the new
  `inc/about-form-handler.php` are now thin wrappers supplying their own
  nonce action/honeypot field/redirect target to one reviewed code path.
  The shared function now also accepts optional `name`/`message` fields
  (the About form uses them; the CTA form still only sends email).
- **`assets/js/cta-form.js` generalized into `assets/js/lead-form-status.js`**
  — reads a `data-lead-status="remotive_<form_key>"` attribute instead of
  a hardcoded element ID, so both the CTA and About forms share one
  status-message script instead of each needing their own.
- Post/article title styling (`.rm-post-card__title`, `.rm-post__title`,
  `.rm-sidebar__recent-title`) explicitly set to normal case, 700 weight
  — the site's default heading style (900 weight, uppercase, from
  `theme.json`) suits short marketing headlines but reads as shouting on
  natural-language article titles. Caught during visual QA, not code
  review — the first render showed it clearly.

### Verified, not changed

- Confirmed the About hero's parallax gradient layer is genuinely full-
  viewport-width (`getBoundingClientRect()` check), not visually
  constrained — it only *looks* narrower in a screenshot because the
  radial gradient's own falloff fades out before reaching the edges.
- `is_page_template('page-about')` confirmed correct for block-theme
  custom templates (the stored `_wp_page_template` value matches
  `theme.json`'s `customTemplates` "name" field exactly — no file path,
  no extension), rather than assumed.

## [1.9.1] — 2026-08-26

Fixed a real, previously undetected desktop layout bug — content wasn't
actually centered/width-constrained on a real WordPress install, despite
looking correct in every prior QA screenshot. Root cause and fix below.

### Fixed

- **Missing `is-layout-*` classes throughout `templates/front-page.html`,
  `parts/header.html`, and `parts/footer.html`.** `core/group` and
  `core/columns` are static blocks — WordPress outputs their saved HTML
  exactly as written, with no server-side reconciliation against the
  block's declared attributes. Every block comment in this theme
  correctly declared `"layout":{"type":"constrained"}` (or `"flex"`/
  `"default"`), which matters for the Site Editor's own behavior — but
  the actual rendered `<div class="...">` never carried the matching
  `is-layout-constrained`/`is-layout-flex`/`is-layout-flow` class, which
  is what WordPress core's *own* generated CSS actually selects on to
  apply the centering/max-width rules from `theme.json`'s
  `settings.layout.contentSize`/`wideSize`. Net effect: on a real
  WordPress install, none of that centering ever applied — content
  would have rendered pinned to the left edge with the rest of the
  viewport empty on any screen wider than ~740px, exactly matching this
  report's "doesn't meet desktop display standards." Fixed by adding
  the correct class to all 28 affected blocks (a scripted pass across
  all three files, individually verified against the mapped layout
  type, not a blanket find-replace).
- **`<main>` (`templates/front-page.html`) had no `layout` declared at
  all**, relying on implicit default behavior this fix doesn't gamble
  on being correct — now explicitly `"layout":{"type":"constrained"}`
  with the matching class, removing any ambiguity.
- **The hero headline and the services/proof sections were still too
  narrow even after the above fix** — WordPress's own `contentSize`
  (740px, appropriate for reading-width prose) is too narrow for this
  homepage's bold marketing layout; a headline at the hero's display
  size wrapped "MAKE NOISE" onto two lines when confined to it. Added
  an explicit override widening `.rm-hero`'s, `#services`'s, and
  `#work`'s direct children to the theme's `wideSize` (1320px)
  uniformly — not selectively per-element, since this design is
  left-aligned rather than centered, and two different centered widths
  side by side would misalign their left edges against each other. Not
  reliant on WordPress's `alignwide` class cascade, for the same
  "be explicit, don't depend on cascade subtlety" reasoning documented
  elsewhere in this file (see the `ink`/`paper` and `--rm-blend`
  gotchas in `readme.md`). The CTA section was deliberately left at the
  narrower default — a compact, centered form reads better narrow than
  stretched to 1320px.

### How this was caught

Every previous round of visual QA in this project used a hand-built test
harness that never included WordPress core's own auto-generated layout
CSS (the `.is-layout-constrained > *` rules, and `core/columns`'s
`display:flex`) — a real WordPress site loads that automatically; the
standalone harness didn't, so every earlier screenshot looked correct by
accident, for the wrong reason. Rebuilt the harness to include an
accurate approximation of that core CSS (kept in a separate file so
future re-copies of the theme's own CSS can't silently wipe it out again
— exactly what happened once during this same debugging session, caught
before it produced a false result) before re-verifying. Re-tested at
1280/1440/1920px, zero horizontal overflow at any width.

## [1.9.0] — 2026-08-26

Dark mode realigned to `remotive-reporting` — the skill governing every
client-facing deck/report — per explicit request, after confirming the
exact mismatch against the skill's own spec rather than assuming.

### Changed

- **Dark mode background:** `#131118` (near-black, sampled from an early
  concept deck) → `#1a1a2e` (navy, `remotive-reporting` §1.1's primary
  dark background).
- **Dark mode text color:** `#f7f4ec` (cream) → `#ffffff` (pure white,
  matching the skill's "Text on dark" spec — the skill also explicitly
  bans cream/beige backgrounds).
- **Accent 1 (magenta):** `#f0208f` → `#ec008c` (skill's Accent 1).
- **Accent 2 (cyan):** `#00b8e6` → `#00aeef` (skill's Accent 2).
- **Accent 3:** `#f2c200` (yellow) → `#39b54a` (green, skill's Accent 3)
  — a genuine hue change, not a shade adjustment. The `contrast-bg`/
  `contrast-text` pair (CTA band, buttons) updated to match: `#131118`/
  `#f7f4ec` → `#1a1a2e`/`#ffffff`.
- All hardcoded cream `rgba(247,244,236,…)` values used in the
  always-dark CTA band and ticker strip (independent of the `ink`/`paper`
  token swap) updated to white to match.

### Renamed

- **Palette slug `yellow`/`yellow-dark` → `accent-3`/`accent-3-dark`**
  (`theme.json`, `remotive.css`, and the two class modifiers
  `.rm-plate--yellow`/`.rm-stat--yellow` → `.rm-plate--accent-3`/
  `.rm-stat--accent-3` in `templates/front-page.html`). Not named
  `green`: light mode's third accent is genuinely yellow (`#f2f216`,
  from the *separate* `remotive_brand_5.json` brand source), while dark
  mode's is genuinely green. Since both modes share one CSS variable
  name, `green` would mean light mode stores a yellow value inside a
  variable literally called green — the same class of bug that broke the
  CTA section during the v1.2.0 `ink`/`paper` rework. `accent-3` is
  hue-neutral and accurate in both modes.

### Verified, not changed

- **Display font stays Archivo, not the skill's literal Arial Black.**
  Arial Black is largely absent on macOS/iOS/Android by default;
  declaring it would mean most non-Windows visitors see a generic system
  fallback instead, likely worse than intended. Archivo (self-hosted,
  renders identically for every visitor) is the close visual cousin used
  instead. Documented divergence, not an oversight.
- **Newsreader (serif) kept for case-study pull-quotes.** The skill
  specifies no serif anywhere, but its specific rule is "never substitute
  a serif for a title" — Newsreader's use here is scoped to pull-quotes/
  drop-caps only, never a title, so it's technically compliant even if
  not in the skill's full zero-serif spirit. Kept as a deliberate,
  narrow exception rather than stripped for strict alignment.
- **Both the WordPress theme and the standalone static preview file**
  (`remotive-definitive-homepage.html`) updated together — verified via
  a rendered screenshot against the actual `remotive-reporting` color
  values, not just assumed correct from the hex swap.

## [1.8.0] — 2026-08-26

Two passes landed together in this release: four items pulled off the
`upgrading.md` roadmap, and a security audit of everything new. Grouped
by type below rather than by pass, since the audit's fixes touch the same
files the roadmap work just added.

### Security

- **Escape-on-output added for admin-configurable values injected into
  rendered HTML.** `remotive_replace_theme_option_tokens()` (`inc/theme-options.php`)
  previously relied solely on `esc_url_raw()`/`sanitize_email()`/`sanitize_text_field()`
  applied once, at save time, then spliced the stored value directly into
  already-rendered block output via `strtr()`. That's "sanitize on input"
  without "escape on output" — the second half of the standard WordPress
  pattern this project's own security policy names explicitly. Every
  token is now also escaped at the point of output: `esc_url()` for the
  four URL fields (social links, CTA form action), `esc_html()` for the
  contact-detail text fields. No live exploit was confirmed — these
  fields require `edit_theme_options` capability to set in the first
  place — but the fix removes any dependency on exactly how the
  storage-time sanitizer behaves internally, now or in a future
  WordPress version, which is the point of doing both steps
  independently rather than trusting one to cover the other.
- **CRLF header-injection guard added to the CTA form's `Reply-To`
  header** (`inc/cta-form-handler.php`). `sanitize_email()` and
  `is_email()` should already make this impossible — neither permits raw
  control characters in a valid address — but the fix doesn't rely on
  that being true; it explicitly rejects any submitted email containing
  `\r` or `\n` before it ever reaches `wp_mail()`, independent of the
  sanitizer's internal implementation.
- **Rate limiting added to the CTA form endpoint.** It's a public,
  unauthenticated POST target (correctly so — anonymous visitors must be
  able to submit a lead form), which previously had no limit on
  submission frequency beyond the nonce and honeypot. Added a
  transient-based throttle: three submissions per IP per ten minutes.
  Documented as a real but imperfect mitigation (`REMOTE_ADDR` can be
  spoofed or shared behind a proxy) rather than a guarantee — a
  CAPTCHA or WAF-level rate limit would be stronger, but choosing a
  vendor for that is a product decision, not something to pick
  unilaterally here. Proposed, not implemented.
- **`realpath()` containment added to `remotive_import_theme_image()`**
  (`functions.php`). Not currently exploitable — both call sites pass
  hardcoded literal paths, never user input — but the fix guarantees the
  resolved file path can never leave the theme directory even if this
  function is ever called differently later, rather than relying on
  "nothing calls it unsafely today" as the only protection.
- **Verified, not changed:** every nonce/capability check across the
  codebase (Settings API's own `settings_fields()`/`options.php` flow,
  `check_admin_referer()` on the CTA handler, `edit_theme_options` on the
  admin page); no `$wpdb`/SQL usage anywhere in the theme; no
  `unserialize()`; `wp_safe_redirect()` used exclusively, never the
  unsafe `wp_redirect()`; no direct `$_SERVER['HTTP_HOST']` usage in any
  URL construction (`home_url()`/`admin_url()`/`get_stylesheet_directory_uri()`
  used throughout instead); no debug output, hardcoded credentials, or
  backup/dev artifacts anywhere in the package. One item flagged as
  informational rather than fixed: `register_setting()` doesn't specify
  an explicit capability override, so the Settings API's own save-path
  check defaults to `manage_options` while the admin page's own access
  check uses `edit_theme_options` — identical in a stock install (both
  admin-only), so no practical gap today, but noted for any site that
  customizes role capabilities. Not speculatively "fixed" without being
  able to verify the correct mechanism against a live WordPress install.

### Added

- **CTA form now has a real, working default handler** (`inc/cta-form-handler.php`)
  using WordPress's standard `admin-post.php` pattern — nonce-protected,
  honeypot-guarded (verified genuinely invisible via computed CSS, not
  just visual inspection — `getBoundingClientRect()` confirmed 1×1px),
  rate-limited, emails submissions via `wp_mail()` to whatever's set in
  Theme Options, shows an accessible success/error status
  (`role="status"`, WCAG 4.1.3) after redirect. No third-party account
  needed by default; `cta_form_action` in Theme Options can still be
  pointed at Formspree/a CRM/a form plugin instead. Default value for
  `cta_form_action` changed from `#` to `admin_url('admin-post.php')` —
  see Upgrade Notice below.
- **Ticker pauses when scrolled off-screen** (`assets/js/ticker.js`,
  `IntersectionObserver`-based). Small, real performance saving on longer
  sessions; degrades safely (keeps animating continuously) if
  `IntersectionObserver` isn't available.
- **`languages/remotive.pot`** — 30 translatable strings extracted from
  `inc/theme-options.php` and `inc/cta-form-handler.php`. Hand-extracted
  rather than generated via `wp i18n make-pot`, since no WP-CLI/PHP is
  available in this build environment — verified line-by-line against
  strict `.pot` syntax rules afterward (every line is a comment, `msgid`,
  `msgstr`, or string continuation; no stray content) rather than just
  assumed correct. Scope limitation, not a defect: this only covers
  PHP-generated admin-page strings. The homepage's actual copy
  (headlines, service descriptions) lives in static block markup, which
  gettext doesn't reach — real content translation for that would need a
  multilingual plugin (WPML/Polylang) working at the content layer
  instead. Documented in `languages/README.txt`.

### Fixed

- **Theme-toggle flash-of-wrong-mode**, previously a documented known
  limitation (see the removed note in earlier versions of this file).
  `assets/js/theme-toggle.js` runs from the footer (correct, non-blocking
  practice) but that meant a returning visitor whose saved mode differed
  from the server-rendered default saw one frame of the wrong mode before
  it corrected. Fixed with a small inline `<script>` in `wp_head`
  (`remotive_prevent_theme_flash()`, priority 1) that reads the same
  `localStorage` key and applies `data-theme` before first paint. Kept
  deliberately tiny — a handful of statements, no dependencies — so the
  render-blocking cost stays negligible.

### Changed

- **`cta_form_action` default changed from `#` to the native handler's
  URL.** See Upgrade Notice.

## [1.7.0]

WCAG 2.2 AA audit pass, conducted against a structured policy brief. Real
contrast ratios were computed (not estimated) for every text/background
pair currently in use; two confirmed failures found and fixed. Frontend
and admin UI both covered.

### Fixed
- **1.4.3 Contrast (Minimum) — two real failures in light mode**, both
  traced to the client's brand JSON values used as-is for text:
  - `yellow-dark` (`#00aa15`, brand `accent_4`) on the cream background
    measured **2.83:1** — failed even the 3:1 large-text minimum, let
    alone 4.5:1 for normal text. Used for a stat value (large text) but
    still failed at that size.
  - `magenta-dark` (`#f2410f`, brand `accent_6`) measured **3.45:1** —
    passed for large text (stat/spread numbers) but failed 4.5:1 where
    it's also used for smaller text (`.rm-row__link:hover`).
  - Both replaced with darkened, same-hue variants (`#008110`, `#cd360b`)
    that clear 4.5:1, computed via the actual WCAG relative-luminance
    formula, not eyeballed. Dark mode's equivalents were already
    compliant (6.69:1 and 13.60:1) and are unchanged. See `ssot.md` for
    the exact before/after values and reasoning.
  - Also fixed: the CTA email input's border was `rgba(cream, .3)` against
    the always-dark CTA background — computed at 2.54:1, below the 3:1
    required for non-text UI components (1.4.11). Raised to `.5` alpha
    (4.92:1).
- **2.4.11 Focus Not Obscured (Minimum) — the existing fix only covered
  anchor-link jumps.** `:where([id]){ scroll-margin-top:100px }` (added
  in the mobile-first pass) only helps elements *with an id*, and only
  when reached via `#fragment` navigation. Plain Tab-key focus moving to
  any other element near the top of the viewport — most nav links,
  buttons, and footer links don't have ids — wasn't covered. Added
  `html{ scroll-padding-top:100px }`, which WCAG-correctly covers *any*
  focus-triggered scroll-into-view, id or not.
- **2.5.8 Target Size (Minimum) — several small text links were under
  24×24px, at every viewport width, not just the ones already fixed for
  mobile in the earlier pass.** `.rm-footer__links` and
  `.rm-footer__socials` had no padding at all outside the mobile
  breakpoint; `.rm-section-link` ("All services →") and `.rm-row__link`
  ("View case study →") had no padding on the actual anchor at any width.
  All four now have adequate padding on the `<a>` itself, applied
  unconditionally rather than only under `max-width:900px`. The
  mobile-specific override for `.rm-nav__links` that's now redundant with
  the corrected base rule was simplified rather than left duplicated.
- The (currently unused) admin toggle-switch component's track was 22px
  tall, under the 24px minimum. Bumped to 24px pre-emptively since it's
  documented as "ready for the first boolean setting" — no reason to ship
  it already non-compliant.

### Added
- **2.4.7 Focus Visible — a global "sandwich" focus ring** (`.rm-nav
  :focus-visible`, `.rm-hero :focus-visible`, etc. in `remotive.css`):
  a white outline plus a black box-shadow ring around it. This design has
  interactive surfaces spanning near-black, cream, cyan, magenta, and
  yellow — no single fixed outline colour can guarantee 3:1 contrast
  against all of them, but a light+dark pair guarantees at least one ring
  is always visible regardless of what's underneath. Scoped to the
  theme's own content regions so it can't affect wp-admin chrome or other
  plugins' UI.

### Verified, not changed
Computed and confirmed already-passing rather than assumed: every dark
mode text/background pair (17.04:1 body text, 6.69–13.60:1 across the
three stat colours); light mode's body text and cyan-dark (15.17:1,
10.50:1); the CTA section's fixed `contrast-bg`/`contrast-text` pairing in
both modes (17.04:1); the header/hero landmark structure (a `<header>`
nested inside `<main>` correctly does *not* get an implicit ARIA `banner`
role per the HTML-AAM spec, so there's no duplicate-landmark issue despite
two `<header>` elements existing on the page); the CTA email input's
`aria-label` (already present, not relying on the placeholder as a sole
label — one of this policy's explicitly named common failures, already
avoided); no drag-only interactions, data tables, media, or authentication
flows exist in this theme, so several whole sections of the policy
(2.5.7, tables, multimedia, 3.3.8) are not applicable.

## [1.6.0]

Rebuilt the Theme Options admin page's UI against a card-based/tabbed
design system spec. Data layer (option storage, defaults, sanitization,
the token-substitution mechanism that gets values onto the front end) is
completely unchanged — this release is presentation-layer only.

### Changed
- Settings page no longer uses WordPress's default `do_settings_sections()`
  table renderer. Now a card-based layout with three tabs (Contact &
  Socials / Call-to-Action / Display), each holding one card of field
  rows. `add_settings_section()`/`add_settings_field()` calls removed
  (they only fed the old renderer); field/tab data now lives in
  `remotive_theme_options_tabs()`, a single array the new renderer reads
  from. `remotive_sanitize_theme_options()` is intentionally still
  independent of that array — see `readme.md`.
- "Default colour mode" is now a visual-selector (three clickable cards
  with a colour-swatch preview) instead of a plain radio button list.

### Added
- `assets/css/admin-theme-options.css` and `assets/js/admin-theme-options.js`,
  loaded only on the Remotive Options page via its specific hook suffix
  (not every wp-admin screen).
- Real WAI-ARIA "Tabs" pattern: `role="tablist"`/`"tab"`/`"tabpanel"`,
  roving `tabindex`, arrow-key/Home/End keyboard navigation. Verified with
  an automated keyboard-navigation test, not just assumed from the markup.
- Active tab persists via `sessionStorage` across the save-triggered page
  reload, so saving doesn't bounce the admin back to the first tab.
- A toggle-switch CSS component, ready for the first genuinely boolean
  setting this theme adds — none exists yet, so nothing currently uses it
  (see `readme.md`; noted rather than silently omitted, since the design
  spec this was built against assumed at least one boolean option).

### Fixed
- Caught during visual QA, not just code review: the visual-selector
  cards' colour swatches, titles, and descriptions are `<span>` elements,
  which are inline by default and silently ignore `width`/`height` — the
  swatches were rendering as invisible slivers instead of 28×28px colour
  squares. Fixed with explicit `display:block` on each.

## [1.5.0]

WordPress modern-standards compliance pass, conducted against a structured
rules brief (security, stability, compatibility, performance,
accessibility, maintainability — in that priority order). Not a
WordPress.org directory-submission pass; this theme is site-specific and
not distributed there (see `ssot.md`).

### Fixed
- **Settings API validation errors were silently discarded.** `inc/theme-options.php`
  called `add_settings_error()` on invalid input, but `remotive_render_theme_options_page()`
  never called `settings_errors()` to actually display it — the error was
  stored and then lost. Now shown correctly.
- **`style.css` had no `License`/`License URI` header.** Present in
  `readme.txt` already, but the theme's own required header block didn't
  have it — now does (`GPL-2.0-or-later`, matching the parent theme).

### Added
- **Skip link + `<main>` landmark.** Neither existed before this pass. A
  skip link (`parts/header.html`, first focusable element on the page) now
  jumps to a `<main id="main">` landmark wrapping all of
  `templates/front-page.html`'s content — required for keyboard/screen-reader
  users to bypass the nav, and the expected semantic structure regardless.
- **Underlined links within body/comment content.** `theme.json` sets a
  global "no underline" style for links, correct for this theme's nav/button/component
  links (distinguished by colour, hover, or an icon instead) but would have
  also suppressed underlines in any future blog post or comment content,
  where WCAG 1.4.1 wants links distinguishable by more than colour alone.
  Added a scoped override for `.entry-content a`, `.wp-block-post-content a`,
  `.comment-content a` — affects nothing on the current homepage, correct
  by default the moment real content appears.
- **Explicit `add_theme_support('title-tag')` and `add_theme_support('automatic-feed-links')`**
  in `functions.php`. Twenty Twenty-Five already declares both and this was
  never a live bug, but declaring them in the child theme too means neither
  is silently lost if the parent theme is ever swapped.
- **`load_theme_textdomain()`** call + a `languages/` folder placeholder —
  no `.pot` file exists yet (tracked in `upgrading.md`), but the loading
  mechanism is now in place for when one is generated.
- **Full gettext wrapping in `inc/theme-options.php`.** Every user-facing
  string on the Theme Options admin page (section headings, field labels,
  descriptions, the error message) now goes through `__()`/`esc_html__()`
  with the `remotive` text domain. Previously none of them did, despite
  the text domain being declared in `style.css`.
- **`resources.md`** — consolidated license/copyright record: the theme's
  own GPL license, each bundled font's SIL OFL license and upstream
  source, and an explicit note that the two logo image files are
  proprietary (Remotive Media Asia's own brand assets, not GPL, and not
  required to be).
- **Privacy and Upgrade Notice sections in `readme.txt`.** Documents what
  the theme does and doesn't collect (nothing collected by the theme
  itself; `localStorage` for the toggle stays client-side; the CTA form
  sends data wherever the admin points it, which is disclosed as
  configurable, not fixed).

### Verified, not changed
Several ruleset items were checked and found already compliant, so
nothing needed to change: nonce/capability checks on the settings page
(Settings API handles this), sanitization on save, no deprecated function
usage, no admin-bar suppression, no stray VCS artifacts, consistent LF
line endings throughout, cache-busted asset versioning via `filemtime()`,
scripts enqueued non-render-blocking (footer placement), and the existing
`rm-*` CSS class prefix — which is outside this ruleset's namespacing
requirement, since that rule's scope is explicitly PHP functions/constants/option
keys/script handles, not arbitrary CSS class names.

## [1.4.0]

Mobile-first audit pass, conducted against a structured audit brief.
Full findings, risk classification, and rationale are in the audit
response; summarized here.

### Fixed
- **CTA section background bug (found during the audit, not originally in
  scope).** The v1.2.0 dark-mode rework swapped what the `ink`/`paper`
  palette slugs mean (ink became the light/cream value, paper became
  near-black), but the CTA band's `backgroundColor:"ink"` /
  `textColor:"paper"` block attributes were never updated to match —
  producing a light cream CTA band in the middle of an otherwise-black
  page in dark mode, the site's default. Same root cause made the CTA
  email input's typed text color effectively invisible against the CTA
  background. Fixed by adding two new palette entries, `contrast-bg`
  (`#131118`) and `contrast-text` (`#f7f4ec`), deliberately **not**
  overridden by the light-mode CSS block, so they stay constant across
  both modes — matching the original intent of an always-dark CTA band.
- **Global button style had the identical bug.** `theme.json`'s
  `styles.elements.button` used the same swappable `ink`/`paper` vars,
  meaning every solid button site-wide (nav CTA, hero CTAs) rendered as a
  light cream pill instead of the intended dark solid button, in dark
  mode. Repointed to the same fixed `contrast-bg`/`contrast-text` tokens.
- CTA submit `<button>` had no explicit padding (browser-default sizing,
  well under a usable tap target). Now matches the input's proportions.
- CTA email input font-size was 15.2px, under the 16px threshold that
  prevents iOS Safari's auto-zoom-on-focus. Bumped to 16px.
- Sticky nav (~84px+) had no `scroll-margin-top` on in-page anchor
  targets, so clicking a nav link scrolled the destination heading
  directly behind the sticky header. Added a zero-specificity
  `:where([id])` rule.
- Theme-toggle button's clickable area was only as tall as its content
  (~24px), below usable tap-target size. Added padding + min-height.
- Nav link tap targets (mobile overlay + footer) had ~23px effective
  height. Increased padding-block within mobile breakpoints only —
  desktop's tighter spacing is untouched.
- `.rm-row` and `.rm-spread` had `grid-template-columns` overrides on
  `display:flex` elements (dead CSS — Columns blocks are flex, not grid).
  For `.rm-spread`/`.rm-stats` this left a real, if narrow, 782–899px gap
  where the intended stacking never fired; for `.rm-row` it was inert
  since core's own stacking already covered its target range. Replaced
  with the flex equivalent (`flex-direction:column`).
- Footer's "About" nav link pointed to `#about`, which didn't exist
  anywhere on the page. Added the anchor to the footer's brand column.

### Added
- `aria-label` on the theme-toggle button — previously its accessible
  name was just "Dark"/"Light" (current state), ambiguous about what
  clicking it does.
- `overflow-wrap:break-word` on footer contact text as a defensive
  measure against future longer content (current content already fit at
  every tested width).
- Two new theme.json palette entries: `contrast-bg`, `contrast-text` —
  see "Fixed" above.

### Changed
- Hero display font-size floor lowered from a constant 48px (for every
  viewport under 600px) to a fluid `clamp(2.25rem, 8vw, 6.75rem)` —
  previously the headline stopped shrinking at 600px and stayed flat all
  the way down to 320px; now it continues scaling to a lower floor (36px)
  specifically for narrow phones. Desktop sizing (the `6.75rem` max, and
  everything above 600px) is byte-for-byte unchanged.

### Not changed (flagged, not fixed — see audit response for reasoning)
- CSS cascade architecture remains desktop-first (base rules +
  `max-width` overrides) rather than a `min-width`-based mobile-first
  structure. Converting it would touch nearly every rule in
  `remotive.css` for no change in rendered outcome (which was already
  mobile-safe) — judged not worth the risk for this pass. Revisit only
  if there's a specific reason to.
- Case-study proof rows (`.rm-row`) still fully stack to 3 lines on
  mobile (core WordPress default behavior) rather than staying a compact
  horizontal row further down. This is a design decision, not a bug —
  flagged for manual/design review rather than unilaterally building
  custom mobile-specific layout logic for it.

## [1.3.0]

### Added
- **Theme Options admin page** (Appearance → Remotive Options):
  contact email, three address lines, three social URLs, the CTA form's
  submission URL, and a default colour-mode setting (dark / light / match
  the visitor's device). New file: `inc/theme-options.php`.
- `render_block` filter that substitutes `__REMOTIVE_*__` tokens in
  rendered block output with live option values — the mechanism that gets
  admin-entered values into the otherwise-static block templates. See
  `readme.md` for why this exists instead of the official Block Bindings
  API (Navigation Link and Custom HTML blocks aren't bindable as of this
  writing).
- `assets/js/theme-toggle.js` now reads `window.remotiveThemeOptions.defaultTheme`
  (set via `wp_add_inline_script`) instead of always defaulting to dark,
  and supports a `system` mode that checks `prefers-color-scheme`.

### Changed
- `parts/footer.html`: contact email, address lines, and social links
  replaced with `__REMOTIVE_*__` tokens (values now come from Theme
  Options; front-end output is identical until someone changes a setting).
- `templates/front-page.html`: CTA form's `action` attribute replaced with
  `__REMOTIVE_CTA_FORM_ACTION__` token, same mechanism.
- Default values for every new option match exactly what was previously
  hardcoded, so installing this update changes nothing on the front end
  until a setting is actually edited.

## [1.2.0]

### Fixed
- **Dark mode was not actually dark.** Background was a cream `#f7f4ec`
  (light) instead of black. Replaced with `#131118`, colour-sampled directly
  from the original Overprint Bold concept deck's screenshots, plus matching
  `card`/`paper-2` surfaces and lightened `-dark` accent variants (the old
  darker variants were tuned for text-on-cream and were nearly invisible on
  black).
- **`mix-blend-mode:multiply` silently broke on the new black background.**
  Multiply darkens toward whatever's underneath, so on black it rendered the
  hero misregistration hover effect and the hero watermark as effectively
  invisible. Introduced a `--rm-blend` custom property (`screen` for dark,
  `multiply` for light) so the effect renders correctly in both modes.
- Nav background was hardcoded to a cream `rgba()` value regardless of
  active theme; now derives from `var(--paper)` via `color-mix()`.

### Changed
- Light mode's background changed from pure white (`#ffffff`, the brand
  JSON's literal `primary` colour) to cream `#f7f4ec`, per explicit request
  — the only token overridden from the brand spec's literal values.

### Removed
- The "Plate 01 — Cyan" / "Plate 02 — Magenta" / etc. labels on each service
  card in the "What we do" grid. Kept the colour swatch, dropped the
  confusing print-jargon text label.

### Added
- `screenshot.png` (1200×900) — was missing entirely; WordPress's theme
  picker had nothing to show.
- This documentation set (`readme.txt`, `readme.md`, `ssot.md`,
  `upgrading.md`, `changelog.md`), replacing the single `README.md` from
  earlier versions.

### Corrected (content, not code)
- Footer address updated from a placeholder ("Kuala Lumpur, Malaysia") to
  the actual registered entity address (Singapore), sourced from an ACRA
  business profile. See `ssot.md` for the canonical record.

## [1.1.0]

### Added
- Light/dark mode toggle in the site header. Dark (default) is the CMYK
  "Registration" direction; light is Remotive Media Asia's official brand
  system (`remotive_brand_5.json`) — Saira typeface, brand accent colours.
- Saira self-hosted as `.woff2` (matching the existing GDPR-conscious
  approach already used for Archivo/Newsreader).
- `assets/js/theme-toggle.js` — persists the visitor's choice via
  `localStorage`; first-time visitors default to dark.
- Six brand accent colours mapped onto the same six service "plates" dark
  mode already uses, so the printing-plate metaphor holds in both modes.

## [1.0.0]

### Added
- Initial release. Child theme of Twenty Twenty-Five implementing the
  "Registration" homepage direction as real WordPress block templates
  (`templates/front-page.html`, `parts/header.html`, `parts/footer.html`).
- `theme.json` design tokens: CMYK-inspired colour palette, Archivo +
  Newsreader (self-hosted, no Google Fonts CDN calls).
- `functions.php`: component CSS enqueue, one-time logo/favicon bootstrap
  from bundled brand assets into the media library.
- Almost entirely real, editable core blocks (Group, Columns, Heading,
  Paragraph, Navigation, Quote, Site Logo); three intentional Custom HTML
  blocks for the hero headline's hover effect, the ticker marquee, and the
  CTA form — documented in `readme.md`.

## Language layer build (separate line of work, entries as written there)

Numbered 1.89.7 to 1.92.1 in that build; the numbers do not match this repository's 1.90.0 to 1.92.0. All of it is included in 1.104.0.

## [1.92.1] — 2026-10-04

### Fixed

- **The FAQ no longer states numbers that go stale.** Two answers said the team
  page lists "six people" (there are ten) and that "Thirteen engagements" are
  written up (there are 14). They now read the live roster size and the number
  of published case studies (`__REMOTIVE_TEAM_COUNT_LC__`,
  `__REMOTIVE_CASE_COUNT__`, `remotive_case_study_count()`), so they follow as
  people and case studies are added. The FAQ structured data is parsed from the
  same template and resolves the same tokens, so it carries the live words and
  never a raw token. The three translations changed from fixed words to count
  patterns, so they follow the numbers too, with "ten" and "Ten" cased as the
  English sentence uses them. Checked by adding an 11th member and a 15th case
  study in the test site: the English and all three translated FAQs, text and
  structured data, followed both.
- **Translated FAQ pages published their FAQ structured data in English**
  (questions and answers) beside a translated page. It is now rebuilt from the
  translated page, so it matches what the visitor reads exactly: 11 of 11 entries
  in each language.

### Not changed, and why

- **The structured-data service names stay "Demand Creation / Demand Capture /
  Conversion & Data".** 1.92.0 called them stale; that was wrong. They match the
  visible Services page, whose three sections still use exactly those names.
  Renaming them alone would make the structured data contradict the page it
  describes. The Services page and the home page describe the services with two
  different models (three blocks versus Fix / Found / Scale); that is a content
  decision, not a code fix.
- English: 41 of 42 pages are byte-identical to 1.92.0; `/faq/` differs in
  exactly the two answers and its structured data.

## [1.92.0] — 2026-10-01

### Added

- **Every page is now translated** into Bahasa Melayu, Simplified Chinese and
  Traditional Chinese: all 37 pages that have a search listing (home, about,
  services and its six pages, case studies and its 14 pages, 7 articles, blog,
  FAQ, team, contact, privacy, terms), 1,193 strings per language, each
  language written natively. The thank-you page is left English on purpose: it
  is noindex, and a noindex URL does not belong in a sitemap.
- **Tools > Translations**, a translation management system built into the
  theme, with no plugin (`inc/i18n-admin.php`). A scanner that finds every
  translatable string on every page by walking each page with the same code
  that translates it, so the list cannot drift from what the site renders; a
  per-page editor with the English beside each string, the page's search title
  and description, and a Live switch; draft and approved states; an all-strings
  search; CSV and JSON export and import (imports arrive as drafts, and only
  text the site really contains is accepted); and a list of saved translations
  the site no longer uses. Edits live in three small tables (translations,
  strings seen, per-language page state) and are laid over the shipped
  dictionaries, so a theme update never overwrites them. Administrators only,
  nonce-checked, text sanitised to plain text.
- Number patterns (`%n%`, `%count%`) keep sentences that quote a changing figure
  translated when the figure changes.

### Fixed

- **A second `<title>` printed by Rank Math stayed English** on translated pages;
  all titles are now replaced. Found by testing against the real Rank Math
  1.0.279 instead of an emulation.
- **Rank Math's Article structured data** kept the English headline, description,
  language and URL; it now becomes the translated page.
- The blog index (WordPress's posts page, an archive rather than a page) was not
  recognised and redirected to English; it is now translated and live.
- The comments-feed link named the English post and pointed at its English feed;
  it is removed from translated pages.
- Lone punctuation between inline elements (`,` `;` `:`) uses the Chinese marks,
  as the full stop already did.

### Verified

- Every English page, robots.txt and both sitemaps were rendered with the original
  1.89.7 theme and with this one in the same site, with the real Rank Math: 44 of
  45 responses are byte-identical once the footer selector and hreflang tags are
  removed; the 45th is robots.txt, which gains one `Sitemap:` line.
- 1,594 checks across all 111 translated pages (routing, canonical, hreflang,
  titles, descriptions, `lang`, no leftover English, every internal link resolving
  and staying in language, selector, fallbacks, sitemap), 39 checks of head and
  structured data against the real Rank Math, 41 end-to-end checks of the
  management screen including access control and sanitising.

### Known limits

- The thank-you page after a form submission is English.
- (Fixed in 1.92.1: the first two of these.) Three statements in the **English** copy were out of date and were translated
  faithfully rather than changed: the FAQ says the team page lists "six people"
  (there are ten) and "Thirteen engagements" are written up (there are 14), and
  the home page's structured data still names the three services "Demand Creation", "Demand Capture" and "Conversion & Data" (the old model, before Fix / Found / Scale). Those names are also left untranslated in the structured data.
- Written natively but not reviewed by a native speaker; a review before the
  texts are published is recommended.

## [1.90.0] — 2026-10-01

### Added

- **Bahasa Melayu, Simplified Chinese and Traditional Chinese versions**
  at `/ms/`, `/zh-hans/` and `/zh-hant/`, beside the unchanged English site.
  A language layer, not a set of copies: the English content is delivered four
  ways at once (page bodies in the database, copy hardcoded in templates, theme
  options, PHP-generated HTML), and the template-driven pages have no stored body
  to copy, so separate WordPress pages per language would have missed most of
  it. `inc/i18n.php` removes the language prefix from the request before
  WordPress parses it, so `/ms/team/` is served by the same page as `/team/`;
  the rendered page is then translated from per-language dictionaries
  (`inc/i18n/ms.php`, `zh-hans.php`, `zh-hant.php`) by text node. No posts,
  pages, options or rewrite rules are created: verified by checksumming the
  posts, postmeta, options, users and terms tables before and after about 35
  language requests, with zero rows changed.
- **First content set:** the header and footer, and the Home, Team and Contact
  pages, 209 strings per language with SEO titles and descriptions, written
  natively for each language (Simplified and Traditional are not conversions of
  one another). The remaining pages are untranslated by design; their prefixed
  addresses redirect (302) to the English page, so a translated URL never shows
  an English body, and links to them stay on English.
- **SEO per language:** each translated page has its own title and description
  (set explicitly, because the live ones come from Rank Math settings), a
  self-referencing canonical, `<html lang>` and `Content-Language`, and
  `hreflang` between every language that has the page (`en`, `ms-MY`,
  `zh-Hans`, `zh-Hant`, `x-default`; script codes for Chinese, not country codes),
  also printed on the English page so the relationship is mutual. Open Graph
  and Twitter tags follow. The page's own structured data (WebPage node and
  breadcrumb) becomes the translated page, while the Organization and WebSite
  stay one shared entity. Worked against whichever of core or Rank Math prints
  the tags, because it edits what was printed.
- **`/sitemap-languages.xml`** lists every translated URL with its alternates,
  advertised in robots.txt so it is found independently of any SEO plugin, and
  offered to Rank Math's sitemap index where that hook exists (not verified
  against the real plugin). The existing sitemap is untouched.
- **Language selector** in the footer's bottom bar: English, Bahasa Melayu,
  简体中文, 繁體中文, each linking to the equivalent page where it exists and to
  that language's home page where it does not.
- **Typography for the new languages:** CJK-capable font stacks, tracking off
  and looser line height for Chinese, and word-breaking for Malay's longer words,
  all scoped to a non-English `<html lang>`.
- An administrator can append `?rm_i18n_missing=1` to a translated page to list
  its untranslated strings in an HTML comment.

### Changed

- The colour-mode toggle reads its Light / Dark words from the button when a
  language version supplies them, so its label no longer flips back to English
  on click. English supplies none and behaves exactly as before.
- Footer: one extra block (the language list). The header is untouched.

### Fixed during testing

- **The selector was first placed in the header and was moved to the footer.**
  The header has no spare room at most widths: adding to it made the nav wrap
  and grew the header from 85px to 123px at 1100px wide, collapsed the
  hamburger on phones, and pushed the Malay page 11px past the screen edge.
  Compared against the original header at 13 widths before it was moved.
- WordPress resolves the path from `PATH_INFO` ahead of `REQUEST_URI` when the
  server sets it, so the prefix is now removed from both.
- WordPress's own canonical redirects (a missing trailing slash, `?p=ID`,
  `index.php`) computed the English address and dropped visitors out of their
  language; they now keep it. An upper-case prefix (`/MS/`) redirects to the
  lower-case one so each page has one address.

### Known limits

- 27 long-form pages remain untranslated (6 services, 14 case studies, 7
  articles, about 19,000 words).
- The thank-you page after a form submission is the English one.
- Structured data for the home page's Service nodes stays English.
- Written and checked here; a native speaker should review the Malay and both
  Chinese texts before they are published.

## [1.89.7] — 2026-09-30

### Fixed

- **Elfie's portrait showed where his photograph ended.** 1.89.4 kept his face
  at the set's size (31%), which shrank a chest-up photo until its own left
  and right edges sat inside the tile at 16% and 84%: the sleeves stopped in
  two straight vertical lines and his torso read as a boxed-in block, where
  every other tile has shoulders that round off or run off the edge. That
  release's note called the straight sides acceptable "at tile size"; on the
  live page they are plainly not, and that judgement was wrong. Rebuilt with
  `--cover`, which fills the tile from the photograph edge to edge (sides 0%
  to 99.9%, bottom 99.9%), so there is no photograph edge left to see. The
  face is 46% of the tile, the same trade Ally's tile already makes, and
  checked against both the light and the dark tile with clean hair edges.
  `COVER` now records both `ally` and `elfie`.
