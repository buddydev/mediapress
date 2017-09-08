<?php
/**
 * Base logger.
 *
 * @package mediapress.
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for logger implementation
 */
abstract class MPP_Logger {

	/**
	 * Log details.
	 *
	 * @param array $args log fields.
	 *
	 * @return int|boolean
	 */
	abstract public function log( $args );

	/**
	 * Delete logs.
	 *
	 * @param array $args fields.
	 *
	 * @return bool
	 */
	abstract public function delete( $args );

	/**
	 * Check if log exists for the given fields.
	 *
	 * @param array $args args.
	 *
	 * @return mixed
	 */
	abstract public function log_exists( $args );

	/**
	 * Get logs by given args.
	 *
	 * @param array $args args.
	 *
	 * @return mixed
	 */
	abstract public function get( $args );

	/**
	 * Get where conditions.
	 *
	 * @param array $args fields.
	 *
	 * @return mixed
	 */
	abstract public function get_where_sql( $args );
}
