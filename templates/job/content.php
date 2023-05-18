<?php
/**
 * Template for the job content template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/job/content.php
 *
 * @version 1.2.4
 *
 * @var array $jb_job_content
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $jb_job_content['job_id'] ) ) {
	$job_id = $jb_job_content['job_id'];
	?>

	<div class="jb-job-content">
		<div class="jb-job-content-separator"><?php esc_html_e( 'Description', 'jobboardwp' ); ?></div>
		<div class="jb-job-content-section">
			<?php
			// when we use JobBoardWP Default template, [jb_job id="{job_id}" /] shortcode or preview job after posting
			// !!! Important: don't use `the_content()` here because we are inside `the_content()` function in the current template and it cause PHP loop on server
			echo do_shortcode( wp_filter_content_tags( prepend_attachment( shortcode_unautop( wpautop( wptexturize( do_blocks( get_the_content( null, false, $job_id ) ) ) ) ) ) ) );
			?>
		</div>
	</div>

	<?php
}
