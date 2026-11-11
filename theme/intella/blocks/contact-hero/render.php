<?php
/**
 * Contact hero: H1, intro, ways to reach us and ticked notes, beside the enquiry form.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'contact-title' );
$check    = intella_icon( 'check' );
?>
<section class="page-hero" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="container page-hero__inner">
		<div>
			<h1 class="page-hero__title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo intella_accent( $attributes['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
			<p class="page-hero__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
			<ul class="contact__ways" role="list">
				<?php foreach ( intella_items( $attributes['ways'] ) as $way ) : ?>
				<li class="contact-way">
					<span class="icon-tile"><?php echo intella_icon( ! empty( $way['icon'] ) ? $way['icon'] : 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<?php if ( empty( $way['url'] ) ) : ?>
					<div><p class="contact-way__label"><?php echo esc_html( $way['label'] ?? '' ); ?></p><address class="contact-way__value contact-way__address"><?php echo nl2br( esc_html( $way['value'] ?? '' ), false ); ?></address></div>
					<?php else : ?>
					<div><p class="contact-way__label"><?php echo esc_html( $way['label'] ?? '' ); ?></p><a class="contact-way__value" href="<?php echo esc_url( intella_url( $way['url'] ) ); ?>"><?php echo esc_html( $way['value'] ?? '' ); ?></a></div>
					<?php endif; ?>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( intella_lines( $attributes['notes'] ) ) : ?>
			<ul class="note-list" role="list">
				<?php foreach ( intella_lines( $attributes['notes'] ) as $note ) : ?>
				<li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $note ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</div>
		<?php
		get_template_part(
			'template-parts/forms/enquiry',
			null,
			array(
				'form'     => 'contact',
				'id'       => 'contact-form',
				'title'    => $attributes['formTitle'],
				'sub'      => $attributes['formSub'],
				'services' => intella_lines( $attributes['services'] ),
				'button'   => $attributes['buttonText'],
				'trust'    => intella_lines( $attributes['trust'] ),
				'note'     => $attributes['note'],
			)
		);
		?>
	</div>
</section>
