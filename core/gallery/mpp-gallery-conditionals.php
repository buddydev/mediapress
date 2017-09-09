<?php

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is it a mixed gallery?
 *
 * Check if mixed gallery is allowed and then checks if this is a mixed gallery
 *
 * @param MPP_Gallery $gallery Gallery object.
 *
 * @return boolean
 */
function mpp_is_mixed_gallery( $gallery ) {

	if ( mpp_get_option( 'allow_mixed_gallery' ) && 'mixed' === $gallery->type ) {
		return true;
	}

	return false;
}

/**
 * Are we on Gallery Directory or User Gallery Pages?
 *
 * @return boolean
 */
function mpp_is_gallery_component() {

	if ( function_exists( 'bp_is_current_component' ) && bp_is_current_component( 'mediapress' ) ) {
		return true;
	}

	return false;
}

/**
 * Are we on Sitewide gallery page?
 *
 * @return boolean
 */
function mpp_is_sitewide_gallery_component() {

	if ( is_singular( mpp_get_gallery_post_type() ) && mpp_is_sitewide_gallery( get_queried_object_id() ) ) {
		return true;
	}

	return false;
}

/**
 * Are we on component gallery
 *
 * Is gallery associated to a component(groups/events etc)
 *
 * @return boolean
 */
function mpp_is_component_gallery() {

	$is_gallery = false;

	if ( function_exists( 'bp_is_current_action' ) && bp_is_current_action( MPP_GALLERY_SLUG ) && mpp_is_enabled( bp_current_component(), mpp_get_current_component_id() ) ) {
		$is_gallery = true;
	}

	return apply_filters( 'mpp_is_component_gallery', $is_gallery );
}

/**
 * Is it gallery directory?
 *
 * @return boolean
 *
 * @todo handle the single gallery case for root gallery
 */
function mpp_is_gallery_directory() {

	if ( mpp_is_gallery_component() && ! bp_is_user() ) {
		return true;
	}

	return false;
}

/**
 * Is User Gallery screen
 */

/**
 * Is it User Gallery component?
 *
 * @return boolean
 */
function mpp_is_user_gallery_component() {

	if ( function_exists( 'bp_is_user' ) && bp_is_user() && mpp_is_gallery_component() ) {
		return true;
	}

	return false;
}

/**
 * Is it groups gallery component?
 *
 * @return boolean
 */
function mpp_is_group_gallery_component() {

	if ( function_exists( 'bp_is_group' ) && bp_is_group() && bp_is_current_action( MPP_GALLERY_SLUG ) ) {
		return true;
	}

	return false;
}

/**
 * Is my group Galleries Screen for Users?
 *
 * @return boolean
 */
function mpp_is_my_group_galleries() {

	if ( function_exists( 'bp_is_user_groups' ) && bp_is_user_groups() && bp_is_current_action( MPP_GALLERY_SLUG ) ) {
		return true;
	}

	return false;
}

/**
 * Is the current action create gallery?
 *
 * @return boolean
 */
function mpp_is_gallery_create() {
	return mediapress()->is_action( 'create' );
}

/**
 * Is it gallery management page?
 *
 * @return bool
 */
function mpp_is_gallery_management() {
	return mediapress()->is_editing( 'gallery' ) && mediapress()->is_action( 'manage' );
}

/**
 * Is it the add media page of single gallery?
 *
 * @return bool
 */
function mpp_is_gallery_add_media() {
	return mpp_is_gallery_management() && mediapress()->is_edit_action( 'add' );
}


/**
 *
 * Is this media edit page for the gallery
 *
 * @return boolean
 */
function mpp_is_gallery_edit_media() {

	return mpp_is_gallery_management() && mediapress()->is_edit_action( 'edit' );
}

/**
 * Is it the reorder media page of single gallery?
 *
 * @return bool
 */
function mpp_is_gallery_reorder_media() {
	return mpp_is_gallery_management() && mediapress()->is_edit_action( 'reorder' );
}

/**
 * Is it the delete gallery page for single gallery?
 *
 * @return bool
 */
function mpp_is_gallery_delete() {
	return mpp_is_gallery_management() && mediapress()->is_edit_action( 'delete' );
}

/**
 * Is it single gallery settings page.
 *
 * @return bool
 */
function mpp_is_gallery_settings() {
	return mpp_is_gallery_management() && mediapress()->is_edit_action( 'settings' );
}

/**
 * Is it cover upload page/action.
 *
 * @return bool
 */
function mpp_is_gallery_cover_upload() {
	return mpp_is_gallery_management() && mediapress()->is_edit_action( 'cover' );
}

/**
 * Is it the cover delete action?
 *
 * @return bool
 */
function mpp_is_gallery_cover_delete() {
	return mpp_is_gallery_management() && mediapress()->is_edit_action( 'delete-cover' );
}
/**
 * @todo update
 */
function mpp_is_add_from_web() {
}

/**
 * Unused.
 *
 * @return bool
 */
function mpp_is_gallery_upload() {
	return mediapress()->is_edit_action( 'upload' );
}

/**
 * Is it the gallery home page(galleries-list) for the given component.
 *
 * @return bool
 */
function mpp_is_gallery_home() {
	return mediapress()->is_gallery_home;
}

/**
 * Get current MediaPress main action, view|create/manage/upload
 *
 * @return string
 */
function mpp_get_current_action() {
	return mediapress()->get_action(); // is it create/manage/upload?
}

/**
 * Get the current editing action.
 *
 * @return string
 */
function mpp_get_current_edit_action() {
	return mediapress()->get_edit_action(); // is it create/manage/upload?
}

/**
 * Should we show the upload quota?
 *
 * @return boolean
 */
function mpp_show_upload_quota() {
	return mpp_get_option( 'show_upload_quota' );
}

/**
 * Check if the given gallery has some media
 *
 * @param int $gallery_id gallery id.
 *
 * @return int
 */
function mpp_gallery_has_media( $gallery_id ) {
	// check the gallery has media.
	return mpp_gallery_get_media_count( $gallery_id );
}

/**
 * Is activity upload enabled?
 *
 * @param  string $component component name.
 *
 * @return boolean
 */
function mpp_is_activity_upload_enabled( $component = null ) {
	// default should be yes.
	return apply_filters( 'mpp_is_activity_upload_enabled', mpp_get_option( 'activity_upload', true ), $component );
}

/**
 * Is gallery directory section enabled?
 *
 * @return boolean
 */
function mpp_has_gallery_directory() {
	return mpp_get_option( 'has_gallery_directory' );
}

/**
 * Is media directory section enabled?
 *
 * @return boolean
 */
function mpp_has_media_directory() {
	return mpp_get_option( 'has_media_directory' );
}

/**
 * Check if the given media(attachment) is cover image?
 *
 * @param int $gallery_id gallery id.
 * @param int $image_id the given media id.
 *
 * @return boolean
 */
function mpp_is_cover_image( $gallery_id, $image_id ) {

	$gallery = mpp_get_gallery( $gallery_id );

	if ( mpp_get_gallery_cover_id( $gallery->id ) === $image_id ) {
		return true;
	}

	return false;
}

/**
 * Does the gallery or type supports playlist
 *
 * @param int    $gallery_id gallery id.
 * @param string $type gallery type.
 *
 * @return boolean
 */
function mpp_gallery_supports_playlist( $gallery_id = null, $type = null ) {


	if ( $gallery_id ) {
		$gallery = mpp_get_gallery( $gallery_id );
		$type    = $gallery->type;
	}

	// currently hardcoded types, in future, we will allow registering support.
	if ( ! $type ) {
		return false;
	}

	if ( 'audio' !== $type && 'video' !== $type ) {
		return false;
	}

	// let us not worry about individual gallery preference yet.
	if ( mpp_get_option( 'enable_' . $type . '_playlist' ) ) {
		// enable_audio_playlist,
		// enable_video_playlist.
		return true;
	}

	return false;
}
