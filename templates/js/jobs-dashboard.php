<?php
/**
 * Template for the Jobs dashboard JS template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/js/jobs-dashboard.php
 *
 * @version 1.2.8
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<script type="text/template" id="tmpl-jb-jobs-dashboard-line">
	<# if ( data.length > 0 ) { #>
		<# _.each( data, function( job, key, list ) { #>
			<div class="jb-job-dashboard-row<# if ( ! Object.keys( job.actions ).length ) { #> jb-job-dashboard-no-actions<# } #>" data-job-id="{{{job.id}}}">

				<div class="jb-row-data">

					<div class="job_title">
						<# if ( job.is_published ) { #>
							<a href="{{{job.permalink}}}">{{{job.title}}}</a>
						<# } else { #>
							{{{job.title}}}
						<# } #>
					</div>

					<div class="status jb-responsive jb-ui-m jb-ui-l jb-ui-xl">
						<span class="status-tag jb-status-{{{job.status}}}">
							{{{job.status_label}}}
						</span>
					</div>

					<div class="date jb-responsive jb-ui-m jb-ui-l jb-ui-xl">
						{{{job.date}}}
					</div>

					<div class="expires jb-responsive jb-ui-m jb-ui-l jb-ui-xl">
						<# if ( job.expires ) { #>
							{{{job.expires}}}
						<# } #>
					</div>

					<div class="jb-row-info-small jb-responsive jb-ui-xs jb-ui-s">

						<div class="status">
							<span class="info-label">
								<?php esc_attr_e( 'Status: ', 'jobboardwp' ); ?>
							</span>
							<span class="status-tag jb-status-{{{job.status}}}">
								{{{job.status_label}}}
							</span>
						</div>

						<div class="separator"></div>

						<div class="date">
							<span class="info-label">
								<?php esc_attr_e( 'Posted: ', 'jobboardwp' ); ?>
							</span>
							{{{job.date}}}
						</div>

						<# if ( job.expires ) { #>
							<div class="separator"></div>
							<div class="expires">
								<span class="info-label">
									<?php esc_attr_e( 'Expires: ', 'jobboardwp' ); ?>
								</span>
								{{{job.expires}}}
							</div>
						<# } #>
					</div>
				</div>
				<div class="jb-row-actions">
					<# if ( Object.keys( job.actions ).length > 0 ) { #>
						<div class="jb-job-actions-dropdown">
							<i class="fas fa-ellipsis-h" title="<?php esc_attr_e( 'More Actions', 'jobboardwp' ); ?>"></i>
							<div class="jb-dropdown" data-element=".jb-job-actions-dropdown" data-trigger="click">
								<ul>
									<# _.each( job.actions, function( action, act_key, act_list ) { #>
										<li>
											<a href="<# if ( action.href ) { #>{{{action.href}}}<# } else { #>javascript:void(0);<# } #>" <# if ( ! action.href ) { #>data-job-id="{{{job.id}}}"<# } #> class="jb-jobs-action-{{{act_key}}}">
												{{{action.title}}}
											</a>
										</li>
									<# }); #>
								</ul>
							</div>
						</div>
					<# } #>
				</div>
			</div>
		<# }); #>
	<# } else { #>
		<div class="jb-job-dashboard-empty-row">
			<?php
			// translators: %s: Post a job URL
			echo wp_kses( sprintf( __( 'No created jobs yet. <a href="%s">Create</a> new one.', 'jobboardwp' ), JB()->common()->permalinks()->get_predefined_page_link( 'job-post' ) ), JB()->get_allowed_html() );
			?>
		</div>
	<# } #>
</script>
