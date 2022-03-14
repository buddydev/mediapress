/* global jQuery, ajaxurl, mpp, _mppData, WPPlaylistView, mpp_add_attached_media, mpp_reset_attached_media */
jQuery(document).ready(function () {

    var jq = jQuery;


//For cover uploader

    mpp.cover_uploader = new mpp.Uploader({
        container: 'body',
        dropzone: '.mpp-gallery-editable-cover',
        browser: '#mpp-cover-upload',
        feedback: '#mpp-cover-gallery-upload-feedback',
        media_list: '',//where we will list the media
        uploading_media_list: _.template("<li id='<%= id %>'><span class='mpp-attached-file-name'><%= name %></span>(<span class='mpp-attached-file-size'><%= size %></spa>)<span class='mpp-remove-file-attachment'>x</span> <b></b></li>"),
        uploaded_media_list: _.template("<li class='mpp-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>'><img src='<%= url %>' /></li>"),


        complete: function () {

            // console.log('Cover Uploaded');
        },

        success: function (file) {

            var sizes = file.get('sizes');
            var original_url = file.get('url');
            var id = file.get('id');
            var file_obj = file.get('file');

            var thumbnail = sizes.thumbnail;

            //on success change cover image

            var cover = '#mpp-cover-' + file.get('parent_id');

            jq(cover).find('.mpp-cover-uploading').hide();

            jq(cover).find('img.mpp-cover-image').attr('src', thumbnail.url);

        },

        clear_media_list: function () {

        },
        clear_feedback: function () {
            if (!this.feedback) {
                return;
            }

            jq('ul', this.feedback).empty();
        },

        hide_dropzone: function () {

            if (!this.dropzone) {
                return;
            }

            jq(this.dropzone).hide();
        },
        hide_ui: function () {

            this.clear_media_list();
            this.clear_feedback();
            this.hide_dropzone();
        },

        onAddFile: function (file) {
            //wehn file is added, set context

            this.param('context', 'cover');//it is cover upload
            this.param('action', 'mpp_upload_cover');//it is cover upload


            var parent = this.browser.parents('.mpp-cover-wrapper');

            //update parent media or gallery id
            this.param('mpp-parent-id', parent.find('.mpp-parent-id').val());//it is cover upload
            //update parent gallery id
            this.param('mpp-gallery-id', parent.find('.mpp-gallery-id').val());//it is cover upload
            this.param('mpp-parent-type', parent.find('.mpp-parent-type').val());//it is cover upload

            parent.find('.mpp-cover-uploading').show();

        },

        init: function () {

            var parent = this.browser.parents('.mpp-cover-wrapper');

            jq.each(parent, function () {
                jq(this).find('.mpp-gallery-editable-cover').append(jq('#mpp-cover-uploading').clone());

            });

        }

    });

    mpp.shortcode_uploader = new mpp.Uploader({
        container: 'body',
        dropzone: '#mpp-upload-dropzone-shortcode',
        browser: '#mpp-upload-media-button-shortcode',
        feedback: '#mpp-upload-feedback-shortcode',
        media_list: '#mpp-uploaded-media-list-shortcode',//where we will list the media
        uploading_media_list: _.template("<li id='<%= id %>'><span class='mpp-attached-file-name'><%= name %></span>(<span class='mpp-attached-file-size'><%= size %></spa>)<span class='mpp-remove-file-attachment'>x</span> <b></b></li>"),
        uploaded_media_list: _.template("<li class='mpp-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>'><img src='<%= url %>' /></li>"),


        onAddFile: function (file) {
            //when file is added, set context

            this.param('context', 'shortcode');//it is cover upload
            var parent = this.browser.parents('.mpp-upload-shortcode');
            var $gallery = parent.find('#mpp-shortcode-upload-gallery-id');
            var $skip_check = parent.find('#mpp-shortcode-skip-gallery-check');
            if (!$skip_check.get(0) && (!$gallery.get(0) || $gallery.val() == 0)) {

                this.uploader.removeFile(file);
                this.refresh();

                //remove the feedback that we added
                this.removeFileFeedback(file);
                this.uploader.stop();
                //notify error message
                mpp.notify("Please select a gallery before uploading.", 1);

            }

            //update parent gallery id
            this.param('gallery_id', parent.find('#mpp-shortcode-upload-gallery-id').val());//it is gallery upload voia shortcode
            jq('.mpp-loader', this.media_list).show();
        }
    });


    //apply these only when the dropzone exists
    if (jq('#mpp-upload-dropzone-shortcode').get(0)) {

        var $type = jq('#mpp-upload-dropzone-shortcode').parents('.mpp-upload-shortcode').find('.mpp-uploading-media-type');
        if ($type.get(0)) {
            mpp_setup_uploader_file_types(mpp.shortcode_uploader, $type.val());
        }
    }

    //on gallery selection change, we need to update the the media type too

    jQuery('.mpp-upload-shortcode #mpp-shortcode-upload-gallery-id').change(function () {
        var $option = jQuery(this).find("option:selected");
        mpp_setup_uploader_file_types(mpp.shortcode_uploader, $option.data('mpp-type'));
    });
    /// Trigger delete, deletes any trace of a Media
    // I hurts when people delete loved ones from their heart, but deleting a media is fine
    jq(document).on('click', '.mpp-uploading-media-list .mpp-delete-uploaded-media-item', function () {

        var $this = jq(this);
        var $parent = jq($this.parent()); //parents are very important in our life, how can we forget them
        //is the data-media-id attribute set, like parents keep their child in heart, our $parent does too
        var id = $parent.data('media-id');

        if (!id) {
            return false;
        }

        var $img = $parent.find('img');
        var old_image = $img.attr('src');
        //set the loader icon as source

        $img.attr('src', _mppData.loader_src);
        $this.hide();//no delete button

        //get the security pass for clearance because unidentified intruders are not welcome in the family
        var nonce = jq('#_mpp_manage_gallery_nonce').val();

        //Now is the time to take action,
        jq.post(ajaxurl, {
            action: 'mpp_delete_media',
            media_id: id,
            cookie: encodeURIComponent(document.cookie),
            _wpnonce: nonce
        }, function (response) {
            //how rude the nature is
            //you deleted my media and still sending me message
            if (typeof  response.success !== "undefined") {
                $parent.remove(); //can't believe the parent is going away too

                mpp_remove_attached_media(id);
                mpp.notify(response.message); //let the superman know what consequence his action has done

            } else {
                //something went wrong, perhaps the media escaped the deletion
                $this.show();
                $img.attr('src', old_image);

                mpp.notify(response.message);
            }
            //enough, let us hide the round round feedback


        }, 'json');

        return false;
    });

});
