<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}
?>
<?php while( mpp_have_media() ): mpp_the_media(); ?>

	<div class="mpp-u <?php mpp_media_class( mpp_get_media_grid_column_class() );?>">
		
		<?php do_action( 'mpp_before_media_item' ); ?>
		
		<div class='mpp-item-entry mpp-media-entry mpp-photo-entry'>
			<a href="<?php mpp_media_permalink() ;?>" <?php mpp_media_html_attributes( array( 'class' => "mpp-item-thumbnail mpp-media-thumbnail mpp-photo-thumbnail", 'mpp-data-context' => 'gallery', 'title' => mpp_get_media_title() ) ); ?>>
				<img src="<?php mpp_media_src( 'thumbnail' ) ;?>" alt="<?php mpp_media_title();?> "/>
			</a>
		</div>
		
		<div class="mpp-item-actions mpp-media-actions mpp-photo-actions">
			<?php mpp_media_action_links();?>
		</div>
				
		<?php do_action( 'mpp_after_media_item' ); ?>
	</div>

<?php endwhile; ?>