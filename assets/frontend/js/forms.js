if ( typeof ( wp.JB ) !== 'object' ) {
	wp.JB = {};
}

if ( typeof ( wp.JB.forms ) !== 'object' ) {
	wp.JB.forms = {};
}

wp.JB.forms = {
	classes: {
		form: 'jb-form',
		row: 'jb-form-row',
	},
	objects: {
		wrapper: {},
		rows: {},
	},
	setObjects: function() {
		wp.JB.forms.objects.wrapper = jQuery( '.' + wp.JB.forms.classes.form );
		wp.JB.forms.objects.rows = jQuery( '.' + wp.JB.forms.classes.row );
	},
	conditionalFields: {
		/**
		 * Run conditional logic
		 */
		trigger: function() {
			jQuery( '.jb-form-row' ).removeClass('jb-forms-line-conditioned').each( function() {
				if ( typeof jQuery(this).data('conditional') === 'undefined' || jQuery(this).hasClass('jb-forms-line-conditioned') ) {
					return;
				}
				var required = jQuery(this).attr('data-required');

				if ( wp.JB.forms.conditionalFields.checkLineCondition( jQuery(this) ) ) {
					jQuery(this).show();
					jQuery(this).find('select, input').prop('disabled', false);
					if ( required === 'required' ) {
						jQuery(this).find('select, input').attr('required', 'required');
					}
				} else {
					jQuery(this).hide();
					jQuery(this).find('select, input').removeAttr('required').prop('disabled', true);
				}
			});
		},
		/**
		 * Conditional logic
		 *
		 * true - show field
		 * false - hide field
		 *
		 * @returns {boolean}
		 */
		checkLineCondition: function( form_line ) {
			form_line.addClass( 'jb-forms-line-conditioned' );
			var conditional = form_line.data('conditional');
			var condition = conditional[1];
			var value = conditional[2];

			var parent_condition = true;

			if ( conditional[0].indexOf( '||' ) === -1 ) {
				var condition_field = jQuery( '#' + conditional[0] );
				if ( typeof condition_field.parents('.jb-form-row').data('conditional') !== 'undefined' ) {
					parent_condition = wp.JB.forms.conditionalFields.checkLineCondition( condition_field.parents('.jb-form-row') );
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
						var condition_field = jQuery( '#' + selectors[i] );

						own_condition = false;

						parent_condition = true;
						if ( typeof condition_field.parents('.jb-form-row').data('conditional') !== 'undefined' ) {
							parent_condition = wp.JB.forms.conditionalFields.checkLineCondition( condition_field.parents('.jb-form-row') );
						}

						var tagName = condition_field.prop("tagName").toLowerCase();

						if ( tagName === 'input' ) {
							var input_type = condition_field.attr('type');
							if ( input_type === 'checkbox' ) {
								own_condition = ( value == '1' ) ? condition_field.is(':checked') : ! condition_field.is(':checked');
							} else {
								if ( Array.isArray( value ) ) {
									own_condition = ( value.indexOf( condition_field.val() ) !== -1 );
								} else {
									own_condition = ( condition_field.val() === value );
								}
							}
						} else if ( tagName === 'select' ) {
							if ( Array.isArray( value ) ) {
								own_condition = ( value.indexOf( condition_field.val() ) !== -1 );
							} else {
								own_condition = ( condition_field.val() === value );
							}
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
							if ( Array.isArray( value ) ) {
								own_condition = ( value.indexOf( condition_field.val() ) !== -1 );
							} else {
								own_condition = ( condition_field.val() === value );
							}
						}
					} else if ( tagName === 'select' ) {
						if ( Array.isArray( value ) ) {
							own_condition = ( value.indexOf( condition_field.val() ) !== -1 );
						} else {
							own_condition = ( condition_field.val() === value );
						}
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
	}
};

jQuery( document ).ready( function($) {
	wp.JB.forms.setObjects();

	/**
	 * jQuery UI - Datepicker
	 *
	 * @type {*|jQuery|HTMLElement}
	 */
	var jb_media_uploader;
	var jb_media_uploaders = {};
	var jb_datepicker = $('.jb-datepicker');
	if ( jb_datepicker.length ) {
		jb_datepicker.each( function() {
			var $this = $(this);
			// we don't use dateFormat WP datepicker UI gets the default format from WP settings by default
			$this.datepicker({
				altField:  $this.siblings('.jb-datepicker-default-format'),
				altFormat: 'yy-mm-dd'
			});
		});
	}

	$( document.body ).on( 'change', '.jb-datepicker', function() {
		var $this = $(this);
		if ( '' === $this.val() ) {
			$this.siblings('.jb-datepicker-default-format').val('');
		}
	});

	$( document.body ).on( 'click', 'input.jb-forms-condition-option', function() {
		var value = $(this).val();

		var sub_wrappers = $(this).parents( '.' + wp.JB.forms.classes.row ).find('span[data-visible-if]');
		sub_wrappers.hide().find('input, select').attr('disabled', 'disabled').prop('disabled', true);

		var sub_wrapper = $(this).parents( '.' + wp.JB.forms.classes.row ).find('span[data-visible-if="' + value + '"]');
		sub_wrapper.show().find('input, select').removeAttr('disabled').prop('disabled', false);
	});

	$( 'input.jb-forms-condition-option:checked' ).trigger('click');

	$( document.body ).on( 'click', '.jb-change-media', function(e) {
		e.preventDefault();
		$(this).parents('.jb-uploaded-wrapper').removeClass('jb-uploaded').addClass('jb-waiting-change');
		$(this).parents('.jb-uploaded-wrapper').siblings('.jb-uploader').removeClass('jb-uploaded');
	});

	$( document.body ).on( 'click', '.jb-cancel-change-media', function(e) {
		e.preventDefault();
		$(this).parents('.jb-uploaded-wrapper').addClass('jb-uploaded').removeClass('jb-waiting-change');
		$(this).parents('.jb-uploaded-wrapper').siblings('.jb-uploader').addClass('jb-uploaded');
	});

	$( document.body ).on( 'click', '.jb-clear-media', function(e) {
		e.preventDefault();
		var plupload_id = $(this).parents('.jb-form-field-content').attr('data-uploader');
		jb_media_uploaders[ plupload_id ].splice();
		$(this).parents('.jb-uploaded-wrapper').siblings('.jb-media-value').val('');
		$(this).parents('.jb-uploaded-wrapper').siblings('.jb-media-value-hash').val('');
		$(this).parents('.jb-uploaded-wrapper').removeClass('jb-uploaded').removeClass('jb-waiting-change');
		$(this).parents('.jb-uploaded-wrapper').siblings('.jb-uploader').removeClass('jb-uploaded');
	});

	$('.jb-select-media').each( function() {
		var $button = $(this);
		var $action = $button.data('action');
		var $filelist = $button.parents('.jb-uploader-dropzone');
		var $button_wrapper = $button.parents('.jb-select-media-button-wrapper');
		var $errorlist = $filelist.siblings( '.jb-uploader-errorlist' );
		var extensions = 'jpg,jpeg,gif,png,bmp,ico,tiff';

		var uploader_args = {
			browse_button: $button.get( 0 ), // you can pass in id...
			drop_element: $filelist.get( 0 ), // ... or DOM Element itself
			container: $button_wrapper.get( 0 ), // ... or DOM Element itself
			url: wp.ajax.settings.url + '?action=' + $action + '&nonce=' + jb_front_data.nonce,
			chunk_size: '1024kb',
			max_retries: 1,
			multipart: true,
			multi_selection: false,
			filters: {
				max_file_size: '10mb',
				mime_types: [
					{ title: wp.i18n.__( 'Image files', 'jobboardwp' ), extensions: extensions },
				],
				prevent_duplicates: true,
				max_file_count: 1
			},
			init: {
				Error: function ( up, err ) {
					$errorlist.html( '<p>' + wp.i18n.__( 'Error!', 'jobboardwp' ) + ' ' + err.message + '</p>' );
				},
				FileFiltered: function ( up, file ) {

					$errorlist.empty();

					if ( ! up.getOption( 'multi_selection' ) ) {
						$filelist.find( '.jb-uploader-file' ).each( function ( u, item ) {
							up.removeFile( item.id );
						} );
					}
				},
				FilesAdded: function ( up, files ) {
					up.start();
				},
				FilesRemoved: function ( up, files ) {
					$.each( files, function ( i, file ) {
						$( '#' + file.id ).remove();
					} );

					if ( ! $filelist.find( '.jb-uploader-file' ).length ) {
						$errorlist.empty();
					}
				},
				FileUploaded: function ( up, file, result ) {
					if ( result.status === 200 && result.response ) {

						var response = JSON.parse( result.response );

						if ( ! response ) {
							$errorlist.append( '<p>' + wp.i18n.__( 'Error! Wrong file upload server response.', 'jobboardwp' ) + '</p>' );
						} else if ( response.info && response.OK === 0 ) {
							console.error( response.info );
						} else if ( response.data ) {

							$button.parents('.jb-uploader').addClass( 'jb-uploaded' );
							$button.parents('.jb-uploader').siblings('.jb-uploaded-wrapper').addClass( 'jb-uploaded' ).removeClass('jb-waiting-change');
							$button.parents('.jb-uploader').siblings('.jb-uploaded-wrapper').find('img').attr( 'src', response.data[0].url );
							wp.hooks.doAction( 'jb_job_uploader_after_success_upload', $button, response );

							$button.parents('.jb-uploader').siblings('.jb-media-value').val( response.data[0].name_saved );
							$button.parents('.jb-uploader').siblings('.jb-media-value-hash').val( response.data[0].hash );
						}

					} else {
						// translators: %s is the error status code.
						console.error( wp.i18n.__( 'File was not loaded, Status Code %s', 'jobboardwp' ), [ result.status ] );
					}
				},
				PostInit: function ( up ) {
					$filelist.find( '.jb-uploader-file' ).remove();
				},
				UploadProgress: function ( up, file ) {
					$( '#' + file.id ).find( 'b' ).html( '<span>' + file.percent + '%</span>' );
				},
				UploadComplete: function ( up, files ) {
				}
			}
		};
		uploader_args = wp.hooks.applyFilters( 'jb_job_uploader_filters_attrs', uploader_args, $button );

		jb_media_uploader = new plupload.Uploader( uploader_args );
		jb_media_uploaders[ jb_media_uploader['id'] ] = jb_media_uploader;
		jb_media_uploader.init();

		$(this).parents('.jb-form-field-content').attr('data-uploader', jb_media_uploader['id']);
	});

	/**
	 * On option fields change.
	 */
	jQuery( document.body ).on( 'change', '.jb-forms-field', function() {
		if ( jQuery('.jb-form-row[data-conditional*=\'"' + jQuery(this).data('field_id') + '",\']').length > 0 ||
			jQuery('.jb-form-row[data-conditional*=\'' + jQuery(this).data('field_id') + '|\']').length > 0 ||
			jQuery('.jb-form-row[data-conditional*=\'|' + jQuery(this).data('field_id') + '\']').length > 0 ) {
			wp.JB.forms.conditionalFields.trigger();
		}
	});

	// First load hide unconditional fields.
	wp.JB.forms.conditionalFields.trigger();

	// Submit company details
	$( document.body ).on('submit', '.jb-form[name="jb-company-details"]', function() {
		console.log( window.location.pathname );
		if ( window.history ) {
			window.history.replaceState({}, document.title, window.location.pathname );
		}
		$(this).find('.jb-form-button').attr('disabled', true).prop('disabled', true);
	});
});
