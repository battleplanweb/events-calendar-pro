<?php
/**
 * The main service provider for version 2 of the Pro Widgets.
 *
 * @since   5.2.0
 *
 * @package Tribe\Events\Pro\Views\V2\Widgets
 */

namespace Tribe\Events\Pro\Views\V2\Widgets;

/**
 * Class Service_Provider
 *
 * @since   5.2.0
 *
 * @package Tribe\Events\Pro\Views\V2\Widgets
 */
class Service_Provider extends \tad_DI52_ServiceProvider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.2.0
	 * @since 5.3.0 Added Countdown Widget, separated shortcode hooks.
	 */
	public function register() {
		// Activate the compatibility coding for V1 and V2 Event List Widgets.
		add_filter( 'tribe_events_views_v2_advanced_list_widget_primary', '__return_true' );

		// Determine if V2 widgets should load.
		if ( ! tribe_events_widgets_v2_is_enabled() ) {
			return;
		}

		$this->register_compatibility();

		$this->register_hooks();
		$this->register_assets();

	}

	/**
	 * Registers the provider handling for compatibility hooks.
	 *
	 * @since 5.6.0
	 */
	protected function register_compatibility() {
		$compatibility = new Compatibility();
		$this->container->singleton( Compatibility::class, $compatibility );
		$this->container->singleton( 'pro.views.v2.widgets.compatibility', $compatibility );
	}

	/**
	 * Registers the provider handling for first level v2 widgets.
	 *
	 * @since 5.2.0
	 */
	protected function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container.
		$this->container->singleton( Hooks::class, $hooks );
		$this->container->singleton( 'pro.views.v2.widgets.hooks', $hooks );

		$this->container->singleton( 'pro.views.v2.widgets.taxonomy', Taxonomy_Filter::class );
	}


	/**
	 * Registers the provider handling all assets for widgets v2.
	 *
	 * @since 5.5.0
	 */
	protected function register_assets() {
		$assets = new Assets( $this->container );
		$assets->register();

		$this->container->singleton( Assets::class, $assets );
	}

}
