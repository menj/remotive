/**
 * Accessible lightbox for the About page's gallery grid.
 *
 * Deliberately built from scratch rather than adapted from any
 * third-party template's lightbox script — most off-the-shelf gallery
 * lightboxes (including the ones in the html5up templates this page's
 * layout was inspired by) are mouse-only: no keyboard path to open them,
 * no focus trap once open, no Escape to close, no focus returned to the
 * trigger on close. All four are handled here:
 *
 * - Trigger elements are real <button>s (native keyboard operability,
 *   no role/tabindex workarounds needed).
 * - role="dialog" + aria-modal="true" on the lightbox (already in the
 *   template); focus moves into it on open, is trapped there via a
 *   Tab/Shift+Tab handler, and returns to whichever gallery button
 *   opened it on close.
 * - Escape closes; Left/Right arrows navigate between images.
 * - No dependency on any external library.
 */
(function () {
	var gallery = document.querySelectorAll('.rm-gallery__tile');
	var lightbox = document.getElementById('rmLightbox');
	if (!gallery.length || !lightbox) {
		return;
	}

	var stage = document.getElementById('rmLightboxStage');
	var caption = document.getElementById('rmLightboxCaption');
	var closeBtn = document.getElementById('rmLightboxClose');
	var prevBtn = document.getElementById('rmLightboxPrev');
	var nextBtn = document.getElementById('rmLightboxNext');

	var tiles = Array.prototype.slice.call(gallery);
	var currentIndex = -1;
	var lastFocused = null;

	function focusableElements() {
		return [closeBtn, prevBtn, nextBtn].filter(function (el) {
			return el && !el.hidden;
		});
	}

	function render(index) {
		var tile = tiles[index];
		// Copies the tile's CSS gradient into the stage — works because
		// the gallery currently uses placeholder gradients, not real
		// photos (see docs/upgrading.md). Once real images replace these
		// tiles, this should read an actual image URL (e.g. a
		// data-full-src attribute) and set stage's background-image (or
		// swap to an <img> element) instead of copying computed CSS.
		stage.style.background = getComputedStyle(tile).background;
		caption.textContent = tile.getAttribute('data-caption') || '';
	}

	function open(index) {
		lastFocused = document.activeElement;
		currentIndex = index;
		render(currentIndex);
		lightbox.hidden = false;
		document.body.style.overflow = 'hidden';
		closeBtn.focus();
		document.addEventListener('keydown', onKeydown);
	}

	function close() {
		lightbox.hidden = true;
		document.body.style.overflow = '';
		document.removeEventListener('keydown', onKeydown);
		if (lastFocused) {
			lastFocused.focus();
		}
	}

	function show(delta) {
		currentIndex = (currentIndex + delta + tiles.length) % tiles.length;
		render(currentIndex);
	}

	function onKeydown(event) {
		if (event.key === 'Escape') {
			event.preventDefault();
			close();
			return;
		}
		if (event.key === 'ArrowLeft') {
			event.preventDefault();
			show(-1);
			return;
		}
		if (event.key === 'ArrowRight') {
			event.preventDefault();
			show(1);
			return;
		}
		if (event.key === 'Tab') {
			// Focus trap: keep Tab/Shift+Tab cycling within the lightbox's
			// own controls rather than escaping into the page behind it.
			var focusable = focusableElements();
			if (!focusable.length) {
				return;
			}
			var first = focusable[0];
			var last = focusable[focusable.length - 1];
			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		}
	}

	tiles.forEach(function (tile, index) {
		tile.addEventListener('click', function () {
			open(index);
		});
	});

	closeBtn.addEventListener('click', close);
	prevBtn.addEventListener('click', function () { show(-1); });
	nextBtn.addEventListener('click', function () { show(1); });

	// Click on the dark overlay (outside the stage/caption/controls) also closes.
	lightbox.addEventListener('click', function (event) {
		if (event.target === lightbox) {
			close();
		}
	});
})();
