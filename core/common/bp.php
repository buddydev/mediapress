<?php


/**
 * Are we on Gallery Directory or User Gallery Pages?
 * 
 * @return boolean
 */
function mpp_is_gallery_component() {
    
    if ( bp_is_current_component( 'mediapress' ) ) 
            return true;

    return false;
}
/**
 * Are we on User Gallery Pages?
 * 
 * @return boolean
 */
function mpp_is_user_gallery() {
    
    if ( bp_is_user() && mpp_is_gallery_component() ) 
            return true;

    return false;
}


/**
 * Are we on component gallery
 * 
 * Is gallery associated to a component(groups/events etc)
 * 
 * @return boolean
 */
function mpp_is_component_gallery() {
    
    $is_gallery = false;
    
	
    if( bp_is_current_action( MPP_GALLERY_SLUG ) && mpp_is_active_component( bp_current_component() ) )
        $is_gallery = true;
  
    return apply_filters( 'mpp_is_component_gallery', $is_gallery );
}
