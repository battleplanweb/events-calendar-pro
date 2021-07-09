<?php
/**
 * View: Summary View Month separator
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/summary/month-separator.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://m.tri.be/1aiy
 *
 * @version 5.7.0
 *
 * @var \Tribe\Utils\Date_I18n_Immutable $group_date       The date for the date group.
 * @var array                            $events           The array of events for the date group.
 * @var WP_Post                          $event            The event post object with properties added by the `tribe_get_event` function.
 * @var array                            $month_transition An array of dates that should trigger a month separator
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( ! in_array( $event->ID, $month_transition ) ) {
	return;
}

if ( ! $event->summary_view->is_first_event_in_view && $event->multiday && ! $event->summary_view->is_multiday_and_start_of_month ) {
	return;
}
?>
<div class="tribe-events-pro-summary__month-separator">
	<time
		class="tribe-common-h7 tribe-common-h6--min-medium tribe-common-h--alt tribe-events-pro-summary__event-date-tag tribe-events-pro-summary__month-separator-text"
		datetime="<?php echo esc_attr( $group_date->format( 'Y-m' ) ); ?>"
	>
		<?php echo esc_html( $group_date->format_i18n( 'M Y' ) ); ?>
	</time>
</div>
