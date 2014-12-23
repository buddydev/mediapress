<?php

/**
 * Filter get_permalink for mediapress gallery post type( mpp-gallery)
 * aand make it like site.com/members/username/mediapressslug/gallery-name or site.com/{component-page}/{single-component}/mediapress-slug/gallery-name
 * It allows us to get the permalink to gallery by using the_permalink/get_permalink functions
 */


function mpp_filter_gallery_permalink( $permalink, $post, $leavename, $sample ) {

	
	if ( mpp_get_gallery_post_type() != $post->post_type )
		return $permalink;

	$gallery = mpp_get_gallery( $post );

	
	$slug = $gallery->slug;
	
	
	
	$base_url = mpp_get_gallery_base_url( $gallery->component, $gallery->component_id );

	
	return apply_filters( 'mpp_get_gallery_permalink', $base_url . '/' . $slug, $gallery );
}

add_filter( 'post_type_link', 'mpp_filter_gallery_permalink', 10, 4 );