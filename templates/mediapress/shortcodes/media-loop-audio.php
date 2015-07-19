<?php
/**
 * mediapress/shortcodes/media-loop-audio.php
 * Audio list in shortcode grid view
 * 
 */
$query = mpp_shortcode_get_media_data( 'query' );
while( $query->have_media() ): $query->the_media(); ?>

	<div class="<?php mpp_media_class( mpp_get_grid_column_class ( mpp_shortcode_get_media_data( 'column') ) );?>">
		
		<?php do_action( 'mpp_before_media_shortcode_item' ); ?>
		<?php 

		$args = array(
			'src'		=> mpp_get_media_src(),
			'loop'		=> false,
			'autoplay'	=> false,
		);


		//$ids = mpp_get_all_media_ids();
		//echo wp_playlist_shortcode( array( 'ids' => $ids));

		?>
		
		<div class='mpp-item-entry mpp-media-entry mpp-audio-entry'>
			
		<a href="<?php mpp_media_permalink() ;?>" <?php mpp_media_html_attributes( array( 'class' => "mpp-item-thumbnail mpp-media-thumbnail mpp-audio-thumbnail", 'mpp-data-context' => 'shortcode' ) ); ?>>
		
				
				<img src="<?php mpp_media_src('thumbnail') ;?>" alt="<?php mpp_media_title();?> "/>
			</a>
		</div>
		
		<div class="mpp-item-content mpp-audio-content mpp-audio-player">
			<?php echo wp_audio_shortcode(  $args );?>
		</div>
		
		<a href="<?php mpp_media_permalink() ;?>" <?php mpp_media_html_attributes( array( 'class' => "mpp-item-title mpp-media-title mpp-audio-title", 'mpp-data-context' => 'shortcode' ) ); ?> >
			<?php mpp_media_title() ;?>
		</a>

		
		<div class="mpp-item-actions mpp-media-actions mpp-audio-actions">
			<?php mpp_media_action_links();?>
		</div>
		
		<div class="mpp-type-icon"><?php do_action( 'mpp_type_icon', mpp_get_media_type(), mpp_get_media() );?></div>
		
		<?php do_action( 'mpp_after_media_shortcode_item' ); ?>
		
	</div>
<?php endwhile; ?>
<?php mpp_reset_media_data(); ?>