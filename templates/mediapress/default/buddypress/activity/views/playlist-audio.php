<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mpp-container mpp-activity-container mpp-media-list mpp-activity-media-list mpp-activity-audio-list mpp-media-list-view-playlist mpp-audio-playlist mpp-activity-audio-playlist">
	<?php
	$ids = mpp_activity_get_displayable_media_ids( $activity_id );
	// if there is only one media, use the poster too.
	if ( count( $ids ) == 1 ) {
		$ids   = array_pop( $ids );
		$media = mpp_get_media( $ids );
		$args  = array(
			'src'    => mpp_get_media_src( '', $media ),
			'poster' => mpp_get_media_src( 'thumbnail', $media ),

		);
		echo wp_audio_shortcode( $args );

	} else {
		// show playlist, should we use the gallery cover as poster?
		echo wp_playlist_shortcode( array( 'ids' => $ids ) );

	}
	?>
	<script type='text/javascript'>
		mpp_mejs_activate(<?php echo $activity_id;?>);
	</script>
</div>
