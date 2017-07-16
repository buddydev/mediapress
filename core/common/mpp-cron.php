<?php
/**
 * Add our 15 minute schedule for the cron job
 *
 * @param array $schedules array of schedules.
 *
 * @return mixed
 */
function mpp_add_cron_schedule( $schedules ) {
	// 15 minute.
	$schedules['quarterhour'] = array( 'interval' => 900, 'display' => __( 'Once per 15 minutes', 'mediapress' ) );

	return $schedules;
}
add_filter( 'cron_schedules', 'mpp_add_cron_schedule' );

/**
 * Schedule the cron job
 * Called When MediaPress is activated
 */
function mpp_schedule_cron_job() {
	wp_schedule_event( time(), 'quarterhour', 'mpp_cleanup_schedule' );
}

/**
 * Clear our scheduled cron job
 *
 *  Called on deactivation
 */
function mpp_clear_scheduled_cron_job() {
	wp_clear_scheduled_hook( 'mpp_cleanup_schedule' );
}

/**
 * Delete orphaned Media if it is enabled in the setting
 */
function mpp_delete_orphan_media() {

	// if deletion is not enabled, return
	if ( ! mpp_get_option( 'delete_orphaned_media' ) ) {
		return;
	}
	// get next 5 orphan media and delete.
	global $wpdb;
	$orphaned_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s LIMIT 0, 10", '_mpp_is_orphan' ) );

	if ( empty( $orphaned_ids ) ) {
		return;
	}
	// cache.
	_prime_post_caches( $orphaned_ids, true, true );

	foreach ( $orphaned_ids as $media_id ) {
		mpp_delete_media( $media_id );
	}
}
add_action( 'mpp_cleanup_schedule', 'mpp_delete_orphan_media' );
