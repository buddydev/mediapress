<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<a href='#' id='mpp-reload-add-media-tab' class='mpp-reload' title="<?php _e( 'Reload add media panel', 'mediapress' );?>"><span class="dashicons dashicons-update"></span><?php _e( 'Reload', 'mediapress' );?></a>
<div class="mpp-media-upload-container"><!-- mediapress upload container -->
<!-- append uploaded media here -->
<div id="mpp-uploaded-media-list-admin" class="mpp-uploading-media-list">
	<ul> 
		<?php
		
			$mppq = mediapress()->the_media_query; //new MPP_Media_Query( array( 'gallery_id' => $gallery_id, 'per_page' => -1, 'nopaging' => true ) );
		?>	
		<?php while ( $mppq->have_media() ) : $mppq->the_media(); ?>

			<li id="mpp-uploaded-media-item-<?php mpp_media_id(); ?>" class="<?php mpp_media_class( 'mpp-uploaded-media-item' ); ?>" data-media-id="<?php mpp_media_id(); ?>">
				<img src="<?php mpp_media_src( 'thumbnail' ); ?>">
				<a href='#' class='mpp-delete-uploaded-media-item'>x</a>
			</li>
		<?php endwhile; ?>
		<?php wp_reset_query(); ?>
		<?php mpp_reset_media_data();// gallery_data(); ?>
		<?php // wp_reset_postdata();?>
	</ul>
</div>
    <input type="hidden" name="mpp-context" value="admin" class="mpp-context"/>
<!-- drop files here for uploading -->
<?php mpp_upload_dropzone( 'admin' );?>
<!-- show any feedback here -->
<div id="mpp-upload-feedback-admin" class="mpp-feedback">
	<ul> </ul>
</div>
    <input type='hidden' name='mpp-upload-gallery-id' id='mpp-upload-gallery-id' value="<?php echo mpp_get_current_gallery_id(); ?>"/>
	<?php if ( mpp_is_remote_enabled( 'admin' ) ) : ?>
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