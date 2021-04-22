<?php

/**
 * Class Tribe__Events__Pro__Editor__Recurrence__Queue_Status
 *
 * @since 4.5
 */
class Tribe__Events__Pro__Editor__Recurrence__Queue_Status {
	/**
	 * Store if we can start the recurrence queue or not.
	 *
	 * @since 5.5.0
	 *
	 * @var string
	 */
	public static $meta_editor_save_race_condition_tries = '_tribe_events_editor_save_race_condition_tries';

	/**
	 * The Queue_Realtime constructor method.
	 */
	public function hook() {
		if ( ! class_exists( 'Tribe__Events__Ajax__Operations' ) ) {
			return;
		}
		add_action( 'wp_ajax_gutenberg_events_pro_recurrence_queue', array( $this, 'ajax' ) );
	}

	/**
	 * Fetches the max amount of tries we will do before ignoring race conditions from meta box save in blocks editor.
	 *
	 * @since 5.5.0
	 *
	 * @param string|int $post_id To which parent post we are trying to regenerate recurrence.
	 *
	 * @return int
	 */
	public static function get_max_tries( $post_id ) {
		/**
		 * Allows the modification of the maximum amount of tries to save while waiting for the sidebar race condition
		 * to finish.
		 *
		 * @since 5.5.0
		 *
		 * @param int        $max_tries How many tries we need will do before we ignore race conditions.
		 * @param string|int $post_id   To which parent post we are trying to regenerate recurrence.
		 */
		return (int) apply_filters( 'tribe_events_pro_editor_recurrence_saver_queue_max_tries', 10, $post_id );
	}


	/**
	 * When we save the metabox we can set the number of tries to the max, since we reached the race condition problem.
	 *
	 * @since 5.5.0
	 *
	 * @param int|string $post_id Post ID of the event being saved.
	 * @param WP_Post    $post    Which post object we are dealing with.
	 *
	 */
	public static function action_set_max_editor_save_race_condition_tries( $post_id, $post ) {
		$is_event = tribe_is_event( $post_id );
		if ( ! $is_event ) {
			return;
		}

		$original_post     = wp_is_post_revision( $post );
		$is_event_revision = $original_post && tribe_is_event( $original_post );

		if ( $is_event_revision ) {
			return;
		}

		/** @var Tribe__Editor $editor */
		$editor = tribe( 'editor' );
		/** @var Tribe__Events__Editor__Compatibility $compatibility */
		$compatibility        = tribe( 'events.editor.compatibility' );
		$has_gutenberg_editor = $compatibility->is_blocks_editor_toggled_on() && ! $editor->is_classic_plugin_active();

		// Save only the meta that does not have blocks when the Gutenberg editor is present.
		if ( ! tribe( 'tec.gutenberg' )->should_display() || ! $has_gutenberg_editor ) {
			return;
		}

		update_post_meta( $post_id, static::$meta_editor_save_race_condition_tries, static::get_max_tries( $post_id ) );
	}

	/**
	 * Method used to reply back into the ajax admin request
	 *
	 * @since 4.5
	 */
	public function ajax() {
		$post_id = (int) tribe_get_request_var( 'post_id', 0 );
		$nonce   = sanitize_text_field( tribe_get_request_var( 'recurrence_queue_status_nonce', '' ) );

		$should_start_meta = (int) get_post_meta( $post_id, static::$meta_editor_save_race_condition_tries, true );
		$max_tries         = static::get_max_tries( $post_id );

		// If we have non-existent meta we can proceed too.
		$should_start =
			! metadata_exists( 'post', $post_id, static::$meta_editor_save_race_condition_tries )
			|| $should_start_meta >= $max_tries;

		if ( ! $should_start ) {
			$start_dates = tribe_get_recurrence_start_dates( $post_id );
			update_post_meta( $post_id, static::$meta_editor_save_race_condition_tries, $should_start_meta + 1 );

			$response = [
				'done'            => false,
				'items_created'   => count( $start_dates ),
				'last_created_at' => end( $start_dates ),
				'percentage'      => 0,
			];
			exit( $this->response( $response ) );
		}

		$response = false;
		if (
			tribe_is_recurring_event( $post_id )
			&& wp_verify_nonce( $nonce, $this->get_ajax_action() )
		) {
			$response = $this->process( $post_id );
		}
		exit( $this->response( $response ) );
	}

	/**
	 * Function used to trigger que recurrence Queue
	 *
	 * @since 4.5
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public function process( $post_id ) {
		$queue = new Tribe__Events__Pro__Recurrence__Queue( $post_id );

		$is_empty = $queue->is_empty();
		if ( ! $is_empty ) {
			$queue_processor = Tribe__Events__Pro__Main::instance()->queue_processor;
			if ( null !== $queue_processor ) {
				$queue_processor->process_batch( $post_id );
			}
		}

		$start_dates = tribe_get_recurrence_start_dates( $post_id );

		return array(
			'done'            => $is_empty,
			'items_created'   => count( $start_dates ),
			'last_created_at' => end( $start_dates ),
			'percentage'      => $is_empty ? 100 : $queue->progress_percentage(),
		);
	}

	/**
	 * Return the nonce for the ajax action
	 *
	 * @since 4.5
	 *
	 * @return string
	 */
	public function get_ajax_nonce() {
		return wp_create_nonce( $this->get_ajax_action() );
	}

	/**
	 * Name of the action used on the page to create the nonce
	 *
	 * @since 4.5
	 *
	 * @return string
	 */
	public function get_ajax_action() {
		return 'gutenberg_events_pro_recurrence_queue_status' . get_current_user_id();
	}

	/**
	 * Exit and return the response as json encoded string
	 *
	 * @since 4.5
	 *
	 * @param $data
	 *
	 * @return string
	 */
	public function response( $data ) {
		$encoded = json_encode( $data );

		return false === $encoded ? '' : $encoded;
	}
}
