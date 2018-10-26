<?php
/**
 * MediaPress Media Query
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MediaPress Media Query class
 */
class MPP_Media_Query extends WP_Query {

	/**
	 * Media post type 'attachment'
	 *
	 * @var string
	 */
	private $post_type;

	/**
	 * MPP_Media_Query constructor.
	 *
	 * @param array $query array of query vars.
	 */
	public function __construct( $query = array() ) {

		$this->post_type = mpp_get_media_post_type();

		parent::__construct( $query );

	}

	/**
	 * Query media.
	 *
	 * @param array $args array of query vars.
	 *
	 * @return array of posts list.
	 */
	public function query( $args ) {

		// make sure that the query params was not built before.
		if ( ! isset( $args['_mpp_mapped_query'] ) ) {
			$args = self::build_params( $args );
		}

		$helper = new MPP_Hooks_Helper();

		// detach 3rd party hooks. These are the hooks that caused most pain in our query.
		$helper->detach( 'pre_get_posts', 'posts_join', 'posts_where', 'posts_groupby' );

		// now, if there is any MediaPress specific plugin interested in attaching to the above hooks,
		// the should do it on the action 'mpp_before_media_query'.
		do_action( 'mpp_before_media_query', $this, $args );

		$posts = parent::query( $args );

		// if you need to do some processing after the query has finished.
		do_action( 'mpp_after_media_query', $this, $args );

		// restore hooks to let it work as expected for others..
		$helper->restore( 'pre_get_posts', 'posts_join', 'posts_where', 'posts_groupby' );

		return $posts;
	}

	/**
	 * Map media query parameters to wp_query parameters
	 *
	 * @param array $args array of query vars.
	 *
	 * @return array
	 */
	public function build_params( $args ) {

		$defaults = array(
			// media type, all,audio,video,photo etc.
			'type'              => array_keys( mpp_get_active_types() ),
			// pass specific media id.
			'id'                => false,
			// pass specific media ids as array.
			'in'                => false,
			// pass media ids to exclude.
			'exclude'           => false,
			// pass media slug to include.
			'slug'              => false,
			// public,private,friends one or more privacy level.
			'status'            => array_keys( mpp_get_active_statuses() ),
			// one or more component name user,groups, evenets etc.
			'component'         => array_keys( mpp_get_active_components() ),
			// the associated component id, could be group id, user id, event id.
			'component_id'      => false,
			// gallery specific.
			'gallery_id'        => false,
			'galleries'         => false,
			'galleries_exclude' => false,

			// storage related.
			// pass any valid registered Storage manager identifier such as local|oembed|aws etc to filter by storage.
			'storage'      => '',

			// how many items per page.
			'per_page'     => mpp_get_option( 'media_per_page' ),
			// how many galleries to offset/displace.
			'offset'       => false,
			// which page when paged.
			'page'         => isset( $_REQUEST['mpage'] ) ? absint( $_REQUEST['mpage'] ) : false,
			// to avoid paging.
			'nopaging'     => false,
			// order.
			'order'        => 'DESC',
			// none, id, user, title, slug, date,modified, random, comment_count, meta_value,meta_value_num, ids.
			'orderby'      => false,

			// user params.
			'user_id'      => false,
			'user_name'    => false,
			'include_users' => false,
			'exclude_users' => false,
			'scope'        => false,
			'search_terms' => '',

			// time parameter.
			// this years.
			'year'         => false,
			// 1-12 month number.
			'month'        => false,
			// 1-53 week.
			'week'         => '',
			// specific day.
			'day'          => '',
			// specific hour.
			'hour'         => '',
			// specific minute.
			'minute'       => '',
			// specific second 0-60.
			'second'       => '',
			// yearMonth, 201307 // july 2013.
			'yearmonth'    => '',

			// 'meta_key'          => false,
			// 'meta_value'        => false,
			'meta_query'   => false,
			// which fields to return ids, id=>parent, all fields(default).
			'fields'       => false,
		);


		/**
		 * Build params for WP_Query.
		 */

		/**
		 * If we are querying for a single gallery
		 * and the gallery media were sorted by the user, show the media in the sort order instead of the default date
		 */
		if ( isset( $args['gallery_id'] ) && mpp_is_gallery_sorted( $args['gallery_id'] ) ) {
			$defaults['orderby'] = 'menu_order';
		}

		$r = wp_parse_args( $args, $defaults );

		// build the wp_query args.
		$wp_query_args = array(
			'post_type'           => mpp_get_media_post_type(),
			'post_status'         => 'any',
			'p'                   => $r['id'],
			'post__in'            => $r['in'] ? wp_parse_id_list( $r['in'] ) : false,
			'post__not_in'        => $r['exclude'] ? wp_parse_id_list( $r['exclude'] ) : false,
			'name'                => $r['slug'],

			// gallery specific.
			'post_parent'         => $r['gallery_id'],
			'post_parent__in'     => ! empty( $r['galleries'] ) ? wp_parse_id_list( $r['galleries'] ) : 0,
			'post_parent__not_in' => ! empty( $r['galleries_exclude'] ) ? wp_parse_id_list( $r['galleries_exclude'] ) : 0,
			'posts_per_page'      => $r['per_page'],
			'paged'               => $r['page'],
			'offset'              => $r['offset'],
			'nopaging'            => $r['nopaging'],
			// user params.
			'author'              => $r['user_id'],
			'author_name'         => $r['user_name'],
			'author__in'          => $r['include_users'] ? wp_parse_id_list( $r['include_users'] ) : false,
			'author__not_in'      => $r['exclude_users'] ? wp_parse_id_list( $r['exclude_users'] ) : false,
			// date time params.
			'year'                => $r['year'],
			'monthnum'            => $r['month'],
			'w'                   => $r['week'],
			'day'                 => $r['day'],
			'hour'                => $r['hour'],
			'minute'              => $r['minute'],
			'second'              => $r['second'],
			'm'                   => $r['yearmonth'],
			// order by.
			'order'               => $r['order'],
			'orderby'             => $r['orderby'],
			's'                   => $r['search_terms'],
			// meta key, may be we can set them here?
			// 'meta_key'              => $meta_key,
			// 'meta_value'            => $meta_value,
			// which fields to fetch.
			'fields'              => $r['fields'],
			'_mpp_mapped_query'   => true,
			'_mpp_original_args'  => $args,
		);

		// we will need to build tax query/meta query
		// taxonomy query to filter by component|status|privacy.
		$tax_query = isset( $r['tax_query'] ) ? $r['tax_query'] : array();

		// meta query.
		$gmeta_query = array();

		$type         = $r['type'];
		$status       = $r['status'];
		$component    = $r['component'];
		$component_id = $r['component_id'];

		if ( isset( $r['meta_key'] ) && $r['meta_key'] ) {
			$wp_query_args['meta_key'] = $r['meta_key'];
		}

		if ( isset( $r['meta_key'] ) && $r['meta_key'] && isset( $r['meta_value'] ) ) {
			$wp_query_args['meta_value'] = $r['meta_value'];
		}

		// if meta query was specified, let us keep it and we will add our conditions.
		if ( ! empty( $r['meta_query'] ) ) {
			$gmeta_query = $r['meta_query'];
		}


		// we will need to build tax query/meta query
		// type, audio video etc
		// if type is given and it is valid gallery type
		// Pass one or more types.
		if ( $r['gallery_id'] ) {
			// if gallery id is given, avoid worrying about type.
			$type      = '';
			$component = '';
		}

		if ( ! empty( $type ) && mpp_are_registered_types( $type ) ) {
			$type = mpp_string_to_array( $type );

			// we store the terms with _name such as private becomes _private, members become _members to avoid conflicting terms.
			$type = mpp_get_tt_ids( $type, mpp_get_type_taxname() );

			$tax_query[] = array(
				'taxonomy' => mpp_get_type_taxname(),
				'field'    => 'term_taxonomy_id',
				'terms'    => $type,
				'operator' => 'IN',
			);
		}

		// privacy
		// pass one or more privacy level.
		if ( ! empty( $status ) && mpp_are_registered_statuses( $status ) ) {

			$status = mpp_string_to_array( $status );
			$status = mpp_get_tt_ids( $status, mpp_get_status_taxname() );

			$tax_query[] = array(
				'taxonomy' => mpp_get_status_taxname(),
				'field'    => 'term_taxonomy_id',
				'terms'    => $status,
				'operator' => 'IN',
			);
		}

		if ( ! empty( $component ) && mpp_are_registered_components( $component ) ) {

			$component = mpp_string_to_array( $component );
			$component = mpp_get_tt_ids( $component, mpp_get_component_taxname() );

			$tax_query[] = array(
				'taxonomy' => mpp_get_component_taxname(),
				'field'    => 'term_taxonomy_id',
				'terms'    => $component,
				'operator' => 'IN',
			);
		}

		// done with the tax query.
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}

		if ( ! empty( $tax_query ) ) {
			$wp_query_args['tax_query'] = $tax_query;
		}

		// now, for components.
		if ( ! empty( $component_id ) ) {
			$meta_compare = '=';

			if ( is_array( $component_id ) ) {
				$meta_compare = 'IN';
			}

			$gmeta_query[] = array(
				'key'     => '_mpp_component_id',
				'value'   => $component_id,
				'compare' => $meta_compare,
				'type'    => 'UNSIGNED',
			);
		}

		// also make sure that it only looks for gallery media.
		$gmeta_query[] = array(
			'key'     => '_mpp_is_mpp_media',
			'value'   => 1,
			'compare' => '=',
			'type'    => 'UNSIGNED',
		);

		// should we avoid the orphaned media
		// Let us discuss with the community and get it here.
		if ( ! mpp_get_option( 'show_orphaned_media' ) ) {

			$gmeta_query[] = array(
				'key'     => '_mpp_is_orphan',
				'compare' => 'NOT EXISTS',
			);
		}

		// Let us filter the media by storage method.
		if ( ! empty( $storage ) ) {

			$gmeta_query[] = array(
				'key'     => '_mpp_storage_method',
				'value'   => $storage,
				'compare' => '=',
			);
		}

		// and what to do when a user searches by the media source(say youtube|vimeo|xyz.. how do we do that?)
		// reset meta query.
		if ( ! empty( $gmeta_query ) ) {
			$wp_query_args['meta_query'] = $gmeta_query;
		}

		return $wp_query_args;

		// http://wordpress.stackexchange.com/questions/53783/cant-sort-get-posts-by-post-mime-type .
	}

	/**
	 * Get all media in current query.
	 *
	 * @return array of media posts.
	 */
	public function get_media() {
		return parent::get_posts();
	}

	/**
	 * Move to next media.
	 *
	 * @return WP_Post
	 */
	public function next_media() {
		return parent::next_post();
	}

	/**
	 * Move back to previous media in the query.
	 *
	 * @return WP_Post
	 */
	public function reset_next() {

		$this->current_post --;

		$this->post = $this->posts[ $this->current_post ];

		return $this->post;
	}

	/**
	 * Move to next media.
	 */
	public function the_media() {

		global $post;
		$this->in_the_loop = true;

		if ( $this->current_post == - 1 ) {
			// loop has just started.
			do_action_ref_array( 'mpp_media_loop_start', array( &$this ) );
		}

		$post = $this->next_media();

		setup_postdata( $post );

		mediapress()->current_media = mpp_get_media( $post );
	}

	/**
	 * Equivalent of have_posts()
	 *
	 * @return bool
	 */
	public function have_media() {
		return parent::have_posts();
	}

	/**
	 * Rewind media.
	 */
	public function rewind_media() {
		parent::rewind_posts();
	}

	/**
	 * Check if it is main media query.
	 *
	 * @return bool
	 */
	public function is_main_query() {

		$mediapress = mediapress();

		return $this == $mediapress->the_media_query;
	}

	/**
	 * Reset the query loop.
	 */
	public function reset_media_data() {

		parent::reset_postdata();

		if ( ! empty( $this->post ) ) {
			mediapress()->current_media = mpp_get_media( $this->post );
		}
	}


	/**
	 * Show/get pagination links
	 *
	 * @param bool $default use default schema.
	 *
	 * @return string pagination links.
	 */
	public function paginate( $default = true ) {

		$total        = $this->max_num_pages;
		$current_page = $this->get( 'paged' );
		// only bother with the rest if we have more than 1 page!
		if ( $total > 1 ) {
			// get the current page.
			$perma_struct = get_option( 'permalink_structure' );
			$format       = empty( $perma_struct ) ? '&page=%#%' : 'page/%#%/';

			$link = get_pagenum_link( 1 );

			if ( ! $current_page ) {
				$current_page = 1;
			}
			// structure of “format” depends on whether we’re using pretty permalinks.
			if ( ! $default ) {
				// if not using default scheme, override the things.
				$current_page = isset( $_REQUEST['mpage'] ) && $_REQUEST['mpage'] > 0 ? intval( $_REQUEST['mpage'] ) : 1;
				$link         = add_query_arg( null, null );
				// it will return the current url, alpha is meaningless here.
				$chunks       = explode( '?', $link );
				$link         = $chunks[0];
				$format       = '?mpage=%#%';
			}

			$base = trailingslashit( $link );

			return paginate_links( array(
				'base'     => $base . '%_%',
				'format'   => $format,
				'current'  => $current_page,
				'total'    => $total,
				'mid_size' => 4,
				'type'     => 'list',
			) );
		}
	}

	/**
	 * Show pagination count.
	 */
	public function pagination_count() {

		$paged          = $this->get( 'paged' ) ? $this->get( 'paged' ) : 1;
		$posts_pet_page = $this->get( 'posts_per_page' );

		$from_num = intval( ( $paged - 1 ) * $posts_pet_page ) + 1;

		$to_num = ( $from_num + ( $posts_pet_page - 1 ) > $this->found_posts ) ? $this->found_posts : $from_num + ( $posts_pet_page - 1 );

		printf( __( 'Viewing  %d to %d (of %d %s)', 'mediapress' ), $from_num, $to_num, $this->found_posts, mpp_get_media_type() );
	}

	/**
	 * Utility method to get all the ids in this request
	 *
	 * @return array of mdia ids
	 */
	public function get_ids() {

		$ids = array();

		if ( empty( $this->request ) ) {
			return $ids;
		}

		global $wpdb;
		$ids = $wpdb->get_col( $this->request );

		return $ids;
	}
}

/**
 * Reset global media data
 */
function mpp_reset_media_data() {

	if ( mediapress()->the_media_query ) {
		mediapress()->the_media_query->reset_media_data();
	}

	wp_reset_postdata();

}
