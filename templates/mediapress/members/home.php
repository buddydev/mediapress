<div class="mpp-container mpp-clearfix mpp-members-component" id="mpp-container">
	<div class="mpp-breadcrumbs"><?php mpp_gallery_breadcrumb();?></div>
	<?php 
	if( is_super_admin() || bp_is_my_profile() )
		mpp_display_space_usage();
	?>
<?php
//main file loaded by MediaPress
//it loads the requested file

if( mpp_is_gallery_create() )
	$template = 'gallery/create.php';
//elseif( )
elseif( mpp_is_gallery_management() )
	$template = 'gallery/manage.php';
elseif( mpp_is_media_management() )
	$template = 'gallery/media/manage.php';
elseif( mpp_is_single_media() )
	$template = 'gallery/media/single.php';
elseif( mpp_is_single_gallery() )
	$template = 'gallery/single.php';
elseif( mpp_is_gallery_home() )
	$template = 'gallery/loop-gallery.php';
else
	$template = 'gallery/404.php';//not found

	

$template = apply_filters( 'mpp_get_members_gallery_template', $template );

mpp_get_template( $template );
?>
</div>  <!-- end of mpp-container -->