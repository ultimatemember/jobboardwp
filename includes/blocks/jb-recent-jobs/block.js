wp.blocks.registerBlockType('jb-block/jb-recent-jobs', {
	edit: wp.data.withSelect(function (select) {
		return {
			types: select('core').getEntityRecords('taxonomy', 'jb-job-type', {
				per_page: -1,
				_fields: ['id', 'name']
			}),
			categories: select('core').getEntityRecords('taxonomy', 'jb-job-category', {
				per_page: -1,
				_fields: ['id', 'name']
			})
		};
	})(function (props) {
			var useBlockProps = wp.blockEditor.useBlockProps;
			var blockProps = useBlockProps();
			var className = props.className,
				number = props.attributes.number,
				no_logo = props.attributes.no_logo,
				no_job_types = props.attributes.no_job_types,
				hide_filled = props.attributes.hide_filled,
				orderby = props.attributes.orderby,
				orderby_opt = [
					{label: wp.i18n.__('Posting date', 'jobboardwp'), value: 'date'},
					{label: wp.i18n.__('Expiry date', 'jobboardwp'), value: 'expiry_date'}
				],
				types = props.types,
				type = props.attributes.type,
				types_data = [],
				categories = props.categories,
				category = props.attributes.category,
				categories_data = [],
				remote_only = props.attributes.remote_only,
				content = props.attributes.content,
				category_hide = '-hide',
				type_hide = '-hide';

			if (types !== null) {
				types_data = types_data.concat(types);
				if (types.length !== 0) {
					type_hide = '';
				}
			}

			if (categories !== null) {
				categories_data = categories_data.concat(categories);
				if (categories.length !== 0) {
					category_hide = '';
				}
			}

			function get_option(data, type) {

				var option = [];

				if (type === 'type') {
					data.map(function (type) {
						option.push(
							{
								label: type.name,
								value: type.id
							}
						);
					});
				} else if (type === 'category') {
					data.map(function (category) {
						option.push(
							{
								label: category.name,
								value: category.id
							}
						);
					});
				}

				return option;
			}

			function jbShortcode(number, category, type, remote_only, orderby, hide_filled, no_logo, no_job_types) {
				var shortcode = '[jb_recent_jobs';

				if (number !== undefined && number !== '') {
					shortcode = shortcode + ' number="' + number + '"';
				} else {
					shortcode = shortcode + ' number="' + 5 + '"';
				}

				if (no_logo === true) {
					shortcode = shortcode + ' no_logo="' + 1 + '"';
				} else {
					shortcode = shortcode + ' no_logo="' + 0 + '"';
				}

				if (hide_filled === true) {
					shortcode = shortcode + ' hide_filled="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide_filled="' + 0 + '"';
				}

				if (no_job_types === true) {
					shortcode = shortcode + ' no_job_types="' + 1 + '"';
				} else {
					shortcode = shortcode + ' no_job_types="' + 0 + '"';
				}

				if (type !== undefined && type !== '') {
					shortcode = shortcode + ' type="' + type + '"';
				}

				if (category !== undefined && category !== '') {
					shortcode = shortcode + ' category="' + category + '"';
				}

				if (orderby !== undefined) {
					shortcode = shortcode + ' orderby="' + orderby + '"';
				}

				if (remote_only === true) {
					shortcode = shortcode + ' remote_only="' + 1 + '"';
				} else {
					shortcode = shortcode + ' remote_only="' + 0 + '"';
				}

				shortcode = shortcode + ']';

				props.setAttributes({content: shortcode});
			}

			if (!types_data || !categories_data) {
				return wp.element.createElement(
					'p',
					{
						className: className
					},
					wp.element.createElement(
						wp.components.Spinner,
						null
					),
					wp.i18n.__('Loading data', 'jobboardwp')
				);
			}

			if (0 === types_data.length || 0 === categories_data.length) {
				return wp.element.createElement(
					'p',
					null,
					wp.i18n.__('No data', 'jobboardwp')
				);
			}

			if (content === undefined) {
				props.setAttributes({content: '[jb_recent_jobs]'});
			}

			var get_category = get_option(categories_data, 'category');
			var get_types = get_option(types_data, 'type');

			return wp.element.createElement('div', blockProps, [
				wp.element.createElement(wp.components.ServerSideRender, {
					block: 'jb-block/jb-recent-jobs',
					attributes: props.attributes
				}),
				wp.element.createElement(
					wp.blockEditor.InspectorControls,
					{},
					wp.element.createElement(
						wp.components.PanelBody,
						{
							title: wp.i18n.__('Recent jobs', 'jobboardwp')
						},
						wp.element.createElement(
							wp.components.TextControl,
							{
								label: wp.i18n.__('Number', 'jobboardwp'),
								className: 'jb_number',
								type: 'number',
								min: 1,
								value: props.attributes.number,
								onChange: function onChange(value) {
									if (value === '') {
										value = 1;
									}
									props.setAttributes({number: value});
									jbShortcode(value, category, type, remote_only, orderby, hide_filled, no_logo, no_job_types);
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__('Hide logo', 'jobboardwp'),
								className: 'jb_no_logo',
								checked: props.attributes.no_logo,
								onChange: function onChange(value) {
									props.setAttributes({no_logo: value});
									jbShortcode(number, category, type, remote_only, orderby, hide_filled, value, no_job_types);
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__('Hide filled', 'jobboardwp'),
								className: 'jb_hide_filled',
								checked: props.attributes.hide_filled,
								onChange: function onChange(value) {
									props.setAttributes({hide_filled: value});
									jbShortcode(number, category, type, remote_only, orderby, value, no_logo, no_job_types);
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__('Hide job types', 'jobboardwp'),
								className: 'jb_no_job_types',
								checked: props.attributes.no_job_types,
								onChange: function onChange(value) {
									props.setAttributes({no_job_types: value});
									jbShortcode(number, category, type, remote_only, orderby, hide_filled, no_logo, value);
								}
							}
						),
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__('Select category', 'jobboardwp'),
								className: 'jb_select_category' + category_hide,
								value: props.attributes.category,
								multiple: true,
								style: {
									height: '80px',
									overflow: 'auto'
								},
								suffix: ' ',
								options: get_category,
								onChange: function onChange(value) {
									props.setAttributes({category: value});
									jbShortcode(number, value, type, remote_only, orderby, hide_filled, no_logo, no_job_types);
								}
							}
						),
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__('Select type', 'jobboardwp'),
								className: 'jb_select_type' + type_hide,
								value: props.attributes.type,
								multiple: true,
								style: {
									height: '80px',
									overflow: 'auto'
								},
								suffix: ' ',
								options: get_types,
								onChange: function onChange(value) {
									props.setAttributes({type: value});
									jbShortcode(number, category, value, remote_only, orderby, hide_filled, no_logo, no_job_types);
								}
							}
						),
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__('Select order by', 'jobboardwp'),
								className: 'jb_select_orderby',
								value: props.attributes.orderby,
								options: orderby_opt,
								onChange: function onChange(value) {
									props.setAttributes({orderby: value});
									jbShortcode(number, category, type, remote_only, value, hide_filled, no_logo, no_job_types);
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__('Remote only', 'jobboardwp'),
								className: 'jb_remote_only',
								checked: props.attributes.remote_only,
								onChange: function onChange(value) {
									props.setAttributes({remote_only: value});
									jbShortcode(number, category, type, value, orderby, hide_filled, no_logo, no_job_types);
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
