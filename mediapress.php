<?php

/**
 * Plugin Name: MediaPress
 * Version: 1.0 Beta 1
 * Author: Brajesh Singh
 * Plugin URI: http://buddydev.com/mediapress/
 * Author URI: http://buddydev.com
 * Description: MediaPress is the most powerful media plugin for BuddyPress. It allows uploading images(photos), videos, audios, documents 
 *				and can be used to add any type of content. It has a well defined api to allow extending the plugin. 
 * License: GPL2 or above
 * Domain Path: /languages
 * Text Domain: mediapress
 */
// Exit if the file is accessed directly over web
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
 * 
 */



class MediaPress {
	/**
	 *
	 * Private instace of the MediaPress class
	 * 
	 * @var MediaPress
	 */
	private static $instance;
	
	/**
	 * We keep any extra data here to pass around
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
	 * @var type 
	 */
	private $tables = array();
	/**
	 * file system absolute path to the mediapress plugin eg. /home/xyz/public_html/wp-content/plugins/mediapress/
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
	 * relative path to this plugin
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
	public $the_gallery_query; //main gallery query

	/**
	 *
	 * @var MPP_Media_Query 
	 */
	public $the_media_query; //main media query

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
	
	public $current_comment;

	/**
	 * Array of all registered Status object
	 * 
	 * @var MPP_Status[] array of status objects 
	 */
	public $statuses = array();

	/**
	 * Array of all registerd Gallery status
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
	 * Activetypes are sub set of the registered types
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
	public $media_views;
	/**
	 * Multi dimensional array to store the media size specific details
	 * 
	 * @see mpp_register_media_size()
	 * 
	 * @var mixed 
	 */
	public $media_sizes = array();
	//screen identifiers

	public $is_gallery_home		 = false;
	
	/**
	 * We keep the probable current action here and later move to $action if validated
	 * 
	 *  Do not use it in your plugins
	 * 
	 * @var type 
	 */
	private $temp_action	= '';//it should be the action if validated but we can not say that with confident yet 100%. Fo checking current action, please use get_action 
	/**
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
	 * @var type 
	 */
	private $action_variables = array();
	
	/**
	 * Which object type is ebing edited, gallery or media?
	 * 
	 * @var string
	 */
	private $editing_item_type = '';//gallery|media
	
	/**
	 * Restricted media slugs
	 * 
	 * @var array() 
	 */
	private $restricted_media_slugs	 = array( 'edit', 'delete', 'publish', 'reorder', 'manage', 'gallery' );
	
	/**
	 * Contains gallery/media admin menus
	 * 
	 * @var MPP_Menu[] 
	 */
	private $menus		 = array(); // $menus['gallery'], $menus['media']

	
	private function __construct() {
		
		$this->basename = plugin_basename( __FILE__ );
		$this->core_init();
	}

	/**
	 * Factory method to generate/access singleton instance
	 * 
	 * @return MediaPress
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function core_init() {

		$this->plugin_path	 = plugin_dir_path( __FILE__ );
		$this->plugin_url	 = plugin_dir_url( __FILE__ );
		
		global $wpdb;
		//logs table name
		$this->store_table_name( 'logs', $wpdb->prefix . 'mpp_logs' );
		
		register_activation_hook( __FILE__, array( $this, 'do_activation' ) );
		
		add_action( 'plugins_loaded', array( $this, 'load_core' ), 0 );
				
		add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		
		
	}

	public function load_core() {

		$files	 = array(
			
			'core/common/mpp-feedback-functions.php',
			'core/common/mpp-misc-functions.php',
			'core/common/mpp-common-functions.php',
			'core/common/init.php',
			'core/common/class-mpp-cached-media-query.php',
			'core/common/class-mpp-gallery-query.php',
			'core/common/class-mpp-media-query.php',
			'core/common/mpp-nav-functions.php',
			'core/mpp-post-type.php',
			'core/class-mpp-deletion-actions-mapper.php',
			'core/common/class-mpp-taxonomy.php',
			'core/common/class-mpp-menu.php',
			'core/common/class-mpp-features.php',
			'core/common/mpp-taxonomy-functions.php',
			
			
			'core/gallery/class-mpp-gallery.php',
			'core/gallery/mpp-gallery-conditionals.php',
			'core/gallery/mpp-gallery-cover-templates.php',
			'core/gallery/mpp-gallery-functions.php',
			'core/gallery/mpp-gallery-link-template.php',
			'core/gallery/mpp-gallery-meta.php',
			'core/gallery/mpp-gallery-screen.php',
			'core/gallery/mpp-gallery-template-tags.php',
			'core/gallery/mpp-gallery-hooks.php',
			'core/gallery/mpp-gallery-actions.php',
			'core/gallery/mpp-gallery-activity.php',
			'core/gallery/mpp-gallery-template.php',
			
			'core/media/mpp-media-functions.php',
			'core/media/mpp-media-meta.php',
			'core/media/class-mpp-media.php',
			'core/media/mpp-media-template-tags.php',
			'core/media/mpp-media-link-templates.php',
			'core/media/mpp-media-actions.php',
			'core/media/mpp-media-cover-template.php',
			'core/media/mpp-media-activity.php',
			'core/media/mpp-media-hooks.php',
			//media viewer
			'core/media/views/class-mpp-media-view.php',
			'core/media/views/class-mpp-media-view-doc.php', //for doc files
			'core/media/views/class-mpp-media-view-video.php', //for video files
			'core/media/views/class-mpp-media-view-audio.php', //for audio files
			
			//api
			'core/api/mpp-actions-api.php',
			'core/api/mpp-api.php',
			'core/mpp-hooks.php',
			//user
			'core/users/mpp-user-meta.php',
			'core/users/mpp-user-functions.php',
			'core/users/mpp-user-hooks.php',
			//activity, we will move all activity related functions to BuddyPress module in future, that will cut down loading
			'core/activity/class-mpp-activity-media-cache-helper.php',
			'core/activity/mpp-activity-functions.php',
			'core/activity/mpp-activity-actions.php',
			'core/activity/mpp-activity-template.php',
			'core/activity/mpp-activity-hooks.php',
			//comment
			'core/comments/mpp-comment-functions.php',
			'core/comments/class-mpp-comment.php',
			'core/comments/class-mpp-comments-helper.php',
			'core/comments/mpp-comment-template-tags.php',
			//component loader
			
			
			'assets/mpp-script-loader.php',
			
			'core/mpp-ajax.php',
			'core/ajax/class-mpp-ajax-comment-helper.php',
			
			'core/mpp-template-helpers.php',
			'core/mpp-permissions.php',
			
			//storage related
			'core/storage/mpp-storage-functions.php',
			'core/storage/mpp-storage-space-stats-functions.php',
			'core/storage/class-mpp-storage-manager.php',
			'core/storage/class-mpp-local-storage.php',
			
			'core/shortcodes/mpp-shortcode-functions.php',
			'core/shortcodes/mpp-shortcode-gallery-list.php',
			'core/shortcodes/mpp-shortcode-media-list.php',
			
			'core/widgets/mpp-widget-functions.php',
			'core/widgets/mpp-widget-gallery.php',
			'core/widgets/mpp-widget-media.php',
			
			
			//theme compat
			'core/mpp-theme-compat.php',
			
			//logger
			'core/logger/class-mpp-logger.php',
			'core/logger/class-mpp-db-logger.php',
			'core/logger/mpp-logger-functions.php',
			'mpp-core-component.php',
			
		);
		
		if( is_admin() ) {
			$files[] = 'admin/mpp-admin-loader.php';
		}
		//include BuddyPress module loader
		$files[] = 'modules/buddypress/mpp-bp-loader.php';
		
		
		$path		= $this->get_path();
		
		foreach ( $files as $file ) {
			require_once $path . $file;
		}

		do_action( 'mpp_loaded' );
	}

	
	public function do_activation() {
		
		//on activation, create logger table
		require_once $this->plugin_path . 'admin/mpp-admin-install.php';
		mpp_install_db();
	}
	

	/**
	 * Load textdomain
	 * 
	 */
    public function load_textdomain() {
        
		load_plugin_textdomain( 'mediapress', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages'  );
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
	 * @return type
	 */
	public function get_basename() {
		
		return $this->basename;
	}
	/**
	 * Get the url of an asset
	 * 
	 * @param type $key
	 * @return string
	 */
	public function get_asset( $key ) {

		if ( isset( $this->assets[ $key ] ) )
			return $this->assets[ $key ];

		return ''; //empty
	}

	/**
	 * Add an asset to our cached collection
	 * 
	 * @param type $key unique key for the asset
	 * @param type $asset_url
	 * @return string asset url
	 */
	public function add_asset( $key, $asset_url ) {

		$this->assets[ $key ] = $asset_url;
		return $asset_url;
	}

	/**
	 * Set current action
	 * 
	 * @param type $action
	 * @return string
	 */
	public function set_action( $action ) {

		$this->action = $action;
		return $this->action;
	}

	/**
	 * Get current acion
	 * 
	 * @return type
	 */
	public function get_action() {
		
		return $this->action;
	}

	/**
	 * Check for current action
	 * 
	 * @param type $action
	 * @return type
	 */
	public function is_action( $action ) {

		return $this->get_action() == $action;
	}

	
	/**
	 * Set action variables array
	 * 
	 * @param array $av
	 */
	public function set_action_variables( $av = array() ) {
		
		$this->action_variables = $av;
		
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
	 * @param type $pos
	 */
	public function get_action_variable( $pos = 0 ) {
		
		isset( $this->action_variables[$pos] )? $this->action_variables[$pos] : '';
	}
	
	/**
	 * Set the current probably happening action
	 * 
	 * @internal sets temporary action
	 * @param type $action
	 */
	public function _set_temp_action( $action ) {
		
		$this->temp_action = $action;
	}
	/**
	 * For Internal Use
	 * get the current probable action
	 * 
	 */
	public function _get_temp_action( ) {
		
		return $this->temp_action ;
	}
	
	/**
	 * Set edit action
	 * 
	 * @param string $action
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
	 * @param type $action
	 * @return type
	 */
	public function is_edit_action( $action ) {

		return  $this->edit_action == $action ;
	}
	
	/**
	 * Set current editing object type
	 * Bad choice of name, I know
	 * Suggest a better name if you can!
	 * 
	 * @param string $type mpp_get_gallery_post_type() or mpp_get_media_post_type()
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
		
		$this->editing_item_type = $type;
	}
	
	/**
	 * Check if the current object being edited is of given type
	 * 
	 * @param type $type
	 * @return type
	 */
	public function is_editing( $type ) {
		
		return $type == $this->editing_item_type;
		
	}

	/**
	 * Get the given MPP_Menu object by the menu name
	 * 
	 * @param string media|gallery
	 * @return MPP_Menu
	 */
	public function get_menu( $type ) {

		return $this->menus[ $type ];
	}

	/**
	 * Add menu for the Gallery/media
	 * 
	 * @param string $type
	 * @param MPP_Menu $menu
	 */
	public function add_menu( $type, $menu ) {

		$this->menus[ $type ] = $menu;
	}

	/**
	 * Store some arbitrary data
	 * most of the time we use to pass the things around methods like a global
	 * 
	 * @param type $type
	 * @param type $data
	 */
	public function add_data( $type, $data ) {
		
		$this->data[ $type ] = $data;
	}
	/**
	 * Get the arbitrary data stored by the key
	 * 
	 * @param type $type
	 * @return boolean
	 */
	public function get_data( $type ) {
		
		if( isset( $this->data[ $type ] ) )
			return $this->data[ $type ];
		
		return false;
	}
	/**
	 * Reset the data set for this key
	 * 
	 * @param type $type
	 */
	public function reset_data( $type ) {
		
		unset( $this->data[ $type ] );
		
	}
	/**
	 * Get the stored table name
	 * 
	 * @param string $key unique table identifier
	 * 
	 * @return string table name or empty string
	 */
	public function get_table_name( $key ) {
		
		if( isset( $this->tables[ $key ] ) ) {
			return $this->tables[ $key ];
		}
		return '';//invalid table
	}
	
	/**
	 * Store a table name for future reference
	 * 
	 * @param string $key unique table identifier
	 * @param string $table_name actual table name
	 * 
	 * @return boolean true on success false on failure
	 */
	public function store_table_name( $key, $table_name ) {
		
		if( empty( $key ) || empty( $table_name ) ) {
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
		
		if( isset( $is_active ) ) {
			return $is_active;
		}
		
		//if we are here, It is the first time
		
		$is_active = function_exists( 'buddypress' );
		return $is_active;
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

//initialize
mediapress();

