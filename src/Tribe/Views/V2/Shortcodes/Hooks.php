<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( Tribe\Events\Pro\Views\V2\Shortcodes\Hooks::class ), 'some_filtering_method' ] );
 * remove_filter( 'some_filter', [ tribe( 'pro.views.v2.shortcodes.hooks' ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( Tribe\Events\Pro\Views\V2\Hooks::class ), 'some_method' ] );
 * remove_action( 'some_action', [ tribe( 'pro.views.v2.shortcodes.hooks' ), 'some_method' ] );
 *
 * @since 4.7.5
 *
 * @package Tribe\Events\Pro\Views\V2\Shortcodes
 */

namespace Tribe\Events\Pro\Views\V2\Shortcodes;

use Tribe\Events\Pro\Views\V2\Assets as Pro_Assets;
use Tribe\Shortcode\Manager;
use Tribe__Events__Pro__Shortcodes__Register as Legacy_Shortcodes;
use WP_REST_Request as Request;

/**
 * Class Hooks.
 *
 * @since 4.7.5
 *
 * @package Tribe\Events\Pro\Views\V2
 */
class Hooks extends \tad_DI52_ServiceProvider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.7.5
	 */
	public function register() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Add actions for all the shortcode related stuff.
	 *
	 * @since 5.5.0
	 */
	public function add_actions() {
		add_action( 'init', [ $this, 'action_disable_shortcode_v1' ], 15 );
		add_action( 'init', [ $this, 'action_add_shortcodes' ], 20 );
		add_action( 'tribe_events_pro_shortcode_tribe_events_after_assets', [ $this, 'action_disable_shortcode_assets_v1' ] );
		add_action( 'tribe_events_views_v2_before_make_view_for_rest', [ $this, 'action_shortcode_toggle_hooks' ], 10, 3 );
	}

	/**
	 * Add filters for all the shortcode related stuff.
	 *
	 * @since 5.5.0
	 */
	public function add_filters() {
		add_filter( 'tribe_shortcodes', [ $this, 'filter_tribe_shortcodes' ] );
		add_filter( 'tribe_context_locations', [ $this, 'filter_context_locations' ] );
	}

	/**
	 * Adds the new shortcodes, this normally will trigger on `init@P20` due to how we the
	 * v1 is added on `init@P10` and we remove them on `init@P15`.
	 *
	 * It's important to leave gaps on priority for better injection.
	 *
	 * @since 4.7.5
	 */
	public function action_add_shortcodes() {
		$this->container->make( Manager::class )->add_shortcodes();
	}

	/**
	 * Add shortcodes for Pro.
	 *
	 * @since 5.5.0
	 *
	 * @param array $shortcodes List of previous shortcodes.
	 *
	 * @return array The modified shortcodes array.
	 */
	public function filter_tribe_shortcodes( $shortcodes ) {
		$shortcodes['tribe_events']        = Tribe_Events::class;
		$shortcodes['tribe_events_list']   = Shortcode_Tribe_Events_List::class;
		$shortcodes['tribe_this_week']     = Shortcode_Tribe_Week::class;
		$shortcodes['tribe_mini_calendar'] = Shortcode_Tribe_Mini_Calendar::class;

		return $shortcodes;
	}

	/**
	 * Filters the context locations to add the ones used by The Events Calendar PRO for Shortcodes.
	 *
	 * @since 4.7.9
	 * @since 5.5.0 Moved this from Tribe\Events\Pro\Views\V2\Hooks.
	 *
	 * @param array $locations The array of context locations.
	 *
	 * @return array The modified context locations.
	 */
	public function filter_context_locations( array $locations = [] ) {
		return Tribe_Events::filter_context_locations( $locations );
	}

	/**
	 * Fires to deregister v1 assets correctly for shortcodes.
	 *
	 * @since 4.7.9
	 * @since 5.5.0 Moved this from Tribe\Events\Pro\Views\V2\Hooks.
	 *
	 * @return  void
	 */
	public function action_disable_shortcode_assets_v1() {
		$this->container->make( Pro_Assets::class )->disable_v1();
	}


	/**
	 * Possibly loads all the shortcode hooks.
	 *
	 * @since 5.5.0
	 *
	 * @param  string    $slug    The current view Slug.
	 * @param  array     $params  Params so far that will be used to build this view.
	 * @param  Request   $request The rest request that generated this call.
	 */
	public function action_shortcode_toggle_hooks( $slug, $params, Request $request ) {
		Tribe_Events::maybe_toggle_hooks_for_rest( $slug, $params, $request );
	}

	/**
	 * Remove old shortcode methods from views v1.
	 *
	 * @since  4.7.5
	 * @since 5.5.0 Moved this from deprecated Shortcodes\Manager.
	 *
	 * @return void
	 */
	public function action_disable_shortcode_v1() {
		remove_shortcode( 'tribe_events' );
		remove_shortcode( 'tribe_events_list' );
		remove_shortcode( 'tribe_this_week' );
		remove_shortcode( 'tribe_mini_calendar' );

		$legacy_shortcodes_instance = tribe( 'events-pro.main' )->shortcodes;

		// Prevents removal with the incorrect class.
		if ( ! $legacy_shortcodes_instance instanceof Legacy_Shortcodes ) {
			return;
		}

		remove_action( 'tribe_events_ical_before', [ $legacy_shortcodes_instance, 'search_shortcodes' ] );
		remove_action( 'save_post', [ $legacy_shortcodes_instance, 'update_shortcode_main_calendar' ] );
		remove_action( 'trashed_post', [ $legacy_shortcodes_instance, 'maybe_reset_main_calendar' ] );
		remove_action( 'deleted_post', [ $legacy_shortcodes_instance, 'maybe_reset_main_calendar' ] );

		// Hooks attached to the main calendar attribute on the shortcodes
		remove_filter( 'tribe_events_get_link', [ $legacy_shortcodes_instance, 'shortcode_main_calendar_link' ], 10 );
	}
}
