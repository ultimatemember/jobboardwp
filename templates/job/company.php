<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $jb_job_company['job_id'] ) ) {

	$job_id = $jb_job_company['job_id'];

	$logo    = apply_filters( 'jb_job_change_logo', JB()->common()->job()->get_logo( $job_id ), $job_id );
	$company = apply_filters( 'jb_job_change_company', JB()->common()->job()->get_company_data( $job_id ), $job_id ); ?>

	<div class="jb-job-company">
		<div class="jb-job-company-info<?php echo empty( $logo ) ? ' jb-job-no-logo' : ''; ?>">
			<?php if ( ! empty( $logo ) ) { ?>
				<div class="jb-job-logo">
					<?php echo wp_kses( $logo, JB()->get_allowed_html( 'templates' ) ); ?>
				</div>
			<?php } ?>
			<div class="jb-job-company-title-tagline">
				<div class="jb-job-company-name">
					<strong><?php echo esc_html( $company['name'] ); ?></strong>
				</div>
				<div class="jb-job-company-tagline">
					<?php echo esc_html( $company['tagline'] ); ?>
				</div>
			</div>
		</div>
		<div class="jb-job-company-links">
			<?php if ( ! empty( $company['website'] ) ) { ?>
				<a href="<?php echo esc_url( $company['website'] ); ?>" target="_blank">
					<i class="fas fa-link"></i>
				</a>
			<?php } ?>

			<?php if ( ! empty( $company['facebook'] ) ) { ?>
				<a href="<?php echo esc_url( 'https://facebook.com/' . $company['facebook'] ); ?>" target="_blank">
					<i class="fab fa-facebook-f"></i>
				</a>
			<?php } ?>

			<?php if ( ! empty( $company['instagram'] ) ) { ?>
				<a href="<?php echo esc_url( 'https://instagram.com/' . $company['instagram'] ); ?>" target="_blank">
					<i class="fab fa-instagram"></i>
				</a>
			<?php } ?>

			<?php if ( ! empty( $company['twitter'] ) ) { ?>
				<a href="<?php echo esc_url( 'https://twitter.com/' . $company['twitter'] ); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
			<?php } ?>
		</div>
	</div>

	<?php
}
