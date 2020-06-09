<?php if ( ! defined( 'ABSPATH' ) ) exit;

$job = $jb_job_preview['job'];
$job_id = $job->ID;

$posted_timestamp = JB()->common()->job()->get_posted_date( $job_id );
$posted = JB()->common()->job()->get_posted_date( $job_id, true );

$job_company_data = JB()->common()->job()->get_company_data( $job_id );

$logo = JB()->common()->job()->get_logo( $job_id ); ?>

<div class="jb jb-job-preview-wrapper">
	<h2><?php _e( 'Preview', 'jobboardwp' ) ?></h2>
	<div class="jb-job-title-info">
		<div class="jb-job-title">
			<h3><?php echo get_the_title( $job_id ) ?></h3>
		</div>
		<div class="jb-job-info">
			<div class="jb-job-info-row jb-job-info-row-first">
				<div class="jb-job-location">
					<i class="fas fa-map-marker-alt"></i>
					<?php echo JB()->common()->job()->get_location( $job_id ) ?>
				</div>
				<div class="jb-job-posted">
					<i class="far fa-calendar-alt"></i>
					<?php echo '<time datetime="' . esc_attr( $posted_timestamp ) . '">' . wp_kses_post( $posted ) . '</time>' ?>
				</div>
			</div>
			<div class="jb-job-info-row jb-job-info-row-second">
				<div class="jb-job-types">
					<?php echo JB()->common()->job()->display_types( $job_id ); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="jb-job-company">
		<div class="jb-job-company-info<?php echo empty( $logo ) ? ' jb-job-no-logo' : '' ?>">
			<?php if ( ! empty( $logo ) ) { ?>
				<div class="jb-job-logo">
					<?php echo $logo; ?>
				</div>
			<?php } ?>
			<div class="jb-job-company-title-tagline">
				<div class="jb-job-company-name"><strong><?php echo $job_company_data['name'] ?></strong></div>
				<div class="jb-job-company-tagline"><?php echo $job_company_data['tagline'] ?></div>
			</div>
		</div>
		<div class="jb-job-company-links">
			<?php if ( ! empty( $job_company_data['website'] ) ) { ?>
				<a href="<?php echo esc_url( $job_company_data['website'] ) ?>" target="_blank"><i class="fas fa-link"></i></a>
			<?php }

			if ( ! empty( $job_company_data['facebook'] ) ) { ?>
				<a href="<?php echo esc_url( 'https://facebook.com/' . $job_company_data['facebook'] ) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
			<?php }

			if ( ! empty( $job_company_data['instagram'] ) ) { ?>
				<a href="<?php echo esc_url( 'https://instagram.com/' . $job_company_data['instagram'] ) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
			<?php }

			if ( ! empty( $job_company_data['twitter'] ) ) { ?>
				<a href="<?php echo esc_url( 'https://twitter.com/' . $job_company_data['twitter'] ) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
			<?php } ?>
		</div>
	</div>
	<div class="jb-job-content">
		<div class="jb-job-content-separator"><?php _e( 'Description', 'jobboardwp' ) ?></div>
		<div class="jb-job-content-section"><?php echo get_the_content( null, false, $job_id ) ?></div>
	</div>
</div>
<form action="" method="post" name="jb-job-preview-submission" id="jb-job-preview-submission">
	<p class="jb-submit-row">
		<input type="hidden" name="jb-action" value="job-publishing" />
		<input type="hidden" name="jb-job-submission-step" value="draft|publish" />
		<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'jb-job-publishing' ) ) ?>" />
		<input type="submit" value="<?php esc_attr_e( 'Continue Editing', 'jobboardwp' ) ?>" class="jb-job-draft-submit" name="draft" />
		<input type="submit" value="<?php esc_attr_e( 'Submit Job', 'jobboardwp' ) ?>" class="jb-job-publish-submit" name="publish" />
	</p>
</form>