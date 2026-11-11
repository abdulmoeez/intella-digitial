<?php
/**
 * Page not found: the 404 message, a search of every page (page-search.js) and buttons.
 * The search index is built from the published pages, so it is always up to date.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'page-title' );
$index    = array();
foreach ( get_pages( array( 'post_status' => 'publish', 'sort_column' => 'post_title' ) ) as $page ) {
	$description = (string) get_post_meta( $page->ID, 'rank_math_description', true );
	$index[]     = array(
		't' => html_entity_decode( get_the_title( $page ), ENT_QUOTES ),
		'd' => $description ? $description : wp_trim_words( wp_strip_all_tags( $page->post_excerpt ), 25 ),
		'u' => get_permalink( $page ),
	);
}
?>
<section class="nf" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="container nf__inner">
		<p class="nf__code" aria-hidden="true"><span>4</span><svg class="nf__glass" viewBox="0 0 120 120" aria-hidden="true"><circle cx="52" cy="52" r="34" fill="#fff" stroke="currentColor" stroke-width="12"/><path d="M78 78 L106 106" stroke="currentColor" stroke-width="14" stroke-linecap="round"/><path d="M40 46 q12 -12 24 0" fill="none" stroke="#d9b6ff" stroke-width="5" stroke-linecap="round"/></svg><span>4</span></p>
		<h1 class="nf__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
		<p class="nf__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
		<div class="nf__search" data-page-search>
			<form class="filter-search nf__search-box" role="search" data-page-search-form>
				<label class="visually-hidden" for="nf-search-input"><?php esc_html_e( 'Search the website', 'intella' ); ?></label>
				<?php echo intella_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<input id="nf-search-input" type="search" placeholder="<?php echo esc_attr( $attributes['placeholder'] ); ?>" autocomplete="off" aria-controls="nf-search-results" data-page-search-input>
			</form>
			<p class="visually-hidden" role="status" aria-live="polite" data-page-search-status></p>
			<ul class="nf__results" id="nf-search-results" role="list" hidden data-page-search-results></ul>
			<script type="application/json" data-page-index><?php echo wp_json_encode( $index, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ); ?></script>
			<?php if ( $attributes['sitemap'] ) : ?>
			<p class="nf__sitemap"><?php echo intella_inline( $attributes['sitemap'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
		<div class="nf__actions">
			<?php foreach ( intella_items( $attributes['buttons'] ) as $button ) : ?>
				<?php $primary = 'outline' !== ( $button['style'] ?? 'primary' ); ?>
				<?php echo intella_button( $button, 'button button--' . ( $primary ? 'primary' : 'outline' ) . ' button--lg', $primary ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
