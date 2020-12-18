<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! empty( $jb_job_content['job_id'] ) ) {

	$job_id = $jb_job_content['job_id']; ?>

	<div class="jb-job-content">
		<div class="jb-job-content-separator"><?php _e( 'Description', 'jobboardwp' ) ?></div>
		<div class="jb-job-content-section">

			<?php if ( ! empty( $jb_job_content['default_template_replaced'] ) ) {
				echo do_shortcode( wp_filter_content_tags( prepend_attachment( shortcode_unautop( wpautop( wptexturize( do_blocks( get_the_content( null, false, $job_id ) ) ) ) ) ) ) );
			} else {
				echo apply_filters( 'the_content', get_the_content( null, false, $job_id ) );
			} ?>
		</div>
	</div>

<?php }