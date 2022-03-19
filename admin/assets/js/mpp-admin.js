import jQuery from "jquery";
import "../../../assets/js/mpp-uploader";

/**
 * MediaPress Admin Management Js
 * Loaded on the Edit Gallery page
 */
(function ($) {
    const uploadSettings = _.clone(_mppUploadSettings),
        uploadFeedbackStrings = _.clone(_mppUploaderFeedbackL10n ),
        utils = mpp.mediaUtils;
    $(document).ready(function ($) {
        // Disable the browser keeping delete selected when user selects it and reloads page
        // this beauty will save our users any misfortune.
        if ($('#mpp-edit-media-bulk-action').get(0)) {
            $('#mpp-edit-media-bulk-action').val('');
        }

        // The following code saves the server any headache caused by overloading
        // We provide you the best experience and for that, let us sacrifice the contents of gallery panel
        // Do not send any gallery data from panels when WordPress post publish button is clicked
        $('form#post').on('submit', function () {
            $(this).find('#mpp-admin-edit-panels').remove();
        });

        // hilight current tab.
        if (uploadSettings.current_type) {

            var $current_menu = $('#menu-posts-mpp-gallery').find('ul li a[href*="mpp-gallery-type=' + _mppUploadSettings.current_type + '"]');

            if ($current_menu.parent('li')) {
                // detect other
                $('#menu-posts-mpp-gallery').find('ul li.wp-first-item').removeClass('current');
                $current_menu.parent().addClass('current');
            }
        }
        //console.log(uploadSettings.current_type);
        // notify is the function that gives any global notification
        // If you are a theme author, you can redefine it to give better feedback
        mpp.notify = function (message, error) {
            // notify message inside the active panel
            var class_name = 'updated';
            if (error !== undefined) {
                class_name = 'error';
            }
            $('#message').remove();// will it have side effects?
            var container_selector = '#mpp-admin-edit-panels .mpp-admin-active-panel';
            $(container_selector).prepend('<div id="message" class="bp-template-notice mpp-template-notice ' + class_name + '"><p>' + message + '</p></div>').show();
        };

        let gallery_id = $('#post_ID').val();
        let extensions = null;

        function mpp_create_admin_uploader() {

            let admin_uploader = new mpp.Uploader('admin_uploader', {
                el: '#mpp-upload-dropzone-admin',
                url: uploadSettings.url,
                l10n: uploadFeedbackStrings,
                params: _.extend({}, uploadSettings.params, {'context': 'admin', 'gallery_id': gallery_id}),
                allowedFileTypes: utils.prepareExtensions(extensions),
                addRemoveLinks: true,
                events: {
                    success: function (file, response) {
                        mpp_admin_reload_edit_media_panel();
                    },

                    complete: function () {
                        // reload edit panel
                        // mpp_admin_reload_edit_media_panel();
                    }
                }
            });
            admin_uploader.init();
            mpp.mediaUtils.setupUploaderFileTypes(admin_uploader, uploadSettings.current_type);
        }

        mpp_create_admin_uploader();

        // Trigger delete, deletes any trace of a Media
        $(document).on('click', '.mpp-uploading-media-list .mpp-delete-uploaded-media-item', function () {

            var $this = $(this);
            var $parent = $($this.parent()); //parents are very important in our life, how can we forget them
            // is the data-media-id attribute set, like parents keep their child in heart, our $parent does too
            var id = $parent.data('media-id');

            if (!id) {
                return false;
            }
            // show the round round round loader, It shows the loader gif
            show_loader();

            // get the security pass for clearance because unidentified intruders are not welcome in the family.
            var nonce = $('#_mpp_manage_gallery_nonce').val();

            // Now is the time to take action,
            $.post(ajaxurl, {
                action: 'mpp_delete_media',
                media_id: id,
                cookie: encodeURIComponent(document.cookie),
                _wpnonce: nonce
            }, function (response) {
                // how rude the nature is
                // you deleted my media and still sending me message
                if (response.success !== undefined) {
                    $parent.remove(); // can't believe the parent is going away too

                    // mpp_remove_media_from_cookie(id);
                    mpp.notify(response.message); // let the superman know what consequence his action has brought

                } else {
                    // something went wrong, perhaps the media escaped the deletion
                    mpp.notify(response.message);
                }
                // enough, let us hide the round round feedback
                hide_loader();

            }, 'json');

            return false;
        });

        function mpp_admin_enable_sorting() {
            if ($.fn.sortable !== undefined) {

                $("#mpp-uploaded-media-list-admin>ul").sortable({
                    opacity: 0.6,
                    cursor: 'move',
                    stop: function (evt, ui) {
                        var sorted = $("#mpp-uploaded-media-list-admin>ul").sortable('serialize', {key: 'mpp-media-ids[]'});
                        mpp_update_sorting(sorted);
                    }
                });
            }
        }

        mpp_admin_enable_sorting();

        /**
         * Updates the sorting order
         * @param {type} ids
         * @returns {undefined}
         */
        function mpp_update_sorting(ids) {

            if (!ids) {
                return;
            }

            show_loader();

            var nonce = $('#_mpp_manage_gallery_nonce').val();
            var data = ids + '&action=mpp_reorder_media&_wpnonce=' + nonce;

            $.post(ajaxurl, data, function (response) {

                if (response.success !== undefined) {
                    mpp.notify(response.message);
                } else {
                    mpp.notify(response.message, 'error');
                }

                hide_loader();

            });

        }

        // bulk edit
        // allows us to rename the media, bulk delete them and change their privacy etc
        // anything that your do from MediaPress->Add/Edit Media -> Edit Media panel is handled by
        $(document).on('click', '#mpp-edit-media-submit, #bulk-action-apply', function () {

            // check if delete action in bulk selected
            // This will nuke all media, and we know that nuke is not good for humanity.
            // let us confirm our president again, if they really want to do it?
            if ($('#mpp-edit-media-bulk-action').val() === 'delete') {
                if (!confirm(_mppStrings.bulk_delete_warning)) {
                    return false;
                }
            }

            show_loader();

            var gallery_id = $('#post_ID').val();

            // var $this = $( this );
            // find our parent
            var $parent = $($('form#post').find('#mpp-media-bulkedit-div'));
            var data = $parent.find('input, textarea, select').serialize(); // get second form element
            var nonce = $('#_mpp_manage_gallery_nonce').val();
            //
            // let us build the data that we send to our server
            // many place we are using serialized array to keep any data added by addons to be part of it

            data = data + '&gallery_id=' + gallery_id + '&action=mpp_bulk_update_media&_wpnonce=' + nonce;

            $.post(ajaxurl, data, function (response) {

                if (response.success !== undefined) {

                    $('#mpp-admin-edit-panel-tab-edit-media').html(response.contents);
                    mpp.notify(response.message);
                    // reload add media panel to reflect the change
                    mpp_admin_reload_add_media_panel();

                } else {
                    // notify
                    mpp.notify(response.message, 'error');
                }

                hide_loader();
            });

            return false;
        });

        // cover delete
        $(document).on('click', '#mpp-cover-delete', function () {

            var gallery_id = $('#post_ID').val();

            if (!gallery_id) {
                return false;
            }

            var nonce = $('#_mpp_manage_gallery_nonce').val();

            show_loader();

            $.post(ajaxurl, {
                action: 'mpp_delete_gallery_cover',
                gallery_id: gallery_id,
                _wpnonce: nonce,
                cookie: encodeURIComponent(document.cookie)
            }, function (response) {

                if (response.success !== undefined) {
                    // delete cover, replace with default
                    $('#mpp-cover-' + gallery_id).find('.mpp-cover-image').attr('src', response.cover);
                    mpp.notify(response.message);

                } else {
                    // notify
                    mpp.notify(response.message, 'error');
                }

                hide_loader();

            }, 'json');

            return false;
        });


        // cover delete
        $(document).on('click', '#mpp-update-gallery-details', function () {
            var $parent = $($('form#post').find('#mpp-gallery-edit-form'));

            var data = $parent.find('input, textarea, select').serialize(); // get second form element
            var nonce = $('#_mpp_manage_gallery_nonce').val();

            data = data + '&action=mpp_update_gallery_details&_wpnonce=' + nonce;

            show_loader();

            $.post(ajaxurl, data, function (response) {

                if (response.success !== undefined) {
                    mpp.notify(response.message);
                } else {
                    // notify
                    mpp.notify(response.message, 'error');
                }

                hide_loader();

            }, 'json');

            return false;
        });


        // Reload edit panel
        $(document).on('click', '#mpp-reload-bulk-edit-tab', function () {
            mpp_admin_reload_edit_media_panel();
            return false;
        });

        // Reload upload contents
        $(document).on('click', '#mpp-reload-add-media-tab', function () {
            mpp_admin_reload_add_media_panel();
            return false;
        });

        function mpp_admin_reload_edit_media_panel() {
            mpp_admin_reload_edit_panel('#mpp-admin-edit-panel-tab-edit-media', 'mpp_reload_bulk_edit');
        }

        function mpp_admin_reload_add_media_panel() {
            var $tab = '#mpp-admin-edit-panel-tab-add-media';
            var gallery_id = $('#post_ID').val();
            var nonce = $('#_mpp_manage_gallery_nonce').val();

            $tab = $($tab);//to reload under this tab

            var loader = $('#mpp-loader-wrapper').clone();
            $tab.find('#mpp-show-loader').remove();
            $tab.prepend('<ul id="mpp-show-loader"></ul>');
            $tab.find('#mpp-show-loader').append(loader.show());

            $.post(ajaxurl, {
                action: 'mpp_reload_add_media',
                gallery_id: gallery_id,
                _wpnonce: nonce

            }, function (response) {

                if (response.success !== undefined) {
                    // $tab.empty();
                    $tab.html(response.contents);
                    // reattach uploader
                    // or should we first destroy earlier uploader before reattaching?
                    mpp_create_admin_uploader();
                    mpp_admin_enable_sorting();
                    if (response.message !== undefined) {
                        mpp.notify(response.message);
                    }
                } else {
                    mpp.notify(response.message, 1);
                }

                $tab.find('#mpp-show-loader').remove();
            }, 'json');
        }


        function mpp_admin_reload_edit_panel($tab, action, gallery_id, nonce) {

            if (!gallery_id) {
                gallery_id = $('#post_ID').val();
            }

            if (!nonce) {
                nonce = $('#_mpp_manage_gallery_nonce').val();
            }

            $tab = $($tab);//to reload under this tab

            var loader = $('#mpp-loader-wrapper').clone();
            $tab.find('#mpp-show-loader').remove();
            $tab.prepend('<ul id="mpp-show-loader"></ul>');
            $tab.find('#mpp-show-loader').append(loader.show());

            $.post(ajaxurl, {
                action: action,
                gallery_id: gallery_id,
                _wpnonce: nonce

            }, function (response) {

                if (response.success !== undefined) {

                    //$tab.empty();
                    $tab.html(response.contents);

                    if (response.message !== undefined) {
                        mpp.notify(response.message);
                    }
                } else {
                    mpp.notify(response.message, 1);
                }


                $tab.find('#mpp-show-loader').remove();
            }, 'json');
        }

        function show_loader() {

            var loader = $('#mpp-loader-wrapper').clone();
            $('#mpp-show-loader').remove();// will it have side effects?
            var container_selector = '.mpp-admin-active-panel';
            $(container_selector).prepend('<ul id="mpp-show-loader"></ul>');
            $('#mpp-show-loader').append(loader.show());

        }

        function hide_loader() {
            $('#mpp-show-loader').remove();//
        }

    });

})(jQuery);
