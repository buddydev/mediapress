<?php
/**
 * Load MediaPress Group extension 
 */
add_action( 'mpp_loaded', 'mpp_group_extension_load' );

function mpp_group_extension_load(){
	
	$files = array(
		'actions.php',
		'functions.php',
		'hooks.php',
		'group-extension.php',
	);
	
	
	$path = mediapress()->get_path() . 'modules/groups/';
	
	foreach( $files as $file )
		require_once $path . $file;
	
	do_action( 'mpp_group_extension_loaded' );
	
}

function mpp_group_init() {
	
	    mpp_register_status( array(
            'key'           => 'groupsonly',
            'label'         => __( 'Group Only', 'mediapress' ),
            'labels'        => array( 
									'singular_name' => __( 'Group Only', 'mediapress' ),
									'plural_name'	=> __( 'Group Only', 'mediapress' )
			),
            'description'   => __( 'Group Only Privacy Type', 'mediapress' ),
            'callback'      => 'mpp_check_group_access'
    ));
    
		
}

add_action( 'mpp_init', 'mpp_group_init', 2 );

//filter status dd

function mpp_group_filter_status( $statuses ) {
	
	if( bp_is_group() ) {
		unset( $statuses['friends'] );
		unset( $statuses['private'] );
		
	}else{
		unset( $statuses['groupsonly'] );
	}
	
	return $statuses;
		
}

//add_filter( 'mpp_get_editable_statuses', 'mpp_group_filter_status' );