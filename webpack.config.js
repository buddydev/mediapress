const path = require( 'path' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );
/**
 * WordPress Dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

function requestToExternal( request ) {
    // Handle imports like `import myModule from 'my-module'`
    if ( request === 'underscore' ) {
        // Expect to find `my-module` as myModule in the global scope:
        return '_';
    } else if( request === 'dropzone') {
        return 'Dropzone';
    } else if( request.endsWith('/mpp-uploader') ) {
        return 'mpp';
    }
}
function requestToHandle( request ) {
    // Handle imports like `import myModule from 'my-module'`
    if( request.endsWith('/mpp-uploader') ) {
        return 'mpp-uploader';
    }
}

//console.log( ...defaultConfig.externals);
module.exports = {

    ...defaultConfig,
    mode: "production",
    ...{
        entry: {
            "assets/js/dist/mpp-core":"./assets/js/mpp-core.js",
            "assets/js/dist/mpp-uploader":"./assets/js/mpp-uploader.js",
            "assets/js/dist/mpp-core-uploaders":"./assets/js/mpp-core-uploaders.js",
            "assets/js/dist/mpp-activity-uploader":"./assets/js/mpp-activity-uploader.js",
            "assets/js/dist/mpp-media-activity":"./assets/js/mpp-media-activity.js",
            "assets/js/dist/mpp-remote":"./assets/js/mpp-remote.js",
            "assets/js/dist/mpp-manage":"./assets/js/mpp-manage.js",
            "admin/assets/js/dist/mpp-admin":"./admin/assets/js/mpp-admin.js",
        },
        output:{
            path: path.resolve(__dirname),
            filename: './[name].js'
        },
        plugins:[
            ...defaultConfig.plugins.filter(
                plugin=> ( plugin.constructor.name !== 'CleanWebpackPlugin' && plugin.constructor.name !== 'DependencyExtractionWebpackPlugin' ) //disables cleanWebpack to avoid all directory from being deleted in our case.
            ),
            new DependencyExtractionWebpackPlugin({requestToExternal, requestToHandle }),
        ],
        // Add any overrides to the default here.
    },
};