<?php
/**
 * View: Top Bar - Navigation
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/widgets/shortcodes/components/month-nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.6.0
 *
 * @var string $prev_url     The URL to the previous page, if any, or an empty string.
 * @var string $next_url     The URL to the next page, if any, or an empty string.
 * @var string $request_date The displayed date (month).
 */
?>
<nav class="tribe-events-c-top-bar__nav">
	<ul class="tribe-events-c-top-bar__nav-list">
		<?php
		if ( ! empty( $prev_url ) ) {
			$this->template( 'components/top-bar/nav/prev' );
		} else {
			$this->template( 'components/top-bar/nav/prev-disabled' );
		}
		?>

		<li class="tribe-events-c-top-bar__nav-list-date"><?php echo $request_date; ?></li>

		<?php
		if ( ! empty( $next_url ) ) {
			$this->template( 'components/top-bar/nav/next' );
		} else {
			$this->template( 'components/top-bar/nav/next-disabled' );
		}
		?>
	</ul>
</nav>
