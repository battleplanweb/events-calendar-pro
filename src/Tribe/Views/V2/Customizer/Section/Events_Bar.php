<?php
/**
 * The Events Calendar Customizer Section Class
 * Events Bar
 *
 * @since 5.8.0
 */

namespace Tribe\Events\Pro\Views\V2\Customizer\Section;

/**
 * Month View
 *
 * @since 5.8.0
 */
class Events_Bar {

	/**
	 * Add default settings for our injected controls.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed> $settings The default settings.
	 *
	 * @return array<string|mixed> $settings The adjusted settings.
	 */
	public function filter_events_bar_default_settings ( $defaults ) {
		$pro_defaults = [
			'view_selector_background_color_choice' => 'default',
			'view_selector_background_color'        => '#FFFFFF',
		];

		return array_merge( $defaults, $pro_defaults );
	}

	/**
	 * Add content settings for our injected controls.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed> $settings The content settings.
	 *
	 * @return array<string|mixed> $settings The adjusted settings.
	 */
	public function filter_events_bar_content_settings ( $settings ) {
		$pro_settings = [
			'view_selector_background_color_choice' => [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'view_selector_background_color'        => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
		];

		return array_merge( $settings, $pro_settings );
	}

	/**
	 * Add controls.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed> $controls The existing controls.
	 *
	 * @return array<string|mixed> $controls The amended controls.
	 */
	public function filter_events_bar_content_controls ( $controls ) {
		$customizer = tribe( 'customizer' );
		$enabled_views = tribe_get_option( 'tribeEnableViews', [] );

		$pro_controls = [
			'view_selector_background_color_choice' => [
				'priority'    => 27, // These are chosen based on the priority in Tribe\Events\Views\V2\Customizer\Section\Events_Bar.
				'type'        => 'radio',
				'label'       => esc_html_x(
					'View Dropdown Background Color',
					'The View Selector background color setting label.',
					'the-events-calendar'
				),
				'choices'     => [
					'default' => esc_html_x(
						'Use Event Bar Color',
						'Label for the default option.',
						'the-events-calendar'
					),
					'custom'	  => esc_html_x(
						'Custom',
						'Label for option to set a custom color.',
						'the-events-calendar'
					),
				],
				'active_callback' => function( $control ) use ( $customizer, $enabled_views ) {
					return 3 < count( $enabled_views );
				},
			],
			'view_selector_background_color'        => [
				'priority'    => 28, // Immediately after view_selector_background_color_choice.
				'type'        => 'color',
				'active_callback' => function( $control ) use ( $customizer, $enabled_views ) {
					$setting_name = $customizer->get_setting_name( 'view_selector_background_color_choice', $control->section );
					$value = $control->manager->get_setting( $setting_name )->value();
					return 'custom' === $value && 3 < count( $enabled_views );
				},
			],
		];

		return array_merge( $controls, $pro_controls );
	}

	/**
	 * Add CSS based on out new controls.
	 *
	 * @since 5.8.0
	 *
	 * @param string $css_template The existing CSS.
	 * @param mixed $section       The section instance we are dealing with (Events_Bar).
	 *
	 * @return string $css_template The amended CSS.
	 */
	public function filter_events_bar_css_template ( $css_template, $section ) {
		// These allow us to continue to _not_ target the shortcode.
		$apply_to_shortcode = apply_filters( 'tribe_customizer_should_print_shortcode_customizer_styles', false );
		$tribe_events       = $apply_to_shortcode ? '.tribe-events' : '.tribe-events:not( .tribe-events-view--shortcode )';

		$css_template .= "\n/* PRO Injected Styles */\n";

		if ( $section->should_include_setting_css( 'events_bar_text_color' ) ) {
			$text_color_obj     = new \Tribe__Utils__Color( $section->get_option( 'events_bar_text_color' ) );
			$text_color         = $text_color_obj->getRgb();
			$text_color_rgb     = $text_color['R'] . ',' . $text_color['G'] . ',' . $text_color['B'];
			$text_color_hover   = 'rgba(' . $text_color_rgb . ',0.12)';

			$css_template .= "
				{$tribe_events} .tribe-events-c-view-selector--labels:not(.tribe-events-c-view-selector--tabs) .tribe-events-c-view-selector__list-item-link:focus,
				{$tribe_events} .tribe-events-c-view-selector--labels:not(.tribe-events-c-view-selector--tabs) .tribe-events-c-view-selector__list-item-link:hover {
					background-color: $text_color_hover;
				}
			";
			$css_template .= "
				.tribe-common--breakpoint-medium{$tribe_events} .tribe-events-c-view-selector--labels .tribe-events-c-view-selector__button-text,
				{$tribe_events} .tribe-events-c-view-selector--labels:not(.tribe-events-c-view-selector--tabs) .tribe-events-c-view-selector__list-item-text,
				{$tribe_events} .tribe-events-c-view-selector--labels:not(.tribe-events-c-view-selector--tabs) .tribe-events-c-view-selector__list-item-link:focus .tribe-events-c-view-selector__list-item-text,
				{$tribe_events} .tribe-events-c-view-selector--labels:not(.tribe-events-c-view-selector--tabs) .tribe-events-c-view-selector__list-item-link:hover .tribe-events-c-view-selector__list-item-text {
					color: <%= tec_events_bar.events_bar_text_color %>;
				}
			";
		}

		if ( $section->should_include_setting_css( 'events_bar_icon_color_choice' ) ) {
			if ( 'custom' === $section->get_option( 'events_bar_icon_color_choice' ) ) {
				$color = "<%= tec_events_bar.events_bar_icon_color %>";
			} elseif (
				'accent' === $section->get_option( 'events_bar_icon_color_choice' )
				&& $section->should_include_setting_css( 'accent_color', 'global_elements' )
			) {
				$color = "<%= global_elements.accent_color %>";
			}

			$css_template .= "
				.tribe-events-c-view-selector__button-icon-caret-svg .tribe-common-c-svgicon__svg-fill {
					 fill: {$color};
				}";

			// Summary view icon.
			$css_template .= "
			{$tribe_events}.tribe-common-c-svgicon .tribe-common-c-svgicon--summary .tribe-common-c-svgicon__svg-stroke tribe-events-c-view-selector__list-item-icon-svg,
			{$tribe_events}.tribe-common-c-svgicon .tribe-common-c-svgicon--summary .tribe-events-c-view-selector__list-item-icon-svg path {
				fill: none;
				stroke: {$color};
			}
		";
		}

		$enabled_views = tribe_get_option( 'tribeEnableViews', [] );

		if ( 3 < count( $enabled_views ) ) {
			if ( $section->should_include_setting_css( 'view_selector_background_color_choice' ) ) {
				$bg_color = 'tec_events_bar.view_selector_background_color';

			} elseif ( $section->should_include_setting_css( 'events_bar_background_color_choice' ) ) {
				$bg_color = 'tec_events_bar.events_bar_background_color';
			}

			if ( ! empty( $bg_color ) ) {
				$css_template .= "
					{$tribe_events} .tribe-events-c-view-selector__content {
						background-color: <%= {$bg_color} %>;
					}
				";
			}
		}

		return $css_template;
	}
}
