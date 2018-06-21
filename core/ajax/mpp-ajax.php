<?php
/**
 * MediaPress AddMedia/Cover handler.
 *
 * Handle media and cover upload.
 *
 * @package    MediaPress
 * @subpackage Core/Ajax
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit( 0 );

/**
 * MediaPress Ajax helper
 *
 * We will be moving out stuff to their respective classes in future
 * For now, It is the monolithic implementation for most of the mediapress actions
 */
class MPP_Ajax_Helper {

	/**
	 * Singleton instance.
	 *
	 * @var MPP_Ajax_Helper
	 */
	private static $instance;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->setup();
	}

	/**
	 * Get singleton instance
	 *
	 * @return MPP_Ajax_Helper
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup hooks for handling actions.
	 */
	private function setup() {
		// add/upload a new Media.
		add_action( 'wp_ajax_mpp_add_media', array( $this, 'add_media' ) );
		add_action( 'wp_ajax_mpp_upload_cover', array( $this, 'cover_upload' ) );
	}

	/**
	 * Add new media via ajax
	 * This method will be refactored in future to allow adding media from web
	 */
	public function add_media() {

		// check for the referrer.
		check_ajax_referer( 'mpp_add_media' );

		$response = array();

		$file = $_FILES;

		// input file name, set via the mpp.Uploader
		// key name in the files array.
		$file_id = '_mpp_file';

		// find the components we are trying to add for.
		$component    = isset( $_POST['component'] ) ? trim( $_POST['component'] ) : null;
		$component_id = isset( $_POST['component_id'] ) ? absint( $_POST['component_id'] ) : 0;
		$context      = isset( $_POST['context'] ) ? $_POST['context'] : '';

		$context      = mpp_get_upload_context( false, $context );

		if ( ! $component ) {
			$component = mpp_get_current_component();
		}

		if ( ! $component_id ) {
			$component_id = mpp_get_current_component_id();
		}

		// To allow posting on other member's wall, we will need to
		// change the component id to current user id if the context is activity.
		if ( 'activity' === $context && 'members' === $component ) {
			$component_id = get_current_user_id();
		}

		// Check if MediaPress is enabled for this component/component id.
		if ( ! mpp_is_enabled( $component, $component_id ) ) {
			wp_send_json_error( array(
				'message' => __( 'Sorry, the upload functionality is disabled temporarily.', 'mediapress' ),
			) );
		}

		// get the uploader.
		// should we pass the component?
		// should we check for the existence of the default storage method?
		$uploader = mpp_get_storage_manager();

		// check if the server can handle the upload?
		if ( ! $uploader->can_handle() ) {
			wp_send_json_error( array(
				'message' => __( 'Server can not handle this much amount of data. Please upload a smaller file or ask your server administrator to change the settings.', 'mediapress' ),
			) );
		}

		// check if the user has available storage for his profile
		// or the component gallery(component could be groups, sitewide).
		if ( ! mpp_has_available_space( $component, $component_id ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unable to upload. You have used the allowed storage quota!', 'mediapress' ),
			) );
		}
		// if we are here, the server can handle upload.
		$gallery_id = 0;

		if ( isset( $_POST['gallery_id'] ) ) {
			$gallery_id = absint( $_POST['gallery_id'] );
		}

		// did the client send us gallery id? If yes, let us try to fetch the gallery object.
		if ( $gallery_id ) {
			$gallery = mpp_get_gallery( $gallery_id );
		} else {
			// not set.
			$gallery = null;
		}

		// get media type from file extension.
		$media_type = mpp_get_media_type_from_extension( mpp_get_file_extension( $file[ $file_id ]['name'] ) );

		// Invalid media type?
		if ( ! $media_type || ! mpp_component_supports_type( $component, $media_type ) ) {
			wp_send_json_error( array( 'message' => __( 'This file type is not supported.', 'mediapress' ) ) );
		}

		// if there is no gallery type defined.
		// It wil happen in case of new gallery creation from admin page
		// we will set the gallery type as the type of the first media.
		if ( $gallery && empty( $gallery->type ) ) {
			// update gallery type
			// set it to media type.
			mpp_update_gallery_type( $gallery, $media_type );
		}

		// fallback to fetch context based gallery, if gallery is not specified.
		// if there is no gallery id given, we may want to auto create the gallery
		// try fetching the available default gallery for the context.
		if ( ! $gallery ) {
			// try fetching context gallery?
			$gallery = mpp_get_context_gallery( array(
				'component'    => $component,
				'component_id' => $component_id,
				'user_id'      => get_current_user_id(),
				'type'         => $media_type,
				'context'      => $context,
			) );
		}

		if ( ! $gallery ) {
			wp_send_json_error( array( 'message' => __( 'The gallery is not selected.', 'mediapress' ) ) );
		}

		// if we are here, It means we have found a gallery to upload
		// check if gallery has a valid status?
		$is_valid_status = mpp_is_active_status( $gallery->status );
		if ( ! $is_valid_status ) {
			$default_status = mpp_get_default_status();
			// Check and update status if applicable.
			if (  mpp_is_active_status( $default_status ) && mpp_component_supports_status( $component, $default_status ) ) {
				// the current gallery status is invalid,
				// update status to current default privacy.
				mpp_update_gallery_status( $gallery, $default_status );
			} else {
				// should we inform user that we can't handle this request due to status issue?
				wp_send_json_error( array( 'message' => __( 'There was a problem with the privacy of your gallery.', 'mediapress' ) ) );
			}
		}

		// we may want to check the upload type and set the gallery to activity gallery etc if it is not set already.
		$error = false;

		// detect media type of uploaded file here and then upload it accordingly.
		// also check if the media type uploaded and the gallery type matches or not.
		// let us build our response for javascript
		// if we are uploading to a gallery, check for type.
		// since we will be allowing upload without gallery too,
		// It is required to make sure $gallery is present or not.
		if ( ! mpp_is_mixed_gallery( $gallery ) && $media_type !== $gallery->type ) {
			// if we are uploading to a gallery and It is not a mixed gallery, the media type must match the gallery type.
			wp_send_json_error( array(
				'message' => sprintf( __( 'This file type is not allowed in current gallery. Only <strong>%s</strong> files are allowed!', 'mediapress' ), mpp_get_allowed_file_extensions_as_string( $gallery->type ) ),
			) );
		}

		// If gallery is given, reset component and component_id to that of gallery's.
		if ( $gallery ) {
			$gallery_id = $gallery->id;
			// reset component and component_id
			// if they are set on gallery.
			if ( ! empty( $gallery->component ) && mpp_is_active_component( $gallery->component ) ) {
				$component = $gallery->component;
			}

			if ( ! empty( $gallery->component_id ) ) {
				$component_id = $gallery->component_id;
			}
		}


		// if we are here, all is well :).
		if ( ! mpp_user_can_upload( $component, $component_id, $gallery ) ) {

			$error_message = apply_filters( 'mpp_upload_permission_denied_message', __( "You don't have sufficient permissions to upload.", 'mediapress' ), $component, $component_id, $gallery );

			wp_send_json_error( array( 'message' => $error_message ) );
		}

		$status = isset( $_POST['media_status'] ) ? $_POST['media_status'] : '';

		if ( empty( $status ) && $gallery ) {
			// inherit from parent,gallery must have an status.
			$status = $gallery->status;
		}

		// we may need some more enhancements here.
		if ( ! $status ) {
			$status = mpp_get_default_status();
		}

		if ( ! mpp_is_active_status( $status ) || ! mpp_component_supports_status( $component, $status ) ) {
			// The status must be valid and supported by current component.
			// else we won't process upload.
			wp_send_json_error( array( 'message' => __( 'There was a problem with the privacy.', 'mediapress' ) ) );
		}

		// if we are here, we have checked for all the basic errors, so let us just upload now.
		$uploaded = $uploader->upload( $file, array(
			'file_id'      => $file_id,
			'gallery_id'   => $gallery_id,
			'component'    => $component,
			'component_id' => $component_id,
		) );

		// upload was successful?
		if ( ! isset( $uploaded['error'] ) ) {

			// file was uploaded successfully.
			if ( apply_filters( 'mpp_use_processed_file_name_as_media_title', false ) ) {
				$title = wp_basename( $uploaded['file'] );
			} else {
				$title = wp_basename( $_FILES[ $file_id ]['name'] );
			}

			$title_parts = pathinfo( $title );
			$title       = trim( substr( $title, 0, - ( 1 + strlen( $title_parts['extension'] ) ) ) );

			$url  = $uploaded['url'];
			$type = $uploaded['type'];
			$file = $uploaded['file'];

			//$title = isset( $_POST['media_title'] ) ? $_POST['media_title'] : '';

			$content = isset( $_POST['media_description'] ) ? $_POST['media_description'] : '';

			$meta = $uploader->get_meta( $uploaded );


			$title_desc = mpp_get_title_desc_from_meta( $type, $meta );

			if ( ! empty( $title_desc ) ) {

				if ( empty( $title ) && ! empty( $title_desc['title'] ) ) {
					$title = $title_desc['title'];
				}

				if ( empty( $content ) && ! empty( $title_desc['content'] ) ) {
					$content = $title_desc['content'];
				}
			}

			$is_orphan = 0;
			// Any media uploaded via activity is marked as orphan
			// Orphan means not associated with the mediapress unless the activity to which it was attached is actually created,
			// check core/activity/actions.php to see how the orphaned media is adopted by the activity :).
			if ( 'activity' === $context ) {
				// by default mark all uploaded media via activity as orphan.
				$is_orphan = 1;
			}

			$media_data = array(
				'title'          => $title,
				'description'    => $content,
				'gallery_id'     => $gallery_id,
				'user_id'        => get_current_user_id(),
				'is_remote'      => false,
				'type'           => $media_type,
				'mime_type'      => $type,
				'src'            => $file,
				'url'            => $url,
				'status'         => $status,
				'comment_status' => 'open',
				'storage_method' => mpp_get_storage_method(),
				'component_id'   => $component_id,
				'component'      => $component,
				'context'        => $context,
				'is_orphan'      => $is_orphan,
			);

			$id = mpp_add_media( $media_data );

			// if the media is not uploaded from activity and auto publishing is not enabled,
			// record as unpublished.
			if (  'activity' !== $context && ! mpp_is_auto_publish_to_activity_enabled( 'add_media' ) ) {
				mpp_gallery_add_unpublished_media( $gallery_id, $id );
			}

			mpp_gallery_increment_media_count( $gallery_id );

			$attachment = mpp_media_to_json( $id );
			echo json_encode( array(
				'success' => true,
				'data'    => $attachment,
			) );

			exit( 0 );
		} else {

			wp_send_json_error( array( 'message' => $uploaded['error'] ) );
		}
	}

	/**
	 * Handle gallery/Media(video,audio,doc) cover upload.
	 */
	public function cover_upload() {

		// check for the referrer.
		check_ajax_referer( 'mpp_add_media' );

		$file = $_FILES;

		// key name in the files array.
		$file_id = '_mpp_file';
		// find the components we are trying to add for.
		$component = $component_id = 0;
		$context = 'cover';

		// default upload to gallery cover.
		$gallery_id  = absint( $_POST['mpp-gallery-id'] );
		$parent_id   = absint( $_POST['mpp-parent-id'] );
		$parent_type = isset( $_POST['mpp-parent-type'] ) ? trim( $_POST['mpp-parent-type'] ) : 'gallery';

		if ( ! $gallery_id || ! $parent_id ) {
			return;
		}

		$gallery = mpp_get_gallery( $gallery_id );

		$component    = $gallery->component;
		$component_id = $gallery->component_id;

		// get the uploader.
		$uploader = mpp_get_storage_manager();

		// check if the server can handle the upload?
		if ( ! $uploader->can_handle() ) {
			wp_send_json_error( array(
				'message' => __( 'Server can not handle this much amount of data. Please upload a smaller file or ask your server administrator to change the settings.', 'mediapress' )
			) );
		}

		$media_type = mpp_get_media_type_from_extension( mpp_get_file_extension( $file[ $file_id ]['name'] ) );

		//cover is always a photo,
		if ( $media_type != 'photo' ) {

			wp_send_json( array(
				'message' => sprintf( __( 'Please upload a photo. Only <strong>%s</strong> files are allowed!', 'mediapress' ), mpp_get_allowed_file_extensions_as_string( $media_type ) )
			) );
		}

		$error = false;

		// if we are here, all is well :).
		if ( ! mpp_user_can_upload( $component, $component_id, $gallery ) ) {

			wp_send_json_error( array( 'message' => __( "You don't have sufficient permissions to upload.", 'mediapress' ) ) );
		}

		// if we are here, we have checked for all the basic errors, so let us just upload now.
		$uploaded = $uploader->upload( $file, array(
			'file_id'      => $file_id,
			'gallery_id'   => $gallery_id,
			'component'    => $component,
			'component_id' => $component_id,
			'is_cover'     => 1
		) );

		// upload was successful?
		if ( ! isset( $uploaded['error'] ) ) {

			// file was uploaded successfully.
			$title = $_FILES[ $file_id ]['name'];

			$title_parts = pathinfo( $title );
			$title       = trim( substr( $title, 0, - ( 1 + strlen( $title_parts['extension'] ) ) ) );

			$url  = $uploaded['url'];
			$type = $uploaded['type'];
			$file = $uploaded['file'];


			// $title = isset( $_POST['media_title'] ) ? $_POST['media_title'] : '';

			$content = isset( $_POST['media_description'] ) ? $_POST['media_description'] : '';

			$meta = $uploader->get_meta( $uploaded );

			$title_desc = mpp_get_title_desc_from_meta( $type, $meta );

			if ( ! empty( $title_desc ) ) {

				if ( empty( $title ) && ! empty( $title_desc['title'] ) ) {
					$title = $title_desc['title'];
				}

				if ( empty( $content ) && ! empty( $title_desc['content'] ) ) {
					$content = $title_desc['content'];
				}
			}

			$status = isset( $_POST['media_status'] ) ? $_POST['media_status'] : '';

			if ( empty( $status ) && $gallery ) {
				// inherit from parent,gallery must have an status.
				$status = $gallery->status;
			}

			// we may need some more enhancements here.
			if ( ! $status ) {
				$status = mpp_get_default_status();
			}

			$is_orphan = 0;


			$media_data = array(
				'title'          => $title,
				'description'    => $content,
				'gallery_id'     => $parent_id,
				'user_id'        => get_current_user_id(),
				'is_remote'      => 0,
				'type'           => $media_type,
				'mime_type'      => $type,
				'src'            => $file,
				'url'            => $url,
				'status'         => $status,
				'comment_status' => 'open',
				'storage_method' => mpp_get_storage_method(),
				'component_id'   => $component_id,
				'component'      => $component,
				'context'        => $context,
				'is_orphan'      => $is_orphan,
				'is_cover'       => true
			);
			// cover should never be recorded as activity.
			add_filter( 'mpp_do_not_record_add_media_activity', '__return_true' );

			$id = mpp_add_media( $media_data );
			// in case media creation failed.
			if ( ! $id ) {
				wp_send_json_error( array( 'message' => __( 'There was a problem. Please try again.', 'mediapress' ) ) );
			}

			if ( $parent_type == 'gallery' ) {
				$old_cover = mpp_get_gallery_cover_id( $parent_id );

			} else {
				$old_cover = mpp_get_media_cover_id( $parent_id );
			}

			if ( $gallery->type == 'photo' ) {
				mpp_gallery_increment_media_count( $gallery_id );
			} else {
				// mark it as non gallery media.
				mpp_delete_media_meta( $id, '_mpp_is_mpp_media' );

				if ( $old_cover ) {
					mpp_delete_media( $old_cover );
				}
			}

			mpp_update_media_cover_id( $parent_id, $id );

			$attachment = mpp_media_to_json( $id );

			echo json_encode( array(
				'success' => true,
				'data'    => $attachment,
			) );

			exit( 0 );

		} else {
			echo json_encode( array( 'error' => 1, 'message' => $uploaded['error'] ) );
			exit( 0 );
		}
	}

}
