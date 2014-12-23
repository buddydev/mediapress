<?php
/* 
 * Meta data for gallery/medias/users
 */


function mpp_add_gallery_meta( $gallery_id, $meta_key, $meta_value, $unique = false ) {
    
    add_post_meta( $gallery_id, $meta_key, $meta_value, $unique) ;
    
}

function mpp_get_gallery_meta( $gallery_id, $meta_key = '', $single = false ){
    if( empty( $meta_key ) )
        $single = false;
   return get_post_meta( $gallery_id, $meta_key, $single );
}


function mpp_update_gallery_meta( $gallery_id, $meta_key, $meta_value  ) {
	
	return update_post_meta( $gallery_id, $meta_key, $meta_value );
}

function mpp_delete_gallery_meta( $gallery_id, $meta_key = '', $meta_value = '' ) {
	
	return delete_post_meta( $gallery_id, $meta_key, $meta_value );
}

