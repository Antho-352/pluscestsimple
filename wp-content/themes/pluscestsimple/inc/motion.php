<?php
/**
 * Motion system — minimal, dependency-free, ~2kb JS total.
 * Respects prefers-reduced-motion system-wide.
 *
 * All data-* attributes are applied AT RUNTIME via JS based on CSS classes,
 * so pattern HTML stays valid from Gutenberg's perspective (no unknown attrs).
 *
 * Classes auto-augmented with data-reveal:         .arw-card, .arw-gear__card,
 *                                                  .arw-dossier, .arw-hero-front__img
 * Classes auto-augmented with data-split-text:     .arw-hero-front__title, .arw-hero-front__title-2,
 *                                                  .arw-section-title, .arw-manifesto__text,
 *                                                  .arw-cover__title, .arw-hero-featured__title
 * Classes auto-augmented with data-reveal-stagger: .arw-gear, .arw-dossiers__list,
 *                                                  .arw-related .wp-block-query
 * Classes auto-augmented with data-scroll-compact: .arw-header
 * Auto-numbered:                                   .arw-dossier__num → 01, 02, 03…
 *
 * View Transitions opt-in via filter arw_pulse_view_transitions.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// View Transitions meta tag (opt-in).
add_action( 'wp_head', function () {
	if ( apply_filters( 'arw_pulse_view_transitions', true ) ) {
		echo '<meta name="view-transition" content="same-origin">' . "\n";
	}
}, 2 );

add_action( 'wp_footer', function () {
	?>
<script>
(function(){
  // Augment DOM with data attributes based on classes (keeps pattern HTML Gutenberg-valid).
  var RS = [
    ['.arw-card', 'data-reveal', 'up'],
    ['.arw-gear__card', 'data-reveal', 'up'],
    ['.arw-dossier', 'data-reveal', 'up'],
    ['.arw-hero-front__img', 'data-reveal', 'scale']
  ];
  var ST = ['.arw-hero-front__title', '.arw-hero-front__title-2', '.arw-section-title', '.arw-manifesto__text', '.arw-cover__title', '.arw-hero-featured__title'];
  var SG = ['.arw-gear', '.arw-dossiers__list', '.arw-related .wp-block-query'];

  RS.forEach(function(r){
    document.querySelectorAll(r[0]).forEach(function(el){ el.setAttribute(r[1], r[2]); });
  });
  ST.forEach(function(sel){
    document.querySelectorAll(sel).forEach(function(el){ el.setAttribute('data-split-text', ''); });
  });
  SG.forEach(function(sel){
    document.querySelectorAll(sel).forEach(function(el){ el.setAttribute('data-reveal-stagger', ''); });
  });

  // Scroll-compact header.
  var header = document.querySelector('.arw-header');
  if (header) header.setAttribute('data-scroll-compact', '');

  // Auto-number dossiers.
  document.querySelectorAll('.arw-dossiers__list .arw-dossier__num').forEach(function(el, i){
    el.textContent = String(i + 1).padStart(2, '0');
  });

  if (!('IntersectionObserver' in window)) return;
  var reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) {
    document.querySelectorAll('[data-reveal]').forEach(function(el){ el.classList.add('is-revealed'); });
    return;
  }

  // Split text helper.
  document.querySelectorAll('[data-split-text]').forEach(function(el){
    var txt = el.textContent;
    el.textContent = '';
    txt.split(/(\s+)/).forEach(function(w, i){
      if (!w.trim()) { el.appendChild(document.createTextNode(w)); return; }
      var s = document.createElement('span');
      s.className = 'arw-word';
      s.style.setProperty('--i', Math.floor(i/2));
      s.textContent = w;
      el.appendChild(s);
    });
  });

  // Stagger children on reveal containers.
  document.querySelectorAll('[data-reveal-stagger]').forEach(function(parent){
    Array.prototype.slice.call(parent.children).forEach(function(child, i){
      if (!child.hasAttribute('data-reveal')) child.setAttribute('data-reveal', 'up');
      child.style.setProperty('--i', i);
    });
  });

  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if (e.isIntersecting) {
        e.target.classList.add('is-revealed');
        io.unobserve(e.target);
      }
    });
  }, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });

  document.querySelectorAll('[data-reveal], [data-split-text]').forEach(function(el){ io.observe(el); });

  // Scroll-compact header fallback (for browsers without scroll-timeline).
  if (header) {
    var onScroll = function(){ header.classList.toggle('is-compact', window.scrollY > 60); };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }
})();
</script>
	<?php
}, 95 );
