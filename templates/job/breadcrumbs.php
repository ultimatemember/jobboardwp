<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $jb_job_breadcrumbs['job_id'] ) ) {

	$job_id = $jb_job_breadcrumbs['job_id'];
	$all_jobs = JB()->common()->permalinks()->get_preset_page_link( 'jobs' );
	$terms = get_the_terms( $job_id, 'jb-job-category' ); ?>

<div class="jb-job-breadcrumbs">
	<a href="<?php esc_url( $all_jobs ); ?>"><?php esc_html_e( 'All jobs', 'jobboardwp' ); ?></a> &gt;
	<a href="<?php echo esc_url( get_term_link( $terms[0]->term_id, 'jb-job-category' ) ); ?>"><?php echo esc_html( $terms[0]->name ); ?></a> &gt;
	<?php the_title(); ?>
</div>
<?php } ?>
