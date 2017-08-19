<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Add a layer to avoid dependency on BuddyPress
 */
/**
 * Get the URl to user profile/posts
 *
 * @param int $user_id user id.
 *
 * @return string
 */
function mpp_get_user_url( $user_id ) {

	if ( function_exists( 'bp_core_get_user_domain' ) ) {
		return bp_core_get_user_domain( $user_id );
	}

	return get_author_posts_url( $user_id );
}

/**
 * Get user display name
 *
 * @param int $user_id user id.
 *
 * @return string user display name
 */
function mpp_get_user_display_name( $user_id ) {

	if ( function_exists( 'bp_core_get_user_displayname' ) ) {
		return bp_core_get_user_displayname( $user_id );
	}

	$user = get_user_by( 'id', $user_id );

	if ( ! $user ) {
		return '';
	}

	$display_name = $user->display_name;
	if ( ! $display_name && ( $user->first_name || $user->last_name ) ) {
		$display_name = trim( $user->first_name . ' ' . $user->last_name );
	}
	// if it is still not set, set it to user_login.
	if ( ! $display_name ) {
		$display_name = $user->user_login;
	}

	return $display_name;
}

/**
 * Get user email.
 *
 * @param int $user_id numeric user id.
 *
 * @return string
 */
function mpp_get_user_email( $user_id ) {

	$user = get_user_by( 'id', $user_id );

	if ( ! $user ) {
		return '';
	}

	return $user->user_email;
}

/**
 * Get link to user page.
 *
 * @param int  $user_id user id.
 * @param bool $no_anchor do not use anchor tags.
 * @param bool $just_link simply return url.
 *
 * @return string
 */
function mpp_get_user_link( $user_id, $no_anchor = false, $just_link = false ) {

	if ( function_exists( 'bp_core_get_userlink' ) ) {
		return bp_core_get_userlink( $user_id, $no_anchor, $just_link );
	}

	$display_name = mpp_get_user_display_name( $user_id );

	if ( empty( $display_name ) ) {
		return '';
	}

	if ( ! empty( $no_anchor ) ) {
		return $display_name;
	}

	if ( ! $url = mpp_get_user_url( $user_id ) ) {
		return '';
	}

	if ( ! empty( $just_link ) ) {
		return $url;
	}


	return apply_filters( 'mpp_get_user_link', '<a href="' . $url . '" title="' . $display_name . '">' . $display_name . '</a>', $user_id );
}

/**
 * Get gallery count for the user.
 *
 * It is not cached, so if you plan to use it, we suggest you implement your own caching mechanism for the count.
 *
 * @param int   $user_id user id.
 * @param array $args array of allowed args.
 *
 * @return int
 */
function mpp_get_user_gallery_count( $user_id, $args = array() ) {
	$args['user_id'] = $user_id;
	return mpp_get_gallery_count( $args );
}
/**
 * Get the total number of gallery created by the user for the given component
 *
 * It is better suitable as the count is cached.
 *
 * @param int    $user_id user id.
 * @param string $component component name.
 *
 * @return int
 */
function mpp_get_user_gallery_count_by_component( $user_id, $component = ''  ) {

	$key = 'mpp-u-' . $user_id;
	$args = array();

	if ( $component ) {
		$key .= '-' . $component;
		$args['component'] = $component;
	}

	$key .= '-gallery';

	// mpp-u-1-gallery-count
	// mpp-u-1-groups-gallery-count.
	$key .= '-count';

	$count = get_user_meta( $user_id, $key,  true );
	if ( ! $count ) {
		$args['user_id'] = $user_id;

		$count = mpp_get_gallery_count( $args );

		update_user_meta( $user_id, $key, $count );
	}

	return $count;
}

/**
 * Get media count for the user.
 *
 * It is not cached, so if you plan to use it, we suggest you implement your own caching mechanism for the count.
 *
 * @param int   $user_id user id.
 * @param array $args array of allowed args.
 *
 * @return int
 */
function mpp_get_user_media_count( $user_id, $args = array() ) {
	$args['user_id'] = $user_id;
	return mpp_get_media_count( $args );
}

/**
 * Get the total number of uploaded media for user based on context.
 *
 * Stores/retrieves from the transients mpp-user-media-$user_id-$component-$component_id
 * e.g mpp-user-media-1-groups-1 for user uploaded media in group 1
 *
 * @param int    $user_id user id.
 * @param string $component component name.
 *
 * @return int
 */
function mpp_get_user_media_count_by_component( $user_id, $component = '' ) {

	$key = 'mpp-u-' . $user_id;

	$args = array();

	if ( $component ) {
		$key .= '-' . $component;
		$args['component'] = $component;
	}

	$key .= '-media';

	// mpp-u-1-media-count
	// mpp-u-1-groups-media-count.
	$key .= '-count';

	$count = get_user_meta( $user_id, $key, true );

	if ( ! $count ) {
		$args['user_id'] = $user_id;
		$count = mpp_get_media_count( $args );

		update_user_meta( $user_id, $key, $count );
	}

	return $count;
}
