/**
 * Remotive front-end bundle.
 *
 * The colour-mode toggle, the mobile navigation fallback and the ticker
 * pause, concatenated into one file. They were three separate requests,
 * each a kilobyte or two, and every one of them was arriving uncached on
 * the audited install: three round trips to deliver less than 10KB. Each
 * part still guards on its own markup, so a page without a ticker or
 * without a navigation block runs only what applies to it.
 *
 * Edit the parts in this file directly. They are kept in source order with
 * their original comments intact; nothing is minified, so this file stays
 * readable and diffable.
 *
 * @package Remotive
 */

/* ==========================================================================
   Colour mode toggle  (was assets/js/theme-toggle.js)
   ========================================================================== */

/**
 * Light/Dark toggle for the "Registration" (dark) direction vs the
 * Remotive Media Asia brand system (light, Saira). Persists the visitor's
 * own choice once they use the toggle. Before that, the mode shown comes
 * from window.remotiveThemeOptions.defaultTheme — set server-side from
 * Appearance -> Remotive Options — falling back to 'dark' if that variable
 * is missing for any reason (e.g. the inline script failed to enqueue).
 */
(function () {
	var STORAGE_KEY = 'remotive-theme';
	var root = document.documentElement;
	var btn = document.getElementById('rmThemeToggle');
	var label = document.getElementById('rmThemeToggleLabel');

	var options = window.remotiveThemeOptions || {};
	var configuredDefault = options.defaultTheme || 'dark';

	function resolveDefault() {
		if (configuredDefault === 'system') {
			var prefersLight = window.matchMedia &&
				window.matchMedia('(prefers-color-scheme: light)').matches;
			return prefersLight ? 'light' : 'dark';
		}
		return configuredDefault === 'light' ? 'light' : 'dark';
	}

	function apply(mode) {
		root.setAttribute('data-theme', mode === 'light' ? 'light' : 'dark');
		if (btn) {
			btn.setAttribute('aria-pressed', mode === 'light' ? 'true' : 'false');
		}
		if (label) {
			label.textContent = mode === 'light' ? 'Light' : 'Dark';
		}
	}

	var saved = null;
	try {
		saved = localStorage.getItem(STORAGE_KEY);
	} catch (e) {
		// localStorage unavailable (privacy mode, etc.) -- fall through to default.
	}
	apply(saved === 'light' || saved === 'dark' ? saved : resolveDefault());

	if (btn) {
		btn.addEventListener('click', function () {
			var next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
			apply(next);
			try {
				localStorage.setItem(STORAGE_KEY, next);
			} catch (e) {
				// Ignore -- the toggle still works for this page view.
			}
		});
	}
})();


/* ==========================================================================
   Mobile navigation fallback  (was assets/js/nav-fallback.js)
   ========================================================================== */

/**
 * Mobile navigation fallback.
 *
 * The header relies on the core Navigation block's overlay menu below
 * 600px. On sites where that overlay does not engage, and one has been
 * observed in the wild, the links either run down the page through the
 * logo or, once the theme's safety net hides them, disappear entirely.
 * Either way the visitor has no navigation on a phone.
 *
 * This script does nothing when core's overlay is working. It checks for a
 * functioning open button and only when none is found builds an equivalent
 * one: a labelled toggle that shows and hides the existing list in place.
 * No markup is replaced, so if core starts working after a plugin change
 * the fallback simply stops activating.
 */
( function () {
	'use strict';

	var BREAKPOINT = 600;
	var nav = document.querySelector( '.rm-nav__links' );

	if ( ! nav ) {
		return;
	}

	var list = nav.querySelector( '.wp-block-navigation__container' );

	if ( ! list ) {
		return;
	}

	/**
	 * Whether the core Navigation block's own overlay is actually usable.
	 *
	 * Checking for the open button is not enough: when core's navigation
	 * stylesheet is missing, the button still renders, as a small unstyled
	 * box, and the overlay it points at does nothing. So the test is
	 * whether core's stylesheet is present at all, probed by asking the
	 * browser what an element in the overlay's open state computes to.
	 * Core positions that container fixed; with no core stylesheet it stays
	 * static. The probe is inserted hidden, measured, and removed in the
	 * same frame.
	 */
	var overlayCache = null;

	function coreOverlayWorks() {
		// Cached: the probe inserts an element and reads a computed style,
		// which forces a style recalculation. Doing that on every resize
		// and twice per pass showed up as forced reflow in the field. The
		// answer cannot change after the stylesheets have settled, so it
		// is measured once and reused; window load clears it for a single
		// re-check in case a stylesheet arrived late.
		if ( null !== overlayCache ) {
			return overlayCache;
		}

		var open = nav.querySelector( '.wp-block-navigation__responsive-container-open' );

		if ( ! open || ! open.getClientRects().length ) {
			overlayCache = false;

			return false;
		}

		var probe = document.createElement( 'div' );

		probe.className = 'wp-block-navigation__responsive-container is-menu-open';
		probe.setAttribute( 'aria-hidden', 'true' );
		probe.style.visibility = 'hidden';
		probe.style.pointerEvents = 'none';
		document.body.appendChild( probe );

		var positioned = 'fixed' === window.getComputedStyle( probe ).position;

		document.body.removeChild( probe );
		overlayCache = positioned;

		return positioned;
	}

	var toggle = null;

	function buildToggle() {
		if ( toggle ) {
			return toggle;
		}

		toggle = document.createElement( 'button' );
		toggle.type = 'button';
		toggle.className = 'rm-nav__fallback-toggle';
		toggle.setAttribute( 'aria-expanded', 'false' );
		toggle.setAttribute( 'aria-controls', listId() );
		toggle.setAttribute( 'aria-label', navLabel() );
		toggle.innerHTML = '<span class="rm-nav__fallback-bars" aria-hidden="true"></span>';

		toggle.addEventListener( 'click', function () {
			setOpen( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
		} );

		nav.insertBefore( toggle, nav.firstChild );

		return toggle;
	}

	function listId() {
		if ( ! list.id ) {
			list.id = 'rm-nav-fallback-list';
		}

		return list.id;
	}

	function navLabel() {
		// Reuse the block's own accessible name when it has one, so the
		// fallback button is announced the same way core's would be.
		return nav.getAttribute( 'aria-label' ) || 'Menu';
	}

	function setOpen( open ) {
		nav.classList.toggle( 'is-fallback-open', open );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );

		if ( open ) {
			var first = list.querySelector( 'a' );

			if ( first ) {
				first.focus();
			}
		}
	}

	function active() {
		return window.innerWidth <= BREAKPOINT && ! coreOverlayWorks();
	}

	// window load re-checks once, in case a stylesheet arrived after the
	// first pass; after that the cached answer stands.
	function recheckOnce() {
		overlayCache = null;
		sync();
	}

	/**
	 * Mark the nav when core's stylesheet is missing entirely.
	 *
	 * Core hides its overlay open and close buttons above the overlay
	 * breakpoint. Without that stylesheet both render as small empty boxes
	 * bracketing the desktop links, so the class below lets CSS hide them
	 * at every width, not only where the fallback menu takes over.
	 */
	function flagMissingCoreStyles() {
		var missing = ! coreOverlayWorks();

		nav.classList.toggle( 'has-no-core-nav-css', missing );
	}

	/**
	 * Publish the header's height so the panel can sit directly beneath it.
	 *
	 * The panel is fixed to the viewport, so it needs a real measurement
	 * rather than a percentage of an ancestor. Measured from the header
	 * element, which is what the visitor sees as the bar.
	 */
	var lastHeight = 0;

	function publishHeaderHeight() {
		var header = nav.closest( '.rm-nav' ) || nav.parentElement;

		if ( ! header ) {
			return;
		}

		// Read in an animation frame, and write only when the value has
		// actually changed, so a resize does not force a synchronous
		// layout on every event.
		window.requestAnimationFrame( function () {
			var height = Math.round( header.getBoundingClientRect().height );

			if ( height > 0 && height !== lastHeight ) {
				lastHeight = height;
				document.documentElement.style.setProperty( '--rm-nav-height', height + 'px' );
			}
		} );
	}

	function sync() {
		flagMissingCoreStyles();

		if ( active() ) {
			publishHeaderHeight();

			buildToggle();
			nav.classList.add( 'has-fallback-nav' );

			// Core's own button is inert without its stylesheet and reads as
			// a stray empty box beside the working one, so it is hidden
			// while the fallback is in charge.
			var stray = nav.querySelector( '.wp-block-navigation__responsive-container-open' );

			if ( stray ) {
				stray.hidden = true;
			}
		} else if ( toggle ) {
			nav.classList.remove( 'has-fallback-nav', 'is-fallback-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );

			var restored = nav.querySelector( '.wp-block-navigation__responsive-container-open' );

			if ( restored ) {
				restored.hidden = false;
			}
		}
	}

	// Escape closes, matching the overlay's own behaviour.
	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key && nav.classList.contains( 'is-fallback-open' ) ) {
			setOpen( false );
			toggle.focus();
		}
	} );

	// Following a link should not leave the menu open behind the new page
	// on a browser that restores scroll position.
	list.addEventListener( 'click', function ( event ) {
		if ( event.target.closest( 'a' ) && nav.classList.contains( 'is-fallback-open' ) ) {
			setOpen( false );
		}
	} );

	var resizeTimer = null;

	window.addEventListener( 'resize', function () {
		window.clearTimeout( resizeTimer );
		resizeTimer = window.setTimeout( sync, 150 );
	} );

	// Core's stylesheet may still be arriving, so re-check once the page has
	// fully loaded before deciding the overlay is broken.
	sync();
	window.addEventListener( 'load', recheckOnce, { once: true } );
}() );


/* ==========================================================================
   Ticker pause when off-screen  (was assets/js/ticker.js)
   ========================================================================== */

/**
 * Pauses the scrolling ticker strip's CSS animation while it's off-screen.
 * The animation itself costs nothing visible when out of view, but most
 * browsers keep compositing/repainting a running animation regardless of
 * viewport visibility unless it's explicitly paused — a small, real
 * performance saving on longer sessions, not a visible behaviour change.
 *
 * Degrades safely: if IntersectionObserver isn't available (very old
 * browsers), the ticker just keeps animating continuously, exactly as it
 * did before this file existed.
 */
(function () {
	if (!('IntersectionObserver' in window)) {
		return;
	}

	var ticker = document.querySelector('.rm-ticker');
	var track = document.querySelector('.rm-ticker__track');
	if (!ticker || !track) {
		return;
	}

	var observer = new IntersectionObserver(function (entries) {
		entries.forEach(function (entry) {
			track.classList.toggle('is-paused', !entry.isIntersecting);
		});
	});

	observer.observe(ticker);
})();

/* ---------------------------------------------------------------------
   Scroll reveal fallback  (v1.69.1)

   Only runs when the browser lacks CSS scroll-driven animations. Where
   animation-timeline is supported the CSS handles everything on the
   compositor thread and this block exits immediately, so modern browsers
   pay nothing for it.

   No scroll listener is used in either path. IntersectionObserver fires
   off the main thread's rendering steps rather than on every scroll tick,
   and each element is unobserved once it has been revealed, so the work
   is bounded by the number of elements rather than the length of the
   scroll.

   The .rm-reveal class that hides an element is added here, by script.
   The templates never carry it. That ordering matters: if this script
   fails to run, or JavaScript is off entirely, nothing is ever hidden and
   the page reads normally without motion.
   --------------------------------------------------------------------- */
(function () {
	// The CSS path is in charge wherever it is supported.
	if (window.CSS && CSS.supports && CSS.supports('animation-timeline', 'view()')) {
		return;
	}

	// Motion is opt-in via the body class, same gate as the CSS.
	if (!document.body || !document.body.classList.contains('rm-motion')) {
		return;
	}

	// Respect the OS setting before doing any work at all.
	var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)');
	if (reduced && reduced.matches) {
		return;
	}

	if (!('IntersectionObserver' in window)) {
		return;
	}

	var selector = [
		'.rm-section-head',
		'.rm-section-lead',
		'.rm-plate',
		'.rm-block',
		'.rm-post-card',
		'.rm-feature',
		'.rm-row',
		'.rm-team__member',
		'.rm-sidebar__card',
		// Kept in step with the CSS selector list in remotive.css. A card
		// type present in one path and missing from the other reveals
		// inconsistently depending on the browser.
		'.rm-service-grid .rm-service-detail',
		'.rm-stat',
		'.rm-cta__title'
	].join(',');

	var targets = document.querySelectorAll(selector);
	if (!targets.length) {
		return;
	}

	var observer = new IntersectionObserver(function (entries) {
		entries.forEach(function (entry) {
			if (!entry.isIntersecting) {
				return;
			}
			entry.target.classList.add('is-in');
			// One-shot: an element that has been revealed never needs
			// watching again.
			observer.unobserve(entry.target);
		});
	}, {
		// Start the transition slightly before the element reaches the
		// viewport edge so it is finishing, not starting, as it arrives.
		rootMargin: '0px 0px -8% 0px',
		threshold: 0.06
	});

	Array.prototype.forEach.call(targets, function (el) {
		// Anything already on screen at load is shown immediately without
		// a transition, so the first viewport never animates in on arrival.
		var rect = el.getBoundingClientRect();
		if (rect.top < window.innerHeight && rect.bottom > 0) {
			return;
		}
		el.classList.add('rm-reveal');
		observer.observe(el);
	});
})();
