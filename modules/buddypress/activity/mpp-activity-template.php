<?php
/**
 * Templates injecting to activity
 *
 * @package mediapress
 */

// No direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Add various upload icons/buttons to activity post form
 */
function mpp_activity_upload_buttons() {

	$component    = mpp_get_current_component();
	$component_id = mpp_get_current_component_id();

	// If activity upload is disabled or the user is not allowed to upload to current component, don't show.
	if ( ! mpp_is_activity_upload_enabled( $component ) || ! mpp_user_can_upload( $component, $component_id ) ) {
		return;
	}

	// if we are here, the gallery activity stream upload is enabled,
	// let us see if we are on user profile and gallery is enabled.
	if ( ! mpp_is_enabled( $component, $component_id ) ) {
		return;
	}
	// if we are on group page and either the group component is not enabled or gallery is not enabled for current group, do not show the icons.
	if ( function_exists( 'bp_is_group' ) && bp_is_group() && ( ! mpp_is_active_component( 'groups' ) || ! ( function_exists( 'mpp_group_is_gallery_enabled' ) && mpp_group_is_gallery_enabled() ) ) ) {
		return;
	}
	// for now, avoid showing it on single gallery/media activity stream.
	if ( mpp_is_single_gallery() || mpp_is_single_media() ) {
		return;
	}

	?>
	<div id="mpp-activity-upload-buttons" class="mpp-upload-buttons">
		<?php do_action( 'mpp_before_activity_upload_buttons' ); // allow to add more type.  ?>

		<?php if ( mpp_is_active_type( 'photo' ) && mpp_component_supports_type( $component, 'photo' ) ) : ?>
			<a href="#" id="mpp-photo-upload" data-media-type="photo" title="<?php _e( 'Upload photo', 'mediapress' ) ; ?>">
                <img src="<?php echo mpp_get_asset_url( 'assets/images/media-button-photo.png', 'media-photo-icon' ); ?>"/>
            </a>
		<?php endif; ?>

		<?php if ( mpp_is_active_type( 'audio' ) && mpp_component_supports_type( $component, 'audio' ) ) : ?>
			<a href="#" id="mpp-audio-upload" data-media-type="audio" title="<?php _e( 'Upload audio', 'mediapress' ) ; ?>">
                <img src="<?php echo mpp_get_asset_url( 'assets/images/media-button-audio.png', 'media-audio-icon' ); ?>"/>
            </a>
		<?php endif; ?>

		<?php if ( mpp_is_active_type( 'video' ) && mpp_component_supports_type( $component, 'video' ) ) : ?>
			<a href="#" id="mpp-video-upload" data-media-type="video" title="<?php _e( 'Upload video', 'mediapress' ) ; ?>">
                <img src="<?php echo mpp_get_asset_url( 'assets/images/media-button-video.png', 'media-video-icon' ) ?>"/>
            </a>
		<?php endif; ?>

		<?php if ( mpp_is_active_type( 'doc' ) && mpp_component_supports_type( $component, 'doc' ) ) : ?>
			<a href="#" id="mpp-doc-upload" data-media-type="doc" title="<?php _e( 'Upload document', 'mediapress' ) ; ?>">
                <img src="<?php echo mpp_get_asset_url( 'assets/images/media-button-doc.png', 'media-doc-icon' ); ?>" />
            </a>
		<?php endif; ?>

		<?php do_action( 'mpp_after_activity_upload_buttons' ); // allow to add more type.  ?>

	</div>
	<?php
}
// Add to activity post form.
add_action( 'bp_after_activity_post_form', 'mpp_activity_upload_buttons' );

/**
 * Add dropzone/feedback/uploaded media list for activity
 */
function mpp_activity_dropzone() {
	?>
    <div id="mpp-activity-media-new-upload-container" class="mpp-new-media-container mpp-activity-new-media-container mpp-new-media-container-inactive"><!-- mediapress upload container -->
            <a href="#" class="mpp-upload-container-close" title="<?php esc_attr_e('Close', 'mediapress');?>"><span>x</span></a>
        <!-- append uploaded media here -->
        <div id="mpp-uploaded-media-list-activity" class="mpp-uploading-media-list">
            <ul></ul>
        </div>

        <div class="mpp-media-upload-container">
		    <?php do_action( 'mpp_after_activity_upload_medialist' ); ?>

		    <?php if ( mpp_is_file_upload_enabled( 'activity' ) ): ?>
                <!-- drop files here for uploading -->
			    <?php mpp_upload_dropzone( 'activity' ); ?>
			    <?php do_action( 'mpp_after_activity_upload_dropzone' ); ?>
                <!-- show any feedback here -->
                <div id="mpp-upload-feedback-activity" class="mpp-feedback">
                    <ul></ul>
                </div>
		    <?php endif; ?>
            <input type='hidden' name='mpp-context' class='mpp-context' value="activity"/>
		    <?php do_action( 'mpp_after_activity_upload_feedback' ); ?>
        </div>

	    <?php if ( mpp_is_remote_enabled( 'activity' ) ) : ?>
            <!-- remote media -->
            <div class="mpp-remote-media-container">
                <div class="mpp-feedback mpp-remote-media-upload-feedback">
                    <ul></ul>
                </div>
                <div class="mpp-remote-add-media-row mpp-remote-add-media-row-activity">
                    <input type="text" placeholder="<?php _e( 'Enter a link', 'mediapress' );?>" value="" name="mpp-remote-media-url" id="mpp-remote-media-url" class="mpp-remote-media-url"/>
                    <button id="mpp-add-remote-media" class="mpp-add-remote-media"><?php _e( '+Add', 'mediapress' ); ?></button>
                </div>

			    <?php wp_nonce_field( 'mpp_add_media', 'mpp-remote-media-nonce' ); ?>
            </div>
            <!-- end of remote media -->
	    <?php endif;?>

    </div><!-- end of mediapress form container -->
	<?php
}
add_action( 'bp_after_activity_post_form', 'mpp_activity_dropzone' );

/**
 * Format activity action for 'mpp_media_upload' activity type.
 *
 * @param string $action activity action.
 * @param BP_Activity_Activity $activity Activity object.
 *
 * @return string
 */
function mpp_format_activity_action_media_upload( $action, $activity ) {

	$userlink = mpp_get_user_link( $activity->user_id );

	$media_id = mpp_activity_get_media_id( $activity->id );

	$media_ids = mpp_activity_get_attached_media_ids( $activity->id );

	if ( ! empty( $media_ids ) ) {
		$media_id = $media_ids[0];
	}

	$gallery_id = mpp_activity_get_gallery_id( $activity->id );

	if ( ! $media_id && ! $gallery_id ) {
		return $action; // not a gallery activity, no need to proceed further.
	}

	$media   = mpp_get_media( $media_id );
	$gallery = mpp_get_gallery( $gallery_id );

	if ( ! $media && ! $gallery ) {
		return $action;
	}

	// is a type specified?
	$activity_type = mpp_activity_get_activity_type( $activity->id );

	$skip = false;

	if ( $activity_type ) {
		if ( in_array( $activity_type, array( 'edit_gallery', 'add_media' ) ) ) {
			// 'create_gallery',
			$skip = true;
		}
	}

	// there us still a chance for improvement,
	// we should dynamically generate the action instead for the above actions too.
	if ( $skip ) {
		return $action;
	}

	// on uploads activity, if it contains content, do not modify action.
	if (  'media_upload' === $activity_type && $activity->content ) {
		return $action;
	}

	if ( 'media_upload' === $activity_type ) {

		$media_count = count( $media_ids );
		$media_id    = current( $media_ids );

		$type = $gallery->type;

		/**
		 * @todo add better support for plural
		 */
		// we need the type plural in case of multi. nee to change in future.
		$type = _n( strtolower( mpp_get_type_singular_name( $type ) ), strtolower( mpp_get_type_plural_name( $type ) ), $media_count ); // photo vs photos etc.

		$action = sprintf( __( '%s added %d new %s', 'mediapress' ), $userlink, $media_count, $type );

		// allow modules to filter the action and change the message.
		$action = apply_filters( 'mpp_activity_action_media_upload', $action, $activity, $media_id, $media_ids, $gallery );
	} elseif ( 'media_comment' === $activity_type ) {

		if ( mpp_is_single_media() ) {
			$action = sprintf( __( '%s', 'mediapress' ), $userlink );
		} else {
			$action = sprintf( __( "%s commented on %s's %s", 'mediapress' ), $userlink, mpp_get_user_link( $media->user_id ), strtolower( mpp_get_type_singular_name( $media->type ) ) ); //brajesh singh commented on @mercime's photo
		}
	} elseif ( 'gallery_comment' === $activity_type ) {

		if ( mpp_is_single_gallery() ) {
			$action = sprintf( '%s', $userlink );
		} else {
			$action = sprintf( __( "%s commented on %s's <a href='%s'>%s gallery</a>", 'mediapress' ), $userlink, mpp_get_user_link( $gallery->user_id ), mpp_get_gallery_permalink( $gallery ), strtolower( mpp_get_type_singular_name( $gallery->type ) ) );
		}
	} elseif ( 'create_gallery' === $activity_type ) {
		$action = sprintf( __( '%s created a %s <a href="%s">gallery</a>', 'mediapress' ), $userlink, strtolower( mpp_get_type_singular_name( $gallery->type ) ), mpp_get_gallery_permalink( $gallery ) );
	} else {
		$action = sprintf( '%s', $userlink );
	}

	return apply_filters( 'mpp_format_activity_action_media_upload', $action, $activity, $media_id, $media_ids );
}


// Activity comments

/* Activity comments*/
/**
 * Inject css class to allow us reposition buttons.
 *
 * @param string $classes css classes list.
 *
 * @return string
 */
function mpp_filter_activity_item_class( $classes ) {

	if ( bp_activity_can_comment() ) {
		$classes .= ' mpp-activity-commentable-item';
	}

	return $classes;
}

add_filter( 'bp_get_activity_css_class', 'mpp_filter_activity_item_class' );
/**
 * Add various upload icons/buttons to activity post form
 */
function mpp_activity_comment_upload_buttons() {

	if ( ! bp_activity_can_comment() ) {
		return;
	}

	$component    = mpp_get_current_component();
	$component_id = mpp_get_current_component_id();

	// If activity upload is disabled or the user is not allowed to upload to current component, don't show.
	if ( ! mpp_is_activity_comment_upload_enabled( $component ) || ! mpp_user_can_upload( $component, $component_id ) ) {
		return;
	}

	// if we are here, the gallery activity stream upload is enabled,
	// let us see if we are on user profile and gallery is enabled.
	if ( ! mpp_is_enabled( $component, $component_id ) ) {
		return;
	}
	// if we are on group page and either the group component is not enabled or gallery is not enabled for current group, do not show the icons.
	if ( function_exists( 'bp_is_group' ) && bp_is_group() && ( ! mpp_is_active_component( 'groups' ) || ! ( function_exists( 'mpp_group_is_gallery_enabled' ) && mpp_group_is_gallery_enabled() ) ) ) {
		return;
	}
	// for now, avoid showing it on single gallery/media activity stream.
	if ( mpp_is_single_gallery() || mpp_is_single_media() ) {
		return;
	}

	?>
    <div class="mpp-activity-comment-upload-buttons" data-activity-id="<?php bp_activity_id();?>">
		<?php do_action( 'mpp_before_activity_comment_upload_buttons' ); // allow to add more type.  ?>

		<?php if ( mpp_is_active_type( 'photo' ) && mpp_component_supports_type( $component, 'photo' ) ) : ?>
            <a href="#" class="mpp-photo-upload" data-media-type="photo" title="<?php _e( 'Upload photo', 'mediapress' ) ; ?>">
                <img src="<?php echo mpp_get_asset_url( 'assets/images/media-button-photo.png', 'media-photo-icon' ); ?>"/>
            </a>
		<?php endif; ?>

		<?php if ( mpp_is_active_type( 'audio' ) && mpp_component_supports_type( $component, 'audio' ) ) : ?>
            <a href="#" class="mpp-audio-upload" data-media-type="audio" title="<?php _e( 'Upload audio', 'mediapress' ) ; ?>">
                <img src="<?php echo mpp_get_asset_url( 'assets/images/media-button-audio.png', 'media-audio-icon' ); ?>"/>
            </a>
		<?php endif; ?>

		<?php if ( mpp_is_active_type( 'video' ) && mpp_component_supports_type( $component, 'video' ) ) : ?>
            <a href="#" class="mpp-video-upload" data-media-type="video" title="<?php _e( 'Upload video', 'mediapress' ) ; ?>">
                <img src="<?php echo mpp_get_asset_url( 'assets/images/media-button-video.png', 'media-video-icon' ) ?>"/>
            </a>
		<?php endif; ?>

		<?php if ( mpp_is_active_type( 'doc' ) && mpp_component_supports_type( $component, 'doc' ) ) : ?>
            <a href="#" class="mpp-doc-upload" data-media-type="doc" title="<?php _e( 'Upload document', 'mediapress' ) ; ?>">
                <img src="<?php echo mpp_get_asset_url( 'assets/images/media-button-doc.png', 'media-doc-icon' ); ?>" />
            </a>
		<?php endif; ?>

		<?php do_action( 'mpp_after_activity_comment_upload_buttons' ); // allow to add more type.  ?>

    </div>
	<?php
}

add_filter( 'bp_nouveau_get_submit_button', function ($args) {

    if( ! isset( $args['activity-new-comment'])) {
        return $args;
    }
    $args['activity-new-comment']['before'] = 'bp_nouveau_before_activity_new_comment';
    return $args;
});
add_action( 'bp_nouveau_before_activity_new_comment', 'mpp_activity_comment_upload_buttons' );

// for legacy template pack.
add_action( 'bp_activity_entry_comments', function () {

	if ( function_exists( 'bp_nouveau' ) ) {
		return;
	}
	mpp_activity_comment_upload_buttons();
} );

/* Keep a copy of dom for copying and creating dropzone*/
/**
 * Add dropzone/feedback/uploaded media list for activity
 */
function mpp_activity_comment_dropzone_body() {
    $activity_id = bp_get_activity_id();
	?>
    <div id="mpp-activity-comment-media-new-upload-container-<?php echo esc_attr( $activity_id ); ?>" class="mpp-new-media-container mpp-activity-comment-new-media-container mpp-new-media-container-inactive"><!-- mediapress upload container -->
        <a href="#" class="mpp-upload-container-close" title="<?php esc_attr_e('Close', 'mediapress');?>" data-activity-id="<?php echo esc_attr( $activity_id ); ?>"><span>x</span></a>
        <!-- append uploaded media here -->
        <div id="mpp-uploaded-media-list-activity-comment" class="mpp-uploading-media-list">
            <ul></ul>
        </div>

        <div class="mpp-media-upload-container">
	        <?php do_action( 'mpp_after_activity_comment_upload_medialist' ); ?>

			<?php if ( mpp_is_file_upload_enabled( 'activity' ) ): ?>
                <!-- drop files here for uploading -->
				<?php mpp_upload_dropzone( 'activity-comment-'. $activity_id ); ?>
				<?php do_action( 'mpp_after_activity_comment_upload_dropzone' ); ?>
                <!-- show any feedback here -->
                <div id="mpp-upload-feedback-activity-comment" class="mpp-feedback">
                    <ul></ul>
                </div>
			<?php endif; ?>
            <input type='hidden' name='mpp-context' class='mpp-context' value="activity"/>
	        <?php do_action( 'mpp_after_activity_comment_upload_feedback' ); ?>
        </div>

    </div><!-- end of mediapress form container -->
	<?php
}
add_action( 'bp_nouveau_before_activity_new_comment', 'mpp_activity_comment_dropzone_body' );

// for legacy.
add_action( 'bp_activity_entry_comments', function () {

	if ( function_exists( 'bp_nouveau' ) ) {
		return;
	}
	mpp_activity_comment_dropzone_body();
} );