<?php
/**
 * View: Summary View - Single day date partial.
 * Used for events that don't span multiple days and aren't all "all-day" events.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/summary/date-group/event/date/single.php
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
	<?php echo esc_html( $event->summary_view->start_time ); ?>
</span> - <span class="tribe-event-date-end">
	<?php echo esc_html( $event->summary_view->end_time ); ?>
</span>
