<?php
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * Title: Bio auteur
 * Slug: arw-pulse/author-bio
 * Categories: arw-magazine
 * Block Types: core/group
 * Description: Bloc auteur en fin d'article.
 */
if ( ! is_singular( 'post' ) ) { return; }
$post   = get_queried_object();
$author = get_userdata( $post->post_author );
if ( ! $author ) { return; }
$desc = $author->description;
if ( ! $desc ) { return; }
?>
<!-- wp:html -->
<aside class="arw-author-bio" style="display:flex;gap:1.25rem;align-items:flex-start;border-top:1px solid var(--wp--preset--color--border);padding-top:2rem;margin-top:3rem">
	<img src="<?php echo esc_url( get_avatar_url( $author->ID, [ 'size' => 96 ] ) ); ?>" alt="" width="64" height="64" style="border-radius:999px;width:64px;height:64px;flex:0 0 64px" loading="lazy" decoding="async">
	<div>
		<p style="font-family:var(--wp--preset--font-family--display);font-weight:700;margin:0">
			<a href="<?php echo esc_url( get_author_posts_url( $author->ID ) ); ?>" rel="nofollow" style="text-decoration:none;color:inherit"><?php echo esc_html( $author->display_name ); ?></a>
		</p>
		<p style="color:var(--wp--preset--color--muted);margin:0.25rem 0 0;font-size:var(--wp--preset--font-size--sm)"><?php echo esc_html( wp_strip_all_tags( $desc ) ); ?></p>
	</div>
</aside>
<!-- /wp:html -->
