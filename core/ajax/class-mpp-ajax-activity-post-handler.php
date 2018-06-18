<?php
/**
 * MediaPress Media Only Activity Post Handler.
 *
 * Helps with posting empty activity for media only activity.
 *
 * @package    MediaPress
 * @subpackage Core/Ajax
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit( 0 );

/**
 * Currently allows posting empty activity(which only has media).
 */
class MPP_Ajax_Activity_Post_Handler {

	/**
	 * Is booted?
	 *
	 * @var bool
	 */
	private static $booted = null;

	/**
	 * Boot the handler.
	 */
	public static function boot() {

		if ( self::$booted ) {
			return;
		}

		self::$booted = true;

		$self = new self();
		$self->setup();
	}

	/**
	 * Setup actions.
	 */
	public function setup() {
		// Posting of activity containing our media.
		add_action( 'wp_ajax_post_update', array( $this, 'activity_post_update' ), 0 );
	}

	/**
	 * Handle posting of empty activity
	 */
	public function activity_post_update() {
		$bp      = buddypress();
		$content = $_POST['content'];

		if ( ! empty( $content ) || empty( $_POST['mpp-attached-media'] ) ) {
			// Let the normal work flow work as expected.
			return;
		}

		// Bail if not a POST action.
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		// Check the nonce.
		check_admin_referer( 'post_update', '_wpnonce_post_update' );

		if ( ! is_user_logged_in() ) {
			exit( '-1' );
		}

		$content     = "";
		$activity_id = 0;
		$item_id     = 0;
		$object      = '';


		// Try to get the item id from posted variables.
		if ( ! empty( $_POST['item_id'] ) ) {
			$item_id = (int) $_POST['item_id'];
		}

		// Try to get the object from posted variables.
		if ( ! empty( $_POST['object'] ) ) {
			$object = sanitize_key( $_POST['object'] );

			// If the object is not set and we're in a group, set the item id and the object.
		} elseif ( bp_is_group() ) {
			$item_id = bp_get_current_group_id();
			$object  = 'groups';
		}


		if ( ( ! $object || 'user' === $object ) && bp_is_active( 'activity' ) ) {
			$activity_id = $this->_post_update( array( 'content' => $content, 'error_type' => 'wp_error' ) );

		} elseif ( 'groups' === $object ) {
			if ( $item_id && bp_is_active( 'groups' ) ) {
				$activity_id = $this->_groups_post_update( array(
					'content'    => $content,
					'group_id'   => $item_id,
					'error_type' => 'wp_error'
				) );
			}

		} else {

			/** This filter is documented in bp-activity/bp-activity-actions.php */
			$activity_id = apply_filters( 'bp_activity_custom_update', false, $object, $item_id, $_POST['content'] );
		}

		if ( false === $activity_id ) {
			exit( '-1<div id="message" class="error bp-ajax-message"><p>' . __( 'There was a problem posting your update. Please try again.', 'mediapress' ) . '</p></div>' );
		} elseif ( is_wp_error( $activity_id ) && $activity_id->get_error_code() ) {
			exit( '-1<div id="message" class="error bp-ajax-message"><p>' . $activity_id->get_error_message() . '</p></div>' );
		}

		$last_recorded = ! empty( $_POST['since'] ) ? date( 'Y-m-d H:i:s', intval( $_POST['since'] ) ) : 0;
		if ( $last_recorded ) {
			$activity_args               = array( 'since' => $last_recorded );
			$bp->activity->last_recorded = $last_recorded;
			add_filter( 'bp_get_activity_css_class', 'bp_activity_newest_class', 10, 1 );
		} else {
			$activity_args = array( 'include' => $activity_id );
		}

		ob_start();

		if ( bp_has_activities( $activity_args ) ) {
			while ( bp_activities() ) {
				bp_the_activity();
				bp_get_template_part( 'activity/entry' );
			}
		}

		if ( ! empty( $last_recorded ) ) {
			remove_filter( 'bp_get_activity_css_class', 'bp_activity_newest_class', 10 );
		}

		$is_private  = false;
		$content = ob_get_clean();

		if ( function_exists( 'bp_nouveau_ajax_post_update' ) ) {
			// bp_nouveau compat..
			wp_send_json_success( array(
				'id'           => $activity_id,
				'message'      => esc_html__( 'Update posted.', 'mediapress' ) . ' ' . sprintf( '<a href="%s" class="just-posted">%s</a>', esc_url( bp_activity_get_permalink( $activity_id ) ), esc_html__( 'View activity.', 'mediapress' ) ),
				'activity'     => $content,

				/**
				 * Filters whether or not an AJAX post update is private.
				 * @param string/bool $is_private Privacy status for the update.
				 */
				'is_private'   => apply_filters( 'bp_nouveau_ajax_post_update_is_private', $is_private ),
				'is_directory' => bp_is_activity_directory(),
			) );
		} else {
			echo $content;
		}
		exit;
	}

	/**
	 * Based on bp_activity_post_update()
	 * Allows empty activity update when media is attached.
	 * It is a temporary solution, going to ask to include such functionality in core BP.
	 *
	 * @param array $args
	 *
	 * @return bool|int
	 */
	private function _post_update( $args = array() ) {
		return mpp_activity_post_update( $args );
	}

	/**
	 * Based on groups_post_update() to allow empty activity when media is attached.
	 *
	 * @param array $args activity args.
	 *
	 * @return bool
	 */
	private function _groups_post_update( $args = array() ) {
		return mpp_activity_post_group_update( $args );
	}

}
