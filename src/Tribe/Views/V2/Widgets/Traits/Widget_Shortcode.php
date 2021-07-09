<?php
/**
 * Widget Shortcode Templates
 *
 * @since   5.5.0
 *
 * @package Tribe\Events\Pro\Views\V2\Widgets
 */

namespace Tribe\Events\Pro\Views\V2\Widgets\Traits;

use Tribe\Events\Pro\Views\V2\Shortcodes\Tribe_Events;
use Tribe\Events\Views\V2\Theme_Compatibility;
use Tribe\Events\Views\V2\Views\Widgets\Widget_View;
use \Tribe__Template as Template;
use Tribe\Events\Views\V2\Widgets\Widget_Abstract;
use Tribe__Utils__Array as Arr;


/**
 * Class Widget_Shortcode
 *
 * @since   5.5.0
 *
 * @package Tribe\Events\Pro\Views\V2\Widgets\Traits
 */
trait Widget_Shortcode {
	/**
	 * Stores the instance pf the template.
	 *
	 * @since 5.5.0
	 *
	 * @var Template
	 */
	protected $shortcode_template;

	/**
	 * Whether the View should display the events bar or not.
	 *
	 * @since 5.5.0
	 *
	 * @var bool
	 */
	protected $display_events_bar = false;

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
		$args = [
			'id'                => $this->id,
			'view'              => $this->view_slug,
			'is-widget'         => true,
			'container-classes' => 'tribe-events-widget tribe-events-widget-' . static::get_widget_slug(),
			'should_manage_url' => false,
		];

		return $args;
	}

	/**
	 * Gets the shortcode tag used for rendering.
	 *
	 * @since 5.5.0
	 *
	 * @return string The Shortcode registered tag.
	 */
	public function get_shortcode_tag() {
		return $this->shortcode_tag;
	}

	/**
	 * Gets the shortcode tag used for rendering.
	 *
	 * @since 5.5.0
	 *
	 * @return string The Shortcode registered tag.
	 */
	public function get_shortcode_string() {
		$attributes_string = \Tribe\Shortcode\Utils::get_attributes_string( $this->get_shortcode_args() );
		$shortcode_tag     = $this->get_shortcode_tag();

		return "[{$shortcode_tag} {$attributes_string}]";
	}

	public function get_shortcode_template() {
		if ( ! isset( $this->shortcode_template ) ) {
			$this->set_shortcode_template();
		}

		return $this->shortcode_template;
	}

	public function set_shortcode_template() {
		$template = new Template();
		$template->set_template_origin( tribe( 'events-pro.main' ) );
		$template->set_template_folder( 'src/views/v2/widgets/shortcodes' );
		$template->set_template_folder_lookup( true );
		$template->set_template_context_extract( true );

		$this->shortcode_template = $template;
	}

	/**
	 * Sets up all the template vars for our templates
	 *
	 * @since 5.5.0
	 *
	 * @return array An array of template vars.
	 */
	public function get_template_vars() {
		$container_classes = [
			'tribe-common',
			'tribe-events',
			'tribe-events-widget-shortcode',
			'tribe-events-widget-shortcode-' . static::get_widget_slug(),
		];

		$compatibility_classes = Theme_Compatibility::get_container_classes();
		$container_classes     = array_merge( $compatibility_classes, $container_classes );


		$template_vars = [
			'shortcode_string'  => $this->get_shortcode_string(),
			'widget'            => $this,
			'container_classes' => $container_classes,
			'view_more_text'    => $this->get_view_more_text(),
			'view_more_title'   => $this->get_view_more_title(),
			'view_more_link'    => $this->get_view_more_link(),
		];

		return $template_vars;
	}

	/**
	 * @inheritDoc
	 */
	public function get_html() {
		$template_vars = $this->get_template_vars();

		return $this->get_shortcode_template()->template( static::get_widget_slug(), $template_vars, false );
	}

	/**
	 * Returns the widget "view more" text.
	 *
	 * @since 5.5.0
	 *
	 * @return string The widget "view more" text.
	 */
	public function get_view_more_text() {
		return tribe( Widget_View::class )->get_view_more_text();
	}

	/**
	 * Returns the widget "view more" title.
	 * Adds context as needed for screen readers.
	 * @see \Tribe\Events\Pro\Views\V2\Views\Widgets\Venue_View for an example.
	 *
	 * @since 5.5.0
	 *
	 * @return string The widget "view more" title.
	 */
	public function get_view_more_title() {
		return tribe( Widget_View::class )->get_view_more_title();
	}

	/**
	 * Returns the widget "view more" url.
	 *
	 * @since 5.5.0
	 *
	 * @return string The widget "view more" url.
	 */
	public function get_view_more_link() {
		return tribe( Widget_View::class )->get_view_more_link();
	}

	/**
	 * Maybe toggles the hooks for a widget class on a rest request.
	 *
	 * @since 5.5.0
	 *
	 * @param string           $slug    The current view Slug.
	 * @param array            $params  Params so far that will be used to build this view.
	 * @param \WP_REST_Request $request The rest request that generated this call.
	 *
	 */
	public static function maybe_toggle_hooks_for_rest( $slug, $params, \WP_REST_Request $request ) {
		$widget_id_raw = Arr::get( $params, 'shortcode', false );

		// Bail when not a shortcode request.
		if ( ! $widget_id_raw ) {
			return;
		}

		$widget_option = array_filter( explode( '-', $widget_id_raw ) );
		$widget_index  = (int) array_pop( $widget_option );
		$widget_wp_id  = implode( '-', $widget_option );

		$option = get_option( 'widget_' . $widget_wp_id );
		if ( empty( $option['_multiwidget'] ) || ! $option['_multiwidget'] ) {
			return;
		}

		$widget_id = str_replace( \Tribe\Widget\Widget_Abstract::PREFIX, '', $widget_wp_id );
		/**
		 * @var \Tribe\Widget\Manager $widgets_manager.
		 */
		$widgets_manager = tribe( \Tribe\Widget\Manager::class );
		if ( ! $widgets_manager->is_widget_registered( $widget_id ) ) {
			return;
		}

		$widgets      = $widgets_manager->get_registered_widgets();
		$widget_class = $widgets[ $widget_id ];

		// If not a widget running a shortcode.
		if ( ! empty( $option[ $widget_index ] ) ) {
			$widget_args  = $option[ $widget_index ];
		} else {
			// It's a shortcode running a widget running a shortcode.

			/**
			 * @var Tribe\Events\Pro\Views\V2\Shortcodes\Tribe_Events $shortcode.
			 */
			$shortcode = new Tribe_Events;
			$widget_args        = $shortcode->get_database_arguments( $widget_id_raw );
		}

		// Safety net.
		if ( empty( $widget_args ) ) {
			return;
		}

		/**
		 * @var Widget_Abstract $widget.
		 */
		$widget = new $widget_class;
		$widget->setup( [], $widget_args );
		$widget->toggle_hooks( true );
	}
}
