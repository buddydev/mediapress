<?php
/**
 * Plugin Name: MediaPress
 * Version: 1.4.4
 * Author: BuddyDev
 * Plugin URI: https://buddydev.com/mediapress/
 * Author URI: https://buddydev.com
 * Description: MediaPress is the most powerful media plugin for BuddyPress . It allows uploading images(photos), videos, audios, documents
 *                and can be used to add any type of content. It has a well defined API to allow extending the plugin.
 * License: GPL2 or above
 * Domain Path: /languages
 * Text Domain: mediapress
 *
 * @package MediaPress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The main MediaPress Singleton class
 * you can access the singleton instance using mediapress() function
 *
 * @see mediapress()
 *
 * Life begins here
 */
class MediaPress {


	/**
	 * Private Singleton instance of the MediaPress class
	 *
	 * @var MediaPress
	 */
	private static $instance;


	/**
	 * We keep any extra data here to pass around
	 *
	 * @see MediaPress::get_data( $key )
	 * @see MediaPress::set_data( $key, $val )
	 *
	 * @var array of mixed data
	 */
	private $data = array();


	/**
	 * Associative array containing table names
	 *
	 * We will store all our table names here
	 *
	 * @var array
	 */
	private $tables = array();


	/**
	 * File system absolute path to the mediapress plugin eg. /home/xyz/public_html/wp-content/plugins/mediapress/
	 *
	 * @see MediaPress::get_path()
	 *
	 * @var string
	 */
	private $plugin_path;


	/**
	 * Absolute url to the mediapress plugin directory e.g http://example.com/wp-content/plugins/mediapress/
	 *
	 * @var string
	 */
	private $plugin_url;


	/**
	 * Relative path to this plugin
	 *
	 * @var string
	 */
	private $basename;


	/**
	 * List of assets k=>v pair where k: asset identifier, v = url
	 *
	 * @var array
	 */
	private $assets = array();


	/**
	 * Main Gallery Query
	 *
	 * Always available
	 *
	 * @var MPP_Gallery_Query
	 */
	public $the_gallery_query = null; // main gallery query.


	/**
	 * Main Media Query
	 *
	 * @var MPP_Media_Query
	 */
	public $the_media_query = null; // main media query.


	/**
	 * Current Gallery object is stored here
	 *
	 * @var MPP_Gallery
	 */
	public $current_gallery;


	/**
	 * Current Media Object is stored here
	 *
	 * @var MPP_Media
	 */
	public $current_media;


	/**
	 * Not Used
	 *
	 * @var MPP_Comment_Query
	 */
	public $the_comment_query;

	/**
	 * Current Comment: Note Used
	 *
	 * @var null
	 */
	public $current_comment;


	/**
	 * Array of all registered Status object
	 *
	 * @var MPP_Status[] array of status objects
	 */
	public $statuses = array();


	/**
	 * Array of all registered Gallery status
	 * Currently, It is same as Mediapress::$statuses
	 *
	 * @var MPP_Status[] array of status objects which are valid for gallery
	 */
	public $gallery_statuses = array();


	/**
	 * Array of all registered Media status objects
	 *
	 * @var MPP_Status[] array of status objects which are valid for Media
	 */
	public $media_statuses = array();


	/**
	 * Array of all registered component objects
	 *
	 * @var MPP_Component[] array of Component objects where keys are component identifier
	 */
	public $components = array();


	/**
	 * Array of all registered type objects
	 *
	 * @var MPP_Type[] array of Media|Gallery type object
	 */
	public $types = array();


	/**
	 * An array of active status objects
	 *
	 * Active statuses are sub set of the registered statuses which are enabled by the site admin for use on the site.
	 * It can be controlled via MediaPress settings page.
	 *
	 * @var MPP_Status[] array of status objects
	 */
	public $active_statuses = array();


	/**
	 * Array of active component objects
	 * Active components are sub set of the registered components
	 *
	 * @var MPP_Component[] array of Component objects where keys are component identifier
	 */
	public $active_components = array();


	/**
	 * Array of of active type objects
	 *
	 * Active types are sub set of the registered types
	 *
	 * @var MPP_Type[] array of Media|Gallery type object
	 */
	public $active_types = array();


	/**
	 * An array of registered storage managers
	 *
	 * @see mpp_register_storage_manager()
	 *
	 * @var MPP_Storage_Manager[]
	 */
	public $storage_managers = array();


	/**
	 * An array of registered view for the media type  and the storage method
	 *
	 * @see mpp_register_media_view()
	 *
	 * @var MPP_Media_View[]
	 */
	public $media_views = array();


	/**
	 * An array of registered views for gallery
	 *
	 * @var MPP_Gallery_View[]
	 */
	public $gallery_views = array();


	/**
	 * Multi dimensional array to store the media size specific details
	 *
	 * @see mpp_register_media_size()
	 *
	 * @var mixed
	 */
	public $media_sizes = array();

	// screen identifiers.
	//
	/**
	 * Is it the gallery home, true when on component/mediapress pages
	 *
	 * @var bool
	 */
	public $is_gallery_home = false;


	/**
	 * We keep the probable current action here and later move to $action if validated
	 *
	 *  Do not use it in your plugins
	 *
	 * @var string
	 */
	private $temp_action = ''; // it should be the action if validated but we can not say that with confident yet 100%. Fo checking current action, please use get_action.


	/**
	 * Current action
	 *
	 * @var string current action  manage/edit etc
	 */
	private $action = '';


	/**
	 * Current edit action only valid if the main action is edit/manage
	 *
	 * @var string
	 */
	private $edit_action = '';


	/**
	 * Action variable stack, we use it to provide consistency for all components
	 *
	 * @var array
	 */
	private $action_variables = array();


	/**
	 * Which object type is being edited, gallery or media?
	 *
	 * @var string
	 */
	private $editing_item_type = ''; // gallery|media.


	/**
	 * Restricted media slugs
	 *
	 * @var array()
	 */
	private $restricted_media_slugs = array( 'edit', 'delete', 'publish', 'reorder', 'manage', 'gallery' );


	/**
	 * Contains gallery/media admin menus
	 *
	 * $menus['gallery'], $menus['media']
	 *
	 * @var MPP_Menu[]
	 */
	private $menus = array();


	/**
	 * Are we using theme compat?
	 *
	 * @var bool whether MediaPress theme compat moe is enabled
	 */
	private $using_theme_compat = false;


	/**
	 * MediaPress constructor. Internally called to create the singleton instance.
	 */
	private function __construct() {

		$this->basename = plugin_basename( __FILE__ );
		$this->setup();
	}


	/**
	 * Factory method to get singleton instance
	 *
	 * @see mediapress() for accessing the instance
	 * @return MediaPress
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Setup MediaPress
	 */
	public function setup() {

		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugin_dir_url( __FILE__ );

		global $wpdb;
		// MediaPress Logs table name.
		$this->store_table_name( 'logs', $wpdb->prefix . 'mpp_logs' );

		// register_activation_hook.
		add_action( 'activate_' . $this->basename, array( $this, 'do_activation' ) );
		// Register deactivation hook for cleanup.
		add_action( 'deactivate_' . $this->basename, array( $this, 'do_deactivation' ) );
		// Load the MediaPress core.
		add_action( 'plugins_loaded', array( $this, 'load' ), 0 );
		// Load translation files.
		add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		add_action( 'update_option_mpp-settings', array( $this, 'flush_rewrite_rules_on_settings_update' ), 10, 2 );
	}


	/**
	 * Loads the MediaPress Core Loader class
	 *
	 * Loading is handled by the MPP_Core_Loader
	 */
	public function load() {

		require_once $this->plugin_path . 'mpp-loader.php';

		$loader = new MPP_Core_Loader();
		$loader->load();

		do_action( 'mpp_loaded' );
	}


	/**
	 * Load logger on demand
	 */
	public function load_logger() {

		$path = $this->plugin_path;
		require_once $path . 'core/logger/class-mpp-logger.php';
		require_once $path . 'core/logger/class-mpp-db-logger.php';
		require_once $path . 'core/logger/mpp-logger-functions.php';

	}


	/**
	 * Does initial setup on activation of the plugin
	 */
	public function do_activation() {

		require_once $this->plugin_path . 'admin/mpp-admin-install.php';

		// mpp_upgrade_legacy_1_0_b1_activity();
		// post type functionality.
		require_once $this->plugin_path . 'core/common/mpp-common-functions.php';
		require_once $this->plugin_path . 'core/mpp-post-type.php';

		// store default settings if not already exists.
		add_option( 'mpp-settings', mpp_get_all_options() );
		// initialize post type( because we want to flush the rewrite rules).
		MPP_Post_Type_Helper::get_instance()->init();

		// Add rewrite end points.
		add_rewrite_endpoint( 'manage', EP_PERMALINK );
		add_rewrite_endpoint( 'media', EP_PERMALINK );

		flush_rewrite_rules();

		// multiple terms creation by WordPress is too much db intensive, let us do it lightly.
		mpp_install_terms();

		// on activation, create logger table.
		mpp_install_db();

		// schedule cron.
		require_once $this->plugin_path . 'core/common/mpp-cron.php';
		// schedule.
		mpp_schedule_cron_job();

	}


	/**
	 * When plugin is deactivated, clears scheduled cron job and flushes rewrite rules
	 */
	public function do_deactivation() {

		flush_rewrite_rules();

		require_once $this->plugin_path . 'core/common/mpp-common-functions.php';
		require_once $this->plugin_path . 'core/common/mpp-cron.php';
		// clear schedule.
		mpp_clear_scheduled_cron_job();

	}


	/**
	 * Flush rewrite rules automatically when our settings is updated and the slug for permalink/archive change.
	 *
	 * @param array $old old settings.
	 * @param array $new new settings.
	 */
	public function flush_rewrite_rules_on_settings_update( $old, $new ) {

		// for the time when there was no old option saved.
		if ( empty( $old ) || empty( $new ) ) {
			flush_rewrite_rules();
			return;
		}

		$old_permalink = isset( $old['gallery_permalink_slug'] )? $old['gallery_permalink_slug'] : false;
		$new_permalink = isset( $new['gallery_permalink_slug'] )? $new['gallery_permalink_slug'] : false;

		$old_archive_slug = isset( $old['gallery_archive_slug'] )? $old['gallery_archive_slug'] : false;
		$new_archive_slug = isset( $new['gallery_archive_slug'] )? $new['gallery_archive_slug'] : false;

		// Detect change in gallery archive/single slug.
		if ( ( $old_archive_slug != $new_archive_slug ) || ( $old_permalink != $new_permalink ) ) {
			// change happened.
			MPP_Post_Type_Helper::get_instance()->init();

			flush_rewrite_rules();
		}
	}


	/**
	 * Load translation files
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'mediapress', false, dirname( $this->basename ) . '/languages' );
	}


	/**
	 * Get the url of the MediaPress plugin directory ( e.g http://site.com/wp-content/plugins/mediapress/)
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->plugin_url;
	}


	/**
	 * Get the absolute path to the mediapress plugin directory( e.g /home/xyz/public_html/wp-content/plugins/mediapress/)
	 *
	 * @return string
	 */
	public function get_path() {
		return $this->plugin_path;
	}


	/**
	 * Get the relative path of this file from the plugins directory e.g mediapress/mediapress.php
	 *
	 * @return string relative path
	 */
	public function get_basename() {
		return $this->basename;
	}


	/**
	 * Get the url of an asset
	 *
	 * @param string $key the unique asset identifier.
	 *
	 * @return string
	 */
	public function get_asset( $key ) {

		if ( isset( $this->assets[ $key ] ) ) {
			return $this->assets[ $key ];
		}

		return ''; // Not found. Return empty string.

	}


	/**
	 * Add an asset to our cached collection
	 *
	 * @param string $key unique key for the asset.
	 * @param string $asset_url the absolute url of the asset.
	 *
	 * @return string asset url
	 */
	public function add_asset( $key, $asset_url ) {

		$this->assets[ $key ] = $asset_url;
		return $asset_url;

	}


	/**
	 * Set current action
	 *
	 * @param string $action name of action to be set.
	 *
	 * @return string
	 */
	public function set_action( $action ) {

		$this->action = $action;

		return $this->action;

	}


	/**
	 * Get current action
	 *
	 * @return string current action
	 */
	public function get_action() {
		return $this->action;
	}


	/**
	 * Check for current action
	 *
	 * @param string $action check for the given action.
	 *
	 * @return boolean Is it the current action?
	 */
	public function is_action( $action ) {
		return $this->get_action() === $action;
	}


	/**
	 * Set action variables array
	 *
	 * @param array $action_variables all action variables in the url.
	 */
	public function set_action_variables( $action_variables = array() ) {
		$this->action_variables = $action_variables;
	}


	/**
	 * Get action variables array
	 *
	 * @return array
	 */
	public function get_action_variables() {
		return $this->action_variables;
	}


	/**
	 * Get an action varibale by position
	 *
	 * @param int $pos the position of the action variable to be returned.
	 *
	 * @return mixed|string
	 */
	public function get_action_variable( $pos = 0 ) {
		return isset( $this->action_variables[ $pos ] ) ? $this->action_variables[ $pos ] : '';
	}


	/**
	 * Set the current probably happening action
	 *
	 * @internal sets temporary action
	 *
	 * @param string $action the action to be saved as temporary.
	 */
	public function _set_temp_action( $action ) {
		$this->temp_action = $action;
	}


	/**
	 * For Internal Use
	 * get the current probable action
	 *
	 * @internal for internal use
	 */
	public function _get_temp_action() {
		return $this->temp_action;
	}


	/**
	 * Set edit action
	 *
	 * @param string $action name of the editing action.
	 */
	public function set_edit_action( $action ) {
		$this->edit_action = $action;
	}


	/**
	 * Get edit action
	 *
	 * @return string
	 */
	public function get_edit_action() {
		return $this->edit_action;
	}


	/**
	 * Check if the given edit action is
	 *
	 * @param string $action name of the action to check against.
	 *
	 * @return boolean is it the current sub action?
	 */
	public function is_edit_action( $action ) {
		return $action === $this->edit_action;
	}


	/**
	 * Set current editing object type
	 * Bad choice of name, I know
	 * Suggest a better name if you can!
	 *
	 * @param string $type mpp_get_gallery_post_type() or mpp_get_media_post_type().
	 */
	public function set_editing( $type ) {
		$this->editing_item_type = $type;
	}


	/**
	 * Get the object type being edited now
	 *
	 * @return string 'media'|'gallery'
	 */
	public function get_editing() {
		return $this->editing_item_type;
	}


	/**
	 * Check if the current object being edited is of given type
	 *
	 * @param string $type Either 'media' or 'gallery'.
	 *
	 * @return boolean
	 */
	public function is_editing( $type ) {
		return $type === $this->editing_item_type;
	}


	/**
	 * Get the given MPP_Menu object by the menu name
	 *
	 * @param string $type Possible values 'media'|'gallery'.
	 *
	 * @return MPP_Menu
	 */
	public function get_menu( $type ) {
		return $this->menus[ $type ];
	}


	/**
	 * Add menu for the Gallery/media
	 *
	 * @param string   $type Context type. 'gallery' or 'media'.
	 * @param MPP_Menu $menu The menu to add.
	 */
	public function add_menu( $type, $menu ) {
		$this->menus[ $type ] = $menu;
	}


	/**
	 * Store some arbitrary data
	 * most of the time we use to pass the things around methods like a global
	 *
	 * @param string $type unique key.
	 * @param mixed  $data data to be cached.
	 */
	public function add_data( $type, $data ) {
		$this->data[ $type ] = $data;
	}


	/**
	 * Get the arbitrary data stored by the key
	 *
	 * @param string $type unique key.
	 *
	 * @return mixed|boolean
	 */
	public function get_data( $type ) {

		if ( isset( $this->data[ $type ] ) ) {
			return $this->data[ $type ];
		}

		return false;

	}


	/**
	 * Reset the data set for this key
	 *
	 * @param string $type the unique key.
	 */
	public function reset_data( $type ) {
		unset( $this->data[ $type ] );
	}


	/**
	 * Get the stored table name
	 *
	 * @param string $key unique table identifier.
	 *
	 * @return string table name or empty string
	 */
	public function get_table_name( $key ) {

		if ( isset( $this->tables[ $key ] ) ) {
			return $this->tables[ $key ];
		}

		return ''; // invalid table.

	}


	/**
	 * Store a table name for future reference
	 *
	 * @param string $key unique table identifier.
	 * @param string $table_name actual table name.
	 *
	 * @return boolean true on success false on failure
	 */
	public function store_table_name( $key, $table_name ) {

		if ( empty( $key ) || empty( $table_name ) ) {
			return false;
		}

		$this->tables[ $key ] = $table_name;

		return true;

	}


	/**
	 * Utility method
	 *
	 * Is BuddyPress active?
	 *
	 * @return boolean
	 */
	public function is_bp_active() {

		static $is_active;

		if ( isset( $is_active ) ) {
			return $is_active;
		}

		// if we are here, It is the first time check.
		$is_active = function_exists( 'buddypress' );

		return $is_active;

	}


	/**
	 * Is MediaPress integration enabled for BuddyPress
	 *
	 * It checks if the Gallery is enabled for the members or groups
	 *
	 * @return bool
	 */
	public function is_bp_enabled() {

		if ( $this->is_bp_active() && ( mpp_is_active_component( 'groups' ) || mpp_is_active_component( 'members' ) ) ) {
			return true;
		}

		return false;

	}


	/**
	 * Set theme compat mode
	 *
	 * @param boolean $bool true to save as On else off.
	 */
	public function set_theme_compat( $bool ) {
		$this->using_theme_compat = $bool;
	}


	/**
	 * Check if using the theme compat mode?
	 *
	 * @return bool
	 */
	public function is_using_theme_compat() {
		return $this->using_theme_compat;
	}
}

/**
 * A shortcut function to allow access to the singleton instance of the MediaPress
 *
 * @return MediaPress
 */
function mediapress() {
	return MediaPress::get_instance();
}

// Initialize MediaPress.
mediapress();
