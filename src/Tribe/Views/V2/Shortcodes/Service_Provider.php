<?php
/**
 * The main service provider for version 2 of the Pro Shortcodes.
 *
 * @since   5.2.0
 *
 * @package Tribe\Events\Pro\Views\V2\Shortcodes
 */

namespace Tribe\Events\Pro\Views\V2\Shortcodes;

/**
 * Class Service_Provider
 *
 * @since   5.2.0
 *
 * @package Tribe\Events\Pro\Views\V2\Shortcodes
 */
class Service_Provider extends \tad_DI52_ServiceProvider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.2.0
	 * @since 5.3.0 Added Countdown Widget, separated shortcode hooks.
	 */
	public function register() {
		$this->register_hooks();
	}

	/**
	 * Registers the provider handling for first level v2 shortcodes.
	 *
	 * @since 5.2.0
	 */
	protected function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container.
		$this->container->singleton( Hooks::class, $hooks );
		$this->container->singleton( 'pro.views.v2.shortcodes.hooks', $hooks );
	}

}
