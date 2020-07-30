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
							$contact_mailto = add_query_arg(
								[
									// translators: %1$s: application type, %2$s: home URL
									'subject' => esc_html__( sprintf( __( 'Application via %1$s job on %2$s', 'jobboardwp' ), $title, home_url() ) )
								],
								'mailto:' . $contact
							); ?>

							<p>
								<?php
								// translators: %1$s: mailto URL, %2$s: contact email
								printf( __( 'To apply for this job <strong>email your details to</strong> <a href="%1$s">%2$s</a>.', 'jobboardwp' ), $contact_mailto, $contact ); ?>
							</p>
						<?php } else { ?>
							<p>
								<?php
								// translators: %1$s: application's website URL
								printf( __( 'To apply for this job please visit <a href="%1$s">%1$s</a>.', 'jobboardwp' ), $contact ); ?>
							</p>
						<?php } ?>

						<a href="javascript:void(0);" class="jb-job-apply-hide"><?php _e( 'Cancel', 'jobboardwp' ); ?></a>
					</div>
				</div>
				<div class="jb-job-after-apply-wrapper">
					<?php do_action( 'jb_after_job_apply_block', $job_id ); ?>
				</div>
			<?php } ?>
		</div>
	</div>

<?php }