//Lightbox utility API.

import $ from 'jquery';

export default {
    /**
     * Open Lightbox with the Media Collection.
     *
     * @param {array} items array of media items
     * @param {int} position numeric position of the media to be shown by default
     * @param {string} fallback_url open this url on error.
     */
    open: function (items, position, fallback_url) {
        if (items.length < 1) {
            window.location = fallback_url;
            return;
        }

        $.magnificPopup.open({
                items: items,
                type: 'inline',
                closeBtnInside: false,
                preload: [1, 3],
                closeOnBgClick: true,
                showCloseBtn: true,
                closeMarkup: '<button title="%title%" type="button" class="mfp-close mpp-lightbox-close-btn">&#215;</button>',
                gallery: {
                    enabled: true,
                    navigateByImgClick: true,
                    //arrowMarkup: '',// disabled default arrows
                    preload: [0, 1] // Will preload 0 - before current, and 1 after the current image
                }
            },
            position
        );

        // new api.
        $(document).trigger('mpp:lightbox:opened', [items, position] );

        // backaward compatibility.
        $(document).trigger('mpp_lightbox_opened');
    }, //open lightbox

    /**
     * Update the lightbox content with given html.
     *
     * @param {string} content content.
     *
     * @returns {boolean}
     */
    update: function (content) {
        if (!mpp.lightbox.isLoaded()) {
            return false;
        }
        var magnificPopup = $.magnificPopup.instance;
        magnificPopup.currItem.src = content;
        magnificPopup.items[magnificPopup.index] = magnificPopup.currItem;
        magnificPopup.updateItemHTML();
    }, // update current open box with the content.

    gallery: function (gallery_id, position, url, media_id) {
        var $lightbox = this;
        //get the details from server.
        $.post(ajaxurl, {
                action: 'mpp_fetch_gallery_media',
                gallery_id: gallery_id,
                cookie: encodeURIComponent(document.cookie)
            },
            function (response) {
                if (response.items === undefined) {
                    return;//should we notify too?
                }

                var items = response.items;
                // If media ID is given
                if (typeof media_id !== 'undefined') {
                    position = get_media_position_in_collection(media_id, items);
                }
                $lightbox.open(items, position, url);

            }, 'json');
    },

    /**
     * Open one or more media(photo) in lightbox
     *
     * @param {string} media_ids comma separated list of media ids
     * @param {integer} position which media to display as first
     * @param {string} url fallback url to open if lightbox is unable to open
     */
    media: function (media_ids, position, url, media_id) {
        var $lightbox = this;
        jQuery.post(ajaxurl, {
                action: 'mpp_lightbox_fetch_media',
                media_ids: media_ids,
                cookie: encodeURIComponent(document.cookie)
            },
            function (response) {
                if (response.items === undefined) {
                    return;//should we notify too?
                }

                var items = response.items;
                // If media ID is given
                if (typeof media_id !== 'undefined') {
                    position = get_media_position_in_collection(media_id, items);
                }
                $lightbox.open(items, position, url);

            }, 'json');
    },

    activity: function (activity_id, position, url, media_id) {
        //get the details from server
        var $lightbox = this;

        jQuery.post(ajaxurl, {
                action: 'mpp_fetch_activity_media',
                activity_id: activity_id,
                cookie: encodeURIComponent(document.cookie)
            },
            function (response) {
                if (response.items === undefined) {
                    return;//should we notify too?
                }

                var items = response.items;
                // If media ID is given
                if (typeof media_id !== 'undefined') {
                    position = get_media_position_in_collection(media_id, items);
                }

                $lightbox.open(items, position, url);

            }, 'json');
    }, //open for activity

    /**
     * Reload given media id.
     *
     * @param media_id
     */
    reloadMedia: function (media_id) {
        var $lightbox = this;
        jq.post(ajaxurl, {action: 'mpp_reload_lightbox_media', 'media_id': media_id}, function (response) {
            if (response.success) {
                // success
                $lightbox.update(response.data.content);
            } else {
                // Failed.
            }
        });
    },

    /**
     * Reload the current lightbox media. It acts as refresh.
     *
     * @returns {boolean}
     */
    reloadCurrentMedia: function () {
        var media_id = this.getCurrentMediaID();
        if (media_id) {
            this.reloadMedia(media_id);
            return true;
        }
        return false;
    },

    /**
     * Get Current Media Opened in the lightbox.
     *
     * returns 0 for invalid call.
     *
     * @returns {int}
     */
    getCurrentMediaID: function () {
        if (!this.isLoaded() || !this.isOpen()) {
            return 0;
        }

        var magnificPopup = jQuery.magnificPopup.instance;
        var data = magnificPopup.currItem.data;
        if (typeof data.id !== 'undefined') {
            return data.id;
        }

        return 0;
    },

    /**
     * Is Lightbox Loaded?
     *
     * @returns {boolean}
     */
    isLoaded: function () {
        return jQuery.fn.magnificPopup !== undefined;
    },

    /**
     * Is lightbox Open?
     *
     * @returns {boolean}
     */
    isOpen: function () {
        return jQuery.magnificPopup.instance.isOpen === true;
    },
    // backward compatibility
    is_lightbox_loaded: function () {
        return this.isLoaded();
    },
    /**
     * Check if lightbox supports given type.
     *
     * @param type
     * @returns {Boolean}
     */
    supportsMediaType: function (type) {
        // type unknown, or nothing is disabled or type is enabled.
        return !type || !_mppSettings.lightboxDisabledTypes || !_mppSettings.lightboxDisabledTypes[type];
    }
};

/**
 * Find position of the item in the given collection.
 *
 * @param media_id
 * @param items
 * @returns {number}
 */
function get_media_position_in_collection(media_id, items) {
    var index = 0;
    var position = 0;
    // calculate the position of this media in the collection
    for (var i in items) {
        if (items[i].id == media_id) {
            position = index;
        }
        index++;
    }

    return position;
}