import $ from 'jquery';
/**
 * Activate audi/video player(MediElelement.js player)
 *
 * @param {type} activity_id
 * @returns {undefined}
 */
function mpp_mejs_activate(activity_id) {

    //when document is loading, mediaelementplayer will be undefined, a workaround to avoid double activating it
    if ($.fn.mediaelementplayer === undefined) {
        return;
    }

    let settings = {};

    if (typeof _wpmejsSettings !== 'undefined') {
        settings = _wpmejsSettings;
    }

    settings.success = function (mejs) {
        let autoplay, loop;

        if ('flash' === mejs.pluginType) {
            autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
            loop = mejs.attributes.loop && 'false' !== mejs.attributes.loop;

            if (autoplay) {
                mejs.addEventListener('canplay', function () {
                    mejs.play();
                }, false);
            }

            if (loop) {
                mejs.addEventListener('ended', function () {
                    mejs.play();
                }, false);
            }
        }
    };

    $('.wp-audio-shortcode, .wp-video-shortcode', $('#activity-' + activity_id)).mediaelementplayer(settings);

    $('.wp-playlist', $('#activity-' + activity_id)).each(function () {
        return new WPPlaylistView({el: this});
    });
}

/**
 * Activate audio/video player(MediElelement.js player) in the lightbox.
 *
 * @returns {undefined}
 */
function mpp_mejs_activate_lightbox_player() {

    //when document is loading, mediaelementplayer will be undefined, a workaround to avoid double activating it
    if ($.fn.mediaelementplayer === undefined) {
        return;
    }

    let settings = {};

    if (typeof _wpmejsSettings !== 'undefined') {
        settings = _wpmejsSettings;
    }

    settings.success = function (mejs) {
        let autoplay, loop;

        if ('flash' === mejs.pluginType) {
            autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
            loop = mejs.attributes.loop && 'false' !== mejs.attributes.loop;

            if (autoplay) {
                mejs.addEventListener('canplay', function () {
                    mejs.play();
                }, false);
            }

            if (loop) {
                mejs.addEventListener('ended', function () {
                    mejs.play();
                }, false);
            }
        }
    };

    $('.wp-audio-shortcode, .wp-video-shortcode', $('.mfp-content')).mediaelementplayer(settings);

    $('.wp-playlist', $('.mfp-content')).each(function () {
        return new WPPlaylistView({el: this});
    });

}

export {mpp_mejs_activate_lightbox_player, mpp_mejs_activate};
