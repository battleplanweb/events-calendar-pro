<?php
/**
 * Handles registering all Assets for the Events Pro V2 Widgets
 *
 * To remove a Assets:
 * tribe( 'assets' )->remove( 'asset-name' );
 *
 * @since 5.5.0
 *
 * @package Tribe\Events\Pro\Views\V2\Widgets
 */
namespace Tribe\Events\Pro\Views\V2\Widgets;

use Tribe__Events__Pro__Main as Pro_Plugin;
use Tribe\Events\Views\V2\Widgets\Widget_List;

use \Tribe\Events\Views\V2\Assets as TEC_Assets;

/**
 * Register Assets related to Widgets.
 *
 * @since 5.5.0
 *
 * @package Tribe\Events\Pro\Views\V2\Widgets
 */
class Assets extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
    * @since 5.5.0
	 */
	public function register() {
		$plugin = Pro_Plugin::instance();

		tribe_asset(
			$plugin,
			'tribe-events-pro-widgets-v2-events-list-skeleton',
			'widget-events-list-skeleton.css',
			[
				'tribe-events-widgets-v2-events-list-skeleton',
			],
			'wp_print_footer_scripts',
			[
				'priority'     => 5,
				'conditionals' => [
					[ Widget_List::class, 'is_widget_in_use' ],
				],
				'groups' => [
					Widget_List::get_css_group(),
					Widget_Advanced_List::get_css_group(),
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-pro-widgets-v2-events-list-full',
			'widget-events-list-full.css',
			[
				'tribe-events-widgets-v2-events-list-full',
				'tribe-events-pro-widgets-v2-events-list-skeleton',
			],
			'wp_print_footer_scripts',
			[
				'priority'     => 5,
				'conditionals' => [
					'operator' => 'AND',
					[ tribe( TEC_Assets::class ), 'should_enqueue_full_styles' ],
					[ Widget_List::class, 'is_widget_in_use' ],
				],
				'groups' => [
					Widget_List::get_css_group(),
					Widget_Advanced_List::get_css_group(),
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-pro-widgets-v2-countdown-skeleton',
			'widget-countdown-skeleton.css',
			[
				'tribe-common-skeleton-style',
			],
			'wp_print_footer_scripts',
			[
				'priority'     => 5,
				'conditionals' => [
					[ Widget_Countdown::class, 'is_widget_in_use' ],
				],
				'groups' => [
					Widget_Countdown::get_css_group(),
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-pro-widgets-v2-countdown-full',
			'widget-countdown-full.css',
			[
				'tribe-events-pro-widgets-v2-countdown-skeleton',
				'tribe-common-full-style',
			],
			'wp_print_footer_scripts',
			[
				'priority'     => 5,
				'conditionals' => [
					'operator' => 'AND',
					[ tribe( TEC_Assets::class ), 'should_enqueue_full_styles' ],
					[ Widget_Countdown::class, 'is_widget_in_use' ],
				],
				'groups' => [
					Widget_Countdown::get_css_group(),
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-pro-widgets-v2-countdown',
			'views/widget-countdown.js',
			[
				'jquery',
				'tribe-common',
				'tribe-events-views-v2-manager',
			],
			'wp_print_footer_scripts',
			[
				'priority'     => 5,
				'conditionals' => [
					[ Widget_Countdown::class, 'is_widget_in_use' ],
				],
				'groups' => [
					Widget_Countdown::get_css_group(),
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-pro-widgets-v2-featured-venue-skeleton',
			'widget-featured-venue-skeleton.css',
			[
				'tribe-common-skeleton-style',
			],
			'wp_print_footer_scripts',
			[
				'priority'     => 5,
				'conditionals' => [
					[ Widget_Featured_Venue::class, 'is_widget_in_use' ],
				],
				'groups' => [
					Widget_Featured_Venue::get_css_group(),
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-pro-widgets-v2-featured-venue-full',
			'widget-featured-venue-full.css',
			[
				'tribe-events-pro-widgets-v2-featured-venue-skeleton',
				'tribe-common-full-style',
			],
			'wp_print_footer_scripts',
			[
				'priority'     => 5,
				'conditionals' => [
					'operator' => 'AND',
					[ tribe( TEC_Assets::class ), 'should_enqueue_full_styles' ],
					[ Widget_Featured_Venue::class, 'is_widget_in_use' ],
				],
				'groups' => [
					Widget_Featured_Venue::get_css_group(),
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-pro-widgets-v2-shortcode-based-skeleton',
			'widget-shortcode-skeleton.css',
			[
				'tribe-common-skeleton-style',
			],
			'wp_print_footer_scripts',
			[
				'priority'     => 5,
				'conditionals' => [
					'operator' => 'OR',
					[ Widget_Week::class, 'is_widget_in_use' ],
					[ Widget_Month::class, 'is_widget_in_use' ],
				],
				'groups' => [
					Widget_Week::get_css_group(),
					Widget_Month::get_css_group(),
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-pro-widgets-v2-shortcode-based-full',
			'widget-shortcode-full.css',
			[
				'tribe-common-full-style',
			],
			'wp_print_footer_scripts',
			[
				'priority'     => 5,
				'conditionals' => [
					'operator' => 'OR',
					[ Widget_Week::class, 'is_widget_in_use' ],
					[ Widget_Month::class, 'is_widget_in_use' ],
				],
				'groups' => [
					Widget_Week::get_css_group(),
					Widget_Month::get_css_group(),
				],
			]
		);

		$widget_overrides_stylesheet = \Tribe__Events__Templates::locate_stylesheet( 'tribe-events/pro/widget-calendar.css' );

		if ( ! empty( $widget_overrides_stylesheet ) ) { // @todo determine if the usage of this is still needed.
			tribe_asset(
				$plugin,
				\Tribe__Events__Main::POSTTYPE . '-widget-calendar-pro-override-style',
				$widget_overrides_stylesheet,
				[],
				null,
				[]
			);
		}
	}
}
