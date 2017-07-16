<?php

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get User meta
 *
 * @param int     $user_id numeric user id.
 * @param string  $meta_key meta name.
 * @param boolean $single whether to return single or array.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function mpp_get_user_meta( $user_id, $meta_key, $single = false ) {

	if ( function_exists( 'bp_get_user_meta' ) ) {
		$callback = 'bp_get_user_meta';
	} else {
		$callback = 'get_user_meta';
	}

	return $callback( $user_id, $meta_key, $single );
}

/**
 * Update User meta
 *
 * @param int    $user_id numeric user id.
 * @param string $meta_key meta name.
 * @param mixed  $meta_value value.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function mpp_update_user_meta( $user_id, $meta_key, $meta_value = '' ) {

	if ( function_exists( 'bp_update_user_meta' ) ) {
		$callback = 'bp_update_user_meta';
	} else {
		$callback = 'update_user_meta';
	}

	return $callback( $user_id, $meta_key, $meta_value );
}

/**
 * Deletes a user meta
 * An abstraction layer for deleting user meta
 *
 * @param int    $user_id numeric user id.
 * @param string $meta_key meta name.
 * @param mixed  $meta_value value.
 *
 * @return bool True on success, false on failure.
 */
function mpp_delete_user_meta( $user_id, $meta_key, $meta_value = '' ) {

	if ( function_exists( 'bp_delete_user_meta' ) ) {
		$callback = 'bp_delete_user_meta';
	} else {
		$callback = 'delete_user_meta';
	}

	return $callback( $user_id, $meta_key, $meta_value );
}
