<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Screen handler.
 */
class MPP_Gallery_Screens {

	/**
	 * Static instance.
	 *
	 * @var MPP_Gallery_Screens
	 */
	private static $instance;

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'mpp_actions', array( $this, 'render' ) );
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MPP_Gallery_Screens
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Render view.
	 */
	public function render() {
		// single gallery
		// single media
		// edit gallery
		// edit media.
		if ( ! mpp_is_user_gallery_component() ) {
			return;
		}


		if ( mpp_is_media_management() ) {
			$this->manage_media();
		} elseif ( mpp_is_single_media() ) {
			$this->single_media();
		} elseif ( mpp_is_single_gallery() ) {
			// mpp single gallery will be true for the single gallery/edit both.
			if ( mpp_is_gallery_management()  ) {
				$this->manage_gallery();
			} else {
				$this->single_gallery();
			}
		} elseif ( mpp_is_list_gallery() ) {
			$this->user_galleries();
		}
	}

	/**
	 * Create gallery view.
	 */
	public function create_gallery() {

		mediapress()->set_action( 'create' );

		add_action( 'bp_template_content', array( $this, 'content_create_gallery' ) );

		do_action( 'gallery_screen_create_gallery' );

		bp_core_load_template( apply_filters( 'mpp_template_user_galleries_create', 'members/single/plugins' ) );
	}

	/**
	 * Upload media screen.
	 */
	public function upload_media() {
		mediapress()->set_action( 'upload' );
	}

	/**
	 * User galleries screen.
	 */
	public function user_galleries() {

		add_action( 'bp_template_content', array( $this, 'content_user_galleries_list' ) );

		do_action( 'gallery_screen_my_galleries' );

		bp_core_load_template( apply_filters( 'mpp_template_user_galleries', 'members/single/plugins' ) );
	}

	/**
	 * Single user gallery screen.
	 */
	public function single_gallery() {

		add_action( 'bp_template_content', array( $this, 'content_single_gallery' ) );

		do_action( 'gallery_screen_single_gallery' );

		bp_core_load_template( apply_filters( 'mpp_template_user_galleries', 'members/single/plugins' ) );
	}

	/**
	 * Single media screen.
	 */
	public function single_media() {

		add_action( 'bp_template_content', array( $this, 'content_single_media' ) );

		do_action( 'gallery_screen_single_gallery' );

		bp_core_load_template( apply_filters( 'mpp_template_user_galleries', 'members/single/plugins' ) );
	}

	/**
	 * Manage media screen.
	 */
	public function manage_media() {

		if ( ! mpp_user_can_manage_current_media_action() ) {
			mpp_add_feedback( __( 'Unauthorized action!', 'mediapress' ), 'error' );
			mpp_redirect( mpp_get_media_permalink( mpp_get_current_media() ) );
		}

		add_action( 'bp_template_content', array( $this, 'content_manage_media' ) );

		do_action( 'mpp_screen_manage_media' );

		bp_core_load_template( apply_filters( 'mpp_template_manage_user_media', 'members/single/plugins' ) );
	}

	/**
	 * Manage gallery screen.
	 */
	public function manage_gallery() {

		if ( ! mpp_user_can_manage_current_gallery_action() ) {
			mpp_add_feedback( __( 'Unauthorized action!', 'mediapress' ), 'error' );
			mpp_redirect( mpp_get_gallery_permalink( mpp_get_current_gallery() ) );
		}

		add_action( 'bp_template_content', array( $this, 'content_manage_gallery' ) );

		do_action( 'mpp_screen_manage_gallery' );

		bp_core_load_template( apply_filters( 'mpp_template_manage_user_galleries', 'members/single/plugins' ) );
	}

	/**
	 * User gallery Home page
	 * List of all galleries
	 */
	public function content_user_galleries_list() {
		mpp_get_component_template_loader( 'members' )->load_template();
	}

	/**
	 * Content of single Gallery
	 */
	public function content_single_gallery() {
		mpp_get_component_template_loader( 'members' )->load_template();
	}

	/**
	 * Contents for manage gallery screen.
	 */
	public function content_manage_gallery() {
		mpp_get_component_template_loader( 'members' )->load_template();
	}

	/**
	 * Content for single media screen.
	 */
	public function content_single_media() {
		mpp_get_component_template_loader( 'members' )->load_template();
	}

	/**
	 * Contents for manage media screen.
	 */
	public function content_manage_media() {
		mpp_get_component_template_loader( 'members' )->load_template();
	}

	/**
	 * Contents for create gallery screen.
	 */
	public function content_create_gallery() {
		mpp_get_component_template_loader( 'members' )->load_template();
	}
}
MPP_Gallery_Screens::get_instance();
