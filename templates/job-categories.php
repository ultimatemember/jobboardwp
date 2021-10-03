<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

JB()->get_template_part( 'js/job-categories-list' ); ?>

<div class="jb jb-job-categories">
	<?php JB()->get_template_part( 'ajax-overlay', $jb_job_categories ); ?>
	<div class="jb-job-categories-header">
		<div class="jb-job-categories-header-row">
			<div class="jb-job-categories-title"><?php esc_html_e( 'Category', 'jobboardwp' ); ?></div>
			<div class="jb-responsive jb-ui-s jb-ui-m jb-ui-l jb-ui-xl jb-job-categories-count"><?php esc_html_e( 'Jobs', 'jobboardwp' ); ?></div>
		</div>
	</div>
	<div class="jb-job-categories-wrapper"></div>
	<?php do_action( 'jb_job_categories_footer', $jb_job_categories ); ?>
</div>
