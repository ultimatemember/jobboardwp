<?php namespace jb\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\admin\Enqueue' ) ) {


	/**
	 * Class Enqueue
	 *
	 * @package jb\admin
	 */
	class Enqueue extends \jb\common\Enqueue {

		/**
		 * Enqueue constructor.
		 */
		public function __construct() {
			parent::__construct();

			$this->url['admin']        = JB_URL . 'assets/admin/';
			$this->js_url['admin']     = JB_URL . 'assets/admin/js/';
			$this->css_url['admin']    = JB_URL . 'assets/admin/css/';
			$this->js_url['frontend']  = JB_URL . 'assets/frontend/js/';
			$this->css_url['frontend'] = JB_URL . 'assets/frontend/css/';

			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ), 11 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_gmap' ), 10 );

			add_action( 'load-post.php', array( &$this, 'maybe_job_page' ) );
			add_action( 'load-post-new.php', array( &$this, 'maybe_job_page' ) );

			global $wp_version;
			if ( version_compare( $wp_version, '5.8', '>=' ) ) {
				add_filter( 'block_categories_all', array( &$this, 'blocks_category' ), 10, 1 );
			} else {
				add_filter( 'block_categories', array( &$this, 'blocks_category' ), 10, 1 );
			}
			add_action( 'enqueue_block_editor_assets', array( &$this, 'block_editor' ), 11 );

			add_action( 'load-job-board_page_jb-settings', array( &$this, 'modules_page' ) );
		}

		/**
		 * @since 1.3.0
		 */
		public function modules_page() {
			add_action( 'admin_enqueue_scripts', array( &$this, 'modules_page_scripts' ) );
		}

		/**
		 * @since 1.3.0
		 */
		public function modules_page_scripts() {
			wp_register_style( 'jb-admin-modules', $this->css_url['admin'] . 'modules' . JB()->scrips_prefix . '.css', array(), JB_VERSION );
			wp_enqueue_style( 'jb-admin-modules' );
		}

		/**
		 * Register location autocomplete scripts if needed
		 *
		 * @since 1.0
		 */
		public function enqueue_gmap() {
			$key = JB()->options()->get( 'googlemaps-api-key' );
			if ( empty( $key ) ) {
				return;
			}

			wp_register_script(
				'jb-location-field',
				$this->js_url['admin'] . 'location_field' . JB()->scrips_prefix . '.js',
				array( 'jquery', 'wp-hooks', 'wp-i18n', 'wp-hooks' ),
				JB_VERSION,
				true
			);

			wp_localize_script(
				'jb-location-field',
				'jb_location_var',
				array(
					'api_key' => $key,
					'is_ssl'  => is_ssl(),
					'region'  => $this->get_g_locale(),
				)
			);
		}

		/**
		 * Register and enqueue admin scripts and styles
		 *
		 * @since 1.0
		 */
		public function admin_scripts() {
			wp_register_script( 'select2', $this->url['common'] . 'libs/select2/js/select2.full.min.js', array( 'jquery' ), JB_VERSION, true );

			wp_register_script(
				'jb-global',
				$this->js_url['admin'] . 'global' . JB()->scrips_prefix . '.js',
				array( 'jquery', 'wp-util', 'wp-i18n', 'wp-hooks' ),
				JB_VERSION,
				true
			);

			$localize_data = array_merge(
				$this->common_localize,
				array(
					'nonce' => wp_create_nonce( 'jb-backend-nonce' ),
				)
			);
			/**
			 * Filters the data array that needs to be localized inside wp-admin global JS.
			 *
			 * @since 1.1.0
			 * @hook jb_admin_enqueue_localize
			 *
			 * @param {array} $localize_data Array with some data for JS.
			 *
			 * @return {array} Data for localize in JS.
			 */
			$localize_data = apply_filters( 'jb_admin_enqueue_localize', $localize_data );
			wp_localize_script( 'jb-global', 'jb_admin_data', $localize_data );

			wp_register_script(
				'jb-helptip',
				$this->js_url['common'] . 'helptip' . JB()->scrips_prefix . '.js',
				array( 'jquery', 'jquery-ui-tooltip' ),
				JB_VERSION,
				true
			);

			$forms_deps = array( 'jquery', 'wp-util', 'jb-global', 'jb-helptip', 'wp-color-picker', 'jquery-ui-datepicker', 'select2' );

			$key = JB()->options()->get( 'googlemaps-api-key' );
			if ( ! empty( $key ) ) {
				$forms_deps[] = 'jb-location-field';
			}
			wp_register_script( 'jb-forms', $this->js_url['admin'] . 'forms' . JB()->scrips_prefix . '.js', $forms_deps, JB_VERSION, true );

			wp_register_script(
				'jb-validation',
				$this->js_url['admin'] . 'validation' . JB()->scrips_prefix . '.js',
				array( 'jquery' ),
				JB_VERSION,
				true
			);

			wp_register_style( 'select2', $this->url['common'] . 'libs/select2/css/select2' . JB()->scrips_prefix . '.css', array(), JB_VERSION );

			wp_register_style( 'jb-helptip', $this->css_url['common'] . 'helptip' . JB()->scrips_prefix . '.css', array( 'dashicons' ), JB_VERSION );
			wp_register_style( 'jb-common', $this->css_url['admin'] . 'common' . JB()->scrips_prefix . '.css', array(), JB_VERSION );
			wp_register_style( 'jb-forms', $this->css_url['admin'] . 'forms' . JB()->scrips_prefix . '.css', array( 'jb-common', 'jb-helptip', 'wp-color-picker', 'jquery-ui', 'select2' ), JB_VERSION );

			// Enqueue scripts and styles
			// Global at all wp-admin pages
			// Forms at JobBoard pages only

			wp_enqueue_script( 'jb-global' );

			if ( JB()->admin()->is_own_screen() ) {
				wp_enqueue_script( 'jb-forms' );
				wp_enqueue_style( 'jb-forms' );
			}

			// render blocks
			wp_register_style( 'jb-common-preview', $this->css_url['frontend'] . 'common' . JB()->scrips_prefix . '.css', array(), JB_VERSION );
			wp_register_style( 'jb-jobs-widget', $this->css_url['frontend'] . 'jobs-widget' . JB()->scrips_prefix . '.css', array( 'jb-common-preview' ), JB_VERSION );
			wp_register_style( 'jb-job', $this->css_url['frontend'] . 'job' . JB()->scrips_prefix . '.css', array( 'jb-common-preview' ), JB_VERSION );
			wp_register_style( 'jb-forms-preview', $this->css_url['frontend'] . 'forms' . JB()->scrips_prefix . '.css', array( 'jb-common-preview', 'jquery-ui' ), JB_VERSION );
			wp_register_style( 'jb-job-categories', $this->css_url['frontend'] . 'job-categories' . JB()->scrips_prefix . '.css', array(), JB_VERSION );
			wp_register_style( 'jb-jobs-dashboard', $this->css_url['frontend'] . 'jobs-dashboard' . JB()->scrips_prefix . '.css', array( 'jb-common' ), JB_VERSION );
			wp_register_style( 'jb-jobs', $this->css_url['frontend'] . 'jobs' . JB()->scrips_prefix . '.css', array( 'jb-common' ), JB_VERSION );

			wp_register_script( 'jb-job-categories', $this->js_url['frontend'] . 'job-categories' . JB()->scrips_prefix . '.js', array( 'jb-front-global' ), JB_VERSION, true );
			wp_register_script( 'jb-dropdown', $this->js_url['frontend'] . 'dropdown' . JB()->scrips_prefix . '.js', array( 'jquery' ), JB_VERSION, true );
			wp_register_script( 'jb-jobs-dashboard', $this->js_url['frontend'] . 'jobs-dashboard' . JB()->scrips_prefix . '.js', array( 'jb-front-global' ), JB_VERSION, true );
			wp_register_script( 'jb-front-global', $this->js_url['frontend'] . 'global' . JB()->scrips_prefix . '.js', array( 'jquery', 'wp-util', 'wp-i18n', 'wp-hooks', 'select2', 'jb-dropdown' ), JB_VERSION, true );

			$jobs_deps = array( 'jb-front-global' );
			$key       = JB()->options()->get( 'googlemaps-api-key' );
			if ( ! empty( $key ) ) {
				$forms_deps[] = 'jb-location-field';
				$jobs_deps[]  = 'jb-location-field';
			}
			wp_register_script( 'jb-jobs', $this->js_url['frontend'] . 'jobs' . JB()->scrips_prefix . '.js', $jobs_deps, JB_VERSION, true );
		}

		/**
		 *
		 */
		public function maybe_job_page() {
			add_action( 'admin_enqueue_scripts', array( &$this, 'validation_scripts' ), 13 );
		}

		/**
		 *
		 */
		public function validation_scripts() {
			if ( JB()->admin()->is_own_post_type() ) {
				wp_enqueue_script( 'jb-validation' );
			}
		}

		/**
		 * Add Gutenberg category for JobBoardWP shortcodes
		 *
		 * @param array $categories
		 *
		 * @return array
		 */
		public function blocks_category( $categories ) {
			return array_merge(
				$categories,
				array(
					array(
						'slug'  => 'jb-blocks',
						'title' => __( 'JobBoardWP', 'jobboardwp' ),
					),
				)
			);
		}

		/**
		 * Enqueue Gutenberg Block Editor assets
		 */
		public function block_editor() {
			global $current_screen;

			wp_register_style( 'jb_admin_blocks_shortcodes', $this->css_url['admin'] . 'blocks' . JB()->scrips_prefix . '.css', array(), JB_VERSION );
			wp_enqueue_style( 'jb_admin_blocks_shortcodes' );
			wp_register_script( 'jb_admin_blocks_shortcodes', $this->js_url['admin'] . 'blocks' . JB()->scrips_prefix . '.js', array( 'wp-i18n', 'wp-blocks', 'wp-components' ), JB_VERSION, true );

			wp_set_script_translations( 'jb_admin_blocks_shortcodes', 'jobboardwp' );

			$jb_options = array(
				'jobs-list-no-logo'              => JB()->options()->get( 'jobs-list-no-logo' ),
				'jobs-list-hide-filled'          => JB()->options()->get( 'jobs-list-hide-filled' ),
				'jobs-list-hide-expired'         => JB()->options()->get( 'jobs-list-hide-expired' ),
				'jobs-list-hide-search'          => JB()->options()->get( 'jobs-list-hide-search' ),
				'jobs-list-hide-location-search' => JB()->options()->get( 'jobs-list-hide-location-search' ),
				'jobs-list-hide-filters'         => JB()->options()->get( 'jobs-list-hide-filters' ),
				'jobs-list-hide-job-types'       => JB()->options()->get( 'jobs-list-hide-job-types' ),
			);
			if ( 'jb-job' === $current_screen->id ) {
				$jb_options['exclude_blocks'] = 1;
			}

			wp_localize_script( 'jb_admin_blocks_shortcodes', 'jb_blocks_options', $jb_options );

			wp_enqueue_script( 'jb_admin_blocks_shortcodes' );

			// render blocks
			wp_enqueue_style( 'jb-common' );
			wp_enqueue_style( 'jb-jobs-widget' );
			wp_enqueue_style( 'jb-font-awesome' );
			wp_enqueue_style( 'jb-job' );
			wp_enqueue_style( 'jb-forms-preview' );
			wp_enqueue_style( 'jb-job-categories' );
			wp_enqueue_style( 'jb-jobs-dashboard' );
			wp_enqueue_style( 'jb-jobs' );

			wp_register_script( 'jb-front-global', $this->js_url['frontend'] . 'global' . JB()->scrips_prefix . '.js', array( 'jb-helptip' ), JB_VERSION, true );
			wp_enqueue_script( 'jb-helptip' );
			wp_localize_script(
				'jb-front-global',
				'jb_front_data',
				array(
					'nonce' => wp_create_nonce( 'jb-frontend-nonce' ),
				)
			);
			wp_enqueue_script( 'jb-front-global' );
			wp_enqueue_script( 'jb-job-categories' );
			wp_enqueue_script( 'jb-dropdown' );
			wp_enqueue_script( 'jb-jobs-dashboard' );
			wp_enqueue_script( 'jb-jobs' );
		}
	}
}
