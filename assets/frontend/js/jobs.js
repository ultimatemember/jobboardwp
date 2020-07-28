if ( typeof ( wp.JB ) !== 'object' ) {
	wp.JB = {};
}

if ( typeof ( wp.JB.jobs_list ) !== 'object' ) {
	wp.JB.jobs_list = {};
}

wp.JB.jobs_list = {
	first_load: true,
	is_search: false,
	objects: {
		wrapper: jQuery( '.jb-jobs' ),
	},
	is_busy: function() {
		return wp.JB.jobs_list.objects.wrapper.hasClass('jb-busy');
	},
	preloader: {
		show: function() {
			wp.JB.jobs_list.objects.wrapper.addClass('jb-busy').find('.jb-overlay').show();
		},
		hide: function() {
			wp.JB.jobs_list.objects.wrapper.removeClass('jb-busy').find('.jb-overlay').hide();
		}
	},
	url: {
		set: function( key, value ) {
			var data = wp.JB.jobs_list.url.get();

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
				new_data[ key ] = value;
			}
			jQuery.each( data, function( data_key ) {
				if ( key === data_key ) {
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

			window.history.pushState( 'string', 'JB Jobs Dashboard', window.location.origin + window.location.pathname + query_string );
		},
		get: function( search_key ) {
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
		get_page: function() {
			var page = wp.JB.jobs_list.objects.wrapper.data( 'page' );
			if ( ! page || typeof page == 'undefined' ) {
				page = 1;
			}
			return page;
		},
		get_employer: function() {
			var employer_id = wp.JB.jobs_list.objects.wrapper.data( 'employer' );
			if ( ! employer_id || typeof employer_id == 'undefined' ) {
				employer_id = '';
			}
			return employer_id;
		},
		get_search: function() {
			if ( wp.JB.jobs_list.objects.wrapper.find('.jb-search-line').length ) {
				return wp.JB.jobs_list.objects.wrapper.find( '.jb-search-line' ).val();
			} else {
				return '';
			}
		},
		get_location: function() {
			if ( wp.JB.jobs_list.objects.wrapper.find('.jb-search-location').length ) {
				return wp.JB.jobs_list.objects.wrapper.find( '.jb-search-location' ).val();
			} else {
				return '';
			}
		},
		get_type: function() {
			if ( wp.JB.jobs_list.objects.wrapper.find('.jb-only-remote').length ) {
				return wp.JB.jobs_list.objects.wrapper.find( '.jb-only-remote' ).is(':checked') ? 1 : 0;
			} else {
				return '';
			}
		},
		get_type_tag: function() {
			if ( wp.JB.jobs_list.objects.wrapper.find('.jb-job-type-filter').length ) {
				return wp.JB.jobs_list.objects.wrapper.find( '.jb-job-type-filter' ).val();
			} else {
				return '';
			}
		},
		get_category: function() {
			if ( wp.JB.jobs_list.objects.wrapper.find('.jb-job-category-filter').length ) {
				return wp.JB.jobs_list.objects.wrapper.find( '.jb-job-category-filter' ).val();
			} else {
				return '';
			}
		}
	},
	ajax: function( append ) {
		var request = {
			page:  wp.JB.jobs_list.url.get_page(),
			search:  wp.JB.jobs_list.url.get_search(),
			location:  wp.JB.jobs_list.url.get_location(),
			remote_only:  wp.JB.jobs_list.url.get_type(),
			type:  wp.JB.jobs_list.url.get_type_tag(),
			category:  wp.JB.jobs_list.url.get_category(),
			employer:  wp.JB.jobs_list.url.get_employer(),
			nonce: jb_front_data.nonce
		};

		wp.JB.jobs_list.is_search = !! ( request.search || request.location || request.remote_only );

		if ( wp.JB.jobs_list.first_load ) {
			if ( request.page > 1 ) {
				request.get_previous = true;
			}
			wp.JB.jobs_list.first_load = false;
		}

		request = wp.hooks.applyFilters( 'jb_jobs_request', request );

		wp.JB.jobs_list.preloader.show();

		wp.ajax.send( 'jb-get-jobs', {
			data:  request,
			success: function( answer ) {
				var template = wp.template( 'jb-jobs-list-line' );

				if ( append ) {
					wp.JB.jobs_list.objects.wrapper.find('.jb-jobs-wrapper').append( template( answer.jobs ) );
				} else {
					wp.JB.jobs_list.objects.wrapper.find('.jb-jobs-wrapper').html( template( answer.jobs ) );
				}

				wp.JB.jobs_list.objects.wrapper.data( 'total_pages', answer.pagination.total_pages );

				if ( answer.pagination.total_pages > 0 ) {
					wp.JB.jobs_list.objects.wrapper.find('.jb-jobs-wrapper').removeClass('jb-no-jobs');

					if ( answer.pagination.total_pages == answer.pagination.current_page ) {
						wp.JB.jobs_list.objects.wrapper.find( '.jb-load-more-jobs' ).hide();
					} else {
						wp.JB.jobs_list.objects.wrapper.find( '.jb-load-more-jobs' ).show();
					}
				} else {

					if ( ! append ) {
						if ( wp.JB.jobs_list.is_search ) {
							wp.JB.jobs_list.objects.wrapper.find('.jb-jobs-wrapper').html( wp.JB.jobs_list.objects.wrapper.data('no-jobs-search') );
						} else {
							wp.JB.jobs_list.objects.wrapper.find('.jb-jobs-wrapper').html( wp.JB.jobs_list.objects.wrapper.data('no-jobs') );
						}
					}

					wp.JB.jobs_list.objects.wrapper.find('.jb-jobs-wrapper').addClass('jb-no-jobs');

					wp.JB.jobs_list.objects.wrapper.find( '.jb-load-more-jobs' ).hide();
				}

				wp.hooks.doAction( 'jb_jobs_list_loaded', answer );

				wp.JB.jobs_list.objects.wrapper.find( '.jb-do-search' ).removeClass('disabled');

				wp.JB.jobs_list.preloader.hide();
			},
			error: function( data ) {
				console.log( data );
				wp.JB.jobs_list.preloader.hide();
				wp.JB.jobs_list.objects.wrapper.find( '.jb-do-search' ).removeClass('disabled');
			}
		});
	}
};


jQuery( document ).ready( function($) {
	if ( wp.JB.jobs_list.objects.wrapper.length ) {
		wp.JB.jobs_list.ajax();
	}

	$( document.body ).on( 'click', '.jb-do-search', function() {
		if ( $(this).hasClass('disabled') ) {
			return;
		}
		if ( wp.JB.jobs_list.is_busy() ) {
			return;
		}

		wp.JB.jobs_list.preloader.show();

		wp.JB.jobs_list.objects.wrapper.data( 'page', 1 );
		wp.JB.jobs_list.url.set( 'jb-page', '' );

		var search = wp.JB.jobs_list.url.get_search();
		wp.JB.jobs_list.url.set( 'jb-search', search );

		var location = wp.JB.jobs_list.url.get_location();
		wp.JB.jobs_list.url.set( 'jb-location-search', location );

		wp.hooks.doAction( 'jb_jobs_list_do_search' );

		$(this).addClass('disabled');

		wp.JB.jobs_list.ajax();
	});


	//make search on Enter click
	$( document.body ).on( 'keypress', '.jb-search-line, .jb-search-location', function(e) {
		if ( e.which === 13 ) {

			var button = $(this).parents('.jb-jobs').find('.jb-do-search');

			if ( button.hasClass('disabled') ) {
				return;
			}
			if ( wp.JB.jobs_list.is_busy() ) {
				return;
			}

			wp.JB.jobs_list.preloader.show();

			wp.JB.jobs_list.objects.wrapper.data( 'page', 1 );
			wp.JB.jobs_list.url.set( 'jb-page', '' );

			var search = wp.JB.jobs_list.url.get_search();
			wp.JB.jobs_list.url.set( 'jb-search', search );

			var location = wp.JB.jobs_list.url.get_location();
			wp.JB.jobs_list.url.set( 'jb-location-search', location );

			wp.hooks.doAction( 'jb_jobs_list_do_search' );

			button.addClass('disabled');

			wp.JB.jobs_list.ajax();
		}
	});


	$( document.body ).on( 'click', '.jb-only-remote', function() {
		wp.JB.jobs_list.objects.wrapper.find( '.jb-do-search' ).addClass('disabled');

		if ( wp.JB.jobs_list.is_busy() ) {
			return;
		}

		wp.JB.jobs_list.preloader.show();

		wp.JB.jobs_list.objects.wrapper.data( 'page', 1 );
		wp.JB.jobs_list.url.set( 'jb-page', '' );

		var is_remote = wp.JB.jobs_list.url.get_type();
		if ( is_remote ) {
			wp.JB.jobs_list.url.set( 'jb-is-remote', is_remote );
		} else {
			wp.JB.jobs_list.url.set( 'jb-is-remote', '' );
		}

		wp.JB.jobs_list.ajax();
	});


	$( document.body ).on( 'change', '.jb-job-type-filter', function() {
		wp.JB.jobs_list.objects.wrapper.find( '.jb-do-search' ).addClass('disabled');

		if ( wp.JB.jobs_list.is_busy() ) {
			return;
		}

		wp.JB.jobs_list.preloader.show();

		wp.JB.jobs_list.objects.wrapper.data( 'page', 1 );
		wp.JB.jobs_list.url.set( 'jb-page', '' );

		wp.JB.jobs_list.url.set( 'jb-job-type', $(this).val() );

		wp.JB.jobs_list.ajax();
	});


	$( document.body ).on( 'change', '.jb-job-category-filter', function() {
		wp.JB.jobs_list.objects.wrapper.find( '.jb-do-search' ).addClass('disabled');

		if ( wp.JB.jobs_list.is_busy() ) {
			return;
		}

		wp.JB.jobs_list.preloader.show();

		wp.JB.jobs_list.objects.wrapper.data( 'page', 1 );
		wp.JB.jobs_list.url.set( 'jb-page', '' );

		wp.JB.jobs_list.url.set( 'jb-job-category', $(this).val() );

		wp.JB.jobs_list.ajax();
	});


	$( document.body ).on( 'click', '.jb-load-more-jobs', function() {
		if ( $(this).hasClass('disabled') ) {
			return;
		}
		if ( wp.JB.jobs_list.is_busy() ) {
			return;
		}

		wp.JB.jobs_list.preloader.show();

		var page = wp.JB.jobs_list.objects.wrapper.data( 'page' )*1 + 1;
		wp.JB.jobs_list.objects.wrapper.data( 'page', page );

		wp.JB.jobs_list.url.set( 'jb-page', page );

		wp.JB.jobs_list.ajax( true );
	});

	window.addEventListener( 'popstate', function(e) {
		if ( wp.JB.jobs_list.objects.wrapper.length ) {
			wp.JB.jobs_list.ajax();
		}
	});
});