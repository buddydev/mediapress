<?php
/**
 * Shortcode Audio Playlist
 */
$query = mpp_shortcode_get_media_data('query' );
$ids = $query->get_ids();
if( $query->have_media() ): ?>
	<div class="mpp-item-playlist mpp-u-1-1">
	<?php
		
		echo wp_playlist_shortcode( array( 'ids' => $ids));

	?>
	</div>
<?php endif;?> 
<?php mpp_reset_media_data(); ?>