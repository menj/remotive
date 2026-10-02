<?php
// Remotive Media fatal-error fallback 1.1.0.
//
// A real WordPress drop-in: since WP 5.2, WP_Fatal_Error_Handler requires
// wp-content/php-error.php automatically when it exists.
//
// Scope, precisely: once the theme has loaded, inc/core/error-handler.php takes
// over fatals with its own branded page (full detail for administrators,
// a reference ID for everyone else) and switches WordPress's handler off,
// so this file is NOT used for those. It covers the gap before that: a
// fatal in a plugin or mu-plugin, which runs before the theme exists and
// therefore before its handler is registered. Without this file that case
// falls back to WordPress's generic "critical error" page.
if ( ! headers_sent() ) {
	header( 'HTTP/1.1 500 Internal Server Error', true, 500 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Cache-Control: no-store, max-age=0' );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Something went wrong | Re:Motive Media</title>
<style>
:root{--paper:#1a1a2e;--paper-2:#0d1117;--ink:#ffffff;--muted:#a9acc4;--card:#252542;--edge:rgba(255,255,255,.12);--cyan:#00aeef;--magenta:#ec008c}
*{box-sizing:border-box}
html,body{height:100%;margin:0}
body{display:flex;align-items:center;justify-content:center;padding:24px;background:var(--paper);color:var(--ink);font-family:"Archivo",Helvetica,Arial,sans-serif;line-height:1.55}
main{width:min(560px,100%);padding:44px 40px 40px;background:var(--card);border:1px solid var(--edge);border-radius:10px;text-align:center;box-shadow:0 14px 44px rgba(0,0,0,.35);position:relative;overflow:hidden}
main:before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--magenta),var(--cyan))}
.mark{display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:12px;background:var(--paper-2);color:var(--ink);font-family:"Archivo",sans-serif;font-size:22px;font-weight:900;letter-spacing:-.02em}
.name{margin:16px auto 0;font-size:.78rem;letter-spacing:.24em;text-transform:uppercase;color:var(--muted)}
h1{margin:24px 0 12px;font-size:clamp(1.6rem,4vw,2.1rem);font-weight:700;letter-spacing:-.02em;line-height:1.2}
p{margin:0 auto 10px;max-width:42ch;font-size:1.05rem;color:var(--muted)}
.again{margin-top:24px;display:inline-block;padding:11px 26px;border-radius:8px;background:var(--magenta);color:#fff;font-weight:700;text-decoration:none}
.again:focus-visible{outline:2px solid var(--ink);outline-offset:3px}
</style>
</head>
<body>
<main role="main">
<div class="mark" aria-hidden="true">R:M</div>
<p class="name">Re:Motive Media</p>
<h1>Something went wrong on our end</h1>
<p>An unexpected error stopped this page from loading. It's already been logged and we'll take a look.</p>
<p>Try heading back to the homepage, or check back in a few minutes.</p>
<a class="again" href="/">Back to homepage</a>
</main>
</body>
</html>
