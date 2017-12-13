<?php
/**
 * Group Gallery functions.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Checks if MediaPress is enabled for this group
 *
 * @param int $group_id group id.
 *
 * @return bool
 */
function mpp_group_is_gallery_enabled( $group_id = 0 ) {

	// is groups component enabled?
	if ( mpp_is_enabled( 'groups', $group_id ) ) {
		$is_enabled = true;
	} else {
		$is_enabled = false;
	}

	// if component is not enabled, return false.
	if ( ! $is_enabled ) {
		return false;
	}

	if ( ! $group_id ) {
		$group = groups_get_current_group();

		if ( ! empty( $group ) ) {
			$group_id = $group->id;
		}
	}

	if ( ! $group_id || bp_is_group_create() ) {
		return $is_enabled;
	}

	// check for group settings.
	$is_enabled = groups_get_groupmeta( $group_id, '_mpp_is_enabled', true );
	// if current group has no preference set, fallback to global preference
	// this global preference can be set by visting Dashboard->MediaPress->Settings->Groups.
	if ( empty( $is_enabled ) ) {
		$is_enabled = mpp_get_option( 'enable_group_galleries_default', 'yes' );
	}

	return $is_enabled == 'yes';
}

/**
 * Sets MediaPress features as enabled/disabled for the given gallery
 *
 * @param int    $group_id group id.
 * @param string $enabled yes|no.
 *
 * @return boolean
 */
function mpp_group_set_gallery_state( $group_id = false, $enabled = 'yes' ) {

	if ( ! $group_id ) {
		$group_id = bp_get_group_id( groups_get_current_group() );
	}

	if ( ! $group_id ) {
		return false;
	}

	// default settings from gloabl.
	$is_enabled = groups_update_groupmeta( $group_id, '_mpp_is_enabled', $enabled );

	return $is_enabled;
}

// for group wall galleries.
/**
 * Returns wall photo gallery id
 *
 * @see mpp_get_wall_gallery_id()
 *
 * @param int $group_id group id.
 *
 * @return int
 */
function mpp_get_groups_wall_photo_gallery_id( $group_id ) {
	return (int) groups_get_groupmeta( $group_id, '_mpp_wall_photo_gallery_id', true );
}

/**
 * Returns wall Video gallery id
 *
 * @param int $group_id group id.
 *
 * @return int
 */
function mpp_get_groups_wall_video_gallery_id( $group_id ) {
	return (int) groups_get_groupmeta( $group_id, '_mpp_wall_video_gallery_id', true );
}

/**
 * Get wall audio gallery id
 *
 * @see mpp_get_wall_gallery_id()
 *
 * @param int $group_id group id.
 *
 * @return int
 */
function mpp_get_groups_wall_audio_gallery_id( $group_id ) {
	return (int) groups_get_groupmeta( $group_id, '_mpp_wall_audio_gallery_id', true );
}

/**
 * Returns the document wall gallery id for group
 *
 * @param int $group_id group id.
 *
 * @return int
 */
function mpp_get_groups_wall_doc_gallery_id( $group_id ) {
	return (int) groups_get_groupmeta( $group_id, '_mpp_wall_doc_gallery_id', true );
}

/**
 * Updates wall photo gallery id
 *
 * @see mpp_update_wall_gallery_id()
 *
 * @param int $group_id group id.
 *
 * @return int
 */
function mpp_update_groups_wall_photo_gallery_id( $group_id, $gallery_id ) {
	return groups_update_groupmeta( $group_id, '_mpp_wall_photo_gallery_id', $gallery_id );
}

/**
 * Updates wall Video gallery id
 *
 * @see mpp_update_wall_gallery_id()
 *
 * @param int $group_id group id.
 *
 * @return int
 */
function mpp_update_groups_wall_video_gallery_id( $group_id, $gallery_id ) {
	return groups_update_groupmeta( $group_id, '_mpp_wall_video_gallery_id', $gallery_id );
}

/**
 * Updates wall audio gallery id
 *
 * @see mpp_update_wall_gallery_id()
 *
 * @param int $group_id group id.
 * @param int $gallery_id gallery id.
 *
 * @return int
 */
function mpp_update_groups_wall_audio_gallery_id( $group_id, $gallery_id ) {
	return groups_update_groupmeta( $group_id, '_mpp_wall_audio_gallery_id', $gallery_id );
}

/**
 * Updates wall document(doc) gallery id
 *
 * @see mpp_update_wall_gallery_id()
 *
 * @param int $group_id group id.
 * @param int $gallery_id gallery id.
 *
 * @return int
 */
function mpp_update_groups_wall_doc_gallery_id( $group_id, $gallery_id ) {
	return groups_update_groupmeta( $group_id, '_mpp_wall_doc_gallery_id', $gallery_id );
}

/**
 * Deletes Group wall gallery
 *
 * @param int    $group_id group id.
 * @param string $type gallery type.
 * @param int    $gallery_id gallery id.
 *
 * @return int
 */
function mpp_delete_groups_wall_gallery_id( $group_id, $type, $gallery_id ) {
	$key = "_mpp_wall_{$type}_gallery_id";

	return groups_delete_groupmeta( $group_id, $key, $gallery_id );
}

/**
 * Checks if current user can access group's gallery/media or not
 *
 * @see mpp_group_init() for use
 *
 * @param string $component_type 'groups'.
 * @param int    $component_id current group id.
 * @param int    $user_id the user for which we are checking the access.
 *
 * @return boolean true if allowed, false if not allowed access
 */
function mpp_check_groups_access( $component_type, $component_id, $user_id = null ) {

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$allow = false;

	if ( is_super_admin() || bp_is_active( 'groups' ) && ( groups_is_user_member( $user_id, $component_id ) ) ) {
		$allow = true;
	}

	return apply_filters( 'mpp_check_groups_access', $allow, $component_type, $component_id, $user_id );
}

/**
 * Checks if 'My Galleries' filter is enabled for the groups component
 *
 * @return boolean
 */
function mpp_group_is_my_galleries_enabled() {
	return mpp_get_option( 'groups_enable_my_galleries' );
}

//a little bit deviation in naming here
//but we will be moving more groups constructs tot his naming convention
/**
 * Group gallery url.
 *
 * @return string
 */
function mpp_group_get_user_galleries_url() {

	$component    = 'groups';
	$component_id = groups_get_current_group()->id;

	return user_trailingslashit( trailingslashit( mpp_get_gallery_base_url( $component, $component_id ) ) . 'my-gallery' );
}
