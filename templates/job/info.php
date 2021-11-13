<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $jb_job_info['job_id'] ) ) {

	$job_id = $jb_job_info['job_id'];
	?>

	<div class="jb-job-info">
		<div class="jb-job-info-row jb-job-info-row-first">
			<div class="jb-job-location">
				<i class="fas fa-map-marker-alt"></i>
				<?php echo wp_kses( JB()->common()->job()->get_location_link( esc_html( JB()->common()->job()->get_location( $job_id ) ) ), JB()->get_allowed_html( 'templates' ) ); ?>
			</div>
			<div class="jb-job-posted">
				<i class="far fa-calendar-alt"></i>
				<time datetime="<?php echo esc_attr( JB()->common()->job()->get_html_datetime( $job_id ) ); ?>">
					<?php echo esc_html( JB()->common()->job()->get_posted_date( $job_id ) ); ?>
				</time>
			</div>
		</div>
		<div class="jb-job-info-row jb-job-info-row-second">
			<div class="jb-job-types">
				<?php echo wp_kses( JB()->common()->job()->display_types( $job_id ), JB()->get_allowed_html( 'templates' ) ); ?>
			</div>
		</div>
	</div>

	<?php
}
