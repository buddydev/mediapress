<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

$mppq = new MPP_Cached_Media_Query( array( 'in' => mpp_activity_get_displayable_media_ids( $activity_id ) ) );

if ( $mppq->have_media() ) : ?>
	<div class="mpp-container mpp-activity-container mpp-media-list mpp-activity-media-list mpp-activity-video-list mpp-activity-video-player mpp-media-list-view-grid mpp-activity-media-list-view-grid mpp-video-view-grid mpp-activity-video-view-grid">

		<?php while ( $mppq->have_media() ) : $mppq->the_media(); ?>

			<div class="mpp-item-content mpp-activity-item-content mpp-video-content mpp-activity-video-content">
				<?php mpp_media_content(); ?>
                <a class="mpp-activity-item-title mpp-activity-video-title" href="<?php mpp_media_permalink() ?>" title="<?php echo esc_attr( mpp_get_media_title() ); ?>" data-mpp-activity-id="<?php echo $activity_id; ?>" data-mpp-media-id="<?php mpp_media_id(); ?>"><?php mpp_media_title(); ?></a>
			</div>

		<?php endwhile; ?>
		<script type='text/javascript'>
			mpp_mejs_activate(<?php echo $activity_id;?>);
		</script>
	</div>
<?php endif; ?>
<?php mpp_reset_media_data(); ?>
