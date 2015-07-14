<?php

/**
 * Template specific hooks
 * Used to attach functionality to template
 */
//show the publish to activity on mediapress edit gallery page

function mpp_gallery_show_publish_gallery_activity_button( ) {
	
	$gallery_id = mpp_get_current_gallery_id();
	//if not a valid gallery id or no unpublished media exists, just don't show it
	if( ! $gallery_id || ! mpp_gallery_has_unpublished_media( $gallery_id ) ) {
		return ;
	}
	
	$gallery = mpp_get_gallery( $gallery_id );
	
	$unpublished_media = mpp_gallery_get_unpublished_media( $gallery_id  );
	//unpublished media count
	$unpublished_media_count = count( $unpublished_media );
	
	$type = $gallery->type;
	
	$type_name = _n( $type, $type.'s',$unpublished_media_count );
	
	//if we are here, there are unpublished media
?>
<div id="mpp-unpublished-media-info">
	<p> <?php printf( 'You have %d %s not published to actvity.', $unpublished_media_count, $type_name );?>
		<span class="mpp-gallery-publish-activity"><?php	mpp_gallery_publish_activity_link( $gallery_id  );?></span>
		<span class="mpp-gallery-unpublish-activity"><?php	mpp_gallery_unpublished_media_delete_link( $gallery_id  );?></span>
	</p>
		
</div>
	
<?php 
}
add_action( 'mpp_before_bulkedit_media_form', 'mpp_gallery_show_publish_gallery_activity_button' );