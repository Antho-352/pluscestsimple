<?php
/**
 * Header du site : <head>, ouverture <body>, header + nav principale.
 *
 * @package pluscestsimple
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="pcs-skip-link screen-reader-text" href="#pcs-main"><?php esc_html_e( 'Aller au contenu principal', 'pluscestsimple' ); ?></a>

<header class="pcs-header" data-scroll-compact>
	<div class="pcs-container pcs-header__inner">

		<div class="pcs-header__brand">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				printf(
					'<a class="pcs-site-title" href="%1$s" rel="home">%2$s</a>',
					esc_url( home_url( '/' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
			}
			?>
		</div>

		<button class="pcs-menu-toggle" aria-expanded="false" aria-controls="pcs-primary-menu" type="button">
			<span class="pcs-menu-toggle__label"><?php esc_html_e( 'Menu', 'pluscestsimple' ); ?></span>
			<span class="pcs-menu-toggle__icon" aria-hidden="true"></span>
		</button>

		<div id="pcs-primary-menu" class="pcs-header__nav">
			<?php pcs_primary_menu(); ?>
		</div>

	</div>
</header>

<main id="pcs-main" class="pcs-main">
