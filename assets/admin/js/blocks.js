// default settings
var default_hide_filled, default_hide_logo, default_hide_job_types, default_hide_expired, default_hide_search, default_hide_search_location, default_hide_filters;
if ( ! jb_blocks_options['jobs-list-hide-filled'] ) {
	default_hide_filled = 0;
} else {
	default_hide_filled = 1;
}
if ( ! jb_blocks_options['jobs-list-no-logo'] ) {
	default_hide_logo = 0;
} else {
	default_hide_logo = 1;
}
if ( ! jb_blocks_options['jobs-list-hide-job-types'] ) {
	default_hide_job_types = 0;
} else {
	default_hide_job_types = 1;
}
if ( ! jb_blocks_options['jobs-list-hide-expired'] ) {
	default_hide_expired = 0;
} else {
	default_hide_expired = 1;
}
if ( ! jb_blocks_options['jobs-list-hide-expired'] ) {
	default_hide_expired = 0;
} else {
	default_hide_expired = 1;
}
if ( ! jb_blocks_options['jobs-list-hide-search'] ) {
	default_hide_search = 0;
} else {
	default_hide_search = 1;
}
if ( ! jb_blocks_options['jobs-list-hide-location-search'] ) {
	default_hide_search_location = 0;
} else {
	default_hide_search_location = 1;
}
if ( ! jb_blocks_options['jobs-list-hide-filters'] ) {
	default_hide_filters = 0;
} else {
	default_hide_filters = 1;
}


//-------------------------------------\\
//---- Jobboard post job shortcode ----\\
//-------------------------------------\\

wp.blocks.registerBlockType( 'jb-block/jb-job-post', {
	title: wp.i18n.__( 'Post Job', 'jobboardwp' ),
	description: wp.i18n.__( 'Displaying jobs posting form', 'jobboardwp' ),
	icon: 'forms',
	category: 'jb-blocks',

	edit: function(props) {
		jQuery('#jb-job-preview, #jb-job-draft, #jb_company_logo_plupload').attr('disabled', 'disabled');

		return wp.element.createElement('div', {}, [
			wp.element.createElement( wp.components.ServerSideRender, {
				block: 'jb-block/jb-job-post'
			} )
		] );

	},

	save: function(props) {
		return null;
	}

});


//-------------------------------\\
//-------- Job shortcode --------\\
//-------------------------------\\

wp.blocks.registerBlockType( 'jb-block/jb-job', {
	title: wp.i18n.__( 'Single job', 'jobboardwp' ),
	description: wp.i18n.__( 'Displaying a single job', 'jobboardwp' ),
	icon: 'text',
	category: 'jb-blocks',
	attributes: {
		job_id: {
			type: 'select'
		}
	},

	edit: wp.data.withSelect( function( select ) {
		return {
			posts: select( 'core' ).getEntityRecords( 'postType', 'jb-job', {
				per_page: -1,
				_fields: ['id', 'title']
			})
		};
	} )( function( props ) {
			var posts         = props.posts,
				className     = props.className,
				job_id        = props.attributes.job_id,
				posts_data;

			posts_data = [ { id: '', title: '' } ].concat(posts);

			function get_option( posts ) {

				var option = [];

				posts.map( function( post ) {
					option.push(
						{
							label: post.title.rendered,
							value: post.id
						}
					);
				});

				return option;
			}

			function jbShortcode( value ) {

				var shortcode = '';

				if ( value !== undefined && value !== '' ) {
					shortcode = '[jb_job id="' + value + '"]';
				} else {
					shortcode = '[jb_job]';
				}

				return shortcode;

			}

			if ( ! posts ) {
				return wp.element.createElement(
					'p',
					{
						className: className
					},
					wp.element.createElement(
						wp.components.Spinner,
						null
					),
					wp.i18n.__( '', 'jobboardwp' )
				);
			}

			if ( 0 === posts.length ) {
				return wp.element.createElement(
					'p',
					null,
					wp.i18n.__( 'No jobs', 'jobboardwp' )
				);
			}

			if ( job_id === undefined ) {
				props.setAttributes({ job_id: posts_data[0]['id'] });
				var shortcode = jbShortcode(posts_data[0]['id']);
				props.setAttributes( { content: shortcode } );
			}

			var get_post = get_option( posts_data );

			jQuery('.jb-button.jb-job-apply').attr('disabled', 'disabled');

			return wp.element.createElement('div', {}, [
				wp.element.createElement( wp.components.ServerSideRender, {
					block: 'jb-block/jb-job',
					attributes: props.attributes
				} ),
				wp.element.createElement(
					wp.blockEditor.InspectorControls,
					{},
					wp.element.createElement(
						wp.components.PanelBody,
						{
							title: wp.i18n.__( 'Job', 'jobboardwp' )
						},
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__( 'Job', 'jobboardwp' ),
								className: 'jb_select_job',
								value: job_id,
								options: get_post,
								style: {
									height: '35px',
									lineHeight: '20px',
									padding: '0 7px'
								},
								onChange: function onChange( value ) {
									props.setAttributes({ job_id: value });
									var shortcode = jbShortcode(value);
									props.setAttributes( { content: shortcode } );
								}
							}
						)
					)
				)
			] );
		} // end withSelect
	), // end edit

	save: function save( props ) {
		return null;
	}

});


//------------------------------------\\
//--- Jobboard dashboard shortcode ---\\
//------------------------------------\\

wp.blocks.registerBlockType( 'jb-block/jb-jobs-dashboard', {
	title: wp.i18n.__( 'Jobs dashboard', 'jobboardwp' ),
	description: wp.i18n.__( 'Displaying jobs dashboard', 'jobboardwp' ),
	icon: 'dashboard',
	category: 'jb-blocks',
	attributes: {
		content: {
			source: 'html',
			selector: 'p'
		}
	},

	edit: function(props) {
		var content = props.attributes.content;

		if ( content === undefined ) {
			props.setAttributes({content: '[jb_jobs_dashboard]'});
		}

		return [
			wp.element.createElement(
				"div",
				{
					className: 'jb-job-dashboard-wrapper'
				},
				wp.i18n.__( 'Jobs dashboard', 'jobboardwp' )
			)
		]

	},

	save: function(props) {
		return null;
	}

});


//------------------------------------------\\
//--- Jobboard categories list shortcode ---\\
//------------------------------------------\\

wp.blocks.registerBlockType( 'jb-block/jb-jobs-categories-list', {
	title: wp.i18n.__( 'Jobs categories list', 'jobboardwp' ),
	description: wp.i18n.__( 'Displaying jobs categories list', 'jobboardwp' ),
	icon: 'editor-ul',
	category: 'jb-blocks',
	attributes: {
		content: {
			source: 'html',
			selector: 'p'
		}
	},

	edit: function(props) {
		var content = props.attributes.content;

		if ( content === undefined ) {
			props.setAttributes({content: '[jb_job_categories_list]'});
		}

		return [
			wp.element.createElement(
				"div",
				{
					className: 'jb-job-categories-list-wrapper'
				},
				wp.i18n.__( 'Jobs categories list', 'jobboardwp' )
			)
		]

	},

	save: function(props) {
		return null;
	}

});


//----------------------------------\\
//------- Jobboard jobs list -------\\
//----------------------------------\\

wp.blocks.registerBlockType( 'jb-block/jb-jobs-list', {
	title: wp.i18n.__( 'Jobs list', 'jobboardwp' ),
	description: wp.i18n.__( 'Displaying jobs list', 'jobboardwp' ),
	icon: 'editor-ul',
	category: 'jb-blocks',
	attributes: {
		user_id: {
			type: 'select'
		},
		per_page: {
			type: 'string'
		},
		no_logo: {
			type: 'boolean',
			default: default_hide_logo
		},
		hide_filled: {
			type: 'boolean',
			default: default_hide_filled
		},
		hide_expired: {
			type: 'boolean',
			default: default_hide_expired
		},
		hide_search: {
			type: 'boolean',
			default: default_hide_search
		},
		hide_location_search: {
			type: 'boolean',
			default: default_hide_search_location
		},
		hide_filters: {
			type: 'boolean',
			default: default_hide_filters
		},
		hide_job_types: {
			type: 'boolean',
			default: default_hide_job_types
		},
		no_jobs_text: {
			type: 'string'
		},
		no_job_search_text: {
			type: 'string'
		},
		load_more_text: {
			type: 'string'
		},
		category: {
			type: 'select'
		},
		type: {
			type: 'select'
		},
		orderby: {
			type: 'select',
			default: 'date'
		},
		order: {
			type: 'select',
			default: 'DESC'
		},
		filled_only: {
			type: 'boolean',
			default: false
		},
		content: {
			source: 'html',
			selector: 'p'
		}
	},

	edit: wp.data.withSelect( function( select ) {
		return {
			users: select( 'core' ).getEntityRecords( 'root', 'user', {
				per_page: -1,
				_fields: ['id', 'name']
			}),
			types: select( 'core' ).getEntityRecords( 'taxonomy', 'jb-job-type', {
				_fields: ['id', 'name']
			}),
			categories: select( 'core' ).getEntityRecords( 'taxonomy', 'jb-job-category', {
				_fields: ['id', 'name']
			})
		};
	} )( function( props ) {
			var users                = props.users,
				user_id              = props.attributes.user_id,
				users_data           = [ { id: '', name: '' } ],
				className            = props.className,
				per_page             = props.attributes.per_page,
				no_logo              = props.attributes.no_logo,
				hide_filled          = props.attributes.hide_filled,
				hide_expired         = props.attributes.hide_expired,
				hide_search          = props.attributes.hide_search,
				hide_location_search = props.attributes.hide_location_search,
				hide_filters         = props.attributes.hide_filters,
				hide_job_types       = props.attributes.hide_job_types,
				no_jobs_text         = props.attributes.no_jobs_text,
				no_job_search_text   = props.attributes.no_job_search_text,
				load_more_text       = props.attributes.load_more_text,
				orderby              = props.attributes.orderby,
				order                = props.attributes.order,
				orderby_opt          = [
					{label: wp.i18n.__( 'Date', 'jobboardwp' ), value: 'date'},
					{label: wp.i18n.__( 'Title', 'jobboardwp' ), value: 'title'}
				],
				order_opt            = [
					{label: wp.i18n.__( 'Ascending', 'jobboardwp' ), value: 'ASC'},
					{label: wp.i18n.__( 'Descending', 'jobboardwp' ), value: 'DESC'}
				],
				types                = props.types,
				type                 = props.attributes.type,
				types_data           = [ { id: '', name: '' } ],
				categories           = props.categories,
				category             = props.attributes.category,
				categories_data      = [ { id: '', name: '' } ],
				filled_only          = props.attributes.filled_only,
				content              = props.attributes.content,
				category_hide        = '-hide',
				type_hide            = '-hide';

			if ( users !== null ) {
				users_data = users_data.concat(users);
			}

			if ( types !== null ) {
				types_data = types_data.concat(types);
				if ( types.length !== 0 ) {
					type_hide = '';
				}
			}

			if ( categories !== null ) {
				categories_data = categories_data.concat(categories);
				if ( categories.length !== 0 ) {
					type_hide = '';
				}
			}

			function get_option( data, type ) {

				var option = [];

				if ( type === 'user' ) {
					data.map( function( user ) {
						option.push(
							{
								label: user.name,
								value: user.id
							}
						);
					});
				} else if ( type === 'type' ) {
					data.map( function( type ) {
						option.push(
							{
								label: type.name,
								value: type.id
							}
						);
					});
				} else if ( type === 'category' ) {
					data.map( function( category ) {
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

			function jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only ) {
				var shortcode = '[jb_jobs';

				if ( user_id !== undefined && user_id !== '' ) {
					shortcode = shortcode + ' employer-id="' + user_id + '"';
				}

				if ( per_page !== undefined && per_page !== '' ) {
					shortcode = shortcode + ' per-page="' + per_page + '"';
				}

				if ( no_logo !== false ) {
					shortcode = shortcode + ' no-logo="' + 1 + '"';
				} else {
					shortcode = shortcode + ' no-logo="' + 0 + '"';
				}

				if ( hide_filled !== false ) {
					shortcode = shortcode + ' hide-filled="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide-filled="' + 0 + '"';
				}

				if ( hide_expired !== false ) {
					shortcode = shortcode + ' hide-expired="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide-expired="' + 0 + '"';
				}

				if ( hide_search !== false ) {
					shortcode = shortcode + ' hide-search="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide-search="' + 0 + '"';
				}

				if ( hide_location_search !== false ) {
					shortcode = shortcode + ' hide-location-search="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide-location-search="' + 0 + '"';
				}

				if ( hide_filters !== false ) {
					shortcode = shortcode + ' hide-filters="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide-filters="' + 0 + '"';
				}

				if ( hide_job_types !== false ) {
					shortcode = shortcode + ' hide-job-types="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide-job-types="' + 0 + '"';
				}

				if ( no_jobs_text !== undefined && no_jobs_text !== '' ) {
					shortcode = shortcode + ' no-jobs-text="' + no_jobs_text + '"';
				}

				if ( no_job_search_text !== undefined && no_job_search_text !== '' ) {
					shortcode = shortcode + ' no-jobs-search-text="' + no_job_search_text + '"';
				}

				if ( load_more_text !== undefined && load_more_text !== '' ) {
					shortcode = shortcode + ' load-more-text="' + load_more_text + '"';
				}

				if ( type !== undefined && type !== '' ) {
					shortcode = shortcode + ' type="' + type + '"';
				}

				if ( category !== undefined && category !== '' ) {
					shortcode = shortcode + ' category="' + category + '"';
				}

				if ( orderby !== undefined ) {
					shortcode = shortcode + ' orderby="' + orderby + '"';
				}

				if ( order !== undefined ) {
					shortcode = shortcode + ' order="' + order + '"';
				}

				if ( filled_only !== false ) {
					shortcode = shortcode + ' filled-only="' + 1 + '"';
				} else {
					shortcode = shortcode + ' filled-only="' + 0 + '"';
				}

				shortcode = shortcode + ']';
				props.setAttributes({ content: shortcode });
			}

			if ( ! users_data || ! types_data || ! categories_data ) {
				return wp.element.createElement(
					'p',
					{
						className: className
					},
					wp.element.createElement(
						wp.components.Spinner,
						null
					),
					wp.i18n.__( 'Loading data', 'jobboardwp' )
				);
			}

			if ( 0 === users_data.length || 0 === types_data.length || 0 === categories_data.length ) {
				return wp.element.createElement(
					'p',
					null,
					wp.i18n.__( 'No data', 'jobboardwp' )
				);
			}

			if ( content === undefined ) {
				props.setAttributes({ content: '[jb_jobs]' });
			}

			var get_category = get_option( categories_data, 'category' );
			var get_users    = get_option( users_data, 'user' );
			var get_types    = get_option( types_data, 'type' );

			return [
				wp.element.createElement(
					"div",
					{
						className: 'jb-jobs-list-wrapper'
					},
					wp.i18n.__( 'Jobs list', 'jobboardwp' )
				),
				wp.element.createElement(
					wp.blockEditor.InspectorControls,
					{},
					wp.element.createElement(
						wp.components.PanelBody,
						{
							title: wp.i18n.__( 'Jobs list', 'jobboardwp' )
						},
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__( 'Select employer', 'jobboardwp' ),
								className: 'jb_select_employer',
								value: props.attributes.user_id,
								options: get_users,
								onChange: function onChange( value ) {
									props.setAttributes( { user_id: value } );
									jbShortcode( value, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.TextControl,
							{
								label: wp.i18n.__( 'Per page', 'jobboardwp' ),
								className: 'jb_per_page',
								type: 'number',
								value: props.attributes.per_page,
								onChange: function onChange( value ) {
									props.setAttributes( { per_page: value } );
									jbShortcode( user_id, value, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__( 'Hide logo', 'jobboardwp' ),
								className: 'jb_no_logo',
								checked: props.attributes.no_logo,
								onChange: function onChange( value ) {
									props.setAttributes( { no_logo: value } );
									jbShortcode( user_id, per_page, value, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__( 'Hide filled', 'jobboardwp' ),
								className: 'jb_hide_filled',
								checked: props.attributes.hide_filled,
								onChange: function onChange( value ) {
									props.setAttributes( { hide_filled: value } );
									jbShortcode( user_id, per_page, no_logo, value, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__( 'Hide expired', 'jobboardwp' ),
								className: 'jb_hide_expired',
								checked: props.attributes.hide_expired,
								onChange: function onChange( value ) {
									props.setAttributes( { hide_expired: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, value, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__( 'Hide search', 'jobboardwp' ),
								className: 'jb_hide_search',
								checked: props.attributes.hide_search,
								onChange: function onChange( value ) {
									props.setAttributes( { hide_search: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, value, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__( 'Hide location search', 'jobboardwp' ),
								className: 'jb_hide_location_search',
								checked: props.attributes.hide_location_search,
								onChange: function onChange( value ) {
									props.setAttributes( { hide_location_search: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, value, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__( 'Hide filters', 'jobboardwp' ),
								className: 'jb_hide_filters',
								checked: props.attributes.hide_filters,
								onChange: function onChange( value ) {
									props.setAttributes( { hide_filters: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, value, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__( 'Hide job types', 'jobboardwp' ),
								className: 'jb_hide_job_types',
								checked: props.attributes.hide_job_types,
								onChange: function onChange( value ) {
									props.setAttributes( { hide_job_types: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, value, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.TextControl,
							{
								label: wp.i18n.__( 'No jobs text', 'jobboardwp' ),
								className: 'jb_no_jobs_text',
								type: 'text',
								value: props.attributes.no_jobs_text,
								onChange: function onChange( value ) {
									props.setAttributes( { no_jobs_text: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, value, no_job_search_text, load_more_text, category, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.TextControl,
							{
								label: wp.i18n.__( 'No job search text', 'jobboardwp' ),
								className: 'jb_no_job_search_text',
								type: 'text',
								value: props.attributes.no_job_search_text,
								onChange: function onChange( value ) {
									props.setAttributes( { no_job_search_text: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, value, load_more_text, category, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.TextControl,
							{
								label: wp.i18n.__( 'Load more text', 'jobboardwp' ),
								className: 'jb_load_more_text',
								type: 'text',
								value: props.attributes.load_more_text,
								onChange: function onChange( value ) {
									props.setAttributes( { load_more_text: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, value, category, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__( 'Select category', 'jobboardwp' ),
								className: 'jb_select_category' + category_hide,
								value: props.attributes.category,
								options: get_category,
								onChange: function onChange( value ) {
									props.setAttributes( { category: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, value, type, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__( 'Select type', 'jobboardwp' ),
								className: 'jb_select_type' + type_hide,
								value: props.attributes.type,
								options: get_types,
								onChange: function onChange( value ) {
									props.setAttributes( { type: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, value, orderby, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__( 'Select order by', 'jobboardwp' ),
								className: 'jb_select_orderby',
								value: props.attributes.orderby,
								options: orderby_opt,
								onChange: function onChange( value ) {
									props.setAttributes( { orderby: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, value, order, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__( 'Select order', 'jobboardwp' ),
								className: 'jb_select_order',
								value: props.attributes.order,
								options: order_opt,
								onChange: function onChange( value ) {
									props.setAttributes( { order: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, value, filled_only );
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__( 'Filled only', 'jobboardwp' ),
								className: 'jb_filled_only',
								checked: props.attributes.filled_only,
								onChange: function onChange( value ) {
									props.setAttributes( { filled_only: value } );
									jbShortcode( user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, value );
								}
							}
						)
					)
				)
			]
		} // end withSelect
	), // end edit

	save: function save( props ) {
		return null;
	}
});


//----------------------------------\\
//------ Jobboard recent jobs ------\\
//----------------------------------\\

wp.blocks.registerBlockType( 'jb-block/jb-recent-jobs', {
	title: wp.i18n.__( 'Recent Jobs', 'jobboardwp' ),
	description: wp.i18n.__( 'Displaying recent jobs', 'jobboardwp' ),
	icon: 'editor-ul',
	category: 'jb-blocks',
	attributes: {
		number: {
			type: 'string',
			default: 5
		},
		no_logo: {
			type: 'boolean',
			default: default_hide_logo
		},
		hide_filled: {
			type: 'boolean',
			default: default_hide_filled
		},
		no_job_types: {
			type: 'boolean',
			default: default_hide_job_types
		},
		category: {
			type: 'select'
		},
		type: {
			type: 'select'
		},
		orderby: {
			type: 'select',
			default: 'date'
		},
		remote_only: {
			type: 'boolean',
			default: false
		}
	},

	edit: wp.data.withSelect( function( select ) {
		return {
			types: select( 'core' ).getEntityRecords( 'taxonomy', 'jb-job-type', {
				_fields: ['id', 'name']
			}),
			categories: select( 'core' ).getEntityRecords( 'taxonomy', 'jb-job-category', {
				_fields: ['id', 'name']
			})
		};
	} )( function( props ) {
			var className       = props.className,
				number          = props.attributes.number,
				no_logo         = props.attributes.no_logo,
				no_job_types    = props.attributes.no_job_types,
				hide_filled     = props.attributes.hide_filled,
				orderby         = props.attributes.orderby,
				orderby_opt     = [
					{label: wp.i18n.__( 'Posting date', 'jobboardwp' ), value: 'date'},
					{label: wp.i18n.__( 'Expiry date', 'jobboardwp' ), value: 'expiry_date'}
				],
				types           = props.types,
				type            = props.attributes.type,
				types_data      = [ { id: '', name: '' } ],
				categories      = props.categories,
				category        = props.attributes.category,
				categories_data = [ { id: '', name: '' } ],
				remote_only     = props.attributes.remote_only,
				content         = props.attributes.content,
				category_hide   = '-hide',
				type_hide       = '-hide';

			if ( types !== null ) {
				types_data = types_data.concat(types);
				if ( types.length !== 0 ) {
					type_hide = '';
				}
			}

			if ( categories !== null ) {
				categories_data = categories_data.concat(categories);
				if ( categories.length !== 0 ) {
					category_hide = '';
				}
			}

			jQuery('.jb-jobs-widget').addClass('jb-ui-s');

			function get_option( data, type ) {

				var option = [];

				if ( type === 'type' ) {
					data.map( function( type ) {
						option.push(
							{
								label: type.name,
								value: type.id
							}
						);
					});
				} else if ( type === 'category' ) {
					data.map( function( category ) {
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

			function jbShortcode( number, category, type, remote_only, orderby, hide_filled, no_logo, no_job_types ) {
				var shortcode = '[jb_recent_jobs';

				if ( number !== undefined && number !== '' ) {
					shortcode = shortcode + ' number="' + number + '"';
				} else  {
					shortcode = shortcode + ' number="' + 5 + '"';
				}

				if ( no_logo !== false ) {
					shortcode = shortcode + ' no_logo="' + 1 + '"';
				} else {
					shortcode = shortcode + ' no_logo="' + 0 + '"';
				}

				if ( hide_filled !== false ) {
					shortcode = shortcode + ' hide_filled="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide_filled="' + 0 + '"';
				}

				if ( no_job_types !== false ) {
					shortcode = shortcode + ' no_job_types="' + 1 + '"';
				} else {
					shortcode = shortcode + ' no_job_types="' + 0 + '"';
				}

				if ( type !== undefined && type !== '' ) {
					shortcode = shortcode + ' type="' + type + '"';
				}

				if ( category !== undefined && category !== '' ) {
					shortcode = shortcode + ' category="' + category + '"';
				}

				if ( orderby !== undefined ) {
					shortcode = shortcode + ' orderby="' + orderby + '"';
				}

				if ( remote_only !== false ) {
					shortcode = shortcode + ' remote_only="' + 1 + '"';
				} else {
					shortcode = shortcode + ' remote_only="' + 0 + '"';
				}

				shortcode = shortcode + ']';

				props.setAttributes({ content: shortcode });

				jQuery('.jb-jobs-widget').addClass('jb-ui-s');
			}

			if ( ! types_data || ! categories_data ) {
				return wp.element.createElement(
					'p',
					{
						className: className
					},
					wp.element.createElement(
						wp.components.Spinner,
						null
					),
					wp.i18n.__( 'Loading data', 'jobboardwp' )
				);
			}

			if ( 0 === types_data.length || 0 === categories_data.length ) {
				return wp.element.createElement(
					'p',
					null,
					wp.i18n.__( 'No data', 'jobboardwp' )
				);
			}

			if ( content === undefined ) {
				props.setAttributes({ content: '[jb_recent_jobs]' });
			}

			var get_category = get_option( categories_data, 'category' );
			var get_types    = get_option( types_data, 'type' );

			return wp.element.createElement('div', {}, [
				wp.element.createElement( wp.components.ServerSideRender, {
					block: 'jb-block/jb-recent-jobs',
					attributes: props.attributes
				} ),
				wp.element.createElement(
					wp.blockEditor.InspectorControls,
					{},
					wp.element.createElement(
						wp.components.PanelBody,
						{
							title: wp.i18n.__( 'Recent jobs', 'jobboardwp' )
						},
						wp.element.createElement(
							wp.components.TextControl,
							{
								label: wp.i18n.__( 'Number', 'jobboardwp' ),
								className: 'jb_number',
								type: 'number',
								value: props.attributes.number,
								onChange: function onChange( value ) {
									props.setAttributes( { number: value } );
									jbShortcode( value, category, type, remote_only, orderby, hide_filled, no_logo, no_job_types );
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__( 'Hide logo', 'jobboardwp' ),
								className: 'jb_no_logo',
								checked: props.attributes.no_logo,
								onChange: function onChange( value ) {
									props.setAttributes( { no_logo: value } );
									jbShortcode( number, category, type, remote_only, orderby, hide_filled, value, no_job_types );
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__( 'Hide filled', 'jobboardwp' ),
								className: 'jb_hide_filled',
								checked: props.attributes.hide_filled,
								onChange: function onChange( value ) {
									props.setAttributes( { hide_filled: value } );
									jbShortcode( number, category, type, remote_only, orderby, value, no_logo, no_job_types );
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__( 'Hide job types', 'jobboardwp' ),
								className: 'jb_no_job_types',
								checked: props.attributes.no_job_types,
								onChange: function onChange( value ) {
									props.setAttributes( { no_job_types: value } );
									jbShortcode( number, category, type, remote_only, orderby, hide_filled, no_logo, value );
								}
							}
						),
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__( 'Select category', 'jobboardwp' ),
								className: 'jb_select_category' + category_hide,
								value: props.attributes.category,
								options: get_category,
								onChange: function onChange( value ) {
									props.setAttributes( { category: value } );
									jbShortcode( number, value, type, remote_only, orderby, hide_filled, no_logo, no_job_types );
								}
							}
						),
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__( 'Select type', 'jobboardwp' ),
								className: 'jb_select_type' + type_hide,
								value: props.attributes.type,
								options: get_types,
								onChange: function onChange( value ) {
									props.setAttributes( { type: value } );
									jbShortcode( number, category, value, remote_only, orderby, hide_filled, no_logo, no_job_types );
								}
							}
						),
						wp.element.createElement(
							wp.components.SelectControl,
							{
								label: wp.i18n.__( 'Select order by', 'jobboardwp' ),
								className: 'jb_select_orderby',
								value: props.attributes.orderby,
								options: orderby_opt,
								onChange: function onChange( value ) {
									props.setAttributes( { orderby: value } );
									jbShortcode( number, category, type, remote_only, value, hide_filled, no_logo, no_job_types );
								}
							}
						),
						wp.element.createElement(
							wp.components.ToggleControl,
							{
								label: wp.i18n.__( 'Remote only', 'jobboardwp' ),
								className: 'jb_remote_only',
								checked: props.attributes.remote_only,
								onChange: function onChange( value ) {
									props.setAttributes( { remote_only: value } );
									jbShortcode( number, category, type, value, orderby, hide_filled, no_logo, no_job_types );
								}
							}
						)
					)
				)
			] );
		} // end withSelect
	), // end edit
	save: function save( props ) {
		return null;
	}
});
