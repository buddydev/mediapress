<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

add_shortcode( 'mpp-gallery-create', 'mpp_shortcode_gallery_create' );

function mpp_shortcode_gallery_create( $atts = array(), $content = null ) {
	
	$defaults = array();
	//do not show it to the non logged user
	if( ! is_user_logged_in() ) {
		return $content;
	}
	
	//mpp_user_can_create_gallery($component, $component_id );
	
	ob_start();
	mpp_get_template( 'shortcodes/gallery-create.php' );
	
	$content = ob_get_clean();
	
	return $content;
}

