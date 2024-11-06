import { useSelect } from '@wordpress/data';
import { PanelBody, SelectControl, TextControl, ToggleControl, Spinner } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { registerBlockType } from '@wordpress/blocks';
import { useMemo, useCallback } from '@wordpress/element';

registerBlockType('jb-block/jb-jobs-list', {
	edit: (props) => {
		const blockProps = useBlockProps();
		const { attributes, setAttributes } = props;
		const {
			user_id, per_page, no_logo, hide_filled, hide_expired, hide_search,
			hide_location_search, hide_filters, hide_job_types, no_jobs_text = '',
			no_job_search_text = '', load_more_text = '', orderby, order, type, category, filled_only
		} = attributes;

		const users = useSelect((select) => select('core').getEntityRecords('root', 'user', { per_page: -1, _fields: ['id', 'name', 'username'] }), []);
		const types = useSelect((select) => select('core').getEntityRecords('taxonomy', 'jb-job-type', { per_page: -1, _fields: ['id', 'name'] }), []);
		const categories = useSelect((select) => select('core').getEntityRecords('taxonomy', 'jb-job-category', { per_page: -1, _fields: ['id', 'name'] }), []);

		const options = useMemo(() => ({
			users: [{ label: '', value: '' }].concat(users ? users.map(({ id, name }) => ({ label: name, value: id })) : []),
			types: types ? types.map(({ id, name }) => ({ label: name, value: id })) : [],
			categories: categories ? categories.map(({ id, name }) => ({ label: name, value: id })) : []
		}), [users, types, categories]);

		const updateAttribute = useCallback((attribute, value) => {
			setAttributes({ [attribute]: value });
		}, [setAttributes]);

		if (!users || !types || !categories) {
			return (
				<p>
					<Spinner />
					{wp.i18n.__('Loading...', 'jobboardwp')}
				</p>
			);
		}

		return (
			<div {...blockProps}>
				<ServerSideRender block="jb-block/jb-jobs-list" attributes={attributes} />
				<InspectorControls>
					<PanelBody title={wp.i18n.__('Jobs list', 'jobboardwp')}>
						<SelectControl
							label={wp.i18n.__('Select employer', 'jobboardwp')}
							value={user_id}
							options={[
								{ label: wp.i18n.__('Select a User', 'jobboardwp'), value: '' },
								...(users ? users.map(user => ({
									label: user.name || user.username,
									value: user.id
								})) : [])
							]}
							onChange={(value) => updateAttribute('user_id', value)}
						/>
						<TextControl
							label={wp.i18n.__('Per page', 'jobboardwp')}
							type="number"
							min={1}
							value={per_page}
							onChange={(value) => updateAttribute('per_page', value || 1)}
						/>
						<ToggleControl
							label={wp.i18n.__('Hide logo', 'jobboardwp')}
							checked={no_logo}
							onChange={(value) => updateAttribute('no_logo', value)}
						/>
						<ToggleControl
							label={wp.i18n.__('Hide filled', 'jobboardwp')}
							checked={hide_filled}
							onChange={(value) => updateAttribute('hide_filled', value)}
						/>
						<ToggleControl
							label={wp.i18n.__('Hide expired', 'jobboardwp')}
							checked={hide_expired}
							onChange={(value) => updateAttribute('hide_expired', value)}
						/>
						<ToggleControl
							label={wp.i18n.__('Hide search', 'jobboardwp')}
							checked={hide_search}
							onChange={(value) => updateAttribute('hide_search', value)}
						/>
						<ToggleControl
							label={wp.i18n.__('Hide location search', 'jobboardwp')}
							checked={hide_location_search}
							onChange={(value) => updateAttribute('hide_location_search', value)}
						/>
						<ToggleControl
							label={wp.i18n.__('Hide filters', 'jobboardwp')}
							checked={hide_filters}
							onChange={(value) => updateAttribute('hide_filters', value)}
						/>
						<ToggleControl
							label={wp.i18n.__('Hide job types', 'jobboardwp')}
							checked={hide_job_types}
							onChange={(value) => updateAttribute('hide_job_types', value)}
						/>
						<TextControl
							label={wp.i18n.__('No jobs text', 'jobboardwp')}
							value={no_jobs_text}
							onChange={(value) => updateAttribute('no_jobs_text', value)}
						/>
						<TextControl
							label={wp.i18n.__('No job search text', 'jobboardwp')}
							value={no_job_search_text}
							onChange={(value) => updateAttribute('no_job_search_text', value)}
						/>
						<TextControl
							label={wp.i18n.__('Load more text', 'jobboardwp')}
							value={load_more_text}
							onChange={(value) => updateAttribute('load_more_text', value)}
						/>
						<SelectControl
							label={wp.i18n.__('Select category', 'jobboardwp')}
							value={category}
							options={options.categories}
							multiple={true}
							onChange={(value) => updateAttribute('category', value)}
						/>
						<SelectControl
							label={wp.i18n.__('Select type', 'jobboardwp')}
							value={type}
							options={options.types}
							multiple={true}
							onChange={(value) => updateAttribute('type', value)}
						/>
						<SelectControl
							label={wp.i18n.__('Select order by', 'jobboardwp')}
							value={orderby}
							options={[
								{ label: wp.i18n.__('Date', 'jobboardwp'), value: 'date' },
								{ label: wp.i18n.__('Title', 'jobboardwp'), value: 'title' }
							]}
							onChange={(value) => updateAttribute('orderby', value)}
						/>
						<SelectControl
							label={wp.i18n.__('Select order', 'jobboardwp')}
							value={order}
							options={[
								{ label: wp.i18n.__('Ascending', 'jobboardwp'), value: 'ASC' },
								{ label: wp.i18n.__('Descending', 'jobboardwp'), value: 'DESC' }
							]}
							onChange={(value) => updateAttribute('order', value)}
						/>
						<ToggleControl
							label={wp.i18n.__('Filled only', 'jobboardwp')}
							checked={filled_only}
							onChange={(value) => updateAttribute('filled_only', value)}
						/>
					</PanelBody>
				</InspectorControls>
			</div>
		);
	},
	save: () => null
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
				const wrapper = document.querySelector('.jb-jobs');

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
				const wrapper = document.querySelector('.jb-jobs');

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
