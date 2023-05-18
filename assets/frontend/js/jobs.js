if ( typeof ( wp.JB ) !== 'object' ) {
	wp.JB = {};
}

if ( typeof ( wp.JB.jobs_list ) !== 'object' ) {
	wp.JB.jobs_list = {};
}

// jQuery.fn.extend({
// 	jb_is_busy: function() {
// 		if ( ! jQuery(this).hasClass('jb-jobs') ) {
// 			throw new Error( "It's not a jobs list" );
// 		} else {
// 			return jQuery(this).hasClass('jb-busy');
// 		}
// 	}
// });

wp.JB.jobs_list = {
	wrapper_selector: '.jb-jobs',
	first_load: true,
	is_search: false,
	objects: {
		wrapper: jQuery( '.jb-jobs' ),
	},
	get_wrapper_index: function( wrapper ) {
		return wrapper.data('wrapper-index');
	},
	is_busy: function( jobs_list ) {
		return jobs_list.hasClass('jb-busy');
	},
	preloader: {
		show: function( jobs_list ) {
			jobs_list.addClass('jb-busy').find('.jb-overlay').show();
		},
		hide: function( jobs_list ) {
			jobs_list.removeClass('jb-busy').find('.jb-overlay').hide();
		}
	},
	url: {
		set: function( jobs_list, key, value ) {
			var data = wp.JB.jobs_list.url.get();

			var list_index = wp.JB.jobs_list.get_wrapper_index( jobs_list );

			var new_data = {};

			if ( jQuery.isArray( value ) ) {
				jQuery.each( value, function( i ) {
					value[ i ] = encodeURIComponent( value[ i ] );
				});
				value = value.join( '||' );
			} else if ( ! jQuery.isNumeric( value ) ) {
				value = value.split( '||' );
				jQuery.each( value, function( i ) {
					value[ i ] = encodeURIComponent( value[ i ] );
				});
				value = value.join( '||' );
			}

			if ( value !== '' ) {
				new_data[ key + '[' + list_index + ']' ] = value;
			}

			jQuery.each( data, function( data_key ) {
				if ( key + '[' + list_index + ']' === data_key ) {
					if ( value !== '' ) {
						new_data[ data_key ] = value;
					}
				} else {
					new_data[ data_key ] = data[ data_key ];
				}
			});

			var query_strings = [];
			jQuery.each( new_data, function( data_key ) {
				query_strings.push( data_key + '=' + new_data[ data_key ] );
			});

			query_strings = wp.hooks.applyFilters( 'jb_job_dashboard_url_attrs', query_strings );

			var query_string = '?' + query_strings.join( '&' );
			if ( query_string === '?' ) {
				query_string = '';
			}

			window.history.pushState( 'string', 'JB Jobs List', window.location.origin + window.location.pathname + query_string );
		},
		get: function( jobs_list, search_key ) {
			var data = {};

			var url_data = wp.JB.jobs_list.url.parse();

			jQuery.each( url_data, function( key ) {
				if ( url_data[ key ] !== '' ) {
					data[ key ] = url_data[ key ];
				}
			});

			if ( ! search_key ) {
				return data;
			} else {
				if ( typeof data[ search_key ] !== 'undefined' ) {
					try {
						data[ search_key ] = decodeURIComponent( data[ search_key ] );
					} catch(e) {
						console.error(e);
					}
				}

				return data[ search_key ];
			}
		},
		parse: function() {
			var data = {};

			var query = window.location.search.substring( 1 );

			if ( query !== '' ) {
				var attrs = query.split( '&' );
				jQuery.each( attrs, function( i ) {
					var attr = attrs[ i ].split( '=' );
					data[ attr[0] ] = attr[1];
				});
			}

			return data;
		},
		get_page: function( jobs_list ) {
			var page = jobs_list.data( 'page' );
			if ( ! page || typeof page == 'undefined' ) {
				page = 1;
			}
			return page;
		},
		get_per_page: function( jobs_list ) {
			var per_page = jobs_list.data( 'per-page' );
			if ( ! per_page || typeof per_page == 'undefined' ) {
				per_page = null;
			}
			return per_page;
		},
		get_employer: function( jobs_list ) {
			var employer_id = jobs_list.data( 'employer' );
			if ( ! employer_id || typeof employer_id == 'undefined' ) {
				employer_id = '';
			}
			return employer_id;
		},
		get_logo: function( jobs_list ) {
			var hide_logo = jobs_list.data( 'no-logo' );
			if ( typeof hide_logo == 'undefined' ) {
				hide_logo = null;
			}
			return hide_logo;
		},
		get_hide_job_types: function( jobs_list ) {
			var hide_job_types = jobs_list.data( 'hide-job-types' );
			if ( typeof hide_job_types == 'undefined' ) {
				hide_job_types = null;
			}
			return hide_job_types;
		},
		get_search: function( jobs_list ) {
			if ( jobs_list.find('.jb-search-line').length ) {
				return jobs_list.find( '.jb-search-line' ).val();
			} else {
				return '';
			}
		},
		get_location: function( jobs_list ) {
			if ( jobs_list.find('.jb-search-location').length ) {
				return jobs_list.find( '.jb-search-location' ).val();
			} else {
				return '';
			}
		},
		get_type: function( jobs_list ) {
			if ( jobs_list.find('.jb-only-remote').length ) {
				return jobs_list.find( '.jb-only-remote' ).is(':checked') ? 1 : 0;
			} else {
				return '';
			}
		},
		get_type_tag: function( jobs_list ) {
			if ( jobs_list.find('.jb-job-type-filter').length ) {
				return jobs_list.find( '.jb-job-type-filter' ).val();
			} else {
				return jobs_list.data('type');
			}
		},
		get_category: function( jobs_list ) {
			if ( jobs_list.find('.jb-job-category-filter').length ) {
				return jobs_list.find( '.jb-job-category-filter' ).val();
			} else {
				return jobs_list.data('category');
			}
		},
		get_hide_expired: function( jobs_list ) {
			var hide_expired = jobs_list.data( 'hide-expired' );
			if ( typeof hide_expired == 'undefined' ) {
				hide_expired = null;
			}
			return hide_expired;
		},
		get_hide_filled: function( jobs_list ) {
			var hide_filled = jobs_list.data( 'hide-filled' );
			if ( typeof hide_filled == 'undefined' ) {
				hide_filled = null;
			}
			return hide_filled;
		},
		get_filled_only: function( jobs_list ) {
			var filled_only = jobs_list.data( 'filled-only' );
			if ( typeof filled_only == 'undefined' ) {
				filled_only = null;
			}
			return filled_only;
		},
		get_orderby: function( jobs_list ) {
			var orderby = jobs_list.data( 'orderby' );
			if ( typeof orderby == 'undefined' ) {
				orderby = 'date';
			}
			return orderby;
		},
		get_order: function( jobs_list ) {
			var order = jobs_list.data( 'order' );
			if ( typeof order == 'undefined' ) {
				order = 'DESC';
			}
			return order;
		},
		get_salary:  function( jobs_list ) {
			let salary;
			if ( jobs_list.find('.jb-double-range').length && jobs_list.find( '.jb-only-salary' ).is(':checked') ) {
				let min = jobs_list.find('.jb-double-range').data( 'min' );
				let max = jobs_list.find('.jb-double-range').data( 'max' );
				salary = min + '-' + max;
			} else {
				salary = jobs_list.data('salary');
			}

			return salary;
		}
	},
	ajax: function( jobs_list, append ) {
		var request = {
			page: wp.JB.jobs_list.url.get_page( jobs_list ),
			per_page: wp.JB.jobs_list.url.get_per_page( jobs_list ),
			search: wp.JB.jobs_list.url.get_search( jobs_list ),
			location: wp.JB.jobs_list.url.get_location( jobs_list ),
			remote_only: wp.JB.jobs_list.url.get_type( jobs_list ),
			type: wp.JB.jobs_list.url.get_type_tag( jobs_list ),
			category: wp.JB.jobs_list.url.get_category( jobs_list ),
			employer: wp.JB.jobs_list.url.get_employer( jobs_list ),
			no_logo: wp.JB.jobs_list.url.get_logo( jobs_list ),
			hide_job_types: wp.JB.jobs_list.url.get_hide_job_types( jobs_list ),
			hide_expired: wp.JB.jobs_list.url.get_hide_expired( jobs_list ),
			hide_filled: wp.JB.jobs_list.url.get_hide_filled( jobs_list ),
			filled_only: wp.JB.jobs_list.url.get_filled_only( jobs_list ),
			orderby: wp.JB.jobs_list.url.get_orderby( jobs_list ),
			order: wp.JB.jobs_list.url.get_order( jobs_list ),
			salary: wp.JB.jobs_list.url.get_salary( jobs_list ),
			nonce: jb_front_data.nonce
		};

		wp.JB.jobs_list.is_search = !! ( request.search || request.location || request.remote_only );

		if ( wp.JB.jobs_list.first_load ) {
			if ( request.page > 1 ) {
				request.get_previous = true;
			}
			wp.JB.jobs_list.first_load = false;
		}

		request = wp.hooks.applyFilters( 'jb_jobs_request', request, jobs_list );

		wp.JB.jobs_list.preloader.show( jobs_list );

		wp.ajax.send( 'jb-get-jobs', {
			data:  request,
			success: function( answer ) {
				var template = wp.template( 'jb-jobs-list-line' );

				if ( append ) {
					jobs_list.find('.jb-jobs-wrapper').append( template( answer ) );
				} else {
					jobs_list.find('.jb-jobs-wrapper').html( template( answer ) );
				}

				jobs_list.data( 'total_pages', answer.pagination.total_pages );

				if ( answer.pagination.total_pages > 0 ) {
					jobs_list.find('.jb-jobs-wrapper').removeClass('jb-no-jobs');

					if ( answer.pagination.total_pages == answer.pagination.current_page ) {
						jobs_list.find( '.jb-load-more-jobs' ).hide();
					} else {
						jobs_list.find( '.jb-load-more-jobs' ).show();
					}
				} else {

					if ( ! append ) {
						if ( wp.JB.jobs_list.is_search ) {
							jobs_list.find('.jb-jobs-wrapper').html( jobs_list.data('no-jobs-search') );
						} else {
							jobs_list.find('.jb-jobs-wrapper').html( jobs_list.data('no-jobs') );
						}
					}

					jobs_list.find('.jb-jobs-wrapper').addClass('jb-no-jobs');

					jobs_list.find( '.jb-load-more-jobs' ).hide();
				}

				wp.hooks.doAction( 'jb_jobs_list_loaded', answer );

				jobs_list.find( '.jb-do-search' ).removeClass('disabled');

				wp.JB.jobs_list.preloader.hide( jobs_list );
			},
			error: function( data ) {
				console.log( data );
				wp.JB.jobs_list.preloader.hide( jobs_list );
				jobs_list.find( '.jb-do-search' ).removeClass('disabled');
			}
		});
	},
	filters: {
		slider: {
			getValues: function() {
				var parent = jQuery(this).parent();

				var symbol = parent.data('symbol');
				var slides = parent.find('input');
				var slide1 = parseFloat( slides[0].value );
				var slide2 = parseFloat( slides[1].value );
				if ( slide1 > slide2 ) {
					var tmp = slide2;
					slide2 = slide1;
					slide1 = tmp;
				}
				var displayElement = jQuery(this).parents('.jb-salary-filter').find('.jb-double-range-values');

				let templateStr = parent.data('format');

				console.log( displayElement );
				console.log( templateStr );
				console.log( slide1 );
				console.log( slide2 );

				templateStr = templateStr.replace( /\$\{salary\}/gi, slide1 + ' - ' + slide2 );
				templateStr = templateStr.replace( /\$\{symbol\}/gi, symbol );

				displayElement.html( templateStr );
				parent.data('min', slide1);
				parent.data('max', slide2);
			}
		}
	}
};


jQuery( document ).ready( function($) {
	if ( wp.JB.jobs_list.objects.wrapper.length ) {
		wp.JB.jobs_list.objects.wrapper.each( function () {
			let jobs_list = $(this);

			// Initialize Sliders
			let sliderSection = jobs_list.find( '.jb-double-range' );
			if ( sliderSection.length > 0 ) {
				sliderSection.find('input[type="range"]').each( function () {
					let slider = $(this)[0];
					slider.oninput = wp.JB.jobs_list.filters.slider.getValues;
				});
			}

			// Run first request
			wp.JB.jobs_list.ajax( jobs_list );
		});
	}

	$( document.body ).on( 'click', '.jb-do-search', function() {
		if ( $(this).hasClass('disabled') ) {
			return;
		}

		var jobs_list = $(this).parents( '.jb-jobs' );

		if ( wp.JB.jobs_list.is_busy( jobs_list ) ) {
			return;
		}

		wp.JB.jobs_list.preloader.show( jobs_list );

		jobs_list.data( 'page', 1 );
		wp.JB.jobs_list.url.set( jobs_list, 'jb-page', '' );

		var search = wp.JB.jobs_list.url.get_search( jobs_list );
		wp.JB.jobs_list.url.set( jobs_list, 'jb-search', search );

		var location = wp.JB.jobs_list.url.get_location( jobs_list );
		wp.JB.jobs_list.url.set( jobs_list, 'jb-location-search', location );

		wp.hooks.doAction( 'jb_jobs_list_do_search', jobs_list );

		$(this).addClass('disabled');

		wp.JB.jobs_list.ajax( jobs_list );
	});


	//make search on Enter click
	$( document.body ).on( 'keypress', '.jb-search-line, .jb-search-location', function(e) {
		if ( e.which === 13 ) {

			var jobs_list = $(this).parents( '.jb-jobs' );

			var button = jobs_list.find('.jb-do-search');

			if ( button.hasClass('disabled') ) {
				return;
			}
			if ( wp.JB.jobs_list.is_busy( jobs_list ) ) {
				return;
			}

			wp.JB.jobs_list.preloader.show( jobs_list );

			jobs_list.data( 'page', 1 );
			wp.JB.jobs_list.url.set( jobs_list, 'jb-page', '' );

			var search = wp.JB.jobs_list.url.get_search( jobs_list );
			wp.JB.jobs_list.url.set( jobs_list, 'jb-search', search );

			var location = wp.JB.jobs_list.url.get_location( jobs_list );
			wp.JB.jobs_list.url.set( jobs_list, 'jb-location-search', location );

			wp.hooks.doAction( 'jb_jobs_list_do_search', jobs_list );

			button.addClass('disabled');

			wp.JB.jobs_list.ajax( jobs_list );
		}
	});


	$( document.body ).on( 'click', '.jb-only-remote', function() {
		var jobs_list = $(this).parents( '.jb-jobs' );

		if ( wp.JB.jobs_list.is_busy( jobs_list ) ) {
			return;
		}

		jobs_list.find( '.jb-do-search' ).addClass('disabled');

		wp.JB.jobs_list.preloader.show( jobs_list );

		jobs_list.data( 'page', 1 );
		wp.JB.jobs_list.url.set( jobs_list, 'jb-page', '' );

		var is_remote = wp.JB.jobs_list.url.get_type( jobs_list );
		if ( is_remote ) {
			wp.JB.jobs_list.url.set( jobs_list, 'jb-is-remote', is_remote );
		} else {
			wp.JB.jobs_list.url.set( jobs_list, 'jb-is-remote', '' );
		}

		wp.JB.jobs_list.ajax( jobs_list );
	});


	$( document.body ).on( 'click', '.jb-only-salary', function() {
		let jobs_list = $(this).parents( '.jb-jobs' );

		if ( wp.JB.jobs_list.is_busy( jobs_list ) ) {
			return;
		}

		if ( $(this).is(':checked') ) {
			jobs_list.find('.jb-salary-filter').show();
			let min = jobs_list.find( '.jb-double-range' ).data('min');
			let max = jobs_list.find( '.jb-double-range' ).data('max');
			wp.JB.jobs_list.url.set( jobs_list, 'jb-salary', min + '-' + max);
		} else {
			jobs_list.find('.jb-salary-filter').hide();

			let min = jobs_list.find( '.jb-double-range > input:first' ).attr('min');
			let max = jobs_list.find( '.jb-double-range > input:first' ).attr('max');

			jobs_list.find( '.jb-double-range' ).data('min',min).data('max',max);

			jobs_list.find( '.jb-double-range input' )[0].value = min;
			jobs_list.find( '.jb-double-range input' )[1].value = max;

			jobs_list.find( '.jb-double-range input' ).each( function(i) {
				let val = max;
				if ( 0 === i ) {
					val = min;
				}
				$(this).val( val ).trigger('change');
			})

			wp.JB.jobs_list.url.set( jobs_list, 'jb-salary', '' );
		}

		jobs_list.find( '.jb-do-search' ).addClass('disabled');

		wp.JB.jobs_list.preloader.show( jobs_list );

		jobs_list.data( 'page', 1 );
		wp.JB.jobs_list.url.set( jobs_list, 'jb-page', '' );

		wp.JB.jobs_list.ajax( jobs_list );
	});


	$( document.body ).on( 'change', '.jb-job-type-filter', function() {
		var jobs_list = $(this).parents( '.jb-jobs' );

		if ( wp.JB.jobs_list.is_busy( jobs_list ) ) {
			return;
		}

		jobs_list.find( '.jb-do-search' ).addClass('disabled');

		wp.JB.jobs_list.preloader.show( jobs_list );

		jobs_list.data( 'page', 1 );
		wp.JB.jobs_list.url.set( jobs_list, 'jb-page', '' );

		wp.JB.jobs_list.url.set( jobs_list, 'jb-job-type', $(this).val() );

		wp.JB.jobs_list.ajax( jobs_list );
	});


	$( document.body ).on( 'change', '.jb-job-category-filter', function() {
		var jobs_list = $(this).parents( '.jb-jobs' );

		if ( wp.JB.jobs_list.is_busy( jobs_list ) ) {
			return;
		}

		jobs_list.find( '.jb-do-search' ).addClass('disabled');

		wp.JB.jobs_list.preloader.show( jobs_list );

		jobs_list.data( 'page', 1 );
		wp.JB.jobs_list.url.set( jobs_list, 'jb-page', '' );

		wp.JB.jobs_list.url.set( jobs_list, 'jb-job-category', $(this).val() );

		wp.JB.jobs_list.ajax( jobs_list );
	});


	$( document.body ).on( 'click', '.jb-load-more-jobs', function() {
		var jobs_list = $(this).parents( '.jb-jobs' );

		if ( $(this).hasClass('disabled') ) {
			return;
		}

		if ( wp.JB.jobs_list.is_busy( jobs_list ) ) {
			return;
		}

		wp.JB.jobs_list.preloader.show( jobs_list );

		var page = jobs_list.data( 'page' )*1 + 1;
		jobs_list.data( 'page', page );

		wp.JB.jobs_list.url.set( jobs_list, 'jb-page', page );

		wp.JB.jobs_list.ajax( jobs_list, true );
	});

	$( document.body ).on( 'change', '.jb-double-range input', function() {
		var jobs_list = $(this).parents( '.jb-jobs' );

		if ( wp.JB.jobs_list.is_busy( jobs_list ) ) {
			return;
		}

		jobs_list.find( '.jb-do-search' ).addClass('disabled');

		wp.JB.jobs_list.preloader.show( jobs_list );

		// Initialize Sliders
		let sliderSection = jobs_list.find( '.jb-double-range' );
		sliderSection.find('input[type="range"]').each( function () {
			let slider = $(this)[0];
			slider.oninput();
		});

		var min = sliderSection.data('min');
		var max = sliderSection.data('max');

		jobs_list.data( 'page', 1 );

		wp.JB.jobs_list.url.set( jobs_list, 'jb-salary', min + '-' + max );

		wp.JB.jobs_list.ajax( jobs_list );
	});

	window.addEventListener( 'popstate', function(e) {
		if ( wp.JB.jobs_list.objects.wrapper.length ) {
			wp.JB.jobs_list.objects.wrapper.each( function () {
				wp.JB.jobs_list.ajax( $(this) );
			});
		}
	});
});
