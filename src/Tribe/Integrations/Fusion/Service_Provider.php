<?php
/**
 * Handles the integration with Fusion Core.
 *
 * @since   5.5.0
 *
 * @package Tribe\Events\Pro\Integrations\Fusion
 */

namespace Tribe\Events\Pro\Integrations\Fusion;

use \Tribe\Events\Pro\Views\V2\Widgets\Widget_Month;
use \Tribe\Events\Pro\Views\V2\Widgets\Widget_Week;
use \Tribe\Events\Pro\Views\V2\Widgets\Widget_Countdown;
use \Tribe\Events\Pro\Views\V2\Widgets\Widget_Featured_Venue;

/**
 * Class Service_Provider
 *
 * @since   5.5.0
 *
 * @package Tribe\Events\Pro\Integrations\Fusion
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Registers the bindings and hooks the filters required for the Fusion Core integration to work.
	 *
	 * @since   5.5.0
	 */
	public function register() {
		// Fusion compatibility only for V2 users.
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return;
		}

		// Register the hooks related to this integration.
		$this->register_hooks();
	}

	/**
	 * Register the hooks for Fusion Core integration.
	 *
	 * @since   5.5.0
	 */
	public function register_hooks() {
		add_filter( 'tribe_events_integrations_fusion_widget_class_map', [ $this, 'filter_add_widget_classes' ] );
	}

	/**
	 * Builds and hooks the class that will handle shortcode support in the context of Fusion Core.
	 *
	 * @since 5.5.0
	 *
	 * @param array $classes List of classes we are
	 *
	 * @return array Classes after including the Pro classes.
	 */
	public function filter_add_widget_classes( $classes ) {
		$classes[] = Widget_Month::class;
		$classes[] = Widget_Week::class;
		$classes[] = Widget_Countdown::class;
		$classes[] = Widget_Featured_Venue::class;

		return $classes;
	}
}
