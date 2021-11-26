<?php namespace jb\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\frontend\Templates' ) ) {


	/**
	 * Class Templates
	 *
	 * @package jb\frontend
	 */
	class Templates {


		/**
		 * @var
		 */
		public $preloader_styles;


		/**
		 * @var
		 */
		public $template_replaced = false;


		/**
		 * Templates constructor.
		 */
		public function __construct() {
			// handle WordPress native post template and add before and after post content
			add_action( 'wp_loaded', array( &$this, 'change_wp_native_post_content' ) );

			/**
			 * Handlers for single job template
			 */
			add_filter( 'single_template', array( &$this, 'cpt_template' ) );
			add_action( 'wp_footer', array( $this, 'output_structured_data' ) );
		}


		/**
		 * Change WP native job post content
		 *
		 * @since 1.0
		 */
		public function change_wp_native_post_content() {
			$template = JB()->options()->get( 'job-template' );
			if ( empty( $template ) ) {
				// add scripts and styles, but later because wp_loaded is earlier
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_single_job' ), 9999 );

				add_filter( 'the_content', array( &$this, 'before_job_content' ), 99999 );
				add_filter( 'the_content', array( &$this, 'after_job_content' ), 99999 );
			}
		}


		/**
		 * Enqueue single job assets
		 *
		 * @since 1.0
		 */
		public function enqueue_single_job() {
			wp_enqueue_script( 'jb-single-job' );
			wp_enqueue_style( 'jb-job' );
		}


		/**
		 * Add JB block before the job post content
		 *
		 * @param string $content
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function before_job_content( $content ) {
			global $post;

			if ( $post && 'jb-job' === $post->post_type && is_singular( 'jb-job' ) && is_main_query() && ! post_password_required() ) {
				ob_start();
				?>

				<div class="jb">
					<?php
					/**
					 * Fires before displaying job data on front.
					 *
					 * Note: When the "Job Template" setting = "Wordpress native post template"
					 *
					 * @since 1.1.0
					 * @hook jb_before_job_content
					 *
					 * @param {int} $post_id Post ID.
					 */
					do_action( 'jb_before_job_content', $post->ID );

					if ( JB()->options()->get( 'job-breadcrumbs' ) ) {
						JB()->get_template_part( 'job/breadcrumbs', array( 'job_id' => $post->ID ) );
					}

					JB()->get_template_part( 'job/notices', array( 'job_id' => $post->ID ) );
					JB()->get_template_part( 'job/info', array( 'job_id' => $post->ID ) );
					JB()->get_template_part( 'job/company', array( 'job_id' => $post->ID ) );
					?>
				</div>

				<?php
				$content = ob_get_clean() . $content;
			}

			return $content;
		}


		/**
		 * Add JB block after the job post content
		 *
		 * @param string $content
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function after_job_content( $content ) {
			global $post;

			if ( $post && 'jb-job' === $post->post_type && is_singular( 'jb-job' ) && is_main_query() && ! post_password_required() ) {
				ob_start();
				?>

				<div class="jb">
					<?php
					JB()->get_template_part(
						'job/footer',
						array(
							'job_id' => $post->ID,
							'title'  => get_the_title( $post->ID ),
						)
					);

					/**
					 * Fires after displaying job data on front.
					 *
					 * Note: When the "Job Template" setting = "Wordpress native post template"
					 *
					 * @since 1.1.0
					 * @hook jb_after_job_content
					 *
					 * @param {int} $post_id Post ID.
					 */
					do_action( 'jb_after_job_content', $post->ID );
					?>
				</div>

				<?php
				$content .= ob_get_clean();
			}

			return $content;
		}


		/**
		 * Check if the Job has custom template, or load by default page template
		 *
		 * @param string $single_template
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function cpt_template( $single_template ) {
			global $post;

			$template = JB()->options()->get( 'job-template' );
			if ( empty( $template ) ) {
				return $single_template;
			}

			if ( 'jb-job' === $post->post_type ) {
				add_filter( 'twentytwenty_disallowed_post_types_for_meta_output', array( &$this, 'add_cpt_meta' ), 10, 1 );
				add_filter( 'template_include', array( &$this, 'cpt_template_include' ), 10, 1 );
				add_filter( 'has_post_thumbnail', array( &$this, 'hide_post_thumbnail' ), 10, 2 );
			}

			return $single_template;
		}


		/**
		 * Callback for twentytwenty WP native theme to disable showing post meta
		 *
		 * @param array $types
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function add_cpt_meta( $types ) {
			$types[] = 'jb-job';
			return $types;
		}


		/**
		 * Hide job thumbnail
		 *
		 * @param bool $has_thumbnail
		 * @param int|\WP_Post $post
		 *
		 * @return bool
		 *
		 * @since 1.0
		 */
		public function hide_post_thumbnail( $has_thumbnail, $post ) {
			if ( ! $post ) {
				$post = get_post( get_the_ID() );
			}

			if ( isset( $post->post_type ) && 'jb-job' === $post->post_type ) {
				$has_thumbnail = false;
			}

			return $has_thumbnail;
		}


		/**
		 * If it's job individual page loading by default Page template from theme
		 *
		 * @param string $template
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function cpt_template_include( $template ) {
			if ( JB()->frontend()->is_job_page() ) {

				$template_setting = JB()->options()->get( 'job-template' );
				if ( 'default' === $template_setting ) {
					$t              = get_template_directory() . DIRECTORY_SEPARATOR . 'singular.php';
					$child_template = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'singular.php';
					if ( file_exists( $child_template ) ) {
						$t = $child_template;
					}

					// load page.php if singular isn't found
					if ( ! file_exists( $t ) ) {
						$t              = get_template_directory() . DIRECTORY_SEPARATOR . 'page.php';
						$child_template = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'page.php';
						if ( file_exists( $child_template ) ) {
							$t = $child_template;
						}
					}

					// load index.php if page isn't found
					if ( ! file_exists( $t ) ) {
						$t              = get_template_directory() . DIRECTORY_SEPARATOR . 'index.php';
						$child_template = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'index.php';
						if ( file_exists( $child_template ) ) {
							$t = $child_template;
						}
					}

					if ( ! file_exists( $t ) ) {
						return $template;
					}

					add_action( 'wp_head', array( &$this, 'on_wp_head_finish' ), 99999999 );
					add_filter( 'the_content', array( &$this, 'cpt_content' ), 10, 1 );
					add_filter( 'post_class', array( &$this, 'hidden_title_class' ), 10, 1 );

					return apply_filters( 'jb_template_include', $t );
				} else {
					$t              = get_template_directory() . DIRECTORY_SEPARATOR . 'jobboardwp' . DIRECTORY_SEPARATOR . $template_setting . '.php';
					$child_template = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'jobboardwp' . DIRECTORY_SEPARATOR . $template_setting . '.php';
					if ( file_exists( $child_template ) ) {
						$t = $child_template;
					}
					return apply_filters( 'jb_template_include', $t );
				}
			}

			return $template;
		}


		/**
		 * Clear the post title
		 *
		 * @since 1.0
		 */
		public function on_wp_head_finish() {
			add_filter( 'the_title', array( $this, 'clear_title' ), 10, 2 );
		}


		/**
		 * Return empty title
		 *
		 * @param string $title
		 * @param int $post_id
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function clear_title( $title, $post_id ) {
			$post = get_post( $post_id );

			if ( ! empty( $post ) && 'jb-job' === $post->post_type ) {
				$title = '';
			}

			return $title;
		}


		/**
		 * Set default content of the job page
		 *
		 * @param string $content
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function cpt_content( $content ) {
			global $post;

			remove_filter( 'the_title', array( $this, 'clear_title' ) );
			remove_filter( 'has_post_thumbnail', array( &$this, 'hide_post_thumbnail' ) );

			if ( JB()->frontend()->is_job_page() ) {
				$this->template_replaced = true;

				$content = JB()->frontend()->shortcodes()->single_job(
					array(
						'id'            => $post->ID,
						'ignore_status' => true,
					)
				);
			}

			return $content;
		}


		/**
		 * Add hidden class if users need to add some custom CSS on page template to hide a header when title is hidden
		 *
		 * @param array $classes
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function hidden_title_class( $classes ) {
			$classes[] = 'jb-hidden-title';
			return $classes;
		}


		/**
		 * Add structured data to the footer of job listing pages.
		 *
		 * @since 1.0
		 */
		public function output_structured_data() {
			if ( ! is_singular( 'jb-job' ) ) {
				return;
			}

			if ( JB()->options()->get( 'disable-structured-data' ) ) {
				return;
			}

			$structured_data = JB()->common()->job()->get_structured_data( get_post() );

			if ( empty( $structured_data ) ) {
				return;
			}

			// test via this page https://developers.google.com/search/docs/advanced/structured-data
			echo '<!-- Job Board Structured Data -->' . "\r\n";
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- structured data output, early escaped
			echo '<script type="application/ld+json">' . _wp_specialchars( wp_json_encode( $structured_data ), ENT_NOQUOTES, 'UTF-8', true ) . '</script>';
		}


		/**
		 * Dropdown menu template
		 *
		 * @param string $element
		 * @param string $trigger
		 * @param array $items
		 *
		 * @since 1.0
		 */
		public function dropdown_menu( $element, $trigger, $items = array() ) {
			// !!!!Important: all links in the dropdown items must have "class" attribute
			?>

			<div class="jb-dropdown" data-element="<?php echo esc_attr( $element ); ?>" data-trigger="<?php echo esc_attr( $trigger ); ?>">
				<ul>
					<?php foreach ( $items as $k => $v ) { ?>
						<li><?php echo wp_kses( $v, JB()->get_allowed_html() ); ?></li>
					<?php } ?>
				</ul>
			</div>

			<?php
		}
	}
}
