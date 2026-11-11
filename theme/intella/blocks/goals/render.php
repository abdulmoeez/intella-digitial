<?php
/**
 * Start with your goal: goal cards; choosing one opens its plan (goals.js) with steps, what to expect,
 * the services used and two buttons.
 *
 * @package Intella
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$title_id = intella_uid( 'goals-title' );
$goals    = intella_items( $attributes['goals'] );
$cue      = '<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';
$close    = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';
$plan_id  = static fn( array $goal, int $i ): string => 'goal-plan-' . ( ! empty( $goal['key'] ) ? sanitize_title( $goal['key'] ) : $i );
?>
<section<?php echo intella_section_attrs( $attributes, '', $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="container">
		<?php echo intella_section_head( $attributes, $title_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<ul class="goals" role="list" data-goals>
			<?php foreach ( $goals as $i => $goal ) : ?>
			<li class="goal goal--<?php echo esc_attr( sanitize_html_class( $goal['color'] ?? 'violet' ) ); ?>" data-goal><h3 class="goal__title"><button class="goal__open" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $plan_id( $goal, $i ) ); ?>"><?php echo esc_html( $goal['title'] ?? '' ); ?></button></h3><span class="goal__cue" aria-hidden="true"><?php echo $cue; // phpcs:ignore WordPress.Security.EscapeOutput ?></span><p class="goal__text"><?php echo esc_html( $goal['text'] ?? '' ); ?></p><p class="goal__fit"><span class="goal__fit-label"><?php esc_html_e( 'We’d use', 'intella' ); ?></span> – <?php echo esc_html( $goal['fit'] ?? '' ); ?></p><p class="goal__more" aria-hidden="true"><?php esc_html_e( 'See the plan', 'intella' ); ?> <?php echo intella_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p></li>
			<?php endforeach; ?>
			<?php foreach ( $goals as $i => $goal ) : ?>
				<?php
				$services = array_map(
					static fn( $s ) => '<a href="' . esc_url( intella_url( $s['url'] ?? '' ) ) . '">' . esc_html( $s['label'] ?? '' ) . '</a>',
					intella_items( $goal['services'] ?? array() )
				);
				?>
			<li class="goal-plan goal--<?php echo esc_attr( sanitize_html_class( $goal['color'] ?? 'violet' ) ); ?>" id="<?php echo esc_attr( $plan_id( $goal, $i ) ); ?>" role="region" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: goal */ __( 'Plan: %s', 'intella' ), $goal['title'] ?? '' ) ); ?>" tabindex="-1" hidden data-goal-plan><div class="goal-plan__head"><div><p class="goal-plan__kicker"><?php esc_html_e( 'Your plan', 'intella' ); ?></p><p class="goal-plan__title"><?php echo esc_html( $goal['title'] ?? '' ); ?></p></div><button class="goal-plan__close" type="button" aria-label="<?php esc_attr_e( 'Close the plan', 'intella' ); ?>" data-goal-close><?php echo $close; // phpcs:ignore WordPress.Security.EscapeOutput ?></button></div><ol class="goal-plan__steps" role="list"><?php foreach ( intella_items( $goal['steps'] ?? array() ) as $step ) : ?><li class="goal-plan__step"><span class="goal-plan__when"><?php echo esc_html( $step['when'] ?? '' ); ?></span><strong><?php echo esc_html( $step['title'] ?? '' ); ?></strong><span><?php echo esc_html( $step['text'] ?? '' ); ?></span></li><?php endforeach; ?></ol><div class="goal-plan__side"><div class="goal-plan__expect"><p class="goal-plan__expect-label"><?php esc_html_e( 'What to expect', 'intella' ); ?></p><p class="goal-plan__expect-text"><?php echo esc_html( $goal['expect'] ?? '' ); ?></p></div><p class="goal-plan__services"><span class="goal__fit-label"><?php esc_html_e( 'We’d use', 'intella' ); ?></span> – <?php echo implode( ', ', $services ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></p><div class="goal-plan__actions"><?php if ( ! empty( $goal['primaryText'] ) ) : ?><a class="button button--primary" href="<?php echo esc_url( intella_url( $goal['primaryUrl'] ?? '' ) ); ?>"><?php echo esc_html( $goal['primaryText'] ); ?></a><?php endif; ?><?php if ( ! empty( $goal['secondaryText'] ) ) : ?><a class="button button--outline" href="<?php echo esc_url( intella_url( $goal['secondaryUrl'] ?? '' ) ); ?>"><?php echo esc_html( $goal['secondaryText'] ); ?></a><?php endif; ?></div></div></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
