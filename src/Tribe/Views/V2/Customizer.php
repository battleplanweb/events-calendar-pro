<?php
/**
 * Handles Views v2 Customizer settings.
 *
 * @since   5.1.1
 *
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Pro\Views\V2;

/**
 * Class Customizer
 *
 * @since   5.1.1
 *
 * @package Tribe\Events\Views\V2
 */
class Customizer {
	/**
	 * Filters the currently registered Customizer sections to add or modify them.
	 *
	 * @since 5.1.1
	 *
	 * @param array<string,array<string,array<string,int|float|string>>> $sections   The registered Customizer sections.
	 * @param \Tribe___Customizer                                        $customizer The Customizer object.
	 *
	 * @return array<string,array<string,array<string,int|float|string>>> The filtered sections.
	 */
	public function filter_sections( array $sections, $customizer ) {
		// TODO Filter the sections.
		return $sections;
	}

	/**
	 * Filters the Global Elements section CSS template to add Views v2 related style templates to it.
	 *
	 * @since 5.1.1
	 *
	 * @param string                      $css_template The CSS template, as produced by the Global Elements.
	 * @param \Tribe__Customizer__Section $section      The Global Elements section.
	 * @param \Tribe__Customizer          $customizer   The current Customizer instance.
	 *
	 * @return string The filtered CSS template.
	 */
	public function filter_global_elements_css_template( $css_template, $section ) {
		$customizer = tribe( 'customizer' );

		// These allow us to continue to _not_ target the shortcode.
		$apply_to_shortcode = apply_filters( 'tribe_customizer_should_print_shortcode_customizer_styles', false );
		$tribe_events = $apply_to_shortcode ? '.tribe-events' : '.tribe-events:not( .tribe-events-view--shortcode )';
		$tribe_common = $apply_to_shortcode ? '.tribe-common' : '.tribe-common:not( .tribe-events-view--shortcode )';

		if ( $customizer->has_option( $section->ID, 'event_title_color' ) ) {
			// Event Title overrides.
			$css_template .= '
				.tribe-events-pro .tribe-events-pro-photo__event-title-link,
				.tribe-events-pro .tribe-events-pro-photo__event-title-link:active,
				.tribe-events-pro .tribe-events-pro-photo__event-title-link:visited,
				.tribe-events-pro .tribe-events-pro-photo__event-title-link:hover,
				.tribe-events-pro .tribe-events-pro-photo__event-title-link:focus,
				.tribe-events-pro .tribe-events-pro-map__event-title,
				.tribe-events-pro .tribe-events-pro-map__event-tooltip-title-link,
				.tribe-events-pro .tribe-events-pro-map__event-tooltip-title-link:active,
				.tribe-events-pro .tribe-events-pro-map__event-tooltip-title-link:visited,
				.tribe-events-pro .tribe-events-pro-map__event-tooltip-title-link:hover,
				.tribe-events-pro .tribe-events-pro-map__event-tooltip-title-link:focus,
				.tribe-events-pro .tribe-events-pro-week-grid__event-title,
				.tribe-events-pro .tribe-events-pro-week-grid__event-tooltip-title-link,
				.tribe-events-pro .tribe-events-pro-week-grid__event-tooltip-title-link:active,
				.tribe-events-pro .tribe-events-pro-week-grid__event-tooltip-title-link:visited,
				.tribe-events-pro .tribe-events-pro-week-grid__event-tooltip-title-link:hover,
				.tribe-events-pro .tribe-events-pro-week-grid__event-tooltip-title-link:focus,
				.tribe-events-pro .tribe-events-pro-week-grid__multiday-event-bar-title,
				.tribe-events-pro .tribe-events-pro-week-mobile-events__event-title-link,
				.tribe-events-pro .tribe-events-pro-week-mobile-events__event-title-link:active,
				.tribe-events-pro .tribe-events-pro-week-mobile-events__event-title-link:visited,
				.tribe-events-pro .tribe-events-pro-week-mobile-events__event-title-link:hover,
				.tribe-events-pro .tribe-events-pro-week-mobile-events__event-title-link:focus,
				.tribe-theme-twentyseventeen .tribe-events-pro .tribe-events-pro-photo__event-title-link:hover,
				.tribe-theme-twentyseventeen .tribe-events-pro .tribe-events-pro-photo__event-title-link:focus,
				.tribe-theme-twentyseventeen .tribe-events-pro .tribe-events-pro-map__event-tooltip-title-link:hover,
				.tribe-theme-twentyseventeen .tribe-events-pro .tribe-events-pro-map__event-tooltip-title-link:focus,
				.tribe-theme-twentyseventeen .tribe-events-pro .tribe-events-pro-week-grid__event-tooltip-title-link:hover,
				.tribe-theme-twentyseventeen .tribe-events-pro .tribe-events-pro-week-grid__event-tooltip-title-link:focus,
				.tribe-theme-twentyseventeen .tribe-events-pro .tribe-events-pro-week-mobile-events__event-title-link:hover,
				.tribe-theme-twentyseventeen .tribe-events-pro .tribe-events-pro-week-mobile-events__event-title-link:focus,
				.tribe-theme-enfold#top .tribe-events-pro .tribe-events-pro-photo__event-title-link,
				.tribe-theme-enfold#top .tribe-events-pro .tribe-events-pro-map__event-tooltip-title-link,
				.tribe-theme-enfold#top .tribe-events-pro .tribe-events-pro-week-grid__event-tooltip-title-link,
				.tribe-theme-enfold#top .tribe-events-pro .tribe-events-pro-week-mobile-events__event-title-link,
				.tribe-events-pro .tribe-events-pro-summary__event-title-link,
				.tribe-events-pro .tribe-events-pro-summary__event-title-link:active,
				.tribe-events-pro .tribe-events-pro-summary__event-title-link:hover,
				.tribe-events-pro .tribe-events-pro-summary__event-title-link:focus {
					color: <%= global_elements.event_title_color %>;
				}
			';

			// Event Title overrides. - Link Underlines.
			$css_template .= '
				.tribe-events-pro .tribe-events-pro-photo__event-title-link:active,
				.tribe-events-pro .tribe-events-pro-photo__event-title-link:hover,
				.tribe-events-pro .tribe-events-pro-photo__event-title-link:focus,
				.tribe-events-pro .tribe-events-pro-map__event-tooltip-title-link:active,
				.tribe-events-pro .tribe-events-pro-map__event-tooltip-title-link:hover,
				.tribe-events-pro .tribe-events-pro-map__event-tooltip-title-link:focus,
				.tribe-events-pro .tribe-events-pro-week-grid__event-tooltip-title-link:active,
				.tribe-events-pro .tribe-events-pro-week-grid__event-tooltip-title-link:hover,
				.tribe-events-pro .tribe-events-pro-week-grid__event-tooltip-title-link:focus,
				.tribe-events-pro .tribe-events-pro-week-mobile-events__event-title-link:active,
				.tribe-events-pro .tribe-events-pro-week-mobile-events__event-title-link:hover,
				.tribe-events-pro .tribe-events-pro-week-mobile-events__event-title-link:focus,
				.tribe-events-pro .tribe-events-pro-summary__event-title-link:active,
				.tribe-events-pro .tribe-events-pro-summary__event-title-link:hover,
				.tribe-events-pro .tribe-events-pro-summary__event-title-link:focus {
					border-color: <%= global_elements.event_title_color %>;
				}
			';
		}

		if ( $customizer->has_option( $section->ID, 'event_date_time_color' ) ) {
			$color = $section->get_option('event_date_time_color');
			$date_color     = new \Tribe__Utils__Color( $color );
			$date_color_rgb = $date_color::hexToRgb( $color );
			$date_css_rgb   = $date_color_rgb['R'] . ',' . $date_color_rgb['G'] . ',' . $date_color_rgb['B'];

			// Event Date Time overrides.
			$css_template .= "
				.tribe-events-pro .tribe-events-pro-photo__event-datetime,
				.tribe-events-pro .tribe-events-pro-map__event-datetime-wrapper,
				.tribe-events-pro .tribe-events-pro-map__event-tooltip-datetime-wrapper,
				.tribe-events-pro .tribe-events-pro-summary__event-datetime-wrapper {
					color: <%= global_elements.event_date_time_color %>;
				}

				.tribe-events-pro .tribe-events-pro-week-grid__event-datetime,
				.tribe-events-pro .tribe-events-pro-week-grid__event-tooltip-datetime,
				.tribe-events-pro .tribe-events-pro-week-mobile-events__event-datetime {
					color: rgba({$date_css_rgb}, .88);
				}
			";
		}

		if ( $customizer->has_option( $section->ID, 'link_color' ) ) {
			// Organizer/Venue Links Overrides.
			$css_template .= '
				.tribe-events-pro .tribe-events-pro-organizer__meta-website-link,
				.tribe-events-pro .tribe-events-pro-organizer__meta-website-link:active,
				.tribe-events-pro .tribe-events-pro-organizer__meta-website-link:visited,
				.tribe-events-pro .tribe-events-pro-organizer__meta-website-link:hover,
				.tribe-events-pro .tribe-events-pro-organizer__meta-website-link:focus,
				.tribe-events-pro .tribe-events-pro-venue__meta-website-link,
				.tribe-events-pro .tribe-events-pro-venue__meta-website-link:active,
				.tribe-events-pro .tribe-events-pro-venue__meta-website-link:visited,
				.tribe-events-pro .tribe-events-pro-venue__meta-website-link:hover,
				.tribe-events-pro .tribe-events-pro-venue__meta-website-link:focus,
				.tribe-theme-twentyseventeen .tribe-events-pro .tribe-events-pro-organizer__meta-website-link:hover,
				.tribe-theme-twentyseventeen .tribe-events-pro .tribe-events-pro-organizer__meta-website-link:focus,
				.tribe-theme-twentyseventeen .tribe-events-pro .tribe-events-pro-venue__meta-website-link:hover,
				.tribe-theme-twentyseventeen .tribe-events-pro .tribe-events-pro-venue__meta-website-link:focus,
				.tribe-theme-enfold .tribe-events-pro .tribe-events-pro-organizer__meta-website-link:active,
				.tribe-theme-enfold .tribe-events-pro .tribe-events-pro-organizer__meta-website-link:visited,
				.tribe-theme-enfold .tribe-events-pro .tribe-events-pro-organizer__meta-website-link:hover,
				.tribe-theme-enfold .tribe-events-pro .tribe-events-pro-organizer__meta-website-link:focus,
				.tribe-theme-enfold .tribe-events-pro .tribe-events-pro-venue__meta-website-link:active,
				.tribe-theme-enfold .tribe-events-pro .tribe-events-pro-venue__meta-website-link:visited,
				.tribe-theme-enfold .tribe-events-pro .tribe-events-pro-venue__meta-website-link:hover,
				.tribe-theme-enfold .tribe-events-pro .tribe-events-pro-venue__meta-website-link:focus {
					color: <%= global_elements.link_color %>;
				}

				.tribe-events-pro .tribe-events-pro-organizer__meta-website-link:active,
				.tribe-events-pro .tribe-events-pro-organizer__meta-website-link:focus,
				.tribe-events-pro .tribe-events-pro-organizer__meta-website-link:hover,
				.tribe-events-pro .tribe-events-pro-venue__meta-website-link:active,
				.tribe-events-pro .tribe-events-pro-venue__meta-website-link:hover,
				.tribe-events-pro .tribe-events-pro-venue__meta-website-link:focus {
					border-color: <%= global_elements.link_color %>;
				}
			';
		}

		if ( $customizer->has_option( $section->ID, 'accent_color' ) ) {
			// Override svg icons color. Widget-specific.
			$css_template .= "
				.tribe-events-widget .tribe-common-c-svgicon {
					color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				.tribe-common .tribe-events-widget .tribe-events-widget-events-list__view-more-link,
				.tribe-common .tribe-events-widget .tribe-events-widget-featured-venue__view-more-link,
				.tribe-common .tribe-events-widget .tribe-events-widget-events-month__view-more-link,
				.tribe-common .tribe-events-widget .tribe-events-widget-events-week__view-more-link {
					color: <%= global_elements.accent_color %>;
				}

				.tribe-common .tribe-events-widget .tribe-events-widget-events-list__view-more-link:active,
				.tribe-common .tribe-events-widget .tribe-events-widget-events-list__view-more-link:focus,
				.tribe-common .tribe-events-widget .tribe-events-widget-events-list__view-more-link:hover,
				.tribe-common .tribe-events-widget .tribe-events-widget-featured-venue__view-more-link:active,
				.tribe-common .tribe-events-widget .tribe-events-widget-featured-venue__view-more-link:focus,
				.tribe-common .tribe-events-widget .tribe-events-widget-featured-venue__view-more-link:hover,
				.tribe-common .tribe-events-widget .tribe-events-widget-events-month__view-more-link:active,
				.tribe-common .tribe-events-widget .tribe-events-widget-events-month__view-more-link:focus,
				.tribe-common .tribe-events-widget .tribe-events-widget-events-month__view-more-link:hover,
				.tribe-common .tribe-events-widget .tribe-events-widget-events-week__view-more-link:active,
				.tribe-common .tribe-events-widget .tribe-events-widget-events-week__view-more-link:focus,
				.tribe-common .tribe-events-widget .tribe-events-widget-events-week__view-more-link:hover {
					border-color: <%= global_elements.accent_color %>;
					color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= '
				.tribe-events-pro .tribe-events-pro-week-day-selector__events-icon {
					background-color: <%= global_elements.accent_color %>;
				}

				.tribe-events-pro .tribe-events-pro-week-day-selector__day--active {
					border-color: <%= global_elements.accent_color %>;
				}

				.tribe-common-c-svgicon.tribe-common-c-svgicon--featured.tribe-events-pro-week-grid__event-datetime-featured-icon-svg {
					color: <%= global_elements.accent_color %>;
				}

				.tribe-events-pro .tribe-events-pro-week-mobile-events__event--featured::before {
					background-color: <%= global_elements.accent_color %>;
				}

				.tribe-events-calendar-month-mobile-events__mobile-event-datetime-recurring-link:active,
				.tribe-events-calendar-month-mobile-events__mobile-event-datetime-recurring-link:focus,
				.tribe-events-calendar-month-mobile-events__mobile-event-datetime-recurring-link:hover,
				.tribe-events-pro-week-mobile-events__event-datetime-recurring-link:active,
				.tribe-events-pro-week-mobile-events__event-datetime-recurring-link:focus,
				.tribe-events-pro-week-mobile-events__event-datetime-recurring-link:hover {
					fill: <%= global_elements.accent_color %>;
					stroke: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= "
				$tribe_common$tribe_events.tribe-events-widget-shortcode.tribe-events-widget-shortcode-events-month .tribe-events-calendar-month__day-cell--mobile:active,
				$tribe_common$tribe_events.tribe-events-widget-shortcode.tribe-events-widget-shortcode-events-month .tribe-events-calendar-month__day-cell--mobile:focus {
					background-color: <%= global_elements.accent_color %>;
				}

				$tribe_common$tribe_events .tribe-events-calendar-month__day-cell--mobile:not(.tribe-events-calendar-month__day-cell--selected):hover,
				$tribe_common$tribe_events .tribe-events-calendar-month__day-cell--mobile:not(.tribe-events-calendar-month__day-cell--selected):focus {
					background-color: <%= global_elements.accent_color %>20;
				}

				$tribe_events .tribe-events-calendar-month__day-cell--selected,
				$tribe_events .tribe-events-calendar-month__day-cell--selected:hover,
				$tribe_events .tribe-events-calendar-month__day-cell--selected:focus,
				.tribe-events.tribe-events-widget  .tribe-events-calendar-month__day-cell--selected,
				.tribe-events.tribe-events-widget  .tribe-events-calendar-month__day-cell--selected:hover,
				.tribe-events.tribe-events-widget  .tribe-events-calendar-month__day-cell--selected:focus {
					background-color: <%= global_elements.accent_color %>;
				}


			";

			$css_template .= "
				.tribe-theme-twentytwentyone $tribe_common button:not(:hover):not(:active):not(.has-background).tribe-events-calendar-month__day-cell--selected,
				.tribe-theme-twentytwentyone .tribe-events-widget button:not(:hover):not(:active):not(.has-background).tribe-events-calendar-month__day-cell--selected {
					background-color: <%= global_elements.accent_color %>;
				}
			";
		}

		if (
			$customizer->has_option( $section->ID, 'background_color_choice' )
			&& 'custom' === $customizer->get_option( [ $section->ID, 'background_color_choice' ] )
			&& $customizer->has_option( $section->ID, 'background_color' )
		) {
			$css_template .= '
				.tribe-events-pro .tribe-events-pro-week-grid__event-link {
					border-color: <%= global_elements.background_color %>;
				}
			';
		}

		return $css_template;
	}

	/**
	 * Filters the Single Event section CSS template to add Views v2 related style templates to it.
	 *
	 * @since 5.1.1
	 *
	 * @param string                      $css_template The CSS template, as produced by the Global Elements.
	 * @param \Tribe__Customizer__Section $section      The Single Event section.
	 * @param \Tribe__Customizer          $customizer   The current Customizer instance.
	 *
	 * @return string The filtered CSS template.
	 */
	public function filter_single_event_css_template( $css_template, $section, $customizer ) {
		return $css_template;
	}
}
