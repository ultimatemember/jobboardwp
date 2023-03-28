import { useSelect } from '@wordpress/data';
import { PanelBody, SelectControl, TextControl, ToggleControl, Spinner } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { registerBlockType } from '@wordpress/blocks';

registerBlockType('jb-block/jb-jobs-list', {
	edit: (function (props) {
			const blockProps = useBlockProps();
			const users = useSelect((select) => {
				return select('core').getEntityRecords('root', 'user', {
					per_page: -1,
					_fields: ['id', 'name']
				});
			});
			const types = useSelect((select) => {
				return select('core').getEntityRecords('taxonomy', 'jb-job-type', {
					per_page: -1,
					_fields: ['id', 'name']
				});
			});
			const categories = useSelect((select) => {
				return select('core').getEntityRecords('taxonomy', 'jb-job-category', {
					per_page: -1,
					_fields: ['id', 'name']
				});
			});
			let user_id = props.attributes.user_id,
				users_data = [{id: '', name: ''}],
				per_page = props.attributes.per_page,
				no_logo = props.attributes.no_logo,
				hide_filled = props.attributes.hide_filled,
				hide_expired = props.attributes.hide_expired,
				hide_search = props.attributes.hide_search,
				hide_location_search = props.attributes.hide_location_search,
				hide_filters = props.attributes.hide_filters,
				hide_job_types = props.attributes.hide_job_types,
				no_jobs_text = props.attributes.no_jobs_text,
				no_job_search_text = props.attributes.no_job_search_text,
				load_more_text = props.attributes.load_more_text,
				orderby = props.attributes.orderby,
				order = props.attributes.order,
				orderby_opt = [
					{label: wp.i18n.__('Date', 'jobboardwp'), value: 'date'},
					{label: wp.i18n.__('Title', 'jobboardwp'), value: 'title'}
				],
				order_opt = [
					{label: wp.i18n.__('Ascending', 'jobboardwp'), value: 'ASC'},
					{label: wp.i18n.__('Descending', 'jobboardwp'), value: 'DESC'}
				],
				type = props.attributes.type,
				types_data = [],
				// categories = props.categories,
				category = props.attributes.category,
				categories_data = [],
				filled_only = props.attributes.filled_only,
				category_hide = '-hide',
				type_hide = '-hide';

			if ('' === category) {
				category = [];
			}
			if ('' === type) {
				type = [];
			}

			if (users !== null) {
				users_data = users_data.concat(users);
			}

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

				let option = [];

				if (type === 'user') {
					data.map(function (user) {
						option.push(
							{
								label: user.name,
								value: user.id
							}
						);
					});
				} else if (type === 'type') {
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

			function jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only) {
				let shortcode = '[jb_jobs';

				if (user_id !== undefined && user_id !== '') {
					shortcode = shortcode + ' employer-id="' + user_id + '"';
				}

				if (per_page !== undefined && per_page !== '') {
					shortcode = shortcode + ' per-page="' + per_page + '"';
				}

				if (no_logo === true) {
					shortcode = shortcode + ' no-logo="' + 1 + '"';
				} else {
					shortcode = shortcode + ' no-logo="' + 0 + '"';
				}

				if (hide_filled === true) {
					shortcode = shortcode + ' hide-filled="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide-filled="' + 0 + '"';
				}

				if (hide_expired === true) {
					shortcode = shortcode + ' hide-expired="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide-expired="' + 0 + '"';
				}

				if (hide_search === true) {
					shortcode = shortcode + ' hide-search="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide-search="' + 0 + '"';
				}

				if (hide_location_search === true) {
					shortcode = shortcode + ' hide-location-search="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide-location-search="' + 0 + '"';
				}

				if (hide_filters === true) {
					shortcode = shortcode + ' hide-filters="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide-filters="' + 0 + '"';
				}

				if (hide_job_types === true) {
					shortcode = shortcode + ' hide-job-types="' + 1 + '"';
				} else {
					shortcode = shortcode + ' hide-job-types="' + 0 + '"';
				}

				if (no_jobs_text !== undefined && no_jobs_text !== '') {
					shortcode = shortcode + ' no-jobs-text="' + no_jobs_text + '"';
				}

				if (no_job_search_text !== undefined && no_job_search_text !== '') {
					shortcode = shortcode + ' no-jobs-search-text="' + no_job_search_text + '"';
				}

				if (load_more_text !== undefined && load_more_text !== '') {
					shortcode = shortcode + ' load-more-text="' + load_more_text + '"';
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

				if (order !== undefined) {
					shortcode = shortcode + ' order="' + order + '"';
				}

				if (filled_only === true) {
					shortcode = shortcode + ' filled-only="' + 1 + '"';
				} else {
					shortcode = shortcode + ' filled-only="' + 0 + '"';
				}

				shortcode = shortcode + ']';
				return shortcode;
			}

			if (!users_data || !types_data || !categories_data) {
				return (
					<p>
						<Spinner />
						{wp.i18n.__('Loading...', 'jobboardwp')}
					</p>
				);
			}

			if (0 === users_data.length || 0 === types_data.length || 0 === categories_data.length) {
				return 'No data.';
			}

			let get_category = get_option(categories_data, 'category');
			let get_users = get_option(users_data, 'user');
			let get_types = get_option(types_data, 'type');

			return (
				<div {...blockProps}>
					<ServerSideRender block="jb-block/jb-jobs-list" attributes={props.attributes} />
					<InspectorControls>
						<PanelBody title={wp.i18n.__('Jobs list', 'jobboardwp')}>
							<SelectControl
								label={wp.i18n.__('Select employer', 'jobboardwp')}
								className="jb_select_employer"
								value={props.attributes.user_id}
								options={get_users}
								style={{height: '35px', lineHeight: '20px', padding: '0 7px'}}
								onChange={(value) => {
									props.setAttributes({user_id: value});
									jbShortcode(value, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only);
								}}
							/>
							<TextControl
								label={wp.i18n.__('Per page', 'jobboardwp')}
								className="jb_per_page"
								type="number"
								min={ 1 }
								value={props.attributes.per_page}
								onChange={(value) => {
									if (value === '') {
										value = 1;
									}
									props.setAttributes({per_page: value});
									jbShortcode(user_id, value, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only);
								}}
							/>
							<ToggleControl
								label={wp.i18n.__('Hide logo', 'jobboardwp')}
								className="jb_no_logo"
								checked={props.attributes.no_logo}
								onChange={(value) => {
									props.setAttributes({no_logo: value});
									jbShortcode(user_id, per_page, value, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only);
								}}
							/>
							<ToggleControl
								label={wp.i18n.__('Hide filled', 'jobboardwp')}
								className="jb_hide_filled"
								checked={props.attributes.hide_filled}
								onChange={(value) => {
									props.setAttributes({hide_filled: value});
									jbShortcode(user_id, per_page, no_logo, value, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only);
								}}
							/>
							<ToggleControl
								label={wp.i18n.__('Hide expired', 'jobboardwp')}
								className="jb_hide_expired"
								checked={props.attributes.hide_expired}
								onChange={(value) => {
									props.setAttributes({hide_expired: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, value, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only);
								}}
							/>
							<ToggleControl
								label={wp.i18n.__('Hide search', 'jobboardwp')}
								className="jb_hide_search"
								checked={props.attributes.hide_search}
								onChange={(value) => {
									props.setAttributes({hide_search: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, value, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only);
								}}
							/>
							<ToggleControl
								label={wp.i18n.__('Hide location search', 'jobboardwp')}
								className="jb_hide_location_search"
								checked={props.attributes.hide_location_search}
								onChange={(value) => {
									props.setAttributes({hide_location_search: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, value, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only);
								}}
							/>
							<ToggleControl
								label={wp.i18n.__('Hide filters', 'jobboardwp')}
								className="jb_hide_filters"
								checked={props.attributes.hide_filters}
								onChange={(value) => {
									props.setAttributes({hide_filters: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, value, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only);
								}}
							/>
							<ToggleControl
								label={wp.i18n.__('Hide job types', 'jobboardwp')}
								className="jb_hide_job_types"
								checked={props.attributes.hide_job_types}
								onChange={(value) => {
									props.setAttributes({hide_job_types: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, value, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, filled_only);
								}}
							/>
							<TextControl
								label={wp.i18n.__('No jobs text', 'jobboardwp')}
								className="jb_no_jobs_text"
								type="text"
								value={props.attributes.no_jobs_text}
								onChange={(value) => {
									props.setAttributes({no_jobs_text: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, value, no_job_search_text, load_more_text, category, type, orderby, order, filled_only);
								}}
							/>
							<TextControl
								label={wp.i18n.__('No job search text', 'jobboardwp')}
								className="jb_no_job_search_text"
								type="text"
								value={props.attributes.no_job_search_text}
								onChange={(value) => {
									props.setAttributes({no_job_search_text: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, value, load_more_text, category, type, orderby, order, filled_only);
								}}
							/>
							<TextControl
								label={wp.i18n.__('Load more text', 'jobboardwp')}
								className="jb_load_more_text"
								type="text"
								value={props.attributes.load_more_text}
								onChange={(value) => {
									props.setAttributes({load_more_text: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, value, category, type, orderby, order, filled_only);
								}}
							/>
							<SelectControl
								label={wp.i18n.__('Select category', 'jobboardwp')}
								className={'jb_select_category' + category_hide}
								value={category}
								options={get_category}
								multiple={true}
								suffix=' '
								style={{height: '35px', lineHeight: '20px', padding: '0 7px'}}
								onChange={(value) => {
									props.setAttributes({category: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, value, type, orderby, order, filled_only);
								}}
							/>
							<SelectControl
								label={wp.i18n.__('Select type', 'jobboardwp')}
								className="{'jb_select_type' + type_hide}"
								value={type}
								options={get_types}
								multiple={true}
								suffix=' '
								style={{height: '80px', overflow: 'auto'}}
								onChange={(value) => {
									props.setAttributes({type: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, value, orderby, order, filled_only);
								}}
							/>
							<SelectControl
								label={wp.i18n.__('Select order by', 'jobboardwp')}
								className='jb_select_orderby'
								value={props.attributes.orderby}
								options={orderby_opt}
								style={{height: '35px', lineHeight: '20px', padding: '0 7px'}}
								onChange={(value) => {
									props.setAttributes({orderby: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, value, order, filled_only);
								}}
							/>
							<SelectControl
								label={wp.i18n.__('Select order', 'jobboardwp')}
								className='jb_select_order'
								value={props.attributes.order}
								options={order_opt}
								style={{height: '35px', lineHeight: '20px', padding: '0 7px'}}
								onChange={(value) => {
									props.setAttributes({order: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, value, filled_only);
								}}
							/>
							<ToggleControl
								label={wp.i18n.__('Filled only', 'jobboardwp')}
								className="jb_filled_only"
								checked={props.attributes.filled_only}
								onChange={(value) => {
									props.setAttributes({filled_only: value});
									jbShortcode(user_id, per_page, no_logo, hide_filled, hide_expired, hide_search, hide_location_search, hide_filters, hide_job_types, no_jobs_text, no_job_search_text, load_more_text, category, type, orderby, order, value);
								}}
							/>
						</PanelBody>
					</InspectorControls>
				</div>
			);
		} // end withSelect
	), // end edit

	save: function save(props) {
		return null;
	}
});

jQuery(window).on( 'load', function($) {
	let observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {

			jQuery(mutation.addedNodes).find('.jb-jobs').each(function() {
				wp.JB.jobs_list.objects.wrapper = jQuery('.jb-jobs');
				if ( wp.JB.jobs_list.objects.wrapper.length ) {
					wp.JB.jobs_list.objects.wrapper.each( function () {
						wp.JB.jobs_list.ajax( jQuery(this) );
					});
				}
			});

			jQuery(mutation.addedNodes).find('.jb').each(function() {
				jb_responsive();
			});
		});
	});

	observer.observe(document, {attributes: false, childList: true, characterData: false, subtree:true});
});
