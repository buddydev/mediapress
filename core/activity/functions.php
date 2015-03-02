<?php
/**
 * a wrapper for bp_has_activity
 * Chck if the activities for a media exist
 * 
 * @param type $args
 * @return type
 */
function mpp_media_has_activity( $args = null ) {
	
	$default = array(
		'media_id' => mpp_get_current_media_id(),
		
	);
	
	$args = wp_parse_args( $args, $default );
	extract( $args );
	
	$args = array(
		'meta_query'=> array(
			array(
				'key'	=> '_mpp_media_id',
				'value' => $media_id,

			)
		),
		'type'		=> 'mpp_media_upload'

	);
	return bp_has_activities(  $args ) ;
}
/**
 * A wrapper for bp_has_activity
 * checks if the gallery has associated activity
 * 
 * @param type $args
 * @return type
 */
function mpp_gallery_has_activity( $args = null ) {
	
	$default = array(
		'gallery_id' => mpp_get_current_gallery_id()
		
	);
	
	$args = wp_parse_args( $args, $default );
	
	extract( $args );
	
	$args = array(
		'meta_query'=> array(
			array(
				'key'	=> '_mpp_gallery_id',
				'value' => $gallery_id

			)
		),
		'type'		=> 'mpp_media_upload'

	);
	return bp_has_activities(  $args ) ;
}
/**
 * Get the attached gallery id for an activity
 * 
 * @param type $activity_id
 * @return type
 * not used
 */
function mpp_activity_get_attached_gallery_id( $activity_id ) {
    
    return bp_activity_get_meta( $activity_id, '_mpp_attached_gallery_id', true );
}
/**
 * Updated attached gallery id for an activity
 * 
 * @param type $activity_id
 * @param type $gallery_id
 * @return type
 * 
 * Not Used
 */
function mpp_activity_update_attached_gallery_id( $activity_id, $gallery_id ) {
    
    return  bp_activity_update_meta( $activity_id, '_mpp_attached_gallery_id', $gallery_id );
    
}
/**
 * delete attached gallery
 * 
 * Not used
 */
function mpp_activity_delete_attached_gallery_id( $activity_id ) {
    
    return  bp_activity_delete_meta( $activity_id, '_mpp_attached_gallery_id' );
    
}

/**
 * Get attached media Ids or an acitivyt 
 * @param type $activity_id
 * @return array of media ids
 */
function mpp_activity_get_attached_media_ids( $activity_id ) {
    
    return bp_activity_get_meta( $activity_id, '_mpp_attached_media_ids', false );
    
}
/**
 * Update Attached list of media ids for an activity
 * 
 * @param type $activity_id
 * @param type $media_ids
 * @return type
 */
function mpp_activity_update_attached_media_ids( $activity_id, $media_ids ) {
	
   foreach( $media_ids as $media_id )
		bp_activity_add_meta( $activity_id, '_mpp_attached_media_ids', $media_id );
   
   return $media_ids;

}
/**
 * Delete Attached list of media ids for an activity
 * 
 */
function mpp_activity_delete_attached_media_ids( $activity_id ){
   
    return bp_activity_delete_meta( $activity_id, '_mpp_attached_media_ids' );

}

/**
 * Delete all the metas where the key and value matches given pair
 * @param type $media_id
 * @return type
 */
function mpp_media_delete_attached_activity_media_id( $media_id ) {
	
	return mpp_delete_meta_by_key_value( '_mpp_attached_media_ids', $media_id );
}

/**
 * Get the activity Id for media/Gallery
 */
/**
 * Check if activity has associated media
 * 
 * @param int $activity_id
 * @return mixed false if no attachment else array of attachment ids
 */
function mpp_activity_has_media( $activity_id = false ) {
    
    if( ! $activity_id )
        $activity_id = bp_get_activity_id ();
    
    return mpp_activity_get_attached_media_ids( $activity_id );
}

/**
 * Get associated activity Id for Media
 * 
 * @param type $media_id
 * @return type
 */
function mpp_media_get_activity_id( $media_id ){
    
    return mpp_get_media_meta( $media_id, '_mpp_activity_id', true );
}
/**
 * 
 * @param type $media_id
 * @param type $activity_id
 * @return type
 */
function mpp_media_update_activity_id( $media_id, $activity_id ){
    
    return mpp_update_media_meta( $media_id, '_mpp_activity_id', $activity_id );
}

/**
 * Check if Media has an activity associated
 * 
 * @param type $media_id
 * @return type
 */
function mpp_media_has_activity_entries( $media_id ){
    
    return mpp_media_get_activity_id( $media_id );
}
/**
 * Get the associated activity for the gallery
 * 
 * For profile gallery, we will have multiple activity ids, we need to handle that in some other way
 * 
 * @param type $gallery_id
 * @return type
 */
function mpp_gallery_get_activity_id( $gallery_id ){
    
    return mpp_get_gallery_meta( $gallery_id, '_mpp_activity_id', true );
}

/**
 * Update the associated activity id for gallery
 * 
 * @param type $gallery_id
 * @param type $activity_id
 * @return type
 */
function mpp_gallery_update_activity_id( $gallery_id, $activity_id ){
    
    return mpp_update_gallery_meta( $gallery_id, '_mpp_activity_id', $activity_id );
}
/**
 * Check if Gallery has associated activity
 * 
 * @param type $gallery_id
 * @return type
 */
function mpp_gallery_has_activity_entries( $gallery_id ){
    
    return mpp_gallery_get_activity_id( $gallery_id );
}

/**
 * Add 1st comment on media
 */
function mpp_activity_new_activity( $media_id ){
    
}

function mpp_activity_get_associated_comment_id( $activity_id ){
    
    return bp_activity_get_meta( $activity_id, '_mpp_comment_id',  true );
    
}

function mpp_activity_update_associated_comment_id( $activity_id, $value ){
    
    return bp_activity_update_meta( $activity_id, '_mpp_comment_id', $value );
    
}

function mpp_activity_delete_associated_comment_id( $activity_id ){
    
    return bp_activity_delete_meta( $activity_id, '_mpp_comment_id' );
    
}

/***
 * For single Media comment
 */

function mpp_activity_get_gallery_id( $activity_id ){
	
	return bp_activity_get_meta( $activity_id, '_mpp_gallery_id', true );
}

function mpp_activity_update_gallery_id( $activity_id, $gallery_id ){
	
	return bp_activity_update_meta( $activity_id, '_mpp_gallery_id', $gallery_id );
}

function mpp_activity_delete_gallery_id( $activity_id ){
	
	bp_activity_delete_meta( $activity_id, '_mpp_gallery_id' );
}

function mpp_activity_get_media_id( $activity_id ){
	
	return bp_activity_get_meta( $activity_id, '_mpp_media_id', true );
}

function mpp_activity_update_media_id( $activity_id, $media_id ){
	
	return bp_activity_update_meta( $activity_id, '_mpp_media_id', $media_id );
}

function mpp_activity_delete_media_id( $activity_id ){
	
	return bp_activity_delete_meta( $activity_id, '_mpp_media_id' );
}

/**
 * When an activity is saved, check if there exists a media attachment cookie,
 *  if yes, mark it as non orphaned and store in the activity meta
 * 
 */

function mpp_activity_mark_attached_media( $activity_id  ) {
    
    if( ! is_user_logged_in() )
        return ;
    
    if( empty( $_COOKIE['_mpp_activity_attached_media_ids'] ) )
        return ;//don't do anything
    
    //let us process
    $media_ids = $_COOKIE['_mpp_activity_attached_media_ids'];
    $media_ids = explode( ',',  $media_ids ); //make an array
    
    foreach( $media_ids as $media_id ){
        //should we verify the logged in user & owner of media is same?
        
        mpp_delete_media_meta( $media_id, '_mpp_is_orphan');//or should we delete the key?
        
        
    }
    mpp_activity_update_attached_media_ids( $activity_id, $media_ids );
     
     
     
    //store the media ids in the activity meta
   
    
    
    //also add the activity to gallery & gallery to activity link
    $media = mpp_get_media( $media_id );
    
    if( $media->gallery_id ) {
        
        mpp_activity_update_gallery_id( $activity_id, $media->gallery_id );
        
    }
    
    //also update this activity and set its action to be mpp_media_upload
    $activity = new BP_Activity_Activity( $activity_id );
   // $activity->component = buddypress()->mediapress->id;
    $activity->type = 'mpp_media_upload';
    $activity->save();
    
    mpp_activity_clear_attached_media_cookie();//clear cookies
    //reset the cookie
}

//add new activity on new gallery
function mpp_gallery_new_gallery_activity( $gallery_id, $user_id = null ) {
	
	
}

function mpp_gallery_new_media_activity( $gallery_id, $media_ids, $user_id ){
	
	$gallery = mpp_get_gallery( $gallery_id );
	
	
}

/**
 * New Media activity
 * 
 * @param type $media_ids
 * @todo remove in future as we don't need it 
 */
function mpp_media_new_activity( $args = array() ) {
	
	$default = array(
		'media_ids'		=> false,
		'gallery_id'	=> 0,
		'media_id'		=> 0,
		'content'		=> '',
		'component'		=> '',
		'component_id'	=> 0,
	);

	$args = wp_parse_args( $args, $default );
	
	extract( $args );
	
	if( empty( $media_ids ) )
		$media_ids = array();
	
	if( !empty( $media_id ) )
		array_push ( $media_ids, $media_id );
	
	if( $gallery_id ) {
		
		$gallery = mpp_get_gallery( $gallery_id );
		
		$component = $gallery->component;
		$component_id = $gallery->component_id;
	}else{
		
		if( empty( $media_ids ) )
			return;
		
		$media_id = $media_ids[0];
		
		$media = mpp_get_media( $media_id );
		
		$component = $media->component;
		
		$component_id = $media->component_id;
		
		
	}
	
	if( ! $component || ! $component_id )
		return;
	
	//now create a new activity
	

	$activity_id = 0;
		
	if ( empty( $component ) && bp_is_active( 'activity' ) ) {
		
		$activity_id = bp_activity_post_update( array( 'content' => $content ) );

	} elseif ( $component == 'groups' ) {
		if ( bp_is_active( 'activity' ) && bp_is_active( 'groups' ) )
			$activity_id = groups_post_update( array( 'content' => $content, 'group_id' => $component_id ) );

	} else {
		$activity_id = apply_filters( 'bp_activity_custom_update', $component, $component_id, $content );
	}
	
	if( ! $activity_id )
		return false;

	//if activity was published
	//we need to update it
	$activity = new BP_Activity_Activity( $activity_id );
   // $activity->component = buddypress()->mediapress->id;
    $activity->type = 'mpp_media_upload';
    $activity->save();
	
	if( ! empty( $media_ids ) )
		mpp_activity_update_attached_media_ids( $activity_id, $media_ids );
	
	mpp_activity_update_gallery_id( $activity_id, $gallery_id );
}