<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( Tribe\Events\Views\Pro\V2\Customizer\Hooks::class ), 'some_filtering_method' ] );
 * remove_filter( 'some_filter', [ tribe( 'views.v2.customizer.filters' ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( Tribe\Events\Views\Pro\V2\Customizer\Hooks::class ), 'some_method' ] );
 * remove_action( 'some_action', [ tribe( 'views.v2.customizer.hooks' ), 'some_method' ] );
 *
 * @since 5.8.0
 *
 * @package Tribe\Events\Views\Pro\V2\Customizer
 */

namespace Tribe\Events\Pro\Views\V2\Customizer;

use Tribe\Events\Pro\Views\V2\Customizer\Section\Events_Bar;

/**
 * Class Hooks
 *
 * @since 5.8.0
 *
 * @package Tribe\Events\Views\Pro\V2\Customizer
 */
class Hooks extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.8.0
	 */
	public function register() {
		$this->add_filters();
	}

	/**
	 * Adds the filters required by each Pro Views v2 component.
	 *
	 * @since 5.8.0
	 */
	public function add_filters() {
		add_filter( 'tribe_customizer_section_events_bar_default_settings', [ $this, 'filter_events_bar_default_settings'], 12, 2 );
		add_filter( 'tribe_customizer_section_events_bar_content_settings', [ $this, 'filter_events_bar_content_settings'], 12, 2 );
		add_filter( 'tribe_customizer_section_events_bar_content_controls', [ $this, 'filter_events_bar_content_controls'], 12, 2 );
		add_filter( 'tribe_customizer_section_events_bar_css_template', [ $this, 'filter_events_bar_css_template'], 12, 2 );
	}

	/**
	 * Filter the default settings for the events bar customizer section.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed> $arguments The existing default settings.
	 * @param mixed $section                 The section instance we are dealing with (Events_Bar).
	 */
	public function filter_events_bar_default_settings( $arguments, $section ) {
		return $this->container->make( Events_Bar::class )->filter_events_bar_default_settings( $arguments, $section );
	}

	/**
	 * Filter the content settings for the events bar customizer section.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed> $arguments The existing content settings.
	 * @param mixed $section                 The section instance we are dealing with (Events_Bar).
	 */
	public function filter_events_bar_content_settings( $arguments, $section ) {
		return $this->container->make( Events_Bar::class )->filter_events_bar_content_settings( $arguments, $section );
 	}

	/**
	 * Filter the content controls for the events bar customizer section.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed> $arguments The existing content controls.
	 * @param mixed $section                 The section instance we are dealing with (Events_Bar).
	 */
	public function filter_events_bar_content_controls( $arguments, $section ) {
		return $this->container->make( Events_Bar::class )->filter_events_bar_content_controls( $arguments, $section );
	}

	/**
	 * Filter the output CSS for the events bar customizer section.
	 *
	 * @since 5.8.0
	 *
	 * @param string $arguments The existing CSS string.
	 * @param mixed  $section   The section instance we are dealing with (Events_Bar).
	 */
	public function filter_events_bar_css_template( $arguments, $section ) {
		return $this->container->make( Events_Bar::class )->filter_events_bar_css_template( $arguments, $section );
	}
}
