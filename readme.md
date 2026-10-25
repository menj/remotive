# Remotive Media, technical readme

Child theme of **Twenty Twenty-Five**, built for `remotivemedia.asia`. This
document is for developers, theme maintainers, and hosting/sysadmins. For a
plain-language overview, see `readme.txt`. For canonical brand/entity facts
that this theme (and any other Remotive collateral) must stay consistent
with, see `docs/ssot.md`. For version history, see `docs/changelog.md`. For planned
work, see `docs/upgrading.md`.

## Requirements

- WordPress 6.7+ (block editor / Site Editor support for child theme.json
  merging, see "theme.json merge behaviour" below)
- PHP 7.4+
- Parent theme **Twenty Twenty-Five** installed and present in
  `wp-content/themes/twentytwentyfive` (folder name must match exactly, it is referenced by `Template: twentytwentyfive` in `style.css`)

No build step, no npm/composer dependency at runtime. Fonts are pre-built
static `.woff2` files already committed to `assets/fonts/`.

## File map

**Root rule (v1.97.0).** The theme root holds only what WordPress needs or
expects there, plus the two readmes: `style.css`, `theme.json`, `functions.php`,
`screenshot.png`, `readme.txt` (the WordPress readme) and `readme.md` (this
file). Every other document lives in `docs/`, code in `inc/`, and everything else
in its own folder. A new document goes in `docs/`, not the root.

```
remotive/
├── style.css              Theme header only (Name/Template/Version/etc).
│                           No CSS rules belong here, block themes do not
│                           auto-enqueue style.css, see functions.php.
├── screenshot.png          1200×900 admin theme-picker thumbnail.
├── theme.json              Design tokens: colour palette, font families
│                           (fontFace definitions pointing at self-hosted
│                           woff2 files), font sizes, layout widths.
├── functions.php           Requires inc/core/error-handler.php FIRST (so it is
│                           registered before anything else can fatal), then
│                           every other module under inc/ by path, in this
│                           order: i18n, options (colours, agency-layout,
│                           theme-options), setup, forms, content, security,
│                           maintenance mode, landing pages, agency page,
│                           schema-elements, not-found, rank-math and
│                           ai-discovery-files (tests/check-modules.php fails
│                           if a file under inc/ is not required). Inlines
│                           the critical header/hero shell, asynchronously
│                           loads assets/css/remotive.css, conditionally
│                           enqueues scripts, adds preload/resource hints;
│                           explicit theme supports + textdomain loading;
│                           one-time logo/favicon bootstrap on activation.
├── readme.txt              WordPress-style readme (stable tag, FAQ, upgrade
│                           notice).
├── readme.md               This file: architecture, file map, gotchas.
├── docs/                   Every other document: ssot.md, changelog.md,
│                           upgrading.md, walkthrough.md, resources.md
│                           (licences), image-credits.md, accessibility.md,
│                           cache-headers.md, htaccess-cache.txt, the legal
│                           drafts and the homepage brief.
├── error-pages/            500.html and 503.html, static pages the server shows
│                           when WordPress cannot start, and htaccess-errors.txt,
│                           the ErrorDocument lines to paste into the site root
│                           .htaccess (v1.118.0). See "Error pages the theme
│                           cannot draw" below.
├── drop-ins/               maintenance.php, db-error.php, php-error.php,
│                           copied to wp-content/ automatically; README.md
│                           explains how.
├── tests/                  bootstrap.php, twelve test-*.php unit tests and
│                           eleven check-*.php checks, all run in CI. Listed
│                           one by one under "Tests and CI" below.
├── tools/                  normalise-portraits.py (portrait processing).
├── .github/workflows/      ci.yml: PHP syntax on 7.4 and 8.3, JavaScript
│                           syntax, valid theme.json, every check-*.php and
│                           every test-*.php, on every pull request.
├── .htaccess               Returns 404 for .git, .github, docs, tests, tools and
│                           drop-ins if they are uploaded to the live theme
│                           folder (Apache and LiteSpeed).
├── languages/
│   ├── remotive.pot         410 translatable strings, extracted by script
│   │                       from every __()/_e()/esc_html__() call in
│   │                       functions.php and inc/*/*.php with real file:line
│   │                       references (v1.69.2, the previous hand-built
│   │                       file covered only 35 and had drifted badly).
│   └── README.txt           Notes on adding translations.
├── inc/                    PHP modules, grouped by job. functions.php requires
│   │                       each one by path; nothing is autoloaded.
│   ├── core/               Site-wide behaviour, no admin screen.
│   │   ├── error-handler.php   Branded fatal error page with detail shown to
│   │   │                   administrators only, plus capture of non-fatal
│   │   │                   notices so they never print into the page. Required
│   │   │                   first so it is active before any other include can
│   │   │                   fail. (v1.69.0, v1.69.2)
│   │   ├── security.php    Login rate limiting, XML-RPC disabled, application
│   │   │                   passwords blocked (switch, v1.120.1), install
│   │   │                   endpoints redirected, version fingerprinting
│   │   │                   removed, user enumeration blocked (author redirect
│   │   │                   at template_redirect 0, REST users routes refused
│   │   │                   at authentication and at dispatch), security
│   │   │                   headers. Theme-layer only, see "Security" below.
│   │   ├── accessibility.php   WCAG remediation helpers. (v1.47.0)
│   │   ├── avif.php        Wraps images in a picture element with an AVIF
│   │   │                   source when a companion file exists. (v1.45.0)
│   │   ├── branded-login.php   Optional branded wp-login screen, off by
│   │   │                   default. (v1.53.0)
│   │   ├── rank-math.php   Rank Math SEO 1.0.279 compatibility: robots,
│   │   │                   canonical and sitemap rules the plugin would
│   │   │                   otherwise discard. (v1.101.0)
│   │   ├── not-found.php   Rescue redirects for old addresses: date style
│   │   │                   article URLs to the article, favicon and touch icon
│   │   │                   names to the site icon. (v1.118.0)
│   │   └── ai-discovery-files.php  AI Discovery Files 2.2.2: keeps the
│   │                       landing pages out of llms.txt. (v1.110.1)
│   ├── options/            Settings the site owner changes.
│   │   ├── theme-options.php   Appearance -> Theme Options admin page,
│   │   │                   settings sanitisation, the render_block token
│   │   │                   filter, and the early flash-prevention script.
│   │   ├── colours.php     Theme Options -> Colours: per-mode palette, CSS
│   │   │                   variables, contrast check, editor palette.
│   │   ├── agency-layout.php   Theme Options -> Agency layout: the switch,
│   │   │                   the second switch that hides the other pages, and
│   │   │                   the words of the one-page layout. (v1.116.0)
│   │   └── maintenance-mode.php  Switch: 503 "back shortly" page for
│   │                       logged-out visitors.
│   ├── setup/              Pages, menus and starter content.
│   │   ├── site-setup.php  Shipped-page list, template assignment, setup
│   │   │                   cards, and the versioned migrations, run under a
│   │   │                   lock on the option row REMOTIVE_SETUP_LOCK so two
│   │   │                   admin loads cannot both run them. (v1.120.0)
│   │   ├── classic-menus.php   Classic menu support and the default menus,
│   │   │                   including the six listed case studies.
│   │   ├── content-seed.php        Creates the shipped pages, posts and case
│   │   └── content-seed-data.php   studies on activation; create-once, with
│   │                       per-item restore from Theme Options. A child page
│   │                       is looked for under its parent only; with the
│   │                       parent missing nothing is matched or created.
│   ├── forms/              Enquiries, from submission to storage.
│   │   ├── lead-form-handler.php  Shared nonce/rate-limit/honeypot/email
│   │   │                   logic every native form goes through
│   │   │                   (remotive_form_nonce_valid(),
│   │   │                   remotive_honeypot_tripped()).
│   │   ├── cta-form-handler.php   Thin wrapper: homepage CTA form.
│   │   ├── about-form-handler.php Thin wrapper: About page form.
│   │   ├── contact-form-handler.php  Thin wrapper: Contact page form.
│   │   │                   The landing and one-page forms call the shared
│   │   │                   function from inc/landing/landing-pages.php. See
│   │   │                   "Security" below for all of them.
│   │   ├── leads.php       Enquiry storage: private custom post type, spam
│   │   │                   status, admin card, read-only Message box on the
│   │   │                   edit screen, CSV export, retention purge.
│   │   │                   remotive_insert_lead_post() slashes the text and
│   │   │                   pauses kses for the one insert.
│   │   ├── akismet.php     Spam checking through the Akismet plugin when
│   │   │                   installed; fails open. (v1.64.0)
│   │   └── thank-you.php   Form confirmation page: noindex, opening line by form,
│   │                   conversion event.
│   ├── landing/            Ad landing pages.
│   │   ├── landing-pages.php   SEO, Google Ads and paid social pages in four
│   │   │                   languages on their own paths, with the
│   │   │                   audit-requested page (checked before the redirect,
│   │   │                   remotive_lp_thanks_url()), the "How did you hear
│   │   │                   about us?" field (remotive_lp_source_options()),
│   │   │                   hreflang, FAQPage schema and noindex rules.
│   │   ├── landing-copy.php    The pages' words: services and FAQ, four
│   │   │                   languages each.
│   │   └── agency-page.php The optional one-page agency home page and the
│   │                       redirects, sitemap and language rules that go with
│   │                       the switch, plus the bar and footer shown on a
│   │                       kept legal page. (v1.116.0, v1.119.11)
│   ├── i18n/               Language versions of the site (v1.104.0).
│   │   ├── i18n.php        /ms/, /zh-hans/, /zh-hant/: removes the prefix
│   │   │                   from the request, translates the rendered page
│   │   │                   from a dictionary, adds title, canonical, hreflang,
│   │   │                   the footer switcher and /sitemap-languages.xml.
│   │   │                   The main pages are live by default.
│   │   ├── i18n-admin.php  Tools > Translations: edit, import and export
│   │   │                   translations; switch pages on. Edits are kept in
│   │   │                   the database.
│   │   └── ms.php, zh-hans.php, zh-hant.php   The dictionaries.
│   └── content/            What the public pages emit.
│       ├── schema-markup.php   JSON-LD structured data; the theme owns the
│       │                   graph, see "Structured data" below.
│       ├── schema-elements.php  WebPageElement nodes for the header, footer
│       │                   and sidebar, and the one-page layout's sections,
│       │                   services, steps, checklist and team. (v1.118.0)
│       ├── schema-extended.php  The rest of schema.org: Brand, Offer,
│       │                   OfferCatalog, Place, Blog, DataFeed, ItemList,
│       │                   SiteNavigationElement, ImageObject, translations,
│       │                   and the fuller Organization, WebSite, WebPage and
│       │                   BlogPosting nodes. (v1.120.5)
│       ├── webmcp.php      Public read-only search endpoint and WebMCP.
│       ├── feature-grids.php   Homepage "problem we solve" and "why Re:Motive" grids.
│       ├── stats-band.php  Homepage three-figure results band.
│       ├── article.php              Reading time and the related-posts query for the article page.
│       └── post-image-fallback.php  A branded gradient for a card whose post
│                           has no featured image. (v1.111.0)
├── templates/            (incl. page-service.html, reusable service
│                          detail template, v1.29.0: title hero + editable
│                          content + CTA band; pages ship separately)
│   ├── front-page.html     The homepage, in WordPress block markup.
│   │                       Content is wrapped in a <main id="main">
│   │                       landmark, see "Accessibility" below.
│   ├── index.html           Blog archive, sidebar layout, real core/query
│   │                       loop. Also WordPress's mandatory fallback
│   │                       template every block theme must have.
│   ├── single.html          Article page (v1.114.0): header, cover, contents rail, reading column, share row, related posts.
│   ├── home.html            Blog posts index (the Posts page), kept
│   │                       distinct from the index.html fallback.
│   ├── archive.html         Category, tag, date and author archives,
│   │                       archive title, term description, post grid.
│   ├── search.html          Search results, query in the heading, a
│   │                       refine field, and the post grid.
│   ├── 404.html             Branded not-found page with a search field
│   │                       and links home and to insights.
│   ├── page.html             Generic fallback for any Page without a
│   │                       selected custom template.
│   ├── page-about.html      Custom template (Template dropdown in the
│   │                       editor): the company page, how it started,
│   │                       why the region, what it stands for, and the
│   │                       contact form. (v1.56.0)
│   ├── page-team.html       Custom template: "Meet the Team" at /team/.
│   │                       Roster and portraits come from Theme Options;
│   │                       the lightbox gallery lives here. (v1.56.0)
│   ├── page-faq.html        Custom template: accordion FAQ whose
│   │                       questions are also parsed into FAQPage
│   │                       structured data. (v1.61.0)
│   ├── page-services.html   Custom template: services grouped into the
│   │                       three blocks (Demand Creation/Capture/Data),
│   │                       matching the homepage; detailed breakdowns.
│   ├── page-case-studies.html  Custom template: one grid of six case studies, uncategorised.
│   ├── page-legal.html      Custom template for Privacy Policy/Terms,
│   │                       minimal chrome, no fabricated legal content. With
│   │                       the one-page layout hiding the other pages, the
│   │                       header and footer parts are swapped for the
│   │                       one-page bar and footer (v1.119.11).
│   ├── page-contact.html    Custom template: contact form + details.
│   ├── page-service.html    Custom template for the individual service and
│   │                       case-study pages: title hero, editable content,
│   │                       CTA band (v1.29.0).
│   ├── page-landing.html    Holds the __REMOTIVE_LANDING__ token that
│   │                       inc/landing/landing-pages.php renders into the ad
│   │                       landing pages and the one-page home page.
│   └── page-thank-you.html  Form confirmation page, noindex, conversion event
│                           (inc/forms/thank-you.php).
├── parts/
│   ├── header.html         Skip link + sticky nav: Site Logo + Navigation
│   │                       blocks + theme toggle button.
│   ├── footer.html         Brand blurb, sitemap, services, contact, socials.
│   └── sidebar.html         About blurb + recent-posts query + contact CTA,
│                           shared by index.html and single.html.
└── assets/
    ├── css/critical.css    Small header/hero shell, inlined by functions.php
    │                       before the asynchronous component stylesheet.
    ├── css/remotive.css    Component CSS, everything theme.json's Global
    │                       Styles cannot express (hover states, grid,
    │                       blend-mode logic, dark/light overrides).
    ├── css/print.css       Print-only palette, visibility, compact layouts,
    │                       and fragmentation/pagination rules.
    ├── css/blog-and-about.css  Blog + About page styles, conditionally
    │                       enqueued, not loaded site-wide.
    ├── css/article.css     The article page (single.html): header, cover,
    │                       contents rail, reading column (v1.114.0).
    ├── css/landing.css     Ad landing pages and the one-page layout: hero,
    │                       form cards, closing band, footer.
    ├── css/agency.css      The one-page layout's own sections (services,
    │                       steps, signs, team, footer), after landing.css.
    ├── css/agency-motion.css  Scroll motion for the one-page layout: colour
    │                       plates on their own scroll rates, lifts and
    │                       staggered rises, on CSS scroll timelines with a
    │                       requestAnimationFrame fallback in landing.js.
    │                       Scoped to body.rm-motion, stilled under
    │                       prefers-reduced-motion (v1.120.0).
    ├── css/login.css       The branded login screen, loaded on wp-login.php
    │                       only while the switch is on (v1.53.0).
    ├── js/remotive.js       Front-end bundle: the colour-mode toggle
    │                       (localStorage-backed), the mobile navigation
    │                       fallback, the ticker pause off-screen and the
    │                       form nonce refresh (v1.54.0, v1.94.0).
    ├── js/landing.js        Campaign capture, funnel events, sticky CTA and
    │                       the scroll motion fallback for the one-page layout.
    ├── js/article.js        Reading progress, contents list and share buttons
    │                       on single posts.
    ├── js/lead-form-status.js  Reveals the pre-rendered success or error
    │                       message (.rm-cta__msg) of any native form after
    │                       its handler's redirect.
    ├── js/lightbox.js       Accessible image-preview dialog (Team page).
    ├── js/webmcp.js         Experimental in-browser agent tools: search,
    │                       navigation, safe opening, and human-reviewed
    │                       preparation of visible lead forms.
    ├── css/admin-theme-options.css  Admin settings page UI (tabs, cards,
    │                       visual selector, toggles), loaded only on that page.
    ├── js/admin-theme-options.js    Admin tabs: ARIA Tabs pattern, keyboard nav.
    ├── fonts/               Archivo, Newsreader, Saira, Space Grotesk, self-hosted woff2;
    │                       kagnue/ (licensed display serif, figures only, v1.109.0).
    ├── icons/               instagram, linkedin, tiktok SVG sources, kept for
    │                       reference; the markup inlines them.
    ├── images/               Brand logo files (landscape mark, square lockup);
    │                       landing/ (ad landing photos) and case-studies/ (the
    │                       six Case Studies card photos, v1.110.2), both Pexels.
    ├── team/                 One AVIF portrait per team member, by slug.
    └── seed-images/          Insights article photos (JPEG + AVIF, v1.111.0).
```

## WebMCP

The theme progressively exposes a small set of in-page tools to browsers that
implement `document.modelContext`: public site search, canonical navigation,
same-origin page opening, and preparation of the visible contact/audit forms.
It has no polyfill and no external dependency. Browsers without WebMCP receive
the same HTML, forms and navigation as before.

Lead tools deliberately stop before submission. They never populate hidden
action, nonce or honeypot controls, and the visitor must inspect the highlighted
form and press its submit button. The existing native form handler remains the
only write path. Public search is limited to published posts and pages, ten
results per request, and a 200-character query.

**Spec conformance, checked against the W3C draft (v1.79.1).** Verified
directly against the WebMCP specification source rather than against
memory of the API:

| Spec item | Status |
|---|---|
| `document.modelContext.registerTool()` | matches, current API, not a legacy form |
| `ModelContextTool` fields (`name`, `title`, `description`, `inputSchema`, `execute`, `annotations`) | all present, matching the IDL |
| Declarative `toolname`, `tooldescription`, `toolautosubmit`, `toolparamdescription` | all four, correct names, on the correct elements |
| Secure-context and feature detection before any call | present; fails silently rather than throwing |
| `options.signal` forwarded to `fetch` | present, so tool calls are cancellable |

Two things worth recording so they are not "fixed" later by mistake:

- **`execute()` returns bare objects, not `{ content: [{ type, text }] }`.**
  The spec's README shows the `content` array in its examples, but the
  normative IDL is
  `callback ToolExecuteCallback = Promise<any> (object, ToolExecuteCallbackOptions)`.
  Any return value is valid; the `content` shape is MCP convention, not a
  WebMCP requirement. Rewriting the five tools to match the example would
  be churn based on the README rather than the normative text.
- **`exposedTo` and `signal` registration options are omitted, deliberately.**
  Both are optional. In a top-level document a missing `exposedTo` is what
  exposes a tool to the built-in agent, which is the intent here, and
  `signal` exists for unregistration that a page-lifetime tool on a static
  site does not need.

## Blog & secondary page templates

Added in v1.10.0 (blog, About) and v1.11.0 (Services, Case Studies,
Legal, Contact, generic `page.html`). A few things worth knowing:

- **The blog needs a "Posts page" configured** (Settings → Reading) to
  show at a real URL, `templates/index.html` is ready the moment that is
  set up; nothing in the theme can configure that setting itself, it is
  an admin action.
- **`inc/forms/lead-form-handler.php`** is the one security-reviewed code path
  every native form on this theme goes through: the homepage CTA, the
  About page, the Contact page, the ad landing pages and the one-page
  layout. See `inc/forms/cta-form-handler.php`,
  `inc/forms/about-form-handler.php`, and `inc/forms/contact-form-handler.php` for
  the three thin wrappers (`inc/landing/landing-pages.php` calls the shared
  function for the landing and one-page forms), and the "Security" section
  below for the full threat-model writeup (nonce, honeypot, rate limit,
  CRLF guard, all of it applies to every form identically). A new form
  should be another thin wrapper around the same shared function, with
  its own nonce action and honeypot field name so the rate-limit state
  cannot collide if a visitor has several forms open across tabs.
- **The Case Studies page's six cards carry Pexels photographs
  (v1.110.2)** and the Insights articles their own (v1.111.0), both
  credited in `docs/image-credits.md`. The homepage's featured case-study
  spread still uses the gradient treatment. The Team page's portrait grid is
  the one gallery `assets/js/lightbox.js` serves (it moved there from the
  About page in v1.56.0).
- **The Legal page template (`page-legal.html`) does not contain any
  actual legal text**, that is deliberate. It provides the chrome (title,
  a live "last updated" date pulled from the page's own post date,
  readable typography for long-form content) and expects the admin to
  write the real Privacy Policy / Terms content in the block editor.
- **The lightbox was built from scratch**, not adapted from any
  reference template, see `docs/resources.md` for why, and the "Security"-
  adjacent reasoning does not apply here, but the same "verify, do not
  assume" discipline does: the focus trap, arrow-key navigation, and
  focus-return-on-close were all confirmed with an automated test during
  development instead of being assumed correct.

## Architecture notes

### theme.json merge behaviour

As of WordPress 6.1+, a child theme's `theme.json` is **deep-merged** with
the parent's, not used as a wholesale replacement. Scalar values (a colour,
a font size) override the parent's cleanly. Arrays (like `settings.color.palette`)
are replaced as a whole array if the child defines that key at all, so this
theme's palette is a complete standalone list, not additions to Twenty
Twenty-Five's default palette. If you add a new colour, add it to the full
array in `theme.json`, do not assume it merges item-by-item with the parent's.

### Design tokens vs. component CSS

`theme.json` owns anything Global Styles can represent: colour palette, font
families/sizes, layout widths. `assets/css/remotive.css` owns everything
else, grid layouts, hover states, the plate hover-fill animation, the
ticker marquee, and the dark/light override. Every custom-CSS selector is
scoped under an `rm-*` class applied via each block's "Additional CSS
class(es)" field, so nothing here can leak into core blocks, other plugins,
or the parent theme's own styling.

### Performance delivery (v1.44.1)

The first viewport is intentionally split into two CSS layers. The small
`assets/css/critical.css` header/hero shell is read and inlined by
`functions.php`; its logo URL placeholder is replaced with the active child
theme URL. The complete `assets/css/remotive.css` is requested with
`rel="preload"`, then applied by changing its media value on load. A
`<noscript>` stylesheet preserves the complete design for visitors who disable
JavaScript. Keep critical CSS limited to mode variables, header, hero and their
responsive states; whenever those rules change in `remotive.css`, update the
corresponding critical declarations in the same release.

On the front page, WordPress emits a desktop-only preload for the hero mark
with `fetchpriority="high"`. This makes the CSS-background LCP candidate
discoverable in the initial HTML without downloading the decorative mark at
the mobile breakpoint where it is hidden. The Site Logo block is 56px wide and
the custom-logo attributes report `sizes="56px"`, so WordPress's responsive
image selection does not fetch the old 300px candidate for a 55px display.

All self-hosted `fontFace` entries use `fontDisplay: "swap"`. Front-end theme
scripts use WordPress's deferred loading strategy; the homepage ticker, About
interactions, and post-redirect form-status helper are only queued where their
markup or state exists. WebMCP is also deferred. A preconnect to
`accounts.google.com` is conditional on another component actually queueing a
Google Sign-In script, avoiding an unused third-party connection hint.

HTTP cache lifetime is deliberately not set by theme code. Response headers
for theme files, WordPress core/plugin files, media uploads, and Google-hosted
resources are controlled by the origin server, caching plugin, reverse proxy,
CDN, or third party. Production should give versioned theme assets a long
browser lifetime (normally one year plus `immutable`), set a deliberate policy
for media-library files, and verify plugin/core files in the active cache
layer. Never add a theme-local `.htaccess` as the supposed fix: it is
Apache-specific, does not control uploads/core/plugin files, and disappears
when themes change. The live follow-up is tracked in `docs/upgrading.md`.

### Print and PDF layout (v1.44.1)

`assets/css/print.css` removes interactive chrome and decorative media, forces
the paper palette, and applies fragmentation rules for cards, case rows, stats,
team members and the footer. The featured case spread's artwork column is
collapsed while its useful label remains; the proof sequence starts on a fresh
sheet; three service cards print as two plus a centred final card; stats remain
one compact row; and the About heading stays with a two-column text roster.
These choices specifically prevent the empty panels, detached borders,
oversized metric bands and orphaned headings seen in the supplied A4 PDF.
When editing homepage structure, verify Print Preview at A4 and Letter in both
Chrome and Safari/Firefox before changing `break-*` or `page-break-*` rules.

### What comes from the parent, and what does not

Checked against Twenty Twenty-Five 1.5, not assumed. Worth reading before
guessing what the parent provides, since the answer is narrower than it
looks in a couple of places and wider in others.

**Inherited, because this theme ships none of its own:** all 98 block
patterns, all 8 style variations, post-format support, the
`checkmark-list` block style, the parent's pattern categories, and its
block bindings. Also four template parts (`footer-columns`,
`footer-newsletter`, `header-large-title`, `vertical-header`), which are
available as alternates in the Site Editor.

**Barely inherited: templates.** Twenty Twenty-Five ships only eight, and
this theme overrides seven of them. The single template actually inherited
is `page-no-title.html`. There is no author, category, tag or date
template in the parent at all; those requests fall through to
`archive.html` and `index.html`, both of which this theme provides. So the
template layer is close to fully self-contained.

**Not inherited: responsive behaviour for anything custom.** The parent
contains zero media queries across all three of its CSS files. It scales
through fluid typography, viewport-aware root padding, and the rules baked
into core blocks, with the only real breakpoint being core's own 781px
column stacking. None of that reaches a `div.rm-blocks`, because roughly
40% of `front-page.html` is raw markup inside `wp:html` blocks and core
ships no CSS for it. That is why the custom grids use intrinsic
`auto-fit` sizing (see v1.22.0): it reproduces the parent's continuous
model rather than bolting fixed breakpoints alongside it.

**Overridden, not inherited: the layout measures.** `contentSize` is
740px here against the parent's 645px, and `wideSize` is 1320px against
the parent's 1340px. Both numbers belong to this theme. Anywhere the
documentation or a code comment calls 740px a "WordPress default", that is
wrong; it was corrected in v1.22.1.

**Typography is a third case.** The parent sets proper `fluid` objects
with explicit min and max and lets WordPress compute the scaling. This
theme hard-codes `clamp()` strings with no `fluid` key, so WordPress's
fluid engine is bypassed and type scales on its own curve.

**Do not enqueue the parent stylesheet.** The parent enqueues its own
`style.min.css` on `wp_enqueue_scripts`, so the usual child-theme
`wp_enqueue_style( 'parent-style' )` would only duplicate it. Block themes
deliver styling through `theme.json` and per-block stylesheets anyway.

### Dark/light mode implementation

Two modes exist, both defined as CSS custom-property overrides layered on
top of what WordPress already generates from `theme.json`
(`--wp--preset--color--*`, `--wp--preset--font-family--*`):

- **Dark** (the baseline in the CSS, no `data-theme` attribute needed),
  colour values come straight from `theme.json`'s palette. True near-black
  background (`#1a1a2e`). The type is Saira in both modes: `remotive.css`
  points the Archivo and Newsreader font variables at Saira on
  `html[data-theme]`, so the modes differ in colour alone. Colour values
  match `remotive-reporting` (see `docs/ssot.md`) as of v1.9.0.
- **Light** (`[data-theme="light"]` on `<html>`), overridden in
  `remotive.css`: Remotive Media Asia's brand palette (from
  `remotive_brand_5.json`, see `docs/ssot.md`), Saira throughout, cream
  background (`#f7f4ec`). **Light is the default a first-time visitor sees
  (v1.120.3).** Dark was the default before that; migration 1.120.3 moves a
  site still on `dark` to `light` once and leaves `system` alone. The CSS
  baseline and the shipped default are two different things: the
  stylesheet is still written dark-first, and the server adds
  `data-theme="light"` to `<html>` when the saved default is light.

Because every block already reads colour/type via `var()`, the override
cascades to the entire page with zero per-component or per-template changes.

The toggle in `assets/js/remotive.js` (the former `theme-toggle.js`, bundled
since v1.54.0) toggles the `data-theme` attribute on
`document.documentElement` and persists the visitor's choice via
`localStorage` (key: `remotive-theme`). Before a visitor has ever used the
toggle, the mode shown comes from `window.remotiveThemeOptions.defaultTheme`, set server-side by `inc/options/theme-options.php` from the **Appearance →
Theme Options → Site behaviour → Default colour mode** setting (light /
dark / match the visitor's device via `prefers-color-scheme`), falling back
to light if that variable is somehow missing.

**Flash prevention:** the toggle script itself is enqueued in the footer
(standard WP practice, does not block rendering), which alone would mean a
returning visitor who chose light sees one frame of dark before their
saved preference applies. Fixed by `remotive_prevent_theme_flash()`
(`inc/options/theme-options.php`, hooked to `wp_head` at priority 1), a small
inline `<script>` that reads the same `localStorage` key and applies
`data-theme` before first paint. It duplicates a little of
`remotive.js`'s own resolution logic on purpose: the early copy
prevents the flash, the footer copy still needs to run to wire up the
toggle button's click handler.

### The `--rm-blend` gotcha

The "misregistration" hover effect on the hero headline and the hero
watermark both use `mix-blend-mode`, layering a coloured duplicate of the
same element via CSS `content: attr(data-t)`. This mode **must** flip
between the two themes:

- `multiply` when the mode has a **light** background (multiply darkens
  toward whatever's underneath, invisible or near-invisible on black)
- `screen` when the mode has a **dark** background (screen lightens toward
  whatever's underneath, invisible on white)

This is handled by a `--rm-blend` custom property, set per-theme alongside
the colour tokens: `screen` for dark, `multiply` for light. **If you add a
third theme variant, set `--rm-blend` to match whether its background is
light or dark, or these hover effects will silently render as nothing**, there is no visible error, the element is just imperceptible.

### The `ink`/`paper` swap gotcha

`ink` and `paper` are **swappable palette slugs**, in dark mode (the
baseline), `ink` is the light/cream value and `paper` is near-black; in
light mode, the CSS override flips them back to the conventional pairing
(`ink` dark, `paper` light). This is intentional and is what makes the
whole page swap modes for free, but it means **any block that explicitly
sets `backgroundColor:"ink"` or `textColor:"paper"` (or vice versa) will
silently invert its own colours between modes**, even though nothing
about that specific block changed. This bit the CTA section and the
global button style in v1.2.0–v1.3.0 (both fixed in v1.4.0, see
`docs/changelog.md`), the CTA band rendered as a light cream stripe, and every
solid button as a light pill, specifically in dark mode, the site's
default, which is why it went unnoticed for two releases.

**If an element is supposed to look the same in both modes** (like the CTA
band, or any button), reference the fixed `contrast-bg`/`contrast-text`
palette slugs instead, these are deliberately left out of the
`[data-theme="light"]` override block in `remotive.css`, so they never
change. Only reach for `ink`/`paper` directly when an element is
*supposed* to flip with the rest of the page.

### The static-block layout-class gotcha

`core/group` and `core/columns` are **static blocks**, WordPress
outputs their saved HTML exactly as written in the template file. There
is no server-side step that reconciles the rendered `class="..."`
attribute against the block comment's declared `"layout"` attribute. If
you add a new `wp:group` or `wp:columns` block to a template with
`"layout":{"type":"constrained"}` (or `"flex"`) in its JSON attributes,
you **must also** add the matching `is-layout-constrained` (or
`is-layout-flex`/`is-layout-flow`) class directly to that block's opening
tag, the JSON attribute alone does nothing on the front end. This bit
every `wp:group`/`wp:columns` block across all three template files
until fixed in v1.9.1; see `docs/changelog.md` for the full account, including
how it went undetected through several earlier rounds of visual QA
because the hand-built test harness used for those checks never included
WordPress core's own generated layout CSS in the first place.

Two more things worth knowing if you touch layout again:

- `theme.json`'s `settings.layout.contentSize` (740px) is intentionally
  narrow, appropriate for reading-width prose, too narrow for this
  homepage's bold marketing sections. `.rm-hero`, `#services`, and
  `#work`'s direct children are explicitly widened to `wideSize` (1320px)
  in `remotive.css` rather than relying on WordPress's `alignwide` class
  cascade, same "be explicit" reasoning as the gotchas above. If you add
  a new top-level section to the homepage, decide deliberately whether it
  needs the same treatment or is fine at the narrower default (the CTA
  section is a deliberate exception, narrow reads better for a compact,
  centered form).
- This design is **left-aligned, not centered**, if you ever widen an
  individual element independently (rather than widening a whole
  section's children uniformly, as done here), remember that two
  differently-sized centered boxes will not share a left edge, which looks
  like a mistake in a left-aligned layout even though centering is
  working exactly as specified.
- **The wide-measure fix is a selector list, not a blanket rule, every
  new wide section needs adding to it explicitly.** `.rm-footer` shared
  the exact same "stuck at 740px" bug as the homepage sections this fix
  originally addressed, but was not included in the original selector and
  went unnoticed for several releases (fixed in v1.13.1, see
  `docs/changelog.md` for the full trace, including why it surfaced as a
  broken email address in the footer rather than an obviously narrow
  layout). If you add another section that needs the wide measure, a new template's hero, a new full-width band, add it to this same
  selector rather than writing a new one-off rule, and actually render
  it with real content before assuming it is fixed. A companion rule
  landed in v1.13.2 for a related trap: widening the children does
  nothing while the section wrapper itself keeps the 740px measure and
  centres, so `#services` and `#work` now set the wide measure on the
  wrapper and pin it left. Set both the wrapper and its children when a
  new section must align with the hero edge.

### `front-page.html` and the Reading settings

This theme ships a `templates/front-page.html`, which is WordPress's
highest-priority template for the site's homepage, it always wins
regardless of what Settings → Reading has configured (a static page vs.
"your latest posts"). This is intentional and is the standard, sanctioned
WordPress mechanism for a theme that wants full control over the
homepage's design, not a bug or an oversight. If Reading settings are
ever changed expecting the homepage to reflect that, they will not, as long
as this template exists, worth knowing before anyone spends time
troubleshooting it.

## Accessibility

- **Skip link.** The first focusable element on every page (`parts/header.html`,
  before the nav itself) is `<a class="rm-skip-link" href="#main">Skip to
  content</a>`, invisible until keyboard-focused, then pinned to the
  top-left above everything, including the sticky nav.
- **`<main id="main">` landmark.** `templates/front-page.html` wraps all
  page content (hero through the CTA band, everything between the header
  and footer template parts) in a single `<main>` element, the skip
  link's target, and the expected landmark structure for assistive tech.
- **Link underlining.** Nav, footer, and component links (section links,
  case-study "View case study →" links) are deliberately not underlined, they are distinguished from surrounding text by colour, hover state,
  being inside an obvious nav region, or an arrow icon. Genuine body-content
  prose (a future blog post, a comment) does not have those other cues, so
  `.entry-content a` / `.wp-block-post-content a` / `.comment-content a`
  are explicitly underlined in `remotive.css`, even though nothing on the
  current homepage uses those wrapper classes yet.
- **Sticky-nav-aware scroll offset.** `html{ scroll-padding-top:100px }`
  covers *any* focus-triggered scroll into view (Tab key or anchor jump,
  with or without an id) so the sticky nav never lands directly on top of
  whatever just received focus. `:where([id]){ scroll-margin-top:100px }`
  also covers fragment-link jumps specifically. See `docs/changelog.md`
  v1.7.0 for why both exist, the id-based rule alone did not cover plain
  keyboard tabbing.
- **Focus ring.** A global two-tone "sandwich" focus style (white outline
  + black box-shadow ring, `.rm-nav :focus-visible` and siblings in
  `remotive.css`) guarantees at least one ring contrasts against any of
  this design's background colours, near-black, cream, cyan, magenta,
  and yellow all appear as backgrounds somewhere, and no single fixed
  outline colour could pass 3:1 against all of them.
- **Contrast.** Every text/background colour pair in both modes has been
  computed (not estimated) against the actual WCAG relative-luminance
  formula. Two of the client's brand-JSON colours failed as text in light
  mode and were replaced with darkened, same-hue variants that pass, see
  `docs/ssot.md`'s design tokens table and `docs/changelog.md` v1.7.0 for the exact
  before/after values.

## Editing menus (v1.33.0)

Menus are managed in **Appearance → Menus**, the classic way. WordPress
hides that screen for block themes, so `inc/setup/classic-menus.php` re-adds it
and registers four locations:

| Location | Renders in | Accessible name (v1.119.11) |
|---|---|---|
| Main menu (header) | `parts/header.html` navigation block | Main menu |
| Footer: Services column | first footer navigation block | Services |
| Footer: Case Studies column (v1.98.0, the six listed case studies) | second footer navigation block | Case studies |
| Footer: Company column | third footer navigation block | Company |

A `render_block` filter replaces each navigation block with the assigned
menu, emitting `wp-block-navigation__container`,
`wp-block-navigation-item` and `wp-block-navigation-item__content` so all
existing navigation CSS applies unchanged, including the header's
squeeze breakpoints and mobile overlay button. If a location has no menu
assigned, the block's own links render instead, so the site never shows
an empty menu. Each `<nav>` carries the accessible name above whether the
menu is the block's own or a classic menu assigned to the location. Site
setup creates and assigns all four from the site's pages. The three footer
menus keep their original names (`Footer — Company`, `Footer — Services`,
`Footer — Case Studies`), em dash included, because existing sites look them
up by name.

New pages are never added to a menu automatically. The Site Editor
navigation wiring from v1.11.0 remains in `inc/setup/site-setup.php`, unhooked,
should anyone want to switch back.

## Enquiries and spam (v1.63.0 – v1.65.2)

Contact form submissions used to exist only as email. If `wp_mail()` failed,
and it fails for ordinary reasons such as a misconfigured SPF record or host
throttling, the enquiry was gone with no record anywhere.

**Storage.** Every submission is written to the database first and emailed
second, so a mail problem costs the notification rather than the lead. The
admin card marks any enquiry whose email failed, so someone can reply by
hand. Leads are a private custom post type (`remotive_lead`), not publicly
queryable and excluded from search, so an enquiry can never be reached at a
URL or appear in a sitemap. Being ordinary posts, they survive a theme switch
and export through WordPress's own tools.

**One store, both forms.** When Contact Form 7 is active its submissions are
captured into the same place, tagged with the form they came from. CF7 field
names vary, so the usual conventions (`your-name`, `your-email`,
`your-message`) are tried in order and anything unrecognised is appended to
the message body rather than dropped. CF7 is optional; the theme requires
nothing from it, and styles it to match when present.

**Spam.** With the Akismet plugin installed and connected, submissions are
checked before they are emailed, sent as a `contact-form` type rather than a
blog comment. Spam is moved to a spam folder, its own post status, the way
WordPress separates spam comments, rather than deleted, because no filter is
perfect and a silently discarded enquiry is a lost client. Marking one as not
spam restores it and reports the correction back to Akismet.

**The filter fails open, always.** Akismet holds the API key and the theme
has no way to store one. With the plugin absent, present but unconnected, or
unreachable during an outage, every check returns not-spam and submissions
proceed exactly as before. A spam filter failing should cost filtering, never
a genuine enquiry.

**Retention.** A period in months on the Site behaviour tab deletes stored enquiries
older than it, daily, covering both folders. The privacy policy has to state
a period, and a stated period nothing enforces is worse than none. Zero keeps
them indefinitely.

## Structured data (v1.26.0)

`inc/content/schema-markup.php` emits one JSON-LD `@graph` in `<head>`:
Organization (legal facts per `docs/ssot.md`, contact facts from Theme
Options, the same source the rendered footer uses), WebSite with a
SearchAction, a typed WebPage per template (AboutPage, ContactPage,
CollectionPage, SearchResultsPage), BlogPosting on single posts,
Service nodes on the homepage/Services page and one market-specific
Service per landing page, and FAQPage on the two landing pages with
questions parsed at runtime from the templates' own `core/details`
blocks, so the schema can never drift from the visible accordion
content.

The markup is identical in light and dark mode by construction: it
describes content, and the mode toggle only flips CSS custom
properties.

**The theme owns the graph (v1.120.5).** Rank Math's and Yoast's
automatic nodes (the organisation, the site, the page, the article, the
breadcrumb, the author) are removed from their output through
`rank_math/json_ld` and `wpseo_json_ld_output`, and the theme's complete
nodes print instead: a plugin's automatic graph described the site thinly
(a nameless author on every page, no breadcrumb, an `Article` on the
privacy policy) and the theme used to withhold its own nodes to make room
for it. Schemas an editor attaches to a page through Rank Math's Schema
Generator are kept. `add_filter( 'remotive_schema_theme_owns_graph',
'__return_false' )` goes back to the older per-type yield, where
`remotive_schema_plugin_managed_types` (who is treated as managing what),
`remotive_schema_suppressed_types` (final say per request) and
`remotive_schema_graph` (the assembled nodes) decide; in that mode a page
with a plugin FAQ block yields `FAQPage` on that page alone.

### Google feature coverage (v1.27.0, checked again at v1.120.5)

Audited against Google's "Structured data markup that Google Search
supports" (June 2026). Every feature is accounted for in one of three
states; "excluded" always cites the reason, because Google's spam
policies treat markup describing content not visible on the page as
abuse, and fabricated nodes risk a manual action.

| Google feature | Status | How |
|---|---|---|
| Article | **Emitted** | BlogPosting on single posts: headline, dates, image as an `ImageObject` with its size, author (linked to the team roster's `Person` when the names match; no URL, since the author archives redirect), publisher, wordCount, reading time, keywords, articleSection, inLanguage |
| Breadcrumb | **Emitted** | BreadcrumbList on every non-front page (Home → parent page → page; Home → Insights → post), names from the page titles, localised on the language copies |
| Organization | **Emitted** | Name, alternate names, legal name and UEN (from Theme Options when set), ACRA founding date, registered address and a `Place`, contactPoint, sameAs, logo `ImageObject` with its size, brand, slogan, languages, knowsAbout, audience, the free audit as its `makesOffer` |
| Local business | **Emitted** | The Organization node is dual-typed ProfessionalService (the agency subtype) over the registered office, with the logo as its image; opening hours are not published so none are asserted |
| Profile page | Not emitted | The About page is an `AboutPage` (a company story, which Google does not count as a profile) and the author archives redirect to the home page by design, so no profile page renders |
| Image metadata | **Emitted** | creator / creditText / copyrightNotice on the organization logo; `remotive_schema_logo_license` filter adds license and acquireLicensePage URLs when the site publishes them |
| Speakable | **Emitted** | SpeakableSpecification on BlogPosting pointing at `.rm-art-title` and `.rm-post__content` |
| Sitelinks search | **Emitted** | WebSite SearchAction (retained from v1.26.0; Google retired the search box in 2024, the node is harmless) |
| Video | **Content-gated** | VideoObject emitted when a post contains a self-hosted `core/video` block AND the required properties (thumbnail, upload date) can be stated truthfully, with the duration when WordPress knows it; external embeds are skipped because those properties cannot be asserted |
| Q&A page | **Adjacent** | The landing pages' accordion content is FAQPage, kept from v1.26.0 (valid schema.org, consumed by AI answer engines), QAPage itself is for user-generated single-question threads, which the site does not have; Google dropped FAQ rich results for most sites in 2023 |
| Carousel | Excluded | Requires Recipe, Course, Restaurant or Movie content, none exists |
| Course list | Excluded | No courses offered |
| Dataset | Excluded | No published datasets |
| Discussion forum | Excluded | No user-generated discussion content |
| Education Q&A | Excluded | No flashcard/education content |
| Employer aggregate rating | Excluded | No employer reviews exist; fabricating ratings is named abuse in Google's policy |
| Event | Excluded | No events published |
| Job posting | Excluded | No vacancies published; revisit if a careers page ships |
| Math solver | Excluded | Not a math tool |
| Movie | Excluded | No film content |
| Product | Excluded | The agency sells services (covered by Service nodes), not products with price/availability |
| Recipe | Excluded | No recipes |
| Review snippet | Excluded | No review content on the site; fabricated review markup is the canonical spam-policy example |
| Software app | Excluded | No app distributed |
| Subscription / paywalled | Excluded | Nothing is paywalled |
| Vacation rental | Excluded | No rental properties |

An excluded feature activates by adding the real content and a node via
the `remotive_schema_graph` filter, the acquiescence machinery already
handles any type added to it.

### The rest of schema.org (`inc/content/schema-extended.php`, v1.120.5)

Google's list is the set of rich results; schema.org is larger, and the
theme states everything the site can state truthfully, whatever Google
shows for it. Beyond the nodes above, the graph carries:

| Where | Types and properties |
|---|---|
| Organization | `alternateName`, `Brand` (with the logo), `slogan` (the site tagline), `knowsLanguage` (English and every language switched on), `knowsAbout`, `BusinessAudience`, `Place` for the office, `makesOffer` (the free audit), `mainEntityOfPage` (the About page) |
| WebSite | `inLanguage` (all four), `description`, `about`, `copyrightHolder`, `isAccessibleForFree` |
| Every page (`WebPage` and its subtypes `AboutPage`, `ContactPage`, `CollectionPage`, `ItemPage`, `SearchResultsPage`) | `description`, `datePublished` / `dateModified`, the lead image as `primaryImageOfPage` (`ImageObject` with size and caption), the breadcrumb, a `ReadAction`, `isAccessibleForFree`, `mainEntity` (the article, the case study, the service, the catalogue, the questions, the people, the organisation, the blog, the archive list), and `workTranslation` listing the page's copies in the other languages; each copy carries `translationOfWork` pointing back |
| Posts | `ImageObject` with size, `thumbnailUrl`, `keywords` (tags), `timeRequired` (reading time), `copyrightHolder` / `copyrightYear`, `genre`, `description`, the author as the team roster's `Person` when the names match |
| Insights index | `Blog` with its posts as `BlogPosting` summaries; the RSS feed as a `DataFeed` |
| Category archives | `ItemList` of the posts |
| Case studies | `Article` (genre "Case study") as the page's main entity, by and published by the organisation |
| Services page | `OfferCatalog` of the service pages |
| Service pages and the home page's services | `serviceType`, `BusinessAudience`, the free audit `Offer`, `brand`, a `ServiceChannel` (the contact page, in every language), `termsOfService` |
| The free audit | One `Offer` node on every page: price 0 SGD, in stock, eligible in Singapore and Malaysia, offered by the organisation, pointing at the landing page |
| Team page | `ItemList` of the `Person` nodes |
| Header navigation | A `SiteNavigationElement` per link (full layout), localised on the language copies |
| Landing pages | The page, its breadcrumb, its `Service` and its elements at the copy's own address and in its language |

What the layer never states: ratings, reviews, prices for the services, opening hours, events, jobs or anything else the site does not show. `tests/test-schema-extended.php` covers the pure parts; the two audits in this release checked the printed graph of every page type on a WordPress install for dangling references, duplicate ids and the required properties of each type.

### Structured data for the page furniture and both layouts (`inc/content/schema-elements.php`, v1.118.0)

Every page also describes its own parts. The header (`WPHeader`), the footer (`WPFooter`) and, where the template has one, the sidebar (`WPSideBar`) are `WebPageElement` nodes with a CSS selector, joined to the page by `isPartOf`.

The one-page layout adds more: one element for each section, the six services as `Service` nodes inside an `ItemList`, the four steps as a `HowTo` with `HowToStep` entries, the checklist and the team as `ItemList` nodes (the people are the `Person` nodes the organisation already lists), and the FAQ as its own `FAQPage`. A hidden section leaves no data behind. The ad landing pages add a `Service` for the offer in the language of the URL. The nodes use types that no SEO plugin manages, so they appear with or without Rank Math. `tests/test-schema-elements.php` covers them.

Schema.org has no type for every element. Paragraphs, buttons and decorative images carry none; the page, its parts, its lists and its entities do.

## Language versions (`inc/i18n/`, v1.104.0)

Serves `/ms/`, `/zh-hans/` and `/zh-hant/` beside the English site. It is a
language layer, not a set of copied pages: the prefix is removed from the
request before WordPress parses it, so `/ms/team/` is the same page as
`/team/`, and the rendered text is translated by text node from the
dictionaries. Anything without an entry stays in English, visibly and without
breaking the page. A page is live in a language only if switched on (Tools >
Translations). The main pages (home, services, case studies, about, team,
insights, contact, FAQ, privacy, terms) are live in all four languages from
1.105.0, with a switcher in the header (Theme Options → Languages switches each
language and each switcher on or off; a language that is off redirects to English
everywhere, landing pages included); the unlisted case studies and the
articles are off. The brief's one page is a landing page (see `docs/ssot.md`). A page that is off redirects to its
English address. The ad landing pages carry their own copy for each language and
opt out of the translator with the `remotive_i18n_translates_request` filter.
Every translated page (29 per language: the main pages and all fourteen case studies) is live by default. Insights (the posts page and the articles) is English only, with no language versions, links, hreflang or sitemap entries (`remotive_i18n_is_english_only()`, v1.112.0); switch one off in Tools → Translations, or narrow the list with the `remotive_i18n_live_pages` filter. Hreflang uses
language plus script (`zh-Hans`, `zh-Hant`), `ms-MY` for Malay.

## Security

Audited against a structured security policy (v1.8.0), and again across the
whole theme in v1.103.0 (finding register in `docs/ssot.md`). Tests that guard
this: `tests/test-security.php` and `tests/check-security-patterns.php`.
Client addresses for the rate limits come from `remotive_client_ip()`, which
believes Cloudflare's forwarded-address header only from Cloudflare's own
ranges. Full findings and
fixes are in `docs/changelog.md`'s v1.8.0 entry and `docs/ssot.md`'s audit record;
summarized here for anyone extending this code.

- **Sanitize on input, escape on output, both, independently.** Every
  Theme Options field is sanitized on save
  (`remotive_sanitize_theme_options()`: `sanitize_email`,
  `sanitize_text_field`, `esc_url_raw` per field type) *and* escaped again
  at the point it is injected into rendered HTML
  (`remotive_replace_theme_option_tokens()`: `esc_url()` for URL fields,
  `esc_html()` for text fields). If you add a new option, follow the same
  pattern, do both, do not rely on the save-time sanitizer alone to make
  later output automatically safe.
- **Every native form** (the homepage CTA, the About page, the Contact
  page, the ad landing pages and the one-page layout) goes through one
  shared, reviewed function, `remotive_handle_lead_form_submission()` in
  `inc/forms/lead-form-handler.php`, rather than each having its own copy
  of the same logic. `inc/forms/cta-form-handler.php`,
  `inc/forms/about-form-handler.php`, and `inc/forms/contact-form-handler.php`
  are thin wrappers supplying their own nonce action, honeypot field name,
  and redirect target; `inc/landing/landing-pages.php` calls the same
  function for the landing and one-page forms. All the endpoints are
  intentionally public (`admin_post_nopriv_*`, anonymous visitors must be
  able to submit a lead form, so no capability check applies, that is by
  design). All are protected by: a nonce (`remotive_form_nonce_valid()`,
  which sends a visitor with an expired nonce back to the form with its
  normal error message instead of WordPress's "link has expired" page,
  v1.119.10), a honeypot field invisible to real users (`.rm-cta__honeypot`
  in `remotive.css`, verified via `getBoundingClientRect()` at 1×1px and
  by eye; `remotive_honeypot_tripped()` treats any value, "0" and arrays
  included, as a bot), a CRLF-injection guard on the emailed `Reply-To`
  header (independent of `sanitize_email()`/`is_email()` already blocking
  that, see the code comment for why both exist), and a per-IP, per-form
  rate limit (three submissions per ten minutes for the site forms, ten for
  the landing forms, via a transient, counted only after the honeypot and
  the email check so a mistyped address does not use up the quota). The
  enquiry is stored by `remotive_insert_lead_post()`, which slashes the
  text and pauses kses for that one insert (restored in a `finally`), and
  its name, email and source meta are slashed too. None of this makes the
  endpoint bot-proof, a scripted client that loads the real page first
  (to get a valid nonce) can still submit repeatedly, just at a slower
  rate. A CAPTCHA or WAF-level rate limit would close that gap further;
  deliberately not added here since picking a CAPTCHA vendor is a
  product/UX decision, not something to make unilaterally in a theme.
- **One informational, not-fixed item:** `register_setting()` does not
  specify an explicit capability, so the Settings API's own save-path
  check (inside WordPress core's `options.php`) defaults to
  `manage_options`, while the admin page's own access check
  (`remotive_render_theme_options_page()`) uses `edit_theme_options`.
  Identical in a stock install (both admin-only), no practical gap
  today, but if a site ever grants `edit_theme_options` to a non-admin
  role without also granting `manage_options`, that role could see the
  settings page but get a permission error attempting to save. Annoying,
  not a vulnerability (the write is still correctly blocked either way).
  Not "fixed" here without a live WordPress install to verify the exact
  correct mechanism against, noted instead of guessed at.
- **The only custom REST route is public and read-only.**
  `GET /wp-json/remotive/v1/search` accepts a sanitized, non-empty query of
  at most 200 characters and a result limit from one to ten. Its `WP_Query`
  is fixed to published posts and pages, returns a deliberately small data
  shape, performs no mutation, and uses no direct SQL. It therefore needs no
  nonce or capability check: it exposes the same public content as ordinary
  site search. Any future write route must not copy that permission model.
- **No `$wpdb`/direct SQL, file uploads, or custom authentication exist in
  this theme.** Those attack surfaces remain absent. WebMCP's navigation
  tool also rejects non-HTTP(S) and cross-origin targets; its lead
  tools never submit forms or touch action, nonce, or honeypot fields.

### Hardening module (`inc/core/security.php`, v1.68.1)

Added in response to patterns in the site's own first-week server logs
rather than as generic best practice. Each measure is a named function with
its own hook, so any can be removed individually with `remove_action()` /
`remove_filter()` from a mu-plugin without editing the file.

| Measure | Hook | What prompted it |
|---|---|---|
| Login rate limit, 5 failures per IP per 10 min | `wp_login_failed`, `authenticate`, `wp_login` | 271 `wp-login.php` hits from one IP |
| XML-RPC disabled, pingback methods removed, `X-Pingback` header stripped | `xmlrpc_enabled`, `xmlrpc_methods`, `wp_headers` | 7 probes from 5 IPs |
| Application passwords refused and hidden from profiles while Theme Options → Site behaviour → Block application passwords is on, which it is by default (v1.120.1) | `wp_is_application_passwords_available` | one more REST login route closed |
| Comments closed and the comments feed not advertised (v1.120.6); `remotive_comments_enabled` opens them | `comments_open`, `pings_open`, `feed_links_show_comments_feed` | an unseen comment form is a spam inbox |
| `install.php` / `setup-config.php` redirected to home | `admin_init` | 91 × 500 errors; `setup-config.php` returning 200 to external IPs |
| Generator tag, feed version and `?ver=` strings removed | `wp_head`, `the_generator`, `*_loader_src` | Standard fingerprinting vector |
| REST `wp/v2/users` refused unauthenticated, at authentication and again at dispatch so an `?_embed=author` request cannot step around it; only the path and the `rest_route` argument count as a route (v1.120.0). Author archives and `?author=N` redirected at `template_redirect` priority 0, ahead of `redirect_canonical()`, which would otherwise turn `?author=1` into `/author/<login>/` first | `rest_authentication_errors`, `rest_pre_dispatch`, `template_redirect` | Username harvesting |
| `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy` | `wp_headers` | Baseline hardening |

Two implementation notes that cost time to learn and are easy to repeat:

- **`is_admin()` returns true on `wp-login.php`.** Anything hooked to
  `admin_init` that redirects will fire during login. The install-endpoint
  block matches on `SCRIPT_FILENAME`'s basename for exactly this reason.
- **The `authenticate` filter runs on every page load** and on login
  submissions alike. WordPress uses it to validate the logged-in cookie via
  `determine_current_user()`. Returning a `WP_Error` unconditionally
  fatals the whole site. The rate limiter returns early unless both a
  username and password are present *and* the request is a POST.

Content-Security-Policy is deliberately not set: a CSP covering the admin,
the block editor and third-party scripts needs site-specific tuning and
breaks the editor when wrong. Leave it to the server or a security plugin.

**What a theme cannot do.** Block an IP, rate-limit at TCP level, or
protect `wp-login.php` before PHP runs. Those need the firewall or
`.htaccess`. This module raises the cost of automation; it is not a WAF.

### Error handling (`inc/core/error-handler.php`, v1.69.0 / v1.69.2)

Replaces WordPress's generic critical-error page and keeps PHP diagnostics
off the page entirely.

- **Fatals**: `set_exception_handler()` (with a real stack trace) and
  `register_shutdown_function()` (for `E_ERROR`, `E_PARSE` and friends,
  where only file/line/message exist) render a branded page. Administrators
  see message, file, line and trace; everyone else sees an apology and a
  reference ID. The same ID is written to the error log, so a visitor can
  quote it without ever seeing internals.
- **Non-fatals**: `set_error_handler()` claims notices, warnings and
  deprecations before PHP can print them. This is not cosmetic: a printed
  notice is output, and once output is sent every later `header()` call
  fails for the rest of the request, so one plugin notice cascades into
  failed redirects, cookies and cache headers. Entries are deduplicated by
  file+line+message, capped at 50 per request, and always logged.
  Administrators get a summary panel; visitors get nothing.

Both paths are defensive about their own environment: every WordPress
function is `function_exists()`-guarded with a PHP fallback
(`esc_html()` → `htmlspecialchars()`, `status_header()` → raw `header()`),
CSS is inlined and brand colours hardcoded, and output buffers are flushed
before rendering, a fatal in the theme itself, or one occurring before
`wp-includes/formatting.php` loads, still produces a readable page rather
than a white screen.

The previously registered error handler is captured and still called, so
Query Monitor and similar tools, which register during plugin load, before
a theme's `functions.php` runs, keep working.

`@`-suppressed expressions and anything outside the current
`error_reporting()` mask are handed straight back to PHP.

**WordPress's own fatal handler stays registered (v1.120.0).** It is set up
before any theme loads, so a theme cannot switch it off; the filter earlier
versions used for that (`wp_fatal_error_handler_enabled`) did nothing, and on
a real fatal WordPress printed the theme's `php-error.php` drop-in and then
the theme printed its own page under it. The filter is gone. Instead the
theme defines `REMOTIVE_ERROR_HANDLER` when it takes over, and
`drop-ins/php-error.php` returns at once when that constant is defined, so
one page is printed. Because WordPress's handler still runs, **recovery mode
keeps working**: a broken plugin is paused and the administrator emailed.
Any older note saying recovery-mode emails are lost is wrong. Hand the page
back to WordPress and the drop-in with
`add_filter( 'remotive_use_custom_error_page', '__return_false' );`.

**Non-HTML requests (v1.119.9).** An uncaught exception on a REST, AJAX or
cron request is written to the log and answered with a 500, never with the
HTML page. `E_USER_ERROR` is not claimed by the non-fatal handler, so a
script that raises it still stops.

**Locked out?** Define `REMOTIVE_SHOW_ERRORS` true in `wp-config.php` to
see full detail without an admin session. Remove it afterwards.

**Limit worth stating plainly:** errors raised before the theme loads, during core or plugin bootstrap, are outside any theme's reach. Production
still wants `WP_DEBUG_DISPLAY` false and `display_errors` off.

### Error pages the theme cannot draw (`error-pages/`)

The October 2026 server logs showed WordPress dying in `wp-settings.php` because `wp-includes/sodium_compat/src/Compat.php` was missing. The server answered 500 with an empty body, no theme code ran, and visitors saw a blank page. A theme cannot draw a page in that case, so the server has to.

`error-pages/500.html` and `error-pages/503.html` are static pages with the inline styling of the other error pages, in English, Malay and Chinese. `error-pages/htaccess-errors.txt` holds the `ErrorDocument` lines that point the server at them. Copy those lines into the site root `.htaccess`, above the WordPress block. The theme folder `.htaccess` cannot do this, because it only governs its own folder.

| Failure | Page |
|---|---|
| A fatal error after the theme loads | `inc/core/error-handler.php` |
| A fatal error before the theme loads, in a plugin | `drop-ins/php-error.php` (it returns early once the theme's handler has defined `REMOTIVE_ERROR_HANDLER`) |
| The database connection fails | `drop-ins/db-error.php` |
| An update is running | `drop-ins/maintenance.php` |
| WordPress cannot start at all (a core file missing) | `error-pages/500.html`, through `ErrorDocument` |
| An address that does not exist | `templates/404.html` |

### Addresses that used to work (`inc/core/not-found.php`, v1.118.0)

Two kinds of 404 in the logs are rescued with a 301: date style article addresses such as `/2026/09/seo-vs-sem/` (sent to the article's permalink, in the language of the request), and `/favicon.ico` and the Apple touch icon names (sent to the site icon). Every other 404 keeps the normal page.

## Colour scheme (`inc/options/colours.php`)

Theme Options → Colours sets the twelve palette roles separately for dark and
light mode. The slugs are roles, not shades: `ink` is the text colour and
`paper` the page background in whichever mode is showing, and the `-dark`
variants are the stronger accent used for text and buttons (only darker than
the plain colour in light mode; in dark mode `cyan-dark` equals `cyan`).

- **Only changes are emitted.** A colour that matches the shipped value writes
  nothing, so an untouched install is unchanged. Changed colours become CSS
  variables inline in the head with the critical CSS (no flash), in
  `html:not([data-theme="light"])` and `html[data-theme="light"]`, one step
  more specific than the stylesheet so they win in any load order.
- **Contrast** is checked live in the admin (WCAG ratio, eight key pairs per
  mode) and again on save. A pair under 4.5:1 is allowed but flagged. The
  button label on magenta uses the fixed label colour the stylesheet uses in
  that mode.
- **Defaults live in three places** and must stay in step: the dark values in
  `theme.json`, the light overrides in `assets/css/remotive.css` and
  `assets/css/critical.css`, and the defaults in `remotive_colour_tokens()`.
- The block editor's own palette (`theme.json`) is not changed by this tab.

## Two layouts in one package

The theme ships two layouts, and a single switch chooses between them.

1. **The full version.** This is the original theme and the default: the multi-page site with the menu, the services, the case studies, the blog, the team, the legal pages and the country ticker.
2. **The one-page landing version** (the Boss version). One scrolling page in the format of the ad landing pages, with a contact form at the top and another at the bottom. It is switched on in **Appearance → Theme Options → Agency layout**.

Rules that keep the two apart:

- With the switch off, nothing in the full version changes. Switching on and off again returns the site to where it was, because nothing is deleted or restructured.
- The two layouts share the language layer, the landing pages and their forms, the Rank Math handling, the migrations and the error pages. A change to any of those reaches both, so test both.
- The words of the full version live in the seeded pages and templates. The words of the one-page version live in Theme Options (`remotive_agency_*` defaults). Say which version a piece of text belongs to before editing it.
- Every change gets checked with the switch off and on, at desktop, tablet (768 and 1024) and phone widths, and in the three translated languages.

## Single-page agency layout (`inc/options/agency-layout.php`, `inc/landing/agency-page.php`)

An optional alternative home page, switched on and off in **Appearance → Theme Options → Agency layout**. It is **off by default**; with it off nothing in the theme changes, and switching it off puts the site back exactly as it was. Nothing is deleted or restructured.

- **On:** the home page renders one scrolling agency page in the format of the ad landing pages: the logo, the headline and introduction beside a contact form above the fold (the form sits straight under the headline on a phone), then the services (six cards), how we work (four numbered steps), signs it is time to talk (a six-line checklist), the team (small portraits, three sizes), the common questions, and a closing band with the second contact form. Each section has its own switch and its words are editable in the tab. No menu, no country ticker, no hero buttons, no reassurance line. The words (headline, introduction, form text, six services, headings) are editable in the tab. **The translated versions are kept:** the page is served in Bahasa Melayu and Simplified and Traditional Chinese (`/ms/`, `/zh-hans/`, `/zh-hant/`) through the language layer (language links in the bar, hreflang, sitemap), and is translated in Tools → Translations like the rest of the site; an edited English sentence needs its translation updated there.
- **Other pages:** a second switch (on by default once the layout is on) sends About, Team, Services, Case Studies, Insights, Contact, FAQ and every other page to the home page with a temporary 302, and removes them from the Rank Math, WordPress and AI Discovery sitemaps and from the language versions (the legal pages keep theirs). The ad landing pages, their thank-you pages and the legal pages (privacy, terms) stay. Editors can still preview any page. Turn the second switch off to keep the other pages live next to the new home page.
- **Forms:** both forms carry an optional "How did you hear about us?" select (`remotive_lp_source_options()`), recorded in English as "Heard about us via" in the email and the stored lead. Both forms post as the service `agency` through the existing landing-page handler (same nonce, honeypot, rate limit, email and thank-you page); the enquiry email says "Service: Agency (home page)".
- **How it works:** `template_include` swaps the home page to the landing template while the switch is on; `remotive_agency_should_redirect()` decides the redirects (tested); the language layer reports no language versions through the new `remotive_i18n_available` filter; the page styles are `assets/css/agency.css` (after `landing.css`) and `assets/css/agency-motion.css`, the scroll motion layer: three colour plates behind the hero and the closing band and one per section, each on its own scroll rate, the headline lifting against them, section headings and cards rising in with a stagger, and the big numbers drifting against their cards. It runs on CSS scroll timelines, with a `requestAnimationFrame` fallback in `landing.js` for a browser without them, is scoped to `body.rm-motion` (Theme Options → Site behaviour → Scroll motion) and is stilled under `prefers-reduced-motion`.
- **Section ids (v1.119.2):** `ag-services`, `ag-process`, `ag-signs`, `ag-team`, `ag-questions`. The full version's ids (`services`, `work`, `problem`, `why`, `about`, `contact`) carry layout rules in `remotive.css`, so the one-page sections must never reuse them; `tests/test-agency.php` fails if one does.
- **Kept legal pages (v1.119.11, v1.120.0):** while the other pages are hidden, Privacy and Terms replace the header and footer template parts with the one-page bar and footer (`remotive_agency_legal_chrome_block()`), so they offer no link that would redirect home. The bar's logo links to the home page, and its language links point at the legal page in each language (`remotive_agency_lang_url()`, `remotive_agency_current_page_path()`), not at that language's home page. The three language bars carry `translate="no"` so the language layer leaves their addresses alone (v1.119.3).
- **Redirect rule (v1.119.10):** a request counts as content to redirect only when a page stands behind it (`remotive_agency_is_content_request()`), so a site with Settings → Reading on "A static page" and no Homepage chosen cannot loop; the core sitemap endpoints are left to core.

## Ad landing pages (`inc/landing/landing-pages.php`)

Three single-purpose pages for paid and social traffic, one per service:
`/seo-audit/`, `/google-ads-management/` and `/paid-social-advertising/`.
Each is also served in Bahasa Melayu (`/ms/…`), Simplified Chinese
(`/zh-hans/…`) and Traditional Chinese (`/zh-hant/…`; the `/zh-cn/` and
`/zh-tw/` addresses of v1.90.0 to v1.103.x redirect). The server renders only
the language of the URL, with a self-referencing canonical, `<html lang>` and
`hreflang` alternates; there is no `?lang=` parameter.

- **Copy and data** live in `inc/landing/landing-copy.php` (`remotive_landing_services()`,
  one entry per service, and the FAQ; each text an array of en, ms, zh-Hans,
  zh-Hant) and, for the short shared labels, in the render functions.
  `php tests/check-landing-copy.php` (also run in CI) fails if any text is
  missing a language. `templates/page-landing.html` only holds the
  `__REMOTIVE_LANDING__` token. A new service is one entry there plus one
  page in `remotive_required_pages()`.
- **Funnel:** form above the fold, how it works, six market photo tiles, a
  second form, an FAQ with matching `FAQPage` JSON-LD. No site navigation.
  Forms use the shared lead handler, record the service, the optional "How
  did you hear about us?" answer (`remotive_lp_source_options()`, eight
  listed answers, stored in English as "Heard about us via", v1.119.0) and
  the campaign parameters (`utm_*`, `gclid`, `fbclid`, `ttclid`) with the
  enquiry, and redirect to `/audit-requested/` (also per language). The
  handler checks that page exists and is published first
  (`remotive_lp_thanks_url()`, v1.120.0); without it the visitor goes to the
  shared thank-you page or back to the landing page with its inline
  message, never to a 404. The confirmation page is the conversion URL;
  `inc/forms/thank-you.php` pushes `remotive_lead` with the form, service and
  language. Its language links carry `service` and never `from`, because
  `from=lp` is what fires the event (v1.119.7).
- **Search engines (from v1.110.0):** `noindex, nofollow` by default, and
  controllable in Rank Math (the page's Advanced tab, `rank_math_robots`): the
  tag, the `X-Robots-Tag` header and the sitemap all follow that choice, and the
  theme fills the box once per page so it starts ticked. Excluded from site
  search. Never block them in `robots.txt`: crawlers must fetch the page to see
  the noindex, and Google Ads must fetch it to review the ad.
- **Closed off from the main site (v1.110.0):** hidden from page lists, menus,
  navigation blocks, the REST page listing, search and the sitemaps, and a click
  from any page of this site is turned back to the home page
  (`remotive_lp_guard_internal_entry()`, using `Referer` and `Sec-Fetch-Site`).
  Ads, social, search, typed addresses and bookmarks get in; the language
  buttons, a reload and the form's thank-you redirect work. Exclude the landing
  slugs from any page cache.
- **Results strip (v1.109.0):** "Results from the work", at least three cards
  per service (figure, one-line result, client), from the case studies, in four
  languages: the `proof` list of each service in `landing-copy.php`. The figures
  use the licensed Kagnue display serif (`assets/fonts/kagnue/`, WOFF2 subset;
  the plus sign and en dash fall back to Saira). On phones (v1.109.1) the page is
  shorter: the form comes straight after the headline, the results swipe
  sideways, steps and cities are compact.
- **Assets:** `assets/css/landing.css`, `assets/js/landing.js` (campaign
  capture, funnel events, sticky CTA; no language logic) and
  `assets/images/landing/` (Pexels photography, credited in
  `docs/image-credits.md`).
- **Requires pretty permalinks.** The language paths are handled by the
  language layer (`inc/i18n/i18n.php`); the stored rewrite rules are flushed
  once per rule-set version (`remotive_lp_rewrite_v`, now `3`) to drop the
  rules earlier versions registered.

## Maintenance mode (`inc/options/maintenance-mode.php`)

Theme Options → Site behaviour → Maintenance mode. Logged-out visitors get
`drop-ins/maintenance.php` with a 503 and `Retry-After`; users who can edit
posts, wp-admin, cron, AJAX, feeds and REST are unaffected. Off by default.

## Theme Options (`inc/options/theme-options.php`)

An admin page under **Appearance → Theme Options** covers the values that
used to be hardcoded directly in the block templates, and the switches
added since: contact email, the three footer address lines, the seven social
URLs, the CTA form's submission URL, which colour mode first-time visitors
land on (light / dark / match their device; light since v1.120.3), the
colour scheme, the languages, the one-page layout and the site behaviour
switches.

**Tabs (as of v1.120.1).** Nine settings tabs, in this order, plus two card
panels:

| Tab | Holds | Fields |
|---|---|---|
| **Homepage** | Hero, Section headings, Closing call to action, Numbers and Team, as groups in page order | 28 |
| **Agency layout** (`inc/options/agency-layout.php`, v1.116.0) | The one-page layout switch, the switch that sends the other pages home, and the words of every section (opening, services, how we work, signs, questions, team, closing form), each section with its own show/hide switch | 65 |
| **Business details** | Contact email, three address lines, registered name, UEN, seven social URLs | 13 |
| **Call-to-Action** | The homepage form's submission URL (`cta_form_action`); blank or `#` falls back to the built-in handler at save time and at render time, `remotive_form_action_or_default()`, v1.120.0 | 1 |
| **Country ticker** (v1.66.8) | Countries, visibility, colours, size, weight, spacing, padding, speed, direction | 12 |
| **Languages** (v1.106.0) | Bahasa Melayu, Simplified and Traditional Chinese on or off; header and footer switchers | 5 |
| **Colours** (`inc/options/colours.php`, v1.95.0) | Twelve palette roles per mode, a reset switch, with a live contrast check | 27 |
| **Integrations** (v1.91.0) | The Pexels API key (write-only) and its remove switch | 2 |
| **Site behaviour** | Enquiry retention, branded login and its message, maintenance mode, graceful error handling, application passwords (v1.120.1), scroll motion, default colour mode | 8 |
| **Site setup** (panel) | Shipped content with per-item "Restore shipped version", Menus, Site pages | |
| **Enquiries** (panel) | The stored enquiries, the spam folder, CSV export | |

`remotive_theme_option_defaults()` holds 165 stored settings: 75 base
settings, 25 colours and 65 agency settings. Every one is declared,
defaulted and covered by `remotive_sanitize_theme_options()`.

**History.** Until v1.79.1 there were seven tabs, four of which edited the
homepage; v1.79.1 merged them into one **Homepage** tab with groups and
renamed **Display** to **Site behaviour** and **Contact & Socials** to
**Business details**. v1.79.2 made the two card panels tabs. The Languages,
Colours, Integrations and Agency layout tabs came later.

**Site setup and Enquiries are tabs too (v1.79.2).** The site-setup card
(Shipped content, Menus, Site pages) and the enquiries card both contain
their own `<form>` elements, so they cannot be nested inside the settings
form and are rendered after it closes. Until v1.79.2 that left them sitting
below the entire tabbed area, visible on every tab, they looked repeated
in each one. They now have real tab buttons appended to the tablist and
panels carrying the standard `rm-admin__panel` / `remotive_panel_*`
markup, so the existing tab script picks them up with no special-casing:
it resolves panels by `getElementById()` from `aria-controls`, which does
not care where in the DOM the panel sits, and collects tab buttons with a
`[role="tab"]` query, so keyboard navigation and the remembered-tab
restore work unchanged.

The settings form's Save button is hidden while either card tab is
showing, since those panels manage their own state through their own
forms and there is nothing for it to submit.

**Groups.** `remotive_theme_options_tabs()` entries may declare either a
flat `fields` array or a `groups` array of
`array( 'label', 'description', 'fields' )`. The renderer handles both, so
tabs that do not need internal structure are unaffected, the change is
additive rather than a rewrite of the settings screen. Group styling lives
in `assets/css/admin-theme-options.css` under "Field groups within a tab".

**Country ticker tab (v1.66.8).** The scrolling market strip below the
homepage hero was hardcoded in `templates/front-page.html`, five country
names, duplicated four times for the continuous loop. It is now twelve
options: countries (comma-separated), visibility toggle, background and
text colour (native colour pickers), separator colour (hex or `rgba()`),
font size, weight, letter spacing, vertical and horizontal padding, scroll
speed and direction.

`remotive_render_ticker()` builds the markup and handles the four-times
duplication the CSS animation needs, so the field takes a clean list with
no manual repetition. `remotive_ticker_css()` generates a `<style>` block
attached to `remotive-style` via `wp_add_inline_style()`, same pattern as
the team portrait CSS.

**Colours are emitted only when they differ from the defaults (v1.79.1).**
This is load-bearing, not an optimisation. The band's colour defaults
(`#1a1a2e` on `#f7f4ec`) describe the *light-mode* strip, navy on cream.
Emitting them unconditionally overrode `.rm-ticker`'s own rule, which is
built on `ink`/`paper` and is meant to invert with the mode, so dark mode
rendered a navy band on the navy page: visible only as floating text and
dividers. Skipping the declaration at default values lets the stylesheet
invert as designed, while an owner who has deliberately picked colours
still gets exactly those in both modes. The separator is handled the same
way for the same reason, its default is a translucent white, invisible
against the white band dark mode is supposed to show.

`.rm-ticker` also carries literal fallbacks (`var(--…--ink, #ffffff)`).
`ink` and `paper` are the one pair in the palette that swap between modes,
and the theme defines them for light mode only; dark relies entirely on
the `:root` block WordPress generates from `theme.json`. If a performance
plugin strips or defers that stylesheet the variables are undefined, the
background falls back to transparent and the page shows through. The
item divider derives from `currentColor` rather than a hardcoded white,
so it follows the text colour on either surface. Animation duration scales with the country count so
perceived speed stays constant as entries are added or removed (reference:
26s at five countries). Direction reverses by swapping the keyframe
`translateX` target and the track's `flex-direction`, which leaves the
IntersectionObserver pause-when-off-screen logic untouched.

**Site behaviour tab.**

| Option | Default | Effect |
|---|---|---|
| `lead_retention_months` | 24 | Stored enquiries older than this many months are deleted daily, both folders. 0 keeps them. See "Enquiries and spam". |
| `branded_login`, `branded_login_message` | off, "Team access only." | `inc/core/branded-login.php`: wp-login.php in the site's logo, colours and typeface, with one line above the form. Appearance only; a login-branding plugin keeps control. |
| `maintenance_mode` | off | `inc/options/maintenance-mode.php`: 503 "back shortly" page for logged-out visitors. See "Maintenance mode". |
| `graceful_errors` | on | Registers the non-fatal error handler in `inc/core/error-handler.php`. Read directly from the option row, not via `remotive_get_theme_option()`, because that file loads before `inc/options/theme-options.php`. |
| `block_app_passwords` | on | Filters `wp_is_application_passwords_available` to false (`inc/core/security.php`, v1.120.1), so WordPress refuses application passwords and hides the section on every profile. Read straight from the option row; an options array saved by an older version, which has no such key, counts as on. Switch it off only while an integration (a mobile app, a connector plugin, a script) needs to log in to the REST API with one. |
| `motion_effects` | on | Adds an `rm-motion` body class. All scroll-motion CSS (`remotive.css` and `agency-motion.css`) and the JS fallbacks are scoped to it, so off means no rule matches and no observer is created rather than effects being overridden. Not added in the admin, so the block editor is unaffected. |
| `default_theme` | `light` (v1.120.3; `dark` before) | The mode a visitor sees before they use the toggle: light, dark or system. The visual selector lists Light first. Migration 1.120.3 moves a saved `dark` to `light` once and leaves `system` alone. |

**Scroll motion (v1.69.1).** Two implementations, one behaviour. Where the
browser supports CSS scroll-driven animations (`animation-timeline`,
Chromium 115+, Safari 26+) the effects are pure CSS running on the
compositor thread, there is no scroll listener at all, so the number of
animated elements cannot cause jank. Where it does not, an
`IntersectionObserver` adds `.is-in` once per element and a transition
finishes the job; each element is unobserved after firing. Modern browsers
exit the JS block immediately via a `CSS.supports()` check.

Only `opacity` and `transform` are animated, both compositor properties,
neither triggering layout or paint, so none of it can contribute to CLS.

The `.rm-reveal` class that hides an element pre-animation is added by
script, never by a template. If JavaScript fails or is disabled nothing is
ever hidden; the page simply renders without motion. `prefers-reduced-motion`
disables everything in both paths, and the JS checks the media query before
doing any work.

**Mechanism.** Block templates (`templates/front-page.html`,
`parts/footer.html`) are static files parsed at request time, there is no
PHP interpolation available inside them, so the options cannot be injected
via the normal "just echo a PHP variable" approach classic themes use.
Instead, the templates contain plain-text placeholder tokens, `__REMOTIVE_CONTACT_EMAIL__`, `__REMOTIVE_SOCIAL_LINKS__`, and so on, and a `render_block` filter in `inc/options/theme-options.php` does a `strtr()`
token swap against every block's rendered output, on every request.

This runs *after* WordPress renders each block, so it works identically
whether the token started out inside a Paragraph's text content, a
Navigation Link's `url` attribute, or a raw Custom HTML block's `action`
attribute, all three occur in this theme. It sidesteps the official Block
Bindings API (`register_block_bindings_source()`, WP 6.5+), which as of
this writing only supports binding `content`/`url` on a small allow-list of
core blocks (paragraph, heading, image, button), Navigation Link is not on
that list, and Custom HTML blocks are not bindable at all. The token/filter
approach works uniformly across all three, at the cost of being a
theme-specific convention rather than a documented WordPress API, noted
here so a future maintainer does not go looking for `metadata.bindings` in
the template files and wonder where it went.

**Token naming.** Deliberately alphanumeric-plus-underscore only
(`__REMOTIVE_KEY__`), no braces or punctuation, so the token string passes
through `esc_url()` and WordPress's other sanitisation functions completely
unchanged before the filter ever runs. Curly-brace-style tokens were tried
first and rejected for this reason, see `docs/changelog.md` v1.3.0.

**Defaults.** `remotive_theme_option_defaults()` in `inc/options/theme-options.php`
matches exactly what was hardcoded before this page existed, so installing
this feature changes nothing on the front end until someone actually edits
a setting.

**What is still hardcoded on purpose.** The six services, the two
case-study rows, the three stats, and all hero/CTA copy remain directly in
`templates/front-page.html`, these are real, block-editable content (edit
them in the Site Editor), not settings-page material. Only things that are
either outside the block editor's reach (a `<form>` attribute) or naturally
site-configuration rather than content (contact details, socials) went into
Theme Options. See `docs/upgrading.md` for the custom-post-type plan for
services/case studies specifically.

**Admin UI.** The settings page does not use WordPress's default
`do_settings_sections()` table renderer, `remotive_admin_enqueue_assets()`
(hooked to `admin_enqueue_scripts`, scoped to only this page via its hook
suffix) loads `assets/css/admin-theme-options.css` and
`assets/js/admin-theme-options.js`, which render a card-based, tabbed
layout instead. A few things worth knowing if you touch this:

- `register_setting()` is still used (required, it is what makes
  `options.php` accept the save), but `add_settings_section()`/
  `add_settings_field()` are **not**, those exist purely to feed the old
  table renderer this page no longer calls, so they would just be unused
  infrastructure. Field/tab data lives in `remotive_theme_options_tabs()`
  instead, a single array the renderer reads from directly.
  `remotive_sanitize_theme_options()` deliberately does **not** read from
  that array, it stays hand-written and explicit about every key it
  accepts, so a typo in the tabs array cannot silently widen what gets
  saved.
- Tabs follow the WAI-ARIA "Tabs" pattern (`role="tablist"`/`"tab"`/`"tabpanel"`,
  roving `tabindex`, arrow-key/Home/End navigation), see
  `assets/js/admin-theme-options.js`. All fields across all tabs still
  submit together in one `<form>` POST; switching tabs is purely a
  client-side visibility toggle, not separate forms or an AJAX save.
  The active tab is remembered via `sessionStorage` across the save
  reload, so hitting "Save Changes" does not dump you back on the first
  tab.
- The "Default colour mode" field is a visual-selector (clickable cards
  with a colour-swatch preview) instead of a plain radio list, the CSS
  uses `:has()` for the active-card styling, with a small JS class-toggle
  as a defensive fallback for browsers without `:has()` support.
- The toggle-switch component in `admin-theme-options.css`
  (`.rm-admin__toggle`, field type `toggle`) is used by every on/off
  setting: the ticker, the languages, the Site behaviour switches
  (branded login, maintenance mode, graceful errors, application
  passwords, scroll motion) and the Agency layout switches. A toggle
  posts `1` when on and nothing when off, which is why migrations that
  rewrite the options array must go through `remotive_update_options_raw()`
  and never through the settings sanitiser (v1.119.9).
- Two inline `<span>` elements inside the visual-selector cards
  (`.rm-admin__visual-swatch`/`-title`/`-desc`) needed explicit
  `display:block`, a `<span>` is inline by default and silently ignores
  `width`/`height`, which collapsed the colour swatches to invisible
  slivers before this was caught in testing. Worth remembering if you add
  more components with inline elements sized via CSS.



Almost the entire homepage is real, editable WordPress blocks. Three
exceptions, all in `templates/front-page.html`, and one in `parts/header.html`:

| Element | Why it is Custom HTML |
|---|---|
| Hero headline | Needs `data-t="..."` attributes on each line for the misregistration hover (see above). If you edit the wording via the Code Editor, update the matching `data-t` attribute or the hover will show stale text. |
| Ticker marquee | Infinite horizontal scroll of repeated text, no core block does this. |
| CTA email form | Core WordPress has no native `<form>` block. Submits by default to `inc/forms/cta-form-handler.php`'s native handler (see "Security" above), no third-party service needed, but the destination address is set via Theme Options, not hardcoded here; a blank or `#` value falls back to the built-in handler at render time (`remotive_form_action_or_default()`). Its success and error messages are pre-rendered `.rm-cta__msg` spans that `lead-form-status.js` only reveals, so the language layer translates them (v1.120.0); the About and Contact forms do the same. |
| Theme toggle button | Needs `id` attributes (`rmThemeToggle`, `rmThemeToggleLabel`) as JS hooks, not editable copy. |

## Logo bootstrap

On `after_switch_theme`, `functions.php` copies the two bundled logo files
(`assets/images/remotive-mark.png`, `assets/images/remotive-lockup-square.jpg`)
into the media library via `wp_upload_bits()` + `wp_insert_attachment()`, no network fetch, pure local file copy, and sets them as Custom Logo and
Site Icon respectively. Guarded by:

- Only runs if `get_theme_mod('custom_logo')` / `get_option('site_icon')`
  are empty, so it never overwrites a later manual choice.
- Only runs once total, guarded by the `remotive_branding_done` option flag,
  so reactivating the theme does not re-import duplicate attachments.

## Social icons

Added in v1.13.0. Instagram/LinkedIn/TikTok icons in the footer and the
Contact page's sidebar are inlined `<svg>` markup (`fill="currentColor"`,
so they follow light/dark mode automatically) inside Custom HTML blocks, not `<img>` tags, and not loaded from `assets/icons/` at runtime (those
files are kept for reference/reuse only). The same
`__REMOTIVE_SOCIAL_*__` tokens from Theme Options still drive the `href`
values, via the existing `render_block` token filter, nothing new
needed there, it already applies to any block's output regardless of
type. See `docs/resources.md` for licensing, two of the three icons are CC0
(no attribution needed), one (LinkedIn) is CC BY 4.0 and requires the
visible "Icons by Font Awesome" credit already added next to the
copyright line, do not remove that credit without removing the icon it
covers.

## Fonts

Archivo, Newsreader, Saira, and Space Grotesk are self-hosted as `.woff2` files (plus Kagnue, a licensed one-weight display serif used only for the big figures on the results cards and the Case Studies cards; its plus sign and en dash fall back to Saira through `unicode-range`)
under
`assets/fonts/`, declared via `theme.json`'s `fontFace` mechanism
(`file:./assets/fonts/...` relative paths, WordPress core resolves these
relative to the theme root and auto-generates `@font-face` rules). Every face
declares `fontDisplay: "swap"`, so fallback text remains visible while a font
downloads. No Google Fonts CDN calls at runtime, deliberate, for GDPR
compliance on the EU/DACH-facing side of Remotive's client work.

## Local development

No build tooling required. Edit `assets/css/remotive.css` directly; changes
are picked up immediately (cache-busted via `filemtime()` in the
`wp_enqueue_style()` call in `functions.php`, so no manual version bumping
needed during development). If a change affects the first viewport, mirror the
minimum required declarations in `assets/css/critical.css`; if it affects paper
output, update `assets/css/print.css` and inspect A4 and Letter Print Preview.
Remember to bump the theme's own `Version:` in `style.css` for releases, see
`docs/changelog.md`.

To validate the block templates after hand-editing them, check that every
`<!-- wp:X -->` has a matching `<!-- /wp:X -->` (or is self-closing via
`/-->`), a quick Python script pairing regex-matched open/close tags is
sufficient; there is no official CLI linter for hand-authored block HTML.

## Writing style

All English text follows the anti-AI writing style guide: no contractions, no em dashes, no banned vocabulary or dead phrases, no negation followed by a correction, sentence-case headings, no emoji. `php tests/check-writing-style.php` enforces the hard rules and CI runs it. It covers the agency page, the templates and parts, the seeded pages and articles, the strings in the PHP and JavaScript code, and the documentation for the current release series.

Malay text is also checked against Tatabahasa Dewan Edisi Ketiga by `php tests/check-tatabahasa.php` (the catalogue of frequent errors, plus the Indonesian forms to remove), next to the spelling check in `tests/check-ejaan.php`. Both run in CI.

Three rules for changing text:

- The Malay and Chinese dictionaries in `inc/i18n/` are keyed by the exact English sentence. When you reword a sentence, add the new wording as a key with the existing translation and leave the old key in place, so a page that was edited keeps its translation.
- A page or article that already exists on a site is refreshed by the migration in `inc/setup/site-setup.php`, which replaces a value only while it matches an earlier seed (`remotive_old_article_seo_hashes()`, `remotive_old_page_copy_hashes()`). Add the hash of the old text when you change a seeded page.
- Menu names that sites look up by name are identifiers, so they keep their wording.

## Tests and CI

Every check and test is plain PHP with no WordPress install:
`tests/bootstrap.php` stubs the WordPress functions the tested logic calls,
and anything else fails as an undefined function, which is the point.
`.github/workflows/ci.yml` runs all of them on every pull request, on PHP
7.4 and 8.3, after `php -l` over every PHP file, `node --check` over every
script and a JSON parse of `theme.json`. Run one with `php tests/<file>`.

**Checks** (`tests/check-*.php`, static rules over the source):

| File | Fails when |
|---|---|
| `check-article-seo.php` | A seeded article misses Rank Math's own content tests: description length, keyword density and placement, 1,000 to 1,500 words, an internal and an external link, block markup throughout. |
| `check-block-markup.php` | A block comment in `templates/` or `parts/` is unclosed, its attributes are not valid JSON, or a heading's level disagrees with its tag or is not followed by a heading (v1.119.10). |
| `check-ejaan.php` | A Malay string breaks a Pedoman Umum Ejaan rule the script can check (spelling of loan words, di/ke/dari, numbers, commas, dashes, quotes). |
| `check-landing-copy.php` | A landing-page text is missing one of its four languages, or a Chinese string holds no Chinese. |
| `check-modules.php` | A file under `inc/` is not required by `functions.php`, or a require points at a missing file. |
| `check-parent.php` | `theme.json` or `remotive.css` stops carrying Twenty Twenty-Five's colour slugs, or a parent function is redefined. |
| `check-security-patterns.php` | `wp_redirect()`, `eval()`, exec-family calls, `base64_decode()`, `unserialize()` without `allowed_classes`, a raw `$wpdb` query outside `inc/i18n/`, a module without the direct-access guard, a REST route without `permission_callback`, or JSON-LD without `JSON_HEX_TAG` appears. |
| `check-seed-images.php` | A seeded article names no image, or its JPEG or AVIF is missing from `assets/seed-images/`. |
| `check-tatabahasa.php` | A Malay string breaks a Tatabahasa Dewan rule the script can check, or uses one of the Indonesian forms on the list (v1.119.5, v1.119.6). |
| `check-versions.php` | `style.css`, `readme.txt` (Stable tag and "Latest version") and the newest `docs/changelog.md` entry disagree. |
| `check-writing-style.php` | Any English prose in scope breaks the writing style guide: contractions, em dashes, banned words, dead phrases, emoji, negation then correction. See "Writing style". |

**Unit tests** (`tests/test-*.php`, the theme's own logic run against stubs):

| File | Covers |
|---|---|
| `test-agency.php` | The one-page layout: defaults, sanitising, redirect rules, the page markup, the section ids, the legal page chrome, the parent rule for seeded pages. |
| `test-agency-i18n.php` | The one-page layout rendered through the real language layer for each language. |
| `test-colours.php` | Colour scheme defaults, sanitising, contrast arithmetic, generated CSS. |
| `test-forms.php` | Form handling: the slashed and kses-free lead insert, the honeypot, the rate-limit order, the nonce check, the form URL fallback, the non-HTML exception path (v1.119.9). |
| `test-i18n.php` | The language layer: which pages are live in which language, URLs, dictionaries. |
| `test-i18n-tokenizer.php` | The HTML translator's tokeniser: scripts and styles as one token, void elements, `translate="no"`, placeholders (v1.119.8). |
| `test-landing.php` | Landing pages: language from the URL, URLs, hreflang, `<html lang>`, robots, the confirmation page check, the source field, the `translate="no"` language bars. |
| `test-not-found.php` | The rescue of date style article addresses and icon requests. |
| `test-rank-math.php` | The theme's rules reach Rank Math's own filters. |
| `test-schema-elements.php` | Structured data for the page furniture and both layouts. |
| `test-schema-extended.php` | The rest of schema.org: durations, reading time, text decoding, the free audit offer, the organisation's extra properties, the plugin graph removal. |
| `test-security.php` | Client address, the REST users routes at authentication and dispatch, the author redirect, generic login errors, enquiry length caps, the application password switch. |
| `test-setup.php` | Setup migrations, the migration lock, the seeded page lookup, the contact address correction. |

The suite is for the repository. On a site, `remotive_remove_dev_files()` deletes `tests/` and `.github/` from the theme folder on activation and on the first request after an upload (v1.120.7); a git checkout keeps them, as does a site that defines `REMOTIVE_KEEP_DEV_FILES` as true.

## Known limitations

### Left deliberately (reviewed v1.96.0)

- **Inline `style=""` in templates.** What remains is WordPress's own block
  markup (column `flex-basis`, group padding, footer spacing), which must match
  each block's comment attributes or the editor reports the block as invalid,
  plus one runtime value (`--rm-team-cols` on the team grid). Hand-written
  inline styles (the case-study card gradients) were moved to classes.
- **Archivo and Newsreader files.** They look unused because `remotive.css`
  points both presets at Saira, but that override only applies once the page
  script has set `data-theme`. They are the fallback when JavaScript is off,
  and browsers only download font files a page actually uses, so they cost
  nothing on a normal visit.
- **315 `!important` rules.** Most beat WordPress's block and global styles
  (`.wp-block-*`, theme.json element styles). They can only be pruned safely
  with a real WordPress page to compare against, because a test page without
  WordPress's own CSS would show every removal as harmless. Do it on a staging
  site with before/after screenshots, one component at a time.
- **`remotive.css` size.** 143 KB raw, about 40 KB gzipped, loaded
  asynchronously behind inline critical CSS. Splitting it per template is
  possible (`blog-and-about.css` is already loaded conditionally) but the saving
  should be measured on the live site first.

- The six services and two case-study rows on the homepage are hardcoded in
  `templates/front-page.html`, not backed by a custom post type. See
  `docs/upgrading.md`.
- The tests run without WordPress, so a rendered page is never checked.
  Manual QA against both theme modes is still recommended after any change
  to `remotive.css` or `theme.json`.
- WebMCP browser support is experimental and limited. The integration is
  feature-detected, ships no polyfill, and cannot make an unsupported browser
  expose `document.modelContext`. See `docs/upgrading.md` for the live-validation
  checklist and future compatibility work.
- Static-asset cache headers cannot be guaranteed by the theme. They belong to
  the production origin/cache/CDN and include WordPress core, plugin and upload
  paths outside this theme; see "Performance delivery" and `docs/upgrading.md`.
- Internationalized as far as `.pot` extraction reaches: `languages/remotive.pot`
  covers the 410 gettext strings in `functions.php` and `inc/*/*.php` (the
  admin UI, form messages, email strings). It does **not** cover the
  homepage's copy, which lives as static content in
  `templates/front-page.html`'s block markup, which gettext has no reach
  into. The visitor-facing translations (Malay and both Chinese scripts)
  are the language layer's job (`inc/i18n/`, see "Language versions"), not
  gettext. `.mo`/`.po` files for the admin, once created from the `.pot`,
  go in `languages/`.
