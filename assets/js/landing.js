/* Service landing pages: language switch, campaign capture, funnel events. */
(function () {
	var root = document.querySelector('.rm-lp');
	if (!root) { return; }

	var langs = { en: 'en', ms: 'ms', zh: 'zh-Hans', zht: 'zh-Hant' };
	var aliases = { 'zh-cn': 'zh', 'zh-hans': 'zh', 'zh-tw': 'zht', 'zh-hk': 'zht', 'zh-hant': 'zht' };
	var buttons = root.querySelectorAll('[data-set-lang]');

	function pick() {
		var q = /[?&]lang=([A-Za-z-]+)/.exec(location.search);
		if (q) {
			var want = q[1].toLowerCase();
			want = aliases[want] || want;
			if (langs[want]) { return want; }
		}
		try {
			var s = localStorage.getItem('remotive-lang');
			if (langs[s]) { return s; }
		} catch (e) {}
		var n = (navigator.language || 'en').toLowerCase();
		if (n.indexOf('ms') === 0 || n.indexOf('id') === 0) { return 'ms'; }
		if (n.indexOf('zh') === 0) {
			// Traditional for Taiwan, Hong Kong, Macau or an explicit Hant tag.
			return /^zh-(tw|hk|mo|hant)/.test(n) ? 'zht' : 'zh';
		}
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
		buttons[i].addEventListener('click', function () {
			var l = this.getAttribute('data-set-lang');
			apply(l, true);
			// Keep the address in step with the language (and keep any utm_*),
			// so a copied or shared link opens in the same language.
			try {
				var u = new URL(location.href);
				u.searchParams.set('lang', l);
				history.replaceState({}, '', u.toString());
			} catch (e) {}
		});
	}
	apply(pick(), false);

	// Campaign fields (utm_*, gclid, fbclid, ttclid): taken from the landing
	// URL, kept for the session so a reload or a language switch does not lose
	// them, and sent with the lead. The saved set belongs to one landing page:
	// another service's page ignores it, and a URL that carries any campaign
	// field starts a fresh set, so an older click can never be attributed to a
	// newer lead.
	var service = root.getAttribute('data-service');
	var fields = root.querySelectorAll('input[data-track]');
	var params = {};
	try {
		var saved = JSON.parse(sessionStorage.getItem('remotive-lp-track') || '{}') || {};
		if (saved.service === service && saved.data) { params = saved.data; }
	} catch (e) {}
	try {
		var sp = new URLSearchParams(location.search);
		var fresh = {};
		var any = false;
		for (var j = 0; j < fields.length; j++) {
			var k = fields[j].getAttribute('data-track');
			if (sp.get(k)) { fresh[k] = sp.get(k); any = true; }
		}
		if (any) { params = fresh; }
		sessionStorage.setItem('remotive-lp-track', JSON.stringify({ service: service, data: params }));
	} catch (e) {}
	for (var m = 0; m < fields.length; m++) {
		var key = fields[m].getAttribute('data-track');
		if (params[key]) { fields[m].value = params[key]; }
	}

	// Funnel events for a tag manager. The conversion itself fires on the
	// thank-you page (inc/thank-you.php).
	window.dataLayer = window.dataLayer || [];
	window.dataLayer.push({ event: 'remotive_lp_view', service: service });

	var started = false;
	root.addEventListener('focusin', function (e) {
		if (!started && e.target.closest && e.target.closest('.rm-lp__form')) {
			started = true;
			window.dataLayer.push({ event: 'remotive_lp_form_start', service: service });
		}
	});

	// Sticky CTA: shown while the hero form is out of view.
	var sticky = root.querySelector('.rm-lp__sticky');
	var forms = root.querySelectorAll('.rm-lp__card');
	if (sticky && forms.length && 'IntersectionObserver' in window) {
		var inView = {};
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) { inView[en.target.getAttribute('data-i')] = en.isIntersecting; });
			var any = Object.keys(inView).some(function (k) { return inView[k]; });
			sticky.classList.toggle('is-on', !any);
		});
		for (var g = 0; g < forms.length; g++) { forms[g].setAttribute('data-i', g); }
		for (var f = 0; f < forms.length; f++) { io.observe(forms[f]); }
	}
})();
