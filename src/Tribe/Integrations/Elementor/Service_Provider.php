<?php
/**
 * Handles the integration with Elementor.
 *
 * @since   5.1.4
 *
 * @package Tribe\Events\Pro\Integrations\Elementor
 */

namespace Tribe\Events\Pro\Integrations\Elementor;

/**
 * Class Service_Provider
 *
 * @since   5.1.4
 *
 * @package Tribe\Events\Pro\Integrations\Elementor
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Registers the bindings and hooks the filters required for the Elementor integration to work.
	 *
	 * @since 5.1.4
	 */
	public function register() {
		// Hook on the AJAX call Elementor will make during edits to support the archive shortcodes.
		add_action( 'wp_ajax_elementor_ajax', [ $this, 'support_archive_shortcode' ] );

		$this->container->singleton( Shortcodes::class, Shortcodes::class );
	}

	/**
	 * Builds and hooks the class that will handle shortcode support in the context of Elementor.
	 *
	 * @since 5.1.4
	 */
	public function support_archive_shortcode() {
		$shortcodes = $this->container->make( Shortcodes::class );
		add_filter( 'do_shortcode_tag', [ $shortcodes, 'support_archive_shortcode' ], 10, 2 );
	}
}
