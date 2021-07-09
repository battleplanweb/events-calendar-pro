<?php
/**
 * View: List Single Event Cost
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/summary/date-group/event/cost.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.7.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event->cost ) ) {
	return;
}

$et_loaded   = function_exists( 'tribe_get_ticket_label_plural' );
$is_sold_out = $et_loaded && $event->tickets->sold_out();

?>
<div class="tribe-events-c-small-cta tribe-common-b3 tribe-common-b3--bold tribe-events-pro-summary__event-cost">
	<?php if ( $is_sold_out ) : ?>
		<span class="tribe-events-c-small-cta__text">
			<?php echo esc_html( __( 'Sold out', 'the-events-calendar' ) ); ?>
		</span>
	<?php elseif ( $et_loaded && $event->summary_view->has_tickets ) : ?>
		<a
			href="<?php echo esc_url( $event->permalink . '#tribe-tickets__tickets-form' ); ?>"
			title="<?php echo esc_attr( $event->title ); ?>"
			rel="bookmark"
			class="tribe-events-c-small-cta__text"
		><?php echo esc_html( sprintf( __( 'Get %1$s', 'the-events-calendar' ), tribe_get_ticket_label_plural() ) ); ?></a>
		</a>
		<span class="tribe-events-c-small-cta__price">
			<?php echo esc_html( $event->cost ) ?>
		</span>
	<?php elseif ( $et_loaded ) : ?>
		<a
			href="<?php echo esc_url( $event->permalink . '#rsvp-now' ); ?>"
			title="<?php echo esc_attr( $event->title ); ?>"
			rel="bookmark"
			class="tribe-events-c-small-cta__text"
		><?php echo esc_html( sprintf( __( '%1$s now', 'the-events-calendar' ), tribe_get_rsvp_label_singular() ) ); ?></a>
		</a>
	<?php else : ?>
		<div class="tibe-events-c-small-cta__text">
			<span class="tribe-events-c-small-cta__price">
				<?php echo esc_html( $event->cost ) ?>
			</span>
		</div>
	<?php endif; ?>
</div>
