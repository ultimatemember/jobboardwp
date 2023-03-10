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

import { registerBlockType } from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('jb-block/jb-jobs-dashboard', {
	edit: function (props) {
		const blockProps = useBlockProps();

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
