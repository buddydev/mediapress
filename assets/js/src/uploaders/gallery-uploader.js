
    mpp.guploader = new mpp.Uploader({
        container: 'body',
        dropzone: '#mpp-upload-dropzone-gallery',
        browser: '#mpp-upload-media-button-gallery',
        feedback: '#mpp-upload-feedback-gallery',
        media_list: '#mpp-uploaded-media-list-gallery',//where we will list the media
        uploading_media_list: _.template("<li id='<%= id %>'><span class='mpp-attached-file-name'><%= name %></span>(<span class='mpp-attached-file-size'><%= size %></spa>)<span class='mpp-remove-file-attachment'>x</span> <b></b></li>"),
        uploaded_media_list: _.template("<li class='mpp-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>'><img src='<%= url %>' /></li>")


    });
    /** For single gallery  upload */

    var context = 'gallery';//context defines from where it was uploaded
    var gallery_id = 0;

    if (jq('#mpp-context').get(0)) {
        context = jq('#mpp-context').val();
    }

    if (jq('#mpp-upload-gallery-id').get(0)) {
        gallery_id = jq('#mpp-upload-gallery-id').val();
    }
    //apply these only when the dropzone exists
    if (jq('#mpp-upload-dropzone-gallery').get(0)) {

        mpp.guploader.param('context', context);
        mpp.guploader.param('gallery_id', gallery_id);
        mpp_setup_uploader_file_types(mpp.guploader);
    }
