<?php
/**
 * MediaPress Gallery query
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MediaPress Gallery Query
 *
 * It extends WP_Query and provides a simple API for Querying galleries, the wp way.
 */
class MPP_Gallery_Query extends WP_Query {

	/**
	 * Gallery post type
	 *
	 * @var string
	 */
	private $post_type;

	/**
	 * Constructor.
	 *
	 * @param string|array $query of queery variables.
	 */
	public function __construct( $query = array() ) {

		$this->post_type = mpp_get_gallery_post_type();

		parent::__construct( $query );
	}

	/**
	 * The Main Query
	 *
	 * @param array $args array of query vars.
	 *
	 * @return array List of posts/galleries.
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
		// the should do it on the action 'mpp_before_gallery_query'.
		do_action( 'mpp_before_gallery_query', $this, $args );

		$posts = parent::query( $args );
		// if you need to do some processing after the query has finished.
		do_action( 'mpp_after_gallery_query', $this, $args );

		// restore hooks to let it work as expected for others..
		$helper->restore( 'pre_get_posts', 'posts_join', 'posts_where', 'posts_groupby' );

		return $posts;
	}

	/**
	 * Map gallery parameters to wp_query native parameters
	 *
	 * @param array $args array of query vars.
	 *
	 * @return array
	 */
	public function build_params( $args ) {

		$defaults = array(
			'type'          => array_keys( mpp_get_active_types() ),
			// gallery type, all,audio,video,photo etc.
			'id'            => false,
			// pass specific gallery id.
			'in'            => false,
			// pass specific gallery ids as array.
			'exclude'       => false,
			// pass gallery ids to exclude.
			'slug'          => false,
			// pass gallery slug to include.
			'status'        => array_keys( mpp_get_active_statuses() ),
			// public,private,friends one or more privacy level.
			'component'     => array_keys( mpp_get_active_components() ),
			// one or more component name user,groups, evenets etc.
			'component_id'  => false,
			// the associated component id, could be group id, user id, event id.
			'per_page'      => mpp_get_option( 'galleries_per_page' ),
			'offset'        => false,
			// How many galleries to offset/displace.
			'page'          => isset( $_REQUEST['gpage'] ) ? absint( $_REQUEST['gpage'] ) : false,
			// which page when paged.
			'nopaging'      => false,
			// to avoid paging.
			'order'         => 'DESC',
			// order.
			'orderby'       => 'date',
			// none, id, user, title, slug, date,modified, random, comment_count, meta_value,meta_value_num, ids
			// user params.
			'user_id'       => false,
			'include_users' => false,
			'exclude_users' => false,
			// users to exclude.
			'user_name'     => false,
			'scope'         => false,
			'search_terms'  => '',
			// time parameter.
			'year'          => false,
			// this years.
			'month'         => false,
			// 1-12 month number
			'week'          => '',
			// 1-53 week.
			'day'           => '',
			// specific day.
			'hour'          => '',
			// specific hour.
			'minute'        => '',
			// specific minute.
			'second'        => '',
			// specific second 0-60.
			'yearmonth'     => false,
			// yearMonth, 201307 // july 2013.
			// 'meta_key'=>',
			// 'meta_value'=>'',
			// 'meta_query'=>false.
			'fields'        => false,
			// which fields to return ids, id=>parent, all fields(default).
		);


		// build params for WP_Query.
		$r = wp_parse_args( $args, $defaults );
		// build the wp_query args.
		$wp_query_args = array(
			'post_type'          => mpp_get_gallery_post_type(),
			'post_status'        => 'any',
			'p'                  => $r['id'],
			'post__in'           => $r['in'] ? wp_parse_id_list( $r['in'] ) : false,
			'post__not_in'       => $r['exclude'] ? wp_parse_id_list( $r['exclude'] ) : false,
			'name'               => $r['slug'],
			'posts_per_page'     => $r['per_page'],
			'paged'              => $r['page'],
			'offset'             => $r['offset'],
			'nopaging'           => $r['nopaging'],
			// user params.
			'author'             => $r['user_id'],
			'author_name'        => $r['user_name'],
			'author__in'         => $r['include_users'] ? wp_parse_id_list( $r['include_users'] ) : false,
			'author__not_in'     => $r['exclude_users'] ? wp_parse_id_list( $r['exclude_users'] ) : false,
			// date time params.
			'year'               => $r['year'],
			'monthnum'           => $r['month'],
			'w'                  => $r['week'],
			'day'                => $r['day'],
			'hour'               => $r['hour'],
			'minute'             => $r['minute'],
			'second'             => $r['second'],
			'm'                  => $r['yearmonth'],
			// order by.
			'order'              => $r['order'],
			'orderby'            => $r['orderby'],
			's'                  => $r['search_terms'],
			// meta key, may be we can set them here?
			// 'meta_key'=>$r['meta_key'],
			// 'meta_value'=>$r['meta_value'],
			// which fields to fetch.
			'fields'             => $r['fields'],
			'_mpp_mapped_query'  => true,
			'_mpp_original_args' => $args,
		);

		$tax_query   = isset( $r['tax_query'] ) ? $r['tax_query'] : array();
		$gmeta_query = array(); // meta.


		if ( isset( $r['meta_key'] ) && $r['meta_key'] ) {
			$wp_query_args['meta_key'] = $r['meta_key'];
		}

		if ( isset( $r['meta_value'] ) ) {
			$wp_query_args['meta_value'] = $r['meta_value'];
		}

		if ( isset( $r['meta_query'] ) ) {
			$gmeta_query = $r['meta_query'];
		}

		$status       = $r['status'];
		$component    = $r['component'];
		$component_id = $r['component_id'];
		$type         = $r['type'];


		// we will need to build tax query/meta query
		// type, audio video etc
		// if type is given and it is valid gallery type
		// Pass one or more types
		// should we restrict to active types only here? I guess no, Instead the calling scope should take care of that.
		if ( ! empty( $type ) && mpp_are_registered_types( $type ) ) {

			$type = mpp_string_to_array( $type );
			$type = mpp_get_tt_ids( $type, mpp_get_type_taxname() );

			$tax_query[] = array(
				'taxonomy' => mpp_get_type_taxname(),
				'field'    => 'term_taxonomy_id',
				'terms'    => $type,
				'operator' => 'IN',
			);
		}

		// privacy
		// pass ne or more privacy level.
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
			// we enforce the AND.
			$tax_query['relation'] = 'AND';
		}

		$wp_query_args['tax_query'] = $tax_query;
		// meta query
		// now, for component.
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

		// reset meta query.
		if ( ! empty( $gmeta_query ) ) {
			$wp_query_args['meta_query'] = $gmeta_query;
		}

		return $wp_query_args;
	}

	/**
	 * Just a wrapper for get_posts()
	 *
	 * @return array
	 */
	public function get_galleries() {
		return parent::get_posts();
	}

	/**
	 * Just a wrapper for the next_post()
	 *
	 * @return WP_Post
	 */
	public function next_gallery() {
		return parent::next_post();
	}

	/**
	 * Wrapper for the_post() allows us to loop through galleries.
	 */
	public function the_gallery() {

		global $post;

		$this->in_the_loop = true;

		if ( $this->current_post === - 1 ) {
			// loop has just started.
			do_action_ref_array( 'mpp_gallery_loop_start', array( &$this ) );
		}

		$post = $this->next_gallery();

		setup_postdata( $post );
		// setup current gallery.
		mediapress()->current_gallery = mpp_get_gallery( $post );

		mpp_setup_gallery_data( $post );
	}

	/**
	 * Wrapper for have_posts()
	 *
	 * Helps us loop through galleries.
	 *
	 * @return bool
	 */
	public function have_galleries() {
		return parent::have_posts();
	}

	/**
	 * Rewind galleries. resets the loop.
	 */
	public function rewind_galleries() {
		parent::rewind_posts();
	}

	/**
	 * Check if it is main gallery query.
	 *
	 * @return bool
	 */
	public function is_main_query() {

		$mediapress = mediapress();

		return $this === $mediapress->the_gallery_query;
	}

	/**
	 * Reset current gallery
	 */
	public function reset_gallery_data() {

		parent::reset_postdata();

		if ( ! empty( $this->post ) ) {
			mediapress()->current_gallery = mpp_get_gallery( $this->post );
		}
	}

	/**
	 * Putting helpers to allow easy pagination in the loops
	 *
	 * @param boolean $default where to use default schema.
	 *
	 * @return string pagination string.
	 */
	public function paginate( $default = true ) {

		$total = $this->max_num_pages;
		// only bother with the rest if we have more than 1 page!
		if ( $total > 1 ) {
			// structure of “format” depends on whether we’re using pretty permalinks.
			$perma_struct = get_option( 'permalink_structure' );
			$format       = empty( $perma_struct ) ? '&page=%#%' : 'page/%#%/';

			if ( ! defined('DOING_AJAX' ) ) {
				$link         = get_pagenum_link( 1 );
			} else {
				$link = mpp_get_current_page_uri();
				// for paginated links, strip anything after "/page/"

				$link_chunks =  explode('/page/', $link );
				$link = $link_chunks[0];
			}

			// get the current page.
			if ( ! $current_page = $this->get( 'paged' ) ) {
				$current_page = 1;
			}

			if ( ! $default ) {
				// if not using default scheme, override the things.
				$current_page = isset( $_REQUEST['gpage'] ) && $_REQUEST['gpage'] > 0 ? intval( $_REQUEST['gpage'] ) : 1;
				$link         = add_query_arg( null, null );
				$chunks       = explode( '?', $link );
				$link         = $chunks[0];
				$format       = '?gpage=%#%';
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
	 * Print pagination count.
	 */
	public function pagination_count() {

		$paged          = $this->get( 'paged' ) ? $this->get( 'paged' ) : 1;
		$posts_pet_page = $this->get( 'posts_per_page' );

		$from_num = intval( ( $paged - 1 ) * $posts_pet_page ) + 1;

		$to_num = ( $from_num + ( $posts_pet_page - 1 ) > $this->found_posts ) ? $this->found_posts : $from_num + ( $posts_pet_page - 1 );

		printf( __( 'Viewing gallery %d to %d (of %d galleries)', 'mediapress' ), $from_num, $to_num, $this->found_posts );
	}

	/**
	 * Get all the ids in this request
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

	/**
	 * Build this MPP_Gallery_Query from WP_Query object
	 *
	 * @param WP_Query $wp_query the WP_Query object.
	 *
	 * @return MPP_Gallery_Query
	 */
	public static function build_from_wp_query( WP_Query $wp_query ) {

		$query = new self();

		$vars = get_object_vars( $wp_query );

		foreach ( $vars as $name => $value ) {
			$query->{$name} = $value;
		}

		return $query;
	}

}

/**
 * Not used.
 *
 * @param WP_Post $post post object.
 *
 * @return bool
 */
function mpp_setup_gallery_data( $post ) {

	// setup gallery data for current gallery.
	return true;
}

/**
 * Reset global gallery data
 */
function mpp_reset_gallery_data() {

	if ( mediapress()->the_gallery_query ) {
		mediapress()->the_gallery_query->reset_gallery_data();
	}

	wp_reset_postdata();
}

/**
 * Cache all the thumbnails on the Gallery loop start.
 *
 * @param WP_Query $query current query.
 */
function _mpp_cache_gallery_cover( $query ) {

	if ( empty( $query->posts ) ) {
		return;
	}

	$gallery_ids = wp_list_pluck( $query->posts, 'ID' );

	$thumb_ids = array();

	foreach ( (array) $gallery_ids as $gallery_id ) {

		$media_id = mpp_get_gallery_cover_id( $gallery_id );

		if ( $media_id ) {
			$thumb_ids[] = $media_id;
		}
	}

	if ( ! empty( $thumb_ids ) ) {
		// ok there are times when we are only looking for one gallery, in that case don't do anything.
		if ( count( $thumb_ids ) <= 1 ) {
			return;
		}

		_prime_post_caches( $thumb_ids, true, true );

	}
}
add_action( 'mpp_gallery_loop_start', '_mpp_cache_gallery_cover' );
