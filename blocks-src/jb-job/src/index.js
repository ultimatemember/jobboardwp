import { useSelect } from '@wordpress/data';
import { PanelBody, SelectControl, Spinner } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { registerBlockType } from "@wordpress/blocks";

registerBlockType('jb-block/jb-job', {
	edit: function (props) {
		const { attributes, setAttributes } = props;
		const { job_id } = attributes;
		const blockProps = useBlockProps();

		const posts = useSelect((select) => {
			return select('core').getEntityRecords('postType', 'jb-job', {
				per_page: -1,
				_fields: ['id', 'title']
			});
		}, []);

		if (!posts) {
			return (
				<p>
					<Spinner />
					{wp.i18n.__('Loading...', 'jobboardwp')}
				</p>
			);
		}

		if (posts.length === 0) {
			return <p>{wp.i18n.__('Jobs not found', 'jobboardwp')}</p>;
		}

		const get_post = [{ label: '', value: '' }].concat(
			posts.map((post) => ({
				label: post.title.rendered,
				value: post.id
			}))
		);

		return (
			<div {...blockProps}>
				<ServerSideRender block="jb-block/jb-job" attributes={attributes} />
				<InspectorControls>
					<PanelBody title={wp.i18n.__('Job', 'jobboardwp')}>
						<SelectControl
							label={wp.i18n.__('Job', 'jobboardwp')}
							className="jb_select_job"
							value={job_id}
							options={get_post}
							onChange={(value) => setAttributes({ job_id: value })}
						/>
					</PanelBody>
				</InspectorControls>
			</div>
		);
	},

	save: () => null
});

jQuery(window).on( 'load', function($) {
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			jQuery(mutation.addedNodes).find('.jb-single-job-wrapper').each(function() {
				const wrapper = document.querySelector('.jb-single-job-wrapper');

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
