<?php
/**
 * Single audio view
 */
$media = mpp_get_current_media();
if ( ! $media ) {
	return '';
}

$args = array(
	'src'      => mpp_get_media_src(),
	'loop'     => false,
	'autoplay' => false,
);

echo wp_audio_shortcode( $args );
?>
<script type='text/javascript'>
    mpp_mejs_activate_lightbox_player();
</script>