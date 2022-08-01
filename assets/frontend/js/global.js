jQuery.ajaxSetup({
	beforeSend: function( jqXHR, settings ) {
		if ( settings.processData ) {
			if ( settings.data !== '' ) {
				settings.data += '&jb_current_locale=' + jb_front_data.locale;
			} else {
				settings.data = 'jb_current_locale=' + jb_front_data.locale;
			}
		} else {
			settings.data = jQuery.extend(
				settings.data,
				{
					jb_current_locale: jb_front_data.locale
				}
			);
		}

		return true;
	}
});

jQuery( document ).ready( function($) {

	if ( typeof( $.fn.select2 ) === "function" ) {
		$(".jb-s1").select2({
			allowClear: true,
			placeholder: jQuery(this).data('placeholder')
		});

		$(".jb-s2").select2({
			allowClear: false,
			placeholder: jQuery(this).data('placeholder')
		});

		$(".jb-s3").select2({
			tags: true,
			allowClear: true,
			placeholder: jQuery(this).data('placeholder')
		});
	}


	$( window ).on( 'resize', function() {
		jb_responsive();
	});
	
});

jQuery( window ).on( 'load', function() {
	jb_responsive();
	jb_init_helptips();
});

//important order by ASC
var jb_resolutions = {
	xs: 320,
	s:  576,
	m:  768,
	l:  992,
	xl: 1024
};


/**
 *
 * @param number
 * @returns {*}
 */
function jb_get_size( number ) {
	for ( var key in jb_resolutions ) {
		if ( jb_resolutions.hasOwnProperty( key ) && jb_resolutions[ key ] === number ) {
			return key;
		}
	}

	return false;
}


/**
 *
 */
function jb_responsive() {

	var $resolutions = Object.values( jb_resolutions );
	$resolutions.sort( function(a, b){ return b-a; });

	jQuery('.jb').each( function() {
		var obj = jQuery(this);
		var element_width = obj.outerWidth();

		jQuery.each( $resolutions, function( index ) {
			var $class = jb_get_size( $resolutions[ index ] );
			obj.removeClass('jb-ui-' + $class );
		});

		jQuery.each( $resolutions, function( index ) {
			var $class = jb_get_size( $resolutions[ index ] );

			if ( element_width >= $resolutions[ index ] ) {
				obj.addClass('jb-ui-' + $class );
				return false;
			} else if ( $class === 'xs' && element_width <= $resolutions[ index ] ) {
				obj.addClass('jb-ui-' + $class );
				return false;
			}
		});
	});
}
