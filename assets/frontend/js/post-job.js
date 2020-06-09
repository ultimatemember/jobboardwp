jQuery( document ).ready( function($) {

	var $button = jQuery( '#jb_company_logo_plupload' );
	var $filelist = jQuery( '#jb_company_logo_filelist' );
	var $errorlist = jQuery( '#jb_company_logo_errorlist' );

	var uploader = new plupload.Uploader({
		browse_button: $button.get( 0 ), // you can pass in id...
		drop_element: $filelist.get( 0 ), // ... or DOM Element itself
		url: wp.ajax.settings.url + '?action=jb-upload-company-logo&nonce=' + jb_front_data.nonce,
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
				$errorlist.html( '<p>Error! ' + err.message + '</p>' );
			},
			FileFiltered: function ( up, file ) {

				$errorlist.empty();

				if ( !up.getOption( 'multi_selection' ) ) {
					$filelist.find( '.um-fs-uploader-file' ).each( function ( u, item ) {
						up.removeFile( item.id );
					} );
				}
			},
			FilesAdded: function ( up, files ) {
				up.start();
			},
			FilesRemoved: function ( up, files ) {
				jQuery.each( files, function ( i, file ) {
					jQuery( '#' + file.id ).remove();
				} );

				if ( !$filelist.find( '.um-fs-uploader-file' ).length ) {
					$errorlist.empty();
				}

				$button.prop( 'um-uploader-data', jQuery.extend( args, {
					files: files
				} ) ).trigger( 'um_fs_FilesRemoved' );

			},
			FileUploaded: function ( up, file, result ) {
				if ( result.status === 200 && result.response ) {

					var response = JSON.parse( result.response );

					if ( ! response ) {
						$errorlist.append( '<p>Error! Wrong file upload server response.</p>' );
					} else if ( response.info && response.OK === 0 ) {
						console.error( response.info );
					} else if ( response.data ) {

						$button.parents('.jb-uploader').addClass( 'jb-company-logo-uploaded' );
						$button.parents('.jb-uploader').siblings('.jb-company-logo-wrapper').addClass( 'jb-company-logo-uploaded' );
						$button.parents('.jb-uploader').siblings('.jb-company-logo-wrapper').find('img').attr( 'src', response.data[0].url );

						$('#jb_company_logo').val( response.data[0].name_saved );
						$('#jb_company_logo_hash').val( response.data[0].hash );
					}

				} else {
					console.error( 'File was not loaded, Status Code %s', [ result.status ] );
				}
			},
			PostInit: function ( up ) {
				$button.prop( 'um-uploader', up ).next( 'div' ).hide();
				$filelist.find( '.um-fs-uploader-file' ).remove();
			},
			UploadProgress: function ( up, file ) {
				jQuery( '#' + file.id ).find( 'b' ).html( '<span>' + file.percent + '%</span>' );
			},
			UploadComplete: function ( up, files ) {
				// $button.prop( 'um-uploader-data', jQuery.extend( args, {
				// 	files: files
				// } ) ).trigger( 'um_fs_UploadComplete' );
			}
		}
	});

	uploader.init();


	$( document.body ).on( 'click', '.jb-change-logo', function() {
		$(this).parents('.jb-company-logo-wrapper').siblings('.jb-uploader').removeClass('jb-company-logo-uploaded');
	});

	$( document.body ).on( 'click', '.jb-clear-logo', function() {
		$('#jb_company_logo').val('');
		$('#jb_company_logo_hash').val('');
		$(this).parents('.jb-company-logo-wrapper').removeClass('jb-company-logo-uploaded');
		$(this).parents('.jb-company-logo-wrapper').siblings('.jb-uploader').removeClass('jb-company-logo-uploaded');
	});


	$( document.body ).on( 'click', '.jb-job-preview-submit, .jb-job-draft-submit', function(e) {
		e.preventDefault();
		$(this).siblings('input[name="jb-job-submission-step"]').val( $(this).attr('name') );
		$(this).parents('form').submit();
	});


	$( document.body ).on( 'click', 'input[name="job_location_type"]', function() {
		var field_type = $(this).data('location_field');
		var form_field = $(this).parents('.jb-form-field-content');
		form_field.find('.jb-locations-fields').hide();
		form_field.find('.jb-locations-fields').find('input').attr('disabled', 'disabled').prop('disabled', true);
		form_field.find('.jb-' + field_type + '-location').show();
		form_field.find('.jb-' + field_type + '-location').find('input').removeAttr('disabled').prop('disabled', false);
	});



	$( 'input[name="job_location_type"]:checked' ).trigger('click');
});