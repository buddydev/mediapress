/* global window */

import jQuery from 'jquery';
import "./mpp-uploader";

(function ($) {
    // private copy to avoid user modifications.
    const uploadSettings = _.clone(_mppUploadSettings),
          uploadFeedbackStrings = _.clone(_mppUploaderFeedbackL10n );
    $(document).ready(function () {
        const utils = mpp.mediaUtils;

        ///1.
        // single gallery uploader setup.
        // context defines from where it was uploaded
        let context = uploadSettings.params.context,
            gallery_id = 0;

        if ($('#mpp-context').length) {
            context = $('#mpp-context').val();
        }

        if ($('#mpp-upload-gallery-id').length) {
            gallery_id = $('#mpp-upload-gallery-id').val();
        }

        let extensions = utils.getExtensions(uploadSettings.current_type);

        // single gallery uploader
        let uploader = new mpp.Uploader('gallery', {
            el: '#mpp-upload-dropzone-gallery',
            url: uploadSettings.url,
            l10n: uploadFeedbackStrings,
            params: _.extend({}, uploadSettings.params, {'context': context, 'gallery_id': gallery_id}),
            allowedFileTypes: utils.prepareExtensions(extensions),
            addRemoveLinks: true,
        });

        // initialize.
        uploader.init();
        // to make sure file type notices are shown. we can handle it in better way in future.
        utils.setupUploaderFileTypes(uploader, uploadSettings.current_type);

        /// 2.
        // shortcode uploader setup.
        let shortcode_gallery_id = 0, shortcodeExtensions = null;

        if ($('.mpp-upload-shortcode #mpp-shortcode-upload-gallery-id').length) {
            shortcode_gallery_id = $('.mpp-upload-shortcode #mpp-shortcode-upload-gallery-id').val();
        }
        if ($('.mpp-upload-shortcode .mpp-uploading-media-type').length) {
            shortcodeExtensions = utils.getExtensions($('.mpp-upload-shortcode .mpp-uploading-media-type').first().val());
        }

        let shortcodeUploader = new mpp.Uploader('shortcode', {
            el: '#mpp-upload-dropzone-shortcode',
            url: uploadSettings.url,
            l10n: uploadFeedbackStrings,
            params: _.extend({}, uploadSettings.params, {'context': 'shortcode', 'gallery_id': shortcode_gallery_id}),
            allowedFileTypes: shortcodeExtensions,
            addRemoveLinks: true,
        });
        // initialize.
        shortcodeUploader.init();

        if (!shortcode_gallery_id) {
            shortcodeUploader.disable();
        }
        shortcodeUploader.hideHelpMessages();

        //apply these only when the dropzone exists
        if ($('#mpp-upload-dropzone-shortcode').length) {

            var $type = $('#mpp-upload-dropzone-shortcode').parents('.mpp-upload-shortcode').find('.mpp-uploading-media-type');
            if ($type.length) {
                utils.setupUploaderFileTypes(shortcodeUploader, $type.val());
                shortcodeUploader.refresh();
            }
        }

        //on gallery selection change, we need to update the the media type too

        $('.mpp-upload-shortcode #mpp-shortcode-upload-gallery-id').on('change', function () {
            let $this = $(this),
                $option = $this.find("option:selected");

            if ($this.val()) {
                if ($this.val() !== '0') {
                    shortcodeUploader.enable();
                } else {
                    shortcodeUploader.disable();
                    // @todo we should disable and show feed back too?
                }
            }

            shortcodeUploader.setParam('gallery_id', $this.val());
            utils.setupUploaderFileTypes(shortcodeUploader, $option.data('mpp-type'));
            shortcodeUploader.refresh();
        });
        //For cover uploader

        let $editableCover = $('.mpp-editable-cover').first(),
            $coverUploadingIndicator = $('#mpp-cover-uploading'),
            oldCoverURL = '';
        let galleryID = $editableCover.find('.mpp-gallery-id').val(),
            parentID = $editableCover.find('.mpp-parent-id').val(),
            parentType = $editableCover.find('.mpp-parent-type').val();

        // single gallery /media cover uploader
        let coverUploader = new mpp.Uploader('cover_uploader', {
            el: '.mpp-editable-cover',
            clickable: '#mpp-cover-upload',
            url: uploadSettings.url,
            l10n: uploadFeedbackStrings,
            params: _.extend({}, uploadSettings.params, {
                'context': 'cover',
                'action': 'mpp_upload_cover',
                'mpp-parent-id': parentID,
                'mpp-gallery-id': galleryID,
                'mpp-parent-type': parentType,

            }),
            allowedFileTypes: utils.prepareExtensions(utils.getExtensions('photo')),
            addRemoveLinks: true,
            addedfile: function (file) {
                $coverUploadingIndicator.show();
                // show loader
                //file.previewElement = Dropzone.createElement(this.options.previewTemplate);
                // Now attach this new element some where in your page
            },
            thumbnail: function (file, dataUrl) {
                // display thumbnail.
                // Display the image in your file.previewElement
            },
            uploadprogress: function (file, progress, bytesSent) {
                // Display the progress
            },
            error: function () {
                mpp.notify('Error');
            },
            events: {
                success: function (file, response) {
                    response = response.data;
                    let url = response.sizes && response.sizes.thumbnail ? response.sizes.thumbnail.url : null;
                    if (url) {
                        $editableCover.find('.mpp-cover-image').attr('src', url);
                    }

                },
                complete: function () {
                    // hide Loader.
                    $coverUploadingIndicator.hide();
                }
            }
        });
        // initialize.
        coverUploader.init();

        // disable event propagation.
        $(document).on('click', '#mpp-cover-upload', function () {
            return false;
        });
    });
})(jQuery);