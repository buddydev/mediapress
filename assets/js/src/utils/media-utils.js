

/**
 * Activate audi/video player(MediElelement.js player)
 *
 * @param {type} activity_id
 * @returns {undefined}
 */
function mpp_mejs_activate(activity_id) {

    /* global mejs, _wpmejsSettings */
    var jq = jQuery;

    //when document is loading, mediaelementplayer will be undefined, a workaround to avoid double activating it
    if (jq.fn.mediaelementplayer === undefined) {
        return;
    }

    var settings = {};

    if (typeof _wpmejsSettings !== 'undefined') {
        settings = _wpmejsSettings;
    }

    settings.success = function (mejs) {
        var autoplay, loop;

        if ('flash' === mejs.pluginType) {
            autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
            loop = mejs.attributes.loop && 'false' !== mejs.attributes.loop;

            autoplay && mejs.addEventListener('canplay', function () {
                mejs.play();
            }, false);

            loop && mejs.addEventListener('ended', function () {
                mejs.play();
            }, false);
        }
    };

    jq('.wp-audio-shortcode, .wp-video-shortcode', jq('#activity-' + activity_id)).mediaelementplayer(settings);

    jq('.wp-playlist', jq('#activity-' + activity_id)).each(function () {
        return new WPPlaylistView({el: this});
    });

}

/**
 * Activate audio/video player(MediElelement.js player) in the lightbox.
 *
 * @returns {undefined}
 */
function mpp_mejs_activate_lightbox_player() {

    /* global mejs, _wpmejsSettings */
    var jq = jQuery;

    //when document is loading, mediaelementplayer will be undefined, a workaround to avoid double activating it
    if (jq.fn.mediaelementplayer === undefined) {
        return;
    }

    var settings = {};

    if (typeof _wpmejsSettings !== 'undefined') {
        settings = _wpmejsSettings;
    }

    settings.success = function (mejs) {
        var autoplay, loop;

        if ('flash' === mejs.pluginType) {
            autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
            loop = mejs.attributes.loop && 'false' !== mejs.attributes.loop;

            autoplay && mejs.addEventListener('canplay', function () {
                mejs.play();
            }, false);

            loop && mejs.addEventListener('ended', function () {
                mejs.play();
            }, false);
        }
    };

    jq('.wp-audio-shortcode, .wp-video-shortcode', jq('.mfp-content')).mediaelementplayer(settings);

    jq('.wp-playlist', jq('.mfp-content')).each(function () {
        return new WPPlaylistView({el: this});
    });

}

export {mpp_mejs_activate_lightbox_player, mpp_mejs_activate};