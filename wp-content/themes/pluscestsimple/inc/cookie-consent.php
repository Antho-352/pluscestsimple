<?php
/**
 * Native cookie consent banner.
 * - Google Consent Mode v2 compatible (default denied; updates on user choice)
 * - 3 actions: Accept all / Reject all / Customize (granular)
 * - Persists choice in localStorage + cookie `arw_consent` (13 months, CNIL-compliant)
 * - Reopenable via any link with class `arw-consent-open`
 *
 * This is intentionally inline (no external file, no external CSS) to keep JS at
 * a few hundred bytes and render pre-hydration with a deferred mount.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	// Fire Consent Mode v2 defaults as early as possible, before any analytics loads.
	?>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('consent', 'default', {
  ad_storage: 'denied',
  ad_user_data: 'denied',
  ad_personalization: 'denied',
  analytics_storage: 'denied',
  functionality_storage: 'granted',
  security_storage: 'granted',
  wait_for_update: 500
});
(function(){
  try {
    var saved = localStorage.getItem('arw_consent');
    if (saved) {
      var c = JSON.parse(saved);
      gtag('consent', 'update', c);
    }
  } catch(e) {}
})();
</script>
	<?php
}, 1 );

add_action( 'wp_footer', function () {
	?>
<div id="arw-consent" hidden aria-live="polite" aria-label="<?php esc_attr_e( 'Préférences cookies', 'arw-pulse' ); ?>">
  <div class="arw-consent__inner">
    <p class="arw-consent__text"><?php esc_html_e( 'Nous utilisons des cookies pour mesurer l\'audience et améliorer votre expérience. Vous pouvez accepter, refuser ou personnaliser.', 'arw-pulse' ); ?> <a href="<?php echo esc_url( get_privacy_policy_url() ?: '/politique-de-confidentialite/' ); ?>"><?php esc_html_e( 'En savoir plus', 'arw-pulse' ); ?></a></p>
    <div class="arw-consent__actions">
      <button type="button" data-consent="reject"><?php esc_html_e( 'Refuser', 'arw-pulse' ); ?></button>
      <button type="button" data-consent="customize"><?php esc_html_e( 'Personnaliser', 'arw-pulse' ); ?></button>
      <button type="button" data-consent="accept" class="is-primary"><?php esc_html_e( 'Tout accepter', 'arw-pulse' ); ?></button>
    </div>
    <fieldset class="arw-consent__custom" hidden>
      <label><input type="checkbox" data-k="analytics_storage"> <?php esc_html_e( 'Mesure d\'audience', 'arw-pulse' ); ?></label>
      <label><input type="checkbox" data-k="ad_storage"> <?php esc_html_e( 'Publicité', 'arw-pulse' ); ?></label>
      <label><input type="checkbox" data-k="ad_user_data"> <?php esc_html_e( 'Données utilisateur pub', 'arw-pulse' ); ?></label>
      <label><input type="checkbox" data-k="ad_personalization"> <?php esc_html_e( 'Personnalisation pub', 'arw-pulse' ); ?></label>
      <button type="button" data-consent="save" class="is-primary"><?php esc_html_e( 'Enregistrer mes choix', 'arw-pulse' ); ?></button>
    </fieldset>
  </div>
</div>
<script>
(function(){
  var el = document.getElementById('arw-consent');
  if (!el) return;
  var saved = null;
  try { saved = JSON.parse(localStorage.getItem('arw_consent') || 'null'); } catch(e) {}
  if (!saved) el.hidden = false;

  function apply(c){
    localStorage.setItem('arw_consent', JSON.stringify(c));
    var d = new Date(); d.setTime(d.getTime() + 13*30*24*60*60*1000);
    var secureFlag = (location.protocol === 'https:') ? '; Secure' : '';
    document.cookie = 'arw_consent=1; expires=' + d.toUTCString() + '; path=/; SameSite=Lax' + secureFlag;
    if (window.gtag) gtag('consent', 'update', c);
    el.hidden = true;
    document.dispatchEvent(new CustomEvent('arw:consent', { detail: c }));
  }

  el.addEventListener('click', function(e){
    var t = e.target.closest('[data-consent]');
    if (!t) return;
    var a = t.dataset.consent;
    if (a === 'accept') {
      apply({ ad_storage:'granted', ad_user_data:'granted', ad_personalization:'granted', analytics_storage:'granted', functionality_storage:'granted', security_storage:'granted' });
    } else if (a === 'reject') {
      apply({ ad_storage:'denied', ad_user_data:'denied', ad_personalization:'denied', analytics_storage:'denied', functionality_storage:'granted', security_storage:'granted' });
    } else if (a === 'customize') {
      el.querySelector('.arw-consent__custom').hidden = false;
    } else if (a === 'save') {
      var c = { functionality_storage:'granted', security_storage:'granted' };
      el.querySelectorAll('[data-k]').forEach(function(b){ c[b.dataset.k] = b.checked ? 'granted' : 'denied'; });
      apply(c);
    }
  });

  // Re-open handler.
  document.addEventListener('click', function(e){
    var t = e.target.closest('.arw-consent-open');
    if (t) { e.preventDefault(); el.hidden = false; }
  });
})();
</script>
	<?php
}, 99 );

