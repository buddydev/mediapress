<?php
//No direct access to the file 
if( ! defined( 'ABSPATH' ) )
	exit( 0 );

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
    
    if( empty( $_POST['mpp-attached-media'] ) )
        return ;//don't do anything
    
    //let us process
    $media_ids = $_POST['mpp-attached-media'];
    $media_ids = explode( ',',  $media_ids ); //make an array
    
	$media_ids = array_filter( array_unique( $media_ids ) );
	
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
	
    //save activity privacy
	$status_object = mpp_get_status_object( $media->status );
	//if you have BuddyPress Activity privacy plugin enabled, this will work out of the box
	if( $status_object ) {
		bp_activity_update_meta( $activity->id, 'activity-privacy', $status_object->activity_privacy );
	}
	
    
    //reset the cookie
}

function mpp_activity_create_comment_for_activity( $activity_id ) {
	
	if ( ! $activity_id || ! mpp_get_option( 'activity_comment_sync' ) ) {
		return ;
	}
	
		
	$activity = new BP_Activity_Activity( $activity_id );
	
	if( $activity->type != 'mpp_media_upload' ) {
		return ;
	}
	
	$gallery_id = mpp_activity_get_gallery_id( $activity_id );
	$media_id	= mpp_activity_get_media_id( $activity_id );
	
	//this is not MediaPress activity
	if( ! $gallery_id && ! $media_id ) {
		return ;
	}
	//parent post id for the comment
	$parent_id = $media_id > 0 ? $media_id : $gallery_id;
	
	//now, create a top level comment and save
	
	$comment_data = array(
		'post_id'			=> $parent_id,
		'user_id'			=> get_current_user_id(),
		'comment_parent'	=> 0,
		'comment_content'	=> $activity->content,
		'comment_type'		=> mpp_get_comment_type(), 

    );
    
    $comment_id = mpp_add_comment( $comment_data );
    
    //update comment meta
    if( $comment_id ) {
		
        mpp_update_comment_meta( $comment_id, '_mpp_activity_id', $activity_id );
        
        mpp_activity_update_associated_comment_id( $activity_id, $comment_id );
		
		//also since there are media attched and we are mirroring activity, let us save the attached media too
		
		$media_ids = mpp_activity_get_attached_media_ids( $activity_id );
		//it is a gallery upload post from activity
		if( $gallery_id && ! empty( $media_ids )  ) {
			//only available when sync is enabled
			if( function_exists( 'mpp_comment_update_attached_media_ids' ) ) {
				mpp_comment_update_attached_media_ids($comment_id, $media_ids );
			}
		}
		//most probably a comment on media
		if( ! empty( $media_id ) ) {
			//should we add media as the comment meta? no, we don't need that at the moment
		}
    }
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
	$status_object = null;
	
	if( $args['status']  ) {
		$status_object = mpp_get_status_object( $args['status'] );
		
		if( $status_object && ( $status_object->activity_privacy =='hidden' || $status_object->activity_privacy == 'onlyme' ) ) {
			$hide_sitewide = 1;
		}

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
	//save activity privacy
	if( $status_object ) {
		bp_activity_update_meta( $activity_id, 'activity-privacy', $status_object->activity_privacy );
	}
	return $activity_id;
	
}

/***
 * Since BuddyPress does not allow filtering activity comment template, we do it ourself here
 * 
 * @see bp_activity_comments for the originalc code
 */
function mpp_activity_comments( $args = '' ) {
	echo mpp_activity_get_comments( $args );
}

/**
 * Get the comment markup for an activity item.
 * clone of bp_activity_get_comments
*/
function mpp_activity_get_comments( $args = '' ) {
	global $activities_template;

	if ( empty( $activities_template->activity->children ) ) {
		return false;
	}
	
	
	mpp_activity_recurse_comments( $activities_template->activity );
}
/**
 * Loops through a level of activity comments and loads the template for each.
 *
 * Note: The recursion itself used to happen entirely in this function. Now it is
 * split between here and the comment.php template.
 *
 * It is a copy of bp_activity_recurse_comments, since bp dioes not allow using custom template for activity comment, It acts as a filler
 * 
 * @since 1.0.0
 * @see bp_activity_recurse_comments
 *
 * @param object $comment The activity object currently being recursed.
 *
 * @global object $activities_template {@link BP_Activity_Template}
 * @uses locate_template()
 *
 * @return bool|string
 */
function mpp_activity_recurse_comments( $comment ) {
	global $activities_template;

	if ( empty( $comment ) ) {
		return false;
	}

	if ( empty( $comment->children ) ) {
		return false;
	}
	
	
	/**
	 * Filters the opening tag for the template that lists activity comments.
	 *
	 * @since BuddyPress (1.6.0)
	 *
	 * @param string $value Opening tag for the HTML markup to use.
	 */
	echo apply_filters( 'bp_activity_recurse_comments_start_ul', '<ul>' );
	
	$template = mpp_locate_template( array( 'buddypress/activity/comment.php' ), false, false );
	
	// Backward compatibility. In older versions of BP, the markup was
		// generated in the PHP instead of a template. This ensures that
		// older themes (which are not children of bp-default and won't
		// have the new template) will still work.
		if ( !$template ) {
			$template = buddypress()->plugin_dir . '/bp-themes/bp-default/activity/comment.php';
		}
		
		
	foreach ( (array) $comment->children as $comment_child ) {

		// Put the comment into the global so it's available to filters
		$activities_template->activity->current_comment = $comment_child;
		
		
		load_template( $template, false );

		unset( $activities_template->activity->current_comment );
	}

	/**
	 * Filters the closing tag for the template that list activity comments.
	 *
	 * @since BuddyPress (1.6.0)
	 *
	 * @param string $value Closing tag for the HTML markup to use.
	 */
	echo apply_filters( 'bp_activity_recurse_comments_end_ul', '</ul>' );
}
