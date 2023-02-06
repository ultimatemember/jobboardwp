var useBlockProps = wp.blockEditor.useBlockProps;
wp.blocks.registerBlockType('jb-block/jb-jobs-dashboard', {
	title: wp.i18n.__('Jobs dashboard', 'jobboardwp'),
	description: wp.i18n.__('Displaying jobs dashboard', 'jobboardwp'),
	icon: 'dashboard',
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
			props.setAttributes({content: '[jb_jobs_dashboard]'});
		}

		return wp.element.createElement('div', blockProps, [
			wp.element.createElement(wp.components.ServerSideRender, {
				block: 'jb-block/jb-jobs-dashboard'
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

			jQuery(mutation.addedNodes).find('.jb-job-dashboard').each(function() {
				wp.JB.jobs_dashboard.objects.wrapper = jQuery('.jb-job-dashboard');
				if ( wp.JB.jobs_dashboard.objects.wrapper.length ) {
					wp.JB.jobs_dashboard.ajax();
				}
			});

			jQuery(mutation.addedNodes).find('.jb').each(function() {
				jb_responsive();
			});
		});
	});

	observer.observe(document, {attributes: false, childList: true, characterData: false, subtree:true});
});
