// import for side effects.
import Uploader from "./src/uploader";

import * as mediaUtils from './src/utils/media-utils';

let mpp = window.mpp|| {};
mpp.Uploader = Uploader;
mpp.utils = {};
mpp.mediaUtils = mediaUtils;
window.mpp = mpp;
