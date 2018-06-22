<?php
/**
 * Activity related functions.
 *
 * @package mediapress
 */

// No direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Returns attached media ids for an activity.
 *
 * @param int $activity_id the activity id.
 *
 * @return array of media ids
 */
function mpp_activity_get_attached_media_ids( $activity_id ) {
	return apply_filters( 'mpp_activity_get_attached_media_ids', bp_activity_get_meta( $activity_id, '_mpp_attached_media_id', false ), $activity_id );
}

/**
 * Updates attached list of media ids for given activity.
 *
 * @param int   $activity_id reated activity id.
 * @param array $media_ids attached media ids.
 *
 * @return array
 */
function mpp_activity_update_attached_media_ids( $activity_id, $media_ids ) {

	foreach ( $media_ids as $media_id ) {
		bp_activity_add_meta( $activity_id, '_mpp_attached_media_id', $media_id );
	}

	return $media_ids;
}

/**
 * Deletes attached list of media ids for an activity.
 *
 * @param int $activity_id related activity id.
 *
 * @return boolean
 */
function mpp_activity_delete_attached_media_ids( $activity_id ) {
	return bp_activity_delete_meta( $activity_id, '_mpp_attached_media_id' );
}

/**
 * Returns the ids of media that should be shown as attached to activity.
 *
 * It uses the activity media limit to decide the cap.
 *
 * @param int $activity_id related activity id.
 *
 * @return array of media ids.
 */
function mpp_activity_get_displayable_media_ids( $activity_id ) {

	$media_ids = mpp_activity_get_attached_media_ids( $activity_id );
	$max       = mpp_activity_get_media_display_cap();

	return array_slice( $media_ids, 0, $max );
}

/**
 * Returns number of media to be listed as the attachment of activity.
 *
 * @return mixed
 */
function mpp_activity_get_media_display_cap() {
	return mpp_get_option( 'activity_media_display_limit', 6 );
}

/**
 * Checks if activity has associated media.
 *
 * @param int $activity_id related activity id.
 *
 * @return mixed false if no attachment else array of attachment ids
 */
function mpp_activity_has_media( $activity_id = null ) {

	if ( ! $activity_id ) {
		$activity_id = bp_get_activity_id();
	}

	return mpp_activity_get_attached_media_ids( $activity_id );
}

/**
 * Returns the id of the gallery associated with this activity
 * _mpp_gallery_id meta key is added for activity uploads as well as single gallery activity/comment
 *
 * If it is a single gallery activity(comments on single gallery page), there won't exist the meta _mpp_media_id
 *
 * This meta is added to activity when an activity has uploads from activity page or a comment is made on the single gallery page(not the single media).
 * The only way to differentiate these two types of activity is to check for the presence of the _mpp_attached_media_ids meta
 *
 *  If a new activity is created by posting on single media page(comments), It does not have _mpp_gallery_id associated with it
 *
 * @param int $activity_id related activity id.
 *
 * @return int gallery id
 */
function mpp_activity_get_gallery_id( $activity_id ) {
	return bp_activity_get_meta( $activity_id, '_mpp_gallery_id', true );
}

/**
 * Updates the gallery id associated with this activity
 *
 * @param int $activity_id related activity id.
 * @param int $gallery_id attached gallery id.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function mpp_activity_update_gallery_id( $activity_id, $gallery_id ) {
	return bp_activity_update_meta( $activity_id, '_mpp_gallery_id', $gallery_id );
}

/**
 * Deletes gallery id associated with this activity
 *
 * @param int $activity_id related activity id.
 *
 * @return  boolean
 */
function mpp_activity_delete_gallery_id( $activity_id ) {
	return bp_activity_delete_meta( $activity_id, '_mpp_gallery_id' );
}

/**
 * Returns the id of the media associated with this activity
 * It is used to differentiate single media activity from the activity upload
 * for activity uploaded media please see _mpp_attached_media_id
 *
 * Please note, we do not consider activity uploads as media activity(We consider activity uploads as gallery activity instead),
 * see _mpp_attached_media_id for the same
 *
 * It is for single media activity comment
 *
 * @param int $activity_id related activity id.
 *
 * @return mixed
 */
function mpp_activity_get_media_id( $activity_id ) {
	return bp_activity_get_meta( $activity_id, '_mpp_media_id', true );
}


/**
 * Update attached media id.
 *
 * @param int $activity_id related activity id.
 * @param int $media_id attached media id.
 *
 * @return bool|int
 */
function mpp_activity_update_media_id( $activity_id, $media_id ) {
	return bp_activity_update_meta( $activity_id, '_mpp_media_id', $media_id );
}

/**
 * Delete attached media id(s).
 *
 * @param int $activity_id related activity id.
 *
 * @return bool
 */
function mpp_activity_delete_media_id( $activity_id ) {
	return bp_activity_delete_meta( $activity_id, '_mpp_media_id' );
}

/**
 * Get activity context.
 *
 * @param int $activity_id related activity id.
 *
 * @return string gallery|media
 */
function mpp_activity_get_context( $activity_id ) {
	return bp_activity_get_meta( $activity_id, '_mpp_context', true );
}

/**
 * Updates activity context
 *
 * @param int    $activity_id  related activity id.
 * @param string $context activity context.
 *
 * @return bool|int
 */
function mpp_activity_update_context( $activity_id, $context = 'gallery' ) {
	return bp_activity_update_meta( $activity_id, '_mpp_context', $context );
}

/**
 * Deletes activity context
 *
 * @param int $activity_id related activity id.
 *
 * @return mixed
 */
function mpp_activity_delete_context( $activity_id ) {
	return bp_activity_delete_meta( $activity_id, '_mpp_context' );
}

/**
 * Storing/retrieving mpp activity type(create_gallery|update_gallery|media_upload) etc in activity meta
 *
 * @param int $activity_id related activity id.
 *
 * @return mixed
 */
function mpp_activity_get_activity_type( $activity_id ) {
	return bp_activity_get_meta( $activity_id, '_mpp_activity_type', true );
}

/**
 * Update MediaPress activity type
 *
 * @param int    $activity_id related activity id.
 * @param string $type activity type.
 *
 * @return mixed
 */
function mpp_activity_update_activity_type( $activity_id, $type ) {
	return bp_activity_update_meta( $activity_id, '_mpp_activity_type', $type );
}

/**
 * Delete MediaPress activity type
 *
 * @param  int $activity_id related activity id.
 *
 * @return mixed
 */
function mpp_activity_delete_activity_type( $activity_id ) {
	return bp_activity_delete_meta( $activity_id, '_mpp_activity_type' );
}

/**
 * When an activity is saved, check if there exists a media attachment cookie,
 * if yes, mark it as non orphaned and store in the activity meta
 */
function mpp_activity_mark_attached_media( $activity_id ) {

	if ( empty( $_POST['mpp-attached-media'] ) || ! is_user_logged_in() ) {
		return;
	}

	// let us process.
	$media_ids = $_POST['mpp-attached-media'];
	$media_ids = explode( ',', $media_ids ); // make an array.

	$media_ids = array_filter( array_unique( $media_ids ) );

	foreach ( $media_ids as $media_id ) {
		// should we verify the logged in user & owner of media is same?
		mpp_delete_media_meta( $media_id, '_mpp_is_orphan' ); // or should we delete the key?
	}

	mpp_activity_update_attached_media_ids( $activity_id, $media_ids );

	mpp_activity_update_context( $activity_id, 'gallery' );
	mpp_activity_update_activity_type( $activity_id, 'media_upload' );

	$activity = new BP_Activity_Activity( $activity_id );

	// store the media ids in the activity meta
	// also add the activity to gallery & gallery to activity link.
	$media = mpp_get_media( $media_id );
	// if the media was uploaded from the sitewide activity page and group was selected
	// move media from user wall to groups wall.
	if ( 'groups' === $activity->component && mpp_is_active_component( 'groups' ) && 'groups' !== $media->component ) {

		$group_wall_gallery = mpp_get_context_gallery( array(
			'component'    => 'groups',
			'component_id' => $activity->item_id,
			'type'         => $media->type,
			'context'      => 'activity',
			'user_id'      => bp_loggedin_user_id(),
		) );

		if ( $group_wall_gallery ) {
			// cache all media.
			_prime_post_caches( $media_ids, true, true );
			// loop and move.
			foreach ( $media_ids as $media_id ) {
				mpp_move_media( $media_id, $group_wall_gallery->id );
				// clear media cache(details have changed).
				mpp_clean_media_cache( $media_id );
			}
		}
		// refetch media.
		$media = mpp_get_media( $media->id );
	}

	if ( $media->gallery_id ) {
		mpp_activity_update_gallery_id( $activity_id, $media->gallery_id );
	}

	// also update this activity and set its action to be mpp_media_upload
	// $activity->component = buddypress()->mediapress->id;
	$activity->type = 'mpp_media_upload';
	$activity->save();

	// save activity privacy.
	$status_object = mpp_get_status_object( $media->status );
	// if you have BuddyPress Activity privacy plugin enabled, this will work out of the box.
	if ( $status_object ) {
		bp_activity_update_meta( $activity->id, 'activity-privacy', $status_object->activity_privacy );
	}

	do_action( 'mpp_activity_media_marked_attached', $media_ids );
}

/**
 * Record Media Activity
 *
 * It does not actually records activity, simply simulates the activity update and rest are done by the actions.php functions
 *
 * It will be removed in future for a better record_activity method
 *
 * @param array $args
 *
 * @return boolean
 */
function mpp_record_activity( $args = null ) {

	// if activity module is not active, why bother.
	if ( ! bp_is_active( 'activity' ) ) {
		return false;
	}

	$default = array(
		'id'           => false, // activity id.
		'gallery_id'   => 0,
		'media_id'     => 0,
		'media_ids'    => null, // single id or an array of ids.
		'action'       => '',
		'content'      => '',
		'type'         => '', // type of activity  'create_gallery, update_gallery, media_upload etc'.
		'component'    => mpp_get_current_component(),
		'component_id' => mpp_get_current_component_id(),
		'user_id'      => get_current_user_id(),
		'status'       => '',
	);

	$args = wp_parse_args( $args, $default );


	// at least a gallery id or a media id should be given.
	if ( ( ! $args['gallery_id'] && ! $args['media_id'] )
	     || ! mpp_is_enabled( $args['component'], $args['component_id'] )
	     || ! $args['component_id']
	) {
		return false;
	}

	$gallery_id = absint( $args['gallery_id'] );
	$media_id   = absint( $args['media_id'] );

	$type = $args['type']; // should we validate type too?

	$hide_sitewide = 0;
	$status_object = null;

	if ( $args['status'] ) {
		$status_object = mpp_get_status_object( $args['status'] );

		if ( $status_object && ( 'hidden' === $status_object->activity_privacy  || 'onlyme' === $status_object->activity_privacy ) ) {
			$hide_sitewide = 1;
		}
		// if BuddyPress Activity privacy plugin is not active, revert back to hiding all non public activity.
		if ( ! function_exists( 'bp_activity_privacy_check_config' ) ) {
			$hide_sitewide = ( 'public' === $args['status'] ) ? 0 : 1; // overwrite privacy.
		}
	}

	$media_ids = $args['media_ids'];

	if ( ! empty( $media_ids ) && ! is_array( $media_ids ) ) {
		$media_ids = explode( ',', $media_ids );
	}

	$component = $args['component'];

	if ( $component == buddypress()->members->id ) {
		// for user gallery updates, let it be simple activity , do not set the component to 'members'.
		$component = buddypress()->activity->id;
	}

	$activity_args = array(
		'id'                => $args['id'],
		'user_id'           => $args['user_id'],
		'action'            => $args['action'],
		'content'           => $args['content'],
		//'primary_link'      => '',
		'component'         => $component,
		'type'              => 'mpp_media_upload',
		'item_id'           => absint( $args['component_id'] ),
		'secondary_item_id' => false,
		'hide_sitewide'     => $hide_sitewide,
	);

	// only update record time if this is a new activity.
	if ( empty( $args['id'] ) ) {
		$activity_args['recorded_time'] = bp_core_current_time();
	}

	// let us give an opportunity to customize the activity args
	// use this filter to work with the activity privacy.
	$activity_args = apply_filters( 'mpp_record_activity_args', $activity_args, $default );

	$activity_id = bp_activity_add( $activity_args );

	if ( ! $activity_id ) {
		return false; // there was a problem!
	}

	// store the type of gallery activity in meta.
	if ( $type ) {
		mpp_activity_update_activity_type( $activity_id, $type );
	}

	if ( $media_ids ) {
		$media_ids = wp_parse_id_list( $media_ids );
		mpp_activity_update_attached_media_ids( $activity_id, $media_ids );
	}

	if ( $gallery_id ) {
		mpp_activity_update_gallery_id( $activity_id, $gallery_id );
	}

	if ( $media_id ) {
		mpp_activity_update_media_id( $activity_id, $media_id );
	}

	mpp_activity_update_context( $activity_id, 'gallery' );

	// save activity privacy.
	if ( $status_object ) {
		bp_activity_update_meta( $activity_id, 'activity-privacy', $status_object->activity_privacy );
	}

	return $activity_id;
}

/**
 * Since BuddyPress does not allow filtering activity comment template, we do it ourself here
 *
 * @see bp_activity_comments for the originalc code
 *
 * @param string $args comment args.
 */
function mpp_activity_comments( $args = '' ) {
	echo mpp_activity_get_comments( $args );
}

/**
 * Get the comment markup for an activity item.
 * clone of bp_activity_get_comments
 *
 * @param string $args activity comment args.
 *
 * @return bool|mixed
 */
function mpp_activity_get_comments( $args = '' ) {
	global $activities_template;

	if ( empty( $activities_template->activity->children ) ) {
		return false;
	}

	mpp_activity_recurse_comments( $activities_template->activity );
}

/**
 * Loops through a level of activity comments and loads the template for each.
 *
 * It is a copy of bp_activity_recurse_comments, since bp dioes not allow using custom template for activity comment, It acts as a filler
 *
 * @param  BP_Activity_Activity $comment Activity comment.
 *
 * @return bool|mixed
 */
function mpp_activity_recurse_comments( $comment ) {
	global $activities_template;

	if ( empty( $comment ) ) {
		return false;
	}

	if ( empty( $comment->children ) ) {
		return false;
	}

	/**
	 * Filters the opening tag for the template that lists activity comments.
	 *
	 * @param string $value Opening tag for the HTML markup to use.
	 */
	echo apply_filters( 'bp_activity_recurse_comments_start_ul', '<ul>' );

	$template = mpp_locate_template( array( 'buddypress/activity/comment.php' ), false, false );

	// Backward compatibility. In older versions of BP, the markup was
	// generated in the PHP instead of a template. This ensures that
	// older themes (which are not children of bp-default and won't
	// have the new template) will still work.
	if ( ! $template ) {
		$template = buddypress()->plugin_dir . '/bp-themes/bp-default/activity/comment.php';
	}

	foreach ( (array) $comment->children as $comment_child ) {
		// Put the comment into the global so it's available to filters.
		$activities_template->activity->current_comment = $comment_child;

		load_template( $template, false );
		unset( $activities_template->activity->current_comment );
	}

	/**
	 * Filters the closing tag for the template that list activity comments.
	 *
	 * @param string $value Closing tag for the HTML markup to use.
	 */
	echo apply_filters( 'bp_activity_recurse_comments_end_ul', '</ul>' );
}

/**
 * Activity comment sync
 */

/**
 * Get associated comment for the activity
 *
 * @param int $activity_id activity id.
 *
 * @return mixed
 */
function mpp_activity_get_associated_comment_id( $activity_id ) {
	return bp_activity_get_meta( $activity_id, '_mpp_comment_id', true );
}

/**
 * Associate a WordPress comment to BuddyPress activity
 *
 * @param int $activity_id activity id.
 * @param int $value comment id.
 *
 * @return bool|int
 */
function mpp_activity_update_associated_comment_id( $activity_id, $value ) {
	return bp_activity_update_meta( $activity_id, '_mpp_comment_id', $value );
}

/**
 * Detach related WordPress comment.
 *
 * @param int $activity_id activity id.
 *
 * @return bool
 */
function mpp_activity_delete_associated_comment_id( $activity_id ) {
	return bp_activity_delete_meta( $activity_id, '_mpp_comment_id' );
}

/**
 * Create a new WordPress comment for activity
 *
 * @param int $activity_id activity id.
 */
function mpp_activity_create_comment_for_activity( $activity_id ) {

	if ( ! $activity_id || ! mpp_get_option( 'activity_comment_sync' ) ) {
		return;
	}

	$activity = new BP_Activity_Activity( $activity_id );

	if ( 'mpp_media_upload' !== $activity->type ) {
		return;
	}

	$gallery_id = mpp_activity_get_gallery_id( $activity_id );
	$media_id   = mpp_activity_get_media_id( $activity_id );

	// this is not MediaPress activity.
	if ( ! $gallery_id && ! $media_id ) {
		return;
	}
	// parent post id for the comment.
	$parent_id = $media_id > 0 ? $media_id : $gallery_id;
	// now, create a top level comment and save.
	$comment_data = array(
		'post_id'         => $parent_id,
		'user_id'         => get_current_user_id(),
		'comment_parent'  => 0,
		'comment_content' => $activity->content,
		'comment_type'    => mpp_get_comment_type(),
	);

	$comment_id = mpp_add_comment( $comment_data );

	// update comment meta.
	if ( $comment_id ) {

		mpp_update_comment_meta( $comment_id, '_mpp_activity_id', $activity_id );

		mpp_activity_update_associated_comment_id( $activity_id, $comment_id );

		// also since there are media attached and we are mirroring activity, let us save the attached media too.
		$media_ids = mpp_activity_get_attached_media_ids( $activity_id );
		// it is a gallery upload post from activity.
		if ( $gallery_id && ! empty( $media_ids ) ) {
			// only available when sync is enabled.
			if ( function_exists( 'mpp_comment_update_attached_media_ids' ) ) {
				mpp_comment_update_attached_media_ids( $comment_id, $media_ids );
			}
		}
		// most probably a comment on media.
		if ( ! empty( $media_id ) ) {
			// should we add media as the comment meta? no, we don't need that at the moment.
		}
	}
}

/**
 * Deletes activity meta entries by given key/val
 * @global wpdb $wpdb
 *
 * @param string $key meta key.
 * @param int    $object_id the related object id.
 *
 * @return bool
 */
function mpp_delete_activity_meta_by_key_value( $key, $object_id ) {

	if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'activity' ) ) {
		return false;
	}

	global $wpdb;
	$bp    = buddypress();
	$query = $wpdb->prepare( "DELETE FROM {$bp->activity->table_name_meta} WHERE meta_key = %s AND meta_value = %d", $key, $object_id );

	return $wpdb->query( $query );
}

/**
 * Delete related activity for the given media.
 *
 * @param int $media_id media id.
 *
 * @return bool
 */
function mpp_delete_activity_for_single_published_media( $media_id ) {

	if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'activity' ) ) {
		return false; //or false?
	}

	global $wpdb;
	$bp = buddypress();

	// select ids , we need to delete comment too?
	$query = "SELECT activity_id FROM {$bp->activity->table_name_meta} WHERE  ( meta_key = %s AND meta_value = %d ) OR ( meta_key = %s AND meta_value = %d ) ";

	$query = $wpdb->prepare( $query, '_mpp_attached_media_id', $media_id, '_mpp_media_id', $media_id );

	$activity_ids = $wpdb->get_col( $query );

	if ( empty( $activity_ids ) ) {
		return false;
	}

	// cache the activity meta.
	bp_activity_update_meta_cache( $activity_ids );

	$to_delete_ids = array();

	foreach ( $activity_ids as $activity_id ) {

		$ids = mpp_activity_get_attached_media_ids( $activity_id );

		if ( count( $ids ) <= 1 ) {
			$to_delete_ids[] = $activity_id;
		}
	}

	if ( empty( $to_delete_ids ) ) {
		return false;
	}

	$list = '(' . join( ',', $to_delete_ids ) . ')';

	if ( ! $wpdb->query( "DELETE FROM {$bp->activity->table_name} WHERE id IN {$list}" ) ) {
		return false;
	}

	// delete  comments.
	$activity_comment_ids = mpp_delete_activity_comments( $to_delete_ids );

	// delete all activities.
	$deleted_ids = array_merge( $to_delete_ids, $activity_comment_ids );

	// get associated WordPress comment ids? No need to worry about that.
	BP_Activity_Activity::delete_activity_meta_entries( $deleted_ids );
}

/**
 * Delete activity items by activity meta key and value
 *
 * @param string $key activity key.
 * @param int    $object_id meta value to identify the object.
 *
 * @return bool|array false opn failure else list of activity ids.
 */

function mpp_delete_activity_by_meta_key_value( $key, $object_id = null ) {

	global $bp, $wpdb;

	if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'activity' ) ) {
		return false; // or false?
	}

	$where_sql = array();

	$where_sql [] = $wpdb->prepare( 'meta_key=%s', $key );

	if ( $object_id ) {
		$where_sql[] = $wpdb->prepare( 'meta_value = %d', $object_id );
	}

	$where_sql = join( ' AND ', $where_sql );

	// Fetch the activity IDs so we can delete any comments for this activity item.
	$activity_ids = $wpdb->get_col( "SELECT activity_id FROM {$bp->activity->table_name_meta} WHERE {$where_sql}" );

	if ( empty( $activity_ids ) ) {
		return false;
	}

	$list = '(' . join( ',', $activity_ids ) . ')';

	if ( ! $wpdb->query( "DELETE FROM {$bp->activity->table_name} WHERE id IN {$list}" ) ) {
		return false;
	}

	// Handle accompanying activity comments and meta deletion

	$activity_comment_ids = mpp_delete_activity_comments( $activity_ids );

	$activity_ids = array_merge( $activity_ids, $activity_comment_ids );

	BP_Activity_Activity::delete_activity_meta_entries( $activity_ids );

	return $activity_ids;
}

/**
 * Delete all comments for the given array of activities
 *
 * @param int[] $activity_ids array of activity ids.
 *
 * @return array|bool
 */
function mpp_delete_activity_comments( $activity_ids ) {
	global $wpdb;

	if ( ! function_exists( 'buddypress' ) ) {
		return false;
	}

	$bp = buddypress();

	if ( ! $activity_ids ) {
		return array();
	}

	$activity_ids_comma          = implode( ',', wp_parse_id_list( $activity_ids ) );
	$activity_comments_where_sql = "WHERE type = 'activity_comment' AND item_id IN ({$activity_ids_comma})";

	// Fetch the activity comment IDs for our deleted activity items.
	$activity_comment_ids = $wpdb->get_col( "SELECT id FROM {$bp->activity->table_name} {$activity_comments_where_sql}" );

	// We have activity comments!
	if ( ! empty( $activity_comment_ids ) ) {
		// Delete activity comments.
		$wpdb->query( "DELETE FROM {$bp->activity->table_name} {$activity_comments_where_sql}" );
	}

	return $activity_comment_ids;
	// Delete all activity meta entries for activity items and activity comments.
}

/**
 * Based on bp_activity_post_update()
 * Allows empty activity update when media is attached.
 * It is a temporary solution, going to ask to include such functionality in core BP.
 *
 * @param array $args
 *
 * @return bool|int
 */
function mpp_activity_post_update( $args ) {
	$r = wp_parse_args( $args, array(
		'content'    => false,
		'user_id'    => bp_loggedin_user_id(),
		'error_type' => 'bool',
	) );


	if ( bp_is_user_inactive( $r['user_id'] ) ) {
		return false;
	}

	// Record this on the user's profile.
	$activity_content = $r['content'];
	$primary_link     = bp_core_get_userlink( $r['user_id'], false, true );

	/**
	 * Filters the new activity content for current activity item.
	 *
	 * @param string $activity_content Activity content posted by user.
	 */
	$add_content = apply_filters( 'bp_activity_new_update_content', $activity_content );

	/**
	 * Filters the activity primary link for current activity item.
	 *
	 *
	 * @param string $primary_link Link to the profile for the user who posted the activity.
	 */
	$add_primary_link = apply_filters( 'bp_activity_new_update_primary_link', $primary_link );

	// Now write the values.
	$activity_id = bp_activity_add( array(
		'user_id'      => $r['user_id'],
		'content'      => $add_content,
		'primary_link' => $add_primary_link,
		'component'    => buddypress()->activity->id,
		'type'         => 'activity_update',
		'error_type'   => $r['error_type'],
	) );

	// Bail on failure.
	if ( false === $activity_id || is_wp_error( $activity_id ) ) {
		return $activity_id;
	}

	/**
	 * Filters the latest update content for the activity item.
	 *
	 * @param string $r Content of the activity update.
	 * @param string $activity_content Content of the activity update.
	 */
	$activity_content = apply_filters( 'bp_activity_latest_update_content', $r['content'], $activity_content );

	// Add this update to the "latest update" usermeta so it can be fetched anywhere.
	bp_update_user_meta( bp_loggedin_user_id(), 'bp_latest_update', array(
		'id'      => $activity_id,
		'content' => $activity_content,
	) );

	/**
	 * Fires at the end of an activity post update, before returning the updated activity item ID.
	 *
	 *
	 * @param string $content Content of the activity post update.
	 * @param int $user_id ID of the user posting the activity update.
	 * @param int $activity_id ID of the activity item being updated.
	 */
	do_action( 'bp_activity_posted_update', $r['content'], $r['user_id'], $activity_id );

	return $activity_id;
}
/**
 * Based on groups_post_update() to allow empty activity when media is attached.
 *
 * @param array $args args.
 *
 * @return bool|int|WP_Error
 */
function mpp_activity_post_group_update( $args = array() ) {
	if ( ! bp_is_active( 'activity' ) ) {
		return false;
	}

	$bp = buddypress();

	$defaults = array(
		'content'    => false,
		'user_id'    => bp_loggedin_user_id(),
		'group_id'   => 0,
		'error_type' => 'bool',
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( empty( $group_id ) && ! empty( $bp->groups->current_group->id ) ) {
		$group_id = $bp->groups->current_group->id;
	}

	if ( empty( $user_id ) || empty( $group_id ) ) {
		return false;
	}

	$bp->groups->current_group = groups_get_group( $group_id );

	// Be sure the user is a member of the group before posting.
	if ( ! bp_current_user_can( 'bp_moderate' ) && ! groups_is_user_member( $user_id, $group_id ) ) {
		return false;
	}

	// Record this in activity streams.
	$activity_action  = sprintf( __( '%1$s posted an update in the group %2$s', 'buddypress' ), bp_core_get_userlink( $user_id ), '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . '">' . esc_attr( $bp->groups->current_group->name ) . '</a>' );
	$activity_content = $r['content'];

	/**
	 * Filters the action for the new group activity update.
	 *
	 *
	 * @param string $activity_action The new group activity update.
	 */
	$action = apply_filters( 'groups_activity_new_update_action', $activity_action );

	/**
	 * Filters the content for the new group activity update.
	 *
	 *
	 * @param string $activity_content The content of the update.
	 */
	$content_filtered = apply_filters( 'groups_activity_new_update_content', $activity_content );

	$activity_id = groups_record_activity( array(
		'user_id'    => $user_id,
		'action'     => $action,
		'content'    => $content_filtered,
		'type'       => 'activity_update',
		'item_id'    => $group_id,
		'error_type' => $r['error_type'],
	) );

	groups_update_groupmeta( $group_id, 'last_activity', bp_core_current_time() );

	/**
	 * Fires after posting of an Activity status update affiliated with a group.
	 *
	 *
	 * @param string $content The content of the update.
	 * @param int $user_id ID of the user posting the update.
	 * @param int $group_id ID of the group being posted to.
	 * @param bool $activity_id Whether or not the activity recording succeeded.
	 */
	do_action( 'bp_groups_posted_update', $r['content'], $user_id, $group_id, $activity_id );

	return $activity_id;
}