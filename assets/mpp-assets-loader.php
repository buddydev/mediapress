<?php
/**
 * Asset Loader.
 *  Loads various scripts/styles for MediaPress
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Script Loader for MediaPress, loads appropriate scripts as enqueued by various components of gallery
 */
class MPP_Assets_Loader {

	/**
	 * Absolute url to the mediapress plugin dir
	 *
	 * @var string
	 */
	private $url = '';

	/**
	 * Singleton instance of MPP_Assets_Loader
	 *
	 * @var MPP_Assets_Loader
	 */
	private static $instance;

	/**
	 * MPP_Assets_Loader constructor.
	 */
	private function __construct() {

		$this->url = mediapress()->get_url();

		// load js on front end.
		add_action( 'mpp_enqueue_scripts', array( $this, 'register' ) );
		add_action( 'mpp_enqueue_scripts', array( $this, 'enqueue' ) );
        // add various settings needed in js.
		add_action( 'mpp_enqueue_scripts', array( $this, 'add_js_settings' ) );

		// load admin js.
		add_action( 'mpp_admin_enqueue_scripts', array( $this, 'register' ) );
		add_action( 'mpp_admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
		add_action( 'mpp_admin_enqueue_scripts', array( $this, 'add_js_settings' ) );

		add_action( 'wp_footer', array( $this, 'footer' ) );
		add_action( 'in_admin_footer', array( $this, 'footer' ) );
	}

	/**
	 * Factory Method, Retrieves or creates singleton instance.
	 *
	 * @return MPP_Assets_Loader singleton instance
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
     * Registers assets for loading.
     */
    public function register() {
        $this->register_vendors();
        $this->register_core();
    }

	/**
     * Enqueues Front end assets
	 */
	public function enqueue() {

		if ( ! apply_filters( 'mpp_load_js', true ) ) {
			// is this  a good idea? should we allow this?
			return;
		}

		wp_enqueue_script( 'mpp-core' );

        // only logged users can upload currently.
		if ( is_user_logged_in() ) {
			wp_enqueue_script( 'mpp-core-uploaders' );
			wp_enqueue_script( 'mpp-activity-uploader' );
			wp_enqueue_script( 'mpp-remote' );
			wp_enqueue_script( 'mpp-manage' );
			wp_enqueue_script( 'mpp-media-activity' );
		}

		// only load the lightbox if it is enabled in the admin settings.
		if ( mpp_get_option( 'load_lightbox' ) ) {
			wp_enqueue_script( 'magnific-js' );
			wp_enqueue_style( 'magnific-css' );
		}

		wp_enqueue_style( 'mpp-extra-css' );
		wp_enqueue_style( 'mpp-core-css' );
		wp_enqueue_style( 'dropzone' );

        $this->enqueue_player_assets();
	}

    /**
     * Enqueues admin assets
	 */
	public function enqueue_admin() {

		if ( ! apply_filters( 'mpp_load_js', true ) ) {
			// is this  a good idea? should we allow this?
			return;
		}

		// we have to be selective about admin only? we always load it on front end
		// do not load on any admin page except the edit gallery?
		if ( is_admin() && function_exists( 'get_current_screen' ) && get_current_screen()->post_type != mpp_get_gallery_post_type() ) {
			return;
		}
		wp_enqueue_script( 'mpp-uploader' );
		wp_enqueue_script( 'mpp-core-uploaders' );
		wp_enqueue_script( 'mpp-core' );
		wp_enqueue_script( 'mpp-remote' );
		$this->enqueue_player_assets();
	}

	/**
     * Registers core assets.
	 */
    private function register_core() {

	    $mpp_core_deps_info = require 'js/dist/mpp-core.asset.php';

	    wp_register_script( 'mpp-core',
		    $this->url . 'assets/js/dist/mpp-core.js',
		    array_merge(
			    $mpp_core_deps_info['dependencies'],
			    array(
				    'jquery-ui-sortable',
				    'jquery-touch-punch', // for mobile jquery ui drag/drop support.
			    )
		    ),
		    $mpp_core_deps_info['version']
	    );

	    $mpp_uploader_deps_info = require 'js/dist/mpp-uploader.asset.php';

        wp_register_script( 'mpp-uploader', $this->url . 'assets/js/dist/mpp-uploader.js',
	        array_merge( $mpp_uploader_deps_info['dependencies'], array('mpp-core')),
	        $mpp_uploader_deps_info['version']
        );

	    $mpp_uploader_deps_info = require 'js/dist/mpp-core-uploaders.asset.php';

        wp_register_script( 'mpp-core-uploaders', $this->url . 'assets/js/dist/mpp-core-uploaders.js',
	        $mpp_uploader_deps_info['dependencies'],
	        $mpp_uploader_deps_info['version']
        );

        $activity_uploader_deps_info = require 'js/dist/mpp-activity-uploader.asset.php';
        wp_register_script( 'mpp-activity-uploader', $this->url . 'assets/js/dist/mpp-activity-uploader.js',
	        $activity_uploader_deps_info['dependencies'],
            $activity_uploader_deps_info['version']
        );
	    // manage
	    $manage_deps_info = require 'js/dist/mpp-media-activity.asset.php';
	    wp_register_script( 'mpp-media-activity', $this->url . 'assets/js/dist/mpp-media-activity.js',
		    $manage_deps_info['dependencies'],
		    $manage_deps_info['version']
	    );

        // remote
	    $manage_deps_info = require 'js/dist/mpp-remote.asset.php';
	    wp_register_script( 'mpp-remote', $this->url . 'assets/js/dist/mpp-remote.js',
		    $manage_deps_info['dependencies'],
		    $manage_deps_info['version']
	    );

        // manage
	    $manage_deps_info = require 'js/dist/mpp-manage.asset.php';
	    wp_register_script( 'mpp-manage', $this->url . 'assets/js/dist/mpp-manage.js',
		    $manage_deps_info['dependencies'],
		    $manage_deps_info['version']
	    );
        $this->add_uploader_settings();
        $this->plupload_localize();

	    wp_register_script( 'mpp_settings_uploader', $this->url . 'admin/mpp-settings-manager/core/_inc/uploader.js', array( 'jquery' ) );

	    wp_register_style( 'mpp-core-css', $this->url . 'assets/css/mpp-core.css' );
	    wp_register_style( 'mpp-extra-css', $this->url . 'assets/css/mpp-pure/mpp-pure.css' );
	    wp_register_style( 'dropzone', $this->url . 'assets/css/uploader.css' );
    }

	/**
     * Registers third party assets.
	 */
    private function register_vendors() {
        // dopzone js.
	    wp_register_script( 'dropzone', $this->url . 'assets/vendors/dropzone/dropzone.dist.js', array( 'jquery' ) );
	    // magnific popup for lightbox.
	    wp_register_script( 'magnific-js', $this->url . 'assets/vendors/magnific/jquery.magnific-popup.min.js', array( 'jquery' ) );
	    wp_register_style( 'magnific-css', $this->url . 'assets/vendors/magnific/magnific-popup.css' );
    }

	/**
	 * Default settings.
	 */
	private function add_uploader_settings() {
		global $wp_scripts;

		$data = $wp_scripts->get_data( 'mpp-uploader', 'data' );

		if ( $data && false !== strpos( $data, '_mppUploadSettings' ) ) {
			return;
		}

		$max_upload_size = wp_max_upload_size();


		$defaults = array(
			'file_data_name'      => '_mpp_file', // key passed to $_FILE.
			'multiple_queues'     => true,
			'max_file_size'       => $max_upload_size . 'b',
			'url'                 => admin_url( 'admin-ajax.php' ),
		);

		// Multi-file uploading doesn't currently work in iOS Safari,
		// single-file allows the built-in camera to be used as source for images.
		if ( wp_is_mobile() ) {
			$defaults['multi_selection'] = false;
		}

		$defaults = apply_filters( 'mpp_upload_default_settings', $defaults );

		$params = array(
			'action'           => 'mpp_add_media',
			'_wpnonce'         => wp_create_nonce( 'mpp_add_media' ),
			'deleteMediaNonce' => wp_create_nonce( 'mpp-manage-gallery' ), //back compat.
			'component'        => mpp_get_current_component(),
			'component_id'     => mpp_get_current_component_id(),
			'context'          => 'gallery', // default context.
		);

		$params = apply_filters( 'mpp_plupload_default_params', $params );
		// $params['_wpnonce'] = wp_create_nonce( 'media-form' );
		$defaults['params'] = $params;


		$settings = array_merge(
			$defaults,
			array(
				'browser'       => array(
					'mobile'    => wp_is_mobile(),
					'supported' => _device_can_upload(),
				),
				'limitExceeded' => false, // always false, we have other ways to check this.
			) );

		$active_types = mpp_get_active_types();

		$extensions            = $type_errors = array();
		$allowed_type_messages = array();
		$type_browser_messages = array();
		foreach ( $active_types as $type => $object ) {
			$type_extensions = mpp_get_allowed_file_extensions_as_string( $type, ',' );

			$extensions[ $type ]            = array(
				'title'      => sprintf( 'Select %s', mpp_get_type_singular_name( $type ) ),
				'extensions' => $type_extensions,
			);
			$readable_extensions            = mpp_get_allowed_file_extensions_as_string( $type, ', ' );
			$type_errors[ $type ]           = sprintf( _x( 'This file type is not allowed. Allowed file types are: %s', 'type error message', 'mediapress' ), $readable_extensions );
			$allowed_type_messages[ $type ] = sprintf( _x( 'Please only select : %s', 'type error message', 'mediapress' ), $readable_extensions );
			$type_browser_messages[ $type ] = sprintf( _x( '<strong>+Add %s</strong> Or drag and drop', 'dropzone file browse message', 'mediapress' ), mpp_get_type_plural_name( $type ) );
		}

		$settings['types']                 = $extensions;
		$settings['type_errors']           = $type_errors;
		$settings['allowed_type_messages'] = $allowed_type_messages;
		$settings['max_allowed_file_size'] = sprintf( _x( 'Maximum allowed file size: %s', 'maximum allowed file size info', 'mediapress' ), size_format( wp_max_upload_size() ) );
		$settings['type_browser_messages'] = $type_browser_messages;

		if ( mpp_is_single_gallery() ) {
			$settings['current_type'] = mpp_get_current_gallery()->type;
		}

		$settings['activity_disable_auto_file_browser'] = mpp_get_option( 'activity_disable_auto_file_browser', 0 );
		$settings['empty_url_message'] = __( 'Please provide a url.', 'mediapress' );

		$settings['loader_src'] = mpp_get_asset_url( 'assets/images/loader.gif', 'mpp-loader' );

        $settings = apply_filters( 'mpp_upload_settings', $settings );
		$script = 'var _mppUploadSettings = ' . json_encode( $settings ) . ';';

		if ( $data ) {
			$script = "$data\n$script";
		}

		$wp_scripts->add_data( 'mpp-uploader', 'data', $script );
	}

	/**
	 * Add extra js settings.
	 */
	public function add_js_settings() {

		$settings = array(
			'enable_activity_lightbox'              => mpp_get_option( 'enable_activity_lightbox' ) ? true : false,
			'enable_gallery_lightbox'               => mpp_get_option( 'enable_gallery_lightbox' ) ? true : false,
			'enable_lightbox_in_gallery_media_list' => mpp_get_option( 'enable_lightbox_in_gallery_media_list' ) ? true : false,
		);

		$disabled_types_as_keys = array();

		$disabled_types = mpp_get_option( 'lightbox_disabled_types', array() );

		if ( empty( $disabled_types ) ) {
			$disabled_types = array();
		}

		foreach ( $disabled_types as $type ) {
			$disabled_types_as_keys[ $type ] = 1;
		}

		$settings['lightboxDisabledTypes'] = $disabled_types_as_keys;

		$settings = apply_filters( 'mpp_localizable_settings', $settings );

        // backward compat, keep the data filter.
		$settings = apply_filters( 'mpp_localizable_data', $settings );

		wp_localize_script( 'mpp-core', '_mppSettings', $settings );
	}

    /**
	 * Enforces loading MediaElement.js player.
	 */
	private function enqueue_player_assets() {
		// we only need these to be loaded for activity page, should we put a condition here?
		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script( 'wp-mediaelement' );
		// force wp to load _js template for the playlist and the code to.
		do_action( 'wp_playlist_scripts' ); // may not be a good idea.

	}

	/**
	 * Simply injects the html which we later use for showing loaders
	 * The benefit of loading it into dom is that the images are preloaded and have better user experience
	 */
	public function footer() {
		?>
        <ul style="display: none;">
            <li id="mpp-loader-wrapper" style="display:none;" class="mpp-loader">
                <div id="mpp-loader"><img
                            src="<?php echo mpp_get_asset_url( 'assets/images/loader.gif', 'mpp-loader' ); ?>"/></div>
            </li>
        </ul>

        <div id="mpp-cover-uploading" style="display:none;" class="mpp-cover-uploading">
            <img src="<?php echo mpp_get_asset_url( 'assets/images/loader.gif', 'mpp-cover-loader' ); ?>"/>
        </div>


		<?php
	}

	/**
	 * Localize strings for use at various places
	 */
	public function localize_strings() {

		$params = apply_filters( 'mpp_js_strings', array(
			'show_all'            => __( 'Show all', 'mediapress' ),
			'show_all_comments'   => __( 'Show all comments for this thread', 'mediapress' ),
			'show_x_comments'     => __( 'Show all %d comments', 'mediapress' ),
			'mark_as_fav'         => __( 'Favorite', 'mediapress' ),
			'my_favs'             => __( 'My Favorites', 'mediapress' ),
			'remove_fav'          => __( 'Remove Favorite', 'mediapress' ),
			'view'                => __( 'View', 'mediapress' ),
			'bulk_delete_warning' => _x( 'Deleting will permanently remove all selected media and files. Do you want to proceed?', 'bulk deleting warning message', 'mediapress' ),
		) );
		wp_localize_script( 'mpp_core', '_mppStrings', $params );
	}

	/**
	 * A copy from wp pluload localize.
	 */
	private function plupload_localize() {

		// error message for both plupload and swfupload.
		$uploader_l10n = array(
			'dictFileTooBig'               => _x( 'File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.', 'Uploader feedback', 'mediapress' ),
			'dictInvalidFileType'          => _x( "You can't upload files of this type.", 'Uploader feedback', 'mediapress' ),
			'dictResponseError'            => _x( 'Server responded with {{statusCode}} code.', 'Uploader feedback', 'mediapress' ),
			'dictCancelUpload'             => _x( 'Cancel upload.', 'Uploader feedback', 'mediapress' ),
			'dictUploadCanceled'           => _x( 'Upload canceled.', 'Uploader feedback', 'mediapress' ),
			'dictCancelUploadConfirmation' => _x( 'Are you sure you want to cancel this upload?', 'Uploader feedback', 'mediapress' ),
			'dictRemoveFile'               => _x( 'Remove file', 'Uploader feedback', 'mediapress' ),
			'dictMaxFilesExceeded'         => _x( 'You can not upload any more files.', 'Uploader feedback', 'mediapress' ),

			/*
			'queue_limit_exceeded'      => __( 'You have attempted to queue too many files.' ),
		'file_exceeds_size_limit'   => __( '%s exceeds the maximum upload size for this site.' ),
		'zero_byte_file'            => __( 'This file is empty. Please try another.' ),
		'invalid_filetype'          => __( 'This file type is not allowed. Please try another.' ),
		'not_an_image'              => __( 'This file is not an image. Please try another.' ),
		'image_memory_exceeded'     => __( 'Memory exceeded. Please try another smaller file.' ),
		'image_dimensions_exceeded' => __( 'This is larger than the maximum size. Please try another.' ),
		'default_error'             => __( 'An error occurred in the upload. Please try again later.' ),
		'missing_upload_url'        => __( 'There was a configuration error. Please contact the server administrator.' ),
		'upload_limit_exceeded'     => __( 'You may only upload 1 file.' ),
		'http_error'                => __( 'HTTP error.' ),
		'upload_failed'             => __( 'Upload failed.' ),
		'big_upload_failed'         => __( 'Please try uploading this file with the %1$sbrowser uploader%2$s.' ),
		'big_upload_queued'         => __( '%s exceeds the maximum upload size for the multi-file uploader when used in your browser.' ),
		'io_error'                  => __( 'IO error.' ),
		'security_error'            => __( 'Security error.' ),
		'file_cancelled'            => __( 'File canceled.' ),
		'upload_stopped'            => __( 'Upload stopped.' ),
		'dismiss'                   => __( 'Dismiss' ),
		'crunching'                 => __( 'Crunching&hellip;' ),
		'deleted'                   => __( 'moved to the trash.' ),
		'error_uploading'           => __( '&#8220;%s&#8221; has failed to upload.' ),
			*/
		);

		wp_localize_script( 'mpp-uploader', '_mppUploaderFeedbackL10n', $uploader_l10n );
	}

}

// initialize.
MPP_Assets_Loader::get_instance(); //initialize

