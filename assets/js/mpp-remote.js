
import jQuery from 'jquery';

(function ($){

    $(function () {
        const utils = mpp.mediaUtils;

        let uploadedItemBuilder = _.template("<li class='mpp-uploaded-media-item mpp-remote-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>' data-media-id='<%= id %>'><a href='<%=  permalink %>'><img src='<%= thumb_url %>' /></a><a href='#' class='mpp-delete-uploaded-media-item'>x</a></li>"),
            embeddedItemBuilder = _.template("<li class='mpp-uploaded-media-item mpp-remote-uploaded-media-item mpp-remote-uploaded-media-item mpp-remote-uploaded-media-item-type-<%= remote_type %>' id='mpp-uploaded-media-item-<%= id %>' data-media-id='<%= id %>'><%= html %><a href='#' class='mpp-delete-uploaded-media-item'>x</a></li>"),
            uploadingFeedback = _.template("<li id='<%= id %>'><span class='mpp-remote-url-name'><%= url %></span><span class='mpp-remove-remote-media'>x</span> <b></b></li>"),
            $remoteInput,
            $container;

        // on click of the Add button.
        $(document).on('click', '.mpp-add-remote-media', function () {
            // clear notices.
            mpp.clearNotice();

            var $this = $(this);
            var context = '';//context defines from where it was uploaded
            var galleryID = 0;
            var rId = randomID();
            var $gallery = $('#mpp-upload-gallery-id');

            if ($gallery.get(0)) {
                galleryID = $gallery.val();
            }

            $container = $this.parents('.mpp-new-media-container');
            $remoteInput = $container.find('input.mpp-remote-media-url');
            if (!$remoteInput.get(0) || $remoteInput.val().trim() === "") {
                mpp.notify(_mppUploadSettings.empty_url_message, 'error');
                return false;
            }

            var $context = $container.find('.mpp-context');
            if ($context.get(0)) {
                context = $context.val();
            }

            var url = $remoteInput.val();

            var $uploadingList = $container.find('.mpp-uploading-media-list ul');
            $uploadingList.find('.mpp-loader').show();

            var $feedback = $container.find('.mpp-remote-media-upload-feedback ul');

            var trimmedURL = url.length > 20 ? url.substr(0, 20) + '...' : url;

            $feedback.append(uploadingFeedback({id: rId, url: trimmedURL}));
            $remoteInput.val("");
            $.post(ajaxurl, {
                'action': 'mpp_add_remote_media',
                'url': url,
                'gallery_id': galleryID,
                'context': context,
                '_wpnonce': $container.find('#mpp-remote-media-nonce').val()

            }, function (response) {
                $uploadingList.find('.mpp-loader').hide();
                var $li = $feedback.find('li#' + rId);
                $li.remove();
                if (response.success) {
                   utils.addAttachedMedia($container, response.data.id);
                    var data = response.data;
                    if (data.is_oembed) {
                        $uploadingList.append(embeddedItemBuilder(response.data));
                    } else {
                        $uploadingList.append(uploadedItemBuilder(response.data));
                    }

                } else {
                    mpp.notify(response.data.message, 'error');
                    // $li.find('b').text(response.data.message);
                }

            });

            return false;
        });

        /// Trigger delete, deletes any trace of a Media
        $(document).on('click', '.mpp-uploading-media-list .mpp-delete-uploaded-media-item', function () {

            let $this = $(this),
                $parent = $this.parent(),
                $container = $this.parents('.mpp-new-media-container'),
                id = $parent.data('media-id');

            if (!id) {
                return false;
            }

            let $img = $parent.find('img'),
                old_image = $img.attr('src');
            //set the loader icon as source

            $img.attr('src', _mppSettings.loader_src);
            $this.hide();// no delete button.

            var nonce = $('#_mpp_manage_gallery_nonce').val();

            $.post(ajaxurl, {
                action: 'mpp_delete_media',
                media_id: id,
                cookie: encodeURIComponent(document.cookie),
                _wpnonce: nonce
            }, function (response) {

                if (typeof  response.success !== "undefined") {
                    $parent.remove(); //can't believe the parent is going away too
                    if(mpp.mediaUtils ) {
                        mpp.mediaUtils.removeAttachedMedia($container, id);
                    }
                    mpp.notify(response.message);

                } else {

                    $this.show();
                    $img.attr('src', old_image);

                    mpp.notify(response.message);
                }

            }, 'json');

            return false;
        });

        /**
         * Get random ID.
         *
         * Credit: https://gist.github.com/6174/6062387
         * @returns {string}
         */
        function randomID() {
            return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        }
    });
})(jQuery);

