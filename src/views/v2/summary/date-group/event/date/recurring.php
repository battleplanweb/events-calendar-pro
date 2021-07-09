<?php
/**
 * View: Summary View - Single Event Recurring Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/summary/date-group/event/date/recurring.php
 *
 * See more documentation about our views templating system.
 *
 * Note this view uses classes from the list view event datetime to leverage those styles.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 5.7.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event->recurring ) ) {
	return;
}
?>
<a
	href="<?php echo esc_url( $event->permalink_all ); ?>"
	class="tribe-events-pro-summary__event-datetime-recurring-link"
>
	<em
		class="tribe-events-pro-summary__event-datetime-recurring-icon"
		title="<?php esc_attr_e( 'Recurring', 'tribe-events-calendar-pro' ); ?>"
	>
		<?php $this->template( 'components/icons/recurring', [ 'classes' => [ 'tribe-events-pro-summary__event-datetime-recurring-icon-svg' ] ] ); ?>
	</em>
</a>
