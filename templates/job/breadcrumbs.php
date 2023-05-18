<?php
/**
 * Template for the job breadcrumbs template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/job/breadcrumbs.php
 *
 * @version 1.2.0
 *
 * @var array $jb_job_breadcrumbs
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $jb_job_breadcrumbs['job_id'] ) ) {

	$job_id   = $jb_job_breadcrumbs['job_id'];
	$all_jobs = JB()->common()->permalinks()->get_predefined_page_link( 'jobs' );

	if ( JB()->options()->get( 'job-categories' ) ) {
		$terms = get_the_terms( $job_id, 'jb-job-category' );
	}
	?>

	<div class="jb-job-breadcrumbs">
		<a href="<?php echo esc_url( $all_jobs ); ?>"><?php esc_html_e( 'All jobs', 'jobboardwp' ); ?></a> &gt;
		<?php if ( JB()->options()->get( 'job-categories' ) && ! empty( $terms ) && ! is_wp_error( $terms ) ) { ?>
			<a href="<?php echo esc_url( get_term_link( $terms[0]->term_id, 'jb-job-category' ) ); ?>"><?php echo esc_html( $terms[0]->name ); ?></a> &gt;
		<?php } ?>
		<?php echo esc_html( get_the_title() ); ?>
	</div>

	<?php
}
