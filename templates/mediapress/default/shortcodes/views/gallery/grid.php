<?php
/**
 * Single Gallery Grid View
 * 
 */
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}
?>
<?php

$args = mpp_shortcode_get_media_data( 'shortcode_args' );
$gallery = mpp_get_gallery( $args['gallery_id'] );

mpp_get_template_part( 'shortcodes/gallery/loops/loop', $gallery->type );
