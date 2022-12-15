function JBLocationSelectOnEnter( input ) {
	// store the original event binding function
	var _addEventListener = ( input.addEventListener ) ? input.addEventListener : input.attachEvent;

	function addEventListenerWrapper( type, listener ) {
		// Simulate a 'down arrow' keypress on hitting 'return' when no pac suggestion is selected,
		// and then trigger the original listener.

		if ( type === 'keydown' ) {
			var orig_listener = listener;

			listener = function( event ) {
				var suggestion_selected = jQuery(".pac-item-selected").length > 0;

				if ( event.which === 13 && ! suggestion_selected ) {
					var simulated_downarrow = jQuery.Event( 'keydown', {
						keyCode: 40,
						which: 40
					});

					orig_listener.apply( input, [ simulated_downarrow ] );

					$selected_autocomplete = jQuery( input );
				}

				orig_listener.apply( input, [ event ] );
			};
		}

		_addEventListener.apply( input, [ type, listener ] );
	}

	if ( input.addEventListener ) {
		input.addEventListener = addEventListenerWrapper;
	} else if ( input.attachEvent ) {
		input.attachEvent = addEventListenerWrapper;
	}
}

function JBLocationAutocomplete() {
	var $selected_autocomplete;

	//remove marker when location field is empty
	jQuery( document.body ).on( 'keyup', '.jb-location-autocomplete', function() {
		if ( jQuery(this).val() === '' ) {
			jQuery(this).siblings('.jb-location-autocomplete-data').val( '' );
		}
	});

	jQuery( document.body ).on( 'change', '.jb-location-autocomplete', function() {
		if ( jQuery(this).val() === '' ) {
			jQuery(this).siblings('.jb-location-autocomplete-data').val( '' );
		}
	});

	jQuery('.jb-location-autocomplete').each( function() {

		JBLocationSelectOnEnter( jQuery(this).get(0) );

		var autocomplete = new google.maps.places.Autocomplete( jQuery(this).get(0), {
			types: ['(regions)']
		});

		autocomplete.addListener( 'place_changed', function(e) {
			var place = this.getPlace();

			if ( typeof place == 'undefined' || typeof place.geometry == 'undefined' || typeof place.geometry.location == 'undefined' ) {
				if ( typeof $selected_autocomplete !== 'undefined' ) {
					$selected_autocomplete.siblings('.jb-location-autocomplete-data').val( '' );
				}
				return;
			}

			if ( typeof $selected_autocomplete !== 'undefined' ) {
				$selected_autocomplete.siblings('.jb-location-autocomplete-data').val( JSON.stringify( place ) );
			}
		});

	}).on('click', function() {
		$selected_autocomplete = jQuery(this);
	});
}

var jb_location_script = document.createElement( 'script' );
jb_location_script.src = '//maps.googleapis.com/maps/api/js?key=' + jb_location_var.api_key + '&libraries=places&callback=JBLocationAutocomplete';
if ( jb_location_var.region ) {
    jb_location_script.src += '&language=' + jb_location_var.region;
}
document.body.appendChild( jb_location_script );
