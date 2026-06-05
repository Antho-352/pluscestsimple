/**
 * pcs-directory v2 — JS
 *
 * 1. Carte Leaflet (lazy via IntersectionObserver) — archive + taxonomies
 * 2. Filtre AJAX — met à jour grille + carte simultanément
 * 3. Carte single boutique
 *
 * Dépend de : Leaflet 1.9.x (chargé avant ce script)
 * Config     : window.pcsDir.ajaxurl + window.pcsDir.nonce
 */
(function () {
  'use strict';

  var cfg = window.pcsDir || {};

  /* ── Utilitaires ───────────────────────────────────────────────────────── */

  function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
  function qsa(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

  /* ── 1. Carte archive / taxonomy ───────────────────────────────────────── */

  var archiveMap  = null;
  var markerLayer = null;

  function initArchiveMap(el) {
    if (archiveMap || typeof window.L === 'undefined') return;

    var raw = el.getAttribute('data-markers') || '[]';
    var markers;
    try { markers = JSON.parse(raw); } catch(e) { markers = []; }

    if (!markers.length) return;

    // Centre et zoom automatiques.
    var lats = markers.map(function(m){ return m.lat; });
    var lngs = markers.map(function(m){ return m.lng; });
    var latMin = Math.min.apply(null, lats), latMax = Math.max.apply(null, lats);
    var lngMin = Math.min.apply(null, lngs), lngMax = Math.max.apply(null, lngs);
    var centerLat = (latMin + latMax) / 2;
    var centerLng = (lngMin + lngMax) / 2;

    archiveMap = L.map(el).setView([centerLat, centerLng], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(archiveMap);

    markerLayer = L.layerGroup().addTo(archiveMap);
    renderMarkers(markers);

    // Fit bounds si plusieurs points.
    if (markers.length > 1) {
      try {
        archiveMap.fitBounds([[latMin, lngMin], [latMax, lngMax]], { padding: [30, 30] });
      } catch(e) {}
    }
  }

  function renderMarkers(markers) {
    if (!markerLayer) return;
    markerLayer.clearLayers();
    markers.forEach(function(m) {
      if (!m.lat || !m.lng) return;
      var popup = '<strong><a href="' + m.url + '">' + m.nom + '</a></strong>'
        + (m.ville ? '<br><small>' + m.ville + '</small>' : '');
      L.marker([m.lat, m.lng])
        .bindPopup(popup)
        .addTo(markerLayer);
    });
  }

  // IntersectionObserver : init carte seulement quand visible.
  function setupArchiveMap() {
    var mapEl = qs('#pcs-map');
    if (!mapEl) return;

    if ('IntersectionObserver' in window) {
      var obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
          if (entry.isIntersecting) {
            initArchiveMap(mapEl);
            obs.unobserve(mapEl);
          }
        });
      }, { threshold: 0.1 });
      obs.observe(mapEl);
    } else {
      initArchiveMap(mapEl);
    }
  }

  /* ── 2. Filtres AJAX ───────────────────────────────────────────────────── */

  function setupFilters() {
    var form = qs('#pcs-filters');
    var grid = qs('#pcs-grid');
    var pager = qs('#pcs-pagination');
    var btn = qs('.pcs-filter-btn', form);

    if (!form || !grid) return;

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      doFilter(1);
    });

    // Changement de page via pagination générée dynamiquement.
    document.addEventListener('click', function(e) {
      var link = e.target.closest('[data-pcs-page]');
      if (!link) return;
      e.preventDefault();
      doFilter(parseInt(link.getAttribute('data-pcs-page'), 10) || 1);
    });

    function doFilter(page) {
      if (!cfg.ajaxurl || !cfg.nonce) return;

      var body = new URLSearchParams();
      body.set('action', 'pcs_filter');
      body.set('nonce',  cfg.nonce);
      body.set('page',   page);

      // Sérialise tous les selects du formulaire.
      qsa('select', form).forEach(function(sel) {
        body.set(sel.name, sel.value);
      });

      if (grid) grid.classList.add('is-loading');
      if (btn)  btn.classList.add('is-loading');

      fetch(cfg.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body
      })
      .then(function(r) { return r.json(); })
      .then(function(json) {
        if (!json || !json.success) {
          console.error('pcs_filter error', json);
          return;
        }
        var data = json.data;

        // Mise à jour liste.
        grid.innerHTML = data.posts.length
          ? '<ul class="pcs-list">' + data.posts.map(rowHtml).join('') + '</ul>'
          : '<p class="pcs-empty">Aucune boutique ne correspond à ces critères.</p>';

        // Mise à jour pagination.
        if (pager) {
          pager.innerHTML = buildPager(page, data.pages);
        }

        // Mise à jour carte.
        if (archiveMap && data.markers) {
          renderMarkers(data.markers);
        }

        // Mise à jour URL.
        var params = new URLSearchParams(body);
        params.delete('action');
        params.delete('nonce');
        params.set('page', page);
        history.pushState({}, '', '?' + params.toString());
      })
      .catch(function(err) { console.error('pcs_filter fetch error', err); })
      .finally(function() {
        if (grid) grid.classList.remove('is-loading');
        if (btn)  btn.classList.remove('is-loading');
      });
    }
  }

  function rowHtml(b) {
    var meta = [];
    if (b.adresse) meta.push(esc(b.adresse));
    var cpVille = [b.code_postal, b.ville].filter(Boolean).join(' ').trim();
    if (cpVille) meta.push(esc(cpVille));

    var flags = '';
    if (b.is_enseigne) flags += '<span class="pcs-flag pcs-flag--enseigne">enseigne</span>';
    if (b.categorie)   flags += '<span class="pcs-flag pcs-flag--cat">' + esc(b.categorie) + '</span>';
    if (b.website)     flags += '<span class="pcs-flag pcs-flag--web">site web</span>';
    if (b.phone)       flags += '<span class="pcs-flag pcs-flag--phone">tél.</span>';

    return '<li class="pcs-list__item">'
      + '<a class="pcs-list__link" href="' + esc(b.url) + '">' + esc(b.title) + '</a>'
      + (meta.length ? '<span class="pcs-list__meta">' + meta.join(' · ') + '</span>' : '')
      + '<span class="pcs-list__flags">' + flags + '</span>'
      + '</li>';
  }

  function buildPager(current, total) {
    if (total <= 1) return '';
    var html = '<nav class="pcs-pagination"><div class="nav-links">';
    if (current > 1) {
      html += '<a href="#" class="page-numbers" data-pcs-page="' + (current - 1) + '">‹ Préc.</a>';
    }
    for (var i = 1; i <= total; i++) {
      if (i === current) {
        html += '<span class="page-numbers current">' + i + '</span>';
      } else if (i === 1 || i === total || Math.abs(i - current) <= 2) {
        html += '<a href="#" class="page-numbers" data-pcs-page="' + i + '">' + i + '</a>';
      } else if (Math.abs(i - current) === 3) {
        html += '<span class="page-numbers">…</span>';
      }
    }
    if (current < total) {
      html += '<a href="#" class="page-numbers" data-pcs-page="' + (current + 1) + '">Suiv. ›</a>';
    }
    html += '</div></nav>';
    return html;
  }

  function esc(s) {
    return String(s || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /* ── 3. Carte single boutique ──────────────────────────────────────────── */

  function setupSingleMap() {
    var el = qs('#pcs-single-map');
    if (!el || typeof window.L === 'undefined') return;

    var lat = parseFloat(el.getAttribute('data-lat'));
    var lng = parseFloat(el.getAttribute('data-lng'));
    var nom = el.getAttribute('data-nom') || '';

    if (!lat || !lng) return;

    var map = L.map(el).setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    L.marker([lat, lng])
      .addTo(map)
      .bindPopup('<strong>' + esc(nom) + '</strong>')
      .openPopup();
  }

  /* ── Init ──────────────────────────────────────────────────────────────── */

  document.addEventListener('DOMContentLoaded', function() {
    setupArchiveMap();
    setupFilters();
    setupSingleMap();
  });

})();
