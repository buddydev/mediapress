<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mpp-menu mpp-menu-open mpp-menu-horizontal mpp-media-admin-menu">

	<?php mpp_media_menu( mpp_get_current_media(), mpp_get_current_edit_action() ); ?>
</div>
<hr/>
<?php
$template = '';
if ( mpp_is_media_delete() ) {
	$template = 'gallery/media/manage/delete.php';
} elseif ( mpp_is_media_management() ) {
	$template = 'gallery/media/manage/edit.php';
}

$template = apply_filters( 'mpp_get_media_management_template', $template );
// load it.
if ( $template ) {
	mpp_get_template( $template );
}
unset( $template );// don't let the global litter unintentionally.

do_action( 'mpp_load_media_management_template' );
