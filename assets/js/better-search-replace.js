(function( $ ) {
	'use strict';

	/**
	 * Initializes our event handlers.
	 */
	function WPJS_init() {
		WPJS_search_replace();
		WPJS_update_sliders();
	}

	/**
	 * Recursive function for performing batch operations.
	 */
	function WPJS_process_step( action, step, page, data ) {

		$.ajax({
			type: 'POST',
			url: WPJS_object_vars.endpoint + action,
			data: {
				WPJS_ajax_nonce : WPJS_object_vars.ajax_nonce,
				action: action,
				WPJS_step: step,
				WPJS_page: page,
				WPJS_data: data
			},
			dataType: 'json',
			success: function( response ) {

				// Maybe display more details.
				if ( typeof response.message != 'undefined' ) {
					$('.bsr-description').remove();
					$('.bsr-progress-wrap').append( '<p class="description bsr-description">' + response.message + '</p>' );
				}

				if ( 'done' == response.step ) {

					WPJS_update_progress_bar( '100%' );

					// Maybe run another action.
					if ( typeof response.next_action != 'undefined' ) {
						WPJS_update_progress_bar( '0%', 0 );
						WPJS_process_step( response.next_action, 0, 0, response.WPJS_data );
					} else {
						$('.bsr-processing-wrap').remove();
						$('.bsr-disabled').removeClass('bsr-disabled button-disabled' );
						window.location = response.url;
					}

				} else {
					WPJS_update_progress_bar( response.percentage );
					WPJS_process_step( action, response.step, response.page, response.WPJS_data );
				}

			}
		}).fail(function (response) {
			$('.bsr-processing-wrap').remove();
			$('.bsr-disabled').removeClass('bsr-disabled button-disabled' );
			$('#bsr-error-wrap').html( '<div class="error"><p>' + WPJS_object_vars.unknown + '</p></div>' ).show();
			if ( window.console && window.console.log ) {
				console.log(response);
			}
		});

	}

	/**
	 * Initializes a search/replace.
	 */
	function WPJS_search_replace() {

		var search_replace_submit = $( '#bsr-submit' );
		var WPJS_error_wrap = $( '#bsr-error-wrap' );
		search_replace_submit.on( 'click', function( e ) {

			e.preventDefault();

			if ( ! search_replace_submit.hasClass( 'button-disabled' ) ) {

				if ( ! $( '#search_for' ).val() ) {
					WPJS_error_wrap.html( '<div class="error"><p>' + WPJS_object_vars.no_search + '</p></div>' ).show();
				} else if ( ! $( '#bsr-table-select' ).val() ) {
					WPJS_error_wrap.html( '<div class="error"><p>' + WPJS_object_vars.no_tables + '</p></div>' ).show();
				} else {
					var str 	= $( '.bsr-action-form' ).serialize();
					var data 	= str.replace(/%5C/g, "#WPJS_BACKSLASH#" );

					WPJS_error_wrap.html('').hide();
					search_replace_submit.addClass( 'bsr-disabled button-disabled' );
					$( '#bsr-submit-wrap' ).before('<div class="bsr-processing-wrap"><div class="spinner is-active bsr-spinner"></div><div class="bsr-progress-wrap"><div class="bsr-progress"></div></div></div>');
					$('.bsr-progress-wrap').append( '<p class="description bsr-description">' + WPJS_object_vars.processing + '</p>' );
					WPJS_process_step( 'process_search_replace', 0, 0, data );
				}

			}

		});

	}

	/**
	 * Updates the progress bar for AJAX bulk actions.
	 */
	function WPJS_update_progress_bar( percentage, speed ) {
		if ( typeof speed == 'undefined' ) {
			speed = 150;
		}
		$( '.bsr-progress' ).animate({
			width: percentage
		}, speed );
	}

	/**
	 * Updates the "Max Page Size" slider.
	 */
	function WPJS_update_sliders( percentage ) {
		$('#bsr-page-size-slider').slider({
			value: WPJS_object_vars.page_size,
			range: "min",
			min: 1000,
			max: 50000,
			step: 1000,
			slide: function( event, ui ) {
				$('#bsr-page-size-value').text( ui.value );
				$('#WPJS_page_size').val( ui.value );
			}
		});
	}

	WPJS_init();

	function toggle_tooltip( icon ) {
		var icon = $( icon );
		var bubble = icon.next();

		// Close any that are already open
		$( '.helper-message' ).not( bubble ).hide();

		var position = icon.position();

		if ( icon.parent()[0].nodeName === 'TD' ) {
			position = icon.offset();
		}

		if ( bubble.hasClass( 'left' ) ) {
			bubble.css({
				'left': ( position.left - bubble.width() - icon.width() - 29 ) + 'px',
				'top': ( position.top + icon.height() / 2 - 18 ) + 'px'
			})
		} else if ( bubble.hasClass( 'bottom' ) ) {
			bubble.css( {
				'left': ( ( position.left - bubble.width() / 2 ) - 5 ) + 'px',
				'top': ( position.top + icon.height() + 19 ) + 'px'
			} );
		} else {
			bubble.css( {
				'left': ( position.left + icon.width() + 19 ) + 'px',
				'top': ( position.top + icon.height() / 2 - 18 ) + 'px'
			} );
		}

		bubble.toggle();
	}

	$('body').on('thickbox:iframe:loaded', function(){
		var $iframeBody = $( '#TB_window iframe' ).contents().find( 'body' );

		$iframeBody.on( 'mouseover', '.tooltip', function( e ) {
			e.preventDefault();
			$iframeBody.find( '.helper-message' ).hide();
			toggle_tooltip( this );
			e.stopPropagation();
		});

		$iframeBody.on( 'mouseleave', 'td', function( e ) {
			$iframeBody.find( '.helper-message' ).hide();
		});
	});

	$( 'body' ).on( 'mouseover', '.tooltip', function( e ) {
		toggle_tooltip( this );
	} );

	$( 'body' ).on( 'mouseleave', '.tooltip', function( e ) {
		$( '.helper-message' ).hide();
	} );

	$( '.notice.inline' )
		.appendTo('.bsr-notice-container' )
		.css( 'display', 'block' );

	setTimeout(function() {
		const $settings_saved_notice = $( '#setting-error-settings_updated' );
		const $WPJS_notices = $( '.bsr-updated' );

		if ( $settings_saved_notice.length || $WPJS_notices.length ) {
			$( '<div class="bsr-inner-notice-container"></div>' ).prependTo( '.inside' );
			$settings_saved_notice.prependTo( '.bsr-inner-notice-container' ).css( 'display', 'block' );
			$WPJS_notices.prependTo( '.bsr-inner-notice-container' ).css( 'display', 'block' );
		}

		$( '.bsr-inner-notice-container .notice-dismiss' ).on( 'click', function ( e ) {
			if ( ! $WPJS_notices.length ) {
				$( '.bsr-inner-notice-container' ).remove();
			}
		});
	}, 75);



})( jQuery );
