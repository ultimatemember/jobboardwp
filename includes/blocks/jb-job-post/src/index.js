import { registerBlockType } from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps } from '@wordpress/block-editor';
import jQuery from 'jquery';

registerBlockType('jb-block/jb-job-post', {
	edit: function (props) {
		jQuery('#jb-job-preview, #jb-job-draft, #jb_company_logo_plupload').attr('disabled', 'disabled');
		const blockProps = useBlockProps();
		return (
			<div {...blockProps}>
				<ServerSideRender block="jb-block/jb-job-post" />
			</div>
		);
	},
	save: function () {
		return null;
	}
});
