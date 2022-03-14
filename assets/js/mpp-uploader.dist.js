/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/js/src/globals.js":
/*!**********************************!*\
  !*** ./assets/js/src/globals.js ***!
  \**********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _uploader__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./uploader */ "./assets/js/src/uploader.js");
/* harmony import */ var _utils_media_utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utils/media-utils */ "./assets/js/src/utils/media-utils.js");
/* harmony import */ var _utils_lightbox_utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./utils/lightbox-utils */ "./assets/js/src/utils/lightbox-utils.js");
/* harmony import */ var _utils_notice__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./utils/notice */ "./assets/js/src/utils/notice.js");
 // Make mpp global object.





let mpp = window.mpp || {};
mpp.hooks = (0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__.createHooks)();
mpp.Uploader = _uploader__WEBPACK_IMPORTED_MODULE_1__["default"];
mpp.lightbox = _utils_lightbox_utils__WEBPACK_IMPORTED_MODULE_3__["default"]; //allow plugins/theme to override the notification

if (mpp.notify === undefined) {
  mpp.notify = _utils_notice__WEBPACK_IMPORTED_MODULE_4__.notify;
  mpp.clearNotice = _utils_notice__WEBPACK_IMPORTED_MODULE_4__.clearNotice;
}

window.mpp = mpp;
window.mpp_mejs_activate = _utils_media_utils__WEBPACK_IMPORTED_MODULE_2__.mpp_mejs_activate;
window.mpp_mejs_activate_lightbox_player = _utils_media_utils__WEBPACK_IMPORTED_MODULE_2__.mpp_mejs_activate_lightbox_player; // Keeps track of the uploader.

window._mppUploaders = window._mppUploaders || {};
window._mppUploadSettings = window._mppUploadSettings || {};

/***/ }),

/***/ "./assets/js/src/uploader.js":
/*!***********************************!*\
  !*** ./assets/js/src/uploader.js ***!
  \***********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ Uploader; }
/* harmony export */ });
/* harmony import */ var underscore__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! underscore */ "underscore");
/* harmony import */ var underscore__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(underscore__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! jquery */ "jquery");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var dropzone__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! dropzone */ "dropzone");
/* harmony import */ var dropzone__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(dropzone__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _globals__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./globals */ "./assets/js/src/globals.js");
/* harmony import */ var _utils_event_registry__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./utils/event-registry */ "./assets/js/src/utils/event-registry.js");
// If you are using JavaScript/ECMAScript modules:





class Uploader {
  constructor(id, args) {
    this.id = id;
    this.mediaType = args.mediType || '';
    this.allowedFileTypes = args.allowedFileTypes || null;
    this.params = args.params || {};
    this.$el = null;
    this.$container = null;
    this.$wrapper = null;
    this.$feedback = null;
    this.settings = underscore__WEBPACK_IMPORTED_MODULE_0___default().extend({}, {
      'paramName': '_mpp_file',
      'showFeedback': true
    }, args); // default events map if passed.

    this._events = args.events && underscore__WEBPACK_IMPORTED_MODULE_0___default().isObject(args.events) ? args.events : {}; // keeps a list of all registered events and associated callbacks.

    this._eventRegistry = new _utils_event_registry__WEBPACK_IMPORTED_MODULE_4__["default"]();
    this._dropzone = null; // is the Uploader enabled?

    this._isEnabled = !!args.isEnabled;
    this._initialized = false;
    window._mppUploaders[this.id] = this;
  } // Initialize uploader.


  init() {
    // should we test if the uploader was initialized earlier?
    if (this._initialized) {
      return false;
    }

    console.log("Uploader initialized");

    if (!this.settings.el) {
      return false;
    }

    this.$el = jquery__WEBPACK_IMPORTED_MODULE_1___default()(this.settings.el);

    if (!this.$el.length) {
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
    this.settings.acceptedFiles = this.allowedFileTypes; //this.settings.accept = this.settings.accept || this.accept.bind(this);

    this._dropzone = new (dropzone__WEBPACK_IMPORTED_MODULE_2___default())(this.$el.get(0), this.settings);
  }
  /**
   * Binds various events.
   */


  _bindEvents() {
    if (!mpp.hooks.applyFilters('mpp_test_blabla', true, "HELLOOOOO")) {
      return;
    }

    console.log("Initializing events"); // attach extra parameters to Request when a new request is being created.

    this.on('sending', this._appendParametersToRequest.bind(this)); // on success.

    this.on('success', this._onSucess.bind(this)); //on upload error.

    this.on('error', this._onError.bind(this)); // when the remove is clicked.

    this.on('removedfile', this._onFileRemove.bind(this));
    let events = this._events || {};

    for (let event in events) {
      let callback = events[event];
      this.on(event, callback); // this.uploader.on(event, callback);
    }
  }

  accept(file, done) {}

  _onFileRemove(file) {
    if (!file.attachmentID) {
      this.cleanFeedback(file);
      return;
    }

    console.log("Removing file..." + file.attachmentID); // send ajax request for deletion
    // clean.
  }

  _onSucess(file, response, e) {
    if (!underscore__WEBPACK_IMPORTED_MODULE_0___default().isObject(response)) {
      response = JSON.parse(response);
    }

    if (response.success) {
      file.attachmentID = response.data.id;
    }

    console.log("response");
    console.log(response);
    console.log(response);
    console.log(response.data);
    console.log(response.data.filename);
  }

  _onError(file, response, e) {
    if (!underscore__WEBPACK_IMPORTED_MODULE_0___default().isObject(response) || !file.previewElement) {
      return;
    }

    let message;
    file.previewElement.classList.add("dz-error");

    if (response.data && response.data.message) {
      message = response.data.message;
    } else {
      message = "There was an issue uploading";
    }

    for (let node of file.previewElement.querySelectorAll("[data-dz-errormessage]")) {
      node.textContent = message;
    }

    this.$feedback.append(`<li id="mpp-upload-feedback-${file.upload.uuid}">${message}</li>`);
  }

  _appendParametersToRequest(file, xhr, formData) {
    for (let paramName in this.params) {
      formData.set(paramName, this.params[paramName]);
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
    if (!this._dropzone) {
      return this;
    }

    if (underscore__WEBPACK_IMPORTED_MODULE_0___default().isObject(event)) {
      for (let evt in event) {
        this._dropzone.on(event, event[evt]);

        this._eventRegistry.add(event, event[evt]);
      }
    } else {
      this._dropzone.on(event, callback);

      this._eventRegistry.add(event, callback);
    }

    return this;
  }

  off(event, callback) {
    if (this._dropzone) {
      this._dropzone.off(event, callback);

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

  setMediaType(type) {
    this.mediaType = type;
  }

  getAllowedFileTypes() {
    return this.allowedFileTypes;
  }

  setAllowedFileTypes(types) {
    this.allowedFileTypes = underscore__WEBPACK_IMPORTED_MODULE_0___default().isArray(types) ? types.join(',') : types;
  }

  getParam(name) {
    return this.params[name] || null;
  }

  setParam(name, val) {
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
    this.settings = underscore__WEBPACK_IMPORTED_MODULE_0___default().isObject(settings) ? settings : this.settings;
  }

  isEnabled() {
    return this._isEnabled;
  }

  disable() {
    if (this._dropzone) {
      this._dropzone.disable();

      this._isEnabled = false;
    }
  }

  enable() {
    if (this._dropzone) {
      this._dropzone.enable();
    }
  }

  reset() {
    // this.params = {};
    // this.context = '';
    if (this._dropzone) {
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
    for (let key in this) {
      // do not print functions
      // dom reference(non serializable)
      // dropzone object(non serializable)
      if ('$' === key[0] || '_dropzone' === key || underscore__WEBPACK_IMPORTED_MODULE_0___default().isFunction(this[key])) {
        continue;
      }

      console.log(key + '=>' + JSON.stringify(this[key]));
    }
  }

  getUploadedFiles() {
    if (this._dropzone) {
      return this._dropzone.getFilesWithStatus((dropzone__WEBPACK_IMPORTED_MODULE_2___default().SUCCESS));
    }

    return [];
  } //Get all successful uploaded media ids


  getUploadedMediaIDs() {
    let mediaIDs = [],
        files = this.getUploadedFiles();

    for (let file of files) {
      if (!file.attachmentID) {
        continue;
      }

      mediaIDs.push(file.attachmentID);
    }

    return mediaIDs;
  }

  refresh() {
    if (this._dropzone) {
      this._dropzone.removeAllFiles(true);

      this.update({
        'allowedFileTypes': this.allowedFileTypes
      });
    }
  } // Hides the UI.


  hideUI() {
    if (!this.$wrapper) {
      return;
    }

    this.$wrapper.slideUp('slow', function () {
      jquery__WEBPACK_IMPORTED_MODULE_1___default()(this).removeClass('mpp-new-media-container-active').addClass('mpp-new-media-container-inactive');
    });
  } // Shows Ui back.


  showUI() {
    if (!this.$wrapper) {
      return;
    }

    this.$wrapper.slideDown('slow', function () {
      jquery__WEBPACK_IMPORTED_MODULE_1___default()(this).removeClass('mpp-new-media-container-inactive').addClass('mpp-new-media-container-active');
    }); //this.$container.show();
  }

  isAttached() {
    return !!this.$el;
  }

  openFileChooser() {
    if (this._dropzone && this._dropzone.clickableElements.length) {
      let $el = jquery__WEBPACK_IMPORTED_MODULE_1___default()(this._dropzone.clickableElements[this._dropzone.clickableElements.length - 1]).first();

      if ($el.length) {
        $el.click();
      }
    }
  }

  update(options) {
    let dropzone = this._dropzone;

    if (!dropzone) {
      return false;
    }

    if (options.allowedFileTypes) {
      this.settings.allowedFileTypes = options.allowedFileTypes;
      dropzone.options.acceptedFiles = options.allowedFileTypes;
      dropzone.hiddenFileInput.setAttribute("accept", dropzone.options.acceptedFiles);
    }

    if (options.dictInvalidFileType) {
      dropzone.options.dictInvalidFileType = options.dictInvalidFileType;
    }

    return true;
  }

}

/***/ }),

/***/ "./assets/js/src/utils/event-registry.js":
/*!***********************************************!*\
  !*** ./assets/js/src/utils/event-registry.js ***!
  \***********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ EventRegistry; }
/* harmony export */ });
/// Event helper is based on Dropzone.js's Emitter class
// @see https://github.com/dropzone/dropzone/blob/main/src/emitter.js
class EventRegistry {
  constructor() {
    this._callbacks = {};
  } // Add an event listener for given event


  add(event, fn) {
    // Create namespace for this event
    if (!this._callbacks[event]) {
      this._callbacks[event] = [];
    }

    this._callbacks[event].push(fn);

    return this;
  } // Remove event listener for given event. If fn is not provided, all event
  // listeners for that event will be removed. If neither is provided, all
  // event listeners will be removed.


  remove(event, fn) {
    if (!this._callbacks || arguments.length === 0) {
      this._callbacks = {};
      return this;
    } // specific event


    let callbacks = this._callbacks[event];

    if (!callbacks) {
      return this;
    } // remove all handlers


    if (arguments.length === 1) {
      delete this._callbacks[event];
      return this;
    } // remove specific handler


    for (let i = 0; i < callbacks.length; i++) {
      let callback = callbacks[i];

      if (callback === fn) {
        callbacks.splice(i, 1);
        break;
      }
    }

    return this;
  }

  get(event) {
    // specific event
    let callbacks = this._callbacks[event];

    if (!callbacks) {
      return null;
    }

    return callbacks;
  }

  getAll() {
    return this._callbacks;
  }

}

/***/ }),

/***/ "./assets/js/src/utils/functions.js":
/*!******************************************!*\
  !*** ./assets/js/src/utils/functions.js ***!
  \******************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "getQueryParameter": function() { return /* binding */ getQueryParameter; },
/* harmony export */   "getURLParameter": function() { return /* binding */ getURLParameter; },
/* harmony export */   "prepareExtensions": function() { return /* binding */ prepareExtensions; }
/* harmony export */ });
function prepareExtensions(extension) {
  if (!extension || !extension.length) {
    return '';
  }

  let exts = extension.split(','),
      preparedExts = [];

  for (let extension of exts) {
    extension = extension.trim();

    if (!extension.length) {
      continue;
    }

    if ('.' !== extension[0]) {
      extension = '.' + extension;
    }

    preparedExts.push(extension);
  }

  return preparedExts.join(',');
}
/**
 * Get the  value of a query parameter from the url
 *
 * @param param string the query var to be found.
 * @param queryString the query string.
 * @returns string
 */


function getQueryParameter(param, queryString) {
  var items;

  if (typeof queryString === "undefined" || !queryString.length) {
    return false;
  }

  var data_fields = queryString.split('&');

  for (var i = 0; i < data_fields.length; i++) {
    items = data_fields[i].split('=');

    if (items[0] == param) {
      return items[1];
    }
  }

  return false;
}
/**
 * Extract a query variable from url
 *
 * @param param string
 * @param url string
 * @returns {Boolean|String|mixed}
 */


function getURLParameter(param, url) {
  let chunks = url.split('?');
  return getQueryParameter(param, chunks.length > 1 ? chunks[1] : '');
}



/***/ }),

/***/ "./assets/js/src/utils/lightbox-utils.js":
/*!***********************************************!*\
  !*** ./assets/js/src/utils/lightbox-utils.js ***!
  \***********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "jquery");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
//Lightbox utility API.

/* harmony default export */ __webpack_exports__["default"] = ({
  /**
   * Open Lightbox with the Media Collection.
   *
   * @param {array} items array of media items
   * @param {int} position numeric position of the media to be shown by default
   * @param {string} fallback_url open this url on error.
   */
  open: function (items, position, fallback_url) {
    if (items.length < 1) {
      window.location = fallback_url;
      return;
    }

    jquery__WEBPACK_IMPORTED_MODULE_0___default().magnificPopup.open({
      items: items,
      type: 'inline',
      closeBtnInside: false,
      preload: [1, 3],
      closeOnBgClick: true,
      showCloseBtn: true,
      closeMarkup: '<button title="%title%" type="button" class="mfp-close mpp-lightbox-close-btn">&#215;</button>',
      gallery: {
        enabled: true,
        navigateByImgClick: true,
        //arrowMarkup: '',// disabled default arrows
        preload: [0, 1] // Will preload 0 - before current, and 1 after the current image

      }
    }, position); // new api.

    jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).trigger('mpp:lightbox:opened', [items, position]); // backaward compatibility.

    jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).trigger('mpp_lightbox_opened');
  },
  //open lightbox

  /**
   * Update the lightbox content with given html.
   *
   * @param {string} content content.
   *
   * @returns {boolean}
   */
  update: function (content) {
    if (!mpp.lightbox.isLoaded()) {
      return false;
    }

    var magnificPopup = (jquery__WEBPACK_IMPORTED_MODULE_0___default().magnificPopup.instance);
    magnificPopup.currItem.src = content;
    magnificPopup.items[magnificPopup.index] = magnificPopup.currItem;
    magnificPopup.updateItemHTML();
  },
  // update current open box with the content.
  gallery: function (gallery_id, position, url, media_id) {
    var $lightbox = this; //get the details from server.

    jquery__WEBPACK_IMPORTED_MODULE_0___default().post(ajaxurl, {
      action: 'mpp_fetch_gallery_media',
      gallery_id: gallery_id,
      cookie: encodeURIComponent(document.cookie)
    }, function (response) {
      if (response.items === undefined) {
        return; //should we notify too?
      }

      var items = response.items; // If media ID is given

      if (typeof media_id !== 'undefined') {
        position = get_media_position_in_collection(media_id, items);
      }

      $lightbox.open(items, position, url);
    }, 'json');
  },

  /**
   * Open one or more media(photo) in lightbox
   *
   * @param {string} media_ids comma separated list of media ids
   * @param {integer} position which media to display as first
   * @param {string} url fallback url to open if lightbox is unable to open
   */
  media: function (media_ids, position, url, media_id) {
    var $lightbox = this;
    jQuery.post(ajaxurl, {
      action: 'mpp_lightbox_fetch_media',
      media_ids: media_ids,
      cookie: encodeURIComponent(document.cookie)
    }, function (response) {
      if (response.items === undefined) {
        return; //should we notify too?
      }

      var items = response.items; // If media ID is given

      if (typeof media_id !== 'undefined') {
        position = get_media_position_in_collection(media_id, items);
      }

      $lightbox.open(items, position, url);
    }, 'json');
  },
  activity: function (activity_id, position, url, media_id) {
    //get the details from server
    var $lightbox = this;
    jQuery.post(ajaxurl, {
      action: 'mpp_fetch_activity_media',
      activity_id: activity_id,
      cookie: encodeURIComponent(document.cookie)
    }, function (response) {
      if (response.items === undefined) {
        return; //should we notify too?
      }

      var items = response.items; // If media ID is given

      if (typeof media_id !== 'undefined') {
        position = get_media_position_in_collection(media_id, items);
      }

      $lightbox.open(items, position, url);
    }, 'json');
  },
  //open for activity

  /**
   * Reload given media id.
   *
   * @param media_id
   */
  reloadMedia: function (media_id) {
    var $lightbox = this;
    jq.post(ajaxurl, {
      action: 'mpp_reload_lightbox_media',
      'media_id': media_id
    }, function (response) {
      if (response.success) {
        // success
        $lightbox.update(response.data.content);
      } else {// Failed.
      }
    });
  },

  /**
   * Reload the current lightbox media. It acts as refresh.
   *
   * @returns {boolean}
   */
  reloadCurrentMedia: function () {
    var media_id = this.getCurrentMediaID();

    if (media_id) {
      this.reloadMedia(media_id);
      return true;
    }

    return false;
  },

  /**
   * Get Current Media Opened in the lightbox.
   *
   * returns 0 for invalid call.
   *
   * @returns {int}
   */
  getCurrentMediaID: function () {
    if (!this.isLoaded() || !this.isOpen()) {
      return 0;
    }

    var magnificPopup = jQuery.magnificPopup.instance;
    var data = magnificPopup.currItem.data;

    if (typeof data.id !== 'undefined') {
      return data.id;
    }

    return 0;
  },

  /**
   * Is Lightbox Loaded?
   *
   * @returns {boolean}
   */
  isLoaded: function () {
    return jQuery.fn.magnificPopup !== undefined;
  },

  /**
   * Is lightbox Open?
   *
   * @returns {boolean}
   */
  isOpen: function () {
    return jQuery.magnificPopup.instance.isOpen === true;
  },
  // backward compatibility
  is_lightbox_loaded: function () {
    return this.isLoaded();
  },

  /**
   * Check if lightbox supports given type.
   *
   * @param type
   * @returns {Boolean}
   */
  supportsMediaType: function (type) {
    // type unknown, or nothing is disabled or type is enabled.
    return !type || !_mppSettings.lightboxDisabledTypes || !_mppSettings.lightboxDisabledTypes[type];
  }
});
/**
 * Find position of the item in the given collection.
 *
 * @param media_id
 * @param items
 * @returns {number}
 */

function get_media_position_in_collection(media_id, items) {
  var index = 0;
  var position = 0; // calculate the position of this media in the collection

  for (var i in items) {
    if (items[i].id == media_id) {
      position = index;
    }

    index++;
  }

  return position;
}

/***/ }),

/***/ "./assets/js/src/utils/media-utils.js":
/*!********************************************!*\
  !*** ./assets/js/src/utils/media-utils.js ***!
  \********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "mpp_mejs_activate": function() { return /* binding */ mpp_mejs_activate; },
/* harmony export */   "mpp_mejs_activate_lightbox_player": function() { return /* binding */ mpp_mejs_activate_lightbox_player; }
/* harmony export */ });
/**
 * Activate audi/video player(MediElelement.js player)
 *
 * @param {type} activity_id
 * @returns {undefined}
 */
function mpp_mejs_activate(activity_id) {
  /* global mejs, _wpmejsSettings */
  var jq = jQuery; //when document is loading, mediaelementplayer will be undefined, a workaround to avoid double activating it

  if (jq.fn.mediaelementplayer === undefined) {
    return;
  }

  var settings = {};

  if (typeof _wpmejsSettings !== 'undefined') {
    settings = _wpmejsSettings;
  }

  settings.success = function (mejs) {
    var autoplay, loop;

    if ('flash' === mejs.pluginType) {
      autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
      loop = mejs.attributes.loop && 'false' !== mejs.attributes.loop;
      autoplay && mejs.addEventListener('canplay', function () {
        mejs.play();
      }, false);
      loop && mejs.addEventListener('ended', function () {
        mejs.play();
      }, false);
    }
  };

  jq('.wp-audio-shortcode, .wp-video-shortcode', jq('#activity-' + activity_id)).mediaelementplayer(settings);
  jq('.wp-playlist', jq('#activity-' + activity_id)).each(function () {
    return new WPPlaylistView({
      el: this
    });
  });
}
/**
 * Activate audio/video player(MediElelement.js player) in the lightbox.
 *
 * @returns {undefined}
 */


function mpp_mejs_activate_lightbox_player() {
  /* global mejs, _wpmejsSettings */
  var jq = jQuery; //when document is loading, mediaelementplayer will be undefined, a workaround to avoid double activating it

  if (jq.fn.mediaelementplayer === undefined) {
    return;
  }

  var settings = {};

  if (typeof _wpmejsSettings !== 'undefined') {
    settings = _wpmejsSettings;
  }

  settings.success = function (mejs) {
    var autoplay, loop;

    if ('flash' === mejs.pluginType) {
      autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
      loop = mejs.attributes.loop && 'false' !== mejs.attributes.loop;
      autoplay && mejs.addEventListener('canplay', function () {
        mejs.play();
      }, false);
      loop && mejs.addEventListener('ended', function () {
        mejs.play();
      }, false);
    }
  };

  jq('.wp-audio-shortcode, .wp-video-shortcode', jq('.mfp-content')).mediaelementplayer(settings);
  jq('.wp-playlist', jq('.mfp-content')).each(function () {
    return new WPPlaylistView({
      el: this
    });
  });
}



/***/ }),

/***/ "./assets/js/src/utils/notice.js":
/*!***************************************!*\
  !*** ./assets/js/src/utils/notice.js ***!
  \***************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "clearNotice": function() { return /* binding */ clearNotice; },
/* harmony export */   "notify": function() { return /* binding */ notify; }
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "jquery");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);


function notify(message, error) {
  var class_name = 'updated success';

  if (error !== undefined) {
    class_name = 'error';
  }

  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#mpp-notice-message').remove(); // will it have side effects?

  var selectors = ['#mpp-container', '#whats-new-form', '.mpp-upload-shortcode']; //possible containers in preferred order

  var container_selector = ''; //default

  for (var i = 0; i < selectors.length; i++) {
    if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(selectors[i]).get(0)) {
      container_selector = selectors[i];
      break;
    }
  } //if container exists, let us append the message


  if (container_selector) {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(container_selector).prepend('<div id="mpp-notice-message" class="mpp-notice mpp-template-notice ' + class_name + '"><p>' + message + '</p></div>').show();
  }
}

function clearNotice() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#mpp-notice-message').remove();
}



/***/ }),

/***/ "dropzone":
/*!***************************!*\
  !*** external "Dropzone" ***!
  \***************************/
/***/ (function(module) {

module.exports = window["Dropzone"];

/***/ }),

/***/ "underscore":
/*!********************!*\
  !*** external "_" ***!
  \********************/
/***/ (function(module) {

module.exports = window["_"];

/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ (function(module) {

module.exports = window["jQuery"];

/***/ }),

/***/ "@wordpress/hooks":
/*!*******************************!*\
  !*** external ["wp","hooks"] ***!
  \*******************************/
/***/ (function(module) {

module.exports = window["wp"]["hooks"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!***********************************!*\
  !*** ./assets/js/mpp-uploader.js ***!
  \***********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "jquery");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _src_utils_functions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./src/utils/functions */ "./assets/js/src/utils/functions.js");
/* harmony import */ var _src_globals__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./src/globals */ "./assets/js/src/globals.js");
/* global window */




(function ($) {
  // private copy to avoid user modifications.
  const uploadSettings = _.clone(_mppUploadSettings);

  $(document).ready(function () {
    // context defines from where it was uploaded
    let context = uploadSettings.params.context,
        gallery_id = 0;

    if ($('#mpp-context').length) {
      context = $('#mpp-context').val();
    }

    if ($('#mpp-upload-gallery-id').length) {
      gallery_id = $('#mpp-upload-gallery-id').val();
    }

    let extensions = uploadSettings.types && uploadSettings.current_type && uploadSettings.types[uploadSettings.current_type] ? uploadSettings.types[uploadSettings.current_type] : '';
    let uploader = new mpp.Uploader('gallery', {
      el: '#mpp-upload-dropzone-gallery',
      url: uploadSettings.url,
      params: _.extend({}, uploadSettings.params, {
        'context': context,
        'gallery_id': gallery_id
      }),
      allowedFileTypes: (0,_src_utils_functions__WEBPACK_IMPORTED_MODULE_1__.prepareExtensions)(extensions),
      addRemoveLinks: true
    });

    if (mpp.hooks) {
      mpp.hooks.addFilter("mpp_test_blabla", 'mpp', function (val, arg1) {
        console.log("Value=" + val);
        console.log("Arg=" + arg1);
        return true;
      }, 10);
    }

    console.log("Before init"); // initialize.

    uploader.init();
  });
})((jquery__WEBPACK_IMPORTED_MODULE_0___default()));
}();
/******/ })()
;
//# sourceMappingURL=mpp-uploader.dist.js.map