<?php
/**
 * Template for the job dashboard template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/dashboard/content.php
 *
 * @version 1.2.8
 *
 * @var array $jb_dashboard_jobs
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis
// There are "undefined" variables here because they're defined in the code that includes this file as a template.

if ( ! is_user_logged_in() ) {
	?>

	<p>
		<?php
		// translators: %s: login link
		echo wp_kses( sprintf( __( '<a href="%s">Sign in</a> to view your job listings.', 'jobboardwp' ), wp_login_url( get_permalink() ) ), JB()->get_allowed_html( 'templates' ) );
		?>
	</p>

	<?php
} else {

	/** @noinspection PhpUndefinedVariableInspection */
	JB()->get_template_part( 'js/jobs-dashboard', $jb_dashboard_jobs );
	?>

	<div class="jb jb-job-dashboard">

		<?php JB()->get_template_part( 'ajax-overlay', $jb_dashboard_jobs ); ?>

		<div class="jb-responsive jb-ui-m jb-ui-l jb-ui-xl">
			<div class="jb-job-dashboard-heading">
				<?php foreach ( $jb_dashboard_jobs['columns'] as $key => $col_title ) { ?>
					<span class="jb-job-col-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $col_title ); ?></span>
				<?php } ?>
			</div>
		</div>

		<div class="jb-job-dashboard-rows"></div>
	</div>

	<?php
}
