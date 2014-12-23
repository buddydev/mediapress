<?php

/**
 * Theme compat for Directory pages
 * 
 */
/**
 * Handle the display of the mediapress directory index.
 */
function mpp_gallery_screen_directory() {
	if ( mpp_is_gallery_directory() ) {
		bp_update_is_directory( true, 'mediapress' );

		do_action( 'mpp_gallery_screen_directory' );

		bp_core_load_template( apply_filters( 'mpp_gallery_screen_directory', 'mediapress/directory/index-full' ) );
	}
}
add_action( 'bp_screens', 'mpp_gallery_screen_directory', 1 );
/**
 * 
 * This class sets up the necessary theme compatability actions to safely output
 * registration template parts to the_title and the_content areas of a theme.
 *
 *
 */
class MPP_Directory_Theme_Compat {

	
	public function __construct() {
		
		add_action( 'bp_setup_theme_compat', array( $this, 'is_directory' ) );
	}

	/**
	 * Are we looking at Gallery or Media Directories?
	 *
	 * @since BuddyPress (1.7)
	 */
	public function is_directory() {
		
		// Bail if not looking at the registration or activation page
		if ( ! mpp_is_gallery_directory() ) {
			return;
		}
		bp_set_theme_compat_active( true );
		buddypress()->theme_compat->use_with_current_theme = true;
		// Not a directory
		bp_update_is_directory( true, 'mediapress' );

		// Setup actions
		add_filter( 'bp_get_buddypress_template',                array( $this, 'template_hierarchy' ) );
		add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'dummy_post'    ) );
		add_filter( 'bp_replace_the_content',                    array( $this, 'directory_content' ) );
		
	}

	
	public function template_hierarchy( $templates = array() ) {
		

		// Setup our templates based on priority
		$new_templates = apply_filters( "mpp_template_hierarchy_directory", array(
			"mediapress/directory/index-full.php"
		) );

		// Merge new templates with existing stack
		// @see bp_get_theme_compat_templates()
		$templates = array_merge( (array) $new_templates, $templates );

		return $templates;
	}

	/**
	 * Update the global $post with dummy data
	 *
	 * @since BuddyPress (1.7)
	 */
	public function dummy_post() {
		// Registration page
		if ( mpp_is_gallery_directory() ) {
			$title = __( 'Gallery Directory', 'mediapress' );

			

		} 
		

		bp_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => bp_get_directory_title( 'mediapress' ),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => mpp_get_gallery_post_type(),
			'post_status'    => 'publish',
			'is_page'        => true,
			'comment_status' => 'closed'
		) );
	}

	/**
	 * Filter the_content with either the register or activate templates.
	 *
	 * @since BuddyPress (1.7)
	 */
	public function directory_content() {
		if ( mpp_is_gallery_component() ) {
			return bp_buffer_template_part( 'mediapress/directory/index', null, false );
		} 
	}
}
new MPP_Directory_Theme_Compat();

function mpp_add_bp_template_stack( $templates ) {
    // if we're on a page of our plugin and the theme is not BP Default, then we
    // add our path to the template path array
    if ( mpp_is_gallery_component() ) {
 
        $templates[] = mediapress()->get_path() . 'templates/';
    }

    return $templates;
}
 
add_filter( 'bp_get_template_stack', 'mpp_add_bp_template_stack', 10, 1 );