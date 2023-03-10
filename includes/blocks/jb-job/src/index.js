import { withSelect } from '@wordpress/data';
import { PanelBody, SelectControl, Spinner } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import {registerBlockType} from "@wordpress/blocks";

registerBlockType('jb-block/jb-job', {
	edit: withSelect(function (select) {
		return {
			posts: select('core').getEntityRecords('postType', 'jb-job', {
				per_page: -1,
				_fields: ['id', 'title']
			})
		};
	})(function(props) {
		let posts = props.posts;
		if (!posts) {
			return (
				<p>
					<Spinner />
					{wp.i18n.__('Loading...', 'jobboardwp')}
				</p>
			);
		}
		if (0 === posts.length) {
			return 'No posts found.';
		}
		let job_id = props.attributes.job_id, posts_data;

		posts_data = [{id: '', title: ''}].concat(posts);

		let get_post = get_option(posts_data);

		function get_option(posts) {

			let option = [];

			posts.map(function (post) {
				option.push(
					{
						label: post.title.rendered,
						value: post.id
					}
				);
			});
			return option;
		}

		function jbShortcode(value) {
			let shortcode = '';

			if (value !== undefined && value !== '') {
				shortcode = '[jb_job id="' + value + '"]';
			} else {
				shortcode = '[jb_job]';
			}
			return shortcode;
		}

		let blockProps = useBlockProps();
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
							style={{height: '35px', lineHeight: '20px', padding: '0 7px'}}
							onChange={(value) => {
								props.setAttributes({job_id: value});
								let shortcode = jbShortcode(value);
							}}
						/>
					</PanelBody>
				</InspectorControls>
			</div>
		);
	}), // end withSelect

	save: function save(props) {
		return null;
	}

});
