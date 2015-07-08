<?php


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
function mpp_activity_delete_attached_media_ids( $activity_id ) {
   
    return bp_activity_delete_meta( $activity_id, '_mpp_attached_media_ids' );

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




function mpp_activity_get_associated_comment_id( $activity_id ) {
    
    return bp_activity_get_meta( $activity_id, '_mpp_comment_id',  true );
    
}

function mpp_activity_update_associated_comment_id( $activity_id, $value ) {
    
    return bp_activity_update_meta( $activity_id, '_mpp_comment_id', $value );
    
}

function mpp_activity_delete_associated_comment_id( $activity_id ){
    
    return bp_activity_delete_meta( $activity_id, '_mpp_comment_id' );
    
}

/***
 * For single Media comment
 */
/**
 * Get the id of the gallery associated with this activity
 * _mpp_gallery_id meta key is added for activity uploads as well as single gallery activity/comment
 * 
 * If it is a single gallery activity(comments on single gallery page), there won't exist the meta _mpp_media_id
 * 
 * This meta is added to activity when an activity has uploads from activity page or a comment is made on the single gallery page(not the single media).
 * The only way to differentiate these two types of activity is to check for the presence of the _mpp_attached_media_ids meta
 * 
 *  If a new activity is created by posting on single media page(comments), It does not have _mpp_gallery_id associated with it
 * 
 * @param type $activity_id
 * @return int gallery id
 */
function mpp_activity_get_gallery_id( $activity_id ) {
	
	return bp_activity_get_meta( $activity_id, '_mpp_gallery_id', true );
}
/**
 * Update the gallery id associated with this activity
 * 
 * @param int $activity_id
 * @param int $gallery_id
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function mpp_activity_update_gallery_id( $activity_id, $gallery_id ) {
	
	return bp_activity_update_meta( $activity_id, '_mpp_gallery_id', $gallery_id );
}
/**
 * Deete gallery id associated with this activity
 * 
 * @param int $activity_id
 */
function mpp_activity_delete_gallery_id( $activity_id ) {
	
	bp_activity_delete_meta( $activity_id, '_mpp_gallery_id' );
}
/**
 * Get the id of the media associated with this activity
 * It is used to differentiate single media activity from the activity upload
 * for activity uploaded media please see _mpp_attached_media_ids
 * 
 * 
 * Please note, we do not consider activity uploads as media activity(We consider activity uploads as gallery activity instead), see _mpp_attached_media_ids for the same
 * 
 * It is for single media activity comment
 * 
 * @param type $activity_id
 * @return type
 */
function mpp_activity_get_media_id( $activity_id ) {
	
	return bp_activity_get_meta( $activity_id, '_mpp_media_id', true );
}

function mpp_activity_update_media_id( $activity_id, $media_id ) {
	
	return bp_activity_update_meta( $activity_id, '_mpp_media_id', $media_id );
}

function mpp_activity_delete_media_id( $activity_id ) {
	
	return bp_activity_delete_meta( $activity_id, '_mpp_media_id' );
}


/**
 * Storing/retrieving mpp activity type(create_gallery|update_gallery|media_upload) etc in activity meta
 */
/**
 * Get the MediaPress activity type
 * When checking for a single activity, the $activity->type is always set to 'mpp_media_upload',
 * We use this meta to determine the type of gallery activity
 * 
 * @param type $activity_id
 * @return type
 */
function mpp_activity_get_activity_type( $activity_id ) {
	
	return bp_activity_get_meta( $activity_id, '_mpp_activity_type', true );
}
/**
 * Update MediaPress activity type
 * 
 * @param int $activity_id
 * @param string $type type of activity( create_gallery|update_gallery|media_upload)
 * @return type
 */
function mpp_activity_update_activity_type( $activity_id, $type ) {
	
	return bp_activity_update_meta( $activity_id, '_mpp_activity_type', $type );
}

/**
 * Delet MediaPress activity type
 * @param type $activity_id
 * @return type
 */
function mpp_activity_delete_activity_type( $activity_id ) {
	
	return bp_activity_delete_meta( $activity_id, '_mpp_activity_type' );
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
    
    foreach( $media_ids as $media_id ) {
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


/**
 * Record Media Activity
 * 
 * It does not actually records activity, simply simulates the activity update and rest are done by the actions.php functions
 * 
 * It will be removed in future for a better record_activity method
 * @param type $args
 * @return boolean
 */
function mpp_record_activity( $args = null ) {
	
	//if activity module is not active, why bother
	if( ! bp_is_active( 'activity' ) ) {
		return false; 
	}

	$default = array(
		'gallery_id'	=> 0,
		'media_id'		=> 0,
		'media_ids'		=> null,//single id or an array of ids
		'action'		=> '', 
		'content'		=> '',
		'type'			=> '',//type of activity  'create_gallery, update_gallery, media_upload etc'
		'component'		=> mpp_get_current_component(),
		'component_id'	=> mpp_get_current_component_id(),
		'user_id'		=> get_current_user_id(),
		'status'		=> '',
	
	);
	
	$args = wp_parse_args( $args, $default );
	
	
	//atleast a gallery id or a media id should be given
	if(  ( ! $args['gallery_id'] && ! $args['media_id'] ) || ! mpp_is_active_component( $args['component'] ) || ! $args['component_id'] ) {
		return false;
	}
	
	$gallery_id = absint( $args['gallery_id'] );
	$media_id = absint( $args['media_id'] );
	
	$type = $args['type'];//should we validate type too?
	
	$hide_sitewide = 0;
	
	if( $args['status'] != 'public' ) {
		$hide_sitewide = 1;
	}
	
	$media_ids = $args['media_ids'];
	
	if( ! empty( $media_ids ) && ! is_array( $media_ids ) ) {
		$media_ids = explode( ',', $media_ids );
	}
	
	$component = $args['component'];
	
	if( $component == buddypress()->members->id ) {
		
		$component = buddypress()->activity->id; //for user gallery updates, let it be simple activity , do not set the component to 'members'
	}
	
	$activity_id = bp_activity_add( array(
		'id'                => false,
		'user_id'           => $args['user_id'],
		'action'            => $args['action'],
		'content'           => $args['content'],
		//'primary_link'      => '',
		'component'         => $component,
		'type'              => 'mpp_media_upload',
		'item_id'           => absint( $args['component_id'] ),
		'secondary_item_id' => false,
		'recorded_time'     => bp_core_current_time(),
		'hide_sitewide'     => $hide_sitewide
	) );
	
	if( ! $activity_id ) {
		return false;//there was a problem
		
	}
	
	//store the type of gallery activity in meta
	
	if( $type ) {
		mpp_activity_update_activity_type( $activity_id, $type );
	}
	
	if( $media_ids ) {
		$media_ids = wp_parse_id_list( $media_ids );
		mpp_activity_update_attached_media_ids( $activity_id, $media_ids );
	}
	
	if( $gallery_id ) {
		mpp_activity_update_gallery_id( $activity_id, $gallery_id );
	}
	
	if( $media_id ) {
		mpp_activity_update_media_id( $activity_id, $media_id );
	}
	
	return $activity_id;
	
}