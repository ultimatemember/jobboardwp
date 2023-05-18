<?php
/**
 * Template for the job information template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/job/info.php
 *
 * @version 1.2.6
 *
 * @var array $jb_job_info
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $jb_job_info['job_id'] ) ) {

	$job_id = $jb_job_info['job_id'];

	$amount_output = JB()->common()->job()->get_formatted_salary( $job_id );
	?>

	<div class="jb-job-info">
		<div class="jb-job-info-row jb-job-info-row-first">
			<div class="jb-job-location">
				<i class="fas fa-map-marker-alt"></i>
				<?php echo wp_kses( JB()->common()->job()->get_location_link( $job_id ), JB()->get_allowed_html( 'templates' ) ); ?>
			</div>
			<div class="jb-job-posted">
				<i class="far fa-calendar-alt"></i>
				<time datetime="<?php echo esc_attr( JB()->common()->job()->get_html_datetime( $job_id ) ); ?>">
					<?php echo esc_html( JB()->common()->job()->get_posted_date( $job_id ) ); ?>
				</time>
			</div>
			<?php if ( JB()->options()->get( 'job-categories' ) ) { ?>
				<div class="jb-job-cat">
					<?php echo wp_kses( JB()->common()->job()->get_job_category( $job_id ), JB()->get_allowed_html( 'templates' ) ); ?>
				</div>
			<?php } ?>
			<?php if ( '' !== $amount_output ) { ?>
				<div class="jb-job-salary">
					<i class="far fa-money-bill-alt"></i>
					<?php echo esc_html( $amount_output ); ?>
				</div>
			<?php } ?>
		</div>
		<div class="jb-job-info-row jb-job-info-row-second">
			<div class="jb-job-types">
				<?php echo wp_kses( JB()->common()->job()->display_types( $job_id ), JB()->get_allowed_html( 'templates' ) ); ?>
			</div>
		</div>
	</div>

	<?php
}
