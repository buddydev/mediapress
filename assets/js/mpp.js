jQuery( document ).ready( function() {
	
	var jq = jQuery;
	/**
	 * Bulk Actions checkbox on Gallery-> Edit Media page
	 * Check/uncheck based on user action
	 */
	jq( document ).on( 'click', '#mpp-check-all', function () {
		
		if( jq( this ).is( ':checked' ) ) {
			//check all others
			jq('input.mpp-delete-media-check').prop('checked', true );
			
		} else {
			//uncheck all
			jq('input.mpp-delete-media-check').prop('checked', false );
		}
	} );
	
	/**
	 * Single Gallery -> Edit Media page
	 * Handle publish to activity action
	 */
	jq( document ).on( 'click', '.mpp-publish-to-activity-button', function() {
		
		var $this = jq( this );
		var url = $this.attr('href');
		var gallery_id = get_var_in_url('gallery_id', url );
		var nonce = get_var_in_url( '_wpnonce', url );
		
		jq.post(ajaxurl, {
			action: 'mpp_publish_gallery_media',
			gallery_id: gallery_id,
			_wpnonce: nonce,
			cookie: encodeURIComponent( document.cookie)
		}, function(response ) {
			var error;	
			if( response.error != undefined ) {
				error = 1;
			}
			//hide the button
			jq( '#mpp-unpublished-media-info' ).hide();

			mpp.notify( response.message, error );

		},
		
		'json' );
		
		return false;
		
	});
	/**
	 * Single Gallery->Edit Media
	 * Handle delete unpublished media
	 */
	jq( document ).on( 'click', '.mpp-delete-unpublished-media-button', function() {
		
		var $this = jq( this );
		var url = $this.attr( 'href' );
		var gallery_id = get_var_in_url( 'gallery_id', url );
		var nonce = get_var_in_url( '_wpnonce', url );
		
		jq.post( ajaxurl, {
			action: 'mpp_hide_unpublished_media',
			gallery_id: gallery_id,
			_wpnonce: nonce,
			cookie: encodeURIComponent( document.cookie )
		}, function( response ) {

			var error;	
			if( response.error != undefined ) {
				error = 1;
			}
			//hide the button
			jq( '#mpp-unpublished-media-info' ).hide();

			mpp.notify( response.message, error );

		},
		
		'json' );
		
		return false;
		
	});
	
	/**
	 * Single Gallery->Reorder
	 * Enable Media sorting/reodering on manage gallery/reorder page
	 * 
	 */
	if( jq.fn.sortable != undefined ) {
		jq( "#mpp-sortable" ).sortable( { opacity: 0.6, cursor: 'move' } );
	}
	/**
	 * Activity upload Form handling
	 * Prepend the upload buttons to Activity form
	 */	
  
	jq( '#whats-new-options' ).prepend( jq( '#mpp-activity-upload-buttons' ) );
    //jq('#whats-new-post-in-box').prepend( jq( '#mpp-activity-upload-buttons') );
    
       
	 //Create an instance of uploader for activity  
    //Creat an instance of mpp Uploader and attach it to the activity upload elements
    mpp.activity_uploader = new mpp.Uploader({
        container: 'body',
        dropzone: '#mpp-upload-dropzone-activity',
        browser: '#mpp-upload-media-button-activity',
        feedback: '#mpp-upload-feedback-activity',
        media_list: '#mpp-uploaded-media-list-activity',//where we will list the media
        uploading_media_list : _.template ( "<li id='<%= id %>'><span class='mpp-attached-file-name'><%= name %></span>(<span class='mpp-attached-file-size'><%= size %></spa>)<span class='mpp-remove-file-attachment'>x</span> <b></b></li>" ),
        uploaded_media_list : _.template ( "<li class='mpp-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>'><img src='<%= url %>' /></li>" ),
    	
		success:  function( file ) {
			//let the Base class success mmethod handle the things
			mpp.Uploader.prototype.success( file );
            //save media id in cookie
            mpp_add_media_to_cookie( file.get('id') );    
                    
        },
		
		isRestricted: function ( up, file ) {
			
			return false; //return true to restrict upload
			/*this.error( "Unable to add", {}, file );
			if( ! this.media_list )
				return;
			//show loader
			jq( '.mpp-loader', this.media_list ).hide();
			
			return true;
			*/
		}	
     
    });
    
    //When any of the media icons(audio/video etc) is clicked
	//show the dropzone
	
    jq( document ).on( 'click', '#mpp-activity-upload-buttons a', function() {
        
		var el = jq( this );
		//set upload context as activity
        mpp.activity_uploader.param( 'context', 'activity' );
		
        var dropzone = mpp.activity_uploader.dropzone;//.remove();
        var type = jq( this ).data( 'media-type' );//use id as type detector , may be photo/audio/video
        //set current type as the clicked button
		_mppData.current_type = type;
		mpp_setup_uploader_file_types( mpp.activity_uploader );
		
		dropzone.show();
		//this may not work on mobile
		//check
		jq( '#mpp-upload-media-button-activity' ).click();//simulate click;
			
		return false;
	});
    
    //Intercept the ajax actions to check if there was an upload from activity
	//if yes, when it is complete, hide the dropzone
   
   jq( document ).ajaxComplete( function( evt, xhr, options ) {
      
		var action = get_var_in_query( 'action', options.data ) ;
       
		//switch
		switch( action ) {
           
			case 'post_update':
				mpp.activity_uploader.hide_ui() ; //clear the list of uploaded media
            	break;
        }
       
   });
   
   /** For single gallery  upload */
       
	mpp.guploader = new mpp.Uploader({
        container: 'body',
        dropzone: '#mpp-upload-dropzone-gallery',
        browser: '#mpp-upload-media-button-gallery',
        feedback: '#mpp-upload-feedback-gallery',
        media_list: '#mpp-uploaded-media-list-gallery',//where we will list the media
        uploading_media_list : _.template ( "<li id='<%= id %>'><span class='mpp-attached-file-name'><%= name %></span>(<span class='mpp-attached-file-size'><%= size %></spa>)<span class='mpp-remove-file-attachment'>x</span> <b></b></li>" ),
        uploaded_media_list : _.template ( "<li class='mpp-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>'><img src='<%= url %>' /></li>" )
        
	
    });
	
	var context = 'gallery';//context defines from where it was uploaded
	var gallery_id = 0;
	
	if( jq('#mpp-context').get(0) ) {
		context = jq('#mpp-context').val();
	}
	
	if( jq( '#mpp-upload-gallery-id' ).get(0) ) {
		gallery_id = jq( '#mpp-upload-gallery-id' ).val();
	}
	//apply these only when the dropzone exits
	if( jq('#mpp-upload-dropzone-gallery').get(0) ) {
	
		mpp.guploader.param( 'context', context );
		mpp.guploader.param( 'gallery_id', gallery_id );
	}

	mpp_setup_uploader_file_types( mpp.guploader );
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
						
						jq( cover ).find('.mpp-cover-uploading' ).hide();
						
						jq( cover).find('img.mpp-cover-image ').attr('src',thumbnail.url );
                       
                    },
                    
        clear_media_list: function() {
        
        },
        clear_feedback : function () {
			if( ! this.feedback ) {
				return;
			}
			
            jq( 'ul', this.feedback ).empty();
        },
        
        hide_dropzone : function () {
			
			if( ! this.dropzone ) {
				return;
			}
			
            jq( this.dropzone).hide();
        },
        hide_ui : function() {
            
            this.clear_media_list();
            this.clear_feedback();
            this.hide_dropzone();
        },
		
		onAddFile: function ( file ) {
			//wehn file is added, set context
			
			this.param( 'context', 'cover' );//it is cover upload
			this.param( 'action', 'mpp_upload_cover' );//it is cover upload
			
			
			var parent = this.browser.parents('.mpp-cover-wrapper');
			
			//update parent media or gallery id
			this.param( 'mpp-parent-id', parent.find('.mpp-parent-id').val() );//it is cover upload
			//update parent gallery id
			this.param( 'mpp-gallery-id', parent.find('.mpp-gallery-id').val() );//it is cover upload
			
			parent.find('.mpp-cover-uploading').show();
			
		},
		
		init: function() {
			
			var parent = this.browser.parents('.mpp-cover-wrapper');
			
			jq.each( parent, function(){
				jq(this).find('.mpp-cover-image').append( jq('#mpp-cover-uploading').clone() );
				
			} );
			
		}
        
    });	
	//allow plugins/theme to override the notification	
	if( mpp.notify == undefined ) {

		mpp.notify = function( message, error ) {

			var class_name = 'success';
			if( error != undefined ) {
				class_name = 'error';
			}

			jq('#message').remove();// will it have sideeffects?
			var container_selector = '#mpp-container';
			
			if( ! jQuery( container_selector ).get(0) ) {
				container_selector = '#whats-new-form';//activity posting form
			}
			
			jq( container_selector ).prepend( '<div id="message" class="bp-template-notice mpp-template-notice ' + class_name + '"><p>'+message +'</p></div>').show();
		};

	}

	function open_lightbox( activity_id, position ) {

		//get the details from server

		jq.post(ajaxurl, {
				action: 'mpp_fetch_activity_media',
				activity_id: activity_id,
				cookie: encodeURIComponent(document.cookie)
			},
			function ( response ) {
				if( response.items == undefined ){
					return ;//should we notify too?
				}

				var items = response.items;

				jq.magnificPopup.open({
						items: items,
						type: 'inline',
						closeBtnInside: false,
						preload: [1, 3],
						closeOnBgClick: true,
						gallery: {
							enabled: true,
							navigateByImgClick: true,
							//arrowMarkup: '',// disabled default arrows
							preload: [0, 1] // Will preload 0 - before current, and 1 after the current image
						}
					},
					position
				);

			}, 'json' );
	}
	//popup
	if( jq.fn.magnificPopup != undefined && _mppData.enable_activity_lightbox ) {

		jq(document).on( 'click', '.mpp-activity-photo-list a', function () {

			var $this = jq(this);
			var activity_id = $this.find('img.mpp-attached-media-item').data('mpp-activity-id')
			var position =  $this.index() ;

			if( ! activity_id ) {
				return true;
			}
			//open lightbox
			open_lightbox( activity_id, position );

			return false;
		});


	}

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
       
		if( ! str ) {
			return false;
		}
		
		var data_fields = str.split('&');
		
		for( var i=0; i< data_fields.length; i++ ) {
           
			items = data_fields[i].split('=');
		   
			if( items[0] == item ) {
               return items[1];
			}
		}
       
		return false;
	}
	/**
	 * Extract a query variable from url
	 * 
	 * @param {type} item
	 * @param {type} url
	 * @returns {Boolean|mpp_L1.get_var_in_query.items|String}
	 */
	function get_var_in_url( item, url ) {
		 var url_chunks = url.split( '?' );
		 
		 return get_var_in_query( item, url_chunks[1] );
		 
	}
	
});
/**
 * Activate audi/video player(MediElelement.js player)
 * 
 * @param {type} activity_id
 * @returns {undefined}
 */
function mpp_mejs_activate( activity_id ) {
	
	/* global mejs, _wpmejsSettings */
	var jq = jQuery;
	
	//when document is loading, mediaelementplayer will be undefined, a workaround to avoid double activating it
	if( jq.fn.mediaelementplayer == undefined ) {
		return;
	}

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

	jq('.wp-audio-shortcode, .wp-video-shortcode', jq( '#activity-'+activity_id ) ).mediaelementplayer( settings );
	
	jq('.wp-playlist', jq( '#activity-'+activity_id ) ).each( function() {
			return new WPPlaylistView({ el: this });
	} );
	
}