# Accessibility conformance notes

Target: **WCAG 2.2 Level AA**. This file records what was tested, what was
fixed, what is deliberately exempt, and what still needs human judgement.

## Method

- Automated: axe-core against 11 templates in both colour modes at 320,
  390, 768 and 1280px (88 runs).
- Manual: keyboard traversal with focus-visibility and obscuring checks,
  200% text zoom, the 1.4.12 text-spacing override, reduced-motion, and
  colour-contrast maths computed directly from the palette.

## Fixed

| Criterion | Defect | Fix |
|---|---|---|
| 1.4.3 | Muted ink 3.68:1 on paper (eyebrows, tags, dates, notes) | Token raised to 4.7:1 |
| 1.4.3 | Reassurance line invisible on the inverted CTA band (1.01:1) | Band-scoped muted white |
| 1.4.3 | Inverted band note 3.85:1 | Alpha raised to 5.9:1 |
| 1.4.3 | Submit buttons 3.19:1 light, 4.25:1 dark | Fixed near-black label; dark uses the lighter accent (5.72:1 / 4.97:1) |
| 1.4.3 | Accent headings 2.51 to 2.90:1 on their surfaces | -dark ramp in light, lifted accent on dark cards |
| 1.4.4 | Nav overflowed the viewport at 200% text zoom | Nav may wrap when it genuinely cannot fit |
| 1.3.1, 2.4.6 | Footer used h6 after h2; cards used h3 under h1 | Levels corrected, classes and visual sizes unchanged |
| 2.4.4, 4.1.2 | Linked featured images had no accessible name | Alt falls back to the post title when empty |
| 2.5.8 | Standalone links 15 to 17px tall | Block padding to a 24px minimum at every width |
| 4.1.2 | `role="listitem"` on `<button>` (invalid) | Gallery is a labelled group |
| 4.1.2 | Current page not exposed in navigation | `aria-current="page"` added |
| 2.2.2 | Ticker had no reduced-motion path | Animation stops under `prefers-reduced-motion` |

## Already conformant before this pass

Skip link; focus-visible styles on all interactive regions; the lightbox
dialog (focus moved in on open, trapped while open, restored on close,
Escape and arrow keys); labelled theme toggle with `aria-pressed`; form
status regions using `role="status"` with `aria-live="polite"`; scroll
padding so the sticky header never hides an anchor target (2.4.11).

## Deliberate exemptions

- **Links inside running sentences** keep their natural size under 2.5.8's
  inline exception. Enlarging them would break line rhythm and the
  criterion does not require it.
- **Decorative images** placed in content keep empty alt. The title
  fallback applies only to linked featured images, where a link would
  otherwise have no name at all.

## Needs human verification

- **Screen-reader smoke test.** Not possible in this environment. Run one
  pass with NVDA or VoiceOver over the homepage, a case study, the blog
  index and the contact form.
- **Third-party sign-in.** The Google Sign-In client is a plugin's markup
  and outside the theme's control; audit it separately (3.3.8).
- **Author-entered content.** Alt text, heading order and link text inside
  posts are the author's responsibility; the theme cannot enforce them.
- **Real-device touch testing** for the enlarged targets.
