<?php
/**
 * Various sections for admin enhancement that does not fit at anoy other place will go here
 */

//add "Galleries" link in the Admin User list
function mpp_admin_user_row_actions( $actions = array(), $user = null ) {

	if ( empty( $user->ID ) ) {
		return $actions;
	}

	$url = mpp_admin()->get_menu_slug();

	$url = add_query_arg( array(
		'author' => $user->ID
	), $url );

	$actions['view-mpp-gallery'] = sprintf( '<a href="%s" title="%s">%s</a>', $url , __( 'View User Galleries', 'mediapress'), __( 'Galleries', 'mediapress') );
	return $actions;
}

add_filter( 'user_row_actions', 'mpp_admin_user_row_actions', 10, 2 );