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