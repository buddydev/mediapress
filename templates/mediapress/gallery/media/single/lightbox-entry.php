<div class="mpp-lightbox-content mpp-clearfix">
	<?php $media = mpp_get_media();?>
	<div class="mpp-lightbox-media-container">
		
	<?php do_action( 'mpp_before_lightbox_media', $media );?>
		
		<a href="<?php mpp_media_permalink();?>" title="<?php echo esc_attr( mpp_get_media_title() ) ;?>">
		<img src="<?php mpp_media_src() ;?>" alt="<?php echo esc_attr( mpp_get_media_title() ) ;?>" class="mpp-lightbox-single-media"/>
		</a>
		
	<?php do_action( 'mpp_after_lightbox_media', $media );?>
		
	</div>
	
	<div class="mpp-lightbox-activity-container">
		
	<?php do_action( 'mpp_before_lightbox_media_activity', $media );?>
		
	<?php mpp_locate_template( array( 'gallery/media/single/activity.php' ), true ); ?>
		
	<?php do_action( 'mpp_after_lightbox_media_activity', $media );?>
		
	</div>
	
</div>
