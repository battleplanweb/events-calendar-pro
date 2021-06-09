<?php
/**
 * Compatibility for Month View Widget and Week View Widget.
 *
 * @since   5.6.0
 *
 * @package Tribe\Events\Pro\Views\V2\Widgets
 */

namespace Tribe\Events\Pro\Views\V2\Widgets;

/**
 * Class Compatibility
 *
 * @since   5.6.0
 *
 * @package Tribe\Events\Pro\Views\V2\Widgets
 */
class Compatibility {
	/**
	 * The default primary shortcode widget id base string.
	 * In the format 'legacy' => 'updated'
	 *
	 * @since 5.6.0
	 *
	 * @var array<string,string>
	 */
	protected $id_migration_map = [
		'tribe-mini-calendar'           => 'tribe-widget-events-month',
		'tribe-this-week-events-widget' => 'tribe-widget-events-week',
	];

	/**
	 * Get the migration map for Widget IDs.
	 *
	 * @since 5.6.0
	 *
	 * @return array<string,string>
	 */
	public function get_id_migration_map() {
		/**
		 * Filter the migration map for Widget IDs.
		 *
		 * @since 5.6.0
		 *
		 * @param array<string,string> $id_migration_map Map to migrate the Widget IDs.
		 */
		return apply_filters( 'tribe_events_pro_views_v2_widgets_compatibility_id_migration_map', $this->id_migration_map );
	}

	/**
	 * Merge the Event List and Advanced List Widget Options.
	 *
	 * @since 5.6.0
	 *
	 * @return void
	 */
	public function migrate_legacy_widgets() {
		$id_migration_map = $this->get_id_migration_map();
		array_walk( $id_migration_map, [ $this, 'migrate_legacy_widget_options' ] );
	}

	/**
	 * Converts between legacy and new in the database.
	 *
	 * @since 5.6.0
	 *
	 * @param string $new_id_base    The new widget ID base.
	 * @param string $legacy_id_base The old widget ID base.
	 *
	 * @return void
	 */
	public function migrate_legacy_widget_options( $new_id_base, $legacy_id_base ) {
		// Get the saved initial widgets.
		$primary_options = get_option( "widget_{$legacy_id_base}" );

		if ( ! is_array( $primary_options ) ) {
			return;
		}

		$new_options = get_option( "widget_{$new_id_base}" );
		if ( empty( $new_options ) ) {
			$new_options = [];
		}

		foreach( $primary_options as $id => $widget ) {
			// Enforce our new limit on the "count" option.
			if ( isset( $widget['count'] ) && 10 < $widget['count'] ) {
				$primary_options[ $id ]['count'] = 10;
			}
		}

		// Combine arrays save.
		$new_options += $primary_options;

		$update = update_option( "widget_{$new_id_base}", $new_options );

		if ( $update ) {
			// remove the old widget settings
			delete_option( "widget_{$legacy_id_base}" );
		}
	}

	/**
	 * Using the `sidebars_widgets` value from the options table we migrate the widget IDs than update the option with the new IDs.
	 *
	 * @since 5.6.0
	 *
	 * @return boolean
	 */
	public function migrate_legacy_sidebars() {
		$sidebars = get_option( 'sidebars_widgets' );

		if ( empty( $sidebars ) ) {
			return false;
		}

		if ( ! is_array( $sidebars ) ) {
			return false;
		}

		$sidebars = $this->migrate_legacy_widget_ids( $sidebars );

		return update_option( 'sidebars_widgets', $sidebars );
	}

	/**
	 * Given a set of sidebar widgets, update the widget ids based on the map of legacy and update widgets.
	 *
	 * @since 5.6.0
	 *
	 * @param array $sidebars Sidebars to be migrated.
	 *
	 * @return array Sidebars array after having the widgets IDs migrated.
	 */
	public function migrate_legacy_widget_ids( array $sidebars ) {
		$id_migration_map = $this->get_id_migration_map();
		foreach ( $id_migration_map as $legacy_id_base => $new_id_base ) {
			$legacy_widget = get_option( "widget_{$legacy_id_base}" );

			// If we don't have any legacy widget saved we skip this one.
			if ( ! is_array( $legacy_widget ) || empty( $legacy_widget ) ) {
				continue;
			}

			// All of our widgets have at least 2 items when valid.
			if ( 1 === count( $legacy_widget ) ) {
				continue;
			}

			foreach ( $sidebars as $sidebar_id => $widgets ) {
				if ( ! is_array( $widgets ) || empty( $widgets ) ) {
					continue;
				}

				foreach ( $widgets as $index => $widget ) {
					// We are specifically using 0 to check if the legacy shows on the start of the string.
					if ( 0 !== stripos( $widget, $legacy_id_base ) ) {
						continue;
					}

					$sidebars[ $sidebar_id ][ $index ] = str_replace( $legacy_id_base, $new_id_base, $widget );
				}
			}
		}

		return $sidebars;
	}
}
