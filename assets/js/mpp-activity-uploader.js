/* global window */

import jQuery from 'jquery';

import {prepareExtensions, getQueryParameter} from "./src/utils/functions";
// import for side effects.
import "./src/globals";

/** globals _mppUploadSettings */
(function ($) {
    // private copy to avoid user modifications.
    const uploadSettings = _.clone(_mppUploadSettings);

    $(document).ready(function () {

        let $postSubmitBtn = $('#aw-whats-new-submit'),
            $uploadButtonsContainer = $('#mpp-activity-upload-buttons'),
            $activityFormOptions = $('#whats-new-options'),
            context = 'activity',
            extensions = getExtensions( uploadSettings.current_type );

        // Move the buttons if the elemt exists.
        if ($uploadButtonsContainer.length && $activityFormOptions.length) {
            $activityFormOptions.prepend($uploadButtonsContainer);
        }

        let activityUploader = new mpp.Uploader('activity', {
            el: '#mpp-upload-dropzone-activity',
            url: uploadSettings.url,
            params: _.extend({}, uploadSettings.params, {'context': context}),
            allowedFileTypes: prepareExtensions(extensions),
            addRemoveLinks: true,
            events: {
                error: function () {
                    $postSubmitBtn.prop('disabled', false);
                }, complete: function () {
                    $postSubmitBtn.prop('disabled', false);
                },
                allAdded: function () {
                    $postSubmitBtn.prop('disabled', true);
                }
            }
        });

        // initialize.
        activityUploader.init();

        // When any of the media icons(audio/video etc) is clicked
        // show the dropzone
        $(document).on('click', '#mpp-activity-upload-buttons a', function () {
            if( activityUploader.isAttached() ) {
                //set current type as the clicked button
                uploadSettings.current_type = $(this).data('media-type');
                //use id as type detector , may be photo/audio/video
                setupUploaderFileTypes(activityUploader,  uploadSettings.current_type );
                activityUploader.refresh();
                activityUploader.showUI();
                // option to disable in 1.4.0
                uploadSettings.activity_disable_auto_file_browser = parseInt(uploadSettings.activity_disable_auto_file_browser, 10);
                if (!uploadSettings.activity_disable_auto_file_browser) {
                    activityUploader.openFileChooser();//simulate click;
                }
            }

            $('.mpp-remote-add-media-row-activity').show();

            return false;
        });

        // Enable closing of the dropzone and clearing the queue for activity post form upload.
        $(document).on('click', '.mpp-activity-new-media-container .mpp-upload-container-close', function () {
            activityUploader.hideUI();
            activityUploader.refresh();
            return false;
        });

        // Intercept the ajax actions to check if there was an upload from activity
        // if yes, when it is complete, hide the dropzone
            $(document).ajaxSend(function (event, jqxhr, settings) {

                let action = getQueryParameter('action', settings.data);
                let attached_media = null, uploader;
                switch ( action ) {

                    case 'post_update':
                    case 'swa_post_update':
                        uploader = activityUploader;
                        break;

                    case 'new_activity_comment':
                        let formID = getQueryParameter('form_id', settings.data);
                        uploader = getUploader( getUploaderIDForActivity( formID));
                        break;
                }

                if( uploader) {
                    //uploader.debug();
                    attached_media = uploader.getUploadedMediaIDs().join(',');
                    if( attached_media ) {
                        settings.data = settings.data + '&mpp-attached-media=' + attached_media;
                        uploader.reset();
                    }
                }
            });

            // On ajax complete, hide the uploader ui.
            $(document).ajaxComplete(function (evt, xhr, options) {

                let action = getQueryParameter('action', options.data);
                //switch
                switch (action) {
                    case 'post_update':
                    case 'swa_post_update':
                        $('.mpp-remote-add-media-row-activity').hide();
                        activityUploader.hideUI();
                        activityUploader.refresh();
                        break;
                    case 'new_activity_comment':
                        let uploader = getUploader( getUploaderIDForActivity( getQueryParameter('form_id', options.data)));
                        if( uploader ) {
                            uploader.hideUI();
                            uploader.refresh();
                        }
                        break;
                }
            });


        /// -------------------- Activity Comment Uploading ------- ///

        // When any of the media icons(audio/video etc) is clicked
        //show the dropzone
        $(document).on('click', '.mpp-activity-comment-upload-buttons a', function () {
            let $this= $(this),
                $btnContainer = $this.closest('.mpp-activity-comment-upload-buttons'),
                $activityItem = $btnContainer.closest('.comment-item'),
                activityID= $btnContainer.length? $btnContainer.data('activity-id') : 0,
                commentID = 0,
                currentType = $this.data('media-type');

            mpp_log("Type="+currentType);
            // try to find comment ID
            if($activityItem.length ) {
                commentID = $activityItem.data('bp-activity-comment-id');
            }

            mpp_log('Activity: '+activityID, ' , Comment ID: '+commentID );

            let uploaderID = getUploaderIDForActivity(activityID);
            let uploader = getUploader(uploaderID);

            // if uploader does exist, we should destroy it if the comment id is different

            if (uploader ) {
                if (uploader.getParam('comment_id') != commentID) {
                    uploader.destroy();
                    uploader = null;
                } else if( uploader.type && uploader.type !== currentType ){
                   // mpp_log("Current type:"+ currentType + 'OLD Type='+uploader.type);
                    setupUploaderFileTypes(uploader, currentType );
                }
            }

            if( ! uploader ) {
                uploader = new mpp.Uploader(uploaderID, {
                    el: '#mpp-upload-dropzone-activity-comment-' + activityID,
                    url: uploadSettings.url,
                    params: _.extend({}, uploadSettings.params, {context: 'activity-comment', activity_id: activityID}),
                    allowedFileTypes: prepareExtensions(getExtensions(currentType)),
                    addRemoveLinks: true,
                    events: {
                        error: function () {
                            //$postSubmitBtn.prop('disabled', false);
                        }, complete: function () {
                            //  $postSubmitBtn.prop('disabled', false);
                        },
                        allAdded: function () {
                            // $postSubmitBtn.prop('disabled', true);
                        }
                    }
                });
                uploader.type = currentType;
                uploader.init();
            }

            uploader.setParam('comment_id', commentID);

            if( uploader.isAttached() ) {
                uploader.showUI();
                // option to disable in 1.4.0
                uploadSettings.activity_disable_auto_file_browser = parseInt(uploadSettings.activity_disable_auto_file_browser, 10);
                if (!uploadSettings.activity_disable_auto_file_browser) {
                    uploader.openFileChooser();//simulate click;
                }
            }

            return false;
        });

        // Enable closing of the dropzone and clearing the queue on close.
        $(document).on('click', '.mpp-activity-comment-new-media-container .mpp-upload-container-close', function () {
            let activityID = $(this).data('activity-id');
            if( ! activityID ) {
                return false;
            }

            let uploaderID = getUploaderIDForActivity(activityID);
            let uploader = getUploader(uploaderID);

            if( uploader ) {
                uploader.hideUI();
                uploader.refresh();
            }

            return false;
        });

        // whenever a comment reply button is clicked, we need to destroy the previous uploader attached with this activity
        $(document).on('click', '.acomment-reply', function() {

            let activityID = $(this).closest('.activity-item').data('bp-activity-id');
            let uploader = getUploader(getUploaderIDForActivity(activityID));

            if(uploader ) {
                uploader.hideUI();
                uploader.destroy();
            }
        });

        /**
         * Set the accepted file types for the uploader.
         *
         * @param {mpp.Uploader} uploader uploader instance.
         *
         * @param {string} type media type('photo', 'audio', 'video' etc ).
         */
        function setupUploaderFileTypes(uploader, type) {

            if (!uploadSettings || !uploadSettings.types) {
                return;
            }

            if ( ! type && uploadSettings.current_type ) {
                type = uploadSettings.current_type;
            }

            //if type is still not defined, go back
            if ( !type ) {
                return;
            }
            uploader.type = type;
            console.log(prepareExtensions(getExtensions(type)));
            uploader.setAllowedFileTypes(prepareExtensions(getExtensions(type)));

            /*
            uploader.updateFeedback(_mppUploadeSettings.allowed_type_messages[type]);
            if (uploader.dropzone) {
                jQuery(mpp_uploader.dropzone).find('.mpp-uploader-allowed-file-type-info').html(_mppData.allowed_type_messages[type]);
                jQuery(mpp_uploader.dropzone).find('.mpp-uploader-allowed-max-file-size-info').html(_mppData.max_allowed_file_size);
            }*/
        }

        /**
         * Retrieves acceptable file extensions for the given media type.
         *
         * @param {string} type media type(photo,video,audio etc).
         * @returns {string} comma separated file extension.
         */
        function getExtensions( type ) {
            let typeInfo =  ( type && uploadSettings.types && uploadSettings.types[type] ) ? uploadSettings.types[type] : {};
            return  ( typeInfo && typeInfo.extensions ) ? typeInfo.extensions : '';
        }

    });

    function getUploaderIDForActivity(activityID) {
        return 'mpp-activity-uploader-'+activityID;
    }

    function getUploader(id) {
        return _mppUploaders[id] ? window._mppUploaders[id]: null;
    }


    function mpp_log(...arg) {
        console.log(...arg);
    }
})(jQuery);
