<?php
/**
 * Create gallery shortcode
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Handle shortcode for create gallery form [mpp-create-gallery]
 *
 * @param array  $atts allowed atts.
 * @param string $content n/a.
 *
 * @return null|string
 */
function mpp_shortcode_create_gallery( $atts = array(), $content = null ) {

	$defaults = array();
	// do not show it to the non logged user.
	if ( ! is_user_logged_in() ) {
		return $content;
	}

	ob_start();

	mpp_get_template( 'shortcodes/create-gallery.php' );

	$content = ob_get_clean();

	return $content;
}
add_shortcode( 'mpp-create-gallery', 'mpp_shortcode_create_gallery' );
