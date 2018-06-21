<?php
/**
 * Media template tags.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modeled after have_posts(), alternative for media loop
 *
 * Check if there are galleries.
 *
 * @return boolean
 */
function mpp_have_media() {

	$the_media_query = mediapress()->the_media_query;

	if ( $the_media_query ) {
		return $the_media_query->have_media();
	}

	return false;
}

/**
 * Fetch the current media
 *
 * @return boolean
 */
function mpp_the_media() {
	return mediapress()->the_media_query->the_media();
}

/**
 * Print media id
 *
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_id( $media = null ) {
	echo mpp_get_media_id( $media );
}

/**
 * Get media id
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return int media id
 */
function mpp_get_media_id( $media = null ) {

	$media = mpp_get_media( $media );

	return apply_filters( 'mpp_get_media_id', $media->id );

}

/**
 * Print media title
 *
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_title( $media = null ) {
	echo mpp_get_media_title( $media );
}

/**
 * Get media title
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return string title.
 */
function mpp_get_media_title( $media = null ) {

	$media = mpp_get_media( $media );

	return apply_filters( 'mpp_get_media_title', $media->title, $media->id );

}

/**
 * Print media source url to be used for rendering( it is most of the time the value of src attribute)
 *
 * @param string             $size Registered media size type( e.g thumbnail, full, mid, original etc).
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_src( $size = '', $media = null ) {
	echo mpp_get_media_src( $size, $media );
}

/**
 * Get media source url to be used for rendering( it is most of the time the value of src attribute)
 *
 * @param string             $size Registered media size type( e.g thumbnail, full, mid, original etc).
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return string absolute source url.
 */
function mpp_get_media_src( $size = '', $media = null ) {

	$media = mpp_get_media( $media );
	// if media is not photo and the type specified is empty, or not 'original' get cover.
	if ( 'photo' !== $media->type ) {

		if ( ! empty( $size ) && 'original' !== $size ) {
			return mpp_get_media_cover_src( $size, $media->id );
		}
	}
	$storage_manager = mpp_get_storage_manager( $media->id );

	return apply_filters( 'mpp_get_media_src', $storage_manager->get_src( $size, $media->id ), $size, $media );

}

/**
 * Print the absolute path to the media understandable by the storage manager.
 *
 * @param string             $size Registered media size type( e.g thumbnail, full, mid, original etc).
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_path( $size = '', $media = null ) {
	echo mpp_get_media_path( $size, $media );
}

/**
 * Get the absolute path to the media understandable by the storage manager.
 *
 * @param string             $size Registered media size type( e.g thumbnail, full, mid, original etc).
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return mixed
 */
function mpp_get_media_path( $size = '', $media = null ) {

	$media = mpp_get_media( $media );

	$storage_manager = mpp_get_storage_manager( $media->id );

	return $storage_manager->get_path( $size, $media->id );
}

/**
 *  Print media slug
 *
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_slug( $media = null ) {
	echo mpp_get_media_slug( $media );
}

/**
 * Get media slug
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return string
 */
function mpp_get_media_slug( $media = null ) {

	$media = mpp_get_media( $media );

	return apply_filters( 'mpp_get_media_slug', $media->slug, $media->id );

}

/**
 * To Generate the actual code for showing media
 * We will rewrite it with better api in future, currently, It acts as fallback
 *
 * The goal of this function is to generate appropriate output for listing media based on media type
 *
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_load_media_view( $media = null ) {
	$view = mpp_get_media_view( $media );

	if ( ! $view ) {
		printf( __( 'There are no view object registered to handle the display of the content of <strong> %s </strong> type', 'mediapress' ), strtolower( mpp_get_type_singular_name( $media->type ) ) );
	} else {
		$view->display( $media );
	}
}

/**
 * Display Media (Render it for viewing)
 *
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_content( $media = null ) {
	if ( ! $media ) {
		$media = mpp_get_media();
	}

	mpp_load_media_view( $media );
}

/**
 * Show lightbox content.
 *
 * @param $media
 *
 * @return string
 */
function mpp_lightbox_content( $media ) {
	if ( ! $media ) {
		return '';
	}

	$media = mpp_get_media( $media );

	$type = $media->type;

	$templates = array(
		"gallery/media/views/lightbox/{$type}.php", // grid-audio.php etc .
		'gallery/media/views/lightbox/photo.php',
	);

	if ( $media->is_oembed ) {
		array_unshift( $templates, 'gallery/media/views/lightbox/oembed.php' );
	}

	mpp_locate_template( $templates, true );
}
/**
 * Check if the media has description.
 *
 * @since 1.1.1
 *
 * @param MPP_Media|int|null $media media id or object.
 *
 * @return bool true if has description else false.
 */
function mpp_media_has_description( $media = null ) {
	$media = mpp_get_media( $media );

	if ( empty( $media ) || empty( $media->description ) ) {
		return false;
	}

	return true;
}

/**
 * Print media description
 *
 * @param MPP_Media|int|null $media media id or object.
 */
function mpp_media_description( $media = null ) {
	echo mpp_get_media_description( $media );
}

/**
 * Get media description
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return string
 */
function mpp_get_media_description( $media = null ) {

	$media = mpp_get_media( $media );

	return apply_filters( 'mpp_get_media_description', stripslashes( $media->description ), $media->id );

}

/**
 * Print the type of media
 *
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_type( $media = null ) {
	echo mpp_get_media_type( $media );
}

/**
 * Get Media Type.
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return string media type (audio|video|photo etc)
 */
function mpp_get_media_type( $media = null ) {

	$media = mpp_get_media( $media );

	return apply_filters( 'mpp_get_media_type', $media->type, $media->id );

}

/**
 * Print Gallery status (private|public)
 *
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_status( $media = null ) {
	echo mpp_get_media_status( $media );
}

/**
 * Get media status.
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return string Gallery status(public|private|friends only)
 */
function mpp_get_media_status( $media = null ) {

	$media = mpp_get_media( $media );

	return apply_filters( 'mpp_get_media_status', $media->status, $media->id );

}

/**
 * Print the date of creation for the media
 *
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_date_created( $media = null ) {
	echo mpp_get_media_date_created( $media );
}

/**
 * Get the date this media was created
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return string|int|bool Formatted date string or Unix timestamp. False if $date is empty.
 */
function mpp_get_media_date_created( $media = null, $format = '', $translate = true ) {
	if ( ! $format ) {
		$format = get_option( 'date_format' );
	}
	$media = mpp_get_media( $media );

	return apply_filters( 'mpp_get_media_date_created', mysql2date( $format, $media->date_created, $translate ), $media->id );

}

/**
 * Print When was the last time media was updated
 *
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_last_updated( $media = null ) {
	echo mpp_get_media_last_updated( $media );
}

/**
 * Get the date this media was last updated
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return string|int|bool Formatted date string or Unix timestamp. False if $date is empty.
 */
function mpp_get_media_last_updated( $media = null ) {

	$media = mpp_get_media( $media );

	return apply_filters( 'mpp_get_media_date_updated', mysql2date( get_option( 'date_format' ), $media->date_updated, true ), $media->id );

}

/**
 * Print the user id of the person who created this media
 *
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_creator_id( $media = null ) {
	echo mpp_get_media_creator_id( $media );
}

/**
 * Get the ID of the person who created this Gallery
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return int ID of the user who uploaded/created this media.
 */
function mpp_get_media_creator_id( $media = null ) {

	$media = mpp_get_media( $media );

	return apply_filters( 'mpp_get_media_creator_id', $media->user_id, $media->id );

}

/**
 * Print media creator's link.
 *
 * @param int|MPP_Media $media media id or object.
 */
function mpp_media_creator_link( $media = null ) {
	echo mpp_get_media_creator_link( $media );
}

/**
 * Get media creator user link.
 *
 * @param int|MPP_Media $media media id or object.
 *
 * @return string
 */
function mpp_get_media_creator_link( $media = null ) {
	$media = mpp_get_media( $media );

	return mpp_get_user_link( $media->user_id );
}
/**
 * Print the css class list
 *
 * @param string             $class Optional css classes to append.
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_class( $class = '', $media = null ) {
	echo mpp_get_media_class( $class, $media );
}

/**
 * Get css class list fo the media
 *
 * @param string             $class Additional css classes for the media entry.
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return string list of classes for teh media entry.
 */
function mpp_get_media_class( $class = '', $media = null ) {

	$media = mpp_get_media( $media );

	$class_list = "mpp-item mpp-media mpp-media-{$media->type}";

	if ( mpp_is_single_media() ) {
		$class_list .= " mpp-item-single mpp-media-single mpp-media-single-{$media->type}";
	}

	return apply_filters( 'mpp_get_media_class', "{$class_list} {$class}" );
}

/**
 * Print the media anchor html attributes
 *
 * @param array $args any valid html attribute is allowed as key/val pair.
 */
function mpp_media_html_attributes( $args = null ) {
	echo mpp_get_media_html_attributes( $args );
}

/**
 * Build the attributes(prop=val) for the media anchor elemnt
 * It may be useful in adding some extra attributes to the anchor
 *
 * @param array $args any valid html attribute is allowed as key/val pair.
 *
 * @return string
 */
function mpp_get_media_html_attributes( $args = null ) {

	$default = array(
		'class'             => '',
		'id'                => '',
		'title'             => '',
		'data-mpp-context'  => 'gallery',
		'media'             => 0, // pass gallery id or media, not required inside a loop.
		'data-mpp-media-id' => 0,
	);

	$args = wp_parse_args( $args, $default );

	$media = mpp_get_media( $args['media'] );

	if ( ! $media ) {
		return '';
	}

	// if(! $args['id'] )
	//	$args['id'] = 'mpp-media-thumbnail-' . $gallery->id;

	$args['media']             = $media; // we will pass the media object to the filter too.
	$args['data-mpp-media-id'] = mpp_get_media_id( $media );

	$args = (array) apply_filters( 'mpp_media_html_attributes_pre', $args );

	unset( $args['media'] );

	if ( empty( $args['title'] ) ) {
		$args['title'] = mpp_get_media_title( $media );
	}

	return mpp_get_html_attributes( $args ); // may be a filter in future here?
}

/**
 * Print media loop pagination
 */
function mpp_media_pagination() {
	echo mpp_get_media_pagination();
}

/**
 * Get the pagination text
 *
 * @return string
 */
function mpp_get_media_pagination() {

	// check if the current gallery supports playlist, then do not show pagination.
	if ( ! mediapress()->the_media_query || mpp_gallery_supports_playlist( mpp_get_gallery() ) ) {
		return '';
	}

	return "<div class='mpp-paginator'>" . mediapress()->the_media_query->paginate() . '</div>';
}

/**
 * Show the pagination count like showing 1-10 of 20
 */
function mpp_media_pagination_count() {

	if ( ! mediapress()->the_media_query ) {
		return;
	}

	mediapress()->the_media_query->pagination_count();
}

/**
 * Get the next media id based on the given media. It is used for adjacent media.
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return int
 */
function mpp_get_next_media_id( $media ) {

	if ( ! $media ) {
		return 0;
	}

	$media = mpp_get_media( $media );

	$args = array(
		'component'     => $media->component,
		'component_id'  => $media->component_id,
		'object_id'     => $media->id,
		'object_parent' => $media->gallery_id,
		'next'          => true,
	);

	$prev_gallery_id = mpp_get_adjacent_object_id( $args, mpp_get_media_post_type() );

	return $prev_gallery_id;

}

/**
 * Get the previous media id based on the given media.
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return int previous media id.
 */
function mpp_get_previous_media_id( $media ) {

	if ( ! $media ) {
		return 0;
	}

	$media = mpp_get_media( $media );

	$args = array(
		'component'     => $media->component,
		'component_id'  => $media->component_id,
		'object_id'     => $media->id,
		'object_parent' => $media->gallery_id,
		'next'          => false,
	);

	$prev_gallery_id = mpp_get_adjacent_object_id( $args, mpp_get_media_post_type() );

	return $prev_gallery_id;
}

/**
 * Get adjacent media link.
 *
 * @param string $format how to format the link.
 * @param string $link link.
 * @param int    $media_id current media id.
 * @param bool   $previous previous or next to link.
 *
 * @return mixed|null
 */
function mpp_get_adjacent_media_link( $format, $link, $media_id = null, $previous = false ) {

	if ( ! $media_id ) {
		$media_id = mpp_get_current_media_id();
	}

	if ( ! $previous ) {
		$next_media_id = mpp_get_next_media_id( $media_id );
	} else {
		$next_media_id = mpp_get_previous_media_id( $media_id );
	}

	if ( ! $next_media_id ) {
		return;
	}

	$media = mpp_get_media( $next_media_id );

	if ( empty( $media ) ) {
		return;
	}

	$title = mpp_get_media_title( $media );

	$css_class = $previous ? 'mpp-previous' : 'mpp-next'; // css class.

	if ( empty( $title ) ) {
		$title = $previous ? __( 'Previous', 'mediapress' ) : __( 'Next', 'mediapress' );
	}

	$date = mysql2date( get_option( 'date_format' ), $media->date_created );
	$rel  = $previous ? 'prev' : 'next';

	$string = "<a href='" . mpp_get_media_permalink( $media ) . "' rel='{$rel}'>";
	$inlink = str_replace( '%title', $title, $link );
	$inlink = str_replace( '%date', $date, $inlink );
	$inlink = $string . $inlink . '</a>';

	$output = str_replace( '%link', $inlink, $format );

	return "<span class='{$css_class}'>{$output}</span>";

}

/**
 * Print next media link.
 *
 * @param string   $format how to format link.
 * @param string   $link Link.
 * @param int|null $media_id current media id.
 */
function mpp_next_media_link( $format = '%link &raquo;', $link = '%title', $media_id = null ) {
	echo mpp_get_adjacent_media_link( $format, $link, $media_id, false );
}

/**
 * Print previous media link.
 *
 * @param string   $format how to format link.
 * @param string   $link Link.
 * @param int|null $media_id current media id.
 */
function mpp_previous_media_link( $format = '&laquo; %link ', $link = '%title', $media_id = null ) {
	echo mpp_get_adjacent_media_link( $format, $link, $media_id, true );
}

/**
 * Stats Related
 * must be used inside the media loop.
 */

/**
 * Print the total media count for the current query
 */
function mpp_total_media_count() {
	echo mpp_get_total_media_count();
}

/**
 * Get the total number of media in current query
 *
 * @return int
 */
function mpp_get_total_media_count() {

	$found = 0;

	if ( mediapress()->the_media_query ) {
		$found = mediapress()->the_media_query->found_posts;
	}

	return apply_filters( 'mpp_get_total_media_count', $found );

}

/**
 * Total media count for user
 */
function mpp_total_media_count_for_member() {
	echo mpp_get_total_media_count_for_member();
}

/**
 * Get total media count for user
 *
 * @todo Implement it?
 *
 * @return int total count.
 */
function mpp_get_total_media_count_for_member() {
	// mpp_get_total_media_for_user() does not exist at the moment.
	$total = function_exists( 'mpp_get_total_media_for_user' ) ? mpp_get_total_media_for_user() : 0;

	return apply_filters( 'mpp_get_total_media_count_for_member', $total );
}

/**
 * Other functions
 */

/**
 * Get The Single media ID
 *
 * @return int
 */
function mpp_get_current_media_id() {
	return mediapress()->current_media->id;
}

/**
 * Get current Media
 *
 * @return MPP_Media|null
 */
function mpp_get_current_media() {
	return mediapress()->current_media;
}

/**
 * Is it media directory?
 *
 * @todo handle the single media case for root media
 *
 * @return boolean
 */
function mpp_is_media_directory() {

	$action = bp_current_action();

	if ( mpp_is_gallery_directory() && ! empty( $action ) ) {
		return true;
	}

	return false;

}

/**
 * Is Single Media
 *
 * @return boolean
 */
function mpp_is_single_media() {

	if ( mediapress()->the_media_query && mediapress()->the_media_query->is_single() ) {
		return true;
	}

	return false;
}


/**
 * Check if the current action is media editing/management
 *
 * @return boolean
 */
function mpp_is_media_management() {
	return mediapress()->is_editing( 'media' ) && mediapress()->is_action( 'edit' );
}

/**
 * Is it media delete action?
 *
 * @return boolean
 */
function mpp_is_media_delete() {
	return mpp_is_media_management() && mediapress()->is_edit_action( 'delete' );
}

/**
 * Print No media found message.
 *
 * @todo update
 */
function mpp_no_media_message() {
	// detect the type here.
	$type_name = bp_action_variable( 0 );

	// $type_name = media_get_type_name_plural( $type );

	if ( ! empty( $type_name ) ) {
		$message = sprintf( __( 'There are no %s yet.', 'mediapress' ), strtolower( $type_name ) );
	} else {
		$message = __( 'There are no galleries yet.', 'mediapress' );
	}

	echo $message;
}

/**
 * Print media action links.
 *
 * @param MPP_Media|int|null $media media id or Object.
 */
function mpp_media_action_links( $media = null ) {
	echo mpp_get_media_action_links( $media );
}

/**
 * Get media action links like view/edit/delete/upload to show on individual media
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return string action links.
 */
function mpp_get_media_action_links( $media = null ) {

	$links = array();

	$media = mpp_get_media( $media );
	// $links ['view'] = sprintf( '<a href="%1$s" title="view %2$s" class="mpp-view-media">%3$s</a>', mpp_get_media_permalink( $media ), esc_attr( $media->title ), __( 'view', 'mediapress' ) );
	// upload?
	if ( mpp_user_can_edit_media( $media->id ) ) {
		$links['edit'] = sprintf( '<a href="%1$s" title="' . __( 'Edit %2$s', 'mediapress' ) . '">%3$s</a>', mpp_get_media_edit_url( $media ), mpp_get_media_title( $media ), __( 'edit', 'mediapress' ) );
	}
	// delete?
	if ( mpp_user_can_delete_media( $media ) ) {
		$links['delete'] = sprintf( '<a href="%1$s" title="' . __( 'delete %2$s', 'mediapress' ) . '" class="confirm mpp-confirm mpp-delete mpp-delete-media">%3$s</a>', mpp_get_media_delete_url( $media ), mpp_get_media_title( $media ), __( 'delete', 'mediapress' ) );
	}

	return apply_filters( 'mpp_media_actions_links', join( ' ', $links ), $links, $media );

}

/**
 * Get the column class to be assigned to the media grid
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return string
 */
function mpp_get_media_grid_column_class( $media = null ) {
	// we are using 1-24 col grid, where 3-24 represents 1/8th and so on.
	$col = mpp_get_option( 'media_columns' );

	return mpp_get_grid_column_class( $col );
}
