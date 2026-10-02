/* Language switcher for the one-page template: ?lang=, then the saved
   choice, then the browser language, falling back to English. */
(function () {
	var root = document.querySelector('.rm-op');
	if (!root) { return; }
	var langs = { en: 'en', ms: 'ms', zh: 'zh-Hans' };
	var buttons = root.querySelectorAll('[data-set-lang]');

	function pick() {
		var q = /[?&]lang=(en|ms|zh)\b/.exec(location.search);
		if (q) { return q[1]; }
		try {
			var s = localStorage.getItem('remotive-lang');
			if (langs[s]) { return s; }
		} catch (e) {}
		var n = (navigator.language || 'en').toLowerCase();
		if (n.indexOf('ms') === 0 || n.indexOf('id') === 0) { return 'ms'; }
		if (n.indexOf('zh') === 0) { return 'zh'; }
		return 'en';
	}

	function apply(l, save) {
		root.setAttribute('data-lang', l);
		document.documentElement.setAttribute('lang', langs[l]);
		for (var i = 0; i < buttons.length; i++) {
			buttons[i].setAttribute('aria-pressed', buttons[i].getAttribute('data-set-lang') === l ? 'true' : 'false');
		}
		if (save) { try { localStorage.setItem('remotive-lang', l); } catch (e) {} }
	}

	for (var i = 0; i < buttons.length; i++) {
		buttons[i].addEventListener('click', function () { apply(this.getAttribute('data-set-lang'), true); });
	}
	apply(pick(), false);
})();
