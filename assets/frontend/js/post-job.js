jQuery( document ).ready( function($) {
	$( document.body ).on( 'click', '.jb-form-button[name="job-preview"], .jb-form-button[name="job-draft"], .jb-form-button[name="job-publish"]', function(e) {
		$(this).parents('.jb-form').find('input[name="jb-job-submission-step"]').val( $(this).data('action') );
	});

	$( document.body ).on('submit', '.jb-form', function() {
		$(this).find('.jb-form-button').attr('disabled', true).prop('disabled', true);
	});

	$( document.body ).on( 'click', '#jb-show-login-form', function(e) {
		e.preventDefault();
		var form = $('#jb-job-submission');

		$('#jb-login-form-wrapper').show();
		$('#jb-sign-in-notice').hide();
		$('#jb-sign-up-notice').show();
		form.find('.jb-form-section-fields-wrapper[data-key="my-details"]').hide();

		if ( form.data('account-required') === 1 ) {
			form.find( '.jb-form-buttons-section input' ).prop('disabled', true).attr('disabled', true);
		}
	});

	$( document.body ).on( 'click', '#jb-hide-login-form', function(e) {
		e.preventDefault();
		var form = $('#jb-job-submission');

		$('#jb-login-form-wrapper').hide();
		$('#jb-sign-in-notice').show();
		$('#jb-sign-up-notice').hide();
		form.find('.jb-form-section-fields-wrapper[data-key="my-details"]').show();

		if ( form.data('account-required') === 1 ) {
			form.find( '.jb-form-buttons-section input' ).prop('disabled', false).attr('disabled', false);
		}
	});
});
