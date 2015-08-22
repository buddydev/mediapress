<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/** Anything admin related here
 * */

class MPP_Admin {
    /**
	 *
	 * @var string parent menu slug 
	 */
    private $menu_slug = '';//
	/**
	 *
	 * @var MPP_Admin_Settings_Page 
	 */
	private $page;
	
    private static $instance = null;
    
	
    private function __construct() {
        
		$this->menu_slug = 'edit.php?post_type='. mpp_get_gallery_post_type();
		
    }
    /**
     * 
     * @return MPP_Admin
     */
    public static function get_instance() {
        
        if( is_null( self::$instance ) ) {
            self::$instance = new self();
		}
		
        return self::$instance;
    }
		
	/**
	 * Get the parent slug for adding new admin menu items 
	 * @return string
	 */
	public function get_menu_slug() {
		
		return $this->menu_slug;
	}
	
	/**
	 * Keep a reference to our settings page object here
	 * 
	 * @param MPP_Admin_Settings_Page $page
	 */
	public function set_page( $page ) {
		$this->page = $page;
	}
	/**
	 * 
	 * @return MPP_Admin_Settings_Page 
	 */
	public function get_page() {
		return $this->page;
	}
}	
/**
 * 
 * @return MPP_Admin
 */	
function mpp_admin() {
	
	return MPP_Admin::get_instance();
}
/**
 * Handle admin Settings Screen
 */


class MPP_Admin_Settings_Helper {
    
    
    private static $instance = null;
    /**
	 *
	 * @var MPP_Admin_Settings_Page 
	 */
	private $page;
	
    private function __construct() {
     	
        add_action( 'admin_init', array( $this, 'init' ) );
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
       // add_action( 'admin_enqueue_scripts', array( $this, 'load_js' ) );
        //add_action( 'admin_enqueue_scripts', array( $this, 'load_css' ) );
        
    }
    /**
     * 
     * @return MPP_Admin_Settings_Helper
     */
    public static function get_instance() {
        
        if( is_null( self::$instance ) ) {
            self::$instance = new self();
		}
		
        return self::$instance;
    }
	
    /**
	 * Initialize the admin settings panel and fields
	 * 
	 */
	public function init() {
		
		//'mpp-settings' is used as page slug as well as option to store in the database
		$page = new MPP_Admin_Settings_Page( 'mpp-settings' );//MPP_Admin_Page( 'mpp-settings' );
		
		//Add a panel to to the admin
		//A panel is a Tab and what coms under that tab
		$panel = $page->add_panel( 'general', _x( 'General', 'Admin settings panel title', 'mediapress' ) );
		
		//A panel can contain one or more sections. each sections or
		
		$section = $panel->add_section( 'component-settings', _x( 'Component Settings', 'Admin settings section title', 'mediapress' ) );
		
		$components_details = array();
		
		$components = mpp_get_registered_components();
		
		foreach( $components as $key => $component ) {
			
			$components_details[ $key ] = $component->label;
		}
		
		$component_keys = array_keys( $components_details );
		
		$default_components = array_combine( $component_keys, $component_keys );
		
		$active_components = array_keys( mpp_get_active_components() );
		
		if( ! empty( $active_components ) ) {
		
			$default_components = array_combine( $active_components, $active_components );
		}
		
		$section->add_field( array(
				'name'		=> 'active_components',
				//'id'=>	'active_components',
				'label'		=> _x( 'Enable Galleries for?', 'Admin settings', 'mediapress' ),
				'type'		=> 'multicheck',
				'options'	=> $components_details,
				'default'	=> $default_components, //array( 'members' => 'members' ), //enable members component by default
			) );
		
		//second section
		//
		//enabled status
		//status
		$available_media_stati = mpp_get_registered_statuses();
		
		$options = array();
		
		foreach( $available_media_stati as $key => $available_media_status ) {
			$options[ $key ] = $available_media_status->get_label ();
		}	
		
		$panel->add_section( 'status-settings', _x( 'Privacy Settings', 'Admin settings section title', 'mediapress' ) )
				->add_field( array(
						'name'			=> 'default_status',
						'label'			=> _x( 'Default status for Gallery/Media', 'Admin settings', 'mediapress' ), 
						'description'	=> _x( 'It will be used when we are not allowed to get the status from user', 'Admin settings', 'mediapress' ),
						'default'		=> mpp_get_default_status(),
						'options'		=> $options,
						'type'			=> 'select'
				) );
		
		$section = $panel->get_section( 'status-settings' );
		
		$registered_statuses = mpp_get_registered_statuses();
		
		$status_info = array();
		
		foreach( $registered_statuses as $key => $status ) {
		
			$status_info[ $key ] = $status->label;
		}	
				
		$active_statuses = array_keys( mpp_get_active_statuses() );
		
		$status_keys = array_keys( $status_info );
		
		$default_statuses = array_combine( $status_keys, $status_keys );
		
		if( ! empty( $active_statuses ) ) {
			$default_statuses = array_combine( $active_statuses, $active_statuses );
		}	
		
		$section->add_field( array(
				'name'		=> 'active_statuses',
				//'id'=>	'active_components',
				'label'		=> _x( 'Enabled Media/Gallery Statuses', 'Admin settings', 'mediapress' ),
				'type'		=> 'multicheck',
				'options'	=> $status_info,
				'default'	=> $default_statuses
		) );
		
		//3rd section
		
		//enabled type?
		
		
		//types
				
		$section = $panel->add_section( 'types-settings', _x( 'Media Type settings', 'Admin settings section title', 'mediapress' ) );
		$valid_types = mpp_get_registered_types();
		
		$options = array();
		$types_info = array();
		$extension_fields = array();
		
		foreach( $valid_types as $type => $type_object ) {
			
			$types_info[$type] = $type_object->label;
			
			$extension_fields [] =	 array( 
				'id'				=> 'extensions-'. $type,
				'name'				=> 'extensions',
				'label'				=> sprintf( _x( 'Allowed extensions for %s', 'Settings page', 'mediapress' ), $type ),
				'description'		=> sprintf( _x( 'Use comma separated list of file extensions for %s ', 'Settings page', 'mediapress '), $type ),
				'default'			=> join( ',', (array) $type_object->get_registered_extensions() ),
				'type'				=> 'extensions',
				'extra'				=> array( 'key' => $type, 'name' => 'extensions' )
				
			) ;
		}
		
		$type_keys =  array_keys( $types_info  );
		
		$default_types = array_combine( $type_keys, $type_keys );
		
		$active_types = array_keys( mpp_get_active_types() );
		
		if( ! empty( $active_types ) ) {
		
			$default_types = array_combine( $active_types, $active_types );
		}
		
		$section->add_field( array(
				'name'		=> 'active_types',
				'label'		=> _x( 'Enabled Media/Gallery Types', 'Settings page', 'mediapress' ),
				'type'		=> 'multicheck',
				'options'	=> $types_info,
				'default'	=> $default_types, //array( 'photo' => 'photo', 'audio' => 'audio', 'video' => 'video' )
		) );
		/*
		$section->add_field( array(
						'name'			=> 'allow_mixed_gallery',
						'label'			=> __( 'Allow mixed Galleries?', 'mediapress' ),
						'type'			=> 'radio',
						'default'		=> 0,//10 MB
						'options'		=> array(
											1 => 'Yes',
											0 => 'No',
											
										),
						'description'	=> __( 'Please keep it disabled. It is not truly enabled at the moment', 'mediapress' )				
						
		) );*/
		
		$section->add_fields( $extension_fields );
		

		//4th section
		//enabled storage
		//Storage section
		$panel->add_section( 'storage-settings', _x( 'Storage Settings', 'Settings page section title', 'mediapress' ) )
					->add_field( array(
						'name'			=> 'mpp_upload_space',
						'label'			=> _x( 'maximum Upload space per user(MB)?', 'Admin storage settings',  'mediapress' ),
						'type'			=> 'text',
						'default'		=> 10,//10 MB
						
					) )
					->add_field( array(
						'name'			=> 'mpp_upload_space_groups',
						'label'			=> _x( 'maximum Upload space per group(MB)?', 'Admin storage settings', 'mediapress' ),
						'type'			=> 'text',
						'default'		=> 10,//10 MB
						
					) )
					->add_field( array(
						'name'			=> 'show_upload_quota',
						'label'			=> _x( 'Show upload Quota?', 'Admin storage settings', 'mediapress' ),
						'type'			=> 'radio',
						'default'		=> 0,//10 MB
						'options'		=> array(
							1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
							0 => _x( 'No', 'Admin settings option', 'mediapress' ),
											
						)
						
					) );
		$storage_methods = mpp_get_registered_storage_managers();
		
		$storage_methods = array_keys( $storage_methods );
		
		$storage_method_options = array();
		
		foreach( $storage_methods as $storage_method ) {
			$storage_method_options[$storage_method] = ucfirst ( $storage_method );
		}
					//->add_field();
		
		$panel->get_section( 'storage-settings' )->add_field( array(
						'name'		=> 'default_storage',
						'label'		=> _x( 'Which should be marked as default storage?', 'Admin storage settings', 'mediapress' ),
						'default'	=> mpp_get_default_storage_method(),
						'options'	=> $storage_method_options ,
						'type'		=> 'radio'
					) );
		

		//5th section
		
		//activity settings
		$activity_section = $panel->add_section( 'activity-settings', _x( 'Activity Settings', 'Admin settings section title', 'mediapress' ) );
		
		
		$activity_section->add_field( array(
						'name'			=> 'activity_upload',
						'label'			=> _x( 'Allow Activity Upload?', 'Admin settings', 'mediapress' ),
						'desc'			=> _x( 'Allow users to uploading from Activity screen?', 'Admin settings', 'mediapress' ),
						'default'		=> 1,
						'type'			=> 'radio',
						'options'		=> array(
							1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
							0 => _x( 'No', 'Admin settings option', 'mediapress' ),
											
						)
		));
		
		
		$activity_options = array(
			'create_gallery'	=> _x( 'New Gallery is created.', 'Admin settings',  'mediapress'),
			'add_media'			=> _x( 'New Media added/uploaded.', 'Admin settings',  'mediapress'),
		);
		
		$default_activities = array_keys( $activity_options );
		
		$default_activities = array_combine( $default_activities, $default_activities );				
		
		
		
		$activity_section->add_field( array(
				'name'		=> 'autopublish_activities',
				//'id'=>	'active_components',
				'label'		=> _x( 'Automatically Publish to activity When?', 'Admin settings',  'mediapress' ),
				'type'		=> 'multicheck',
				'options'	=> $activity_options,
				'default'	=> $default_activities
		) );
		//6th section
		//directory settings
		
		$panel->add_section( 'directory-settings', _x( 'Directory Settings', 'Admin settings section title', 'mediapress' ) )

					->add_field( array(
						'name'			=> 'has_gallery_directory',
						'label'			=> _x( 'Enable Gallery Directory?', 'Admin settings', 'mediapress' ),
						'desc'			=> _x( 'Create a page to list all galleries?', 'Admin settings', 'mediapress' ),
						'default'		=> 1,
						'type'			=> 'radio',
						'options'		=> array(
							1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
							0 => _x( 'No', 'Admin settings option', 'mediapress' ),
											
						)
					))
					->add_field( array(
						'name'			=> 'has_media_directory',
						'label'			=> _x ( 'Enable Media directory?', 'Admin settings', 'mediapress' ),
						'desc'			=> _x( 'Create a page to list all photos, videos etc? Please keep it disabled for now )', 'Admin settings', 'mediapress' ),
						'default'		=> 1,
						'type'			=> 'radio',
						'options'		=> array(
							1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
							0 => _x( 'No', 'Admin settings option', 'mediapress' ),
											
						)
					));



		
		

		$panel->add_section( 'group-settings', _x( 'Group Settings', 'Admin settings section title', 'mediapress' ) )			
					->add_field( array(
						'name'			=> 'contributors_can_edit',
						'label'			=> _x( 'Contributors can edit their own media?','Admin settings group section', 'mediapress' ),
						'type'			=> 'radio',
						'default'		=> 1,//10 MB
						'options'		=> array(
							1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
							0 => _x( 'No', 'Admin settings option', 'mediapress' ),
											
						)
						
					) )
					->add_field( array(
						'name'			=> 'contributors_can_delete',
						'label'			=> _x( 'Contributors can delete their own media?', 'Admin settings group section', 'mediapress' ),
						'type'			=> 'radio',
						'default'		=> 1,//10 MB
						'options'		=> array(
							1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
							0 => _x( 'No', 'Admin settings option', 'mediapress' ),
											
						)
						
					) );
		

		
		$panel->add_section( 'misc-settings', _x( 'Miscellaneous Settings', 'Admin settings section title', 'mediapress' ) )
					->add_field( array(
						'name'			=> 'show_orphaned_media',
						'label'			=> _x( 'Show orphaned media to the user?', 'Admin settings option', 'mediapress' ),
						'desc'			=> _x( 'Do you want to list the media if it was uploaded from activity but the activity was not published?', 'Admin settings', 'mediapress' ),
						'type'			=> 'radio',
						'default'		=> 0,
						'options'		=> array(
							1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
							0 => _x( 'No', 'Admin settings option', 'mediapress' ),
											
						)
						
					) )
					->add_field( array(
						'name'			=> 'delete_orphaned_media',
						'label'			=> _x( 'Delete orphaned media automatically?', 'Admin settings', 'mediapress' ),
						'desc'			=> _x( 'Do you want to delete the abandoned media uploade from activity?', 'Admin settings', 'mediapress' ),
						'type'			=> 'radio',
						'default'		=> 1,//10 MB
						'options'		=> array(
							1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
							0 => _x( 'No', 'Admin settings option', 'mediapress' ),
											
						)
						
					) )

		;
		


		$theme_panel = $page->add_panel( 'theming', _x( 'Theming', 'Admin settings theme panel tab title', 'mediapress' ) );
		$theme_panel->add_section( 'display-settings', _x( 'Display Settings ', 'Admin settings theme section title', 'mediapress' ) )
				->add_field( array(
						'name'			=> 'galleries_per_page',
						'label'			=> _x( 'How many galleries to list per page?', 'Admin theme settings', 'mediapress' ),
						'type'			=> 'text',
						'default'		=> 12
				) )
				->add_field( array(
						'name'			=> 'media_per_page',
						'label'			=> _x( 'How many Media per page?', 'Admin theme settings', 'mediapress' ),
						'type'			=> 'text',
						'default'		=> 12,
						
				) )		
				->add_field( array(
					'name'			=> 'media_columns',
					'label'			=> _x( 'How many media per row?', 'Admin theme settings', 'mediapress' ),
					'type'			=> 'text',
					'default'		=> 4
				) )
				->add_field( array(
					'name'			=> 'gallery_columns',
					'label'			=> _x( 'How many galleries per row?', 'Admin theme settings', 'mediapress' ),
					'type'			=> 'text',
					'default'		=> 4
				) );
		
		
		$theme_panel->add_section( 'audio-video', _x( 'Audio/Video specific settings', ' Admin theme section title', 'mediapress' ) )		
				->add_field( array(
					'name'			=> 'enable_audio_playlist',
					'label'			=> _x( 'Enable Audio Playlist?', 'admin theme settings', 'mediapress' ),
					'description'	=> _x( 'Should an audio gallery be listed as a playlist?', 'admin theme settings', 'mediapress' ),
					'default'		=> 1,//mpp_get_option( 'enable_audio_playlist' ),
					'type'			=> 'radio',
					'options'		=> array(
						1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
						0 => _x( 'No', 'Admin settings option', 'mediapress' ),

					),
				) )
				->add_field( array(
					'name'			=> 'enable_video_playlist',
					'label'			=> _x( 'Enable Video Playlist?', 'admin theme settings', 'mediapress' ),
					'description'	=> _x( 'Should a video gallery be listed as a playlist?', 'admin theme settings', 'mediapress' ), 
					'default'		=> 1,//mpp_get_option( 'enable_audio_playlist' ),
					'type'			=> 'radio',
					'options'		=> array(
						1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
						0 => _x( 'No', 'Admin settings option', 'mediapress' ),

					),
				) );
		
		$theme_panel->add_section( 'comments', _x( 'Comment Settings', 'Admin theme section title', 'mediapress' ) )
				->add_field( array(
						'name'			=> 'enable_media_comment',
						'label'			=> _x( 'Enable Commenting on single media?', 'admin theme comment settings', 'mediapress' ),
						//'description' => 'Should a video gallery be listed as a playlist?',
						'default'		=> 1,//mpp_get_option( 'enable_audio_playlist' ),
						'type'			=> 'radio',
						'options'		=> array(
							1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
							0 => _x( 'No', 'Admin settings option', 'mediapress' ),
											
						),
				) )
				->add_field( array(
						'name'			=> 'enable_gallery_comment',
						'label'			=> 'Enable Commenting on single Gallery?',
						//'description' => 'Should a video gallery be listed as a playlist?',
						'default'		=> 1,//mpp_get_option( 'enable_audio_playlist' ),
						'type'			=> 'radio',
						'options'		=> array(
							1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
							0 => _x( 'No', 'Admin settings option', 'mediapress' ),
											
						)
				) );
		
		$theme_panel->add_section( 'lightbox', _x( 'Lightbox Settings', 'admin theme section title', 'mediapress' ) )
				->add_field( array(
						'name'			=> 'load_lightbox',
						'label'			=> _x( 'Load Lightbox javascript & css )?', 'Admin theme settings', 'mediapress' ),
						'description'	=> _x( 'Should we load the included lightbox script? Set no, if you are not using lightbox or want to use your own', 'Admin settings', 'mediapress' ),
						'default'		=> 1,//mpp_get_option( 'enable_audio_playlist' ),
						'type'			=> 'radio',
						'options'		=> array(
							1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
							0 => _x( 'No', 'Admin settings option', 'mediapress' ),
											
						)
				) )
				->add_field( array(
						'name'			=> 'enable_activity_lightbox',
						'label'			=> _x( 'Open Activity media in lightbox ?', 'Admin theme settings', 'mediapress' ),
						'description'	=> _x( 'If you set yes, the photos etc will be open in lightbox on activity screen.', 'Admin theme settings', 'mediapress' ),
						'default'		=> 1,//mpp_get_option( 'enable_audio_playlist' ),
						'type'			=> 'radio',
						'options'		=> array(
							1 => _x( 'Yes', 'Admin settings option', 'mediapress' ), 
							0 => _x( 'No', 'Admin settings option', 'mediapress' ),
											
						)
				) )
		
		;
		//add an empty addons panel to allow plugins to register any setting here
		//though a plugin can add a new panel, smaller plugins should use this panel instead
		$page->add_panel( 'addons', _x( 'Addons', 'Admin settings Addons panel tab title', 'mediapress' ), _x( 'MediaPress Addon Settings', 'Addons panel description', 'mediapress' ) );
	
		//auto posting to activity on gallery upload?
		//should post after the whole gallery is uploaded or just after each media?
				
		$this->page = $page;
		
		mpp_admin()->set_page( $this->page );
		
		do_action( 'mpp_admin_register_settings', $page );
		//allow enab
		$page->init();

	}
	/**
	 * Add Menu
	 */
	public function add_menu() {
		
		add_submenu_page( mpp_admin()->get_menu_slug(), _x( 'Settings', 'Admin settings page title', 'mediapress' ), _x( 'Settings', 'Admin settings menu label', 'mediapress' ), 'manage_options', 'mpp-settings', array( $this, 'render' ) );
		
	}
	/**
	 * Show/render the setting page
	 * 
	 */
	public function render() {
		
		$this->page->render();
	}
	

}


//instantiate
MPP_Admin_Settings_Helper::get_instance();
