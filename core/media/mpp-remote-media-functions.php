<?php
/**
 * Remote Media functions.
 *
 * @package    MediaPress
 * @subpackage Core/Media
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit( 0 );

/**
 * Is given media remote?
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return boolean
 */
function mpp_is_remote_media( $media = null ) {

	$media = mpp_get_media( $media );

	return apply_filters( 'mpp_get_media_is_remote', $media->is_remote );

}

/**
 * Is it Oembed media?
 *
 * @param MPP_Media|int|null $media media id or Object.
 *
 * @return boolean
 */
function mpp_is_oembed_media( $media = null ) {

	$media = mpp_get_media( $media );

	return apply_filters( 'mpp_is_oembed_media', $media->is_oembed );
}
