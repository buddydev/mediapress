<?php

/**
 * Media/Gallery Activity Post Form
 *
 */

?>

<form action="<?php bp_activity_post_form_action(); ?>" method="post" id="whats-new-form" name="whats-new-form" role="complementary">

	<?php do_action( 'bp_before_activity_post_form' ); ?>

	<div id="whats-new-avatar">
		<a href="<?php echo bp_loggedin_user_domain(); ?>">
			<?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
		</a>
	</div>

	<h5>
		<?php	printf( __( "Want to say Something, %s?", 'buddypress' ), bp_get_user_firstname() );
	?></h5>

	<div id="whats-new-content">
		<div id="whats-new-textarea">
			<textarea name="whats-new" id="whats-new" cols="50" rows="10"><?php if ( isset( $_GET['r'] ) ) : ?>@<?php echo esc_textarea( $_GET['r'] ); ?> <?php endif; ?></textarea>
		</div>

		<div id="whats-new-options">
			<div id="whats-new-submit">
				<input type="submit" name="mpp-whats-new-submit" id="mpp-whats-new-submit" value="<?php esc_attr_e( 'Post', 'buddypress' ); ?>" />
			</div>

			
				

			<?php if ( bp_is_active('groups') &&  bp_is_group() ) : ?>

				<input type="hidden" id="whats-new-post-object" name="whats-new-post-object" value="groups" />
				<input type="hidden" id="whats-new-post-in" name="whats-new-post-in" value="<?php bp_group_id(); ?>" />

			<?php endif; ?>
				<?php if( mpp_is_single_gallery() && !mpp_is_single_media()  ):?>
					<input type="hidden" name='mpp-item-id' id="mpp-item-id" value="<?php echo mpp_get_current_gallery_id();?>" />
					<input type="hidden" name='mpp-activity-type' id="mpp-activity-type" value="gallery" />
				<?php else:?>
						
					<input type="hidden" name='mpp-item-id' id="mpp-item-id" value="<?php echo mpp_get_current_media_id();?>" />
					<input type="hidden" name='mpp-activity-type' id="mpp-activity-type" value="media" />
				<?php endif; ?>
					
			<?php do_action( 'bp_activity_post_form_options' ); ?>

		</div><!-- #whats-new-options -->
	</div><!-- #whats-new-content -->

	<?php wp_nonce_field( 'post_update', '_wpnonce_post_update' ); ?>
	<?php do_action( 'bp_after_activity_post_form' ); ?>

</form><!-- #whats-new-form -->
