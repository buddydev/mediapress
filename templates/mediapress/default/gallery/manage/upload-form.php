<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mpp-media-upload-container"><!-- mediapress upload container -->
    <!-- append uploaded media here -->
    <div id="mpp-uploaded-media-list-gallery" class="mpp-uploading-media-list">
        <ul>
        </ul>
    </div>

	<?php do_action( 'mpp_after_gallery_upload_medialist' ); ?>

    <!-- drop files here for uploading -->
	<?php mpp_upload_dropzone( 'gallery' ); ?>
	<?php do_action( 'mpp_after_gallery_upload_dropzone' ); ?>

    <!-- show any feedback here -->
    <div id="mpp-upload-feedback-gallery" class="mpp-feedback">
        <ul></ul>
    </div>

    <?php do_action( 'mpp_after_gallery_upload_feedback' ); ?>
    <input type='hidden' name='mpp-context' id='mpp-context' class="mpp-context" value='gallery'/>
    <input type='hidden' name='mpp-upload-gallery-id' id='mpp-upload-gallery-id' value="<?php echo mpp_get_current_gallery_id(); ?>"/>
    <?php if ( mpp_is_remote_enabled( 'gallery' ) ) : ?>
    <!-- remote media -->
    <div class="mpp-remote-media-container">
        <div class="mpp-feedback mpp-remote-media-upload-feedback">
            <ul></ul>
        </div>
        <div class="mpp-remote-add-media-row">
            <input type="text" placeholder="<?php _e( 'Enter a link', 'mediapress' );?>" value="" name="mpp-remote-media-url" id="mpp-remote-media-url" class="mpp-remote-media-url"/>
            <button id="mpp-add-remote-media" class="mpp-add-remote-media"><?php _e( '+Add', 'mediapress' ); ?></button>
        </div>

		<?php wp_nonce_field( 'mpp_add_media', 'mpp-remote-media-nonce' ); ?>
    </div>
    <!-- end of remote media -->
    <?php endif;?>

</div><!-- end of mediapress form container -->