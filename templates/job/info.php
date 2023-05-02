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
		if ( '' !== $salary_type ) {
			$currency         = JB()->options()->get( 'job-salary-currency' );
			$currency_symbols = JB()->config()->get( 'currencies' );
			$currency_symbol  = $currency_symbols[ $currency ][1];

			$salary_amount_type = get_post_meta( $job_id, 'jb-salary-amount-type', true );
			if ( 'numeric' === $salary_amount_type ) {
				$salary_amount = get_post_meta( $job_id, 'jb-salary-amount', true );

				$amount_output = sprintf( JB()->get_job_salary_format(), $currency_symbol, $salary_amount );
			} else {
				$salary_min_amount = get_post_meta( $job_id, 'jb-salary-min-amount', true );
				$salary_max_amount = get_post_meta( $job_id, 'jb-salary-max-amount', true );

				$amount_output = sprintf( JB()->get_job_salary_format(), $currency_symbol, $salary_min_amount . '-' . $salary_max_amount );
			}
			if ( 'recurring' === $salary_type ) {
				$salary_period         = get_post_meta( $job_id, 'jb-salary-period', true );
				$amount_output .= ' ' . esc_html__( 'per', 'jobboardwp' ) . ' ' . $salary_period;
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
