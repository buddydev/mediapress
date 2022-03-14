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
    }
}
//console.log( ...defaultConfig.externals);
module.exports = {

    ...defaultConfig,
    mode: "development",
    ...{
        entry: {
            "assets/js/mpp":"./assets/js/mpp.js",
            "assets/js/mpp-uploader":"./assets/js/mpp-uploader.js",
            "assets/js/mpp-activity-uploader":"./assets/js/mpp-activity-uploader.js",
           // "assets/vendors/dropzone/dropzone":"./assets/vendors/dropzone/dropzone.js",
          //  "assets/css/uploader":"./assets/css/uploader.scss",
         //   "assets/css/default": "./assets/scss/default.scss",
        },
        output:{
            path: path.resolve(__dirname),
            filename: './[name].dist.js'
        },
        plugins:[
            ...defaultConfig.plugins.filter(
                plugin=> ( plugin.constructor.name !== 'CleanWebpackPlugin' && plugin.constructor.name !== 'DependencyExtractionWebpackPlugin' ) //disables cleanWebpack to avoid all directory from being deleted in our case.
            ),
            new DependencyExtractionWebpackPlugin({requestToExternal   }),
        ],
        // Add any overrides to the default here.
    },
};