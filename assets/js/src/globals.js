import { createHooks } from '@wordpress/hooks';
// Make mpp global object.
import {mpp_mejs_activate_lightbox_player, mpp_mejs_activate} from "./utils/media-player-utils";
import lightbox from './utils/lightbox-utils';
import {notify, clearNotice} from './utils/notice';

let mpp = window.mpp || {};

mpp.hooks = createHooks();

mpp.lightbox = lightbox;

//allow plugins/theme to override the notification
if (mpp.notify === undefined) {
    mpp.notify = notify;
    mpp.clearNotice = clearNotice;
}
window.mpp = mpp;

window.mpp_mejs_activate = mpp_mejs_activate;
window.mpp_mejs_activate_lightbox_player = mpp_mejs_activate_lightbox_player;

