<?php
// Exit if the file is accessed directly over web.
// fallback view for activity media grid.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Default grid view for media items.
 *
 * Media List attached to an activity
 * This is a fallback template for new media types
 */


$mppq = new MPP_Cached_Media_Query( array( 'in' => mpp_activity_get_displayable_media_ids( $activity_id ) ) );

if ( $mppq->have_media() ) : ?>
	<div class="mpp-container mpp-activity-container mpp-media-list mpp-activity-media-list mpp-media-default-list mpp-activity-default-media-list mpp-media-default-list-view-grid mpp-activity-media-default-list-view-grid">

		<?php while ( $mppq->have_media() ) : $mppq->the_media(); ?>
            <?php $type = mpp_get_media_type();?>
            <div class="mpp-item-content mpp-activity-item-content mpp-<?php echo $type;?>-content mpp-activity-<?php echo $type;?>-content">
                <a href="<?php mpp_media_permalink(); ?>">
                    <img src="<?php mpp_media_src( 'thumbnail' ); ?>" class='mpp-attached-media-item' data-mpp-activity-id="<?php echo $activity_id; ?>" title="<?php echo esc_attr( mpp_get_media_title() ); ?>"/>
                </a>
                <a class="mpp-activity-item-title mpp-activity-<?php echo $type;?>-title" href="<?php mpp_media_permalink() ?>" title="<?php mpp_media_title(); ?>"><?php mpp_media_title(); ?></a>
            </div>
		<?php endwhile; ?>
	</div>
<?php endif; ?>
<?php mpp_reset_media_data(); ?>
