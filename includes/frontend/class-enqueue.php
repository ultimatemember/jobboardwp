<?php
namespace jb\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\frontend\Enqueue' ) ) {

	/**
	 * Class Enqueue
	 *
	 * @package jb\frontend
	 */
	class Enqueue extends \jb\common\Enqueue {

		/**
		 * Enqueue constructor.
		 */
		public function __construct() {
			parent::__construct();

			$this->url['frontend']     = JB_URL . 'assets/frontend/';
			$this->js_url['frontend']  = JB_URL . 'assets/frontend/js/';
			$this->css_url['frontend'] = JB_URL . 'assets/frontend/css/';

			add_action( 'wp_enqueue_scripts', array( &$this, 'register_common_scripts' ), 10 );
			add_action( 'wp_enqueue_scripts', array( &$this, 'register_scripts' ), 20 );
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_gmap' ), 19 );
		}

		/**
		 * Google Maps enqueue
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
				$this->js_url['frontend'] . 'location_field' . JB()->scrips_prefix . '.js',
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
		 * Register frontend scripts
		 *
		 * @since 1.2.3
		 */
		public function register_common_scripts() {
			wp_register_script( 'select2', $this->url['common'] . 'libs/select2/js/select2.full.min.js', array( 'jquery' ), JB_VERSION, true );

			wp_register_script( 'jb-helptip', $this->js_url['common'] . 'helptip' . JB()->scrips_prefix . '.js', array( 'jquery', 'jquery-ui-tooltip' ), JB_VERSION, true );

			wp_register_script( 'jb-dropdown', $this->js_url['frontend'] . 'dropdown' . JB()->scrips_prefix . '.js', array( 'jquery' ), JB_VERSION, true );

			wp_register_script( 'jb-front-global', $this->js_url['frontend'] . 'global' . JB()->scrips_prefix . '.js', array( 'jquery', 'wp-util', 'wp-i18n', 'wp-hooks', 'select2', 'jb-dropdown', 'jb-helptip' ), JB_VERSION, true );

			$localize_data = array_merge(
				$this->common_localize,
				array(
					'nonce' => wp_create_nonce( 'jb-frontend-nonce' ),
				)
			);
			/**
			 * Filters the data array that needs to be localized inside frontend global JS.
			 *
			 * @since 1.0
			 * @hook jb_enqueue_localize
			 *
			 * @param {array} $localize_data Array with some data for JS.
			 *
			 * @return {array} Data for localize in JS.
			 */
			$localize_data = apply_filters( 'jb_enqueue_localize', $localize_data );
			wp_localize_script( 'jb-front-global', 'jb_front_data', $localize_data );
		}

		/**
		 * Register frontend scripts
		 *
		 * @since 1.0
		 */
		public function register_scripts() {
			$forms_deps = array( 'jb-front-global', 'jquery-ui-datepicker' );
			$jobs_deps  = array( 'jb-front-global' );

			$key = JB()->options()->get( 'googlemaps-api-key' );
			if ( ! empty( $key ) ) {
				$forms_deps[] = 'jb-location-field';
				$jobs_deps[]  = 'jb-location-field';
			}

			/**
			 * Filters the 'jb-jobs' script dependencies array.
			 *
			 * @since 1.0
			 * @hook jb_jobs_scripts_enqueue
			 *
			 * @param {array} $scripts Scripts dependencies.
			 *
			 * @return {array} Scripts dependencies.
			 */
			$jobs_deps = apply_filters( 'jb_jobs_scripts_enqueue', $jobs_deps );

			wp_register_script( 'jb-front-forms', $this->js_url['frontend'] . 'forms' . JB()->scrips_prefix . '.js', $forms_deps, JB_VERSION, true );

			wp_register_script( 'jb-jobs', $this->js_url['frontend'] . 'jobs' . JB()->scrips_prefix . '.js', $jobs_deps, JB_VERSION, true );
			wp_register_script( 'jb-post-job', $this->js_url['frontend'] . 'post-job' . JB()->scrips_prefix . '.js', array( 'jb-front-forms', 'plupload' ), JB_VERSION, true );
			wp_register_script( 'jb-single-job', $this->js_url['frontend'] . 'single-job' . JB()->scrips_prefix . '.js', array( 'jb-front-global' ), JB_VERSION, true );
			wp_register_script( 'jb-preview-job', $this->js_url['frontend'] . 'preview-job' . JB()->scrips_prefix . '.js', array( 'jb-front-global' ), JB_VERSION, true );
			wp_register_script( 'jb-jobs-dashboard', $this->js_url['frontend'] . 'jobs-dashboard' . JB()->scrips_prefix . '.js', array( 'jb-front-global' ), JB_VERSION, true );
			wp_register_script( 'jb-job-categories', $this->js_url['frontend'] . 'job-categories' . JB()->scrips_prefix . '.js', array( 'jb-front-global' ), JB_VERSION, true );

			wp_register_style( 'select2', $this->url['common'] . 'libs/select2/css/select2' . JB()->scrips_prefix . '.css', array(), JB_VERSION );

			$helptip_css_deps = array(
				'dashicons',
			);
			if ( $this->is_jquery_ui_enabled() ) {
				$helptip_css_deps[] = 'jquery-ui';
			}
			wp_register_style( 'jb-helptip', $this->css_url['common'] . 'helptip' . JB()->scrips_prefix . '.css', $helptip_css_deps, JB_VERSION );

			/**
			 * Filters the 'jb-common' style dependencies array.
			 *
			 * @since 1.0
			 * @hook jb_frontend_common_styles_deps
			 *
			 * @param {array} $styles Style dependencies.
			 *
			 * @return {array} Style dependencies.
			 */
			$common_frontend_deps = apply_filters( 'jb_frontend_common_styles_deps', array( 'select2', 'jb-helptip' ) );
			wp_register_style( 'jb-common', $this->css_url['frontend'] . 'common' . JB()->scrips_prefix . '.css', $common_frontend_deps, JB_VERSION );

			$forms_css_deps = array(
				'jb-common',
			);
			if ( $this->is_jquery_ui_enabled() ) {
				$forms_css_deps[] = 'jquery-ui';
			}
			wp_register_style( 'jb-forms', $this->css_url['frontend'] . 'forms' . JB()->scrips_prefix . '.css', $forms_css_deps, JB_VERSION );

			wp_register_style( 'jb-job', $this->css_url['frontend'] . 'job' . JB()->scrips_prefix . '.css', array( 'jb-common' ), JB_VERSION );
			wp_register_style( 'jb-jobs', $this->css_url['frontend'] . 'jobs' . JB()->scrips_prefix . '.css', array( 'jb-common' ), JB_VERSION );

			wp_register_style( 'jb-jobs-widget', $this->css_url['frontend'] . 'jobs-widget' . JB()->scrips_prefix . '.css', array( 'jb-common' ), JB_VERSION );

			wp_register_style( 'jb-post-job', $this->css_url['frontend'] . 'post-job' . JB()->scrips_prefix . '.css', array( 'jb-forms' ), JB_VERSION );
			wp_register_style( 'jb-preview-job', $this->css_url['frontend'] . 'preview-job' . JB()->scrips_prefix . '.css', array( 'jb-forms' ), JB_VERSION );
			wp_register_style( 'jb-jobs-dashboard', $this->css_url['frontend'] . 'jobs-dashboard' . JB()->scrips_prefix . '.css', array( 'jb-common' ), JB_VERSION );
			wp_register_style( 'jb-job-categories', $this->css_url['frontend'] . 'job-categories' . JB()->scrips_prefix . '.css', array( 'jb-common' ), JB_VERSION );

			global $post;

			if ( $post && 'jb-job' === $post->post_type && is_singular( 'jb-job' ) && is_main_query() && ! post_password_required() ) {
				wp_enqueue_style( 'jb-job' );
			}

			if ( $post && has_shortcode( $post->post_content, 'jb_post_job' ) ) {
				wp_enqueue_script( 'jb-post-job' );
				wp_enqueue_style( 'jb-post-job' );
			}
		}
	}
}
