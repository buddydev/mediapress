<?php
/** Anything admin related here
 * */

class MPP_Admin {
    
    private $menu_slug = '';//
	private $page;
	
    private static $instance;
    
	
    private function __construct() {
        
		$this->menu_slug = 'edit.php?post_type='. mpp_get_gallery_post_type();
		
    }
    /**
     * 
     * @return MPP_Admin
     */
    public static function get_instance() {
        
        if( ! isset( self::$instance ) )
                self::$instance = new self();
        
        return self::$instance;
    }
		
	
	public function get_menu_slug() {
		
		return $this->menu_slug;
	}
	
	public function set_page( $page ) {
		$this->page = $page;
	}
	/**
	 * 
	 * @return OptionsBuddy_Settings_Page 
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
    
    
    private static $instance;
    /**
	 *
	 * @var OptionsBuddy_Settings_Page 
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
        
        if( ! isset( self::$instance ) )
                self::$instance = new self();
        
        return self::$instance;
    }
    
	public function init() {
		
		$page = new OptionsBuddy_Settings_Page( 'mpp-settings' );//MPP_Admin_Page( 'mpp-settings' );
		
		$page->add_section( 'general', __( 'General', 'mediapress' ) );
		
		
		$page->get_section( 'general')
					->add_field( array(
						'name'			=> 'activity_upload',
						'label'			=> 'Allow Activity Upload?',
						'desc'			=> 'allow Uploading from Activity screen?',
						'default'		=> 1,
						'type'			=> 'radio',
						'options'		=> array(
											1=> 'Yes',
											0 => 'No'
										)
					))
					->add_field( array(
						'name'			=> 'has_gallery_directory',
						'label'			=> 'Enable Gallery Directory?',
						'desc'	=> 'Create a page to list all galleries?',
						'default'		=> 1,
						'type'			=> 'radio',
						'options'		=> array(
											1=> 'Yes',
											0 => 'No'
										)
					))
					->add_field( array(
						'name'			=> 'has_media_directory',
						'label'			=> 'Enable Media directory?',
						'desc'			=> 'Create a page to list all photos, videos etc?',
						'default'		=> 1,
						'type'			=> 'radio',
						'options'		=> array(
											1=> 'Yes',
											0 => 'No'
										)
					))
					->add_field( array(
						'name'			=> 'galleries_per_page',
						'label'			=> __( 'How many galleries to list per page?', 'mediapress' ),
						'type'			=> 'text',
						'default'		=> 12
					) )
					->add_field( array(
						'name'			=> 'media_per_page',
						'label'			=> __( 'How many Media per page?', 'mediapress' ),
						'type'			=> 'text',
						'default'		=> 12,
						
					) )
					->add_field( array(
						'name'			=> 'mpp_upload_space',
						'label'			=> __( 'maximum Upload space per user(MB)?', 'mediapress' ),
						'type'			=> 'text',
						'default'		=> 10,//10 MB
						
					) )
					->add_field( array(
						'name'			=> 'mpp_upload_space_groups',
						'label'			=> __( 'maximum Upload space per group(MB)?', 'mediapress' ),
						'type'			=> 'text',
						'default'		=> 10,//10 MB
						
					) )
					->add_field( array(
						'name'			=> 'allow_mixed_gallery',
						'label'			=> __( 'Allow mixed Galleries?', 'mediapress' ),
						'type'			=> 'radio',
						'default'		=> 0,//10 MB
						'options'		=> array(
											0 => 'No',
											1 => 'Yes'
										)
						
					) )
					->add_field( array(
						'name'			=> 'show_upload_quota',
						'label'			=> __( 'Show upload Quota?', 'mediapress' ),
						'type'			=> 'radio',
						'default'		=> 0,//10 MB
						'options'		=> array(
											0 => 'No',
											1 => 'Yes'
										)
						
					) )
					
					->add_field( array(
						'name'			=> 'contributors_can_edit',
						'label'			=> __( 'Contributors can edit their own media?', 'mediapress' ),
						'type'			=> 'radio',
						'default'		=> 1,//10 MB
						'options'		=> array(
											0 => 'No',
											1 => 'Yes'
										)
						
					) )
					->add_field( array(
						'name'			=> 'contributors_can_delete',
						'label'			=> __( 'Contributors can delete their own media?', 'mediapress' ),
						'type'			=> 'radio',
						'default'		=> 1,//10 MB
						'options'		=> array(
											0 => 'No',
											1 => 'Yes'
										)
						
					) )
					->add_field( array(
						'name'			=> 'show_orphaned_media',
						'label'			=> __( 'Show orphaned media to the user?', 'mediapress' ),
						'desc'			=> __( 'Do you want to list the media if it was uploaded from activity but the activity was not published?', 'mediapress' ),
						'type'			=> 'radio',
						'default'		=> 0,
						'options'		=> array(
											0 => 'No',
											1 => 'Yes'
										)
						
					) )
					->add_field( array(
						'name'			=> 'delete_orphaned_media',
						'label'			=> __( 'Delete orphaned media automatically?', 'mediapress' ),
						'desc'			=> __( 'Do you want to delete the abandoned media uploade from activity?', 'mediapress' ),
						'type'			=> 'radio',
						'default'		=> 1,//10 MB
						'options'		=> array(
											1 => 'Yes',
											0 => 'No'
										)
						
					) )

		;
		
		$storage_methods = mpp_get_registered_storage_managers();
		
		$storage_methods = array_keys( $storage_methods );
		$storage_method_options = array();
		foreach( $storage_methods as $storage_method )
			$storage_method_options[$storage_method] = ucfirst ( $storage_method );
		
					//->add_field();
		
		$page->get_section( 'general' )->add_field( array(
						'name'		=> 'default_storage',
						'label'		=> 'Which should be marked as default storage?',
						'default'	=> mpp_get_default_storage_method(),
						'options'	=> $storage_method_options ,
						'type'		=> 'radio'
					) );
		
		
		$available_media_stati = mpp_get_registered_statuses();
		
		$options = array();
		foreach( $available_media_stati as $key => $available_media_status )
			$options[ $key] = $available_media_status->get_label ();
		
		$page->get_section( 'general' )->add_field( array(
						'name'			=> 'default_status',
						'label'			=> 'Default status for Gallery/media',
						'description'	=> 'It will be used when we are not ale to get the status from user',
						'default'		=> mpp_get_default_status(),
						'options'		=> $options,
						'type'			=> 'select'
					) );
		
		//types
				
		$section = $page->get_section( 'general' );
		$valid_types = mpp_get_registered_types();
		
		$options = array();
		$types_info = array();
		foreach( $valid_types as $type => $type_object ) {
			
			$types_info[$type] = $type_object->label;
			
			$page->get_section( 'general' )->add_field( array( 
				'id'				=> 'extensions-'. $type,
				'name'				=> 'extensions-'. $type,
				'label'				=> 'Allowed extensions for ' . $type,
				'description'		=> 'Allowed extensions for ' . $type,
				'default'			=> join( ',', (array)$type_object->get_extensions() ),
				'type'				=> 'multioption',
				'extra'				=> array( 'key' => $type, 'name' => 'extensions' )
				
				
				
			) );
		}
		
		$default_types = array();
		
		$active_types = array_keys( mpp_get_active_types() );
		
		if( ! empty( $active_types ) )
			$default_types = array_combine( $active_types, $active_types );
		
		$section->add_field( array(
				'name'		=> 'active_types',
				//'id'=>	'active_components',
				'label'		=> __( 'Enabled Media/Gallery Types', 'mediapress' ),
				'type'		=> 'multicheck',
				'options'	=> $types_info,
				'default'	=> $default_types, //array( 'photo' => 'photo', 'audio' => 'audio', 'video' => 'video' )
		) );
				$components_details = array();
		
		$components = mpp_get_registered_components();
		
		

		
		foreach( $components as $key => $component ){
			$components_details[$key] = $component->label;
		}
		$default_components = array();
		
		$active_components = array_keys( mpp_get_active_components() );
		
		if( ! empty( $active_components ) )
			$default_components = array_combine( $active_components, $active_components ); 
		$section->add_field( array(
				'name'		=> 'active_components',
				//'id'=>	'active_components',
				'label'		=> __( 'Enabled Components', 'mediapress' ),
				'type'		=> 'multicheck',
				'options'	=> $components_details,
				'default'	=> $default_components, //array( 'members' => 'members' ), //enable members component by default
			) );
		
		
		//status
		
		$registered_statuses = mpp_get_registered_statuses();
		
		$status_info = array();
		
		foreach( $registered_statuses as $key => $status )
			$status_info[$key] = $status->label;
				
		$active_statuses = array_keys( mpp_get_active_statuses() );
		
		$default_statuses = array();
		
		if( ! empty( $active_statuses ) )
			$default_statuses = array_combine( $active_statuses, $active_statuses );
		
		$section->add_field( array(
				'name'		=> 'active_statuses',
				//'id'=>	'active_components',
				'label'		=> __( 'Enabled Media/Gallery Statuses', 'mediapress' ),
				'type'		=> 'multicheck',
				'options'	=> $status_info,
				'default'	=> $default_statuses
		) );
		
		$page->add_section( 'theming', __( 'Theming', 'mediapress' ) )
									
				->add_field( array(
					'name'			=> 'media_columns',
					'label'			=> __( 'How many media per row?', 'mediapress' ),
					'type'			=> 'text',
					'default'		=> 4
				) )
				->add_field( array(
					'name'			=> 'gallery_columns',
					'label'			=> __( 'How many galleries per row?', 'mediapress' ),
					'type'			=> 'text',
					'default'		=> 4
				) )
				->add_field( array(
					'name'			=> 'enable_audio_playlist',
					'label'			=> 'Enable Audio Playlist?',
					'description'	=> 'Should an audio gallery be listed as a playlist?',
					'default'		=> 1,//mpp_get_option( 'enable_audio_playlist' ),
					'options'		=> array(
										1 => 'Yes',
										0 => 'No'
									),
					'type'			=> 'radio'		
				) )
				->add_field( array(
					'name'			=> 'enable_video_playlist',
					'label'			=> 'Enable Video Playlist?',
					'description'	=> 'Should a video gallery be listed as a playlist?',
					'default'		=> 1,//mpp_get_option( 'enable_audio_playlist' ),
					'options'		=> array(
										1 => 'Yes',
										0 => 'No'
									),
					'type'			=> 'radio'		
				) )
				->add_field( array(
						'name'			=> 'enable_media_comment',
						'label'			=> 'Enable Commenting on single media?',
						//'description' => 'Should a video gallery be listed as a playlist?',
						'default'		=> 1,//mpp_get_option( 'enable_audio_playlist' ),
						'options'		=> array(
											1 => 'Yes',
											0 => 'No'
										),
						'type'			=> 'radio'		
				) )
				->add_field( array(
						'name'			=> 'enable_gallery_comment',
						'label'			=> 'Enable Commenting on single Gallery?',
						//'description' => 'Should a video gallery be listed as a playlist?',
						'default'		=> 1,//mpp_get_option( 'enable_audio_playlist' ),
						'options'		=> array(
											1 => 'Yes',
											0 => 'No'
										),
						'type'			=> 'radio'		
				) )
				->add_field( array(
						'name'			=> 'load_lightbox',
						'label'			=> __( 'Load Lightbox javascript & css )?', 'mediapress' ),
						'description'	=> __( 'Should we load the included lightbox script? Set no, if you are not using lightbox or ant to use your own', 'mediapress' ),
						'default'		=> 1,//mpp_get_option( 'enable_audio_playlist' ),
						'options'		=> array(
											1 => __( 'Yes', 'mediapress' ),
											0 => __( 'No', 'mediapress' )
										),
						'type'			=> 'radio'		
				) )
				->add_field( array(
						'name'			=> 'enable_activity_lightbox',
						'label'			=> __( 'Open Activity media in lightbox ?', 'mediapress' ),
						'description'	=> __( 'If you set yes, the photos etc will be open in lightbox on activity screen.', 'mediapress' ),
						'default'		=> 1,//mpp_get_option( 'enable_audio_playlist' ),
						'options'		=> array(
											1 => __( 'Yes', 'mediapress' ),
											0 => __( 'No', 'mediapress' )
										),
						'type'			=> 'radio'		
				) )
		
		;
		
		//auto posting to activity on gallery upload?
		//should post after the whole gallery is uploaded or just after each media?
		
		

		

		//allow enab
		$page->init();
		$this->page = $page;
		mpp_admin()->set_page($this->page);
	}
	/**
	 * Add Menu
	 */
	public function add_menu(){
		
		add_submenu_page( mpp_admin()->get_menu_slug(), __( 'Settings', 'mediapress' ), __( 'Settings', 'mediapress' ), 'manage_options', 'mpp-settings', array( $this, 'render' ) );
		
	}
	
	public function render() {
		
		$this->page->render();
	}
	

}


//instantiate
MPP_Admin_Settings_Helper::get_instance();



