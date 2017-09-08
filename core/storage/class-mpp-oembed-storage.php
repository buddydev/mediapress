<?php
/**
 * Oembed storage manager(not implemented yet).
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Just a clone, not being used, we will restructure and use in 1.1
 * Handles the Oembed storage on the server
 *
 * This allows to store the files on the same server where WordPress is installed
 */
class MPP_Oembed_Storage extends MPP_Storage_Manager {

	/**
	 * Singleton.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Oembed instance.
	 *
	 * @var WP_oEmbed
	 */
	private $oembed;

	/**
	 * Seriously?
	 *
	 * @var array
	 */
	private $upload_errors = array();

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->oembed = new MPP_oEmbed();
	}

	/**
	 * Create/get singleton instance.
	 *
	 * @return MPP_Oembed_Storage
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get src.
	 *
	 * @param string $size media size(thumbnail, mid etc).
	 * @param int    $id media id.
	 *
	 * @return string
	 */
	public function get_src( $size = null, $id = null ) {
		// ID must be given.
		if ( ! $id ) {
			return '';
		}
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

		return '';
	}

	/**
	 * Uploads a file
	 *
	 * @param array $file PHP $_FIlES.
	 * @param array $args {
	 *  args.
	 *
	 * @type string $component
	 * @type int $component_id
	 * @type int $gallery_id
	 *
	 * }
	 *
	 * @return boolean
	 */
	public function upload( $file, $args ) {
		return false;
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
		// we are not storing anything on our server.
		return 0;
	}

	/**
	 * Server can handle upload?
	 *
	 * @return boolean
	 */
	public function can_handle() {
		// in future we may check the url for provider.
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
		return false;
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
		return false;
	}

	/**
	 * Called after gallery deletion
	 *
	 * @param int $gallery_id numeric gallery id.
	 *
	 * @return boolean
	 */
	public function delete_gallery( $gallery_id ) {
		return false;
	}

}
/**
 * Singleton Instance of MPP_Oembed_Storage_Manager
 *
 * @return MPP_Oembed_Storage
 */
function mpp_oembed_storage() {

	return MPP_Oembed_Storage::get_instance();
}

