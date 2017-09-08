<?php
/**
 * Base gallery view. All gallery view must inherit it.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Superclass for all the gallery views
 * All valid gallery view must extend this
 *
 * A Gallery view supports only one media type
 * but may support one or more component( members/sitewide/groups etc)
 */
class MPP_Gallery_View {

	/**
	 * Unique identifier for this view
	 * Each view must have a unique identifier that identifies it for the given media type uniquely
	 *
	 * @var string unique identifier.
	 */
	protected $id = '';
	/**
	 * Label for this view.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Which view types are supported.
	 *
	 * @var array
	 */
	protected $supported_views = array();

	/**
	 * List of components supported by this view.
	 *
	 * @var array
	 */
	protected $supported_components = array();

	/**
	 * MPP_Gallery_View constructor.
	 *
	 * @param array $args view args.
	 */
	protected function __construct( $args = null ) {
		// let us support all views by default, the child class can explicitly reset it if they want.
		$this->supported_views = array( 'shortcode', 'gallery', 'media-list', 'activity' );
		$this->set_supported_components( array( 'sitewide', 'members', 'groups' ) );
	}

	/**
	 * Check if this supports the views for 'widget', 'shortcode', 'gallery', 'media-list', 'activity' etc
	 *
	 * @param string $view_type one of the 'widget', 'shortcode', 'gallery', 'media-list', 'activity' etc.
	 *
	 * @return boolean
	 */
	public function supports( $view_type ) {

		if ( in_array( $view_type, $this->supported_views ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get all view contexts supported by this view.
	 *
	 * @return array
	 */
	public function get_supported_views() {
		return $this->supported_views;
	}

	/**
	 * Does this view supports component
	 *
	 * @param string $component component name(members,groups,sitewide etc).
	 *
	 * @return boolean
	 */
	public function supports_component( $component ) {

		if ( in_array( $component, $this->supported_components ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Reset the list of supported components
	 *
	 * @param array $components the new set of components to be supported.
	 */
	public function set_supported_components( $components ) {
		$this->supported_components = $components;
	}

	/**
	 * Get an array of supported components
	 *
	 * @return array
	 */
	public function get_supported_components() {
		return $this->supported_components;
	}

	/**
	 * Get unique view id
	 *
	 * @return string unique view ID
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get human readable name for this view
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Display single gallery media list
	 *
	 * @param MPP_Gallery $gallery gallery object.
	 */
	public function display( $gallery ) {
		// Please implement it in your child view.
	}

	/**
	 * Display single gallery settings
	 *
	 * @param MPP_Gallery $gallery gallery object.
	 */
	public function display_settings( $gallery ) {
		// for future use to allow view settings for user.
	}

	/**
	 * Display list of media for the given widget settings
	 *
	 * @param array $args args.
	 */
	public function widget( $args = array() ) {
	}

	/**
	 * Display widget settings
	 */
	public function widget_settings() {
	}

	/**
	 * Receives a widget instance object and returns updated value
	 *
	 * @param Object $instance widget instance.
	 * @param Object $old_instance widget instance.
	 *
	 * @return Object.
	 */
	public function update_widget_settings( $instance, $old_instance ) {
		return $instance;
	}

	/**
	 * Display media list for the shortcode
	 *
	 * @param array $args args.
	 */
	public function shortcode( $args = array() ) {
	}

	/**
	 * Settings for shortcode.
	 */
	public function shrtcode_settings() {
	}

	/**
	 * Display the activity attachment list
	 *
	 * @param array $media_ids media ids.
	 * @param int   $activity_id activity id.
	 */
	public function activity_display( $media_ids = array(), $activity_id = 0 ) {
	}
}
