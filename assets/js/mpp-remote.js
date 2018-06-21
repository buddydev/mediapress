jQuery(function ($) {

    var $remoteInput, $container;
    // templates.
    var uploadedItemBuilder = _.template("<li class='mpp-uploaded-media-item mpp-remote-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>' data-media-id='<%= id %>'><a href='<%=  permalink %>'><img src='<%= thumb_url %>' /></a><a href='#' class='mpp-delete-uploaded-media-item'>x</a></li>");
    var embeddedItemBuilder = _.template("<li class='mpp-uploaded-media-item mpp-remote-uploaded-media-item mpp-remote-uploaded-media-item mpp-remote-uploaded-media-item-type-<%= remote_type %>' id='mpp-uploaded-media-item-<%= id %>' data-media-id='<%= id %>'><%= html %><a href='#' class='mpp-delete-uploaded-media-item'>x</a></li>");
    var uploadingFeedback = _.template("<li id='<%= id %>'><span class='mpp-remote-url-name'><%= url %></span><span class='mpp-remove-remote-media'>x</span> <b></b></li>");

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

        $container = $this.parents('.mpp-media-upload-container');
        $remoteInput = $container.find('input.mpp-remote-media-url');
        if (!$remoteInput.get(0) || $remoteInput.val().trim() === "") {
            mpp.notify(_mppData.empty_url_message, 'error');
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
                mpp_add_attached_media(response.data.id);
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
