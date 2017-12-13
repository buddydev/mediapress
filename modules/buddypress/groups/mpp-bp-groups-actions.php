<?php
/**
 * Group Gallery actions.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Delete Galleries of the group when a group is deleted
 *
 * @param int $group_id group id.
 */
function mpp_delete_galleries_for_group( $group_id ) {

	// Get All Galleries for this group.
	$query = new MPP_Gallery_Query( array( 'component_id' => $group_id, 'fields' => 'ids', 'component' => 'groups' ) );
	$ids   = $query->get_ids();

	// Delete all galleries.
	foreach ( $ids as $gallery_id ) {
		mpp_delete_gallery( $gallery_id );
	}
}

add_action( 'groups_delete_group', 'mpp_delete_galleries_for_group' ); // group id.
