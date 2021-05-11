<?php
/**
 * Events Pro Shortcode Month Widget V2
 * This is the template for the output of the event month widget.
 * All the items are turned on and off through the widget admin.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/widgets/shortcodes/events-month.php
 *
 * @link    https://event.is/1aiy
 *
 * @version 5.5.0
 *
 * @var Template     $this              Instance of template engine used to render this view.
 * @var Widget_Month $widget            Instance of the widget that invoked this view.
 * @var string       $shortcode_string  String to be passed to `do_shortcode`.
 * @var array        $container_classes Array classes applied to the widget container.
 *
 * @see tribe_classes()
 *
 */

use \Tribe\Events\Pro\Views\V2\Widgets\Widget_Month;
use \Tribe__Template as Template;
?>

<?php $this->template( 'components/before' ); ?>
<div <?php tribe_classes( $container_classes ); ?>>

	<?php $this->template( 'components/title' ); ?>

	<?php echo do_shortcode( $shortcode_string ); ?>

</div>
<?php $this->template( 'components/after' ); ?>
