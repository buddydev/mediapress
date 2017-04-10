<?php
/**
 * Pre fetches and Caches media for current activity query
 *
 * @package mediapress
 */

// No direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Cache the Media/Galleries when activity is being fetched
 */
class MPP_Activity_Media_Cache_Helper {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Setup when to preftech and cache.
	 */
	public function setup_hooks() {
		add_filter( 'bp_activity_prefetch_object_data', array( $this, 'cache' ) );
	}

	/**
	 * Since we are filtering on 'bp_activity_prefetch_object_data', the activity meta is already cached,
	 * So, we won't query for media ids instead loop and build the list
	 *
	 * @param BP_Activity_Activity[] $activities array of activities.
	 *
	 * @return array $activities
	 */
	public function cache( $activities ) {

		if ( empty( $activities ) ) {
			return array();
		}

		$media_ids   = array();
		$gallery_ids = array();

		foreach ( $activities as $activity ) {
			// check if the activity has attached gallery.
			$gallery_id = mpp_activity_get_gallery_id( $activity->id );

			if ( $gallery_id ) {
				$gallery_ids[] = $gallery_id;
			}
			// check for media ids.
			$attached_media_ids = mpp_activity_get_attached_media_ids( $activity->id );

			if ( ! empty( $attached_media_ids ) ) {
				$media_ids = array_merge( $media_ids, $attached_media_ids );
			}

			$associated_media_id = mpp_activity_get_media_id( $activity->id );

			if ( ! empty( $associated_media_id ) ) {
				$media_ids[] = $associated_media_id;
			}
		}

		$merged_ids = array_merge( $media_ids, $gallery_ids );

		$merged_ids = array_unique( $merged_ids );

		if ( ! empty( $merged_ids ) ) {
			_prime_post_caches( $merged_ids, true, true );
		}

		return $activities;
	}

}

// prefetch activity associated gallery/media data.
new MPP_Activity_Media_Cache_Helper();
