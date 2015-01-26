jQuery( document ).ready( function(){
    var jq = jQuery;
    //prepend the selector buttons to what-new post box
  //  jq('#whats-new-post-in-box').prepend(jq( '#mpp-activity-upload-buttons'));
    
        //on clicking of
    
    mpp.admin_uploader = new mpp.Uploader({
        container: 'body',
        dropzone: '#mpp-gallery-admin-dropzone',
        browser: '#mpp-add-gallery-admin-media',
        feedback: '#mpp-gallery-upload-admin-feedback',
        media_list: '#mpp-gallery-media-admin-list',//where we will list the media
        uploading_media_list : _.template ( "<li id='<%= id %>'><span class='mpp-attached-file-name'><%= name %></span>(<span class='mpp-attached-file-size'><%= size %></spa>)<span class='mpp-remove-file-attachment'>x</span> <b></b></li>" ),
        uploaded_media_list : _.template ( "<li class='mpp-uploaded-media-item' id='mpp-uploaded-media-item-<%= id %>'><img src='<%= url %>' /></li>" ),
        

		complete : function() {
			
            console.log('All success');
		},
        
		success:  function( file ) {
            
                        var sizes = file.get( 'sizes' );
                        var original_url = file.get('url');
                        var id = file.get('id');
                        var file_obj = file.get('file');

                        var thumbnail = sizes.thumbnail;

                        var html ='';
                        html = this.uploaded_media_list({id:id, url: thumbnail.url, });

                        jq(this.feedback).find('li#'+file_obj.id ).remove();

                        jq('ul', this.media_list).append( html);
                        //save in cookie
                        mpp_add_media_to_cookie( id );    
                        //console.log( thumbnail);
                        //update gallery type
                        //
                        jq('#tax_input\\[gallery-type\\]\\[\\]').val( file.get('type_id'));
                        //console.log( 'Type_Id:'+ file.get('type_id'));
                       // console.log( sizes);
                        //console.log('Url:'+original_url );
                        //console.log('ID:'+ id);
                    },
                    
      
        hide_ui : function(){
            
            //this.clear_media_list();
            this.clear_feedback();
            this.hide_dropzone();
        },
		init: function (){
			
		},
        
    });
	if( jq( '#mpp-gallery-admin-dropzone').get(0)){
		mpp.admin_uploader.param( 'context', 'admin' );
		mpp.admin_uploader.param( 'gallery_id', jq('#post_ID').val() );
	}

});