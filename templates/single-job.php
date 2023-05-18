<?php
/**
 * Template for the single job page
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/single-job.php
 *
 * Page: "Single Job"
 *
 * @version 1.2.4
 *
 * @var array $jb_single_job
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $jb_single_job['id'] ) ) {

	$default_template_replaced = '';
	if ( isset( $jb_single_job['default_template_replaced'] ) ) {
		$default_template_replaced = $jb_single_job['default_template_replaced'];
	}

	$job_id    = $jb_single_job['id'];
	$job_title = get_the_title( $job_id );
	?>

	<div class="jb jb-single-job-wrapper" id="jb-single-job-<?php echo esc_attr( $job_id ); ?>">
		<div class="jb-job-title-info">
			<div class="jb-job-title">
				<?php if ( is_singular( 'jb-job' ) && get_the_ID() === (int) $jb_single_job['id'] ) { ?>
					<h1><?php echo esc_html( $job_title ); ?></h1>
				<?php } else { ?>
					<h2><?php echo esc_html( $job_title ); ?></h2>
				<?php } ?>
			</div>

			<?php
			if ( JB()->options()->get( 'job-breadcrumbs' ) ) {
				JB()->get_template_part( 'job/breadcrumbs', array( 'job_id' => $job_id ) );
			}

			JB()->get_template_part( 'job/info', array( 'job_id' => $job_id ) );
			?>
		</div>

		<?php
		JB()->get_template_part( 'job/company', array( 'job_id' => $job_id ) );

		JB()->get_template_part( 'job/notices', array( 'job_id' => $job_id ) );

		JB()->get_template_part(
			'job/content',
			array(
				'job_id'                    => $job_id,
				'default_template_replaced' => $default_template_replaced,
			)
		);

		JB()->get_template_part(
			'job/footer',
			array(
				'job_id' => $job_id,
				'title'  => $job_title,
			)
		);
		?>
	</div>

	<?php
}
