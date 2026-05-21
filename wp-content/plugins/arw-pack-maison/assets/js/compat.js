/* arw-maison Compatibilimètre — fuzzy search + lead-magnet trigger
 * Vanilla JS, no dependency. Index URL + lead config injected via wp_add_inline_script.
 */
(function () {
	'use strict';

	// ─── Index page : search UI ────────────────────────────────────────

	var rootIndex = document.querySelector('.arw-compat');
	if (rootIndex) {
		initIndexSearch(rootIndex);
	}

	// ─── Single rule page : lead-magnet counter + modal ────────────────

	var rootRule = document.querySelector('.arw-rule');
	if (rootRule && window.arwMaisonLead && window.arwMaisonLead.enabled) {
		initLeadMagnet(rootRule);
	}

	// ─── Inline lead forms (home / index / rule pages) ─────────────────

	if (window.arwMaisonLead && window.arwMaisonLead.enabled) {
		document.querySelectorAll('[data-lead-inline]').forEach(initInlineForm);
	}

	// ===================================================================
	// Index search
	// ===================================================================

	function initIndexSearch(root) {
		var indexURL  = window.arwMaisonIndexURL || '/wp-content/uploads/arw-maison/rules-index.json';
		var inputEl   = root.querySelector('#arw-compat-q');
		var resultsEl = root.querySelector('[data-results]');
		var countEl   = root.querySelector('[data-count]');
		var emptyEl   = root.querySelector('.arw-compat__empty');
		var chipsRows = root.querySelectorAll('.arw-compat__chips');

		var state = { rules: [], query: '', category: '', verdict: '' };

		var iconByVerdict = {
			'compatible':  '✓', 'conditional': '◐', 'discouraged': '!', 'forbidden':   '✕'
		};
		var labelByVerdict = {
			'compatible':  'Compatible',  'conditional': 'Sous conditions',
			'discouraged': 'Déconseillé', 'forbidden':   'Interdit'
		};

		fetch(indexURL, { credentials: 'same-origin' })
			.then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
			.then(function (data) {
				state.rules = (data && Array.isArray(data.rules)) ? data.rules : [];
				render();
			})
			.catch(function () {
				countEl.textContent = 'Index indisponible. Réessayez dans un instant.';
			});

		var debounceTimer = null;
		inputEl.addEventListener('input', function () {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(function () {
				state.query = inputEl.value || '';
				render();
			}, 100);
		});

		chipsRows.forEach(function (row) {
			var filterKey = row.getAttribute('data-filter');
			row.addEventListener('click', function (e) {
				var btn = e.target.closest('.arw-chip');
				if (!btn) return;
				row.querySelectorAll('.arw-chip').forEach(function (b) { b.classList.remove('is-active'); });
				btn.classList.add('is-active');
				state[filterKey] = btn.getAttribute('data-value') || '';
				render();
			});
		});

		function normalize(str) {
			return (str || '').toString().toLowerCase()
				.normalize('NFD').replace(/[̀-ͯ]/g, '')
				.replace(/[^\w\s]/g, ' ').replace(/\s+/g, ' ').trim();
		}

		function scoreRule(rule, terms) {
			if (!terms.length) return 1;
			var titleN    = normalize(rule.title);
			var keywordsN = (rule.keywords || []).map(normalize).join(' ');
			var explanN   = normalize(rule.explanation);
			var categoryN = normalize(rule.category);
			var score = 0;
			for (var i = 0; i < terms.length; i++) {
				var t = terms[i];
				if (!t) continue;
				if (titleN.indexOf(t) !== -1)    score += 10;
				if (keywordsN.indexOf(t) !== -1) score += 6;
				if (categoryN.indexOf(t) !== -1) score += 4;
				if (explanN.indexOf(t) !== -1)   score += 2;
			}
			return score;
		}

		function filtered() {
			var terms = normalize(state.query).split(' ').filter(Boolean);
			var rows = state.rules.filter(function (r) {
				if (state.category && r.category !== state.category) return false;
				if (state.verdict  && r.verdict  !== state.verdict)  return false;
				return true;
			});
			if (terms.length === 0) {
				return rows.slice().sort(function (a, b) {
					return (a.title || '').localeCompare(b.title || '', 'fr');
				});
			}
			var scored = [];
			for (var i = 0; i < rows.length; i++) {
				var s = scoreRule(rows[i], terms);
				if (s > 0) scored.push({ rule: rows[i], score: s });
			}
			scored.sort(function (a, b) { return b.score - a.score; });
			return scored.map(function (x) { return x.rule; });
		}

		function render() {
			var rows = filtered();
			countEl.textContent = rows.length === 0
				? 'Aucun résultat'
				: (rows.length + (rows.length > 1 ? ' règles' : ' règle'));
			emptyEl.hidden = rows.length > 0;
			resultsEl.innerHTML = '';
			if (rows.length === 0) return;
			var frag = document.createDocumentFragment();
			for (var i = 0; i < rows.length; i++) frag.appendChild(buildResultItem(rows[i]));
			resultsEl.appendChild(frag);
		}

		function buildResultItem(rule) {
			var li = document.createElement('li');
			li.className = 'arw-result';
			var a = document.createElement('a');
			a.className = 'arw-result__link';
			a.href = rule.url || '#';
			var v = document.createElement('span');
			v.className = 'arw-result__verdict arw-result__verdict--' + (rule.verdict || 'compatible');
			v.setAttribute('aria-hidden', 'true');
			v.textContent = iconByVerdict[rule.verdict] || '?';
			var t = document.createElement('h3');
			t.className = 'arw-result__title';
			t.textContent = rule.title || '';
			var m = document.createElement('span');
			m.className = 'arw-result__meta';
			m.textContent = labelByVerdict[rule.verdict] || rule.verdict || '';
			a.appendChild(v); a.appendChild(t); a.appendChild(m);
			li.appendChild(a);
			return li;
		}
	}

	// ===================================================================
	// Lead magnet (single rule page)
	// ===================================================================

	function initLeadMagnet(rootRule) {
		var cfg = window.arwMaisonLead;
		var STORE_VIEWS    = 'arw_compat_views';
		var STORE_UNLOCKED = 'arw_compat_unlocked';

		// Bail if already unlocked.
		if (safeGet(STORE_UNLOCKED) === '1') return;

		var modal = document.querySelector('[data-lead-modal]');
		if (!modal) return;

		// Track this rule as visited (deduplicated).
		var views = safeGetJSON(STORE_VIEWS) || [];
		if (cfg.currentRuleId && views.indexOf(cfg.currentRuleId) === -1) {
			views.push(cfg.currentRuleId);
			safeSet(STORE_VIEWS, JSON.stringify(views));
		}

		// If under threshold, do nothing.
		if (views.length < cfg.threshold) return;

		// Apply blur to rule content blocks (preserve title + verdict header visible).
		var blurTargets = rootRule.querySelectorAll('.arw-rule__block, .arw-rule__refs');
		blurTargets.forEach(function (el) { el.classList.add('arw-blur'); });

		// Show modal.
		showModal();

		// Wire dismiss handlers.
		modal.querySelectorAll('[data-lead-dismiss]').forEach(function (el) {
			el.addEventListener('click', function () { hideModal(); });
		});

		// Wire form submit.
		var form = modal.querySelector('[data-lead-form]');
		var errorEl = modal.querySelector('[data-lead-error]');
		var submitBtn = form.querySelector('.arw-lead-modal__submit');

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			errorEl.hidden = true;

			// Honeypot check.
			if (form.elements['website'] && form.elements['website'].value) return;

			var email = (form.elements['email'].value || '').trim();
			if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
				errorEl.textContent = 'Adresse email invalide.';
				errorEl.hidden = false;
				return;
			}
			if (!form.elements['consent'].checked) {
				errorEl.textContent = 'Merci de confirmer votre consentement.';
				errorEl.hidden = false;
				return;
			}

			submitBtn.disabled = true;
			submitBtn.textContent = '…';

			var body = new URLSearchParams();
			body.set('form_type', 'lead-magnet');
			body.set('email', email);
			body.set('consent', '1');
			body.set('source', 'compatibilimetre');
			body.set('nonce', cfg.nonce);

			fetch(cfg.restUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'X-WP-Nonce': cfg.nonce,
					'Accept': 'application/json'
				},
				body: body
			}).then(function (r) {
				return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, body: j }; });
			}).then(function (res) {
				if (!res.ok) {
					var msg = (res.body && (res.body.message || res.body.error)) || 'Échec de l\'envoi. Réessayez dans un instant.';
					errorEl.textContent = msg;
					errorEl.hidden = false;
					submitBtn.disabled = false;
					submitBtn.textContent = cfg.modalButton;
					return;
				}
				// Success.
				safeSet(STORE_UNLOCKED, '1');
				removeBlur();
				switchView('success');
			}).catch(function () {
				errorEl.textContent = 'Connexion impossible. Réessayez dans un instant.';
				errorEl.hidden = false;
				submitBtn.disabled = false;
				submitBtn.textContent = cfg.modalButton;
			});
		});

		// Success "Continuer la lecture" button : closes modal.
		var successCloseBtn = modal.querySelector('[data-lead-close-success]');
		if (successCloseBtn) {
			successCloseBtn.addEventListener('click', function () { hideModal(); });
		}

		// ─── Helpers ─────────────────────────────────────────────────

		function showModal() {
			modal.hidden = false;
			document.documentElement.style.overflow = 'hidden';
			// Focus trap : focus the email input.
			var email = modal.querySelector('#arw-lead-email');
			if (email) setTimeout(function () { email.focus(); }, 60);
		}
		function hideModal() {
			modal.hidden = true;
			document.documentElement.style.overflow = '';
		}
		function switchView(name) {
			modal.querySelectorAll('[data-lead-view]').forEach(function (v) {
				v.hidden = (v.getAttribute('data-lead-view') !== name);
			});
		}
		function removeBlur() {
			rootRule.querySelectorAll('.arw-blur').forEach(function (el) {
				el.classList.remove('arw-blur');
			});
		}
		function safeGet(k) {
			try { return localStorage.getItem(k); } catch (e) { return null; }
		}
		function safeSet(k, v) {
			try { localStorage.setItem(k, v); } catch (e) {}
		}
		function safeGetJSON(k) {
			try { var v = localStorage.getItem(k); return v ? JSON.parse(v) : null; } catch (e) { return null; }
		}
	}

	// ===================================================================
	// Inline lead form (standalone, embedded in pages)
	// ===================================================================

	function initInlineForm(root) {
		var STORE_UNLOCKED  = 'arw_compat_unlocked';
		var STORE_DISMISSED = 'arw_compat_side_dismissed';
		var isSide          = root.hasAttribute('data-lead-side');
		var formState    = root.querySelector('[data-lead-inline-form-state]');
		var successState = root.querySelector('[data-lead-inline-success]');
		var form         = root.querySelector('[data-lead-inline-form]');
		var errorEl      = root.querySelector('[data-lead-inline-error]');

		if (!form) return;

		// If already unlocked, hide the entire encart (no need to take screen space).
		if (safeGet(STORE_UNLOCKED) === '1') {
			root.style.display = 'none';
			return;
		}

		// Side widget : honor session-dismissed flag.
		if (isSide && safeGet(STORE_DISMISSED) === '1') {
			root.classList.add('is-dismissed');
			return;
		}

		// Side widget : wire close button.
		if (isSide) {
			var closeBtn = root.querySelector('[data-lead-side-close]');
			if (closeBtn) {
				closeBtn.addEventListener('click', function () {
					root.classList.add('is-dismissed');
					safeSet(STORE_DISMISSED, '1');
				});
			}
		}

		var submitBtn = form.querySelector('.arw-lead-inline__submit');

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			errorEl.hidden = true;

			if (form.elements['website'] && form.elements['website'].value) return;

			var email = (form.elements['email'].value || '').trim();
			if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
				errorEl.textContent = 'Adresse email invalide.';
				errorEl.hidden = false;
				return;
			}
			if (!form.elements['consent'].checked) {
				errorEl.textContent = 'Merci de confirmer votre consentement.';
				errorEl.hidden = false;
				return;
			}

			var originalLabel = submitBtn.textContent;
			submitBtn.disabled = true;
			submitBtn.textContent = '…';

			var cfg = window.arwMaisonLead;
			var body = new URLSearchParams();
			body.set('form_type', 'lead-magnet');
			body.set('email', email);
			body.set('consent', '1');
			body.set('source', 'inline');
			body.set('nonce', cfg.nonce);

			fetch(cfg.restUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'X-WP-Nonce': cfg.nonce, 'Accept': 'application/json' },
				body: body
			}).then(function (r) {
				return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, body: j }; });
			}).then(function (res) {
				if (!res.ok) {
					var msg = (res.body && (res.body.message || res.body.error)) || 'Échec de l\'envoi. Réessayez dans un instant.';
					errorEl.textContent = msg;
					errorEl.hidden = false;
					submitBtn.disabled = false;
					submitBtn.textContent = originalLabel;
					return;
				}
				safeSet(STORE_UNLOCKED, '1');
				// Also remove blur if present on the page (rule page case).
				document.querySelectorAll('.arw-blur').forEach(function (el) { el.classList.remove('arw-blur'); });
				// Hide modal if open.
				var modal = document.querySelector('[data-lead-modal]');
				if (modal) {
					modal.hidden = true;
					document.documentElement.style.overflow = '';
				}
				if (formState)    formState.hidden = true;
				if (successState) successState.hidden = false;
			}).catch(function () {
				errorEl.textContent = 'Connexion impossible. Réessayez dans un instant.';
				errorEl.hidden = false;
				submitBtn.disabled = false;
				submitBtn.textContent = originalLabel;
			});
		});

		function safeGet(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }
		function safeSet(k, v) { try { localStorage.setItem(k, v); } catch (e) {} }
	}
})();
