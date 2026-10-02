/**
 * Shows a status message after any native lead form's admin-post.php
 * redirect (inc/lead-form-handler.php) sends the visitor back with a
 * `?remotive_<form_key>=success|error` query param attached. Shared by
 * every form this theme has — each one just needs a status element with
 * `data-lead-status="remotive_<form_key>"` matching its form_key (see
 * `role="status" aria-live="polite"` already on those elements in the
 * templates, so screen readers announce the message without this script
 * needing to manage focus — WCAG 4.1.3). The query string is stripped
 * via history.replaceState() after reading it, so refreshing the page
 * doesn't re-show a stale message.
 */
(function () {
	var params;
	try {
		params = new URLSearchParams(window.location.search);
	} catch (e) {
		return;
	}

	var statusEls = document.querySelectorAll('[data-lead-status]');
	if (!statusEls.length) {
		return;
	}

	var messages = {
		success: "Thanks — we'll be in touch within three business days.",
		error: 'Something went wrong sending that. Please try again, or email us directly.'
	};

	var consumedKey = null;

	Array.prototype.forEach.call(statusEls, function (el) {
		var key = el.getAttribute('data-lead-status');
		var result = params.get(key);
		if (result !== 'success' && result !== 'error') {
			return;
		}
		el.setAttribute('data-state', result);
		if (el.querySelector('[data-msg]')) {
			// Pre-rendered, translated messages: CSS shows the one matching
			// the state and the page language.
			el.removeAttribute('hidden');
		} else {
			el.textContent = messages[result];
		}
		consumedKey = key;
	});

	if (consumedKey) {
		try {
			var url = new URL(window.location.href);
			url.searchParams.delete(consumedKey);
			window.history.replaceState({}, '', url.toString());
		} catch (e) {
			// Non-fatal — the message still shows, the URL just keeps the param.
		}
	}
})();
