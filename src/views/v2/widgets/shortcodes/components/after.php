<?php
/**
 * Events Pro Shortcode Component Before Widget.
 * This is the template for the output of the event month widget.
 * All the items are turned on and off through the widget admin.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/widgets/shortcodes/components/after.php
 *
 * @link    https://event.is/1aiy
 *
 * @version 5.5.0
 *
 * @var Template        $this              Instance of template engine used to render this view.
 * @var Widget_Abstract $widget            Instance of the widget that invoked this view.
 *
 * @see     tribe_classes()
 *
 */

use Tribe__Utils__Array as Arr;
use \Tribe__Template as Template;
use \Tribe\Events\Views\V2\Widgets\Widget_Abstract;

$sidebar_arguments = $widget->get_sidebar_arguments();
$after_widget      = Arr::get( $sidebar_arguments, 'after_widget', '' );

echo $after_widget;
