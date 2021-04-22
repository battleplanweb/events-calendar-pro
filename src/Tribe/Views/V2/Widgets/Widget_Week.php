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

/**
 * Class for the Week Widget.
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Widget_Week extends Widget_Abstract {
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
	protected $view_slug = 'widget-week';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected static $widget_css_group = 'events-week-widget';

	/**
	 * {@inheritDoc}
	 *
	 * @var array<string,mixed>
	 */
	protected $default_arguments = [
		// View options.
		'view'                      => null,
		'should_manage_url'         => false,

		// week widget options.
		'id'                        => null,
		'alias-slugs'               => null,
		'title'                     => '',
		'layout'                    => 'vertical',
		'count'                     => 3,
		'operand'                   => 'OR',
		'hide-header'               => true,
		'hide-view-switcher'        => true,
		'hide-search'               => true,
		'hide-datepicker'           => true,
		'hide-export'               => true,
		'jsonld_enable'             => true,
	];

	/**
	 * {@inheritDoc}
	 */
	public static function get_default_widget_name() {
		return esc_html( sprintf(
			_x(
				'%1$s This Week',
				'The name of the This Week Widget.',
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
			_x( 'Display %1$s by day for the week.', 'Description of the This Week Widget.', 'tribe-events-calendar-pro' ),
			tribe_get_event_label_plural_lowercase()
		);
		return [
			'description' => esc_html( sprintf(
				_x( 'Display %1$s by day for the week.', 'Description of the This Week Widget.', 'tribe-events-calendar-pro' ),
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
	public function update( $new_instance, $old_instance ) {
		$updated_instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$updated_instance['title']               = wp_strip_all_tags( $new_instance['title'] );
		$updated_instance['jsonld_enable']       = ! empty( $new_instance['jsonld_enable'] );

		return $this->filter_updated_instance( $updated_instance, $new_instance );
	}

	/**
	 * {@inheritDoc}
	 */
	public function setup_admin_fields() {
		$admin_fields = [
			'title'          => [
				'type'  => 'text',
				'label' => _x(
					'Title:',
					'The label for the title field of the Week Widget.',
					'tribe-events-calendar-pro'
				),
			],
			'layout' => [
				'type'  => 'dropdown',
				'label' => _x(
					'Layout:',
					'The label for the layout field of the Week Widget.',
					'tribe-events-calendar-pro'
				),
				'default' => $this->default_arguments['layout'],
				'options' => [
					[
						'value' => 'vertical',
						'text'  => _x(
							'Vertical Layout',
							'The text for the vertical layout option.',
							'tribe-events-calendar-pro'
						)
					],
					[
						'value' => 'horizontal',
						'text'  => _x(
							'Horizontal Layout',
							'The text for the horizontal layout option.',
							'tribe-events-calendar-pro'
						)
					],
				],
			],
			'count' => [
				'type'  => 'number',
				'label' => _x(
					'Number of events to show per day:',
					'tribe-events-calendar-pro'
				),
				'default' => $this->default_arguments['count'],
				'min'  => 1,
				'max'  => 10,
				'step' => 1,
			],
		];


		// Add the taxonomy filter controls. Before the JSON checkbox.
		$admin_fields = array_merge( $admin_fields, tribe( 'pro.views.v2.widgets.taxonomy' )->get_taxonomy_admin_section() );

		$admin_fields ['jsonld_enable']  = [
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
		$alterations                      = parent::args_to_context( $arguments, $context );
		$alterations['widget_title']      = ! empty( $arguments['title'] ) ? $arguments['title'] : '';
		$alterations['jsonld_enable']     = (int) tribe_is_truthy( $arguments['jsonld_enable'] );

		return $this->filter_args_to_context( $alterations );
	}

	/**
	 * Fetches the arguments that we will pass down to the shortcode.
	 *
	 * @since 5.5.0
	 *
	 * @see \Tribe\Shortcode\Utils::get_attributes_string()
	 *
	 * @return array Arguments passed down to the shortcode.
	 */
	public function get_shortcode_args() {
		$args = [
			'is-widget'         => true,
			'view'              => $this->view_slug,
			'container-classes' => 'tribe-events-widget tribe-events-widget-' . static::get_widget_slug(),
		];

		/* @var Taxonomy_Filter $taxonomy_filters */
		$taxonomy_filters = tribe( 'pro.views.v2.widgets.taxonomy' );

		// Handle tax filters.
		if ( ! empty( $arguments['filters'] ) ) {
			$alterations            = $taxonomy_filters->set_taxonomy_args( $arguments['filters'], $arguments['operand'] );
			$alterations['operand'] = $arguments['operand'];
		}

		return $args;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html() {
		$attributes_string = \Tribe\Shortcode\Utils::get_attributes_string( $this->get_shortcode_args() );

		$html = do_shortcode( "[tribe_events {$attributes_string}]" );

		return $html;
	}
}
