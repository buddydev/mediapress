<?php
//initialize core

add_action( 'mpp_init', 'mpp_init', 1 );

/**
 * Register various core features
 * Registers statuses
 * Registers types(media type)
 * Also registers Component types
 */
function mpp_init() {
    
    //register privacies
    //private
    mpp_register_status( array(
            'key'           => 'public',
            'label'         => __( 'Public', 'mediapress' ),
            'labels'        => array( 
									'singular_name' => __( 'Public', 'mediapress' ),
									'plural_name'	=> __( 'Public', 'mediapress' )
			),
            'description'   => __( 'Public Gallery Privacy Type'),
            'callback'      => 'mpp_check_public_access'
    ));
   
    mpp_register_status( array(
            'key'           => 'private',
            'label'         => __( 'Private', 'mediapress' ),
            'labels'        => array( 
									'singular_name' => __( 'Private', 'mediapress' ),
									'plural_name'	=> __( 'Private', 'mediapress' )
			),
            'description'   => __( 'Private Privacy Type'. 'mediapress' ),
            'callback'      => 'mpp_check_private_access'
    ));
    //if friends component is active, only then
    mpp_register_status( array(
            'key'           => 'friendsonly',
            'label'         => __( 'Friends Only', 'mediapress' ),
            'labels'        => array( 
									'singular_name' => __( 'Friends Only', 'mediapress' ),
									'plural_name'	=> __( 'Friends Only', 'mediapress' )
			),
            'description'   => __( 'Friends Only Privacy Type', 'mediapress' ),
            'callback'      => 'mpp_check_friends_access'
    ));
		
	mpp_register_status( array(
				'key'           => 'loggedin',
				'label'         => __( 'Logged In Users Only', 'mediapress' ),
				'labels'        => array( 
										'singular_name' => __( 'Logged In Users Only', 'mediapress' ),
										'plural_name'	=> __( 'Logged In Users Only', 'mediapress' )
				),
				'description'   => __( 'Logged In Users Only Privacy Type', 'mediapress' ),
				'callback'      => 'mpp_check_loggedin_access'
		));
    //if followers component is active only then
	
	if( function_exists( 'bp_follow_is_following' ) ) {
		
		mpp_register_status( array(
				'key'           => 'followersonly',
				'label'         => __( 'Followers Only', 'mediapress' ),
				'labels'        => array( 
										'singular_name' => __( 'Followers Only', 'mediapress' ),
										'plural_name'	=> __( 'Followers Only', 'mediapress' )
				),
				'description'   => __( 'Followers Only Privacy Type', 'mediapress' ),
				'callback'      => 'mpp_check_followers_access'
		));
		mpp_register_status( array(
				'key'           => 'followingonly',
				'label'         => __( 'Persons I Follow', 'mediapress' ),
				'labels'        => array( 
										'singular_name' => __( 'Persons I Follow', 'mediapress' ),
										'plural_name'	=> __( 'Persons I Follow', 'mediapress' )
				),
				'description'   => __( 'Following Only Privacy Type', 'mediapress' ),
				'callback'      => 'mpp_check_following_access'
		));
		
	}
    
    //register types
    //photo
    mpp_register_type( array(
            'key'           => 'photo',
            'label'         => __( 'Photo', 'mediapress' ),
            'description'   => __( 'taxonomy for image media type', 'mediapress' ),
			'labels'		=> array(
								'singular_name'	=> __( 'Photo', 'mediapress' ),
								'plural_name'	=> __( 'Photos', 'mediapress' )
			),
            'extensions'    => array( 'jpeg', 'jpg', 'gif', 'png' ),
    ) );
    //video
    mpp_register_type( array(
            'key'           => 'video',
            'label'         => __( 'Video', 'mediapress' ),
			'labels'		=> array(
								'singular_name'	=> __( 'Video', 'mediapress' ),
								'plural_name'	=> __( 'Videos', 'mediapress' )
			),
            'description'   => __( 'Video media type taxonomy', 'mediapress' ),
			'extensions'	=> array( 'mp4', 'flv', 'mpeg' )
    ) );
	
    mpp_register_type( array(
            'key'           => 'audio',
            'label'         => __( 'Audio', 'mediapress' ),
			'labels'		=> array(
								'singular_name'	=> __( 'Audio', 'mediapress' ),
								'plural_name'	=> __( 'Audios', 'mediapress' )
			),
            'description'   => __( 'Audio Media type taxonomy', 'mediapress' ),
			'extensions'	=> array( 'mp3', 'wmv', 'midi' )
    ) );
	
    mpp_register_type( array(
            'key'           => 'doc',
            'label'         => __( 'Documents', 'mediapress' ),
			'labels'		=> array(
								'singular_name'	=> __( 'Document', 'mediapress' ),
								'plural_name'	=> __( 'Documents', 'mediapress' )
			),
            'description'   => __( 'This is documents gallery', 'mediapress' ),
            'extensions'    => array( 'zip', 'gz', 'doc', 'pdf', 'docx', 'xls' )
    ) );
    
	
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
	
	
	if( bp_is_active( 'friends' ) )
		mpp_component_register_status( 'members', 'friendsonly' );
	
	//allow members component to support the followers privacy 
	if( function_exists( 'bp_follow_is_following' ) ) {
		
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
	
	
	
    mpp_register_media_size( array(
            'name'  => 'thumbnail',
            'height'=> 200,
            'width' => 200,
            'crop'  => true,
            'type'  => 'default'
    ) );
    
    mpp_register_media_size( array(
            'name'  => 'mid',
            'height'=> 300,
            'width' => 500,
            'crop'  => true,
            'type'  => 'default'
    ) );
    
    mpp_register_media_size( array(
            'name'  => 'large',
            'height'=> 800,
            'width' => 600,
            'crop'  => false,
            'type'  => 'default'
    ) );
    
    //register storage managers here
    //local storage manager
    mpp_register_storage_manager( 'local', MPP_Local_Storage::get_instance() );
    //mpp_register_storage_manager( 'aws', MPP_Local_Storage::get_instance() );
    
	
	//register viewer
	//please note, google doc viewer will not work for local files
	//files must be somewhere accessible from the web
	mpp_register_media_view( 'doc', new MPP_Media_View_Docs() );
	
	//we are registering for video so we can replace it in future for flexible video views
	
	mpp_register_media_view( 'video', new MPP_Media_View_Video() );
	
	//audio
	mpp_register_media_view( 'audio', new MPP_Media_View_Audio() );
	
	//should we register a photo viewer too? may be for the sake of simplicity?
	
	
    //setup the tabs
    mediapress()->add_menu( 'gallery', new MPP_Gallery_Menu() );
    mediapress()->add_menu( 'media', new MPP_Media_Menu() );
	
	
}

add_action( 'mpp_setup_globals', 'mpp_setup_gallery_nav' );

function mpp_setup_gallery_nav() {
    
	//only add on single gallery
	
	if( ! mpp_is_single_gallery() )
		return;
	
    $gallery = mpp_get_current_gallery();
	
    $url = '';
	
    if( $gallery ) {
        
        $url = mpp_get_gallery_permalink( $gallery );
    }
    
	//only add view/edit/dele links on the single mgallery view
	
    mpp_add_gallery_nav_item( array(
        'label'		=> __( 'View', 'mediapress' ),
        'url'		=> $url,
        'action'	=> 'view',
        'slug'		=> 'view'
        
    ));
	
	$user_id = get_current_user_id();
	
    if( mpp_user_can_edit_gallery( $gallery->id, $user_id ) ) {
		
		mpp_add_gallery_nav_item( array(
			'label'		=> __( 'Edit Media', 'mediapress' ), //we can change it to media type later
			'url'		=> mpp_get_gallery_edit_media_url( $gallery ),
			'action'	=> 'edit',
			'slug'		=> 'edit'

		));
	}
	if( mpp_user_can_upload($gallery->component, $gallery->component_id ) ) {
		
		mpp_add_gallery_nav_item( array(
			'label'		=> __( 'Add Media', 'mediapress' ), //we can change it to media type later
			'url'		=> mpp_get_gallery_add_media_url( $gallery ),
			'action'	=> 'add',
			'slug'		=> 'add'

		));
	}
	if( mpp_user_can_edit_gallery( $gallery->id, $user_id ) ) {
		
	
		mpp_add_gallery_nav_item( array(
			'label'		=> __( 'Reorder', 'mediapress' ), //we can change it to media type later
			'url'		=> mpp_get_gallery_reorder_media_url( $gallery ),
			'action'	=> 'reorder',
			'slug'		=> 'reorder'

		));

		mpp_add_gallery_nav_item( array(
			'label'		=> __( 'Edit Details', 'mediapress' ),
			'url'		=> mpp_get_gallery_settings_url( $gallery ),
			'action'	=> 'settings',
			'slug'		=> 'settings'

		));
	}
	
	if( mpp_user_can_delete_gallery( $gallery->id ) ) {
		
		mpp_add_gallery_nav_item( array(
			'label'		=> __( 'Delete', 'mediapress' ),
			'url'		=> mpp_get_gallery_delete_url( $gallery ),
			'action'	=> 'delete',
			'slug'		=> 'delete'

		));
	}
    
}

add_action( 'template_redirect', 'mpp_actions', 4  );

function mpp_actions() {
	
	do_action( 'mpp_actions' );
}