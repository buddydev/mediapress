<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template specific hooks
 * Used to attach functionality to template
 */

/**
 * Show the publish to activity on mediapress edit gallery page
 */
function mpp_gallery_show_publish_gallery_activity_button() {

	if ( ! mediapress()->is_bp_active() ) {
		return;
	}

	$gallery_id = mpp_get_current_gallery_id();
	// if not a valid gallery id or no unpublished media exists, just don't show it.
	if ( ! $gallery_id || ! mpp_gallery_has_unpublished_media( $gallery_id ) ) {
		return;
	}

	$gallery = mpp_get_gallery( $gallery_id );

	$unpublished_media = mpp_gallery_get_unpublished_media( $gallery_id );
	// unpublished media count.
	$unpublished_media_count = count( $unpublished_media );

	$type = $gallery->type;

	$type_name = _n( mpp_get_type_singular_name( $type ), mpp_get_type_plural_name( $type ), $unpublished_media_count );

	// if we are here, there are unpublished media.
	?>
    <div id="mpp-unpublished-media-info">
        <p> <?php printf( __( 'You have %d %s not published to actvity.', 'mediapress' ), $unpublished_media_count, strtolower( $type_name ) ); ?>
            <span class="mpp-gallery-publish-activity"><?php mpp_gallery_publish_activity_link( $gallery_id ); ?></span>
            <span class="mpp-gallery-unpublish-activity"><?php mpp_gallery_unpublished_media_delete_link( $gallery_id ); ?></span>
        </p>
    </div>

	<?php
}
add_action( 'mpp_before_bulkedit_media_form', 'mpp_gallery_show_publish_gallery_activity_button' );

/**
 * Generate the dropzone
 *
 * @param string $context context for the dropzone.
 */
function mpp_upload_dropzone( $context ) {
	$sanitized_context = esc_attr( $context );

	$default_message = _x( '<strong>Add files</strong> Or drag and drop', 'default file brose message', 'mediapress' );

	if ( 'gallery' === $context && mpp_is_single_gallery() ) {
		$default_message = sprintf( _x( '<strong>+Add %s</strong> Or drag and drop', 'dropzone file browse message', 'mediapress' ), mpp_get_type_plural_name( mpp_get_current_gallery()->type ) );
	}

	?>
    <div id="mpp-upload-dropzone-<?php echo $sanitized_context; ?>" class="mpp-dropzone" data-context="<?php echo $sanitized_context;?>">
        <div class="dz-default dz-message">
            <button class="dz-button" type="button">
                <?php echo $default_message;?>
            </button>
        </div>
    </div>
	<?php wp_nonce_field( 'mpp-manage-gallery', '_mpp_manage_gallery_nonce' ); ?>
	<?php
    ?>
	<?php
    $show_help = apply_filters( 'mpp_show_file_upload_help', in_array( $context, array(
	    'activity',
	    'gallery',
	    'shortcode',
	    'admin'
    ) ) ) ;
    if (  $show_help ) : ?>
    <div class="mpp-dropzone-upload-help">
            <p class="mpp-uploader-allowed-file-type-info"></p>
            <?php if ( mpp_get_option('show_max_upload_file_size' ) ) : ?>
                <p class="mpp-uploader-allowed-max-file-size-info"></p>
            <?php endif; ?>
    </div>
    <?php endif;?>
<?php
}
