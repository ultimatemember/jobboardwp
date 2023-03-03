wp.blocks.registerBlockType('jb-block/jb-job', {
	edit: wp.data.withSelect(function (select) {
		return {
			posts: select('core').getEntityRecords('postType', 'jb-job', {
				per_page: -1,
				_fields: ['id', 'title']
			})
		};
	})(function (props) {
			var posts = props.posts,
				className = props.className,
				job_id = props.attributes.job_id,
				posts_data;

			posts_data = [{id: '', title: ''}].concat(posts);

			function get_option(posts) {

				var option = [];

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

				var shortcode = '';

				if (value !== undefined && value !== '') {
					shortcode = '[jb_job id="' + value + '"]';
				} else {
					shortcode = '[jb_job]';
				}

				return shortcode;

			}

			if (!posts) {
				return wp.element.createElement(
					'p',
					{
						className: className
					},
					wp.element.createElement(
						wp.components.Spinner,
						null
					),
					wp.i18n.__('', 'jobboardwp')
				);
			}

			if (0 === posts.length) {
				return wp.element.createElement(
					'p',
					null,
					wp.i18n.__('No33 jobs', 'jobboardwp')
				);
			}

			if ( job_id === undefined ) {
				props.setAttributes({job_id: posts_data[0]['id']});
				var shortcode = jbShortcode(posts_data[0]['id']);
				props.setAttributes({content: shortcode});
			}

			var get_post = get_option(posts_data);

			jQuery('.jb-button.jb-job-apply').attr('disabled', 'disabled');

			var useBlockProps = wp.blockEditor.useBlockProps;
			var blockProps = useBlockProps();

			return wp.element.createElement('div', blockProps, [
				wp.element.createElement(wp.components.ServerSideRender, {
					block: 'jb-block/jb-job',
					attributes: props.attributes
				}),
				wp.element.createElement(
					wp.blockEditor.InspectorControls,
					{},
					wp.element.createElement(
						wp.components.PanelBody,
						{
							title: wp.i18n.__('Job', 'jobboardwp')
						},
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__('Job', 'jobboardwp'),
								className: 'jb_select_job',
								value: job_id,
								options: get_post,
								style: {
									height: '35px',
									lineHeight: '20px',
									padding: '0 7px'
								},
								onChange: function onChange(value) {
									props.setAttributes({job_id: value});
									var shortcode = jbShortcode(value);
									console.log(shortcode)
									props.setAttributes({content: shortcode});
								}
							}
						)
					)
				)
			]);
		} // end withSelect
	), // end edit

	save: function save(props) {
		return null;
	}

});
