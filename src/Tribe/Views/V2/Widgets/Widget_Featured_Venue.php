<?php
/**
 * Featured Venue Widget
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Pro\Views\V2\Widgets;

use Tribe__Context as Context;
use Tribe\Events\Views\V2\Widgets\Widget_Abstract;

/**
 * Class for the Featured Venue Widget.
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Widget_Featured_Venue extends Widget_Abstract {
	/**
	 * {@inheritDoc}
	 */
	protected static $widget_in_use;

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected static $widget_slug = 'featured-venue';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected $view_slug = 'widget-featured-venue';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected static $widget_css_group = 'featured-venue-widget';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	public $id_base = 'tribe-events-venue-widget';

	/**
	 * {@inheritDoc}
	 *
	 * @var array<string,mixed>
	 */
	protected $default_arguments = [
		// View options.
		'view'              => null,
		'should_manage_url' => false,

		// Event widget options.
		'id'                => null,
		'alias-slugs'       => null,
		'title'             => '',
		'venue_ID'          => null,
		'count'             => 3,
		'hide_if_empty'     => false,
		'jsonld_enable'     => true,
	];

	/**
	 * {@inheritDoc}
	 */
	public static function get_default_widget_name() {
		return esc_html_x( 'Events Featured Venue', 'The name of the Featured Venue.', 'tribe-events-calendar-pro' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_default_widget_options() {
		return [
			'description' => esc_html_x( 'Displays a list of upcoming events at a specific venue.', 'The description of the Featured Venue Widget.', 'tribe-events-calendar-pro' ),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function setup_view( $_deprecated ) {
		parent::setup_view( $_deprecated );

		add_filter( 'tribe_customizer_should_print_widget_customizer_styles', '__return_true' );
		add_filter( 'tribe_customizer_inline_stylesheets', [ $this, 'add_full_stylesheet_to_customizer' ], 12, 2 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function update( $new_instance, $old_instance ) {
		$updated_instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$updated_instance['title']         = wp_strip_all_tags( $new_instance['title'] );
		$updated_instance['venue_ID']      = $new_instance['venue_ID'];
		$updated_instance['count']         = $new_instance['count'];
		$updated_instance['hide_if_empty'] = ! empty( $new_instance['hide_if_empty'] );
		$updated_instance['jsonld_enable'] = ! empty( $new_instance['jsonld_enable'] );

		return $this->filter_updated_instance( $updated_instance, $new_instance );
	}

	/**
	 * {@inheritDoc}
	 */
	public function setup_admin_fields() {
		return [
			'title'         => [
				'label' => _x( 'Title:', 'The label for the field of the title of the Featured Venue Widget.', 'tribe-events-calendar-pro' ),
				'type'  => 'text',
			],
			'venue_ID'      => [
				'type'        => 'venue-dropdown',
				'label'       => _x( 'Venue:', 'The label for the venue field of the Featured Venue Widget.', 'tribe-events-calendar-pro' ),
				'placeholder' => sprintf( /* Translators: 1: single event term */ esc_html__( 'Select an %1$s', 'tribe-events-calendar-pro' ), tribe_get_venue_label_singular() ),
				'disabled'    => '',
				'options'     => [
					[
						'text'  => _x( 'Choose a venue.', 'The label to choose the venue to show in the Featured Venue Widget.', 'tribe-events-calendar-pro' ),
						'value' => '',
					],
				],
				'selected'    => $this->get_default_venue_id(),
			],
			'count'         => [
				'label'   => _x( 'Show:', 'The label for the amount of events to show in the Featured Venue Widget.', 'tribe-events-calendar-pro' ),
				'type'    => 'number',
				'default' => $this->default_arguments['count'],
				'min'     => 1,
				'max'     => 10,
				'step'    => 1,
			],
			'hide_if_empty' => [
				'label' => _x( 'Hide this widget if there are no upcoming events.', 'The label for the option to hide the Featured Venue Widget if no upcoming events.', 'tribe-events-calendar-pro' ),
				'type'  => 'checkbox',
			],
			'jsonld_enable' => [
				'label' => _x( 'Generate JSON-LD data', 'The label for the option to enable JSON-LD in the Featured Venue Widget.', 'tribe-events-calendar-pro' ),
				'type'  => 'checkbox',
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function add_hooks() {
		parent::add_hooks();

		add_filter( 'tribe_events_virtual_assets_should_enqueue_widget_styles', '__return_true' );
		add_filter( 'tribe_events_virtual_assets_should_enqueue_widget_groups', [ $this, 'add_self_to_virtual_widget_groups' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function remove_hooks() {
		parent::remove_hooks();

		remove_filter( 'tribe_events_virtual_assets_should_enqueue_widget_groups', [ $this, 'add_self_to_virtual_widget_groups'] );
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
	protected function args_to_context( array $arguments, Context $context ) {
		$alterations = parent::args_to_context( $arguments, $context );

		// Add venue id
		$alterations['venue'] = (int) absint( $arguments['venue_ID'] );

		// Enable JSON-LD?
		$alterations['jsonld_enable'] = (int) tribe_is_truthy( $arguments['jsonld_enable'] );

		// Hide widget if no events.
		$alterations['no_upcoming_events'] = tribe_is_truthy( $arguments['hide_if_empty'] );

		// Add posts per page.
		$alterations['events_per_page'] = (int) isset( $arguments['count'] ) && $arguments['count'] > 0 ? (int) $arguments['count'] : 5;

		return $this->filter_args_to_context( $alterations );
	}

	/**
	 * Add full events featured venue widget stylesheets to customizer styles array to check.
	 *
	 * @since 5.3.0
	 *
	 * @param array<string> $sheets       Array of sheets to search for.
	 * @param string        $css_template String containing the inline css to add.
	 *
	 * @return array Modified array of sheets to search for.
	 */
	public function add_full_stylesheet_to_customizer( $sheets, $css_template ) {
		return array_merge( $sheets, [ 'tribe-events-widgets-v2-events-featured-venue-full' ] );
	}

	/**
	 * Get the first alphabetical venue id as the default.
	 *
	 * @since 5.3.0
	 *
	 * @return int|null $venue_id The venue id for the default venue.
	 */
	public function get_default_venue_id() {

		$venue_id = tribe_venues()
				->fields( 'ids' )
				->order_by( 'post_title', 'ASC' )
				->first();

		/**
		 * Filter the default venue for the Featured Venue Widget.
		 *
		 * @since 5.3.0
		 *
		 * @param int|null              $venue_id The venue id for the default venue.
		 * @param Widget_Featured_Venue $this     This featured widget venue post object.
		 */
		return apply_filters( 'tribe_events_widget_featured_venue_default_venue_id', $venue_id, $this );
	}
}
