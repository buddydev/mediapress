<?php

//hooks applied which are not specific to any gallery component and applies to all
function mpp_modify_page_title( $complete_title, $title, $sep, $seplocation ) {
  $sub_title = array();
   
  if( !mpp_is_component_gallery() && !mpp_is_gallery_component() )
     return $complete_title;

  if( mpp_is_single_gallery() )
    $sub_title[] = get_the_title( mpp_get_current_gallery_id() );

  if( mpp_is_single_media() ) 
    $sub_title[] = get_the_title( mpp_get_current_media_id() );
  
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

//filter body class
function mpp_filter_body_class( $classes, $class ) {
  
  // $new_classes = array();
  
  $component = mpp_get_current_component();

  //if not mediapress pages, return 
  if( ! mpp_is_gallery_component() && ! mpp_is_component_gallery() ) {
    return $classes;
  }
  
  //ok, It must be mpp pages

  //$classes[] .= 'mpp-page'; //for all mediapress pages
  array_push($classes, "mpp-page");

  //if it is a directory page
  if( mpp_is_gallery_directory() ) {
    array_push($classes, "mpp-page-directory");
    
  } elseif( mpp_is_gallery_component() || mpp_is_component_gallery() ) {
    //we are on user gallery  page or a component gallery page
    //append class mpp-page-members or mpp-page-groups or mpp-page-events etc depending on the current associated component
    // $new_classes[] .= 'mpp-page-'. $component;
    array_push($classes, "mpp-page-". $component);

    if( mpp_is_media_management() ) {
      //is it edit media? 
      array_push($classes, 
        "mpp-page-media-management", 
        "mpp-page-media-management-" . mpp_get_media_type(), 
        "mpp-page-media-manage-action-" . mediapress()->get_edit_action() 
      );
  
    }elseif( mpp_is_single_media() ) {
      //is it single media
      array_push($classes, 
        "mpp-page-media-single", 
        "mpp-page-media-single-" .mpp_get_media_type() 
      );

    }elseif( mpp_is_gallery_management() ) {
      //id gallery management?
      array_push($classes, 
        "mpp-page-gallery-management", 
        "mpp-page-gallery-management-" . mpp_get_gallery_type(), 
        "mpp-page-gallery-manage-action-" . mediapress()->get_edit_action() 
      );
    }elseif( mpp_is_single_gallery() ) {
      //is singe gallery
      array_push($classes, 
        "mpp-page-single-gallery", 
        "mpp-page-single-gallery-" . mpp_get_gallery_type(), 
        "mpp-page-single-gallery-" . mpp_get_gallery_status() 
      );
    }else {
      //it is the gallery listing page of the component
      array_push($classes, 
        "mpp-page-gallery-list", 
        "mpp-page-gallery-list-" . $component
      );
    }
  }
  return $classes;
}
add_filter( 'body_class', 'mpp_filter_body_class', 10, 2 );