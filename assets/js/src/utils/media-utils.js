
import $ from 'jquery';
import _ from 'underscore';

window._mppUploadSettings = window._mppUploadSettings || {};
// private copy to avoid user modifications.
const uploadSettings = _.clone(_mppUploadSettings);

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

    uploader.setAllowedFileTypes(prepareExtensions(getExtensions(type)));
    let allowedTypeMessage = uploadSettings.allowed_type_messages[type];
    let broseMessage = uploadSettings.type_browser_messages[type];

    uploader.updateHelpMessages({
        browse: broseMessage,
        fileSize: uploadSettings.max_allowed_file_size,
        allowedFileType: allowedTypeMessage
    });
}

function prepareExtensions(extension) {
    if( ! extension || ! extension.length ) {
        return '';
    }

    let exts = extension.split(','), preparedExts=[];
    for( let extension of exts ) {
        extension = extension.trim();

        if ( ! extension.length ) {
            continue;
        }

        if( '.' !== extension[0]) {
            extension = '.' + extension;
        }
        preparedExts.push(extension);
    }

    return preparedExts.join(',');
}

/**
 *
 * @returns {Object}Get media attached to the activity form
 */
function getAttachedMedia( $container ) {
    let media =  $container.data('mpp-attached-media');
    if( ! media || ! _.isArray(media)) {
        media = [];
    }

    return media;
}

/**
 * Add a media to attachment list
 *
 * @param int media_id
 * @returns {undefined}
 */
function addAttachedMedia($container, media_id) {
    let attached_media = $container.data('mpp-attached-media');

    if (!attached_media || !_.isArray(attached_media) ) {
        attached_media = [];
    }

    attached_media.push(media_id);
    $container.data('mpp-attached-media', attached_media);

}

/**
 * Remove an attached media id from dom
 *
 * @param int media_id
 * @returns {Boolean}
 */
function removeAttachedMedia($container, media_id) {

    var attached_media = $container.data('mpp-attached-media');

    if (!attached_media) {
        return false;
    } else {
        //attached_media = attached_media.split(',');
        attached_media = _.without(attached_media, '' + media_id);
    }

    $container.data('mpp-attached-media', attached_media);
}

function resetAttachedMedia( $container ) {
    $container.data('mpp-attached-media', []);
}

export {
    getExtensions,
    prepareExtensions,
    setupUploaderFileTypes,
    getAttachedMedia,
    addAttachedMedia,
    removeAttachedMedia,
    resetAttachedMedia,
};
