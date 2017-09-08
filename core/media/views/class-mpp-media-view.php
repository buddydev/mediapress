<?php
/**
 * Base media view.
 * Media views are used to display content for single media entry.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All media views must inherit this class.
 */
abstract class MPP_Media_View {
	/**
	 * Display the view for media.
	 *
	 * @param MPP_Media $media media object.
	 */
	public abstract function display( $media );
}
