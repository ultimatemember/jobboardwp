if ( typeof ( wp.JB ) !== 'object' ) {
	wp.JB = {};
}

if ( typeof ( wp.JB.job_categories_list ) !== 'object' ) {
	wp.JB.job_categories_list = {};
}

wp.JB.job_categories_list = {
	first_load: true,
	objects: {
		wrapper: jQuery( '.jb-job-categories' ),
	},
	is_busy: function() {
		return wp.JB.job_categories_list.objects.wrapper.hasClass('jb-busy');
	},
	preloader: {
		show: function() {
			wp.JB.job_categories_list.objects.wrapper.addClass('jb-busy').find('.jb-overlay').show();
		},
		hide: function() {
			wp.JB.job_categories_list.objects.wrapper.removeClass('jb-busy').find('.jb-overlay').hide();
		}
	},
	ajax: function() {
		var request = {
			nonce: jb_front_data.nonce
		};

		wp.JB.job_categories_list.preloader.show();

		wp.ajax.send( 'jb-get-categories', {
			data:  request,
			success: function( answer ) {
				if ( answer.total > 0 ) {
					var template = wp.template( 'jb-job-categories-list' );
					wp.JB.job_categories_list.objects.wrapper.find('.jb-job-categories-wrapper').html( template( answer.terms ) );

					wp.JB.job_categories_list.objects.wrapper.find('.jb-job-categories-wrapper').removeClass('jb-no-job-categories');
				} else {
					wp.JB.job_categories_list.objects.wrapper.find('.jb-job-categories-wrapper').html( wp.i18n.__( 'No Job Categories', 'jobboardwp' ) );
					wp.JB.job_categories_list.objects.wrapper.find('.jb-job-categories-wrapper').addClass('jb-no-job-categories');
				}

				wp.JB.job_categories_list.preloader.hide();
			},
			error: function( data ) {
				console.log( data );
				wp.JB.job_categories_list.preloader.hide();
			}
		});
	}
};


jQuery( document ).ready( function($) {
	if ( wp.JB.job_categories_list.objects.wrapper.length ) {
		wp.JB.job_categories_list.ajax();
	}
});
