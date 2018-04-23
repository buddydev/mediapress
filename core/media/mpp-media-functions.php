<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if given post is a valid MediaPress media.
 *
 * Checks for post type + _is_mpp_media meta
 *
 * @param int $media_id media id.
 *
 * @return boolean
 */
function mpp_is_valid_media( $media_id ) {

	if ( mpp_get_media_meta( $media_id, '_mpp_is_mpp_media', true ) && ( get_post_type( $media_id ) == mpp_get_media_post_type() ) ) {
		return true;
	}

	return false;
}

/**
 * Get all media ids.
 *
 * @param array $args {
 *  Args.
 *
 * @type int     $gallery_id gallery id.
 * @type string  $component component.
 * @type int     $component_id component id.
 * @type int     $per_page how many per page.
 * @type string  $status media status.
 * @type boolean $nopaging whether to get all.
 * @type string  $fields which fields to fetch.
 *
 * }
 *
 * @return array
 */
function mpp_get_all_media_ids( $args = null ) {

	$component    = mpp_get_current_component();
	$component_id = mpp_get_current_component_id();

	$default = array(
		'gallery_id'   => mpp_get_current_gallery_id(),
		'component'    => $component,
		'component_id' => $component_id,
		'per_page'     => - 1,
		'status'       => mpp_get_accessible_statuses( $component, $component_id, get_current_user_id() ),
		'nopaging'     => true,
		'fields'       => 'ids',
	);

	$args = wp_parse_args( $args, $default );

	$ids = new MPP_Media_Query( $args );

	return $ids->get_ids();
}

/**
 * Get all media count for the give groups/users.
 *
 * @param array $args see mpp_get_object_count() for details.
 *
 * @see mpp_get_object_count()
 * @see mpp_get_user_media_count_by_component() for a better performance alternative.
 *
 * @return int
 */
function mpp_get_media_count( $args ) {

	$args['post_status'] = 'inherit';

	return mpp_get_object_count( $args, mpp_get_media_post_type() );
}

/**
 * Check if a media exists or not when the slug and the component, component_id is given.
 *
 * @param string $media_slug media slug.
 * @param string $component component name(groups, members etc).
 * @param int    $component_id component id( e.g group id or the uer id).
 *
 * @return bool|object
 */
function mpp_media_exists( $media_slug, $component, $component_id ) {

	if ( ! $media_slug ) {
		return false;
	}

	return mpp_post_exists( array(
		'component'    => $component,
		'component_id' => $component_id,
		'slug'         => $media_slug,
		'post_status'  => 'inherit',
		'post_type'    => mpp_get_media_post_type(),
	) );
}

/**
 * Add a New Media to the Gallery
 *
 * @param array $args {
 * Allowed parameters for the media.
 *
 * @type int $user_id user who is uploading.
 * @type int $gallery_id the gallery where we are uploading.
 * @type int $post_parent the media parent.
 * @type int $is_orphan Is media orphan. Possible values 0,1.
 * @type int $is_uploaded Was the media added via upload method? Possible values 0,1.
 * @type int $is_remote Is media located on another server? Possible values 0,1.
 * @type int $is_imported Is media imported? Possible values 0,1.
 * @type int $is_embedded Is media embedded? Possible values 0,1.
 * @type string $embed_url Url for the embedded media.
 * @type string $embed_html Embedded media content as html.
 * @type int $component_id Upload component id.
 * @type string $component Upload component.
 * @type string $context Upload context.
 * @type string $status media status(public,private etc).
 * @type string $type media type(photo,video,audio,doc etc).
 * @type string $storage_method media storage manager identifier.
 * @type string $mime_type media mime type.
 * @type string $title media title.
 * @type string $description media description.
 * @type int $sort_order media sort order in the gallery.
 * @type string $date_created date created.
 * @type string $date_updated date updated.
 * }
 *
 * @return int|boolean
 */
function mpp_add_media( $args ) {

	$default = array(
		'component'      => '',
		'component_id'   => 0,
		'context'        => '',
		'description'    => '',
		'date_created'   => '',
		'date_updated'   => '',
		'embed_html'     => '',
		'embed_url'      => '',
		'gallery_id'     => 0,
		'is_orphan'      => 0, // not orphan.
		'is_uploaded'    => 0,
		'is_remote'      => 0,
		'is_imported'    => 0,
		'is_embedded'    => 0,
		'mime_type'      => '',
		'post_parent'    => 0,
		'status'         => '',
		'sort_order'     => 0, // sort order.
		'storage_method' => '',
		'type'           => '',
		'title'          => '',
		'url'            => '',
		'user_id'        => get_current_user_id(),
	);

	$r = wp_parse_args( $args, $default );

	if ( ! $r['title'] || ! $r['user_id'] || ! $r['type'] ) {
		return false;
	}

	$post_data = array();

	$sort_order = $r['sort_order'];
	$gallery_id = $r['gallery_id'];

	// check if the gallery is sorted and the sorting order is not set explicitly
	// we update it.
	if ( ! $sort_order && mpp_is_gallery_sorted( $gallery_id ) ) {
		// current max sort order +1.
		$sort_order = (int) mpp_get_max_media_order( $gallery_id ) + 1;
	}

	// Construct the attachment array.
	$attachment = array_merge( array(
		'menu_order'     => $sort_order,
		'guid'           => $r['url'],
		'post_content'   => $r['description'],
		'post_title'     => $r['title'],
		'post_mime_type' => $r['mime_type'],
		'post_parent'    => $gallery_id,
	), $post_data );

	// This should never be set as it would then overwrite an existing attachment.
	if ( isset( $attachment['ID'] ) ) {
		unset( $attachment['ID'] );
	}

	if ( ! empty( $r['date_created'] ) ) {
		$attachment['post_date'] = $r['date_created'];
	}

	if ( ! empty( $r['date_updated'] ) ) {
		$attachment['post_modified'] = $r['date_updated'];
	}
	// Save the data.
	$id = wp_insert_attachment( $attachment, $r['src'], $gallery_id );

	if ( ! is_wp_error( $id ) ) {

		$component      = $r['component'];
		$component_id   = $r['component_id'];
		$type           = $r['type'];
		$status         = $r['status'];
		$context        = isset( $r['context'] ) ? $r['context'] : '';
		$storage_method = isset( $r['storage_method'] ) ? $r['storage_method'] : '';

		// set component.
		if ( $component ) {
			wp_set_object_terms( $id, mpp_underscore_it( $component ), mpp_get_component_taxname() );
		}

		// set _component_id meta key user_id/gallery_id/group id etc.
		if ( $component_id ) {
			mpp_update_media_meta( $id, '_mpp_component_id', $component_id );
		}
		// set upload context.
		if ( $context && 'activity' === $context ) {
			// only store context for activity uploaded media.
			mpp_update_media_meta( $id, '_mpp_context', $context );
		}

		// set media privacy.
		if ( $status ) {
			wp_set_object_terms( $id, mpp_underscore_it( $status ), mpp_get_status_taxname() );
		}
		// set media type internally as audio/video etc.
		if ( $type ) {
			wp_set_object_terms( $id, mpp_underscore_it( $type ), mpp_get_type_taxname() );
		}

		if ( $storage_method && 'local' !== $storage_method ) {
			// keep storage manager info if it is not default.
			mpp_update_media_meta( $id, '_mpp_storage_method', $storage_method );
		}

		// add all extras here.
		if ( $r['is_orphan'] ) {
			mpp_update_media_meta( $id, '_mpp_is_orphan', 1 );
		}
		// is_uploaded
		// is_remote
		// mark as mediapress media.
		mpp_update_media_meta( $id, '_mpp_is_mpp_media', 1 );

		wp_update_attachment_metadata( $id, mpp_generate_media_metadata( $id, $r['src'] ) );

		do_action( 'mpp_media_added', $id, $gallery_id );

		return $id;
	}

	return false; // there was an error.
}

/**
 * Update Details of the media.
 *
 * @param array $args {
 * Allowed parameters for the media.
 *
 * @type int $id media id.
 * @type int $user_id media uploader user id.
 * @type int $gallery_id media parent gallery id.
 * @type int $post_parent the media parent.
 * @type int $is_orphan Is media orphan. Possible values 0,1.
 * @type int $is_uploaded Was the media added via upload method? Possible values 0,1.
 * @type int $is_remote Is media located on another server? Possible values 0,1.
 * @type int $is_imported Is media imported? Possible values 0,1.
 * @type int $is_embedded Is media embedded? Possible values 0,1.
 * @type string $embed_url Url for the embedded media.
 * @type string $embed_html Embedded media content as html.
 * @type int $component_id Upload component id.
 * @type string $component Upload component.
 * @type string $context Upload context.
 * @type string $status media status(public,private etc).
 * @type string $type media type(photo,video,audio,doc etc).
 * @type string $storage_method media storage manager identifier.
 * @type string $mime_type media mime type.
 * @type string $title media title.
 * @type string $description media description.
 * @type int $sort_order media sort order in the gallery.
 * @type string $date_created date created.
 * @type string $date_updated date updated.
 * }
 *
 * @return int|boolean
 */
function mpp_update_media( $args ) {

	// updating media can not change the Id & SRC.
	// check for id.
	if ( ! isset( $args['id'] ) ) {
		return false;
	}

	$default = array(
		'component'      => '',
		'component_id'   => 0,
		'context'        => '',
		'date_created'   => '',
		'date_updated'   => '',
		'description'    => '',
		'embed_html'     => '',
		'embed_url'      => '',
		'gallery_id'     => 0,
		'is_orphan'      => 0,
		'is_uploaded'    => 0,
		'is_remote'      => 0,
		'is_imported'    => 0,
		'is_embedded'    => 0,
		'mime_type'      => '',
		'post_parent'    => 0,
		'status'         => '',
		'sort_order'     => 0,
		'storage_method' => '',
		'title'          => '',
		'type'           => '',
		'user_id'        => get_current_user_id(),
	);

	$r = wp_parse_args( $args, $default );

	$id = absint( $r['id'] );
	$gallery_id = absint( $r['gallery_id'] );

	$post_data = get_post( $id, ARRAY_A );

	if ( ! $gallery_id ) {
		$gallery_id = $post_data['post_parent'];
	}

	if ( $r['title'] ) {
		$post_data['post_title'] = $r['title'];
	}

	if ( $r['description'] ) {
		$post_data['post_content'] = $r['description'];
	}

	if ( $gallery_id ) {
		$post_data['post_parent'] = $gallery_id;
	}

	// check if the gallery is sorted and the sorting order is not set explicitly
	// we update it.
	$sort_order = $r['sort_order'];

	if ( ! $sort_order && ! $post_data['menu_order'] && mpp_is_gallery_sorted( $gallery_id ) ) {
		// current max sort order +1.
		$sort_order = (int) mpp_get_max_media_order( $gallery_id ) + 1;
	}

	if ( $sort_order ) {
		$post_data['menu_order'] = absint( $sort_order );
	}

	if ( ! empty( $r['date_created'] ) ) {
		$post_data['post_date'] = $r['date_created'];
	}

	if ( ! empty( $r['date_updated'] ) ) {
		$post_data['post_modified'] = $r['date_updated'];
	}
	// Save the data.
	$id = wp_insert_attachment( $post_data, false, $gallery_id );

	$component      = $r['component'];
	$component_id   = $r['component_id'];
	$type           = $r['type'];
	$status         = $r['status'];
	$context        = isset( $r['context'] ) ? $r['context'] : '';
	$storage_method = isset( $r['storage_method'] ) ? $r['storage_method'] : '';

	if ( ! is_wp_error( $id ) ) {
		// set component.
		if ( $component ) {
			wp_set_object_terms( $id, mpp_underscore_it( $component ), mpp_get_component_taxname() );
		}

		// set _component_id meta key user_id/gallery_id/group id etc.
		if ( $component_id ) {
			mpp_update_media_meta( $id, '_mpp_component_id', $component_id );
		}

		// set upload context.
		if ( $context && 'activity' === $context ) {
			// only store context for media uploaded from activity.
			mpp_update_media_meta( $id, '_mpp_context', $context );
		}

		// set media privacy.
		if ( $status ) {
			wp_set_object_terms( $id, mpp_underscore_it( $status ), mpp_get_status_taxname() );
		}
		// set media type internally as audio/video etc.
		if ( $type ) {
			wp_set_object_terms( $id, mpp_underscore_it( $type ), mpp_get_type_taxname() );
		}

		if ( $storage_method && 'local' !== $storage_method ) {
			// let us not waste extra entries on local storage.
			// Store storage info only if it is not the default local storage.
			mpp_update_media_meta( $id, '_mpp_storage_method', $storage_method );
		}

		//
		// add all extras here.
		if ( $r['is_orphan'] ) {
			mpp_update_media_meta( $id, '_mpp_is_orphan', 1 );
		} else {
			mpp_delete_media_meta( $id, '_mpp_is_orphan' );
		}

		do_action( 'mpp_media_updated', $id, $gallery_id );

		return $id;
	}

	return false; // there was an error.
}

/**
 * Remove a Media entry from gallery
 *
 * @see MPP_Deletion_Actions_Mapper::map_before_delete_post_action()
 * @see MPP_Deletion_Actions_Mapper::map_deleted_post() for the approprivte function
 *
 * Action flow
 *  wp_delete_attachment()
 *        -> do_action('delete_attachment', $post_id )
 *        -> MPP_Deletion_Actions_Mapper::map_before_delete_attachment()
 *        -> do_action ( 'mpp_before_media_delete', $gallery_id )
 *        -> cleanup gallery
 *        .........
 *        .........
 *
 *  wp_delete_attachment()
 *        -> do_action( 'deleted_post', $post_id )
 *        -> do_action( 'mpp_media_deleted', $gallery_id )
 *
 * @param int $media_id media to be deleted.
 *
 * @return mixed
 */
function mpp_delete_media( $media_id ) {
	return wp_delete_attachment( $media_id, true );
}

/**
 * Move a media ( may be from one gallery to another).
 *
 * @param int|MPP_Media   $media_id to be moved.
 * @param int|MPP_Gallery $gallery_id where the media will be moved.
 * @param array           $override parameters to override while updating media details.
 *
 * @return bool
 */
function mpp_move_media( $media_id, $gallery_id, $override = array() ) {

	$storage = mpp_get_storage_manager( $media_id );
	// first move files.
	if ( ! $storage->move_media( $media_id, $gallery_id ) ) {
		// there was a problem.
		return false;
	}

	$gallery = mpp_get_gallery( $gallery_id );
	// update media info.
	$details = wp_parse_args( array(

		'id'           => $media_id,
		'gallery_id'   => $gallery->id,
		'component'    => $gallery->component,
		'component_id' => $gallery->component_id,
		'is_orphan'    => 0,
	), $override );

	mpp_update_media( $details );

	return true;
}

/**
 * Import a WordPress attachment to a MediaPress gallery.
 *
 * @since 1.3.6
 *
 * @param int             $attachment_id to be imported.
 * @param int|MPP_Gallery $gallery_id where the media will be moved.
 * @param array           $override parameters to override while updating media details.
 *
 * @return WP_Error|int
 */
function mpp_import_attachment( $attachment_id, $gallery_id, $override = array() ) {

	$media_id = $attachment_id;

	if ( ! $media_id ) {
		return new WP_Error( 'invalid_media', __( 'Invalid attachment id given.', 'mediapress' ) );
	}

	$post = get_post( $media_id );

	if ( ! $post || 'attachment' !== $post->post_type ) {
		return new WP_Error( 'invalid_attachment', __( 'Attachment is not valid.', 'mediapress' ) );
	}

	$gallery = mpp_get_gallery( $gallery_id );

	if ( ! $gallery ) {
		return new WP_Error( 'gallery_not_exists', sprintf( __( 'Gallery Id %d does not exist or is not a valid gallery.', 'mediapress' ), $gallery_id ) );
	}

	$file = get_attached_file( $media_id );

	$type = mpp_get_media_type_from_extension( mpp_get_file_extension( $file ) );

	if ( ! $type ) {
		return new WP_Error( 'invalid_type', __( 'Invalid media type.', 'mediapress' ) );
	}

	if ( $type !== $gallery->type ) {
		return new WP_Error( 'invalid_gallery_type', __( 'Invalid file type for the gallery.', 'mediapress' ) );
	}

	$storage = mpp_local_storage();

	// Copy file to MediaPress dir.
	$info = $storage->import_file( $file, $gallery_id );

	if ( is_wp_error( $info ) ) {
		return $info;
	}

	$url       = $info['url'];
	$mime_type = $info['type'];
	$new_file  = $info['file'];

	// if we are here, let us delete all attachment sizes except the original.
	$storage->delete_all_sizes( $media_id );
	// all is good, delete original file(we have copied it before).
	@unlink( $file );

	// mark media as mpp media.
	mpp_update_media_meta( $media_id, '_mpp_is_mpp_media', 1 );

	// update media info.
	$details = wp_parse_args( array(
		'id'             => $media_id,
		'gallery_id'     => $gallery->id,
		'post_parent'    => $gallery->id,
		'user_id'        => $gallery->user_id,
		'status'         => $gallery->status,
		'component'      => $gallery->component,
		'component_id'   => $gallery->component_id,
		'context'        => 'gallery',
		'type'           => $type,
		'storage_method' => 'local',
		'sort_order'     => 0, // sort order.
		'is_orphan'      => 0,
		'is_uploaded'    => 1,
		'is_remote'      => 0,
		'is_imported'    => 1,
		'mime_type'      => $mime_type,
		'src'            => $new_file,
		'url'            => $url,
	), $override );

	mpp_update_media( $details );
	mpp_gallery_increment_media_count( $gallery_id );
	// set attached file.
	update_attached_file( $media_id, $new_file );
	// regenerate.
	wp_update_attachment_metadata( $media_id, mpp_generate_media_metadata( $media_id, $new_file ) );
	// enjoy.
	return $media_id;
}

/**
 * Import a local file from server to MediaPress gallery.
 *
 * It does not delete the original file.
 *
 * @since 1.3.6
 *
 * @param string $file absolute path of the file.
 * @param int    $gallery_id gallery where it should be imported.
 * @param array  $override parameters to override while updating media details.
 *
 * @return WP_Error|int
 */
function mpp_import_file( $file, $gallery_id, $override = array() ) {

	if ( ! is_file( $file ) || ! is_readable( $file ) ) {
		return new WP_Error( 'file_not_readable', sprintf( __( 'File %s is not readable.', 'mediapress' ), $file ) );
	}

	$gallery = mpp_get_gallery( $gallery_id );

	if ( ! $gallery ) {
		return new WP_Error( 'gallery_not_exists', sprintf( __( 'Gallery Id %d does not exist or is not a valid gallery.', 'mediapress' ), $gallery_id ) );
	}

	$type = mpp_get_media_type_from_extension( mpp_get_file_extension( $file ) );

	if ( ! $type ) {
		return new WP_Error( 'invalid_file_type', __( 'Invalid file type.', 'mediapress' ) );
	}

	if ( $type !== $gallery->type ) {
		return new WP_Error( 'invalid_gallery_type', __( 'Invalid file type for the gallery.', 'mediapress' ) );
	}

	$storage = mpp_local_storage();
	// copy.
	$info = $storage->import_file( $file, $gallery_id );

	if ( is_wp_error( $info ) ) {
		return $info;
	}

	$url       = $info['url'];
	$mime_type = $info['type'];
	$new_file  = $info['file'];

	// file was uploaded successfully.
	$title = wp_basename( $info['file'] );

	$title_parts = pathinfo( $title );
	$title       = trim( substr( $title, 0, - ( 1 + strlen( $title_parts['extension'] ) ) ) );

	$content = '';

	$meta = $storage->get_meta( $info );


	$title_desc = mpp_get_title_desc_from_meta( $type, $meta );

	if ( ! empty( $title_desc ) ) {

		if ( empty( $title ) && ! empty( $title_desc['title'] ) ) {
			$title = $title_desc['title'];
		}

		if ( empty( $content ) && ! empty( $title_desc['content'] ) ) {
			$content = $title_desc['content'];
		}
	}

	$media_data = array(
		'title'          => $title,
		'description'    => $content,
		'gallery_id'     => $gallery_id,
		'user_id'        => get_current_user_id(),
		'is_remote'      => false,
		'type'           => $type,
		'mime_type'      => $mime_type,
		'src'            => $new_file,
		'url'            => $url,
		'status'         => $gallery->status,
		'comment_status' => 'open',
		'storage_method' => mpp_get_storage_method(),
		'component_id'   => $gallery->component_id,
		'component'      => $gallery->component,
		'context'        => 'gallery',
		'is_orphan'      => 0,
	);
	// do the override.
	$media_data = wp_parse_args( $media_data, $override );
	$id = mpp_add_media( $media_data );

	if ( ! $id ) {
		return new WP_Error( 'media_not_added', __( 'Unable to import media', 'mediapress' ) );
	}

	mpp_gallery_increment_media_count( $gallery_id );

	return $id;
}

/**
 * Updates a given media Order
 *
 * @param int $media_id media id.
 * @param int $order_number sort order.
 *
 * @return boolean|int
 */
function mpp_update_media_order( $media_id, $order_number ) {

	global $wpdb;

	$query = $wpdb->prepare( "UPDATE {$wpdb->posts} SET menu_order =%d WHERE ID =%d", $order_number, $media_id );

	return $wpdb->query( $query );
}

/**
 * Get the order no. for the last sorted item
 *
 * @param int $gallery_id gallery id.
 *
 * @return int
 *
 * @todo improve name, suggestions are welcome
 */
function mpp_get_max_media_order( $gallery_id ) {

	global $wpdb;

	$query = $wpdb->prepare( "SELECT MAX(menu_order) as sort_order FROM {$wpdb->posts}  WHERE post_parent =%d", $gallery_id );

	return $wpdb->get_var( $query );
}

/**
 * Get media type from the given extension.
 *
 * @param string $ext file extension.
 *
 * @return boolean|string
 */
function mpp_get_media_type_from_extension( $ext ) {

	$ext = strtolower( $ext );

	$all_extensions = mpp_get_all_media_extensions();

	foreach ( $all_extensions as $type => $extensions ) {

		if ( in_array( $ext, $extensions ) ) {
			return $type;
		}
	}

	return false; // invalid type.
}

/**
 * Get file extension from file name.
 *
 * @param string $file_name file name.
 *
 * @return string
 */
function mpp_get_file_extension( $file_name ) {

	$parts = explode( '.', $file_name );

	return end( $parts );
}

/**
 * Is doc viewer enabled for the doc?
 *
 * @param int|MPP_Media $media media id or object.
 *
 * @return bool
 */
function mpp_is_doc_viewer_enabled( $media ) {
	$enabled = mpp_get_option( 'gdoc_viewer_enabled', 1 );
	// by default is is enabled.
	return apply_filters( 'mpp_doc_viewer_enabled', $enabled, $media );
}
/**
 * Does the viewer supports given type.
 *
 * @param string $viewer viewer identifier.
 * @param string $ext file extension.
 *
 * @return bool
 */
function mpp_doc_viewer_supports_file_type( $viewer = 'gdoc', $ext = '' ) {

	if ( ! $viewer || ! trim( $ext ) ) {
		return false;
	}

	$supported = array(); // supported types.
	// currently, we only check for the google doc.
	if ( 'gdoc' === $viewer ) {

		$supported = array(
			'ai',
			'doc',
			'docx',
			'eps',
			'pages',
			'pdf',
			'ppt',
			'pptx',
			'ps',
			'psd',
			'svg',
			'ttf',
			'xls',
			'xlsx',
			'xps',
		);
	}

	$supported = apply_filters( 'mpp_doc_viewer_supported_types', $supported, $viewer, $ext );

	return in_array( strtolower( $ext ), $supported, true );
}

/**
 * Is the document viewable?
 *
 * @param int|MPP_Media $media media id or object.
 *
 * @return bool
 */
function mpp_is_doc_viewable( $media ) {

	$viewable = false;
	$media    = mpp_get_media( $media );
	if ( $media && mpp_is_doc_viewer_enabled( $media ) && mpp_doc_viewer_supports_file_type( 'gdoc', mpp_get_file_extension( mpp_get_media_src( '', $media ) ) ) ) {
		$viewable = true;
	}

	return apply_filters( 'mpp_doc_viewable', $viewable, $media );
}

/**
 * Prepare Media for JSON
 *  this is a copy from send json for attachment, we will improve it in our 1.1 release
 *
 * @todo refactor.
 *
 * @param int|WP_Post $attachment post id or attachment post.
 *
 * @return array|null
 */
function mpp_media_to_json( $attachment ) {

	if ( ! $attachment = get_post( $attachment ) ) {
		return null;
	}

	if ( 'attachment' !== $attachment->post_type ) {
		return null;
	}

	// the attachment can be either a media or a cover
	// in case of media, if it is non photo, we need the thumb.url to point to the cover(or generated cover)
	// in case of cover, we don't care.
	$media = mpp_get_media( $attachment->ID );

	$meta = wp_get_attachment_metadata( $attachment->ID );

	if ( false !== strpos( $attachment->post_mime_type, '/' ) ) {
		list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
	} else {
		list( $type, $subtype ) = array( $attachment->post_mime_type, '' );
	}

	$attachment_url = wp_get_attachment_url( $attachment->ID );

	$response = array(
		'id'            => $media->id,
		'title'         => mpp_get_media_title( $media ),
		'filename'      => wp_basename( $attachment->guid ),
		'url'           => $attachment_url,
		'link'          => mpp_get_media_permalink( $media ),
		'alt'           => mpp_get_media_title( $media ),
		'author'        => $media->user_id,
		'description'   => $media->description,
		'caption'       => $media->excerpt,
		'name'          => $media->slug,
		'status'        => $media->status,
		'parent_id'     => $media->gallery_id,
		'date'          => strtotime( $attachment->post_date_gmt ) * 1000,
		'modified'      => strtotime( $attachment->post_modified_gmt ) * 1000,
		'menuOrder'     => $attachment->menu_order,
		'mime'          => $attachment->post_mime_type,
		'type'          => $media->type,
		'subtype'       => $subtype,
		'dateFormatted' => mysql2date( get_option( 'date_format' ), $attachment->post_date ),
		'meta'          => false,
		// 'thumbnail'		=> mpp_get_media_src('thumbnail', $media ).
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
		$bytes                             = filesize( $attached_file );
		$response['filesizeInBytes']       = $bytes;
		$response['filesizeHumanReadable'] = size_format( $bytes );
	}

	if ( $meta && 'image' === $type ) {
		$sizes = array();

		/** This filter is documented in wp-admin/includes/media.php */
		$possible_sizes = apply_filters( 'image_size_names_choose', array(
			'thumbnail' => __( 'Thumbnail' ),
			'medium'    => __( 'Medium' ),
			'large'     => __( 'Large' ),
			'full'      => __( 'Full Size' ),
		) );

		unset( $possible_sizes['full'] );

		// Loop through all potential sizes that may be chosen. Try to do this with some efficiency.
		// First: run the image_downsize filter. If it returns something, we can use its data.
		// If the filter does not return something, then image_downsize() is just an expensive
		// way to check the image metadata, which we do second.
		foreach ( $possible_sizes as $size => $label ) {

			/** This filter is documented in wp-includes/media.php */
			if ( $downsize = apply_filters( 'image_downsize', false, $attachment->ID, $size ) ) {

				if ( ! $downsize[3] ) {
					continue;
				}

				$sizes[ $size ] = array(
					'height'      => $downsize[2],
					'width'       => $downsize[1],
					'url'         => $downsize[0],
					'orientation' => $downsize[2] > $downsize[1] ? 'portrait' : 'landscape',
				);
			} elseif ( isset( $meta['sizes'][ $size ] ) ) {
				if ( ! isset( $base_url ) ) {
					$base_url = str_replace( wp_basename( $attachment_url ), '', $attachment_url );
				}

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
			$sizes['full']['height']      = $meta['height'];
			$sizes['full']['width']       = $meta['width'];
			$sizes['full']['orientation'] = $meta['height'] > $meta['width'] ? 'portrait' : 'landscape';
		}

		$response = array_merge( $response, array( 'sizes' => $sizes ), $sizes['full'] );
	} elseif ( $meta && 'video' === $type ) {
		if ( isset( $meta['width'] ) ) {
			$response['width'] = (int) $meta['width'];
		}

		if ( isset( $meta['height'] ) ) {
			$response['height'] = (int) $meta['height'];
		}
	}

	if ( $meta && ( 'audio' === $type || 'video' === $type ) ) {
		if ( isset( $meta['length_formatted'] ) ) {
			$response['fileLength'] = $meta['length_formatted'];
		}

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
			$url               = mpp_get_media_cover_src( 'thumbnail', $media->id );
			$width             = 48;
			$height            = 64;
			$response['image'] = compact( 'url', 'width', 'height' );
			$response['thumb'] = compact( 'url', 'width', 'height' );
		}
	}

	if ( ! in_array( $type, array( 'image', 'audio', 'video' ) ) ) {
		// inject thumbnail.
		$url               = mpp_get_media_cover_src( 'thumbnail', $media->id );
		$width             = 48;
		$height            = 64;
		$response['image'] = compact( 'url', 'width', 'height' );
		$response['thumb'] = compact( 'url', 'width', 'height' );
	}

	// do a final check here to see if the sizes array is set but we don't have a thumbnail.
	if ( ! empty( $response['sizes'] ) && empty( $response['sizes']['thumbnail'] ) ) {
		$thumb_dimension                = mpp_get_media_size( 'thumbnail' );
		$url                            = mpp_get_media_cover_src( 'thumbnail', $media->id );
		$width                          = $thumb_dimension['width'];
		$height                         = $thumb_dimension['height'];
		$response['sizes']['thumbnail'] = compact( 'url', 'width', 'height' );
		// $response['thumb'] = compact( 'url', 'width', 'height' );.
	}

	return apply_filters( 'mpp_prepare_media_for_js', $response, $attachment, $meta );
}

/**
 * Generate & Get wp compatible attachment meta data for the media
 *
 * @param int    $media_id media id.
 * @param string $src media abs path.
 *
 * @return array
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
 * Is it media delete action?
 *
 * @return boolean
 */
function mpp_is_media_delete() {
	return mpp_is_media_management() && mediapress()->is_edit_action( 'delete' );
}

/**
 * Check if user can comment on the given media.
 *
 * @param int $media_id media id.
 *
 * @return bool
 */
function mpp_media_user_can_comment( $media_id ) {

	// for now, just return true.
	return true;
	// in future, add an option in settings and also we can think of doing something for the user.
	if ( mpp_get_option( 'allow_media_comment' ) ) {
		return true;
	}

	return false;
}

/**
 * Record media activity.
 *
 * @param array $args media activity args.
 *
 * @return bool
 */
function mpp_media_record_activity( $args ) {

	$default = array(
		'id'       => false, // activity id.
		'media_id' => null,
		'action'   => '',
		'content'  => '',
		'type'     => '', // type of activity  'create_gallery, update_gallery, media_upload etc'.
	);

	$args = wp_parse_args( $args, $default );

	if ( ! $args['media_id'] ) {
		return false;
	}

	$media_id = absint( $args['media_id'] );

	$media = mpp_get_media( $media_id );


	if ( ! $media ) {
		return false;
	}

	$gallery_id = $media->gallery_id;
	$gallery    = mpp_get_gallery( $gallery_id );

	$status = $media->status;
	// when a media is public, make sure to check that the gallery is public too.
	if ( 'public' === $status ) {
		$status = mpp_get_gallery_status( $gallery );
	}
	// it is actually a gallery activity, isn't it?
	unset( $args['media_id'] );

	$args['status']     = $status;
	$args['gallery_id'] = $gallery->id;
	$args['media_ids']  = (array) $media_id;

	return mpp_record_activity( $args );
}

/**
 * Should we show media description on single media pages?
 *
 * @param MPP_Media $media media object.
 *
 * @return boolean
 */
function mpp_show_media_description( $media = null ) {

	$media = mpp_get_media( $media );

	$show = mpp_get_option( 'show_media_description' ); // under theme tab in admin panel.

	return apply_filters( 'mpp_show_media_description', $show, $media );
}

/**
 * Utility method to extract title/deesc from meta
 *
 * @param string $type type.
 * @param array  $meta file meta.
 *
 * @return array( 'title'=> Extracted title, 'content'=>  Extracted content )
 */
function mpp_get_title_desc_from_meta( $type, $meta ) {
	$title = $content = '';
	// match mime type.
	if ( preg_match( '#^audio#', $type ) ) {


		if ( ! empty( $meta['title'] ) ) {
			$title = $meta['title'];
		}

		if ( ! empty( $title ) ) {

			if ( ! empty( $meta['album'] ) && ! empty( $meta['artist'] ) ) {
				/* translators: 1: audio track title, 2: album title, 3: artist name */
				$content .= sprintf( __( '"%1$s" from %2$s by %3$s.', 'mediapress' ), $title, $meta['album'], $meta['artist'] );
			} elseif ( ! empty( $meta['album'] ) ) {
				/* translators: 1: audio track title, 2: album title */
				$content .= sprintf( __( '"%1$s" from %2$s.', 'mediapress' ), $title, $meta['album'] );
			} elseif ( ! empty( $meta['artist'] ) ) {
				/* translators: 1: audio track title, 2: artist name */
				$content .= sprintf( __( '"%1$s" by %2$s.', 'mediapress' ), $title, $meta['artist'] );
			} else {
				$content .= sprintf( __( '"%s".' ), $title );
			}
		} elseif ( ! empty( $meta['album'] ) ) {

			if ( ! empty( $meta['artist'] ) ) {
				/* translators: 1: audio album title, 2: artist name */
				$content .= sprintf( __( '%1$s by %2$s.', 'mediapress' ), $meta['album'], $meta['artist'] );
			} else {
				$content .= $meta['album'] . '.';
			}
		} elseif ( ! empty( $meta['artist'] ) ) {

			$content .= $meta['artist'] . '.';
		}

		if ( ! empty( $meta['year'] ) ) {
			$content .= ' ' . sprintf( __( 'Released: %d.', 'mediapress' ), $meta['year'] );
		}

		if ( ! empty( $meta['track_number'] ) ) {

			$track_number = explode( '/', $meta['track_number'] );

			if ( isset( $track_number[1] ) ) {
				$content .= ' ' . sprintf( __( 'Track %1$s of %2$s.', 'mediapress' ), number_format_i18n( $track_number[0] ), number_format_i18n( $track_number[1] ) );
			} else {
				$content .= ' ' . sprintf( __( 'Track %1$s.', 'mediapress' ), number_format_i18n( $track_number[0] ) );
			}
		}

		if ( ! empty( $meta['genre'] ) ) {
			$content .= ' ' . sprintf( __( 'Genre: %s.', 'mediapress' ), $meta['genre'] );
		}
		// use image exif/iptc data for title and caption defaults if possible.
	} elseif ( $meta ) {

		if ( trim( $meta['title'] ) && ! is_numeric( sanitize_title( $meta['title'] ) ) ) {
			$title = $meta['title'];
		}

		if ( trim( $meta['caption'] ) ) {
			$content = $meta['caption'];
		}
	}

	return compact( $title, $content );
}
