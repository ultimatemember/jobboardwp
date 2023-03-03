wp.blocks.registerBlockType('jb-block/jb-job-post', {
	edit: function (props) {
		jQuery('#jb-job-preview, #jb-job-draft, #jb_company_logo_plupload').attr('disabled', 'disabled');
		var useBlockProps = wp.blockEditor.useBlockProps;
		var blockProps = useBlockProps();
		return wp.element.createElement('div', blockProps, [
			wp.element.createElement(wp.components.ServerSideRender, {
				block: 'jb-block/jb-job-post'
			})
		]);

	},

	save: function (props) {
		return null;
	}

});
