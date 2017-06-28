<?php
/**
 * Various sections for admin enhancement that does not fit at any other place will go here.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add "View User Galleries" link in the Admin User list
 *
 * @param array   $actions action links.
 * @param WP_User $user current row user.
 *
 * @return array
 */
function mpp_admin_user_row_actions( $actions = array(), $user = null ) {

	if ( empty( $user->ID ) ) {
		return $actions;
	}

	$url = mpp_admin()->get_menu_slug();

	$url = add_query_arg( array(
		'author' => $user->ID,
	), $url );

	$actions['view-mpp-gallery'] = sprintf( '<a href="%s" title="%s">%s</a>', $url, _x( 'View User Galleries', 'admin user list action link', 'mediapress' ), __( 'Galleries', 'mediapress' ) );

	return $actions;
}
add_filter( 'user_row_actions', 'mpp_admin_user_row_actions', 10, 2 );
