<?php
/**
 * Template Loader for Groups
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Groups template loader.
 */
class MPP_Groups_Gallery_Template_Loader extends MPP_Gallery_Template_Loader {

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

		$this->id   = 'default';
		$this->path = 'buddypress/groups/';
	}

	/**
	 * Create/get singleton instance.
	 *
	 * @return MPP_Groups_Gallery_Template_Loader
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load template for groups galleries.
	 */
	public function load_template() {

		$template = $this->path . 'home.php';
		$template = apply_filters( 'mpp_get_groups_gallery_template', $template );

		mpp_get_template( $template );
	}

}
