<?php
/**
 * Dashboard: every board with its open cards, overdue cards and open bugs, and a form to add a board.
 *
 * @package Intella_Admin
 * @var array $args Boards.
 */

defined( 'ABSPATH' ) || exit;

$intella_stats = intella_admin_board_stats();
?>
<h1><?php
	/* translators: %s: person's name */
	echo esc_html( sprintf( __( 'Hello, %s', 'intella' ), intella_admin_board_name( wp_get_current_user() ) ) );
?></h1>
<p class="muted"><?php esc_html_e( 'Client and in-house project boards for the Intella Digital team.', 'intella' ); ?></p>

<h2><?php esc_html_e( 'Boards', 'intella' ); ?></h2>
<ul class="tiles">
	<?php foreach ( $args['boards'] as $intella_b ) : ?>
		<?php $intella_s = $intella_stats[ (int) $intella_b->id ] ?? array( 'open' => 0, 'overdue' => 0, 'bugs' => 0 ); ?>
		<li class="tile">
			<a class="tile__link" href="<?php echo esc_url( intella_admin_board_url( $intella_b ) ); ?>">
				<span class="tile__title"><?php echo esc_html( $intella_b->name ); ?></span>
				<span class="tile__stats">
					<span><?php
						/* translators: %d: number of cards */
						echo esc_html( sprintf( _n( '%d open card', '%d open cards', $intella_s['open'], 'intella' ), $intella_s['open'] ) );
					?></span>
					<?php if ( $intella_s['overdue'] ) : ?>
						<span class="tile__overdue"><?php
							/* translators: %d: number of cards */
							echo esc_html( sprintf( __( '%d overdue', 'intella' ), $intella_s['overdue'] ) );
						?></span>
					<?php endif; ?>
					<?php if ( $intella_s['bugs'] ) : ?>
						<span><?php
							/* translators: %d: number of bugs */
							echo esc_html( sprintf( _n( '%d open bug', '%d open bugs', $intella_s['bugs'], 'intella' ), $intella_s['bugs'] ) );
						?></span>
					<?php endif; ?>
				</span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>

<form class="inline-form" method="post">
	<?php wp_nonce_field( 'intella_admin_add_board' ); ?>
	<input type="hidden" name="intella_action" value="add_board">
	<label for="board-name"><?php esc_html_e( 'New board', 'intella' ); ?></label>
	<input id="board-name" name="board_name" required maxlength="200" placeholder="<?php esc_attr_e( 'Client or project name', 'intella' ); ?>">
	<button class="btn btn--primary"><?php esc_html_e( 'Add board', 'intella' ); ?></button>
</form>
