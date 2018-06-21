<?php
/**
 * Local Storage Manager for MediaPress
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the local storage on the server
 *
 * This allows to store the files on the same server where WordPress is installed
 */
class MPP_Local_Storage extends MPP_Storage_Manager {

	/**
	 * Singleton instance
	 *
	 * @var MPP_Local_Storage
	 */
	private static $instance;

	/**
	 * Upload errors.
	 *
	 * @var array
	 */
	private $upload_errors = array();

	/**
	 * Constructor
	 */
	private function __construct() {
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MPP_Local_Storage
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the source url for the given size
	 *
	 * @param string $size names of the various image sizes(thumb, mid,etc).
	 * @param int    $id ID of the media.
	 *
	 * @return string source( absolute url) of the media
	 */
	public function get_src( $size = null, $id = null ) {
		// if object was given, let us find the id.
		if ( $id && is_a( $id, 'MPP_Media' ) ) {
			$id = mpp_get_media_id( $id );
		}

		// ID must be given.
		if ( ! $id ) {
			return '';
		}

		$url = wp_get_attachment_url( $id );

		// if type is not give, return original media url.
		if ( ! $size || 'original' === $size ) {
			return $url;
		}

		$meta = wp_get_attachment_metadata( $id );

		// if size info is not available, return original media src.
		if ( empty( $meta['sizes'][ $size ] ) || empty( $meta['sizes'][ $size ]['file'] ) ) {
			return $url; // return original size url/src.
		}

		$base_url = str_replace( wp_basename( $url ), '', $url );

		$src = $base_url . $meta['sizes'][ $size ]['file'];

		return $src;
	}

	/**
	 * Get the absolute path to a file ( file system path like /home/xyz/public_html/wp-content/uploads/mediapress/members/1/xyz)
	 *
	 * @param string $size size type(thumbnail,mid,large).
	 * @param int    $id media id.
	 *
	 * @return string
	 */
	public function get_path( $size = null, $id = null ) {
		// if object was given, let us find the id.
		if ( $id && is_a( $id, 'MPP_Media' ) ) {
			$id = mpp_get_media_id( $id );
		}

		// ID must be given.
		if ( ! $id ) {
			return '';
		}


		$upload_info = wp_upload_dir();

		$base_dir = $upload_info['basedir'];

		$meta = wp_get_attachment_metadata( $id );

		if ( empty( $meta ) || empty( $meta['sizes'] ) ) {
			// doc files most probably.
			$file = get_attached_file( $id );
			return $file;
		}

		$file = isset( $meta['file'] ) ? $meta['file'] : '';

		if ( ! $size ) {
			if ( $file ) {
				$file = path_join( $base_dir, $file );
			}

			return $file;
		}

		if ( empty( $meta['sizes'][ $size ]['file'] ) ) {
			return '';
		}

		$rel_dir_path = str_replace( wp_basename( $file ), '', $file );

		$dir_path = path_join( $base_dir, $rel_dir_path );

		$abs_path = path_join( $dir_path, $meta['sizes'][ $size ]['file'] );

		return $abs_path;
	}

	/**
	 * Uploads a file
	 *
	 * @param array $file PHP $_FIlES.
	 * @param array $args {
	 *  args.
	 * @type string $component
	 * @type int $component_id
	 * @type int $gallery_id
	 *
	 * }
	 *
	 * @return boolean
	 */
	public function upload( $file, $args ) {

		extract( $args );

		if ( empty( $file_id ) ) {
			return false;
		}

		// setup error.
		$this->setup_upload_errors( $args['component_id'] );

		$ms_flag = false;

		if ( is_multisite() && has_filter( 'upload_mimes', 'check_upload_mimes' ) ) {
			remove_filter( 'upload_mimes', 'check_upload_mimes' );
			$ms_flag = true;
		}

		// $_FILE['_mpp_file']
		$file = $file[ $file_id ];

		$unique_filename_callback = null;

		// include from wp-admin dir for media processing.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		if ( ! function_exists( 'mpp_handle_upload_error' ) ) {

			function mpp_handle_upload_error( $file, $message ) {
				return array( 'error' => $message );
			}
		}

		$upload_error_handler = 'mpp_handle_upload_error';

		$file = apply_filters( 'mpp_upload_prefilter', $file );

		// All tests are on by default. Most can be turned off by $overrides[{test_name}] = false.
		$test_form   = true;
		$test_size   = true;
		$test_upload = isset( $args['test_upload'] ) ? $args['test_upload'] : true;

		// If you override this, you must provide $ext and $type.
		$test_type = true;
		$mimes     = false;

		// Install user overrides. Did we mention that this voids your warranty?
		if ( ! empty( $overrides ) && is_array( $overrides ) ) {
			extract( $overrides, EXTR_OVERWRITE );
		}


		// A successful upload will pass this test. It makes no sense to override this one.
		if ( $file['error'] > 0 ) {
			return call_user_func( $upload_error_handler, $file, $this->upload_errors[ $file['error'] ] );
		}
		// A non-empty file will pass this test.
		if ( $test_size && ! ( $file['size'] > 0 ) ) {

			if ( is_multisite() ) {
				$error_msg = _x( 'File is empty. Please upload something more substantial.', 'upload error message', 'mediapress' );
			} else {
				$error_msg = _x( 'File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your php.ini or by post_max_size being defined as smaller than upload_max_filesize in php.ini.', 'upload error message', 'mediapress' );
			}

			return call_user_func( $upload_error_handler, $file, $error_msg );
		}

		// A properly uploaded file will pass this test. There should be no reason to override this one.
		if ( $test_upload && ! @ is_uploaded_file( $file['tmp_name'] ) ) {
			return call_user_func( $upload_error_handler, $file, _x( 'Specified file failed upload test.', 'upload error message', 'mediapress' ) );
		}

		// A correct MIME type will pass this test. Override $mimes or use the upload_mimes filter.
		if ( $test_type ) {
			$wp_filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $mimes );

			extract( $wp_filetype );

			// Check to see if wp_check_filetype_and_ext() determined the filename was incorrect.
			if ( ! empty( $proper_filename ) ) {
				$file['name'] = $proper_filename;
			}

			if ( ( empty( $type ) || empty( $ext ) ) && ! current_user_can( 'unfiltered_upload' ) ) {
				return call_user_func( $upload_error_handler, $file, _x( 'Sorry, this file type is not permitted for security reasons.', 'upload error message', 'mediapress' ) );
			}

			if ( ! $ext ) {
				$ext = ltrim( strrchr( $file['name'], '.' ), '.' );
			}

			if ( ! $type ) {
				$type = $file['type'];
			}
		} else {
			$type = '';
		}

		// A writable uploads dir will pass this test. Again, there's no point overriding this one.
		if ( ! ( ( $uploads = $this->get_upload_dir( $args ) ) && false === $uploads['error'] ) ) {

			return call_user_func( $upload_error_handler, $file, $uploads['error'] );
		}

		$filename = wp_unique_filename( $uploads['path'], $file['name'], $unique_filename_callback );

		// Move the file to the uploads dir.
		$new_file = $uploads['path'] . "/$filename";

		if ( ! file_exists( $uploads['path'] ) ) {
			wp_mkdir_p( $uploads['path'] );
		}

		$moved = $test_upload ? @ move_uploaded_file( $file['tmp_name'], $new_file ) : @rename( $file['tmp_name'], $new_file );

		if ( false === $moved ) {

			if ( 0 === strpos( $uploads['basedir'], ABSPATH ) ) {
				$error_path = str_replace( ABSPATH, '', $uploads['basedir'] ) . $uploads['subdir'];
			} else {
				$error_path = basename( $uploads['basedir'] ) . $uploads['subdir'];
			}

			return $upload_error_handler( $file, sprintf( _x( 'The uploaded file could not be moved to %s.', 'upload error message', 'mediapress' ), $error_path ) );
		}

		// Set correct file permissions.
		$stat  = stat( dirname( $new_file ) );
		$perms = $stat['mode'] & 0000666;
		@ chmod( $new_file, $perms );

		// Compute the URL.
		$url = $uploads['url'] . "/$filename";

		$this->invalidate_transient( $component, $component_id );
		// if required, fix rotation.
		$this->fix_rotation( $new_file );

		return apply_filters( 'mpp_handle_upload', array(
			'file' => $new_file,
			'url'  => $url,
			'type' => $type,
		), 'upload' );
	}

	/**
	 * Save binary data
	 *
	 * @param string $name name of the file.
	 * @param mixed  $bits bits.
	 * @param array  $upload {
	 *  Upload path details.
	 *
	 *      @type string $path absolute path to the directory where file will be created.
	 *      @type string $url absolute url to the directory.
	 * }
	 *
	 * @return array|boolean
	 */
	public function upload_bits( $name, $bits, $upload ) {

		if ( empty( $name ) ) {
			return array( 'error' => _x( 'Empty filename', 'upload error message', 'mediapress' ) );
		}

		$wp_filetype = wp_check_filetype( $name );

		if ( ! $wp_filetype['ext'] && ! current_user_can( 'unfiltered_upload' ) ) {
			return array( 'error' => _x( 'Invalid file type', 'upload error message', 'mediapress' ) );
		}

		if ( ! $upload['path'] ) {
			return false;
		}

		$upload_bits_error = apply_filters( 'mpp_upload_bits', array(
			'name' => $name,
			//'bits' => $bits,
			'path' => $upload['path'],
		) );

		if ( ! is_array( $upload_bits_error ) ) {
			$upload['error'] = $upload_bits_error;

			return $upload;
		}

		$filename = wp_unique_filename( $upload['path'], $name );

		$new_file = trailingslashit( $upload['path'] ) . "$filename";

		if ( ! wp_mkdir_p( dirname( $new_file ) ) ) {

			$message = sprintf( _x( 'Unable to create directory %s. Is its parent directory writable by the server?', 'upload error message', 'mediapress' ), dirname( $new_file ) );

			return array( 'error' => $message );
		}

		$ifp = @ fopen( $new_file, 'wb' );
		if ( ! $ifp ) {
			return array( 'error' => sprintf( _x( 'Could not write file %s', 'upload error message', 'mediapress' ), $new_file ) );
		}

		@fwrite( $ifp, $bits );

		fclose( $ifp );
		clearstatcache();

		// Set correct file permissions.
		$stat  = @ stat( dirname( $new_file ) );
		$perms = $stat['mode'] & 0007777;
		$perms = $perms & 0000666;
		@ chmod( $new_file, $perms );
		clearstatcache();

		// Compute the URL.
		$url = $upload['url'] . "/$filename";

		$this->fix_rotation( $new_file );

		return array( 'file' => $new_file, 'url' => $url, 'error' => false );
	}

	/**
	 * Delete one or more files inside the given directory.
	 *
	 * @param array|string $files files.
	 * @param string       $dir_path dir path.
	 */
	public function delete_files( $files, $dir_path ) {

		if ( ! is_array( $files ) ) {
			$files = (array) $files;
		}

		$dir_path = $this->get_sanitized_base_dir( $dir_path );

		if ( ! $dir_path ) {
			return;
		}

		foreach ( $files as $file ) {
			$file = ltrim( $file, '\\/' );
			if ( '.' === $file || '..' === $file ) {
				continue;
			}
			// If the file is relative, prepend upload dir.
			$file = path_join( $dir_path, $file );
			if ( validate_file( $file ) === 0 ) {
				@ unlink( $file );
			}
		}
	}

	/**
	 * Delete all media sizes except the original.
	 *
	 * @param int $media_id media id.
	 */
	public function delete_all_sizes( $media_id ) {
		$meta = wp_get_attachment_metadata( $media_id );
		$backup_sizes = get_post_meta( $media_id, '_wp_attachment_backup_sizes', true );
		$file = get_attached_file( $media_id );

		$abs_path = str_replace( basename( $file ), '', $file );

		// Remove intermediate and backup images if there are any.
		if ( isset( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
			$this->delete_sizes( $meta['sizes'], $abs_path );
			$meta['sizes'] = array();
			wp_update_attachment_metadata( $media_id, $meta );
		}

		if ( $backup_sizes && is_array( $backup_sizes ) ) {
			$this->delete_sizes( $backup_sizes, $abs_path );
			delete_post_meta( $media_id, '_wp_attachment_backup_sizes' );
		}
	}

	/**
	 * Delete all sizes.
	 *
	 * @param array  $sizes meta sizes.
	 * @param string $base_dir base dir path.
	 */
	public function delete_sizes( $sizes, $base_dir ) {
		$base_dir = $this->get_sanitized_base_dir( $base_dir );
		if ( ! $base_dir ) {
			return;
		}

		$base_dir = rtrim( $base_dir, '\\/' );

		foreach ( $sizes as $size => $sizeinfo ) {
			$intermediate_file = $base_dir . '/' . $sizeinfo['file'];
			if ( validate_file( $intermediate_file ) === 0 ) {
				@ unlink( $intermediate_file );
			}
		}
	}

	/**
	 * Get sanitized absolute path. Must be inside the upload directory.
	 *
	 * You should still call validate_file() to check for directory traversal.
	 *
	 * @param string $dir relative or absolute path.
	 *
	 * @return string
	 */
	private function get_sanitized_base_dir( $dir ) {
		$uploads = wp_get_upload_dir();
		// relative path is given.
		if ( 0 !== strpos( $dir, '/' ) ) {
			return path_join( $uploads['basedir'], $dir );
		} elseif ( 0 === strpos( $dir, $uploads['basedir'] ) ) {
			// if the file starts with / or \ and is inside the uploads dir, it's valid.
			return $dir;
		}
		// in all other cases, we do not consider it valid.
		return '';
	}

	/**
	 * Extract meta from uploaded data
	 *
	 * @param array $uploaded uploaded file info.
	 *
	 * @return array
	 */
	public function get_meta( $uploaded ) {

		$meta = array();

		$url  = $uploaded['url'];
		$type = $uploaded['type'];
		$file = $uploaded['file'];

		// match mime type.
		if ( preg_match( '#^audio#', $type ) ) {
			$meta = wp_read_audio_metadata( $file );
			// use image exif/iptc data for title and caption defaults if possible.
		} else {
			$meta = @wp_read_image_metadata( $file );
		}

		return $meta;
	}

	/**
	 * Generate meta data for the media
	 *
	 * @since 1.0.0
	 *
	 * @access  private
	 *
	 * @param int    $attachment_id Media ID  to process.
	 * @param string $file File path of the Attached image.
	 *
	 * @return mixed Metadata for attachment.
	 */
	public function generate_metadata( $attachment_id, $file ) {

		$attachment = get_post( $attachment_id );

		$mime_type = get_post_mime_type( $attachment );

		$metadata = array();

		if ( preg_match( '!^image/!', $mime_type ) && file_is_displayable_image( $file ) ) {

			$imagesize = getimagesize( $file );

			$metadata['width']  = $imagesize[0];
			$metadata['height'] = $imagesize[1];

			// Make the file path relative to the upload dir.
			$metadata['file'] = _wp_relative_upload_path( $file );

			// get the registered media sizes.
			$sizes = mpp_get_media_sizes();

			$sizes = apply_filters( 'mpp_intermediate_image_sizes', $sizes, $attachment_id );

			if ( $sizes ) {
				$editor = wp_get_image_editor( $file );

				if ( ! is_wp_error( $editor ) ) {
					$metadata['sizes'] = $editor->multi_resize( $sizes );
				}
			} else {
				$metadata['sizes'] = array();
			}

			// fetch additional metadata from exif/iptc.
			$image_meta = wp_read_image_metadata( $file );

			if ( $image_meta ) {
				$metadata['image_meta'] = $image_meta;
			}
		} elseif ( preg_match( '#^video/#', $mime_type ) ) {
			$metadata = wp_read_video_metadata( $file );
		} elseif ( preg_match( '#^audio/#', $mime_type ) ) {
			$metadata = wp_read_audio_metadata( $file );
		}

		$dir_path = trailingslashit( dirname( $file ) ) . 'covers';
		$url      = wp_get_attachment_url( $attachment_id );
		$base_url = str_replace( wp_basename( $url ), '', $url );

		// processing for audio/video cover.
		if ( ! empty( $metadata['image']['data'] ) ) {
			$ext = '.jpg';

			switch ( $metadata['image']['mime'] ) {
				case 'image/gif':
					$ext = '.gif';
					break;
				case 'image/png':
					$ext = '.png';
					break;
			}
			$basename = str_replace( '.', '-', basename( $file ) ) . '-image' . $ext;
			$uploaded = $this->upload_bits( $basename, $metadata['image']['data'], array(
				'path' => $dir_path,
				'url'  => $base_url,
			) );

			if ( false === $uploaded['error'] ) {
				$attachment        = array(
					'post_mime_type' => $metadata['image']['mime'],
					'post_type'      => 'attachment',
					'post_content'   => '',
				);
				$sub_attachment_id = wp_insert_attachment( $attachment, $uploaded['file'] );
				$attach_data       = $this->generate_metadata( $sub_attachment_id, $uploaded['file'] );

				wp_update_attachment_metadata( $sub_attachment_id, $attach_data );
				// if the option is set to set post thumbnail.
				if ( mpp_get_option( 'set_post_thumbnail' ) ) {
					mpp_update_media_meta( $attachment_id, '_thumbnail_id', $sub_attachment_id );
				}
				// set the cover id.
				mpp_update_media_cover_id( $attachment_id, $sub_attachment_id );
			}
		}

		// remove the blob of binary data from the array.
		if ( isset( $metadata['image']['data'] ) ) {
			unset( $metadata['image']['data'] );
		}

		return apply_filters( 'mpp_generate_metadata', $metadata, $attachment_id );
	}

	/**
	 * Delete all the files associated with a Media
	 * For local storage, WordPress handles deleting, we simply invalidate the transiesnt
	 *
	 * @param int $media_id numeric media/attachment id.
	 *
	 * @return boolean
	 */
	public function delete_media( $media_id ) {

		$media = mpp_get_media( $media_id );
		$this->invalidate_transient( $media->component, $media->component_id );

		return true;
	}

	/**
	 * Move media to the given gallery
	 *
	 * @param int|MPP_Media   $media_id the media to be moved.
	 * @param int|MPP_Gallery $to_gallery_id Destination gallery id.
	 *
	 * @return  boolean status
	 */
	public function move_media( $media_id, $to_gallery_id ) {

		$media = mpp_get_media( $media_id );

		if ( $media->gallery_id == $to_gallery_id ) {
			// no need to move.
			return true;
		}

		$from_gallery = mpp_get_gallery( $media->gallery_id );
		$to_gallery   = mpp_get_gallery( $to_gallery_id );

		// both the source and destination must exists.
		if ( ! $from_gallery || ! $to_gallery ) {
			return false;
		}

		// source.
		$src_dir = $this->get_upload_dir( array(
			'component'    => $from_gallery->component,
			'component_id' => $from_gallery->component_id,
			'gallery_id'   => $from_gallery->id,
		) );


		// destination.
		$dest_dir = $this->get_upload_dir( array(
			'component'    => $to_gallery->component,
			'component_id' => $to_gallery->component_id,
			'gallery_id'   => $to_gallery->id,
		) );

		// if destination directory does not exists, try creating it.
		if ( ! file_exists( $dest_dir['path'] ) ) {
			wp_mkdir_p( $dest_dir['path'] );
		}

		$attachment_meta = wp_get_attachment_metadata( $media->id );

		$attached_file = mpp_get_media_meta( $media_id, '_wp_attached_file', true );

		$original_filename = $new_filename = '';

		// move original file.
		if ( $attached_file ) {

			$original_filename = wp_basename( $attached_file );

			if ( ! is_readable( $src_dir['path'] . '/' . $original_filename ) ) {
				return false;
			}

			$new_filename = wp_unique_filename( $dest_dir['path'], $original_filename );

			$new_file = $dest_dir['path'] . '/' . $new_filename;

			@rename( $src_dir['path'] . '/' . $original_filename, $new_file );

			$rel_path = _wp_relative_upload_path( $new_file );
			mpp_update_media_meta( $media_id, '_wp_attached_file', $rel_path );
			$attachment_meta['file'] = $rel_path;
		}

		$sizes = $attachment_meta['sizes'];

		foreach ( $sizes as $size => $fileinfo ) {

			if ( empty( $fileinfo['file'] ) ) {
				continue;
			}
			// this file is same as original.
			// since original is already moved, we only update the path.
			if ( $fileinfo['file'] === $original_filename ) {
				$sizes[ $size ]['file'] = $new_filename;
				continue;
			}

			$old_file = $fileinfo['file'];
			$new_name = wp_unique_filename( $dest_dir['path'], $old_file );
			$new_file = $dest_dir['path'] . '/' . $new_name;

			@rename( $src_dir['path'] . '/' . $old_file, $new_file );
			// update meta.
			$sizes[ $size ]['file'] = wp_basename( $new_file );
		}

		$attachment_meta['sizes'] = $sizes;

		wp_update_attachment_metadata( $media->id, $attachment_meta );
		// invalidate transients to allow recalculating space later.
		$this->invalidate_transient( $from_gallery->component, $from_gallery->component_id );
		$this->invalidate_transient( $to_gallery->component, $to_gallery->component_id );

		return true;
	}

	/**
	 * Import a file to the gallery.
	 *
	 * @param string $file absolute file path.
	 * @param int    $gallery_id gallery id.
	 *
	 * @return WP_Error|array
	 */
	public function import_file( $file, $gallery_id ) {

		if ( ! is_file( $file ) || ! is_readable( $file ) ) {
			return new WP_Error( 'file_not_readable', sprintf( __( 'File %s is not readable.', 'mediapress' ), $file ) );
		}

		$gallery   = mpp_get_gallery( $gallery_id );

		// destination gallery must exists.
		if ( ! $gallery ) {
			return new WP_Error( 'gallery_not_exists', sprintf( __( 'Gallery id %d does not exist or is not a valid gallery.', 'mediapress' ), $gallery_id ) );
		}
		// include from wp-admin dir for media processing.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';


		// destination.
		$uploads = $this->get_upload_dir( array(
			'component'    => $gallery->component,
			'component_id' => $gallery->component_id,
			'gallery_id'   => $gallery->id,
		) );

		// if destination directory does not exists, try creating it.
		if ( ! file_exists( $uploads['path'] ) ) {
			wp_mkdir_p( $uploads['path'] );
		}


		$original_filename = wp_basename( $file );

		$new_filename = wp_unique_filename( $uploads['path'], $original_filename );

		$new_file = $uploads['path'] . '/' . $new_filename;

		if ( false === copy( $file, $new_file ) ) {
			return new WP_Error( 'file_not_copied', sprintf( __( 'Unable to copy file from %1$s to %2$s. Please check directory permission.', 'mediapress' ), $file, $new_file ) );
		}

		// Set correct file permissions.
		$stat  = stat( dirname( $new_file ) );
		$perms = $stat['mode'] & 0000666;
		@ chmod( $new_file, $perms );

		$this->invalidate_transient( $gallery->component, $gallery->component_id );

		// Compute the URL.
		$url = $uploads['url'] . "/$new_filename";

		$type = mime_content_type( $new_file );

		return apply_filters( 'mpp_handle_upload', array(
			'file' => $new_file,
			'url'  => $url,
			'type' => $type,
		), 'import' );
	}

	/**
	 * Import from remote to a gallery.
	 *
	 * @param string $url raw media url.
	 * @param int    $gallery_id gallery id.
	 *
	 * @return WP_Error|array|bool
	 */
	public function import_url( $url, $gallery_id ) {

		// include from wp-admin dir for media processing.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = download_url( $url );

		if ( is_wp_error( $tmp ) ) {
			return new WP_Error( 'unable_to_download', __( 'There was a problem downloading media.', 'mediapress' ) );
		}

		$file_array['name'] = wp_basename( $url );
		$file_array['tmp_name'] = $tmp;

		// on error. unlink it and return.
		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );

			return new WP_Error( 'unable_to_download_2', __( 'There was a problem downloading media.', 'mediapress' ) );
		}

		$file_array['size'] = filesize( $tmp );
		$gallery = mpp_get_gallery( $gallery_id );
		$files = array();
		$files['_mpp_file'] = $file_array;

		return $this->upload( $files, array(
			'file_id'      => '_mpp_file',
			'gallery_id'   => $gallery->id,
			'component'    => $gallery->component,
			'component_id' => $gallery->component_id,
			'test_upload' => false,
		) );
	}

	/**
	 * Called after gallery deletion
	 *
	 * @param int $gallery_id numeric gallery id.
	 *
	 * @return boolean
	 */
	public function delete_gallery( $gallery_id ) {

		$gallery = mpp_get_gallery( $gallery_id );

		$dir = $this->get_component_base_dir( $gallery->component, $gallery->component_id );

		$dir = untrailingslashit( wp_normalize_path( $dir ) ) . '/' . $gallery->id . '/';

		if ( $dir ) {
			mpp_recursive_delete_dir( $dir );
		}

		$this->invalidate_transient( $gallery->component, $gallery->component_id );

		return true;
	}


	/**
	 * Calculate the Used space by a component
	 *
	 * @see mpp_get_used_space
	 *
	 * @access private do not call it directly, use mpp_get_used_space instead
	 *
	 * @param string $component component name e.g 'groups', 'members', 'sitewide'.
	 * @param int    $component_id numeric component id(group id, user id).
	 *
	 * @return int
	 */
	public function get_used_space( $component, $component_id ) {

		// let us check for the transient as space calculation is bad every time.
		$key = "mpp_space_used_by_{$component}_{$component_id}"; // transient key.

		$used_space = get_transient( $key );

		if ( ! $used_space ) {
			// base gallery directory for owner.
			$dir_name = trailingslashit( $this->get_component_base_dir( $component, $component_id ) );

			if ( ! is_dir( $dir_name ) || ! is_readable( $dir_name ) ) {
				return 0; // we don't know the usage or no usage.
			}

			$dir  = dir( $dir_name );
			$size = 0;

			while ( $file = $dir->read() ) {

				if ( $file !== '.' && $file !== '..' ) {

					if ( is_dir( $dir_name . $file ) ) {
						$size += self::get_dirsize( $dir_name . $file );
					} else {
						$size += filesize( $dir_name . $file );
					}
				}
			}

			$dir->close();
			set_transient( $key, $size, DAY_IN_SECONDS );

			$used_space = $size;
		}

		$used_space = $used_space / 1024 / 1024;

		return $used_space;
	}

	/**
	 * Get errors. Not implemented.
	 */
	public function get_errors() {
	}

	/**
	 * Server can handle upload?
	 *
	 * @return boolean
	 */
	public function can_handle() {

		if ( $_FILES['_mpp_file']['size'] < wp_max_upload_size() ) {
			return true;
		}

		return false;
	}

	// ******************************************************************************
	// Utility methods below
	//
	// ******************************************************************************
	/**
	 * Calculate the upload path for our files
	 *
	 * It uses wp_upload_dir and appends our path to it, the returned result is similar to what wp_upload_dir provides
	 *
	 * @since 1.0.0
	 * @see wp_upload_dir
	 *
	 * @param array $args {
	 * args.
	 * @type string $component the associated component for the media ( groups|members etc)
	 *
	 * @type int $component_id The associated component object id( group id or user id depending on the $component )
	 *
	 * @type int $gallery_id The parent gallery id
	 *
	 * @type boolean $is_cover Is it cover upload?     if true, the appends /covers
	 *                            at the end of the path
	 *
	 * }
	 * @return string
	 */
	public function get_upload_dir( $args ) {

		$default = array(
			'component'    => '',
			'component_id' => 0,
			'gallery_id'   => 0,
			'is_cover'     => false,
		);

		$args = wp_parse_args( $args, $default );

		// extract( $args );.
		$component = $args['component'];
		$component_id = $args['component_id'];

		$uploads = wp_upload_dir();

		// if a component is not given or the component id is not given, do not alter the upload path.
		if ( ! $component || ! $component_id ) {
			return $uploads;
		}

		$uploads['path'] = str_replace( $uploads['subdir'], '', $uploads['path'] );
		$uploads['url']  = str_replace( $uploads['subdir'], '', $uploads['url'] );

		// now reset upload/sub dir, we have hardcoded mediapress for now, if you want it to be changed, please create a ticket.
		$uploads['subdir'] = "/mediapress/{$component}/{$component_id}";

		// make folder like /mediapress/{groups|members}/{user_id or group_id}.
		if ( $args['gallery_id'] ) {
			$uploads['subdir'] = $uploads['subdir'] . "/{$args['gallery_id']}";
		}

		if ( $args['is_cover'] ) {
			$uploads['subdir'] = $uploads['subdir'] . '/covers';
		}

		$uploads['path'] = untrailingslashit( $uploads['path'] ) . $uploads['subdir'];
		$uploads['url']  = untrailingslashit( $uploads['url'] ) . $uploads['subdir'];

		return $uploads;
	}

	/**
	 * Get the path to the base dir of a component
	 *
	 * @access private
	 *
	 * @param string $component component name(groups|members|sitewide etc).
	 * @param int    $component_id numeric omponent id(group id|user_id).
	 *
	 * @return string
	 */
	public function get_component_base_dir( $component, $component_id ) {

		$uploads = $this->get_upload_dir( array( 'component' => $component, 'component_id' => $component_id ) );

		return $uploads['path'];
	}

	/**
	 * Setup Possible upload errors
	 *
	 * @param string $component Component name(groups|members etc).
	 */
	public function setup_upload_errors( $component ) {

		$allowed_size = mpp_get_allowed_space( $component );

		$this->upload_errors = array(
			UPLOAD_ERR_OK         => _x( 'Great! the file uploaded successfully!', 'upload error message', 'mediapress' ),
			UPLOAD_ERR_INI_SIZE   => sprintf( _x( 'Your file size was bigger than the maximum allowed file size of: %s', 'upload error message', 'mediapress' ), $allowed_size ),
			UPLOAD_ERR_FORM_SIZE  => sprintf( _x( 'Your file was bigger than the maximum allowed file size of: %s', 'upload error message', 'mediapress' ), $allowed_size ),
			UPLOAD_ERR_PARTIAL    => _x( 'The uploaded file was only partially uploaded', 'upload error message', 'mediapress' ),
			UPLOAD_ERR_NO_FILE    => _x( 'No file was uploaded', 'upload error message', 'mediapress' ),
			UPLOAD_ERR_NO_TMP_DIR => _x( 'Missing a temporary folder.', 'upload error message', 'mediapress' ),
		);
	}

	/**
	 * Delete transients.
	 *
	 * @param string $component Component type(members|groups|sitewide).
	 * @param int    $component_id numeric component id(user_id, group_id).
	 */
	private function invalidate_transient( $component, $component_id = null ) {

		if ( ! $component || ! $component_id ) {
			return;
		}

		$key = "mpp_space_used_by_{$component}_{$component_id}"; // transient key.

		delete_transient( $key );
		delete_transient( 'dirsize_cache' );
	}

	/**
	 * Fix the image rotation issues on mobile devices
	 *
	 * @param string $file absolute path to the file.
	 *
	 * @return string
	 */
	private function fix_rotation( $file ) {
		// exif support not available.
		if ( ! function_exists( 'exif_read_data' ) ) {
			return $file;
		}

		if ( ! $this->is_valid_image_file( $file ) ) {
			return $file;
		}

		$exif = @exif_read_data( $file );

		$orientation = isset( $exif['Orientation'] ) ? $exif['Orientation'] : 0;

		if ( ! $orientation ) {
			return $file;
		}

		$rotate          = false;
		$horizontal_flip = false;
		$vertrical_flip  = false;

		switch ( $orientation ) {

			case 2:
				$horizontal_flip = true;
				break;

			case 3:
				$rotate = 180;
				break;

			case 4:
				$vertrical_flip = true;
				break;

			case 5:
				// transpose.
				$rotate         = 90;
				$vertrical_flip = true;
				break;

			case 6:
				$rotate = 270;
				break;

			case 7:
				$rotate          = 90;
				$horizontal_flip = true;
				break;

			case 8:
				$rotate = 90;
				break;

		}

		$image_editor = wp_get_image_editor( $file );

		if ( is_wp_error( $image_editor ) ) {
			return $file;
		}

		if ( $rotate ) {
			$image_editor->rotate( $rotate );
		}

		if ( $horizontal_flip || $vertrical_flip ) {
			$image_editor->flip( $horizontal_flip, $vertrical_flip );

		}

		$image_editor->save( $file ); // save to the file.

		return $file;
	}

	/**
	 * Check if given file is image
	 *
	 * A copy of file_is_valid_image
	 *
	 * @see file_is_valid_image()
	 *
	 * @param string $file file path.
	 *
	 * @return boolean
	 */
	private function is_valid_image_file( $file ) {

		$size = @getimagesize( $file );
		return ! empty( $size );
	}


	// MS compat for calculating space.
	/**
	 *  Copy of get_dirsize() function for compat.
	 *
	 * Get the size of a directory.
	 *
	 * A helper function that is used primarily to check whether
	 * a blog has exceeded its allowed upload space.
	 *
	 * @since MU
	 * @uses recurse_dirsize()
	 *
	 * @param string $directory directory path.
	 *
	 * @return int
	 */
	private static function get_dirsize( $directory ) {

		$dirsize = get_transient( 'dirsize_cache' );

		if ( is_array( $dirsize ) && isset( $dirsize[ $directory ]['size'] ) ) {
			return $dirsize[ $directory ]['size'];
		}

		if ( false === is_array( $dirsize ) ) {
			$dirsize = array();
		}

		$dirsize[ $directory ]['size'] = self::recurse_dirsize( $directory );

		set_transient( 'dirsize_cache', $dirsize, HOUR_IN_SECONDS );

		return $dirsize[ $directory ]['size'];
	}

	/**
	 * Get the size of a directory recursively.
	 *
	 * A copy of recurse_dirsize()
	 *
	 * Used by get_dirsize() to get a directory's size when it contains
	 * other directories.
	 *
	 * @since MU
	 *
	 * @param string $directory directory path.
	 *
	 * @return int
	 */
	private static function recurse_dirsize( $directory ) {
		$size = 0;

		$directory = untrailingslashit( $directory );

		if ( ! file_exists( $directory ) || ! is_dir( $directory ) || ! is_readable( $directory ) ) {
			return false;
		}

		if ( $handle = opendir( $directory ) ) {

			while ( ( $file = readdir( $handle ) ) !== false ) {
				$path = $directory . '/' . $file;
				if ( $file !== '.' && $file !== '..' ) {

					if ( is_file( $path ) ) {
						$size += filesize( $path );
					} elseif ( is_dir( $path ) ) {

						$handlesize = self::recurse_dirsize( $path );

						if ( $handlesize > 0 ) {
							$size += $handlesize;
						}
					}
				}
			}
			closedir( $handle );
		}

		return $size;
	}
}

/**
 * Singleton Instance of Local Stroage
 *
 * @return MPP_Local_Storage
 */
function mpp_local_storage() {
	return MPP_Local_Storage::get_instance();
}
