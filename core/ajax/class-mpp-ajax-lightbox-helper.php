<?php
/**
 * MediaPress Gallery directory ajax loader
 *
 * Loads gallery directory.
 *
 * @package    MediaPress
 * @subpackage Core/Ajax
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

// Exit if the file is accessed directly over web.
defined( 'ABSPATH' ) || exit( 0 );


/**
 * MediaPress Ajax helper
 * Not implementing it as singleton, if you need to add custom handler, attach your own with higher priority
 */
class MPP_Ajax_Lightbox_Helper {

	/**
	 * Template directory(cached)
	 *
	 * @var string
	 */
	private $template_dir;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->template_dir = mediapress()->get_path() . 'admin/templates/';

		$this->setup_hooks();
	}

	/**
	 * Setup.
	 */
	private function setup_hooks() {
		// activity media.
		add_action( 'wp_ajax_mpp_fetch_activity_media', array( $this, 'fetch_activity_media' ) );
		add_action( 'wp_ajax_nopriv_mpp_fetch_activity_media', array( $this, 'fetch_activity_media' ) );
		// for lightbox when clicked on gallery.
		add_action( 'wp_ajax_mpp_fetch_gallery_media', array( $this, 'fetch_gallery_media' ) );
		add_action( 'wp_ajax_nopriv_mpp_fetch_gallery_media', array( $this, 'fetch_gallery_media' ) );

		add_action( 'wp_ajax_mpp_lightbox_fetch_media', array( $this, 'fetch_media' ) );
		add_action( 'wp_ajax_nopriv_mpp_lightbox_fetch_media', array( $this, 'fetch_media' ) );
		add_action( 'wp_ajax_mpp_update_lightbox_media', array( $this, 'update_lightbox_media' ) );

		add_action( 'wp_ajax_mpp_reload_lightbox_media', array( $this, 'reload_lightbox_media' ) );
		add_action( 'wp_ajax_nopriv_mpp_reload_lightbox_media', array( $this, 'reload_lightbox_media' ) );


	}

	/**
	 * Get media fro activity
	 */
	public function fetch_activity_media() {

		// do we need nonce validation for this request too?
		$items = array();
		$activity_id = $_POST['activity_id'];

		if ( ! $activity_id ) {
			exit( 0 );
		}

		$media_ids = mpp_activity_get_attached_media_ids( $activity_id );

		if ( empty( $media_ids ) ) {
			$media_ids = (array) mpp_activity_get_media_id( $activity_id );
		}

		if ( empty( $media_ids ) ) {

			array_push( $items, __( 'Sorry, Nothing found!', 'mediapress' ) );

			wp_send_json( array( 'items' => $items ) );
			exit( 0 );
		}

		$gallery_id = mpp_activity_get_gallery_id( $activity_id );
		$gallery    = mpp_get_gallery( $gallery_id );

		if ( 'groups' === $gallery->component && function_exists( 'bp_is_active' ) && bp_is_active( 'groups' ) ) {
			//if( empty( buddypress()->groups))
		}

		$media_query = new MPP_Media_Query( array( 'in' => $media_ids, 'per_page' => - 1, 'nopaging' => true ) );

		if ( $media_query->have_media() ) :
			?>

			<?php while ( $media_query->have_media() ) : $media_query->the_media(); ?>

			<?php $items[] = array( 'src' => $this->get_media_lightbox_entry(), 'id' => mpp_get_media_id() ); ?>

		<?php endwhile; ?>

		<?php endif; ?>
		<?php mpp_reset_media_data(); ?>
		<?php

		wp_send_json( array( 'items' => $items ) );
		exit( 0 );
	}

	/**
	 * Fetch for gallery.
	 */
	public function fetch_gallery_media() {

		// do we need nonce validation for this request too? no.
		$items = array();

		$gallery_id = absint( $_POST['gallery_id'] );
		$gallery    = mpp_get_gallery( $gallery_id );

		if ( ! $gallery_id || empty( $gallery ) ) {
			exit( 0 );
		}

		$statuses = mpp_get_accessible_statuses( $gallery->component, $gallery->component_id, get_current_user_id() );

		$media_query = new MPP_Media_Query( array(
			'gallery_id' => $gallery_id,
			'per_page'   => - 1,
			'nopaging'   => true,
			'status'     => $statuses,
		) );

		if ( $media_query->have_media() ) :
			?>

			<?php while ( $media_query->have_media() ) : $media_query->the_media(); ?>

			<?php $items[] = array( 'src' => $this->get_media_lightbox_entry(), 'id' => mpp_get_media_id() ); ?>

		<?php endwhile; ?>

		<?php endif; ?>
		<?php mpp_reset_media_data(); ?>
		<?php

		wp_send_json( array( 'items' => $items ) );
		exit( 0 );
	}

	/**
	 * Fetch individual media or media list.
	 */
	public function fetch_media() {
		// do we need nonce validation for this request too? no.
		$items = array();

		$media_ids = $_POST['media_ids'];
		$media_ids = wp_parse_id_list( $media_ids );

		if ( empty( $media_ids ) ) {
			exit( 0 );
		}


		$media_query = new MPP_Media_Query( array(
			'in'       => $media_ids,
			'per_page' => - 1,
			'nopaging' => true,
			'orderby'  => 'none',
		) );
		$user_id     = get_current_user_id();

		if ( $media_query->have_media() ) :
			?>


			<?php while ( $media_query->have_media() ) : $media_query->the_media(); ?>

			<?php
			if ( ! mpp_user_can_view_media( mpp_get_media_id(), $user_id ) ) {
				continue;
			}

			?>
			<?php $items[ mpp_get_media_id() ] = array( 'id'=> mpp_get_media_id(), 'src' => $this->get_media_lightbox_entry() ); ?>

		<?php endwhile; ?>

		<?php endif; ?>

		<?php mpp_reset_media_data(); ?>
		<?php
		// reorder items according to our ids order, WP resets to desc order.
		$new_items = array();
		// it may not be the best way but it seems to be the only way to make it work where we should not order media at all.
		foreach ( $media_ids as $media_id ) {
			if ( isset( $items[ $media_id ] ) ) {
				$new_items[] = $items[ $media_id ];
			}
		}

		wp_send_json( array( 'items' => $new_items ) );
		exit( 0 );
	}


	/**
	 * Update media details for inline editing via ajax.
	 */
	public function update_lightbox_media() {
		if ( ! wp_verify_nonce( $_POST['mpp-nonce'], 'mpp-lightbox-edit-media' ) ) {
			wp_send_json_error( array( 'message' => __( 'Not authorized!', 'mediapress' ) ) );
		}
		$media_id = absint( $_POST['mpp-media-id'] );

		$media = mpp_get_media( $media_id );

		if ( ! $media ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request!', 'mediapress' ) ) );
		}

		// check permissions.
		if ( ! mpp_user_can_edit_media( $media_id, get_current_user_id() ) ) {
			wp_send_json_error( array( 'message' => __( 'Not authorized!', 'mediapress' ) ) );
		}

		// if we are here, check the title, description.
		// make sure title is given and the status is valid.
		$title       = isset( $_POST['mpp-media-title'] ) ? $_POST['mpp-media-title'] : '';
		$description = isset( $_POST['mpp-media-description'] ) ? $_POST['mpp-media-description'] : '';
		$status      = isset( $_POST['mpp-media-status'] ) ? $_POST['mpp-media-status'] : '';

		if ( empty( $title ) ) {
			wp_send_json_error( array( 'message' => __( "Title can't be empty.", 'mediapress' ) ) );
		}


		if ( ! mpp_component_supports_status( $media->component, $status ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid status.', 'mediapress' ) ) );
		}

		// if we are here, let us update.
		$media_info = array(
			'id'          => $media_id,
			'title'       => $title,
			'description' => $description,
			'status'      => $status,
		);

		$id = mpp_update_media( $media_info );

		// Setup current media.
		mediapress()->current_media = mpp_get_media( $id );

		if ( $id ) {
			wp_send_json_success( array(
				'message' => __( 'Updated.', 'mediapress' ),
				'content' => $this->get_media_lightbox_entry(),
			) );
		}

		// if we are here, it was an error.
		wp_send_json_error( array( 'message' => __( 'There was a problem. Please try again later!', 'mediapress' ) ) );

	}

	/**
	 * Resend the html for the given media
	 */
	public function reload_lightbox_media() {
		$media_id = isset( $_POST['media_id'] ) ? absint( $_POST['media_id'] ) : 0;

		if ( ! mpp_user_can_view_media( $media_id, get_current_user_id() ) ) {
			wp_send_json_error( __( 'Permission denied.', 'mediapress' ) );
		}
		$media = mpp_get_media( $media_id );

		if ( ! $media || ! mpp_is_valid_media( $media_id ) ) {
			wp_send_json_error( __( 'An error occurred. Please try again later.', 'mediapress' ) );
		}

		// Setup current media.
		mediapress()->current_media = $media;
		wp_send_json_success( array(
			'content' => $this->get_media_lightbox_entry(),
		) );
	}
	/**
	 * Entry for individual media.
	 *
	 * @return string
	 */
	private function get_media_lightbox_entry() {

		if ( mpp_get_option( 'lightbox_media_only' ) ) {
			$template = 'gallery/media/views/lightbox.php';
		} else {
			$template = 'gallery/media/views/lightbox-comment.php';
		}

		$located_template = apply_filters( 'mpp_lightbox_template', mpp_locate_template( array( $template ), false ) );

		if ( ! is_readable( $located_template ) ) {
			return '';
		}

		ob_start();

		require $located_template;

		return ob_get_clean();
	}

}