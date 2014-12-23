<?php


function mpp_current_component_id_for_user( $component_id ) {

  if( bp_is_user() )
    return bp_displayed_user_id();//that is displayed user id

  return $component_id;
}

add_filter( 'mpp_get_current_component_id', 'mpp_current_component_id_for_user' );//won't work in ajax mode

add_action( 'wpmu_delete_user', 'mpp_delete_galleries_for_user', 1 );
add_action( 'delete_user', 'mpp_delete_galleries_for_user', 1 );
add_action( 'make_spam_user', 'mpp_delete_galleries_for_user', 1 );

function mpp_delete_galleries_for_user( $user_id ){
	
	$query = new MPP_Gallery_Query( array('user_id' => $user_id, 'fields' => 'ids' ) );
	$ids = $query->get_ids();
	
	//Delete all galleries
	foreach( $ids as $gallery_id ){
		mpp_delete_gallery( $gallery_id );
	}
	
}