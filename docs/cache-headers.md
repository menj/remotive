# Cache headers for theme assets

PageSpeed's "Use efficient cache lifetimes" audit reports the theme's own
files as `None` or `7d`. Neither value comes from the theme: `Cache-Control`
is set by the web server or the CDN in front of it, and WordPress does not
set it for static files. This file is the configuration to paste in.

## What the report showed, and why

Two patterns appear in the audit:

- **Fonts, images, AVIF portraits and CSS: `7d`.** A rule already matches
  these extensions, but a week is short for files that never change in
  place.
- **JavaScript: `None`.** Every script has no cache lifetime while CSS,
  fonts and images have seven days. The query string is not the cause: the
  stylesheet is requested with the same `?ver=` and still gets a header. The
  server's rule simply lists some file types and not JavaScript, so each
  script is fetched again on every visit.

The theme versions every asset URL with the file's own modification time,
so a changed file always arrives under a new URL. That makes a long
immutable lifetime safe: there is no scenario where a visitor is stuck with
a stale file after a theme update.

## The short version

`docs/htaccess-cache.txt` is a drop-in: copy the marked block into the
`.htaccess` in your WordPress root, above the `# BEGIN WordPress` line. The
rest of this file explains what it does.

## Apache

Add to `.htaccess` above the WordPress block, or to the vhost:

```apache
<IfModule mod_expires.c>
	ExpiresActive On
	ExpiresByType text/css                 "access plus 1 year"
	ExpiresByType application/javascript   "access plus 1 year"
	ExpiresByType text/javascript          "access plus 1 year"
	ExpiresByType font/woff2               "access plus 1 year"
	ExpiresByType image/avif               "access plus 1 year"
	ExpiresByType image/webp               "access plus 1 year"
	ExpiresByType image/png                "access plus 1 year"
	ExpiresByType image/jpeg               "access plus 1 year"
	ExpiresByType image/svg+xml            "access plus 1 year"
</IfModule>

# Matches on extension, so the ?ver= query string is irrelevant. This is the
# part that fixes the scripts reporting no cache lifetime at all.
<IfModule mod_headers.c>
	<FilesMatch "\.(css|js|woff2|avif|webp|png|jpe?g|svg|ico)$">
		Header set Cache-Control "public, max-age=31536000, immutable"
	</FilesMatch>
</IfModule>
```

## nginx

```nginx
location ~* \.(css|js|woff2|avif|webp|png|jpe?g|svg|ico)$ {
	add_header Cache-Control "public, max-age=31536000, immutable";
	access_log off;
}
```

## Notes

- `immutable` tells the browser not to revalidate on reload. It is correct
  here only because every URL carries a version; do not apply it to files
  served without one.
- Apply the rule to static assets only. HTML must stay uncached or
  short-cached, or edits will not appear.
- Behind a CDN, set the same policy at the edge; a CDN default often
  overrides the origin's headers.
- The front page sends `Vary: Accept` because the hero mark is chosen from
  the request's `Accept` header. Caches must honour it, which any correct
  HTTP cache does by default.
- Third-party assets, such as the Google Sign-In client, are served from
  their own origins under their own headers. Nothing here changes them; the
  only lever is whether the plugin loads on the page at all.
