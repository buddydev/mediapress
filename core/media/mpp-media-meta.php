<?php
/**
 * Media meta functions.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Add meta.
 *
 * @param int    $media_id media id.
 * @param string $meta_key meta key.
 * @param mixed  $meta_value meta value.
 * @param bool   $unique is unique.
 *
 * @return false|int
 */
function mpp_add_media_meta( $media_id, $meta_key, $meta_value, $unique = false ) {
	return add_post_meta( $media_id, $meta_key, $meta_value, $unique );
}

/**
 * Get meta value.
 *
 * @param int    $media_id media id.
 * @param string $meta_key meta key.
 * @param bool   $single get as single value or array.
 *
 * @return mixed
 */
function mpp_get_media_meta( $media_id, $meta_key = '', $single = false ) {

	if ( empty( $meta_key ) ) {
		$single = false;
	}

	return get_post_meta( $media_id, $meta_key, $single );
}

/**
 * Update meta value.
 *
 * @param int    $media_id media id.
 * @param string $meta_key meta key.
 * @param mixed  $meta_value meta value.
 * @param mixed  $prev_value previous value.
 *
 * @return bool|int
 */
function mpp_update_media_meta( $media_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_post_meta( $media_id, $meta_key, $meta_value, $prev_value );

}

/**
 * Delete meta.
 *
 * @param int    $media_id media id.
 * @param string $meta_key meta key.
 * @param mixed  $meta_value meta value.
 *
 * @return bool
 */
function mpp_delete_media_meta( $media_id, $meta_key = '', $meta_value = '' ) {
	return delete_post_meta( $media_id, $meta_key, $meta_value );
}
