<?php
/**
 * View: Month View - Single Multiday Event Recurring Icon
 * Also the icon for all-day events.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/month/calendar-event/multiday/recurring.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1aiy
 *
 * @since 5.7.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 *
 * @version 5.7.0
 */

if ( empty( $event->recurring ) ) {
	return;
}
?>
<a
	href="<?php echo esc_url( $event->permalink_all ); ?>"
	class="tribe-events-calendar-month__calendar-event-multiday-recurring-link"
>
	<em
		class="tribe-events-calendar-month__calendar-event-multiday-recurring-icon"
		title="<?php esc_attr_e( 'Recurring', 'tribe-events-calendar-pro' ) ?>"
	>
		<?php $this->template( 'components/icons/recurring', [ 'classes' => [ 'tribe-events-calendar-month__calendar-event-datetime-recurring-icon-svg' ] ] ); ?>
	</em>
</a>
