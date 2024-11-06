import { useSelect } from '@wordpress/data';
import { PanelBody, SelectControl, TextControl, ToggleControl, Spinner } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { registerBlockType } from '@wordpress/blocks';
import { useMemo, useCallback } from '@wordpress/element';

registerBlockType('jb-block/jb-recent-jobs', {
	edit: (props) => {
		const blockProps = useBlockProps();
		const { attributes, setAttributes } = props;
		const { number, no_logo, hide_filled, no_job_types, orderby, type, category, remote_only } = attributes;

		const types = useSelect((select) => select('core').getEntityRecords('taxonomy', 'jb-job-type', { per_page: -1, _fields: ['id', 'name'] }), []);
		const categories = useSelect((select) => select('core').getEntityRecords('taxonomy', 'jb-job-category', { per_page: -1, _fields: ['id', 'name'] }), []);

		const options = useMemo(() => ({
			types: types ? types.map(({ id, name }) => ({ label: name, value: id })) : [],
			categories: categories ? categories.map(({ id, name }) => ({ label: name, value: id })) : []
		}), [types, categories]);

		const updateAttribute = useCallback((attribute, value) => setAttributes({ [attribute]: value }), [setAttributes]);

		if (!types || !categories) {
			return (
				<p>
					<Spinner />
					{wp.i18n.__('Loading...', 'jobboardwp')}
				</p>
			);
		}

		return (
			<div {...blockProps}>
				<ServerSideRender block="jb-block/jb-recent-jobs" attributes={attributes} />
				<InspectorControls>
					<PanelBody title={wp.i18n.__('Recent jobs', 'jobboardwp')}>
						<TextControl
							label={wp.i18n.__('Number', 'jobboardwp')}
							type="number"
							min={1}
							value={number}
							onChange={(value) => updateAttribute('number', value || 1)}
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
							label={wp.i18n.__('Hide job types', 'jobboardwp')}
							checked={no_job_types}
							onChange={(value) => updateAttribute('no_job_types', value)}
						/>
						<SelectControl
							label={wp.i18n.__('Select category', 'jobboardwp')}
							value={Array.isArray(category) ? category : []} // Ensure it's an array
							options={options.categories}
							multiple
							onChange={(value) => updateAttribute('category', Array.isArray(value) ? value : [value])}
						/>
						<SelectControl
							label={wp.i18n.__('Select type', 'jobboardwp')}
							value={Array.isArray(type) ? type : []} // Ensure it's an array
							options={options.types}
							multiple
							onChange={(value) => updateAttribute('type', Array.isArray(value) ? value : [value])}
						/>
						<SelectControl
							label={wp.i18n.__('Select order by', 'jobboardwp')}
							value={orderby}
							options={[
								{ label: wp.i18n.__('Posting date', 'jobboardwp'), value: 'date' },
								{ label: wp.i18n.__('Expiry date', 'jobboardwp'), value: 'expiry_date' }
							]}
							onChange={(value) => updateAttribute('orderby', value)}
						/>
						<ToggleControl
							label={wp.i18n.__('Remote only', 'jobboardwp')}
							checked={remote_only}
							onChange={(value) => updateAttribute('remote_only', value)}
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
			jQuery(mutation.addedNodes).find('.jb-jobs-widget').each(function() {
				const wrapper = document.querySelector('.jb-jobs-widget');

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
