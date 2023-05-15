<?php
/**
 * Template for the jobs list wrapper template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/jobs/wrapper.php
 *
 * @version 1.2.6
 *
 * @var array $jb_jobs_wrapper
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $jb_jobs_shortcode_index;

// phpcs:disable VariableAnalysis
// There are "undefined" variables here because they're defined in the code that includes this file as a template.

// phpcs:disable WordPress.Security.NonceVerification -- getting value from GET line

$current_page = ( ! empty( $_GET['jb-page'][ $jb_jobs_shortcode_index ] ) && is_numeric( $_GET['jb-page'][ $jb_jobs_shortcode_index ] ) ) ? absint( $_GET['jb-page'][ $jb_jobs_shortcode_index ] ) : 1;
?>

<div class="jb jb-jobs" data-base-post="<?php echo isset( $post->ID ) ? esc_attr( $post->ID ) : ''; ?>"
	data-hide-expired="<?php /** @noinspection PhpUndefinedVariableInspection */echo esc_attr( $jb_jobs_wrapper['hide-expired'] ); ?>"
	data-hide-filled="<?php echo esc_attr( $jb_jobs_wrapper['hide-filled'] ); ?>"
	data-filled-only="<?php echo esc_attr( $jb_jobs_wrapper['filled-only'] ); ?>"
	data-orderby="<?php echo esc_attr( $jb_jobs_wrapper['orderby'] ); ?>"
	data-order="<?php echo esc_attr( $jb_jobs_wrapper['order'] ); ?>"
	data-employer="<?php echo esc_attr( $jb_jobs_wrapper['employer-id'] ); ?>"
	data-no-logo="<?php echo esc_attr( $jb_jobs_wrapper['no-logo'] ); ?>"
	data-hide-job-types="<?php echo esc_attr( $jb_jobs_wrapper['hide-job-types'] ); ?>"
	data-page="<?php echo esc_attr( $current_page ); ?>"
	data-per-page="<?php echo esc_attr( $jb_jobs_wrapper['per-page'] ); ?>"
	data-no-jobs="<?php echo esc_attr( $jb_jobs_wrapper['no-jobs-text'] ); ?>"
	data-no-jobs-search="<?php echo esc_attr( $jb_jobs_wrapper['no-jobs-search-text'] ); ?>"
	data-category="<?php echo esc_attr( $jb_jobs_wrapper['category'] ); ?>"
	data-type="<?php echo esc_attr( $jb_jobs_wrapper['type'] ); ?>"
	<?php echo JB()->options()->get( 'job-salary' ) ? 'data-salary="' . esc_attr( $jb_jobs_wrapper['salary'] ) . '"' : ''; ?>
	data-wrapper-index="<?php echo esc_attr( $jb_jobs_shortcode_index ); ?>">

	<?php
	JB()->get_template_part( 'ajax-overlay', $jb_jobs_wrapper );

	JB()->get_template_part( 'jobs/search-bar', $jb_jobs_wrapper );

	/**
	 * Fires in Jobs List wrapper above the list and below the search bar.
	 *
	 * @since 1.2.5
	 * @hook jb_before_jobs_list
	 *
	 * @param {array} $args Arguments passed into template.
	 */
	do_action( 'jb_before_jobs_list', $jb_jobs_wrapper );

	JB()->get_template_part( 'jobs/list', $jb_jobs_wrapper );

	/**
	 * Fires in Jobs List footer below the list.
	 *
	 * @since 1.0
	 * @hook jb_jobs_head_after
	 *
	 * @param {array} $args Arguments passed into template.
	 */
	do_action( 'jb_jobs_footer', $jb_jobs_wrapper );
	?>
</div>
