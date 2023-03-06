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

import { useBlockProps } from '@wordpress/block-editor';
import { ServerSideRender } from '@wordpress/components';

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
		const blockProps = useBlockProps();

		let { content } = props.attributes;
		if (content === undefined) {
			props.setAttributes({ content: '[jb_jobs_dashboard]' });
			content = '[jb_jobs_dashboard]';
		}

		return (
			<div {...blockProps}>
				<ServerSideRender block="jb-block/jb-jobs-dashboard" />
			</div>
		);
	},

	save: function () {
		return null;
	}
});
