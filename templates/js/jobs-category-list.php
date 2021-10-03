<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<script type="text/template" id="tmpl-jb-jobs-category-list">
	<# if ( data.length > 0 ) { #>
		<# _.each( data, function( job, key, list ) { #>
			<div class="jb-row-data">
				<div class="jb-category-title <# if ( job.class ) { #>subcat<# } #>">
					{{{job.name}}}
					<# if ( job.description ) { #>
						<div>{{{job.description}}}</div>
					<# } #>
				</div>
				<div class="jb-category-count">
					{{{job.count}}}
				</div>
			</div>
		<# }); #>
	<# } #>
</script>
