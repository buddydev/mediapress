<?php
/**
 * Base storage manager implementation.
 *
 * @package mediapress.
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Storage Manager base class.
 *
 * All the storage managers must implement this class
 */
abstract class MPP_Storage_Manager {
	/**
	 * Upload a file.
	 *
	 * @param resource $file uploaded file.
	 * @param array    $args extra args.
	 *
	 * @return mixed
	 */
	abstract public function upload( $file, $args );

	/**
	 * Save binary data
	 *
	 * @param string $file_name name of the file.
	 * @param mixed  $bits bits.
	 * @param array  $upload {
	 *  Upload path details.
	 *
	 *		@type string $path absolute path to the directory where file will be created.
	 *      @type string $url absolute url to the directory.
	 * }
	 *
	 * @return array|boolean
	 */
	abstract public function upload_bits( $file_name, $bits, $upload );

	/**
	 * Import a file to MediaPress gallery.
	 *
	 * @param string $file absolute file path.
	 * @param int    $gallery_id gallery id.
	 *
	 * @return int|WP_Error
	 */
	abstract function import_file( $file, $gallery_id );

	/**
	 * Import raw files from a url to the gallery.
	 *
	 * @param string $url raw url.
	 * @param int    $gallery_id gallery id.
	 *
	 * @return mixed
	 */
	abstract public function import_url( $url, $gallery_id );


	/**
	 * Get the media meta based on the given upload info.
	 *
	 * @param array $uploaded_info upload details.
	 *
	 * @return mixed
	 */
	abstract public function get_meta( $uploaded_info );

	/**
	 * Genetare metadata for the attachment.
	 *
	 * @param int    $id media id.
	 * @param string $file file name.
	 *
	 * @return mixed
	 */
	abstract public function generate_metadata( $id, $file );

	/**
	 * Cleanup media. Is used after media is deleted from database.
	 *
	 * @param MPP_Media $media media object.
	 *
	 * @return mixed
	 */
	abstract public function delete_media( $media );

	/**
	 * Move Media to the given gallery
	 *
	 * @param int $media_id media id.
	 * @param int $destination_gallery gallery id where we want to move.
	 *
	 * @return mixed
	 */
	abstract public function move_media( $media_id, $destination_gallery );

	/**
	 * Called when a Gallery is being deleted
	 * Use it to cleanup any remnant of the gallery
	 *
	 * @param MPP_Gallery $gallery gallery that was deleted.
	 */
	abstract public function delete_gallery( $gallery );

	/**
	 * Delete one or more files in the given directory.
	 *
	 * @param array|string $files files.
	 * @param string       $base_dir dir path.
	 */
	abstract function delete_files( $files, $base_dir );

	/**
	 * Delete all sizes except the original.
	 *
	 * @param int $media_id media id.
	 */
	abstract function delete_all_sizes( $media_id );

	/**
	 * Delete all sizes given by the array(see attachment meta['sizes'] for details).
	 *
	 * @param array  $sizes meta sizes.
	 * @param string $base_dir base dir path.
	 */
	abstract public function delete_sizes( $sizes, $base_dir );

	/**
	 * Get the used space for the given component
	 *
	 * @param string $component component name.
	 * @param int    $component_id component id.
	 */
	abstract public function get_used_space( $component, $component_id );

	/**
	 * Get the absolute url to a media file
	 * e.g http://example.com/wp-content/uploads/mediapress/members/1/xyz.jpg
	 *
	 * @param string $size media size(thumbnail,mid etc).
	 * @param int    $id media id.
	 */
	public abstract function get_src( $size = '', $id = null );

	/**
	 * Get the absolute file system path to the
	 *
	 * @param string $size media size(thumbnail,mid etc).
	 * @param int    $id media id.
	 */
	public abstract function get_path( $size = '', $id = null );

	/**
	 * An alias for self::get_src()
	 *
	 * @param string $size media size(thumbnail,mid etc).
	 * @param int    $id media id.
	 *
	 * @return string absolute url of the image
	 */
	public function get_url( $size, $id ) {
		return $this->get_src( $size, $id );
	}

	/**
	 * Assume that the server can handle upload
	 *
	 * Mainly used in case of local uploader for checking postmax size etc
	 * If you are implementing, return false if the upload data can be handled otherwise return false
	 *
	 * @return boolean
	 */
	public function can_handle() {
		return true;
	}
}
