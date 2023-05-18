<?php
/**
 * Template for the Jobs list template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/jobs/list.php
 *
 * @version 1.2.0
 *
 * @var array $jb_jobs_list
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis
// There are "undefined" variables here because they're defined in the code that includes this file as a template.

/** @noinspection PhpUndefinedVariableInspection */
JB()->get_template_part( 'js/jobs-list', $jb_jobs_list );
?>

<div class="jb-jobs-wrapper"></div>

<div class="jb-jobs-pagination-box">
	<a href="javascript:void(0);" class="jb-load-more-jobs">
		<?php echo esc_html( $jb_jobs_list['load-more-text'] ); ?>
	</a>
</div>
