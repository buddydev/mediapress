var mpJQ = jQuery.noConflict;
mpJQ( document ).ready( function(){
	
	///
	///
	/// Manage Gallery 
	///
	
	/**
	 * On Single Gallery Bulk Edit media
	 * Allow selecting/deselcting all media in one click
	 * 
	 */
	mpJQ(document).on( 'click', '#mpp-check-all', function (){
		
		if( mpJQ(this).is(':checked') ){
			//check all others
			mpJQ('input.mpp-delete-media-check').prop('checked', true );
			
		}else{
			//uncheck all
			mpJQ('input.mpp-delete-media-check').prop('checked', false );
		}
	});
	
	/**
	 * Enable Media sorting/reodering on manage gallery/reorder page
	 * 
	 * 
	 */
	if( mpJQ.fn.sortable != undefined )
		mpJQ("#mpp-sortable").sortable({opacity: 0.6, cursor: 'move'});
	
	/**
	 * Activity upload Form handling
	 * 
	 */	
    //prepend the selector buttons to what-new post box
    mpJQ('#whats-new-options').prepend( mpJQ( '#mpp-activity-upload-buttons') );
    //mpJQ('#whats-new-post-in-box').prepend( mpJQ( '#mpp-activity-upload-buttons') );
    
       
    //Creat an instance of mpp Uploader and attach it to the activity upload elemnts
    mpp.activity_uploader = new mpp.Uploader({
        container: 'body',
        dropzone: '#mpp-activity-dropzone',
        browser: '#add-activity-media',
        feedback: '#mpp-activity-feedback',
        media_list: '#mpp-activity-media-list',//where we will list the media
        uploading_media_list : _.template ( "<li id='<%= id %>'><span class='mpp-attached-file-name'><%= name %></span>(<span class='mpp-attached-file-size'><%= size %></spa>)<span class='mpp-remove-file-attachment'>x</span> <b></b></li>" ),
        uploaded_media_list : _.template ( "<li class='mpp-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>'><img src='<%= url %>' /></li>" ),
    	
		success:  function( file ) {
			
					//let the Base class success mmethod handle the things
					mpp.Uploader.prototype.success( file );
                       
                     
                    //save media id in cookie
                    mpp_add_media_to_cookie( file.get('id') );    
                    
                    },
     
    });
    
    //When any of the media icons(audio/video etc) is clicked
	//show the dropzone
	
    mpJQ( document ).on( 'click', '#mpp-activity-upload-buttons a', function() {
        
		var el=mpJQ(this);
		//set upload context as activity
        mpp.activity_uploader.param( 'context', 'activity' );
		
        var dropzone = mpp.activity_uploader.dropzone;//.remove();
        //var type = j(this).attr("id");//use id as type detector , may be photo/audio/video
        //if the form was already added earlier
	//        if( type == form.data('media-type')){
	//            form.toggle();
	//            return false;   
	//        }
	//        
		dropzone.show();
		
		mpJQ( '#add-activity-media' ).click();//simulate click;
			
		return false;
	});
    
    //Intercept the ajax actions to check if there was an upload from activity
	//if yes, when it is complete, hide the dropzone
   
   mpJQ( document ).ajaxComplete( function( evt, xhr, options ) {
      
       var action = get_var_in_query( 'action', options.data ) ;
       
       //switch
       switch( action ){
           
           case 'post_update':
               
               mpp.activity_uploader.hide_ui() ; //clear the list of uploaded media
               break;
         
       }
       
   });
   
   /** For single gallery  upload */
       
	mpp.guploader = new mpp.Uploader({
        container: 'body',
        dropzone: '#mpp-gallery-dropzone',
        browser: '#mpp-add-gallery-media',
        feedback: '#mpp-gallery-upload-feedback',
        media_list: '#mpp-gallery-media-list',//where we will list the media
        uploading_media_list : _.template ( "<li id='<%= id %>'><span class='mpp-attached-file-name'><%= name %></span>(<span class='mpp-attached-file-size'><%= size %></spa>)<span class='mpp-remove-file-attachment'>x</span> <b></b></li>" ),
        uploaded_media_list : _.template ( "<li class='mpp-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>'><img src='<%= url %>' /></li>" ),
        
	
    });
	
	var context = 'gallery';//context defines from where it was uploaded
	var gallery_id = 0;
	
	if( mpJQ('#mpp-context').get(0) )
		context = mpJQ('#mpp-context').val();
	
	if( mpJQ( '#mpp-upload-gallery-id' ).get(0) )
		gallery_id = mpJQ( '#mpp-upload-gallery-id' ).val();
	//apply these only when the dropzone exits
	if( mpJQ('#mpp-gallery-dropzone').get(0) ){
	
     mpp.guploader.param( 'context', context );
     mpp.guploader.param( 'gallery_id', gallery_id );
 }
 
	
//For cover uploader

 	mpp.cover_uploader = new mpp.Uploader({
        container: 'body',
        dropzone: '.mpp-cover-image',
        browser: '#mpp-cover-upload',
        feedback: '#mpp-cover-gallery-upload-feedback',
        media_list: '',//where we will list the media
        uploading_media_list : _.template ( "<li id='<%= id %>'><span class='mpp-attached-file-name'><%= name %></span>(<span class='mpp-attached-file-size'><%= size %></spa>)<span class='mpp-remove-file-attachment'>x</span> <b></b></li>" ),
        uploaded_media_list : _.template ( "<li class='mpp-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>'><img src='<%= url %>' /></li>" ),
        

		complete : function() {
			
           // console.log('Cover Uploaded');
		},
        
		success:  function( file ) {
            
                        var sizes = file.get( 'sizes' );
                        var original_url = file.get('url');
                        var id = file.get('id');
                        var file_obj = file.get('file');

                        var thumbnail = sizes.thumbnail;
						
						//on success change cover image
						
						var cover = '#mpp-cover-'+file.get('parent_id');
						
						mpJQ(cover).find('.mpp-cover-uploading' ).hide();
						
						mpJQ( cover).find('img.mpp-cover-image ').attr('src',thumbnail.url );
                       
                    },
                    
        clear_media_list: function(){
            
			
        },
        clear_feedback : function (){
			if( !this.feedback )
				return;
			
            mpJQ( 'ul', this.feedback ).empty();
        },
        
        hide_dropzone : function (){
			
			if( !this.dropzone )
				return;
			
            mpJQ( this.dropzone).hide();
        },
        hide_ui : function(){
            
            this.clear_media_list();
            this.clear_feedback();
            this.hide_dropzone();
        },
		
		onAddFile: function ( file ){
			//wehn file is added, set context
			
			this.param( 'context', 'cover' );//it is cover upload
			this.param( 'action', 'mpp_upload_cover' );//it is cover upload
			
			
			var parent = this.browser.parents('.mpp-cover-wrapper');
			
			//update parent media or gallery id
			this.param( 'mpp-parent-id', parent.find('.mpp-parent-id').val() );//it is cover upload
			//update parent gallery id
			this.param( 'mpp-gallery-id', parent.find('.mpp-gallery-id').val() );//it is cover upload
			
			parent.find('.mpp-cover-uploading').show();
			
			console.log( 'uploading....' );
			console.log( 'file addedd');
		},
		init: function(){
			var parent = this.browser.parents('.mpp-cover-wrapper');
			mpJQ.each( parent, function(){
				mpJQ(this).find('.mpp-cover-image').append( mpJQ('#mpp-cover-uploading').clone());
				
			});
			
		}
        
    });	

//popup
if( mpJQ.fn.magnificPopup != undefined && _mppData.enable_activity_lightbox )
	mpJQ('.mpp-activity-photo-list').magnificPopup({
		delegate: 'a',
		type: 'ajax',
		closeBtnInside: true,
		preload: [1, 3],
		closeOnBgClick: false,
		gallery: {
			enabled: true,
			navigateByImgClick: true,
			arrowMarkup: '',// disabled default arrows
			preload: [0, 1] // Will preload 0 - before current, and 1 after the current image
		},
		callbacks: {
			parseAjax: function ( mfpResponse ){
				var data = mpJQ("<div class='mpp-lightbox-content mpp-clearfix'></div>").append(mpJQ(mfpResponse.data).find('#mpp-container'));
				mfpResponse.data = data;
			},
			 ajaxContentAdded: function(){
				var mfp = jQuery.magnificPopup.instance;

				var media = mpJQ( mfp.content).find('.mpp-media-single').get(0);

				mpJQ( mfp.content).find('.mpp-media-activity').css( 'height', mpJQ(media).height()+'px');
			},

		},
	});

   /** utility functions*/
   
   /**
    * Get the  value of a query parameter from the url
	* 
	* @param {type} item url
	* @param {string} str the name of query string key
	* @returns {string|Boolean}
    */
   function get_var_in_query( item,  str ){
       var items;
       if( !str )
           return false;
       var data_fields = str.split('&');
       for( var i=0; i< data_fields.length; i++ ){
           
           items = data_fields[i].split('=');
           if( items[0] == item )
               return items[1];
       }
       
       return false;
   }
	
});

function mpp_mejs_activate( activity_id ){
	
	/* global mejs, _wpmejsSettings */
	var mpJQ= jQuery.noConflict();
	
	//when document is loading, mediaelementplayer will be undefined, a workaround to avoid double activating it
	if( mpJQ.fn.mediaelementplayer == undefined )
		return;

		var settings = {};

		if ( typeof _wpmejsSettings !== 'undefined' ) {
			settings = _wpmejsSettings;
		}

		settings.success = function (mejs) {
			var autoplay, loop;

			if ( 'flash' === mejs.pluginType ) {
				autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
				loop = mejs.attributes.loop && 'false' !== mejs.attributes.loop;

				autoplay && mejs.addEventListener( 'canplay', function () {
					mejs.play();
				}, false );

				loop && mejs.addEventListener( 'ended', function () {
					mejs.play();
				}, false );
			}
		};

		


mpJQ('.wp-audio-shortcode, .wp-video-shortcode', mpJQ( '#activity-'+activity_id ) ).mediaelementplayer( settings );
mpJQ('.wp-playlist', mpJQ( '#activity-'+activity_id ) ).each( function() {
			return new WPPlaylistView({ el: this });
		} );
}