<?php
/**
 * Closes <main>, then the site footer.
 *
 * @package Intella
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<?php get_template_part( 'template-parts/site-footer' ); ?>

<?php wp_footer(); ?>
</body>
</html>
