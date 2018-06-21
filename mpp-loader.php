<?php

/**
 * Loads MediaPress core files.
 *
 * @package MediaPress
 */

/**
 * Core loader
 */
class MPP_Core_Loader {

	/**
	 * Path to the plugin directory
	 *
	 * @var string
	 */
	private $path = '';


	/**
	 * Constructor, sets up path.
	 */
	public function __construct() {
		$this->path = mediapress()->get_path();
	}


	/**
	 * Load all kind of dependencies
	 */
	public function load() {

		$this->load_core();
		$this->load_widgets();
		$this->load_shortcodes();

		$this->load_ajax_handlers();
	}


	/**
	 * Load core dependencies.
	 */
	public function load_core() {

		$files = array(
			'core/common/mpp-feedback-functions.php',
			'core/common/mpp-misc-functions.php',
			'core/common/mpp-common-functions.php',
			'core/common/class-mpp-hooks-helper.php',
			'core/common/class-mpp-cached-media-query.php',
			'core/common/class-mpp-gallery-query.php',
			'core/common/class-mpp-media-query.php',
			'core/common/mpp-nav-functions.php',
			'core/mpp-post-type.php',
			'core/class-mpp-deletion-actions-mapper.php',
			'core/common/class-mpp-taxonomy.php',
			'core/common/class-mpp-menu.php',
			'core/common/class-mpp-features.php',
			'core/common/mpp-taxonomy-functions.php',
			// Gallery related.
			'core/gallery/class-mpp-gallery.php',
			'core/gallery/mpp-gallery-conditionals.php',
			'core/gallery/mpp-gallery-cover-templates.php',
			'core/gallery/mpp-gallery-functions.php',
			'core/gallery/mpp-gallery-link-template.php',
			'core/gallery/mpp-gallery-meta.php',
			'core/gallery/mpp-gallery-screen.php',
			'core/gallery/mpp-gallery-template-tags.php',
			'core/gallery/mpp-gallery-hooks.php',
			'core/gallery/mpp-gallery-actions.php',
			'core/gallery/mpp-gallery-activity.php',
			'core/gallery/mpp-gallery-template.php',
			// Media related.
			'core/media/class-mpp-media-importer.php',
			'core/media/class-remote-media-parser.php',
			'core/media/mpp-media-functions.php',
			'core/media/mpp-remote-media-functions.php',
			'core/media/mpp-media-meta.php',
			'core/media/class-mpp-media.php',
			'core/media/mpp-media-template-tags.php',
			'core/media/mpp-media-link-templates.php',
			'core/media/mpp-media-actions.php',
			'core/media/mpp-media-cover-template.php',
			'core/media/mpp-media-activity.php',
			'core/media/mpp-media-hooks.php',
			// Views related, gallery views.
			'core/views/class-mpp-gallery-view.php',
			'core/views/class-mpp-gallery-view-default.php',
			'core/views/class-mpp-gallery-view-audio-playlist.php',
			'core/views/class-mpp-gallery-view-video-playlist.php',
			'core/views/class-mpp-gallery-view-list.php',
			'core/views/mpp-gallery-view-functions.php',
			//
			// Views: media viewer.
			'core/media/views/class-mpp-media-view.php',
			'core/media/views/class-mpp-media-view-photo.php', // for image files
			'core/media/views/class-mpp-media-view-doc.php', // for doc files
			'core/media/views/class-mpp-media-view-video.php', // for video files
			'core/media/views/class-mpp-media-view-audio.php', // for audio files
			// API.
			'core/api/mpp-actions-api.php',
			'core/api/mpp-api.php',
			'core/mpp-hooks.php',
			// User related.
			'core/users/mpp-user-meta.php',
			'core/users/mpp-user-functions.php',
			'core/users/mpp-user-hooks.php',
			//
			// Asset loading.
			'assets/mpp-assets-loader.php',
			// Template/Permissions.
			'core/mpp-template-helpers.php',
			'core/mpp-permissions.php',
			// Storage related.
			'core/storage/mpp-storage-functions.php',
			'core/storage/mpp-storage-space-stats-functions.php',
			'core/storage/class-mpp-storage-manager.php',
			'core/storage/class-mpp-local-storage.php',
			// Theme compat.
			'core/mpp-theme-compat.php',
			'mpp-init.php',
			'mpp-core-component.php',

			// cron job.
			'core/common/mpp-cron.php',
		);

		if ( is_admin() ) {
			$files[] = 'admin/mpp-admin-loader.php';
		}

		if ( mediapress()->is_bp_active() ) {
			$files[] = 'modules/buddypress/mpp-bp-loader.php';
		}

		$path = mediapress()->get_path();

		foreach ( $files as $file ) {
			require_once $path . $file;
		}

	}


	/**
	 * Load ajax handlers
	 */
	public function load_ajax_handlers() {

		if ( ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		$files = array(
			'core/ajax/mpp-ajax.php',
			'core/ajax/class-mpp-ajax-remote-media-handler.php',
			'core/ajax/class-mpp-ajax-activity-post-handler.php',
			'core/ajax/class-mpp-ajax-gallery-action-handler.php',
			'core/ajax/class-mpp-ajax-gallery-dir-loader.php',
			'core/ajax/class-mpp-ajax-comment-helper.php',
			'core/ajax/class-mpp-ajax-lightbox-helper.php',
		);

		$path = mediapress()->get_path();

		foreach ( $files as $file ) {
			require_once $path . $file;
		}

		// initialize.
		MPP_Ajax_Helper::get_instance();
		MPP_Ajax_Remote_Media_Handler::boot();
		MPP_Ajax_Activity_Post_Handler::boot();
		// commenting.
		MPP_Ajax_Comment_Helper::get_instance();

		// Lightbox handler.
		new MPP_Ajax_Lightbox_Helper();

		MPP_Ajax_Gallery_Dir_Loader::boot();
		MPP_Ajax_Gallery_Action_Handler::boot();
	}


	/**
	 * Load comments handlers
	 */
	public function load_comment_handlers() {
		// comment.
		require_once $this->path . 'core/comments/mpp-comment-functions.php';
		require_once $this->path . 'core/comments/class-mpp-comment.php';
		require_once $this->path . 'core/comments/class-mpp-comments-helper.php';
		require_once $this->path . 'core/comments/mpp-comment-template-tags.php';
	}


	/**
	 * Load shortcode related files.
	 */
	private function load_shortcodes() {

		require_once $this->path . 'core/shortcodes/mpp-shortcode-functions.php';
		require_once $this->path . 'core/shortcodes/mpp-shortcode-gallery-list.php';
		require_once $this->path . 'core/shortcodes/mpp-shortcode-media-list.php';
		require_once $this->path . 'core/shortcodes/mpp-shortcode-create-gallery.php';
		require_once $this->path . 'core/shortcodes/mpp-shortcode-media-uploader.php';

	}


	/**
	 * Load Widgets
	 */
	private function load_widgets() {

		require_once $this->path . 'core/widgets/mpp-widget-functions.php';
		require_once $this->path . 'core/widgets/mpp-widget-gallery.php';
		require_once $this->path . 'core/widgets/mpp-widget-media.php';

	}

}
