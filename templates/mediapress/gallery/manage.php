<?php
/**
 * This template loads appropriate template file for the current Edit gallery or Edit media action
 * 
 */

?>

<div class="mpp-menu mpp-menu-open  mpp-menu-horizontal mpp-gallery-admin-menu">
	<?php mpp_gallery_admin_menu( mpp_get_current_gallery(), mpp_get_current_edit_action() );?>
</div>
<hr />

<?php

	if ( mpp_is_gallery_add_media() ) {
		$template = 'gallery/single/manage/add-media.php';
	} elseif ( mpp_is_gallery_edit_media() ) {
		$template = 'gallery/single/manage/edit-media.php';
	} elseif ( mpp_is_gallery_reorder_media() ) {
		$template = 'gallery/single/manage/reorder-media.php';
	} elseif ( mpp_is_gallery_settings() ) {
		$template = 'gallery/single/manage/settings.php';
	} elseif ( mpp_is_gallery_delete() ) {
		$template ='gallery/single/manage/delete.php';
	}
	
	$template = apply_filters( 'mpp_get_gallery_management_template', $template );
	//load it

	mpp_get_template( $template );
