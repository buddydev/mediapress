
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
