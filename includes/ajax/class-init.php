<?php namespace jb\ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\ajax\Init' ) ) {


	/**
	 * Class Init
	 *
	 * @package jb\ajax
	 */
	class Init {


		/**
		 * Init constructor.
		 */
		public function __construct() {
			add_action( 'wp_ajax_jb_dismiss_notice', array( $this->notices(), 'dismiss_notice' ) );

			add_action( 'wp_ajax_jb-get-jobs', array( $this->jobs(), 'get_jobs' ) );
			add_action( 'wp_ajax_nopriv_jb-get-jobs', array( $this->jobs(), 'get_jobs' ) );

			add_action( 'wp_ajax_jb-get-categories', array( $this->jobs(), 'get_categories' ) );
			add_action( 'wp_ajax_nopriv_jb-get-categories', array( $this->jobs(), 'get_categories' ) );

			add_action( 'wp_ajax_jb-upload-company-logo', array( $this->employer(), 'upload_logo' ) );
			add_action( 'wp_ajax_nopriv_jb-upload-company-logo', array( $this->employer(), 'upload_logo' ) );

			add_action( 'wp_ajax_jb-get-employer-jobs', array( $this->jobs(), 'get_employer_jobs' ) );

			add_action( 'wp_ajax_jb-delete-job', array( $this->jobs(), 'delete_job' ) );
			add_action( 'wp_ajax_jb-fill-job', array( $this->jobs(), 'fill_job' ) );
			add_action( 'wp_ajax_jb-unfill-job', array( $this->jobs(), 'unfill_job' ) );
		}


		/**
		 * All AJAX includes
		 *
		 * @since 1.0
		 */
		public function includes() {
			JB()->admin()->metabox();
		}


		/**
		 * Check nonce
		 *
		 * @param bool|string $action
		 *
		 * @since 1.0
		 */
		public function check_nonce( $action = false ) {
			$nonce  = isset( $_REQUEST['nonce'] ) ? sanitize_key( $_REQUEST['nonce'] ) : '';
			$action = empty( $action ) ? 'jb-common-nonce' : $action;

			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_send_json_error( __( 'Wrong AJAX Nonce', 'jobboardwp' ) );
			}
		}


		/**
		 * @return Notices()
		 *
		 * @since 1.0
		 */
		public function notices() {
			if ( empty( JB()->classes['jb\ajax\notices'] ) ) {
				JB()->classes['jb\ajax\notices'] = new Notices();
			}
			return JB()->classes['jb\ajax\notices'];
		}


		/**
		 * @return Jobs()
		 *
		 * @since 1.0
		 */
		public function jobs() {
			if ( empty( JB()->classes['jb\ajax\jobs'] ) ) {
				JB()->classes['jb\ajax\jobs'] = new Jobs();
			}
			return JB()->classes['jb\ajax\jobs'];
		}


		/**
		 * @return Employer()
		 *
		 * @since 1.0
		 */
		public function employer() {
			if ( empty( JB()->classes['jb\ajax\employer'] ) ) {
				JB()->classes['jb\ajax\employer'] = new Employer();
			}
			return JB()->classes['jb\ajax\employer'];
		}
	}
}
