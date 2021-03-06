<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

$mppq = new MPP_Cached_Media_Query( array( 'in' => mpp_activity_get_displayable_media_ids( $activity_id ) ) );

if ( $mppq->have_media() ) : ?>
	<div class="mpp-container mpp-activity-container mpp-media-list mpp-activity-media-list mpp-activity-audio-list mpp-activity-audio-player mpp-media-list-view-grid mpp-audio-view-grid mpp-activity-audio-view-grid">

		<?php while ( $mppq->have_media() ) : $mppq->the_media(); ?>
			<?php $type = mpp_get_media_type(); ?>
			<div class="mpp-item-content mpp-activity-item-content mpp-audio-content mpp-activity-audio-content" data-mpp-type="<?php echo $type;?>">
				<?php mpp_media_content(); ?>
                <a href="<?php mpp_media_permalink() ?>" title="<?php echo esc_attr( mpp_get_media_title() ); ?>" class="mpp-activity-item-title mpp-activity-audio-title" data-mpp-type="<?php echo $type;?>" data-mpp-activity-id="<?php echo $activity_id; ?>" data-mpp-media-id="<?php mpp_media_id(); ?>"><?php mpp_media_title(); ?></a>
			</div>

		<?php endwhile; ?>
		<script type='text/javascript'>
			mpp_mejs_activate(<?php echo $activity_id;?>);
		</script>
	</div>
<?php endif; ?>
<?php mpp_reset_media_data(); ?>
