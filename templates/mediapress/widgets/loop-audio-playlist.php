<?php
/**
 * Shortcode Audio Playlist
 */
$query = mpp_widget_get_media_data('query' );
$ids = $query->get_ids();
if( $query->have_media() ): ?>
	<div class="mpp-u-1-1 mpp-item-playlist  mpp-item-playlist-audio mpp-item-playlist-audio-widget">
	<?php
		
		echo wp_playlist_shortcode( array( 'ids' => $ids));

	?>
	</div>
<?php endif;?> 
<?php mpp_reset_media_data(); ?>