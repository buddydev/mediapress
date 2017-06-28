<?php
/**
 * Admin loader
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load our admin panel, files for admin features.
 */
function mpp_admin_load() {

	if ( ! is_admin() || ( is_admin() && defined( 'DOING_AJAX' ) ) ) {
		return;
	}

	$path = mediapress()->get_path() . 'admin/';

	$files = array(
		'mpp-admin-functions.php',

		'mpp-admin.php',
		'class-mpp-admin-post-helper.php',
		'class-mpp-admin-gallery-list-helper.php',
		'tools/debug/mpp-admin-debug-helper.php',
		'class-mpp-admin-edit-gallery-panel.php',
		'mpp-admin-misc.php',
	);

	if ( mpp_get_option( 'enable_debug' ) ) {
		$files[] = 'tools/class-mpp-media-debugger.php';
	}

	foreach ( $files as $file ) {
		require_once $path . $file;
	}

	do_action( 'mpp_admin_loaded' );
}
add_action( 'mpp_loaded', 'mpp_admin_load' );
