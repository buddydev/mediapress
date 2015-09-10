<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * MediaPress Core Component
 * Sets up Galleries/Media for the current page
 *   
 */

class MPP_Core_Component  {

    private static $instance = null;
    /**
     * Array of names not available as gallery names
	 * 
     * @var type 
     */
    public $forbidden_name;
    /**
     *
     * @var I am not sure why I added it in the first place 
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
	 * Current MediaPress action 'create/upload/manage/
	 * 
	 * @var type 
	 */
	private $current_action = '';
	
	/**
	 * What type of management option is this? delete/edit/reorder etc
	 * 
	 * @var string
	 */
	private $current_manage_action = '';
	
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

	private function setup() {
		//add erwrite end point for manage
		add_action( 'mpp_setup', array( $this, 'add_rewrite_endpoints' ) );
		//setup galleries
		add_action( 'mpp_actions', array( $this, 'setup_globals' ), 0 );
		//add context menu to user & groups sub nav
		add_action( 'bp_member_plugin_options_nav', array( $this, 'context_menu_edit' ) );
		add_action( 'mpp_group_nav', array( $this, 'context_menu_edit' ) );
		
	}

	/**
	 * Setup everything for BuddyPress Specific installation
	 * 
	 */
 
    public function setup_globals( $args = array() ) {
        
		
		//get current component/component_id
		$this->component	= mpp_get_current_component();
		$this->component_id = mpp_get_current_component_id();
        
		//override the component id if we are on user page
        if( function_exists( 'bp_is_user' ) && bp_is_user() ) {
            $this->component_id = bp_displayed_user_id ();
		}
        
        //let us setup global queries
       $current_action = '';
	   
	   //initialize query objects
       mediapress()->the_gallery_query	= new MPP_Gallery_Query();
       mediapress()->the_media_query	= new MPP_Media_Query();
       
	   //set the status types allowed for current user
       $this->accessible_statuses = mpp_get_accessible_statuses( $this->component, $this->component_id, get_current_user_id() );
        
		//is the root gallery enabled?
        if( mpp_is_root_enabled() ) {
			
			$this->setup_root_gallery();
		    
        }//end of root gallery section
        
		
		//if it is either member gallery OR Gallery Directory, let us process it
		if( mpp_is_gallery_component() ) {
			
            $this->action_variables = buddypress()->action_variables;
			
			//add the current action at the begining of the stack, we are doing it to unify the things for User gallery and component gallery
			array_unshift( $this->action_variables, bp_current_action() );
			
			$this->setup_user_gallery();
			
            
        }elseif( mpp_is_component_gallery() ) {
			//are we on component gallery like groups or events etc?
			$this->action_variables = buddypress()->action_variables;
			
            
            $this->setup_component_gallery();
            
            
        }
        //once we are here, the basic action variables for mediapress are setup and so 
		//we can go ahead and test for the single gallery/media
		$mp = mediapress();
		//setup Single Gallery specific things
		if( mpp_is_single_gallery() ) {
			
			$current_action = $this->current_action;
			
			 //setup and see the actions etc to find out what we need to do
			 //if it is one of the edit actions, It was already taken care of, don't do anything
			if( in_array( $current_action, mpp_get_reserved_actions() ) )
					 return ;
			
			//check if we are on management screen?
			if( $this->current_action == 'manage' ) {
				//this is media management page
				
				
				$mp->set_editing( 'gallery' );
				
				$mp->set_action( 'manage' );
				$mp->set_edit_action( $this->current_manage_action );
				
				//on edit bulk media page
				if( $mp->is_edit_action( 'edit' ) ) {
					$this->setup_gallery_media_query ();
				}
				
			} elseif ( $media = $this->get_media_id( $this->current_action, $this->component, $this->component_id ) ) {
				 //yes, It is single media

				$this->setup_single_media_query( $media );
				
			} else {
				//we already know it is single gallery, so let us setup the media list query
				$this->setup_gallery_media_query();

        }
         
     }
        
     do_action( 'mpp_setup_globals' );
    }

	/**
	 * Check and get single media id else false
	 * 
	 * @param type $slug
	 * @param type $component
	 * @param type $component_id
	 * @return type
	 */
    public function get_media_id( $slug, $component, $component_id ) {
		
		return mpp_media_exists( $slug, $component, $component_id );
		
	}
	/**
	 * Set up query for fetching single media
	 * 
	 * @param type $media_id
	 */
	public function setup_single_media_query( $media ) {
		
		$mp = mediapress();
		
		$mp->current_media = mpp_get_media( $media );
				
		$mp->the_media_query = new MPP_Media_Query(
						array(
							'id' => $media->ID
						));
		

					//now check if we are on edit page nor not?
					
		$this->current_action = isset( $this->action_variables[2] ) ? $this->action_variables[2] : '';
		
		if( $this->current_action == 'edit' ) {
			

			$mp->set_editing( 'media' );
			//it is single media edit
			$mp->set_action( 'edit' );

			$edit_action = isset( $this->action_variables[3] ) ? $this->action_variables[3] : 'edit';
			$mp->set_edit_action( $edit_action );

		}
	}
	/**
	 * Setup query for listing Media inside single gallery
	 * 
	 */
	public function setup_gallery_media_query() {
		
			//since we already know that this is a single gallery, It muist be media list screen

		$args = array(
					'component_id'	=> $this->component_id,
					'component'		=> $this->component,
					'gallery_id'	=> mpp_get_current_gallery_id(),
					'status'		=> $this->accessible_statuses,

				);

		if( $this->mpage ) {

			$args['page'] = absint( $this->mpage );
		}

		//check for pagination
		//
		//we are on User gallery home page
		//we do need to check for the access level here and pass it to the query
		mediapress()->the_media_query = new MPP_Media_Query( $args );

		 //set it is the user galleries list view      
		//mediapress()->is_gallery_home = true;
	}
	/**
	 * Setup Root Galleries
	 * @todo Make it work in 1.1 when we introduce site galleries
	 * 
	 */
	public function setup_root_gallery() {
		
			//if sitewide gallery is not enabled, or current page is not sitewide gallery, no need to proceed 
			if( !  mpp_is_active_component( 'sitewide' ) ) {
				return ;
			}
			
			//this is our single gallery page
            if( mpp_is_sitewide_gallery_component() ) {
                
                $gallery_id = get_queried_object_id();
                
                //setup current gallery
                mediapress()->current_gallery = mpp_get_gallery( $gallery_id );
                //setup gallery query
                mediapress()->the_gallery_query = new MPP_Gallery_Query(
                        array(
                            'id' => $gallery_id
                        ));
                
                //check for end points to edit
                if ( get_query_var( 'manage' ) ) {
                    
                    $action = get_query_var( 'manage' );
					$this->current_action = 'manage';
					$this->current_manage_action = $action;  

					
				} elseif ( get_query_var( 'media' ) ) {
					
					$action = get_query_var( 'media' );
					$this->current_action = $action;
					$this->current_manage_action = '';//$action;  

				}
                
            } elseif( is_post_type_archive( mpp_get_gallery_post_type() ) ) {
                
                mediapress()->the_gallery_query = new MPP_Gallery_Query(
                              array(
                                 'status' => 'public'
                                 
                              ));
                
            }
                
	}
	
	
	
	public function setup_user_gallery() {
				
		if( mpp_is_active_component( 'members' ) && bp_is_user() ) {
                //is User Gallery enabled? and are we on the user section?  
			
			
                $user_id			= bp_displayed_user_id();
                $this->component	= 'members';
                 
				$current_action		= bp_current_action();
				
				if( $current_action == 'create' || $current_action == 'upload' ) {
					
					mediapress()->set_action( $current_action );
					mediapress()->set_edit_action( $current_action );
					
					
					return ;
				}
				 
                //Are we looking at single gallery? or Media?
				//current action in this case is checked for being  a gallery slug
                if( $gallery = mpp_gallery_exists( $this->action_variables[0], $this->component, $user_id ) ) {
					
                    //setup current gallery & gallery query
                    mediapress()->current_gallery	= mpp_get_gallery( $gallery );
                    mediapress()->the_gallery_query = new MPP_Gallery_Query(
                            array(
                                'id' => $gallery->ID
                            ));
                    
                    $this->current_action			= bp_action_variable( 0 );
                    $this->current_manage_action	= bp_action_variable( 1 ); 
					
                    if( ! empty( $this->action_variables[1] ) && $this->action_variables[1] == 'page' && $this->action_variables[2] > 0 )
                         $this->mpage = (int) $this->action_variables[2];
                      
                      
                } else {
					
					if( $this->action_variables[0] == 'page' && $this->action_variables[1] > 0 )
						$this->gpage = (int) $this->action_variables[1];
					
					
					$args =  array(
                                'user_id'	=> $user_id,
                                'component'	=> $this->component,
                                'status'    => $this->accessible_statuses,

                            );
					
					if( $this->gpage ) {

						$args['page'] = absint( $this->gpage );
					}
					
                    //we are on User gallery home page(gallery list)
                    //we do need to check for the access level here and pass it to the query
                    //how about gallery pagination?
                    mediapress()->the_gallery_query = new MPP_Gallery_Query( $args );
					

                     //set it is the user galleries list view      
                    mediapress()->is_gallery_home = true;
                }
            //in this case, we are on the gallery directory, check if we have it enabled?
            }elseif( mpp_has_gallery_directory()){
                
				$this->setup_gallery_directory_query();

            }
          	
		
	}
	/**
	 * Setup query for gallery directory
	 * 
	 */
	public function setup_gallery_directory_query() {
		//make the query and setup 
		mediapress()->is_directory = true;

		//get all public galleries, should we do type filtering
		mediapress()->the_gallery_query = new MPP_Gallery_Query(
				array(
					'status'=> 'public'

				));
		
				
		
				
	}
	/**
	 * Setup gallery for components like groups/events etc
	 */
	public function setup_component_gallery(){
		
		//current_action = mpp_slug(mediapress)
		
		
		if( mpp_is_active_component( bp_current_component() ) ) {
                //is Component Gallery enabled? and are we on the Component section?  
               
			
                
                 
				$current_action = bp_action_variable( 0 );
				if( $current_action == 'create' || $current_action == 'upload' ) {
					
					mediapress()->set_action( $current_action );
					mediapress()->set_edit_action( $current_action );
					
					
					return ;
				}
				
                //Are we looking at single gallery? or Media?
				//current action in this case is checked for being  a gallery slug
				
                if( $this->action_variables && $gallery = mpp_gallery_exists( $this->action_variables[0], $this->component, $this->component_id ) ) {
                    
                    //setup current gallery & gallery query
                    mediapress()->current_gallery	= mpp_get_gallery( $gallery );
                    mediapress()->the_gallery_query = new MPP_Gallery_Query(
                            array(
                                'id' => $gallery->ID
                            ));
                   
                    $this->current_action			= bp_action_variable( 1 );
                    $this->current_manage_action	= bp_action_variable( 2 ); 
					
                    if( ! empty( $this->action_variables[1] ) && $this->action_variables[1] == 'page' && $this->action_variables[2] > 0 )
                         $this->mpage = (int) $this->action_variables[2];
                      
                      
                      
                } else {
					
					if( $this->action_variables && $this->action_variables[0] == 'page' && $this->action_variables[1] > 0 )
						$this->gpage = (int) $this->action_variables[1];
					
					
					$args =  array(
                                'component_id'	=> $this->component_id,
                                'component'		=> $this->component,
                                'status'		=> $this->accessible_statuses,

                            );
					
					if( $this->gpage ) {

						$args['page'] = absint( $this->gpage );
					}
					
                    //we are on User gallery home page(gallery list)
                    //we do need to check for the access level here and pass it to the query
                    //how about gallery pagination?
                    mediapress()->the_gallery_query = new MPP_Gallery_Query( $args );
					

                     //set it is the user galleries list view      
                    mediapress()->is_gallery_home = true;
                }
            //in this case, we are on the gallery directory, check if we have it enabled?
            }
            		
		
	}
	
	
	
	

    public function setup_nav( $main = array(), $sub = array() ) {
    
		$bp = buddypress();
		
        if ( ! mpp_is_active_component( 'members' ) )//allow to disable user galleries in case they don't want it
				return false;

        $view_helper = MPP_Gallery_Screens::get_instance();
        
		// Add 'Gallery' to the user's main navigation
        $main_nav = array(
            'name'					=> sprintf( __( 'Gallery <span>%d</span>', 'mediapress' ), mpp_get_total_gallery_for_user() ),
            'slug'					=> $this->slug,
            'position'				=> 86,
            'screen_function'		=> array( $view_helper, 'user_galleries' ),
            'default_subnav_slug'	=> 'my-galleries',
            'item_css_id'			=> $this->id
        );
		if( bp_is_user() )
			$user_domain = bp_displayed_user_domain ( );
		else
			$user_domain = bp_loggedin_user_domain ( );
		
        $gallery_link = trailingslashit( $user_domain . $this->slug ); //with a trailing slash
        

		// Add the My Gallery nav item
        $sub_nav[] = array(
            'name'				=> __( 'My Gallery', 'mediapress' ),
            'slug'				=> 'my-galleries',
            'parent_url'		=> $gallery_link,
            'parent_slug'		=> $this->slug,
            'screen_function'	=> array( $view_helper, 'my_galleries' ),
            'position'			=> 10,
            'item_css_id'		=> 'gallery-my-gallery'
        );

		if( mpp_user_can_create_gallery( 'members', get_current_user_id() ) ) {
			// Add the Create gallery link to gallery nav
			$sub_nav[] = array(
				'name'				=> __( 'Create a Gallery', 'mediapress' ),
				'slug'				=> 'create',
				'parent_url'		=> $gallery_link,
				'parent_slug'		=> $this->slug,
				'screen_function'	=> array( $view_helper, 'create_gallery' ),
				'user_has_access'	=> bp_is_my_profile(),
				'position'			=> 20
			);

		}
       
        // Add the Upload link to gallery nav
        /*$sub_nav[] = array(
            'name'				=> __( 'Upload', 'mediapress'),
            'slug'				=> 'upload',
            'parent_url'		=> $gallery_link,
            'parent_slug'		=> $this->slug,
            'screen_function'	=> array( $view_helper, 'upload_media' ),
            'user_has_access'	=> bp_is_my_profile(),
            'position'			=> 30
        );*/

        parent::setup_nav( $main_nav, $sub_nav ); 
		
       //disallow these names in various lists
		//we have yet to implement it
        $this->forbidden_names = apply_filters( 'mpp_forbidden_names', array( 'gallery', 'galleries', 'my-gallery', 'create', 'delete', 'upload', 'add', 'edit', 'admin', 'request', 'upload', 'tags', 'audio', 'video', 'photo' ) );
        

		//use this to extend the valid status
        $this->valid_status = apply_filters( 'mpp_valid_gallery_status', array_keys( mpp_get_active_statuses() ) ) ;
        
		do_action( 'mpp_setup_nav' ); // $bp->gallery->current_gallery->user_has_access
    }

	//Add the Edit context menu when a user is on single gallery
	public function context_menu_edit() {
		
		if( mpp_is_gallery_management() || mpp_is_media_management() )
			return;
		
		if( ! mpp_is_single_gallery() )
			return;
		
		
		if( ! mpp_user_can_edit_gallery( mpp_get_current_gallery_id() ) ) 
			return;
		
		$links = '';
		
		if ( mpp_is_single_media() ) {
			$url = mpp_get_media_edit_url();
			$links .= sprintf( '<li><a href="%1$s" title ="%2$s"> %3$s</a></li>', $url, _x( 'Edit media', 'Profile context menu rel', 'mediapress' ), _x( 'Edit', 'Profile context menu media edit label', 'mediapress' ) );
		} else {
			$url = mpp_get_gallery_edit_media_url( mpp_get_current_gallery() );//bulk edit media url
			
			$links .= sprintf( '<li><a href="%1$s" title ="%2$s"> %3$s</a></li>', $url, _x( 'Edit Gallery', 'Profile context menu rel attribute', 'mediapress' ), _x( 'Edit', 'Profile contextual edit gallery menu label', 'mediapress' ) );
			
			$links .= sprintf( '<li><a href="%1$s" title ="%2$s"> %3$s</a></li>', mpp_get_gallery_add_media_url( mpp_get_current_gallery() ), _x( 'Add Media', 'Profile context menu rel attribute', 'mediapress' ), _x( 'Add Media', 'Profile contextual add media  menu label', 'mediapress' ) );
		}
			
		echo $links;
		
	}
 
	/**
	 * Setup title for various screens
	 * 
	 */
	public function setup_title() {
		
		
	}
	/**
	 * Set up the Toolbar.
	 *
	 * @param array $wp_admin_nav See {BP_Component::setup_admin_bar()}
	 *        for details.
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {
		
		$bp = buddypress();
		
		// Menus for logged in user if the members gallery is enabled
		if ( is_user_logged_in() && mpp_is_active_component( 'members' ) ) {

			$component = 'members';
			$component_id = get_current_user_id();
			
			$gallery_link  = trailingslashit( mpp_get_gallery_base_url( $component, $component_id ) );

			$title = __( 'Gallery', 'mediapress' );
			
			$my_galleries = __( 'My Gallery', 'mediapress' );
			
			$create = __( 'Create', 'mediapress' );
			

			// Add main mediapress menu
			$wp_admin_nav[] = array(
				'parent' => $bp->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => $title,
				'href'   => trailingslashit( $gallery_link )
			);
			// Add main mediapress menu
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-my-galleries',
				'title'  => $my_galleries,
				'href'   => trailingslashit( $gallery_link )
			);

			if( mpp_user_can_create_gallery( $component, $component_id ) ) {
				
					$wp_admin_nav[] = array(
						'parent' => 'my-account-' . $this->id,
						'id'     => 'my-account-' . $this->id . '-create',
						'title'  => $create,
						'href'   => mpp_get_gallery_create_url( $component, $component_id )
					);
			}		

		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

	public function add_rewrite_endpoints() {
		add_rewrite_endpoint( 'manage', EP_PERMALINK );
		add_rewrite_endpoint( 'media', EP_PERMALINK );
	}
}

//setup core gallery component
MPP_Core_Component::get_instance();


