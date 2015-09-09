<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

class MPP_BuddyPress_Helper {
	
	private static $instance = null;
	
	private function __construct() {
		
		$this->setup();
	}
	/**
	 * Get singleton instance
	 * 
	 * @return MPP_BuddyPress_Helper
	 */
	public static function get_instance() {
		if( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
		
	}
	
	private function setup() {
				
		if( !  mediapress()->is_bp_active() ) {
			return ;
		}
		
		add_action( 'bp_include', array( $this, 'load' ), 2 );
		
		add_action( 'mpp_init', array( $this, 'init' ) );
		
	}
	
	public function load() {
		

		
		$path = mediapress()->get_path() . 'modules/buddypress/';
		
		$files = array(
			'mpp-bp-component.php',
			//extensions
			'groups/mpp-bp-groups-loader.php',
		);
		
		foreach ( $files as $file ) {
			require_once $path . $file;
		}
		//MediaPress BuddyPress module is loaded now
		do_action( 'mpp_buddypress_module_loaded' );
	}
	
	public function init() {
		
		//Register status
		//if friends component is active, only then
		mpp_register_status( array(
				'key'				=> 'friendsonly',
				'label'				=> __( 'Friends Only', 'mediapress' ),
				'labels'			=> array( 
										'singular_name' => __( 'Friends Only', 'mediapress' ),
										'plural_name'	=> __( 'Friends Only', 'mediapress' )
				),
				'description'		=> __( 'Friends Only Privacy Type', 'mediapress' ),
				'callback'			=> 'mpp_check_friends_access',
				'activity_privacy'	=> 'friends',
		));
		
		//if followers component is active only then
		if( function_exists( 'bp_follow_is_following' ) ) {

			mpp_register_status( array(
					'key'				=> 'followersonly',
					'label'				=> __( 'Followers Only', 'mediapress' ),
					'labels'			=> array( 
											'singular_name' => __( 'Followers Only', 'mediapress' ),
											'plural_name'	=> __( 'Followers Only', 'mediapress' )
					),
					'description'		=> __( 'Followers Only Privacy Type', 'mediapress' ),
					'callback'			=> 'mpp_check_followers_access',
					'activity_privacy'	=> 'followers',
			));
			
			mpp_register_status( array(
					'key'				=> 'followingonly',
					'label'				=> __( 'Persons I Follow', 'mediapress' ),
					'labels'			=> array( 
											'singular_name' => __( 'Persons I Follow', 'mediapress' ),
											'plural_name'	=> __( 'Persons I Follow', 'mediapress' )
					),
					'description'		=> __( 'Following Only Privacy Type', 'mediapress' ),
					'callback'			=> 'mpp_check_following_access',
					'activity_privacy'	=> 'following', //tthis is not implemented by BP Activity privacy at the moment
			));

		}//end of check for followers plugin
		
		mpp_register_component( array(
				'key'           => 'members',
				'label'         => __( 'User', 'mediapress' ),
				'labels'		=> array(
									'singular_name'	=> __( 'User', 'mediapress' ),
									'plural_name'	=> __( 'Users', 'mediapress' )
				),
				'description'   => __( 'User Galleries', 'mediapress' ),
		) );
	
		//add support

		mpp_component_register_status( 'members', 'public' );
		mpp_component_register_status( 'members', 'private' );
		mpp_component_register_status( 'members', 'loggedin' );
	
	
		if ( function_exists('bp_is_active') && bp_is_active( 'friends' ) ) {
			mpp_component_register_status( 'members', 'friendsonly' );
		}

		//allow members component to support the followers privacy 
		if ( function_exists( 'bp_follow_is_following' ) ) {

			mpp_component_register_status( 'members', 'followersonly' );
			mpp_component_register_status( 'members', 'followingonly' );

		}

	
		//register type support
		mpp_component_register_type( 'members', 'photo' );
		mpp_component_register_type( 'members', 'audio' );
		mpp_component_register_type( 'members', 'video' );
		mpp_component_register_type( 'members', 'doc' );
	

	
		mpp_register_component( array(
				'key'           => 'groups',
				'label'         => __( 'Groups', 'mediapress' ),
				'labels'		=> array(
									'singular_name'	=> __( 'Group', 'mediapress' ),
									'plural_name'	=> __( 'Groups', 'mediapress' )
				),
				'description'   => __( 'Groups Galleries', 'mediapress' ),
		) );
   
	
		mpp_component_register_status( 'groups', 'public' );
		mpp_component_register_status( 'groups', 'private' );
		mpp_component_register_status( 'groups', 'loggedin' );
		mpp_component_register_status( 'groups', 'groupsonly' );         
		//register media sizes

		mpp_component_register_type( 'groups', 'photo' );
		mpp_component_register_type( 'groups', 'audio' );
		mpp_component_register_type( 'groups', 'video' );
		mpp_component_register_type( 'groups', 'doc' );
		
	}
	
}


MPP_BuddyPress_Helper::get_instance();
