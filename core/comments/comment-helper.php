<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class MPP_Comments_Helper {

	private static $instance;
	
	
	private function __construct() {
	
		// Hide in comment listing
		add_filter( 'comments_clauses', array( $this, 'exclude_comments' ), 10, 2 );
		add_filter( 'comment_feed_where', array( $this, 'hide_from_feeds' ), 10, 2 );

		// Update count comments
		add_filter( 'wp_count_comments', array( $this, 'filter_count_comments' ), 10, 2 );
	}

	/**
	 * 
	 * @return MPP_Comments_Helper
	 */
	public static function get_instance(){
		
		if( !isset( self::$instance ) )
			self::$instance = new self();
		
		return self::$instance;
		
	}

	public function exclude_comments( $clauses, $wp_comment_query ) {
		global $wpdb;

		$clauses['where'] .= $wpdb->prepare( ' AND comment_type != %s', mpp_get_comment_type() );
		return $clauses;
	}


	
	public function hide_from_feeds( $where, $wp_comment_query ) {
		global $wpdb;

		$where .= $wpdb->prepare( " AND comment_type != %s", mpp_get_comment_type() );
		return $where;
	}

	

	/**
	 * Remove order notes from wp_count_comments()
	 *
	 * 
	 */
	public  function filter_count_comments( $stats, $post_id ) {
		global $wpdb;

		if ( 0 === $post_id ) {

			$count = wp_cache_get( 'comments-mpp', 'counts' );
			
			if ( false !== $count ) {
				return $count;
			}

			$count = $wpdb->get_results( $wpdb->prepare( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} WHERE comment_type != %s GROUP BY comment_approved", mpp_get_comment_type() ), ARRAY_A );

			$total = 0;
			$approved = array( '0' => 'moderated', '1' => 'approved', 'spam' => 'spam', 'trash' => 'trash', 'post-trashed' => 'post-trashed' );

			foreach ( (array) $count as $row ) {
				// Don't count post-trashed toward totals
				if ( 'post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'] ) {
					$total += $row['num_comments'];
				}
				if ( isset( $approved[ $row['comment_approved'] ] ) ) {
					$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
				}
			}

			$stats['total_comments'] = $total;
			
			foreach ( $approved as $key ) {
				if ( empty( $stats[ $key ] ) ) {
					$stats[ $key ] = 0;
				}
			}

			$stats = (object) $stats;
			
			wp_cache_set( 'comments-mpp', $stats, 'counts' );
		}

		return $stats;
	}
	
	

}
MPP_Comments_Helper::get_instance();