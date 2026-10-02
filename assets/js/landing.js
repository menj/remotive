/* Service landing pages: campaign capture, funnel events, sticky CTA.
   Language is chosen by URL (/ms/, /zh-cn/, /zh-tw/), not by script. */
(function () {
	var root = document.querySelector('.rm-lp');
	if (!root) { return; }

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

	// Language links keep the campaign parameters, so switching language does
	// not lose the ad click.
	var qs = Object.keys(params).filter(function (k) { return params[k]; }).map(function (k) {
		return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
	}).join('&');
	if (qs) {
		var langLinks = root.querySelectorAll('.rm-lp__lang');
		for (var n = 0; n < langLinks.length; n++) {
			langLinks[n].href += (langLinks[n].href.indexOf('?') < 0 ? '?' : '&') + qs;
		}
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
