<?php

/**
 * MediaPress Ajax helper
 * 
 */
class MPP_Ajax_Helper{

	private static $instance;

	private function __construct() {

		$this->setup_hooks();
	}

	/**
	 * 
	 * @return MPP_Ajax_Helper
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) )
			self::$instance = new self();

		return self::$instance;
	}

	public function setup_hooks() {

		//directory loop
		add_action( 'wp_ajax_mpp_filter', array( $this, 'load_dir_list' ) );
		add_action( 'wp_ajax_nopriv_mpp_filter', array( $this, 'load_dir_list' ) );
		//add/upload a new Media
		add_action( 'wp_ajax_mpp_add_media', array( $this, 'add_media' ) );
		add_action( 'wp_ajax_mpp_upload_cover', array( $this, 'cover_upload' ) );
		
		add_action( 'wp_ajax_mpp_add_comment', array( $this, 'post_comment' ) );
		
	}
	
	public function load_dir_list(){
		
		$type = isset( $_POST['filter'] ) ? $_POST['filter'] : '';
		$page = absint( $_POST['page'] );
		
		$scope = $_POST['scope'];
		
		$search_terms = $_POST['search_terms'];
		
				//make the query and setup 
		mediapress()->is_directory = true;

		//get all public galleries, should we do type filtering
		mediapress()->the_gallery_query = new MPP_Gallery_Query(
				array(
					'status'		=> 'public',
					'type'			=> $type,
					'page'			=> $page,
					'search_terms'	=> $search_terms,

				) );
		
		
				mpp_get_template('gallery/loop-gallery.php' );
					
		
		exit( 0 );
	}
	//add media via ajax
	public function add_media() {

		check_ajax_referer( 'mpp_add_media' ); //check for the referrer


		$response = array();

		$file = $_FILES;

		$file_id = '_mpp_file'; //key name in the files array
		//find the components we are trying to add for
		$component		 = $_POST[ 'component' ];
		$component_id	 = $_POST[ 'component_id' ];
		$context		 = mpp_get_upload_context( false, $_POST[ 'context' ] );
		
		if ( ! $component )
			$component		 = mpp_get_current_component();

		if ( ! $component_id )
			$component_id	 = mpp_get_current_component_id();

		//get the uploader
		$uploader = mpp_get_storage_manager(); //should we pass the component?
		////should we check for the existence of the default storage method?
		
		//setup for component
		$uploader->setup_for( $component, $component_id );

		//check if the server can handle the upload?
		if ( ! $uploader->can_handle() ) {

			wp_send_json_error( array(
				'message' => __( 'Server can not handle this much amount of data. Please upload a smaller file or ask your server administrator to change the settings.', 'mediapress' )
			) );
		}
		
		if( ! mpp_has_available_space( $component, $component_id ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unable to upload. You have used the allowed storage quota!', 'mediapress' )
			) );
		}
		//if we are here, the server can handle upload 
		//check should be here
		$gallery_id = 0;
		
		if ( isset( $_POST[ 'gallery_id' ] ) )
			$gallery_id = absint( $_POST[ 'gallery_id' ] );

		if ( $gallery_id )
			$gallery = mpp_get_gallery( $gallery_id );
		else
			$gallery = false; //not set
			
		  //if there is no gallery id given and the context is activity, we may want to auto create the gallery

		$media_type = mpp_get_media_type_from_extension( mpp_get_file_extension( $file[ $file_id ][ 'name' ] ) );
		
		if( !$media_type ){
			
			wp_send_json_error( array( 'message' => __( "This file type is not supported.", 'mediapress' ) ) );
			
		}
		
		//if there is no gallery type defined( It wil happen in case of new gallery creation from admin page
		//we will set the gallery type as the type of the first media

		if ( $gallery && empty( $gallery->type ) ) {
			//update gallery type
			//set it to media type
			mpp_update_gallery_type( $gallery, $media_type );
		}
		//If the gallery is not given and It is members component, check if the upload context is activity?
		//Check if we have a profile gallery set for the current user for this type of media
		//if yes, then use that gallery to upload the media
		//otherwise we create a gallery of the current media type and set it as the profile gallery for that type

		if ( ! $gallery && $context == 'activity' ) {

			//if gallery is not given and the component supports wall gallery
			//then create
			if( ! mpp_is_activity_upload_enabled( $component ) ){
				
				wp_send_json_error( array( 'message' => __( "The gallery is not selected.", 'mediapress' ) ) );
				
			}
			$gallery_id = mpp_get_wall_gallery_id( array( 'component' => $component, 'component_id'=> $component_id, 'media_type'=> $media_type ));

			if ( ! $gallery_id ) {
				//if gallery does not exist, create 1

				$gallery_id = mpp_create_gallery( array(
					'creator_id'	 => get_current_user_id(),
					'title'			 => sprintf( _x( 'Wall %s Gallery', 'wall gallery name', 'mediapress' ), $media_type ),
					'description'	 => '',
					'status'		 => 'public',
					'component'		 => $component,
					'component_id'	 => $component_id,
					'type'			 => $media_type
				) );

				if ( $gallery_id ) {
					//save the profile gallery id
					mpp_update_wall_gallery_id( array(
						'component'		=> $component,
						'component_id'	=> $component_id,
						'media_type'	=> $media_type,
						'gallery_id'	=> $gallery_id
					) );
				}
			}
			//setup gallery object from the profile gallery id
			if ( $gallery_id )
				$gallery = mpp_get_gallery( $gallery_id );
		}
		//we may want to check the upload type and set the gallery to activity gallery etc if it is not set already

		$error = false;

		//detect media type of uploaded file here and then upload it accordingly also check if the media type uploaded and the gallery type matches or not
		//let us build our response for javascript
		//if we are uploading to a gallery, check for type
		//since we will be allowin g upload without gallery too, It is required to make sure $gallery is present or not

		if ( $gallery && !mpp_is_mixed_gallery( $gallery ) && $media_type !== $gallery->type ) {
			//if we are uploading to a gallery and It is not a mixed gallery, the media type must match the gallery type
			wp_send_json_error( array(
				'message' => sprintf( __( 'This file type is not allowed in current gallery. Only <strong>%s</strong> files are allowed!', 'mediapress' ), mpp_get_allowed_file_extensions_as_string( $gallery->type ) )
			) );
		}


		//if we are here, all is well :)

		if ( !mpp_user_can_upload( $component, $component_id, $gallery ) ) {

			$error_message = apply_filters( 'mpp_upload_permission_denied_message', __( "You don't have sufficient permissions to upload.", 'mediapress' ) );
			wp_send_json_error( array( 'message' => $error_message ) );
		}

		//if we are here, we have checked for all the basic errors, so let us just upload now


		$uploaded = $uploader->upload( $file, array( 'file_id' => $file_id, 'gallery_id' => $gallery_id, 'component' => $component, 'component_id' => $component_id ) );

		//upload was succesfull?
		if ( !isset( $uploaded[ 'error' ] ) ) {

			//file was uploaded successfully
			$title = $_FILES[ $file_id ][ 'name' ];

			$title_parts = pathinfo( $title );
			$title		 = trim( substr( $title, 0, -( 1 + strlen( $title_parts[ 'extension' ] ) ) ) );

			$url	 = $uploaded[ 'url' ];
			$type	 = $uploaded[ 'type' ];
			$file	 = $uploaded[ 'file' ];


			//$title = isset( $_POST['media_title'] ) ? $_POST['media_title'] : '';

			$content = isset( $_POST[ 'media_description' ] ) ? $_POST[ 'media_description' ] : '';

			$meta = $uploader->get_meta( $uploaded );

						
			$title_desc = $this->get_title_desc_from_meta( $type, $meta );
			
			if( !empty( $title_desc ) ) {
				
				if( empty( $title ) && !empty( $title_desc['title'] ) )
					$title = $title_desc['title'];
				
				if( empty( $content ) && !empty( $title_desc['content'] ) )
					$content = $title_desc['content'];
				
			}



			$status = isset( $_POST['media_status' ] ) ? $_POST[ 'media_status' ] : '';

			if ( empty( $status ) && $gallery )
				$status	 = $gallery->status; //inherit from parent,gallery must have an status
				
			  //we may need some more enhancements here
			if ( !$status )
				$status	 = mpp_get_default_status();

			//   print_r($upload_info);
			$is_orphan	 = 0;
			//Any media uploaded via activity is marked as orphan( Not associated with the mediapress unless the activity to which it was attached is actually created, check core/activity/actions.php to see how the orphaned media is adopted by the activity :) )
			if ( $context == 'activity' )
				$is_orphan	 = 1; //by default mark all uploaded media via activity as orphan

			
			$media_data = array(
				'title'			 => $title,
				'description'	 => $content,
				'gallery_id'	 => $gallery_id,
				'user_id'		 => get_current_user_id(),
				'is_remote'		 => false,
				'type'			 => $media_type,
				'mime_type'		 => $type,
				'src'			 => $file,
				'url'			 => $url,
				'status'		 => $status,
				'comment_status' => 'open',
				'storage_method' => mpp_get_storage_method(),
				'component_id'	 => $component_id,
				'component'		 => $component,
				'context'		 => $context,
				'is_orphan'		 => $is_orphan,
			);

			$id = mpp_add_media(
				$media_data
			);


			//should we update and resize images here?
			//
			mpp_gallery_increment_media_count( $gallery_id );
			
            $attachment = mpp_media_to_json( $id );
			//$attachment['data']['type_id'] = mpp_get_type_term_id( $gallery->type );
			echo json_encode( array(
				'success'	 => true,
				'data'		 => $attachment,
			) );
			//wp_send_json_success( array('name'=>'what') );
			exit( 0 );
		}else {


			wp_send_json_error( array( 'message' => $uploaded['error'] ) );
			
		}
	}
	
	public function add_oembed_media() {
		
		check_ajax_referer( 'mpp_add_media' ); //check for the referrer
		
		
		$media_type = '';
		$gallery_id = '';
		
		$component		 = $_POST[ 'component' ];
		$component_id	 = $_POST[ 'component_id' ];
		$context		 = mpp_get_upload_context( false, $_POST[ 'context' ] );
		
		if ( ! $component )
			$component		 = mpp_get_current_component();

		if ( ! $component_id )
			$component_id	 = mpp_get_current_component_id();

		//get the uploader
		$uploader = mpp_get_storage_manager( 'oembed' ); //should we pass the component?
		//setup for component
		$uploader->setup_for( $component, $component_id );

		//check if the server can handle the upload?
		if ( ! $uploader->can_handle() ) {

			wp_send_json_error( array(
				'message' => __( 'Server can not handle this much amount of data. Please upload a smaller file or ask your server administrator to change the settings.', 'mediapress' )
			) );
		}
		
		if( ! mpp_has_available_space( $component, $component_id ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unable to upload. You have used the allowed storage quota!', 'mediapress' )
			) );
		}
		//if we are here, the server can handle upload 
		//check should be here
		$gallery_id = 0;
		
		if ( isset( $_POST[ 'gallery_id' ] ) )
			$gallery_id = absint( $_POST[ 'gallery_id' ] );

		if ( $gallery_id )
			$gallery = mpp_get_gallery( $gallery_id );
		else
			$gallery = false; //not set
			
		  //if there is no gallery id given and the context is activity, we may want to auto create the gallery

		$media_type = mpp_get_media_type_from_extension( mpp_get_file_extension( $file[ $file_id ][ 'name' ] ) );
		
		if( !$media_type ){
			
			wp_send_json_error( array( 'message' => __( "This file type is not supported.", 'mediapress' ) ) );
			
		}
		
		//if there is no gallery type defined( It wil happen in case of new gallery creation from admin page
		//we will set the gallery type as the type of the first media

		if ( $gallery && empty( $gallery->type ) ) {
			//update gallery type
			//set it to media type
			mpp_update_gallery_type( $gallery, $media_type );
		}
		//If the gallery is not given and It is members component, check if the upload context is activity?
		//Check if we have a profile gallery set for the current user for this type of media
		//if yes, then use that gallery to upload the media
		//otherwise we create a gallery of the current media type and set it as the profile gallery for that type

		if ( ! $gallery && $context == 'activity' ) {

			//if gallery is not given and the component supports wall gallery
			//then create
			if( ! mpp_is_activity_upload_enabled( $component ) ){
				
				wp_send_json_error( array( 'message' => __( "The gallery is not selected.", 'mediapress' ) ) );
				
			}
			$gallery_id = mpp_get_wall_gallery_id( array( 'component' => $component, 'component_id'=> $component_id, 'media_type'=> $media_type ));

			if ( ! $gallery_id ) {
				//if gallery does not exist, create 1

				$gallery_id = mpp_create_gallery( array(
					'creator_id'	 => get_current_user_id(),
					'title'			 => sprintf( _x( 'Wall %s Gallery', 'wall gallery name', 'mediapress' ), $media_type ),
					'description'	 => '',
					'status'		 => 'public',
					'component'		 => $component,
					'component_id'	 => $component_id,
					'type'			 => $media_type
				) );

				if ( $gallery_id ) {
					//save the profile gallery id
					mpp_update_wall_gallery_id( array(
						'component'		=> $component,
						'component_id'	=> $component_id,
						'media_type'	=> $media_type,
						'gallery_id'	=> $gallery_id
					) );
				}
			}
			//setup gallery object from the profile gallery id
			if ( $gallery_id )
				$gallery = mpp_get_gallery( $gallery_id );
		}
		//we may want to check the upload type and set the gallery to activity gallery etc if it is not set already

		$error = false;

		//detect media type of uploaded file here and then upload it accordingly also check if the media type uploaded and the gallery type matches or not
		//let us build our response for javascript
		//if we are uploading to a gallery, check for type
		//since we will be allowin g upload without gallery too, It is required to make sure $gallery is present or not

		if ( $gallery && !mpp_is_mixed_gallery( $gallery ) && $media_type !== $gallery->type ) {
			//if we are uploading to a gallery and It is not a mixed gallery, the media type must match the gallery type
			wp_send_json_error( array(
				'message' => sprintf( __( 'This file type is not allowed in current gallery. Only <strong>%s</strong> files are allowed!', 'mediapress' ), mpp_get_allowed_file_extensions_as_string( $gallery->type ) )
			) );
		}


		//if we are here, all is well :)

		if ( !mpp_user_can_upload( $component, $component_id, $gallery ) ) {

			wp_send_json_error( array( 'message' => __( "You don't have sufficient permissions to upload.", 'mediapress' ) ) );
		}

		//if we are here, we have checked for all the basic errors, so let us just upload now


		$uploaded = $uploader->upload( $file, array( 'file_id' => $file_id, 'gallery_id' => $gallery_id, 'component' => $component, 'component_id' => $component_id ) );

		//upload was succesfull?
		if ( !isset( $uploaded[ 'error' ] ) ) {

			//file was uploaded successfully
			$title = $_FILES[ $file_id ][ 'name' ];

			$title_parts = pathinfo( $title );
			$title		 = trim( substr( $title, 0, -( 1 + strlen( $title_parts[ 'extension' ] ) ) ) );

			$url	 = $uploaded[ 'url' ];
			$type	 = $uploaded[ 'type' ];
			$file	 = $uploaded[ 'file' ];


			//$title = isset( $_POST['media_title'] ) ? $_POST['media_title'] : '';

			$content = isset( $_POST[ 'media_description' ] ) ? $_POST[ 'media_description' ] : '';

			$meta = $uploader->get_meta( $uploaded );

						
			$title_desc = $this->get_title_desc_from_meta( $type, $meta );
			
			if( !empty( $title_desc ) ) {
				
				if( empty( $title ) && !empty( $title_desc['title'] ) )
					$title = $title_desc['title'];
				
				if( empty( $content ) && !empty( $title_desc['content'] ) )
					$content = $title_desc['content'];
				
			}



			$status = isset( $_POST['media_status' ] ) ? $_POST[ 'media_status' ] : '';

			if ( empty( $status ) && $gallery )
				$status	 = $gallery->status; //inherit from parent,gallery must have an status
				
			  //we may need some more enhancements here
			if ( !$status )
				$status	 = mpp_get_default_status();

			//   print_r($upload_info);
			$is_orphan	 = 0;
			//Any media uploaded via activity is marked as orphan( Not associated with the mediapress unless the activity to which it was attached is actually created, check core/activity/actions.php to see how the orphaned media is adopted by the activity :) )
			if ( $context == 'activity' )
				$is_orphan	 = 1; //by default mark all uploaded media via activity as orphan

			
			$media_data = array(
				'title'			 => $title,
				'description'	 => $content,
				'gallery_id'	 => $gallery_id,
				'user_id'		 => get_current_user_id(),
				'is_remote'		 => false,
				'type'			 => $media_type,
				'mime_type'		 => $type,
				'src'			 => $file,
				'url'			 => $url,
				'status'		 => $status,
				'comment_status' => 'open',
				'storage_method' => mpp_get_storage_method(),
				'component_id'	 => $component_id,
				'component'		 => $component,
				'context'		 => $context,
				'is_orphan'		 => $is_orphan,
			);

			$id = mpp_add_media(
				$media_data
			);


			//should we update and resize images here?
			//
			mpp_gallery_increment_media_count( $gallery_id );
			
            $attachment = mpp_media_to_json( $id );
			//$attachment['data']['type_id'] = mpp_get_type_term_id( $gallery->type );
			echo json_encode( array(
				'success'	 => true,
				'data'		 => $attachment,
			) );
			//wp_send_json_success( array('name'=>'what') );
			exit( 0 );
		}else {


			wp_send_json_error( array( 'message' => $uploaded['error'] ) );
			
		}
	}
	
	public function cover_upload() {
	
		
		check_ajax_referer( 'mpp_add_media' ); //check for the referrer


		$response = array();

		$file = $_FILES;

		$file_id = '_mpp_file'; //key name in the files array
		
		//find the components we are trying to add for
		$component		 = 	$component_id	 = 0;
		
		$context		 = 'cover';
		
		$gallery_id	= absint( $_POST['mpp-gallery-id']);
		$parent_id = absint( $_POST['mpp-parent-id'] );
		
		
		if( ! $gallery_id || ! $parent_id )
			return;
		
		$gallery = mpp_get_gallery( $gallery_id );
		
		$component		= $gallery->component;
		$component_id	= $gallery->component_id;
		
		//get the uploader
		$uploader = mpp_get_storage_manager(); //should we pass the component?
		//setup for component
		$uploader->setup_for( $component, $component_id );

		//check if the server can handle the upload?
		if ( ! $uploader->can_handle( ) ) {

			wp_send_json_error( array(
				'message' => __( 'Server can not handle this much amount of data. Please upload a smaller file or ask your server administrator to change the settings.', 'mediapress' )
			) );
		}
		
		

		$media_type = mpp_get_media_type_from_extension( mpp_get_file_extension( $file[ $file_id ][ 'name' ] ) );
		
		//cover is always a photo,
		if( $media_type != 'photo' ) {
			
			wp_send_json( array(
				'message' => sprintf( __( 'Please upload a photo. Only <strong>%s</strong> files are allowed!', 'mediapress' ), mpp_get_allowed_file_extensions_as_string( $media_type ) )
			) );
			
		}
		
		

		$error = false;

		//if we are here, all is well :)

		if ( ! mpp_user_can_upload( $component, $component_id, $gallery ) ) {

			wp_send_json_error( array( 'message' => __( "You don't have sufficient permissions to upload.", 'mediapress' ) ) );
		}

		//if we are here, we have checked for all the basic errors, so let us just upload now


		$uploaded = $uploader->upload( $file, array( 'file_id' => $file_id, 'gallery_id' => $gallery_id, 'component' => $component, 'component_id' => $component_id, 'is_cover' => 1 ) );

		//upload was succesfull?
		if ( ! isset( $uploaded[ 'error' ] ) ) {

			//file was uploaded successfully
			$title = $_FILES[ $file_id ][ 'name' ];

			$title_parts = pathinfo( $title );
			$title		 = trim( substr( $title, 0, -( 1 + strlen( $title_parts[ 'extension' ] ) ) ) );

			$url	 = $uploaded[ 'url' ];
			$type	 = $uploaded[ 'type' ];
			$file	 = $uploaded[ 'file' ];


			//$title = isset( $_POST['media_title'] ) ? $_POST['media_title'] : '';

			$content = isset( $_POST[ 'media_description' ] ) ? $_POST[ 'media_description' ] : '';

			$meta = $uploader->get_meta( $uploaded );

			$title_desc = $this->get_title_desc_from_meta( $type, $meta );
			
			if( ! empty( $title_desc ) ) {
				
				if( empty( $title ) && !empty( $title_desc['title'] ) )
					$title = $title_desc['title'];
				
				if( empty( $content ) && !empty( $title_desc['content'] ) )
					$content = $title_desc['content'];
				
			}



			$status = isset( $_POST['media_status' ] ) ? $_POST[ 'media_status' ] : '';

			if ( empty( $status ) && $gallery )
				$status	 = $gallery->status; //inherit from parent,gallery must have an status
				
			  //we may need some more enhancements here
			if ( !$status )
				$status	 = mpp_get_default_status();

			//   print_r($upload_info);
			$is_orphan	 = 0;
			
			

			
			$media_data = array(
				'title'			 => $title,
				'description'	 => $content,
				'gallery_id'	 => $parent_id,
				'user_id'		 => get_current_user_id(),
				'is_remote'		 => false,
				'type'			 => $media_type,
				'mime_type'		 => $type,
				'src'			 => $file,
				'url'			 => $url,
				'status'		 => $status,
				'comment_status' => 'open',
				'storage_method' => mpp_get_storage_method(),
				'component_id'	 => $component_id,
				'component'		 => $component,
				'context'		 => $context,
				'is_orphan'		 => $is_orphan,
				'is_cover'		 => true	
			);

			$id = mpp_add_media(
					$media_data
			);


			$old_cover = mpp_get_gallery_cover_id( $gallery_id );
			
			
			if( $gallery->type == 'photo' ) {
				mpp_gallery_increment_media_count( $gallery_id );
				
			}else{
				//mark it as non gallery media
				mpp_delete_media_meta( $id, '_mpp_is_mpp_media' );
				
				if( $old_cover ){
					mpp_delete_media( $old_cover );
				
				}
			}	
			
			mpp_update_media_cover_id( $parent_id, $id );
			
            $attachment = mpp_media_to_json( $id );
			//$attachment['data']['type_id'] = mpp_get_type_term_id( $gallery->type );
			echo json_encode( array(
				'success'	 => true,
				'data'		 => $attachment,
			) );
			//wp_send_json_success( array('name'=>'what') );
			exit( 0 );
		}else {


			echo json_encode( array( 'error' => 1, 'message' => $uploaded['error'] ) );
			exit( 0 );
		}
		
		
		
		
	}
	


	/**
	 * Utility method to extract title/deesc from meta
	 * 
	 * @param type $type
	 * @param type $meta
	 * @return array( 'title'=> Extracted title, 'content'=>  Extracted content )
	 */
	public function get_title_desc_from_meta( $type, $meta ){
		
		
		$title = $content = '';
				//match mime type
		if ( preg_match( '#^audio#', $type ) ) {


			if ( !empty( $meta[ 'title' ] ) )
				$title = $meta[ 'title' ];

			// $content = '';

			if ( !empty( $title ) ) {

				if ( !empty( $meta[ 'album' ] ) && !empty( $meta[ 'artist' ] ) ) {
					/* translators: 1: audio track title, 2: album title, 3: artist name */
					$content .= sprintf( __( '"%1$s" from %2$s by %3$s.' ), $title, $meta[ 'album' ], $meta[ 'artist' ] );
				} elseif ( !empty( $meta[ 'album' ] ) ) {
					/* translators: 1: audio track title, 2: album title */
					$content .= sprintf( __( '"%1$s" from %2$s.' ), $title, $meta[ 'album' ] );
				} elseif ( !empty( $meta[ 'artist' ] ) ) {
					/* translators: 1: audio track title, 2: artist name */
					$content .= sprintf( __( '"%1$s" by %2$s.' ), $title, $meta[ 'artist' ] );
				} else {
					$content .= sprintf( __( '"%s".' ), $title );
				}
			} elseif ( !empty( $meta[ 'album' ] ) ) {

				if ( !empty( $meta[ 'artist' ] ) ) {
					/* translators: 1: audio album title, 2: artist name */
					$content .= sprintf( __( '%1$s by %2$s.' ), $meta[ 'album' ], $meta[ 'artist' ] );
				} else {
					$content .= $meta[ 'album' ] . '.';
				}
			} else if ( !empty( $meta[ 'artist' ] ) ) {

				$content .= $meta[ 'artist' ] . '.';
			}

			if ( !empty( $meta[ 'year' ] ) )
				$content .= ' ' . sprintf( __( 'Released: %d.' ), $meta[ 'year' ] );

			if ( !empty( $meta[ 'track_number' ] ) ) {
				$track_number = explode( '/', $meta[ 'track_number' ] );
				if ( isset( $track_number[ 1 ] ) )
					$content .= ' ' . sprintf( __( 'Track %1$s of %2$s.' ), number_format_i18n( $track_number[ 0 ] ), number_format_i18n( $track_number[ 1 ] ) );
				else
					$content .= ' ' . sprintf( __( 'Track %1$s.' ), number_format_i18n( $track_number[ 0 ] ) );
			}

			if ( !empty( $meta[ 'genre' ] ) )
				$content .= ' ' . sprintf( __( 'Genre: %s.' ), $meta[ 'genre' ] );

			// use image exif/iptc data for title and caption defaults if possible
		} elseif ( $meta ) {
			if ( trim( $meta[ 'title' ] ) && !is_numeric( sanitize_title( $meta[ 'title' ] ) ) )
				$title	 = $meta[ 'title' ];
			if ( trim( $meta[ 'caption' ] ) )
				$content = $meta[ 'caption' ];
		}
		
		return compact( $title, $content );
	}
	
	/**
	 * Post a gallery or media Main comment on single page
	 * 
	 * @return type
	 */
	public function post_comment() {
		// Bail if not a POST action
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
			return;
		
		
		// Check the nonce
		check_admin_referer( 'post_update', '_wpnonce_post_update' );

		if ( ! is_user_logged_in() )
			exit( '-1' );
		
		$mpp_type = $_POST['mpp-type'];
		$mpp_id = $_POST['mpp-id'];
		
		if ( empty( $_POST['content'] ) )
			exit( '-1<div id="message" class="error"><p>' . __( 'Please enter some content to post.', 'buddypress' ) . '</p></div>' );

		$activity_id = 0;
		if ( empty( $_POST['object'] ) && bp_is_active( 'activity' ) ) {
			$activity_id = bp_activity_post_update( array( 'content' => $_POST['content'] ) );

		} elseif ( $_POST['object'] == 'groups'  ) {
			if ( ! empty( $_POST['item_id'] ) && bp_is_active( 'groups' ) )
				$activity_id = groups_post_update( array( 'content' => $_POST['content'], 'group_id' => $_POST['item_id'] ) );

		} else {
			$activity_id = apply_filters( 'bp_activity_custom_update', $_POST['object'], $_POST['item_id'], $_POST['content'] );
		}

		if ( empty( $activity_id ) )
			exit( '-1<div id="message" class="error"><p>' . __( 'There was a problem posting your update, please try again.', 'buddypress' ) . '</p></div>' );

		//if we have got activity id, let us add a meta key
		if( $mpp_type =='gallery' ){
			
			mpp_activity_update_gallery_id( $activity_id, $mpp_id );
			
		}elseif( $mpp_type == 'media' ){
			
			mpp_activity_update_media_id( $activity_id, $mpp_id );
		}
		
		 $activity = new BP_Activity_Activity( $activity_id );
		// $activity->component = buddypress()->mediapress->id;
		 $activity->type = 'mpp_media_upload';
		 $activity->save();
		
		if ( bp_has_activities ( 'include=' . $activity_id ) ) {
			while ( bp_activities() ) {
				bp_the_activity();
				bp_locate_template( array( 'activity/entry.php' ), true );
			}
		}

		exit;
	}
}

//initialize
MPP_Ajax_Helper::get_instance();
