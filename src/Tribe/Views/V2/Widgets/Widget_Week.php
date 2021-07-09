<?php
/**
 * Week Widget
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Pro\Views\V2\Widgets;

use Tribe\Events\Views\V2\View;
use \Tribe\Events\Views\V2\Widgets\Widget_Abstract;
use Tribe__Context as Context;
use Tribe__Date_Utils as Dates;
use Tribe\Events\Views\V2\Template as View_Template;

/**
 * Class for the Week Widget.
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Widget_Week extends Widget_Abstract {
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
	protected static $widget_slug = 'events-week';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected $view_slug = 'week';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected static $widget_css_group = 'events-week-widget';

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
	 * @var array<string,mixed>
	 */
	protected $default_arguments = [
		// View options.
		'view'                => null,
		'should_manage_url'   => false,

		// week widget options.
		'id'                  => null,
		'alias-slugs'         => null,
		'title'               => '',
		'layout'              => 'vertical', // @todo Change to auto when we enable that option.
		'count'               => 3,
		'operand'             => 'OR',
		'hide-header'         => true,
		'hide-view-switcher'  => true,
		'hide-search'         => true,
		'hide-datepicker'     => true,
		'hide-export'         => true,
		'jsonld_enable'       => true,
		'week_offset'         => 0,
		'week_events_per_day' => null,
		'hide_weekends'       => false,
	];

	/**
	 * {@inheritDoc}
	 */
	public static function get_default_widget_name() {
		return esc_html( sprintf(
			_x(
				'%1$s By Week',
				'The name of the Events By Week Widget.',
				'tribe-events-calendar-pro'
			),
			tribe_get_event_label_plural()
		) );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_default_widget_options() {
		sprintf(
			_x( 'Display %1$s by day for the week.', 'Description of the Events By Week Widget.', 'tribe-events-calendar-pro' ),
			tribe_get_event_label_plural_lowercase()
		);

		return [
			'description' => esc_html( sprintf(
				_x( 'Display %1$s by day for the week.', 'Description of the Events By Week Widget.', 'tribe-events-calendar-pro' ),
				tribe_get_event_label_plural_lowercase()
			) ),
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
	protected function add_hooks() {
		parent::add_hooks();

		add_filter( 'tribe_events_virtual_assets_should_enqueue_widget_styles', '__return_true' );
		add_filter( 'tribe_events_virtual_assets_should_enqueue_widget_groups', [ $this, 'add_self_to_virtual_widget_groups' ] );
		add_filter( 'tribe_template_include_html:events-pro/v2/week/grid-body/events-row-header', '__return_false' );
		add_filter( 'tribe_template_include_html:events-pro/v2/week/grid-body/multiday-events-row-header', '__return_false' );
		add_filter( 'tribe_template_include_html:events-pro/v2/week/mobile-events/nav', '__return_false' );
		add_filter( 'tribe_template_include_html:events-pro/v2/week/top-bar', [ $this, 'filter_top_bar' ], 10, 4 );

		add_action( 'tribe_template_after_include:events/v2/components/after', [ $this, 'add_view_more_link' ], 15, 3 );

		add_filter( 'tribe_template_include_html:events-pro/v2/week/grid-body/events-day/more-events', [ $this, 'filter_html_remove_managed_link' ], 15, 4 );
		add_filter( 'tribe_template_include_html:events-pro/v2/week/mobile-events/day/more-events', [ $this, 'filter_html_remove_managed_link' ], 15, 4 );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function remove_hooks() {
		parent::remove_hooks();

		remove_filter( 'tribe_events_virtual_assets_should_enqueue_widget_groups', [ $this, 'add_self_to_virtual_widget_groups' ] );
		remove_filter( 'tribe_template_include_html:events-pro/v2/week/grid-body/events-row-header', '__return_false' );
		remove_filter( 'tribe_template_include_html:events-pro/v2/week/grid-body/multiday-events-row-header', '__return_false' );
		remove_filter( 'tribe_template_include_html:events-pro/v2/week/top-bar', [ $this, 'filter_top_bar' ], 10 );
		remove_filter( 'tribe_template_include_html:events-pro/v2/week/mobile-events/nav', '__return_false' );
		remove_filter( 'tribe_template_include_html:events-pro/v2/week/grid-body/events-day/more-events', [ $this, 'filter_html_remove_managed_link' ], 15 );
		remove_filter( 'tribe_template_include_html:events-pro/v2/week/mobile-events/day/more-events', [ $this, 'filter_html_remove_managed_link' ], 15 );

		remove_action( 'tribe_template_after_include:events/v2/components/after', [ $this, 'add_view_more_link' ], 15 );
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
	 * {@inheritDoc}
	 */
	public function update( $new_instance, $old_instance ) {
		$updated_instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$updated_instance['title']         = wp_strip_all_tags( $new_instance['title'] );
		$updated_instance['layout']        = $new_instance['layout'];
		$updated_instance['count']         = (int) $new_instance['count'];
		$updated_instance['week_offset']   = ! empty( $new_instance['week_offset'] ) ? (int) $new_instance['week_offset'] : $this->default_arguments['week_offset'];
		$updated_instance['hide_weekends'] = ! empty( $new_instance['hide_weekends'] );
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
			'title'  => [
				'type'  => 'text',
				'label' => _x(
					'Title:',
					'The label for the title field of the Week Widget.',
					'tribe-events-calendar-pro'
				),
			],
			'layout' => [
				'type'    => 'dropdown',
				'label'   => _x(
					'Layout:',
					'The label for the layout field of the Week Widget.',
					'tribe-events-calendar-pro'
				),
				'default' => $this->default_arguments['layout'],
				'options' => [
					/*
					@todo Uncomment when we have breakpoints and are ready. Don't forget to change the default in ../Shortcodes/Tribe_Events.php

					[
						'value' => 'auto',
						'text'  => _x(
							'Auto Layout',
							'The text for the automatic layout option.',
							'tribe-events-calendar-pro'
						)
					],
					*/
					[
						'value' => 'vertical',
						'text'  => _x(
							'Vertical',
							'The text for the vertical layout option.',
							'tribe-events-calendar-pro'
						)
					],
					[
						'value' => 'horizontal',
						'text'  => _x(
							'Horizontal',
							'The text for the horizontal layout option.',
							'tribe-events-calendar-pro'
						)
					],
				],
			],
			'count'  => [
				'type'    => 'number',
				'label'   => _x(
					'Number of events to show per day:',
					'tribe-events-calendar-pro'
				),
				'default' => $this->default_arguments['count'],
				'min'     => 1,
				'max'     => 10,
				'step'    => 1,
			],
			/* @todo Uncomment these when we are ready to release this functionality.
			 * 'week_offset' => [
			 * 'type'  => 'number',
			 * 'label' => _x(
			 * 'Week offset:',
			 * 'tribe-events-calendar-pro'
			 * ),
			 * 'tooltip' => __( 'Use this to show a week in the future (positive) or past (negative).', 'tribe-events-calendar-pro' ),
			 * 'default' => $this->default_arguments['week_offset'],
			 * 'min'  => -5,
			 * 'max'  => 5,
			 * 'step' => 1,
			 * ],
			 * 'hide_weekends' => [
			 * 'type'    => 'checkbox',
			 * 'label'   => __( 'Hide weekends in the widget', 'tribe-events-calendar-pro' ),
			 * 'tooltip' => __( 'Check this to only show weekdays in the widget. Defaults to the global setting.', 'tribe-events-calendar-pro' ),
			 * 'default' => tribe_get_option( 'week_view_hide_weekends', false ),
			 * ],
			 */
		];


		// Add the taxonomy filter controls. Before the JSON checkbox.
		$admin_fields = array_merge( $admin_fields, tribe( 'pro.views.v2.widgets.taxonomy' )->get_taxonomy_admin_section() );

		$admin_fields ['jsonld_enable'] = [
			'type'  => 'checkbox',
			'label' => _x(
				'Generate JSON-LD data',
				'The label for the option to enable JSON-LD in the Week View Widget.',
				'tribe-events-calendar-pro'
			),
		];

		return $admin_fields;
	}

	/**
	 * Add full events week widget stylesheets to customizer styles array to check.
	 *
	 * @since 5.3.0
	 *
	 * @param array<string> $sheets Array of sheets to search for.
	 *
	 * @return array Modified array of sheets to search for.
	 */
	public function add_full_stylesheet_to_customizer( $sheets ) {
		return array_merge( (array) $sheets, [ 'tribe-events-pro-widgets-v2-week-full' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function args_to_context( array $arguments, Context $context ) {
		$alterations                        = parent::args_to_context( $arguments, $context );
		$alterations['widget_title']        = ! empty( $arguments['title'] ) ? $arguments['title'] : '';
		$alterations['layout']              = ! empty( $arguments['layout'] ) ? $arguments['layout'] : $this->default_arguments['layout'];
		$alterations['week_offset']         = ! empty( $arguments['week_offset'] );
		$alterations['hide_weekends']       = ! empty( $arguments['hide_weekends'] );
		$alterations['week_events_per_day'] = absint( $arguments['count'] );
		$alterations['count']               = absint( $arguments['count'] );
		$alterations['jsonld_enable']       = (int) tribe_is_truthy( $arguments['jsonld_enable'] );

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
			'layout'              => $this->arguments['layout'],
			'week_offset'         => $this->arguments['week_offset'],
			'week_events_per_day' => $this->arguments['count'],
			'hide_weekends'       => $this->arguments['hide_weekends'] || tribe_get_option( 'week_view_hide_weekends' ),
		];

		if ( ! empty( $this->arguments['filters'] ) ) {
			/* @var Taxonomy_Filter $taxonomy_filters */
			$taxonomy_filters = tribe( 'pro.views.v2.widgets.taxonomy' );
			$args             = $taxonomy_filters->set_shortcode_taxonomy_params( $args, $this->arguments['filters'], $this->arguments['operand'] );
		}

		return array_merge( $default_args, $args );
	}

	/**
	 * Filters the top bar to add the widget header.
	 *
	 * @since 5.6.0
	 *
	 * @param string $html     The final HTML.
	 * @param string $file     Complete path to include the PHP File.
	 * @param array  $name     Template name.
	 * @param self   $template Current instance of the Tribe__Template.
	 *
	 * @return string $html The final HTML.
	 */
	public function filter_top_bar( $html, $file, $name, $template ) {
		$is_widget = $template->get( 'view' )->get_context()->get( 'is-widget' );

		if ( ! $is_widget ) {
			return $html;
		}

		$now           = Dates::build_date_object( $template->get( 'now', 'now' ) );
		$today         = $template->get( 'today', 'today' );
		$request_date  = Dates::build_date_object( $template->get( 'url_event_date', $today ) );
		$week_start    = (int) $template->get( 'week_start' )->format( 'w' );
		$hide_weekends = $this->arguments['hide_weekends'] || tribe_get_option( 'week_view_hide_weekends' );

		/**
		 * Allows filtering the "hide weekends" application to the widget - so we can hide them on main views
		 * and show them on widgets. Or the reverse.
		 *
		 * @since 5.6.0
		 *
		 * @param boolean $hide_weekends Whether to hide weekends on week view widget.
		 */
		$hide_weekends = apply_filters( 'tribe_events_pro_events_by_week_widget_hide_weekends', $hide_weekends );

		// If we're hiding weekends and the setting is to start on a Sunday or Saturday, start on Monday.
		if ( tribe_is_truthy( $hide_weekends ) ) {
			if ( 0 === $week_start || 6 === $week_start ) {
				$week_start = 1;
			}
		}

		// Fix mismatch between "week starts on" setting and today.
		while ( $week_start !== (int) $request_date->format( 'w' ) ) {
			$di = Dates::interval( "P1D" );
			// We back up so we don't lose "today" in our adjustments.
			$request_date = $request_date->sub( $di );
		}

		/**
		 * Allows filtering the date format for the widgets title.
		 *
		 * @since 5.6.0
		 *
		 * @param mixed $date_format The format to use for the date in the widget title.
		 */
		$date_format = apply_filters( 'tribe_events_pro_events_by_week_widget_title_date_format', tribe_get_date_format() );

		/* translators: %s: date of the first day of the week, like "April 01" */
		$request_date = sprintf(
			_x( 'Week of %s', 'The "week of" header', 'tribe-events-calendar-pro' ),
			$request_date->format( $date_format )
		);

		$new_vars = [
			'now'          => $now->format( 'F Y' ),
			'request_date' => $request_date,
			'prev_url'     => $template->get( 'prev_url' ),
			'next_url'     => $template->get( 'next_url' ),
			'layout'       => $this->get_argument( 'layout', 'vertical' ),
		];

		$template_vars = array_merge( $this->get_template_vars(), $new_vars );

		return $this->get_shortcode_template()->template( 'components/week-top-bar', $template_vars, false );
	}

	/**
	 * Adding a view more link to the bottom of the widget.
	 *
	 * @since 5.6.0
	 *
	 * @param string        $file     Complete path to include the PHP File.
	 * @param array         $name     Template name.
	 * @param View_Template $template Which template instance we are dealing with.
	 *
	 * @return false|string|void
	 */
	public function add_view_more_link( $file, $name, $template ) {
		if ( ! $template instanceof View_Template ) {
			return;
		}

		$view      = $template->get_view();
		$view_slug = $view->get_slug();

		// Bail if it's not week view for some reason.
		if ( 'week' !== $view_slug && 'widget-week' !== $view_slug ) {
			return;
		}

		$is_widget = $view->get_context()->get( 'is-widget' );

		if ( ! $is_widget ) {
			return;
		}

		// Don't show if there are no events at all (now or future)
		if ( empty( $template->get( 'events' ) ) ) {
			$now             = $view->get_context()->get( 'now', Dates::build_date_object()->format( 'Y-m-d H:i:s' ) );
			$from_date       = tribe_beginning_of_day( $now );
			$upcoming_events = (int) tribe_events()->where( 'starts_after', $from_date )->found();

			if ( empty( $upcoming_events ) ) {
				return;
			}
		}

		return $this->get_shortcode_template()->template( 'components/view-more', $this->get_template_vars() );
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
