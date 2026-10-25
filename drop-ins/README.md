# Drop-ins

`db-error.php`, `maintenance.php` and `php-error.php` are real WordPress
drop-ins. WordPress only looks for them at the root of `wp-content/`, never
inside a theme folder, so they are shipped here and **installed
automatically**, see below.

| File | When WordPress shows it |
|---|---|
| `db-error.php` | The database connection fails |
| `maintenance.php` | A core / plugin / theme update is in progress |
| `php-error.php` | A fatal error occurs **before the theme loads** (a plugin or mu-plugin). Once the theme is running, `inc/core/error-handler.php` handles fatals with its own branded page; WordPress still loads this file first, and it returns at once when the theme's handler is active (`REMOTIVE_ERROR_HANDLER`) so only one page is printed. |

## How they install

`remotive_version_sync()` (`inc/setup/site-setup.php`) copies all three into
`wp-content/` once per theme version: on activation and on the first request
after any upload (Appearance upload, FTP or a deploy script alike). Editing a
file here and shipping a new version is enough; there is no manual step.

It never overwrites a drop-in that is not ours: each file carries a "Remotive
Media" signature in its opening comment, and the copy is skipped if the file
already at `wp-content/` does not.

If `wp-content/` is not writable by PHP the copy silently does nothing: make
it writable, or copy the three files across once by hand.

## What they are not

A failure before WordPress starts at all (a core file missing, a PHP version
change, a full disk) never reaches a drop-in, because WordPress is what loads
them. For that case the theme ships static pages in `error-pages/` and the
`ErrorDocument` lines to paste into the site root `.htaccess`
(`error-pages/htaccess-errors.txt`).

All three pages carry the Re:Motive Media logo embedded in the file
(v1.119.1), so it shows even when the stylesheet or the uploads folder cannot
be reached, and their wording follows the writing style guide
(`tests/check-writing-style.php` covers them).
