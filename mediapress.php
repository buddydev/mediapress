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
 */
define( 'MPP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MPP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

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
	 * 
	 * @var array mof mixed data 
	 */
	private $data = array();
	/**
	 * file system absolute path to the mediapress plugin eg. /home/xyz/public_html/wp-content/plugins/mediapress/ 
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
	 *
	 * @var MPP_Status[] array of status objects 
	 */
	public $statuses = array();

	/**
	 *
	 * @var MPP_Status[] array of status objects which are valid for gallery 
	 */
	public $gallery_statuses = array();

	/**
	 *
	 * @var MPP_Status[] array of status objects which are valid for Media 
	 */
	public $media_statuses = array();

	/**
	 *
	 * @var MPP_Component[] array of Component objects where keys are component identifier 
	 */
	public $components = array();

	/**
	 *
	 * @var MPP_Type[] array of Media|Gallery type object 
	 */
	public $types = array();
	/**
	 * There are the statuses which are allowed by site admin
	 * 
	 * @var MPP_Status[] array of status objects 
	 */
	public $active_statuses = array();

	

	/**
	 * Array of components which are allowed to have a gallery
	 * 
	 * @var MPP_Component[] array of Component objects where keys are component identifier 
	 */
	public $active_components = array();

	/**
	 * Array of types allowed for the gallery
	 * 
	 * @var MPP_Type[] array of Media|Gallery type object 
	 */
	public $active_types = array();

	/**
	 * An array of registered storage managers
	 * 
	 * @var MPP_Storage_Manager[] 
	 */
	public $storage_managers = array();

	/**
	 *
	 * @var MPP_Media_View 
	 */
	public $media_views;
	/**
	 * Multi dimensional array to store the media size specific details
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
		
		$this->basename = basename( MPP_PLUGIN_DIR ) . '/' . basename( __FILE__ );
		$this->core_init();
	}

	/**
	 * Factory method to generate/access singleton instance
	 * 
	 * @return MediaPress
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) )
			self::$instance = new self();

		return self::$instance;
	}

	public function core_init() {

		$this->plugin_path	 = plugin_dir_path( __FILE__ );
		$this->plugin_url	 = plugin_dir_url( __FILE__ );

		add_action( 'bp_include', array( $this, 'load_core' ) );
		
		add_action( 'bp_include', array( $this, 'load_textdomain' ) );
		
		add_action( 'init', array( $this, 'init' ), 2 );

		
	}

	public function load_core() {

		$files	 = array(
			
			'core/common/feedback.php',
			'core/common/common.sql.php',
			'core/common/functions.php',
			'core/common/init.php',
			'core/common/query-gallery.php',
			'core/common/query-media.php',
			
			'core/post-type.php',
			'core/common/taxonomy.php',
			'core/common/class-menu-manager.php',
			'core/common/class-features.php',
			
			
			
			'core/gallery/class-gallery.php',
			'core/gallery/conditionals.php',
			'core/gallery/cover-templates.php',
			'core/gallery/functions.php',
			'core/gallery/link-template.php',
			'core/gallery/meta.php',
			'core/gallery/screen.php',
			'core/gallery/template-tags.php',
			'core/gallery/hooks.php',
			'core/gallery/actions.php',
			
			'core/media/functions.php',
			'core/media/meta.php',
			'core/media/class-media.php',
			'core/media/template-tags.php',
			'core/media/link-templates.php',
			'core/media/actions.php',
			'core/media/cover-template.php',
			//media viewer
			'core/media/views/media-view-base.php',
			'core/media/views/media-view-doc.php', //for doc files
			'core/media/views/media-view-video.php', //for video files
			'core/media/views/media-view-audio.php', //for audio files
			
			//api
			'core/api/api.php',
			'core/hooks.php',
			//user
			'core/users/meta.php',
			'core/users/hooks.php',
			//activity
			'core/activity/functions.php',
			'core/activity/actions.php',
			'core/activity/template.php',
			'core/activity/hooks.php',
			//comment
			'core/comments/functions.php',
			'core/comments/template-tags.php',
			//component loader
			'loader.php',
			
			'assets/script-loader.php',
			
			'core/ajax.php',
			'core/template-helpers.php',
			'core/permissions.php',
			
			//storage related
			'core/storage/functions.php',
			'core/storage/space-stats.php',
			'core/storage/storage-manager.php',
			'core/storage/local-storage.php',
			
			'core/shortcodes/functions.php',
			'core/shortcodes/gallery-list.php',
			'core/shortcodes/media-list.php',
			
			'core/widgets/functions.php',
			'core/widgets/widget-gallery.php',
			'core/widgets/widget-media.php',
			//extensions
			'modules/groups/loader.php',
			
			//theme compat
			'core/theme-compat.php'
		);
		
		if( is_admin() )
			$files[] = 'admin/init.php';
		
		$bp_files = array();
		
		$files		= array_merge( $files, $bp_files );
		$path		= $this->get_path();
		
		foreach ( $files as $file )
			require_once $path . $file;

		do_action( 'mpp_loaded' );
	}

	public function init() {

		//allow to hook
		do_action( 'mpp_init' );
	}

 
    public function load_textdomain() {
        
		$locale = apply_filters( 'mpp_textdomain_get_locale', get_locale() );
        
		$mofile_default = '';
		
		// if load .mo file
		if ( ! empty( $locale ) ) {
			$mofile_default = sprintf( '%slanguages/%s.mo', $this->plugin_path, $locale );
              
		$mofile = apply_filters( 'mpp_textdomain_mofile', $mofile_default );
		
        if ( is_readable( $mofile ) ) {
                    // make sure file exists, and is readable
			load_textdomain( 'mediapress', $mofile );
		}
	}
}
	/**
	 * Get the url of the MediaPress plugin directory
	 * 
	 * @return string
	 */
	public function get_url() {
		
		return $this->plugin_url;
		
	}

	/**
	 * Get the absolute path to the mediapress plugin directory
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
		
		$this->data[$type] = $data;
	}
	/**
	 * Get the arbitrary data stored by the key
	 * 
	 * @param type $type
	 * @return boolean
	 */
	public function get_data( $type ) {
		
		if( isset( $this->data[$type] ) )
			return $this->data[$type];
		
		return false;
	}
	/**
	 * Reset the data set for this key
	 * 
	 * @param type $type
	 */
	public function reset_data( $type ) {
		
		unset( $this->data[$type] );
		
	}
}

/**
 * A shortcut function to allow access to the singleton instance of the mediapress
 * 
 * @return MediaPress
 */
function mediapress() {

	return MediaPress::get_instance();
}

//initialize
mediapress();

