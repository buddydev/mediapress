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
			<?php $media = mpp_get_media(); ?>
			<?php
			if ( ! mpp_is_doc_viewable( $media ) ) {
				$url   = mpp_get_media_src( '', $media );
				$class = 'mpp-no-lightbox';

			} else {
				$url   = mpp_get_media_permalink( $media );
				$class = '';
			}
			?>
            <div class="mpp-item-content mpp-activity-item-content mpp-doc-content mpp-activity-doc-content">
                <a href="<?php echo esc_url( $url ); ?>" class="mpp-media mpp-activity-media mpp-activity-media-doc <?php echo $class;?>" data-mpp-activity-id="<?php echo $activity_id; ?>" data-mpp-media-id="<?php mpp_media_id(); ?>" >
                    <img src="<?php mpp_media_src( 'thumbnail' ); ?>" class='mpp-attached-media-item' title="<?php echo esc_attr( mpp_get_media_title() ); ?>"/>
                </a>
                <a href="<?php echo esc_url( $url ); ?>" title="<?php echo esc_attr( mpp_get_media_title() ); ?>" class="mpp-activity-item-title mpp-activity-doc-title <?php echo $class;?>" data-mpp-activity-id="<?php echo $activity_id; ?>" data-mpp-media-id="<?php mpp_media_id(); ?>"><?php mpp_media_title(); ?></a>
            </div>
		<?php endwhile; ?>
	</div>
<?php endif; ?>
<?php mpp_reset_media_data(); ?>
