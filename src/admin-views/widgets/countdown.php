<?php
/**
 * Admin View: Countdown Widget
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/widgets/countdown.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://m.tri.be/1aiy
 *
 * @var Widget_Abstract $widget_obj   An instance of the widget abstract.
 * @var array<array,mixed>    $admin_fields An array of admin fields to display in the widget form.
 *
 * @version TBD
 */

foreach ( $admin_fields as $field ) {
	$this->template( "widgets/components/{$field['type']}", $field );
}
