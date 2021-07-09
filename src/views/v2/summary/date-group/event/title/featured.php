<?php
/**
 * View: Summary View - Single Event Featured Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/summary/date-group/event/title/featured.php
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
 *
 * @version 5.7.0
 */

if ( empty( $event->featured ) ) {
	return;
}
?>
<em
	class="tribe-events-pro-summary__event-title-icon"
	title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
>
	<?php $this->template( 'components/icons/featured', [ 'classes' => [ 'tribe-events-pro-summary__event-title-featured-icon-svg' ] ] ); ?>
</em>
<span class="tribe-events-pro-summary__event-title-featured-text tribe-common-a11y-visual-hide">
	<?php esc_html_e( 'Featured', 'the-events-calendar' ); ?>
</span>
