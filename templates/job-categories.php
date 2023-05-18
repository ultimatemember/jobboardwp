<?php
/**
 * Template for the Job's categories
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/job-categories.php
 *
 * @version 1.2.0
 *
 * @var array $jb_job_categories
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis
// There are "undefined" variables here because they're defined in the code that includes this file as a template.

JB()->get_template_part( 'js/job-categories-list' ); ?>

<div class="jb jb-job-categories">
	<?php /** @noinspection PhpUndefinedVariableInspection */ JB()->get_template_part( 'ajax-overlay', $jb_job_categories ); ?>
	<div class="jb-job-categories-header">
		<div class="jb-job-categories-header-row">
			<div class="jb-job-categories-title"><?php esc_html_e( 'Category', 'jobboardwp' ); ?></div>
			<div class="jb-responsive jb-ui-s jb-ui-m jb-ui-l jb-ui-xl jb-job-categories-count"><?php esc_html_e( 'Jobs', 'jobboardwp' ); ?></div>
		</div>
	</div>
	<div class="jb-job-categories-wrapper"></div>
	<?php
	/**
	 * Fires in the job categories list footer.
	 *
	 * @since 1.1.0
	 * @hook jb_job_categories_footer
	 *
	 * @param {array} $args Arguments passed into template.
	 */
	do_action( 'jb_job_categories_footer', $jb_job_categories );
	?>
</div>
