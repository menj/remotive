<?php
/**
 * Remotive Media — branded error handling.
 *
 * Replaces WordPress's generic "There has been a critical error on this
 * website" page with a branded page that shows the actual fault — but only
 * to people entitled to see it.
 *
 * Why the split
 * -------------
 * A stack trace names absolute server paths, function names, plugin files
 * and often version numbers. Showing that to anonymous visitors hands an
 * attacker a map of the installation, which directly undoes the version
 * fingerprinting removal in inc/core/security.php. So:
 *
 *   - Administrators (manage_options) see the full message, file, line and
 *     trace, formatted and readable.
 *   - Everyone else sees a branded apology with a reference ID and nothing
 *     technical.
 *
 * The reference ID is a short hash of the error's file+line+message. It is
 * printed on the public page and written to the PHP error log alongside the
 * full detail, so a visitor can quote it in an email and the administrator
 * can find the exact entry without the visitor ever seeing the internals.
 *
 * Two handlers are registered because they catch different things:
 *   - set_exception_handler() catches uncaught exceptions and gives a real
 *     stack trace.
 *   - register_shutdown_function() catches fatals that are not exceptions
 *     (E_ERROR, E_PARSE, undefined function calls, memory exhaustion),
 *     where only file/line/message are available.
 *
 * WordPress's own handler is disabled via wp_fatal_error_handler_enabled so
 * the two do not both try to render a page. Note this also disables
 * WordPress recovery mode emails; the trade-off is deliberate and is
 * documented in readme.txt.
 *
 * Non-HTML contexts (AJAX, REST, cron, WP-CLI) are passed through untouched
 * so a fatal there still produces a machine-readable failure rather than an
 * HTML page a JSON client cannot parse.
 *
 * @package Remotive
 */

defined( 'ABSPATH' ) || exit;

/**
 * Minimal fallbacks.
 *
 * A fatal can occur before WordPress has loaded formatting.php or
 * functions.php from wp-includes, in which case esc_html() and friends do
 * not exist yet. Calling them from inside the error handler would fatal
 * again, producing a blank white page — the exact failure this file exists
 * to prevent. These wrappers use the WordPress function when available and
 * fall back to the PHP equivalent when not.
 */
function remotive_e( $text ) {
	return function_exists( 'esc_html' )
		? esc_html( $text )
		: htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function remotive_eu( $url ) {
	if ( function_exists( 'esc_url' ) ) {
		return esc_url( $url );
	}
	$url = filter_var( (string) $url, FILTER_SANITIZE_URL );
	return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
}

/**
 * Take over from WordPress's fatal error handler.
 *
 * Returning false here stops WP_Fatal_Error_Handler from rendering its own
 * page, leaving ours as the only output. Filterable so a site owner can hand
 * control back to WordPress without editing the theme:
 *
 *   add_filter( 'remotive_use_custom_error_page', '__return_false' );
 */
function remotive_disable_wp_fatal_handler( $enabled ) {
	if ( ! apply_filters( 'remotive_use_custom_error_page', true ) ) {
		return $enabled;
	}
	return false;
}
add_filter( 'wp_fatal_error_handler_enabled', 'remotive_disable_wp_fatal_handler' );

/**
 * Whether the current request should see full technical detail.
 *
 * Checked defensively: a fatal can occur before pluggable.php has loaded, in
 * which case current_user_can() does not exist yet and calling it would
 * itself fatal inside the error handler. Every check is guarded.
 *
 * Detail is shown when either:
 *   - The current user can manage_options (an administrator), or
 *   - REMOTIVE_SHOW_ERRORS is defined true in wp-config.php. This exists so
 *     an administrator locked out by the very error being diagnosed can
 *     still read it.
 *
 * @return bool
 */
function remotive_may_see_error_detail() {
	if ( defined( 'REMOTIVE_SHOW_ERRORS' ) && REMOTIVE_SHOW_ERRORS ) {
		return true;
	}

	if ( ! function_exists( 'current_user_can' ) || ! function_exists( 'wp_get_current_user' ) ) {
		return false;
	}

	// wp_get_current_user() can itself throw if the DB connection is what
	// failed, so the whole check is wrapped.
	try {
		return (bool) current_user_can( 'manage_options' );
	} catch ( Throwable $e ) {
		return false;
	}
}

/**
 * Short, stable reference for one error occurrence.
 *
 * Same fault produces the same ID, so repeat reports from different visitors
 * are recognisably the same problem.
 *
 * @param array $error file/line/message.
 * @return string 8-character uppercase hex.
 */
function remotive_error_reference( $error ) {
	$seed = ( $error['file'] ?? '' ) . '|' . ( $error['line'] ?? '' ) . '|' . ( $error['message'] ?? '' );
	return strtoupper( substr( md5( $seed ), 0, 8 ) );
}

/**
 * Shorten an absolute server path to something readable, relative to the
 * WordPress root, so the admin view is legible without printing the full
 * filesystem layout on every line of a trace.
 *
 * @param string $path
 * @return string
 */
function remotive_relative_path( $path ) {
	$root = defined( 'ABSPATH' ) ? ABSPATH : '';
	if ( $root && 0 === strpos( $path, $root ) ) {
		return substr( $path, strlen( $root ) );
	}
	return $path;
}

/**
 * Whether this request can accept an HTML error page.
 *
 * AJAX, REST, cron and CLI callers expect JSON or plain output; replacing
 * that with an HTML page turns a diagnosable failure into a parse error at
 * the other end. Those contexts are left to PHP's default handling.
 *
 * @return bool
 */
function remotive_error_wants_html() {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return false;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return false;
	}
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		return false;
	}
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		return false;
	}
	return true;
}

/**
 * Write the full detail to the PHP error log with the reference ID attached,
 * so the log entry and the page the visitor saw can be matched up.
 *
 * @param array  $error
 * @param string $ref
 * @param string $trace
 */
function remotive_log_error( $error, $ref, $trace = '' ) {
	$line = sprintf(
		'[Remotive %s] %s in %s on line %s',
		$ref,
		$error['message'] ?? 'Unknown error',
		$error['file'] ?? 'unknown file',
		$error['line'] ?? '?'
	);
	if ( $trace ) {
		$line .= "\nTrace:\n" . $trace;
	}
	error_log( $line );
}

/**
 * Render the branded error page and stop.
 *
 * Styles are inlined because a fatal can occur before the stylesheet is
 * enqueued, and because linking to the theme stylesheet from an error page
 * risks a second fatal if the theme itself is what failed. Colours are
 * hardcoded to the brand palette rather than read from theme.json for the
 * same reason.
 *
 * @param array  $error file/line/message.
 * @param string $trace Optional stack trace (exceptions only).
 */
function remotive_render_error_page( $error, $trace = '' ) {
	$ref     = remotive_error_reference( $error );
	$is_priv = remotive_may_see_error_detail();

	remotive_log_error( $error, $ref, $trace );

	// Discard anything already half-rendered so the error page is not
	// appended to a broken partial document.
	while ( ob_get_level() > 0 ) {
		ob_end_clean();
	}

	if ( ! headers_sent() ) {
		if ( function_exists( 'status_header' ) ) {
			status_header( 500 );
		} else {
			header( 'HTTP/1.1 500 Internal Server Error' );
		}
		if ( function_exists( 'nocache_headers' ) ) {
			nocache_headers();
		}
		header( 'Content-Type: text/html; charset=utf-8' );
	}

	$site  = function_exists( 'get_bloginfo' ) ? get_bloginfo( 'name' ) : 'This site';
	$home  = function_exists( 'home_url' ) ? home_url( '/' ) : '/';
	$title = $is_priv ? 'Error detail' : 'Something went wrong';

	echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">';
	echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
	echo '<meta name="robots" content="noindex,nofollow">';
	echo '<title>' . remotive_e( $title ) . '</title>';
	?>
<style>
  :root{ --ink:#1e1e1e; --paper:#f7f4ec; --magenta:#ff449f; --line:rgba(30,30,30,.14); }
  *{ box-sizing:border-box; }
  body{
    margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
    padding:2rem 1.25rem; background:var(--paper); color:var(--ink);
    font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif; line-height:1.6;
  }
  .rm-err{ width:100%; max-width:56rem; }
  .rm-err__eyebrow{
    font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.12em;
    color:var(--magenta); margin:0 0 .75rem;
  }
  .rm-err__title{
    font-size:clamp(1.9rem,5vw,3rem); line-height:1.05; margin:0 0 1rem; font-weight:800;
    letter-spacing:-.02em;
  }
  .rm-err__lede{ font-size:1.05rem; margin:0 0 1.75rem; max-width:52ch; }
  .rm-err__ref{
    display:inline-block; font-family:ui-monospace,SFMono-Regular,Menlo,monospace;
    font-size:.85rem; background:rgba(30,30,30,.06); border:1px solid var(--line);
    border-radius:6px; padding:.35rem .7rem; margin:0 0 1.75rem;
  }
  .rm-err__panel{
    border:1px solid var(--line); border-left:4px solid var(--magenta); border-radius:8px;
    background:#fff; padding:1.25rem 1.4rem; margin:0 0 1.5rem;
  }
  .rm-err__label{
    font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em;
    color:rgba(30,30,30,.6); margin:0 0 .35rem;
  }
  .rm-err__msg{
    font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:.92rem;
    margin:0 0 1rem; word-break:break-word;
  }
  .rm-err__loc{ font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:.85rem; margin:0; }
  .rm-err__loc b{ color:var(--magenta); }
  .rm-err__trace{
    font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:.78rem;
    white-space:pre-wrap; word-break:break-word; margin:.5rem 0 0;
    max-height:22rem; overflow:auto; background:rgba(30,30,30,.04);
    border-radius:6px; padding:.9rem 1rem;
  }
  .rm-err__btn{
    display:inline-block; background:var(--ink); color:#fff; text-decoration:none;
    padding:.85rem 1.75rem; border-radius:6px; font-size:.85rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.06em;
  }
  .rm-err__btn:hover{ background:var(--magenta); }
  .rm-err__note{ font-size:.82rem; color:rgba(30,30,30,.62); margin:1.5rem 0 0; }
</style>
</head><body><main class="rm-err">
	<?php if ( $is_priv ) : ?>
		<p class="rm-err__eyebrow">Administrator view</p>
		<h1 class="rm-err__title">Something broke.</h1>
		<p class="rm-err__lede">You are seeing the full detail because you are signed in as an administrator. Visitors see a plain apology with no technical information.</p>
		<p class="rm-err__ref">Reference <?php echo remotive_e( $ref ); ?></p>

		<div class="rm-err__panel">
			<p class="rm-err__label">Message</p>
			<p class="rm-err__msg"><?php echo remotive_e( $error['message'] ?? 'Unknown error' ); ?></p>
			<p class="rm-err__label">Location</p>
			<p class="rm-err__loc"><?php echo remotive_e( remotive_relative_path( $error['file'] ?? 'unknown' ) ); ?> line <b><?php echo remotive_e( (string) ( $error['line'] ?? '?' ) ); ?></b></p>
			<?php if ( $trace ) : ?>
				<p class="rm-err__label" style="margin-top:1rem">Stack trace</p>
				<pre class="rm-err__trace"><?php echo remotive_e( $trace ); ?></pre>
			<?php endif; ?>
		</div>

		<a class="rm-err__btn" href="<?php echo remotive_eu( $home ); ?>">Back to the site</a>
		<p class="rm-err__note">The same detail has been written to the PHP error log against reference <?php echo remotive_e( $ref ); ?>.</p>
	<?php else : ?>
		<p class="rm-err__eyebrow"><?php echo remotive_e( $site ); ?></p>
		<h1 class="rm-err__title">Something went wrong at our end.</h1>
		<p class="rm-err__lede">This page could not be loaded. The fault is on our side, not yours, and it has been recorded. Try again in a moment, or head back to the homepage.</p>
		<p class="rm-err__ref">Reference <?php echo remotive_e( $ref ); ?></p>
		<a class="rm-err__btn" href="<?php echo remotive_eu( $home ); ?>">Back to the homepage</a>
		<p class="rm-err__note">If you were part-way through something and it matters, quote the reference above when you get in touch and we can find exactly what happened.</p>
	<?php endif; ?>
</main></body></html>
	<?php
	exit;
}

/**
 * Uncaught exception handler — gives a real stack trace.
 *
 * @param Throwable $e
 */
function remotive_handle_exception( $e ) {
	if ( ! remotive_error_wants_html() ) {
		return;
	}

	remotive_render_error_page(
		array(
			'message' => get_class( $e ) . ': ' . $e->getMessage(),
			'file'    => $e->getFile(),
			'line'    => $e->getLine(),
		),
		$e->getTraceAsString()
	);
}

/**
 * Shutdown handler — catches fatals that are not exceptions.
 *
 * Only the four unrecoverable types are handled. Warnings and notices are
 * left alone: they do not stop execution, and hijacking the page for them
 * would replace a working page with an error screen.
 */
function remotive_handle_shutdown() {
	$error = error_get_last();

	if ( ! $error ) {
		return;
	}

	$fatal = array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR );
	if ( ! in_array( $error['type'], $fatal, true ) ) {
		return;
	}

	if ( ! remotive_error_wants_html() ) {
		return;
	}

	remotive_render_error_page( $error );
}


/* ========================================================================
   NON-FATAL NOTICES, WARNINGS AND DEPRECATIONS
   ========================================================================

   Fatals are handled above. This section handles everything that does not
   stop execution — notices, warnings, deprecations — which PHP would
   otherwise print directly into the page whenever display_errors is on.

   Why this matters beyond tidiness: a printed notice is output. Once any
   output has been sent, no header() call can succeed for the rest of the
   request, so every subsequent cookie, redirect and cache header fails
   with "Cannot modify header information". One notice from one plugin
   therefore cascades into a page full of warnings and can genuinely break
   redirects and logins. Intercepting the notice removes the cascade at its
   source rather than hiding the symptoms.

   set_error_handler() receives these instead of PHP's internal handler.
   Returning true tells PHP the error is fully handled, so it prints
   nothing and writes nothing itself — this file does the logging instead,
   which means nothing is lost, only relocated.

   Fatals cannot be caught here (PHP does not route E_ERROR, E_PARSE,
   E_CORE_ERROR or E_COMPILE_ERROR to a user handler); those are already
   covered by the shutdown handler above.
   ======================================================================== */

/**
 * Whether graceful handling of non-fatal errors is switched on.
 *
 * Read straight from the option row rather than through
 * remotive_get_theme_option(), because this file is required first — before
 * inc/options/theme-options.php exists — so that the handler is registered before
 * any other include can emit anything. Defaults to on when the option has
 * never been saved.
 *
 * @return bool
 */
function remotive_graceful_errors_enabled() {
	if ( ! function_exists( 'get_option' ) ) {
		return true;
	}

	$opts = get_option( 'remotive_theme_options' );

	if ( ! is_array( $opts ) || ! array_key_exists( 'graceful_errors', $opts ) ) {
		return true;
	}

	return '1' === (string) $opts['graceful_errors'];
}

/**
 * Human labels for the error constants worth naming.
 *
 * @param int $errno
 * @return string
 */
function remotive_error_type_label( $errno ) {
	$map = array(
		E_WARNING           => 'Warning',
		E_NOTICE            => 'Notice',
		E_USER_WARNING      => 'Warning',
		E_USER_NOTICE       => 'Notice',
		E_DEPRECATED        => 'Deprecated',
		E_USER_DEPRECATED   => 'Deprecated',
		E_RECOVERABLE_ERROR => 'Recoverable error',
		E_CORE_WARNING      => 'Core warning',
		E_COMPILE_WARNING   => 'Compile warning',
	);

	return isset( $map[ $errno ] ) ? $map[ $errno ] : 'Error';
}

/**
 * Capture one non-fatal PHP diagnostic.
 *
 * Deduplicated by file, line and message, so a notice inside a loop that
 * fires two hundred times is recorded once with a count rather than
 * two hundred times. Capped at 50 distinct entries per request so a
 * pathologically noisy plugin cannot exhaust memory through the very
 * mechanism meant to contain it.
 *
 * @param int    $errno
 * @param string $errstr
 * @param string $errfile
 * @param int    $errline
 * @return bool True to tell PHP the error is handled and must not print.
 */
function remotive_collect_php_notice( $errno, $errstr, $errfile = '', $errline = 0 ) {
	// Honour the @ suppression operator and the current error_reporting
	// mask. Returning false hands the error back to PHP, which will then
	// respect the suppression and stay silent.
	if ( ! ( error_reporting() & $errno ) ) {
		return false;
	}

	$key = md5( $errfile . '|' . $errline . '|' . $errstr );

	if ( ! isset( $GLOBALS['remotive_php_notices'] ) ) {
		$GLOBALS['remotive_php_notices'] = array();
	}

	if ( isset( $GLOBALS['remotive_php_notices'][ $key ] ) ) {
		$GLOBALS['remotive_php_notices'][ $key ]['count']++;
		return true;
	}

	if ( count( $GLOBALS['remotive_php_notices'] ) >= 50 ) {
		return true; // Still suppressed, simply no longer recorded.
	}

	$GLOBALS['remotive_php_notices'][ $key ] = array(
		'type'    => remotive_error_type_label( $errno ),
		'message' => $errstr,
		'file'    => $errfile,
		'line'    => $errline,
		'count'   => 1,
	);

	// PHP will not log this itself now that the handler has claimed it,
	// so the entry is written here. Nothing is lost by suppressing the
	// on-screen copy; it moves to the log.
	error_log( sprintf(
		'[Remotive %s] %s in %s on line %d',
		remotive_error_type_label( $errno ),
		$errstr,
		$errfile,
		(int) $errline
	) );

	// Hand the diagnostic on to whatever handler was already registered —
	// Query Monitor and similar debugging plugins install theirs when
	// plugins load, which is before a theme's functions.php runs, so
	// replacing it outright would silently blind those tools. Guarded
	// against recursion and against a previous handler that throws.
	if ( ! empty( $GLOBALS['remotive_prev_error_handler'] )
		&& empty( $GLOBALS['remotive_in_error_handler'] ) ) {
		$GLOBALS['remotive_in_error_handler'] = true;
		try {
			call_user_func(
				$GLOBALS['remotive_prev_error_handler'],
				$errno, $errstr, $errfile, $errline
			);
		} catch ( Throwable $e ) {
			// A broken upstream handler must not take the page down.
		}
		$GLOBALS['remotive_in_error_handler'] = false;
	}

	return true;
}

/**
 * Render captured diagnostics for administrators only.
 *
 * Placed in the footer on the front end and in the standard notice area in
 * the admin. Everyone else sees nothing at all — the diagnostics were
 * suppressed before reaching the page and are never printed for them, which
 * is the whole point of the exercise.
 *
 * @param bool $admin_screen Whether this is the WordPress admin.
 */
function remotive_render_php_notices( $admin_screen = false ) {
	if ( empty( $GLOBALS['remotive_php_notices'] ) ) {
		return;
	}

	if ( ! remotive_may_see_error_detail() ) {
		return;
	}

	$notices = $GLOBALS['remotive_php_notices'];
	$total   = 0;
	foreach ( $notices as $n ) {
		$total += $n['count'];
	}

	if ( $admin_screen ) {
		echo '<div class="notice notice-warning is-dismissible"><p><strong>';
		printf(
			/* translators: 1: distinct issue count, 2: total occurrence count */
			remotive_e( 'Re:Motive — %1$d PHP issue(s) suppressed on this page (%2$d occurrences).' ),
			count( $notices ),
			$total
		);
		echo '</strong></p><ol style="margin:.5em 0 .5em 1.5em">';
		foreach ( $notices as $n ) {
			echo '<li style="margin-bottom:.4em"><code>' . remotive_e( $n['type'] ) . '</code> '
				. remotive_e( $n['message'] )
				. '<br><small>' . remotive_e( remotive_relative_path( $n['file'] ) )
				. ' line ' . remotive_e( (string) $n['line'] )
				. ( $n['count'] > 1 ? ' — ' . remotive_e( (string) $n['count'] ) . '&times;' : '' )
				. '</small></li>';
		}
		echo '</ol><p><em>' . remotive_e( 'Visitors saw none of this. Full detail is in the PHP error log.' ) . '</em></p></div>';
		return;
	}

	// Front-end: a collapsed panel, so it never obscures the page being
	// reviewed and never appears for anyone but an administrator.
	?>
<style>
  .rm-diag{ position:fixed; right:1rem; bottom:1rem; z-index:99999; max-width:min(34rem,calc(100vw - 2rem));
    font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:.78rem; }
  .rm-diag summary{ cursor:pointer; list-style:none; background:#1e1e1e; color:#fff;
    padding:.6rem .9rem; border-radius:6px; font-weight:700; box-shadow:0 4px 16px rgba(0,0,0,.22); }
  .rm-diag summary::-webkit-details-marker{ display:none; }
  .rm-diag[open] summary{ border-radius:6px 6px 0 0; }
  .rm-diag__body{ background:#fff; color:#1e1e1e; border:1px solid rgba(30,30,30,.16);
    border-top:0; border-radius:0 0 6px 6px; padding:.85rem 1rem; max-height:50vh; overflow:auto;
    box-shadow:0 4px 16px rgba(0,0,0,.22); }
  .rm-diag__item{ padding:.5rem 0; border-bottom:1px solid rgba(30,30,30,.1); }
  .rm-diag__item:last-child{ border-bottom:0; }
  .rm-diag__type{ color:#ff449f; font-weight:700; }
  .rm-diag__loc{ color:rgba(30,30,30,.6); }
</style>
<details class="rm-diag">
  <summary><?php printf( remotive_e( '%1$d PHP issue(s) suppressed' ), count( $notices ) ); ?></summary>
  <div class="rm-diag__body">
	<?php foreach ( $notices as $n ) : ?>
	  <div class="rm-diag__item">
		<span class="rm-diag__type"><?php echo remotive_e( $n['type'] ); ?></span>
		<?php echo remotive_e( $n['message'] ); ?>
		<div class="rm-diag__loc">
		  <?php echo remotive_e( remotive_relative_path( $n['file'] ) ); ?> line <?php echo remotive_e( (string) $n['line'] ); ?>
		  <?php echo $n['count'] > 1 ? ' — ' . remotive_e( (string) $n['count'] ) . '&times;' : ''; ?>
		</div>
	  </div>
	<?php endforeach; ?>
	<p style="margin:.6rem 0 0;color:rgba(30,30,30,.6)">
	  <?php echo remotive_e( 'Only administrators see this. Visitors saw nothing, and the page sent its headers normally.' ); ?>
	</p>
  </div>
</details>
	<?php
}

// Registered only when the takeover is enabled, so the filter genuinely
// hands control back to WordPress rather than leaving both active.
if ( apply_filters( 'remotive_use_custom_error_page', true ) ) {
	set_exception_handler( 'remotive_handle_exception' );
	register_shutdown_function( 'remotive_handle_shutdown' );
}

// Non-fatal capture is registered as early as this file loads, which is
// before every other theme include, so anything they emit is caught. Errors
// raised before the theme loads at all (during WordPress core or plugin
// bootstrap) are outside any theme's reach and still need display_errors
// off in wp-config.php.
if ( remotive_graceful_errors_enabled() ) {
	// The return value is the handler that was already in place, kept so
	// remotive_collect_php_notice() can pass diagnostics along to it.
	$GLOBALS['remotive_prev_error_handler'] = set_error_handler( 'remotive_collect_php_notice' );

	if ( function_exists( 'add_action' ) ) {
		add_action( 'admin_notices', function () {
			remotive_render_php_notices( true );
		} );
		add_action( 'wp_footer', function () {
			remotive_render_php_notices( false );
		}, 999 );
	}
}
