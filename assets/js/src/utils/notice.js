
import $ from 'jquery';

function notify(message, error) {

    var class_name = 'updated success';
    if (error !== undefined) {
        class_name = 'error';
    }

    $('#mpp-notice-message').remove();// will it have side effects?
    var selectors = ['#mpp-container', '#whats-new-form', '.mpp-upload-shortcode']; //possible containers in preferred order
    var container_selector = '';//default

    for (var i = 0; i < selectors.length; i++) {
        if ($(selectors[i]).get(0)) {
            container_selector = selectors[i];
            break;
        }
    }

    //if container exists, let us append the message
    if (container_selector) {
        $(container_selector).prepend('<div id="mpp-notice-message" class="mpp-notice mpp-template-notice ' + class_name + '"><p>' + message + '</p></div>').show();
    }
}

function clearNotice() {
    $('#mpp-notice-message').remove();
}
export {notify, clearNotice};
