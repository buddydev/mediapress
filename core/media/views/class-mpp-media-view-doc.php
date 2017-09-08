<?php
/**
 * Single doc view.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Doc view.
 */
class MPP_Media_View_Docs extends MPP_Media_View {

	/**
	 * Display doc.
	 *
	 * @param MPP_Media $media media object.
	 */
	public function display( $media ) {

		mpp_get_template( 'gallery/media/views/doc.php' );

	}

}
