<?php
/**
 * Handling MediaPress Core setup
 *
 * @package MediaPress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Sets up MediaPress core
 * Initializes various core settings/modules like
 *
 * Registers various core features
 * Registers statuses
 * Registers types(media type)
 * Also registers Component types
 * Uploaders, MediaSizes etc
 */
function mpp_setup_core() {

	// if the 'gallery' slug is not set , set it to mediapress?
	if ( ! defined( 'MPP_GALLERY_SLUG' ) ) {
		define( 'MPP_GALLERY_SLUG', 'mediapress' );
	}
	// Register privacies(status).
	// Public status.
	mpp_register_status( array(
		'key'              => 'public',
		'label'            => __( 'Public', 'mediapress' ),
		'labels'           => array(
			'singular_name' => __( 'Public', 'mediapress' ),
			'plural_name'   => __( 'Public', 'mediapress' ),
		),
		'description'      => __( 'Public Gallery Privacy Type', 'mediapress' ),
		'callback'         => 'mpp_check_public_access',
		'activity_privacy' => 'public',
	) );

	// Private status.
	mpp_register_status( array(
		'key'              => 'private',
		'label'            => __( 'Private', 'mediapress' ),
		'labels'           => array(
			'singular_name' => __( 'Private', 'mediapress' ),
			'plural_name'   => __( 'Private', 'mediapress' ),
		),
		'description'      => __( 'Private Privacy Type', 'mediapress' ),
		'callback'         => 'mpp_check_private_access',
		'activity_privacy' => 'onlyme',
	) );

	// Loggedin members only status.
	mpp_register_status( array(
		'key'              => 'loggedin',
		'label'            => __( 'Logged In Users Only', 'mediapress' ),
		'labels'           => array(
			'singular_name' => __( 'Logged In Users Only', 'mediapress' ),
			'plural_name'   => __( 'Logged In Users Only', 'mediapress' ),
		),
		'description'      => __( 'Logged In Users Only Privacy Type', 'mediapress' ),
		'callback'         => 'mpp_check_loggedin_access',
		'activity_privacy' => 'loggedin',
	) );

	/**
	 * For BuddyPress specific status, please check modules/buddypress/loader.php.
	 */

	// Register Components.
	// Register sitewide gallery component.
	mpp_register_component( array(
		'key'         => 'sitewide',
		'label'       => __( 'Sitewide Galleries', 'mediapress' ),
		'labels'      => array(
			'singular_name' => __( 'Sitewide Gallery', 'mediapress' ),
			'plural_name'   => __( 'Sitewide Galleries', 'mediapress' ),
		),
		'description' => __( 'Sitewide Galleries', 'mediapress' ),
	) );

	// Register media/gallery types.
	// Photo.
	mpp_register_type( array(
		'key'         => 'photo',
		'label'       => __( 'Photo', 'mediapress' ),
		'description' => __( 'taxonomy for image media type', 'mediapress' ),
		'labels'      => array(
			'singular_name' => __( 'Photo', 'mediapress' ),
			'plural_name'   => __( 'Photos', 'mediapress' ),
		),
		'extensions'  => array( 'jpeg', 'jpg', 'gif', 'png' ),
	) );

	// video.
	mpp_register_type( array(
		'key'         => 'video',
		'label'       => __( 'Video', 'mediapress' ),
		'labels'      => array(
			'singular_name' => __( 'Video', 'mediapress' ),
			'plural_name'   => __( 'Videos', 'mediapress' ),
		),
		'description' => __( 'Video media type taxonomy', 'mediapress' ),
		'extensions'  => array( 'mp4', 'flv', 'mpeg' ),
	) );

	// audio.
	mpp_register_type( array(
		'key'         => 'audio',
		'label'       => __( 'Audio', 'mediapress' ),
		'labels'      => array(
			'singular_name' => __( 'Audio', 'mediapress' ),
			'plural_name'   => __( 'Audios', 'mediapress' ),
		),
		'description' => __( 'Audio Media type taxonomy', 'mediapress' ),
		'extensions'  => array( 'mp3', 'wmv', 'midi' ),
	) );

	// doc.
	mpp_register_type( array(
		'key'         => 'doc',
		'label'       => __( 'Documents', 'mediapress' ),
		'labels'      => array(
			'singular_name' => __( 'Document', 'mediapress' ),
			'plural_name'   => __( 'Documents', 'mediapress' ),
		),
		'description' => __( 'This is documents gallery', 'mediapress' ),
		'extensions'  => array( 'zip', 'gz', 'doc', 'pdf', 'docx', 'xls' ),
	) );

	$size_thumb = mpp_get_option('size_thumbnail', array(
		'width'     => 200,
		'height'    => 200,
		'crop'      => 1,
	) );

	// Register media sizes.
	mpp_register_media_size( array(
		'name'   => 'thumbnail',
		'label'  => _x( 'Thumbnail', 'Thumbnail size name', 'mediapress' ),
		'height' => $size_thumb['height'],
		'width'  => $size_thumb['width'],
		'crop'   => isset( $size_thumb['crop'] ) ? $size_thumb['crop'] : 0,
		'type'   => 'default',
	) );

	$size_mid = mpp_get_option('size_mid', array(
		'width'     => 350,
		'height'    => 350,
		'crop'      => 1,
	) );
	mpp_register_media_size( array(
		'name'   => 'mid',
		'label'  => _x( 'Medium', 'Medium size name', 'mediapress' ),
		'height' => $size_mid['height'],
		'width'  => $size_mid['width'],
		'crop'   => isset( $size_mid['crop'] ) ? $size_mid['crop'] : 0,
		'type'   => 'default',
	) );

	$size_large = mpp_get_option('size_large', array(
		'width'     => 600,
		'height'    => 600,
		'crop'      => 0,
	) );

	mpp_register_media_size( array(
		'name'   => 'large',
		'label'  => _x( 'Large', 'Large size name', 'mediapress' ),
		'height' => $size_large['height'],
		'width'  => $size_large['width'],
		'crop'   => isset( $size_large['crop'] ) ? $size_large['crop'] : 0,
		'type'   => 'default',
	) );

	// Register status support for components.
	// Sitewide gallery supports 'public', 'private', 'loggedin'.
	mpp_component_add_status_support( 'sitewide', 'public' );
	mpp_component_add_status_support( 'sitewide', 'private' );
	mpp_component_add_status_support( 'sitewide', 'loggedin' );

	// Register type support for sitewide gallery.
	mpp_component_init_type_support( 'sitewide' );

	// Register storage managers here
	// Register 'local' storage manager.
	mpp_register_storage_manager( 'local', MPP_Local_Storage::get_instance() );

	// Register default gallery viewer.
	$default_view = MPP_Gallery_View_Default::get_instance();

	// All gallery types support default viewer.
	mpp_register_gallery_view( 'photo', $default_view );
	mpp_register_gallery_view( 'video', $default_view );
	mpp_register_gallery_view( 'audio', $default_view );
	mpp_register_gallery_view( 'doc', $default_view );

	$list_view = MPP_Gallery_View_List::get_instance();
	// All Gallery types support list view.
	mpp_register_gallery_view( 'photo', $list_view );
	mpp_register_gallery_view( 'video', $list_view );
	mpp_register_gallery_view( 'audio', $list_view );
	mpp_register_gallery_view( 'doc', $list_view );

	// Video playlist view is only supported by video type.
	mpp_register_gallery_view( 'video', MPP_Gallery_View_Video_Playlist::get_instance() );
	// Audio playlist view is only supported by Audio type.
	mpp_register_gallery_view( 'audio', MPP_Gallery_View_Audio_Playlist::get_instance() );

	// Media viewer support.
	mpp_register_media_view( 'photo', 'default', new MPP_Media_View_Photo() );
	mpp_register_media_view( 'doc', 'default', new MPP_Media_View_Docs() );

	// we are registering for video so we can replace it in future for flexible video views.
	mpp_register_media_view( 'video', 'default', new MPP_Media_View_Video() );

	// audio view.
	mpp_register_media_view( 'audio', 'default', new MPP_Media_View_Audio() );

	// should we register a photo viewer too? may be for the sake of simplicity?

	// setup the tabs(menu).
	mediapress()->add_menu( 'gallery', new MPP_Gallery_Menu() );
	mediapress()->add_menu( 'media', new MPP_Media_Menu() );

	do_action( 'mpp_setup_core' );
}

// initialize MediaPress core.
add_action( 'mpp_setup', 'mpp_setup_core' );

/**
 * Setup gallery menu
 */
function mpp_setup_gallery_nav() {

	// only add on single gallery.
	if ( ! mpp_is_single_gallery() ) {
		return;
	}

	$gallery = mpp_get_current_gallery();

	$url = '';

	if ( $gallery ) {
		$url = mpp_get_gallery_permalink( $gallery );
	}

	// only add view/edit/dele links on the single mgallery view.
	mpp_add_gallery_nav_item( array(
		'label'  => __( 'View', 'mediapress' ),
		'url'    => $url,
		'action' => 'view',
		'slug'   => 'view',
	) );

	$user_id = get_current_user_id();

	if ( mpp_user_can_edit_gallery( $gallery->id, $user_id ) ) {

		mpp_add_gallery_nav_item( array(
			'label'  => __( 'Edit Media', 'mediapress' ), // we can change it to media type later.
			'url'    => mpp_get_gallery_edit_media_url( $gallery ),
			'action' => 'edit',
			'slug'   => 'edit',
		) );
	}

	if ( mpp_user_can_upload( $gallery->component, $gallery->component_id, $gallery ) ) {

		mpp_add_gallery_nav_item( array(
			'label'  => __( 'Add Media', 'mediapress' ), // we can change it to media type later.
			'url'    => mpp_get_gallery_add_media_url( $gallery ),
			'action' => 'add',
			'slug'   => 'add',
		) );
	}

	if ( mpp_user_can_edit_gallery( $gallery->id, $user_id ) ) {

		mpp_add_gallery_nav_item( array(
			'label'  => __( 'Reorder', 'mediapress' ), // we can change it to media type later.
			'url'    => mpp_get_gallery_reorder_media_url( $gallery ),
			'action' => 'reorder',
			'slug'   => 'reorder',
		) );

		mpp_add_gallery_nav_item( array(
			'label'  => __( 'Edit Details', 'mediapress' ),
			'url'    => mpp_get_gallery_settings_url( $gallery ),
			'action' => 'settings',
			'slug'   => 'settings',
		) );
	}

	if ( mpp_user_can_delete_gallery( $gallery->id ) ) {

		mpp_add_gallery_nav_item( array(
			'label'  => __( 'Delete', 'mediapress' ),
			'url'    => mpp_get_gallery_delete_url( $gallery ),
			'action' => 'delete',
			'slug'   => 'delete',
		) );
	}

}
add_action( 'mpp_setup_globals', 'mpp_setup_gallery_nav' );
