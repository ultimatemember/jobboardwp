<?php
/**
 * Template for the Recent Jobs widget
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/widget/recent-jobs.php
 *
 * Widget: "Recent Jobs"
 *
 * @version 1.2.8
 *
 * @var array $jb_widgets_recent_jobs
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $jb_widgets_recent_jobs['posts'] ) ) {
	?>
	<div class="jb jb-jobs-widget">
		<?php
		foreach ( $jb_widgets_recent_jobs['posts'] as $job_data ) {
			$list_row_class = '';
			if ( $jb_widgets_recent_jobs['args']['no_logo'] || empty( $job_data['logo'] ) ) {
				$list_row_class = ' jb-job-list-no-logo';
			}
			?>

			<div class="jb-job-list-row<?php echo esc_attr( $list_row_class ); ?>">
				<?php if ( ! $jb_widgets_recent_jobs['args']['no_logo'] && ! empty( $job_data['logo'] ) ) { ?>
					<div class="jb-job-logo">
						<?php echo wp_kses( $job_data['logo'], JB()->get_allowed_html( 'templates' ) ); ?>
					</div>
				<?php } ?>
				<div class="jb-row-data">
					<div class="jb-job-title">
							<span class="jb-job-title-link-line">
								<a href="<?php echo esc_url( $job_data['permalink'] ); ?>" class="jb-job-title-link"><?php echo esc_html( $job_data['title'] ); ?></a>
							</span>
						<?php if ( ! $jb_widgets_recent_jobs['args']['no_job_types'] ) { ?>
							<?php if ( ! empty( $job_data['types'] ) ) { ?>
								<div class="jb-job-types jb-responsive jb-ui-m jb-ui-l jb-ui-xl">
									<?php foreach ( $job_data['types'] as $job_type ) { ?>
										<div class="jb-job-type" style="color:<?php echo esc_attr( $job_type['color'] ); ?>;background:<?php echo esc_attr( $job_type['bg_color'] ); ?>;">
											<?php echo esc_html( $job_type['name'] ); ?>
										</div>
									<?php } ?>
								</div>
							<?php } ?>
						<?php } ?>
					</div>
					<div class="jb-row-info">
						<div class="jb-row-left-side">
							<?php if ( ! empty( $job_data['company']['name'] ) ) { ?>
								<div class="company">
									<i class="far fa-building"></i>
									<span title="<?php echo esc_attr( $job_data['company']['tagline'] ); ?>">
										<?php echo esc_html( $job_data['company']['name'] ); ?>
									</span>
								</div>
							<?php } ?>

							<?php if ( ! empty( $job_data['location'] ) ) { ?>
								<div class="location">
									<i class="fas fa-map-marker-alt"></i>
									<?php echo wp_kses( $job_data['location'], JB()->get_allowed_html( 'templates' ) ); ?>
								</div>
							<?php } ?>

							<?php if ( JB()->options()->get( 'job-categories' ) ) { ?>
								<?php if ( $job_data['category'] ) { ?>
									<div class="category">
										<?php echo wp_kses( $job_data['category'], JB()->get_allowed_html( 'templates' ) ); ?>
									</div>
								<?php } ?>
							<?php } ?>

							<div class="jb-responsive jb-ui-s jb-ui-xs date" title="<?php esc_attr_e( 'Posted', 'jobboardwp' ); ?>">
								<i class="far fa-calendar-alt"></i>
								<?php esc_html_e( 'Posted', 'jobboardwp' ); ?> <?php echo esc_html( $job_data['date'] ); ?>
							</div>

							<?php if ( ! empty( $job_data['expires'] ) ) { ?>
								<div class="expires" title="<?php esc_attr_e( 'Expires', 'jobboardwp' ); ?>">
									<i class="fa fa-calendar-times-o"></i>
									<?php esc_html_e( 'Closing on', 'jobboardwp' ); ?> <?php echo esc_html( $job_data['expires'] ); ?>
								</div>
							<?php } ?>
							<?php
							if ( JB()->options()->get( 'job-salary' ) ) {
								if ( ! empty( $job_data['salary'] ) ) {
									?>
									<div class="jb-job-salary">
										<i class="far fa-money-bill-alt"></i>
										<?php echo esc_html( $job_data['salary'] ); ?>
									</div>
									<?php
								}
							}
							?>
						</div>
						<div class="jb-row-right-side">
							<div class="date jb-responsive jb-ui-m jb-ui-l jb-ui-xl date" title="<?php esc_attr_e( 'Posted', 'jobboardwp' ); ?>">
								<?php echo esc_html( $job_data['date'] ); ?>
							</div>

							<?php if ( ! $jb_widgets_recent_jobs['args']['no_job_types'] ) { ?>
								<?php if ( ! empty( $job_data['types'] ) ) { ?>
									<div class="jb-job-types jb-responsive jb-ui-s jb-ui-xs">
										<?php foreach ( $job_data['types'] as $job_type ) { ?>
											<div class="jb-job-type" style="color:<?php echo esc_attr( $job_type['color'] ); ?>;background:<?php echo esc_attr( $job_type['bg_color'] ); ?>;">
												<?php echo esc_html( $job_type['name'] ); ?>
											</div>
										<?php } ?>
									</div>
								<?php } ?>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
	<?php
}
