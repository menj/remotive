=== Remotive Media ===
Contributors: menj
Tags: block-theme, full-site-editing, child-theme, one-page
Requires at least: 6.7
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.108.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A child theme of Twenty Twenty-Five, built exclusively for remotivemedia.asia.

== Description ==

Remotive Media is the "Registration" homepage design for Remotive Media Asia —
a bold, CMYK-inspired look built around the idea of overprinted colour plates,
paired with a calmer, brand-aligned alternate look you can switch to any time.
Beyond the homepage, the theme also includes a blog (archive + single post)
and nine selectable page templates: About (team grid + contact form),
Services (detailed breakdown), Case Studies, a Service detail template used
by the individual service and case study pages, a Legal page (Privacy
Policy, Terms, etc.), Contact, and two market landing templates for
Singapore and Malaysia.

The site includes a light/dark toggle in the top navigation:

* **Dark** (the default) — navy background with cyan/magenta/green accents
  standing in for printing plates, matching Remotive Media's official
  reporting and deck brand.
* **Light** — a softer cream background using Remotive Media Asia's official
  digital brand colours.

Both modes use the Saira typeface: the toggle changes colour only, never
type. (Before 1.41.2 the two modes rendered in different faces, which was a
bug. Footer column headings use Space Grotesk as a small scoped accent
since 1.66.5; everything else stays Saira.)

Visitors' choice is remembered automatically, so once someone picks a mode it
stays that way on their next visit. Which mode first-time visitors see is
configurable under **Appearance → Theme Options**, along with the site's
contact details, social links, the homepage contact form's destination, the
scrolling country ticker, and switches for scroll motion and error handling.

Sections lift gently into view as you scroll and the hero mark drifts behind
the content. Where the browser supports CSS scroll-driven animations this runs
entirely on the compositor with no scroll listener; elsewhere a one-shot
observer does the same job. Only opacity and position are animated, so page
layout is unaffected. Anyone whose device asks for reduced motion sees none of
it, and there is a switch under Theme Options → Site behaviour.

PHP errors are handled rather than dumped on screen. Visitors see a branded
page with a reference code; administrators see the actual fault, file and line.
Smaller notices and warnings are logged and kept off the page entirely, which
also prevents them breaking redirects and cookies further down the request.

The theme adds what security hardening a theme can reach: login rate limiting,
XML-RPC disabled, install endpoints closed once installed, version
fingerprinting removed, username enumeration blocked, and standard security
headers. Blocking IPs and protecting wp-login.php before PHP runs remain jobs
for the host's firewall.

This theme is **not a general-purpose product** — it's built for one specific
website and its specific content (services, case studies, brand assets). It
isn't intended to be installed on other WordPress sites.

Compatible browser-based AI agents can use the theme's progressive WebMCP
layer to search published content, discover canonical site destinations, and
open same-site pages. On pages containing a contact or audit form, an agent
may prepare the visible fields, but it cannot submit the form: the visitor
must review the highlighted form and press its submit button. Browsers without
WebMCP continue to receive the ordinary website with no loss of functionality.

== Installation ==

1. Make sure the parent theme, **Twenty Twenty-Five**, is installed (it ships
   with WordPress 6.7 and later, or install it via Appearance → Themes → Add New).
2. Upload this theme as a zip file via Appearance → Themes → Add New → Upload
   Theme, or place the `remotive` folder directly into `wp-content/themes/`.
3. Activate **Remotive Media** under Appearance → Themes.

The first time the theme is activated, it automatically sets the Remotive
logo as the site's Custom Logo and Site Icon (favicon) — but only if nothing
has already been set, so it will never override a choice made later in the
dashboard.

== Frequently Asked Questions ==

= Can I install this on a different website? =

It isn't designed for that. Colours, copy, and the registered business
address are all specific to Remotive Media Asia. Treat it as this one site's
theme, not a starting template — see `docs/ssot.md` for what's site-specific.

= Why is there a light/dark toggle? =

Two brand directions existed for this site: a bold CMYK "Registration"
concept, and Remotive Media Asia's own official brand system (Saira, its six
brand colours). Rather than pick one, both are available, with dark set as
the default since that's the primary design direction.

= I edited some text and now it looks broken / a hover effect stopped working. =

A few small pieces of the homepage (the main headline, the marquee strip
under it, and the light/dark toggle button) are built with raw code rather
than the usual text blocks, because they need small technical behaviours a
normal text block can't do. See `readme.md` for exactly which parts these
are and what to be careful of when editing them.

= Where do I update the contact email, address, or social links? =

Appearance → Theme Options. These used to be hardcoded in the theme
files; they're now editable from the dashboard without touching any code.

= How do I make the blog show up at a real URL? =

Go to Settings → Reading and set a "Posts page." The blog archive
template is already built and ready — this one setting is the only step
needed to make it live.

= How do I use the About page template? =

Create or edit a Page, then choose "About (gallery + contact form)" from
the Template dropdown in the editor's sidebar (under Page → Template).
The gallery currently shows placeholder colour tiles rather than real
photos — see the note directly under the gallery on that page, or
`docs/upgrading.md`, for swapping in real images later.

= What about Services, Case Studies, Contact, and a Privacy Policy? =

Same mechanism — create or edit a Page, then pick the matching template
from the dropdown: "Services (detailed)," "Case Studies," "Legal
(Privacy Policy, Terms, etc.)," or "Contact." The Legal template
deliberately doesn't include any actual policy text — write your real
Privacy Policy or Terms content in the block editor after selecting it;
the template just provides the on-brand chrome and readable typography.
The Case Studies page's four cards are placeholder content, same as the
About page's gallery — see `docs/upgrading.md`.

= How do I add, edit or remove a team member? =

Appearance → Theme Options → Homepage → Team. Each member is a row: name, role, a
photo slug and a bio. "Add member" creates a blank row, "Remove" deletes
one, and clearing a name also deletes it on save. The roster feeds three
places at once — the homepage team strip, the Team page grid, and the
site's structured data — so you only edit in one place.

Tick "Leadership" on a member to show them on the homepage and list them
first on the Team page. The homepage introduces the leadership only and links
to the Team page for everyone else; the Team page always shows the whole
roster. With fewer than four ticked, the homepage row ends in a "+N more" tile
linking to the Team page. If nobody is ticked the homepage shows the first four
members.

The photo slug names the portrait files. A member with the slug `gordan`
uses `assets/team/gordan.avif`. One image per person, and it does not change
on hover. Leave the slug empty and that member shows the placeholder tile
instead. There is no upload field for these: the portraits are cut out and
cropped to a shared framing before they ship with the theme, so send new
photography to whoever maintains the theme. To replace a photo, run
`tools/normalise-portraits.py --replace <slug> <photo>`, which builds the new
portrait to the same framing and deletes the old one, then ship the theme;
the site picks up the change on its own.

= A theme update improved the shipped articles or case studies. How do I
get the new version? =

Appearance → Theme Options → the Shipped content card. Every shipped item
is listed with its status and a "Restore shipped version" button, which
replaces that item's title, content, excerpt and SEO fields with the
version in the current release, and recreates it if it was deleted. Setup
itself never overwrites anything, so this is the only path that does, and
any edits you made to that item are lost when you use it.

= Are images served as AVIF? =

Yes, where the browser supports it. Every bundled image ships in both
formats and the original is always kept as the fallback: rendered images
are wrapped in a picture element with an AVIF source ahead of the original
img tag, and CSS backgrounds use a feature query. Images you upload
yourself are wrapped the same way as soon as an AVIF file with the same
name sits beside them in the uploads folder.

= Can the login screen use the site's branding? =

Yes. Appearance > Theme Options > Site behaviour > Branded login screen. It
is off by default. When on, wp-login.php uses the site logo, colours
and typeface, and you can set the line of text shown above the form.

This changes appearance only. It does not alter how anyone signs in,
and it adds no login security: rate limiting, custom login URLs and
similar protections belong in a must-use plugin, which keeps working
even if the theme is switched or breaks. If such a plugin is already
branding the login screen, it keeps control and this setting does
nothing.

= Where do I report a bug or request a change? =

Contact MENJ (https://menj.blog), the theme's developer.

= Does the theme's schema markup conflict with SEO plugins? =

No. The theme checks which schema types your active SEO plugin outputs
(Yoast, Rank Math, All in One SEO, SEOPress, The SEO Framework and Slim
SEO are recognised) and withholds its own node for exactly those types.
Types the plugin does not manage — the Service entries and the landing
pages' FAQ schema — remain the theme's own. No settings needed; it
adjusts automatically when a plugin is activated or deactivated. With
Rank Math specifically, the theme also checks whether its Schema module
is actually switched on (yielding nothing if it's off), and yields
per-page to any schema type you attach through Rank Math's Schema
Generator — remove it and the theme's node returns.

= What is WebMCP, and does the site require it? =

WebMCP is an experimental browser API that lets a compatible AI agent discover
structured actions supplied by the current webpage. It is optional progressive
enhancement: the theme has no polyfill or external WebMCP dependency, and all
search, navigation and forms still work normally without it. The public search
tool returns published posts and pages only. Lead tools fill visible fields but
never submit, bypass confirmation, or alter nonce and anti-spam fields.

= Does the theme fix every PageSpeed caching warning? =

The theme optimizes the requests it controls, but cache lifetime headers are
sent by the web server, host, or CDN. Configure long-lived browser caching for
versioned theme assets and an appropriate media-library policy in that layer;
do not add those rules to the theme because they would not follow the site when
the hosting stack changes. See `readme.md` and `docs/upgrading.md` for the checklist.

= How do I change the scrolling list of countries under the homepage headline? =

Appearance -> Theme Options -> Country ticker. The Countries field takes a
comma-separated list; add, remove, rename or reorder entries and save. The
strip rebuilds itself, including the repetition the seamless scroll needs, so
you do not need to duplicate anything manually. At least two entries are
required for the loop; fewer hides the band.

The same tab controls the band's background, text and separator colours, font
size and weight, letter spacing, padding, scroll speed and direction, and a
toggle to hide the band entirely while keeping its settings.

Scroll speed stays visually consistent as you add or remove countries: the
animation duration scales with the number of entries rather than being fixed.

= Sections fade and lift as I scroll. Can I turn that off? =

Appearance -> Theme Options -> Site behaviour -> Scroll motion.

Anyone whose device is set to reduce motion never sees the effects regardless
of this setting, and the block editor is unaffected either way.

The effects animate only opacity and position, so nothing moves other content
on the page and the layout is identical with the setting on or off.

= What happens if the site hits a PHP error? =

Visitors see a branded page saying something went wrong, with a short reference
code and a link back to the homepage. They are never shown file paths, function
names or stack traces.

Signed-in administrators see the full detail on the same page: the message, the
file, the line number and a stack trace where one is available. The reference
code shown to the visitor is also written to the PHP error log next to the full
detail, so if someone emails you quoting it you can find the exact entry.

Smaller PHP notices and warnings, the kind that do not stop the page, are
recorded to the error log and kept off the page entirely. Administrators get a
summary panel; visitors see nothing.

This is more than tidiness. A notice printed into a page counts as output, and
once any output has been sent no further HTTP headers can be set for that
request, which breaks redirects, cookies and cache headers for the rest of the
page load. Keeping notices off the page prevents that.

Turn it off at Appearance -> Theme Options -> Site behaviour -> Graceful error
handling.

Two limits worth knowing. Errors that happen before the theme loads, during
WordPress core or plugin startup, are outside any theme's control; for those
set WP_DEBUG_DISPLAY to false and display_errors off in wp-config.php, which
you want on a live site anyway. And because the theme takes over the error
page, WordPress recovery mode emails are disabled.

If an error ever prevents you signing in, add this to wp-config.php to see the
detail without an admin session, then remove it once fixed:

    define( 'REMOTIVE_SHOW_ERRORS', true );

= How do the Malay and Chinese versions work, and how do I manage the translations? =

The site has Bahasa Melayu (`/ms/`), Simplified Chinese (`/zh-hans/`) and
Traditional Chinese (`/zh-hant/`) beside English. There are no copies of your
pages: `/ms/team/` is the same page as `/team/`, shown in Malay, so English
content and URLs are never touched. The language links are in the footer's
bottom bar. No plugin is involved.

Translations are managed in **Tools > Translations** (administrators only):

* **Overview** lists every page with, per language, how many of its strings are
  translated and whether the page is live. **Scan the site** finds every
  translatable string on every page; run it after changing English content.
* **Edit** a page in a language: translate each string beside its English, set
  the page's search title and description, and tick **Live** to publish it.
  Unticking Live sends that address back to the English page. Leave a box empty
  to fall back to the shipped translation.
* **Save and make live** publishes straight away; **Save as draft** stores it
  without publishing. **Maintenance > Approve all** turns drafts live.
* **All strings** searches and filters every string by language and status.
* **Import / export** moves a language to a translator and back as CSV (opens
  correctly in Excel, Chinese included) or JSON. Imports arrive as drafts, and
  only text the site really contains is accepted.

Edits are saved in the database and laid over the translations shipped in
`inc/i18n/`, so a theme update never overwrites them. If you change English
wording that has been translated, that sentence shows in English until you
rescan and translate the new wording; nothing breaks, and it appears under
**Untranslated**. Names, addresses and emails are never translated.

On a translated page the search title and description come from this screen,
not from Rank Math, which would only know the English ones. The extra sitemap
is `/sitemap-languages.xml`; it is listed in robots.txt and in Rank Math's
sitemap index. Submit it once in Google Search Console.

= Does the theme add security hardening? =

Yes, within what a theme can reach. It rate-limits repeated failed logins per
IP address, disables XML-RPC, redirects the WordPress install and setup URLs
once the site is installed, removes version numbers used to fingerprint the
site, blocks username enumeration through the REST API and author archive URLs,
and sends a set of standard security headers.

What it cannot do is block an IP address, rate-limit before PHP runs, or
protect wp-login.php at the server level. Those need your host's firewall or
rules in .htaccess. A dedicated security plugin is still worthwhile; this is a
floor, not a replacement.

== Changelog ==

See `docs/changelog.md` for full version history. Latest version: 1.108.1.

== Upgrade Notice ==

= 1.108.1 =
On phones and tablets the form on the ad landing pages now comes first, above the headline, so it is on the first screen. Desktop is unchanged.

= 1.108.0 =
Every translated page is now live in Malay and both Chinese versions by default: the eight unlisted case studies and the seven articles joined the main pages, 37 pages per language. Switch any off in Tools → Translations.

= 1.107.3 =
Fills gaps in the Malay and Chinese dictionaries: the archive and empty-state messages, the form thank-you messages and the case study chart labels were still English.

= 1.107.2 =
Fixes missing last-modified dates in the language sitemap for translated articles, and a saved page being unpublished when its language was switched off.

= 1.107.1 =
The Malay text now also follows the 2010 edition of the Pedoman Umum Ejaan
(Brunei): spaced en dash, curly quotation marks and a comma before the last item
of a list. Where the two editions differ, the newer one is used.

= 1.107.0 =
The Malay text now follows the Dewan Bahasa dan Pustaka's Pedoman Umum Ejaan
Bahasa Melayu: punctuation, number and currency forms, headings without a final
full stop, and dates written the Malay way. Dates on the Malay and Chinese
pages (the Insights list) are no longer in English.

= 1.106.1 =
Fixes the header language switcher on phones: it was pushed off the right edge.
It now sits beside the colour toggle on a row under the logo and menu button.

= 1.106.0 =
A Languages tab in Theme Options switches Bahasa Melayu, Simplified Chinese and
Traditional Chinese on or off for the whole site, and the header and footer
language switchers on or off. The language sitemap now lists English and every
live language on each entry (a complete hreflang set), is added to Rank Math's
sitemap index with a last-modified date, and Rank Math's sitemap cache is
cleared when languages change. Fixes a doubled page title when Rank Math is
active.

= 1.105.0 =
The main pages (home, services and the six service pages, case studies and the
six listed ones, about, team, insights, contact, FAQ, privacy, terms) are now
live in English, Bahasa Melayu, Simplified and Traditional Chinese, and a
language switcher (EN, BM, 简体, 繁體) sits in the header beside the colour-mode
toggle. Other translated pages stay off until switched on under Tools >
Translations.

= 1.104.0 =
Adds a language layer (Bahasa Melayu, Simplified and Traditional Chinese at
/ms/, /zh-hans/ and /zh-hant/) with a switcher and hreflang. It is off until a
page is switched on under Tools > Translations; no ordinary page is live in
another language to begin with. The ad landing pages move
to the same addresses (/zh-hans/, /zh-hant/); the old /zh-cn/ and /zh-tw/
addresses redirect. See docs/upgrading.md.

= 1.103.2 =
The theme thumbnail on the Themes screen now shows the current design.

= 1.103.1 =
The contact address is hello@remotivemedia.asia. A site that had saved the
older .com address is corrected once, on the next admin page load.

= 1.103.0 =
Security release. Closes a way to list usernames, stops the public site search
leaking password-protected posts and unlisted pages, makes the login and form
rate limits work behind Cloudflare, and caps the size of enquiries. See
docs/upgrading.md for the two checks to make after updating.

= 1.102.0 =
Checked against Twenty Twenty-Five 1.5. Anything the parent theme draws (post
dates, comment authors, code blocks, its patterns) now follows this theme's
dark and light colours; before, some of it used the parent's light colours on
the dark page.

= 1.101.0 =
Works properly with Rank Math SEO 1.0.279. With that plugin active, the
confirmation page was left indexable, the Malay and Chinese landing pages
pointed their canonical at the English page, and the landing pages appeared in
its sitemap. All three are fixed; nothing changes without the plugin.

= 1.100.0 =
Internal change only: the PHP modules in inc/ now live in subfolders (core,
options, setup, forms, landing, content). No settings or behaviour change. If
you deploy by copying files over the old theme, delete the old inc/*.php files
afterwards. See docs/upgrading.md.

= 1.99.0 =
The block editor now shows the colours chosen under Theme Options -> Colours,
and visitors without JavaScript get the site's default light or dark mode.
Adds automated tests for the colour and landing-page logic.

= 1.98.0 =
The Case Studies page now shows six case studies in one grid, with no
categories, and the footer lists the same six. The other case-study pages stay
live but are no longer listed. The footer menu refreshes itself once on the
next admin page load. See docs/upgrading.md.

= 1.97.0 =
Adds a Colours tab (the palette per light and dark mode, with a live contrast
check), a rounded-corner design, and tidies the theme folder: documents other
than this readme and readme.md now live in docs/. See docs/upgrading.md for the
deploy checklist.

= 1.94.0 =
Adds ad landing pages in four languages (English, Bahasa Melayu, Simplified
and Traditional Chinese) with their own confirmation page, a maintenance-mode
switch, a rounded-corner design and a fix for white-on-bright text on the
dark-mode call-to-action bands. Lead forms now fetch a fresh security token
when the page loads, so a cached page can no longer reject a real enquiry.
Pretty permalinks must be on. See docs/upgrading.md for the deploy checklist.

= 1.79.1 =
Fixes the country ticker in dark mode, where it rendered as a navy band on
the navy page and appeared to be missing. Also reorganises Theme Options:
the Hero, Section headings, Numbers and Team tabs are now four groups
inside one Homepage tab, since they all edit the same page. Display is
renamed Site behaviour and Contact & Socials is renamed Business details,
to match what they actually contain. No settings are lost and nothing
needs re-entering.


= 1.74.0 =
Adds a short introductory sentence with inline jump links to the top of six
pages, which helps Google offer jump-to-section links for your results. It is
one sentence of prose, not a table of contents, and appears only at the top of
a page so the body text stays clean. Also corrects two heading hierarchy
faults: the Contact page skipped from h1 to h3, and the homepage proof rows
used two headings of equal rank per row.

= 1.73.1 =
Much stronger parallax on the homepage: the hero mark travels further and
scales, the headline lifts against the page as you scroll, and cards arrive
from further away. Interior pages keep the subtle version. Switch it all off
at Theme Options -> Site behaviour -> Scroll motion; reduced-motion settings are
still respected.

= 1.73.0 =
Section headings on About, FAQ, Services, Case Studies, Team and the two
market pages now have anchor IDs, so you can link straight to a section, for
example /faq/#how-we-report-what-numbers-mean. Headings that are not
destinations, such as card figures and calls to action, are left alone, and
the homepage is unchanged because its sections already have anchors.

= 1.72.4 =
Removes a duplicate "All case studies" link from the homepage results
section; the proof section directly above already carries it.

= 1.72.3 =
IMPORTANT: 1.72.0 corrected the homepage results figures in the theme, but a
value already saved in Theme Options overrides a theme default, so the three
unsupported figures were still showing and now carried links to case studies
that do not contain them. This release resets those three figures on the next
admin page load. Only those exact three are touched; any other value you have
set is left alone. Also fixes the results section heading wrapping oddly.

= 1.72.2 =
Team page portraits no longer change on hover: each member shows one image.
The hover swap remains on the homepage team strip.

= 1.72.1 =
Three Insights articles shipped without a featured image and showed blank
cards. The images are added, and existing sites have them attached
automatically on the next admin page load. An article where you chose your
own image is left alone.

= 1.72.0 =
IMPORTANT: the homepage results band previously showed an average ROAS, a
year-one organic figure and a client retention percentage. None of the three
can be supported by the case studies this theme ships, and the organic figure
is contradicted by the only year-on-year measurement in them. They are
replaced with one documented figure per service block, each linking to the
case study that sets out its measurement limits.

If you had saved your own values in Theme Options, they are still stored and
will still display. Review them at Appearance -> Theme Options -> Homepage,
and make sure each one can be evidenced.

= 1.71.1 =
The reply-time promise changes from one business day to three, everywhere it
appears. If your homepage still shows the old wording after upgrading, the
reassurance line has been saved in Theme Options -> Homepage -> Hero and needs
updating there; a saved value always overrides the theme default.

= 1.71.0 =
Forms now confirm on a real /thank-you/ page instead of returning to the form
with a message. This gives you a conversion URL to fire analytics and ad
platform events on, which the previous approach could not do reliably. The
page is created automatically on the next admin page load. A remotive_lead
event is pushed to dataLayer for tag managers; no tracking script is added.

= 1.70.6 =
The company registration number is no longer shown in the footer by default.
The field is still available under Theme Options -> Contact if you want it.

= 1.70.5 =
The footer copyright year is no longer hardcoded, and the line now carries
the registered company name and UEN as Singapore's Companies Act section 144
expects. Privacy Policy and Terms of Service links added to the bottom bar.
Both the name and the UEN are editable under Theme Options -> Contact;
clearing the UEN removes it.

= 1.70.4 =
The featured case study on the homepage is now clickable across the whole
card, not just the small link at the bottom. The gradient panel responds on
hover, and keyboard users get the same state.

= 1.70.3 =
The homepage proof rows now open the specific case study they quote instead
of the case studies index, and the featured case at the top of that section
gains a link it never had.

= 1.70.2 =
Fixes ten links in the homepage "What we do" section that were styled as
plain text and gave no sign they were clickable. Also corrects the top rule
and heading of each card using two different colours, trims the links to one
per service page, and adds a block icon.

= 1.70.1 =
Trims the homepage grid links from 16 to 7, one per destination. Problem
points link to the page or article that answers them; the Why points are
reassurance rather than questions, so only one links. Two now point at
Insights articles instead of service pages.

= 1.70.0 =
The two homepage grids ("The problem we solve" and "Why Re:Motive") now
have icons, larger type, rotating brand accent colours, and each point
links to the page that answers it. Previously every point was a dead end.
Icon motion follows the Scroll motion setting under Theme Options.

= 1.69.5 =
Section and card links ("All services", "Meet the team", "View case study")
now read clearly as links: Space Grotesk, the brand accent colour, an
underline that fills on hover, and an arrow that slides. Previously they
looked like plain bold text.

= 1.69.4 =
Removes an internal production note that was visible to visitors on the Case
Studies page.

= 1.69.3 =
Documentation only, no code changes. Readme, developer notes and the single
source of truth brought current with everything since 1.66.6. Translation
template regenerated: 265 strings, up from 35.

= 1.69.2 =
PHP notices and warnings no longer print into the page. This also stops the
"Cannot modify header information" cascade a single notice can trigger, which
breaks redirects and cookies. Administrators see a summary panel; visitors see
nothing. Toggle at Theme Options -> Site behaviour.

= 1.69.1 =
Subtle scroll motion: headings, cards and stats lift into view, the hero mark
drifts behind the content. CSS scroll-driven animations where supported, an
IntersectionObserver fallback otherwise; no scroll listener in either path and
no layout impact. Off switch at Theme Options -> Site behaviour. Reduced-motion
settings are always respected.

= 1.69.0 =
Branded error page replaces the generic WordPress critical error screen.
Administrators see the actual fault; visitors see an apology and a reference
code that also appears in the error log.

= 1.68.5 =
Fixes a fatal error on every admin page load caused by an undefined function
call in the content seeder, introduced in 1.66.6. Upgrade if you are on any
1.66.6 to 1.68.4 release.

= 1.68.1 =
Security hardening: login rate limiting, XML-RPC disabled, install endpoints
closed, version fingerprinting removed, username enumeration blocked, security
headers added.

= 1.68.0 =
The Case Studies page moves from /work/ to /case-studies/ automatically on the
next admin page load. Child case-study pages move with it.

= 1.67.0 =
Call-to-action bands added to the Insights index, individual posts and archive
pages, which previously ended with no prompt at all.

= 1.66.8 =
The homepage country ticker is now fully editable at Theme Options -> Country
ticker: countries, colours, size, weight, spacing, speed and direction.

= 1.66.6 =
Security: Editor and Author roles no longer have access to enquiry data.
Upgrade immediately. Also fixes: privacy draft accuracy, CSV injection,
Theme Options description fields, Light/System colour mode default, social
icon visibility, hardcoded contact info, team page placeholder text, schema
$org_ref warning flooding error logs, and Accept-header logo caching risk.

= 1.66.5 =
Reverted 1.66.4's site-wide Space Grotesk swap. Saira is the site
typeface again, matching the client's brand-guideline JSON. Space
Grotesk kept only as a scoped accent on the footer column headings.

= 1.66.4 =
Brand typeface changed from Saira to Space Grotesk (bold, geometric,
distinctive) across both light and dark modes. Footer column heading
size increased. This deviates from the client's own brand-guideline
JSON, which still specifies Saira — noted in docs/ssot.md, not silently
changed there. Metric-matched fallback recalculated from real font
data, not estimated, so no layout shift on load.

= 1.66.3 =
Fixed a footer bug: the Case Studies and Company columns both showed
the Services list once a classic menu was assigned to any footer
location, because a render-order counter only recognised two columns
against three. Now matched by explicit class, so it can't drift again.
Also fixed broken Privacy/Terms links pointing at /privacy-policy/ and
/terms-of-service/ instead of the pages' real slugs.

= 1.66.2 =
All 23 seeded blog posts, service pages and case studies rewritten to
pass Rank Math's on-page checklist: real focus keywords (not generic
labels), keyword in title/description/content/subheading, 600+ words,
and internal plus external links throughout. Core pages and location
landing pages are not covered; see changelog for why.

= 1.66.1 =
Theme Options gains Facebook, X, YouTube and Threads URL fields,
joining Instagram, LinkedIn and TikTok. All seven now render in the
footer and Contact page, and feed the Organization schema's sameAs
array. Existing Instagram/LinkedIn/TikTok URLs are untouched.

= 1.66.0 =
SEO meta audit against Rank Math limits (50-char titles, 130-char
descriptions). Fixed 7 oversized case-study descriptions. Bigger find:
the 9 auto-created core pages (Home, Services, Case Studies, About,
Team, Contact, Insights, FAQ, Privacy, Terms) never had any Rank Math
title, description or focus keyword wired at all. Site setup now
fills these in on activation and on existing installs via the
Theme Options re-check button, without touching anything already
edited by hand.

= 1.65.9 =
Site-wide copy audit against the house English voice. Four fixes: a
banned negative-parallelism CTA line, a "not just" tail construction,
"showcase" and "aligned" swapped for plain words. No visual changes.

= 1.65.8 =
About page gains a new "Built to run light" section, placed after Why
Asia and before What we stand for. Covers the distributed-team and
digital-only-deliverable model as the site's culture/sustainability
angle, in the site's existing plain-claim register rather than
generic sustainability language.

= 1.65.7 =
About page's "Three things we will not trade away" section now pairs
its "Meet the team" link with the heading, top-right, instead of
stranding it at the bottom-left below all three columns. Matches the
Problem, Why, Services, Work and Team section pattern. No content
changes.

= 1.65.6 =
Homepage's Problem and Why Re:Motive sections now carry a right-aligned
link, matching the Services, Work and Team sections above and below
them. Problem links to Services; Why links to About. No content changes.

= 1.65.5 =
Sidebar's "Get in touch" card gains a one-line reply-time note above
the email address, matching the commitment already made on the
contact page. No structural changes.

= 1.65.4 =
Studio gallery on the Team page now holds a fixed four tiles per row
at every width instead of packing five and stranding the sixth on its
own. No content or markup changes; CSS only.

= 1.65.3 =
Documentation only: readme.md, docs/upgrading.md and docs/resources.md are
brought up to date with the modules, templates and deployment steps
added since v1.29.0.

= 1.65.2 =
Spam enquiries now sit in their own spam folder with its own view and
count, instead of being hidden inside the main list.

= 1.65.1 =
The legal drafts now address site visitors directly and cover both
Singapore and Malaysian law. Still drafts: fill in the CONFIRM
markers and have them reviewed before publishing.

= 1.65.0 =
The footer is regrouped into Services, Case studies, Company and
Contact columns, giving every page a site-wide link. If your footer
navigation was saved in the Site Editor, update it there too: a saved
menu lives in the database and ignores template changes.

= 1.64.0 =
Contact form submissions are now checked for spam by Akismet when
that plugin is installed and connected. Suspected spam is filed
rather than deleted and can be recovered. The forms work normally
whether Akismet is present or not.

= 1.63.0 =
Contact form submissions are now stored in the database as well as
emailed, so a mail failure no longer loses an enquiry. Set how long
to keep them in Appearance > Theme Options > Site behaviour. Contact Form 7
submissions are captured into the same place when that plugin is
active.

= 1.62.0 =
Adds legal page drafts in docs/, links both pages from the footer,
and puts a privacy note under the contact form. The drafts contain
CONFIRM markers that must be filled in, and need legal review before
you publish them.

= 1.61.0 =
Adds an FAQ page template with eleven questions and matching FAQPage
structured data. Run Appearance > Theme Options > Create missing
pages to add it.

= 1.60.0 =
Every section heading now has a description beneath it, and the
homepage descriptions are editable in Appearance > Theme Options >
Section headings.

= 1.59.0 =
The Work page becomes Case Studies at /case-studies/, and the
thirteen case studies move with it. On a live site, rename the page
in the editor rather than recreating it, so WordPress redirects the
old addresses.

= 1.58.1 =
The Team and About pages now use the same content width and left
edge as the rest of the site.

= 1.58.0 =
The Team, About and Insights pages now use the same header treatment
as the rest of the site, and the gallery lightbox follows the gallery
to the Team page.

= 1.57.4 =
Replaces Jazlan's hover portrait with one framed to match his
resting portrait. No action needed.

= 1.57.3 =
Restores Gordan's hover portrait. No action needed.

= 1.57.2 =
Removes the faint outline around team portraits. Gordan's tile shows
a still portrait until a fuller photograph is available.

= 1.57.1 =
Replaces Jazlan's hover portrait and centres turned poses within
their tiles. No action needed.

= 1.57.0 =
Team portraits are re-cropped so no shoulder is cut off by the tile
edge and every face reads at the same size. No action needed.

= 1.56.1 =
Adds Jazlan's team portraits. No action needed.

= 1.56.0 =
The About page splits into two: Meet the Team at /team keeps the
roster and gallery, and /about becomes the company page with the
contact form. Existing sites: run Appearance > Theme Options >
Create missing pages to add the Team page, then set the About page's
template to "About (company story + contact form)".

= 1.55.0 =
Team portraits are re-cropped so every tile shares the same head
size and position. No action needed.

= 1.54.2 =
Adds Alif's team portraits. No action needed.

= 1.54.1 =
Adds Nabil's team portraits. No action needed.

= 1.54.0 =
Combines three front-end scripts into one request. For the cache
lifetime warnings, copy docs/htaccess-cache.txt into your .htaccess:
the server is not caching JavaScript at all, which a theme cannot
fix from PHP.

= 1.53.6 =
Fixes the gap that appeared between case study rows when one is
hovered. No action needed.

= 1.53.5 =
Tightens the vertical space between page sections, which was
doubling up at every join. No action needed.

= 1.53.4 =
Tightens the space above and below the homepage stats band. No
action needed.

= 1.53.3 =
Tightens the gap in the middle of the call-to-action band on wide
screens. No action needed.

= 1.53.2 =
The branded login screen now styles every part of the login form,
including the password reveal button, Remember Me row and submit
button.

= 1.53.1 =
The branded login screen styles the standard WordPress login form
only; Sign in with Google styling is removed.

= 1.53.0 =
Adds an optional branded login screen, switched off by default. Turn
it on in Appearance > Theme Options > Site behaviour. It changes appearance
only and does not affect how anyone signs in.

= 1.52.6 =
Removes the forced reflow caused by the mobile navigation fallback.
No action needed.

= 1.52.5 =
Fixes the mobile menu opening as a narrow strip over the page. No
action needed.

= 1.52.4 =
Fixes the mobile menu opening as scattered links instead of a solid
panel. No action needed.

= 1.52.3 =
Removes the remaining empty box at the end of the navigation. No
action needed.

= 1.52.2 =
The navigation hover underline is slightly thicker. No action
needed.

= 1.52.1 =
Removes the empty boxes either side of the desktop navigation. No
action needed.

= 1.52.0 =
The Start a project button is back on mobile, the colour-mode toggle
draws correctly again, and the empty box beside the menu button is
gone.

= 1.51.0 =
Important for mobile: if the navigation overlay was not appearing on
phones, the header now provides its own hamburger toggle instead of
leaving the menu hidden. Update if any visitor has reported a
missing mobile menu.

= 1.50.0 =
WebMCP tools now carry the display titles and the read-only and
untrusted-content hints the specification defines, and registration
is guarded for secure context and duplicate names.

= 1.49.0 =
The Shipped content list becomes a readable card stack on narrow
screens, and a toggle switch control is registered for future
boolean settings. No settings change.

= 1.48.0 =
Documentation refresh: the asset licence inventory now covers every
bundled file including team portraits, the description and FAQ match
the theme's current features, and several Theme Options tabs explain
their effect more clearly. No code behaviour changes.

= 1.47.0 =
WCAG 2.2 AA remediation: contrast, heading order, link names, target
sizes, zoom reflow, reduced motion. Some accent colours shift to
their darker ramp where the lighter value could not meet contrast.
See docs/accessibility.md.

= 1.46.0 =
Mobile-first hardening: form overflow fixed, wide tables and code
blocks scroll safely, long URLs wrap, touch targets and small text
meet minimums, and reduced motion is honoured. Design, markup and
desktop layout are unchanged.

= 1.45.1 =
Fixes a broken mobile header caused by a missing viewport meta tag,
and adds a fallback so the phone header stays legible if the
navigation overlay fails to load.

= 1.45.0 =
Images are served as AVIF where the browser supports it, with every
original PNG kept as the fallback. Existing sites gain the seed
image companions when those items are restored or reseeded.

= 1.44.6 =
The header logo now serves theme-shipped 56/112/168px renders in
AVIF or PNG instead of a 300px upload, saving around 17KB on the
first view. No action needed.

= 1.44.5 =
The hero mark is served as AVIF where supported, 29KB smaller. See
docs/cache-headers.md for the server configuration that fixes the
cache lifetime audit.

= 1.44.4 =
Shortens the critical request chain: all above-the-fold font weights
are preloaded, an unused face is removed, and the Google Accounts
preconnect now fires for plugin-injected sign-in scripts.

= 1.44.3 =
Fixes the layout shift PageSpeed reported against the Saira web
fonts by adding a metric-matched fallback. Re-run PageSpeed after
deploying; CLS should drop from 0.259 toward zero.

= 1.44.2 =
Team portrait styling is generated from the roster, so adding a
member with photography needs no stylesheet edit. No action needed.

= 1.44.1 =
Fixes the supplied print/PDF pagination defects and addresses the reported
PageSpeed findings with font swapping, early hero-image discovery, a correctly
sized logo, non-blocking component CSS, and deferred or conditional scripts.
No migration is required. Browser cache lifetime still belongs in the hosting
or CDN configuration; the exact follow-up is documented in `docs/upgrading.md`.

= 1.44.0 =
Adds progressive WebMCP tools for published-content search, canonical
navigation, same-site opening, and human-reviewed preparation of visible
contact and audit forms. No configuration or migration is required; browsers
without WebMCP continue to work normally.

= 1.43.5 =
Elfie's portraits are re-cut to fill the tile at the same scale as
Gordan's. No action needed.

= 1.43.4 =
Team portraits are straightened so resting angles match across the
grid. No action needed.

= 1.43.3 =
Gordan's hover portrait pair ships, joining Elfie's. No action
needed.

= 1.43.2 =
Team Person schema is enriched with anchors, worksFor, bios as
descriptions and portrait images where they exist. No action needed.

= 1.43.1 =
Team members are now managed from a Theme Options tab: add, edit and
remove rows, with the grids and structured data updating together.
Defaults match the current six members, so nothing changes until
you edit.

= 1.43.0 =
The six team members are added to the Organization schema with the
same names and titles as the site displays. No action needed.

= 1.42.10 =
Gordan's role is retitled Managing Partner. No action needed.

= 1.42.9 =
Jazlan's role is retitled Performance Director. No action needed.

= 1.42.8 =
Nabil's role is retitled Data Analyst. No action needed.

= 1.42.7 =
Three team roles are retitled as specialist titles. No action
needed.

= 1.42.6 =
Team member names update to full names in both team grids. No
action needed.

= 1.42.5 =
The hover portrait's crop now matches the base framing, so the swap
reads as a pose change rather than a zoom. No action needed.

= 1.42.4 =
The homepage team section joins the About page at four portraits per
row. No action needed.

= 1.42.3 =
The team portrait hover is guarded for touch devices and documented
against the interaction spec: 250ms, opacity-only, hover only where
hover exists. No action needed.

= 1.42.2 =
Team portraits are re-encoded as AVIF at roughly half the file size.
No action needed.

= 1.42.1 =
The team hover portraits become cutouts on the tile's own gradient,
so the background stays still and follows the colour mode while the
pose swaps. No action needed.

= 1.42.0 =
Team tiles gain a before/after hover portrait effect, shipping with
Elfie's photography. No action needed; further members are two image
files each.

= 1.41.7 =
Corrects the contact email to hello@remotivemedia.asia in the
sidebar, Contact page and Theme Options default. The archive layout
breakage is already cured by 1.41.4; update to pick both up.

= 1.41.6 =
The About team grid now matches the homepage teaser: four members
per row. No action needed.

= 1.41.5 =
Single posts now use the full-width article column, and the style
audit extends to all Insights, service and landing page copy. Use
Restore in Theme Options to pull the cleaned article copy onto a
running site.

= 1.41.4 =
Fixes the Insights page layout: post cards fill the main column in a
proper grid, the stray hairlines are gone, and the sidebar's recent
posts are spaced normally. No action needed.

= 1.41.3 =
Drops the legacy slug redirects and fields; the keyword-led case
study slugs are the only slugs. Delete any pages under old slugs and
use Restore or setup to recreate them cleanly.

= 1.41.2 =
Dark mode now uses the same Saira brand typeface as light mode; the
toggle changes colour only. No action needed.

= 1.41.1 =
Larger nav text, a single hover underline, and a theme toggle that
reads as a labelled switch at every width. No action needed.

= 1.41.0 =
Case study slugs are renamed to keyword-led forms with 301 redirects
from every old address. Running sites keep their pages; use Restore
in Theme Options to move a page onto its new slug.

= 1.40.1 =
Removes the Singapore B2B portfolio case study from the Work page
and the shipped content set. On a running site, delete the page
itself from Pages; the seeder never brings it back.

= 1.40.0 =
The homepage problem and why sections each carry eight points laid
four per row. No action needed.

= 1.39.0 =
Theme Options gains a per-item Restore shipped version button for
the 24 shipped content items, so a running site can pull in improved
case study copy from a theme update. Restore is explicit and
confirmed; nothing changes without the click.

= 1.38.1 =
All case studies are presented as Re:Motive engagements with the
prior-roles framing removed, and the full case text is audited
against the house writing rules. Applies to newly seeded sites.

= 1.38.0 =
Case studies now present their figures as tables and bar charts with
the method in paragraphs. Applies to newly seeded sites; existing
case pages are never rewritten.

= 1.37.1 =
Removes the duplicate "Menus" item under Appearance. The screen
itself is unchanged. No action needed.

= 1.37.0 =
The homepage problem section grows to six friction points laid three
across, filling the bare band below the old four-up row. No action
needed.

= 1.36.2 =
Alignment fixes across the site: hero eyebrows, section notes,
service leads, the About page's sub-heading and form, and legal page
content all now sit on the shared left measure. No action needed.

= 1.36.1 =
Print styles now cover every template, hiding forms, buttons, photo
placeholders, the blog sidebar and other screen-only furniture on
paper. No action needed.

= 1.36.0 =
The Work page is reorganised into category sections and every case
study card now opens its own page. Eight new case pages seed on
setup; sites already running will gain them on the next setup pass.

= 1.35.0 =
Adds a print stylesheet. Pages saved as PDF from the browser now print
ink-on-white with screen chrome, watermarks and placeholder artwork
removed, and cards kept whole across page breaks. No action needed.

= 1.34.0 =
The theme's 16 written items (4 articles, 6 service pages, 6 case
studies) are now published automatically on activation — no import step.
Existing pages are never overwritten, and anything you delete stays
deleted.

= 1.33.0 =
Menus move to Appearance → Menus (classic), with three registered
locations for the header and the two footer columns. No Site Editor
required. Existing header/footer links remain as fallbacks until you
assign menus. No layout or style changes.

= 1.32.1 =
Consistency pass: adds the missing reassurance line to three CTA bands
and a hero lead to service pages. Also adds a "Main menu" card to Theme
Options explaining where block-theme navigation is edited.

= 1.32.0 =
Adds a "Search & organic" section to the Case Studies page with six
further case studies from prior agency engagements, clearly attributed
as such. Detail pages ship separately in the search case studies pack.

= 1.31.2 =
Homepage case strip set to one featured case plus three rows. The full
set of eight remains on the Case Studies page.

= 1.31.1 =
Homepage now shows five case studies (was three) and the Case Studies
page eight (was four), using every case in the source material that can
be told anonymously. No layout or style changes.

= 1.31.0 =
Replaces all placeholder case studies, result rows, stat-band figures
and the fabricated client testimonial with real, anonymised client
results. IMPORTANT: if you have customised the stat band in Theme
Options, your saved values are untouched; only the defaults changed.

= 1.30.1 =
Anonymises the one client name on the Case Studies page. Content packs
updated in parallel: all case studies described rather than named, deck
data unchanged, with a full deck-to-site traceability audit included.

= 1.30.0 =
Service pages restructured to the footer's canonical six (SEO, Paid
Media, Social, Content, Email, Analytics), footer nav now links them,
and the SEO service is renamed "SEO & GEO / AIO / AEO" throughout.
Import the six-page pack; delete any drafts from the eight-page pack.

= 1.29.0 =
Adds the reusable Service detail template, links the Services page and
homepage sub-service labels to the new dedicated service pages
(delivered separately), and emits per-page Service schema on the
template. Layout verified at seven widths in both modes.

= 1.28.0 =
Wires the landing-page FAQ answers to their Insights destinations as
part of the 90-keyword content mapping (content pack delivered
separately). Template text changes only; layout and schema verified.

= 1.27.1 =
Rank Math coexistence verified against plugin source v1.0.277: the theme
now respects the Schema module toggle and yields per-page to schemas
attached via Rank Math's Schema Generator. No markup or style changes.

= 1.27.0 =
Structured data maxed against Google's supported-feature list: adds
breadcrumbs, ProfessionalService local-business typing with ACRA facts,
profile pages, logo image metadata, speakable, article enrichment, and
content-gated video schema. Features without real site content are
documented as excluded rather than fabricated, per Google's spam
policies. Also fixes typed pages escaping SEO-plugin suppression.

= 1.26.0 =
Adds schema.org structured data (Organization, WebSite, WebPage,
BlogPosting, Service, FAQPage) for existing content, identical in light
and dark mode. Coexists with SEO plugins per schema type: the theme
yields any type an active plugin controls and keeps native output for
types the plugin does not manage.

= 1.25.3 =
Dark-mode audit. The CTA band now inverts with the mode instead of
vanishing into the dark page, and the closing bands' fill buttons get a
visible shape in both modes. Verification now covers light and dark with
both brand typefaces; all 120 geometry checks pass.

= 1.25.2 =
Footer polish from live-site review: removes the ~40px gaps between
footer nav links, strengthens the column headings so they read as
headings, and lets the contact address use its full column width. No
markup changes.

= 1.25.1 =
Fixes from rendered verification in headless Chromium: the footer's
fourth column no longer collapses between 782 and 900px, the header CTA
now leaves the row at 980px (it never fit below that), and the nav
squeeze band is corrected and extended to 601-782px. All sixty
overflow/alignment checks across six pages and ten widths now pass.

= 1.25.0 =
Interior-page alignment pass. Services, Case Studies, Contact, the
landing pages and blog lists now sit on the same 1320px measure as their
heroes and the site header, ending the ragged left edges on desktop.
Single posts, legal pages and the About page stay at reading width by
design. No colour, font or copy changes.

= 1.24.4 =
Documentation only. Records the v1.24.2 audit's open items (interior-page
measure alignment decision, real-browser responsive verification) in the
docs/upgrading.md backlog. No code changes.

= 1.24.3 =
Re-anchors the hero sub-heading to the headline's left edge. Its 56ch box
was being centred by the wide-measure auto margins, leaving a large gap
under the headline on desktop. No colour, font or copy changes.

= 1.24.2 =
Layout and consistency audit. Fixes horizontal overflow of the nav row
between 601px and ~720px, removes the duplicate skip link on interior
pages, makes the header's "Start a project" button work from every page,
loads page styles on default-template pages, and removes a duplicate
id="about" from the footer. No colour or font changes.

= 1.24.1 =
Moves the homepage About section's "Meet the team" link from the bottom
of the team grid to the top-right of the section header, styled to match
the "All services" and "All work" links. No colour, font or copy changes.

= 1.24.0 =
Fixes the page gutters: the main content now sits on the same centred
measure as the header and footer, with equal space on both sides, instead
of hugging the left edge. The call-to-action band is rebuilt to match the
rest of the page. No colour, font or copy changes.

= 1.23.0 =
The last three fixed-column grids (post cards, case studies, gallery) now
reflow with the available space like the rest of the theme. Layouts are
unchanged; they simply adapt without fixed breakpoints.

= 1.22.1 =
Documentation only. Corrects a wrong attribution: the 740px content
measure belongs to this theme, not to WordPress. Adds a verified account
of what the theme inherits from Twenty Twenty-Five.

= 1.22.0 =
The homepage grids now reflow continuously with the available space, the
way the parent theme does, instead of snapping at fixed breakpoints. Same
design, fewer arbitrary widths.

= 1.21.0 =
The homepage copy is now editable in Appearance -> Theme Options: hero
headline, sub-heading, button labels, every section heading, and the three
figures. Page order in the header comes from an editable navigation menu.
The panel is renamed Theme Options and carries the landscape logo.

= 1.20.0 =
The site pages are now created automatically when the theme is activated,
so the navigation works straight away with no setup step. It runs once and
never overwrites an existing page. The button in Appearance -> Remotive
Options stays, for re-checking or rebuilding a deleted page.

= 1.19.0 =
Adds a Site pages panel under Appearance -> Theme Options that creates
the Pages the theme's templates and navigation expect, assigns the right
template to each, and sets the Posts page. Navigation and footer links now
point at those real pages instead of homepage anchors.

= 1.18.1 =
Fixes the light/dark toggle. The knob sat slightly high in the track and
stopped short of the right end when switched on. It now sits dead centre
with matching gaps on both sides.

= 1.18.0 =
Adds the six-person team to the homepage and a full roster to the About
page, with names, roles and photo placeholders. Each member has an empty
slot for their own experience line, ready for real copy and photography.

= 1.17.0 =
A full responsive pass across desktop, tablet and mobile. The header now
holds one line at every width, each grid steps at the width its own content
needs, the hero watermark stays off phones, proof-row text is back on
tablet, and the markets strip uses WordPress's own full-width mechanism.
No design, colour or copy changes.

= 1.16.3 =
Removes the visible "Icons by Font Awesome" credit from the footer. The
licence attribution stays where Font Awesome asks for it, in the source
comment beside the icon, so nothing is lost.

= 1.16.2 =
Fixes two desktop layout bugs on the homepage: the hero subhead and
buttons now line up with the headline on wide screens, and the markets
strip runs full width instead of a floating band. No content changes.

= 1.16.1 =
A copy pass on the new homepage and Services sections: plainer wording,
contractions, and a voice that matches the rest of the site. No layout or
structural change.

= 1.16.0 =
Reorganises the Services page into the three-block model (Demand Creation,
Demand Capture, Conversion & Data), matching the homepage. Content only,
with no change to other pages.

= 1.15.1 =
Adds the Insights (blog) link to the header navigation and the footer, so
the blog is reachable alongside the other core pages.

= 1.15.0 =
Completes the template set so every page type has its own template: a
404 page, search results, archives, and a dedicated blog index. Existing
pages and content are unchanged.

= 1.14.0 =
Homepage restructured to the agreed frame: a performance-marketing and
SEO hero, a markets strip, the problem we solve, a three-block modular
capabilities section, the four differentiators, and an about teaser with
room for team photography. Colours follow the official light and dark
palettes. The proof and contact sections are retained.

= 1.13.2 =
Desktop layout follow-up. The call-to-action section now centres
correctly, the middle sections span the full width and line up with the
hero, the six-service plates space evenly, and the footer columns are
rebalanced so the contact email sits on one line. Mobile layout and
page content are unchanged.

= 1.13.1 =
Important desktop layout fix: the footer was rendering too narrow,
causing the contact email to break mid-word. The homepage's six-service
grid could also render with uneven column widths. Both fixed — no
content changes.

= 1.13.0 =
Footer and Contact page social links now show real Instagram/LinkedIn/
TikTok icons instead of plain text. Includes a small required "Icons by
Font Awesome" credit next to the copyright line — please don't remove it,
it covers the LinkedIn icon's license terms.

= 1.12.0 =
Adds two new landing page templates (Singapore, Malaysia) built from
real keyword research, plus an FAQ accordion. A companion blog content
package is available separately — see the theme delivery notes.

= 1.11.0 =
Adds four more page templates (Services, Case Studies, a Legal page for
Privacy Policy/Terms, and Contact), plus a generic fallback so any new
Page looks on-brand by default. No changes to the existing homepage,
blog, or About page.

= 1.10.0 =
Adds a blog (archive + single post) and a new About page template with
a photo gallery and contact form. If you want the blog live at a real
URL, set a Posts page under Settings → Reading — the theme can't do
that step for you. No changes to the existing homepage.

= 1.9.1 =
Important layout fix: page content wasn't actually centered/width-
constrained on desktop screens — it rendered pinned to the left edge on
any monitor wider than about 740px, though this looked fine in every
prior preview screenshot due to a testing gap now also fixed. No content
changes; this only affects layout.

= 1.9.0 =
Dark mode's colours changed: near-black background is now navy, cream
text is now white, and the yellow accent is now green — realigned to
match Remotive Media's official reporting/deck brand. Visible change to
dark mode only; light mode is unaffected.

= 1.8.0 =
The homepage contact form now has a working default (emails submissions
directly, no third-party account needed) instead of doing nothing.
IMPORTANT: if you already customised the "Form submission URL" field in
Appearance -> Theme Options away from the old "#" placeholder, your
custom value is preserved and this doesn't affect you. If you left it at
the default, it now points at the theme's built-in handler automatically
— review Appearance -> Theme Options -> Call-to-Action after updating
to confirm the "Contact email" field is correct, since that's where
submissions will be sent. Also includes a security-hardening pass
(escape-on-output, rate limiting, header-injection guard) with no
user-facing behaviour change.

= 1.7.0 =
Fixes two real WCAG contrast failures in light mode: the green and
red-orange stat/accent colours (sourced directly from the client's brand
spec) measured below the required 4.5:1 against the light background.
Both are now darkened slightly (same colour family) to pass. Also adds a
stronger keyboard focus ring and fixes several small tap-target and
focus-visibility gaps. Minor colour shift in light mode only; dark mode
is unaffected.

= 1.5.0 =
Adds a skip link and a proper `<main>` landmark (neither existed before),
full translation-readiness on the Theme Options page, and a consolidated
license/copyright record (`docs/resources.md`). No visible change to the
homepage design.

= 1.4.0 =
Fixes a real display bug: the CTA section and every solid button rendered
with inverted (light instead of dark) colours in dark mode since 1.2.0.
Also improves touch-target sizing and fixes sticky-nav anchor-link
overlap. No visual change to light mode; dark mode's CTA band and buttons
will look noticeably different (correctly dark again) after updating.

== Privacy ==

This theme does not collect, transmit, or track any visitor data on its
own. Specifically:

* The light/dark mode toggle stores the visitor's chosen mode in their
  browser's `localStorage` (key: `remotive-theme`). This never leaves
  the visitor's browser and isn't sent to any server.
* The homepage's contact form, by default, emails whatever the visitor
  submits to the address set in **Appearance → Theme Options → Contact
  & Socials → Contact email**, using this site's own WordPress installation
  (`wp_mail()`) — no data leaves this site or goes through a third party
  unless you've changed **Appearance → Theme Options → Call-to-Action
  → Form submission URL** away from its default. If you have changed it,
  that destination (a form plugin, a third-party service, a CRM) is your
  own choice, not this theme's, and may have its own separate privacy
  practices — check that destination's privacy policy. Submissions are
  also rate-limited per visitor (a short-lived, anonymous counter keyed
  to IP address) purely to reduce spam; this data is not retained beyond
  a ten-minute window and is never displayed or exported.
* No analytics, tracking pixels, or third-party scripts are loaded by
  this theme. Fonts (Archivo, Newsreader, Saira, Space Grotesk) are
  self-hosted specifically so no requests go to Google Fonts or any
  other external font service.

== Credits ==

Designed and developed by MENJ (https://menj.blog) for Remotive Media Asia
Pte. Ltd. Built on Twenty Twenty-Five, WordPress's default block theme
(bundled, GPL-2.0-or-later). Archivo, Newsreader, Saira, and Space Grotesk
fonts are licensed under the SIL Open Font License and self-hosted within
the theme.
