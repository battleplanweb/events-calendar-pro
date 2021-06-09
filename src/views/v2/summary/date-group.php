<?php
/**
 * View: Summary View Date grouping
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/summary/date-group.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.7.0
 *
 * @var \Tribe\Utils\Date_I18n_Immutable $group_date      The date for the date group.
 * @var array                            $events_for_date The array of events for the date group.
 *
 * @see tribe_get_event() For the format of the event object.
 */

$container_classes = [ 'tribe-common-g-row', 'tribe-events-pro-summary__event-row' ];

if ( 1 < count( $events_for_date ) ) {
	$container_classes[] = 'tribe-events-pro-summary__event-row--multi-event';
}

$first_event = current( $events_for_date );
$this->setup_postdata( $first_event );
?>
<div <?php tribe_classes( $container_classes ); ?>>
	<?php $this->template( 'summary/date-group/date-tag', [ 'event' => $first_event, 'group_date' => $group_date ] ); ?>
	<div class="tribe-common-g-col tribe-events-pro-summary__event-wrapper">
		<?php foreach ( $events_for_date as $event ) : ?>
			<?php $this->setup_postdata( $event ); ?>
			<?php $this->template( 'summary/date-group/event', [ 'event' => $event, 'group_date' => $group_date ] ); ?>
		<?php endforeach; ?>
	</div>

</div>
