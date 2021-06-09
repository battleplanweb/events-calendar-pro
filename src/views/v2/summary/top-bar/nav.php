<?php
/**
 * View: Top Bar - Navigation
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/summary/top-bar/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.7.0
 *
 * @var string $prev_url The URL to the previous page, if any, or an empty string.
 * @var string $next_url The URL to the next page, if any, or an empty string.
 */

?>
<nav class="tribe-events-c-top-bar__nav tribe-common-a11y-hidden">
	<ul class="tribe-events-c-top-bar__nav-list">
		<?php
		if ( empty( $prev_url ) ) {
			$this->template( 'summary/top-bar/nav/prev-disabled' );
		} else {
			$this->template( 'summary/top-bar/nav/prev' );
		}
		?>

		<?php
		if ( empty( $next_url ) ) {
			$this->template( 'summary/top-bar/nav/next-disabled' );
		} else {
			$this->template( 'summary/top-bar/nav/next' );
		}
		?>
	</ul>
</nav>
