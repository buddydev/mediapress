var mpJQ = jQuery.noConflict;
mpJQ( document ).ready( function(){
	

	
/**
 * For Allowing User to post first comment to a media Item when the media item has no entry in the activity table
 * Thanks to @apeatling, this function is taken from bp-default/_inc/global.js but slightly modified
 */

	/* Textarea focus */
	mpJQ(document).on('focus', '#whats-new', function(){
		mpJQ("#whats-new-options").animate({
			height:'40px'
		});
		mpJQ("form#whats-new-form textarea").animate({
			height:'50px'
		});
		mpJQ("#aw-whats-new-submit").prop("disabled", false);

		var $whats_new_form = mpJQ("form#whats-new-form");
		if ( $whats_new_form.hasClass("submitted") ) {
			$whats_new_form.removeClass("submitted");	
		}
	});

	/* On blur, shrink if it's empty */
	mpJQ(document).on('blur', '#whats-new',  function(){
		if (!this.value.match(/\S+/)) {
			this.value = "";
			mpJQ("#whats-new-options").animate({
				height:'40px'
			});
			mpJQ("form#whats-new-form textarea").animate({
				height:'20px'
			});
			mpJQ("#aw-whats-new-submit").prop("disabled", true);
		}
	});

mpJQ("#mpp-whats-new-submit").prop("disabled", false);
	/* New posts */
	mpJQ(document).on('click', 'input#mpp-whats-new-submit', function() {
		var activity_list = '';
		var button = mpJQ(this);
		var form = button.closest("form#whats-new-form");

		form.children().each( function() {
			if ( mpJQ.nodeName(this, "textarea") || mpJQ.nodeName(this, "input") )
				mpJQ(this).prop( 'disabled', true );
		});

		/* Remove any errors */
		mpJQ('div.error').remove();
		
		button.addClass('loading');
		button.prop('disabled', true);
		form.addClass("submitted");
		
		/* Default POST values */
		var object = '';
		var item_id = form.find("#whats-new-post-in").val();
		var content = form.find("textarea#whats-new").val();

		/* Set object for non-profile posts */
		if ( item_id > 0 ) {
			object = form.find("#whats-new-post-object").val();
		}

		var mpp_type = mpJQ(form).find('#mpp-activity-type').val();
		var mpp_id = mpJQ(form).find('#mpp-item-id').val();
		mpJQ.post( ajaxurl, {
			action: 'mpp_add_comment',
			'cookie': bp_get_cookies(),
			'_wpnonce_post_update': form.find("input#_wpnonce_post_update").val(),
			'content': content,
			'object': object,
			'item_id': item_id,
			'mpp-id': mpp_id,
			'mpp-type'	: mpp_type, //media or gallery
			'_bp_as_nonce': mpJQ('#_bp_as_nonce').val() || ''
		},
		function(response) {

			form.children().each( function() {
				if ( mpJQ.nodeName(this, "textarea") || mpJQ.nodeName(this, "input") ) {
					mpJQ(this).prop( 'disabled', false );
				}
			});
			button.prop('disabled', false);
			/* Check for errors and append if found. */
			if ( response[0] + response[1] == '-1' ) {
				form.prepend( response.substr( 2, response.length ) );
				mpJQ( 'form#' + form.attr('id') + ' div.error').hide().fadeIn( 200 );
			} else {
				activity_list = mpJQ(form.parents('.activity').get(0) );
				if ( 0 == (activity_list.find("ul.activity-list")).length ) {
					mpJQ("div.error").slideUp(100).remove();
					mpJQ("div#message").slideUp(100).remove();
					activity_list.append( '<ul id="activity-stream" class="activity-list item-list">' );
				}

				activity_list.find("ul#activity-stream").prepend(response);
				activity_list.find("ul#activity-stream li:first").addClass('new-update just-posted');

				
				form.find("textarea#whats-new").val('');
			}

			form.find("#whats-new-options").animate({
				height:'0px'
			});
			form.find("form#whats-new-form textarea").animate({
				height:'20px'
			});
			form.find("#mpp-whats-new-submit").prop("disabled", false).removeClass('loading');
		});

		return false;
	});
	/* Stream event delegation */
	mpJQ(document).on('click', '.mpp-lightbox-content div.activity', function(event) {
		var activity_list = mpJQ(mpJQ('.mpp-lightbox-content div.activity').get(0));
		
		var target = mpJQ(event.target);

		/* Favoriting activity stream items */
		if ( target.hasClass('fav') || target.hasClass('unfav') ) {
			var type = target.hasClass('fav') ? 'fav' : 'unfav';
			var parent = target.closest('.activity-item');
			var parent_id = parent.attr('id').substr( 9, parent.attr('id').length );

			target.addClass('loading');

			mpJQ.post( ajaxurl, {
				action: 'activity_mark_' + type,
				'cookie': bp_get_cookies(),
				'id': parent_id
			},
			function(response) {
				target.removeClass('loading');

				target.fadeOut( 100, function() {
					mpJQ(this).html(response);
					mpJQ(this).attr('title', 'fav' == type ? BP_DTheme.remove_fav : BP_DTheme.mark_as_fav);
					mpJQ(this).fadeIn(100);
				});

				if ( 'fav' == type ) {
					if ( !mpJQ('.item-list-tabs li#activity-favorites').length )
						mpJQ('.item-list-tabs ul li#activity-mentions').before( '<li id="activity-favorites"><a href="#">' + BP_DTheme.my_favs + ' <span>0</span></a></li>');

					target.removeClass('fav');
					target.addClass('unfav');

					mpJQ('.item-list-tabs ul li#activity-favorites span').html( Number( mpJQ('.item-list-tabs ul li#activity-favorites span').html() ) + 1 );
				} else {
					target.removeClass('unfav');
					target.addClass('fav');

					mpJQ('.item-list-tabs ul li#activity-favorites span').html( Number( mpJQ('.item-list-tabs ul li#activity-favorites span').html() ) - 1 );

					if ( !Number( mpJQ('.item-list-tabs ul li#activity-favorites span').html() ) ) {
						if ( mpJQ('.item-list-tabs ul li#activity-favorites').hasClass('selected') )
							bp_activity_request( null, null );

						mpJQ('.item-list-tabs ul li#activity-favorites').remove();
					}
				}

				if ( 'activity-favorites' == mpJQ( '.item-list-tabs li.selected').attr('id') )
					target.parent().parent().parent().slideUp(100);
			});

			return false;
		}

		/* Delete activity stream items */
		if ( target.hasClass('delete-activity') ) {
			var li        = target.parents('div.activity ul li');
			var id        = li.attr('id').substr( 9, li.attr('id').length );
			var link_href = target.attr('href');
			var nonce     = link_href.split('_wpnonce=');

			nonce = nonce[1];

			target.addClass('loading');

			mpJQ.post( ajaxurl, {
				action: 'delete_activity',
				'cookie': bp_get_cookies(),
				'id': id,
				'_wpnonce': nonce
			},
			function(response) {

				if ( response[0] + response[1] == '-1' ) {
					li.prepend( response.substr( 2, response.length ) );
					li.children('div#message').hide().fadeIn(300);
				} else {
					li.slideUp(300);
				}
			});

			return false;
		}

		// Spam activity stream items
		if ( target.hasClass( 'spam-activity' ) ) {
			var li = target.parents( 'div.activity ul li' );
			target.addClass( 'loading' );

			mpJQ.post( ajaxurl, {
				action: 'bp_spam_activity',
				'cookie': encodeURIComponent( document.cookie ),
				'id': li.attr( 'id' ).substr( 9, li.attr( 'id' ).length ),
				'_wpnonce': target.attr( 'href' ).split( '_wpnonce=' )[1]
			},

			function(response) {
				if ( response[0] + response[1] === '-1' ) {
					li.prepend( response.substr( 2, response.length ) );
					li.children( 'div#message' ).hide().fadeIn(300);
				} else {
					li.slideUp( 300 );
				}
			});

			return false;
		}

		/* Load more updates at the end of the page */
		if ( target.parent().hasClass('load-more') ) {
			activity_list.find("li.load-more").addClass('loading');

			if ( null == mpJQ.cookie('bp-activity-oldestpage') )
				mpJQ.cookie('bp-activity-oldestpage', 1, {
					path: '/'
				} );

			var oldest_page = ( mpJQ.cookie('bp-activity-oldestpage') * 1 ) + 1;

			var just_posted = [];
			
			mpJQ('.activity-list li.just-posted').each( function(){
				just_posted.push( mpJQ(this).attr('id').replace( 'activity-','' ) );
			});

			mpJQ.post( ajaxurl, {
				action: 'activity_get_older_updates',
				'cookie': bp_get_cookies(),
				'page': oldest_page,
				'exclude_just_posted': just_posted.join(',')
			},
			function(response)
			{
				activity_list.find("li.load-more").removeClass('loading');
				mpJQ.cookie( 'bp-activity-oldestpage', oldest_page, {
					path: '/'
				} );
				activity_list.find("ul.activity-list").append(response.contents);

				target.parent().hide();
			}, 'json' );

			return false;
		}
	});

	// Activity "Read More" links
	mpJQ(document).on('click', '.mpp-lightbox-content .activity-read-more a', function(event) {
		var target = mpJQ(event.target);
		var link_id = target.parent().attr('id').split('-');
		var a_id = link_id[3];
		var type = link_id[0]; /* activity or acomment */

		var inner_class = type == 'acomment' ? 'acomment-content' : 'activity-inner';
		var a_inner = mpJQ('li#' + type + '-' + a_id + ' .' + inner_class + ':first' );
		mpJQ(target).addClass('loading');

		mpJQ.post( ajaxurl, {
			action: 'get_single_activity_content',
			'activity_id': a_id
		},
		function(response) {
			mpJQ(a_inner).slideUp(300).html(response).slideDown(300);
		});

		return false;
	});

	/**** Activity Comments *******************************************************/

	/* Hide all activity comment forms */
	mpJQ('.mpp-lightbox-content .activity form.ac-form').hide();

	

	/* Activity list event delegation */
	mpJQ(document).on( 'click', '.mpp-lightbox-content .activity', function(event) {
		var activity_list = mpJQ(mpJQ('.mpp-lightbox-content div.activity').get(0));
		var target = mpJQ(event.target);

		/* Comment / comment reply links */
		if ( target.hasClass('acomment-reply') || target.parent().hasClass('acomment-reply') ) {
			if ( target.parent().hasClass('acomment-reply') )
				target = target.parent();

			var id = target.attr('id');
			ids = id.split('-');

			var a_id = ids[2]
			var c_id = target.attr('href').substr( 10, target.attr('href').length );
			var form = activity_list.find( '#ac-form-' + a_id );

			form.css( 'display', 'none' );
			form.removeClass('root');
			mpJQ('.ac-form').hide();

			/* Hide any error messages */
			form.children('div').each( function() {
				if ( mpJQ(this).hasClass( 'error' ) )
					mpJQ(this).hide();
			});

			if ( ids[1] != 'comment' ) {
				activity_list.find('.activity-comments li#acomment-' + c_id).append( form );
			} else {
				activity_list.find('li#activity-' + a_id + ' .activity-comments').append( form );
			}

			if ( form.parent().hasClass( 'activity-comments' ) )
				form.addClass('root');

			form.slideDown( 200 );
			mpJQ.scrollTo( form, 500, {
				offset:-100,
				easing:'easeOutQuad'
			} );
			activity_list.find('#ac-form-' + ids[2] + ' textarea').focus();

			return false;
		}

		/* Activity comment posting */
		if ( target.attr('name') == 'ac_form_submit' ) {
			var form        = target.parents( 'form' );
			var form_parent = form.parent();
			var form_id     = form.attr('id').split('-');

			if ( !form_parent.hasClass('activity-comments') ) {
				var tmp_id = form_parent.attr('id').split('-');
				var comment_id = tmp_id[1];
			} else {
				var comment_id = form_id[2];
			}

			/* Hide any error messages */
			activity_list.find( 'form#' + form.attr('id') + ' div.error').hide();
			target.addClass('loading').prop('disabled', true);

			var ajaxdata = {
				action: 'new_activity_comment',
				'cookie': bp_get_cookies(),
				'_wpnonce_new_activity_comment': activity_list.find("input#_wpnonce_new_activity_comment").val(),
				'comment_id': comment_id,
				'form_id': form_id[2],
				'content': activity_list.find('form#' + form.attr('id') + ' textarea').val()
			};

			// Akismet
			var ak_nonce = activity_list.find('#_bp_as_nonce_' + comment_id).val();
			if ( ak_nonce ) {
				ajaxdata['_bp_as_nonce_' + comment_id] = ak_nonce;
			}

			mpJQ.post( ajaxurl, ajaxdata, function(response) {
				target.removeClass('loading');

				/* Check for errors and append if found. */
				if ( response[0] + response[1] == '-1' ) {
					form.append( mpJQ( response.substr( 2, response.length ) ).hide().fadeIn( 200 ) );
				} else {
					var activity_comments = form.parent();
					form.fadeOut( 200, function() {
						if ( 0 == activity_comments.children('ul').length ) {
							if ( activity_comments.hasClass('activity-comments') ) {
								activity_comments.prepend('<ul></ul>');
							} else {
								activity_comments.append('<ul></ul>');
							}
						}

						/* Preceeding whitespace breaks output with jQuery 1.9.0 */
						var the_comment = mpJQ.trim( response );

						activity_comments.children('ul').append( mpJQ( the_comment ).hide().fadeIn( 200 ) );
						form.children('textarea').val('');
						activity_comments.parent().addClass('has-comments');
					} );

					activity_list.find( 'form#' + form.attr('id') + ' textarea').val('');

					/* Increase the "Reply (X)" button count */
					activity_list.find('li#activity-' + form_id[2] + ' a.acomment-reply span').html( Number( mpJQ('li#activity-' + form_id[2] + ' a.acomment-reply span').html() ) + 1 );

					// Increment the 'Show all x comments' string, if present
					var show_all_a = activity_comments.find('.show-all').find('a');
					if ( show_all_a ) {
						var new_count = activity_list.find('li#activity-' + form_id[2] + ' a.acomment-reply span').html();
						show_all_a.html( BP_DTheme.show_x_comments.replace( '%d', new_count ) );
					}
				}

				mpJQ(target).prop("disabled", false);
			});

			return false;
		}

		/* Deleting an activity comment */
		if ( target.hasClass('acomment-delete') ) {
			var link_href = target.attr('href');
			var comment_li = target.parent().parent();
			var form = comment_li.parents('div.activity-comments').children('form');

			var nonce = link_href.split('_wpnonce=');
			nonce = nonce[1];

			var comment_id = link_href.split('cid=');
			comment_id = comment_id[1].split('&');
			comment_id = comment_id[0];

			target.addClass('loading');

			/* Remove any error messages */
			mpJQ('.activity-comments ul .error').remove();

			/* Reset the form position */
			comment_li.parents('.activity-comments').append(form);

			mpJQ.post( ajaxurl, {
				action: 'delete_activity_comment',
				'cookie': bp_get_cookies(),
				'_wpnonce': nonce,
				'id': comment_id
			},
			function(response)
			{
				/* Check for errors and append if found. */
				if ( response[0] + response[1] == '-1' ) {
					comment_li.prepend( mpJQ( response.substr( 2, response.length ) ).hide().fadeIn( 200 ) );
				} else {
					var children = mpJQ( 'li#' + comment_li.attr('id') + ' ul' ).children('li');
					var child_count = 0;
					mpJQ(children).each( function() {
						if ( !mpJQ(this).is(':hidden') )
							child_count++;
					});
					comment_li.fadeOut(200, function() {
						comment_li.remove();
					});

					/* Decrease the "Reply (X)" button count */
					var count_span = mpJQ('li#' + comment_li.parents('ul#activity-stream > li').attr('id') + ' a.acomment-reply span');
					var new_count = count_span.html() - ( 1 + child_count );
					count_span.html(new_count);
	
					// Change the 'Show all x comments' text
					var show_all_a = comment_li.siblings('.show-all').find('a');
					if ( show_all_a ) {
						show_all_a.html( BP_DTheme.show_x_comments.replace( '%d', new_count ) );
					}

					/* If that was the last comment for the item, remove the has-comments class to clean up the styling */
					if ( 0 == new_count ) {
						mpJQ(comment_li.parents('ul#activity-stream > li')).removeClass('has-comments');
					}
				}
			});

			return false;
		}

		// Spam an activity stream comment
		if ( target.hasClass( 'spam-activity-comment' ) ) {
			var link_href  = target.attr( 'href' );
			var comment_li = target.parent().parent();

			target.addClass('loading');

			// Remove any error messages
			mpJQ( '.activity-comments ul div.error' ).remove();

			// Reset the form position
			comment_li.parents( '.activity-comments' ).append( comment_li.parents( '.activity-comments' ).children( 'form' ) );

			mpJQ.post( ajaxurl, {
				action: 'bp_spam_activity_comment',
				'cookie': encodeURIComponent( document.cookie ),
				'_wpnonce': link_href.split( '_wpnonce=' )[1],
				'id': link_href.split( 'cid=' )[1].split( '&' )[0]
			},

			function ( response ) {
				// Check for errors and append if found.
				if ( response[0] + response[1] == '-1' ) {
					comment_li.prepend( mpJQ( response.substr( 2, response.length ) ).hide().fadeIn( 200 ) );

				} else {
					var children = mpJQ( 'li#' + comment_li.attr( 'id' ) + ' ul' ).children( 'li' );
					var child_count = 0;
					mpJQ(children).each( function() {
						if ( !mpJQ( this ).is( ':hidden' ) ) {
							child_count++;
						}
					});
					comment_li.fadeOut( 200 );

					// Decrease the "Reply (X)" button count
					var parent_li = comment_li.parents( 'ul#activity-stream > li' );
					mpJQ( 'li#' + parent_li.attr( 'id' ) + ' a.acomment-reply span' ).html( mpJQ( 'li#' + parent_li.attr( 'id' ) + ' a.acomment-reply span' ).html() - ( 1 + child_count ) );
				}
			});

			return false;
		}

		/* Showing hidden comments - pause for half a second */
		if ( target.parent().hasClass('show-all') ) {
			target.parent().addClass('loading');

			setTimeout( function() {
				target.parent().parent().children('li').fadeIn(200, function() {
					target.parent().remove();
				});
			}, 600 );

			return false;
		}
	});


})	;