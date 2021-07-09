<?php
/**
 * Shortcode Tribe_Events.
 *
 * @since   4.7.5
 * @package Tribe\Events\Pro\Views\V2\Shortcodes
 */

namespace Tribe\Events\Pro\Views\V2\Shortcodes;

use Tribe\Utils\Taxonomy;
use Tribe\Shortcode\Shortcode_Abstract;
use Tribe\Events\Pro\Views\V2\Assets as Pro_Assets;
use Tribe\Events\Views\V2\Assets as Event_Assets;
use Tribe\Events\Views\V2\Manager as Views_Manager;
use Tribe\Events\Views\V2\Theme_Compatibility;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\View_Interface;
use Tribe\Utils\Element_Classes;
use Tribe__Context as Context;
use Tribe__Date_Utils;
use Tribe__Events__Main as TEC;
use Tribe__Utils__Array as Arr;
use Tribe__Date_Utils as Dates;

/**
 * Class for Shortcode Tribe_Events.
 *
 * @since   4.7.5
 *
 * @package Tribe\Events\Pro\Views\V2\Shortcodes
 */
class Tribe_Events extends Shortcode_Abstract {
	/**
	 * Prefix for the transient where we will save the base values for the
	 * setup of the context of the shortcode.
	 *
	 * @since 4.7.9
	 *
	 * @var   string
	 */
	const TRANSIENT_PREFIX = 'tribe_events_shortcode_tribe_events_params_';

	/**
	 * {@inheritDoc}
	 */
	protected $slug = 'tribe_events';

	/**
	 * {@inheritDoc}
	 */
	protected $default_arguments = [
		'category'          => null,
		'container-classes' => [],
		'date'              => null,
		'events_per_page'   => null,
		'featured'          => null,
		'filter-bar'        => false,
		'hide_weekends'     => false,
		'hide-datepicker'   => false,
		'hide-export'       => false,
		'id'                => null,
		'is-widget'         => false,
		'keyword'           => null,
		'main-calendar'     => false,
		'tag'               => null,
		'tax-operand'       => 'OR',
		'tribe-bar'         => true,
		'view'              => null,

		'month_events_per_day' => null,
		'week_events_per_day'  => null,
		'layout'               => 'vertical', // @todo Change to auto when we enable that option.
		'week_offset'          => null,

		/**
		 * @todo @bordoni @lucatume @be Update this when shortcode URL management is fixed.
		 */
		'should_manage_url'    => false,
	];

	/**
	 * {@inheritDoc}
	 */
	protected $validate_arguments_map = [
		'container-classes'    => [ self::class, 'validate_array_html_classes' ],
		'featured'             => 'tribe_null_or_truthy',
		'filter-bar'           => 'tribe_is_truthy',
		'hide-datepicker'      => 'tribe_is_truthy',
		'hide-export'          => 'tribe_is_truthy',
		'is-widget'            => 'tribe_is_truthy',
		'main-calendar'        => 'tribe_is_truthy',
		'month_events_per_day' => 'tribe_null_or_number',
		'should_manage_url'    => 'tribe_is_truthy',
		'tax-operand'          => 'strtoupper',
		'tribe-bar'            => 'tribe_is_truthy',
		'week_events_per_day'  => 'tribe_null_or_number',
		'week_offset'          => 'tribe_null_or_number',
		'hide_weekends'        => 'tribe_is_truthy',
	];

	/**
	 * {@inheritDoc}
	 */
	protected $aliased_arguments = [
		'cat'                   => 'category',
		'cats'                  => 'category',
		'tribe_events_category' => 'category',
		'tribe_events_cat'      => 'category',
		'categories'            => 'category',
		'tags'                  => 'tag',
		'event_tags'            => 'tag',
		'event_tag'             => 'tag',
		'post_tag'              => 'tag',
		'post_tags'             => 'tag',
		'start_date'            => 'date',
		'week_layout'           => 'layout',
	];

	/**
	 * Toggles the filtering of URLs to match the place where.
	 * We tend to hook into P15 to allow other things to happen before shortcode.
	 *
	 * @since  4.7.5
	 *
	 * @param bool $toggle Whether to turn the hooks on or off.
	 *
	 * @return void
	 */
	protected function toggle_view_hooks( $toggle ) {
		if ( $toggle ) {
			$this->add_shortcode_hooks();
		} else {
			$this->remove_shortcode_hooks();
		}

		/**
		 * Fires after View hooks have been toggled while rendering a shortcode.
		 *
		 * @since 5.0.0
		 *
		 * @param bool   $toggle Whether the hooks should be turned on or off. This value is `true` before a shortcode
		 *                       HTML is rendered and `false` after the shortcode HTML rendered.
		 * @param static $this   The shortcode object that is toggling the View hooks.
		 */
		do_action( 'tribe_events_pro_shortcode_toggle_view_hooks', $toggle, $this );
	}

	/**
	 * Toggles off portions of the template based on shortcode params.
	 * This runs on the `tribe_events_pro_shortcode_toggle_view_hooks` hook when the toggle is true.
	 *
	 * @since 5.5.0
	 */
	protected function add_shortcode_hooks() {
		add_filter( 'tribe_events_views_v2_url_query_args', [ $this, 'filter_view_query_args' ], 15, 3 );
		add_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_view_repository_args' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_html_classes', [ $this, 'filter_view_html_classes' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_container_data', [ $this, 'filter_view_data' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_url_query_args', [ $this, 'filter_view_url_query_args' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_context', [ $this, 'filter_view_context' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_manager_default_view', [ $this, 'filter_default_url' ] );
		add_filter( 'tribe_events_views_v2_view_url', [ $this, 'filter_view_url' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_next_url', [ $this, 'filter_view_url' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_prev_url', [ $this, 'filter_view_url' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_week_breakpoints', [ $this, 'filter_week_view_breakpoints' ], 10, 2 );

		// Removing tribe-bar when that argument is `false`.
		if (
			! tribe_is_truthy( $this->get_argument( 'tribe-bar' ) )
			|| tribe_is_truthy( $this->get_argument( 'is-widget' ) )
		) {
			add_filter( 'tribe_template_html:events/v2/components/events-bar', '__return_false' );
		}

		// Removing export button when that argument is `true`.
		if (
			tribe_is_truthy( $this->get_argument( 'hide-export' ) )
			|| tribe_is_truthy( $this->get_argument( 'is-widget' ) )
		) {
			add_filter( 'tribe_template_html:events/v2/components/ical-link', '__return_false' );
		}

		/* Filter Bar */
		if (
			! tribe_is_truthy( $this->get_argument( 'filter-bar' ) )
			|| ! tribe_is_truthy( $this->get_argument( 'tribe-bar' ) )
			|| tribe_is_truthy( $this->get_argument( 'is-widget' ) )
		) {
			add_filter( 'tribe_events_filter_bar_views_v2_should_display_filters', '__return_false' );
			add_filter( 'tribe_events_filter_bar_views_v2_1_should_display_filters', '__return_false' );
			add_filter( 'tribe_events_filter_bar_views_v2_assets_should_enqueue_frontend', '__return_false' );
			add_filter( 'tribe_events_views_v2_filter_bar_view_html_classes', '__return_false' );

			if ( class_exists( 'Tribe\Events\Filterbar\Views\V2_1\Hooks' ) ) {
				remove_filter(
					'tribe_events_pro_shortcode_tribe_events_before_assets',
					[ tribe( 'filterbar.views.v2_1.hooks' ), 'action_include_assets' ]
				);
			} else if ( class_exists( 'Tribe\Events\Filterbar\Views\V2\Hooks' ) ) {
				remove_filter(
					'tribe_events_pro_shortcode_tribe_events_before_assets',
					[ tribe( 'filterbar.views.v2.hooks' ), 'action_include_assets' ]
				);
			}
		}

		/* Month widget only. */
		if ( 'month' === $this->get_argument( 'view' ) && tribe_is_truthy( $this->get_argument( 'is-widget' ) ) ) {
			/* Mobile "footer" nav */
			add_filter( 'tribe_template_html:events/v2/month/mobile-events/nav', '__return_false' );
		}

		/* Week view & widget only. */
		if ( 0 === stripos( $this->get_argument( 'view' ), 'week' ) ) {
			// Allows for the "hide_weekends" attribute.
			if ( tribe_is_truthy( $this->get_argument( 'hide_weekends' ) ) ) {
				add_filter( 'tribe_get_option', [ $this, 'week_view_hide_weekends' ], 10, 2 );
			}

			add_filter( 'tribe_events_views_v2_week_events_per_day', [ $this, 'filter_week_events_per_day' ], 10, 2 );
		}

		// Removing datepicker when that argument is `true`.
		if (
			tribe_is_truthy( $this->get_argument( 'hide-datepicker' ) )
			|| tribe_is_truthy( $this->get_argument( 'is-widget' ) )
		) {
			add_filter( "tribe_template_html:events/v2/month/top-bar/datepicker", '__return_false' );
			add_filter( "tribe_template_html:events-pro/v2/week/top-bar/datepicker", '__return_false' );
		}
	}

	/**
	 * Hide weekends on shortcode.
	 *
	 * @since 5.6.0
	 *
	 * @param mixed  $value      The value for the option.
	 * @param string $optionName The name of the option.
	 *
	 * @return mixed The value for the option.
	 */
	public function week_view_hide_weekends( $value, $optionName ) {
		if ( 'week_view_hide_weekends' !== $optionName ) {
			return $value;
		}

		return true;
	}

	/**
	 * Toggles on portions of the template that were toggled off in `template_removes()` above.
	 * This runs on the `tribe_events_pro_shortcode_toggle_view_hooks` hook when the toggle is false.
	 * Thus encapsulating our control of these shared pieces to only when the shortcode is rendering.
	 *
	 * @since 5.5.0
	 */
	protected function remove_shortcode_hooks() {
		remove_filter( 'tribe_events_views_v2_url_query_args', [ $this, 'filter_view_query_args' ], 15 );
		remove_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_view_repository_args' ], 10 );
		remove_filter( 'tribe_events_views_v2_view_html_classes', [ $this, 'filter_view_html_classes' ], 10 );
		remove_filter( 'tribe_events_views_v2_view_container_data', [ $this, 'filter_view_data' ], 10 );
		remove_filter( 'tribe_events_views_v2_view_url_query_args', [ $this, 'filter_view_url_query_args' ], 10 );
		remove_filter( 'tribe_events_views_v2_view_context', [ $this, 'filter_view_context' ], 10 );
		remove_filter( 'tribe_events_views_v2_manager_default_view', [ $this, 'filter_default_url' ] );
		remove_filter( 'tribe_events_views_v2_view_url', [ $this, 'filter_view_url' ], 10 );
		remove_filter( 'tribe_events_views_v2_view_next_url', [ $this, 'filter_view_url' ], 10 );
		remove_filter( 'tribe_events_views_v2_view_prev_url', [ $this, 'filter_view_url' ], 10 );

		remove_filter( 'tribe_template_html:events/v2/components/events-bar', '__return_false' ); // tribe-bar
		remove_filter( 'tribe_template_html:events/v2/components/ical-link', '__return_false' ); // hide-export
		remove_filter( 'tribe_template_html:events/v2/month/top-bar/datepicker', '__return_false' ); // hide-datepicker
		remove_filter( "tribe_template_html:events-pro/v2/week/top-bar/datepicker", '__return_false' ); // hide-datepicker

		// Filter Bar
		remove_filter( 'tribe_events_filter_bar_views_v2_should_display_filters', '__return_false' );
		remove_filter( 'tribe_events_filter_bar_views_v2_1_should_display_filters', '__return_false' );
		remove_filter( 'tribe_events_filter_bar_views_v2_assets_should_enqueue_frontend', '__return_false' );
		remove_filter( 'tribe_events_views_v2_filter_bar_view_html_classes', '__return_false' );
		// Yes, add - we're adding it back.
		if ( class_exists( 'Tribe\Events\Filterbar\Views\V2_1\Hooks' ) ) {
			add_filter( 'tribe_events_pro_shortcode_tribe_events_before_assets', [ tribe( 'filterbar.views.v2_1.hooks' ), 'action_include_assets' ] );
		} else if ( class_exists( 'Tribe\Events\Filterbar\Views\V2\Hooks' ) ) {
			add_filter( 'tribe_events_pro_shortcode_tribe_events_before_assets', [ tribe( 'filterbar.views.v2.hooks' ), 'action_include_assets' ] );
		}

		remove_filter( 'tribe_get_option', [ $this, 'week_view_hide_weekends' ] );
		remove_filter( 'tribe_events_views_v2_view_week_breakpoints', [ $this, 'filter_week_view_breakpoints' ], 10 );

		remove_filter( 'tribe_events_views_v2_week_events_per_day', [ $this, 'views_v2_week_events_per_day' ], 10 );
	}

	/**
	 * Maybe toggles the hooks for this shortcode class on a rest request.
	 *
	 * @since 5.5.0
	 *
	 * @param string           $slug    The current view Slug.
	 * @param array            $params  Params so far that will be used to build this view.
	 * @param \WP_REST_Request $request The rest request that generated this call.
	 *
	 */
	public static function maybe_toggle_hooks_for_rest( $slug, $params, \WP_REST_Request $request ) {
		$shortcode = Arr::get( $params, 'shortcode', false );
		// Bail when not a shortcode request.
		if ( ! $shortcode ) {
			return;
		}
		$shortcode_instance = new static;
		$db_args            = $shortcode_instance->get_database_arguments( $shortcode );

		// When no params were found it means it's not a valid Shortcode.
		if ( empty( $db_args ) ) {
			return;
		}

		$shortcode_instance->setup( $db_args, '' );

		$shortcode_instance->toggle_view_hooks( true );
	}

	/**
	 * Verifies if in this Shortcode we should allow View URL management.
	 *
	 * @since  4.7.5
	 *
	 * @return bool
	 */
	public function should_manage_url() {
		// Defaults to true due to old behaviors on Views V1
		$should_manage_url = $this->get_argument( 'should_manage_url', $this->default_arguments['should_manage_url'] );

		$disallowed_locations = [
			'widget_text_content',
		];

		/**
		 * Allows filtering of the disallowed locations for URL management.
		 *
		 * @since  4.7.5
		 *
		 * @param mixed  $disallowed_locations Which filters we don't allow URL management.
		 * @param static $instance             Which instance of shortcode we are dealing with.
		 */
		$disallowed_locations = apply_filters( 'tribe_events_pro_shortcode_tribe_events_manage_url_disallowed_locations', $disallowed_locations, $this );

		// Block certain locations
		foreach ( $disallowed_locations as $location ) {
			// If any we are in any of the disallowed locations
			if ( doing_filter( $location ) ) {
				$should_manage_url = $this->default_arguments['should_manage_url'];
			}
		}

		/**
		 * Allows filtering if a shortcode URL management is active.
		 *
		 * @since  4.7.5
		 *
		 * @param mixed  $should_manage_url Should we manage the URL for this views shortcode instance.
		 * @param static $instance          Which instance of shortcode we are dealing with.
		 */
		$should_manage_url = apply_filters( 'tribe_events_pro_shortcode_tribe_events_should_manage_url', $should_manage_url, $this );

		return $should_manage_url;
	}

	/**
	 * @inheritDoc
	 *
	 * @since 5.5.0
	 *
	 * @return array List of validated arguments mapping.
	 */
	public function get_validated_arguments_map() {
		$map = parent::get_validated_arguments_map();

		$map['category'] = static function ( $terms ) {
			return Taxonomy::normalize_to_term_ids( $terms, TEC::TAXONOMY );
		};
		$map['tag']      = static function ( $terms ) {
			return Taxonomy::normalize_to_term_ids( $terms, 'post_tag' );
		};

		return $map;
	}

	/**
	 * Changes the URL to match the Shortcode if needed.
	 *
	 * @since 4.7.5
	 *
	 * @param array $query_args Current URL for this view.
	 *
	 * @return array The filtered View query args, with the shortcode ID added.
	 */
	public function filter_view_query_args( $query_args ) {
		// Always add the id of the shortcode to the URLs
		$query_args['shortcode'] = $this->get_id();

		return $query_args;
	}

	/**
	 * Fetches from the database the params of a given shortcode based on the ID created.
	 *
	 * @since  4.7.9
	 *
	 * @param string $shortcode_id The shortcode identifier, or `null` to use the current one.
	 *
	 * @return array Array of params configuring the Shortcode.
	 */
	public function get_database_arguments( $shortcode_id = null ) {
		$shortcode_id        = $shortcode_id ?: $this->get_id();
		$transient_key       = static::TRANSIENT_PREFIX . $shortcode_id;
		$transient_arguments = get_transient( $transient_key );

		return $transient_arguments;
	}

	/**
	 * Configures the Relationship between shortcode ID and their params in the database
	 * allowing us to pass the URL as the base for the Queries.
	 *
	 * @since 4.7.9
	 *
	 * @return  bool  Return if we have the arguments configured or not.
	 */
	public function set_database_params() {
		$shortcode_id       = $this->get_id();
		$transient_key      = static::TRANSIENT_PREFIX . $shortcode_id;
		$db_arguments       = $this->get_database_arguments();
		$db_arguments['id'] = $shortcode_id;

		// If the value is the same it's already on the Database.
		if ( $db_arguments === $this->get_arguments() ) {
			return true;
		}

		return set_transient( $transient_key, $this->get_arguments() );
	}

	/**
	 * Alters the shortcode context with its arguments.
	 *
	 * @since  4.7.9
	 *
	 * @param \Tribe__Context $context Context we will use to build the view.
	 *
	 * @return \Tribe__Context Context after shortcodes changes.
	 */
	public function alter_context( Context $context, array $arguments = [] ) {
		$shortcode_id = $context->get( 'id' );

		if ( empty( $arguments ) ) {
			$arguments    = $this->get_arguments();
			$shortcode_id = $this->get_id();
		}

		$alter_context = $this->args_to_context( $arguments, $context );

		// The View will consume this information on initial state.
		$alter_context['shortcode'] = $shortcode_id;
		$alter_context['id']        = $shortcode_id;


		$context = $context->alter( $alter_context );

		return $context;
	}

	/**
	 * Based on the either a argument "id" of the shortcode definition
	 * or the 8 first characters of the hashed version of a string serialization
	 * of the params sent to the shortcode we will create/get an ID for this
	 * instance of the tribe_events shortcode
	 *
	 * @since  4.7.9
	 *
	 * @return string The shortcode unique(ish) identifier.
	 */
	public function get_id() {
		$arguments = $this->get_arguments();

		// In case we have the ID argument we just return that.
		if ( ! empty( $arguments['id'] ) ) {
			return $arguments['id'];
		}

		// @todo: We hates it, my precious - find a better way.
		if ( is_array( $arguments ) ) {
			ksort( $arguments );
		}

		/*
		 * Generate a string id based on the arguments used to setup the shortcode.
		 * Note that arguments are sorted to catch substantially same shortcode w. diff. order argument.
		 */
		$hash = substr( md5( maybe_serialize( $arguments ) ), 0, 8 );

		return $hash;
	}

	/**
	 * Should not be used carelessly this will remove all request based locations from the read of the context.
	 * Which if not used properly will break all other uses of the context unless it's a shortcode.
	 *
	 * @since 5.5.0
	 *
	 * @param array   $locations An array of read and write location in the shape of the `Context::$locations` one,
	 *                           `[ <location> => [ 'read' => <read_locations>, 'write' => <write_locations> ] ]`.
	 * @param Context $context   Instance of the context.
	 *
	 * @return array Locations after removing the request based ones.
	 */
	public function remove_request_based_context_locations( array $locations, Context $context ) {
		foreach ( $locations as $key => $location ) {
			// no read locations we bail.
			if ( empty( $location['read'] ) ) {
				continue;
			}

			// Check if this location has a read for
			if ( ! empty( $location['read'][ Context::REQUEST_VAR ] ) ) {
				unset( $locations[ $key ]['read'][ Context::REQUEST_VAR ] );
			}
			if ( ! empty( $location['read'][ Context::QUERY_VAR ] ) ) {
				unset( $locations[ $key ]['read'][ Context::QUERY_VAR ] );
			}
			if ( ! empty( $location['read'][ Context::WP_MATCHED_QUERY ] ) ) {
				unset( $locations[ $key ]['read'][ Context::WP_MATCHED_QUERY ] );
			}
			if ( ! empty( $location['read'][ Context::WP_PARSED ] ) ) {
				unset( $locations[ $key ]['read'][ Context::WP_PARSED ] );
			}
		}

		return $locations;
	}

	/**
	 * Allows us to print Scripts and Styles inside of the template but outside of the container.
	 * Preventing Shortcodes from failing on Block Editor saving.
	 *
	 * Hooked to `tribe_template_before_include@P15` and will unhook itself after the first template called.
	 *
	 * @since 5.5.0
	 *
	 * @param string           $file     Complete path to include the PHP File.
	 * @param array            $name     Template name.
	 * @param \Tribe__Template $template Current instance of the Tribe__Template.
	 */
	public function enqueue_assets_before_template( $file = null, $name = null, $template = null ) {
		// Prevent other templates from triggering this.
		if ( ! $template instanceof \Tribe\Events\Views\V2\Template ) {
			return;
		}

		/**
		 * Triggers an action to allow other plugins or extensions to load assets.
		 *
		 * @since 4.7.9
		 *
		 * @param self $shortcode Instance of this class.
		 */
		do_action( 'tribe_events_pro_shortcode_tribe_events_before_assets', $this );

		// Make sure to enqueue assets.
		tribe_asset_enqueue_group( Pro_Assets::$group_key );
		tribe_asset_enqueue_group( Event_Assets::$group_key );

		/**
		 * Triggers an action to allow other plugins or extensions to load assets.
		 *
		 * @since 4.7.9
		 *
		 * @param self $shortcode Instance of this class.
		 */
		do_action( 'tribe_events_pro_shortcode_tribe_events_after_assets', $this );

		// This action once triggered removes itself.
		remove_action( 'tribe_template_before_include', [ $this, 'enqueue_assets_before_template' ], 15 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html() {
		/**
		 * On blocks editor shortcodes are being rendered in the screen which for some unknown reason makes the admin
		 * URL soft redirect (browser history only) to the front-end view URL of that shortcode.
		 *
		 * @see TEC-3157
		 */
		if ( is_admin() && ! tribe( 'context' )->doing_ajax() ) {
			return '';
		}

		$context = new Context();

		/**
		 * Please if you don't understand what these are doing, don't touch this.
		 */
		add_filter( 'tribe_context_locations', [ $this, 'remove_request_based_context_locations' ], 1000, 2 );
		$context->dangerously_repopulate_locations();
		$context->refresh();

		// Before anything happens we set a DB ID and value for this shortcode entry.
		$this->set_database_params();

		// Modifies the Context for the shortcode params.
		$context = $this->alter_context( $context );

		// Fetches if we have a specific view are building.
		$view_slug = $this->get_argument( 'view', $context->get( 'view' ) );

		// Toggle the shortcode required modifications.
		$this->toggle_view_hooks( true );

		$shortcode_object = $this;

		add_filter( 'tribe_events_views_v2_view_cached_html', static function ( $cached_html, $view ) use ( $shortcode_object ) {
			$shortcode_object->enqueue_assets_before_template( null, null, $view->get_template() );

			return $cached_html;
		}, 15, 2 );

		add_action( 'tribe_template_before_include', [ $this, 'enqueue_assets_before_template' ], 15, 3 );

		// Setup the view instance.
		$view = View::make( $view_slug, $context );

		// Setup whether this view should manage url or not.
		$view->get_template()->set( 'should_manage_url', $this->should_manage_url() );

		$theme_compatibility = tribe( Theme_Compatibility::class );

		$html = '';

		/**
		 * Allows removing the compatibility container.
		 *
		 * @since 5.5.0
		 *
		 * @param bool   $compatibility_required Is compatibility required for this shortcode.
		 * @param static $shortcode              Shortcode instance that is being rendered.
		 */
		$compatibility_required = apply_filters(
			'tribe_events_pro_shortcode_compatibility_required',
			$theme_compatibility->is_compatibility_required(),
			$this
		);

		if ( $compatibility_required ) {
			$container       = [ 'tribe-compatibility-container' ];
			$classes         = array_merge( $container, $theme_compatibility::get_compatibility_classes() );
			$element_classes = new Element_Classes( $classes );
			$html            .= '<div ' . $element_classes->get_attribute() . '>';
		}

		$html .= $view->get_html();

		if ( $compatibility_required ) {
			$html .= '</div>';
		}

		// Toggle the shortcode required modifications.
		$this->toggle_view_hooks( false );

		/**
		 * Please if you don't understand what these are doing, don't touch this.
		 */
		remove_filter( 'tribe_context_locations', [ $this, 'remove_request_based_context_locations' ], 1000 );
		$context->dangerously_repopulate_locations();
		$context->refresh();

		return $html;
	}

	/**
	 * Filters the View repository args to add the ones required by shortcodes to work.
	 *
	 * @since 4.7.9
	 *
	 * @param array           $repository_args An array of repository arguments that will be set for all Views.
	 * @param \Tribe__Context $context         The current render context object.
	 * @param View_Interface  $view            The View that will use the repository arguments.
	 *
	 * @return array          Repository arguments after shortcode args added.
	 */
	public function filter_view_repository_args( $repository_args, $context, $view ) {
		if ( ! $context instanceof Context ) {
			return $repository_args;
		}

		$shortcode_id = $context->get( 'shortcode', false );

		if ( false === $shortcode_id ) {
			return $repository_args;
		}

		$shortcode_args = $this->get_database_arguments( $shortcode_id );

		$repository_args = $this->args_to_repository( (array) $repository_args, (array) $shortcode_args, $context, $view );

		return $repository_args;
	}

	/**
	 * Filters the context locations to add the ones used by Shortcodes.
	 *
	 * @since 4.7.9
	 * @since 5.5.0 Transform into static method to avoid creating instance when not using.
	 *
	 * @since 5.5.0 Move this to a method inside of Shortcodes|Tribe_Events
	 *
	 * @param array $locations The array of context locations.
	 *
	 * @return array The modified context locations.
	 */
	public static function filter_context_locations( array $locations = [] ) {
		$locations['shortcode'] = [
			'read' => [
				Context::REQUEST_VAR   => 'shortcode',
				Context::LOCATION_FUNC => [
					'view_prev_url',
					static function ( $url ) {
						return tribe_get_query_var( $url, 'shortcode', Context::NOT_FOUND );
					},
				],
			],
		];

		return $locations;
	}

	/**
	 * Translates shortcode arguments to their Context argument counterpart.
	 *
	 * @since 4.7.9
	 *
	 * @param array   $arguments The shortcode arguments to translate.
	 * @param Context $context   The request context.
	 *
	 * @return array The translated shortcode arguments.
	 */
	protected function args_to_context( array $arguments, Context $context ) {
		$context_args = [];

		if ( ! empty( $arguments['date'] ) ) {
			$context_args['event_date'] = $arguments['date'];
		}

		if ( isset( $arguments['featured'] ) ) {
			$context_args['featured'] = tribe_is_truthy( $arguments['featured'] );
		}

		if ( ! empty( $arguments['events_per_page'] ) ) {
			$context_args['events_per_page'] = (int) $arguments['events_per_page'];
		}

		if ( tribe_is_truthy( $arguments['is-widget'] ) ) {
			$context_args['is-widget'] = tribe_is_truthy( $arguments['is-widget'] );
		}

		if ( ! empty( $arguments['month_events_per_day'] ) ) {
			$context_args['month_posts_per_page'] = (int) $arguments['month_events_per_day'];
		}

		if ( ! empty( $arguments['week_events_per_day'] ) ) {
			$context_args['week_events_per_day'] = (int) $arguments['week_events_per_day'];
		}

		if ( ! empty( $arguments['keyword'] ) ) {
			$context_args['keyword'] = sanitize_text_field( $arguments['keyword'] );
		}

		if ( null === $context->get( 'eventDisplay' ) ) {
			if ( empty( $arguments['view'] ) ) {
				$default_view_class   = tribe( Views_Manager::class )->get_default_view();
				$context_args['view'] = $context_args['event_display_mode'] = tribe( Views_Manager::class )->get_view_slug_by_class( $default_view_class );
			} else {
				$context_args['view'] = $context_args['event_display_mode'] = $arguments['view'];
			}
		}

		return $context_args;
	}

	/**
	 * Translates shortcode arguments to their Repository argument counterpart.
	 *
	 * @since 4.7.9
	 *
	 * @param array          $repository_args The current repository arguments.
	 * @param array          $arguments       The shortcode arguments to translate.
	 * @param Context        $context         The shortcode arguments to translate.
	 * @param View_Interface $view            The View that will use the repository arguments.
	 *
	 * @return array The translated shortcode arguments.
	 */
	public function args_to_repository( array $repository_args, array $arguments, $context, $view ) {

		if ( ! empty( $arguments['tag'] ) || ! empty( $arguments['category'] ) ) {
			$operand = Arr::get( $arguments, 'tax-operand', 'OR' );

			// Makes sure tax query exists.
			if ( empty( $repository_args['tax_query'] ) ) {
				$repository_args['tax_query'] = [];
			}

			foreach ( [ 'tag' => 'post_tag', 'category' => TEC::TAXONOMY ] as $key => $taxonomy ) {
				if ( empty( $arguments[ $key ] ) ) {
					continue;
				}

				$repository_args['tax_query'] = array_merge_recursive(
					$repository_args['tax_query'],
					Taxonomy::translate_to_repository_args( $taxonomy, $arguments[ $key ], $operand )
				);
			}

			$repository_args['tax_query']['relation'] = $operand;
		}

		if ( isset( $arguments['date'] ) ) {
			// The date can be used in many ways, so we juggle a bit here.
			$date_filters = tribe_events()->get_date_filters();
			$date_keys    = array_filter(
				$repository_args,
				static function ( $key ) use ( $date_filters ) {
					return in_array( $key, $date_filters, true );
				},
				ARRAY_FILTER_USE_KEY
			);

			if ( count( $date_keys ) === 1 ) {
				$date_indices = array_keys( $date_keys );
				$date_index   = reset( $date_indices );
				$date_key     = $date_keys[ $date_index ];
				if ( $date_key === $arguments['date'] ) {
					// Let's only set it if we are sure.
					$repository_args[ $date_index ] = $arguments['date'];
				} else {
					$repository_args[ $date_index ] = $date_key;
				}
			}
		}

		if ( isset( $arguments['featured'] ) ) {
			$repository_args['featured'] = tribe_is_truthy( $arguments['featured'] );
		}

		return $repository_args;
	}

	/**
	 * Alters the context of the view based on the shortcode params stored in the database based on the ID.
	 *
	 * @since  5.0.0
	 *
	 * @param Context $view_context Context for this request.
	 * @param string  $view_slug    Slug of the view we are building.
	 * @param View    $instance     Which view instance we are dealing with.
	 *
	 * @return Context               Altered version of the context ready for shortcodes.
	 */
	public function filter_view_context( Context $view_context, $view_slug, $instance ) {
		if ( ! $shortcode_id = $view_context->get( 'shortcode' ) ) {
			return $view_context;
		}

		$arguments = $this->get_database_arguments( $shortcode_id );

		if ( empty( $arguments ) ) {
			return $view_context;
		}

		/* Week view/widget only. */
		if ( false !== stripos( $view_slug, 'week' ) ) {
			$offset = $this->get_argument( 'week_offset' );
			if ( tribe_is_truthy( $offset ) && empty( $view_context->get( 'eventDate' ) ) ) {
				$start_date  = Dates::build_date_object( $this->get_argument( 'date', 'now' ) );
				$is_negative = '-' === substr( $offset, 0, 1 );
				// Set up for negative weeks.
				$interval = ( $is_negative )
					? substr( $offset, 1, 1 )
					: $offset;

				$di         = Dates::interval( "P{$interval}W" );
				$di->invert = absint( $is_negative );

				$start_date->add( $di );

				$arguments['date'] = $start_date->format( Dates::DBDATEFORMAT );
			}

			$arguments['week_events_per_day'] = $this->get_argument( 'week_events_per_day' );
		}


		return $this->alter_context( $view_context, $arguments );
	}

	/**
	 * Filters the default view in the views manager for shortcodes navigation.
	 *
	 * @since  4.7.9
	 *
	 * @param string $view_class Fully qualified class name for default view.
	 *
	 * @return string             Fully qualified class name for default view of the shortcode in question.
	 */
	public function filter_default_url( $view_class ) {
		if ( tribe_context()->doing_php_initial_state() ) {
			return $view_class;
		}

		// Use the global context here as we should be in the context of an AJAX shortcode request.
		$shortcode_id = tribe_context()->get( 'shortcode', false );

		if ( false === $shortcode_id ) {
			// If we're not in the context of an AJAX shortcode request, bail.
			return $view_class;
		}

		$shortcode_args = $this->get_database_arguments( $shortcode_id );

		if ( ! $shortcode_args['view'] ) {
			return $view_class;
		}

		return tribe( Views_Manager::class )->get_view_class_by_slug( $shortcode_args['view'] );
	}

	/**
	 * Filters the View HTML classes to add some related to PRO features.
	 *
	 * @since 5.0.0
	 *
	 * @param array<string>  $html_classes The current View HTML classes.
	 * @param string         $slug         The View registered slug.
	 * @param View_Interface $view         The View currently rendering.
	 *
	 * @return array<string> The filtered HTML classes.
	 */
	public function filter_view_html_classes( $html_classes, $slug, $view ) {
		$context = $view->get_context();

		if ( ! $context instanceof Context ) {
			return $html_classes;
		}

		$shortcode = $context->get( 'shortcode', false );

		if ( ! $shortcode ) {
			return $html_classes;
		}
		$shortcode_args = $this->get_database_arguments( $shortcode );

		$html_classes[] = 'tribe-events-view--shortcode';
		$html_classes[] = 'tribe-events-view--shortcode-' . $shortcode;

		$container_classes = Arr::get( $shortcode_args, 'container-classes', '' );

		if ( ! empty( $container_classes ) ) {
			$html_classes = array_merge( $html_classes, $container_classes );
		}

		return $html_classes;
	}

	/**
	 * Cleans up an array of values as html classes.
	 *
	 * @since 5.5.0
	 *
	 * @param mixed $value Which classes we are cleaning up.
	 *
	 * @return array Resulting clean html classes.
	 */
	public static function validate_array_html_classes( $value ) {
		if ( ! is_array( $value ) ) {
			$value = explode( ' ', $value );
		}

		return array_map( 'sanitize_html_class', (array) $value );
	}

	/**
	 * Filters the View data attributes to add some related to PRO features.
	 *
	 * @since 5.0.0
	 *
	 * @param array<string,string> $data The current View data attributes classes.
	 * @param string               $slug The View registered slug.
	 * @param View_Interface       $view The View currently rendering.
	 *
	 * @return array<string,string> The filtered data attributes.
	 */
	public function filter_view_data( $data, $slug, $view ) {
		if ( ! $view instanceof View_Interface ) {
			return $data;
		}

		$context = $view->get_context();

		if ( ! $context instanceof Context ) {
			return $data;
		}

		if ( $shortcode = $context->get( 'shortcode', false ) ) {
			$data['shortcode'] = $shortcode;
		}

		return $data;
	}

	/**
	 * Filters the View URL to add the shortcode query arg, if required.
	 *
	 * @since 4.7.9
	 * @since 5.5.0 Moved this from deprecated Shortcodes\Manager.
	 *
	 * @param string         $url       The View current URL.
	 * @param bool           $canonical Whether to return the canonical version of the URL or the normal one.
	 * @param View_Interface $view      This view instance.
	 *
	 * @return string  The URL for the view shortcode.
	 */
	public function filter_view_url( $url, $canonical, View_Interface $view ) {
		$context = $view->get_context();

		if ( empty( $url ) ) {
			return $url;
		}

		if ( ! $context instanceof Context ) {
			return $url;
		}

		$shortcode_id = $context->get( 'shortcode', false );

		if ( false === $shortcode_id ) {
			return $url;
		}

		return add_query_arg( [ 'shortcode' => $shortcode_id ], $url );
	}

	/**
	 * Filters the query arguments array and add the Shortcodes.
	 *
	 * @since 4.7.9
	 * @since 5.5.0 Moved this from deprecated Shortcodes\Manager.
	 *
	 * @param array          $query     Arguments used to build the URL.
	 * @param string         $view_slug The current view slug.
	 * @param View_Interface $view      The current View object.
	 *
	 * @return  array  Filtered the query arguments for shortcodes.
	 */
	public function filter_view_url_query_args( array $query, $view_slug, View_Interface $view ) {
		$context = $view->get_context();

		if ( ! $context instanceof Context ) {
			return $query;
		}

		$shortcode = $context->get( 'shortcode', false );

		if ( false === $shortcode ) {
			return $query;
		}

		$query['shortcode'] = $shortcode;

		return $query;
	}

	/**
	 * Filter the breakpoints for the week view widget based on layout.
	 *
	 * @since 5.6.0
	 *
	 * @param array $breakpoints All breakpoints available.
	 * @param View  $view        The current View instance being rendered.
	 *
	 * @return array Modified array of available breakpoints.
	 */
	public function filter_week_view_breakpoints( $breakpoints, $view ) {
		$context   = $view->get_context();
		$widget    = $context->get( 'is-widget', false );
		$shortcode = $context->get( 'shortcode', false );

		if ( false === $widget ) {
			return $breakpoints;
		}

		if ( false === $shortcode ) {
			return $breakpoints;
		}

		$shortcode_args = $this->get_database_arguments( $shortcode );
		if ( ! $shortcode_args ) {
			return $breakpoints;
		}

		if ( 'vertical' === $shortcode_args['layout'] ) {
			// Remove all breakpoints to remain in "mobile view".
			return [];
		} elseif ( 'horizontal' === $shortcode_args['layout'] ) {
			// Simplify breakpoints to remain in "desktop view".
			unset( $breakpoints['xsmall'] );
			$breakpoints['medium'] = 0;

			return $breakpoints;
		}

		// Fallback and space for "auto".
		return $breakpoints;
	}

	/**
	 * Modify the Week events per day of a given view based on arguments from Shortcode.
	 *
	 * @since 5.6.0
	 *
	 * @param int|string $events_per_day Number of events per day.
	 * @param View       $view           Current view being rendered.
	 *
	 * @return mixed
	 */
	public function filter_week_events_per_day( $events_per_day, $view ) {
		$context   = $view->get_context();
		$shortcode = $context->get( 'shortcode', false );

		if ( false === $shortcode ) {
			return $events_per_day;
		}

		$shortcode_args = $this->get_database_arguments( $shortcode );
		if ( ! $shortcode_args || ! isset( $shortcode_args['count'] ) ) {
			return $events_per_day;
		}

		return $shortcode_args['count'];
	}
}
