<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! empty ( $jb_single_job['id'] ) ) {

	$default_template_replaced = $jb_single_job['default_template_replaced'];

	$job_id = $jb_single_job['id'];
	$title = get_the_title( $job_id ); ?>

	<div class="jb jb-single-job-wrapper" id="jb-single-job-<?php echo esc_attr( $job_id ) ?>">
		<div class="jb-job-title-info">
			<div class="jb-job-title">
				<?php if ( is_singular( 'jb-job' ) && $jb_single_job['id'] == get_the_ID() ) { ?>
					<h1><?php echo $title ?></h1>
				<?php } else { ?>
					<h2><?php echo $title ?></h2>
				<?php } ?>
			</div>

			<?php JB()->get_template_part( 'job/info', [ 'job_id' => $job_id ] ); ?>
		</div>

		<?php JB()->get_template_part( 'job/company', [ 'job_id' => $job_id ] );

		JB()->get_template_part( 'job/notices', [ 'job_id' => $job_id ] );

		JB()->get_template_part( 'job/content', [ 'job_id' => $job_id, 'default_template_replaced' => $default_template_replaced ] );

		JB()->get_template_part( 'job/footer', [ 'job_id' => $job_id, 'title' => $title ] ); ?>
	</div>

<?php }