/**
 * Remotive Options — tab switching.
 *
 * Implements the WAI-ARIA Authoring Practices "Tabs" pattern: click or
 * arrow-key/Home/End to move focus between tabs, Enter/Space (native
 * button behaviour) to activate. Roving tabindex — only the active tab
 * is in the normal tab order, matching the spec.
 *
 * All fields across all tabs still submit together in one <form> POST to
 * options.php — this is purely a client-side display toggle, not
 * separate forms. If JavaScript fails to load, every panel's CSS default
 * is "display:none" except the first, via the "is-active" class already
 * present in the server-rendered HTML — so the Contact tab's fields are
 * still visible and submittable with JS disabled, even though the other
 * two tabs' fields would be hidden. Documented as a known limitation
 * rather than solved with a no-JS fallback, since editing every field
 * without JS is an edge case not worth the complexity here.
 */
(function () {
	'use strict';

	var STORAGE_KEY = 'remotive-admin-active-tab';

	function init() {
		var tablist = document.querySelector('.rm-admin__tabs');
		if (!tablist) {
			return;
		}
		var tabs = Array.prototype.slice.call(tablist.querySelectorAll('[role="tab"]'));
		if (!tabs.length) {
			return;
		}

		function panelFor(tab) {
			return document.getElementById(tab.getAttribute('aria-controls'));
		}

		function activate(tab, focus) {
			tabs.forEach(function (t) {
				var selected = t === tab;
				t.setAttribute('aria-selected', selected ? 'true' : 'false');
				t.tabIndex = selected ? 0 : -1;
				var panel = panelFor(t);
				if (panel) {
					panel.classList.toggle('is-active', selected);

					// The Site setup and Enquiries panels are not settings
					// fields — they manage their own state through their own
					// forms — so the settings form's Save button has nothing
					// to act on while either is showing.
					if (selected) {
						var submit = document.querySelector('.rm-admin__submit');
						if (submit) {
							submit.hidden = panel.id === 'remotive_panel_setup' ||
								panel.id === 'remotive_panel_enquiries';
						}
					}
				}
			});
			if (focus) {
				tab.focus();
			}
			try {
				sessionStorage.setItem(STORAGE_KEY, tab.id);
			} catch (e) {
				// Ignore — tab restore on reload just won't persist this time.
			}
		}

		tabs.forEach(function (tab, index) {
			tab.addEventListener('click', function () {
				activate(tab, false);
			});

			tab.addEventListener('keydown', function (event) {
				var newIndex = null;
				if (event.key === 'ArrowRight') {
					newIndex = (index + 1) % tabs.length;
				} else if (event.key === 'ArrowLeft') {
					newIndex = (index - 1 + tabs.length) % tabs.length;
				} else if (event.key === 'Home') {
					newIndex = 0;
				} else if (event.key === 'End') {
					newIndex = tabs.length - 1;
				}
				if (newIndex !== null) {
					event.preventDefault();
					activate(tabs[newIndex], true);
				}
			});
		});

		// Restore whichever tab was active before the last form submit/reload.
		var restoreId = null;
		try {
			restoreId = sessionStorage.getItem(STORAGE_KEY);
		} catch (e) {
			// Ignore — just falls back to whichever tab the server marked active.
		}
		if (restoreId) {
			var restoreTab = document.getElementById(restoreId);
			if (restoreTab) {
				activate(restoreTab, false);
			}
		}
	}

	// Defensive belt-and-suspenders active-state class for the visual
	// selector cards, alongside the CSS-only :has() styling — keeps the
	// active card visually correct even in the (now rare) case of a
	// browser without :has() support.
	function initVisualSelectors() {
		var radios = document.querySelectorAll('.rm-admin__visual-card input[type="radio"]');
		Array.prototype.forEach.call(radios, function (radio) {
			radio.addEventListener('change', function () {
				var name = radio.getAttribute('name');
				var group = document.querySelectorAll('.rm-admin__visual-card input[name="' + CSS.escape(name) + '"]');
				Array.prototype.forEach.call(group, function (r) {
					r.closest('.rm-admin__visual-card').classList.toggle('is-active', r.checked);
				});
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			init();
			initVisualSelectors();
		});
	} else {
		init();
		initVisualSelectors();
	}
})();


/* Team repeater: Add member clones the hidden template row with a fresh
   index; Remove drops the row from the form, so the sanitizer never sees
   it and the member is deleted on save. */
(function () {
	var wrap = document.getElementById('rmTeamRepeater');
	if (!wrap) { return; }

	var addBtn = document.getElementById('rmTeamAdd');
	var template = wrap.querySelector('.rm-admin__team-row--template');

	function nextIndex() {
		var rows = wrap.querySelectorAll('.rm-admin__team-row:not(.rm-admin__team-row--template)');
		var max = -1;
		rows.forEach(function (row) {
			var input = row.querySelector('input[name*="[team]["]');
			if (!input) { return; }
			var m = input.name.match(/\[team\]\[(\d+)\]/);
			if (m) { max = Math.max(max, parseInt(m[1], 10)); }
		});
		return max + 1;
	}

	if (addBtn && template) {
		addBtn.addEventListener('click', function () {
			var row = template.cloneNode(true);
			var idx = String(nextIndex());
			row.classList.remove('rm-admin__team-row--template');
			row.disabled = false;
			row.hidden = false;
			row.querySelectorAll('input, textarea').forEach(function (field) {
				field.name = field.name.replace('__INDEX__', idx);
			});
			wrap.insertBefore(row, addBtn.closest('p'));
			var first = row.querySelector('input');
			if (first) { first.focus(); }
		});
	}

	wrap.addEventListener('click', function (event) {
		var btn = event.target.closest('.rm-admin__team-remove');
		if (!btn) { return; }
		var row = btn.closest('.rm-admin__team-row');
		if (row && !row.classList.contains('rm-admin__team-row--template')) {
			row.remove();
		}
	});
})();

/**
 * Colours tab: live contrast check.
 *
 * Each row of a contrast table names its foreground and background by the id
 * of a colour picker (or a fixed #hex for a colour the stylesheet hard-codes).
 * On any change, the ratio is recomputed with the WCAG formula and the row is
 * marked Good or Low. The server renders the same table from the saved values,
 * so it is right before this runs; the same check runs again on save.
 */
(function () {
	'use strict';

	function luminance(hex) {
		hex = hex.replace('#', '');
		if (hex.length === 3) {
			hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
		}
		var rgb = [0, 2, 4].map(function (i) {
			var c = parseInt(hex.substr(i, 2), 16) / 255;
			return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
		});
		return 0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2];
	}

	function contrast(a, b) {
		var la = luminance(a);
		var lb = luminance(b);
		return (Math.max(la, lb) + 0.05) / (Math.min(la, lb) + 0.05);
	}

	function colourOf(ref) {
		if (ref.charAt(0) === '#' && /^#[0-9a-f]{3,6}$/i.test(ref)) {
			return ref;
		}
		var input = document.querySelector(ref.replace(/^#/, '#'));
		return input ? input.value : '#000000';
	}

	function refresh() {
		Array.prototype.forEach.call(document.querySelectorAll('.rm-admin__contrast tr'), function (row) {
			var fg = colourOf(row.getAttribute('data-fg'));
			var bg = colourOf(row.getAttribute('data-bg'));
			var ratio = contrast(fg, bg);
			var ok = ratio >= parseFloat(row.getAttribute('data-min'));
			var swatch = row.querySelector('.rm-admin__swatch');
			if (swatch) {
				swatch.style.background = bg;
				swatch.style.color = fg;
			}
			row.querySelector('.rm-admin__ratio').textContent = ratio.toFixed(2) + ':1';
			row.querySelector('.rm-admin__verdict').textContent = ok ? row.getAttribute('data-good') || 'Good' : row.getAttribute('data-bad') || 'Low';
			row.classList.toggle('is-ok', ok);
			row.classList.toggle('is-low', !ok);
		});
	}

	if (!document.querySelector('.rm-admin__contrast')) {
		return;
	}

	document.addEventListener('input', function (event) {
		if (event.target && event.target.type === 'color') {
			refresh();
		}
	});
	refresh();
})();
