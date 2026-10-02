# Homepage copy brief — v1.80.0

Source: Gordan Domlija's homepage draft (`HOMEPAGE_RMA_site.docx`),
reviewed and implemented 2026-09-09.

Two open items from the original draft were **not** carried into this
release and still need a decision before the copy is finalised in
Appearance → Theme Options → Homepage:

1. **`hero_reassure`** — the current value ("30 minutes · no
   commitment · reply in three business days") was written for the
   old primary CTA ("Get a free audit"). It doesn't fit the new one
   ("Show me a growth plan"). The draft didn't supply a replacement
   line.
2. **Two headlines were given in the draft** ("Performance marketing,
   media & SEO built to scale Asian brands" vs. "Create, Capture, and
   Convert Global Demand"). This release uses the first — it fits the
   existing three-part hero token structure (`hero_line_1` /
   `hero_line_2` / `hero_highlight`); the second is closer in spirit
   to the existing "services" section framework blocks (Create /
   Capture / Convert), which already ship largely as-is.

The problem-grid content ("For brands" / "For agencies") and the new
closing-CTA copy ("Stop guessing where your growth is going to come
from." / "Run a Market Diagnostic") are taken directly from the draft
with no changes.

See `docs/changelog.md` [1.80.0] for the full list of code changes.
