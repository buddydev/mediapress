!function(){"use strict";var e,p,o={567:function(e){e.exports=window.jQuery},821:function(e){e.exports=window.mpp}},t={};function l(e){var p=t[e];if(void 0!==p)return p.exports;var n=t[e]={exports:{}};return o[e](n,n.exports,l),n.exports}l.n=function(e){var p=e&&e.__esModule?function(){return e.default}:function(){return e};return l.d(p,{a:p}),p},l.d=function(e,p){for(var o in p)l.o(p,o)&&!l.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:p[o]})},l.o=function(e,p){return Object.prototype.hasOwnProperty.call(e,p)},e=l(567),p=l.n(e),l(821),function(e){const p=_.clone(_mppUploadSettings),o=_.clone(_mppUploaderFeedbackL10n);e(document).ready((function(){const t=mpp.mediaUtils;let l=p.params.context,n=0;e("#mpp-context").length&&(l=e("#mpp-context").val()),e("#mpp-upload-gallery-id").length&&(n=e("#mpp-upload-gallery-id").val());let r=t.getExtensions(p.current_type),a=new mpp.Uploader("gallery",{el:"#mpp-upload-dropzone-gallery",url:p.url,l10n:o,params:_.extend({},p.params,{context:l,gallery_id:n}),allowedFileTypes:t.prepareExtensions(r),addRemoveLinks:!0});a.init(),t.setupUploaderFileTypes(a,p.current_type);let d=0,i=null;e(".mpp-upload-shortcode #mpp-shortcode-upload-gallery-id").length&&(d=e(".mpp-upload-shortcode #mpp-shortcode-upload-gallery-id").val()),e(".mpp-upload-shortcode .mpp-uploading-media-type").length&&(i=t.getExtensions(e(".mpp-upload-shortcode .mpp-uploading-media-type").first().val()));let s=new mpp.Uploader("shortcode",{el:"#mpp-upload-dropzone-shortcode",url:p.url,l10n:o,params:_.extend({},p.params,{context:"shortcode",gallery_id:d}),allowedFileTypes:i,addRemoveLinks:!0});if(s.init(),d||s.disable(),s.hideHelpMessages(),e("#mpp-upload-dropzone-shortcode").length){var u=e("#mpp-upload-dropzone-shortcode").parents(".mpp-upload-shortcode").find(".mpp-uploading-media-type");u.length&&(t.setupUploaderFileTypes(s,u.val()),s.refresh())}e(".mpp-upload-shortcode #mpp-shortcode-upload-gallery-id").on("change",(function(){let p=e(this),o=p.find("option:selected");p.val()&&("0"!==p.val()?s.enable():s.disable()),s.setParam("gallery_id",p.val()),t.setupUploaderFileTypes(s,o.data("mpp-type")),s.refresh()}));let c=e(".mpp-editable-cover").first(),m=e("#mpp-cover-uploading"),f=c.find(".mpp-gallery-id").val(),h=c.find(".mpp-parent-id").val(),y=c.find(".mpp-parent-type").val();new mpp.Uploader("cover_uploader",{el:".mpp-editable-cover",clickable:"#mpp-cover-upload",url:p.url,l10n:o,params:_.extend({},p.params,{context:"cover",action:"mpp_upload_cover","mpp-parent-id":h,"mpp-gallery-id":f,"mpp-parent-type":y}),allowedFileTypes:t.prepareExtensions(t.getExtensions("photo")),addRemoveLinks:!0,addedfile:function(e){m.show()},thumbnail:function(e,p){},uploadprogress:function(e,p,o){},error:function(){mpp.notify("Error")},events:{success:function(e,p){let o=(p=p.data).sizes&&p.sizes.thumbnail?p.sizes.thumbnail.url:null;o&&c.find(".mpp-cover-image").attr("src",o)},complete:function(){m.hide()}}}).init(),e(document).on("click","#mpp-cover-upload",(function(){return!1}))}))}(p())}();
//# sourceMappingURL=mpp-core-uploaders.js.map