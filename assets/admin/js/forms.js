jQuery( document ).ready( function($) {

	/**
	 * WP Color Picker
	 *
	 * @type {jQuery|HTMLElement}
	 */
	var colorpicker = $('.jb-admin-colorpicker');
	if ( colorpicker.length ) {
		colorpicker.wpColorPicker();
	}


	/**
	 * jQuery UI - Datepicker
	 *
	 * @type {*|jQuery|HTMLElement}
	 */
	var datepicker = $('.jb-datepicker');
	if ( datepicker.length ) {
		datepicker.datepicker({
			dateFormat: $(this).data('format')
		});
	}


	jb_init_helptips();

	/**
	 * Media uploader
	 */
	jQuery( '.jb-media-upload' ).each( function() {
		var field = jQuery(this).find( '.jb-forms-field' );
		var default_value = field.data('default');

		if ( field.val() !== '' && field.val() !== default_value ) {
			field.siblings('.jb-set-image').hide();
			field.siblings('.jb-clear-image').show();
			field.siblings('.icon_preview').show();
		} else {
			if ( field.val() === default_value ) {
				field.siblings('.icon_preview').show();
			}
			field.siblings('.jb-set-image').show();
			field.siblings('.jb-clear-image').hide();
		}
	});


	if ( typeof wp !== 'undefined' && wp.media && wp.media.editor ) {
		var frame;

		jQuery( '.jb-set-image' ).click( function(e) {
			var button = jQuery(this);

			e.preventDefault();

			// If the media frame already exists, reopen it.
			if ( frame ) {
				frame.remove();
				/*frame.open();
				 return;*/
			}

			// Create a new media frame
			frame = wp.media({
				title: button.data('upload_frame'),
				button: {
					text: php_data.texts.select
				},
				multiple: false  // Set to true to allow multiple files to be selected
			});

			// When an image is selected in the media frame...
			frame.on( 'select', function() {
				// Get media attachment details from the frame state
				var attachment = frame.state().get('selection').first().toJSON();

				// Send the attachment URL to our custom image input field.
				button.siblings('.icon_preview').attr( 'src', attachment.url ).show();

				button.siblings('.jb-forms-field').val( attachment.url );
				button.siblings('.jb-media-upload-data-id').val(attachment.id);
				button.siblings('.jb-media-upload-data-width').val(attachment.width);
				button.siblings('.jb-media-upload-data-height').val(attachment.height);
				button.siblings('.jb-media-upload-data-thumbnail').val(attachment.thumbnail);
				button.siblings('.jb-media-upload-data-url').trigger('change');
				button.siblings('.jb-media-upload-url').val(attachment.url);

				button.siblings('.jb-clear-image').show();
				button.hide();

				jQuery( document ).trigger( 'jb_media_upload_select', [button, attachment] );
			});

			frame.open();
		});

		jQuery('.icon_preview').click( function(e) {
			jQuery(this).siblings('.jb-set-image').trigger('click');
		});

		jQuery('.jb-clear-image').click( function() {
			var clear_button = jQuery(this);
			var default_image_url = clear_button.siblings('.jb-forms-field').data('default');
			clear_button.siblings('.jb-set-image').show();
			clear_button.hide();
			clear_button.siblings('.icon_preview').attr( 'src', default_image_url );
			clear_button.siblings('.jb-media-upload-data-id').val('');
			clear_button.siblings('.jb-media-upload-data-width').val('');
			clear_button.siblings('.jb-media-upload-data-height').val('');
			clear_button.siblings('.jb-media-upload-data-thumbnail').val('');
			clear_button.siblings('.jb-forms-field').val( default_image_url );
			clear_button.siblings('.jb-media-upload-data-url').trigger('change');
			clear_button.siblings('.jb-media-upload-url').val( default_image_url );

			jQuery( document ).trigger( 'jb_media_upload_clear', clear_button );
		});
	}


	/**
	 * On option fields change
	 */
	jQuery( document.body ).on( 'change', '.jb-forms-field', function() {
		if ( jQuery('.jb-forms-line[data-conditional*=\'"' + jQuery(this).data('field_id') + '",\']').length > 0 ||
			 jQuery('.jb-forms-line[data-conditional*=\'' + jQuery(this).data('field_id') + '|\']').length > 0 ||
			 jQuery('.jb-forms-line[data-conditional*=\'|' + jQuery(this).data('field_id') + '\']').length > 0 ) {
			run_check_conditions();
		}
	});


	//first load hide unconditional fields
	run_check_conditions();


	/**
	 * Run conditional logic
	 */
	function run_check_conditions() {
		jQuery( '.jb-forms-line' ).removeClass('jb-forms-line-conditioned').each( function() {
			if ( typeof jQuery(this).data('conditional') === 'undefined' || jQuery(this).hasClass('jb-forms-line-conditioned') ) {
				return;
			}

			if ( check_condition( jQuery(this) ) ) {
				jQuery(this).show();
			} else {
				jQuery(this).hide();
			}
		});
	}


	/**
	 * Conditional logic
	 *
	 * true - show field
	 * false - hide field
	 *
	 * @returns {boolean}
	 */
	function check_condition( form_line ) {

		form_line.addClass( 'jb-forms-line-conditioned' );

		var conditional = form_line.data('conditional');
		var condition = conditional[1];
		var value = conditional[2];

		var prefix = form_line.data( 'prefix' );
		var parent_condition = true;

		if ( conditional[0].indexOf( '||' ) === -1 ) {
			var condition_field = jQuery( '#' + prefix + '_' + conditional[0] );

			if ( typeof condition_field.parents('.jb-forms-line').data('conditional') !== 'undefined' ) {
				parent_condition = check_condition( condition_field.parents('.jb-forms-line') );
			}
		}

		var tagName = '';
		var input_type = '';
		var own_condition = false;
		if ( condition === '=' ) {

			if ( conditional[0].indexOf( '||' ) !== -1 ) {

				var complete_condition = false;

				var selectors = conditional[0].split('||');

				jQuery.each( selectors, function( i ) {
					var condition_field = jQuery( '#' + prefix + '_' + selectors[i] );

					own_condition = false;

					parent_condition = true;
					if ( typeof condition_field.parents('.jb-forms-line').data('conditional') !== 'undefined' ) {
						parent_condition = check_condition( condition_field.parents('.jb-forms-line') );
					}

					var tagName = condition_field.prop("tagName").toLowerCase();

					if ( tagName === 'input' ) {
						var input_type = condition_field.attr('type');
						if ( input_type === 'checkbox' ) {
							own_condition = ( value == '1' ) ? condition_field.is(':checked') : ! condition_field.is(':checked');
						} else {
							own_condition = ( condition_field.val() == value );
						}
					} else if ( tagName === 'select' ) {
						own_condition = ( condition_field.val() == value );
					}

					if ( own_condition && parent_condition ) {
						complete_condition = true;
					}
				});

				return complete_condition;

			} else {

				tagName = condition_field.prop("tagName").toLowerCase();

				if ( tagName === 'input' ) {
					input_type = condition_field.attr('type');
					if ( input_type === 'checkbox' ) {
						own_condition = ( value === '1' ) ? condition_field.is(':checked') : ! condition_field.is(':checked');
					} else {
						own_condition = ( condition_field.val() === value );
					}
				} else if ( tagName === 'select' ) {
					own_condition = ( condition_field.val() === value );
				}

			}
		} else if ( condition === '!=' ) {
			tagName = condition_field.prop("tagName").toLowerCase();

			if ( tagName === 'input' ) {
				input_type = condition_field.attr('type');
				if ( input_type === 'checkbox' ) {
					own_condition = ( value === '1' ) ? ! condition_field.is(':checked') : condition_field.is(':checked');
				} else {
					own_condition = ( condition_field.val() !== value );
				}
			} else if ( tagName === 'select' ) {
				own_condition = ( condition_field.val() !== value );
			}
		}

		return ( own_condition && parent_condition );
	}
});