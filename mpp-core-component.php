<?php
/**
 * MediaPress Component
 *
 * @package MediaPress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MediaPress Core Component
 * Sets up Galleries/Media for the current page
 */
class MPP_Core_Component {
	/**
	 * Singleton instance
	 *
	 * @var $this
	 */
	private static $instance = null;

	/**
	 * Global MediaQuery for single media page
	 *
	 * @var MPP_Media_Query
	 */
	private $single_media_query = null;
	/**
	 * Array of names not available as gallery names
	 *
	 * @var array
	 */
	public $forbidden_name;
	/**
	 * Allowed status.
	 *
	 * @var string
	 */
	public $valid_status;

	/**
	 * Paginated page no. for gallery
	 *
	 * @var int
	 */
	private $gpage = 0;

	/**
	 * Paginated page no. for single Media
	 *
	 * @var int
	 */
	private $mpage = 0;

	/**
	 * What are the media/gallery status which are allowed to the current user for the current component?
	 *
	 * @var array of strings(status strings like array('private', 'public')
	 */
	private $accessible_statuses = array();

	/**
	 * Current component the gallery is associated, could be 'members','groups' etc
	 *
	 * @var string
	 */
	private $component = '';

	/**
	 * The current component ID(owner of gallery/media), It could be user id or group id depending on the context
	 *
	 * @var int
	 */
	private $component_id = 0;

	/**
	 * Status
	 *
	 * @var string|array of statuses e.g 'public' or array('public', 'private' , 'loggedin')..etc
	 */
	private $status = array();

	/**
	 * Type
	 *
	 * @var string|array of types eg. 'photo' or array( 'photo', 'audio', 'video', 'doc') etc
	 */
	private $type = '';

	/**
	 * Temporary gallery id
	 *
	 * @var int
	 */
	private $gallery_id = 0;

	/**
	 * Temp media id.
	 *
	 * @var int
	 */
	private $media_id = 0;

	/**
	 * Current MediaPress action 'create/upload/manage/
	 *
	 * @var string
	 */
	private $current_action = '';

	/**
	 * What type of management option is this? delete/edit/reorder etc
	 *
	 * @var string
	 */
	private $current_manage_action = '';

	/**
	 * MediaPress action variables
	 *
	 * @var array
	 */
	private $action_variables = array();

	/**
	 * Get the singleton instance
	 *
	 * @return MPP_Core_Component
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Everything starts here
	 */
	private function __construct() {
		$this->setup();
	}

	/**
	 * Setup our components
	 */
	private function setup() {
		// add rewrite end point for manage gallery/media.
		add_action( 'mpp_setup', array( $this, 'add_rewrite_endpoints' ) );
		// setup galleries.
		add_action( 'mpp_actions', array( $this, 'setup_globals' ), 0 );

		// add context menu to user & groups sub nav.
		add_action( 'bp_member_plugin_options_nav', array( $this, 'context_menu_edit' ) );
		add_action( 'mpp_group_nav', array( $this, 'context_menu_edit' ) );

		add_filter( 'redirect_canonical', array( $this, 'redirect_canonical' ), 10, 2 );
		add_filter( 'mpp_template_redirect', array( $this, 'redirect_attachment' ) );

	}

	/**
	 * Setup everything for BuddyPress Specific installation
	 *
	 * @param array $args array of global settings.
	 */
	public function setup_globals( $args = array() ) {

		// get current component/component_id.
		$this->component    = mpp_get_current_component();
		$this->component_id = mpp_get_current_component_id();

		// override the component id if we are on user page.
		if ( function_exists( 'bp_is_user' ) && bp_is_user() ) {
			$this->component_id = bp_displayed_user_id();
		}

		// let us setup global queries.
		// initialize query objects.
		mediapress()->the_gallery_query = new MPP_Gallery_Query();
		mediapress()->the_media_query   = new MPP_Media_Query();

		if ( ! mpp_is_enabled( $this->component, $this->component_id ) ) {
			return; // do not setup.
		}
		// set the status types allowed for current user.
		$this->accessible_statuses = mpp_get_accessible_statuses( $this->component, $this->component_id, get_current_user_id() );

		$this->status = $this->accessible_statuses;

		// is this sitewide gallery?
		if ( mpp_is_enabled( 'sitewide', $this->component_id ) ) {
			$this->setup_sitewide_gallery();
		}

		// I know we are not using if-else, check setup_root_gallery() to know why.
		if ( mpp_is_gallery_component() ) {
			$this->action_variables = buddypress()->action_variables;
			// add the current action at the beginning of the stack, we are doing it to unify the things for User gallery and component gallery.
			array_unshift( $this->action_variables, bp_current_action() );

			$this->setup_user_gallery();
		} elseif ( mpp_is_component_gallery() ) {
			// are we on component gallery like groups or events etc?
			$this->action_variables = buddypress()->action_variables;

			$this->setup_component_gallery();
		}
		// fire this action to allow plugins do their own thing on mediapress()->the_gallery_query.
		do_action( 'mpp_setup_gallery_query', $this );

		// once we are here, the basic action variables for mediapress are setup and so
		// we can go ahead and test for the single gallery/media.
		$mp = mediapress();
		// setup Single Gallery specific things.
		if ( mpp_is_single_gallery() ) {

			// will save some db query with a few themes.
			if ( has_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' ) ) {
				remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
			}

			$current_action = $this->current_action;

			// setup and see the actions etc to find out what we need to do
			// if it is one of the edit actions, It was already taken care of, don't do anything.
			//
			// check if we are on management screen?
			if ( 'manage' === $this->current_action ) {
				// this is media management page.
				$mp->set_editing( 'gallery' );

				$mp->set_action( 'manage' );
				$mp->set_edit_action( $this->current_manage_action );

				// on edit bulk media page.
				if ( $mp->is_edit_action( 'edit' ) ) {
					$this->setup_gallery_media_query();
				}
			} elseif ( $media = $this->get_media_id( $this->current_action, $this->component, $this->component_id ) ) {
				// yes, It is single media.
				$this->setup_single_media_query( $media );
			} else {
				// we already know it is single gallery, so let us setup the media list query.
				$this->setup_gallery_media_query();
			}
		}

		do_action( 'mpp_setup_globals' );

	}

	/**
	 * Setup Sitewide/root Galleries
	 *
	 * @todo Make it work in 1.1 when we introduce site galleries
	 */
	public function setup_sitewide_gallery() {

		global $wp_query;

		// this is our single gallery page.
		if ( mpp_is_sitewide_gallery_component() ) {

			$gallery_id = get_queried_object_id();
			// setup gallery query.
			mediapress()->the_gallery_query = MPP_Gallery_Query::build_from_wp_query( $wp_query );
			mediapress()->current_gallery   = mpp_get_gallery( $gallery_id );

			// check for end points to edit.
			if ( get_query_var( 'manage' ) ) {
				$action                      = get_query_var( 'manage' );
				$this->current_action        = 'manage';
				$this->current_manage_action = $action;
			} elseif ( get_query_var( 'media' ) ) {
				$action                 = $this->parse_media_action( get_query_var( 'media' ) );
				$this->action_variables = $action;

				$this->current_action        = $action[0];
				$this->current_manage_action = '';
				// push empty string at top to make compatible with bp returned action variables array.
				array_unshift( $this->action_variables, '' );
			} elseif ( get_query_var( 'paged' ) ) {
				$this->mpage = absint( get_query_var( 'paged' ) );
			}
		} elseif ( is_post_type_archive( mpp_get_gallery_post_type() ) ) {

			$status = array();
			if ( mpp_is_active_status( 'public' ) ) {
				$status[] = 'public';
			}

			if ( is_user_logged_in() && mpp_is_active_status( 'loggedin' ) ) {
				$status[] = 'loggedin';
			}

			mediapress()->the_gallery_query = new MPP_Gallery_Query( array(
				'status' => $status,
			) );
		}

	}

	/**
	 * Setup Query for User profile Galleries
	 */
	public function setup_user_gallery() {

		if ( mpp_is_enabled( 'members', bp_displayed_user_id() ) && function_exists( 'bp_is_user' ) && bp_is_user() ) {
			// is User Gallery enabled? and are we on the user section?
			$this->component = 'members';
			// initialize for members component.
			$this->init();
			// in this case, we are on the gallery directory, check if we have it enabled?
		} elseif ( mpp_has_gallery_directory() ) {
			$this->setup_gallery_directory_query();
		}

		// finally setup galleries.
		$this->setup_galleries();
	}

	/**
	 * Setup query args for gallery directory when BuddyPress is active
	 */
	public function setup_gallery_directory_query() {
		// make the query and setup.
		mediapress()->is_directory = true;

		$status = array();
		if ( mpp_is_active_status( 'public' ) ) {
			$status[] = 'public';
		}

		if ( is_user_logged_in() && mpp_is_active_status( 'loggedin' ) ) {
			$status[] = 'loggedin';
		}

		$this->component    = ''; // reset.
		$this->component_id = 0; // reset.
		$this->status       = $status;
		$this->gpage        = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

	}

	/**
	 * Setup gallery for components like groups/events etc
	 */
	public function setup_component_gallery() {

		// initialize.
		$this->init();
		// setup.
		$this->setup_galleries();

	}

	/**
	 * Initilize settings based on the current stack of action variables and component etc
	 */
	public function init() {

		$current_action = $this->get_variable( 0 );
		// on create or upload we don't need to setup Media or Gallery Query.
		if ( 'create' === $current_action || 'upload' === $current_action ) {
			mediapress()->set_action( $current_action );
			mediapress()->set_edit_action( $current_action );

			return;
		}

		// check for type/status.
		$type_or_status = $this->get_variable( 1 );

		if ( 'type' === $current_action && $type_or_status && mpp_is_active_type( $type_or_status ) ) {
			$this->type                   = $type_or_status;
			mediapress()->is_gallery_home = true;
		} elseif ( 'status' === $current_action && $type_or_status && mpp_is_active_status( $type_or_status ) ) {
			$this->status                 = $type_or_status;
			mediapress()->is_gallery_home = true;
		} elseif ( $gallery = mpp_gallery_exists( $current_action, $this->component, $this->component_id ) ) {
			// Are we looking at single gallery? or Media?
			// current action in this case is checked for being  a gallery slug
			// setup current gallery & gallery query.
			mediapress()->current_gallery = mpp_get_gallery( $gallery );
			$this->gallery_id             = $gallery->ID;

			$this->current_action        = $this->get_variable( 1 );
			$this->current_manage_action = $this->get_variable( 2 );

			// setup pagination for single gallery media.
			if ( $this->get_variable( 1 ) === 'page' && $this->get_variable( 2 ) > 0 ) {
				$this->mpage = (int) $this->get_variable( 2 );
			}
		} else {
			if ( $this->get_variable( 0 ) === 'page' && $this->get_variable( 1 ) > 0 ) {
				$this->gpage = (int) $this->get_variable( 1 );
			}
			// set it is the user galleries list view.
			mediapress()->is_gallery_home = true;
		}

		// check and setup pagination args for status/type archives.
		if ( $this->get_variable( 2 ) === 'page' && $this->get_variable( 3 ) > 0 ) {
			$this->gpage = (int) $this->get_variable( 3 );
		}

	}

	/**
	 * Utility function to Setup current gallery Query based on the various args
	 */
	public function setup_galleries() {

		// check if it is single gallery query.
		if ( $this->gallery_id ) {
			mediapress()->the_gallery_query = new MPP_Gallery_Query( array(
				'id' => $this->gallery_id,
			) );

			return; // no need to proceed further.
		}

		// if we are here, it is the gallery list page for user or component or directory.
		$args = array(
			'status'       => $this->status,
			'type'         => $this->type,
			'component'    => $this->component,
			'component_id' => $this->component_id,
			'page'         => $this->gpage,
		);

		// let the intelligent ones play with it.
		$args = apply_filters( 'mpp_main_gallery_query_args', $args );

		// filter out the empty things.
		$args = array_filter( $args );

		mediapress()->the_gallery_query = new MPP_Gallery_Query( $args );

	}

	/**
	 * Add the Edit context menu when a user is on single gallery
	 */
	public function context_menu_edit() {

		if ( ! mpp_is_single_gallery() ) {
			return;
		}

		if ( mpp_is_gallery_management() || mpp_is_media_management() ) {
			return;
		}

		$links = '';

		if ( mpp_is_single_media() && mpp_user_can_edit_media( mpp_get_current_media_id(), get_current_user_id() ) ) {

			$url = mpp_get_media_edit_url();
			$links .= sprintf( '<li><a href="%1$s" title ="%2$s"> %3$s</a></li>', $url, _x( 'Edit media', 'Profile context menu rel', 'mediapress' ), _x( 'Edit', 'Profile context menu media edit label', 'mediapress' ) );

		} else {

			if ( mpp_user_can_edit_gallery( mpp_get_current_gallery_id() ) ) {
				$url = mpp_get_gallery_edit_media_url( mpp_get_current_gallery() ); // bulk edit media url.
				$links .= sprintf( '<li><a href="%1$s" title ="%2$s"> %3$s</a></li>', $url, _x( 'Edit Gallery', 'Profile context menu rel attribute', 'mediapress' ), _x( 'Edit', 'Profile contextual edit gallery menu label', 'mediapress' ) );
			}

			$gallery = mpp_get_current_gallery();

			if ( mpp_user_can_upload( $gallery->component, $gallery->component_id, $gallery ) ) {
				$links .= sprintf( '<li><a href="%1$s" title ="%2$s"> %3$s</a></li>', mpp_get_gallery_add_media_url( mpp_get_current_gallery() ), _x( 'Add Media', 'Profile context menu rel attribute', 'mediapress' ), _x( 'Add Media', 'Profile contextual add media  menu label', 'mediapress' ) );
			}
		}

		echo $links;

	}

	/**
	 * Setup title for various screens
	 */
	public function setup_title() {
	}

	/**
	 * For sitewide galleries, we add rewrite endpoints
	 */
	public function add_rewrite_endpoints() {

		if ( ! mpp_is_active_component( 'sitewide' ) ) {
			return;
		}

		add_rewrite_endpoint( 'manage', EP_PERMALINK );
		add_rewrite_endpoint( 'media', EP_PERMALINK );
	}

	/**
	 * Set up query for fetching single media
	 *
	 * @param MPP_Media $media MediaPress media object.
	 */
	public function setup_single_media_query( $media ) {

		$mp = mediapress();


		if ( ! is_null( $this->single_media_query ) ) {

			$mp->the_media_query = $this->single_media_query;

		} else {
			$mp->the_media_query = new MPP_Media_Query(
				array(
					'id' => $media->ID,
				)
			);
		}

		$mp->current_media = mpp_get_media( $media );

		// now check if we are on edit page nor not?
		$this->current_action = isset( $this->action_variables[2] ) ? $this->action_variables[2] : '';

		if ( 'edit' === $this->current_action ) {
			$mp->set_editing( 'media' );
			// it is single media edit.
			$mp->set_action( 'edit' );

			$edit_action = isset( $this->action_variables[3] ) ? $this->action_variables[3] : 'edit';
			$mp->set_edit_action( $edit_action );
		}

	}

	/**
	 * Setup query for listing Media inside single gallery
	 */
	public function setup_gallery_media_query() {

		// since we already know that this is a single gallery, It muist be media list screen.
		$args = array(
			'component_id' => $this->component_id,
			'component'    => $this->component,
			'gallery_id'   => mpp_get_current_gallery_id(),
			'status'       => $this->accessible_statuses,
		);

		if ( $this->mpage ) {
			$args['page'] = absint( $this->mpage );
		}

		// let them do the magic if they want to.
		$args = apply_filters( 'mpp_main_media_query_args', $args );
		// remove empty.
		$args = array_filter( $args );
		// we are on User gallery home page
		// we do need to check for the access level here and pass it to the query.
		mediapress()->the_media_query = new MPP_Media_Query( $args );
	}

	/**
	 * Check and get single media id else false
	 *
	 * @param string $slug media slug.
	 * @param string $component component type(members|groups etc).
	 * @param int    $component_id numeric context based component id.
	 *
	 * @return boolean|Object post object.
	 */
	public function get_media_id( $slug, $component, $component_id ) {

		if ( ! $component_id || ! $slug || ! $component ) {
			return false;
		}

		global $wpdb;
		// on single post, why bother about the component etc, that makes our query slow, just do a simple post query instead.
		$post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_name = %s and post_type = %s ", $slug, mpp_get_media_post_type() ) );

		return $post;

	}

	/**
	 * Parsing permalinks parts for sitewide gallery actions
	 *
	 * @param string $action_string action sttring.
	 *
	 * @return array
	 */
	private function parse_media_action( $action_string ) {
		// string anything after?
		$actions = explode( '/', $action_string );
		$actions = array_filter( $actions );

		return $actions;

	}

	/**
	 * Handle http://site.com/gallery/xyzgallery/page/{page_number}/
	 *
	 * @param string $redirect_url redirect url.
	 * @param string $requested_url requested url.
	 *
	 * @return string
	 */
	public function redirect_canonical( $redirect_url, $requested_url ) {

		if ( is_singular( mpp_get_gallery_post_type() ) && get_query_var( 'paged' ) ) {
			return $requested_url;
		}

		return $redirect_url;

	}

	/**
	 * Redirect attachment link to single media page
	 */
	public function redirect_attachment() {

		if ( is_attachment() && mpp_is_valid_media( get_queried_object_id() ) ) {
			$redirect_url = mpp_get_media_url( get_queried_object() );
			mpp_redirect( $redirect_url, 301 );
		}

	}

	/**
	 * Return the action variable at given position or empty string
	 *
	 * @param int $position numeric position.
	 *
	 * @return string
	 */
	public function get_variable( $position ) {

		if ( isset( $this->action_variables[ $position ] ) ) {
			return $this->action_variables[ $position ];
		}

		return '';

	}
}

// setup core gallery component.
MPP_Core_Component::get_instance();
