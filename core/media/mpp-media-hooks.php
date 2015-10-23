<?php
//filter attachment permalink

add_filter( 'attachment_link', 'mpp_media_filter_permalink', 10, 2 );

function mpp_media_filter_permalink( $link, $post_id ) {
	
	if( ! mpp_is_valid_media( $post_id ) ) {
		return $link;
			
	}
	
	$media = mpp_get_media( $post_id );
	
	if( $media->component != 'sitewide' ) {
		return $link;
	}
	
	//in case of sitewide gallery, the permalink is like
	
	$gallery_permalink = mpp_get_gallery_permalink( $media->gallery_id );
	return user_trailingslashit( untrailingslashit( $gallery_permalink ) . '/media/' . $media->slug );
}
//just the formatting
add_filter( 'bp_get_media_description', 'wpautop' );
add_filter( 'bp_get_media_description', 'make_clickable' );