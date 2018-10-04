<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$media = mpp_get_current_media();

if ( ! $media ) {
	return;
}

?>
<div class="mpp-lightbox-content mpp-lightbox-content-without-comment mpp-clearfix">
	<div class="mpp-lightbox-media-container">

		<?php do_action( 'mpp_before_lightbox_media', $media ); ?>

		<div class="mpp-item-meta mpp-media-meta mpp-lightbox-media-meta mpp-lightbox-media-meta-top">
			<?php do_action( 'mpp_lightbox_media_meta_top', $media ); ?>
		</div>

        <div class="mpp-lightbox-media-entry mpp-lightbox-no-comment-media-entry">
			<?php mpp_lightbox_content( $media );?>
        </div>

		<div class="mpp-item-meta mpp-media-meta mpp-lightbox-media-meta mpp-lightbox-media-meta-bottom">
			<?php do_action( 'mpp_lightbox_media_meta', $media ); ?>
		</div>

		<?php do_action( 'mpp_after_lightbox_media', $media ); ?>

	</div>

</div>
