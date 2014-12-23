<?php
/**
 * Single Gallery View Switcher
 * 
 * If we are here, It must be single gallery
 * 
 * It loads gallery/single/{galery-type}.php file if exists or uses default.php
 * e.g for photo, it will try to load gallery/single/photo.php and if not found will use gallery/single/default.php
 * same applies for other types
 * 
 */

$gallery = mpp_get_gallery();

$type = mpp_get_gallery_type( $gallery );

mpp_locate_sub_template(  'gallery/single/', $type .".php", 'default.php' );
