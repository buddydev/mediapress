<?php
/**
 * Shortcode helper functions.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the piece of data.
 *
 * @param string $type type token.
 * @param string $key unique name.
 *
 * @return bool|mixed
 */
function mpp_shortcode_get_data( $type, $key ) {

	$data = mediapress()->get_data( 'shortcode' );

	if ( isset( $data[ $type ][ $key ] ) ) {
		return $data[ $type ][ $key ];
	}

	return false;
}

/**
 * Save a piece of data related to shortcode
 *
 * @param string $type type token.
 * @param string $key unique name.
 * @param mixed  $value value.
 */
function mpp_shortcode_save_data( $type, $key, $value ) {

	$data = mediapress()->get_data( 'shortcode' );

	if ( ! $data ) {
		$data = array();
	}

	$data[ $type ][ $key ] = $value;

	mediapress()->add_data( 'shortcode', $data );
}

/**
 * Reset all data for the given token/key.
 *
 * @param string $type type token.
 * @param string $key key name(optional).
 */
function mpp_shortcode_reset_data( $type, $key = null ) {

	$data = mediapress()->get_data( 'shortcode' );

	if ( ! $key ) {
		unset( $data[ $type ] );
	} else {
		unset( $data[ $type ][ $key ] );
	}

	// save the updated data.
	mediapress()->add_data( 'shortcode', $data );
}

/**
 * Get data for gallery shortcode.
 *
 * @param string $key name for the piece of data.
 *
 * @return bool|mixed
 */
function mpp_shortcode_get_gallery_data( $key ) {
	return mpp_shortcode_get_data( 'gallery', $key );
}

/**
 * Save data for the gallery shortcode.
 *
 * @param string $key unique name we use to store data.
 * @param mixed  $value value to be stored.
 */
function mpp_shortcode_save_gallery_data( $key, $value ) {
	mpp_shortcode_save_data( 'gallery', $key, $value );
}

/**
 * Reset data related to gallery shortcode.
 *
 * @param string $key key name.
 */
function mpp_shortcode_reset_gallery_data( $key = null ) {
	mpp_shortcode_reset_data( 'gallery', $key );
}

/**
 * Get data related to the media shortcode.
 *
 * @param string $key key name.
 *
 * @return bool|mixed
 */
function mpp_shortcode_get_media_data( $key ) {
	return mpp_shortcode_get_data( 'media', $key );
}

/**
 * Save data related to the media shortcode.
 *
 * @param string $key key name.
 * @param mixed  $value value.
 */
function mpp_shortcode_save_media_data( $key, $value ) {
	mpp_shortcode_save_data( 'media', $key, $value );
}

/**
 * Reset data related to the media shortcode.
 *
 * @param string $key unique name.
 */
function mpp_shortcode_reset_media_data( $key = null ) {
	mpp_shortcode_reset_data( 'media', $key );
}
