var exclude_blocks;
if ( ! jb_blocks_options['exclude_blocks'] ) {
	exclude_blocks = 0;
} else {
	exclude_blocks = 1;
}
jQuery(window).on( 'load', function($) {
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {

			// Please don't delete. This is fix for firefox browser widgets page for legacy widget and multiple select
			if ( jQuery('#widgets-editor'.length > 0 ) && navigator.userAgent.toLowerCase().indexOf('firefox') > -1 ) {
				jQuery('input.id_base').each(function () {
					if ('jb_recent_jobs' === jQuery(this).val()) {
						var container = jQuery(this).closest('.wp-block-legacy-widget__edit-form');
						if ('hidden' === container.attr('hidden')) {
							container.find('select').each(function () {
								jQuery(this).change();
							});
						}
					}
				});
			}

		});
	});

	observer.observe(document, {attributes: false, childList: true, characterData: false, subtree:true});
});

// remove duplicated taxonomy panels
if ( 1 === exclude_blocks ) {
	wp.data.dispatch('core/edit-post').removeEditorPanel('taxonomy-panel-jb-job-category');
	wp.data.dispatch('core/edit-post').removeEditorPanel('taxonomy-panel-jb-job-type');

	wp.data.dispatch('core/edit-post').hideBlockTypes('jb-block/jb-job-post');
	wp.data.dispatch('core/edit-post').hideBlockTypes('jb-block/jb-jobs-dashboard');
	wp.data.dispatch('core/edit-post').hideBlockTypes('jb-block/jb-job');
	wp.data.dispatch('core/edit-post').hideBlockTypes('jb-block/jb-jobs-categories-list');
	wp.data.dispatch('core/edit-post').hideBlockTypes('jb-block/jb-jobs-list');
	wp.data.dispatch('core/edit-post').hideBlockTypes('jb-block/jb-recent-jobs');
} else {
	wp.data.dispatch('core/edit-post').showBlockTypes('jb-block/jb-job-post');
	wp.data.dispatch('core/edit-post').showBlockTypes('jb-block/jb-jobs-dashboard');
	wp.data.dispatch('core/edit-post').showBlockTypes('jb-block/jb-job');
	wp.data.dispatch('core/edit-post').showBlockTypes('jb-block/jb-jobs-categories-list');
	wp.data.dispatch('core/edit-post').showBlockTypes('jb-block/jb-jobs-list');
	wp.data.dispatch('core/edit-post').showBlockTypes('jb-block/jb-recent-jobs');
}
