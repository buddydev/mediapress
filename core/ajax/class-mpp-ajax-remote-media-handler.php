<?php
/**
 * MediaPress Remote Media Handler
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
 * Remote Media Handler
 */
class MPP_Ajax_Remote_Media_Handler {

	/**
	 * Is booted?
	 *
	 * @var bool
	 */
	private static $booted = null;

	/**
	 * Boot the handler.
	 */
	public static function boot() {

		if ( self::$booted ) {
			return;
		}

		self::$booted = true;

		$self = new self();
		$self->setup();
	}

	/**
	 * Setup actions.
	 */
	public function setup() {
		add_action( 'wp_ajax_mpp_add_remote_media', array( $this, 'add_remote_media' ) );
	}

	/**
	 * Add Remote Media.
	 */
	public function add_remote_media() {

		// Is remote enabled?
		if ( ! mpp_is_remote_enabled() ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid action.', 'mediapress' ),
			) );
		}

		// check for the referrer.
		check_ajax_referer( 'mpp_add_media' );

		// Remote url.
		$url = isset( $_POST['url'] ) ? trim( $_POST['url'] ) : null;

		if ( ! $url ) {
			wp_send_json_error( array(
				'message' => __( 'Please provide a valid url.', 'mediapress' ),
			) );
		}

		// find the components we are trying to add for.
		$component    = isset( $_POST['component'] ) ? trim( $_POST['component'] ) : null;
		$component_id = isset( $_POST['component_id'] ) ? absint( $_POST['component_id'] ) : 0;
		$context      = isset( $_POST['context'] ) ? $_POST['context'] : '';

		$context = mpp_get_upload_context( false, $context );

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
				'message' => __( 'Sorry, the functionality is disabled temporarily.', 'mediapress' ),
			) );
		}
		$remote_args = array();
		$size_info = mpp_get_media_size( 'large' );
		$remote_args['width'] = isset( $size_info['width'] ) ? $size_info['width'] : 600;

		$parser = new MPP_Remote_Media_Parser( $url, $remote_args );

		// It is neither raw url, nor oembed, we can not handle it.
		if ( ! $parser->is_raw && ! $parser->is_oembed ) {
			wp_send_json_error( array(
				'message' => __( 'Sorry, can not add the url.', 'mediapress' ),
			) );
		}

		$type = $parser->type;
		// Invalid media type?
		if ( ! $type || ! mpp_component_supports_type( $component, $type ) ) {
			wp_send_json_error( array( 'message' => __( 'This type is not supported.', 'mediapress' ) ) );
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

		// if there is no gallery type defined.
		// It wil happen in case of new gallery creation from admin page
		// we will set the gallery type as the type of the first media.
		if ( $gallery && empty( $gallery->type ) ) {
			// update gallery type
			// set it to media type.
			mpp_update_gallery_type( $gallery, $type );
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
				'type'         => $type,
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
			if ( mpp_is_active_status( $default_status ) && mpp_component_supports_status( $component, $default_status ) ) {
				// the current gallery status is invalid,
				// update status to current default privacy.
				mpp_update_gallery_status( $gallery, $default_status );
			} else {
				// should we inform user that we can't handle this request due to status issue?
				wp_send_json_error( array( 'message' => __( 'There was a problem with the privacy of your gallery.', 'mediapress' ) ) );
			}
		}

		// detect media type of uploaded file here and then upload it accordingly.
		// also check if the media type uploaded and the gallery type matches or not.
		// let us build our response for javascript
		// if we are uploading to a gallery, check for type.
		// since we will be allowing upload without gallery too,
		// It is required to make sure $gallery is present or not.
		if ( ! mpp_is_mixed_gallery( $gallery ) && $type !== $gallery->type ) {
			// if we are uploading to a gallery and It is not a mixed gallery, the media type must match the gallery type.
			wp_send_json_error( array(
				'message' => sprintf( __( 'This type is not allowed in current gallery. Only <strong>%s</strong> is allowed!', 'mediapress' ), mpp_get_type_singular_name( $type ) ),
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
		if ( ! mpp_user_can_add_remote_media( $component, $component_id, $gallery ) ) {

			$error_message = apply_filters( 'mpp_remote_media_add_permission_denied_message', __( "You don't have sufficient permissions.", 'mediapress' ), $component, $component_id, $gallery );

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

		// Do the actual handling of media.
		if ( $parser->is_oembed ) {
			$info = $this->handle_oembed( $parser, $gallery_id );
		} else {
			$info = $this->handle_raw( $parser, $gallery_id );
		}

		if ( is_wp_error( $info ) ) {
			wp_send_json_error( array( 'message' => $info->get_error_message() ) );
		}

		/*
		$info = array(
			'title' => '',
			'content' => '',
			'file' => '',
			'url' => '',
			'html' => '',
			'mime_type' => '',
			'is_remote' => '',
			'is_oembed' => '',
		);*/

		if ( empty( $info['content'] ) && ! empty( $_POST['media_description'] ) ) {
			$info['content'] = $_POST['media_description'];
		}

		$is_orphan = 0;
		// Any media uploaded via activity is marked as orphan
		// Orphan means not associated with the mediapress unless the activity to which it was attached is actually created,
		// check core/activity/actions.php to see how the orphaned media is adopted by the activity :).
		if ( 'activity' === $context ) {
			// by default mark all uploaded media via activity as orphan.
			$is_orphan = 1;
		}

		$is_remote = isset( $info['is_remote'] ) ? $info['is_remote'] : 0;
		$is_oembed = isset( $info['is_oembed'] ) ? $info['is_oembed'] : 0;

		$media_data = array(
			'title'          => $info['title'],
			'description'    => $info['content'],
			'gallery_id'     => $gallery_id,
			'user_id'        => get_current_user_id(),
			'type'           => $type,
			'mime_type'      => $info['mime_type'],
			'src'            => $info['file'],
			'url'            => $info['url'],
			'embed_html'     => $info['html'],
			'embed_url'      => $parser->url,
			'status'         => $status,
			'comment_status' => 'open',
			'storage_method' => isset( $info['storage_method'] ) ? $info['storage_method'] : '',
			'component_id'   => $component_id,
			'component'      => $component,
			'context'        => $context,
			'is_remote'      => $is_remote,
			'is_oembed'      => $is_oembed,
			'is_orphan'      => $is_orphan,
			'is_raw'         => isset( $info['is_raw'] ) ? $info['is_raw'] : 0,
			'source'         => $url,
		);

		$id = mpp_add_media( $media_data );
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'There was a problem. Please try again.', 'mediapress' ) ) );
		}

		// if the media is not uploaded from activity and auto publishing is not enabled,
		// record as unpublished.
		if ( 'activity' !== $context && ! mpp_is_auto_publish_to_activity_enabled( 'add_media' ) ) {
			mpp_gallery_add_unpublished_media( $gallery_id, $id );
		}

		mpp_gallery_increment_media_count( $gallery_id );
		// For Remote Media, we will send a lighter response.
		wp_send_json_success( array(
			'id'          => $id,
			'url'         => $info['is_raw'] ? esc_url( $info['url'] ) : esc_url( $url ),
			'title'       => $info['title'],
			'content'     => $info['content'],
			'thumb_url'   => mpp_get_media_src( 'thumbnail', $id ),
			'is_remote'   => $is_remote,
			'is_raw'      => isset( $info['is_raw'] ) ? $info['is_raw'] : 0,
			'is_oembed'   => $is_oembed,
			'remote_type' => $is_oembed ? 'oembed' : 'raw',
			'html'        => $is_oembed ? mpp_get_oembed_content( $id, 'thumbnail' ) : $info['html'],
			'permalink'   => mpp_get_media_permalink( $id ),
		) );
	}

	/**
	 * Process oembed media.
	 *
	 * @param MPP_Remote_Media_Parser $parser importer.
	 * @param int                     $gallery_id gallery id.
	 *
	 * @return array|WP_Error
	 */
	private function handle_oembed( MPP_Remote_Media_Parser $parser, $gallery_id ) {

		if ( ! $parser || ! $parser->data ) {
			return new WP_Error( 'invalid_embed', __( 'Invalid media.', 'mediapress' ) );
		}

		if ( ! mpp_is_oembed_enabled() ) {
				return new WP_Error( 'not_supported', __( 'Not supported.', 'mediapress' ) );
		}

		$info = array(
			'title'     => $parser->title,
			'content'   => '',
			'file'      => '',
			'url'       => '',
			'mime_type' => '',
			'is_remote' => 1,
			'is_raw'    => 0,
			'is_oembed' => 1,
		);

		switch ( $parser->type ) {

			case 'photo':
				$info['mime_type'] = 'photo/x-embed';
				$info['is_oembed'] = 0;// we are linking to raw url.
				$html              = $parser->get_html();
				if ( ! $html ) {
					return new WP_Error( 'no_data', __( 'There was an issue, please try again.', 'mediapress' ) );
				}

				$info['html']       = $html;
				$info['gallery_id'] = $gallery_id;

				return $this->process_media( $parser->data->url, $info );

				break;

			case 'video':
				$info['mime_type'] = 'video/x-embed';
				$html              = $parser->get_html();
				if ( ! $html ) {
					return new WP_Error( 'no_data', __( 'There was an issue, please try again.', 'mediapress' ) );
				}
				$info['html'] = $html;

				break;
		}

		return $info;
	}

	/**
	 * Process raw remote media.
	 *
	 * @param MPP_Remote_Media_Parser $parser importer.
	 * @param int                     $gallery_id gallery id.
	 *
	 * @return array|WP_Error
	 */
	private function handle_raw( MPP_Remote_Media_Parser $parser, $gallery_id ) {

		if ( ! mpp_is_remote_file_enabled() ) {
			return new WP_Error( 'not_supported', __( 'Not supported.', 'mediapress' ) );
		}

		return $this->process_media( $parser->url, array(
			'title'      => $parser->title,
			'gallery_id' => $gallery_id,
		) );
	}

	/**
	 * Process a raw remote media.
	 *
	 * @param string $url raw file url.
	 * @param array  $args args.
	 *
	 * @return array|WP_Error
	 */
	private function process_media( $url, $args ) {

		$gallery_id = $args['gallery_id'];
		unset( $args['gallery_id'] );

		$info = wp_parse_args( $args, array(
			'title'     => '',
			'content'   => '',
			'file'      => '',
			'url'       => '',
			'mime_type' => '',
			'is_remote' => 1,
			'is_raw'    => 1,
			'is_oembed' => 0,
			'html'      => '',
		) );

		// Do wee download remote media?
		$download_remote_media = mpp_is_remote_file_download_enabled();
		// get the uploader.
		// should we pass the component?
		// should we check for the existence of the default storage method?
		$uploader = mpp_get_storage_manager();
		$gallery  = mpp_get_gallery( $gallery_id );
		// check if the server can handle the upload?
		if ( $download_remote_media && ! $uploader->can_handle() ) {
			wp_send_json_error( array(
				'message' => __( 'Server can not handle this much amount of data. Please upload a smaller file or ask your server administrator to change the settings.', 'mediapress' ),
			) );
		}

		// check if the user has available storage for his profile
		// or the component gallery(component could be groups, sitewide).
		if ( $download_remote_media && ! mpp_has_available_space( $gallery->component, $gallery->component_id ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unable to upload. You have used the allowed storage quota!', 'mediapress' ),
			) );
		}

		if ( ! $download_remote_media ) {
			$info['is_remote'] = 1;
			$info['is_raw']    = 1;
			$info['url']       = $url;

			// mime type and all?
			return $info;
		}

		$uploaded = $uploader->import_url( $url, $gallery_id );

		if ( isset( $uploaded['error'] ) ) {
			return new WP_Error( 'upload_error', $uploaded['error'] );
		}

		$info['mime_type'] = $uploaded['type'];
		$info['file']      = $uploaded['file'];
		$info['url']       = $uploaded['url'];
		$info['is_remote'] = 0;
		$info['is_raw']    = 0;

		return $info;
	}

}
