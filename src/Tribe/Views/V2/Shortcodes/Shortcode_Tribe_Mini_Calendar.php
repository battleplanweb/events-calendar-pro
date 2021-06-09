<?php

namespace Tribe\Events\Pro\Views\V2\Shortcodes;

use Tribe\Events\Pro\Views\V2\Widgets\Widget_Month;
use Tribe\Utils\Taxonomy;
use Tribe\Shortcode\Shortcode_Abstract;
use Tribe__Events__Main as TEC;

/**
 * Class Shortcode_Tribe_Mini_Calendar.
 *
 * @since   5.5.0
 *
 * @package Tribe\Events\Pro\Views\V2\Shortcodes
 */
class Shortcode_Tribe_Mini_Calendar extends Shortcode_Abstract {
	/**
	 * {@inheritDoc}
	 */
	protected $slug = 'tribe_mini_calendar';

	/**
	 * {@inheritDoc}
	 */
	protected $default_arguments = [
		'id' => null,

		'category'    => [],
		'tag'         => [],
		'tax-operand' => 'OR',

		'title'              => '',
		'per-page'           => 5,
		'jsonld'             => false,
		'featured'           => null,
		'no-upcoming-events' => false,
	];

	/**
	 * {@inheritDoc}
	 */
	protected $validate_arguments_map = [
		'featured'           => 'tribe_null_or_truthy',
		'no-upcoming-events' => 'tribe_null_or_truthy',
		'tax-operand'        => 'strtoupper',
	];

	/**
	 * {@inheritDoc}
	 */
	protected $aliased_arguments = [
		'cat'                   => 'category',
		'cats'                  => 'category',
		'tribe_events_category' => 'category',
		'categories'            => 'category',
		'tags'                  => 'tag',
		'event_tags'            => 'tag',
		'event_tag'             => 'tag',
		'post_tag'              => 'tag',
		'featured_events_only'  => 'featured',
		'events_per_page'       => 'per-page',
		'limit'                 => 'per-page',
		'count'                 => 'per-page',
		'no_upcoming_events'    => 'no-upcoming-events',
	];

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
	 * Populate the arguments that are passed to the widget
	 *
	 * @since 5.6.0
	 *
	 * @return array<string, mixed> An array of arguments.
	 */
	protected function get_arguments_for_widget() {
		$arguments   = $this->get_arguments();
		$widget_args = [
			'title'                => $arguments['title'],
			'count'                => $arguments['per-page'],
			'no_upcoming_events'   => $arguments['no-upcoming-events'],
			'featured_events_only' => $arguments['featured'],
			'jsonld_enable'        => $arguments['jsonld'],

			// Taxonomy
			'filters'              => [
				'post_tag'    => $arguments['tag'],
				TEC::TAXONOMY => $arguments['category'],
			],
			'operand'              => $arguments['tax-operand'],
		];

		return $widget_args;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html() {
		ob_start();
		$arguments = $this->get_arguments_for_widget();
		the_widget( Widget_Month::class, $arguments, $arguments );
		$html = ob_get_clean();

		return $html;
	}

}
