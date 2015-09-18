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

$gallery = mpp_get_current_gallery();

mpp_get_template_part( 'gallery/views/loops/loop', $gallery->type );
?>