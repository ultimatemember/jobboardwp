<?php
/**
 * Template for the Jobs categories JS template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/js/job-categories-list.php
 *
 * @version 1.2.8
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<script type="text/template" id="tmpl-jb-job-categories-list">
	<# if ( data.length > 0 ) { #>
		<# _.each( data, function( category, key, list ) { #>
			<div class="jb-job-category-list-row" style="padding-left: {{{category.level * <?php /** @noinspection CssInvalidPropertyValueInspection */ ?>10}}}px;">
				<div class="jb-row-left-side">
					<div class="jb-job-category-title <# if ( category.class ) { #>subcat<# } #>">
						<a href="{{{category.permalink}}}">{{{category.name}}}</a>
					</div>
					<# if ( category.description ) { #>
						<div class="jb-job-category-description">
							{{{category.description}}}
						</div>
					<# } #>

					<div class="jb-responsive jb-ui-xs jb-job-category-count" title="<?php esc_attr_e( 'Jobs', 'jobboardwp' ); ?>">
						<?php esc_html_e( 'Jobs:', 'jobboardwp' ); ?> {{{category.count}}}
					</div>
				</div>
				<div class="jb-row-right-side">
					<div class="jb-responsive jb-ui-s jb-ui-m jb-ui-l jb-ui-xl jb-job-category-count">
						{{{category.count}}}
					</div>
				</div>
			</div>
		<# }); #>
	<# } #>
</script>
