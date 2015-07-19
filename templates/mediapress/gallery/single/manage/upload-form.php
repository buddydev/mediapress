        <!-- append uploaded media here -->
        <div id="mpp-gallery-media-list" class="mpp-uploading-media-list">
            <ul> 
           
            </ul>
        </div>
		
        <!-- drop files here for uploading -->
        <div id="mpp-gallery-dropzone" class="mpp-dropzone">
            <button id="mpp-add-gallery-media"><?php _e( 'Add media', 'mediapress' );?></button>
        </div>
        <!-- show any feedback here -->
        <div id="mpp-gallery-upload-feedback" class="mpp-feedback">
            <ul> </ul>
        </div>
		
		<input type='hidden' name='mpp-context' id='mpp-context' value='gallery' />
		<input type='hidden' name='mpp-upload-gallery-id' id='mpp-upload-gallery-id' value="<?php echo mpp_get_current_gallery_id() ;?>" />