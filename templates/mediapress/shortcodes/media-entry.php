<?php
/**
 * mediapress/shortcodes/media-entry.php
 */
?>
<div class="mpp-media-item mpp-shortcode-media-item" id="mpp-shortcode-media-<?php mpp_media_id();?>">

		<?php do_action( 'mpp_before_media_item' ); ?>
		
		<a href="<?php mpp_media_permalink() ;?>" <?php mpp_media_html_attributes( array( 'class' => "mpp-item-thumbnail mpp-media-thumbnail", 'mpp-data-context' => 'shortcode' ) ); ?>>
		
			<img src="<?php mpp_media_src( 'thumbnail' ) ;?>" class="mpp-image" />
		</a>
		
		<a href="<?php mpp_media_permalink() ;?>" <?php mpp_media_html_attributes( array( 'class' => "mpp-item-title mpp-media-title ", 'mpp-data-context' => 'shortcode' ) ); ?>>
			<?php mpp_media_title() ;?>
		</a>
		
		<div class="mpp-item-actions mpp-media-actions mpp-audio-actions">
			<?php mpp_media_action_links();?>
		</div>
		
		<div class="mpp-type-icon"><?php do_action( 'mpp_type_icon', mpp_get_media_type(), mpp_get_media() );?></div>
			
		<?php do_action( 'mpp_after_media_item' ); ?>
</div>