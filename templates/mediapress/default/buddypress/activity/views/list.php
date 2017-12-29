<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Activity:- Items List.
 *
 * Media List attached to an activity
 */


$mppq = new MPP_Cached_Media_Query( array( 'in' => mpp_activity_get_displayable_media_ids( $activity_id ) ) );

$ids = mpp_activity_get_attached_media_ids( $activity_id );

if ( $mppq->have_media() ) : ?>

	<ul class="mpp-container mpp-activity-container mpp-media-list mpp-activity-media-list mpp-media-list-view-list mpp-activity-media-list-view-list">
		<?php while ( $mppq->have_media() ) : $mppq->the_media(); ?>
			<li class="mpp-list-item-entry mpp-list-item-entry-<?php mpp_media_type(); ?>">
				<?php do_action( 'mpp_before_media_activity_item' ); ?>

				<a href="<?php mpp_media_permalink(); ?>" class="mpp-activity-item-title mpp-activity-<?php mpp_media_type(); ?>-title" data-mpp-activity-id="<?php echo $activity_id; ?>" data-mpp-media-id="<?php mpp_media_id(); ?>"><?php mpp_media_title(); ?></a>

				<?php do_action( 'mpp_after_media_activity_item' ); ?>
			</li>

		<?php endwhile; ?>
	</ul>
<?php endif; ?>
<?php mpp_reset_media_data(); ?>
