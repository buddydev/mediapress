<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}
?>

<!-- append uploaded media here -->
<div id="mpp-gallery-media-list" class="mpp-uploading-media-list">
	<ul> 

	</ul>
</div>
<?php do_action( 'mpp_after_gallery_upload_medialist' );?>		
<!-- drop files here for uploading -->
<div id="mpp-gallery-dropzone" class="mpp-dropzone">
	<button id="mpp-add-gallery-media"><?php _e( 'Add media', 'mediapress' );?></button>
</div>
<?php do_action( 'mpp_after_gallery_upload_dropzone' );?>
<!-- show any feedback here -->
<div id="mpp-gallery-upload-feedback" class="mpp-feedback">
	<ul> </ul>
</div>
<?php do_action( 'mpp_after_gallery_upload_feedback' );?>
<input type='hidden' name='mpp-context' id='mpp-context' value='gallery' />
<input type='hidden' name='mpp-upload-gallery-id' id='mpp-upload-gallery-id' value="<?php echo mpp_get_current_gallery_id() ;?>" />