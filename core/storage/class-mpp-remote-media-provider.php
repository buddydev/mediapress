<?php
/**
 * Remote Media Manage(Not implemented yet).
 *
 * @package mediapress.
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Acts as storage provide for remote media.
 */
class MPP_Remote_Media_Provider extends MPP_Storage_Manager {

	/**
	 * Get src.
	 *
	 * @param string $size media size(thumbnail, mid etc).
	 * @param int    $id media id.
	 *
	 * @return string
	 */
	public function get_src( $size = '', $id = null ) {
		// ID must be given.
		if ( ! $id ) {
			return '';
		}

		$url = mpp_get_media_meta( $id, '_mpp_remote_url' );

		if ( ! $size && $type_url = mpp_get_media_meta( $id, '_mpp_remote_url_' . $size, true ) ) {
			// update with current size url.
			$url = $type_url;
		}

		// we may have to change this implementation for the media.
		return $url;
	}


	/**
	 * Get absolute path.
	 *
	 * @param string $size media size(thumbnail,mid,large etc).
	 * @param int    $id media id.
	 *
	 * @return string
	 */
	public function get_path( $size = '', $id = null ) {
		// do we return the abs url or what?
		// let us say we don't have a local path available.
		return false;
	}

	/**
	 * Dummy.
	 *
	 * @param resource $file file.
	 * @param array    $args args.
	 *
	 * @return mixed
	 */
	public function upload( $file, $args ) {
		// TODO: Implement upload() method.
	}

	/**
	 * Extract media meta.
	 *
	 * @param array $uploaded_info upload info.
	 *
	 * @return array
	 */
	public function get_meta( $uploaded_info ) {

		$meta          = array();
		$meta['title'] = wp_basename( $uploaded_info['file'] );

		return $meta;
	}

	/**
	 * Generate metadata.
	 *
	 * @param int    $id media id.
	 * @param string $file file path.
	 *
	 * @return mixed|void
	 */
	public function generate_metadata( $id, $file ) {
		$meta       = array();
		$attachment = get_post( $id );

		$mime_type = get_post_mime_type( $attachment );

		$metadata = array();

		if ( preg_match( '!^image/!', $mime_type ) ) {
			// can not check for displayable image on remote media
			// && file_is_displayable_image( $file )
			// Make the file path relative to the upload dir.
			$metadata['file'] = $file;// storing abs path.
			// for remote media,
			// unless we start saving it locally, we can not have multiple versions.
			$metadata['sizes'] = array();

		} elseif ( preg_match( '#^video/#', $mime_type ) ) {

			$metadata = array();

		} elseif ( preg_match( '#^audio/#', $mime_type ) ) {

			$metadata = array();

		}

		// remove the blob of binary data from the array.
		if ( isset( $metadata['image']['data'] ) ) {
			unset( $metadata['image']['data'] );
		}

		return apply_filters( 'mpp_generate_metadata', $metadata, $id );
	}

	/**
	 * Move media to gallery.
	 *
	 * @param int $media_id media id.
	 * @param int $to_gallery_id where to move.
	 *
	 * @return bool
	 */
	public function move_media( $media_id, $to_gallery_id ) {
		// there is nothing to do here, no physical file to be moved.
		return true;
	}

	/**
	 * Get used space.
	 *
	 * @param string $component component name(e.g groups, members , sitewide etc).
	 * @param int    $component_id context based component id(group_id, user_id etc).
	 *
	 * @return float
	 */
	public function get_used_space( $component, $component_id ) {
		// for this storage manager, we do ot store locally, so the local storage space is 0.
		return 0;
	}


	/**
	 * Cleanup media. Is used after media is deleted from database.
	 *
	 * @param MPP_Media $media media object.
	 *
	 * @return mixed
	 */
	public function delete_media( $media ) {
		return true;
		// nothing to do here since the media is not stored locally
		// For meta, WordPress will delete meta automatically.
	}

	/**
	 * Called when a Gallery is being deleted
	 * Use it to cleanup any remnant of the gallery
	 *
	 * @param MPP_Gallery $gallery gallery that was deleted.
	 *
	 * @return bool
	 */
	public function delete_gallery( $gallery ) {
		return true;// no file exist.
	}


}