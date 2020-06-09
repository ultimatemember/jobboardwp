jQuery( document ).ready( function($) {

	$( document.body ).on( 'click', '.jb-post-popup-action-fullsize', function() {
		var popup = $(this).parents( '.jb-post-popup-wrapper' );
		if ( popup.hasClass( 'jb-fullsize' ) ) {
			popup.removeClass( 'jb-fullsize' );
		} else {
			popup.addClass( 'jb-fullsize' );
		}
	});


	$(window).on( 'resize', function() {
		jb_resize_popup();
	}).on( 'load', jb_resize_popup() );

});

function jb_resize_popup() {
	var obj = [
		'reply',
		'topic',
		'forum'
	];

	jQuery.each( obj, function( item ) {
		if ( ! jQuery('#jb-' + obj[ item ] + '-popup-editor').length ) {
			return;
		}
		var height = jQuery('#jb-' + obj[ item ] + '-popup-editor').outerHeight();

		height = height - jQuery('#jb-' + obj[ item ] + '-popup-editor').find( '.mce-statusbar' ).outerHeight() - jQuery('#jb-' + obj[ item ] + '-popup-editor').find( '.mce-top-part' ).outerHeight() - 1;
		jQuery('#jb' + obj[ item ] + 'content_ifr').css( 'height', height + 'px' );
	});
}


function jb_extractLast( term ) {
	return term.split(" ").pop();
}

function jb_extract_string( term ) {
	return term.split(" ");
}