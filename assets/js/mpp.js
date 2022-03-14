import jQuery from 'jquery';

import "./src/globals";

(function ($) {

    $(document).ready(function () {
        console.log("MPP Core Loaded");
        // Lightbox Code
        let isLightBoxLoaded = mpp.lightbox.isLoaded();
        // Lightbox popup for activity.
        if (isLightBoxLoaded && _mppSettings.enable_activity_lightbox) {

            $(document).on('click.mpp:activity:lightbox.mpp:lightbox', '.mpp-activity-media-list a.mpp-activity-media, .mpp-activity-media-list a.mpp-activity-item-title', function () {

                let $this = $(this);
                if ($this.hasClass('mpp-no-lightbox')) {
                    return;
                }

                if (!mpp.lightbox.supportsMediaType($this.data('mpp-type'))) {
                    return;
                }

                let $parent = $this.parents('.mpp-activity-item-content'),
                    activity_id = $this.data('mpp-activity-id'),
                    position = 0,
                    media_id,
                    url;

                if ($parent.length) {
                    position = $this.parents('.mpp-container').find('.mpp-activity-item-content').index($parent);
                    // newer template
                    // or non photo media
                } else if (!activity_id && $this.find('img.mpp-attached-media-item').length) {
                    activity_id = $this.find('img.mpp-attached-media-item').data('mpp-activity-id');
                }

                url = $this.attr('href');
                if (!activity_id) {
                    return true;
                }
                media_id = $this.data('mpp-media-id');
                //open lightbox
                mpp.lightbox.activity(activity_id, position, url, media_id);

                return false;
            });

            //for comment
            $(document).on('click.mpp:activity:comment:lightbox.mpp:lightbox', '.mpp-activity-comment-media-list a', function () {

                let $this = $(this);
                if ($this.hasClass('mpp-no-lightbox')) {
                    return;
                }

                if (!mpp.lightbox.supportsMediaType($this.data('mpp-type'))) {
                    return;
                }

                let media_id = $this.data('mpp-media-id'),
                    position = 0,
                    url = $this.attr('href');

                if (!media_id) {
                    return true;
                }
                //open lightbox
                mpp.lightbox.media(media_id, position, url);

                return false;
            });


        } //end of activity lightbox

        // For Gallery(when a gallery cover is clicked )
        if (isLightBoxLoaded && _mppSettings.enable_gallery_lightbox) {

            $(document).on('click.mpp:gallery:cover:lightbox.mpp:lightbox', '.mpp-gallery a.mpp-gallery-cover', function () {

                let $this = $(this);
                if ($this.hasClass('mpp-no-lightbox')) {
                    return;
                }

                if (!mpp.lightbox.supportsMediaType($this.data('mpp-type'))) {
                    return;
                }

                let gallery_id = $this.data('mpp-gallery-id'),
                    position = 0,// open first media
                    url = $this.attr('href');

                if (!gallery_id) {
                    return true;
                }
                //open lightbox
                mpp.lightbox.gallery(gallery_id, position, url);

                return false;
            });


        }
        //for shortcodes, when a media(photo) is clicked
        if (isLightBoxLoaded) {
            $(document).on('click.mpp:shortcode:lightbox.mpp:lightbox', '.mpp-shortcode-lightbox-enabled a.mpp-media-thumbnail, .mpp-shortcode-lightbox-enabled a.mpp-media-title', function () {
                let $container = $($(this).parents('.mpp-shortcode-lightbox-enabled').get(0));
                if (!$container.get(0)) {
                    return;
                }

                var $this = $(this);
                if ($this.hasClass('mpp-no-lightbox')) {
                    return;
                }

                if (!mpp.lightbox.supportsMediaType($this.data('mpp-type'))) {
                    return;
                }

                let media_ids = $container.data('media-ids'),
                    url = $this.attr('href'),
                    position = 0,// $( 'a.mpp-media-thumbnail', $container) .index( $this );
                    media_id = $this.data('mpp-media-id');

                mpp.lightbox.media(media_ids, position, url, media_id);
                return false;

            });

        } //end of lightbox for the shortcode

        // enable lightbox for click on the photo inside gallery


        // For Gallery(when a gallery cover is clicked )
        if (isLightBoxLoaded && _mppSettings.enable_lightbox_in_gallery_media_list) {

            $(document).on('click.mpp:gallery:media:lightbox.mpp:lightbox', '.mpp-single-gallery-media-list a.mpp-photo-thumbnail, .mpp-single-gallery-media-list a.mpp-media-title', function () {

                let $this = $(this);

                if ($this.hasClass('mpp-no-lightbox')) {
                    return;
                }

                if (!mpp.lightbox.supportsMediaType($this.data('mpp-type'))) {
                    return;
                }

                let gallery_id = $this.parents('.mpp-single-gallery-media-list').data('gallery-id'),
                    position = 0,//open first media
                    url = $this.attr('href'),
                    media_id = $this.data('mpp-media-id');

                if (!gallery_id || !media_id) {
                    return true;
                }

                //open lightbox
                mpp.lightbox.gallery(gallery_id, position, url, media_id);

                return false;
            });
        }

        // Create trigger to open lightbox on any link that have the class 'mpp-lightbox-link' and context
        if (isLightBoxLoaded) {
            $(document).on('click.mpp:link:lightbox.mpp:lightbox', '.mpp-lightbox-link', function () {

                var $this = $(this);

                if ($this.hasClass('mpp-no-lightbox')) {
                    return;
                }

                if (!mpp.lightbox.supportsMediaType($this.data('mpp-type'))) {
                    return;
                }

                let activity_id = $this.data('activity-id'),
                    gallery_id = $this.data('gallery-id'),
                    media_id = $this.data('media-id'),
                    url = $this.attr('href'),
                    lightbox_opened = false,
                    position = $this.data('position');

                if (!position) {
                    position = 0;
                } else {
                    position = position - 1;
                }

                if (gallery_id) {
                    // open lightbox
                    mpp.lightbox.gallery(gallery_id, position, url);
                    lightbox_opened = true;
                } else if (media_id) {
                    mpp.lightbox.media(media_id, position, url);
                    lightbox_opened = true;
                } else if (activity_id) {
                    mpp.lightbox.activity(activity_id, position, url);
                    lightbox_opened = true;
                }

                if (lightbox_opened) {
                    return false;
                }

            });
        }
    });

})(jQuery);
