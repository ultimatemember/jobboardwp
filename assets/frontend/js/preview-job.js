jQuery( document ).ready( function($) {
    $( document.body ).on( 'click', '.jb-form-button[name="job-preview"], .jb-form-button[name="job-draft"], .jb-form-button[name="job-publish"]', function(e) {
        $(this).parents('.jb-form').find('input[name="jb-job-submission-step"]').val( $(this).data('action') );
    });
});