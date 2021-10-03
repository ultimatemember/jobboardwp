if ( typeof ( wp.JB ) !== 'object' ) {
	wp.JB = {};
}

if ( typeof ( wp.JB.jobs_category_list ) !== 'object' ) {
	wp.JB.jobs_category_list = {};
}

wp.JB.jobs_category_list = {
	first_load: true,
	objects: {
		wrapper: jQuery( '.jb-jobs-category-list-wrapper' ),
	},
	is_busy: function() {
		return wp.JB.jobs_category_list.objects.wrapper.hasClass('jb-busy');
	},
	preloader: {
		show: function() {
			wp.JB.jobs_category_list.objects.wrapper.addClass('jb-busy').find('.jb-overlay').show();
		},
		hide: function() {
			wp.JB.jobs_category_list.objects.wrapper.removeClass('jb-busy').find('.jb-overlay').hide();
		}
	},
	ajax: function() {
		var request = {
			nonce: jb_front_data.nonce
		};

		wp.JB.jobs_category_list.preloader.show();

		wp.ajax.send( 'jb-get-categories', {
			data:  request,
			success: function( answer ) {
				console.log(answer)
				var template = wp.template( 'jb-jobs-category-list' );
				wp.JB.jobs_category_list.objects.wrapper.find('.category-list').html( template( answer ) );

				wp.JB.jobs_category_list.preloader.hide();
			},
			error: function( data ) {
				console.log( data );
				wp.JB.jobs_category_list.preloader.hide();
			}
		});
	}
};


jQuery( document ).ready( function($) {
	if ( wp.JB.jobs_category_list.objects.wrapper.length ) {
		wp.JB.jobs_category_list.ajax();
	}
});
