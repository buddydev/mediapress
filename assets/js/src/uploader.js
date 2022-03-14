
// If you are using JavaScript/ECMAScript modules:
import _ from 'underscore';
import $ from 'jquery';
import Dropzone from 'dropzone';

import "./globals";
import EventRegistry from './utils/event-registry';

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

        console.log( "Uploader initialized");

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


        if( ! mpp.hooks.applyFilters('mpp_test_blabla', true, "HELLOOOOO" ) ) {
            return;
        }

        console.log("Initializing events");
        // attach extra parameters to Request when a new request is being created.
        this.on('sending' , this._appendParametersToRequest.bind(this));
        // on success.
        this.on('success' , this._onSucess.bind(this));
        //on upload error.
        this.on('error' , this._onError.bind(this));
        // when the remove is clicked.
        this.on('removedfile' , this._onFileRemove.bind(this));

        let events = this._events || {};

        for( let event in events ) {
            let callback = events[event];
            this.on(event ,callback);
           // this.uploader.on(event, callback);
        }
    }

    accept(file, done) {
    }

    _onFileRemove(file ) {
        if( ! file.attachmentID ) {
            this.cleanFeedback(file);
                return;
        }
        console.log("Removing file..."  + file.attachmentID );
        // send ajax request for deletion
        // clean.
    }

    _onSucess(file, response, e) {
        if( ! _.isObject(response)) {
            response = JSON.parse( response);
        }

        if( response.success ) {
            file.attachmentID = response.data.id;
        }
        console.log("response");
        console.log(response);
        console.log(response);
        console.log(response.data);
        console.log(response.data.filename);
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
       // this.params = {};
       // this.context = '';
        if( this._dropzone) {
            this._dropzone.removeAllFiles(true);
        }
    }

    destroy() {
        if (this._dropzone) {
            this._dropzone.destroy();
        }
        this._initialized = false;
        delete window._mppUploaders[this.id];
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
        let mediaIDs=[], files= this.getUploadedFiles();
        for(let file of files ) {
            if( ! file.attachmentID ) {
                continue;
            }

            mediaIDs.push(file.attachmentID);
        }
        return mediaIDs;
    }

    refresh() {
        if( this._dropzone) {
            this._dropzone.removeAllFiles(true);
            this.update({'allowedFileTypes': this.allowedFileTypes});
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
