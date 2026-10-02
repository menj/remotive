# Upgrading — roadmap & future plans

This is **not** a version-upgrade guide (there are no breaking changes to
migrate yet — see `changelog.md` for what actually shipped). This is a
running list of planned work, ideas, and known gaps, roughly ordered by
what would matter most to fix or build next. Nothing here is scheduled or
committed; it's a backlog for whoever picks this theme up next.

## Deploying the ad landing pages (v1.90.0 – v1.92.0)

Do these once after the release reaches the site:

- **Pretty permalinks must be on** (Settings → Permalinks, not "Plain"); the
  `/ms/`, `/zh-cn/` and `/zh-tw/` paths are rewrite rules.
- The next admin load creates the pages (`seo-audit`, `google-ads-management`,
  `paid-social-advertising`, `audit-requested`) and flushes rewrites. Use
  Appearance → Theme Options → Create missing pages if any is missing.
- **Open each URL once** (4 pages × 4 languages) and confirm none is a 404.
- Submit a test form; check the stored lead (service and campaign lines),
  the notification email, the `/audit-requested/` redirect and the
  `remotive_lead` event in the tag manager.
- Paste the Pexels key under Theme Options → Integrations if image sourcing
  from the live site is wanted; nothing on the front end needs it.
- Have native speakers review the Malay and both Chinese versions before paid
  traffic is pointed at them. Add approved client results to the pages: real
  proof is the biggest conversion lever still missing.
- A site that saved its own country-ticker list keeps it; add Vietnam there
  by hand.

## Shipped since this file was last revised (v1.66.6 – v1.69.2)

Moved here from the roadmap rather than deleted, so the reasoning survives.

- **Country ticker made configurable** (v1.66.7, v1.66.8) — twelve options
  under its own Theme Options tab.
- **CTA coverage and consistency** (v1.67.0, v1.67.3–v1.67.8) — bands added
  to the Insights index, single posts and archives, which previously ended
  with no prompt; colour variants and a centred layout for button-only bands.
- **`/work/` → `/case-studies/` slug migration** (v1.68.0) — runs once on the
  next admin load; child pages follow.
- **Security hardening module** (v1.68.1) — see `readme.md` → Security.
- **Branded error handling** (v1.69.0, v1.69.2) — fatals and non-fatals.
- **Scroll motion** (v1.69.1) — CSS scroll-driven animations with an
  observer fallback.

### Still outstanding from the log review

Server-level, unchanged by any theme release:

- Block `103.138.189.98` at firewall or `.htaccess` (active brute force)
- Deny `xmlrpc.php` and `wp-admin/install.php` at server level, so PHP never
  executes for those requests
- Purge CDN/browser cache to clear 404s on the pre-consolidation JS files
  (`theme-toggle.js`, `ticker.js`, `webmcp.js`)
- `WP_DEBUG_DISPLAY` false and `display_errors` off in production
- Remove the duplicate `define( 'WP_DEBUG', ... )` in `wp-config.php`

### Documentation debt cleared

`readme.md`, `readme.txt` and `ssot.md` had drifted roughly three days
behind the code by v1.69.2 — none of the ticker, CTA, security, error
handling or motion work appeared in any of them. Brought current in the
same release. Worth a standing check: **if a release adds a Theme Options
field or an `inc/` file, it is not finished until `readme.md`'s file map,
`readme.txt`'s FAQ, and `ssot.md`'s configurable-values list say so.**

## Near-term (would improve the current build)

- **Validate the WebMCP integration from theme v1.44.1 on a live HTTPS WordPress installation with a
  supporting browser/agent.** Confirm that `document.modelContext` is exposed,
  the four always-available tools register, the contact/audit preparation tool
  appears only while its form exists, and unsupported browsers remain free of
  console errors. Exercise `GET /wp-json/remotive/v1/search` with empty,
  overlong and limit-boundary inputs; verify that only published posts/pages
  are returned. Finally, confirm that prepared lead forms are highlighted but
  never submitted until the visitor presses the button.
- **Track the WebMCP specification and browser implementation status.** The
  current adapter follows the experimental `document.modelContext` imperative
  API plus declarative form attributes. Before changing its tool schemas,
  return shapes, registration lifecycle or Permissions Policy handling, check
  the current specification and re-run the live validation above. Do not add a
  client-side polyfill that claims native agent integration where none exists.

- **Add Team and Case Studies to the navigation by hand.** A saved
  Navigation block lives in the database, so template changes to
  `parts/header.html` and `parts/footer.html` do not reach it. Edit the menu
  in Appearance → Editor → Navigation, or delete the saved menu to fall back
  to the template's version. The Team page has been missing from the live
  header since v1.56.0 for this reason.
- **Rename the Work page rather than recreating it.** v1.59.0 moves it to
  /case-studies/ and takes the thirteen case studies with it. Renaming in the
  editor makes WordPress record the old slug and redirect it; recreating the
  page loses that.
- **Create the pages added since your last update**: Team (v1.56.0), FAQ
  (v1.61.0), and set the About page's template to "About (company story +
  contact form)". Appearance → Theme Options → Create missing pages.
- **Fill in the legal drafts before linking them anywhere public.**
  `docs/legal-privacy-policy.md` and `docs/legal-terms-of-service.md` contain
  CONFIRM markers for facts the theme cannot know, and both need a
  Singapore-qualified lawyer to review them. They are already linked from the
  footer, so an empty page is publicly reachable until they are written.
- **Decide the enquiry retention period** on Appearance → Theme Options →
  Display. It has to match whatever the privacy policy states.
- **Apply the cache headers in docs/cache-headers.md.** The theme cannot
  set Cache-Control; the audit's `None` and `7d` values come from the
  server. The scripts report no lifetime because they are requested with a
  `?ver=` query string that typical extension-only rules miss. Every theme
  asset URL is versioned by file modification time, so the one-year
  immutable policy in that file is safe.
- **Decide whether Google Sign-In belongs on the front page.** The
  accounts.google.com client is roughly 99KB of JavaScript loaded by a
  plugin, and it sits on the measured critical path. The theme now
  preconnects the origin, which only shortens connection setup. If
  sign-in is not needed for anonymous visitors, restrict the plugin to
  the pages that use it, or add
  `add_filter( 'remotive_preconnect_google_accounts', '__return_true' );`
  if it is injected as a raw tag and should stay.
- **Re-run PageSpeed Insights and Print Preview after deploying v1.45.0.**
  Confirm that the hero mark preload appears in the initial front-page HTML
  with desktop media and high fetch priority, self-hosted font rules use
  `font-display: swap`, the component stylesheet is not render-blocking, the
  custom logo selects a small responsive source, and deferred/conditional
  theme scripts no longer lengthen the critical request chain. Test A4 and
  Letter output in the production browser: the featured case, case rows, stats,
  About/team section and footer must not fragment into blank or detached
  sheets. Performance results vary with the host, plugins and cache state, so
  record the tested URL, device profile and date with the result.

- **Configure production browser-cache headers outside the theme.** Give
  fingerprinted/versioned theme assets a long lifetime (typically one year
  plus `immutable`), choose an appropriate long-lived media-upload policy, and
  inspect the cacheability of WordPress core and plugin assets. Apply this in
  the host, caching plugin, reverse proxy or CDN that actually serves the
  response. The theme cannot set reliable headers for uploads, core/plugin
  files, or Google Sign-In, and a `.htaccess` file inside the theme would be an
  incomplete Apache-only workaround. Purge the cache after rollout, then use
  response headers and a fresh PageSpeed run to verify the policy.

- **Confirm the v1.25.1 rendered-verification results on a live install.**
  The verification ran the theme's real templates and stylesheets in
  headless Chromium against a harness that emulates WordPress's emitted
  layout CSS, with a placeholder 120px logo. Sixty checks (six pages,
  ten widths from 320px to 1900px: overflow, nav-row fit, left-edge
  alignment, grid column counts) pass. Worth one confirming pass on a
  live WordPress install with the production logo, since the harness
  approximates core's markup rather than running it.

- **Real photography for the featured case study, the Case Studies page's
  four cards, and the About page gallery.** All currently use CSS
  gradient placeholders instead of actual images. `assets/js/lightbox.js`
  has an inline comment marking exactly what needs to change (reading an
  image URL instead of copying computed CSS) once real photos exist.
- **Replace hardcoded services/case studies with custom post types.**
  Right now the homepage's six service "plates," `page-services.html`'s
  detailed breakdown, and `page-case-studies.html`'s four cards are all
  hand-written block markup, maintained by hand in three separate places.
  The theme's classic-PHP predecessor (`remotive` v1.0.0/v1.1.0,
  pre-block-theme) had `remotive_service` and `remotive_case_study`
  custom post types for exactly this — worth reintroducing here once
  there's enough real content that hand-editing three templates in
  parallel stops being convenient. This would also make individual
  case-study detail pages possible (currently the Case Studies page's
  "View case study →" links go nowhere — `href="#"` — since there's no
  per-case-study page to link to yet). Deliberately not built
  speculatively ahead of that need.
- **Stronger form bot resistance.** `inc/lead-form-handler.php` (shared
  by the CTA, About, and Contact forms) has a nonce, a honeypot, an
  Akismet check (v1.67.2) and a per-IP rate limit (3/10min) — and since
  v1.68.1 `inc/security.php` applies a comparable limit to login attempts
  — but none of that stops a scripted client
  that first loads the real page for a valid nonce, then submits
  repeatedly at a slower rate. A CAPTCHA (hCaptcha/Turnstile) or a
  WAF/security-plugin rate limit would close that gap — deliberately not
  picked here, since choosing a CAPTCHA vendor is a product/UX decision
  (added friction to the funnel), not something to decide unilaterally
  in theme code.
- **Verify the `edit_theme_options` vs `manage_options` capability
  mismatch against a live install.** `register_setting()` doesn't specify
  an explicit capability, so WordPress core's own `options.php` save-path
  check defaults to `manage_options`, while this theme's own page-access
  check uses `edit_theme_options`. Identical in a stock install (both
  admin-only) — no known live install to test this against yet. Worth a
  quick check on a site that customizes role capabilities, and aligning
  both if there's a supported way to do so.

## Content & structure

- **A "Posts page" needs configuring in Settings → Reading** for the
  blog archive (`templates/index.html`) to show at a real URL — the
  template is ready, this is a one-time admin action the theme can't do
  for itself.
- **Actual pages need creating and assigning their templates.** Eight
  custom templates now exist (`page-about`, `page-services`,
  `page-case-studies`, `page-legal`, `page-contact`) and are selectable in
  the editor's
  Template dropdown, but no actual WordPress Pages have been created and
  assigned to them yet — that's a content/admin step, not a code one.
  **When creating them, use plain titles matching the four blog posts'
  internal links** ("Services", "Contact", etc.) — see the content
  package's own README for why that matters.
- **Import the blog seed content** (`remotive-blog-seed-content.xml`,
  delivered alongside the theme zip, not inside it — content and theme
  code are kept separate) via Tools → Import → WordPress. Four posts,
  written against real keyword research, ready to populate the blog
  archive the moment a Posts page exists.
- **Web design as a service, if that ever changes.** Six Malaysia
  keywords in the research (~9,000 combined volume) are web-design
  terms — deliberately untargeted since Remotive doesn't offer that
  service. If it's added later, this is the first keyword cluster worth
  building a landing page or content around.

## Design system extensions

- **Swiss Grid and Editorial Studio as additional style variations.** The
  original concept deck explored three directions (Overprint Bold, Swiss
  Grid, Editorial Studio); this theme ships only the merged "Registration"
  direction plus the brand-system light mode. If there's ever a reason to
  offer more visual variety (seasonal campaigns, A/B testing a calmer look),
  WordPress's native style variations (`styles/*.json` files, switchable in
  the Site Editor) would be the right mechanism — distinct from the
  visitor-facing dark/light toggle, which is a different, runtime-only
  system.
- **A third `--rm-blend`-aware theme, if ever added, needs its blend mode
  set explicitly** (see `readme.md`) — flagging here so it's not forgotten
  mid-build.
- **Any future palette change should grep for `"ink"` and `"paper"` usage
  across every template/theme.json before shipping.** The v1.2.0 dark-mode
  rework silently broke the CTA section and every button for two releases
  by swapping what those slugs mean without checking where they were
  referenced directly (see `readme.md`'s "ink/paper swap gotcha"). A
  simple `grep -rn '"ink"\|"paper"' templates/ parts/ theme.json` before
  any future palette edit would have caught it immediately.
- **Revisit the token/`render_block`-filter approach if WordPress's Block
  Bindings API widens its allow-list.** `inc/theme-options.php` currently
  uses a theme-specific token convention (see `readme.md`) instead of the
  official Block Bindings API because Navigation Link and Custom HTML
  blocks aren't bindable as of this writing. If core adds support for
  those block types, migrating to the standard mechanism would be more
  maintainable than the current `strtr()` filter — worth checking on
  future WordPress releases.

## Performance & accessibility

- **Custom post types for services/case studies would also unlock
  per-item alt text and structured image handling** once real photography
  (see above) replaces the placeholder gradient.

## Explicitly out of scope for now

- General-purpose reusability. This theme is intentionally single-site
  (see `ssot.md`) — no plans to genericize it into a distributable product.
- Automated testing. Given the theme's small, single-purpose surface area,
  manual QA has been judged sufficient so far; revisit if the codebase grows
  past a single homepage template.
- A backend MCP server or autonomous administrative agent. WebMCP in this
  theme augments the open webpage and does not replace a separately
  authenticated server integration. Publishing, deleting, settings changes,
  lead submission and other mutations remain outside the exposed tool set.
