<?php
/**
 * View: Summary View Nav Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/summary/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://m.tri.be/1aiy
 *
 * @var string $prev_url The URL to the previous page, if any, or an empty string.
 * @var string $next_url The URL to the next page, if any, or an empty string.
 * @var string $today_url The URL to the today page, if any, or an empty string.
 *
 * @version 5.7.0
 */

?>
<nav class="tribe-events-pro-summary-nav tribe-events-c-nav">
	<ul class="tribe-events-c-nav__list">
		<?php
		if ( ! empty( $prev_url ) ) {
			$this->template( 'summary/nav/prev', [ 'link' => $prev_url ] );
		} else {
			$this->template( 'summary/nav/prev-disabled' );
		}
		?>

		<?php $this->template( 'summary/nav/today' ); ?>

		<?php
		if ( ! empty( $next_url ) ) {
			$this->template( 'summary/nav/next', [ 'link' => $next_url ] );
		} else {
			$this->template( 'summary/nav/next-disabled' );
		}
		?>
	</ul>
</nav>
