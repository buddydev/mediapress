import jQuery from 'jquery';
import './src/utils/cookie';

//this file mostly contains the modified version of functions
//taken from buddypress.js to add/delete activity and other activity related action

(function ($){

	$(document).ready( function() {

		/**
		 * For Allowing User to post first comment to a media Item when the media item has no entry in the activity table
		 * Thanks to @apeatling, this function is taken from bp-default/_inc/global.js but slightly modified to use prefix mpp-
		 */

		/* Textarea focus */
		$(document).on('focus', '#mpp-whats-new', function() {
			$("#mpp-whats-new-options").animate({
				height:'50px'
			});
			/*$("form#mpp-whats-new-form textarea").animate({
                height:'50px'
            });
            */
			$("#mpp-aw-whats-new-submit").prop("disabled", false);

			let $whats_new_form = $("form#mpp-whats-new-form");
			if ( $whats_new_form.hasClass("submitted") ) {
				$whats_new_form.removeClass("submitted");
			}
		});

		/* On blur, shrink if it's empty */
		$(document).on('blur', '#mpp-whats-new',  function(){
			if (!this.value.match(/\S+/)) {
				this.value = "";
				$("#mpp-whats-new-options").animate({
					height:'0'
				});
				/*
                $("form#mpp-whats-new-form textarea").animate({
                    height:'20px'
                });*/
				$("#mpp-aw-whats-new-submit").prop("disabled", true);
			}
		});

		/* New posts Activity comment(not the replies to comment) on media/gallery */
		$(document).on('click', 'input#mpp-aw-whats-new-submit', function() {
			let activity_list = '',
				button = $(this),
				form = button.closest("form#mpp-whats-new-form");

			form.children().each(function () {
				if ($.nodeName(this, "textarea") || $.nodeName(this, "input"))
					$(this).prop('disabled', true);
			});

			/* Remove any errors */
			$('div.error').remove();

			button.addClass('loading');
			button.prop('disabled', true);
			form.addClass("submitted");

			/* Default POST values */
			let object = '',
				item_id = form.find("#mpp-whats-new-post-in").val(),
				content = form.find("textarea#mpp-whats-new").val();

			/* Set object for non-profile posts */
			if ( item_id > 0 ) {
				object = form.find("#mpp-whats-new-post-object").val();
			}

			let mpp_type = $(form).find('#mpp-activity-type').val(),
				mpp_id = $(form).find('#mpp-item-id').val();
			$.post( ajaxurl, {
					action: 'mpp_add_comment',
					'cookie': get_cookies(),
					'_wpnonce_post_update': form.find("input#_wpnonce_post_update").val(),
					'content': content,
					'object': object,
					'item_id': item_id,
					'mpp-id': mpp_id,
					'mpp-type'	: mpp_type, //media or gallery
					'_bp_as_nonce': $('#_bp_as_nonce').val() || ''
				},
				function(response) {

					form.children().each( function() {
						if ( $.nodeName(this, "textarea") || $.nodeName(this, "input") ) {
							$(this).prop( 'disabled', false );
						}
					});
					button.prop('disabled', false);
					/* Check for errors and append if found. */
					if ( response[0] + response[1] == '-1' ) {
						form.prepend( response.substr( 2, response.length ) );
						$( 'form#' + form.attr('id') + ' div.error').hide().fadeIn( 200 );
					} else {
						activity_list = $(form.parents('.mpp-activity').get(0) );
						if ( 0 === (activity_list.find("ul.mpp-activity-list")).length ) {
							$("div.error").slideUp(100).remove();
							$("div#message").slideUp(100).remove();
							activity_list.append( '<ul id="mpp-activity-stream" class="mpp-activity-list item-list">' );
						}

						activity_list.find("ul#mpp-activity-stream").prepend(response);
						activity_list.find("ul#mpp-activity-stream li:first").addClass('new-update just-posted');


						form.find("textarea#mpp-whats-new").val('');
					}

					form.find("#mpp-whats-new-options").animate({
						height:'0px'
					});
					form.find("textarea").animate({
						height:'20px'
					});
					form.find("#mpp-whats-new-submit").prop("disabled", false).removeClass('loading');
				});

			return false;
		});

		/* Stream event delegation */
		$(document).on('click', 'div.mpp-activity', function(event) {
			let target = $(event.target),
				type, parent, parent_id,
				li, id, link_href, nonce, timestamp,
				oldest_page, just_posted,load_more_args,load_more_search;

			/* Favoriting activity stream items */
			if ( target.hasClass('fav') || target.hasClass('unfav') ) {
				type      = target.hasClass('fav') ? 'fav' : 'unfav';
				parent    = target.closest('.activity-item');
				parent_id = parent.attr('id').substr( 9, parent.attr('id').length );

				target.addClass('loading');

				$.post( ajaxurl, {
						action: 'activity_mark_' + type,
						'cookie': get_cookies(),
						'id': parent_id
					},
					function(response) {
						target.removeClass('loading');

						target.fadeOut( 200, function() {
							$(this).html(response);
							$(this).attr('title', 'fav' === type ? _mppStrings.remove_fav : _mppStrings.mark_as_fav);
							$(this).fadeIn(200);
						});

						if ( 'fav' === type ) {
							if ( !$('.item-list-tabs #activity-favs-personal-li').length ) {
								if ( !$('.item-list-tabs #activity-favorites').length ) {
									$('.item-list-tabs ul #activity-mentions').before( '<li id="activity-favorites"><a href="#">' + _mppStrings.my_favs + ' <span>0</span></a></li>');
								}

								$('.item-list-tabs ul #activity-favorites span').html( Number( $('.item-list-tabs ul #activity-favorites span').html() ) + 1 );
							}

							target.removeClass('fav');
							target.addClass('unfav');

						} else {
						}
					});

				return false;
			}

			/* Delete activity stream items */
			if ( target.hasClass('delete-activity') ) {
				li        = target.parents('div.mpp-activity ul li');
				id        = li.attr('id').substr( 9, li.attr('id').length );
				link_href = target.attr('href');
				nonce     = link_href.split('_wpnonce=');
				timestamp = li.prop( 'class' ).match( /date-recorded-([0-9]+)/ );
				nonce     = nonce[1];

				target.addClass('loading');

				$.post( ajaxurl, {
						action: 'delete_activity',
						'cookie': get_cookies(),
						'id': id,
						'_wpnonce': nonce
					},
					function(response) {

						if ( response[0] + response[1] === '-1' ) {
							li.prepend( response.substr( 2, response.length ) );
							li.children('#message').hide().fadeIn(300);
						} else {
							li.slideUp(300);

							// reset vars to get newest activities
							if ( timestamp && activity_last_recorded === timestamp[1] ) {
								newest_activities = '';
								activity_last_recorded  = 0;
							}
						}
					});

				return false;
			}

			// Spam activity stream items
			if ( target.hasClass( 'spam-activity' ) ) {
				li        = target.parents( 'div.mpp-activity ul li' );
				timestamp = li.prop( 'class' ).match( /date-recorded-([0-9]+)/ );
				target.addClass( 'loading' );

				$.post( ajaxurl, {
						action: 'bp_spam_activity',
						'cookie': encodeURIComponent( document.cookie ),
						'id': li.attr( 'id' ).substr( 9, li.attr( 'id' ).length ),
						'_wpnonce': target.attr( 'href' ).split( '_wpnonce=' )[1]
					},

					function(response) {
						if ( response[0] + response[1] === '-1' ) {
							li.prepend( response.substr( 2, response.length ) );
							li.children( '#message' ).hide().fadeIn(300);
						} else {
							li.slideUp( 300 );
							// reset vars to get newest activities
							if ( timestamp && activity_last_recorded === timestamp[1] ) {
								newest_activities = '';
								activity_last_recorded  = 0;
							}
						}
					});

				return false;
			}

			/* Load more updates at the end of the page */
			if ( target.parent().hasClass('mpp-load-more') ) {
				if ( bp_ajax_request ) {
					bp_ajax_request.abort();
				}

				target.parent().find('.mpp-load-more').addClass('loading');

				if ( null === $.cookie('bp-activity-oldestpage') ) {
					$.cookie('bp-activity-oldestpage', 1, {
						path: '/'
					} );
				}

				oldest_page = ( $.cookie('bp-activity-oldestpage') * 1 ) + 1;
				just_posted = [];

				$('.mpp-activity-list li.just-posted').each( function(){
					just_posted.push( $(this).attr('id').replace( 'mpp-activity-','' ) );
				});

				load_more_args = {
					action: 'activity_get_older_updates',
					'cookie': get_cookies(),
					'page': oldest_page,
					'exclude_just_posted': just_posted.join(',')
				};

				load_more_search = getQueryString('s');

				if ( load_more_search ) {
					load_more_args.search_terms = load_more_search;
				}

				bp_ajax_request = $.post( ajaxurl, load_more_args,
					function(response)
					{
						target.parent().find('.mpp-load-more').removeClass('loading');
						$.cookie( 'bp-activity-oldestpage', oldest_page, {
							path: '/'
						} );
						$('ul.mpp-activity-list').append(response.contents);

						target.parent().hide();
					}, 'json' );

				return false;
			}

			/* Load newest updates at the top of the list */
			if ( target.parent().hasClass('load-newest') ) {

				event.preventDefault();

				target.parent().hide();

				/**
				 * If a plugin is updating the recorded_date of an activity
				 * it will be loaded as a new one. We need to look in the
				 * stream and eventually remove similar ids to avoid "double".
				 */
				let activity_html = $.parseHTML( newest_activities );

				$.each( activity_html, function( i, el ){
					if( 'LI' === el.nodeName && $(el).hasClass( 'just-posted' ) ) {
						if( $( '#' + $(el).attr( 'id' ) ).length ) {
							$( '#' + $(el).attr( 'id' ) ).remove();
						}
					}
				} );

				// Now the stream is cleaned, prepend newest
				$( 'ul.mpp-activity-list' ).prepend( newest_activities );

				// reset the newest activities now they're displayed
				newest_activities = '';
			}
		});

		// Activity "Read More" links inside the gallery/media activity
		$(document).on('click', 'div.mpp-activity .activity-read-more a', function(event) {

			let target = $(event.target),
				link_id = target.parent().attr('id').split('-'),
				a_id    = link_id[4],
				type    = link_id[1], /* activity or acomment */
				inner_class, a_inner;

			inner_class = type === 'acomment' ? 'mpp-acomment-content' : 'mpp-activity-inner';
			a_inner = $('#' + type + '-' + a_id + ' .' + inner_class + ':first' );
			$(target).addClass('loading');

			$.post( ajaxurl, {
					action: 'get_single_activity_content',//should we override it too?
					'activity_id': a_id
				},
				function(response) {
					$(a_inner).slideUp(300).html(response).slideDown(300);
				});

			return false;
		});

		/**** Activity Comments *******************************************************/

		/* Hide all activity comment forms */
		$('form.mpp-ac-form').hide();

		/* Activity list event delegation */
		$(document).on( 'click', '.mpp-activity', function(event) {

			let target = $(event.target),
				id, ids, a_id, c_id, form,
				form_parent, form_id,
				tmp_id, comment_id, comment,content,
				ajaxdata,
				ak_nonce,
				show_all_a, new_count,
				link_href, comment_li, nonce;

			/* Comment / comment reply links */
			if ( target.hasClass('mpp-acomment-reply') || target.parent().hasClass('mpp-acomment-reply') ) {
				if ( target.parent().hasClass('mpp-acomment-reply') ) {
					target = target.parent();
				}

				 id = target.attr('id');
				ids = id.split('-');

				 a_id = ids[3];
				 c_id = target.attr('href').substr( 10, target.attr('href').length );

				 form = $( '#mpp-ac-form-' + a_id );

				form.css( 'display', 'none' );
				form.removeClass('root');
				$('.mpp-ac-form').hide();

				/* Hide any error messages */
				form.children('div').each( function() {
					if ( $(this).hasClass( 'error' ) )
						$(this).hide();
				});

				if ( ids[2] !== 'comment' ) {
					$('#mpp-acomment-' + c_id).append( form );
				} else {
					$('#mpp-activity-' + a_id + ' .mpp-activity-comments').append( form );
				}

				if ( form.parent().hasClass( 'mpp-activity-comments' ) ) {
					form.addClass('root');
				}

				form.slideDown( 200 );
				$.scrollTo( form, 500, {
					offset:-100,
					easing:'swing'
				} );
				$('#mpp-ac-form-' + ids[3] + ' textarea').focus();

				return false;
			}

			/* Activity comment posting */
			if ( target.attr('name') == 'mpp_ac_form_submit' ) {
				 form        = target.parents( 'form' );
				 form_parent = form.parent();
				 form_id     = form.attr('id').split('-');

				if ( !form_parent.hasClass('mpp-activity-comments') ) {
					 tmp_id = form_parent.attr('id').split('-');
					 comment_id = tmp_id[2];
				} else {
					 comment_id = form_id[3];
				}
				content = $( '#' + form.attr('id') + ' textarea' );

				/* Hide any error messages */
				$( '#' + form.attr('id') + ' div.error').hide();
				target.addClass('loading').prop('disabled', true);
				content.addClass('loading').prop('disabled', true);

				 ajaxdata = {
					action: 'mpp_add_reply',
					'cookie': get_cookies(),
					'_wpnonce_new_activity_comment': $("input#_wpnonce_new_activity_comment").val(),
					'comment_id': comment_id,
					'form_id': form_id[3],
					'content': content.val()
				};

				// Akismet
				ak_nonce = $('#_bp_as_nonce_' + comment_id).val();
				if ( ak_nonce ) {
					ajaxdata['_bp_as_nonce_' + comment_id] = ak_nonce;
				}

				$.post( ajaxurl, ajaxdata, function(response) {
					target.removeClass('loading');
					content.removeClass('loading');
					/* Check for errors and append if found. */
					if ( response[0] + response[1] == '-1' ) {
						form.append( $( response.substr( 2, response.length ) ).hide().fadeIn( 200 ) );
					} else {
						let activity_comments = form.parent();
						form.fadeOut( 200, function() {
							if ( 0 === activity_comments.children('ul').length ) {
								if ( activity_comments.hasClass('mpp-activity-comments') ) {
									activity_comments.prepend('<ul></ul>');
								} else {
									activity_comments.append('<ul></ul>');
								}
							}

							/* Preceeding whitespace breaks output with jQuery 1.9.0 */
							let the_comment = $.trim( response );

							activity_comments.children('ul').append( $( the_comment ).hide().fadeIn( 200 ) );
							form.children('textarea').val('');
							activity_comments.parent().addClass('has-comments');
						} );

						$( '#' + form.attr('id') + ' textarea').val('');

						/* Increase the "Reply (X)" button count */
						$('#mpp-activity-' + form_id[3] + ' a.mpp-acomment-reply span').html( Number( $('#mpp-activity-' + form_id[3] + ' a.mpp-acomment-reply span').html() ) + 1 );

						// Increment the 'Show all x comments' string, if present
						let show_all_a = activity_comments.find('.show-all').find('a');
						if ( show_all_a ) {
							new_count = $('li#mpp-activity-' + form_id[3] + ' a.mpp-acomment-reply span').html();
							show_all_a.html( _mppStrings.show_x_comments.replace( '%d', new_count ) );
						}
					}
					$(target).prop('disabled', false);
					$(content).prop('disabled', false);
				});

				return false;
			}

			/* Deleting an activity comment */
			if ( target.hasClass('mpp-acomment-delete') ) {
				 link_href = target.attr('href');
				 comment_li = target.parent().parent();
				 form = comment_li.parents('div.mpp-activity-comments').children('form');

				 nonce = link_href.split('_wpnonce=');
				nonce = nonce[1];

				comment_id = link_href.split('cid=');
				comment_id = comment_id[1].split('&');
				comment_id = comment_id[0];

				target.addClass('loading');

				/* Remove any error messages */
				$('.mpp-activity-comments ul .error').remove();

				/* Reset the form position */
				comment_li.parents('.mpp-activity-comments').append(form);

				$.post( ajaxurl, {
						action: 'delete_activity_comment',
						'cookie': get_cookies(),
						'_wpnonce': nonce,
						'id': comment_id
					},
					function(response) {
						/* Check for errors and append if found. */
						if ( response[0] + response[1] === '-1' ) {
							comment_li.prepend( $( response.substr( 2, response.length ) ).hide().fadeIn( 200 ) );
						} else {
							let children  = $( '#' + comment_li.attr('id') + ' ul' ).children('li'),
								child_count = 0,
								count_span, new_count, show_all_a;

							$(children).each( function() {
								if ( !$(this).is(':hidden') ) {
									child_count++;
								}
							});
							comment_li.fadeOut(200, function() {
								comment_li.remove();
							});

							/* Decrease the "Reply (X)" button count */
							count_span = $('#' + comment_li.parents('#mpp-activity-stream > li').attr('id') + ' a.mpp-acomment-reply span');
							new_count = count_span.html() - ( 1 + child_count );
							count_span.html(new_count);

							// Change the 'Show all x comments' text
							show_all_a = comment_li.siblings('.show-all').find('a');
							if ( show_all_a ) {
								show_all_a.html( _mppStrings.show_x_comments.replace( '%d', new_count ) );
							}

							/* If that was the last comment for the item, remove the has-comments class to clean up the styling */
							if ( 0 === new_count ) {
								$(comment_li.parents('#mpp-activity-stream > li')).removeClass('has-comments');
							}
						}
					});


				return false;
			}

			// Spam an activity stream comment
			if ( target.hasClass( 'spam-activity-comment' ) ) {
				link_href  = target.attr( 'href' );
				comment_li = target.parent().parent();

				target.addClass('loading');

				// Remove any error messages
				$( '.mpp-activity-comments ul div.error' ).remove();

				// Reset the form position
				comment_li.parents( '.mpp-activity-comments' ).append( comment_li.parents( '.mpp-activity-comments' ).children( 'form' ) );

				$.post( ajaxurl, {
						action: 'bp_spam_activity_comment',
						'cookie': encodeURIComponent( document.cookie ),
						'_wpnonce': link_href.split( '_wpnonce=' )[1],
						'id': link_href.split( 'cid=' )[1].split( '&' )[0]
					},

					function ( response ) {
						// Check for errors and append if found.
						if ( response[0] + response[1] === '-1' ) {
							comment_li.prepend( $( response.substr( 2, response.length ) ).hide().fadeIn( 200 ) );

						} else {
							let children  = $( '#' + comment_li.attr( 'id' ) + ' ul' ).children( 'li' ),
								child_count = 0,
								parent_li;

							$(children).each( function() {
								if ( !$( this ).is( ':hidden' ) ) {
									child_count++;
								}
							});
							comment_li.fadeOut( 200 );

							// Decrease the "Reply (X)" button count
							parent_li = comment_li.parents( '#mpp-activity-stream > li' );
							$( '#' + parent_li.attr( 'id' ) + ' a.mpp-acomment-reply span' ).html( $( '#' + parent_li.attr( 'id' ) + ' a.mpp-acomment-reply span' ).html() - ( 1 + child_count ) );
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

			// Canceling an activity comment
			if ( target.hasClass( 'mpp-ac-reply-cancel' ) ) {
				$(target).closest('.mpp-ac-form').slideUp( 200 );
				return false;
			}
		});
		/* Escape Key Press for cancelling comment forms */
		$(document).on('keydown',  function(e) {
			let element;
			e = e || window.event;
			if (e.target) {
				element = e.target;
			} else if (e.srcElement) {
				element = e.srcElement;
			}

			if( element.nodeType === 3) {
				element = element.parentNode;
			}

			if( e.ctrlKey === true || e.altKey === true || e.metaKey === true ) {
				return;
			}

			let keyCode = (e.keyCode) ? e.keyCode : e.which;

			if ( keyCode === 27 ) {
				if (element.tagName === 'TEXTAREA') {
					if ( $(element).hasClass('mpp-ac-input') ) {
						$(element).parent().parent().parent().slideUp( 200 );
						return false;
					}
				}
			}
		});
	});//end of domready.

	//a replacement of bp_get_querystring for themes that does not have it
	function getQueryString( n ) {
		let half = location.search.split( n + '=' )[1];
		return half ? decodeURIComponent( half.split('&')[0] ) : null;
	}

//copy of bp_get_cookies since some themes might not include that
	/* Returns a querystring of BP cookies (cookies beginning with 'bp-') */
	function get_cookies() {
		// get all cookies and split into an array
		let allCookies   = document.cookie.split(";"),
			bpCookies    = {},
			cookiePrefix = 'bp-';

		// loop through cookies
		for (let i = 0; i < allCookies.length; i++) {
			let cookie    = allCookies[i],
				delimiter = cookie.indexOf("=");
			let name      = $.trim( unescape( cookie.slice(0, delimiter) ) ),
				value     = unescape( cookie.slice(delimiter + 1) );

			// if BP cookie, store it
			if ( name.indexOf(cookiePrefix) == 0 ) {
				bpCookies[name] = value;
			}
		}

		// returns BP cookies as querystring
		return encodeURIComponent( $.param(bpCookies) );
	}

})(jQuery);
