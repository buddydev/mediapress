<?php

/**
 * Default menu item visibility check callback
 *
 * @param array       $item menu item.
 * @param MPP_Gallery $gallery Gallery object.
 *
 * @return boolean
 */
function mpp_is_menu_item_visible( $item, $gallery ) {

	$can_see    = false;
	$user_id    = get_current_user_id();
	$gallery_id = $gallery->id;

	// if the current user is super admin or owner of the gallery, they can see everything.
	if ( is_super_admin() || $user_id == $gallery->user_id ) {
		return apply_filters( 'mpp_is_menu_item_visible', true, $item, $gallery );
	}

	switch ( $item['action'] ) {

		case 'view':
			$can_see = mpp_user_can_list_media( $gallery->id, $user_id );
			break;

		case 'manage':
		case 'edit':
		case 'reorder':
			$can_see = mpp_user_can_edit_gallery( $gallery_id, $user_id );
			break;

		case 'upload':
		case 'add':
			$can_see = mpp_user_can_upload( $gallery->component, $gallery->component_id, $gallery );
			break;

		case 'settings' :
			break;

		case 'delete':
			break;
	}

	/*if ( ! $can_see ) {

		// check if action is protected, If it is not protected, anyone can see.
		if ( ! in_array( $item['action'], array( 'view', 'manage', 'edit', 'reorder', 'upload' ) ) ) {
			$can_see = true;
		}
	}*/

	// should we provide a filter here, I am sure people will misuse it.
	return apply_filters( 'mpp_is_menu_item_visible', $can_see, $item, $gallery );
}

/**
 * Add a new menu item to the current gallery menu
 *
 * @param array $args item args.
 *
 * @return null
 */
function mpp_add_gallery_nav_item( $args ) {
	return mediapress()->get_menu( 'gallery' )->add_item( $args );
}

/**
 * Remove a nav item from the current gallery nav
 *
 * @param string $slug menu slug.
 *
 * @return null
 */
function mpp_remove_gallery_nav_item( $slug ) {
	return mediapress()->get_menu( 'gallery' )->remove_item( $slug );
}

/**
 * Render gallery menu
 *
 * @param MPP_Gallery $gallery gallery object.
 * @param string      $selected selected menu item.
 */
function mpp_gallery_admin_menu( $gallery, $selected = '' ) {

	$gallery = mpp_get_gallery( $gallery );

	mediapress()->get_menu( 'gallery' )->render( $gallery, $selected );
}

/**
 * Add a new nav item in the media nav
 *
 * @param array $args menu item args.
 *
 * @return boolean
 */
function mpp_add_media_nav_item( $args ) {

	return mediapress()->get_menu( 'media' )->add_item( $args );
}

/**
 * Remove a nav item from the media nav
 *
 * @param array $args array of args.
 *
 * @return null
 */
function mpp_remove_media_nav_item( $args ) {

	return mediapress()->get_menu( 'media' )->remove_item( $args );
}

/**
 * Render media admin tabs
 *
 * @param MPP_Media $media media object.
 */
function mpp_media_menu( $media, $action = '' ) {

	$media = mpp_get_media( $media );
	mediapress()->get_menu( 'media' )->render( $media );
}
