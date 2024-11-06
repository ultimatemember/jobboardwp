jQuery(window).on( 'load', function($) {
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {

			jQuery(mutation.addedNodes).find('.jb-job-categories').each(function() {
				wp.JB.job_categories_list.objects.wrapper = jQuery('.jb-job-categories');
				if ( wp.JB.job_categories_list.objects.wrapper.length ) {
					wp.JB.job_categories_list.ajax();
				}
				const wrapper = document.querySelector('.jb-job-categories');

				if (wrapper) {
					wrapper.addEventListener('click', (event) => {
						if (event.target !== wrapper) {
							event.preventDefault();
							event.stopPropagation();
						}
					});
				}
			});

			jQuery(mutation.addedNodes).find('.jb').each(function() {
				jb_responsive();
				const wrapper = document.querySelector('.jb-job-categories');

				if (wrapper) {
					wrapper.addEventListener('click', (event) => {
						if (event.target !== wrapper) {
							event.preventDefault();
							event.stopPropagation();
						}
					});
				}
			});
		});
	});

	observer.observe(document, {attributes: false, childList: true, characterData: false, subtree:true});
});

import { registerBlockType } from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('jb-block/jb-jobs-categories-list', {
	edit: function(props) {
		const blockProps = useBlockProps();

		return (
			<div {...blockProps}>
				<ServerSideRender block="jb-block/jb-jobs-categories-list" />
			</div>
		);
	},
	save: function() {
		return null;
	}
});
