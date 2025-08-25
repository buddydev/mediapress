<?php
/**
 * Gallery listing shortcodes.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gallery Listing shortcode
 *
 * @param array  $atts see args.
 * @param string $content not applicable.
 *
 * @return string
 */
function mpp_shortcode_list_gallery( $atts = null, $content = '' ) {
	// allow everything that can be done to be passed via this shortcode.
	$default_status = mpp_is_active_status( 'public' ) ? 'public' : mpp_get_default_status();
	$defaults       = array(
		// gallery type, all,audio,video,photo etc.
		'type'            => '',
		// pass specific gallery id.
		'id'              => '',
		// pass specific gallery ids as array.
		'in'              => array(),
		// pass gallery ids to exclude.
		'exclude'         => array(),
		// pass gallery slug to include.
		'slug'            => '',
		// public,private,friends one or more privacy level.
		'status'          => $default_status,
		// one or more component name user,groups, events etc.
		'component'       => '',
		// the associated component id, could be group id, user id, event id.
		'component_id'    => '',
		// how many items per page.
		'per_page'        => false,
		'offset'          => false,
		// how many galleries to offset/displace
		// which page when paged.
		'page'            => isset( $_REQUEST['gpage'] ) ? absint( $_REQUEST['gpage'] ) : '',
		// to avoid paging.
		'nopaging'        => false,
		// order.
		'order'           => 'DESC',
		// none, id, user, title, slug, date,modified, random, comment_count, meta_value,meta_value_num, ids.
		'orderby'         => 'date',
		// user params.
		'user_id'         => '',
		'include_users'   => array(),
		// users to exclude.
		'exclude_users'   => array(),
		'user_name'       => '',
		'scope'           => false,
		'search_terms'    => '',
		// time parameter.
		// this years.
		'year'            => '',
		// 1-12 month number.
		'month'           => '',
		// 1-53 week.
		'week'            => '',
		// specific day.
		'day'             => '',
		// specific hour.
		'hour'            => '',
		// specific minute.
		'minute'          => '',
		// specific second 0-60.
		'second'          => '',
		// yearMonth, 201307//july 2013.
		'yearmonth'       => '',
		'meta_key'        => '',
		'meta_value'      => '',
		// which fields to return ids, id=>parent, all fields(default).
		'fields'          => '',
		'column'          => 4,
		// show the pagination links?
		'show_pagination' => 1,
		'show_creator'    => 0,
		'before_creator'  => '',
		'after_creator'   => '',
		'for'             => '', // 'displayed', 'logged', 'author'.
	);

	// allow extending shortcode with extra parameters.
	$defaults = apply_filters( 'mpp_shortcode_list_gallery_defaults', $defaults );

	$atts = shortcode_atts( $defaults, $atts );

	if ( ! $atts['meta_key'] ) {
		unset( $atts['meta_key'] );
		unset( $atts['meta_value'] );
	}
	// These variables are used in the template.
	$shortcode_column = $atts['column'];
	$show_pagination  = $atts['show_pagination'];

	$show_creator   = $atts['show_creator'];
	$before_creator = $atts['before_creator'];
	$after_creator  = $atts['after_creator'];

	unset( $atts['column'] );
	// unset( $atts['view'] );

	$for = $atts['for'];
	unset( $atts['for'] );

	if ( ! empty( $for ) ) {
		$atts['user_id'] = mpp_get_dynamic_user_id_for_context( $for );
		if ( empty( $atts['user_id'] ) ) {
			return ''; // shortcircuit.
		}
	}

	$atts = apply_filters( 'mpp_shortcode_list_gallery_query_args', $atts, $defaults );

	// the query is available in the shortcode template.
	$query = new MPP_Gallery_Query( $atts );

	$view = null; // @todo allow filtering by view in future.
	$located = apply_filters( 'mpp_shortcode_list_gallery_located_template', mpp_locate_template( array( 'shortcodes/gallery-list.php' ), false ), $atts, $view );

	ob_start();
	// include shortcode template.
	if ( $located ) {
		require $located;
	}

	$content = ob_get_clean();

	return $content;
}
add_shortcode( 'mpp-list-gallery', 'mpp_shortcode_list_gallery' );

/**
 * Show media from single gallery.
 *
 * @param array  $atts see args.
 * @param string $content content to return.
 *
 * @return string
 */
function mpp_shortcode_show_gallery( $atts = null, $content = '' ) {

	$defaults = array(
		// pass specific gallery id.
		'id'            => '',
		// pass specific gallery ids as array.
		'in'            => array(),
		// pass gallery ids to exclude.
		'exclude'       => array(),
		// pass gallery slug to include.
		'slug'          => '',
		// how many items per page.
		'per_page'      => false,
		// how many galleries to offset/displace.
		'offset'        => false,
		// which page when paged.
		'page'          => isset( $_REQUEST['mpage'] ) ? absint( $_REQUEST['mpage'] ) : '',
		// to avoid paging.
		'nopaging'      => false,
		// order.
		'order'         => 'DESC',
		// none, id, user, title, slug, date,modified, random, comment_count, meta_value,meta_value_num, ids.
		'orderby'       => 'date',
		// user params.
		'user_id'       => '',
		'include_users' => array(),
		// users to exclude.
		'exclude_users' => array(),
		'user_name'     => '',
		'scope'         => false,
		'search_terms'  => '',

		'meta_key'        => '',
		'meta_value'      => '',
		'column'          => 4,
		'view'            => 'grid',
		'show_pagination' => 1,
		'show_creator'    => 0,
		'before_creator'  => '',
		'after_creator'   => '',
		'lightbox'        => 0,
	);

	$defaults = apply_filters( 'mpp_shortcode_show_gallery_defaults', $defaults );

	$atts = shortcode_atts( $defaults, $atts );

	if ( ! $atts['id'] ) {
		return '';
	}

	$gallery_id = absint( $atts['id'] );

	$show_creator   = $atts['show_creator'];
	$before_creator = $atts['before_creator'];
	$after_creator  = $atts['after_creator'];


	global $wpdb;

	$attachments = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = %s ", $gallery_id, 'attachment' ) );

	array_push( $attachments, $gallery_id );

	_prime_post_caches( $attachments, true, true );

	$gallery = mpp_get_gallery( $gallery_id );
	// if gallery does not exist, there is no point in further proceeding.
	if ( ! $gallery ) {
		return '';
	}

	if ( ! $atts['meta_key'] ) {
		unset( $atts['meta_key'] );
		unset( $atts['meta_value'] );
	}

	$view = $atts['view'];

	unset( $atts['id'] );
	unset( $atts['view'] );

	$atts['gallery_id'] = $gallery_id;
	$atts['status'] = mpp_get_accessible_statuses( $gallery->component, $gallery->component_id );
	$shortcode_column = $atts['column'];
	mpp_shortcode_save_media_data( 'column', $shortcode_column );

	mpp_shortcode_save_media_data( 'shortcode_args', $atts );

	unset( $atts['column'] );

	$show_pagination = $atts['show_pagination'];
	unset( $atts['show_pagination'] );

	$atts = array_filter( $atts );

	$atts = apply_filters( 'mpp_shortcode_show_gallery_query_args', $atts, $defaults );

	$query = new MPP_Media_Query( $atts );
	mpp_shortcode_save_media_data( 'query', $query );

	$content = apply_filters( 'mpp_shortcode_mpp_show_gallery_content', '', $atts, $view );

	if ( ! $content ) {

		$templates = array(
			'shortcodes/grid.php'

		);

		if ( $view && mpp_is_safe_template_part_name( $view ) ) {
			$type = $gallery->type;

			$preferred_templates = array(
				"shortcodes/{$view}-{$type}.php",
				"shortcodes/{$view}.php",
			);
			// audio-playlist, video-playlist.
			$templates = array_merge( $preferred_templates, $templates );
		}

		ob_start();

		// locate template.
		$located = mpp_locate_template( $templates, false );

		if ( $located ) {
			require $located;
		}

		$content = ob_get_clean();
	}

	mpp_shortcode_reset_media_data( 'column' );
	mpp_shortcode_reset_media_data( 'query' );
	mpp_shortcode_reset_media_data( 'shortcode_args' );

	return $content;
}
add_shortcode( 'mpp-show-gallery', 'mpp_shortcode_show_gallery' );
