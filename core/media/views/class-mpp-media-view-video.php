<?php
/**
 * Single video view.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single video view.
 */
class MPP_Media_View_Video extends MPP_Media_View {

	/**
	 * Display video.
	 *
	 * @param MPP_Media $media media object.
	 */
	public function display( $media ) {

		mpp_get_template( 'gallery/media/views/video.php' );
	}

}
