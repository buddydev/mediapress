<?php
/**
 * Media related shortcodes.
 *
 * @package mediapress.
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Media List shortcode handler.
 * Handles [mpp-list-media ...] as shortcode
 *
 * Please see the function for the list of available options.
 */
function mpp_shortcode_media_list( $atts = null, $content = '' ) {
	// allow everything that can be done to be passed via this shortcode.
	$default_status = mpp_is_active_status( 'public' ) ? 'public' : mpp_get_default_status();
	$defaults       = array(
		'view'              => 'grid',
		// gallery type, all,audio,video,photo etc.
		'type'              => false,
		// pass specific media id.
		'id'                => false,
		// pass specific media ids as array.
		'in'                => false,
		// pass gallery ids to exclude.
		'exclude'           => false,
		// pass gallery slug to include.
		'slug'              => false,
		// public,private,friends one or more privacy level.
		'status'            => $default_status,
		// one or more component name user,groups, evenets etc.
		'component'         => false,
		// the associated component id, could be group id, user id, event id.
		'component_id'      => false,
		'gallery_id'        => false,
		'galleries'         => false,
		'galleries_exclude' => false,

		// how many items per page.
		'per_page'        => false,
		// how many galleries to offset/displace.
		'offset'          => false,
		// which page when paged.
		'page'            => isset( $_REQUEST['mpage'] ) ? absint( $_REQUEST['mpage'] ) : false,
		// to avoid paging.
		'nopaging'        => false,
		// order.
		'order'           => 'DESC',
		// order by, possible options : none, id, user, title, slug, date,modified, random, comment_count, meta_value,meta_value_num, ids.
		'orderby'         => 'date',
		// user params.
		'user_id'         => false,
		'include_users'   => false,
		// users to exclude.
		'exclude_users'   => false,
		'user_name'       => false,
		'scope'           => false,
		'search_terms'    => '',
		// time parameter.
		// this years.
		'year'            => false,
		// 1-12 month number.
		'month'           => false,
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
		// yearMonth, 201307 for july 2013.
		'yearmonth'       => false,
		'meta_key'        => '',
		'meta_value'      => '',
		'column'          => 4,
		'playlist'        => 0,
		// which fields to return ids, id=>parent, all fields(default).
		'fields'          => false,
		'show_pagination' => 1,
		'show_creator'    => 0,
		'before_creator'  => '',
		'after_creator'   => '',
		'lightbox'        => 0,
		'for'             => '', // 'displayed', 'logged', 'author'.
	);

	$defaults = apply_filters( 'mpp_shortcode_list_media_defaults', $defaults );
	$atts     = shortcode_atts( $defaults, $atts );

	if ( ! $atts['meta_key'] ) {
		unset( $atts['meta_key'] );
		unset( $atts['meta_value'] );
	}

	$cols = $atts['column'];
	$view = $atts['view'];
	$type = $atts['type'];

	$show_pagination = $atts['show_pagination'];

	$show_creator   = $atts['show_creator'];
	$before_creator = $atts['before_creator'];
	$after_creator  = $atts['after_creator'];

	unset( $atts['column'] );
	unset( $atts['view'] );
	unset( $atts['show_pagination'] );
	unset( $atts['show_creator'] );
	unset( $atts['before_creator'] );
	unset( $atts['after_creator'] );
	$activity_for = $atts['for'];

	$for = $atts['for'];
	unset( $atts['for'] );

	if ( ! empty( $for ) ) {
		$atts['user_id'] = mpp_get_dynamic_user_id_for_context( $for );
		if ( empty( $atts['user_id'] ) ) {
			return ''; // shortcircuit.
		}
	}

	mpp_shortcode_save_media_data( 'column', $cols );

	$atts = apply_filters( 'mpp_shortcode_list_media_query_args', $atts, $defaults );

	$query = new MPP_Media_Query( $atts );

	mpp_shortcode_save_media_data( 'query', $query );

	$content = apply_filters( 'mpp_shortcode_mpp_media_content', '', $atts, $view );

	if ( ! $content ) {

		$templates = array(
			"shortcodes/{$view}-{$type}.php",
			"shortcodes/$view.php",
			'shortcodes/grid.php',
		);

		ob_start();

		$located = mpp_locate_template( $templates, false );
		if ( $located ) {
			require $located;
		}

		$content = ob_get_clean();
	}

	mpp_shortcode_reset_media_data( 'query' );
	mpp_shortcode_reset_media_data( 'column' );

	return $content;
}

add_shortcode( 'mpp-list-media', 'mpp_shortcode_media_list' );

/**
 * @deprecated
 * Please use mpp-list-media instead.
 */
add_shortcode( 'mpp-media', 'mpp_shortcode_media_list' );
