
import jQuery from 'jquery';
import {getURLParameter, getQueryParameter} from './src/utils/functions';

(function ($){

    $(document).ready(function (){
        /**
         * Bulk Actions checkbox on Gallery-> Edit Media page
         * Check/uncheck based on user action
         */
        $(document).on('click', '#mpp-check-all', function () {

            if ($(this).is(':checked')) {
                // check all others
                $('input.mpp-delete-media-check').prop('checked', true);

            } else {
                // uncheck all
                $('input.mpp-delete-media-check').prop('checked', false);
            }
        });

        /**
         * Single Gallery -> Edit Media page
         * Handle publish to activity action
         */
        $(document).on('click', '.mpp-publish-to-activity-button', function () {

            let $this = $(this),
                url = $this.attr('href'),
                gallery_id = getURLParameter('gallery_id', url),
                nonce = getURLParameter('_wpnonce', url);

            $.post(ajaxurl, {
                    action: 'mpp_publish_gallery_media',
                    gallery_id: gallery_id,
                    _wpnonce: nonce,
                    cookie: encodeURIComponent(document.cookie)
                }, function (response) {
                    let error;
                    if (response.error !== undefined) {
                        error = 1;
                    }
                    //hide the button
                    $('#mpp-unpublished-media-info').hide();

                    mpp.notify(response.message, error);
                },

                'json');

            return false;

        });

        /**
         * Single Gallery->Edit Media
         * Handle delete unpublished media
         */
        $(document).on('click', '.mpp-delete-unpublished-media-button', function () {

            let $this = $(this),
                url = $this.attr('href'),
                gallery_id = getURLParameter('gallery_id', url),
                nonce = getURLParameter('_wpnonce', url);

            $.post(ajaxurl, {
                    action: 'mpp_hide_unpublished_media',
                    gallery_id: gallery_id,
                    _wpnonce: nonce,
                    cookie: encodeURIComponent(document.cookie)
                }, function (response) {

                    let error;
                    if (typeof  response.error !== "undefined") {
                        error = 1;
                    }
                    //hide the button
                    $('#mpp-unpublished-media-info').hide();

                    mpp.notify(response.message, error);
                },

                'json');

            return false;
        });

        /**
         * Single Gallery->Reorder
         * Enable Media sorting/re-odering on manage gallery/reorder page
         *
         */
        if ($.fn.sortable !== undefined) {
            $("#mpp-sortable").sortable({opacity: 0.6, cursor: 'move'});
        }

        /**
         * Show error message in the lighbox media edit form.
         *
         * @param form
         * @param message
         */
        function mpp_ligtbox_show_edit_error(form, message) {
            let $el = form.find('.mpp-lightbox-edit-error');
            if (!$el.length) {
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
        $(document).on('click', '.mpp-lightbox-edit-media-link', function () {
            let $this = $(this);
            $this.hide();
            let $form = $('#mpp-lightbox-media-edit-form-' + $this.data('mpp-media-id'));

            $form.removeClass('mpp-form-hidden');
            $('.mpp-lightbox-edit-media-cancel-link').show();
            $('.mpp-lightbox-media-description').hide();

            return false;
        });

        // Lightbox edit media cancel link clicked
        $(document).on('click', '.mpp-lightbox-edit-media-cancel-link', function () {
            let $this = $(this),
                $form = $('#mpp-lightbox-media-edit-form-' + $this.data('mpp-media-id'));

            $form.addClass('mpp-form-hidden');
            $this.hide();

            $('.mpp-lightbox-edit-media-link').show();
            $('.mpp-lightbox-media-description').show();

            return false;
        });

        // Lightbox Edit:- Cancel button in the form clicked.
        $(document).on('click', '.mpp-lightbox-edit-media-cancel-button', function () {
            let $this = $(this),
                $form = $('#mpp-lightbox-media-edit-form-' + $this.data('mpp-media-id'));

            // Hide form.
            $form.addClass('mpp-form-hidden');
            // show edit link.
            $('.mpp-lightbox-edit-media-cancel-link').hide();
            $('.mpp-lightbox-edit-media-link').show();
            $('.mpp-lightbox-media-description').show();
            return false;
        });

        // Lightbox Edit Media:- On submit.
        $(document).on('click', '.mpp-lightbox-edit-media-submit-button', function () {
            let $btn_submit = $(this),
                $form = $btn_submit.parents('.mpp-lightbox-media-edit-form'),
                $btn_cancel = $form.find('.mpp-lightbox-edit-media-cancel-button');

            $form.find('.mpp-loader-image').show();

            //disable buttons
            $btn_submit.attr('disabled', true);
            $btn_cancel.attr('disabled', true);

            mpp_lightbox_hide_edit_error($form);
            // submit form
            let data = $form.serialize();
            data += '&action=mpp_update_lightbox_media';

            $.post(ajaxurl, data, function (response) {
                let magnificPopup = $.magnificPopup.instance;

                if (response.success) {
                    // success
                    magnificPopup.currItem.src = response.data.content;
                    magnificPopup.items[magnificPopup.index] = magnificPopup.currItem;
                    magnificPopup.updateItemHTML();
                } else {
                    // Failed.
                    mpp_ligtbox_show_edit_error($form, response.data.message);
                }

                $btn_submit.attr('disabled', false);
                $btn_cancel.attr('disabled', false);

                $form.find('.mpp-loader-image').hide();

            });

            return false;
        });
    });

})(jQuery);