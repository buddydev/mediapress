<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 *
 * Single photo view
 *
 */
$media = mpp_get_current_media();
?>

<img src="<?php mpp_media_src( mpp_get_selected_single_media_size(), $media ); ?>" alt="<?php mpp_media_title( $media ); ?>" class="mpp-large"/>