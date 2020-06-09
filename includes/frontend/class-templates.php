<?php namespace jb\frontend;


if ( ! defined( 'ABSPATH' ) ) exit;


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
		var $preloader_styles;


		/**
		 * Templates constructor.
		 */
		function __construct() {
			/**
			 * Handlers for single job template
			 */
			add_filter( 'single_template', [ &$this, 'cpt_template' ] );
			add_action( 'wp_loaded', [ &$this, 'change_wp_native_post_content' ] );
			add_action( 'wp_footer', [ $this, 'output_structured_data' ] );
		}


		/**
		 * Change WP native job post content
		 */
		function change_wp_native_post_content() {
			$template = JB()->options()->get( 'job-template' );
			if ( empty( $template ) ) {
				add_filter( 'the_content', [ &$this, 'before_job_content' ] );
				add_filter( 'the_content', [ &$this, 'after_job_content' ] );
			}
		}


		/**
		 * Check if the Forum or Topic has custom template, or load by default page template
		 *
		 * @param string $single_template
		 *
		 * @return string
		 */
		function cpt_template( $single_template ) {
			global $post;

			$template = JB()->options()->get( 'job-template' );
			if ( empty( $template ) ) {
				return $single_template;
			}

			if ( $post->post_type == 'jb-job' ) {
				add_filter( 'template_include', [ &$this, 'cpt_template_include' ], 10, 1 );
			}

			return $single_template;
		}


		/**
		 * If it's forum or topic individual page loading by default Page template from theme
		 *
		 * @param string $template
		 *
		 * @return string
		 */
		function cpt_template_include( $template ) {
			if ( JB()->frontend()->is_job_page() ) {
				$template = get_template_directory() . DIRECTORY_SEPARATOR . 'singular.php';
				$child_template = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'singular.php';
				if ( file_exists( $child_template ) ) {
					$template = $child_template;
				}

				// load index.php if page isn't found
				if ( ! file_exists( $template ) ) {
					$template = get_template_directory() . DIRECTORY_SEPARATOR . 'index.php';
					$child_template = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'index.php';
					if ( file_exists( $child_template ) ) {
						$template = $child_template;
					}
				}

				if ( ! file_exists( $template ) ) {
					return $template;
				}

				add_action( 'wp_head', array( &$this, 'on_wp_head_finish' ), 99999999 );
				add_filter( 'the_content', [ &$this, 'cpt_content' ], 10, 1 );
				add_filter( 'post_class', [ &$this, 'hidden_title_class' ], 10, 3 );
			}

			return apply_filters( 'jb_template_include', $template );
		}


		/**
		 * Clear the post title
		 */
		function on_wp_head_finish() {
			add_filter( 'the_title', [ $this, 'clear_title' ], 10, 2 );
		}


		/**
		 * Return empty title
		 * @param $title
		 * @param $post_id
		 *
		 * @return string
		 */
		function clear_title( $title, $post_id ) {
			$post = get_post( $post_id );

			if ( $post->post_type == 'jb-job' ) {
				$title = '';
			}

			return $title;
		}


		/**
		 * Set default content of the forum and topic page
		 *
		 * @param $content
		 *
		 * @return string
		 */
		function cpt_content( $content ) {
			global $post;

			remove_filter( 'the_title', [ $this, 'clear_title' ] );

			if ( JB()->frontend()->is_job_page() ) {
				$content = JB()->frontend()->shortcodes()->single_job( [ 'id' => $post->ID ] );
			}

			return $content;
		}


		/**
		 * Add hidden class if users need to add some custom CSS on page template to hide a header when title is hidden
		 *
		 * @param array $classes
		 * @param $class
		 * @param int $post_id
		 *
		 * @return array
		 */
		function hidden_title_class( $classes, $class, $post_id ) {
			$classes[] = 'jb-hidden-title';
			return $classes;
		}



		/**
		 * Add structured data to the footer of job listing pages.
		 */
		public function output_structured_data() {
			if ( ! is_singular( 'jb-job' ) ) {
				return;
			}

			if ( empty( $structured_data = JB()->common()->job()->get_structured_data( get_post() ) ) ) {
				return;
			}

			echo '<!-- Job Board Structured Data -->' . "\r\n";
			echo '<script type="application/ld+json">' . _wp_specialchars( wp_json_encode( $structured_data ), ENT_NOQUOTES, 'UTF-8', true ) . '</script>';
		}


		/**
		 * New menu
		 *
		 * @param string $element
		 * @param string $trigger
		 * @param array $items
		 */
		function dropdown_menu( $element, $trigger, $items = array() ) {
			?>

			<div class="jb-dropdown" data-element="<?php echo $element; ?>" data-trigger="<?php echo $trigger; ?>">
				<ul>
					<?php foreach ( $items as $k => $v ) { ?>
						<li><?php echo $v; ?></li>
					<?php } ?>
				</ul>
			</div>

			<?php
		}


		/**
		 * @param $content
		 *
		 * @return string
		 */
		function before_job_content( $content ) {
			global $post;

			if ( $post && $post->post_type == 'jb-job' && is_singular( 'jb-job' ) && is_main_query() && ! post_password_required() ) {

				$logo = JB()->common()->job()->get_logo( $post->ID );
				$job_company_data = JB()->common()->job()->get_company_data( $post->ID );

				ob_start();

				do_action( 'jb_before_job_content', $post->ID );

				if ( JB()->common()->job()->is_filled( $post->ID ) ) { ?>
                    <div class="jb-job-filled-notice"><i class="fas fa-exclamation-circle"></i><?php _e( 'This job has been filled', 'jobboardwp' ); ?></div>
				<?php } ?>

				<div class="jb jb-job-info">
					<div class="jb-job-info-row jb-job-info-row-first">
						<div class="jb-job-location">
							<i class="fas fa-map-marker-alt"></i>
							<?php echo JB()->common()->job()->get_location( $post->ID ) ?>
						</div>
					</div>
					<div class="jb-job-info-row jb-job-info-row-second">
						<div class="jb-job-types">
							<?php echo JB()->common()->job()->display_types( $post->ID ); ?>
						</div>
					</div>
				</div>

				<div class="jb jb-job-company">
					<div class="jb-job-company-info<?php echo empty( $logo ) ? ' jb-job-no-logo' : '' ?>">
						<?php if ( ! empty( $logo ) ) { ?>
							<div class="jb-job-logo">
								<?php echo $logo; ?>
							</div>
						<?php } ?>
						<div class="jb-job-company-title-tagline">
							<div class="jb-job-company-name"><strong><?php echo $job_company_data['name'] ?></strong></div>
							<div class="jb-job-company-tagline"><?php echo $job_company_data['tagline'] ?></div>
						</div>
					</div>
					<div class="jb-job-company-links">
						<?php if ( ! empty( $job_company_data['website'] ) ) { ?>
							<a href="<?php echo esc_url( $job_company_data['website'] ) ?>" target="_blank"><i class="fas fa-link"></i></a>
						<?php }

						if ( ! empty( $job_company_data['facebook'] ) ) { ?>
							<a href="<?php echo esc_url( 'https://facebook.com/' . $job_company_data['facebook'] ) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
						<?php }

						if ( ! empty( $job_company_data['instagram'] ) ) { ?>
							<a href="<?php echo esc_url( 'https://instagram.com/' . $job_company_data['instagram'] ) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
						<?php }

						if ( ! empty( $job_company_data['twitter'] ) ) { ?>
							<a href="<?php echo esc_url( 'https://twitter.com/' . $job_company_data['twitter'] ) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
						<?php } ?>
					</div>
				</div>

				<?php $content = ob_get_clean() . $content;
			}

			return $content;
		}


		/**
		 * @param $content
		 *
		 * @return string
		 */
		function after_job_content( $content ) {
			global $post;

			if ( $post && $post->post_type == 'jb-job' && is_singular( 'jb-job' ) && is_main_query() && ! post_password_required() ) {
				ob_start(); ?>

				<div class="jb-job-footer">
					<div class="jb-job-footer-row">
						<?php if ( JB()->common()->job()->can_applied( $post->ID ) ) { ?>
							<input type="button" class="jb-button" value="<?php esc_attr_e( 'Apply for job', 'jobboardwp' ); ?>" />
						<?php } ?>
					</div>
				</div>

				<?php do_action( 'jb_after_job_content', $post->ID );

				$content .= ob_get_clean();
			}

			return $content;
		}


		/**
		 * Check if preloader styles already loaded
		 *
		 * @param $size
		 * @param $display
		 *
		 * @return bool
		 */
		function check_preloader_css( $size, $display ) {
			if ( ! empty( $this->preloader_styles[ $size ][ $display ] ) ) {
				return true;
			} else {
				$this->preloader_styles[ $size ][ $display ] = true;
				return false;
			}
		}
	}
}