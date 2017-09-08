<?php
/**
 * Single photo view.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single photo.
 */
class MPP_Media_View_Photo extends MPP_Media_View {
	/**
	 * Display photo.
	 *
	 * @param MPP_Media $media media object.
	 */
	public function display( $media ) {
		mpp_get_template( 'gallery/media/views/photo.php' );
	}

}
