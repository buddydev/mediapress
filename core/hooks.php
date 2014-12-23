<?php

//hooks applied which are not specific to any gallery component and applies to all

function mpp_modify_page_title( $complete_title, $title, $sep, $seplocation ) {
  
	
	$sub_title = array();
   
	if( ! mpp_is_component_gallery() && ! mpp_is_gallery_component() )
	   return $complete_title;
   
	
	
	if( mpp_is_single_gallery() ){
		
		$sub_title[] = get_the_title( mpp_get_current_gallery_id() );
	}
	if( mpp_is_single_media() ) {
		
		$sub_title[] = get_the_title( mpp_get_current_media_id() );
	}
	
	if( mpp_is_gallery_management() || mpp_is_media_management() ) {
		
		$sub_title[] = ucwords( mediapress()->get_action() );
		$sub_title[] = ucwords( mediapress()->get_edit_action() );
		
	}
	
	$sub_title = array_filter( $sub_title );
	
	if( !empty( $sub_title ) )
		$complete_title = $complete_title .  join( ' | ', $sub_title ) . ' | ';
	
	return $complete_title;
}

add_filter( 'bp_modify_page_title', 'mpp_modify_page_title', 20, 4 );

