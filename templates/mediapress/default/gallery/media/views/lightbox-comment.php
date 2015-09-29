<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}
$media = mpp_get_current_media();
?>
<div class="mpp-lightbox-content mpp-clearfix">
	<?php $media = mpp_get_media();?>
	<div class="mpp-lightbox-media-container">
		
	<?php do_action( 'mpp_before_lightbox_media', $media );?>
		
		<a href="<?php mpp_media_permalink();?>" title="<?php echo esc_attr( mpp_get_media_title() ) ;?>">
		<img src="<?php mpp_media_src() ;?>" alt="<?php echo esc_attr( mpp_get_media_title() ) ;?>" class="mpp-lightbox-single-media"/>
		</a>
		
		<?php do_action( 'mpp_after_lightbox_media', $media );?>
		<div class="mpp-lightbox-media-meta">
			<?php do_action( 'mpp_lightbox_media_meta', $media ); ?> 
		</div>
	</div>
	
	<div class="mpp-lightbox-activity-container">
		
	<?php do_action( 'mpp_before_lightbox_media_activity', $media );?>
		<?php mpp_locate_template( array( 'gallery/media/views/lightbox/activity.php' ), true ); ?>
		
	<?php do_action( 'mpp_after_lightbox_media_activity', $media );?>
		
	</div>
	
</div>
