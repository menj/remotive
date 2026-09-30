<?php
// Remotive Media db-error drop-in 1.0.0. Place at wp-content/db-error.php.
// WordPress serves this automatically whenever it cannot connect to the
// database — it loads before plugins, themes, or most of WP core, so it
// must stay a single self-contained file with no external dependencies.
if ( ! headers_sent() ) {
	header( 'HTTP/1.1 503 Service Unavailable', true, 503 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Retry-After: 120' );
	header( 'Cache-Control: no-store, max-age=0' );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Back shortly | Re:Motive Media</title>
<style>
:root{--paper:#1a1a2e;--paper-2:#0d1117;--ink:#ffffff;--muted:#a9acc4;--card:#252542;--edge:rgba(255,255,255,.12);--cyan:#00aeef;--magenta:#ec008c}
*{box-sizing:border-box}
html,body{height:100%;margin:0}
body{display:flex;align-items:center;justify-content:center;padding:24px;background:var(--paper);color:var(--ink);font-family:"Archivo",Helvetica,Arial,sans-serif;line-height:1.55}
main{width:min(560px,100%);padding:44px 40px 40px;background:var(--card);border:1px solid var(--edge);border-radius:10px;text-align:center;box-shadow:0 14px 44px rgba(0,0,0,.35);position:relative;overflow:hidden}
main:before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--cyan),var(--magenta))}
.mark{display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:12px;background:var(--paper-2);color:var(--ink);font-family:"Archivo",sans-serif;font-size:22px;font-weight:900;letter-spacing:-.02em}
.name{margin:16px auto 0;font-size:.78rem;letter-spacing:.24em;text-transform:uppercase;color:var(--muted)}
h1{margin:24px 0 12px;font-size:clamp(1.6rem,4vw,2.1rem);font-weight:700;letter-spacing:-.02em;line-height:1.2}
p{margin:0 auto 10px;max-width:42ch;font-size:1.05rem;color:var(--muted)}
.again{margin-top:24px;display:inline-block;padding:11px 26px;border-radius:8px;background:var(--cyan);color:#0d1117;font-weight:700;text-decoration:none}
.again:focus-visible{outline:2px solid var(--ink);outline-offset:3px}
</style>
</head>
<body>
<main role="main">
<div class="mark" aria-hidden="true">R:M</div>
<p class="name">Re:Motive Media</p>
<h1>We're sorting a database hiccup</h1>
<p>The site can't reach its database right now. This is almost always temporary and resolves on its own within a few minutes.</p>
<p>If this keeps happening, we already know — our monitoring flags it too.</p>
<a class="again" href="/">Try again</a>
</main>
</body>
</html>
