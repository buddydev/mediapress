<?php
/**
 * Get the view associated with current gallery
 * 
 * @param int $gallery_id
 * @param string $default fallback view id
 * @return string view id
 */
function mpp_get_gallery_view_id( $gallery_id, $default = '' ) {
	
	$view_id = mpp_get_gallery_meta( $gallery_id, '_mpp_view', true );
	
	if( ! $view_id ) {
		$view_id = 'default';
	}
	
	return $view_id;
}
/**
 * Set gallery view
 * 
 * @param type $gallery_id
 * @param type $view_id
 * @return type
 */
function mpp_update_gallery_view_id( $gallery_id, $view_id ) {
	
	return mpp_update_gallery_meta( $gallery_id, '_mpp_view', $view_id );
}
/**
 * Delete gallery view
 * 
 * @param type $gallery_id
 * @param type $view_id
 * @return type
 */
function mpp_delete_gallery_view_id( $gallery_id ) {
	
	return mpp_delete_gallery_meta( $gallery_id, '_mpp_view' );
}

/**
 * 
 * @param string $component
 * @return MPP_Gallery_Template_Loader
 */
function mpp_get_component_template_loader( $component ) {
	if( !  class_exists( 'MPP_Gallery_View_Loader') ) {
		$path = mediapress()->get_path() . 'core/views/loaders/';
		
		require_once $path . 'class-mpp-gallery-view-loader.php';
		require_once $path . 'class-mpp-members-gallery-template-loader.php';
		require_once $path . 'class-mpp-groups-gallery-template-loader.php';
		require_once $path . 'class-mpp-sitewide-gallery-template-loader.php';
		
	}
	if ( $component == 'groups' ) {
		$loader = MPP_Groups_Gallery_Template_Loader::get_instance();
	} elseif ( $component =='members' ) {
		$loader = MPP_Members_Gallery_Template_Loader::get_instance();
	} else {
		$loader = MPP_Sitewide_Gallery_Template_Loader::get_instance();
	}
	
	return $loader;
}
/**
 * @todo update
 * @param type $component
 * @return string
 */
function mpp_get_component_gallery_view( $component ) {
	
	return 'default';
}