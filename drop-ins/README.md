# Drop-ins

`db-error.php`, `maintenance.php` and `php-error.php` are real WordPress
drop-ins. WordPress only looks for them at the root of `wp-content/`, never
inside a theme folder, so they are shipped here and **installed
automatically** — see below.

| File | When WordPress shows it |
|---|---|
| `db-error.php` | The database connection fails |
| `maintenance.php` | A core / plugin / theme update is in progress |
| `php-error.php` | A fatal error occurs **before the theme loads** (a plugin or mu-plugin). Once the theme is running, `inc/core/error-handler.php` handles fatals with its own branded page and this file is not used. |

## How they install

`remotive_version_sync()` (`inc/setup/site-setup.php`) copies all three into
`wp-content/` once per theme version: on activation and on the first request
after any upload — Appearance upload, FTP or a deploy script alike. Editing a
file here and shipping a new version is enough; there is no manual step.

It never overwrites a drop-in that is not ours: each file carries a "Remotive
Media" signature in its opening comment, and the copy is skipped if the file
already at `wp-content/` does not.

If `wp-content/` is not writable by PHP the copy silently does nothing — make
it writable, or copy the three files across once by hand.
