<?php
/**
 * mediapress/shortcodes/gallery/audio-playlist.php
 * Single Audio Gallery Playlist View
 * 
 */
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

?>
<div class="mpp-item-playlist mpp-audio-playlist mpp-shortcode-audio-playlist mpp-u-1-1">
	
	<?php do_action( 'mpp_before_audio_shortode_playlist' ); ?>
	
<?php
	$args = mpp_shortcode_get_media_data( 'shortcode_args' );
	
	$ids = mpp_get_all_media_ids( array( 'gallery_id' => $args['gallery_id'] ) );
	
	echo wp_playlist_shortcode( array( 'ids' => $ids ) );

?>
		
<?php do_action( 'mpp_after_audio_shortode_playlist' ); ?>
	
</div>