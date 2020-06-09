var jb_embed_selector = "iframe[src*='//player.vimeo.com'], iframe[src*='//www.youtube.com'], object, embed";
var jb_embed_containers = [];

jQuery( document ).ready( function($) {

	$('.jb-tip-n').tipsy({gravity: 'n', opacity: 1, live: 'a.live', offset: 3 });
	$('.jb-tip-w').tipsy({gravity: 'w', opacity: 1, live: 'a.live', offset: 3 });
	$('.jb-tip-e').tipsy({gravity: 'e', opacity: 1, live: 'a.live', offset: 3 });
	$('.jb-tip-s').tipsy({gravity: 's', opacity: 1, live: 'a.live', offset: 3 });


	if( typeof( $.fn.select2 ) === "function" ){
		$(".jb-s1").select2({
			allowClear: true
		});

		$(".jb-s2").select2({
			allowClear: false,
        	placeholder: 'Please select...'
		});
	}


	$(document.body).on( 'click', '#jb-popup-overlay', function() {
		$('.jb-popup').hide();
		$(this).hide();
	});

	$(document.body).on( 'click', '.jb-popup-close', function() {
		$('#jb-popup-overlay').hide();
		$(this).parents('.jb-popup').hide();
		$(this).tipsy('hide');
	});

	$( window ).on( 'resize', function() {
		jb_popup_resize( false );
		jb_responsive();
		jb_embed_resize();
	});

	$( window ).on( 'load',function() {
		jb_responsive();
		jb_embed_resize_async();
		jb_init_helptips();
	});
});


/**
 *
 */
function jb_embed_resize() {
	jQuery.each( jb_embed_containers, function( i ) {
		var containers = jQuery( jb_embed_containers[ i ] );
		if ( ! containers.is(':visible') ) {
			return;
		}

		containers.each( function() {
			var container = jQuery(this);
			var newWidth = container.width();

			var jb_embed_elements = container.find( jb_embed_selector );
			jb_embed_elements.each( function() {
				var $el = jQuery(this);
				$el.width( newWidth ).height( newWidth * $el.attr( 'data-jb-aspectratio' ) );
			});
		});
	});
}


/**
 *
 */
function jb_set_embed_size() {
	jQuery.each( jb_embed_containers, function( i ) {
		var container = jQuery( jb_embed_containers[ i ] );
		if ( ! container.is(':visible') ) {
			return;
		}

		var jb_embed_elements = container.find( jb_embed_selector );
		jb_embed_elements.each( function() {
			// jQuery .data does not work on object/embed elements
			if ( ! this.hasAttribute( 'data-jb-aspectratio' ) ) {
				jQuery(this).attr( 'data-jb-aspectratio', this.height / this.width ).removeAttr( 'height' ).removeAttr( 'width' );
			}
		});
	});
}


/**
 *
 */
function jb_embed_resize_async() {
	jb_set_embed_size();
	jb_embed_resize();
}


/**
 *
 * @param animation
 */
function jb_popup_resize( animation ) {

	var w = window.innerWidth
		|| document.documentElement.clientWidth
		|| document.body.clientWidth;

	var h = window.innerHeight
		|| document.documentElement.clientHeight
		|| document.body.clientHeight;

	var popup = jQuery('.jb-popup:visible');

	if ( popup.length ) {
		if ( w - 10 < popup.outerWidth() ) {
			if ( animation ) {
				popup.animate({
					'left': '5px',
					'top': ( h - popup.height() ) / 2 + 'px',
					'width' : 'calc( 100% - 10px )'
				}, 300);
			} else {
				popup.css({
					'left': '5px',
					'top': ( h - popup.height() ) / 2 + 'px',
					'width' : 'calc( 100% - 10px )'
				});
			}
		} else {
			if ( animation ) {
				popup.animate({
					'left': ( w - popup.outerWidth() ) / 2 + 'px',
					'top': ( h - popup.height() ) / 2 + 'px'
				}, 300);
			} else {
				popup.css({
					'left': ( w - popup.outerWidth() ) / 2 + 'px',
					'top': ( h - popup.height() ) / 2 + 'px'
				});
			}
		}
	}

}


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


/**
 * Init tags suggestions
 * @param obj
 */
function jb_init_tags_suggest( obj ) {
	obj.suggest( wp.ajax.settings.url + "?action=ajax-tag-search&tax=jb-job-type", {
		multiple: true,
		multipleSep: ",",
		onSelect: function() {},
		resultsClass: 'jb-ac-results',
		selectClass: 'jb-ac-over',
		matchClass: 'jb-ac-match'
	});
}


/**
 * Init tags suggestions
 * @param obj
 */
function jb_init_categories_suggest( obj ) {
	if ( ! obj.length ) {
		return;
	}
	obj.suggest( wp.ajax.settings.url + "?action=ajax-tag-search&tax=jb-job-category", {
		multiple: true,
		multipleSep: ",",
		onSelect: function() {},
		resultsClass: 'jb-ac-results',
		selectClass: 'jb-ac-over',
		matchClass: 'jb-ac-match'
	});
}


/**
 * Rebuild dropdown actions for post row in templates
 *
 * @param data
 * @param obj
 */
function jb_rebuild_dropdown( data, obj ) {
	var dropdown_html = '';
	jQuery.each( data.dropdown_actions, function( key ) {
		dropdown_html += '<li><a href="javascript:void(0);" class="' + key + '">' + data.dropdown_actions[ key ] + '</a></li>';
	});
	obj.parents('.jb-dropdown ul').html( dropdown_html );
}


var jb_ajax_busy = {};


function jb_set_busy( key, value ) {
	jb_ajax_busy[ key ] = value;
}


function jb_is_busy( key ) {
	return !!jb_ajax_busy[ key ];
}