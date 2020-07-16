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

			// posting a job form
			add_shortcode( 'jb_post_job', [ &$this, 'job_post' ] );
			add_filter( 'jb_forms_before_render_section', [ &$this, 'render_section' ], 10, 3 );



			add_shortcode( 'jb_job', [ &$this, 'single_job' ] );
			add_shortcode( 'jb_jobs', [ &$this, 'jobs' ] );
			add_shortcode( 'jb_jobs_dashboard', [ &$this, 'jobs_dashboard' ] );
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

			if ( empty( $_GET['job-id'] ) ) {

				// empty posting form
				// handle draft notice after submission to draft

				$posting_form = JB()->frontend()->forms( [ 'id' => 'jb-job-submission', ] );
				$jobs_dashboard_link = JB()->common()->permalinks()->get_preset_page_link( 'jobs-dashboard' );

				if ( ! empty( $_GET['msg'] ) ) {
					switch ( sanitize_key( $_GET['msg'] ) ) {
						case 'draft':
							$posting_form->add_notice(
								sprintf( __( 'Job\'s draft was saved. You could resumed it from the <a href="%s" title="Job Dashboard">job dashboard</a>', 'jobboardwp' ), $jobs_dashboard_link ),
								'draft'
							);

							break;
						case 'on-moderation':
							$notice = JB()->options()->get( 'job-submitted-notice' );
							$notice = ! empty( $notice ) ? $notice : __( 'Job is submitted successfully. It will be visible once approved.', 'jobboardwp' );

							$posting_form->add_notice(
								$notice,
								'on-moderation'
							);

							break;
						case 'published':

							if ( ! empty( $_GET['published-id'] ) ) {
								$posting_form->add_notice(
									sprintf( __( 'Job is posted successfully. To view your job <a href="%s">click here</a>', 'jobboardwp' ), get_permalink( $_GET['published-id'] ) ),
									'published'
								);
							} else {
								$posting_form->add_notice(
									__( 'Job is posted successfully.', 'jobboardwp' ),
									'published'
								);
							}

							break;
					}
				}

				wp_enqueue_script( 'jb-post-job' );
				wp_enqueue_style( 'jb-post-job' );

				ob_start();

				JB()->get_template_part( 'job-submission', $atts );

				return ob_get_clean();

			} else {

				// getting job post if $_GET['job-id'] isn't empty
				// validate Job by ID

				$job_id = absint( $_GET['job-id'] );
				$job = get_post( $job_id );

				if ( is_wp_error( $job ) || empty( $job ) ) {
					return __( 'Wrong job', 'jobboardwp' );
				}

				if ( ! is_user_logged_in() && $job->post_author != 0 ) {

					ob_start(); ?>

					<p>
						<?php printf( __( '<a href="%s">Sign in</a> to post a job.', 'jobboardwp' ), wp_login_url( get_permalink() ) ); ?>
					</p>

					<?php return ob_get_clean();

				} elseif ( is_user_logged_in() && get_current_user_id() != $job->post_author ) {

					return __( 'Wrong job', 'jobboardwp' );

				}

				if ( empty( $_GET['preview'] ) ) {

					// edit job form
					if ( ! wp_verify_nonce( $_GET['nonce'], 'jb-job-draft' . $job_id ) ) {
						return __( 'Security check wrong', 'jobboardwp' );
					}

					if ( ! in_array( $job->post_status, [ 'draft', 'publish', 'jb-preview', 'jb-expired' ] ) ) {
						return __( 'Wrong job', 'jobboardwp' );
					}

					if ( ! empty( $job ) && in_array( $job->post_status, [ 'publish' ] ) && JB()->options()->get( 'published-job-editing' ) == '0' ) {
						return __( 'You haven\'t ability to edit this job.', 'jobboardwp' );
					}

					$atts['job'] = $job;

					wp_enqueue_script( 'jb-post-job' );
					wp_enqueue_style( 'jb-post-job' );

					ob_start();

					JB()->get_template_part( 'job-submission', $atts );

					return ob_get_clean();

				} else {

					// preview job
					if ( ! wp_verify_nonce( $_GET['nonce'], 'jb-job-preview' . $job_id ) ) {
						return __( 'Security check wrong', 'jobboardwp' );
					}

					if ( $job->post_status != 'jb-preview' ) {
						return __( 'Wrong job preview', 'jobboardwp' );
					}

					$atts['job_id'] = $job->ID;

					wp_enqueue_script( 'jb-preview-job' );
					wp_enqueue_style( 'jb-preview-job' );

					ob_start();

					JB()->get_template_part( 'job-preview', $atts );

					return ob_get_clean();
				}
			}
		}


		/**
		 * @param $html
		 * @param $section_data
		 * @param $form_data
		 *
		 * @return string
		 */
		function render_section( $html, $section_data, $form_data ) {
			if ( $section_data['key'] == 'my-details' ) {

				if ( JB()->options()->get( 'account-creation' ) && ! is_user_logged_in() ) {

					$id = isset( $form_data['id'] ) ? $form_data['id'] : 'jb-frontend-form-' . uniqid();
					$name = isset( $form_data['name'] ) ? $form_data['name'] : $id;
					$action = isset( $form_data['action'] ) ? $form_data['action'] : '';
					$method = isset( $form_data['method'] ) ? $form_data['method'] : 'post';

					$data_attrs = isset( $form_data['data'] ) ? $form_data['data'] : [];
					$data_attr = '';
					foreach ( $data_attrs as $key => $val ) {
						$data_attr .= " data-{$key}=\"{$val}\" ";
					}

					add_filter( 'jb_forms_move_form_tag', '__return_true' );

					//use WP native function for fill $_SERVER variables by correct values
					wp_fix_server_vars();

					$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

					ob_start(); ?>

					<p id="jb-sign-in-notice" class="jb-form-pre-section-notice">
						<?php if ( JB()->options()->get( 'account-required' ) ) {
							if ( ! JB()->options()->get( 'account-username-generate' ) ) {
								_e( 'If you don\'t have an account you can create one below by entering your email address/username or <a href="javascript:void(0);" id="jb-show-login-form">sign in</a>.', 'jobboardwp' );
							} else {
								_e( 'If you don\'t have an account you can create one below by entering your email address or <a href="javascript:void(0);" id="jb-show-login-form">sign in</a>.', 'jobboardwp' );
							}
						} else {
							if ( ! JB()->options()->get( 'account-username-generate' ) ) {
								_e( 'If you don\'t have an account you can optionally create one below by entering your email address/username or <a href="javascript:void(0);" id="jb-show-login-form">sign in</a>.', 'jobboardwp' );
							} else {
								_e( 'If you don\'t have an account you can optionally create one below by entering your email address or <a href="javascript:void(0);" id="jb-show-login-form">sign in</a>.', 'jobboardwp' );
							}
						} ?>

					</p>

					<p id="jb-sign-up-notice" class="jb-form-pre-section-notice" style="display: none;">
						<?php _e( 'You could login below or <a href="javascript:void(0);" id="jb-hide-login-form">create account</a>.', 'jobboardwp' ); ?>
					</p>

					<div id="jb-login-form-wrapper" style="display: none;">

						<?php if ( isset( $_GET['login'] ) && 'failed' === $_GET['login'] ) { ?>
							<span class="jb-frontend-form-error">
								<?php _e( 'Invalid username, email address or incorrect password.', 'jobboardwp' ) ?>
							</span>
						<?php }

						$login_args = [
							'echo'           => false,
							'remember'       => true,
							'redirect'       => $redirect,
							'form_id'        => 'jb-loginform',
							'id_username'    => 'user_login',
							'id_password'    => 'user_pass',
							'id_remember'    => 'rememberme',
							'id_submit'      => 'wp-submit',
							'label_username' => __( 'Username or Email Address', 'jobboardwp' ),
							'label_password' => __( 'Password', 'jobboardwp' ),
							'label_remember' => __( 'Remember Me', 'jobboardwp' ),
							'label_log_in'   => __( 'Log In', 'jobboardwp' ),
							'value_username' => '',
							'value_remember' => false,
						];

						echo wp_login_form( $login_args ); ?>

						<div class="clear"></div>

					</div>

					<form action="<?php echo esc_attr( $action ) ?>" method="<?php echo esc_attr( $method ) ?>"
						name="<?php echo esc_attr( $name ) ?>" id="<?php echo esc_attr( $id ) ?>" class="jb-form" <?php echo $data_attr ?>>

					<?php $html .= ob_get_clean();

				} elseif ( ! JB()->options()->get( 'account-creation' ) && ! is_user_logged_in() ) {
					ob_start(); ?>

					<p>
						<?php printf( __( '<a href="%s">Sign in</a> to post a job.', 'jobboardwp' ), wp_login_url( get_permalink() ) ); ?>
					</p>

					<?php $html .= ob_get_clean();
				}
			}

			return $html;
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
		 * [jb_jobs /]
		 *
		 * @param array $atts
		 *
		 * @return string
		 */
		function jobs( $atts ) {
			$default = apply_filters( 'jb_jobs_shortcode_default_atts', [
				'per-page'              => JB()->options()->get( 'jobs-list-pagination' ),
				'no-logo'               => JB()->options()->get( 'jobs-list-no-logo' ),
				'hide-filled'           => JB()->options()->get( 'jobs-list-hide-filled' ),
				'hide-expired'          => JB()->options()->get( 'jobs-list-hide-expired' ),
				'hide-search'           => JB()->options()->get( 'jobs-list-hide-search' ),
				'hide-location-search'  => JB()->options()->get( 'jobs-list-hide-location-search' ),
				'hide-filters'          => JB()->options()->get( 'jobs-list-hide-filters' ),
				'hide-job-types'        => JB()->options()->get( 'jobs-list-hide-job-types' ),
				'no-jobs-text'          => __( 'No Jobs', 'jobboardwp' ),
				'no-jobs-search-text'   => __( 'No Jobs found', 'jobboardwp' ),
				'load-more-text'        => __( 'Load more jobs','jobboardwp' ),
			] );

			$atts = shortcode_atts( $default, $atts );
			$atts = apply_filters( 'jb_jobs_shortcode_atts', $atts );

			wp_enqueue_script( 'jb-jobs' );
			wp_enqueue_style( 'jb-jobs' );

			ob_start();

			JB()->get_template_part( 'jobs/wrapper', $atts );

			return ob_get_clean();
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

			$atts['columns'] = apply_filters( 'jb_jobs_dashboard_header_columns', [
				'title'    => __( 'Title', 'jobboardwp' ),
				'status'   => __( 'Status', 'jobboardwp' ),
				'posted'   => __( 'Posted', 'jobboardwp' ),
				'expired'  => __( 'Closing on', 'jobboardwp' ),
			] );
			$atts = apply_filters( 'jb_jobs_dashboard_shortcode_atts', $atts );

			wp_enqueue_script( 'jb-jobs-dashboard' );
			wp_enqueue_style( 'jb-jobs-dashboard' );

			ob_start();

			JB()->get_template_part( 'dashboard/jobs', $atts );

			return ob_get_clean();
		}
	}
}