<?php
/**
 * Admins: everyone who can open the panel, their board name and colour, and (for WordPress
 * administrators) forms to add and remove people.
 *
 * @package Intella_Admin
 */

defined( 'ABSPATH' ) || exit;

$intella_team   = intella_admin_team();
$intella_manage = intella_admin_can_manage();
$intella_colors = intella_admin_colors();
$intella_me     = get_current_user_id();
?>
<h1><?php esc_html_e( 'Admins', 'intella' ); ?></h1>
<p class="muted"><?php esc_html_e( 'People who can open this panel. The board name is how someone appears on cards and bugs; the colour marks their cards.', 'intella' ); ?></p>

<div class="table-wrap">
	<table class="table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Name', 'intella' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Email', 'intella' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Access', 'intella' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Board name and colour', 'intella' ); ?></th>
				<?php if ( $intella_manage ) : ?>
					<th scope="col"><span class="visually-hidden"><?php esc_html_e( 'Remove', 'intella' ); ?></span></th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $intella_team as $intella_u ) : ?>
				<?php
				$intella_editable = $intella_manage || $intella_me === $intella_u->ID;
				$intella_form_id  = 'admin-' . $intella_u->ID;
				$intella_color    = intella_admin_user_color( $intella_u );
				?>
				<tr>
					<td>
						<span class="who who--<?php echo esc_attr( $intella_color ); ?>"><?php echo esc_html( intella_admin_board_name( $intella_u ) ); ?></span>
						<span class="table__sub"><?php echo esc_html( $intella_u->display_name . ' · ' . $intella_u->user_login ); ?></span>
					</td>
					<td><?php echo esc_html( $intella_u->user_email ); ?></td>
					<td><?php echo in_array( 'administrator', $intella_u->roles, true ) ? esc_html__( 'WordPress administrator', 'intella' ) : esc_html__( 'Intella Team', 'intella' ); ?></td>
					<td>
						<?php if ( $intella_editable ) : ?>
							<form class="row-form" method="post" id="<?php echo esc_attr( $intella_form_id ); ?>">
								<?php wp_nonce_field( 'intella_admin_update_admin' ); ?>
								<input type="hidden" name="intella_action" value="update_admin">
								<input type="hidden" name="user_id" value="<?php echo esc_attr( $intella_u->ID ); ?>">
								<label class="visually-hidden" for="<?php echo esc_attr( $intella_form_id ); ?>-name"><?php esc_html_e( 'Board name', 'intella' ); ?></label>
								<input id="<?php echo esc_attr( $intella_form_id ); ?>-name" name="board_name" value="<?php echo esc_attr( intella_admin_board_name( $intella_u ) ); ?>" maxlength="100" required>
								<label class="visually-hidden" for="<?php echo esc_attr( $intella_form_id ); ?>-color"><?php esc_html_e( 'Colour', 'intella' ); ?></label>
								<select id="<?php echo esc_attr( $intella_form_id ); ?>-color" name="color">
									<?php foreach ( $intella_colors as $intella_key => $intella_label ) : ?>
										<option value="<?php echo esc_attr( $intella_key ); ?>" <?php selected( $intella_color, $intella_key ); ?>><?php echo esc_html( $intella_label ); ?></option>
									<?php endforeach; ?>
								</select>
								<button class="btn"><?php esc_html_e( 'Save', 'intella' ); ?></button>
							</form>
						<?php else : ?>
							<?php echo esc_html( $intella_colors[ $intella_color ] ); ?>
						<?php endif; ?>
					</td>
					<?php if ( $intella_manage ) : ?>
						<td>
							<?php if ( $intella_me !== $intella_u->ID ) : ?>
								<form method="post" data-confirm="<?php echo esc_attr( sprintf( /* translators: %s: name */ __( 'Remove %s from the admin panel? Their WordPress account stays.', 'intella' ), intella_admin_board_name( $intella_u ) ) ); ?>">
									<?php wp_nonce_field( 'intella_admin_remove_admin' ); ?>
									<input type="hidden" name="intella_action" value="remove_admin">
									<input type="hidden" name="user_id" value="<?php echo esc_attr( $intella_u->ID ); ?>">
									<button class="btn btn--danger"><?php esc_html_e( 'Remove', 'intella' ); ?></button>
								</form>
							<?php else : ?>
								<span class="muted"><?php esc_html_e( 'You', 'intella' ); ?></span>
							<?php endif; ?>
						</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php if ( $intella_manage ) : ?>
	<h2><?php esc_html_e( 'Add an admin', 'intella' ); ?></h2>
	<p class="muted"><?php esc_html_e( "If the email already has a WordPress account, that account gets access. Otherwise a new account is created with the Intella Team role (it can't change the website) and the person is emailed a link to set their password.", 'intella' ); ?></p>
	<form class="stack-form" method="post">
		<?php wp_nonce_field( 'intella_admin_add_admin' ); ?>
		<input type="hidden" name="intella_action" value="add_admin">
		<label><?php esc_html_e( 'Full name', 'intella' ); ?><input name="name" required maxlength="100" autocomplete="off"></label>
		<label><?php esc_html_e( 'Email', 'intella' ); ?><input name="email" type="email" required autocomplete="off"></label>
		<label><?php esc_html_e( 'Colour on boards', 'intella' ); ?>
			<select name="color">
				<?php foreach ( $intella_colors as $intella_key => $intella_label ) : ?>
					<option value="<?php echo esc_attr( $intella_key ); ?>"><?php echo esc_html( $intella_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<button class="btn btn--primary"><?php esc_html_e( 'Add admin', 'intella' ); ?></button>
	</form>
<?php endif; ?>
<script type="module" src="<?php echo esc_url( intella_admin_asset( 'js/confirm.js' ) ); ?>"></script>
