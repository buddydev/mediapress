<?php

function mpp_group_is_gallery_enabled( $group_id = false ) {
	
	if( ! $group_id ) {
		$group = groups_get_current_group();
		if( !empty( $group ) )
			$group_id = $group->id;
	
	}
	if( ! $group_id )
		return false;
	
	//default settings from gloabl
	
	$is_enabled = groups_get_groupmeta( $group_id, '_mpp_is_enabled',  true );
	
	
	//if not set, get the global settings
	if( empty( $is_enabled ) ) {
		
		
		
		if( mpp_is_active_component( 'groups' ) )
			$is_enabled = 'yes';
		else
			$is_enabled = 'no';
	}
	
	return $is_enabled == 'yes';// if is_enabled is set to yes
}
/**
 * Set Gallery as enabled/disabled
 * 
 * @param type $group_id
 * @param type $enabled
 * @return boolean
 */
function mpp_group_set_gallery_state( $group_id = false, $enabled = 'yes' ) {
	
	if( ! $group_id )
		$group_id = bp_get_group_id ( groups_get_current_group() );
	
	if( ! $group_id )
		return false;
	
	//default settings from gloabl
	
	$is_enabled = groups_update_groupmeta( $group_id, '_mpp_is_enabled',  $enabled );
	
	return $is_enabled;
}

//for group wall galleries

/**
 * Get wall photo gallery id
 * 
 * @param type $group_id
 * @return type
 */
function mpp_get_groups_wall_photo_gallery_id( $group_id ) {

	return (int) groups_get_groupmeta( $group_id, '_mpp_wall_photo_gallery_id', true );
}

/**
 * Get wall Video gallery id
 * 
 * @param type $group_id
 * @return type
 */
function mpp_get_groups_wall_video_gallery_id( $group_id ) {
	
	return (int)groups_get_groupmeta( $group_id, '_mpp_wall_video_gallery_id', true );
}

/**
 * Get wall audio gallery id
 * 
 * @param type $group_id
 * @return type
 */
function mpp_get_groups_wall_audio_gallery_id( $group_id ) {
	
	return (int) groups_get_groupmeta( $group_id, '_mpp_wall_audio_gallery_id', true );
}

/**
 * update wall photo gallery id
 * 
 * @param type $group_id
 * @return type
 */
function mpp_update_groups_wall_photo_gallery_id( $group_id, $gallery_id ) {

	return  groups_update_groupmeta( $group_id, '_mpp_wall_photo_gallery_id', $gallery_id );
}

/**
 * update wall Video gallery id
 * 
 * @param type $group_id
 * @return type
 */
function mpp_update_groups_wall_video_gallery_id( $group_id, $gallery_id ) {
	
	return groups_update_groupmeta( $group_id, '_mpp_wall_video_gallery_id', $gallery_id );
}

/**
 * update wall audio gallery id
 * 
 * @param type $user_id
 * @return type
 */
function mpp_update_groups_wall_audio_gallery_id( $group_id, $gallery_id ) {
	
	return groups_update_groupmeta( $group_id, '_mpp_wall_audio_gallery_id', $gallery_id );
}

/***
 * Delete
 */

function mpp_delete_groups_wall_gallery_id( $group_id, $type, $gallery_id ) {
	
	$key = "_mpp_wall_{$type}_gallery_id";
	
	return groups_delete_groupmeta( $group_id, $key, $gallery_id );
}


function mpp_check_groups_access( $component_type, $component_id, $user_id = null ){
    
	if( ! $user_id )
		$user_id = get_current_user_id ( );
    
	$allow = false;
      
    if( is_super_admin() || bp_is_active( 'groups' ) && ( groups_is_user_member( $user_id, $component_id ) ) ) 
            $allow = true;
	
    return apply_filters( 'mpp_check_friends_access', $allow, $component_type, $component_id, $user_id );
    
}
