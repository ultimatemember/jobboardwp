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
		</div>
		<div class="jb-job-info-row jb-job-info-row-second">
			<div class="jb-job-types">
				<?php echo wp_kses( JB()->common()->job()->display_types( $job_id ), JB()->get_allowed_html( 'templates' ) ); ?>
			</div>
		</div>
		<?php
		$amount_output = '';
		$salary_type   = get_post_meta( $job_id, 'jb-salary-type', true );
		if ( 'not' !== $salary_type ) {
			$currency         = JB()->options()->get( 'job-salary-currency' );
			$currency_symbols = JB()->config()->get( 'currency_symbols' );
			$currency_symbol  = $currency_symbols[ $currency ];

			$amount_type = get_post_meta( $job_id, 'jb-amount-type', true );
			if ( 'numeric' === $amount_type ) {
				$amount = get_post_meta( $job_id, 'jb-amount', true );

				$amount_output = $amount . ' ' . $currency_symbol;
			} else {
				$amount_min = get_post_meta( $job_id, 'jb-min-amount', true );
				$amount_max = get_post_meta( $job_id, 'jb-max-amount', true );

				$amount_output = $amount_min . '-' . $amount_max . $currency_symbol;
			}
			if ( 'recurring' === $salary_type ) {
				$period        = get_post_meta( $job_id, 'jb-period', true );
				$amount_output .= ' ' . esc_html__( 'per', 'jobboardwp' ) . ' ' . $period;
			}
		}
		if ( '' !== $amount_output ) {
			?>
			<div class="jb-job-info-row jb-job-info-row-third">
				<div class="jb-job-types">
					<?php echo esc_html__( 'Salary:', 'jobboardwp' ) . ' ' . wp_kses( $amount_output, JB()->get_allowed_html( 'templates' ) ); ?>
				</div>
			</div>
		<?php } ?>
	</div>

	<?php
}
