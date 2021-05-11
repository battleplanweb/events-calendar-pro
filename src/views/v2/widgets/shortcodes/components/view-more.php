<?php
/**
 * Widget: Shortcode - View More Link
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/widgets/shortcodes/components/view-more.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.5.0
 *
 * @var string $view_more_link  The URL to view all events.
 * @var string $view_more_text  The text for the "view more" link.
 * @var string $view_more_title The widget "view more" link title attribute. Adds some context to the link for screen readers.
 */

if ( empty( $view_more_link ) ) {
	return;
}
?>
<div class="tribe-common-b1 tribe-common-b2--min-medium tribe-events-widget-<?php echo esc_attr( $widget->get_slug() ); ?>__view-more">
	<a
		href="<?php echo esc_url( $view_more_link ); ?>"
		class="tribe-common-anchor-thin tribe-events-widget-<?php echo esc_attr( $widget->get_slug() ); ?>__view-more-link"
		title="<?php echo esc_attr( $view_more_title ); ?>"
	>
		<?php echo esc_html( $view_more_text ); ?>
	</a>
</div>
