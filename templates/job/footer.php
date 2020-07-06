<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! empty( $jb_job_footer['job_id'] ) ) {

	$job_id = $jb_job_footer['job_id'];
	$title = ! empty( $jb_job_footer['title'] ) ? $jb_job_footer['title'] : ''; ?>

	<div class="jb-job-footer">
		<div class="jb-job-footer-row">
			<?php if ( JB()->common()->job()->can_applied( $job_id ) ) {

				$contact = get_post_meta( $job_id, 'jb-application-contact', true ); ?>

				<div class="jb-job-apply-wrapper">

					<input type="button" class="jb-button jb-job-apply" value="<?php esc_attr_e( 'Apply for job', 'jobboardwp' ); ?>" />

					<div class="jb-job-apply-description">
						<?php if ( is_email( $contact ) ) {
							$contact_mailto = add_query_arg([ 'subject' => esc_html__( sprintf( __( 'Application via %s job on %s', 'jobboardwp' ), $title, home_url() ) ) ], 'mailto:' . $contact ); ?>

							<p>
								<?php printf( __( 'To apply for this job <strong>email your details to</strong> <a href="%s">%s</a>.', 'jobboardwp' ), $contact_mailto, $contact ); ?>
							</p>
						<?php } else { ?>
							<p>
								<?php printf( __( 'To apply for this job please visit <a href="%s">%s</a>.', 'jobboardwp' ), $contact, $contact ); ?>
							</p>
						<?php } ?>

						<a href="javascript:void(0);" class="jb-job-apply-hide"><?php _e( 'Cancel', 'jobboardwp' ); ?></a>
					</div>
				</div>

			<?php } ?>
		</div>
	</div>

<?php }