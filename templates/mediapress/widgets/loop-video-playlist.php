<?php
/**
 * Shortcode Video Playlist
 */
$query = mpp_widget_get_media_data( 'query' );
if( $query->have_media() ): 
	$ids = $query->get_ids();
	
?>

<div class="mpp-u-1-1 mpp-item-playlist  mpp-item-playlist-video mpp-item-playlist-video-widget">
<?php
	
	echo wp_playlist_shortcode( array( 'ids' => $ids, 'type' => 'video' ));

?>
</div>
<?php mpp_reset_media_data(); ?>
<?php endif; ?>
