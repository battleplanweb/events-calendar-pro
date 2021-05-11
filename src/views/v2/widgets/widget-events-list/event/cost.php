<?php
/**
 * Widget: Events List Event Cost
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/widgets/widget-events-list/event/cost.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1aiy
 *
 * @version 5.2.0
 *
 * @var WP_Post            $event   The event post object with properties added by the `tribe_get_event` function.
 * @var array<string,bool> $display Associative array of display settings for event meta.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event->cost ) || empty( $display['cost'] ) ) {
	return;
}
?>
<div class="tribe-common-b2 tribe-events-widget-events-list__event-cost">
	<span class="tribe-events-widget-events-list__event-cost-price">
		<?php echo esc_html( $event->cost ); ?>
	</span>
</div>
