<?php
/**
 * The Summary View.
 *
 * @package Tribe\Events\Pro\Views\V2\Views
 * @since 5.7.0
 */

namespace Tribe\Events\Pro\Views\V2\Views;

use \DateTimeInterface;
use Tribe\Events\Views\V2\Messages;
use Tribe__Date_Utils as Dates;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Utils\Date_I18n;
use Tribe\Utils\Date_I18n_Immutable;

class Summary_View extends List_View {

	/**
	 * Slug for this view.
	 *
	 * @since 5.7.0
	 *
	 * @var string
	 */
	public static $view_slug = 'summary';

	/**
	 * @inheritdoc
	 */
	public function __construct( Messages $messages = null ) {
		parent::__construct( $messages );

		$this->slug = static::$view_slug;

		add_action( 'tribe_template_before_include:events-pro/v2/summary', [ $this, 'add_view_hooks' ] );
		add_action( 'tribe_template_after_include:events-pro/v2/summary', [ $this, 'remove_view_hooks' ] );
	}

	/**
	 * Add hooks that are specific to the execution of this view.
	 *
	 * @since 5.7.0
	 */
	public function add_view_hooks() {
		add_filter( 'tribe_format_second_date_in_range', [ $this, 'filter_second_date_in_range_for_summary_view' ] );
	}

	/**
	 * Remove hooks that are specific to the execution of this view.
	 *
	 * @since 5.7.0
	 */
	public function remove_view_hooks() {
		remove_filter( 'tribe_format_second_date_in_range', [ $this, 'filter_second_date_in_range_for_summary_view' ] );
	}

	/**
	 * Filter the second date provided in a range of dates.
	 *
	 * @since 5.7.0
	 *
	 * @param string $format Date format.
	 *
	 * @return string
	 */
	public function filter_second_date_in_range_for_summary_view() {
		/**
		 * Filter the date in range format for the Summary View.
		 *
		 * @param string $format Date format for the second date in range.
		 */
		return apply_filters( 'tribe_events_views_v2_summary_second_date_in_range', 'M j' );
	}

	/**
	 * Overrides the base View method to fix the order of the events in the `past` display mode.
	 *
	 * @since 5.7.0
	 *
	 * @return array The List View template vars, modified if required.
	 */
	protected function setup_template_vars() {
		$template_vars             = parent::setup_template_vars();
		$events_by_date            = [];
		$month_transition          = [];
		$month_transition_datetime = [];
		$injectable_events         = [];
		$earliest_event            = current( $template_vars['events'] );
		$ids                       = wp_list_pluck( $template_vars['events'], 'ID' );

		$previous_event = ( $earliest_event instanceof \WP_Post )
			? $this->get_previous_event( $earliest_event, $ids )
			: false;

		foreach ( $template_vars['events'] as $event  ) {
			$event_start            = $event->dates->start_display;
			$start_date_day_of_year = tribe_beginning_of_day( $event_start->format( Dates::DBDATEFORMAT ), 'z' );
			$end_date_day_of_year   = tribe_beginning_of_day( $event->dates->end_display->format( Dates::DBDATEFORMAT ), 'z' );
			$date_diff              = $end_date_day_of_year - $start_date_day_of_year;
			$event_start_datetime   = $event_start->format( Dates::DBDATETIMEFORMAT );

			for ( $x = 0; $x <= $date_diff; $x++ ) {
				$new_event = clone $event;
				$event_day = $new_event->dates->start_display;

				if ( 0 < $x ) {
					$event_day = $event_day->add( Dates::interval( "P{$x}D" ) );
				}

				$event_date  = $event_day->format( Dates::DBDATEFORMAT );
				$event_month = $event_day->format( Dates::DBYEARMONTHTIMEFORMAT );

				if ( ! isset( $month_transition[ $event_month ] ) ) {
					$month_transition[ $event_month ]          = $event->ID;
					$month_transition_datetime[ $event_month ] = $event_start_datetime;
				}

				$events_by_date[ $event_date ][ $event_start_datetime  . ' - ' . $new_event->ID ] = $this->add_view_specific_properties_to_event( $new_event, $event_date );
			}
		}

		if ( ! empty( $previous_event ) ) {
			$injectable_events = $this->maybe_include_overlapping_events( $events_by_date, $previous_event, $injectable_events );
		}

		// Ensure that the correct event is set during month transitions.
		foreach ( $injectable_events as $dates ) {
			foreach ( $dates as $event_group_date => $event ) {
				$event_month          = substr( $event_group_date, 0, 7 );
				$event_start_datetime = $event->dates->start_display->format( Dates::DBDATETIMEFORMAT );

				// If we've found an event that starts earlier than the one that is already stored, let's use the event we found.
				if ( $month_transition_datetime[ $event_month ] > $event_start_datetime ) {
					$month_transition[ $event_month ]          = $event->ID;
					$month_transition_datetime[ $event_month ] = $event_start_datetime;
				}
			}
		}

		$events_by_date = $this->inject_events_into_result_dates( $injectable_events, $events_by_date );

		// Ensure event dates are sorted in ascending order.
		ksort( $events_by_date );

		// Mark the first event in the first date with events.
		foreach ( $events_by_date as &$date ) {
			foreach ( $date as &$event ) {
				$event->summary_view->is_first_event_in_view = true;
				break 2;
			}
		}

		$template_vars['events_by_date']   = $events_by_date;
		$template_vars['month_transition'] = $month_transition;

		return $template_vars;
	}

	/**
	 * Adds Summary View specific properties to events for ease of consumption within the templates.
	 *
	 * @since 5.7.0
	 *
	 * @param \WP_Post $event Event post object.
	 * @param \DateTimeImmutable $group_date Grouping for the event date.
	 *
	 * @return mixed
	 */
	protected function add_view_specific_properties_to_event( $event, $group_date ) {
		$start_date = tribe_beginning_of_day( $event->dates->start->format( Dates::DBDATEFORMAT ), Dates::DBDATEFORMAT );
		$end_date   = tribe_beginning_of_day( $event->dates->end->format( Dates::DBDATEFORMAT ), Dates::DBDATEFORMAT );

		$format                         = tribe_get_date_format();
		$formatted_start_date_beginning = tribe_beginning_of_day( $event->dates->start->format( Dates::DBDATEFORMAT ), $format );
		$formatted_end_date_ending      = tribe_beginning_of_day( $event->dates->end->format( Dates::DBDATEFORMAT ), $format );
		$formatted_group_date           = tribe_beginning_of_day( $group_date, $format );

		$is_multiday_start              = false !== $event->multiday && $formatted_group_date === $formatted_start_date_beginning;
		$is_multiday_end                = false !== $event->multiday && $formatted_group_date === $formatted_end_date_ending;
		$is_multiday_and_start_of_month = false !== $event->multiday && substr( $group_date, 7 ) !== substr( $start_date, 7 ) && substr( $group_date, -2 ) === '01';
		$is_all_day                     = $event->all_day;

		// @TODO: Decouple the hard dependency with Event Tickets and replace with a filter.
		$counts = class_exists( 'Tribe__Tickets__Tickets' ) ? \Tribe__Tickets__Tickets::get_ticket_counts( $event->ID ) : [];

		$has_tickets = ! empty( $counts['tickets'] ) && ! empty( array_filter( $counts['tickets'] ) );
		$has_rsvp    = ! empty( $counts['rsvp'] ) && ! empty( array_filter( $counts['rsvp'] ) );

		// Make "middle" days of a multi-day event all-day events.
		if (
			false !== $event->multiday
			&& ! $is_multiday_start
			&& ! $is_multiday_end
		) {
			$is_all_day = true;
		}

		$date_format = get_option( 'time_format' );
		$end_time    = $event->dates->end_display->format( $date_format );
		$start_time  = $event->dates->start_display->format( get_option( 'time_format' ) );
		if ( tribe_get_option( 'tribe_events_timezones_show_zone', false ) ) {
			if ( ! $is_multiday_start ) {
				$end_time .= ' ' . $event->dates->end_display->format( 'T' );
			} else {
				$start_time .= ' ' . $event->dates->end_display->format( 'T' );
			}
		}

		$event->summary_view = (object) [
			'is_first_event_in_view'         => false,
			'start_time'                     => $start_time,
			'end_time'                       => $end_time,
			'start_date'                     => $start_date,
			'end_date'                       => $end_date,
			'formatted_start_date'           => $formatted_start_date_beginning,
			'formatted_end_date'             => $formatted_end_date_ending,
			'is_multiday_start'              => $is_multiday_start,
			'is_multiday_end'                => $is_multiday_end,
			'is_multiday_and_start_of_month' => $is_multiday_and_start_of_month,
			'is_all_day'                     => $is_all_day,
			'has_tickets'                    => $has_tickets,
			'has_rsvp'                       => $has_rsvp,
		];

		return $event;
	}

	/**
	 * Gets events that start before and end during the event result set.
	 *
	 * @since 5.7.0
	 *
	 * @param Date_I18n|Date_I18n_Immutable $first_date First date in the result set.
	 * @param Date_I18n|Date_I18n_Immutable $last_date Last date in the result set.
	 *
	 * @return array
	 */
	protected function get_events_that_start_before_and_end_between( DateTimeInterface $first_date, DateTimeInterface $last_date ) {
		$first_date_beginning_of_day = tribe_beginning_of_day( $first_date->format( Dates::DBDATEFORMAT ) );
		$last_date_end_of_day        = tribe_end_of_day( $last_date->format( Dates::DBDATEFORMAT ) );

		return tribe_events()
			->where( 'starts_before', $first_date_beginning_of_day )
			->where( 'ends_between', $first_date_beginning_of_day, $last_date_end_of_day )
			->all();
	}

	/**
	 * Gets events that start and end around the event result set.
	 *
	 * @since 5.7.0
	 *
	 * @param Date_I18n|Date_I18n_Immutable $first_date First date in the result set.
	 * @param Date_I18n|Date_I18n_Immutable $last_date Last date in the result set.
	 *
	 * @return array
	 */
	protected function get_events_that_start_and_end_around( DateTimeInterface $first_date, DateTimeInterface $last_date ) {
		$first_date_beginning_of_day = tribe_beginning_of_day( $first_date->format( Dates::DBDATEFORMAT ) );
		$last_date_beginning_of_day  = tribe_beginning_of_day( $last_date->format( Dates::DBDATEFORMAT ) );

		return tribe_events()
			->where( 'starts_before', $first_date_beginning_of_day )
			->where( 'ends_after', $last_date_beginning_of_day )
			->all();
	}

	/**
	 * Gets the most recent event backward in time.
	 *
	 * @since 5.7.0
	 *
	 * @param \WP_Post $earliest_event First event in result set.
	 * @param array $exclude_ids Event IDs in result set.
	 *
	 * @return \WP_Post
	 */
	protected function get_previous_event( \WP_Post $earliest_event, array $exclude_ids = [] ) {
		return tribe_events()
			->where( 'starts_before', $earliest_event->dates->start )
			->not_in( $exclude_ids )
			->per_page( 1 )
			->page( 1 )
			->order( 'DESC' )
			->first();
	}

	/**
	 * Take an event and extend it within a date array to each date that it occurs on.
	 *
	 * @since 5.7.0
	 *
	 * @param \WP_Post            $event Event post.
	 * @param Date_I18n|Date_I18n_Immutable $start_date Start date of the event.
	 * @param Date_I18n|Date_I18n_Immutable $end_date End date of the event.
	 * @param array               $dates Array of dates to organize events (by reference).
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	protected function maybe_extend_event_to_other_dates( \WP_Post $event, DateTimeInterface $start_date, DateTimeInterface $end_date, array $dates ) {
		$start_date_beginning = Dates::build_date_object( tribe_beginning_of_day( $start_date->format( Dates::DBDATETIMEFORMAT ) ) );
		$end_date_beginning   = Dates::build_date_object( tribe_beginning_of_day( $end_date->format( Dates::DBDATETIMEFORMAT ) ) );


		// Calculate the difference in days between the start and end of the event.
		$diff = $start_date_beginning->diff( $end_date_beginning )->format( '%a' );

		for ( $i = 1; $i <= $diff; $i++ ) {
			// We need to clone the event here so we don't change all versions of it each iteration!
			$new_event = clone $event;

			// Don't modify the $start_date in the loop!
			$start = Dates::build_date_object( $start_date );
			$date = tribe_beginning_of_day( $start->add( new \DateInterval( 'P' . $i . 'D' ) )->format( Dates::DBDATEFORMAT ), Dates::DBDATEFORMAT );
			$new_event  = $this->add_view_specific_properties_to_event( $new_event, $date );

			if ( empty( $dates[ $date ] ) ) {
				$dates[ $date ] = [];
			}

			$dates[ $date ][ $date . ' - ' . $new_event->ID ] = $new_event;
		}

		return $dates;
	}

	/**
	 * Gets the first and last dates included in the result set.
	 *
	 * @since 5.7.0
	 *
	 * @param array $event_dates Associative array indexed by date of events in the initial result set.
	 *
	 * @return array
	 */
	protected function get_first_and_last_dates_in_the_result_set( array $event_dates ) {
		$dates_in_result_set = array_keys( $event_dates );

		if ( count( $dates_in_result_set ) > 1 ) {
			$first_date = Dates::build_date_object( array_shift( $dates_in_result_set ) );
			$last_date  = Dates::build_date_object( array_pop( $dates_in_result_set ) );
		} else {
			$first_date = Dates::build_date_object( array_shift( $dates_in_result_set ) );
			$last_date  = $first_date;
		}

		return [ $first_date, $last_date ];
	}

	/**
	 * Include any events that start before events in the result set that overlap with the result set.
	 *
	 * @since 5.7.0
	 *
	 * @param array $events_by_date Nested array of events in result set indexed by date.
	 * @param \WP_Post $previous_event Event right before the view's result set.
	 * @param array $injectable_events Nested array of injectable events indexed by date.
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	protected function maybe_include_overlapping_events( array $events_by_date, \WP_Post $previous_event, array $injectable_events ) {
		list( $first_date, $last_date ) = $this->get_first_and_last_dates_in_the_result_set( $events_by_date );

		$previous_event_date                  = $previous_event->dates->start;
		$first_date_beginning_of_day          = tribe_beginning_of_day( $first_date->format( Dates::DBDATEFORMAT ), 'Y-m-d' );
		$previous_event_date_beginning_of_day = tribe_beginning_of_day( $previous_event_date->format( Dates::DBDATEFORMAT ), 'Y-m-d' );

		$overlapping_events = [];

		if ( $previous_event_date_beginning_of_day !== $first_date_beginning_of_day ) {
			$start_before         = $this->get_events_that_start_before_and_end_between( $first_date, $last_date );
			$start_and_end_around = $this->get_events_that_start_and_end_around( $first_date, $last_date );
			$overlapping_events   = array_merge( $start_before, $start_and_end_around );
		}

		foreach ( $overlapping_events as $event ) {
			$start_date           = $event->dates->start_display;
			$end_date             = $event->dates->end_display;
			$formatted_start_date = tribe_beginning_of_day( $start_date->format( Dates::DBDATEFORMAT ), 'Y-m-d' );

			if ( ! isset( $injectable_events[ $formatted_start_date ] ) ) {
				$injectable_events[ $formatted_start_date ] = [];
			}

			$injectable_events = $this->maybe_extend_event_to_other_dates( $event, $start_date, $end_date, $injectable_events );
		}

		return $injectable_events;
	}

	/**
	 * Merge injectable events into the array of dates returned by the result set.
	 *
	 * Any injectable event that occurs on a date that does not appear in the result set will be excluded.
	 *
	 * @since 5.7.0
	 *
	 * @param array $injectable_events Nested array of events that can be injected indexed by date.
	 * @param array $events_by_date Nested array of events in result set indexed by date.
	 *
	 * @return array
	 */
	protected function inject_events_into_result_dates( array $injectable_events, array $events_by_date ) {
		foreach ( $injectable_events as $date => $events ) {
			if ( ! isset( $events_by_date[ $date ] ) ) {
				$events_by_date[ $date ] = [];
			}

			$events_by_date[ $date ] = array_merge( $events, $events_by_date[ $date ] );
			ksort( $events_by_date[ $date ] );
		}

		return $events_by_date;
	}

	/**
	 * @inheritdoc
	 *
	 * @since 5.7.0
	 */
	public static function get_asset_origin( $slug ) {
		return tribe( 'events-pro.main' );
	}
}
