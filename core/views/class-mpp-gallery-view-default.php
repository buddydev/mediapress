<?php
/**
 * Default grid view.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default View.
 */
class MPP_Gallery_View_Default extends MPP_Gallery_View {

	/**
	 * Singleton instance.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		parent::__construct();

		$this->id = 'default';
		$this->name = __( 'Default Grid layout', 'mediapress' );
	}

	/**
	 * Create/get singleton instance.
	 *
	 * @return MPP_Gallery_View_Default
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Display single gallery media as grid
	 *
	 * @param MPP_Gallery $gallery gallery object.
	 */
	public function display( $gallery ) {

		$gallery = mpp_get_gallery( $gallery );

		$type = $gallery->type;

		$templates = array(
			"gallery/views/grid-{$type}.php", // grid-audio.php etc .
			'gallery/views/grid.php',
		);

		mpp_locate_template( $templates, true );
	}

	/**
	 * Display grid for activity
	 *
	 * @param int[] $media_ids media ids.
	 * @param int   $activity_id activity id.
	 *
	 * @return null
	 */
	public function activity_display( $media_ids = array(), $activity_id = 0 ) {

		if ( ! $media_ids ) {
			return;
		}

		$media = $media_ids[0];

		$media = mpp_get_media( $media );

		if ( ! $media ) {
			return;
		}
		if ( ! $activity_id ) {
			$activity_id = bp_get_activity_id();
		}


		$type = $media->type;
		// we will use include to load found template file,
		// the file will have $media_ids available.
		$templates = array(
			"buddypress/activity/views/grid-{$type}.php", // loop-audio.php etc.
			'buddypress/activity/views/grid.php',
		);

		$located_template = mpp_locate_template( $templates, false );

		include $located_template;
	}

}
