<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Attachment in single media comment
 * This is a fallback template for new media types
 */

$mppq = new MPP_Cached_Media_Query( array( 'in' => (array) mpp_activity_get_media_id( $activity_id ) ) );

if ( $mppq->have_media() ) : ?>
	<div class="mpp-container mpp-media-list mpp-activity-comment-media-list mpp-activity-comment-photo-list">

		<?php while ( $mppq->have_media() ) : $mppq->the_media(); ?>

            <?php if ( mpp_user_can_view_media( mpp_get_media_id() ) ) : ?>

                <div class="<?php mpp_media_class( 'mpp-activity-comment-media-entry mpp-activity-comment-media-entry-photo' ); ?>" id="mpp-activity-comment-media-entry-<?php mpp_media_id(); ?>">

                    <a href="<?php mpp_media_permalink(); ?>" title="<?php echo esc_attr( mpp_get_media_title() ); ?>" data-mpp-activity-id="<?php echo $activity_id; ?>" data-mpp-media-id="<?php mpp_media_id(); ?>" class="mpp-media mpp-activity-comment-media mpp-activity-comment-photo">
                        <img src="<?php mpp_media_src( 'thumbnail' ); ?>" class='mpp-attached-media-item' />
                    </a>

                </div>

            <?php endif; ?>

		<?php endwhile; ?>
	</div>
<?php endif; ?>
<?php mpp_reset_media_data(); ?>
