<?php if ( ! defined( 'ABSPATH' ) ) exit;

$current_page = ( ! empty( $_GET['jb-page'] ) && is_numeric( $_GET['jb-page'] ) ) ? (int) $_GET['jb-page'] : 1; ?>

<script type="text/template" id="tmpl-jb-jobs-dashboard-line">
	<# if ( data.length > 0 ) { #>
		<# _.each( data, function( job, key, list ) { #>
			<div class="jb-job-dashboard-row<# if ( ! Object.keys( job.actions ).length ) { #> jb-job-dashboard-no-actions<# } #>">
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
							<span class="info-label"><?php esc_attr_e( 'Status: ', 'jobboardwp' ) ?></span>
							<span class="status-tag jb-status-{{{job.status}}}">
								{{{job.status_label}}}
							</span>
						</div>

						<div class="separator"></div>

						<div class="date">
							<span class="info-label"><?php esc_attr_e( 'Posted: ', 'jobboardwp' ) ?></span>
							{{{job.date}}}
						</div>

						<# if ( job.expires ) { #>
							<div class="separator"></div>
							<div class="expires">
								<span class="info-label"><?php esc_attr_e( 'Expires: ', 'jobboardwp' ) ?></span>
								{{{job.expires}}}
							</div>
						<# } #>
					</div>
				</div>
				<div class="jb-row-actions">
					<# if ( Object.keys( job.actions ).length > 0 ) { #>
						<div class="jb-job-actions-dropdown">
							<i class="fas fa-ellipsis-h" title="<?php esc_attr_e( 'More Actions', 'jobboardwp' ) ?>"></i>
							<div class="jb-dropdown" data-element=".jb-job-actions-dropdown" data-trigger="click">
								<ul>
									<# _.each( job.actions, function( action, act_key, act_list ) { #>
										<li><a href="{{{action.href}}}" class="jb-jobs-action-{{{act_key}}}">{{{action.title}}}</a></li>
									<# }); #>
								</ul>
							</div>
						</div>
					<# } #>
				</div>
			</div>
		<# }); #>
	<# } #>
</script>

<div id="jb-job-dashboard" class="jb" data-page="<?php echo esc_attr( $current_page ) ?>">
	<div class="jb-overlay">
		<div class="jb-ajax-loading"></div>
	</div>

	<div class="jb-responsive jb-ui-m jb-ui-l jb-ui-xl">
		<?php $header_cols = [
			'title'    => __( 'Title', 'jobboardwp' ),
			'status'   => __( 'Status', 'jobboardwp' ),
			'posted'   => __( 'Posted', 'jobboardwp' ),
			'expired'  => __( 'Closing on', 'jobboardwp' ),
		];
		$header_cols = apply_filters( 'jb_jobs_dashboard_header_columns', $header_cols ); ?>

		<div class="jb-job-dashboard-heading">
			<?php foreach ( $header_cols as $key => $title ) { ?>
				<span class="jb-job-col-<?php echo esc_attr( $key ) ?>"><?php echo $title ?></span>
			<?php } ?>
		</div>
	</div>

	<div id="jb-job-dashboard-rows">

	</div>
</div>

<?php JB()->frontend()->templates()->dropdown_menu( '.jb-job-actions-dropdown', 'click' );