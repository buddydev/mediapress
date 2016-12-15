<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

//the goal of this class to provide the remote media

class MPP_Remote_Media_Provider extends MPP_Storage_Manager {

	public function get_src( $type = '', $id = null ) {
		//ID must be given
		if ( ! $id ) {
			return '';
		}

		$url = mpp_get_media_meta( $id, '_mpp_remote_url' );

		if ( ! $type && $type_url = mpp_get_media_meta( $id, '_mpp_remote_url_'.$type, true ) ) {
			$url = $type_url;//update with current size url
		}
		//we may have to change this implementation for the media
		return $url;
	}


	public function get_path( $type = '', $id = null ) {
		//do we return the abs url or what?
		return false;//let us say we don't have a local path available
	}

	public function upload( $file, $args ) {
		// TODO: Implement upload() method.
	}

	/**
	 * It is used only to extract the title etc
	 * @param $uploaded_info
	 * @return  array
	 */
	public function get_meta( $uploaded_info ) {

		$meta = array();
		$meta['title'] = wp_basename( $uploaded_info['file'] );
		return $meta;
	}

	/**
	 * Return non empty generated
	 * @param $id
	 * @param $file
	 *
	 * @return array
	 *
	 */
	public function generate_metadata( $id, $file ) {
		$meta = array();
		$attachment = get_post( $id );

		$mime_type = get_post_mime_type( $attachment );

		$metadata	 = array();

		if ( preg_match( '!^image/!', $mime_type )  ) {
			//can not check for displayable image on remote media
			//&& file_is_displayable_image( $file )
			// Make the file path relative to the upload dir
			$metadata['file'] =  $file ;//storing abs path
			//for remote media, unless we start saving it locally, we can not have multiple versions
			$metadata['sizes'] = array();

		} elseif ( preg_match( '#^video/#', $mime_type ) ) {

			$metadata = array();// wp_read_video_metadata( $file );

		} elseif ( preg_match( '#^audio/#',  $mime_type) ) {

			$metadata = array();//wp_read_audio_metadata( $file );

		}

		// remove the blob of binary data from the array
		if ( isset( $metadata['image']['data'] ) ) {
			unset( $metadata['image']['data'] );
		}

		return apply_filters( 'mpp_generate_metadata', $metadata, $id );

	}

	public function move_media( $media_id, $to_gallery_id ) {
		return true;//there is nothing to do here, no physical file to be moved
	}

	public function get_used_space( $component, $component_id ) {
		return 0;
		//for this storage manager, we do ot store locally, so the local storage space is 0
	}


	public function delete_media( $media ) {
		return true;
		//nothing to do here since the media is not stored locally
		//For meta, WordPress will delete meta automatically
	}

	public function delete_gallery( $gallery ) {
		return true;//no file exist
	}



}