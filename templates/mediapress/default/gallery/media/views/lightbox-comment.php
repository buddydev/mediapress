<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$media = mpp_get_current_media();
?>
<div class="mpp-lightbox-content mpp-lightbox-with-comment-content mpp-clearfix" id="mpp-lightbox-media-<?php mpp_media_id(); ?>">

    <div class="mpp-lightbox-media-container mpp-lightbox-with-comment-media-container">

		<?php do_action( 'mpp_before_lightbox_media', $media ); ?>

        <?php mpp_locate_template( array( 'gallery/media/views/lightbox/media-meta-top.php' ), true ); ?>

        <div class="mpp-lightbox-media-entry mpp-lightbox-with-comment-media-entry">
	        <?php mpp_lightbox_content( $media );?>
        </div>

	    <?php mpp_locate_template( array( 'gallery/media/views/lightbox/media-meta-bottom.php' ), true ); ?>

        <?php do_action( 'mpp_after_lightbox_media', $media ); ?>
    </div> <!-- end of media container -->

    <div class="mpp-lightbox-activity-container">

        <?php mpp_locate_template( array( 'gallery/media/views/lightbox/media-info.php' ), true ); ?>

        <div class="mpp-lightbox-item-meta-activities mpp-lightbox-item-meta-activities-top">
			<?php do_action( 'mpp_before_lightbox_media_activity', $media ); ?>
        </div>

		<?php mpp_locate_template( array( 'gallery/media/views/lightbox/activity.php' ), true ); ?>

        <div class="mpp-lightbox-item-meta-activities mpp-lightbox-item-meta-activities-bottom">
	        <?php do_action( 'mpp_after_lightbox_media_activity', $media ); ?>
        </div>
    </div><!-- end of right panel -->

</div> <!-- end of lightbox content -->
