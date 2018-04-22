<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$media = mpp_get_current_media();
?>
<div class="mpp-lightbox-media-uploader-meta mpp-clearfix">
	<div class="mpp-lightbox-media-uploader-avatar">
		<a href="<?php echo bp_core_get_user_domain( mpp_get_media_creator_id() ); ?>">
			<?php echo bp_core_fetch_avatar( array(
				'item_id' => mpp_get_media_creator_id(),
				'object'  => 'user',
				'width'   => bp_core_avatar_thumb_width(),
				'height'  => bp_core_avatar_thumb_height(),
			) ); ?>
		</a>
	</div>

	<div class="mpp-lightbox-uploader-upload-details">
		<div class="mpp-lightbox-uploader-link">
			<?php echo bp_core_get_userlink( mpp_get_media_creator_id() ); ?>
		</div>
		<span class="mpp-lightbox-upload-time"><?php echo bp_core_time_since( mpp_get_media_date_created( null, 'Y-m-d H:i:s', false ) ); ?></span>
		<div class="mpp-lightbox-action-links">
			<?php do_action( 'mpp_lightbox_media_action_before_link', $media );?>
			<?php if ( mpp_user_can_edit_media( mpp_get_media_id() ) ) : ?>
                <a class="mpp-lightbox-media-action-link mpp-lightbox-edit-media-link" href="#" data-mpp-media-id="<?php mpp_media_id();?>"><?php _ex('Edit', 'lightbox edit media edit action label', 'mediapress' );?> </a>
                <a class="mpp-lightbox-media-action-link mpp-lightbox-edit-media-cancel-link" href="#" data-mpp-media-id="<?php mpp_media_id();?>"><?php _ex('Cancel', 'lightbox edit media cancel action label', 'mediapress' );?></a>
			<?php endif;?>
			<?php do_action( 'mpp_lightbox_media_action_after_link', $media );?>
        </div>
	</div>
</div><!--end of the top row -->
<?php
if ( mpp_media_has_description() ) {
	$class = 'mpp-media-visible-description';
} else {
	$class = 'mpp-media-hidden-description';
}
?>
<div class="mpp-item-description mpp-media-description mpp-lightbox-media-description <?php echo $class; ?> mpp-clearfix">
	<?php mpp_media_description(); ?>
</div>

<?php if ( mpp_user_can_edit_media( mpp_get_media_id() ) ) : ?>
	<?php mpp_locate_template( array( 'gallery/media/views/lightbox/media-edit-form.php' ), true ); ?>
<?php endif; ?>
