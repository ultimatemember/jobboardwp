jQuery( document ).ready( function($) {
	$( document.body ).on( 'click', '.jb-job-apply', function(e) {
		$(this).siblings('.jb-job-apply-description').show();
		$(this).hide();
	});

	$( document.body ).on( 'click', '.jb-job-apply-hide', function(e) {
		$(this).parents('.jb-job-apply-description').siblings('.jb-job-apply').show();
		$(this).parents('.jb-job-apply-description').hide();
	});
});