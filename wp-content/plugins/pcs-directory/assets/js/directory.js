/**
 * Annuaire — pluscestsimple
 *
 * Petit script vanilla (~50 lignes) :
 *   - Toggle des filtres en mobile.
 *   - Hook d'activation de la carte (placeholder pour intégration Leaflet future).
 *
 * Pas de dépendance. Pas de framework.
 */
(function () {
	'use strict';

	// ─── 1) Toggle filtres mobile ──────────────────────────────────────────
	var filterForms = document.querySelectorAll('.pcs-directory-filters');
	filterForms.forEach(function (form) {
		var toggle = form.querySelector('.pcs-directory-filters__toggle');
		if (!toggle) return;
		// État initial = fermé en mobile, ouvert en desktop (CSS s'en occupe via media query).
		form.setAttribute('data-open', 'false');
		toggle.addEventListener('click', function () {
			var open = form.getAttribute('data-open') === 'true';
			form.setAttribute('data-open', open ? 'false' : 'true');
			toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
		});
	});

	// ─── 2) Hook d'activation carte ────────────────────────────────────────
	// Pour activer Leaflet plus tard, ajouter le CDN dans le footer puis :
	//   window.pcsDirectoryActivateMap(document.querySelector('.pcs-directory-map'));
	window.pcsDirectoryActivateMap = function (el) {
		if (!el || typeof window.L === 'undefined') {
			return false;
		}
		var lat  = parseFloat(el.getAttribute('data-lat'));
		var lng  = parseFloat(el.getAttribute('data-lng'));
		var zoom = parseInt(el.getAttribute('data-zoom') || '15', 10);
		if (isNaN(lat) || isNaN(lng)) return false;

		el.classList.add('is-active');
		el.innerHTML = '';
		var map = window.L.map(el).setView([lat, lng], zoom);
		window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: '© OpenStreetMap contributors'
		}).addTo(map);
		window.L.marker([lat, lng]).addTo(map);
		return true;
	};

	// Auto-activation si Leaflet déjà chargé (compat thème custom).
	if (typeof window.L !== 'undefined') {
		document.querySelectorAll('.pcs-directory-map[data-lat][data-lng]').forEach(function (el) {
			window.pcsDirectoryActivateMap(el);
		});
	}
})();
