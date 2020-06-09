<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<script type="text/template" id="tmpl-jb-jobs-list-line">
	<# if ( data.length > 0 ) { #>
		<# _.each( data, function( job, key, list ) { #>
			<div class="jb-job-list-row<# if ( ! job.logo ) { #> jb-job-list-no-logo<# } #>">
				<# if ( job.logo ) { #>
					<div class="jb-job-logo">
						{{{job.logo}}}
					</div>
				<# } #>
				<div class="jb-row-data">
					<div class="job_title">
						<a href="{{{job.permalink}}}">{{{job.title}}}</a>
						<# if ( data.length > 0 ) { #>
							<div class="jb-job-types">
								<# _.each( job.types, function( type, t_key, t_list ) { #>
									<div class="jb-job-type" style="color:{{{type.color}}};background:{{{type.bg_color}}};">
										{{{type.name}}}
									</div>
								<# }); #>
							</div>
						<# } #>
					</div>
					<div class="jb-row-info">
						<div class="jb-row-left-side">
							<div class="company">
								<i class="far fa-building"></i>&nbsp;
								<span title="{{{job.company.tagline}}}">{{{job.company.name}}}</span>
							</div>
							<# if ( job.location ) { #>
							<div class="location">
								<i class="fas fa-map-marker-alt"></i>&nbsp;
								{{{job.location}}}
							</div>
							<# } #>

							<# if ( job.expires ) { #>
								<div class="expires" title="<?php esc_attr_e( 'Expires', 'jobboardwp' ) ?>">
									<i class="far fa-calendar-alt"></i>&nbsp;<?php _e( 'Closing on', 'jobboardwp' ) ?>&nbsp;{{{job.expires}}}
								</div>
							<# } #>
						</div>
						<div class="jb-row-right-side">
							<div class="date" title="<?php esc_attr_e( 'Posted', 'jobboardwp' ) ?>">{{{job.date}}}</div>
						</div>
					</div>
				</div>
			</div>
		<# }); #>
	<# } #>
</script>

<div class="jb-jobs-wrapper"></div>

<div class="jb-jobs-pagination-box">
	<a href="javascript:void(0);" class="jb-load-more-jobs">
		<?php _e( 'Load more jobs','jobboardwp' ) ?>
	</a>
</div>