import { registerBlockType } from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps } from '@wordpress/block-editor';
import { useEffect } from '@wordpress/element';

registerBlockType('jb-block/jb-job-post', {
	edit: function () {
		useEffect(() => {
			document.querySelectorAll('#jb-job-preview, #jb-job-draft, #jb_company_logo_plupload')
			.forEach(element => {
				element.setAttribute('disabled', 'disabled');
			});
		}, []);

		const blockProps = useBlockProps();
		return (
			<div {...blockProps}>
				<ServerSideRender block="jb-block/jb-job-post" />
			</div>
		);
	},
	save: () => null
});

jQuery(window).on('load', function () {
	const observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			jQuery(mutation.addedNodes)
			.find('.jb-job-submission-form-wrapper')
			.each(function () {
				const wrapper = document.querySelector('.jb-job-submission-form-wrapper');

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
