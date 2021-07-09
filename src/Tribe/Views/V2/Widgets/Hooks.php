<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( Tribe\Events\Pro\Views\V2\Hooks::class ), 'some_filtering_method' ] );
 * remove_filter( 'some_filter', [ tribe( 'pro.views.v2.hooks' ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( Tribe\Events\Pro\Views\V2\Hooks::class ), 'some_method' ] );
 * remove_action( 'some_action', [ tribe( 'pro.views.v2.hooks' ), 'some_method' ] );
 *
 * @since   5.2.0
 *
 * @package Tribe\Events\Pro\Views\V2\Widgets
 */

namespace Tribe\Events\Pro\Views\V2\Widgets;

use Tribe\Events\Pro\Views\V2\Views\Widgets\Countdown_View;
use Tribe\Events\Pro\Views\V2\Views\Widgets\Venue_View;
use Tribe\Events\Pro\Views\V2\Views\Widgets\Week_View;
use Tribe\Events\Pro\Views\V2\Widgets\Traits\Widget_Shortcode;
use Tribe\Events\Views\V2\View_Interface;
use Tribe\Events\Views\V2\Views\Widgets\Widget_View;
use Tribe\Events\Views\V2\Widgets\Widget_Abstract;
use Tribe\Events\Views\V2\Widgets\Widget_List;
use \Tribe\Events\Pro\Views\V2\Shortcodes\Tribe_Events as Tribe_Events_Shortcode;

/**
 * Class Hooks.
 *
 * @since   5.2.0
 *
 * @package Tribe\Events\Pro\Views\V2\Widgets
 */
class Hooks extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.2.0
	 */
	public function register() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds the actions for V2 widgets.
	 *
	 * @since 5.2.0
	 */
	protected function add_actions() {
		add_action(
			'tribe_template_entry_point:events/v2/widgets/widget-events-list/event:event_meta',
			[ $this, 'widget_events_list_event_meta_cost' ],
			10,
			3
		);

		add_action(
			'tribe_template_entry_point:events/v2/widgets/widget-events-list/event:event_meta',
			[ $this, 'widget_events_list_event_meta_venue' ],
			15,
			3
		);

		add_action(
			'tribe_template_entry_point:events/v2/widgets/widget-events-list/event:event_meta',
			[ $this, 'widget_events_list_event_meta_organizers' ],
			20,
			3
		);

		add_action(
			'tribe_template_entry_point:events/v2/widgets/widget-events-list/event/date:after_event_datetime',
			[ $this, 'widget_events_list_event_recurring_icon' ],
			10,
			3
		);

		add_action(
			'tribe_events_pro_v1_registered_widget_classes',
			[ $this, 'deregister_v1_widgets' ]
		);

		add_action(
			'tribe_events_pro_v1_unregistered_widget_classes',
			[ $this, 'unregister_v1_widgets' ]
		);

		add_action(
			'wp_ajax_tribe_widget_dropdown_events',
			[ $this, 'ajax_get_events' ]
		);

		add_action(
			'wp_ajax_tribe_widget_dropdown_venues',
			[ $this, 'ajax_get_venues' ]
		);

		add_action(
			'tribe_events_views_v2_before_make_view_for_rest',
			[ $this, 'maybe_toggle_hooks_for_rest' ],
			15,
			3
		);

		add_action( 'tribe_plugins_loaded', [ $this, 'maybe_migrate_legacy_sidebars' ] );
	}

	/**
	 * Adds the filters for V2 Widgets
	 *
	 * @since 5.2.0
	 */
	protected function add_filters() {
		add_filter( 'tribe_widgets', [ $this, 'filter_register_widget' ] );
		add_filter( 'tribe_events_views', [ $this, 'filter_add_widget_views' ] );
		add_filter( 'tribe_cache_last_occurrence_option_triggers', [ $this, 'filter_add_widget_caching_triggers' ], 15, 3 );

		// Setup the Advanced List Widget by filtering the The Events Calendar List Widget.
		add_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_widget_recurrence_repository_args' ], 10, 2 );
		add_filter( 'tribe_widget_events-list_default_arguments', [ $this, 'filter_list_widget_default_arguments' ] );
		add_filter( 'tribe_widget_events-list_admin_fields', [ $this, 'filter_list_widget_admin_fields' ] );
		add_filter( 'tribe_widget_events-list_updated_instance', [ $this, 'filter_list_widget_updated_instance' ], 10, 2 );
		add_filter( 'tribe_widget_events-list_args_to_context', [ $this, 'filter_list_widget_args_to_context' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_widget-events-list_template_vars', [ $this, 'filter_list_widget_template_vars' ], 10, 2 );
		add_filter( 'tribe_widget_field_data', [ $this, 'filter_taxonomy_filters_field_data' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_widget_repository_args', [ $this, 'filter_repository_taxonomy_args' ], 10, 3 );
		add_filter( 'tribe_customizer_inline_stylesheets', [ $this, 'filter_add_full_stylesheet_to_customizer' ], 12, 2 );

		add_filter( 'tribe_events_pro_shortcodes_list_widget_class', [ $this, 'alter_list_widget_class' ], 10, 2 );
		add_filter( 'tribe_events_pro_shortcodes_countdown_widget_class', [ $this, 'alter_shortcode_countdown_widget_class' ], 10, 2 );
		add_filter( 'tribe_events_pro_shortcodes_venue_widget_class', [ $this, 'alter_venue_widget_class' ], 10, 2 );
		add_filter( 'tribe_events_pro_shortcodes_week_widget_class', [ $this, 'alter_week_widget_class' ], 10, 2 );
		add_filter( 'tribe_events_pro_shortcodes_month_widget_class', [ $this, 'alter_shortcode_month_widget_class' ], 10, 2 );

		add_filter(
			'tribe_events_pro_shortcode_compatibility_required',
			[ $this, 'alter_shortcode_compatibility_required' ],
			10,
			2
		);
	}

	/**
	 * Stop registration of the v1 Widgets as needed.
	 *
	 * @since 5.2.0
	 *
	 * @param array<string> $widgets The array of widget classes to register.
	 *
	 * @return array<string> $widgets The modified array of widget classes to register.
	 */
	public function deregister_v1_widgets( $widgets ) {
		unset( $widgets['Tribe__Events__Pro__Advanced_List_Widget'] );
		unset( $widgets['Tribe__Events__Pro__Countdown_Widget'] );
		unset( $widgets['Tribe__Events__Pro__Venue_Widget'] );
		unset( $widgets['Tribe__Events__Pro__Mini_Calendar_Widget'] );
		unset( $widgets['Tribe__Events__Pro__This_Week_Widget'] );

		return $widgets;
	}

	/**
	 * Un-registration of the v1 Widgets as needed.
	 *
	 * @since 5.2.0
	 *
	 * @param array<string> $widgets The array of widget classes to unregister.
	 *
	 * @return array<string> $widgets The modified array of widget classes to unregister.
	 */
	public function unregister_v1_widgets( $widgets ) {
		$widgets[] = 'Tribe__Events__Pro__Advanced_List_Widget';
		$widgets[] = 'Tribe__Events__Pro__Countdown_Widget';
		$widgets[] = 'Tribe__Events__Pro__Venue_Widget';
		$widgets[] = 'Tribe__Events__Pro__Mini_Calendar_Widget';
		$widgets[] = 'Tribe__Events__Pro__This_Week_Widget';

		return $widgets;
	}

	/**
	 * Add the widgets to register with WordPress.
	 *
	 * @since 5.2.0
	 * @since 5.3.0 Added Countdown Widget.
	 * @since 5.5.0 Moved from Service_Provider class to the Hooks class.
	 *
	 * @param array<string,string> $widgets An array of widget classes to register.
	 *
	 * @return array<string,string> An array of registered widget classes.
	 */
	public function filter_register_widget( $widgets ) {
		$widgets[ Widget_Countdown::get_widget_slug() ]      = Widget_Countdown::class;
		$widgets[ Widget_Featured_Venue::get_widget_slug() ] = Widget_Featured_Venue::class;
		$widgets[ Widget_Month::get_widget_slug() ]          = Widget_Month::class;
		$widgets[ Widget_Week::get_widget_slug() ]           = Widget_Week::class;

		return $widgets;
	}

	/**
	 * Add the widget views to the view manager.
	 *
	 * @since 5.2.0
	 * @since 5.3.0 Added Countdown Widget view.
	 * @since 5.5.0 Moved from Service_Provider class to the Hooks class.
	 *
	 * @param array<string,string> $views An associative array of views in the shape `[ <slug> => <class> ]`.
	 *
	 * @return array<string,string> $views The modified array of views in the shape `[ <slug> => <class> ]`.
	 */
	public function filter_add_widget_views( $views ) {
		$views['widget-countdown']      = Countdown_View::class;
		$views['widget-featured-venue'] = Venue_View::class;
		$views['widget-week']           = Week_View::class;

		return $views;
	}

	/**
	 * Swaps in the new V2 widget for the old one in the widget shortcode.
	 *
	 * @since 5.2.0
	 * @since 5.3.0 renamed to indicate this is specific to the List Widget.
	 * @since 5.5.0 Moved from Service_Provider class to the Hooks class.
	 *
	 * @param string              $widget_class The widget class name we're currently implementing.
	 * @param array<string,mixed> $arguments    The widget arguments.
	 *
	 * @return string             $widget_class The modified (V2) widget class name we want to implement.
	 */
	public function alter_list_widget_class( $widget_class, $arguments ) {
		return Widget_List::class;
	}

	/**
	 * Swaps in the new Countdown V2 widget for the old one in the widget shortcode.
	 *
	 * @since 5.3.0
	 * @since 5.5.0 Moved from Service_Provider class to the Hooks class.
	 *
	 * @param string              $widget_class The widget class name we're currently implementing.
	 * @param array<string,mixed> $arguments    The widget arguments.
	 *
	 * @return string             $widget_class The modified (V2) widget class name we want to implement.
	 */
	public function alter_shortcode_countdown_widget_class( $widget_class, $arguments ) {
		return Widget_Countdown::class;
	}

	public function alter_shortcode_calendar_widget_class( $widget_class, $arguments ) {
		return Widget_Month::class;
	}

	/**
	 * Swaps in the new Featured Venue V2 widget for the old one in the widget shortcode.
	 *
	 * @since 5.3.0
	 * @since 5.5.0 Moved from Service_Provider class to the Hooks class.
	 *
	 * @param string              $widget_class The widget class name we're currently implementing.
	 * @param array<string,mixed> $arguments    The widget arguments.
	 *
	 * @return string             $widget_class The modified (V2) widget class name we want to implement.
	 */
	public function alter_venue_widget_class( $widget_class, $arguments ) {
		return Widget_Featured_Venue::class;
	}

	/**
	 * Swaps in the new Featured Venue V2 widget for the old one in the widget shortcode.
	 *
	 * @since 5.5.0
	 *
	 * @param string              $widget_class The widget class name we're currently implementing.
	 * @param array<string,mixed> $arguments    The widget arguments.
	 *
	 * @return string             $widget_class The modified (V2) widget class name we want to implement.
	 */
	public function alter_week_widget_class( $widget_class, $arguments ) {
		return Widget_Week::class;
	}

	/**
	 * Swaps in the new Featured Venue V2 widget for the old one in the widget shortcode.
	 *
	 * @since 5.5.0
	 *
	 * @param string              $widget_class The widget class name we're currently implementing.
	 * @param array<string,mixed> $arguments    The widget arguments.
	 *
	 * @return string             $widget_class The modified (V2) widget class name we want to implement.
	 */
	public function alter_shortcode_month_widget_class( $widget_class, $arguments ) {
		return Widget_Month::class;
	}

	/**
	 * Creates the select2 event dropdown for widgets.
	 *
	 * @since 5.3.0
	 *
	 * @return string The HTML to display.
	 */
	public function ajax_get_events() {
		return $this->container->make( Ajax::class )->get_events();
	}

	/**
	 * Creates the select2 venue dropdown for widgets.
	 *
	 * @since 5.3.0
	 *
	 * @return array The array of venues.
	 */
	public function ajax_get_venues() {
		return $this->container->make( Ajax::class )->get_venues();
	}

	/**
	 * Checks if we need to migrate the Widgets and Sidebars after an update.
	 *
	 * @since 5.6.0
	 *
	 * @return void
	 */
	public function maybe_migrate_legacy_sidebars() {
		// First migrate the sidebars.
		$this->container->make( Compatibility::class )->migrate_legacy_sidebars();

		// Now we can migrate the widgets.
		$this->container->make( Compatibility::class )->migrate_legacy_widgets();
	}

	/**
	 * Action to inject the cost meta into the events list widget event.
	 *
	 * @since 5.2.0
	 *
	 * @param string           $file     Complete path to include the PHP File.
	 * @param array<string>    $name     Template name.
	 * @param \Tribe__Template $template Current instance of the Tribe__Template.
	 */
	public function widget_events_list_event_meta_cost( $file, $name, $template ) {
		$this->container->make( Widget_Advanced_List::class )->render_event_cost( $template );
	}

	/**
	 * Action to inject the venue meta into the events list widget event.
	 *
	 * @since 5.2.0
	 *
	 * @param string           $file     Complete path to include the PHP File.
	 * @param array<string>    $name     Template name.
	 * @param \Tribe__Template $template Current instance of the Tribe__Template.
	 */
	public function widget_events_list_event_meta_venue( $file, $name, $template ) {
		$this->container->make( Widget_Advanced_List::class )->render_event_venue( $template );
	}

	/**
	 * Action to inject the organizers meta into the events list widget event.
	 *
	 * @since 5.2.0
	 *
	 * @param string           $file     Complete path to include the PHP File.
	 * @param array<string>    $name     Template name.
	 * @param \Tribe__Template $template Current instance of the Tribe__Template.
	 */
	public function widget_events_list_event_meta_organizers( $file, $name, $template ) {
		$this->container->make( Widget_Advanced_List::class )->render_event_organizers( $template );
	}

	/**
	 * Action to inject the recurring icon into the events list widget event.
	 *
	 * @since 5.2.0
	 *
	 * @param string           $file     Complete path to include the PHP File.
	 * @param array<string>    $name     Template name.
	 * @param \Tribe__Template $template Current instance of the Tribe__Template.
	 */
	public function widget_events_list_event_recurring_icon( $file, $name, $template ) {
		$this->container->make( Widget_Advanced_List::class )->render_event_recurring_icon( $template );
	}

	/**
	 * Filter the default arguments for the list widget.
	 *
	 * @since 5.2.0
	 *
	 * @param array<string,mixed> $arguments Current set of arguments.
	 *
	 * @return array<string,mixed> The map of widget default arguments.
	 */
	public function filter_list_widget_default_arguments( $arguments ) {
		return $this->container->make( Widget_Advanced_List::class )->filter_default_arguments( $arguments );
	}

	/**
	 * Filter the admin fields for the list widget.
	 *
	 * @since 5.2.0
	 *
	 * @param array<string,mixed> $admin_fields The array of widget admin fields.
	 *
	 * @return array<string,mixed> The array of widget admin fields.
	 */
	public function filter_list_widget_admin_fields( $admin_fields ) {
		return $this->container->make( Widget_Advanced_List::class )->filter_admin_fields( $admin_fields );
	}

	/**
	 * Filters the updated instance for the list widget.
	 *
	 * @since 5.2.0
	 *
	 * @param array<string,mixed> $updated_instance The updated instance of the widget.
	 * @param array<string,mixed> $new_instance     The new values for the widget instance.
	 *
	 * @return array<string,mixed> The updated instance to be saved for the widget.
	 */
	public function filter_list_widget_updated_instance( $updated_instance, $new_instance ) {
		return $this->container->make( Widget_Advanced_List::class )->filter_widgets_updated_instance( $updated_instance, $new_instance );
	}

	/**
	 * Filters the args to context for the list widget.
	 *
	 * @since 5.2.0
	 *
	 * @param array<string,mixed> $alterations The alterations to make to the context.
	 * @param array<string,mixed> $arguments   Current set of arguments.
	 * @param Widget_Abstract     $widget      The widget instance we are dealing with.
	 *
	 * @return array<string,mixed> $alterations The alterations to make to the context.
	 */
	public function filter_list_widget_args_to_context( $alterations, $arguments, $widget ) {
		return $this->container->make( Widget_Advanced_List::class )->filter_args_to_context( $alterations, $arguments, $widget );
	}

	/**
	 * Filters the template vars for the list widget.
	 *
	 * @since 5.2.0
	 *
	 * @param array<string,mixed> $template_vars  The updated instance of the widget.
	 * @param View_Interface      $view_interface The current view template.
	 *
	 * @return array<string,mixed> $template_vars The updated instance of the widget.
	 */
	public function filter_list_widget_template_vars( $template_vars, $view_interface ) {
		return $this->container->make( Widget_Advanced_List::class )->filter_template_vars( $template_vars, $view_interface );
	}

	/**
	 * Adds the (hide) Recurring event instances setting to the widget args.
	 *
	 * @since 5.2.0
	 * @since 5.3.0   Apply to all widgets.
	 *
	 * @param array<string,mixed> $args    The unmodified arguments.
	 * @param \Tribe__Context     $context The context.
	 *
	 * @return array<string,mixed> The arguments, ready to be set on the View repository instance.
	 */
	public function filter_widget_recurrence_repository_args( $args, $context ) {
		if ( ! $context->get( 'widget' ) ) {
			return $args;
		}

		$hide_recurring = tribe_is_truthy( tribe_get_option( 'hideSubsequentRecurrencesDefault', false ) );

		/**
		 * Allows filtering recurrence display rules for widgets.
		 *
		 * @since 5.3.0
		 *
		 * @param boolean             $hide_recurring Whether to hide (true) or show (false) the subsequent recurrences of events.
		 * @param array<string,mixed> $args           The unmodified arguments.
		 * @param \Tribe__Context     $context        The context.
		 */
		$hide_recurring = apply_filters( 'tribe_widget_hide_subsequent_recurring_events', $hide_recurring, $args, $context );

		$args['hide_subsequent_recurrences'] = $hide_recurring;

		/**
		 * @todo @bordoni We need to check if this is really necessary, it seems like if someone filtered this all the
		 *       subsequent queries would be broken.
		 */
		if ( $hide_recurring ) {
			add_filter( 'tribe_repository_events_collapse_recurring_event_instances', '__return_true' );
		}

		return $args;
	}

	/**
	 * Adds the correct field data to the taxonomy input.
	 *
	 * @since 5.2.0
	 *
	 * @param array<string,mixed> $data       The field data we're editing.
	 * @param array<string,mixed> $field_name The info for the field we're rendering.
	 * @param Widget_Abstract     $widget_obj The widget object.
	 *
	 * @return array<string,mixed>
	 */
	public function filter_taxonomy_filters_field_data( $data, $field_name, $widget_obj ) {
		return $this->container->make( Taxonomy_Filter::class )->add_taxonomy_filters_field_data( $data, $field_name, $widget_obj );
	}

	/**
	 * Add some repository args pre-query.
	 *
	 * @since 5.1.1
	 * @since 5.3.0 Include $widget_view param.
	 *
	 * @param array<string,mixed> $args        The arguments to be set on the View repository instance.
	 * @param \Tribe__Context     $context     The context to use to setup the args.
	 * @param Widget_View         $widget_view Widget View being filtered.
	 *
	 * @return array<string,mixed> $args       The arguments, ready to be set on the View repository instance.
	 */
	public function filter_repository_taxonomy_args( $args, $context, $widget_view ) {
		return $this->container->make( Taxonomy_Filter::class )->add_taxonomy_filters_repository_args( $args, $context, $widget_view );
	}

	/**
	 * Add full events list widget stylesheets to customizer styles array to check.
	 *
	 * @since 5.2.0
	 *
	 * @param array<string> $sheets       Array of sheets to search for.
	 * @param string        $css_template String containing the inline css to add.
	 *
	 * @return array Modified array of sheets to search for.
	 */
	public function filter_add_full_stylesheet_to_customizer( $sheets, $css_template ) {
		return array_merge( $sheets, [ 'tribe-events-widgets-v2-events-list-full' ] );
	}

	/**
	 * Removes the compatibility container for widgets, as that will be handled by the widget itself.
	 *
	 * @since 5.5.0
	 *
	 * @param bool                   $compatibility_required Is compatibility required for this shortcode.
	 * @param Tribe_Events_Shortcode $shortcode              Shortcode instance that is being rendered.
	 *
	 * @return bool
	 */
	public function alter_shortcode_compatibility_required( $compatibility_required, $shortcode ) {
		if ( $shortcode->get_argument( 'is-widget' ) ) {
			return false;
		}

		return $compatibility_required;
	}

	/**
	 * Maybe toggles the hooks for a widget class on a rest request.
	 *
	 * @since 5.5.0
	 *
	 * @param string           $slug    The current view Slug.
	 * @param array            $params  Params so far that will be used to build this view.
	 * @param \WP_REST_Request $request The rest request that generated this call.
	 */
	public function maybe_toggle_hooks_for_rest( $slug, $params, \WP_REST_Request $request ) {
		Widget_Shortcode::maybe_toggle_hooks_for_rest( $slug, $params, $request );
	}

	/**
	 * Filters the caching triggers to add the widgets Week and Month.
	 *
	 * @since 5.6.0
	 *
	 * @param array<string,bool> $triggers Which options will trigger this given action last occurrence.
	 * @param string             $action   Which action this trigger will set.
	 * @param array              $args     Which arguments from the updated option method.
	 *
	 * @return array
	 */
	public function filter_add_widget_caching_triggers( $triggers, $action, $args ) {
		$triggers[ 'widget_' . Widget_Month::PREFIX . Widget_Month::get_widget_slug() ] = true;
		$triggers[ 'widget_' . Widget_Week::PREFIX . Widget_Week::get_widget_slug() ]   = true;

		return $triggers;
	}

	/**********************
	 * Deprecated Methods *
	 **********************/

	/**
	 * Adds the (hide) Recurring event instances setting to the widget args.
	 *
	 * @see        filter_widget_recurrence_repository_args()
	 *
	 * @since      5.2.0
	 *
	 * @deprecated 5.3.0 Deprecated in favor of one function for all widgets.
	 *
	 * @param \Tribe__Context     $context The context.
	 * @param array<string,mixed> $args    The unmodified arguments.
	 *
	 * @return array<string,mixed> The arguments, ready to be set on the View repository instance.
	 */
	public function filter_list_widget_repository_args( $args, $context ) {
		_deprecated_function( __METHOD__, '5.3.0', 'filter_widget_recurrence_repository_args' );

		return $this->filter_widget_recurrence_repository_args( $args, $context );
	}

	/**
	 * Action to enqueue assets for PRO version of events list widget.
	 *
	 * @since      5.2.0
	 *
	 * @deprecated 5.5.0 Deprecated in favor of using Widget_List::is_in_use() on conditional for asset.
	 *
	 * @param \Tribe__Context $context        Context we are using to build the view.
	 * @param View_Interface  $view           Which view we are using the template on.
	 *
	 * @param boolean         $should_enqueue Whether assets are enqueued or not.
	 */
	public function widget_events_list_after_enqueue_assets( $should_enqueue, $context, $view ) {
		_deprecated_function( __METHOD__, '5.5.0', 'Widget_List::is_in_use()' );
	}

	/**
	 * Action to enqueue assets for PRO version of events countdown widget.
	 *
	 * @since      5.2.0
	 *
	 * @deprecated 5.5.0 Deprecated in favor of using Widget_Countdown::is_in_use() on conditional for asset.
	 *
	 * @param \Tribe__Context $context        Context we are using to build the view.
	 * @param View_Interface  $view           Which view we are using the template on.
	 *
	 * @param boolean         $should_enqueue Whether assets are enqueued or not.
	 */
	public function widget_events_countdown_after_enqueue_assets( $should_enqueue, $context, $view ) {
		_deprecated_function( __METHOD__, '5.5.0', 'Widget_Countdown::is_in_use()' );
	}
}
