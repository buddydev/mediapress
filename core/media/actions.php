<?php


/**
 * Handles Single media Edit details
 * 
 * @return type
 */
function mpp_action_edit_media() {
	
	//allow media to be edited from anywhere
		
	if( empty( $_POST['mpp-action'] ) || $_POST['mpp-action'] != 'edit-media' )
		return;
	
	$referer = wp_get_referer();
	
	//if we are here, It is media edit action
	
	if( !wp_verify_nonce( $_POST['mpp-nonce'], 'mpp-edit-media' ) ) {
		//add error message and return back to the old page
		mpp_add_feedback( __( 'Action not authorized!', 'mediapress' ), 'error' );
		
		if( $referer )
			mpp_redirect( $referer );
		return;
		
	}
	
	$media_id = absint( $_POST['mpp-media-id'] );
	
	if( ! $media_id )
		return;
	
	
	
	//check for permission
	if( !mpp_user_can_edit_media( $media_id ) ) {
		
		mpp_add_feedback( __( "You don't have permission to edit this!", 'mediapress' ), 'error' );
		
		if( $referer )
			mpp_redirect( $referer );
		return;
	}
			
	//if we are here, validate the data and let us see if we can update
	
	$title = $_POST['mpp-media-title'];
	$description = $_POST['mpp-media-description'];
	
	
	$status = $_POST['mpp-media-status'];
	$errors = array();
	//todo
	//In future, replace with media type functions
	if( ! mpp_is_active_status( $status ) )
		$errors['status'] = __( 'Invalid media status!', 'mediapress' );
	
	if( empty( $title ) )
		$errors['title'] = __('Title can not be empty', 'mediapress' );
	
	
	
	//give opportunity to other plugins to add their own validation errors
	$validation_errors = apply_filters( 'mpp-edit-media-field-validation', $errors, $_POST );
	
	if( !empty( $validation_errors ) ) {
		//let us add the validation error and return back to the earlier page
		
		$message = join( '\r\n', $validation_errors );
		
		mpp_add_feedback( $message, 'error' );
		
		if( $referer )
			mpp_redirect( $referer );
		
		return;
	}
		
	//let us create gallery
	
	$media_id = mpp_update_media( array(
			'title'			=> $title,
			'description'	=> $description,
			'status'		=> $status,
			'creator_id'	=> get_current_user_id(),
			'id'			=> $media_id,
			
	));
	

	if( ! $media_id ) {
		
		mpp_add_feedback( __( 'Unable to update!', 'mediapress' ), 'error' );
		
		if( $referer )
			mpp_redirect( $referer );
		return;
	}
	
	//if we are here, the gallery was created successfully,
	
	//let us redirect to the gallery_slug/manage/upload page
	
	$redirect_url = mpp_get_media_edit_url( $media_id );
	
	mpp_add_feedback( __( 'Updated successfully!', 'mediapress' ) );
	
	mpp_redirect( $redirect_url );
	
}
add_action( 'bp_actions', 'mpp_action_edit_media', 2 );//update gallery settings, cover

/**
 * Handles Media deletion
 * 
 * @return type
 */
function mpp_action_delete_media() {
	

	
	if( empty( $_REQUEST['mpp-action'] ) || $_REQUEST['mpp-action'] != 'delete-media' )
		return;
	
	if( ! $_REQUEST['mpp-media-id'] )
		return;
	
	$referer = wp_get_referer();
	
	if( ! wp_verify_nonce( $_REQUEST['mpp-nonce'], 'mpp-delete-media' ) ) {
		//add error message and return back to the old page
		mpp_add_feedback( __( 'Action not authorized!', 'mediapress' ), 'error' );
		
		if( $referer )
			mpp_redirect( $referer );
		return;
	}
	
	
	$media = '';
	
	if( !empty( $_REQUEST['mpp-media-id'] ) )
		$media = mpp_get_media ( (int) $_REQUEST['mpp-media-id'] );
	
			
	//check for permission
	//we may want to allow passing of component from the form in future!
	if( !mpp_user_can_delete_media( $media->id ) ) {
		
		mpp_add_feedback( __( "You don't have permission to delete this!", 'mediapress' ), 'error' );
		
		if( $referer )
			mpp_redirect( $referer );
		
		return;
	}
	
	//if we are here, delete media and redirect to the component base url
	mpp_delete_media( $media->id );
	$redirect_url = mpp_get_gallery_permalink( $media->gallery_id ) ;
	mpp_add_feedback( __( "Successfully deleted!", 'mediapress' ), 'error' );
	
	
	mpp_redirect( $redirect_url );
	
}
add_action( 'bp_actions', 'mpp_action_delete_media', 2 );