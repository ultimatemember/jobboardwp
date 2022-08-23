if ( typeof ( wp.JB ) !== 'object' ) {
	wp.JB = {};
}

if ( typeof ( wp.JB.jobs_dashboard ) !== 'object' ) {
	wp.JB.jobs_dashboard = {};
}

wp.JB.jobs_dashboard = {
	objects: {
		wrapper: jQuery( '.jb-job-dashboard' ),
	},
	is_busy: function() {
		return wp.JB.jobs_dashboard.objects.wrapper.hasClass('jb-busy');
	},
	preloader: {
		show: function() {
			wp.JB.jobs_dashboard.objects.wrapper.addClass('jb-busy').find('.jb-overlay').show();
		},
		hide: function() {
			wp.JB.jobs_dashboard.objects.wrapper.removeClass('jb-busy').find('.jb-overlay').hide();
		}
	},
	url: {
		set: function( key, value ) {
			var data = wp.JB.jobs_dashboard.url.get();

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

			var url_data = wp.JB.jobs_dashboard.url.parse();
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
			var attrs = query.split( '&' );
			jQuery.each( attrs, function( i ) {
				var attr = attrs[ i ].split( '=' );
				data[ attr[0] ] = attr[1];
			});
			return data;
		},
	},
	ajax: function( append ) {
		var request = {
			nonce: jb_front_data.nonce
		};

		wp.JB.jobs_dashboard.preloader.show();

		wp.ajax.send( 'jb-get-employer-jobs', {
			data:  request,
			success: function( answer ) {
				var template = wp.template( 'jb-jobs-dashboard-line' );

				if ( append ) {
					wp.JB.jobs_dashboard.objects.wrapper.find('.jb-job-dashboard-rows').append( template( answer.jobs ) );
				} else {
					wp.JB.jobs_dashboard.objects.wrapper.find('.jb-job-dashboard-rows').html( template( answer.jobs ) );
				}

				if ( answer.jobs.length > 0 ) {
					wp.JB.jobs_dashboard.objects.wrapper.find('.jb-job-dashboard-rows').addClass('isset-jobs');
				} else {
					wp.JB.jobs_dashboard.objects.wrapper.find('.jb-job-dashboard-rows').removeClass('isset-jobs');
				}

				wp.hooks.doAction( 'jb_jobs_dashboard_loaded', answer );

				jb_init_dropdown();

				wp.JB.jobs_dashboard.preloader.hide();
			},
			error: function( data ) {
				console.log( data );
				wp.JB.jobs_dashboard.preloader.hide();
			}
		});
	}
};


jQuery( document ).ready( function($) {
	if ( wp.JB.jobs_dashboard.objects.wrapper.length ) {
		wp.JB.jobs_dashboard.ajax();
	}

	// $( document.body ).on( 'click', '.jb-load-more-jobs', function() {
	// 	if ( $(this).hasClass('disabled') ) {
	// 		return;
	// 	}
	// 	if ( wp.JB.jobs_dashboard.is_busy() ) {
	// 		return;
	// 	}
	//
	// 	wp.JB.jobs_dashboard.preloader.show();
	//
	// 	var page = wp.JB.jobs_dashboard.objects.wrapper.data( 'page' )*1 + 1;
	// 	wp.JB.jobs_dashboard.objects.wrapper.data( 'page', page );
	//
	// 	wp.JB.jobs_dashboard.ajax( true );
	// });

	window.addEventListener( 'popstate', function(e) {
		if ( wp.JB.jobs_dashboard.objects.wrapper.length ) {
			wp.JB.jobs_dashboard.ajax();
		}
	});


	$(document.body).on('click', '.jb-jobs-action-fill', function() {
		var job_id = $(this).data('job-id');

		var request = {
			job_id: job_id,
			nonce: jb_front_data.nonce
		};

		wp.ajax.send( 'jb-fill-job', {
			data:  request,
			success: function( answer ) {
				var template = wp.template( 'jb-jobs-dashboard-line' );
				$('.jb-job-dashboard-row[data-job-id="' + job_id + '"]').replaceWith( template( answer.jobs ) );
				wp.hooks.doAction( 'jb_jobs_dashboard_job_filled', answer );
			},
			error: function( data ) {
				console.log( data );
			}
		} );
	});

	$(document.body).on('click', '.jb-jobs-action-un-fill', function() {
		var job_id = $(this).data('job-id');

		var request = {
			job_id: job_id,
			nonce: jb_front_data.nonce
		};

		wp.ajax.send( 'jb-unfill-job', {
			data:  request,
			success: function( answer ) {
				var template = wp.template( 'jb-jobs-dashboard-line' );
				$('.jb-job-dashboard-row[data-job-id="' + job_id + '"]').replaceWith( template( answer.jobs ) );
				wp.hooks.doAction( 'jb_jobs_dashboard_job_unfilled', answer );
			},
			error: function( data ) {
				console.log( data );
			}
		} );
	});

	$(document.body).on('click', '.jb-jobs-action-delete', function() {

		if ( ! confirm( wp.i18n.__( 'Are you sure that you want to delete this job?', 'jobboardwp' ) ) ) {
			return false;
		}

		var job_id = $(this).data('job-id');

		var request = {
			job_id: job_id,
			nonce: jb_front_data.nonce
		};

		wp.ajax.send( 'jb-delete-job', {
			data:  request,
			success: function( answer ) {
				$('.jb-job-dashboard-row[data-job-id="' + job_id + '"]').remove();

				wp.hooks.doAction( 'jb_jobs_dashboard_job_deleted', answer, job_id );
			},
			error: function( data ) {
				console.log( data );
			}
		} );
	});
});
