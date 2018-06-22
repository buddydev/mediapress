<?php
/**
 * Notifications
 *
 * @package    MediaPress
 * @subpackage modules/buddypress
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Notifications Helper.
 */
class MPP_BP_Notifications_Helper {

	/**
	 * It is not singleton, do not confuse.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Setup hooks.
	 */
	public function setup() {
		add_action( 'bp_template_redirect', array( $this, 'clear_notifications' ) );
		// on gallery/media delete.
		add_action( 'mpp_gallery_deleted', array( $this, 'delete_gallery_notifications' ) );
		add_action( 'mpp_media_deleted', array( $this, 'delete_media_notifications' ) );
	}

	/**
	 * Clear notifications.
	 */
	public function clear_notifications() {

		if ( mpp_is_single_media() ) {
			BP_Notifications_Notification::delete( array(
				'item_id'          => mpp_get_current_media_id(),
				'component_name'   => 'mediapress',
				'component_action' => 'mpp_media_comment',
			) );
		} elseif ( mpp_is_single_gallery() ) {
			BP_Notifications_Notification::delete( array(
				'item_id'          => mpp_get_current_gallery_id(),
				'component_name'   => 'mediapress',
				'component_action' => 'mpp_gallery_comment',
			) );
		}
	}
	/**
	 * Delete notifications for the gallery.
	 *
	 * @param int $gallery_id gallery id.
	 */
	public function delete_gallery_notifications( $gallery_id ) {
		BP_Notifications_Notification::delete( array(
			'item_id'          => $gallery_id,
			'component_name'   => 'mediapress',
			'component_action' => 'mpp_media_comment',
		) );
	}

	/**
	 * Delete media notifications.
	 *
	 * @param int $media_id media id.
	 */
	public function delete_media_notifications( $media_id ) {
		BP_Notifications_Notification::delete( array(
			'item_id'          => $media_id,
			'component_name'   => 'mediapress',
			'component_action' => 'mpp_media_comment',
		) );
	}
}
