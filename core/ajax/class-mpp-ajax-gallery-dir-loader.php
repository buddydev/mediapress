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

defined( 'ABSPATH' ) || exit( 0 );

/**
 * Gallery directory loader
 */
class MPP_Ajax_Gallery_Dir_Loader {

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

		// directory loop.
		add_action( 'wp_ajax_mpp_filter', array( $this, 'load_dir_list' ) );
		add_action( 'wp_ajax_nopriv_mpp_filter', array( $this, 'load_dir_list' ) );
	}

	/**
	 * Loads directory gallery list via ajax
	 */
	public function load_dir_list() {

		$type = isset( $_POST['filter'] ) ? $_POST['filter'] : '';
		$page = absint( $_POST['page'] );

		$scope        = $_POST['scope'];
		$search_terms = $_POST['search_terms'];

		// make the query and setup.
		mediapress()->is_directory = true;

		$status = array();
		if ( mpp_is_active_status( 'public' ) ) {
			$status[] = 'public';
		}

		if ( is_user_logged_in() && mpp_is_active_status( 'loggedin' ) ) {
			$status[] = 'loggedin';
		}

		// get all public galleries, should we do type filtering.
		mediapress()->the_gallery_query = new MPP_Gallery_Query( array(
			'status'       => $status,
			'type'         => $type,
			'page'         => $page,
			'search_terms' => $search_terms,
		) );

		mpp_get_template( 'gallery/loop-gallery.php' );

		exit( 0 );
	}
}
