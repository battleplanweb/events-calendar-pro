<?php
/**
 * Renders the week view
 *
 * @since   4.7.5
 * @package Tribe\Events\PRO\Views\V2\Views
 */

namespace Tribe\Events\Pro\Views\V2\Views\Widgets;

use Tribe\Events\Pro\Views\V2\Views\Week_View as Original_Week_View;

/**
 * Class Week_View
 *
 * @since   4.7.5
 *
 * @package Tribe\Events\PRO\Views\V2\Views
 */
class Week_View extends Original_Week_View {

	/**
	 * Slug for this view
	 *
	 * @since 4.7.5
	 *
	 * @var string
	 */
	protected $slug = 'widget-week';

	/**
	 * Visibility for this view.
	 *
	 * @since 4.7.5
	 * @since 4.7.9 Made the property static.
	 *
	 * @var bool
	 */
	protected static $publicly_visible = false;

		/**
	 * {@inheritDoc}
	 */
	protected function setup_template_vars() {
		$template_vars = parent::setup_template_vars();

		// For the purposes of widget we don't ever display this since it will break.
		$template_vars['display_events_bar']       = false;

		return $template_vars;
	}
}
