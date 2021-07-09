<?php
/**
 * Countdown Widget
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Pro\Views\V2\Widgets;

use \Tribe\Events\Views\V2\Widgets\Widget_Abstract;
use Tribe__Context as Context;

/**
 * Class for the Countdown Widget.
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Widget_Countdown extends Widget_Abstract {
	/**
	 * {@inheritDoc}
	 */
	protected static $widget_in_use;

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected static $widget_slug = 'event-countdown';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected $view_slug = 'widget-countdown';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected static $widget_css_group = 'event-countdown-widget';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	public $id_base = 'tribe-events-countdown-widget';

	/**
	 * {@inheritDoc}
	 *
	 * @var array<string,mixed>
	 */
	protected $default_arguments = [
		// View options.
		'view'                      => null,
		'should_manage_url'         => false,

		// Countdown widget options.
		'id'                        => null,
		'alias-slugs'               => null,
		'title'                     => '',
		'type'                      => 'next-event',
		'event'                     => null,
		'show_seconds'              => true,
		'complete'                  => '',
		'jsonld_enable'             => true,
	];

	/**
	 * {@inheritDoc}
	 */
	public function get_validated_arguments_map() {
		return array_merge(
			$this->validate_arguments_map,
			[
				'type' => [ $this, 'validate_countdown_type' ],
			]
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_default_widget_name() {
		return esc_html_x(
			'Events Countdown',
			'The name of the Countdown Widget.',
			'tribe-events-calendar-pro'
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_default_widget_options() {
		return [
			'description' => esc_html_x(
				'Displays the time remaining until a specified event.',
				'The description of the Countdown Widget.',
				'tribe-events-calendar-pro'
			),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function setup_view( $_deprecated ) {
		parent::setup_view( $_deprecated );

		add_filter( 'tribe_customizer_should_print_widget_customizer_styles', '__return_true' );
		add_filter( 'tribe_customizer_inline_stylesheets', [ $this, 'add_full_stylesheet_to_customizer' ], 12 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function update( $new_instance, $old_instance ) {
		$updated_instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$updated_instance['title']               = wp_strip_all_tags( $new_instance['title'] );
		$updated_instance['type']                = ! empty( $new_instance['type'] ) ? $new_instance['type'] : $this->default_arguments['type'];
		$updated_instance['event']               = ! empty( $new_instance['event'] ) && '-1' !== $new_instance['event'] ? absint( $new_instance['event'] ) : null;
		$updated_instance['complete']            = wp_strip_all_tags( $new_instance['complete'] );
		$updated_instance['show_seconds']        = ! empty( $new_instance['show_seconds'] );
		$updated_instance['jsonld_enable']       = ! empty( $new_instance['jsonld_enable'] );

		if ( 'future-event' === $updated_instance['type'] ) {
			$updated_instance['type'] = 'next-event';
		}

		return $this->filter_updated_instance( $updated_instance, $new_instance );
	}

	/**
	 * {@inheritDoc}
	 */
	public function setup_admin_fields() {
		return [
			'title'          => [
				'type'  => 'text',
				'label' => _x(
					'Title:',
					'The label for the title field of the Countdown Widget.',
					'tribe-events-calendar-pro'
				),
			],
			'type'           => [
				'type'     => 'fieldset',
				'classes'  => 'tribe-common-form-control-checkbox-radio-group',
				'label'    => _x(
					'Countdown to:',
					'The label for the type field of the Countdown Widget.',
					'tribe-events-calendar-pro'
				),
				'children' => [
					[
						'type'         => 'radio',
						'label'        => _x(
							'Next upcoming event',
							'Label for the "countdown to a single event" option.',
							'tribe-events-calendar-pro'
						),
						'button_value' => 'next-event',
					],
					[
						'type'         => 'radio',
						'label'        => _x(
							'Specific event',
							'Label for the "countdown to a single event" option.',
							'tribe-events-calendar-pro'
						),
						'button_value' => 'single-event',
					],
					'type_container' => [
						'type'       => 'fieldset',
						'classes'    => 'tribe-dependent',
						'dependency' => [
							'ID' => 'type-single-event',
							'is-checked' => true,
							'parent'     => '.tribe-common-form-control-checkbox-radio-group',
						],
						'children'   => [
							'event'              => [
								'type'    => 'event-dropdown',
								'label'   => _x(
									'Event:',
									'The label for the event field of the Countdown Widget.',
									'tribe-events-calendar-pro'
								),
								'placeholder' => sprintf(
									/* Translators: 1: single event term */
									esc_html__( 'Select an %1$s', 'tribe-events-calendar-pro' ),
									tribe_get_event_label_singular_lowercase()
								),
								'disabled' => '',
								'options' => [
									[
										'text'  => 'Choose an event.',
										'value' => '',
									],
								],
							],
						],
					],
				],
			],
			'complete'       => [
				'type'        => 'text',
				'label'       => _x(
					'Countdown Completed Text',
					'The label for the field to change the displayed text on countdown completion.',
					'tribe-events-calendar-pro'
				),
				'description' => _x(
					'On “Next Event” type of countdown, this text will only show when there are no events to show.',
					'A note about what this shows when "Next Event" is the countdown type.',
					'tribe-events-calendar-pro'
				),
			],
			'show_seconds'   => [
				'type'  => 'checkbox',
				'label' => _x(
					'Show seconds?',
					'The label for the option to show seconds in the countdown widget.',
					'tribe-events-calendar-pro'
				),
			],
			'jsonld_enable'  => [
				'type'  => 'checkbox',
				'label' => _x(
					'Generate JSON-LD data',
					'The label for the option to enable JSON-LD in the List Widget.',
					'tribe-events-calendar-pro'
				),
			],
		];
	}

	/**
	 * Add full events countdown widget stylesheets to customizer styles array to check.
	 *
	 * @since 5.3.0
	 *
	 * @param array<string> $sheets Array of sheets to search for.
	 *
	 * @return array Modified array of sheets to search for.
	 */
	public function add_full_stylesheet_to_customizer( $sheets ) {
		return array_merge( (array) $sheets, [ 'tribe-events-pro-widgets-v2-countdown-full' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_default_arguments() {
		parent::setup_default_arguments();

		$this->default_arguments['complete'] = esc_attr__( 'Hooray!', 'tribe-events-calendar-pro' );

		return $this->default_arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function args_to_context( array $arguments, Context $context ) {
		$alterations                      = parent::args_to_context( $arguments, $context );
		$alterations['widget_title']      = ! empty( $arguments['title'] ) ? $arguments['title'] : '';
		$alterations['jsonld_enable']     = (int) tribe_is_truthy( $arguments['jsonld_enable'] );
		$alterations['show_seconds']      = tribe_is_truthy( $arguments['show_seconds'] );
		$alterations['complete']          = wp_strip_all_tags( $arguments['complete'] );

		return $this->filter_args_to_context( $alterations );
	}

	/**
	 * This function grabs and returns the passed event/event ID as an object.
	 * If none is passed it first looks for the next upcoming event
	 * if no upcoming event is found, the last (most recent) event.
	 *
	 * @since 5.3.0
	 *
	 * @param null|int|\WP_Post $event  The event ID or post object or `null` to use the global one.
	 *
	 * @return array|mixed|void|\WP_Post|null See tribe_get_event() for details.
	 */
	public function get_fallback_event( $event = null ) {
		// If we have an event specified, use it.
		if ( ! empty( $event ) ) {
			return tribe_get_event( $event );
		}

		$future_event = tribe_events()->where( 'start_date', tribe_context()->get( 'now', 'now' ) )->first();

		// If there is an upcoming event, use it.
		if ( ! empty( $future_event ) ) {
			return $future_event;
		}

		// If there are NO upcoming events, use the last event (will show as completed).
		return tribe_events()->where( 'ends_before', tribe_context()->get( 'now', 'now' ) )->order( 'DESC' )->first();
	}

	/**
	 * Returns the rendered View HTML code.
	 *
	 * @since 5.5.0.1
	 *
	 * @return string Rendered View HTML code.
	 */
	public function get_html() {
		$arguments = $this->get_arguments();
		$widget_obj = $this;
		$callback = static function ( $template_vars, $view ) use ( $arguments, $widget_obj ) {
			// Use set event or the next upcoming one.
			$template_vars['event'] = $widget_obj->get_fallback_event( $arguments['event'] );

			// The widget only uses one event, but some things expect an array of event(s) here (like JSON_LD).
			$template_vars['events'] = (array) $template_vars['event'];

			list(
				$template_vars['count_to_date'],
				$template_vars['count_to_stamp'],
				$template_vars['event_done']
			) = $view->calculate_countdown( $template_vars['event'] );

			return $template_vars;
		};

		add_filter( 'tribe_events_views_v2_view_template_vars', $callback, 10, 2 );

		$html = parent::get_html();

		remove_filter( 'tribe_events_views_v2_view_template_vars', $callback, 10 );

		return $html;
	}

	/**
	 * Validates the countdown type when setting up the widget.
	 *
	 * @since 5.5.0
	 *
	 * @param string $value Current value for the type of countdown.
	 *
	 * @return string
	 */
	public function validate_countdown_type( $value ) {
		// Only modify when it's a 'future-event' which is the legacy value.
		if ( 'future-event' !== $value ) {
			return $value;
		}

		return 'next-event';
	}
}
