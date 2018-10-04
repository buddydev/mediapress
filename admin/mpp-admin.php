<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Anything admin related here
 **/
class MPP_Admin {

	/**
	 * Parent Menu slug.
	 *
	 * @var string
	 */
	private $menu_slug = '';

	/**
	 * Settings page instance.
	 *
	 * @var MPP_Admin_Settings_Page
	 */
	private $page;

	/**
	 * Singleton instance.
	 *
	 * @var MPP_Admin
	 */
	private static $instance = null;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->menu_slug = 'edit.php?post_type=' . mpp_get_gallery_post_type();
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MPP_Admin
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the parent slug for adding new admin menu items
	 *
	 * @return string
	 */
	public function get_menu_slug() {
		return $this->menu_slug;
	}

	/**
	 * Set the page object. It saves the reference to page.
	 *
	 * @param MPP_Admin_Settings_Page $page page object.
	 */
	public function set_page( $page ) {
		$this->page = $page;
	}

	/**
	 * Get the page object.
	 *
	 * @return MPP_Admin_Settings_Page
	 */
	public function get_page() {
		return $this->page;
	}
}

/**
 * Shortcut to access APP_Admin class.
 *
 * @return MPP_Admin
 */
function mpp_admin() {
	return MPP_Admin::get_instance();
}

/**
 * Handle admin Settings Screen
 */
class MPP_Admin_Settings_Helper {

	/**
	 * Singleton instance.
	 *
	 * @var MPP_Admin_Settings_Helper
	 */
	private static $instance = null;

	/**
	 * Page object.
	 *
	 * @var MPP_Admin_Settings_Page
	 */
	private $page;

	/**
	 * Array of active media types.
	 *
	 * @var array
	 */
	private $active_types = array();

	/**
	 * Array of media type options.
	 *
	 * @var array
	 */
	private $type_options = array();

	/**
	 * Constructor.
	 */
	private function __construct() {

		add_action( 'admin_init', array( $this, 'update_notice_visibility' ) );
		add_action( 'admin_init', array( $this, 'reset_settings' ) );

		add_action( 'admin_init', array( $this, 'init' ) );

		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		// add_action( 'admin_enqueue_scripts', array( $this, 'load_js' ) );
		//add_action( 'admin_enqueue_scripts', array( $this, 'load_css' ) );

	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MPP_Admin_Settings_Helper
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Reset MediaPress settings.
	 */
	public function reset_settings() {
		// is it our action?
		if ( ! isset( $_POST['mpp-action-reset-settings'] ) ) {
			return;
		}

		// nonce verify?
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'mpp-action-reset-settings' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// store default settings if not already exists.
		update_option( 'mpp-settings', mpp_get_default_options() );
		delete_option( 'mpp_settings_saved' );
	}

	/**
	 * Add notice in admin bar.
	 */
	public function admin_notice() {
		// only if user can manage_option.
		if ( ! current_user_can( 'manage_options' ) || get_option( 'mpp_settings_saved' ) ) {
			return;
		}
		$link = add_query_arg( 'page', 'mpp-settings', mpp_admin()->get_menu_slug() );
		?>
        <div class="notice notice-success">
            <p><?php _ex( 'MediaPress is almost ready. Please review & update settings(Save at least once).', 'admin notice message', 'mediapress' ); ?>
                <a href="<?php echo $link; ?>"
                   title="<?php _ex( 'Update now', 'admin notice action link title', 'mediapress' ); ?>"><?php _ex( 'Do it.', 'admin notice message' ); ?></a>
            </p>
        </div>
		<?php
	}

	/**
	 * Save if the admin notice was dismissed.
	 */
	public function update_notice_visibility() {
		if ( $this->is_settings_page() && isset( $_GET['settings-updated'] ) && current_user_can( 'manage_options' ) ) {
			update_option( 'mpp_settings_saved', 1, true );
		}
	}

	/**
	 * Build options for page rendering.
	 */
	private function build_options() {

		$this->active_types = mpp_get_active_types();

		foreach ( $this->active_types as $type => $object ) {
			$this->type_options[ $type ] = $object->label;
		}

	}

	/**
	 * Gte the types array for the component.
	 *
	 * @param string $component component name(groups|members etc).
	 *
	 * @return array
	 */
	private function get_type_options( $component = '' ) {
		return $this->type_options;
	}

	/**
	 * Check if it is settings page.
	 *
	 * @return bool
	 */
	private function is_settings_page() {

		global $pagenow;

		// we need to load on options.php otherwise settings won't be registered.
		if ( 'options.php' === $pagenow ) {
			return true;
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'mpp-settings' && isset( $_GET['post_type'] ) && $_GET['post_type'] == mpp_get_gallery_post_type() ) {
			return true;
		}

		return false;
	}

	/**
	 * Initialize the admin settings panel and fields
	 */
	public function init() {

		if ( ! $this->is_settings_page() ) {
			return;
		}


		$this->build_options();

		if ( ! class_exists( 'MPP_Admin_Settings_Page' ) ) {
			require_once mediapress()->get_path() . 'admin/mpp-settings-manager/mpp-admin-settings-loader.php';
		}


		// 'mpp-settings' is used as page slug as well as option to store in the database.
		$page = new MPP_Admin_Settings_Page( 'mpp-settings' );

		// Add a panel to to the admin.
		// A panel is a Tab and what comes under that tab.
		$panel = $page->add_panel( 'general', _x( 'General', 'Admin settings panel title', 'mediapress' ) );

		// A panel can contain one or more sections. each sections can contain fields.
		$section = $panel->add_section( 'component-settings', _x( 'Component Settings', 'Admin settings section title', 'mediapress' ) );

		$components_details = array();
		$components         = mpp_get_registered_components();

		foreach ( $components as $key => $component ) {
			$components_details[ $key ] = $component->label;
		}

		$component_keys     = array_keys( $components_details );
		$default_components = array_combine( $component_keys, $component_keys );
		$active_components  = array_keys( mpp_get_active_components() );

		if ( ! empty( $active_components ) ) {
			$default_components = array_combine( $active_components, $active_components );
		}

		$section->add_field( array(
			'name'    => 'active_components',
			'label'   => _x( 'Enable Galleries for?', 'Admin settings', 'mediapress' ),
			'type'    => 'multicheck',
			'options' => $components_details,
			'default' => $default_components,
		) );

		/**
		 * Status section.
		 */
		$registered_statuses = $available_media_stati = mpp_get_registered_statuses();

		$options = array();

		foreach ( $available_media_stati as $key => $available_media_status ) {
			$options[ $key ] = $available_media_status->get_label();
		}

		$panel->add_section( 'status-settings', _x( 'Privacy Settings', 'Admin settings section title', 'mediapress' ) )
		      ->add_field( array(
			      'name'    => 'default_status',
			      'label'   => _x( 'Default status for Gallery/Media', 'Admin settings', 'mediapress' ),
			      'desc'    => _x( 'It will be used when we are not allowed to get the status from user', 'Admin settings', 'mediapress' ),
			      'default' => mpp_get_default_status(),
			      'options' => $options,
			      'type'    => 'select',
		      ) );

		$section = $panel->get_section( 'status-settings' );

		// $registered_statuses = mpp_get_registered_statuses();
		$status_info = array();

		foreach ( $registered_statuses as $key => $status ) {
			$status_info[ $key ] = $status->label;
		}

		$active_statuses  = array_keys( mpp_get_active_statuses() );
		$status_keys      = array_keys( $status_info );
		$default_statuses = array_combine( $status_keys, $status_keys );

		if ( ! empty( $active_statuses ) ) {
			$default_statuses = array_combine( $active_statuses, $active_statuses );
		}

		$section->add_field( array(
			'name'    => 'active_statuses',
			'label'   => _x( 'Enabled Media/Gallery Statuses', 'Admin settings', 'mediapress' ),
			'type'    => 'multicheck',
			'options' => $status_info,
			'default' => $default_statuses,
		) );

		// enabled type ?
		$section     = $panel->add_section( 'types-settings', _x( 'Media Type settings', 'Admin settings section title', 'mediapress' ) );
		$valid_types = mpp_get_registered_types();

		$options          = array();
		$types_info       = array();
		$extension_fields = array();

		foreach ( $valid_types as $type => $type_object ) {

			$types_info[ $type ] = $type_object->label;

			$extension_fields [] = array(
				'id'      => 'extensions-' . $type,
				'name'    => 'extensions',
				'label'   => sprintf( _x( 'Allowed extensions for %s', 'Settings page', 'mediapress' ), $type ),
				'desc'    => _x( 'Separate file extensions by comma', 'Settings page', 'mediapress ' ),
				'default' => join( ',', (array) $type_object->get_registered_extensions() ),
				'type'    => 'extensions',
				'extra'   => array( 'key' => $type, 'name' => 'extensions' ),
			);
		}

		$type_keys     = array_keys( $types_info );
		$default_types = array_combine( $type_keys, $type_keys );
		$active_types  = array_keys( $this->active_types );

		if ( ! empty( $active_types ) ) {
			$default_types = array_combine( $active_types, $active_types );
		}

		$section->add_field( array(
			'name'    => 'active_types',
			'label'   => _x( 'Enabled Media/Gallery Types', 'Settings page', 'mediapress' ),
			'type'    => 'multicheck',
			'options' => $types_info,
			'default' => $default_types,
		) );

		$section->add_fields( $extension_fields );

		$section = $panel->add_section( 'sizes-settings', _x( 'Media Size settings', 'Admin settings section title', 'mediapress' ) );

		$defaults       = mpp_get_default_options();
		$size_thumbnail = $defaults['size_thumbnail'];

		$section->add_field( array(
			'name'    => 'size_thumbnail',
			'label'   => _x( 'Thumbnail', 'Settings page', 'mediapress' ),
			'type'    => 'media_size',
			'options' => array(
				'width'  => $size_thumbnail['width'],
				'height' => $size_thumbnail['height'],
				'crop'   => $size_thumbnail['crop'],
			),
			'desc'    => _x( 'Media Thumbnail size. If crop is enabled, photo will be cropped to the size.', 'admin settings hint', 'mediapress' ),
		) );

		$size_mid = $defaults['size_mid'];
		$section->add_field( array(
			'name'    => 'size_mid',
			'label'   => _x( 'Mid', 'Settings page', 'mediapress' ),
			'type'    => 'media_size',
			'options' => array(
				'width'  => $size_mid['width'],
				'height' => $size_mid['height'],
				'crop'   => $size_mid['crop'],
			),
			'desc'    => _x( 'Media mid size. If crop is enabled, photo will be cropped to the size.', 'admin settings hint', 'mediapress' ),

		) );

		$size_large = $defaults['size_large'];
		$section->add_field( array(
			'name'    => 'size_large',
			'label'   => _x( 'Large', 'Settings page', 'mediapress' ),
			'type'    => 'media_size',
			'options' => array(
				'width'  => $size_large['width'],
				'height' => $size_large['height'],
				'crop'   => $size_large['crop'],
			),
			'desc'    => _x( 'Media large size. If crop is enabled, photo will be cropped to the size.', 'admin settings hint', 'mediapress' ),

		) );

		$size_labels = array();
		$media_sizes = mpp_get_media_sizes();

		foreach ( $media_sizes as $name => $size ) {
			$size_labels[ $name ] = $size['label'];
		}

		$size_labels['original'] = _x( 'Original', 'Media size name', 'mediapress' );

		$section->add_field( array(
			'name'    => 'single_media_size',
			'label'   => _x( 'Image size for single media page.', 'Settings page', 'mediapress' ),
			'type'    => 'select',
			'options' => $size_labels,
			'default' => $defaults['single_media_size'],
			'desc'    => _x( 'It will be used for showing the single media.', 'admin settings hint', 'mediapress' ),
		) );

		$section->add_field( array(
			'name'    => 'lightbox_media_size',
			'label'   => _x( 'Image size for lightbox.', 'Settings page', 'mediapress' ),
			'type'    => 'select',
			'options' => $size_labels,
			'default' => $defaults['lightbox_media_size'],
			'desc'    => _x( 'It will be used for showing the media in lightbox.', 'admin settings hint', 'mediapress' ),
		) );

		// 4th section
		// enabled storage
		// Storage section
		$panel->add_section( 'storage-settings', _x( 'Storage Settings', 'Settings page section title', 'mediapress' ) )
		      ->add_field( array(
			      'name'    => 'mpp_upload_space',
			      'label'   => _x( 'maximum Upload space per user(MB)?', 'Admin storage settings', 'mediapress' ),
			      'type'    => 'text',
			      'default' => $defaults['mpp_upload_space'],

		      ) )
		      ->add_field( array(
			      'name'    => 'mpp_upload_space_groups',
			      'label'   => _x( 'maximum Upload space per group(MB)?', 'Admin storage settings', 'mediapress' ),
			      'type'    => 'text',
			      'default' => $defaults['mpp_upload_space_groups'],

		      ) )
		      ->add_field( array(
			      'name'    => 'show_upload_quota',
			      'label'   => _x( 'Show upload Quota?', 'Admin storage settings', 'mediapress' ),
			      'type'    => 'radio',
			      'default' => $defaults['show_upload_quota'],
			      'options' => array(
				      1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
		      ) )
		      ->add_field( array(
			      'name'    => 'show_max_upload_file_size',
			      'label'   => _x( 'Show maximum upload file size?', 'Admin storage settings', 'mediapress' ),
			      'desc'    => _x( 'If enabled, the maximum upload size information will appear in the upload dropzone.', 'admin storage settings', 'mediapress' ),
			      'type'    => 'radio',
			      'default' => $defaults['show_max_upload_file_size'],
			      'options' => array(
				      1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
		      ) );

		$storage_methods        = mpp_get_registered_storage_managers();
		$storage_methods        = array_keys( $storage_methods );
		$storage_method_options = array();

		foreach ( $storage_methods as $storage_method ) {
			$storage_method_options[ $storage_method ] = ucfirst( $storage_method );
		}

		$panel->get_section( 'storage-settings' )->add_field( array(
			'name'    => 'default_storage',
			'label'   => _x( 'Which should be marked as default storage?', 'Admin storage settings', 'mediapress' ),
			'default' => mpp_get_default_storage_method(),
			'options' => $storage_method_options,
			'type'    => 'radio',
		) );

		// 5th section
		// remote Settings.
		// Storage section
		$panel->add_section( 'remote-settings', _x( 'Remote Media Settings', 'Settings page section title', 'mediapress' ), _x( 'Control the remote media behaviour.', 'admin settings section description', 'mediapress' ) )
		      ->add_field( array(
			      'name'    => 'enable_remote',
			      'label'   => _x( 'Enable adding media through link?', 'Admin remote settings', 'mediapress' ),
			      'type'    => 'radio',
			      'options' => array(
				      1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
			      'default' => $defaults['enable_remote'],
			      'desc'    => _x( 'If No, It will completely turn off remote file and links features.', 'Admin remote settings', 'mediapress' ),
		      ) )
		      ->add_field( array(
			      'name'    => 'enable_remote_file',
			      'label'   => _x( 'Enable adding direct link to files from other sites?', 'Admin remote settings', 'mediapress' ),
			      'type'    => 'radio',
			      'options' => array(
				      1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
			      'default' => $defaults['enable_remote_file'],
			      'desc'    => _x( 'User will be add to remote files using direct url eg. http://example.com/hello.jpg', 'Admin remote settings', 'mediapress' ),
		      ) )
		      ->add_field( array(
			      'name'    => 'download_remote_file',
			      'label'   => _x( 'Download the file to your server?', 'Admin remote settings', 'mediapress' ),
			      'type'    => 'radio',
			      'options' => array(
				      1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
			      'default' => $defaults['download_remote_file'],
			      'desc'    => _x( 'When a user adds a remote file, should it be automatically downloaded to your server? We strongly recommend enabling it for photo.', 'Admin remote settings', 'mediapress' ),
		      ) )
		      ->add_field( array(
			      'name'    => 'enable_oembed',
			      'label'   => _x( 'Enable oembed support?', 'Admin storage settings', 'mediapress' ),
			      'type'    => 'radio',
			      'default' => $defaults['enable_oembed'],
			      'options' => array(
				      1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
			      'desc'    => _x( 'Allow users to add links(videos,photos) from youtube, vimeo, facebook etc easily.', 'Admin remote settings', 'mediapress' ),
		      ) );


		$panel->add_section( 'general-misc-settings', _x( 'Misc Settings', 'Settings page section title', 'mediapress' ) )
		      ->add_field( array(
			      'name'    => 'enable_debug',
			      'label'   => _x( 'Enable Debug Info?', 'Admin storage settings', 'mediapress' ),
			      'type'    => 'radio',
			      'default' => $defaults['enable_debug'],
			      'options' => array(
				      1 => __( 'Yes', 'mediapress' ),
				      0 => __( 'No', 'mediapress' ),
			      ),
		      ) );
		// 5th section
		$this->add_sitewide_panel( $page );
		$this->add_buddypress_panel( $page );
		$this->add_members_panel( $page );
		$this->add_groups_panel( $page );

		$theme_panel = $page->add_panel( 'theming', _x( 'Theming', 'Admin settings theme panel tab title', 'mediapress' ) );
		$theme_panel->add_section( 'display-settings', _x( 'Display Settings ', 'Admin settings theme section title', 'mediapress' ) )
		            ->add_field( array(
			            'name'    => 'galleries_per_page',
			            'label'   => _x( 'How many galleries to list per page?', 'Admin theme settings', 'mediapress' ),
			            'type'    => 'text',
			            'default' => $defaults['galleries_per_page'],
		            ) )
		            ->add_field( array(
			            'name'    => 'media_per_page',
			            'label'   => _x( 'How many Media per page?', 'Admin theme settings', 'mediapress' ),
			            'type'    => 'text',
			            'default' => $defaults['media_per_page'],
		            ) )
		            ->add_field( array(
			            'name'    => 'media_columns',
			            'label'   => _x( 'How many media per row?', 'Admin theme settings', 'mediapress' ),
			            'type'    => 'text',
			            'default' => $defaults['media_columns'],
		            ) )
		            ->add_field( array(
			            'name'    => 'gallery_columns',
			            'label'   => _x( 'How many galleries per row?', 'Admin theme settings', 'mediapress' ),
			            'type'    => 'text',
			            'default' => $defaults['gallery_columns'],
		            ) )
		            ->add_field( array(
			            'name'    => 'show_gallery_description',
			            'label'   => _x( 'Show Gallery description on single gallery pages?', 'admin theme settings', 'mediapress' ),
			            'desc'    => _x( 'Should the description for gallery be shown above the media list?', 'admin theme settings', 'mediapress' ),
			            'default' => $defaults['show_gallery_description'],
			            'type'    => 'radio',
			            'options' => array(
				            1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				            0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			            ),
		            ) )
		            ->add_field( array(
			            'name'    => 'show_media_description',
			            'label'   => _x( 'Show media description on single media pages?', 'admin theme settings', 'mediapress' ),
			            'desc'    => _x( 'Should the description for media be shown below the media ?', 'admin theme settings', 'mediapress' ),
			            'default' => $defaults['show_media_description'],
			            'type'    => 'radio',
			            'options' => array(
				            1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				            0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			            ),
		            ) );

		$theme_panel->add_section( 'audio-video', _x( 'Audio/Video specific settings', ' Admin theme section title', 'mediapress' ) )
		            ->add_field( array(
			            'name'    => 'enable_audio_playlist',
			            'label'   => _x( 'Enable Audio Playlist?', 'admin theme settings', 'mediapress' ),
			            'desc'    => _x( 'Should an audio gallery be listed as a playlist?', 'admin theme settings', 'mediapress' ),
			            'default' => $defaults['enable_audio_playlist'],
			            'type'    => 'radio',
			            'options' => array(
				            1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				            0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			            ),
		            ) )
		            ->add_field( array(
			            'name'    => 'enable_video_playlist',
			            'label'   => _x( 'Enable Video Playlist?', 'admin theme settings', 'mediapress' ),
			            'desc'    => _x( 'Should a video gallery be listed as a playlist?', 'admin theme settings', 'mediapress' ),
			            'default' => $defaults['enable_video_playlist'],
			            'type'    => 'radio',
			            'options' => array(
				            1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				            0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			            ),
		            ) )
		            ->add_field( array(
			            'name'    => 'gdoc_viewer_enabled',
			            'label'   => _x( 'Use google Doc viewer?', 'admin theme settings', 'mediapress' ),
			            'desc'    => _x( 'Do you want to use google doc viewer for viewing documents?', 'admin theme settings', 'mediapress' ),
			            'default' => $defaults['gdoc_viewer_enabled'],
			            'type'    => 'radio',
			            'options' => array(
				            1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				            0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			            ),
		            ) );


		$theme_panel->add_section( 'comments', _x( 'Comment Settings', 'Admin theme section title', 'mediapress' ) )
		            ->add_field( array(
			            'name'    => 'enable_media_comment',
			            'label'   => _x( 'Enable Commenting on single media?', 'admin theme comment settings', 'mediapress' ),
			            'default' => $defaults['enable_media_comment'],
			            'type'    => 'radio',
			            'options' => array(
				            1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				            0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			            ),
		            ) )
		            ->add_field( array(
			            'name'    => 'enable_gallery_comment',
			            'label'   => _x( 'Enable Commenting on single Gallery?', 'admin theme comment settings', 'mediapress' ),
			            'default' => $defaults['enable_gallery_comment'],
			            'type'    => 'radio',
			            'options' => array(
				            1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				            0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			            ),
		            ) );

		$theme_panel->add_section( 'lightbox', _x( 'Lightbox Settings', 'admin theme section title', 'mediapress' ) )
		            ->add_field( array(
			            'name'    => 'load_lightbox',
			            'label'   => _x( 'Load Lightbox javascript & css?', 'Admin theme settings', 'mediapress' ),
			            'desc'    => _x( 'Should we load the included lightbox script? Set no, if you are not using lightbox or want to use your own', 'Admin settings', 'mediapress' ),
			            'default' => $defaults['load_lightbox'],
			            'type'    => 'radio',
			            'options' => array(
				            1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				            0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			            ),
		            ) )
		            ->add_field( array(
			            'name'    => 'lightbox_media_only',
			            'label'   => _x( 'Do not show comments in lightbox', 'Admin theme settings', 'mediapress' ),
			            'desc'    => _x( 'Comments are shown by default.', 'Admin theme settings', 'mediapress' ),
			            'default' => $defaults['enable_gallery_lightbox'],
			            'type'    => 'radio',
			            'options' => array(
				            1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				            0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			            ),
		            ) )
		            ->add_field( array(
			            'name'    => 'enable_activity_lightbox',
			            'label'   => _x( 'Open Activity media in lightbox ?', 'Admin theme settings', 'mediapress' ),
			            'desc'    => _x( 'If you set yes, the photos etc will be open in lightbox on activity screen.', 'Admin theme settings', 'mediapress' ),
			            'default' => $defaults['enable_activity_lightbox'],
			            'type'    => 'radio',
			            'options' => array(
				            1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				            0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			            ),
		            ) )
		            ->add_field( array(
			            'name'    => 'enable_gallery_lightbox',
			            'label'   => _x( 'Open photos in lightbox if gallery is clicked?', 'Admin theme settings', 'mediapress' ),
			            'desc'    => _x( 'If you set yes, the photos will be opened in lightbox when a gallery cover is clicked.', 'Admin theme settings', 'mediapress' ),
			            'default' => $defaults['enable_gallery_lightbox'],
			            'type'    => 'radio',
			            'options' => array(
				            1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				            0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			            ),
		            ) )
		            ->add_field( array(
			            'name'    => 'enable_lightbox_in_gallery_media_list',
			            'label'   => _x( 'Open photos in lightbox if a photo inside gallery is clicked?', 'Admin theme settings', 'mediapress' ),
			            'desc'    => _x( 'If you set yes, the photos will be opened in lightbox when a photo inside gallery is clicked.', 'Admin theme settings', 'mediapress' ),
			            'default' => $defaults['enable_lightbox_in_gallery_media_list'],
			            'type'    => 'radio',
			            'options' => array(
				            1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				            0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			            ),
		            ) );

		// add an empty addons panel to allow plugins to register any setting here
		// though a plugin can add a new panel, smaller plugins should use this panel instead.
		$page->add_panel( 'addons', _x( 'Addons', 'Admin settings Addons panel tab title', 'mediapress' ), _x( 'MediaPress Addon Settings', 'Addons panel description', 'mediapress' ) );

		// auto posting to activity on gallery upload?
		// should post after the whole gallery is uploaded or just after each media?
		$this->page = $page;

		mpp_admin()->set_page( $this->page );

		do_action( 'mpp_admin_register_settings', $page );
		// initialize settings.
		$page->init();

	}

	/**
	 * Add settings panel for site gallery.
	 *
	 * @param MPP_Admin_Settings_Page $page page object.
	 */
	private function add_sitewide_panel( $page ) {

		if ( ! mpp_is_active_component( 'sitewide' ) ) {
			return;
		}

		$defaults = mpp_get_default_options();

		$sitewide_panel = $page->add_panel( 'sitewide', _x( 'Sitewide Gallery', 'Admin settings sitewide gallery panel tab title', 'mediapress' ) );

		$sitewide_panel->add_section( 'sitewide-general', _x( 'General Settings ', 'Admin settings sitewide gallery section title', 'mediapress' ) )
		               ->add_field( array(
			               'name'    => 'enable_gallery_archive',
			               'label'   => _x( 'Enable Gallery Archive?', 'admin sitewide gallery  settings', 'mediapress' ),
			               'desc'    => _x( 'If you enable, you will be able to see all galleries on a single page(archive page)', 'admin sitewide gallery settings', 'mediapress' ),
			               'default' => $defaults['enable_gallery_archive'],
			               'type'    => 'radio',
			               'options' => array(
				               1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				               0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			               ),
		               ) )
		               ->add_field( array(
			               'name'    => 'gallery_archive_slug',
			               'label'   => _x( 'Gallery Archive Slug', 'admin sitewide gallery  settings', 'mediapress' ),
			               'desc'    => _x( 'Please choose a slug that becomes part of the gallery archive permalink e.g http://yoursite.com/{slug}. No spaces, only lowercase letters.', 'admin sitewide gallery settings', 'mediapress' ),
			               'default' => $defaults['gallery_archive_slug'],
			               'type'    => 'text',
		               ) )
		               ->add_field( array(
			               'name'    => 'gallery_permalink_slug',
			               'label'   => _x( 'Gallery permalink Slug', 'admin sitewide gallery  settings', 'mediapress' ),
			               'desc'    => _x( 'Please choose a slug that becomes part of the gallery permalink e.g http://yoursite.com/{slug}/gallery-name. No spaces, only lowercase letters.', 'admin sitewide gallery settings', 'mediapress' ),
			               'default' => $defaults['gallery_permalink_slug'],
			               'type'    => 'text',
		               ) );

		$this->add_type_settings( $sitewide_panel, 'sitewide' );
		$this->add_gallery_views_panel( $sitewide_panel, 'sitewide' );
	}

	/**
	 * Add type settings to the panel depending on the component.
	 *
	 * @param MPP_Admin_Settings_Panel $panel panel object.
	 * @param string                   $component component.
	 */
	private function add_type_settings( $panel, $component ) {

		// Get active types and allow admins to support types for components.
		$options      = array();
		$active_types = $this->active_types;

		foreach ( $active_types as $type => $type_object ) {
			$options[ $type ] = $type_object->label;
		}

		$type_keys     = array_keys( $active_types );
		$default_types = array_combine( $type_keys, $type_keys );

		$key                    = $component . '_active_types';
		$active_component_types = mpp_get_option( $key );

		if ( ! empty( $active_component_types ) ) {
			$default_types = array_combine( $active_component_types, $active_component_types );
		}

		$panel->add_section( $component . '-types', _x( 'Types Settings ', 'Admin settings sitewide gallery section title', 'mediapress' ) )
		      ->add_field( array(
			      'name'    => $key,
			      'label'   => _x( 'Enabled Media/Gallery Types', 'Settings page', 'mediapress' ),
			      'type'    => 'multicheck',
			      'options' => $options,
			      'default' => $default_types,
		      ) );
	}

	/**
	 * Add settings panel for BuddyPress section.
	 *
	 * @param MPP_Admin_Settings_Page $page page object.
	 */
	private function add_buddypress_panel( $page ) {

		if ( ! mediapress()->is_bp_active() || ! ( mpp_is_active_component( 'members' ) || mpp_is_active_component( 'groups' ) ) ) {
			return;
		}

		$defaults = mpp_get_default_options();
		$panel    = $page->add_panel( 'buddypress', _x( 'BuddyPress', 'Admin settings BuddyPress panel tab title', 'mediapress' ) );

		// directory settings.
		$panel->add_section( 'directory-settings', _x( 'Directory Settings', 'Admin settings section title', 'mediapress' ) )
		      ->add_field( array(
			      'name'    => 'has_gallery_directory',
			      'label'   => _x( 'Enable Gallery Directory?', 'Admin settings', 'mediapress' ),
			      'desc'    => _x( 'Create a page to list all galleries?', 'Admin settings', 'mediapress' ),
			      'default' => $defaults['has_gallery_directory'],
			      'type'    => 'radio',
			      'options' => array(
				      1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
		      ) );

		// activity settings.
		$activity_section = $panel->add_section( 'activity-settings', _x( 'Activity Settings', 'Admin settings section title', 'mediapress' ) );

		$activity_section->add_field( array(
			'name'    => 'activity_upload',
			'label'   => _x( 'Allow Activity Upload?', 'Admin settings', 'mediapress' ),
			'desc'    => _x( 'Allow users to uploading from Activity screen?', 'Admin settings', 'mediapress' ),
			'default' => $defaults['activity_upload'],
			'type'    => 'radio',
			'options' => array(
				1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			),
		) )->add_field( array(
			'name'    => 'activity_disable_auto_file_browser',
			'label'   => _x( 'Disable automatic file chooser opening?', 'Admin settings', 'mediapress' ),
			'desc'    => _x( 'Disables the automatic opening of file chooser on media icon click on activity post form.', 'Admin settings', 'mediapress' ),
			'default' => $defaults['activity_disable_auto_file_browser'],
			'type'    => 'radio',
			'options' => array(
				1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			),
		) );

		$activity_options = array(
			'create_gallery' => _x( 'New Gallery is created.', 'Admin settings', 'mediapress' ),
			'add_media'      => _x( 'New Media added/uploaded.', 'Admin settings', 'mediapress' ),
		);

		$default_activities = $defaults['autopublish_activities'];

		if ( ! empty( $default_activities ) ) {
			$default_activities = array_combine( $default_activities, $default_activities );
		}

		$activity_section->add_field( array(
			'name'    => 'autopublish_activities',
			'label'   => _x( 'Automatically Publish to activity When?', 'Admin settings', 'mediapress' ),
			'type'    => 'multicheck',
			'options' => $activity_options,
			'default' => $default_activities,
		) );

		$this->add_activity_views_panel( $panel );

		$panel->add_section( 'misc-settings', _x( 'Miscellaneous Settings', 'Admin settings section title', 'mediapress' ) )
		      ->add_field( array(
			      'name'    => 'show_orphaned_media',
			      'label'   => _x( 'Show orphaned media to the user?', 'Admin settings option', 'mediapress' ),
			      'desc'    => _x( 'Do you want to list the media if it was uploaded from activity but the activity was not published?', 'Admin settings', 'mediapress' ),
			      'type'    => 'radio',
			      'default' => $defaults['show_orphaned_media'],
			      'options' => array(
				      1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
		      ) )
		      ->add_field( array(
			      'name'    => 'delete_orphaned_media',
			      'label'   => _x( 'Delete orphaned media automatically?', 'Admin settings', 'mediapress' ),
			      'desc'    => _x( 'Do you want to delete the abandoned media uploaded from activity?', 'Admin settings', 'mediapress' ),
			      'type'    => 'radio',
			      'default' => $defaults['delete_orphaned_media'],
			      'options' => array(
				      1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
		      ) )
		      ->add_field( array(
			      'name'    => 'activity_media_display_limit',
			      'label'   => _x( 'Maximum number of media to display in activity?', 'Admin settings', 'mediapress' ),
			      'desc'    => _x( 'Limit the no. of media that is shown as attached to activity.', 'Admin settings', 'mediapress' ),
			      'type'    => 'text',
			      'default' => $defaults['activity_media_display_limit'],
		      ) );
	}

	/**
	 * Add settings for Members Gallery.
	 *
	 * @param MPP_Admin_Settings_Page $page page object.
	 */
	private function add_members_panel( $page ) {

		if ( ! mediapress()->is_bp_active() || ! mpp_is_active_component( 'members' ) ) {
			return;
		}

		$defaults = mpp_get_default_options();

		$panel = $page->add_panel( 'members', _x( 'Members Gallery', 'Admin settings BuddyPress panel tab title', 'mediapress' ) );
		$this->add_type_settings( $panel, 'members' );

		$section = $panel->get_section( 'members-types' );

		if ( $section ) {

			$section->add_field( array(
				'name'    => 'members_enable_type_filters',
				'label'   => _x( 'Enable Gallery Type Filters on profile?', 'Admin settings group section', 'mediapress' ),
				'type'    => 'radio',
				'default' => $defaults['members_enable_type_filters'],
				'options' => array(
					1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
					0 => _x( 'No', 'Admin settings option', 'mediapress' ),
				),
			) );
		}

		$this->add_gallery_views_panel( $panel, 'members' );
	}

	/**
	 * Add settings panel for BuddyPress Groups.
	 *
	 * @param MPP_Admin_Settings_Page $page page object.
	 */
	private function add_groups_panel( $page ) {

		if ( ! mediapress()->is_bp_active() || ! mpp_is_active_component( 'groups' ) ) {
			return;
		}

		$defaults = mpp_get_default_options();

		$panel = $page->add_panel( 'groups', _x( 'Groups Gallery', 'Admin settings BuddyPress panel tab title', 'mediapress' ) );

		$this->add_type_settings( $panel, 'groups' );
		$this->add_gallery_views_panel( $panel, 'groups' );

		$panel->add_section( 'group-settings', _x( 'Group Settings', 'Admin settings section title', 'mediapress' ) )
		      ->add_field( array(
			      'name'    => 'enable_group_galleries_default',
			      'label'   => _x( 'Enable group galleries by default?', 'Admin settings group section', 'mediapress' ),
			      'desc'    => _x( 'If you set yes, Group galleries will be On by default for all the groups. A group admin can turn off by visiting settings though.', 'Admin settings group section', 'mediapress' ),
			      'type'    => 'radio',
			      'default' => $defaults['enable_group_galleries_default'],
			      'options' => array(
				      'yes' => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      'no'  => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
		      ) )
		      ->add_field( array(
			      'name'    => 'contributors_can_edit',
			      'label'   => _x( 'Contributors can edit their own media?', 'Admin settings group section', 'mediapress' ),
			      'type'    => 'radio',
			      'default' => $defaults['contributors_can_edit'],
			      'options' => array(
				      1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
		      ) )
		      ->add_field( array(
			      'name'    => 'contributors_can_delete',
			      'label'   => _x( 'Contributors can delete their own media?', 'Admin settings group section', 'mediapress' ),
			      'type'    => 'radio',
			      'default' => $defaults['contributors_can_delete'],
			      'options' => array(
				      1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
		      ) )
		      ->add_field( array(
			      'name'    => 'groups_enable_my_galleries',
			      'label'   => _x( 'Show My Galleries to Group members?', 'Admin settings group section', 'mediapress' ),
			      'desc'    => _x( 'It adds a tab named My Gallery on group pages where the logged in user can see the galleries they created in this group.', 'Admin settings group section', 'mediapress' ),
			      'type'    => 'radio',
			      'default' => $defaults['groups_enable_my_galleries'],
			      'options' => array(
				      1 => _x( 'Yes', 'Admin settings option', 'mediapress' ),
				      0 => _x( 'No', 'Admin settings option', 'mediapress' ),
			      ),
		      ) );
	}


	/**
	 * Add Themes setting panel.
	 *
	 * @param MPP_Admin_Settings_Panel $panel panel object.
	 * @param string                   $component component name.
	 */
	private function add_gallery_views_panel( $panel, $component ) {

		$active_types = $this->active_types;

		$section = $panel->add_section( $component . '-gallery-views', sprintf( _x( ' %s Gallery Default Views', 'Gallery view section title', 'mediapress' ), ucwords( $component ) ) );

		$supported_types = mpp_component_get_supported_types( $component );

		foreach ( $active_types as $key => $type_object ) {
			// if the component does not support type, do not add the settings.
			if ( ! empty( $supported_types ) && ! mpp_component_supports_type( $component, $key ) ) {
				continue;
				// if none of the types are enabled, it means, it is the first time and we need not break here.
			}

			$registered_views = mpp_get_registered_gallery_views( $key );
			$options          = array();

			foreach ( $registered_views as $view ) {

				if ( ! $view->supports_component( $component ) || ! $view->supports( 'gallery' ) ) {
					continue;
				}

				$options[ $view->get_id() ] = $view->get_name();
			}

			$section->add_field( array(
				'name'    => $component . '_' . $key . '_gallery_default_view',
				'label'   => sprintf( _x( '%s Gallery', 'admin gallery  settings', 'mediapress' ), mpp_get_type_singular_name( $key ) ),
				'desc'    => _x( 'It will be used as the default view. It can be overridden per gallery', 'admin gallery settings', 'mediapress' ),
				'default' => 'default',
				'type'    => 'radio',
				'options' => $options,
			) );
		}
	}

	/**
	 * Add activity view settings.
	 *
	 * @param MPP_Admin_Settings_Panel $panel panel object.
	 */
	private function add_activity_views_panel( $panel ) {

		$active_types = $this->active_types;

		$section = $panel->add_section( 'activity-gallery-views', _x( 'Activity Media List View', 'Activity view section title', 'mediapress' ) );

		foreach ( $active_types as $key => $type_object ) {

			$registered_views = mpp_get_registered_gallery_views( $key );
			$options          = array();

			foreach ( $registered_views as $view ) {

				if ( ! $view->supports( 'activity' ) ) {
					continue;
				}

				$options[ $view->get_id() ] = $view->get_name();
			}

			$section->add_field( array(
				'name'    => 'activity_' . $key . '_default_view',
				'label'   => sprintf( _x( '%s List', 'admin gallery settings', 'mediapress' ), mpp_get_type_singular_name( $key ) ),
				'desc'    => _x( 'It will be used to display attached activity media.', 'admin gallery settings', 'mediapress' ),
				'default' => 'default',
				'type'    => 'radio',
				'options' => $options,
			) );
		}
	}

	/**
	 * Add Menu
	 */
	public function add_menu() {

		add_submenu_page( mpp_admin()->get_menu_slug(), _x( 'Settings', 'Admin settings page title', 'mediapress' ), _x( 'Settings', 'Admin settings menu label', 'mediapress' ), 'manage_options', 'mpp-settings', array(
			$this,
			'render',
		) );

	}

	/**
	 * Show/render the setting page
	 */
	public function render() {
		$this->page->render();
	}
}

// instantiate.
MPP_Admin_Settings_Helper::get_instance();
