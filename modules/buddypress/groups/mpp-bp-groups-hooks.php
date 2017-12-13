<?php
/**
 * BuddyPress Group hooks for MediaPress.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set the group id as the component_id for the default current component
 *
 * @see mpp_get_current_component_id()
 *
 * @param int $component_id component id.
 *
 * @return int
 */
function mpp_current_component_id_for_groups( $component_id ) {

	if ( bp_is_group() ) {
		$group = groups_get_current_group();

		return $group->id;
	}

	return $component_id;
}

// won't work in ajax mode.
add_filter( 'mpp_get_current_component_id', 'mpp_current_component_id_for_groups' );

/**
 * Set current component_type to groups if we are on groups page
 *
 * @see mpp_get_current_component()
 *
 * @param string $component component type.
 *
 * @return string
 */
function mpp_current_component_type_for_groups( $component ) {

	if ( bp_is_active( 'groups' ) && bp_is_group() ) {
		return buddypress()->groups->id;
	}

	return $component;
}

add_filter( 'mpp_get_current_component', 'mpp_current_component_type_for_groups' );

/**
 * Filter activity action for group uploads.
 *
 * @param string               $action activity action.
 * @param BP_Activity_Activity $activity activity object.
 * @param int                  $media_id media id.
 * @param array                $media_ids attached media ids.
 * @param MPP_Gallery          $gallery gallery object.
 *
 * @return string
 */
function mpp_group_form_uploaded_activity_action( $action, $activity, $media_id, $media_ids, $gallery ) {

	if ( $gallery->component != 'groups' ) {
		return $action;
	}

	$media_count = count( $media_ids );

	$type = $gallery->type;

	// we need the type plural in case of multi.
	// photo vs photos etc.
	$type = _n( mpp_get_type_singular_name( $type ), mpp_get_type_plural_name( $type ), $media_count );

	$group_id = $activity->item_id;

	$group = new BP_Groups_Group( $group_id );

	$group_link = sprintf( "<a href='%s'>%s</a>", bp_get_group_permalink( $group ), bp_get_group_name( $group ) );
	$action     = sprintf( __( '%s uploaded %d new %s to %s', 'mediapress' ), mpp_get_user_link( $activity->user_id ), $media_count, $type, $group_link );

	return $action;
}

add_filter( 'mpp_activity_action_media_upload', 'mpp_group_form_uploaded_activity_action', 11, 5 );

/**
 * Group Nav.
 */
function mp_group_nav() {

	if ( ! bp_is_group() ) {
		return;
	}

	$component    = 'groups';
	$component_id = groups_get_current_group()->id;

	if ( mpp_user_can_create_gallery( $component, $component_id ) ) {
		echo sprintf( "<li><a href='%s'>%s</a></li>", mpp_get_gallery_base_url( $component, $component_id ), __( 'All Galleries', 'mediapress' ) );

		if ( mpp_group_is_my_galleries_enabled() ) {
			echo sprintf( "<li><a href='%s'>%s</a></li>", mpp_group_get_user_galleries_url(), __( 'My Galleries', 'mediapress' ) );
		}

		echo sprintf( "<li><a href='%s'>%s</a></li>", mpp_get_gallery_create_url( $component, $component_id ), __( 'Create Gallery', 'mediapress' ) );
	}
}

add_action( 'mpp_group_nav', 'mp_group_nav', 0 );

/**
 * Filter permissions check.
 *
 * @param bool            $can can user perform the action.
 * @param int|MPP_Gallery $gallery gallery id or object.
 * @param int             $user_id user id.
 *
 * @return bool
 */
function mpp_group_check_gallery_permission( $can, $gallery, $user_id ) {

	$gallery = mpp_get_gallery( $gallery );

	// if it is not a group gallery, we  should not be worried.
	if ( $gallery->component != 'groups' ) {
		return $can;
	}

	$group_id = $gallery->component_id;

	if ( groups_is_user_admin( $user_id, $group_id ) || groups_is_user_mod( $user_id, $group_id ) ) {
		$can = true;
	}

	return $can;
}

// check for edit permission.
add_filter( 'mpp_user_can_edit_gallery', 'mpp_group_check_gallery_permission', 10, 3 );
// check for delete permission.
add_filter( 'mpp_user_can_delete_gallery', 'mpp_group_check_gallery_permission', 10, 3 );

/**
 * Can the user perform media action?
 *
 * @param bool            $can can user perform the action.
 * @param int|MPP_Media   $media media id or object.
 * @param int|MPP_Gallery $gallery gallery id or object.
 * @param int             $user_id user id.
 *
 * @return bool
 */
function mpp_group_check_media_permission( $can, $media, $gallery, $user_id ) {

	$media = mpp_get_media( $media );

	// if it is not a group gallery, we  should not be worried.
	if ( $media->component != 'groups' ) {
		return $can;
	}

	$group_id = $media->component_id;

	if ( groups_is_user_admin( $user_id, $group_id ) || groups_is_user_mod( $user_id, $group_id ) ) {
		$can = true;
	}

	return $can;
}

// filter.
add_filter( 'mpp_user_can_edit_media', 'mpp_group_check_media_permission', 10, 4 );
add_filter( 'mpp_user_can_delete_media', 'mpp_group_check_media_permission', 10, 4 );

/**
 * Filter Main MPP Gallery Query to add support for "my-gallery" view on group component
 *
 * @param array $args Gallery query args.
 *
 * @return array
 */
function mpp_group_filter_gallery_query( $args ) {
	if ( mpp_group_is_my_galleries_enabled() && bp_is_active( 'groups' ) && bp_is_group() && mpp_is_enabled( mpp_get_current_component(), mpp_get_current_component_id() ) ) {
		// check if the current av0 is 'my-gallery'.
		if ( is_user_logged_in() && bp_is_action_variable( 'my-gallery', 0 ) ) {
			$args['user_id'] = bp_loggedin_user_id();
		}
	}

	return $args;
}

add_filter( 'mpp_main_gallery_query_args', 'mpp_group_filter_gallery_query' );
