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
			// handle Wordpress native post template and add before and after post content
			add_action( 'wp_loaded', [ &$this, 'change_wp_native_post_content' ] );

			/**
			 * Handlers for single job template
			 */
			add_filter( 'single_template', [ &$this, 'cpt_template' ] );
			add_action( 'wp_footer', [ $this, 'output_structured_data' ] );
		}


		/**
		 * Change WP native job post content
		 */
		function change_wp_native_post_content() {
			$template = JB()->options()->get( 'job-template' );
			if ( empty( $template ) ) {
				// add scripts and styles, but later because wp_loaded is earlier
				add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_single_job' ], 9999 );

				add_filter( 'the_content', [ &$this, 'before_job_content' ] );
				add_filter( 'the_content', [ &$this, 'after_job_content' ] );
			}
		}


		/**
		 *
		 */
		function enqueue_single_job() {
			wp_enqueue_script( 'jb-single-job' );
			wp_enqueue_style( 'jb-job' );
		}


		/**
		 * @param $content
		 *
		 * @return string
		 */
		function before_job_content( $content ) {
			global $post;

			if ( $post && $post->post_type == 'jb-job' && is_singular( 'jb-job' ) && is_main_query() && ! post_password_required() ) {

				ob_start(); ?>

				<div class="jb">
					<?php do_action( 'jb_before_job_content', $post->ID );

					JB()->get_template_part( 'job/notices', [ 'job_id' => $post->ID ] );
					JB()->get_template_part( 'job/info', [ 'job_id' => $post->ID ] );
					JB()->get_template_part( 'job/company', [ 'job_id' => $post->ID ] ); ?>
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

				<div class="jb">
					<?php JB()->get_template_part( 'job/footer', [ 'job_id' => $post->ID, 'title' => get_the_title( $post->ID ) ] );

					do_action( 'jb_after_job_content', $post->ID ); ?>
				</div>

				<?php $content .= ob_get_clean();
			}

			return $content;
		}


		/**
		 * Check if the Job has custom template, or load by default page template
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
				add_filter( 'twentytwenty_disallowed_post_types_for_meta_output', [ &$this, 'add_cpt_meta' ], 10, 1 );
				add_filter( 'template_include', [ &$this, 'cpt_template_include' ], 10, 1 );
				add_filter( 'has_post_thumbnail', [ &$this, 'hide_post_thumbnail' ], 10, 3 );
			}

			return $single_template;
		}


		/**
		 * @param $types
		 *
		 * @return array
		 */
		function add_cpt_meta( $types ) {
			$types[] = 'jb-job';
			return $types;
		}


		/**
		 * @param $has_thumbnail
		 * @param $post
		 * @param $thumbnail_id
		 *
		 * @return bool
		 */
		function hide_post_thumbnail( $has_thumbnail, $post, $thumbnail_id ) {

			if ( ! $post ) {
				$post = get_post( get_the_ID() );
			}

			if ( isset( $post->post_type ) && $post->post_type == 'jb-job' ) {
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
		 */
		function cpt_template_include( $template ) {

			if ( JB()->frontend()->is_job_page() ) {
				$t = get_template_directory() . DIRECTORY_SEPARATOR . 'singular.php';
				$child_template = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'singular.php';
				if ( file_exists( $child_template ) ) {
					$t = $child_template;
				}

				// load page.php if singular isn't found
				if ( ! file_exists( $t ) ) {
					$t = get_template_directory() . DIRECTORY_SEPARATOR . 'page.php';
					$child_template = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'page.php';
					if ( file_exists( $child_template ) ) {
						$t = $child_template;
					}
				}

				// load index.php if page isn't found
				if ( ! file_exists( $t ) ) {
					$t = get_template_directory() . DIRECTORY_SEPARATOR . 'index.php';
					$child_template = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'index.php';
					if ( file_exists( $child_template ) ) {
						$t = $child_template;
					}
				}

				if ( ! file_exists( $t ) ) {
					return $template;
				}

				add_action( 'wp_head', [ &$this, 'on_wp_head_finish' ], 99999999 );
				add_filter( 'the_content', [ &$this, 'cpt_content' ], 10, 1 );
				add_filter( 'post_class', [ &$this, 'hidden_title_class' ], 10, 3 );
			}

			return apply_filters( 'jb_template_include', $t );
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
		 * Set default content of the job page
		 *
		 * @param $content
		 *
		 * @return string
		 */
		function cpt_content( $content ) {
			global $post;

			remove_filter( 'the_title', [ $this, 'clear_title' ] );
			remove_filter( 'has_post_thumbnail', [ &$this, 'hide_post_thumbnail' ] );

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
		function output_structured_data() {
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
		function dropdown_menu( $element, $trigger, $items = [] ) {
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