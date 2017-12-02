<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$media = mpp_get_current_media();
?>
<div class="mpp-item-meta mpp-media-meta mpp-lightbox-media-meta mpp-lightbox-media-meta-bottom">

	<div class="mpp-media-title-info mpp-lightbox-media-title-info mpp-lightbox-media-title-info-bottom">
		<?php mpp_media_title( $media ); ?>
	</div>
	<?php do_action( 'mpp_lightbox_media_meta', $media ); ?>
</div>
