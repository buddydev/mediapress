<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/***
 * List Photos attched to an activity
 *
 * Media List attached to an activity
 *
 */


$mppq = new MPP_Cached_Media_Query( array( 'in' => mpp_activity_get_displayable_media_ids( $activity_id ) ) );

if ( $mppq->have_media() ) : ?>
	<div class="mpp-container mpp-activity-container mpp-media-list mpp-activity-media-list mpp-activity-photo-list mpp-media-list-view-grid mpp-photo-view-grid mpp-activity-photo-view-grid">

		<?php while ( $mppq->have_media() ) : $mppq->the_media(); ?>

			<a href="<?php mpp_media_permalink(); ?>" data-mpp-activity-id="<?php echo $activity_id; ?>" data-mpp-media-id="<?php mpp_media_id(); ?>" class="mpp-media mpp-activity-media mpp-activity-media-photo">
				<img src="<?php mpp_media_src( 'thumbnail' ); ?>" class='mpp-attached-media-item' title="<?php echo esc_attr( mpp_get_media_title() ); ?>"/>
			</a>

		<?php endwhile; ?>
	</div><!-- end of .mpp-activity-media-list -->
<?php endif; ?>
<?php mpp_reset_media_data(); ?>
