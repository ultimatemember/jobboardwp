<?php
namespace jb\frontend;

use WP_Block_Template;
use WP_Filesystem_Base;
use WP_Post;
use function WP_Filesystem;

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
		 * @var bool
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
			add_filter( 'archive_template', array( &$this, 'cpt_archive_template' ), 100, 2 );
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
					 * Note: When the "Job Template" setting = "WordPress native post template".
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
					 * Note: When the "Job Template" setting = "WordPress native post template".
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
				// check if block theme and change templale
				if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
					add_filter( 'get_block_templates', array( $this, 'jb_change_single_job_block_templates' ), 10, 1 );
				}
				add_filter( 'twentytwenty_disallowed_post_types_for_meta_output', array( &$this, 'add_cpt_meta' ), 10, 1 );
				add_filter( 'template_include', array( &$this, 'cpt_template_include' ), 10, 1 );
				add_filter( 'has_post_thumbnail', array( &$this, 'hide_post_thumbnail' ), 10, 2 );
			}

			return $single_template;
		}

		/**
		 * Change archive template
		 *
		 * @param string $template
		 * @param string $type
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function cpt_archive_template( $template, $type ) {
			if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
				if ( isset( get_queried_object()->taxonomy ) && ( 'jb-job-type' === get_queried_object()->taxonomy || 'jb-job-category' === get_queried_object()->taxonomy ) ) {
					if ( 'archive' === $type && 'default' === JB()->options()->get( 'job-archive-template' ) ) {
						add_filter( 'render_block_data', array( $this, 'jb_change_archive_template' ), 10, 2 );
					}
				}
			} elseif ( isset( get_queried_object()->taxonomy ) && ( 'jb-job-type' === get_queried_object()->taxonomy || 'jb-job-category' === get_queried_object()->taxonomy ) ) {
				if ( 'archive' === $type && 'default' === JB()->options()->get( 'job-archive-template' ) ) {
					$template = untrailingslashit( JB_PATH ) . '/templates/job-archive.php';
				}
			}

			return $template;
		}

		/**
		 * @param $pre_render
		 * @param $parsed_block
		 *
		 * @return mixed
		 */
		public function jb_change_archive_template( $pre_render, $parsed_block ) {
			$tax_id = get_queried_object_id();
			$attrs  = array();

			if ( 'jb-job-category' === get_queried_object()->taxonomy ) {
				$attrs = array(
					'category' => array(
						$tax_id,
					),
				);
			} elseif ( 'jb-job-type' === get_queried_object()->taxonomy ) {
				$attrs = array(
					'type' => array(
						$tax_id,
					),
				);
			}

			if ( 'core/group' === $parsed_block['blockName'] ) {
				$key_path = $this->search_path( 'core/query', $parsed_block );
				if ( false !== $key_path ) {
					$attrs_path = str_replace( '_blockName', '_attrs', $key_path );
					$this->set( $key_path, $parsed_block, 'jb-block/jb-jobs-list' );
					$this->set( $attrs_path, $parsed_block, $attrs );
				}
			}

			return $parsed_block;
		}

		/**
		 * @param string|array $path
		 * @param array        $parsed_block
		 * @param null         $value
		 *
		 * @return array|mixed|null
		 */
		private function set( $path, &$parsed_block = array(), $value = null ) {
			$path = explode( '_', $path );
			$temp = &$parsed_block;

			foreach ( $path as $key ) {
				$temp = &$temp[ $key ];
			}
			$temp = $value;

			return $temp;
		}

		/**
		 * @param $needle
		 * @param $haystack
		 *
		 * @return bool|int|string
		 */
		private function search_path( $needle, $haystack ) {
			if ( ! is_array( $haystack ) ) {
				return false;
			}

			foreach ( $haystack as $key => $value ) {
				if ( 'core/query' === $value && $value === $needle ) {
					return $key;
				}

				if ( is_array( $value ) ) {
					$key_result = $this->search_path( $needle, $value );
					if ( false !== $key_result ) {
						return $key . '_' . $key_result;
					}
				}
			}

			return false;
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
		 * @param int|WP_Post|array $post
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
					if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
						// add scripts and styles, but later because wp_loaded is earlier
						add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_single_job' ), 9999 );

						add_filter( 'the_content', array( &$this, 'before_job_content' ), 99999 );
						add_filter( 'the_content', array( &$this, 'after_job_content' ), 99999 );
						return $template;
					}

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
				} else {
					$t              = get_template_directory() . DIRECTORY_SEPARATOR . 'jobboardwp' . DIRECTORY_SEPARATOR . $template_setting . '.php';
					$child_template = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'jobboardwp' . DIRECTORY_SEPARATOR . $template_setting . '.php';
					if ( file_exists( $child_template ) ) {
						$t = $child_template;
					}
				}

				/**
				 * Filters the individual job page template.
				 *
				 * @since 1.0
				 * @hook jb_template_include
				 *
				 * @param {string} $template Path to the individual job page template.
				 *
				 * @return {array} Path to the individual job page template.
				 */
				return apply_filters( 'jb_template_include', $t );
			}

			return $template;
		}

		/**
		 * Change block template for single job
		 *
		 * @param WP_Block_Template[] $query_result Array of found block templates.
		 *
		 * @return array
		 *
		 * @since 1.2.4
		 */
		public function jb_change_single_job_block_templates( $query_result ) {
			$theme = wp_get_theme();

			global $wp_filesystem;

			if ( ! $wp_filesystem instanceof WP_Filesystem_Base ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';

				$credentials = request_filesystem_credentials( site_url() );
				WP_Filesystem( $credentials );
			}

			$template_contents = $wp_filesystem->get_contents( wp_normalize_path( JB_PATH . 'templates/block-templates/single.html' ) );
			$template_contents = str_replace(
				array(
					'~theme~',
					'~jb_single_job_content~',
				),
				array(
					$theme->get_stylesheet(),
					'[jb_job id="' . get_the_ID() . '" /]',
				),
				$template_contents
			);

			$new_block                 = new WP_Block_Template();
			$new_block->type           = 'wp_template';
			$new_block->theme          = $theme->get_stylesheet();
			$new_block->slug           = 'single';
			$new_block->id             = $theme->get_stylesheet() . '//single';
			$new_block->title          = 'single';
			$new_block->description    = '';
			$new_block->source         = 'plugin';
			$new_block->status         = 'publish';
			$new_block->has_theme_file = true;
			$new_block->is_custom      = true;
			$new_block->content        = $template_contents;
			$new_block->area           = 'uncategorized';

			$query_result[] = $new_block;

			return $query_result;
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
