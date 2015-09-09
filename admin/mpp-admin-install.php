<?php
/**
 * Install tables required by MediaPress
 * Currently, we create only one table ( Log table )
 * 
 * @global WPDB $wpdb
 */
function mpp_install_db() {
	global $wpdb;
	
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
	$charset_collate = !empty( $wpdb->charset ) ? "DEFAULT CHARACTER SET {$wpdb->charset}" : '';
	
	$sql = array();
	
	$log_table = mediapress()->get_table_name( 'logs' );
	
	$sql[] = "CREATE TABLE IF NOT EXISTS {$log_table} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		item_id bigint(20) NOT NULL,
		action varchar(16) NOT NULL,
		value varchar(32) NOT NULL,
		logged_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (id)
	) {$charset_collate};";

	dbDelta( $sql );	
}