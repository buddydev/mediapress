<?php
/**
 * List all galleries for the current widget
 * 
 */

$query = mpp_widget_get_gallery_data( 'query' );
?>

<?php if( $query->have_galleries() ): ?>
<div class="mpp-container mpp-widget-container mpp-gallery-widget-container">
	<div class='mpp-g mpp-item-list mpp-galleries-list'>

		<?php while( $query->have_galleries() ): $query->the_gallery(); ?>

			<div class="<?php mpp_gallery_class(  'mpp-u-1-1' );?>">
				
				<?php do_action( 'mpp_before_gallery_widget_entry' ); ?>
				
				<div class="mpp-item-entry mpp-gallery-entry">
					
					<a href="<?php mpp_gallery_permalink() ;?>" <?php mpp_gallery_html_attributes( array( 'class' => "mpp-item-thumbnail mpp-gallery-cover ", 'mpp-data-context'=> 'widget' ) ); ?> >
						<img src="<?php mpp_gallery_cover_src( 'thumbnail' ) ;?>" />
					</a>
					
				</div>	

				<a href="<?php mpp_gallery_permalink() ;?>" <?php mpp_gallery_html_attributes( array( 'class' => "mpp-item-title mpp-gallery-title ", 'mpp-data-context'=> 'widget' ) ); ?>>
					<?php mpp_gallery_title() ;?>
				</a>
				
				<div class="mpp-item-actions mpp-gallery-actions">
					<?php mpp_gallery_action_links();?>
				</div>
				
				<div class="mpp-type-icon"><?php do_action( 'mpp_type_icon', mpp_get_gallery_type(), mpp_get_gallery() );?></div>
				
				<?php do_action( 'mpp_after_gallery_widget_entry' ); ?>
				
			</div>

		<?php endwhile; ?>
		
		<?php mpp_reset_gallery_data(); ?>
		
	</div>
</div>	
<?php else:?>
	<div class="mpp-notice mpp-no-gallery-notice">
		<p> <?php _ex( 'There are no galleries available!', 'No Gallery Message', 'mediapress' ); ?> 
	</div>
<?php endif;?>
