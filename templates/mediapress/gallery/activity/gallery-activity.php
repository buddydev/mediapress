<?php
/**
 * Copy of BuddyPress - Activity Loop to show gallery loop
 *
 * 
 *
 * @package mediapress
 * @subpackage base
 */
if( !mpp_get_option( 'enable_gallery_comment' ) )
	return;

?>
<?php do_action( 'bp_before_activity_loop' ); ?>
<div class="activity mpp-media-activity" id="mpp-media-activity-list">
	<?php
		if( is_user_logged_in() && mpp_gallery_user_can_comment( mpp_get_current_gallery_id() ) ) :?>
			<?php mpp_locate_template( array('gallery/activity/post-form.php'), true ) ;?>

	<?php endif;?>
	
	<?php if ( mpp_gallery_has_activity( array( 'gallery_id' => mpp_get_gallery_id() ) ) ) : ?>

		<?php /* Show pagination if JS is not enabled, since the "Load More" link will do nothing */ ?>
		<noscript>
			<div class="pagination">
				<div class="pag-count"><?php bp_activity_pagination_count(); ?></div>
				<div class="pagination-links"><?php bp_activity_pagination_links(); ?></div>
			</div>
		</noscript>

		<?php if ( empty( $_POST['page'] ) ) : ?>

			<ul id="activity-stream" class="activity-list item-list">

		<?php endif; ?>

		<?php while ( bp_activities() ) : bp_the_activity(); ?>

			<?php bp_locate_template( array( 'activity/entry.php' ), true, false ); ?>

		<?php endwhile; ?>

		<?php if ( bp_activity_has_more_items() ) : ?>

			<li class="load-more">
				<a href="#more"><?php _e( 'Load More', 'buddypress' ); ?></a>
			</li>

		<?php endif; ?>

		<?php if ( empty( $_POST['page'] ) ) : ?>

			</ul>
		<?php endif; ?>


	<?php endif; ?>

	<?php do_action( 'bp_after_activity_loop' ); ?>

	<?php if ( empty( $_POST['page'] ) ) : ?>

		<form action="" name="activity-loop-form" id="activity-loop-form" method="post">

			<?php wp_nonce_field( 'activity_filter', '_wpnonce_activity_filter' ); ?>

		</form>

	<?php endif; ?>
</div>
