<?php
/**
 * Single gallery entry for mpp-gallery shortcode
 */
?>
<div class="<?php mpp_gallery_class(  mpp_get_grid_column_class( mpp_shortcode_get_gallery_data( 'column' ) ) );?>">
	<div class="mpp-item-entry mpp-gallery-entry">
		<a href="<?php mpp_gallery_permalink() ;?>" class="mpp-item-thumbnail mpp-gallery-cover">
			<img src="<?php mpp_gallery_cover_src( 'thumbnail' ) ;?>" />
		</a>
	</div>	

	<a href="<?php mpp_gallery_permalink() ;?>" class="mpp-gallery-title"><?php mpp_gallery_title() ;?></a>
	<div class="mpp-item-actions mpp-gallery-actions">
		<?php mpp_gallery_action_links();?>
	</div>
	<div class="mpp-type-icon"><?php do_action( 'mpp_type_icon', mpp_get_gallery_type(), mpp_get_gallery() );?></div>
</div>