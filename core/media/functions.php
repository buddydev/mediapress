<?php


function mpp_get_all_media_ids( $args = null ) {
	
	$component		= mpp_get_current_component();
	$component_id	= mpp_get_current_component_id();
	
	$default = array(
		'gallery_id'		=> mpp_get_current_gallery_id(),
		'component'			=> $component,
		'component_id'		=> $component_id,
		'per_page'			=> -1,
		'status'			=> mpp_get_user_access_permissions( $component,$component_id, get_current_user_id() ),		
		'nopaging'			=> true,
		'fields'			=> 'ids'
	);
	
	$args = wp_parse_args( $args, $default );
	
	$ids = new MPP_Media_Query( $args );
	
	return $ids->get_ids();
	
}
/**
 * Check if a media exists or not
 * @param type $media_slug
 * @param type $component
 * @param type $component_id
 * @return type
 */
function mpp_media_exists( $media_slug, $component, $component_id ) {
	if ( !$media_slug )
		return false;

	return mpp_post_exists( array(
		'component'		 => $component,
		'component_id'	 => $component_id,
		'slug'			 => $media_slug,
		'post_status'	 => 'inherit',
		'post_type'		 => mpp_get_media_post_type()
	)
	);
}

/**
 * Add a New Media to teh Gallery
 * 
 * @param type $args
 * @return int|boolean
 */
function mpp_add_media( $args ) {


	$default = array(
		'user_id'		 => get_current_user_id(),
		'gallery_id'	 => 0,
		'post_parent'	 => 0,
		'is_orphan'		 => 0, //notorphan
		'is_uploaded'	 => 0,
		'is_remote'		 => 0,
		'is_imorted'	 => 0,
		'is_embedded'	 => 0,
		'embed_url'		 => '',
		'embed_html'	 => '',
		'component_id'	 => false,
		'component'		 => '',
		'context'		 => '',
		'status'		 => '',
		'type'			 => '',
		'storage_method' => '',
		'mime_type'		 => '',
		'description'	 => '',
		'sort_order'	 => 0, //sort order	
	);
	$args	 = wp_parse_args( $args, $default );
	extract( $args );

	//print_r($args );
	//return ;
	if ( !$title || !$user_id || !$type )
		return false;

	$post_data	 = array();
	
	//check if the gallery is sorted and the sorting order is not set explicitly
	//we update it
	if( !$sort_order && mpp_is_gallery_sorted( $gallery_id ) ){
		//current max sort order +1
		$sort_order = (int) mpp_get_max_media_order( $gallery_id ) + 1;
		
	}
	// Construct the attachment array
	$attachment	 = array_merge( array(
		'post_mime_type' => $mime_type,
		'guid'			 => $url,
		'post_parent'	 => $gallery_id,
		'post_title'	 => $title,
		'post_content'	 => $description,
		'menu_order'	 => $sort_order,
	), $post_data );

	// This should never be set as it would then overwrite an existing attachment.
	if ( isset( $attachment[ 'ID' ] ) )
		unset( $attachment[ 'ID' ] );

	// Save the data
	$id = wp_insert_attachment( $attachment, $src, $gallery_id );

	if ( !is_wp_error( $id ) ) {

		//set component
		if ( $component ) {

			wp_set_object_terms( $id, mpp_underscore_it( $component ), mpp_get_component_taxname() );
		}

		//set _component_id meta key user_id/gallery_id/group id etc
		if ( $component_id ) {
			mpp_update_media_meta( $id, '_mpp_component_id', $component_id );
		}
		//set upload context
		if ( $context ) {

			mpp_update_media_meta( $id, '_mpp_context', $context );
		}

		//set media privacy
		if ( $status ) {

			wp_set_object_terms( $id, mpp_underscore_it( $status ), mpp_get_status_taxname() );
		}
		//set media type internally as audio/video etc
		if ( $type ) {

			wp_set_object_terms( $id, mpp_underscore_it( $type ), mpp_get_type_taxname() );
		}
		//
		if ( $storage_method ) {

			mpp_update_media_meta( $id, '_mpp_storage_method', $storage_method );
		}
		//
		//add all extraz here

		if ( $is_orphan ) {
			mpp_update_media_meta( $id, '_mpp_is_orphan', $is_orphan );
		}
		//is_uploaded
		//is_remote
		//mark as mediapress media
		mpp_update_media_meta( $id, '_mpp_is_mpp_media', 1 );

		wp_update_attachment_metadata( $id, mpp_generate_media_metadata( $id, $src ) );


		

		do_action( 'mpp_media_added', $id, $gallery_id );
		return $id;
	}


	return false; // there was an error
}

function mpp_update_media( $args = null ) {

	//updating media can not change the Id & SRC, so 

	if ( !isset( $args[ 'id' ] ) )
		return false;

	$default = array(
		'user_id'		 => get_current_user_id(),
		'gallery_id'	 => false,
		'post_parent'	 => false,
		'is_orphan'		 => false,
		'is_uploaded'	 => '',
		'is_remote'		 => '',
		'is_imorted'	 => '',
		'is_embedded'	 => '',
		'embed_url'		 => '',
		'embed_html'	 => '',
		'component_id'	 => '',
		'component'		 => '',
		'context'		 => '',
		'status'		 => '',
		'type'			 => '',
		'storage_method' => '',
		'mime_type'		 => '',
		'description'	 => '',
		'sort_order'	 => 0,
	);
	$args	 = wp_parse_args( $args, $default );
	extract( $args );

	//print_r($args );
	//return ;
	if ( !$title )
		return false;

	$post_data = get_post( $id, ARRAY_A );

	if( ! $gallery_id )
		$gallery_id = $post_data['post_parent'];
	
	if ( $title )
		$post_data[ 'post_title' ] = $title;

	if ( $description )
		$post_data[ 'post_content' ] = $description;


	if ( $gallery_id )
		$post_data[ 'post_parent' ] = $gallery_id;

	//check if the gallery is sorted and the sorting order is not set explicitly
	//we update it
	if( !$sort_order && !$post_data['menu_order'] && mpp_is_gallery_sorted( $gallery_id )  ) {
		//current max sort order +1
		$sort_order = (int) mpp_get_max_media_order( $gallery_id ) + 1;
		
	}
	if( $sort_order )
		$post_data['menu_order'] = absin( $sort_order );
	// Save the data
	$id = wp_insert_attachment( $post_data, false, $gallery_id );

	if ( !is_wp_error( $id ) ) {

		//set component
		if ( $component ) {

			wp_set_object_terms( $id, mpp_underscore_it( $component ), mpp_get_component_taxname() );
		}

		//set _component_id meta key user_id/gallery_id/group id etc
		if ( $component_id ) {
			mpp_update_media_meta( $id, '_mpp_component_id', $component_id );
		}
		//set upload context
		if ( $context ) {

			mpp_update_media_meta( $id, '_mpp_context', $context );
		}

		//set media privacy
		if ( $status ) {

			wp_set_object_terms( $id, mpp_underscore_it( $status ), mpp_get_status_taxname() );
		}
		//set media type internally as audio/video etc
		if ( $type ) {

			wp_set_object_terms( $id, mpp_underscore_it( $type ), mpp_get_type_taxname() );
		}
		//
		if ( $storage_method ) {

			mpp_update_media_meta( $id, '_mpp_storage_method', $storage_method );
		}
		//
		//add all extraz here

		if ( $is_orphan ) {
			mpp_update_media_meta( $id, '_mpp_is_orphan', $is_orphan );
		}else{
			mpp_delete_media_meta( $id, '_mpp_is_orphan');
		}


		do_action( 'mpp_media_updated', $id, $gallery_id );

		return $id;
	}


	return false; // there was an error
}

/**
 * Remove a Media entry from gallery
 * 
 * @param type $media_id
 */
function mpp_delete_media( $media_id ) {
	
	
	global $wpdb;

	if ( !$media = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d", $media_id ) ) )
		return $post;

	if ( mpp_get_media_post_type() != $media->post_type )
		return false;

	//firs of all delete all media associated with this post

	$storage_manager = mpp_get_storage_manager( $media_id );

	$storage_manager->delete( $media_id );

	//now proceed to delete the post

	mpp_delete_media_meta( $media_id, '_wp_trash_meta_status' );
	mpp_delete_media_meta( $media_id, '_wp_trash_meta_time' );


	do_action( 'mpp_delete_media', $media_id );

	wp_delete_object_term_relationships( $media_id, array( 'category', 'post_tag' ) );
	wp_delete_object_term_relationships( $media_id, get_object_taxonomies( mpp_get_media_post_type() ) );

	delete_metadata( 'post', null, '_thumbnail_id', $media_id, true ); // delete all for any posts.
	//dele if it is set as cover
	delete_metadata( 'post', null, '_mpp_cover_id', $media_id, true ); // delete all for any posts.

	$comment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d", $media_id ) );
	
	foreach ( $comment_ids as $comment_id )
		wp_delete_comment( $comment_id, true );

	//if media has cover, delete the cover
	if( mpp_media_has_cover_image( $media_id ) ){
		
		mpp_delete_media( mpp_get_media_cover_id( $media_id ) );
	}
	//delete met
	$post_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d ", $media_id ) );
	
	foreach ( $post_meta_ids as $mid )
		delete_metadata_by_mid( 'post', $mid );


	$result = $wpdb->delete( $wpdb->posts, array( 'ID' => $media_id ) );
	if ( !$result ) {
		return false;
	}
		//decrease the media_count in gallery by 1

	mpp_gallery_decrement_media_count( $media->post_parent );
	
	//delete all activities related to this media
	mpp_media_delete_activities( $media_id );
	
	//delete all activity meta key where this media is associated
	
	mpp_media_delete_activity_meta( $media_id );

	clean_post_cache( $media );
	
	do_action( 'mpp_media_deleted', $media_id );



	return $media;
}

/**
 * Updates a given media Order
 * 
 * @global type $wpdb
 * @param type $media_id
 * @param type $order_number
 * @return type
 */
function mpp_update_media_order( $media_id, $order_number ) {

	global $wpdb;

	$query = $wpdb->prepare( "UPDATE {$wpdb->posts} SET menu_order =%d WHERE ID =%d", $order_number, $media_id );

	return $wpdb->query( $query );
}
/**
 * Get the order no. for the last sorted item
 * 
 * @global type $wpdb
 * @param type $gallery_id
 * @return type
 * @todo improve name, suggestions are welcome
 */
function mpp_get_max_media_order( $gallery_id ) {

	global $wpdb;

	$query = $wpdb->prepare( "SELECT MAX(menu_order) as sort_order FROM {$wpdb->posts}  WHERE post_parent =%d", $gallery_id );

	return $wpdb->get_var( $query );
}

function mpp_get_media_type_from_extension( $ext ) {

	$all_extensions = mpp_get_all_media_extensions();

	foreach ( $all_extensions as $type => $extensions ) {

		if ( in_array( $ext, $extensions ) )
			return $type;
	}

	return false; //invalid type
}

function mpp_get_file_extension( $file_name ) {

	return end( explode( '.', $file_name ) );
}
/**
 * Prepare Media for JSON
 * 
 * @param type $attachment
 * @return type
 */
function mpp_media_to_json( $attachment ) {
	
	if ( ! $attachment = get_post( $attachment ) )
		return;

	if ( 'attachment' != $attachment->post_type )
		return;

	//the attachment can be either a media or a cover
	
	//in case of media, if it is non photo, we need the thumb.url to point to the cover(or generated cover)
	
	//in case of cover, we don't care
	
	$media = mpp_get_media( $attachment->ID );
	
	$meta = wp_get_attachment_metadata( $attachment->ID );
	
	if ( false !== strpos( $attachment->post_mime_type, '/' ) )
		list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
	else
		list( $type, $subtype ) = array( $attachment->post_mime_type, '' );

	$attachment_url = wp_get_attachment_url( $attachment->ID );

	$response = array(
		'id'          => $media->id,
		'title'       => $media->title,
		'filename'    => wp_basename( $attachment->guid ),
		'url'         => $attachment_url,
		'link'        => mpp_get_media_permalink( $media),
		'alt'         => $media->title,
		'author'      => $media->user_id,
		'description' => $media->description,
		'caption'     => $media->excerpt,
		'name'        => $media->slug,
		'status'      => $media->status,
		'parent_id'	  => $media->gallery_id,
		'date'        => strtotime( $attachment->post_date_gmt ) * 1000,
		'modified'    => strtotime( $attachment->post_modified_gmt ) * 1000,
		'menuOrder'   => $attachment->menu_order,
		'mime'        => $attachment->post_mime_type,
		'type'        => $media->type,
		'subtype'     => $subtype,
		'dateFormatted' => mysql2date( get_option('date_format'), $attachment->post_date ),
		'meta'			=> false,
		//'thumbnail'		=> mpp_get_media_src('thumbnail', $media )
	);

	
	if ( $attachment->post_parent ) {
		$post_parent = get_post( $attachment->post_parent );
		$parent_type = get_post_type_object( $post_parent->post_type );
		if ( $parent_type && $parent_type->show_ui && current_user_can( 'edit_post', $attachment->post_parent ) ) {
			$response['uploadedToLink'] = get_edit_post_link( $attachment->post_parent, 'raw' );
		}
		$response['uploadedToTitle'] = $post_parent->post_title ? $post_parent->post_title : __( '(no title)' );
	}

	$attached_file = get_attached_file( $attachment->ID );
	
	if ( file_exists( $attached_file ) ) {
		$bytes = filesize( $attached_file );
		$response['filesizeInBytes'] = $bytes;
		$response['filesizeHumanReadable'] = size_format( $bytes );
	}

	
	if ( $meta && 'image' === $type ) {
		$sizes = array();

		/** This filter is documented in wp-admin/includes/media.php */
		$possible_sizes = apply_filters( 'image_size_names_choose', array(
			'thumbnail' => __('Thumbnail'),
			'medium'    => __('Medium'),
			'large'     => __('Large'),
			'full'      => __('Full Size'),
		) );
		unset( $possible_sizes['full'] );

		// Loop through all potential sizes that may be chosen. Try to do this with some efficiency.
		// First: run the image_downsize filter. If it returns something, we can use its data.
		// If the filter does not return something, then image_downsize() is just an expensive
		// way to check the image metadata, which we do second.
		foreach ( $possible_sizes as $size => $label ) {

			/** This filter is documented in wp-includes/media.php */
			if ( $downsize = apply_filters( 'image_downsize', false, $attachment->ID, $size ) ) {
				if ( ! $downsize[3] )
					continue;
				$sizes[ $size ] = array(
					'height'      => $downsize[2],
					'width'       => $downsize[1],
					'url'         => $downsize[0],
					'orientation' => $downsize[2] > $downsize[1] ? 'portrait' : 'landscape',
				);
			} elseif ( isset( $meta['sizes'][ $size ] ) ) {
				if ( ! isset( $base_url ) )
					$base_url = str_replace( wp_basename( $attachment_url ), '', $attachment_url );

				// Nothing from the filter, so consult image metadata if we have it.
				$size_meta = $meta['sizes'][ $size ];

				// We have the actual image size, but might need to further constrain it if content_width is narrower.
				// Thumbnail, medium, and full sizes are also checked against the site's height/width options.
				list( $width, $height ) = image_constrain_size_for_editor( $size_meta['width'], $size_meta['height'], $size, 'edit' );

				$sizes[ $size ] = array(
					'height'      => $height,
					'width'       => $width,
					'url'         => $base_url . $size_meta['file'],
					'orientation' => $height > $width ? 'portrait' : 'landscape',
				);
			}
		}

		$sizes['full'] = array( 'url' => $attachment_url );

		if ( isset( $meta['height'], $meta['width'] ) ) {
			$sizes['full']['height'] = $meta['height'];
			$sizes['full']['width'] = $meta['width'];
			$sizes['full']['orientation'] = $meta['height'] > $meta['width'] ? 'portrait' : 'landscape';
		}

		$response = array_merge( $response, array( 'sizes' => $sizes ), $sizes['full'] );
	} elseif ( $meta && 'video' === $type ) {
		if ( isset( $meta['width'] ) )
			$response['width'] = (int) $meta['width'];
		if ( isset( $meta['height'] ) )
			$response['height'] = (int) $meta['height'];
	}
	
	if ( $meta && ( 'audio' === $type || 'video' === $type ) ) {
		if ( isset( $meta['length_formatted'] ) )
			$response['fileLength'] = $meta['length_formatted'];

		$response['meta'] = array();
		foreach ( wp_get_attachment_id3_keys( $attachment, 'js' ) as $key => $label ) {
			$response['meta'][ $key ] = false;

			if ( ! empty( $meta[ $key ] ) ) {
				$response['meta'][ $key ] = $meta[ $key ];
			}
		}

		$id = mpp_get_media_cover_id( $attachment->ID );
	
		if ( ! empty( $id ) ) {
			list( $url, $width, $height ) = wp_get_attachment_image_src( $id, 'full' );
			$response['image'] = compact( 'url', 'width', 'height' );
			list( $url, $width, $height ) = wp_get_attachment_image_src( $id, 'thumbnail' );
			$response['thumb'] = compact( 'url', 'width', 'height' );
		} else {
			$url = mpp_get_media_cover_src ( 'thumbnail', $media->id );
			$width = 48;
			$height = 64;
			$response['image'] = compact( 'url', 'width', 'height' );
			$response['thumb'] = compact( 'url', 'width', 'height' );
		}
	}

	return apply_filters( 'mpp_prepare_media_for_js', $response, $attachment, $meta );


}

/**
 * Get wp compatible meta data for the media
 * @param type $media_id
 * @param type $src
 * @return type
 */
function mpp_generate_media_metadata( $media_id, $src ) {

	$storage = mpp_get_storage_manager( $media_id );

	return $storage->generate_metadata( $media_id, $src );
}

/**
 * Check if the current action is media editing/management
 * 
 * @return boolean
 */
function mpp_is_media_management() {

	return mediapress()->is_editing( 'media' ) && mediapress()->is_action( 'edit' );
}
/**
 * Is media delete action?
 * 
 * @return boolean
 */
function mpp_is_media_delete() {

	return mpp_is_media_management() && mediapress()->is_edit_action( 'delete' );
}

function mpp_media_user_can_comment( $media_id ){
	
	//for now, just return true
	return true;
	//in future, add an option in settings and aslo we can think of doing something for the user
	if( mpp_get_option( 'allow_media_comment') )
		return true;
	return false;
}
/**
 * Delete all activities for this media
 * 
 * @param type $media_id
 */
function mpp_media_delete_activities( $media_id ){
	
	return mpp_delete_activity_by_meta_key_value( '_mpp_media_id', $media_id );
		
		
}

//delete all activity meta entry for this media
//always call after deleting the media
function mpp_media_delete_activity_meta( $media_id ){
	//delete _mpp_media_id
	mpp_delete_activity_meta_by_key_value( '_mpp_media_id', $media_id );
	
	//delete _mpp_attached_media_ids
	
	mpp_delete_activity_meta_by_key_value( '_mpp_attached_media_ids', $media_id );
}
