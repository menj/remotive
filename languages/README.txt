This folder contains `remotive.pot`, the translation template for gettext
strings in the theme's PHP files. Regenerated in v1.69.2 from source: 265
strings with file:line references, replacing a hand-maintained file that had
fallen to 35 and no longer reflected the code.

If you add a translatable string, regenerate rather than hand-editing. WP-CLI
is the normal tool (`wp i18n make-pot . languages/remotive.pot --domain=remotive`);
where it is unavailable, extract every __()/_e()/_x()/esc_html__()/esc_attr__()
call from functions.php and inc/*/*.php, dedupe by msgid, and keep the file:line
references — they are what makes a translator's job possible. Create locale-specific .po/.mo files from
that template and place them here; `load_theme_textdomain()` in functions.php
already points to this directory.

Static block-template copy and the English WebMCP tool descriptions are not
currently extracted into the POT. Translating the former requires a content-
translation workflow such as WPML or Polylang. If the WebMCP descriptions
become visitor-facing in supported browsers, move them into localized script
data in `inc/content/webmcp.php` and regenerate the POT rather than duplicating
translated strings inside `assets/js/webmcp.js`.
