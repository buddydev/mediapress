<?php
/**
 * Shortcode Video Playlist
 */
$query = mpp_shortcode_get_media_data( 'query' );
if( $query->have_media() ): 
	$ids = $query->get_ids();
	
?>

<div class="mpp-item-playlist mpp-u-1-1 mpp-item-playlist-video mpp-item-playlist-video-shortcode">
<?php
	
	echo wp_playlist_shortcode( array( 'ids' => $ids, 'type' => 'video' ));

?>
</div>

<?php endif; ?>
<?php mpp_reset_media_data(); ?>