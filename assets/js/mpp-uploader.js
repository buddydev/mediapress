/* global window */

import jQuery from 'jquery';

import {prepareExtensions} from "./src/utils/functions";

import "./src/globals";

(function ( $){
    // private copy to avoid user modifications.
    const uploadSettings = _.clone( _mppUploadSettings );
    $(document).ready(function() {

        // context defines from where it was uploaded
        let context = uploadSettings.params.context,
            gallery_id = 0;

        if ($('#mpp-context').length) {
            context = $('#mpp-context').val();
        }

        if ($('#mpp-upload-gallery-id').length) {
            gallery_id = $('#mpp-upload-gallery-id').val();
        }

        let extensions = uploadSettings.types&&uploadSettings.current_type && uploadSettings.types[uploadSettings.current_type] ? uploadSettings.types[uploadSettings.current_type] : '';

        let uploader = new mpp.Uploader('gallery', {
            el: '#mpp-upload-dropzone-gallery',
            url: uploadSettings.url,
            params: _.extend( {}, uploadSettings.params, { 'context':context, 'gallery_id':gallery_id} ),
            allowedFileTypes: prepareExtensions(extensions),
            addRemoveLinks: true,
        });

        if( mpp.hooks ) {
            mpp.hooks.addFilter("mpp_test_blabla", 'mpp', function(val, arg1) {
                    console.log("Value="+val);
                    console.log("Arg="+arg1);
                return true;
            }, 10);
        }
        console.log("Before init");
        // initialize.
        uploader.init();

    });

})(jQuery);