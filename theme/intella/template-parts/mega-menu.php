<?php
/**
 * The Services mega menu: service groups as tabs on the left, each group's links on the right,
 * and an offer strip at the bottom that matches the group in view.
 *
 * @package Intella
 *
 * @var array $args { item: the top menu node with groups as children }
 */

defined( 'ABSPATH' ) || exit;

$item   = $args['item'];
$groups = $item['children'];
$arrow  = intella_icon( 'arrow-right' );

/**
 * One offer strip.
 *
 * @param array  $node Menu node with cta_* fields.
 * @param string $key  Group key ('all' for the general one).
 */
$offer = static function ( array $node, string $key ) use ( $arrow ): void {
	if ( ! $node['cta_title'] ) {
		return;
	}
	?>
	<div class="mega__cta" data-mega-cta="<?php echo esc_attr( $key ); ?>">
		<div class="mega__cta-text">
			<p class="mega__cta-title"><?php echo intella_accent( $node['cta_title'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- filtered by intella_inline(). ?></p>
			<p class="mega__cta-sub"><?php echo esc_html( $node['cta_text'] ); ?></p>
		</div>
		<a class="button button--primary mega__cta-button" href="<?php echo esc_url( intella_url( $node['cta_url'] ) ); ?>"><?php echo esc_html( $node['cta_button'] ); ?> <?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	</div>
	<?php
};
?>
<div class="mega" id="submenu-<?php echo (int) $item['id']; ?>" data-mega>
	<div class="mega__sidebar">
		<p class="mega__label"><?php esc_html_e( 'Explore services', 'intella' ); ?></p>
		<div class="mega__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Service categories', 'intella' ); ?>" aria-orientation="vertical">
			<?php foreach ( $groups as $i => $group ) : ?>
				<button class="mega__tab<?php echo 0 === $i ? ' is-active' : ''; ?>" type="button" role="tab" id="mega-tab-g<?php echo (int) $group['id']; ?>" aria-controls="mega-panel-g<?php echo (int) $group['id']; ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"<?php echo 0 === $i ? '' : ' tabindex="-1"'; ?> data-mega-tab>
					<span class="mega__tab-icon"><?php echo intella_icon( $group['icon'] ? $group['icon'] : 'layers' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="mega__tab-text"><span class="mega__tab-title"><?php echo esc_html( $group['title'] ); ?></span><span class="mega__tab-sub"><?php echo esc_html( $group['description'] ); ?></span></span>
				</button>
			<?php endforeach; ?>
		</div>
		<a class="mega__all" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['link_text'] ? $item['link_text'] : __( 'All services', 'intella' ) ); ?> <?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	</div>

	<div class="mega__panels">
		<?php foreach ( $groups as $i => $group ) : ?>
			<div class="mega__panel<?php echo 0 === $i ? ' is-active' : ''; ?>" role="tabpanel" id="mega-panel-g<?php echo (int) $group['id']; ?>" aria-labelledby="mega-tab-g<?php echo (int) $group['id']; ?>">
				<div class="mega__panel-head">
					<p class="mega__panel-title"><?php echo esc_html( $group['title'] ); ?></p>
					<?php if ( $group['link_text'] ) : ?>
						<a class="mega__panel-link" href="<?php echo esc_url( $group['url'] ); ?>"><?php echo esc_html( $group['link_text'] ); ?> <?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					<?php endif; ?>
				</div>
				<ul class="mega__links" role="list">
					<?php foreach ( $group['children'] as $link ) : ?>
						<li><a class="mega__link mega__link--logo" href="<?php echo esc_url( $link['url'] ); ?>"<?php echo $link['current'] ? ' aria-current="page"' : ''; ?>><?php echo intella_mega_logo( $link ); // phpcs:ignore WordPress.Security.EscapeOutput -- theme SVG files and escaped attributes. ?><span class="mega__link-title"><?php echo esc_html( $link['title'] ); ?></span><span class="mega__link-desc"><?php echo esc_html( $link['description'] ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>

		<?php
		$offer( $item, 'all' );
		foreach ( $groups as $group ) {
			$offer( $group, 'g' . $group['id'] );
		}
		?>
	</div>
</div>
