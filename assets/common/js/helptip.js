function jb_init_helptips() {
	var helptips = jQuery( '.jb-helptip' );
	if ( helptips.length > 0 ) {
		helptips.tooltip({
			tooltipClass: 'jb-helptip',
			content: function () {
				return jQuery( this ).attr( 'title' );
			}
		});
	}
}