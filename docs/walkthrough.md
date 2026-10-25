# Walkthrough sheet

For the line-by-line review Gordan asked for. It lists what is public today and
what the brief ("1 simple, clean, well designed page… in English, Malay and
Simplified Chinese… not lots of pages", a landing page) suggests for each, so the meeting can
agree a decision per line rather than start from the whole site. Nothing here is
decided; every row is a question.

Before the meeting: switch the site off (Theme Options → Site behaviour →
Maintenance mode). Editors still see it.

| Page | Address | Today | Question for the walkthrough |
|---|---|---|---|
| Home | `/` | Long page: hero, problems, services, case studies, stats, team, contact form | The one page is meant to be a landing page, so what does the home page become? |
| Main pages in Malay, Simplified, Traditional | `/ms/` `/zh-hans/` `/zh-hant/` | Live (29 translated pages each, draft wording; Insights is English only), switcher in the header | Gordan asked for one page: which of these stay in other languages? Does a native speaker approve the wording? |
| Services | `/services/` and six service pages | Live, English only | Fold into the home page, or keep? |
| Case studies | `/case-studies/` and 14 pages (six listed) | Live, also in the other three languages; eight unlisted | Which figures are approved for public use? Gordan reports people "trolling" about the case studies |
| Team | `/team/` | Live | Keep, or a short strip on the home page? |
| About, FAQ, Contact | `/about/` `/faq/` `/contact/` | Live | One contact form on the home page instead? |
| Insights (blog) | `/blog/` and 7 articles, with photos | Live, English only (no language versions) | Hold back until there is a plan? |
| Privacy, Terms | `/privacy/` `/terms/` | Live drafts | Needs legal sign-off before launch |
| Ad landing pages | `/seo-audit/`, `/google-ads-management/`, `/paid-social-advertising/` and `/ms/`, `/zh-hans/`, `/zh-hant/` versions | Live, hidden from search, for paid traffic only | Keep for the ads, or pause until the home page is agreed? |
| Thank-you pages | `/thank-you/`, `/audit-requested/` | Live, hidden from search | Needed wherever a form stays |

Switches that exist now: Maintenance mode (whole site); per-language page
switches (Tools → Translations); the ad landing pages can be set to draft in
Pages without touching code. Unpublishing or hiding anything that is live is the
owner's decision, so none of it is done in code.

## Since the sheet was written (v1.116.0 to v1.120.3)

The one page the brief asks for exists as a switch, so the walkthrough can now
decide between two layouts rather than a list of pages to retire:

| Switch | Where | What it does |
|---|---|---|
| One-page layout | Theme Options → Agency layout | The home page becomes one scrolling page in all four languages: headline and contact form, the six services, how we work, signs it is time to talk, the team, the common questions, a closing form. Each section has its own switch and editable words. Off by default; switching it off puts everything back. |
| Send the other pages to the home page | Theme Options → Agency layout | On by default once the layout is on. About, Services, Case Studies, Insights, Team, Contact and FAQ redirect to the home page and leave the sitemaps; Privacy, Terms, the ad landing pages and the thank-you pages stay live, with the one-page bar and footer on the legal pages. |
| Default colour mode | Theme Options → Site behaviour | Light since v1.120.3 (dark before). A site still on dark moves to light once on the first admin load after the upload; System is left alone. |
| Block application passwords | Theme Options → Site behaviour | On by default since v1.120.1. REST API logins with an application password are refused and the profile section is hidden. Turn it off only while an integration needs one. |
| Scroll motion | Theme Options → Site behaviour | The parallax on both layouts; a device set to reduce motion never sees it. |
| Graceful error handling | Theme Options → Site behaviour | PHP notices stay off the page; fatals show the branded page. WordPress recovery mode keeps working beside it. |

Enquiries: every form (both layouts, the ad landing pages) stores the lead
before emailing it. The Enquiries card lists them with a spam folder and a CSV
export, and each enquiry's edit screen ("View all enquiries", then the entry)
shows the full message in a read-only Message box with the email and the form
it came from. The forms also ask "How did you hear about us?" (optional), and
the answer is recorded in English in the email and the stored lead.
