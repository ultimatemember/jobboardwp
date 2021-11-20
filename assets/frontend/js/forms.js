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
	}
};

jQuery( document ).ready( function($) {
	wp.JB.forms.setObjects();

	/**
	 * jQuery UI - Datepicker
	 *
	 * @type {*|jQuery|HTMLElement}
	 */
	var datepicker = $('.jb-datepicker');
	if ( datepicker.length ) {
		datepicker.each( function() {
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
		$(this).parents('.jb-uploaded-wrapper').siblings('.jb-media-value').val('');
		$(this).parents('.jb-uploaded-wrapper').siblings('.jb-media-value-hash').val('');
		$(this).parents('.jb-uploaded-wrapper').removeClass('jb-uploaded').removeClass('jb-waiting-change');
		$(this).parents('.jb-uploaded-wrapper').siblings('.jb-uploader').removeClass('jb-uploaded');
	});

	$('.jb-select-media').each( function() {
		var $button = $(this);
		var $action = $button.data('action');
		var $filelist = $button.parents('.jb-uploader-dropzone');
		var $errorlist = $filelist.siblings( '.jb-uploader-errorlist' );

		var uploader = new plupload.Uploader({
			browse_button: $button.get( 0 ), // you can pass in id...
			drop_element: $filelist.get( 0 ), // ... or DOM Element itself
			url: wp.ajax.settings.url + '?action=' + $action + '&nonce=' + jb_front_data.nonce,
			chunk_size: '1024kb',
			max_retries: 1,
			multipart: true,
			multi_selection: false,
			filters: {
				max_file_size: '10mb',
				mime_types: [
					{ title: wp.i18n.__( 'Image files', 'jobboardwp' ), extensions: 'jpg,jpeg,gif,png,bmp,ico,tiff' },
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

					$button.prop( 'jb-uploader-data', $.extend( args, {
						files: files
					} ) );

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

							$button.parents('.jb-uploader').siblings('.jb-media-value').val( response.data[0].name_saved );
							$button.parents('.jb-uploader').siblings('.jb-media-value-hash').val( response.data[0].hash );
						}

					} else {
						// translators: %s is the error status code.
						console.error( wp.i18n.__( 'File was not loaded, Status Code %s', 'jobboardwp' ), [ result.status ] );
					}
				},
				PostInit: function ( up ) {
					$button.prop( 'jb-uploader', up ).next( 'div' ).hide();
					$filelist.find( '.jb-uploader-file' ).remove();
				},
				UploadProgress: function ( up, file ) {
					$( '#' + file.id ).find( 'b' ).html( '<span>' + file.percent + '%</span>' );
				},
				UploadComplete: function ( up, files ) {
				}
			}
		});

		uploader.init();
	});
});
