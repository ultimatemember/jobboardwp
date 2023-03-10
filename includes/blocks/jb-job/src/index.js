import { useSelect } from '@wordpress/data';
import { PanelBody, SelectControl, Spinner } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { registerBlockType } from "@wordpress/blocks";

registerBlockType('jb-block/jb-job', {
	edit: function (props) {
		let { job_id, setAttributes } = props.attributes;
		const blockProps = useBlockProps();
		const posts = useSelect((select) => {
			return select('core').getEntityRecords('postType', 'jb-job', {
				per_page: -1,
				_fields: ['id', 'title']
			});
		});

		if (!posts) {
			return (
				<p>
					<Spinner />
					{wp.i18n.__('Loading...', 'jobboardwp')}
				</p>
			);
		}

		if (posts.length === 0) {
			return 'No posts found.';
		}

		let posts_data = [{ id: '', title: '' }].concat(posts);

		let get_post = posts_data.map((post) => {
			return {
				label: post.title.rendered,
				value: post.id
			};
		});

		function jbShortcode(value) {
			let shortcode = '';
			if (value !== undefined && value !== '') {
				shortcode = '[jb_job id="' + value + '"]';
			} else {
				shortcode = '[jb_job]';
			}
			return shortcode;
		}

		return (
			<div {...blockProps}>
				<ServerSideRender block="jb-block/jb-job" attributes={props.attributes} />
				<InspectorControls>
					<PanelBody title={wp.i18n.__('Job', 'jobboardwp')}>
						<SelectControl
							label={wp.i18n.__('Job', 'jobboardwp')}
							className="jb_select_job"
							value={job_id}
							options={get_post}
							style={{ height: '35px', lineHeight: '20px', padding: '0 7px' }}
							onChange={(value) => {
								props.setAttributes({ job_id: value });
								jbShortcode(value);
							}}
						/>
					</PanelBody>
				</InspectorControls>
			</div>
		);
	}, // end edit

	save: function save(props) {
		return null;
	}

});
