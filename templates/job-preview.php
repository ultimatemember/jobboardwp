<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! empty( $jb_job_preview['job_id'] ) ) { ?>

	<div id="jb-job-preview-wrapper" class="jb">
		<h2><?php _e( 'Preview', 'jobboardwp' ) ?></h2>

		<div class="jb-job-title-info">
			<div class="jb-job-title">
				<h3><?php echo get_the_title( $jb_job_preview['job_id'] ) ?></h3>
			</div>

			<?php JB()->get_template_part( 'job/info', $jb_job_preview ); ?>
		</div>

		<?php JB()->get_template_part( 'job/company', $jb_job_preview );

		JB()->get_template_part( 'job/content', $jb_job_preview ); ?>
	</div>

	<?php $preview_form = JB()->frontend()->forms( [
		'id'    => 'jb-job-preview-submission',
	] );

	$preview_form->set_data( [
		'id'        => 'jb-job-submission',
		'class'     => '',
		'prefix_id' => '',
		'hiddens'   => [
			'jb-action'                 => 'job-publishing',
			'jb-job-submission-step'    => 'draft||publish',
			'nonce'                     => wp_create_nonce( 'jb-job-publishing' ),
		],
		'buttons'   => [
			'job-draft' => [
				'type'  => 'submit',
				'label' => __( 'Continue Editing', 'jobboardwp' ),
				'data'  => [
					'action'    => 'draft',
				],
			],
			'job-publish' => [
				'type'  => 'submit',
				'label' => __( 'Submit Job', 'jobboardwp' ),
				'data'  => [
					'action'    => 'publish',
				],
			],
		],
	] );

	$preview_form->display();
}