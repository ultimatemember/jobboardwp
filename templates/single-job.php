<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! empty ( $jb_single_job['id'] ) ) {

	$job_id = $jb_single_job['id'];

	$posted_timestamp = JB()->common()->job()->get_posted_date( $job_id );
	$posted = JB()->common()->job()->get_posted_date( $job_id, true );

	$job_company_data = JB()->common()->job()->get_company_data( $job_id );

	$logo = JB()->common()->job()->get_logo( $job_id ); ?>

	<div class="jb jb-single-job-wrapper">
		<div class="jb-job-title-info">
			<div class="jb-job-title">
				<?php if ( is_singular( 'jb-job' ) && $jb_single_job['id'] == get_the_ID() ) { ?>
					<h1><?php echo get_the_title( $job_id ) ?></h1>
				<?php } else { ?>
					<h2><?php echo get_the_title( $job_id ) ?></h2>
				<?php } ?>
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
			<?php if ( JB()->common()->job()->is_filled( $post->ID ) ) { ?>
                <div class="jb-job-filled-notice"><i class="fas fa-exclamation-circle"></i><?php _e( 'This job has been filled', 'jobboardwp' ); ?></div>
			<?php } ?>

			<div class="jb-job-content-separator"><?php _e( 'Description', 'jobboardwp' ) ?></div>
			<div class="jb-job-content-section"><?php echo get_the_content( null, false, $job_id ) ?></div>
		</div>
		<div class="jb-job-footer">
			<div class="jb-job-footer-row">
				<?php if ( JB()->common()->job()->can_applied( $job_id ) ) { ?>
					<input type="button" class="jb-button jb-job-apply" value="<?php esc_attr_e( 'Apply for job', 'jobboardwp' ); ?>" />
				<?php } ?>
			</div>
		</div>
	</div>

<?php }