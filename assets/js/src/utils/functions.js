
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
export {prepareExtensions, getQueryParameter, getURLParameter};