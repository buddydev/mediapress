<?php
/**
 * Edit media form for media lightbox.
 */
?>
<form class="mpp-lightbox-media-edit-form mpp-form-hidden mpp-clearfix" id="mpp-lightbox-media-edit-form-<?php mpp_media_id(); ?>" data-media-id="<?php mpp_media_id(); ?>" method="post" action="">
    <div class="mpp-g mpp-lightbox-media-edit-details-wrapper">

		<?php $media = mpp_get_media(); ?>

        <div class="mpp-u-1-1 mpp-media-status">
            <label for="mpp-media-status"><?php _e( 'Status', 'mediapress' ); ?></label>
			<?php mpp_status_dd( array(
				'name'      => 'mpp-media-status',
				'id'        => 'mpp-media-status',
				'selected'  => $media->status,
				'component' => $media->component,
			) );
			?>
        </div>

        <div class="mpp-u-1-1 mpp-media-title">
            <label for="mpp-media-title"> <?php _e( 'Title', 'mediapress' ); ?></label>
            <input type="text" id="mpp-media-title" class="mpp-input-1"
                   placeholder="<?php _ex( 'Media title (Required)', 'Placeholder for media edit form title', 'mediapress' ); ?>"
                   name="mpp-media-title" value="<?php echo esc_attr( $media->title ); ?>"/>
        </div>

        <div class="mpp-u-1 mpp-media-description">
            <label for="mpp-media-description"><?php _e( 'Description', 'mediapress' ); ?></label>
            <textarea id="mpp-media-description" name="mpp-media-description" rows="3" class="mpp-input-1"><?php echo esc_textarea( $media->description ); ?></textarea>
        </div>

		<?php do_action( 'mpp_after_lightbox_edit_media_form_fields' ); ?>
        <input type='hidden' name="mpp-action" value='edit-lightbox-media'/>
        <input type="hidden" name='mpp-media-id' value="<?php mpp_media_id(); ?> "/>
		<?php wp_nonce_field( 'mpp-lightbox-edit-media', 'mpp-nonce' ); ?>

        <div class="mpp-u-1 mpp-clearfix mpp-lightbox-edit-media-buttons-row">
            <img src="<?php echo mpp_get_asset_url( 'assets/images/loader.gif', 'mpp-loader' ); ?>"
                 class="mpp-loader-image"/>
            <button type="submit" class='mpp-button-secondary mpp-lightbox-edit-media-submit-button mpp-align-right'
                    data-mpp-media-id="<?php mpp_media_id(); ?>"> <?php _e( 'Save', 'mediapress' ); ?></button>
            <button class='mpp-button-secondary mpp-lightbox-edit-media-cancel-button mpp-align-right'
                    data-mpp-media-id="<?php mpp_media_id(); ?>"> <?php _e( 'Cancel', 'mediapress' ); ?></button>
        </div>

    </div>
</form>
