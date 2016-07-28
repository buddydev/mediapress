<?php

//No direct access to the file 
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}
/**
 * Hooks for activity
 * 
 */

/**
 * Filter activity permalink and make it point to single media if the activity has an associated media
 * 
 * @param string $link
 * @param type $activity
 * @return string
 */
function mpp_filter_activity_permalink( $link, $activity ) {

	$activity_id = $activity->id;
	//get parent activity id
	if ( 'activity_comment' == $activity->type ) {
		$activity_id = $activity->item_id;
		$activity = new BP_Activity_Activity( $activity_id );
	}



	if ( $activity->type != 'mpp_media_upload' ) {
		return $link;
	}

	$context = mpp_activity_get_context( $activity_id );
	$gallery_id = mpp_activity_get_gallery_id( $activity->id );

	if ( ! $gallery_id ) {
		return $link;
	}

	//make sure that gallery exists

	$gallery = mpp_get_gallery( $gallery_id );

	if ( ! $gallery ) {
		return $link;
	}

	//if we are here, It is a gallery/media activity

	$media_id = mpp_activity_get_media_id( $activity_id );

	if ( $media_id && $media = mpp_get_media( $media_id ) ) {
		$link = mpp_get_media_permalink( $media ) . "#activity-{$activity_id}";
	} else {
		$link = mpp_get_gallery_permalink( $gallery ) . "#activity-{$activity_id}";
	}

	return $link;
}

add_filter( 'bp_activity_get_permalink', 'mpp_filter_activity_permalink', 10, 2 );

/**
 * Show the list of attached media in an activity
 * Should we add a link to view gallery too?
 * 
 * @return type
 */
function mpp_activity_inject_attached_media_html() {

	$media_ids = mpp_activity_get_attached_media_ids( bp_get_activity_id() );

	if ( empty( $media_ids ) ) {
		return;
	}

	$activity_id = bp_get_activity_id();

	$gallery_id = mpp_activity_get_gallery_id( $activity_id );

	$gallery = mpp_get_gallery( $gallery_id );

	if ( ! $gallery ) {
		return;
	}

	$type = $gallery->type;

	$view = mpp_get_activity_view( $type );

	$view->activity_display( $media_ids );
}

add_action( 'bp_activity_entry_content', 'mpp_activity_inject_attached_media_html' );

function mpp_activity_inject_media_in_comment_replies() {

	$activity_id = bp_get_activity_id();

	$media_id = mpp_activity_get_media_id( $activity_id );

	if ( empty( $media_id ) ) {
		return;
	}

	$media = mpp_get_media( $media_id );

	if ( ! $media ) {
		return;
	}

//	$gallery_id	= mpp_activity_get_gallery_id( $activity_id );
//	
//	$gallery	= mpp_get_gallery( $gallery_id );
//	
//	if( ! $gallery ) {
//		return ;
//	}

	$slug = $media->type;

	//media-loop-audio/media-loop-video,media-loop-photo, media-loop
	mpp_get_template_part( 'buddypress/activity/entry-comment', $slug );
}

add_action( 'bp_activity_entry_content', 'mpp_activity_inject_media_in_comment_replies' );

//Filter on the Context Gallery creation step to allow creating activity gallery

function mpp_get_activity_wall_gallery( $gallery, $args ) {

	if ( ! isset( $args['context'] ) || $args['context'] !='activity' ) {
		return $gallery;
	}
	//is activity upload enabled for this component[members/groups]?
	if ( ! mpp_is_activity_upload_enabled( $args['component'] ) ) {
		return false;
	}

	//check if a gallery exists for the combination
	$gallery_id = mpp_get_wall_gallery_id( array(
		'component'		=> $args['component'],
		'component_id'	=> $args['component_id'],
		'media_type'	=> $args['type']
	) );


	if ( ! $gallery_id ) {
		//if gallery does not exist, create it
		// 1.  let us make sure that the wall gallery creation activity is never recorded
		add_filter( 'mpp_do_not_record_create_gallery_activity', '__return_true' ); //do not record gallery activity

		$gallery_id = mpp_create_gallery( array(
			'creator_id'	=> $args['user_id'],
			'title'			=> sprintf( _x( 'Wall %s Gallery', 'wall gallery name', 'mediapress' ), $args['type'] ),
			'description'	=> '',
			'status'		=> 'public',
			'component'		=> $args['component'],
			'component_id'	=> $args['component_id'],
			'type'			=> $args['type']
		) );

		//remove the filter we added
		remove_filter( 'mpp_do_not_record_create_gallery_activity', '__return_true' );

		if ( $gallery_id ) {
			//save the wall gallery id
			mpp_update_wall_gallery_id( array(
				'component'		=> $args['component'],
				'component_id'	=> $args['component_id'],
				'media_type'	=> $args['type'],
				'gallery_id'	=> $gallery_id
			) );
		}
	}

	if ( $gallery_id ) {
		$gallery = mpp_get_gallery( $gallery_id );
	}

	return $gallery;

}
add_filter( 'mpp_get_context_gallery', 'mpp_get_activity_wall_gallery', 10, 2 );