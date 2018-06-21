/* global jQuery, ajaxurl, mpp, _mppData, WPPlaylistView, mpp_add_attached_media, mpp_reset_attached_media */
jQuery(document).ready(function () {

    var jq = jQuery;
    /**
     * Bulk Actions checkbox on Gallery-> Edit Media page
     * Check/uncheck based on user action
     */
    jq(document).on('click', '#mpp-check-all', function () {

        if (jq(this).is(':checked')) {
            // check all others
            jq('input.mpp-delete-media-check').prop('checked', true);

        } else {
            // uncheck all
            jq('input.mpp-delete-media-check').prop('checked', false);
        }
    });

    /**
     * Single Gallery -> Edit Media page
     * Handle publish to activity action
     */
    jq(document).on('click', '.mpp-publish-to-activity-button', function () {

        var $this = jq(this);
        var url = $this.attr('href');
        var gallery_id = get_var_in_url('gallery_id', url);
        var nonce = get_var_in_url('_wpnonce', url);

        jq.post(ajaxurl, {
                action: 'mpp_publish_gallery_media',
                gallery_id: gallery_id,
                _wpnonce: nonce,
                cookie: encodeURIComponent(document.cookie)
            }, function (response) {
                var error;
                if (response.error !== undefined) {
                    error = 1;
                }
                //hide the button
                jq('#mpp-unpublished-media-info').hide();

                mpp.notify(response.message, error);

            },

            'json');

        return false;

    });
    /**
     * Single Gallery->Edit Media
     * Handle delete unpublished media
     */
    jq(document).on('click', '.mpp-delete-unpublished-media-button', function () {

        var $this = jq(this);
        var url = $this.attr('href');
        var gallery_id = get_var_in_url('gallery_id', url);
        var nonce = get_var_in_url('_wpnonce', url);

        jq.post(ajaxurl, {
                action: 'mpp_hide_unpublished_media',
                gallery_id: gallery_id,
                _wpnonce: nonce,
                cookie: encodeURIComponent(document.cookie)
            }, function (response) {

                var error;
                if (typeof  response.error !== "undefined") {
                    error = 1;
                }
                //hide the button
                jq('#mpp-unpublished-media-info').hide();

                mpp.notify(response.message, error);

            },

            'json');

        return false;

    });

    /**
     * Single Gallery->Reorder
     * Enable Media sorting/reodering on manage gallery/reorder page
     *
     */
    if (jq.fn.sortable !== undefined) {
        jq("#mpp-sortable").sortable({opacity: 0.6, cursor: 'move'});
    }
    /**
     * Activity upload Form handling
     * Prepend the upload buttons to Activity form
     */

    jq('#whats-new-options').prepend(jq('#mpp-activity-upload-buttons'));
    //jq('#whats-new-post-in-box').prepend( jq( '#mpp-activity-upload-buttons') );


    //Create an instance of uploader for activity
    //Creat an instance of mpp Uploader and attach it to the activity upload elements
    mpp.activity_uploader = new mpp.Uploader({
        container: 'body',
        dropzone: '#mpp-upload-dropzone-activity',
        browser: '#mpp-upload-media-button-activity',
        feedback: '#mpp-upload-feedback-activity',
        media_list: '#mpp-uploaded-media-list-activity',//where we will list the media
        uploading_media_list: _.template("<li id='<%= id %>'><span class='mpp-attached-file-name'><%= name %></span>(<span class='mpp-attached-file-size'><%= size %></spa>)<span class='mpp-remove-file-attachment'>x</span> <b></b></li>"),
        uploaded_media_list: _.template("<li class='mpp-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>' data-media-id='<%= id %>'><img src='<%= url %>' /><a href='#' class='mpp-delete-uploaded-media-item'>x</a></li>"),

        success: function (file) {
            //let the Base class success method handle the things
            mpp.Uploader.prototype.success(file);
            //save media id in cookie
            mpp_add_attached_media(file.get('id'));

        },
        error: function (reason, data, file) {
            //let the Base class error handler do its job.
            mpp.Uploader.prototype.error(reason, data, file);
            _mpp_activity_upload_error();
        },
        complete: function () {
            mpp.Uploader.prototype.complete();
            _mpp_activity_upload_complete();
        },
        onAddFile: function (file) {
            mpp.Uploader.prototype.onAddFile(file);

        },
        allFilesAdded: function (up) {
            _mpp_activity_all_files_added();
        },
        isRestricted: function (up, file) {

            return false; //return true to restrict upload
            /*this.error( "Unable to add", {}, file );
            if( ! this.media_list )
                return;
            //show loader
            jq( '.mpp-loader', this.media_list ).hide();

            return true;
            */
        }

    });

    //When any of the media icons(audio/video etc) is clicked
    //show the dropzone

    jq(document).on('click', '#mpp-activity-upload-buttons a', function () {

        var el = jq(this);
        //set upload context as activity
        mpp.activity_uploader.param('context', 'activity');
        var dropzone = mpp.activity_uploader.dropzone;//.remove();
        //set current type as the clicked button
        _mppData.current_type =  jq(this).data('media-type');//use id as type detector , may be photo/audio/video
        mpp_setup_uploader_file_types(mpp.activity_uploader);

        dropzone.show();
        // refresh to reposition the shim.
        mpp.activity_uploader.refresh();
        //this may not work on mobile
        //check
        // option to disable in 1.4.0
        if( ! _mppData.activity_disable_auto_file_browser ) {
            jq('#mpp-upload-media-button-activity').click();//simulate click;
        }

        jq('.mpp-remote-add-media-row-activity').show();

        return false;
    });

    //Intercept the ajax actions to check if there was an upload from activity
    //if yes, when it is complete, hide the dropzone

    //filter ajax request but only if the activity post form is present
    if (jq('#whats-new-form').get(0) || jq('#swa-whats-new-form').get(0)) {


        jQuery(document).ajaxSend(function (event, jqxhr, settings) {

            if (is_post_update(settings.data)) {
                var attached_media = mpp_get_attached_media();

                if (attached_media) {
                    settings.data = settings.data + '&mpp-attached-media=' + attached_media;
                    mpp_reset_attached_media();
                }
            }
        });


        jq(document).ajaxComplete(function (evt, xhr, options) {

            var action = get_var_in_query('action', options.data);

            //switch
            switch (action) {

                case 'post_update':
                case 'swa_post_update':
                    mpp.activity_uploader.hide_ui(); //clear the list of uploaded media
                    jq('.mpp-remote-add-media-row-activity').hide();
                    break;
            }

        });

    }

    // called when all selected files are enqueued.
    function _mpp_activity_all_files_added() {
        // disable submit.
        jq('#aw-whats-new-submit').prop('disabled', true);
    }

    // called when activity upload is complete.
    function _mpp_activity_upload_complete() {
        // enable on upload complete.
        jq('#aw-whats-new-submit').prop('disabled', false);
    }

    // on activity upload error.
    function _mpp_activity_upload_error() {
        // enable on error.
        jq('#aw-whats-new-submit').prop('disabled', false);
    }

    function is_post_update(qs) {
        if (!qs) {
            return false;
        }

        var action = get_var_in_query('action', qs);

        if (action === 'post_update' || action === 'swa_post_update') {
            return true;
        }

        return false;
    }


    /** For single gallery  upload */

    mpp.guploader = new mpp.Uploader({
        container: 'body',
        dropzone: '#mpp-upload-dropzone-gallery',
        browser: '#mpp-upload-media-button-gallery',
        feedback: '#mpp-upload-feedback-gallery',
        media_list: '#mpp-uploaded-media-list-gallery',//where we will list the media
        uploading_media_list: _.template("<li id='<%= id %>'><span class='mpp-attached-file-name'><%= name %></span>(<span class='mpp-attached-file-size'><%= size %></spa>)<span class='mpp-remove-file-attachment'>x</span> <b></b></li>"),
        uploaded_media_list: _.template("<li class='mpp-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>'><img src='<%= url %>' /></li>")


    });

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
    /// Trigger delete, deletes any trace of a Media
    // I hurts when people delete loved ones from their heart, but deleting a media is fine
    jq(document).on('click', '.mpp-uploading-media-list .mpp-delete-uploaded-media-item', function () {

        var $this = jq(this);
        var $parent = jq($this.parent()); //parents are very important in our life, how can we forget them
        //is the data-media-id attribute set, like parents keep their child in heart, our $parent does too
        var id = $parent.data('media-id');

        if (!id) {
            return false;
        }

        var $img = $parent.find('img');
        var old_image = $img.attr('src');
        //set the loader icon as source

        $img.attr('src', _mppData.loader_src);
        $this.hide();//no delete button

        //get the security pass for clearance because unidentified intruders are not welcome in the family
        var nonce = jq('#_mpp_manage_gallery_nonce').val();

        //Now is the time to take action,
        jq.post(ajaxurl, {
            action: 'mpp_delete_media',
            media_id: id,
            cookie: encodeURIComponent(document.cookie),
            _wpnonce: nonce
        }, function (response) {
            //how rude the nature is
            //you deleted my media and still sending me message
            if (typeof  response.success !== "undefined") {
                $parent.remove(); //can't believe the parent is going away too

                mpp_remove_attached_media(id);
                mpp.notify(response.message); //let the superman know what consequence his action has done

            } else {
                //something went wrong, perhaps the media escaped the deletion
                $this.show();
                $img.attr('src', old_image);

                mpp.notify(response.message);
            }
            //enough, let us hide the round round feedback


        }, 'json');

        return false;
    });

    //allow plugins/theme to override the notification
    if (mpp.notify === undefined) {

        mpp.notify = function (message, error) {

            var class_name = 'updated success';
            if (error !== undefined) {
                class_name = 'error';
            }

            jq('#mpp-notice-message').remove();// will it have side effects?
            var selectors = ['#mpp-container', '#whats-new-form', '.mpp-upload-shortcode']; //possible containers in preferred order
            var container_selector = '';//default

            for (var i = 0; i < selectors.length; i++) {
                if (jQuery(selectors[i]).get(0)) {
                    container_selector = selectors[i];
                    break;
                }
            }

            //if container exists, let us append the message
            if (container_selector) {
                jq(container_selector).prepend('<div id="mpp-notice-message" class="mpp-notice mpp-template-notice ' + class_name + '"><p>' + message + '</p></div>').show();
            }
        };

        mpp.clearNotice = function () {
            jQuery('#mpp-notice-message').remove();
        };
    }

    //Lightbox utility API.
    mpp.lightbox = {
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

            jQuery.magnificPopup.open({
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
                },
                position
            );

            // new api.
            jQuery(document).trigger('mpp:lightbox:opened', [items, position] );

            // backaward compatibility.
            jQuery(document).trigger('mpp_lightbox_opened');
        }, //open lightbox

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
            var magnificPopup = jQuery.magnificPopup.instance;
            magnificPopup.currItem.src = content;
            magnificPopup.items[magnificPopup.index] = magnificPopup.currItem;
            magnificPopup.updateItemHTML();
        }, // update current open box with the content.

        gallery: function (gallery_id, position, url, media_id) {
            var $lightbox = this;
            //get the details from server.
            jQuery.post(ajaxurl, {
                    action: 'mpp_fetch_gallery_media',
                    gallery_id: gallery_id,
                    cookie: encodeURIComponent(document.cookie)
                },
                function (response) {
                    if (response.items === undefined) {
                        return;//should we notify too?
                    }

                    var items = response.items;
                    // If media ID is given
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
                },
                function (response) {
                    if (response.items === undefined) {
                        return;//should we notify too?
                    }

                    var items = response.items;
                    // If media ID is given
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
                },
                function (response) {
                    if (response.items === undefined) {
                        return;//should we notify too?
                    }

                    var items = response.items;
                    // If media ID is given
                    if (typeof media_id !== 'undefined') {
                        position = get_media_position_in_collection(media_id, items);
                    }

                    $lightbox.open(items, position, url);

                }, 'json');
        }, //open for activity

        /**
         * Reload given media id.
         *
         * @param media_id
         */
        reloadMedia: function (media_id) {
            var $lightbox = this;
            jq.post(ajaxurl, {action: 'mpp_reload_lightbox_media', 'media_id': media_id}, function (response) {
                if (response.success) {
                    // success
                    $lightbox.update(response.data.content);
                } else {
                    // Failed.
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
        }
    };

    // Lightbox Code
    var isLightBoxLoaded = mpp.lightbox.isLoaded();
    // Lightbox popup for activity.
    if (isLightBoxLoaded && _mppData.enable_activity_lightbox) {

        jq(document).on('click.mpp:activity:lightbox.mpp:lightbox', '.mpp-activity-media-list a.mpp-activity-media, .mpp-activity-media-list a.mpp-activity-item-title', function () {

            var $this = jq(this);
            if ($this.hasClass('mpp-no-lightbox')) {
                return;
            }
            var activity_id = $this.data('mpp-activity-id');
            var $parent = $this.parents('.mpp-activity-item-content');
            var position = 0;
            if ($parent.get(0)) {
                position = $this.parents('.mpp-container').find('.mpp-activity-item-content').index($parent);
                // newer template
                // or non photo media

            } else if (!activity_id && $this.find('img.mpp-attached-media-item').get(0)) {
                activity_id = $this.find('img.mpp-attached-media-item').data('mpp-activity-id');
            }

            var url = $this.attr('href');
            if (!activity_id) {
                return true;
            }
            var media_id = $this.data('mpp-media-id');
            //open lightbox
            mpp.lightbox.activity(activity_id, position, url, media_id);

            return false;
        });
        //for comment
        jq(document).on('click.mpp:activity:comment:lightbox.mpp:lightbox', '.mpp-activity-comment-media-list a', function () {

            var $this = jq(this);
            if ($this.hasClass('mpp-no-lightbox')) {
                return;
            }
            var media_id = $this.data('mpp-media-id');
            var position = 0;
            var url = $this.attr('href');
            if (!media_id) {
                return true;
            }
            //open lightbox
            mpp.lightbox.media(media_id, position, url);

            return false;
        });


    } //end of activity lightbox

    // For Gallery(when a gallery cover is clicked )
    if (isLightBoxLoaded && _mppData.enable_gallery_lightbox) {

        jq(document).on('click.mpp:gallery:cover:lightbox.mpp:lightbox', '.mpp-gallery a.mpp-gallery-cover', function () {

            var $this = jq(this);
            if ($this.hasClass('mpp-no-lightbox')) {
                return;
            }
            var gallery_id = $this.data('mpp-gallery-id');
            var position = 0;//open first media
            var url = $this.attr('href');

            if (!gallery_id) {
                return true;
            }
            //open lightbox
            mpp.lightbox.gallery(gallery_id, position, url);

            return false;
        });


    }
    //for shortcodes, when a media(photo) is clicked
    if (isLightBoxLoaded) {
        jq(document).on('click.mpp:shortcode:lightbox.mpp:lightbox', '.mpp-shortcode-lightbox-enabled a.mpp-media-thumbnail, .mpp-shortcode-lightbox-enabled a.mpp-media-title', function () {
            var $container = jq(jq(this).parents('.mpp-shortcode-lightbox-enabled').get(0));
            if (!$container.get(0)) {
                return;
            }

            var $this = jq(this);
            if ($this.hasClass('mpp-no-lightbox')) {
                return;
            }
            var media_ids = $container.data('media-ids');
            var url = $this.attr('href');
            var position = 0;// jq( 'a.mpp-media-thumbnail', $container) .index( $this );
            var media_id = $this.data('mpp-media-id');
            mpp.lightbox.media(media_ids, position, url, media_id);
            return false;

        });

    } //end of lightbox for the shortcode

    // enable lightbox for click on the photo inside gallery


    // For Gallery(when a gallery cover is clicked )
    if (isLightBoxLoaded && _mppData.enable_lightbox_in_gallery_media_list) {

        jq(document).on('click.mpp:gallery:media:lightbox.mpp:lightbox', '.mpp-single-gallery-media-list a.mpp-photo-thumbnail, .mpp-single-gallery-media-list a.mpp-media-title', function () {

            var $this = jq(this);

            if ($this.hasClass('mpp-no-lightbox')) {
                return;
            }

            var gallery_id = $this.parents('.mpp-single-gallery-media-list').data('gallery-id');
            var position = 0;//open first media
            var url = $this.attr('href');
            var media_id = $this.data('mpp-media-id');
            if (!gallery_id || !media_id) {
                return true;
            }

            //open lightbox
            mpp.lightbox.gallery(gallery_id, position, url, media_id);

            return false;
        });


    }

    // Create trigger to open lightbox on any link that have the class 'mpp-lightbox-link' and context
    if (isLightBoxLoaded) {
        jq(document).on('click.mpp:link:lightbox.mpp:lightbox', '.mpp-lightbox-link', function () {

            var $this = jq(this);

            if ($this.hasClass('mpp-no-lightbox')) {
                return;
            }

            var activity_id = $this.data('activity-id');
            var gallery_id = $this.data('gallery-id');
            var media_id = $this.data('media-id');
            var url = $this.attr('href');
            var lightbox_opened = false;
            var position = $this.data('position');

            if (!position) {
                position = 0;
            } else {
                position = position - 1;
            }

            if (gallery_id) {
                // open lightbox
                mpp.lightbox.gallery(gallery_id, position, url);
                lightbox_opened = true;
            } else if (media_id) {
                mpp.lightbox.media(media_id, position, url);
                lightbox_opened = true;
            } else if (activity_id) {
                mpp.lightbox.activity(activity_id, position, url);
                lightbox_opened = true;
            }

            if (lightbox_opened) {
                return false;
            }

        });
    }

    /**
     * Find position of the item in the given collection.
     *
     * @param media_id
     * @param items
     * @returns {number}
     */
    function get_media_position_in_collection(media_id, items) {
        var index = 0;
        var position = 0;
        // calculate the position of this media in the collection
        for (var i in items) {
            if (items[i].id == media_id) {
                position = index;
            }
            index++;
        }

        return position;
    }

    /**
     * Show error message in the lighbox media edit form.
     *
     * @param form
     * @param message
     */
    function mpp_ligtbox_show_edit_error(form, message) {
        var $el = form.find('.mpp-lightbox-edit-error');
        if (!$el.get(0)) {
            form.prepend("<div class='mpp-error mpp-lightbox-edit-error'></div>");
            $el = form.find('.mpp-lightbox-edit-error');
        }
        $el.html('<p>' + message + '</p>');
    }

    /**
     * Hide error in the lightbox media edit form.
     *
     * @param form
     */
    function mpp_lightbox_hide_edit_error(form) {
        form.find('.mpp-lightbox-edit-error').remove();
    }

    // Handle Lightbox edit media link clicked
    jq(document).on('click', '.mpp-lightbox-edit-media-link', function () {
        var $this = jq(this);
        $this.hide();
        var $form = jq('#mpp-lightbox-media-edit-form-' + $this.data('mpp-media-id'));

        $form.removeClass('mpp-form-hidden');
        jq('.mpp-lightbox-edit-media-cancel-link').show();
        jq('.mpp-lightbox-media-description').hide();

        return false;
    });

    // Lightbox edit media cancel link clicked
    jq(document).on('click', '.mpp-lightbox-edit-media-cancel-link', function () {
        var $this = jq(this);
        var $form = jq('#mpp-lightbox-media-edit-form-' + $this.data('mpp-media-id'));

        $form.addClass('mpp-form-hidden');
        $this.hide();

        jq('.mpp-lightbox-edit-media-link').show();
        jq('.mpp-lightbox-media-description').show();

        return false;
    });


    // Lightbox Edit:- Cancel button in the form clicked.
    jq(document).on('click', '.mpp-lightbox-edit-media-cancel-button', function () {
        var $this = jq(this);
        var $form = jq('#mpp-lightbox-media-edit-form-' + $this.data('mpp-media-id'));

        // Hide form.
        $form.addClass('mpp-form-hidden');
        // show edit link.
        jq('.mpp-lightbox-edit-media-cancel-link').hide();
        jq('.mpp-lightbox-edit-media-link').show();
        jq('.mpp-lightbox-media-description').show();
        return false;
    });

    // Lightbox Edit Media:- On submit.
    jq(document).on('click', '.mpp-lightbox-edit-media-submit-button', function () {
        var $btn_submit = jq(this);
        var $form = $btn_submit.parents('.mpp-lightbox-media-edit-form');
        var $btn_cancel = $form.find('.mpp-lightbox-edit-media-cancel-button');

        $form.find('.mpp-loader-image').show();

        //disable buttons
        $btn_submit.attr('disabled', true);
        $btn_cancel.attr('disabled', true);

        mpp_lightbox_hide_edit_error($form);
        // submit form
        var data = $form.serialize();
        data += '&action=mpp_update_lightbox_media';

        jq.post(ajaxurl, data, function (response) {
            var magnificPopup = jQuery.magnificPopup.instance;

            if (response.success) {
                // success
                var content = response.data.content;
                magnificPopup.currItem.src = content;
                magnificPopup.items[magnificPopup.index] = magnificPopup.currItem;
                magnificPopup.updateItemHTML();
            } else {
                // Failed.
                var message = response.data.message;
                mpp_ligtbox_show_edit_error($form, message);
            }

            $btn_submit.attr('disabled', false);
            $btn_cancel.attr('disabled', false);

            $form.find('.mpp-loader-image').hide();

        });

        return false;
    });


    /** utility functions*/

    /**
     * Get the  value of a query parameter from the url
     *
     * @param item string the query var to be found.
     * @param str the query string.
     * @returns mixed
     */
    function get_var_in_query(item, str) {
        var items;

        if (!str) {
            return false;
        }

        var data_fields = str.split('&');

        for (var i = 0; i < data_fields.length; i++) {

            items = data_fields[i].split('=');

            if (items[0] == item) {
                return items[1];
            }
        }

        return false;
    }

    /**
     * Extract a query variable from url
     *
     * @param item string
     * @param url string
     * @returns {Boolean|String|mixed}
     */
    function get_var_in_url(item, url) {
        var url_chunks = url.split('?');

        return get_var_in_query(item, url_chunks[1]);

    }
});

/**
 * Activate audi/video player(MediElelement.js player)
 *
 * @param {type} activity_id
 * @returns {undefined}
 */
function mpp_mejs_activate(activity_id) {

    /* global mejs, _wpmejsSettings */
    var jq = jQuery;

    //when document is loading, mediaelementplayer will be undefined, a workaround to avoid double activating it
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
        return new WPPlaylistView({el: this});
    });

}

/**
 * Activate audio/video player(MediElelement.js player) in the lightbox.
 *
 * @returns {undefined}
 */
function mpp_mejs_activate_lightbox_player() {

    /* global mejs, _wpmejsSettings */
    var jq = jQuery;

    //when document is loading, mediaelementplayer will be undefined, a workaround to avoid double activating it
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
        return new WPPlaylistView({el: this});
    });

}