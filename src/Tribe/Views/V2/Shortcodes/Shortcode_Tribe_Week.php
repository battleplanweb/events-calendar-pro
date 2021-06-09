<?php

namespace Tribe\Events\Pro\Views\V2\Shortcodes;

use Tribe\Events\Pro\Views\V2\Widgets\Widget_Week;
use Tribe\Utils\Taxonomy;
use Tribe\Shortcode\Shortcode_Abstract;
use Tribe__Events__Main as TEC;

/**
 * Class Shortcode_Tribe_Week.
 *
 * @since   5.6.0
 *
 * @package Tribe\Events\Pro\Views\V2\Shortcodes
 */
class Shortcode_Tribe_Week extends Shortcode_Abstract {
	/**
	 * {@inheritDoc}
	 */
	protected $slug = 'tribe_this_week';

	/**
	 * {@inheritDoc}
	 */
	protected $default_arguments = [
		'id'                  => null,
		'date'                => null,
		'category'            => [],
		'tag'                 => [],
		'tax-operand'         => 'OR',
		'count'               => 3,
		'hide_weekends'       => false,
		'hide-datepicker'     => true,
		'hide-export'         => true,
		'hide-header'         => true,
		'hide-search'         => true,
		'hide-view-switcher'  => true,
		'layout'              => 'vertical',
		'title'               => '',
		'week_events_per_day' => null,
		'week_offset'         => 0,
	];

	/**
	 * {@inheritDoc}
	 */
	protected $validate_arguments_map = [
		'tax-operand'         => 'strtoupper',
		'week_events_per_day' => 'tribe_null_or_number',
		'week_offset'         => 'tribe_null_or_number',
		'count'               => 'tribe_null_or_number',
		'hide_weekends'       => 'tribe_is_truthy',
	];

	/**
	 * {@inheritDoc}
	 */
	protected $aliased_arguments = [
		'cat'                   => 'category',
		'categories'            => 'category',
		'cats'                  => 'category',
		'tribe_events_category' => 'category',
		'event_tag'             => 'tag',
		'event_tags'            => 'tag',
		'post_tag'              => 'tag',
		'tags'                  => 'tag',
		'limit'                 => 'count',
		'week_events_per_day'   => 'count',
		'start_date'            => 'date',
	];

	/**
	 * {@inheritDoc}
	 *
	 * @since 5.6.0
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
	 * Populate the arguments that are passed to the widget.
	 * Note: after $this->get_arguments(), the keys will be the aliased_arguments *values*
	 *
	 * @since 5.6.0
	 *
	 * @return array<string, mixed> An array of arguments.
	 */
	protected function get_arguments_for_widget() {
		$arguments   = $this->get_arguments();
		$widget_args = [
			'title'                => ! empty( $arguments['title'] ) ? $arguments['title'] : '',
			'date'                 => ! empty( $arguments['date'] ) ? $arguments['date'] : null,
			'count'                => ! empty( $arguments['count'] ) ? absint( $arguments['count'] ) : $this->default_arguments['count'],
			'layout'               => ! empty( $arguments['layout'] ) ? $arguments['layout'] : $this->default_arguments['layout'],
			'week_offset'          => ! empty( $arguments['week_offset'] ) ? (int) $arguments['week_offset'] : $this->default_arguments['week_offset'],
			'hide_weekends'        => ! empty( $arguments['hide_weekends'] ),

			// Taxonomy
			'operand'              => $arguments['tax-operand'],
			'filters'              => [
				'post_tag'    => $arguments['tag'],
				TEC::TAXONOMY => $arguments['category'],
			],
		];

		return $widget_args;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html() {
		ob_start();
		$arguments = $this->get_arguments_for_widget();
		the_widget( Widget_Week::class, $arguments, $arguments );
		$html = ob_get_clean();

		return $html;
	}

}
