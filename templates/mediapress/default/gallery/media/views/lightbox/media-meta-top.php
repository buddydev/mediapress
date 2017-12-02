<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$media = mpp_get_current_media();
?>
<div class="mpp-item-meta mpp-media-meta mpp-lightbox-media-meta mpp-lightbox-media-meta-top">
	<?php do_action( 'mpp_lightbox_media_meta_top', $media ); ?>
</div>