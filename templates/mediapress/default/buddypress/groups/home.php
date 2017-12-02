<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Groups Component Gallery list(MediaPress landing page) template
 *  Used by /groups/group_name/mediapress/
 */
?>
<div role="navigation" id="subnav" class="item-list-tabs no-ajax mpp-group-nav">
	<ul>
		<?php do_action( 'mpp_group_nav' ); ?>
	</ul>
</div>
<div class="mpp-container mpp-clearfix" id="mpp-container">

	<div class="mpp-breadcrumbs mpp-clearfix"><?php mpp_gallery_breadcrumb(); ?></div>
	<?php
	if ( mpp_user_can_view_storage_stats( bp_loggedin_user_id(), 'groups', bp_get_current_group_id() ) ) {
		mpp_display_space_usage();
	}
	?>
	<?php
	// main file loaded by MediaPress
	// it loads the requested file.
	$template = '';
	if ( mpp_is_gallery_create() ) {
		$template = 'gallery/create.php';

	} elseif ( mpp_is_gallery_management() ) {
		$template = 'buddypress/groups/gallery/manage.php';
	} elseif ( mpp_is_media_management() ) {
		$template = 'buddypress/groups/media/manage.php';
	} elseif ( mpp_is_single_media() ) {
		$template = 'buddypress/groups/media/single.php';
	} elseif ( mpp_is_single_gallery() ) {
		$template = 'buddypress/groups/gallery/single.php';
	} elseif ( mpp_is_gallery_home() ) {
		$template = 'gallery/loop-gallery.php';
	} else {
		$template = 'gallery/404.php';// not found.
	}

	$template = mpp_locate_template( array( $template ), false );

	$template = apply_filters( 'mpp_groups_gallery_located_template', $template );

	if ( is_readable( $template ) ) {
		include $template;
	}
	unset( $template );
	?>
</div>  <!-- end of mpp-container -->
