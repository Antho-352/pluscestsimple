/*!
 * Plus c'est simple — front-end JS.
 *
 * Périmètre :
 *   - Menu hamburger mobile (toggle aria-expanded + data-open)
 *   - Motion reveal (IntersectionObserver sur [data-reveal])
 *   - Split-text (wrap mots en spans pour stagger)
 *   - Scroll-compact header fallback (browsers sans scroll-timeline)
 *   - Cookie consent reopener (.pcs-consent-open)
 *
 * Vanilla, zéro dépendance, ~3 ko minifié.
 * Respecte prefers-reduced-motion.
 */
(function () {
	'use strict';

	var doc = document;
	var win = window;
	var prefersReduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

	// =================================================================
	// Menu hamburger mobile
	// =================================================================
	(function () {
		var toggle = doc.querySelector('.pcs-menu-toggle');
		var nav    = doc.getElementById('pcs-primary-menu');
		if (!toggle || !nav) return;

		function openMenu() {
			toggle.setAttribute('aria-expanded', 'true');
			nav.setAttribute('data-open', 'true');
			doc.body.style.overflow = 'hidden';
		}
		function closeMenu() {
			toggle.setAttribute('aria-expanded', 'false');
			nav.removeAttribute('data-open');
			doc.body.style.overflow = '';
		}
		function isOpen() {
			return toggle.getAttribute('aria-expanded') === 'true';
		}

		toggle.addEventListener('click', function () {
			if (isOpen()) { closeMenu(); } else { openMenu(); }
		});

		// ESC ferme.
		doc.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && isOpen()) {
				closeMenu();
				toggle.focus();
			}
		});

		// Click sur un lien interne du menu = ferme automatiquement.
		nav.addEventListener('click', function (e) {
			var a = e.target.closest('a');
			if (a && isOpen()) closeMenu();
		});

		// Si l'utilisateur passe en desktop pendant que le menu mobile est ouvert.
		var mql = matchMedia('(min-width: 901px)');
		var onChange = function () { if (mql.matches && isOpen()) closeMenu(); };
		if (mql.addEventListener) mql.addEventListener('change', onChange);
		else mql.addListener(onChange);
	})();

	// =================================================================
	// Scroll-compact header — fallback pour browsers sans scroll-timeline
	// =================================================================
	(function () {
		if (CSS && CSS.supports && CSS.supports('animation-timeline', 'scroll()')) return;
		var header = doc.querySelector('.pcs-header[data-scroll-compact]');
		if (!header) return;
		var onScroll = function () {
			header.classList.toggle('is-compact', win.scrollY > 60);
		};
		win.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	})();

	// =================================================================
	// Split-text — wrap mots en spans pour stagger
	// =================================================================
	(function () {
		var els = doc.querySelectorAll('[data-split-text]');
		if (!els.length) return;
		els.forEach(function (el) {
			var txt = el.textContent;
			el.textContent = '';
			var parts = txt.split(/(\s+)/);
			parts.forEach(function (chunk, i) {
				if (!chunk.trim()) { el.appendChild(doc.createTextNode(chunk)); return; }
				var span = doc.createElement('span');
				span.className = 'pcs-word';
				span.style.setProperty('--i', Math.floor(i / 2));
				span.textContent = chunk;
				el.appendChild(span);
			});
		});
	})();

	// =================================================================
	// Reveal on scroll — IntersectionObserver
	// =================================================================
	(function () {
		var targets = doc.querySelectorAll('[data-reveal], [data-split-text]');
		if (!targets.length) return;

		// Reduced motion : tout révéler immédiatement.
		if (prefersReduced || !('IntersectionObserver' in win)) {
			targets.forEach(function (el) { el.classList.add('is-revealed'); });
			return;
		}

		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add('is-revealed');
					io.unobserve(e.target);
				}
			});
		}, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });

		targets.forEach(function (el) { io.observe(el); });
	})();

	// =================================================================
	// Cookie consent reopener — click sur .pcs-consent-open ré-affiche le banner
	// =================================================================
	(function () {
		doc.addEventListener('click', function (e) {
			var trigger = e.target.closest('.pcs-consent-open');
			if (!trigger) return;
			e.preventDefault();
			var banner = doc.getElementById('pcs-consent');
			if (banner) banner.removeAttribute('hidden');
			// Notifie les modules consent qu'on veut afficher les préférences.
			doc.dispatchEvent(new CustomEvent('pcs:consent:reopen'));
		});
	})();

})();
