<?php
/**
 * The main service provider for the version 2 of the Views.
 *
 * @package Tribe\Events\Views\Pro\V2\Customizer
 * @since   5.7.0
 */

namespace Tribe\Events\Pro\Views\V2\Customizer;

use Tribe\Events\Views\Pro\V2\Customizer;
use Tribe\Events\Views\Pro\V2\Customizer\Section\Events_Bar;

/**
 * Class Service_Provider
 *
 * @since   5.8.0
 *
 * @package Tribe\Events\Views\Pro\V2\Customizer
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.8.0
	 */
	public function register() {
		$this->container->singleton( 'pro.views.v2.customizer.provider', $this );

		$this->register_hooks();

		tribe_register( Events_Bar::class, Events_Bar::class );
	}

	/**
	 * Register the hooks for Tribe_Customizer integration.
	 *
	 * @since 5.8.0
	 */
	public function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container
		$this->container->singleton( Hooks::class, $hooks );
		$this->container->singleton( 'pro.views.v2.customizer.hooks', $hooks );
	}

}
