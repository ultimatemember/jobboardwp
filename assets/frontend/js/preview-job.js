jQuery( document ).ready( function($) {
	$( document.body ).on( 'click', '.jb-job-publish-submit, .jb-job-draft-submit', function(e) {
		e.preventDefault();
		$(this).siblings('input[name="jb-job-submission-step"]').val( $(this).attr('name') );
		$(this).parents('form').submit();
	});
});