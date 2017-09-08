<?php
/**
 * Widget functions.
 *
 * @package mediapress.
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get data related to widget context.
 *
 * @param string $type widget type name.
 * @param string $key unique data piece identifier.
 *
 * @return bool
 */
function mpp_widget_get_data( $type, $key ) {

	$data = mediapress()->get_data( 'widget' );

	if ( isset( $data[ $type ][ $key ] ) ) {
		return $data[ $type ][ $key ];
	}

	return false;
}

/**
 * Save data related to widget context for later use(in the current request)
 *
 * @param string $type widget type name.
 * @param string $key unique data id.
 * @param mixed  $value value to be stored.
 */
function mpp_widget_save_data( $type, $key, $value ) {

	$data = mediapress()->get_data( 'widget' );

	if ( ! $data ) {
		$data = array();
	}

	$data[ $type ][ $key ] = $value;

	mediapress()->add_data( 'widget', $data );
}

/**
 * Reset stored data.
 *
 * @param string $type widget type name.
 * @param string $key data id.
 */
function mpp_widget_reset_data( $type, $key = null ) {

	$data = mediapress()->get_data( 'widget' );

	if ( ! $key ) {
		unset( $data[ $type ] );
	} else {
		unset( $data[ $type ][ $key ] );
	}

	// save the updated data.
	mediapress()->add_data( 'widget', $data );
}

/**
 * Get data associated with gallery widget
 *
 * @param string $key data identifier.
 *
 * @return bool
 */
function mpp_widget_get_gallery_data( $key ) {
	return mpp_widget_get_data( 'gallery', $key );
}

/**
 * Save some data for gallery widget
 *
 * @param string $key data id.
 * @param mixed  $value value to be stored.
 */
function mpp_widget_save_gallery_data( $key, $value ) {
	mpp_widget_save_data( 'gallery', $key, $value );
}

/**
 * Remove the specific data saved for gallery widget
 *
 * @param string $key data id name.
 */
function mpp_widget_reset_gallery_data( $key = null ) {
	mpp_widget_reset_data( 'gallery', $key );
}

/**
 * Get data for media widget
 *
 * @param string $key unique key name.
 *
 * @return bool
 */
function mpp_widget_get_media_data( $key ) {
	return mpp_widget_get_data( 'media', $key );
}

/**
 * Save some media widget data to use later
 *
 * @param string $key key name.
 * @param mixed  $value value.
 */
function mpp_widget_save_media_data( $key, $value ) {
	mpp_widget_save_data( 'media', $key, $value );
}

/**
 * Reset the data stored for media widget
 *
 * @param string $key key name.
 */
function mpp_widget_reset_media_data( $key = null ) {
	mpp_widget_reset_data( 'media', $key );
}
