<?php namespace jb\frontend;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\frontend\Shortcodes' ) ) {


	/**
	 * Class Shortcodes
	 *
	 * @package jb\frontend
	 */
	class Shortcodes {


		/**
		 * Shortcodes constructor.
		 */
		function __construct() {
			add_shortcode( 'jb_jobs', [ &$this, 'jobs' ] );
			add_shortcode( 'jb_job', [ &$this, 'single_job' ] );
			add_shortcode( 'jb_post_job', [ &$this, 'job_post' ] );
			add_shortcode( 'jb_jobs_dashboard', [ &$this, 'jobs_dashboard' ] );
			//add_shortcode( 'jb_employer_dashboard', [ &$this, 'employer_dashboard' ] );
		}


		/**
		 * Jobs shortcode
		 * [jb_jobs /]
		 *
		 * @param array $atts
		 *
		 * @return string
		 */
		function jobs( $atts ) {
			$default = apply_filters( 'jb_jobs_shortcode_default_atts', [] );

			$atts = shortcode_atts( $default, $atts );
			$atts = apply_filters( 'jb_jobs_shortcode_atts', $atts );

			wp_enqueue_script( 'jb-jobs' );
			wp_enqueue_style( 'jb-jobs' );

			ob_start();

			JB()->get_template_part( 'jobs/wrapper' );

			return ob_get_clean();
		}


		/**
		 * Single job shortcode
		 * [jb_job /]
		 *
		 * @param array $atts
		 *
		 * @return string
		 */
		function single_job( $atts ) {
			$default = apply_filters( 'jb_job_shortcode_default_atts', [
				'id'    => '',
			] );

			$atts = shortcode_atts( $default, $atts );
			$atts = apply_filters( 'jb_job_shortcode_atts', $atts );

			if ( empty( $atts['id'] ) ) {
				return '';
			}

			wp_enqueue_script( 'jb-single-job' );
			wp_enqueue_style( 'jb-job' );

			ob_start();

			JB()->get_template_part( 'single-job', $atts );

			return ob_get_clean();
		}


		/**
		 * Jobs shortcode
		 * [jb_post_job /]
		 *
		 * @param array $atts
		 *
		 * @return string
		 */
		function job_post( $atts ) {
			$default = apply_filters( 'jb_post_job_shortcode_default_atts', [] );

			$atts = shortcode_atts( $default, $atts );
			$atts = apply_filters( 'jb_post_job_shortcode_atts', $atts );

			if ( ! empty( $_GET['job-id'] ) && is_numeric( $_GET['job-id'] ) ) {

				if ( isset( $_GET['preview'] ) && 1 == $_GET['preview'] ) {
					$job_id = absint( $_GET['job-id'] );

					if ( wp_verify_nonce( $_GET['nonce'], 'jb-job-preview' . $job_id ) ) {
						$job = get_post( $job_id );
						if ( is_wp_error( $job ) || $job->post_status != 'jb-preview' ) {
							return __( 'Wrong job preview', 'jobboardwp' );
						} else {

							$atts['job'] = $job;

							wp_enqueue_script( 'jb-preview-job' );
							wp_enqueue_style( 'jb-preview-job' );

							ob_start();

							JB()->get_template_part( 'job-preview', $atts );

							return ob_get_clean();
						}
					} else {
						return __( 'Security check wrong', 'jobboardwp' );
					}
				} else {
					$job_id = absint( $_GET['job-id'] );

					if ( wp_verify_nonce( $_GET['nonce'], 'jb-job-draft' . $job_id ) ) {
						$job = get_post( $job_id );

						if ( is_wp_error( $job ) || $job->post_status != 'draft' ) {
							return __( 'Wrong job', 'jobboardwp' );
						} else {
							$atts['job'] = $job;
							$atts['account_required'] = JB()->options()->get( 'account-required' );
							$atts['registration_enabled'] = JB()->options()->get( 'account-creation' );
							$atts['use_username'] = ! JB()->options()->get( 'account-username-generate' );
							$atts['use_standard_password_email'] = JB()->options()->get( 'account-password-email' );
							$atts['job_categories'] = JB()->options()->get( 'job-categories' );

							wp_enqueue_script( 'jb-post-job' );
							wp_enqueue_style( 'jb-post-job' );

							ob_start();

							JB()->get_template_part( 'job-submission', $atts );

							return ob_get_clean();
						}
					} else {
						return __( 'Security check wrong', 'jobboardwp' );
					}
				}
			} else {

				if ( ! empty( $_GET['msg'] ) ) {
					switch ( sanitize_key( $_GET['msg'] ) ) {
						case 'submitted':
							JB()->frontend()->forms()->add_notice( sprintf( __( 'Job\'s draft was saved. You could resumed it from the&nbsp;<a href="%s" title="Job Dashboard">job dashboard</a>', 'jobboardwp' ), JB()->permalinks()->get_preset_page_link( 'jobs-dashboard' ) ), 'submitted' );
							break;
					}
				}

				$atts['account_required'] = JB()->options()->get( 'account-required' );
				$atts['registration_enabled'] = JB()->options()->get( 'account-creation' );
				$atts['use_username'] = ! JB()->options()->get( 'account-username-generate' );
				$atts['use_standard_password_email'] = JB()->options()->get( 'account-password-email' );
				$atts['job_categories'] = JB()->options()->get( 'job-categories' );

				wp_enqueue_script( 'jb-post-job' );
				wp_enqueue_style( 'jb-post-job' );

				ob_start();

				JB()->get_template_part( 'job-submission', $atts );

				return ob_get_clean();
			}
		}


		/**
		 * Jobs dashboard shortcode
		 * [jb_jobs_dashboard /]
		 *
		 * @param array $atts
		 *
		 * @return string
		 */
		function jobs_dashboard( $atts ) {
			$default = apply_filters( 'jb_jobs_dashboard_shortcode_default_atts', [] );

			$atts = shortcode_atts( $default, $atts );
			$atts = apply_filters( 'jb_jobs_dashboard_shortcode_atts', $atts );

			wp_enqueue_script( 'jb-jobs-dashboard' );
			wp_enqueue_style( 'jb-jobs-dashboard' );

			ob_start();

			JB()->get_template_part( 'dashboard/jobs', $atts );

			return ob_get_clean();
		}


		/**
		 * Employer dashboard shortcode
		 * [jb_employer_dashboard /]
		 *
		 * @param array $atts
		 *
		 * @return string
		 */
		function employer_dashboard( $atts ) {
			$default = apply_filters( 'jb_employer_dashboard_shortcode_default_atts', [] );

			$atts = shortcode_atts( $default, $atts );
			$atts = apply_filters( 'jb_employer_dashboard_shortcode_atts', $atts );

			ob_start();

			JB()->get_template_part( 'dashboard/employer', $atts );

			return ob_get_clean();
		}
	}
}