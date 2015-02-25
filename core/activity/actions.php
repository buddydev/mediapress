<?php

/***
 * MediaPress Activity Related actions
 */
/**
 * Clear the attached media list for activity
 * 
 * When a media is uploaded from activity we store the media id in the cookie which we use when the activity is submitted to attach that media to activity item
 * When a page reloads, we need to clear that list and allow a new list 
 * Sometimes, people may abandon media after uploading, this allows us to keep a check of all the orphan media
 */
function mpp_activity_clear_attached_media_cookie() {
    
    setcookie( '_mpp_activity_attached_media_ids', '', time()-3600, '/' );
    $_COOKIE['_mpp_activity_attached_media_ids'] = '';//reset
    
}

add_action( 'bp_actions', 'mpp_activity_clear_attached_media_cookie' );

/**
 * When a user activity is posted, we mark all the media that was uploaded and is set as orphaned(by default) to be attached to this activity
 * @param string $content
 * @param int $user_id
 * @param int $activity_id
 */
function mpp_activity_mark_attached_media_for_user_wall( $content, $user_id, $activity_id  ){
	
	mpp_activity_mark_attached_media( $activity_id );
}

add_action( 'bp_activity_posted_update', 'mpp_activity_mark_attached_media_for_user_wall', 1, 3 );

/**
 * When a group activity is posted, we mark all the media that was uploaded and is set as orphaned(by default) to be attached to this activity
 * @param string $content
 * @param int $user_id
 * @param int $group_id
 * @param int $activity_id
 */
function mpp_activity_mark_attached_media_for_groups_wall( $content, $user_id, $group_id,  $activity_id  ){
	
	mpp_activity_mark_attached_media( $activity_id );
}

add_action( 'bp_groups_posted_update', 'mpp_activity_mark_attached_media_for_groups_wall', 1, 4 );


/**
 * 
 * This section deals with the activity to comment synchronization
 * 
 * 
 */
/**
 * On New Activity Comment, Create a new WordPress comment too
 * 
 * @param type $comment_id
 * @param type $param
 * @param type $activity
 * @return type
 */
function mpp_activity_synchronize_to_comment( $comment_id, $param, $activity ){
    //it must be upload from activity
    //so lt us crea
    //check that the media was posted in activity
    $gallery_id = mpp_activity_get_gallery_id( $activity->id );
    if( !$gallery_id )
        return;
    
    $bp_comment = new BP_Activity_Activity( $comment_id );
    
    //now we need to add a comment
    //
    //my logic to find the parent may be flawed here, Needs a confirmation from other people
    if( $bp_comment->secondary_item_id != $activity->id ){
        $parent_id = $bp_comment->secondary_item_id;
        
        //this is a multilevel comment
        
        //we will add a child comment in wp too as the 
    }else{
        
        $parent_id = $activity->id;
    }
  
    $wp_comment_parent_id = (int) mpp_activity_get_associated_comment_id( $parent_id );
    //if we are here, It must be an activity where we have uploaded media
    //we will create a comment and add
    if( $wp_comment_parent_id > 0 ){
        //we have a parent comment associated, so we will be adding a child comment
        
        $wp_comment = get_comment( $wp_comment_parent_id );
        
        
        
    }
    $commetn_data = array(
                'post_id'			=> $gallery_id,
                'user_id'			=> get_current_user_id(),
                'comment_parent'	=> $wp_comment_parent_id,
                'comment_content'	=> $bp_comment->content,
                'comment_type'		=> mpp_get_comment_type() 
                
    );
    
    $new_comment_id = mpp_add_comment( $commetn_data );
    
    //update comment meta
    if( $new_comment_id ){
        mpp_update_comment_meta( $new_comment_id, '_mpp_activity_id', $comment_id );
        
        mpp_activity_update_associated_comment_id( $comment_id, $new_comment_id );
    }
   
    
}
add_action( 'bp_activity_comment_posted', 'mpp_activity_synchronize_to_comment', 10, 3 );