<?php

namespace Tribe\Events\Pro\Views\V2\Shortcodes;

use Tribe\Events\Views\V2\Widgets\Widget_List;
use Tribe\Utils\Taxonomy;
use Tribe\Shortcode\Shortcode_Abstract;
use Tribe__Events__Main as TEC;

/**
 * Class Shortcode_Tribe_Events_List.
 *
 * @since   5.5.0
 *
 * @package Tribe\Events\Pro\Views\V2\Shortcodes
 */
class Shortcode_Tribe_Events_List extends Shortcode_Abstract {
	/**
	 * {@inheritDoc}
	 */
	protected $slug = 'tribe_events_list';

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

		// Optional additional information to include per event
		'venue'              => false,
		'country'            => false,
		'address'            => false,
		'street'             => false,
		'city'               => false,
		'region'             => false,
		'zip'                => false,
		'phone'              => false,
		'cost'               => false,
		'organizer'          => false,
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

	protected function get_arguments_for_widget() {
		$arguments   = $this->get_arguments();
		$widget_args = [
			'title'                => $arguments['title'],
			'limit'                => $arguments['per-page'],
			'no_upcoming_events'   => $arguments['no-upcoming-events'],
			'featured_events_only' => $arguments['featured'],
			'jsonld_enable'        => $arguments['jsonld'],

			// Optional additional information to include per event
			'venue'                => $arguments['venue'],
			'country'              => $arguments['country'],
			'address'              => $arguments['address'],
			'street'               => $arguments['street'],
			'city'                 => $arguments['city'],
			'region'               => $arguments['region'],
			'zip'                  => $arguments['zip'],
			'phone'                => $arguments['phone'],
			'cost'                 => $arguments['cost'],
			'organizer'            => $arguments['organizer'],

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
		the_widget( Widget_List::class, $arguments, $arguments );
		$html = ob_get_clean();

		return $html;
	}

}