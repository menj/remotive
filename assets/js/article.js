/**
 * Article page: reading progress, contents list, share buttons.
 * Loaded only on single posts (functions.php). Everything is progressive:
 * without the script the page reads normally, just without these extras.
 */
( function () {
	'use strict';

	var body = document.querySelector( '.rm-art-body .rm-post__content' );
	if ( ! body ) {
		return;
	}

	/* ---- Reading progress bar ---- */
	var bar = document.querySelector( '.rm-art-progress span' );
	var ticking = false;

	function progress() {
		var rect = body.getBoundingClientRect();
		var total = rect.height - window.innerHeight * 0.5;
		var done = Math.min( Math.max( -rect.top + window.innerHeight * 0.25, 0 ), Math.max( total, 1 ) );
		if ( bar ) {
			bar.style.transform = 'scaleX(' + ( done / Math.max( total, 1 ) ).toFixed( 4 ) + ')';
		}
		ticking = false;
	}

	window.addEventListener( 'scroll', function () {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( progress );
		}
	}, { passive: true } );
	window.addEventListener( 'resize', progress );
	progress();

	/* ---- Contents list from the h2 headings ---- */
	var toc = document.querySelector( '.rm-art-toc' );
	var heads = Array.prototype.slice.call( body.querySelectorAll( 'h2' ) );

	if ( toc && heads.length > 2 ) {
		var used = {};
		var title = document.createElement( 'p' );
		title.className = 'rm-art-toc__title';
		title.textContent = 'In this article';
		var list = document.createElement( 'ol' );

		heads.forEach( function ( h, i ) {
			var id = h.id || ( h.textContent || '' ).toLowerCase().replace( /[^a-z0-9]+/g, '-' ).replace( /^-+|-+$/g, '' ).slice( 0, 48 ) || 'section-' + ( i + 1 );
			while ( used[ id ] || ( document.getElementById( id ) && document.getElementById( id ) !== h ) ) {
				id += '-' + ( i + 1 );
			}
			used[ id ] = true;
			h.id = id;

			var li = document.createElement( 'li' );
			var a = document.createElement( 'a' );
			a.href = '#' + id;
			a.textContent = h.textContent;
			li.appendChild( a );
			list.appendChild( li );
		} );

		toc.appendChild( title );
		toc.appendChild( list );
		toc.hidden = false;

		if ( 'IntersectionObserver' in window ) {
			var links = toc.querySelectorAll( 'a' );
			var io = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( e ) {
					if ( e.isIntersecting ) {
						links.forEach( function ( l ) {
							l.classList.toggle( 'is-active', l.getAttribute( 'href' ) === '#' + e.target.id );
						} );
					}
				} );
			}, { rootMargin: '-15% 0px -70% 0px' } );
			heads.forEach( function ( h ) { io.observe( h ); } );
		}
	}

	/* ---- Share buttons ---- */
	var url = ( document.querySelector( 'link[rel="canonical"]' ) || {} ).href || window.location.href.split( '#' )[ 0 ];
	var text = document.title;

	document.querySelectorAll( '[data-share]' ).forEach( function ( el ) {
		var kind = el.getAttribute( 'data-share' );

		if ( 'whatsapp' === kind ) {
			el.href = 'https://wa.me/?text=' + encodeURIComponent( text + ' ' + url );
		} else if ( 'linkedin' === kind ) {
			el.href = 'https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent( url );
		} else if ( 'x' === kind ) {
			el.href = 'https://x.com/intent/post?text=' + encodeURIComponent( text ) + '&url=' + encodeURIComponent( url );
		} else if ( 'copy' === kind ) {
			el.addEventListener( 'click', function () {
				var old = el.textContent;
				var say = function ( msg ) {
					el.textContent = msg;
					window.setTimeout( function () { el.textContent = old; }, 2200 );
				};
				// Older browsers and non-secure pages have no Clipboard API; fall back to a
				// hidden field and execCommand, and say so if that fails too.
				var legacy = function () {
					var f = document.createElement( 'textarea' );
					f.value = url;
					f.setAttribute( 'readonly', '' );
					f.style.cssText = 'position:fixed;top:0;left:0;opacity:0';
					document.body.appendChild( f );
					f.select();
					var ok = false;
					try { ok = document.execCommand( 'copy' ); } catch ( e ) { ok = false; }
					document.body.removeChild( f );
					say( ok ? 'Copied' : 'Press Ctrl+C to copy' );
					if ( ! ok ) { window.prompt( 'Copy this link', url ); }
				};
				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( url ).then( function () { say( 'Copied' ); }, legacy );
				} else {
					legacy();
				}
			} );
		}
	} );
}() );
