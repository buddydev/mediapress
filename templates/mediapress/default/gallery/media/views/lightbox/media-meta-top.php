<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$media = mpp_get_current_media();
ob_start();
do_action( 'mpp_lightbox_media_meta_top', $media );

$meta_top = ob_get_clean();
?>
<?php if ( $meta_top ): ?>
    <div class="mpp-item-meta mpp-media-meta mpp-lightbox-media-meta mpp-lightbox-media-meta-top">
		<?php echo $meta_top; ?>
    </div>
<?php endif; ?>