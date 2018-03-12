<?php

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print the absolute url of the cover image
 *
 * @param string $type (thumbnail|mid|large or any registerd size).
 * @param mixed  $gallery id or object.
 */
function mpp_gallery_cover_src( $type = 'thumbnail', $gallery = null ) {
	echo mpp_get_gallery_cover_src( $type, $gallery );
}

/**
 * Get the absolute url of the cover image
 *
 * @param string      $type ( thumbnail|mind|large or any register image size).
 * @param MPP_Gallery $gallery gallery id or object.
 *
 * @return string the gallery cover image url
 */
function mpp_get_gallery_cover_src( $type = 'thumbnail', $gallery = null ) {

	$gallery = mpp_get_gallery( $gallery );

	$thumbnail_id = mpp_get_gallery_cover_id( $gallery->id );

	if ( ( ! $thumbnail_id || ! mpp_get_media( $thumbnail_id ) ) && apply_filters( 'mpp_gallery_auto_update_cover', true, $gallery ) ) {

		// if gallery type is photo, and the media count > 0 then set the latest photo as the cover.
		if ( 'photo' === $gallery->type && $gallery->media_count > 0 ) {
			// && mpp_gallery_has_media( $gallery->id )
			$thumbnail_id = mpp_gallery_get_latest_media_id( $gallery->id );

			// update gallery cover id.
			if ( $thumbnail_id ) {
				mpp_update_gallery_cover_id( $gallery->id, $thumbnail_id );
			}
		}


		if ( ! $thumbnail_id ) {
			$default_image = mpp_get_default_gallery_cover_image_src( $gallery, $type );

			return apply_filters( 'mpp_get_gallery_default_cover_image_src', $default_image, $type, $gallery );
		}
	}

	// Get the image src.
	$thumb_image_url = _mpp_get_cover_photo_src( $type, $thumbnail_id );

	return apply_filters( 'mpp_get_gallery_cover_src', $thumb_image_url, $type, $gallery );
}

/**
 * Check if Gallery has a cover set
 *
 * @param int|MPP_Gallery $gallery gallery id or object.
 *
 * @return boolean|int false if no cover else cover image id
 */
function mpp_gallery_has_cover_image( $gallery = null ) {

	$gallery = mpp_get_gallery( $gallery );

	return mpp_get_gallery_cover_id( $gallery->id );
}

/**
 * If there is no cover set for a gallery, use the default cover image
 *
 * @param int|MPP_Gallery $gallery gallery id or object.
 * @param string          $cover_type cover size(thumbnail, mid etc).
 *
 * @return string
 */
function mpp_get_default_gallery_cover_image_src( $gallery, $cover_type ) {

	$gallery = mpp_get_gallery( $gallery );

	// we need to cache the assets to avoid heavy file system read/write etc.
	$key = $gallery->type . '-' . $cover_type;
	// let us assume a naming convention like this.
	// gallery_type-cover_type.png? or whatever e.g video-thumbnail.png, photo-mid.png.
	$default_image = $gallery->type . '-' . $cover_type . '.png';

	$default_image = apply_filters( 'mpp_default_cover_file_name', $default_image, $cover_type, $gallery );

	return mpp_get_asset_url( 'assets/images/' . $default_image, $key );
}

/**
 * Get the attachment Id which is used for gallery cover
 *
 * @param int $gallery_id gallery id.
 *
 * @return int|boolean attachment id or false
 */
function mpp_get_gallery_cover_id( $gallery_id ) {
	return mpp_get_gallery_meta( $gallery_id, '_mpp_cover_id', true );
}

/**
 * Update Gallery cover attachment id
 *
 * @param int $gallery_id gallery id.
 * @param int $cover_id media id used for cover.
 *
 * @return int|boolean
 */
function mpp_update_gallery_cover_id( $gallery_id, $cover_id ) {
	return mpp_update_gallery_meta( $gallery_id, '_mpp_cover_id', $cover_id );
}

/**
 * Delete gallery cover Id
 *
 * @param int $gallery_id gallery id.
 *
 * @return boolean
 */
function mpp_delete_gallery_cover_id( $gallery_id ) {
	return mpp_delete_gallery_meta( $gallery_id, '_mpp_cover_id' );
}

/**
 * Get the photo absolute src.
 *
 * @param string    $size photo size(thumbnail|mid|large|original etc).
 * @param MPP_Media $media media object.
 *
 * @return mixed
 */
function _mpp_get_cover_photo_src( $size = '', $media = null ) {

	if ( is_object( $media ) ) {
		$media = $media->id;
	}

	$storage_manager = mpp_get_storage_manager( $media );

	return $storage_manager->get_src( $size, $media );
}
