/* Service landing pages: campaign capture, funnel events, sticky CTA, and the
   scroll motion fallback for the one-page layout.
   Language is chosen by URL (/ms/, /zh-cn/, /zh-tw/), not by script. */
(function () {
	var root = document.querySelector('.rm-lp');
	if (!root) { return; }

	// The confirmation page has no form and is not a landing view: the
	// conversion event comes from the server (inc/forms/thank-you.php).
	if (root.getAttribute('data-page') === 'thanks') { return; }

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
	// thank-you page (inc/forms/thank-you.php).
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

	// Scroll motion fallback for the one-page layout (assets/css/agency-motion.css).
	// The stylesheet runs the movement on scroll timelines; a browser without them
	// gets the same from here: the plates and the numbers follow the scroll at
	// their data-depth rate, the hero copy lifts over the first screen, and the
	// rising elements get .is-in as they enter.
	// Nothing runs without the Theme Options motion class, or for a visitor who
	// asked the OS for reduced motion.
	var body = document.body;
	if (!body.classList.contains('rm-motion')) { return; }
	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
	if (window.CSS && CSS.supports && CSS.supports('animation-timeline: view()')) { return; }
	var plates = root.querySelectorAll('[data-depth]');
	var nums = root.querySelectorAll('.rm-ag__num');
	if (!plates.length && !nums.length) { return; }
	body.classList.add('rm-motion--js');

	var risers = root.querySelectorAll('.rm-lp__section > h2, .rm-lp__section > .rm-lp__lead, .rm-ag__card, .rm-ag__step, .rm-ag__checks li, .rm-ag__person, .rm-lp__q, .rm-ag__final > .rm-lp__card, .rm-ag__final > .rm-lp__final-copy');
	if ('IntersectionObserver' in window) {
		var ro = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting) { en.target.classList.add('is-in'); ro.unobserve(en.target); }
			});
		}, { rootMargin: '0px 0px -10% 0px' });
		for (var r = 0; r < risers.length; r++) { ro.observe(risers[r]); }
	} else {
		for (var q = 0; q < risers.length; q++) { risers[q].classList.add('is-in'); }
	}

	// The hero copy lifts against the plates over the first screen, as the stylesheet does with
	// scroll(root): [element, travel in px, opacity at the end].
	var hero = [];
	[['.rm-ag__hero h1', -90, 0.55], ['.rm-ag__hero .rm-lp__eyebrow', -90, 0.55], ['.rm-ag__hero .rm-lp__lead', -44, 0.7], ['.rm-ag__hero > .rm-lp__card', -16, 1]].forEach(function (h) {
		var els = root.querySelectorAll(h[0]);
		for (var e = 0; e < els.length; e++) { hero.push([els[e], h[1], h[2]]); }
	});

	var ticking = false;
	// The rate applies to the untransformed position, so the measurement is taken from the parent (a
	// plate's section, a number's card): measuring the element itself would include its own last shift
	// and feed it back into the next one.
	function shift(el, rate) {
		var box = el.parentNode.getBoundingClientRect();
		var offset = (window.innerHeight / 2 - (box.top + box.height / 2)) * rate;
		el.style.transform = 'translate3d(0,' + offset.toFixed(1) + 'px,0)';
	}
	function frame() {
		ticking = false;
		for (var p = 0; p < plates.length; p++) { shift(plates[p], parseFloat(plates[p].getAttribute('data-depth')) || 0); }
		for (var u = 0; u < nums.length; u++) { shift(nums[u], 0.08); }
		var t = Math.min(1, Math.max(0, window.pageYOffset / (window.innerHeight * 0.9)));
		for (var i = 0; i < hero.length; i++) {
			hero[i][0].style.transform = 'translate3d(0,' + (hero[i][1] * t).toFixed(1) + 'px,0)';
			hero[i][0].style.opacity = (1 - (1 - hero[i][2]) * t).toFixed(3);
		}
	}
	function onScroll() {
		if (!ticking) { ticking = true; window.requestAnimationFrame(frame); }
	}
	window.addEventListener('scroll', onScroll, { passive: true });
	window.addEventListener('resize', onScroll);
	frame();
})();
