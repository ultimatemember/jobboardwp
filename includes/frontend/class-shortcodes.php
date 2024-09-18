<?php
namespace jb\frontend;

use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		public function __construct() {
			// posting a job form
			add_shortcode( 'jb_post_job', array( &$this, 'job_post' ) );
			add_filter( 'jb_forms_before_render_section', array( &$this, 'render_section' ), 10, 3 );

			add_filter( 'login_form_middle', array( $this, 'add_login_form_hidden' ), 10, 2 );

			add_shortcode( 'jb_job', array( &$this, 'single_job' ) );
			add_shortcode( 'jb_jobs', array( &$this, 'jobs' ) );
			add_shortcode( 'jb_jobs_dashboard', array( &$this, 'jobs_dashboard' ) );
			add_shortcode( 'jb_job_categories_list', array( &$this, 'job_categories_list' ) );

			add_shortcode( 'jb_recent_jobs', array( &$this, 'recent_jobs' ) );

			add_shortcode( 'jb_company_details', array( &$this, 'company_details' ) );
		}

		public function add_login_form_hidden( $content, $args ) {
			if ( ! ( array_key_exists( 'form_id', $args ) && 'jb-loginform' === $args['form_id'] ) ) {
				return $content;
			}

			$content .= '<input type="hidden" name="jb_login_form" value="1" />';
			return $content;
		}

		/**
		 * Added for WP_Kses valid CSS attributes
		 *
		 * @param array $attrs
		 *
		 * @return array
		 */
		public function add_display_css_attr( $attrs ) {
			$attrs[] = 'display';
			$attrs[] = 'object-fit';
			return $attrs;
		}

		/**
		 * Jobs shortcode
		 * [jb_post_job /]
		 *
		 * @param array $atts
		 *
		 * @return string
		 * @since 1.0
		 */
		public function job_post( $atts = array() ) {
			// there is possible to use 'shortcode_atts_jb_post_job' filter for getting customized $atts
			$atts = shortcode_atts( array(), $atts, 'jb_post_job' );

			add_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ) );

			// phpcs:disable WordPress.Security.NonceVerification -- getting value from GET line

			if ( empty( $_GET['job-id'] ) ) {
				// empty posting form
				// handle draft notice after submission to draft
				$posting_form        = JB()->frontend()->forms( array( 'id' => 'jb-job-submission' ) );
				$jobs_dashboard_link = JB()->common()->permalinks()->get_predefined_page_link( 'jobs-dashboard' );

				if ( ! empty( $_GET['msg'] ) ) {
					switch ( sanitize_key( $_GET['msg'] ) ) {
						case 'draft':
							$posting_form->add_notice(
								// translators: %s: jobs dashboard page link
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
								$job_permalink = get_permalink( absint( $_GET['published-id'] ) );
								$posting_form->add_notice(
									// translators: %s: link to the published job
									sprintf( __( 'Job is posted successfully. To view your job <a href="%s">click here</a>', 'jobboardwp' ), $job_permalink ),
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

				// phpcs:enable WordPress.Security.NonceVerification -- getting value from GET line

				wp_enqueue_script( 'jb-post-job' );
				wp_enqueue_style( 'jb-post-job' );

				ob_start();

				JB()->get_template_part( 'job-submission', $atts );

				remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ) );
				return ob_get_clean();
			}

			// getting job post if $_GET['job-id'] isn't empty
			// validate Job by ID

			$job_id = absint( $_GET['job-id'] );
			$job    = get_post( $job_id );

			if ( empty( $job ) || is_wp_error( $job ) ) {
				remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ) );
				return __( 'Wrong job', 'jobboardwp' );
			}

			if ( 0 !== (int) $job->post_author && ! is_user_logged_in() ) {

				ob_start();
				?>

				<p>
					<?php
					// translators: %s: login link
					echo wp_kses( sprintf( __( '<a href="%s">Sign in</a> to post a job.', 'jobboardwp' ), esc_attr( wp_login_url( get_permalink() ) ) ), JB()->get_allowed_html( 'templates' ) );
					?>
				</p>

				<?php
				remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ) );
				return ob_get_clean();

			}

			if ( is_user_logged_in() && get_current_user_id() !== (int) $job->post_author ) {
				remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ) );
				return __( 'Wrong job', 'jobboardwp' );
			}

			if ( empty( $_GET['jb-preview'] ) ) {

				// edit job form
				if ( empty( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'jb-job-draft' . $job_id ) ) {
					remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );
					return __( 'Security check wrong', 'jobboardwp' );
				}

				$statuses = array( 'draft', 'publish', 'jb-preview', 'jb-expired' );
				if ( JB()->options()->get( 'pending-job-editing' ) ) {
					$statuses[] = 'pending';
				}

				if ( ! in_array( $job->post_status, $statuses, true ) ) {
					remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );
					return __( 'Wrong job', 'jobboardwp' );
				}

				if ( 'publish' === $job->post_status && 0 === (int) JB()->options()->get( 'published-job-editing' ) ) {
					remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );
					return __( 'You haven\'t ability to edit this job.', 'jobboardwp' );
				}

				$atts['job'] = $job;

				wp_enqueue_script( 'jb-post-job' );
				wp_enqueue_style( 'jb-post-job' );

				ob_start();

				JB()->get_template_part( 'job-submission', $atts );
				remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ) );
				return ob_get_clean();

			}

			// preview job
			if ( empty( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'jb-job-preview' . $job_id ) ) {
				remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ) );
				return __( 'Security check wrong', 'jobboardwp' );
			}

			if ( 'jb-preview' !== $job->post_status ) {
				remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ) );
				return __( 'Wrong job preview', 'jobboardwp' );
			}

			$atts['job_id'] = $job->ID;

			wp_enqueue_script( 'jb-preview-job' );
			wp_enqueue_style( 'jb-preview-job' );

			ob_start();

			JB()->get_template_part( 'job-preview', $atts );

			remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ) );

			return ob_get_clean();
		}

		/**
		 * Customize rendering 'my-details' section based on 'account-creation' option
		 *
		 * @param string $html
		 * @param array $section_data
		 * @param array $form_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_section( $html, $section_data, $form_data ) {
			if ( 'my-details' === $section_data['key'] ) {
				// phpcs:disable Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace -- needed for strict output style attribute
				if ( JB()->options()->get( 'account-creation' ) && ! is_user_logged_in() ) {

					$id     = isset( $form_data['id'] ) ? $form_data['id'] : 'jb-frontend-form-' . uniqid();
					$name   = isset( $form_data['name'] ) ? $form_data['name'] : $id;
					$action = isset( $form_data['action'] ) ? $form_data['action'] : '';
					$method = isset( $form_data['method'] ) ? $form_data['method'] : 'post';

					$data_attrs = isset( $form_data['data'] ) ? $form_data['data'] : array();
					$data_attr  = '';
					foreach ( $data_attrs as $key => $val ) {
						$data_attr .= " data-{$key}=\"" . esc_attr( $val ) . '" ';
					}

					add_filter( 'jb_forms_move_form_tag', '__return_true' );

					//use WP native function for fill $_SERVER variables by correct values
					wp_fix_server_vars();

					$redirect = '';
					if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
						$redirect = ( is_ssl() ? 'https://' : 'http://' ) . wp_unslash( $_SERVER['HTTP_HOST'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- HTTP_HOST ok
					}
					$redirect .= ! empty( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- REQUEST_URI ok

					ob_start();

					/**
					 * Filters the default visibility for the login form section on the job submission form.
					 *
					 * @since 1.2.2
					 * @hook jb_job_visible_login
					 *
					 * @param {bool} $visible_login Login form visibility. Set to true if need to display it.
					 *
					 * @return {bool} Login form visibility.
					 */
					$visible_login = apply_filters( 'jb_job_visible_login', false );
					// phpcs:ignore WordPress.Security.NonceVerification -- getting value from GET line
					if ( isset( $_GET['login'] ) && 'failed' === sanitize_key( $_GET['login'] ) ) {
						$visible_login = true;
					}
					?>

					<p id="jb-sign-in-notice" class="jb-form-pre-section-notice"<?php if ( $visible_login ) { ?> style="display: none;"<?php } ?>>
						<?php
						if ( JB()->options()->get( 'account-required' ) ) {
							if ( ! JB()->options()->get( 'account-username-generate' ) ) {
								echo wp_kses( __( 'If you don\'t have an account you can create one below by entering your email address/username or <a href="#" id="jb-show-login-form">sign in</a>.', 'jobboardwp' ), JB()->get_allowed_html( 'templates' ) );
							} else {
								echo wp_kses( __( 'If you don\'t have an account you can create one below by entering your email address or <a href="#" id="jb-show-login-form">sign in</a>.', 'jobboardwp' ), JB()->get_allowed_html( 'templates' ) );
							}
						} elseif ( ! JB()->options()->get( 'account-username-generate' ) ) {
							echo wp_kses( __( 'If you don\'t have an account you can optionally create one below by entering your email address/username or <a href="#" id="jb-show-login-form">sign in</a>.', 'jobboardwp' ), JB()->get_allowed_html( 'templates' ) );
						} else {
							echo wp_kses( __( 'If you don\'t have an account you can optionally create one below by entering your email address or <a href="#" id="jb-show-login-form">sign in</a>.', 'jobboardwp' ), JB()->get_allowed_html( 'templates' ) );
						}
						?>
					</p>

					<p id="jb-sign-up-notice" class="jb-form-pre-section-notice"<?php if ( ! $visible_login ) { ?> style="display: none;"<?php } ?>>
						<?php echo wp_kses( __( 'You could login below or <a href="#" id="jb-hide-login-form">create account</a>.', 'jobboardwp' ), JB()->get_allowed_html( 'templates' ) ); ?>
					</p>

					<div id="jb-login-form-wrapper"<?php if ( ! $visible_login ) { ?> style="display: none;"<?php } ?>>

						<?php
						// phpcs:ignore WordPress.Security.NonceVerification -- getting value from GET line
						if ( isset( $_GET['login'] ) && 'failed' === sanitize_key( $_GET['login'] ) ) {
							?>

							<span class="jb-frontend-form-error">
								<?php esc_html_e( 'Invalid username, email address or incorrect password.', 'jobboardwp' ); ?>
							</span>

							<?php
						}

						/**
						 * Fires before rendering a login form on the job submission page
						 *
						 * @since 1.2.2
						 * @hook jb_before_login_form
						 */
						do_action( 'jb_before_login_form' );

						$login_args = array(
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
							'jb_login_form'  => true,
						);

						echo wp_login_form( $login_args );
						?>

						<div class="clear"></div>
					</div>

					<?php
					echo wp_kses( '<form action="' . esc_attr( $action ) . '" method="' . esc_attr( $method ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" class="jb-form" ' . $data_attr . '>', JB()->get_allowed_html( 'templates' ) );

					$html .= ob_get_clean();
				} elseif ( ! JB()->options()->get( 'account-creation' ) && ! is_user_logged_in() ) {
					ob_start();
					if ( JB()->options()->get( 'account-required' ) ) {
						?>

						<p>
							<?php
							// translators: %s: login link
							echo wp_kses( sprintf( __( '<a href="%s">Sign in</a> to post a job.', 'jobboardwp' ), esc_attr( wp_login_url( get_permalink() ) ) ), JB()->get_allowed_html( 'templates' ) );
							?>
						</p>

						<?php
					} else {
						?>

						<p>
							<?php
							// translators: %s: login link
							echo wp_kses( sprintf( __( '<a href="%s">Sign in</a> to post a job or to do that as a guest.', 'jobboardwp' ), esc_attr( wp_login_url( get_permalink() ) ) ), JB()->get_allowed_html( 'templates' ) );
							?>
						</p>

						<?php
					}
					$html .= ob_get_clean();
				}
				// phpcs:enable Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace -- needed for strict output style attribute
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
		 *
		 * @since 1.0
		 */
		public function single_job( $atts = array() ) {
			// there is possible to use 'shortcode_atts_jb_job' filter for getting customized $atts
			$atts = shortcode_atts(
				array(
					'id'            => '',
					'ignore_status' => false, // internal argument
				),
				$atts,
				'jb_job'
			);

			if ( empty( $atts['id'] ) ) {
				return '';
			}

			$job = get_post( $atts['id'] );
			if ( empty( $job ) || is_wp_error( $job ) ) {
				return '';
			}

			if ( ! $atts['ignore_status'] && 'publish' !== $job->post_status ) {
				return '';
			}

			$atts['default_template_replaced'] = false;
			if ( JB()->frontend()->templates()->template_replaced ) {
				$atts['default_template_replaced'] = true;
			}

			wp_enqueue_script( 'jb-single-job' );
			wp_enqueue_style( 'jb-job' );

			ob_start();

			JB()->get_template_part( 'single-job', $atts );

			$content = ob_get_clean();

			remove_filter( 'the_content', array( JB()->frontend()->templates(), 'cpt_content' ) );

			return $content;
		}


		/**
		 * Jobs shortcode
		 * [jb_jobs /]
		 *
		 * @param array $atts
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function jobs( $atts = array() ) {
			global $jb_jobs_shortcode_index;

			if ( empty( $jb_jobs_shortcode_index ) ) {
				$jb_jobs_shortcode_index = 1;
			} else {
				++$jb_jobs_shortcode_index;
			}

			$default_args = array(
				'employer-id'          => '',
				'per-page'             => JB()->options()->get( 'jobs-list-pagination' ),
				'no-logo'              => JB()->options()->get( 'jobs-list-no-logo' ),
				'hide-filled'          => JB()->options()->get( 'jobs-list-hide-filled' ),
				'hide-expired'         => JB()->options()->get( 'jobs-list-hide-expired' ),
				'hide-search'          => JB()->options()->get( 'jobs-list-hide-search' ),
				'hide-location-search' => JB()->options()->get( 'jobs-list-hide-location-search' ),
				'hide-filters'         => JB()->options()->get( 'jobs-list-hide-filters' ),
				'hide-job-types'       => JB()->options()->get( 'jobs-list-hide-job-types' ),
				'no-jobs-text'         => __( 'No Jobs', 'jobboardwp' ),
				'no-jobs-search-text'  => __( 'No Jobs found', 'jobboardwp' ),
				'load-more-text'       => __( 'Load more jobs', 'jobboardwp' ),
				'category'             => '',
				'type'                 => '',
				'orderby'              => 'date',
				'order'                => 'DESC',
				'filled-only'          => false, //shortcode attribute only if attribute set 0||1
			);

			if ( JB()->options()->get( 'job-salary' ) ) {
				$default_args['salary'] = '';
			}

			// there is possible to use 'shortcode_atts_jb_jobs' filter for getting customized $atts
			$atts = shortcode_atts(
				$default_args,
				$atts,
				'jb_jobs'
			);

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
		 *
		 * @since 1.0
		 */
		public function jobs_dashboard( $atts = array() ) {
			// there is possible to use 'shortcode_atts_jb_jobs_dashboard' filter for getting customized $atts
			$atts = shortcode_atts(
				array(
					'columns' => array(
						'title'   => __( 'Title', 'jobboardwp' ),
						'status'  => __( 'Status', 'jobboardwp' ),
						'posted'  => __( 'Posted', 'jobboardwp' ),
						'expired' => __( 'Closing on', 'jobboardwp' ),
					),
				),
				$atts,
				'jb_jobs_dashboard'
			);

			wp_enqueue_script( 'jb-jobs-dashboard' );
			wp_enqueue_style( 'jb-jobs-dashboard' );

			ob_start();

			JB()->get_template_part( 'dashboard/jobs', $atts );

			return ob_get_clean();
		}


		/**
		 * Jobs dashboard shortcode
		 * [jb_job_categories_list /]
		 *
		 * @param array $atts
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function job_categories_list( $atts = array() ) {
			if ( ! JB()->options()->get( 'job-categories' ) ) {
				return '';
			}

			// there is possible to use 'shortcode_atts_jb_job_categories_list' filter for getting customized $atts
			$atts = shortcode_atts( array(), $atts, 'jb_job_categories_list' );

			wp_enqueue_script( 'jb-job-categories' );
			wp_enqueue_style( 'jb-job-categories' );

			ob_start();

			JB()->get_template_part( 'job-categories', $atts );

			return ob_get_clean();
		}


		/**
		 * The "Recent Jobs" shortcode
		 * [jb_recent_jobs /]
		 *
		 * @since  1.2.1
		 *
		 * @usedby Widget "JobBoardWP - Recent Jobs".
		 *
		 * @param  array $atts An array of attributes.
		 * @return string
		 */
		public function recent_jobs( $atts = array() ) {
			$default = array(
				'number'       => 5,
				'category'     => '',
				'type'         => '',
				'remote_only'  => false,
				'orderby'      => 'date',
				'hide_filled'  => JB()->options()->get( 'jobs-list-hide-filled' ),
				'no_logo'      => JB()->options()->get( 'jobs-list-no-logo' ),
				'no_job_types' => JB()->options()->get( 'jobs-list-hide-job-types' ),
			);

			$args = shortcode_atts( $default, $atts, 'jb_recent_jobs' );

			$numberposts = absint( $args['number'] );

			$query_args = array(
				'post_type'      => 'jb-job',
				'post_status'    => array( 'publish' ),
				'posts_per_page' => ( $numberposts <= 99 ) ? $numberposts : 99,
				'order'          => 'DESC',
			);

			$types = array();
			if ( ! empty( $args['type'] ) ) {
				$types = array_map( 'absint', array_map( 'trim', explode( ',', $args['type'] ) ) );
			}
			if ( ! empty( $types ) ) {
				$query_args['tax_query'][] = array(
					'taxonomy' => 'jb-job-type',
					'field'    => 'id',
					'terms'    => $types,
				);
			}

			if ( JB()->options()->get( 'job-categories' ) ) {
				$categories = array();
				if ( ! empty( $args['category'] ) ) {
					$categories = array_map( 'absint', array_map( 'trim', explode( ',', $args['category'] ) ) );
				}
				if ( ! empty( $categories ) ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => 'jb-job-category',
						'field'    => 'id',
						'terms'    => $categories,
					);
				}
			}

			$remote_only = (bool) $args['remote_only'];
			if ( $remote_only ) {
				if ( ! isset( $query_args['meta_query'] ) ) {
					$query_args['meta_query'] = array();
				}

				$query_args['meta_query'] = array_merge(
					$query_args['meta_query'],
					array(
						'relation' => 'AND',
						array(
							'key'     => 'jb-location-type',
							'value'   => '1',
							'compare' => '=',
						),
					)
				);
			}

			if ( ! empty( $args['hide_filled'] ) ) {
				if ( ! isset( $query_args['meta_query'] ) ) {
					$query_args['meta_query'] = array();
				}

				$query_args['meta_query'] = array_merge(
					$query_args['meta_query'],
					array(
						'relation' => 'AND',
						array(
							'relation' => 'OR',
							array(
								'key'   => 'jb-is-filled',
								'value' => false,
							),
							array(
								'key'   => 'jb-is-filled',
								'value' => 0,
							),
							array(
								'key'     => 'jb-is-filled',
								'compare' => 'NOT EXISTS',
							),
						),
					)
				);
			}

			if ( 'expiry_date' === sanitize_key( $args['orderby'] ) ) {
				if ( ! isset( $query_args['meta_query'] ) ) {
					$query_args['meta_query'] = array();
				}

				$query_args['meta_query'][] = array(
					'relation'    => 'OR',
					'expiry_date' => array(
						'key'     => 'jb-expiry-date',
						'compare' => 'EXISTS',
						'type'    => 'DATE',
					),
					array(
						'key'     => 'jb-expiry-date',
						'compare' => 'NOT EXISTS',
					),
				);

				$query_args['orderby'] = array(
					'expiry_date' => 'DESC',
					'date'        => 'DESC',
				);
				unset( $query_args['order'] );
			} else {
				$query_args['orderby'] = 'date';
			}

			$r = new WP_Query( $query_args );

			if ( ! $r->have_posts() ) {
				return '';
			}

			wp_enqueue_script( 'jb-front-global' );
			wp_enqueue_style( 'jb-jobs-widget' );

			$attrs = array(
				'posts' => array(),
				'args'  => $args,
			);
			foreach ( $r->posts as $recent_job ) {
				$job_company_data = JB()->common()->job()->get_company_data( $recent_job->ID );

				$title = esc_html( get_the_title( $recent_job ) );
				$title = ! empty( $title ) ? $title : __( '(no title)', 'jobboardwp' );

				$job_data = array(
					'title'     => $title,
					'permalink' => get_permalink( $recent_job ),
					'date'      => JB()->common()->job()->get_posted_date( $recent_job->ID ),
					'expires'   => JB()->common()->job()->get_expiry_date( $recent_job->ID ),
					'company'   => array(
						'name'    => $job_company_data['name'],
						'tagline' => $job_company_data['tagline'],
					),
					'location'  => JB()->common()->job()->get_location_link( $recent_job->ID ),
				);

				if ( JB()->options()->get( 'job-categories' ) ) {
					$job_data['category'] = JB()->common()->job()->get_job_category( $recent_job->ID );
				}

				$amount_output = JB()->common()->job()->get_formatted_salary( $recent_job->ID );
				if ( '' !== $amount_output ) {
					$job_data['salary'] = $amount_output;
				}

				if ( ! $args['no_logo'] ) {
					$job_data['logo'] = JB()->common()->job()->get_logo( $recent_job->ID );
				}

				if ( ! $args['no_job_types'] ) {
					$data_types = array();
					$types      = wp_get_post_terms(
						$recent_job->ID,
						'jb-job-type',
						array(
							'orderby' => 'name',
							'order'   => 'ASC',
						)
					);
					foreach ( $types as $type ) {
						$data_types[] = array(
							'name'     => $type->name,
							'color'    => get_term_meta( $type->term_id, 'jb-color', true ),
							'bg_color' => get_term_meta( $type->term_id, 'jb-background', true ),
						);
					}

					$job_data['types'] = $data_types;
				}

				$attrs['posts'][] = $job_data;
			}

			add_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10, 1 );

			$content = JB()->get_template_html( 'widgets/recent-jobs', $attrs );

			remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );
			return $content;
		}


		/**
		 * The "Company detailds" shortcode
		 * [jb_company_details /]
		 *
		 * @since  1.2.6
		 *
		 * @param  array $atts An array of attributes.
		 * @return string
		 */
		public function company_details( $atts = array() ) {
			$atts = shortcode_atts( array(), $atts, 'jb_company_details' );

			if ( ! is_user_logged_in() ) {
				return '';
			}

			$company_details_form = JB()->frontend()->forms(
				array(
					'id' => 'jb-company-details',
				)
			);

			// phpcs:disable WordPress.Security.NonceVerification -- getting value from GET line
			if ( ! empty( $_GET['msg'] ) ) {
				switch ( sanitize_key( $_GET['msg'] ) ) {
					case 'updated':
						$company_details_form->add_notice(
							__( 'Company details are updated successfully.', 'jobboardwp' ),
							'updated'
						);
						break;
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification -- getting value from GET line

			wp_enqueue_script( 'jb-front-forms' );
			wp_enqueue_style( 'jb-forms' );

			ob_start();

			JB()->get_template_part( 'company-details', $atts );

			return ob_get_clean();
		}
	}
}
