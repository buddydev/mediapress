<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}
?>
<a href='#' id='mpp-reload-add-media-tab' class='mpp-reload' title="<?php _e( 'Reload add media panel', 'mediapress' );?>"><span class="dashicons dashicons-update"></span><?php _e( 'Reload', 'mediapress' );?></a>
<!-- append uploaded media here -->
<div id="mpp-gallery-media-admin-list" class="mpp-uploading-media-list">
	<ul> 
		<?php
		
			$mppq = mediapress()->the_media_query; //new MPP_Media_Query( array( 'gallery_id' => $gallery_id, 'per_page' => -1, 'nopaging' => true ) );
		?>	
		<?php while ( $mppq->have_media() ): $mppq->the_media(); ?>

			<li id="mpp-uploaded-media-item-<?php mpp_media_id(); ?>" class="<?php mpp_media_class( 'mpp-uploaded-media-item' ); ?>" data-media-id="<?php mpp_media_id(); ?>">
				<img src="<?php mpp_media_src( 'thumbnail' ); ?>">
				<a href='#' class='mpp-delete-uploaded-media-item'>x</a>
			</li>
		<?php endwhile; ?>
		<?php wp_reset_query(); ?>
		<?php mpp_reset_media_data();//gallery_data(); ?>
		<?php //wp_reset_postdata();?>
	</ul>
</div>
<!-- drop files here for uploading -->
<div id="mpp-gallery-admin-dropzone" class="mpp-dropzone">
	<div class="mpp-drag-drop-inside">
		<p class="mpp-drag-drop-info"><?php _e( 'Drop files here', 'mediapress' );?></p>
		<p><?php _e( 'or', 'mediapress' );?></p>
		<p class="mpp-drag-drop-buttons"><input id="mpp-add-gallery-admin-media" type="button" class="button" value="<?php _e( 'Select files', 'mediapress' );?>" /></div>
</div>
<!-- show any feedback here -->
<div id="mpp-gallery-upload-admin-feedback" class="mpp-feedback">
	<ul> </ul>
</div>