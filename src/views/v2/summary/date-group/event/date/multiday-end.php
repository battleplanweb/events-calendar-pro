<?php
/**
 * View: Summary View - Multiday "end" date partial.
 * Used for the last day of multi-day events.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/summary/date-group/event/date/multiday-end.php
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

?>
<span class="tribe-event-date-end">
	<?php echo esc_html(
		sprintf(
			/* Translators: %1$s: The event time. */
			_x( 'Until %1$s', '"until" as in "from DATE until DATE"', 'tribe-ext-summary-view' ),
			$event->summary_view->end_time
		)
	);
	?>
</span>
