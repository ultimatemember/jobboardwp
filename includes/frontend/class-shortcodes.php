<?php namespace jb\frontend;

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

			add_shortcode( 'jb_job', array( &$this, 'single_job' ) );
			add_shortcode( 'jb_jobs', array( &$this, 'jobs' ) );
			add_shortcode( 'jb_jobs_dashboard', array( &$this, 'jobs_dashboard' ) );
			add_shortcode( 'jb_job_categories_list', array( &$this, 'job_categories_list' ) );

			add_shortcode( 'jb_recent_jobs', array( &$this, 'recent_jobs' ) );

			add_action( 'init', array( &$this, 'block_editor_render' ), 11 );
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

			add_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10, 1 );

			// phpcs:disable WordPress.Security.NonceVerification -- getting value from GET line

			if ( empty( $_GET['job-id'] ) ) {
				// empty posting form
				// handle draft notice after submission to draft
				$posting_form        = JB()->frontend()->forms( array( 'id' => 'jb-job-submission' ) );
				$jobs_dashboard_link = JB()->common()->permalinks()->get_predefined_page_link( 'jobs-dashboard' );

				if ( ! empty( $_GET['msg'] ) ) {
					switch ( sanitize_key( $_GET['msg'] ) ) {
						case 'draft':
							/** @noinspection HtmlUnknownTarget */
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
								/** @noinspection HtmlUnknownTarget */
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

				remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );
				return ob_get_clean();

			} else {

				// getting job post if $_GET['job-id'] isn't empty
				// validate Job by ID

				$job_id = absint( $_GET['job-id'] );
				$job    = get_post( $job_id );

				if ( is_wp_error( $job ) || empty( $job ) ) {
					remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );
					return __( 'Wrong job', 'jobboardwp' );
				}

				if ( ! is_user_logged_in() && 0 !== (int) $job->post_author ) {

					ob_start();
					?>

					<p>
						<?php
						/** @noinspection HtmlUnknownTarget */
						// translators: %s: login link
						echo wp_kses( sprintf( __( '<a href="%s">Sign in</a> to post a job.', 'jobboardwp' ), esc_attr( wp_login_url( get_permalink() ) ) ), JB()->get_allowed_html( 'templates' ) );
						?>
					</p>

					<?php
					remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );
					return ob_get_clean();

				} elseif ( is_user_logged_in() && get_current_user_id() !== (int) $job->post_author ) {

					remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );
					return __( 'Wrong job', 'jobboardwp' );

				}

				if ( empty( $_GET['jb-preview'] ) ) {

					// edit job form
					if ( ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'jb-job-draft' . $job_id ) ) {
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

					if ( ! empty( $job ) && in_array( $job->post_status, array( 'publish' ), true ) && 0 === (int) JB()->options()->get( 'published-job-editing' ) ) {
						remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );
						return __( 'You haven\'t ability to edit this job.', 'jobboardwp' );
					}

					$atts['job'] = $job;

					wp_enqueue_script( 'jb-post-job' );
					wp_enqueue_style( 'jb-post-job' );

					ob_start();

					JB()->get_template_part( 'job-submission', $atts );
					remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );
					return ob_get_clean();

				} else {

					// preview job
					if ( ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'jb-job-preview' . $job_id ) ) {
						remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );
						return __( 'Security check wrong', 'jobboardwp' );
					}

					if ( 'jb-preview' !== $job->post_status ) {
						remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );
						return __( 'Wrong job preview', 'jobboardwp' );
					}

					$atts['job_id'] = $job->ID;

					wp_enqueue_script( 'jb-preview-job' );
					wp_enqueue_style( 'jb-preview-job' );

					ob_start();

					JB()->get_template_part( 'job-preview', $atts );

					remove_filter( 'safe_style_css', array( $this, 'add_display_css_attr' ), 10 );

					return ob_get_clean();
				}
			}
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

					$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

					ob_start();

					$visible_login = false;
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
						} else {
							if ( ! JB()->options()->get( 'account-username-generate' ) ) {
								echo wp_kses( __( 'If you don\'t have an account you can optionally create one below by entering your email address/username or <a href="#" id="jb-show-login-form">sign in</a>.', 'jobboardwp' ), JB()->get_allowed_html( 'templates' ) );
							} else {
								echo wp_kses( __( 'If you don\'t have an account you can optionally create one below by entering your email address or <a href="#" id="jb-show-login-form">sign in</a>.', 'jobboardwp' ), JB()->get_allowed_html( 'templates' ) );
							}
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
					?>

					<p>
						<?php
						/** @noinspection HtmlUnknownTarget */
						// translators: %s: login link
						echo wp_kses( sprintf( __( '<a href="%s">Sign in</a> to post a job.', 'jobboardwp' ), esc_attr( wp_login_url( get_permalink() ) ) ), JB()->get_allowed_html( 'templates' ) );
						?>
					</p>

					<?php
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

			return ob_get_clean();
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
			// there is possible to use 'shortcode_atts_jb_jobs' filter for getting customized $atts
			$atts = shortcode_atts(
				array(
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
				),
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

			$r = new \WP_Query( $query_args );

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


		public function block_editor_render() {
			$blocks = array(
				'jb-block/jb-job-post'             => array(
					'editor_script'   => 'jb_admin_blocks_shortcodes',
					'render_callback' => array( $this, 'jb_job_post_render' ),
				),
				'jb-block/jb-job'                  => array(
					'editor_script'   => 'jb_admin_blocks_shortcodes',
					'render_callback' => array( $this, 'jb_single_job_render' ),
					'attributes'      => array(
						'job_id' => array(
							'type' => 'string',
						),
					),
				),
				'jb-block/jb-jobs-dashboard'       => array(
					'editor_script'   => 'jb_admin_blocks_shortcodes',
					'render_callback' => array( $this, 'jb_jobs_dashboard_render' ),
				),
				'jb-block/jb-jobs-categories-list' => array(
					'editor_script'   => 'jb_admin_blocks_shortcodes',
					'render_callback' => array( $this, 'jb_jobs_categories_list_render' ),
				),
				'jb-block/jb-jobs-list'            => array(
					'editor_script'   => 'jb_admin_blocks_shortcodes',
					'render_callback' => array( $this, 'jb_jobs_list_render' ),
					'attributes'      => array(
						'user_id'              => array(
							'type' => 'string',
						),
						'per_page'             => array(
							'type'    => 'string',
							'default' => JB()->options()->get( 'jobs-list-pagination' ),
						),
						'no_logo'              => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-no-logo' ),
						),
						'hide_filled'          => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-filled' ),
						),
						'hide_expired'         => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-expired' ),
						),
						'hide_search'          => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-search' ),
						),
						'hide_location_search' => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-location-search' ),
						),
						'hide_filters'         => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-filters' ),
						),
						'hide_job_types'       => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-job-types' ),
						),
						'no_jobs_text'         => array(
							'type' => 'string',
						),
						'no_job_search_text'   => array(
							'type' => 'string',
						),
						'load_more_text'       => array(
							'type' => 'string',
						),
						'category'             => array(
							'type' => 'string',
						),
						'type'                 => array(
							'type' => 'string',
						),
						'orderby'              => array(
							'default' => 'date',
						),
						'order'                => array(
							'type'    => 'string',
							'default' => 'DESC',
						),
						'filled_only'          => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'isLoading'            => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'content'              => array(
							'type'    => 'string',
						),
					),
				),
				'jb-block/jb-recent-jobs'          => array(
					'editor_script'   => 'jb_admin_blocks_shortcodes',
					'render_callback' => array( $this, 'jb_recent_jobs_render' ),
					'attributes'      => array(
						'number'       => array(
							'type'    => 'number',
							'default' => 5,
						),
						'no_logo'      => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-no-logo' ),
						),
						'hide_filled'  => array(
							'type'    => 'string',
							'default' => JB()->options()->get( 'jobs-list-hide-filled' ),
						),
						'no_job_types' => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-job-types' ),
						),
						'remote_only'  => array(
							'type'    => 'boolean',
							'default' => 0,
						),
						'orderby'      => array(
							'default' => 'date',
							'type'    => 'string',
						),
						'type'         => array(
							'type'    => 'string',
							'default' => '',
						),
						'category'     => array(
							'type'    => 'string',
							'default' => '',
						),
					),
				),
			);

			foreach ( $blocks as $block_type => $block_data ) {
				register_block_type( $block_type, $block_data );
			}
		}


		public function jb_job_post_render( $atts ) {
			$shortcode = '[jb_post_job]';

			return apply_shortcodes( $shortcode );
		}


		public function jb_jobs_dashboard_render() {
			$shortcode = '[jb_jobs_dashboard]';

			return apply_shortcodes( $shortcode );
		}


		public function jb_jobs_categories_list_render() {
			$shortcode = '[jb_job_categories_list]';

			return apply_shortcodes( $shortcode );
		}


		public function jb_jobs_list_render( $atts ) {
			$shortcode = '[jb_jobs ';

			if ( isset( $atts['user_id'] ) && '' !== $atts['user_id'] ) {
				$shortcode .= ' employer-id="' . $atts['user_id'] . '"';
			}

			if ( $atts['per_page'] ) {
				$shortcode .= ' per-page="' . $atts['per_page'] . '"';
			}

			$shortcode .= ' no-logo="' . $atts['no_logo'] . '"';

			$shortcode .= ' hide-filled="' . $atts['hide_filled'] . '"';

			$shortcode .= ' hide-expired="' . $atts['hide_expired'] . '"';

			$shortcode .= ' hide-search="' . $atts['hide_search'] . '"';

			$shortcode .= ' hide-location-search="' . $atts['hide_location_search'] . '"';

			$shortcode .= ' hide-filters="' . $atts['hide_filters'] . '"';

			$shortcode .= ' hide-job-types="' . $atts['hide_job_types'] . '"';

			if ( isset( $atts['no_jobs_text'] ) && '' !== $atts['no_jobs_text'] ) {
				$shortcode .= ' no-jobs-text="' . $atts['no_jobs_text'] . '"';
			}

			if ( isset( $atts['no_job_search_text'] ) && '' !== $atts['no_job_search_text'] ) {
				$shortcode .= ' no-jobs-search-text="' . $atts['no_job_search_text'] . '"';
			}

			if ( isset( $atts['load_more_text'] ) && '' !== $atts['load_more_text'] ) {
				$shortcode .= ' load-more-text="' . $atts['load_more_text'] . '"';
			}

			if ( isset( $atts['category'] ) && '' !== $atts['category'] ) {
				$shortcode .= ' category="' . $atts['category'] . '"';
			}

			if ( isset( $atts['type'] ) && '' !== $atts['type'] ) {
				$shortcode .= ' type="' . $atts['type'] . '"';
			}

			if ( $atts['orderby'] ) {
				$shortcode .= ' orderby="' . $atts['orderby'] . '"';
			}

			if ( $atts['order'] ) {
				$shortcode .= ' order="' . $atts['order'] . '"';
			}

			$shortcode .= ' filled-only="' . $atts['filled_only'] . '"';

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}


		public function jb_single_job_render( $atts ) {
			$shortcode = '[jb_job';

			if ( $atts['job_id'] ) {
				$shortcode .= ' id="' . $atts['job_id'] . '"';
			}

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}


		public function jb_recent_jobs_render( $atts ) {
			$shortcode = '[jb_recent_jobs';

			if ( $atts['number'] ) {
				$shortcode .= ' number="' . $atts['number'] . '"';
			}

			$shortcode .= ' no_logo="' . $atts['no_logo'] . '"';

			if ( $atts['type'] ) {
				$shortcode .= ' type="' . $atts['type'] . '"';
			}

			if ( $atts['category'] ) {
				$shortcode .= ' category="' . $atts['category'] . '"';
			}

			$shortcode .= ' remote_only="' . $atts['remote_only'] . '"';

			if ( $atts['orderby'] ) {
				$shortcode .= ' orderby="' . $atts['orderby'] . '"';
			}

			$shortcode .= ' hide_filled="' . $atts['hide_filled'] . '"';

			$shortcode .= ' no_job_types="' . $atts['no_job_types'] . '"';

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}
	}
}
