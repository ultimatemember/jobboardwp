var useBlockProps = wp.blockEditor.useBlockProps;
wp.blocks.registerBlockType('jb-block/jb-jobs-categories-list', {
	title: wp.i18n.__('Jobs categories list', 'jobboardwp'),
	description: wp.i18n.__('Displaying jobs categories list', 'jobboardwp'),
	icon: 'editor-ul',
	category: 'jb-blocks',
	attributes: {
		content: {
			source: 'html',
			selector: 'p',
			default: ''
		}
	},
	edit: function (props) {
		var blockProps = useBlockProps();
		var content = props.attributes.content;

		if (content === undefined) {
			props.setAttributes({content: '[jb_job_categories_list]'});
		}

		return wp.element.createElement('div', blockProps, [
			wp.element.createElement(wp.components.ServerSideRender, {
				block: 'jb-block/jb-jobs-categories-list'
			})
		]);

	},

	save: function (props) {
		return null;
	}

});

jQuery(window).on( 'load', function($) {
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {

			jQuery(mutation.addedNodes).find('.jb-job-categories').each(function() {
				wp.JB.job_categories_list.objects.wrapper = jQuery('.jb-job-categories');
				if ( wp.JB.job_categories_list.objects.wrapper.length ) {
					wp.JB.job_categories_list.ajax();
				}
			});

			jQuery(mutation.addedNodes).find('.jb').each(function() {
				jb_responsive();
			});
		});
	});

	observer.observe(document, {attributes: false, childList: true, characterData: false, subtree:true});
});
