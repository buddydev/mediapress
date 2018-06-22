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
 * Add notification.
 *
 * @param int    $user_id user id.
 * @param string $action_type action.
 * @param int    $mpp_id media or gallery id.
 * @param int    $other_user_id other user id who commented.
 *
 * @return bool|int
 */
function mpp_send_bp_notification( $user_id, $action_type, $mpp_id, $other_user_id ) {

	if ( ! $user_id || ! $action_type || ! $mpp_id ) {
		return false;
	}

	if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'notifications' ) ) {
		return false;
	}

	return bp_notifications_add_notification( array(
		'user_id'           => $user_id,
		'item_id'           => $mpp_id,
		'secondary_item_id' => $other_user_id,
		'component_name'    => 'mediapress',
		'component_action'  => $action_type,
	) );
}

/**
 * Notification formatting callback for MediaPress.
 *
 * @param string $action Action type.
 * @param int    $item_id media or gallery id.
 * @param int    $secondary_item_id The secondary item ID.
 * @param int    $total_items The total number of notifications.
 * @param string $format 'string' or 'array'.
 *
 * @return array|string
 */
function mpp_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

	$link = '';
	switch ( $action ) {
		case 'mpp_media_comment':
			$media = mpp_get_media( $item_id );
			$link  = trailingslashit( mpp_get_media_permalink( $media ) );

			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( '%1$d people commented on your %2$s', 'mediapress' ), (int) $total_items, strtolower( mpp_get_type_singular_name( $media->type ) ) );
			} else {
				$text = sprintf( __( '%1$s commented on your %2$s', 'mediapress' ), bp_core_get_user_displayname( $secondary_item_id ), strtolower( mpp_get_type_singular_name( $media->type ) ) );
			}

			break;

		case 'mpp_gallery_comment':
			$gallery = mpp_get_gallery( $item_id );
			$link    = mpp_get_gallery_permalink( $gallery );

			// Set up the string and the filter.
			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( '%d people commented on your gallery', 'mediapress' ), (int) $total_items );
			} else {
				$text = sprintf( __( '%s commented on your gallery', 'mediapress' ), bp_core_get_user_displayname( $secondary_item_id ) );
			}

			break;
	}

	// Return either an HTML link or an array, depending on the requested format.
	if ( 'string' == $format ) {
		$return = '<a href="' . esc_url( $link ) . '">' . esc_html( $text ) . '</a>';
	} else {
		/** This filter is documented in bp-friends/bp-friends-notifications.php */
		$return = array(
			'link' => $link,
			'text' => $text,
		);
	}

	do_action( 'mpp_format_notifications', $action, $item_id, $secondary_item_id, $total_items, $return );

	return $return;
}

