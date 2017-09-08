<?php
/**
 * Template Loader for Members
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Members gallery template loader.
 */
class MPP_Members_Gallery_Template_Loader extends MPP_Gallery_Template_Loader {


	/**
	 * Singleton instance.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Constructor.
	 */
	public function __construct() {

		parent::__construct();

		$this->id   = 'default';
		$this->path = 'buddypress/members/';
	}

	/**
	 * Create/get singleton instance.
	 *
	 * @return MPP_Members_Gallery_Template_Loader|null
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Load template for members galleries.
	 */
	public function load_template() {

		$template = $this->path . 'home.php';
		$template = apply_filters( 'mpp_get_members_gallery_template', $template );

		mpp_get_template( $template );
	}
}
