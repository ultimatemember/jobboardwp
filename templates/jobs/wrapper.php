<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

// phpcs:disable VariableAnalysis
// There are "undefined" variables here because they're defined in the code that includes this file as a template.

// phpcs:disable WordPress.Security.NonceVerification -- getting value from GET line

$current_page = ( ! empty( $_GET['jb-page'] ) && is_numeric( $_GET['jb-page'] ) ) ? absint( $_GET['jb-page'] ) : 1;
?>

<div class="jb jb-jobs" data-base-post="<?php echo esc_attr( $post->ID ); ?>"
	data-hide-expired="<?php /** @noinspection PhpUndefinedVariableInspection */echo esc_attr( $jb_jobs_wrapper['hide-expired'] ); ?>"
	data-hide-filled="<?php echo esc_attr( $jb_jobs_wrapper['hide-filled'] ); ?>"
	data-filled-only="<?php echo esc_attr( $jb_jobs_wrapper['filled-only'] ); ?>"
	data-orderby="<?php echo esc_attr( $jb_jobs_wrapper['orderby'] ); ?>"
	data-order="<?php echo esc_attr( $jb_jobs_wrapper['order'] ); ?>"
	data-employer="<?php echo esc_attr( $jb_jobs_wrapper['employer-id'] ); ?>"
	data-page="<?php echo esc_attr( $current_page ); ?>"
	data-per-page="<?php echo esc_attr( $jb_jobs_wrapper['per-page'] ); ?>"
	data-no-jobs="<?php echo esc_attr( $jb_jobs_wrapper['no-jobs-text'] ); ?>"
	data-no-jobs-search="<?php echo esc_attr( $jb_jobs_wrapper['no-jobs-search-text'] ); ?>"
	data-category="<?php echo esc_attr( $jb_jobs_wrapper['category'] ); ?>"
	data-type="<?php echo esc_attr( $jb_jobs_wrapper['type'] ); ?>">

	<?php
	JB()->get_template_part( 'ajax-overlay', $jb_jobs_wrapper );

	JB()->get_template_part( 'jobs/search-bar', $jb_jobs_wrapper );

	JB()->get_template_part( 'jobs/list', $jb_jobs_wrapper );

	do_action( 'jb_jobs_footer', $jb_jobs_wrapper );
	?>
</div>
