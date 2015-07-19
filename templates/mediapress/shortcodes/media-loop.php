<?php
/**
 * mediapress/shortcodes/media-loop.php
 * Default media loop to list the media when using shortcode
 * It is a fall back templ;ate if a file with mediapress/shortcodes/media-loop-{$current_gallery_type}.php is not found
 * 
 */
$query = mpp_shortcode_get_media_data('query' );

while( $query->have_media() ): $query->the_media(); ?>

	<div class="mpp-u <?php mpp_media_class( mpp_get_grid_column_class ( mpp_shortcode_get_media_data( 'column') ) );?>">
	
		<?php do_action( 'mpp_before_media_shortcode_item' ); ?>
	
		<div class='mpp-item-entry mpp-media-entry'>
			
			<a href="<?php mpp_media_permalink() ;?>" <?php mpp_media_html_attributes( array( 'class' => "mpp-item-thumbnail mpp-media-thumbnail", 'mpp-data-context' => 'shortcode' ) ); ?>>
		
				<img src="<?php mpp_media_src('thumbnail') ;?>" alt="<?php mpp_media_title();?> "/>
			</a>
		
		</div>		
		
		<a href="<?php mpp_media_permalink() ;?>" <?php mpp_media_html_attributes( array( 'class' => "mpp-item-title mpp-media-title ", 'mpp-data-context' => 'shortcode' ) ); ?> >
			<?php mpp_media_title() ;?>
		</a>
		
		<div class="mpp-item-actions mpp-media-actions mpp-photo-actions">
			<?php mpp_media_action_links();?>
		</div>
	
		<?php do_action( 'mpp_after_media_shortcode_item' ); ?>
	
	</div>

<?php endwhile; ?>

<?php mpp_reset_media_data(); ?>