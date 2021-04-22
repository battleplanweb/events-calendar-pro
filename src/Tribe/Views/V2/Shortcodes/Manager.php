<?php
/**
 * Shortcodes manager for the new views.
 *
 * @since   4.7.5
 *
 * @deprecated  5.1.1
 *
 * @package Tribe\Events\Pro\Views\V2\Shortcodes
 */
namespace Tribe\Events\Pro\Views\V2\Shortcodes;

use Tribe\Events\Views\V2\View_Interface;
use Tribe\Shortcode\Shortcode_Interface;
use Tribe__Context as Context;
use Tribe__Events__Pro__Shortcodes__Register as Legacy_Shortcodes;

/**
 * Class Shortcode Manager.
 *
 * @since   4.7.5
 *
 * @deprecated 5.1.1
 *
 * @package Tribe\Events\Pro\Views\V2\Shortcodes
 */
class Manager extends \Tribe\Shortcode\Manager {
	/**
	 * Get the list of shortcodes available for handling.
	 *
	 * @since  4.7.5
	 *
	 * @deprecated 5.1.1 Use the `tribe_shortcodes` filter in Tribe Common.
	 *
	 * @return array An associative array of shortcodes in the shape `[ <slug> => <class> ]`
	 */
	public function get_registered_shortcodes() {
		_deprecated_function( __METHOD__, '5.5.0', 'Use the `tribe_shortcodes` filter in Tribe Common.' );

		return parent::get_registered_shortcodes();
	}

	/**
	 * Filters the context locations to add the ones used by Shortcodes.
	 *
	 * @since 4.7.9
	 *
	 * @deprecated 5.5.0 Moved this to a method inside of Tribe_Events.
	 *
	 * @param array $locations The array of context locations.
	 *
	 * @return array The modified context locations.
	 */
	public function filter_context_locations( array $locations = [] ) {
		_deprecated_function( __METHOD__, '5.5.0', 'tribe( Tribe_Events::class )->filter_context_locations()' );
		return tribe( Tribe_Events::class )->filter_context_locations( $locations );
	}

	/**
	 * Remove old shortcode methods from views v1.
	 *
	 * @since  4.7.5
	 *
	 * @deprecated 5.5.0 Moved this to a method inside of Tribe_Events.
	 *
	 * @return void
	 */
	public function disable_v1() {
		_deprecated_function( __METHOD__, '5.5.0', 'tribe( Tribe_Events::class )->disable_v1()' );

		return tribe( Tribe_Events::class )->disable_v1();
	}

	/**
	 * Filters the View URL to add the shortcode query arg, if required.
	 *
	 * @since 4.7.9
	 *
	 * @deprecated 5.5.0 Move this to a method inside of Tribe_Events.
	 *
	 * @param string         $url   The View current URL.
	 * @param View_Interface $view  This view instance.
	 *
	 * @return string  The URL for the view shortcode.
	 */
	public function filter_view_url( $url, View_Interface $view ) {
		_deprecated_function( __METHOD__, '5.5.0', 'tribe( Tribe_Events::class )->filter_view_url()' );

		return tribe( Tribe_Events::class )->filter_view_url( $url, $view );
	}

	/**
	 * Filters the query arguments array and add the Shortcodes.
	 *
	 * @since 4.7.9
	 *
	 * @deprecated 5.5.0 Move this to a method inside of Shortcodes|Tribe_Events
	 *
	 * @param array           $query     Arguments used to build the URL.
	 * @param string          $view_slug The current view slug.
	 * @param View_Interface  $view      The current View object.
	 *
	 * @return  array  Filtered the query arguments for shortcodes.
	 */
	public function filter_view_url_query_args( array $query, $view_slug, View_Interface $view ) {
		_deprecated_function( __METHOD__, '5.5.0', 'tribe( Tribe_Events::class )->filter_view_url_query_args()' );

		return tribe( Tribe_Events::class )->filter_view_url_query_args( $query, $view_slug, $view  );
	}

	/**
	 * Deprecated Alias to `render_shortcode`.
	 *
	 * @since  4.7.5
	 * @deprecated  5.1.1 Use `render_shortcode` in Tribe Common.
	 *
	 * @param array  $arguments Set of arguments passed to the Shortcode at hand.
	 * @param string $content   Contents passed to the shortcode, inside of the open and close brackets.
	 * @param string $shortcode Which shortcode tag are we handling here.
	 *
	 * @return string The rendered shortcode HTML.
	 */
	public function handle( $arguments, $content, $shortcode ) {
		_deprecated_function( __METHOD__, '5.5.0', 'Use the `render_shortcode` method in Tribe Common.' );
		return $this->render_shortcode( $arguments, $content, $shortcode );
	}
}
