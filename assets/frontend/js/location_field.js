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
				if ( $selected_autocomplete.siblings('.jb-location-autocomplete-data').length > 1 ) {
                   // jobs list filter
					$selected_autocomplete.siblings('.jb-location-autocomplete-data').val( '' );
				} else {
					// post a job form
                    $selected_autocomplete.siblings('.jb-location-autocomplete-data').val( JSON.stringify( place ) );
				}

				if ( typeof place.address_components !== 'undefined' ) {
					jQuery.each( place.address_components, function( i ) {
						if ( typeof place.address_components[ i ].types !== 'undefined' ) {

							switch ( place.address_components[ i ].types[0] ) {
								case 'sublocality_level_1':
								case 'locality':
								case 'postal_town':
									$selected_autocomplete.siblings('.jb-location-autocomplete-data.jb-location-city').val( place.address_components[ i ].long_name );
									break;
								case 'administrative_area_level_1':
								case 'administrative_area_level_2':
									$selected_autocomplete.siblings('.jb-location-autocomplete-data.jb-location-state-short').val( place.address_components[ i ].short_name );
									$selected_autocomplete.siblings('.jb-location-autocomplete-data.jb-location-state-long').val( place.address_components[ i ].long_name );
									break;
								case 'country':
									$selected_autocomplete.siblings('.jb-location-autocomplete-data.jb-location-country-short').val( place.address_components[ i ].short_name );
									$selected_autocomplete.siblings('.jb-location-autocomplete-data.jb-location-country-long').val( place.address_components[ i ].long_name );
									break;
							}
						}
					});
				}

			}
		});

	}).on('click', function() {
		$selected_autocomplete = jQuery(this);
	});

	wp.hooks.doAction( 'jb_google_maps_api_callback' );
}

var jb_location_script = document.createElement( 'script' );
jb_location_script.src = '//maps.googleapis.com/maps/api/js?key=' + jb_location_var.api_key + '&libraries=places&callback=JBLocationAutocomplete';
if ( jb_location_var.region ) {
	jb_location_script.src += '&language=' + jb_location_var.region;
}
document.body.appendChild( jb_location_script );


// extends AJAX request arguments
wp.hooks.addFilter( 'jb_jobs_request', 'jb_autocomplete_location', function( request, jobs_list ) {
	if ( jobs_list.find('.jb-location-autocomplete-data').length ) {
		request['location-city'] = jobs_list.find( '.jb-location-autocomplete-data.jb-location-city' ).val();
		request['location-state-short'] = jobs_list.find( '.jb-location-autocomplete-data.jb-location-state-short' ).val();
		request['location-state-long'] = jobs_list.find( '.jb-location-autocomplete-data.jb-location-state-long' ).val();
		request['location-country-short'] = jobs_list.find( '.jb-location-autocomplete-data.jb-location-country-short' ).val();
		request['location-country-long'] = jobs_list.find( '.jb-location-autocomplete-data.jb-location-country-long' ).val();
		request['location'] = '';
	}

	return request;
}, 10 );

// add location data to URL on search click
wp.hooks.addAction( 'jb_jobs_list_do_search', 'jb_autocomplete_location', function( jobs_list ) {
	if ( jobs_list.find('.jb-location-autocomplete-data.jb-location-city').length ) {
		wp.JB.jobs_list.url.set( jobs_list, 'jb-location-search-city', jobs_list.find( '.jb-location-autocomplete-data.jb-location-city' ).val() );
	}

	if ( jobs_list.find('.jb-location-autocomplete-data.jb-location-state-short').length ) {
		wp.JB.jobs_list.url.set( jobs_list, 'jb-location-search-state-short', jobs_list.find( '.jb-location-autocomplete-data.jb-location-state-short' ).val() );
	}

	if ( jobs_list.find('.jb-location-autocomplete-data.jb-location-state-long').length ) {
		wp.JB.jobs_list.url.set( jobs_list, 'jb-location-search-state-long', jobs_list.find( '.jb-location-autocomplete-data.jb-location-state-long' ).val() );
	}

	if ( jobs_list.find('.jb-location-autocomplete-data.jb-location-country-short').length ) {
		wp.JB.jobs_list.url.set( jobs_list, 'jb-location-search-country-short', jobs_list.find( '.jb-location-autocomplete-data.jb-location-country-short' ).val() );
	}

	if ( jobs_list.find('.jb-location-autocomplete-data.jb-location-country-long').length ) {
		wp.JB.jobs_list.url.set( jobs_list, 'jb-location-search-country-long', jobs_list.find( '.jb-location-autocomplete-data.jb-location-country-long' ).val() );
	}
}, 10 );
