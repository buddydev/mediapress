<?php while( mpp_have_media() ): mpp_the_media(); ?>

	<div class="<?php mpp_media_class( 'mpp-gallery-item mpp-u-8-24');?>">
		
		<?php do_action( 'mpp_before_media_item' ); ?>
		
		<a href="<?php mpp_media_permalink() ;?>">
			<img src="<?php mpp_media_src('thumbnail') ;?>" class="mpp-image" />
		</a>
		
		<a href="<?php mpp_media_permalink() ;?>" class="mpp-media-title"><?php mpp_media_title() ;?></a>
		
		<div class="mpp-item-actions mpp-media-actions mpp-audio-actions">
			<?php mpp_media_action_links();?>
		</div>
		<div class="mpp-type-icon"><?php do_action( 'mpp_type_icon', mpp_get_media_type(), mpp_get_media() );?></div>
			
		<?php do_action( 'mpp_after_media_item' ); ?>
	</div>

<?php endwhile; ?>