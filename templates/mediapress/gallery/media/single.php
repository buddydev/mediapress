<?php

/**
 * Single Media View switcher
 * Swictehs to gallery/media/single/audio.pho, video.php,
 */

$type = mpp_get_media_type();
mpp_locate_sub_template( 'gallery/media/single/', $type .".php", 'default.php' );