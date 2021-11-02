<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $jb_job_notices['job_id'] ) ) {

	$job_id = $jb_job_notices['job_id'];

	if ( JB()->common()->job()->is_filled( $job_id ) ) {
		?>
		<div class="jb-job-filled-notice">
			<i class="fas fa-exclamation-circle"></i><?php esc_html_e( 'This job has been filled', 'jobboardwp' ); ?>
		</div>
		<?php
	} elseif ( JB()->common()->job()->is_expired( $job_id ) ) {
		?>
		<div class="jb-job-expired-notice">
			<i class="fas fa-exclamation-circle"></i><?php esc_html_e( 'This job has been expired', 'jobboardwp' ); ?>
		</div>
		<?php
	}
}
