<?php
/**
 * 
 */
$query = mpp_shortcode_get_media_data('query' );
while( $query->have_media() ): $query->the_media(); ?>

<div class="mpp-u <?php mpp_media_class( mpp_get_grid_column_class ( mpp_shortcode_get_media_data( 'column') ) );?>">
		<div class='mpp-item-entry mpp-media-entry mpp-photo-entry'>
			<a href="<?php mpp_media_permalink() ;?>" class="mpp-item-thumbnail mpp-media-thumbnail mpp-photo-thumbnail">
				<img src="<?php mpp_media_src('thumbnail') ;?>" alt="<?php mpp_media_title();?> "/>
			</a>
		</div>		
		<a href="<?php mpp_media_permalink() ;?>" class="mpp-item-title mpp-media-title mpp-photo-title"><?php mpp_media_title() ;?></a>
		<div class="mpp-item-actions mpp-media-actions mpp-photo-actions">
			<?php mpp_media_action_links();?>
		</div>
	</div>

<?php endwhile; ?>

<?php mpp_reset_media_data(); ?>