<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="" id="mpp-gallery-edit-form" class="mpp-form mpp-form-stacked mpp-gallery-delete-form">
<div class="mpp-notice mpp-warning">

	<p class="mpp-gallery-delete-warning"> <?php _e( 'Are you sure you want to delete this gallery? You will lose all the media!', 'mediapress' );?></p>

	<?php do_action( 'mpp_gallery_delete_form_fields' ); ?>

	<input type="checkbox" id="mpp-delete-gallery-agree" value="1" name="mpp-delete-agree" /><label for="mpp-delete-gallery-agree" class="screen-reader-text"><?php _e( 'Yes I want to delete this gallery.', 'mediapress' ); ?></label>

	<input type='hidden' name='mpp-action' value='delete-gallery' />
	<input type='hidden' name='gallery_id' value="<?php echo mpp_get_current_gallery_id() ;?>" />
	<?php wp_nonce_field( 'mpp-delete-gallery', 'mpp-nonce' );?>

	<button type="submit" class="mpp-button mpp-button-warning">
		<?php _e( 'Yes, I understand and I want to delete!', 'mediapress' );?>
	</button>
</div>

</form>
