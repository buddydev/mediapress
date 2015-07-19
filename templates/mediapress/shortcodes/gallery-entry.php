<?php
/**
 * mediapress/shortcodes/gallery-entry.php
 * 
 * Single gallery entry for mpp-gallery shortcode
 */
?>
<div class="<?php mpp_gallery_class(  mpp_get_grid_column_class( mpp_shortcode_get_gallery_data( 'column' ) ) );?>">
		<div class="<?php mpp_gallery_class(  mpp_get_gallery_grid_column_class() );?>" id="mpp-gallery-<?php mpp_gallery_id();?>">

			<?php do_action( 'mpp_before_gallery_shortcode_entry' );?>

			<div class="mpp-item-entry mpp-gallery-entry">
				<a href="<?php mpp_gallery_permalink() ;?>" <?php mpp_gallery_html_attributes( array( 'class' => "mpp-item-thumbnail mpp-gallery-cover", 'mpp-data-context'=> 'shortcode' ) ); ?>>
					
					<img src="<?php mpp_gallery_cover_src( 'thumbnail' ) ;?>" />
				</a>
			</div>	

			<?php do_action( 'mpp_before_gallery_title' ); ?>

			<a href="<?php mpp_gallery_permalink() ;?>" <?php mpp_gallery_html_attributes( array( 'class' => "mpp-item-title mpp-gallery-title ", 'mpp-data-context'=> 'shortcode' ) ); ?> ><?php mpp_gallery_title() ;?></a>

			<?php do_action( 'mpp_before_gallery_actions' ); ?>	

			<div class="mpp-item-actions mpp-gallery-actions">
				<?php mpp_gallery_action_links();?>
			</div>

			<?php do_action( 'mpp_before_gallery_type_icon' ); ?>

			<div class="mpp-type-icon"><?php do_action( 'mpp_type_icon', mpp_get_gallery_type(), mpp_get_gallery() );?></div>

			<?php do_action( 'mpp_after_gallery_shortcode_entry' ); ?>
		</div>
</div>