<?php
/**
 * Template for the Job's preview
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/job-preview.php
 *
 * @version 1.1.0
 *
 * @var array $jb_job_preview
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! empty( $jb_job_preview['job_id'] ) ) { ?>

	<div id="jb-job-preview-wrapper" class="jb">
		<h2><?php esc_html_e( 'Preview', 'jobboardwp' ); ?></h2>

		<div class="jb-job-title-info">
			<div class="jb-job-title">
				<h3><?php echo esc_html( get_the_title( $jb_job_preview['job_id'] ) ); ?></h3>
			</div>

			<?php JB()->get_template_part( 'job/info', $jb_job_preview ); ?>
		</div>

		<?php JB()->get_template_part( 'job/company', $jb_job_preview ); ?>

		<?php JB()->get_template_part( 'job/content', $jb_job_preview ); ?>
	</div>

	<?php
	$preview_form = JB()->frontend()->forms(
		array(
			'id' => 'jb-job-preview-submission',
		)
	);

	$preview_form->set_data(
		array(
			'id'        => 'jb-job-submission',
			'class'     => '',
			'prefix_id' => '',
			'hiddens'   => array(
				'jb-action'              => 'job-publishing',
				'jb-job-submission-step' => 'draft||publish',
				'nonce'                  => wp_create_nonce( 'jb-job-publishing' ),
			),
			'buttons'   => array(
				'job-draft'   => array(
					'type'  => 'submit',
					'label' => __( 'Continue Editing', 'jobboardwp' ),
					'data'  => array(
						'action' => 'draft',
					),
				),
				'job-publish' => array(
					'type'  => 'submit',
					'label' => __( 'Submit Job', 'jobboardwp' ),
					'data'  => array(
						'action' => 'publish',
					),
				),
			),
		)
	);

	$preview_form->display();
}
