<?php
/**
 * View: Summary View - Multiday "start" date partial.
 * Used for the first day of multi-day events.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/summary/date-group/event/date/multiday-start.php
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
<span class="tribe-event-date-start">
	<?php echo esc_html(
		sprintf(
			/* Translators: %1$s: The event time. */
			_x( '%1$s onwards', '"onwards" as in "from TIME onwards"', 'tribe-ext-summary-view' ),
			$event->summary_view->start_time
		)
	);
	?>
</span>
