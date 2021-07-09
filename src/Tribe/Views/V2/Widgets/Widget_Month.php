<?php
/**
 * Month Widget
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Pro\Views\V2\Widgets;

use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Widgets\Widget_Abstract;
use Tribe__Context as Context;
use Tribe__Date_Utils as Dates;
use Tribe\Events\Views\V2\Template as View_Template;

/**
 * Class for the Month Widget.
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Widget_Month extends Widget_Abstract {
	use Traits\Widget_Shortcode {
		get_shortcode_args as protected get_default_shortcode_args;
	}

	/**
	 * {@inheritDoc}
	 */
	protected static $widget_in_use;

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected static $widget_slug = 'events-month';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected $view_slug = 'month';

	/**
	 * Stores the shortcode tag used for rendering.
	 *
	 * @since 5.5.0
	 *
	 * @var string
	 */
	protected $shortcode_tag = 'tribe_events';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected static $widget_css_group = 'events-month-widget';

	/**
	 * Holds any calendar messages to output in the widget.
	 *
	 * @since 5.6.0
	 *
	 * @var string
	 */
	protected $messages = '';

	/**
	 * {@inheritDoc}
	 *
	 * @var array<string,mixed>
	 */
	protected $default_arguments = [
		// View options.
		'view'              => null,
		'should_manage_url' => false,

		// Widget options.
		'id'                => null,
		'alias-slugs'       => null,
		'title'             => '',
		'count'             => 5,
		'operand'           => 'OR',
		'featured'          => null,
		'main-calendar'     => true,
		'jsonld_enable'     => true,
	];

	/**
	 * {@inheritDoc}
	 */
	public static function get_default_widget_name() {
		return esc_html( sprintf(
			_x(
				'%1$s Calendar',
				'The name of the Month Widget.',
				'tribe-events-calendar-pro'
			),
			tribe_get_event_label_plural()
		) );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_default_widget_options() {
		return [
			'description' => esc_html( sprintf(
				_x( 'The %1$s calendar mini calendar widget', 'Description of the Events Calendar Widget.', 'tribe-events-calendar-pro' ),
				tribe_get_event_label_plural_lowercase()
			) ),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function add_hooks() {
		parent::add_hooks();

		add_filter( 'tribe_get_option', [ $this, 'filter_month_view_cache_option' ], 10, 3 );
		add_filter( 'tribe_events_virtual_assets_should_enqueue_widget_styles', '__return_true' );
		add_filter( 'tribe_events_virtual_assets_should_enqueue_widget_groups', [ $this, 'add_self_to_virtual_widget_groups' ] );
		add_filter( 'tribe_template_include_html:events/v2/month/top-bar', [ $this, 'filter_top_bar' ], 10, 4 );
		add_filter( 'tribe_template_before_include_html:events/v2/month/mobile-events', [ $this, 'filter_mobile_events' ], 10, 4 );
		add_filter( 'tribe_template_before_include_html:events/v2/month/calendar-body/day', [ $this, 'filter_month_day' ], 10, 4 );
		add_action( 'tribe_template_after_include:events/v2/components/after', [ $this, 'action_components_after' ], 15, 3 );
		add_filter( 'tribe_template_include_html:events/v2/month/mobile-events/mobile-day/more-events', [ $this, 'filter_html_remove_managed_link' ], 15, 4 );
		add_filter( 'tribe_events_views_v2_show_latest_past_events_view', '__return_false' );

		do_action( 'tribe_events_pro_shortcode_month_widget_add_hooks' );

	}

	/**
	 * {@inheritDoc}
	 */
	protected function remove_hooks() {
		parent::remove_hooks();

		remove_filter( 'tribe_get_option', [ $this, 'filter_month_view_cache_option' ], 10 );
		remove_filter( 'tribe_events_virtual_assets_should_enqueue_widget_groups', [ $this, 'add_self_to_virtual_widget_groups' ] );
		remove_filter( 'tribe_template_include_html:events/v2/month/top-bar', [ $this, 'filter_top_bar' ], 10 );
		remove_filter( 'tribe_template_before_include_html:events/v2/month/mobile-events', [ $this, 'filter_mobile_events' ], 10 );
		remove_filter( 'tribe_template_before_include_html:events/v2/month/calendar-body/day', [ $this, 'filter_month_day' ], 10 );
		remove_action( 'tribe_template_after_include:events/v2/components/after', [ $this, 'action_components_after' ], 15 );
		remove_filter( 'tribe_template_include_html:events/v2/month/mobile-events/mobile-day/more-events', [ $this, 'filter_html_remove_managed_link' ], 15 );
		remove_filter( 'tribe_events_views_v2_show_latest_past_events_view', '__return_false' );

		do_action( 'tribe_events_pro_shortcode_month_widget_remove_hooks' );
	}

	public function filter_month_view_cache_option(  $value, $optionName ) {
		if ( 'enable_month_view_cache' !== $optionName ) {
			return $value;
		}

		return false;
	}

	public function action_components_after( $file, $name, $template ) {
		if ( ! $template instanceof View_Template ) {
			return;
		}

		$view_slug = $template->get_view()->get_slug();

		if ( 'month' !== $view_slug && 'widget-month' !== $view_slug ) {
			return;
		}

		return $this->get_shortcode_template()->template( 'components/view-more', $this->get_template_vars() );
	}

	/**
	 * Add this widget's css group to the VE list of widget groups to load icon styles for.
	 *
	 * @since 5.6.0
	 *
	 * @param array<string> $widgets The list of widgets
	 *
	 * @return array<string> The modified list of widgets.
	 */
	public function add_self_to_virtual_widget_groups( $groups ) {
		$groups[] = static::get_css_group();

		return $groups;
	}

	/**
	 * Replaces the top-bar (nav) with our own for the widget.
	 *
	 * @since  5.5.0
	 *
	 * @param string           $html     The final HTML.
	 * @param string           $file     Complete path to include the PHP File.
	 * @param array            $name     Template name.
	 * @param \Tribe__Template $template Current instance of the Tribe__Template.
	 */
	public function filter_top_bar( $html, $file, $name, $template ) {
		$now          = Dates::build_date_object( $template->get( 'now', 'now' ) );
		$today        = $template->get( 'today', 'today' );
		$request_date = Dates::build_date_object( $template->get( 'url_event_date', $today ) );

		$new_vars = [
			'now'          => $now->format( 'F Y' ),
			'request_date' => $request_date->format_i18n( 'F Y' ),
			'prev_url'     => $template->get( 'prev_url' ),
			'next_url'     => $template->get( 'next_url' )
		];

		$template_vars = array_merge( $this->get_template_vars(), $new_vars );

		return $this->get_shortcode_template()->template( 'components/month-top-bar', $template_vars, false );
	}

	/**
	 * Replaces the mobile events list with our own for the widget.
	 *
	 * @since  5.5.0
	 *
	 * @param string           $html     The final HTML.
	 * @param string           $file     Complete path to include the PHP File.
	 * @param array            $name     Template name.
	 * @param \Tribe__Template $template Current instance of the Tribe__Template.
	 *
	 * @return string $html The final HTML.
	 */
	public function filter_mobile_events( $html, $file, $name, $template ) {
		$today           = Dates::build_date_object( $template->get( 'url_event_date', $template->get( 'today', 'today' ) ) );
		$formatted_today = $today->format('Y-m-d');
		$days            = $template->get( 'days', [] );
		$the_day_events  = $days[ $formatted_today ][ 'events' ];
		$events_per_day  = absint( $this->get_argument( 'count' ) );

		if( $events_per_day <= count( $the_day_events ) ) {
			return $html;
		}

		$the_day_events = $this->the_day_events( $template, $events_per_day, $the_day_events );

		$days[ $formatted_today ]['events']       = $the_day_events;
		$days[ $formatted_today ]['found_events'] = count($the_day_events);

		$template->set( 'days', $days );

		return $html;
	}

	/**
	 * Pads "today" in days list with events up to the limit.
	 *
	 * @since 5.6.0
	 *
	 * @param string           $html     The final HTML.
	 * @param string           $file     Complete path to include the PHP File.
	 * @param array            $name     Template name.
	 * @param \Tribe__Template $template Current instance of the Tribe__Template.
	 *
	 * @return string $html The final HTML.
	 */
	public function filter_month_day( $html, $file, $name, $template ) {
		$today = Dates::build_date_object( $template->get( 'url_event_date', $template->get( 'today', 'today' ) ) );

		if ( $today->format('Y-m-d') !== $template->get( 'day_date' ) || tribe_context()->doing_ajax()  ) {
			return $html;
		}

		$day            = $template->get( 'day', [] );
		$the_day_events = $day[ 'events' ];
		$events_per_day = absint( $this->get_argument( 'count' ) );
		$the_day_events = $this->the_day_events( $template, $events_per_day, $the_day_events );

		if ( 0 < count($the_day_events) ) {
			$day['is-widget-today'] = true;
			$template->set( 'day', $day );
		}

		return $html;
	}

	/**
	 * Gets the events for "today", used in filter_month_day() to add to the list if needed.
	 *
	 * @since 5.6.0
	 *
	 * @param \Tribe__Template $template       Current instance of the Tribe__Template.
	 * @param int              $events_per_day The number of events to display per day.
	 * @param array            $the_day_events The events for "today".
	 *
	 * @return array $the_day_events The modified events for "today".
	 */
	public function the_day_events( $template, $events_per_day, $the_day_events ) {
		$days = $template->get( 'days', [] );
		$new_day = Dates::build_date_object( $template->get( 'url_event_date', $template->get( 'today', 'today' ) ) );

		while( $events_per_day >= count( $the_day_events ) ) {
			// add on day to "today".
			$new_day->add( new \DateInterval('P1D') );
			$new_day_formatted = $new_day->format('Y-m-d');

			// Bail if we've reached the end.
			if ( ! isset( $days[ $new_day_formatted ] ) ) {
				break;
			}

			$copied_events = $days[ $new_day_formatted ][ 'events' ];

			// Skip to the next day if no events.
			if ( 0 === count( $copied_events ) ) {
				continue;
			}

			$the_day_events = array_slice( array_merge( $the_day_events, $copied_events ), 0, $events_per_day );
		}

		return $the_day_events;
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
	protected function setup_default_arguments() {
		parent::setup_default_arguments();

		// Setup default title.
		$this->default_arguments['title'] = _x( 'Events Calendar', 'The default title of the Month Widget.', 'tribe-events-calendar-pro' );

		return $this->default_arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	public function update( $new_instance, $old_instance ) {
		$updated_instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$updated_instance['title']         = wp_strip_all_tags( $new_instance['title'] );
		$updated_instance['count']         = ! empty( $new_instance['count'] ) ? absint( $new_instance['count'] ) : 5;
		$updated_instance['jsonld_enable'] = ! empty( $new_instance['jsonld_enable'] );
		$updated_instance['operand']       = ! empty( $new_instance['operand'] ) ? $new_instance['operand'] : false;
		$updated_instance['filters']       = ! empty( $new_instance['filters'] ) ? tribe( 'pro.views.v2.widgets.taxonomy' )->format_taxonomy_filters( $new_instance['filters'] ) : false;

		return $this->filter_updated_instance( $updated_instance, $new_instance );
	}

	/**
	 * {@inheritDoc}
	 */
	public function setup_admin_fields() {
		$admin_fields = [
			'title' => [
				'type'  => 'text',
				'label' => _x(
					'Title:',
					'The label for the title field of the Month Widget.',
					'tribe-events-calendar-pro'
				),
				'default' => sprintf(
					_x(
						'%1$s Calendar',
						'The title of the Month Widget.',
						'tribe-events-calendar-pro'
					),
					tribe_get_event_label_plural()
				),
			],
			'count' => [
				'type'    => 'number',
				'label'   => _x(
					'Number of events to list below the widget calendar:',
					'tribe-events-calendar-pro'
				),
				'default' => $this->default_arguments['count'],
				'min'     => 1,
				'max'     => 10,
				'step'    => 1,
			],
		];

		// Add the taxonomy filter controls. Before the JSON checkbox.
		$admin_fields = array_merge( $admin_fields, tribe( 'pro.views.v2.widgets.taxonomy' )->get_taxonomy_admin_section() );

		$admin_fields ['jsonld_enable'] = [
			'type'  => 'checkbox',
			'label' => _x(
				'Generate JSON-LD data',
				'The label for the option to enable JSON-LD in the Month Widget.',
				'tribe-events-calendar-pro'
			),
		];

		return $admin_fields;
	}

	/**
	 * Add "full" events month widget stylesheets to customizer styles array to check.
	 *
	 * @since 5.3.0
	 *
	 * @param array<string> $sheets Array of sheets to search for.
	 *
	 * @return array Modified array of sheets to search for.
	 */
	public function add_full_stylesheet_to_customizer( $sheets ) {
		return array_merge( (array) $sheets, [ 'tribe-events-pro-widgets-v2-month-full' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function args_to_context( array $arguments, Context $context ) {
		$alterations                  = parent::args_to_context( $arguments, $context );
		$alterations['widget_title']  = ! empty( $arguments['title'] ) ? $arguments['title'] : '';
		$alterations['jsonld_enable'] = (int) tribe_is_truthy( $arguments['jsonld_enable'] );

		// Handle tax filters.
		if ( ! empty( $arguments['filters'] ) ) {
			/* @var Taxonomy_Filter $taxonomy_filters */
			$taxonomy_filters = tribe( 'pro.views.v2.widgets.taxonomy' );
			$alterations      = $taxonomy_filters->set_shortcode_taxonomy_params( $alterations, $arguments['filters'], $arguments['operand'] );
		}

		return $this->filter_args_to_context( $alterations );
	}

	/**
	 * Fetches the arguments that we will pass down to the shortcode.
	 *
	 * @see   \Tribe\Shortcode\Utils::get_attributes_string()
	 *
	 * @since 5.5.0
	 *
	 * @return array Arguments passed down to the shortcode.
	 */
	public function get_shortcode_args() {
		$default_args = $this->get_default_shortcode_args();
		$args         = [
			'month_events_per_day' => $this->arguments['count'],
		];

		if ( ! empty( $this->arguments['filters'] ) ) {
			/* @var Taxonomy_Filter $taxonomy_filters */
			$taxonomy_filters = tribe( 'pro.views.v2.widgets.taxonomy' );
			$args             = $taxonomy_filters->set_shortcode_taxonomy_params( $args, $this->arguments['filters'], $this->arguments['operand'] );
		}

		return array_merge( $default_args, $args );
	}

	/**
	 * Filters a given HTML to remove managed links attribute.
	 *
	 * @since  5.6.0
	 *
	 * @param string           $html     The final HTML.
	 * @param string           $file     Complete path to include the PHP File.
	 * @param array            $name     Template name.
	 * @param \Tribe__Template $template Current instance of the Tribe__Template.
	 *
	 * @return string  HTML after removing Attr for managed link.
	 */
	public function filter_html_remove_managed_link( $html, $file, $name, $template ) {
		return str_replace( 'data-js="tribe-events-view-link"', '', $html );
	}
}
