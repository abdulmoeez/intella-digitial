<?php
/**
 * Document head, icon sprite and site header. Opens <main>, which footer.php closes.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Icon sprite: every <svg><use href="#i-…"/></svg> on the page points into it.
echo file_get_contents( INTELLA_DIR . '/assets/images/icons.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput, WordPress.WP.AlternativeFunctions -- theme file.
?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'intella' ); ?></a>

<?php get_template_part( 'template-parts/site-header' ); ?>

<main id="main" class="site-main" tabindex="-1">
