<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * Gallery Listing shortcode
 */
add_shortcode( 'mpp-list-gallery', 'mpp_shortcode_list_gallery' );

function mpp_shortcode_list_gallery( $atts = null, $content = '' ) {
	//allow everything that can be done to be passed via this shortcode
	$default_status = mpp_is_active_status( 'public' ) ? 'public' : mpp_get_default_status();
	$defaults       = array(
		'type'            => false,
		//gallery type, all,audio,video,photo etc
		'id'              => false,
		//pass specific gallery id
		'in'              => false,
		//pass specific gallery ids as array
		'exclude'         => false,
		//pass gallery ids to exclude
		'slug'            => false,
		//pass gallery slug to include
		'status'          => $default_status,
		//public,private,friends one or more privacy level
		'component'       => false,
		//one or more component name user,groups, evenets etc
		'component_id'    => false,
		// the associated component id, could be group id, user id, event id
		'per_page'        => false,
		//how many items per page
		'offset'          => false,
		//how many galleries to offset/displace
		'page'            => isset( $_REQUEST['gpage'] ) ? absint( $_REQUEST['gpage'] ) : false,
		//which page when paged
		'nopaging'        => false,
		//to avoid paging
		'order'           => 'DESC',
		//order
		'orderby'         => 'date',
		//none, id, user, title, slug, date,modified, random, comment_count, meta_value,meta_value_num, ids
		//user params
		'user_id'         => false,
		'include_users'   => false,
		'exclude_users'   => false,
		//users to exclude
		'user_name'       => false,
		'scope'           => false,
		'search_terms'    => '',
		//time parameter
		'year'            => false,
		//this years
		'month'           => false,
		//1-12 month number
		'week'            => '',
		//1-53 week
		'day'             => '',
		//specific day
		'hour'            => '',
		//specific hour
		'minute'          => '',
		//specific minute
		'second'          => '',
		//specific second 0-60
		'yearmonth'       => false,
		// yearMonth, 201307//july 2013
		'meta_key'        => '',
		'meta_value'      => '',
		// 'meta_query'=>false,
		'fields'          => false,
		//which fields to return ids, id=>parent, all fields(default)
		'column'          => 4,
		'show_pagination' => 1,
		//show the pagination links?
	);

	//allow extending shortcode with extra parameters
	$defaults = apply_filters( 'mpp_shortcode_list_gallery_defaults', $defaults );

    $atts = shortcode_atts( $defaults, $atts );
    
    if ( ! $atts['meta_key'] ) {
        unset( $atts['meta_key'] );
        unset( $atts['meta_value'] );
    }
    //These variables are used in the template
	$shortcode_column = $atts['column'];
	$show_pagination = $atts['show_pagination'];

	unset( $atts['column'] );
	//unset( $atts['view'] );

	$atts = apply_filters( 'mpp_shortcode_list_gallery_query_args', $atts, $defaults );

	//the query is available in the shortcode template
	$query = new MPP_Gallery_Query( $atts );

	$located = mpp_locate_template( array( 'shortcodes/gallery-list.php' ), false );

	ob_start();
    //include shortcode template
	if ( $located ) {
		require $located;
	}
	
    $content = ob_get_clean();

    return $content;
}

add_shortcode( 'mpp-show-gallery', 'mpp_shortcode_show_gallery' );

function mpp_shortcode_show_gallery( $atts = null, $content = '' ) {

	$defaults = array(
		'id'            => false, //pass specific gallery id
		'in'            => false, //pass specific gallery ids as array
		'exclude'       => false, //pass gallery ids to exclude
		'slug'          => false,//pass gallery slug to include
		'per_page'      => false, //how many items per page
		'offset'        => false, //how many galleries to offset/displace
		'page'          => isset( $_REQUEST['mpage'] ) ? absint( $_REQUEST['mpage'] ) : false ,//which page when paged
		'nopaging'      => false, //to avoid paging
		'order'         => 'DESC',//order
		'orderby'       => 'date',//none, id, user, title, slug, date,modified, random, comment_count, meta_value,meta_value_num, ids
		//user params
		'user_id'       => false,
		'include_users' => false,
		'exclude_users' => false,//users to exclude
		'user_name'     => false,
		'scope'         => false,
		'search_terms'  => '',

		'meta_key'		=> '',
		'meta_value'	=> '',
		'column'		=> 4,
		'view'			=> '',
		'show_pagination'=> 1,
		'lightbox'        => 0,
	);

	$defaults = apply_filters( 'mpp_shortcode_show_gallery_defaults', $defaults );

	$atts = shortcode_atts( $defaults, $atts );

	if ( ! $atts['id'] ) {
		return '';
	}

	$gallery_id = absint( $atts['id'] );

	global $wpdb;

	$attachments = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = %s ", $gallery_id, 'attachment' ) );

	array_push( $attachments, $gallery_id );

	_prime_post_caches( $attachments, true, true );

	$gallery = mpp_get_gallery( $gallery_id );
	//if gallery does not exist, there is no proint in further proceeding
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

		if ( $view ) {
			$type = $gallery->type;

			$preferred_templates = array(
				"shortcodes/{$view}-{$type}.php",
				"shortcodes/{$view}.php",
			);//audio-playlist, video-playlist

			$templates = array_merge( $preferred_templates, $templates );
			//array_unshift( $templates, $preferred_template );
		}

		ob_start();

		$located = mpp_locate_template( $templates,  false );//load

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