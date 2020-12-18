<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! empty( $jb_job_info['job_id'] ) ) {

	$job_id = $jb_job_info['job_id']; ?>

	<div class="jb-job-content">
		<div class="jb-job-content-separator"><?php _e( 'Description', 'jobboardwp' ) ?></div>
		<div class="jb-job-content-section"><?php echo apply_filters( 'the_content', get_the_content( null, false, $job_id ) ); ?></div>
	</div>

<?php }