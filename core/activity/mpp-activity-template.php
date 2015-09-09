<?php
//No direct access to the file 
if( ! defined( 'ABSPATH' ) )
	exit( 0 );
/**
 * Add various upload icons to activity post form
 * 
 *  
 * @return type
 */
function mpp_activity_upload_buttons() {
    
    if( ! mpp_is_activity_upload_enabled( mpp_get_current_component() ) )
        return;
   
    //if we are here, the gallery activity stream upload is enabled,
    //let us see if we are on user profile and gallery is enabled
    if( bp_is_user() && ! mpp_is_active_component( 'members' ) )
        return;
    //if we are on group page and either the group component is not enabled or gallery is not enabled for current group, do not show the icons
    if( function_exists( 'bp_is_group' ) && bp_is_group() && ( ! mpp_is_active_component( 'groups' ) || ! mpp_group_is_gallery_enabled() ) )
        return;
	//for now, avoid showing it on single gallery/media activity stream
	if( mpp_is_single_gallery() || mpp_is_single_media() )
		return ;
	?>
    <div id="mpp-activity-upload-buttons" class="mpp-upload-butons">
        <?php do_action("mpp_before_activity_upload_buttons");//allow to add more type ?>
        
    <?php if( mpp_is_active_type( 'photo' ) ):?>
        <a href="#" id="mpp-photo-upload" data-media-type="photo"><img src="<?php echo mediapress()->get_url().'/assets/images/media-button-image.gif'?>"/></a>
     <?php endif;?>
        
    <?php if( mpp_is_active_type( 'audio' ) ):?>
        <a href="#" id="mpp-audio-upload" data-media-type="audio"><img src="<?php echo mediapress()->get_url().'/assets/images/media-button-music.gif'?>"/></a>
     <?php endif;?>

     <?php if( mpp_is_active_type( 'video' ) ): ?>
        <a href="#" id="mpp-video-uploader"  data-media-type="video"><img src="<?php echo mediapress()->get_url().'/assets/images/media-button-video.gif'?>"/></a>
    <?php endif;?>
        
     <?php do_action( 'mpp_after_activity_upload_buttons' );//allow to add more type ?>

    </div>
  <?php
}
//activity filter
add_action( 'bp_after_activity_post_form', 'mpp_activity_upload_buttons' );



//add dropzone/feedback/uploaded media list for activity

function mpp_activity_dropzone() {
    ?>
	<!-- append uploaded media here -->
	<div id="mpp-activity-media-list" class="mpp-uploading-media-list">
		<ul> </ul>
	</div>
	<?php do_action( 'mpp_after_activity_upload_medialist' );?>	
	<!-- drop files here for uploading -->
	<div id="mpp-activity-dropzone" class="mpp-dropzone">
		<button id="add-activity-media"><?php _e( 'Add media', 'mediapress' );?></button>
	</div>
	<?php do_action( 'mpp_after_activity_upload_dropzone' );?>
	<!-- show any feedback here -->
	<div id="mpp-activity-feedback" class="mpp-feedback">
		<ul> </ul>
	</div>
	<?php do_action( 'mpp_after_activity_upload_feedback' );?>
   <?php 
}
add_action( 'bp_after_activity_post_form', 'mpp_activity_dropzone' );

/**
 * Format activity action for 'mpp_media_upload' activity type.
 *
 * 
 * @param string $action  activity action.
 * @param object $activity Activity object.
 * @return string
 */
function mpp_format_activity_action_media_upload( $action, $activity ) {
	
	$userlink = mpp_get_user_link( $activity->user_id );
    
	$media_ids = array();
	//this could be a comment
	//or a media comment
	//or a gallery comment
	//or an activity upload
	$media_id = mpp_activity_get_media_id( $activity->id );
	
	$gallery_id = mpp_activity_get_gallery_id( $activity->id );
	
	if( ! $media_id && ! $gallery_id )
		return $action; //not a gallery activity, no need to proceed further
	
	$type = mpp_activity_get_activity_type( $activity->id );//is a type specified
	
	$skip = false;
	
	if( $type ) {
		
		if( in_array( $type, array( 'create_gallery', 'edit_gallery', 'add_media' ) ) ) {
			$skip = true;
		}
	}
	
	

	if( ! $skip && $media_id ) {
		
		$media = mpp_get_media( $media_id );
		
		//this is an activity comment on single media
		if( mpp_is_single_media() ) {
			
			$action   = sprintf( __( '%s', 'mediapress' ), $userlink );
			
		}else {
			
			$action = sprintf ( __( "%s commented on %s's %s", 'mediapress' ), $userlink, mpp_get_user_link( $media->user_id ), $media->type ) ; //brajesh singh commented on @mercime's photo
			
		}
		
	} elseif( ! $skip && $gallery_id ) {
		
		$gallery = mpp_get_gallery( $gallery_id );
		
		//check for the uploaded media
		$media_ids = mpp_activity_get_attached_media_ids( $activity->id );
   
		//this will never fire but let us be defensive
		if( empty( $media_ids ) ) {
			//this is gallery comment
			if(  mpp_is_single_gallery() ) {
				
				$action = sprintf ( '%s', $userlink );
			
				
			} else {
				
				$action = sprintf ( __( "%s commented on %s's <a href='%s'>%s gallery</a>", 'mediapress' ), $userlink, mpp_get_user_link( $gallery->user_id ), mpp_get_gallery_permalink ( $gallery ), $gallery->type );
			}
			
		} else {
        
			//we will always be here

			$media_count = count( $media_ids );
			$media_id = current( $media_ids );

			$type = $gallery->type;

			//we need the type plural in case of mult
			$type = _n( $type, $type . 's', $media_count );//photo vs photos etc

			$action   = sprintf( __( '%s uploaded %d new %s', 'mediapress' ), $userlink,  $media_count, $type );

			//allow modules to filter the action and change the message
			$action = apply_filters( 'mpp_activity_action_media_upload', $action, $activity, $media_id, $media_ids, $gallery );
		}	
	}
	
	return apply_filters( 'mpp_format_activity_action_media_upload', $action, $activity, $media_id, $media_ids );
}
