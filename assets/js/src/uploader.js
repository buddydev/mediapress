
// If you are using JavaScript/ECMAScript modules:
import _ from 'underscore';
import $ from 'jquery';
import Dropzone from 'dropzone';

import EventRegistry from './utils/event-registry';
// Keeps track of the uploader.
window._mppUploaders = window._mppUploaders || {};
export default class Uploader {

    constructor(id, args) {
        this.id = id;

        this.mediaType = args.mediType|| '';

        this.allowedFileTypes = args.allowedFileTypes || null;

        this.params = args.params || {};

        this.$el = null;
        this.$container = null;
        this.$wrapper = null;
        this.$feedback = null;
        this.isResetting = false;
        this.settings = _.extend({}, {
            'paramName': '_mpp_file',
            'showFeedback': true,
        },
            args
        );

        // default events map if passed.
        this._events = ( args.events && _.isObject(args.events) ) ? args.events : {};
        // keeps a list of all registered events and associated callbacks.
        this._eventRegistry = new EventRegistry();

        this._dropzone = null;
        // is the Uploader enabled?
        this._isEnabled = !!args.isEnabled;
        this._initialized = false;
        window._mppUploaders[this.id] = this;
    }

    // Initialize uploader.
    init() {
        // should we test if the uploader was initialized earlier?

        if( this._initialized ) {
            return false;
        }

        if( ! this.settings.el ) {
            return false;
        }

        this.$el = $(this.settings.el);

        if( ! this.$el.length ) {
            return false;
        }

        this.$container = this.$el.parents('.mpp-media-upload-container');
        this.$wrapper = this.$el.parents('.mpp-new-media-container');
        this.$feedback = this.$container.find('.mpp-feedback ul');

        this._initialized = true;
        this._createDropzone();
        this._bindEvents();

        if( this.settings.help) {
            this.updateHelpMessages(this.settings.help);
        }
    }

    updateHelpMessages(helpMessage) {
        if (!this.$container || !this.$container.length) {
            return;
        }

        if (helpMessage.browse) {
            this.$container.find('.dz-default .dz-button').html(helpMessage.browse);
        }

        if (helpMessage.allowedFileType) {
            this.$container.find('.mpp-uploader-allowed-file-type-info').html(helpMessage.allowedFileType);
        }

        if (helpMessage.allowedFileType) {
            this.$container.find('.mpp-uploader-allowed-max-file-size-info').html(helpMessage.fileSize);
        }

        this.showHelpMessages();
    }

    showHelpMessages() {
        if (!this.$container || !this.$container.length) {
            return;
        }
        this.$container.find('.mpp-dropzone-upload-help').show();
    }

    hideHelpMessages() {
        if (!this.$container || !this.$container.length) {
            return;
        }
        this.$container.find('.mpp-dropzone-upload-help').hide();
    }


    /**
     * Creates a new dropzone.
     */
    _createDropzone() {
        // when creating dropzone, use the
        this.settings.acceptedFiles = this.allowedFileTypes;
        //this.settings.accept = this.settings.accept || this.accept.bind(this);
        this._dropzone = new Dropzone(this.$el.get(0), this.settings);
    }

    /**
     * Binds various events.
     */
    _bindEvents() {

        // attach extra parameters to Request when a new request is being created.
        this.on('sending' , this._appendParametersToRequest.bind(this));
        // on success.
        this.on('success' , this._onSuccess.bind(this));
        //on upload error.
        this.on('error' , this._onError.bind(this));
        // when the remove is clicked.
        this.on('removedfile' , this._onFileRemove.bind(this));

        let events = this._events || {};

        for( let event in events ) {
            let callback = events[event];
            this.on(event ,callback);
        }
    }

    accept(file, done) {
    }

    _onFileRemove(file ) {
        if( ! file.attachmentID ) {
            this.cleanFeedback(file);
                return;
        }

        if( ! this.isResetting ) {
            this._deleteMedia( file.attachmentID );
        }
    }

    /// Trigger delete, deletes any trace of a Media
    _deleteMedia (id) {

        if (!id) {
            return false;
        }

        let nonce = this.settings.params.deleteMediaNonce? this.settings.params.deleteMediaNonce: this.settings.params._wpnonce;

        $.post(ajaxurl, {
            action: 'mpp_delete_media',
            media_id: id,
            cookie: encodeURIComponent(document.cookie),
            _wpnonce: nonce
        }, function (response) {

            if (typeof  response.success !== "undefined") {
                mpp.notify(response.message);
            } else {
                mpp.notify(response.message);
            }

        }, 'json');

        return false;
    }

    _onSuccess(file, response, e) {

        if( ! _.isObject(response)) {
            response = JSON.parse( response);
        }

        // Save file's attachmentID.
        if( response.success ) {
            file.attachmentID = response.data.id;
        }
        let data = response.data;
        console.log(data.filename);
    }

    _onError(file, response, e ) {

        if( ! _.isObject( response) || ! file.previewElement ) {
            return;
        }

        let message;
        file.previewElement.classList.add("dz-error");

        if (response.data && response.data.message ) {
            message = response.data.message;
        } else {
            message = "There was an issue uploading";
        }

        for (let node of file.previewElement.querySelectorAll(
            "[data-dz-errormessage]"
        )) {
            node.textContent = message;
        }

        this.$feedback.append(
         `<li id="mpp-upload-feedback-${file.upload.uuid}">${message}</li>`
        );
    }

    _appendParametersToRequest(file, xhr, formData ) {

        for( let paramName in this.params ) {
            formData.set( paramName, this.params[paramName]);
        }
    }

    cleanFeedback(file) {
        this.$feedback.find(`#mpp-upload-feedback-${file.upload.uuid}`).remove();
    }

    /**
     *
     * @param event  name or map of event:callback
     * @param callback Optional callback if event name is passed as first parameter.
     * @returns {Uploader}
     */
    on(event, callback) {

        if( ! this._dropzone) {
           return this;
        }

        if( _.isObject( event ) ) {
            for ( let evt in event) {
                this._dropzone.on(event , event[evt]);
                this._eventRegistry.add(event, event[evt]);
            }
        } else {
            this._dropzone.on(event , callback);
            this._eventRegistry.add(event, callback);
        }

        return this;
    }

    off(event, callback ) {

        if (  this._dropzone) {
            this._dropzone.off(event , callback);
            this._eventRegistry.remove(event, callback);
        }

        return this;
    }

    getAttachedEvents(eventName) {
        return this._eventRegistry.get(eventName);
    }

    getRegisteredEvents() {
        return this._eventRegistry.getAll();
    }

    getAttachedCallbacks(event) {
        return this._eventRegistry.get(event);
    }

    getMediaType() {
        return this.mediaType;
    }

    setMediaType( type ) {
        this.mediaType = type;
    }

    getAllowedFileTypes() {
        return this.allowedFileTypes;
    }

    setAllowedFileTypes( types ) {
        this.allowedFileTypes = _.isArray( types ) ? types.join(',') : types;
    }

    getParam(name) {
        return this.params[name] || null;
    }

    setParam(name, val ) {
       this.params[name] = val;
       return this;
    }

    getParams() {
        return this.params;
    }

    setParams(params) {
        this.params = params;
        return this;
    }

    getSettings() {
        return this.settings;
    }

    updateSettings(settings) {
        this.settings = _.isObject( settings ) ? settings : this.settings;
    }

    isEnabled() {
      return  this._isEnabled;
    }

    disable() {
        if( this._dropzone ) {
            this._dropzone.disable();
            this._isEnabled = false;
        }
    }

    enable() {
        if( this._dropzone ) {
            this._dropzone.enable();
        }
    }

    reset() {
        this.isResetting = true;
       // this.params = {};
       // this.context = '';
        if( this._dropzone) {
            this._dropzone.removeAllFiles(true);
        }

        if (this.$wrapper && mpp && mpp.mediaUtils) {
            mpp.mediaUtils.resetAttachedMedia(this.$wrapper);
        }
        this.isResetting = false;
    }

    destroy() {
        this.isResetting = true;
        if (this._dropzone) {
            this._dropzone.destroy();
        }
        this._initialized = false;
        delete window._mppUploaders[this.id];
        this.isResetting = false;
    }

    /**
     * Prints debug information.
     */
    debug() {

        for ( let key in this) {
            // do not print functions
            // dom reference(non serializable)
            // dropzone object(non serializable)
            if( '$'=== key[0] || '_dropzone' === key ||  _.isFunction(this[key])) {
                continue;
            }

            console.log( key + '=>' + JSON.stringify( this[key] ) );
        }
    }
    getUploadedFiles() {
        if( this._dropzone){
            return this._dropzone.getFilesWithStatus(Dropzone.SUCCESS);
        }
        return [];
    }

    //Get all successful uploaded media ids
    getUploadedMediaIDs() {
        let mediaIDs = [], files = this.getUploadedFiles();
        for (let file of files) {
            if (!file.attachmentID) {
                continue;
            }

            mediaIDs.push(file.attachmentID);
        }
        // include any appended media via other means(remote-media uses it currently).
        if (this.$wrapper && mpp && mpp.mediaUtils) {
            let attachedMediaIDs = mpp.mediaUtils.getAttachedMedia(this.$wrapper);
            for (let mediaID of attachedMediaIDs) {
                if (!mediaID) {
                    continue;
                }
                mediaIDs.push(mediaID);
            }
        }

        // also, we need to check for the
        return mediaIDs;
    }

    refresh() {
        if( this._dropzone) {
            this.isResetting = true;
            this._dropzone.removeAllFiles(true);
            this.update({'allowedFileTypes': this.allowedFileTypes});
            this.isResetting = false;
        }
    }
    // Hides the UI.
    hideUI() {
        if( ! this.$wrapper ) {
            return;
        }

        this.$wrapper.slideUp('slow', function () {
            $(this).removeClass('mpp-new-media-container-active').addClass('mpp-new-media-container-inactive');
        });
    }

    // Shows Ui back.
    showUI() {

        if( ! this.$wrapper ) {
            return;
        }

        this.$wrapper.slideDown('slow', function () {
            $(this).removeClass('mpp-new-media-container-inactive').addClass('mpp-new-media-container-active');
        });

        //this.$container.show();
    }

    isAttached() {
        return !!this.$el;
    }

    openFileChooser(){
        if( this._dropzone && this._dropzone.clickableElements.length ) {
            let $el =$( this._dropzone.clickableElements[this._dropzone.clickableElements.length-1]).first();
            if( $el.length ) {
             $el.click();
            }
        }
    }

    update(options) {
        let dropzone = this._dropzone;

        if (!dropzone) {
            return false;
        }

        if( options.allowedFileTypes ) {
            this.settings.allowedFileTypes = options.allowedFileTypes;
            dropzone.options.acceptedFiles = options.allowedFileTypes ;
            dropzone.hiddenFileInput.setAttribute(
                "accept",
                dropzone.options.acceptedFiles
            );
        }

        if( options.dictInvalidFileType ) {
            dropzone.options.dictInvalidFileType = options.dictInvalidFileType;
        }

        return true;
    }
}
