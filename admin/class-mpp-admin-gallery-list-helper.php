<?php
/**
 * Admin Galley List helper.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class to update the Galleries List screen
 * Dashboard -> MediaPress -> Galleries screen
 */
class MPP_Admin_Gallery_List_Helper {

	/**
	 * Gallery Post type.
	 *
	 * @var string
	 */
	private $post_type = '';

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->post_type = mpp_get_gallery_post_type();
		// setup hooks.
		$this->setup();
	}

	/**
	 * Setup hooks.
	 */
	private function setup() {

		add_filter( "manage_edit-{$this->post_type}_columns", array( $this, 'add_cols' ) );

		add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'display_cols' ), 10, 2 );

		// add sortable cols.
		add_filter( "manage_edit-{$this->post_type}_sortable_columns", array( $this, 'sortable_cols' ) );
		// update query for it.
		add_action( 'pre_get_posts', array( $this, 'sort_list' ) );

		// filter out the quickedit.
		add_filter( 'post_row_actions', array( $this, 'filter_actions' ), 10, 2 );

		add_action( 'admin_head', array( $this, 'add_inline_css' ) );
	}

	/**
	 * Add custom columns on the WordPress Galleries List screen
	 *
	 * @param array $allcolumns array of columns.
	 *
	 * @return array
	 */
	public function add_cols( $allcolumns ) {

		unset( $allcolumns['date'] );

		$cb = isset( $allcolumns['cb'] ) ? $allcolumns['cb'] : null;

		unset( $allcolumns['cb'] );

		if ( $cb ) {
			$columns = array( 'cb' => $cb );
		} else {
			$columns = array();
		}

		$columns['cover'] = '';
		$columns          = array_merge( $columns, $allcolumns );

		$columns['type']      = _x( 'Type', 'Label for gallery list title', 'mediapress' );
		$columns['status']    = _x( 'Status', 'Label for gallery list title', 'mediapress' );
		$columns['component'] = _x( 'Component', 'Label for gallery list title', 'mediapress' );

		$columns['user_id'] = _x( 'Created By:', 'Label for gallery list title', 'mediapress' );

		$columns['media_count'] = _x( 'Media', 'Label for gallery list title', 'mediapress' );
		$columns['date']        = _x( 'Date', 'Label for gallery list title', 'mediapress' );

		$this->cache_cover();

		return $columns;
	}

	/**
	 * Display the column data
	 *
	 * @param string $col current column.
	 * @param int    $post_id numeric post id.
	 *
	 * @return string
	 */
	public function display_cols( $col, $post_id ) {

		$allowed = array( 'type', 'status', 'component', 'user_id', 'media_count', 'cover' );

		if ( ! in_array( $col, $allowed ) ) {
			return $col;
		}

		$gallery = mpp_get_gallery( get_post( $post_id ) );

		switch ( $col ) {

			case 'cover':
				echo "<img src='" . mpp_get_gallery_cover_src( 'thumbnail', $post_id ) . "' height='100px' width='100px'/>";
				break;

			case 'type':
				echo mpp_get_type_singular_name( $gallery->type );
				break;

			case 'status':
				echo $gallery->status;
				break;

			case 'component':
				echo $gallery->component;
				break;

			case 'media_count':
				echo $gallery->media_count;
				break;

			case 'user_id':
				echo mpp_get_user_link( $gallery->user_id );
				break;
		}

	}

	/**
	 * Sortable column.
	 *
	 * @param array $cols columns.
	 *
	 * @return array updated sortable columns.
	 */
	public function sortable_cols( $cols ) {

		$cols['type']      = 'type';
		$cols['status']    = 'status';
		$cols['component'] = 'component';

		$cols['user_id']     = 'user_id';
		$cols['media_count'] = 'media_count';

		return $cols;

	}

	/**
	 * Sort Gallery list
	 *
	 * @param WP_Query $query Main WordPress query object.
	 */
	public function sort_list( WP_Query $query ) {

		if ( ! mpp_admin_is_gallery_list() ) {
			return;
		}

		// check if the post type.
		if ( ! $query->is_main_query() || $query->get( 'post_type' ) != $this->post_type ) {
			return;
		}

		// if we are here, we may need to sort.
		$orderby = isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : '';

		$sort_order = isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : '';

		if ( ! $orderby || ! $sort_order ) {
			return;
		}

		if ( 'user_id' === $orderby ) {
			$query->set( 'orderby', 'author' );
		} elseif ( 'media_count' === $orderby ) {
			$query->set( 'meta_key', '_mpp_media_count' );
			$query->set( 'orderby', 'meta_value_num' );
		}

		$query->set( 'order', $sort_order );
	}

	/**
	 * Filter actions to disallow editing using quick edit.
	 *
	 * @param array   $actions action links array.
	 * @param WP_Post $post post object.
	 *
	 * @return mixed
	 */
	public function filter_actions( $actions, $post ) {

		if ( $post->post_type != $this->post_type ) {
			return $actions;
		}

		unset( $actions['inline hide-if-no-js'] );

		return $actions;
	}

	/**
	 * Cache the gallery cover for the gallery list.
	 */
	private function cache_cover() {
		global $wp_query;
		_mpp_cache_gallery_cover( $wp_query );
	}

	/**
	 * Add some inline css.
	 */
	public function add_inline_css() {
		// hide the Add New action link in the gallery list.
		if ( ! mpp_admin_is_gallery_list() ) {
			return;
		}
		?>
        <style type="text/css">
            body.post-type-mpp-gallery .page-title-action {
                display: none;
            }
        </style>
		<?php
	}

}
// Initialize.
new MPP_Admin_Gallery_List_Helper();
