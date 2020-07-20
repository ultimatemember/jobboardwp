<?php namespace jb\ajax;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\ajax\Common' ) ) {


	/**
	 * Class Common
	 *
	 * @package jb\ajax
	 */
	class Common {


		/**
		 * Common constructor.
		 */
		function __construct() {
			add_action( 'wp_ajax_jb_dismiss_notice', [ $this->notices(), 'dismiss_notice' ] );

			add_action( 'wp_ajax_jb-get-jobs', [ $this->jobs(), 'get_jobs' ] );
			add_action( 'wp_ajax_nopriv_jb-get-jobs', [ $this->jobs(), 'get_jobs' ] );

			add_action( 'wp_ajax_jb-upload-company-logo', [ $this->employer(), 'upload_logo' ] );
			add_action( 'wp_ajax_nopriv_jb-upload-company-logo', [ $this->employer(), 'upload_logo' ] );

			add_action( 'wp_ajax_jb-get-employer-jobs', [ $this->jobs(), 'get_employer_jobs' ] );

			add_action( 'wp_ajax_jb-delete-job', [ $this->jobs(), 'delete_job' ] );
			add_action( 'wp_ajax_jb-fill-job', [ $this->jobs(), 'fill_job' ] );
			add_action( 'wp_ajax_jb-unfill-job', [ $this->jobs(), 'unfill_job' ] );
		}


		/**
		 * All AJAX includes
		 *
		 * @since 1.0
		 */
		function includes() {
			JB()->admin()->metabox();
		}


		/**
		 * Check nonce
		 *
		 * @param bool|string $action
		 *
		 * @since 1.0
		 */
		function check_nonce( $action = false ) {
			$nonce = isset( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : '';
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
		function notices() {
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
		function jobs() {
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
		function employer() {
			if ( empty( JB()->classes['jb\ajax\employer'] ) ) {
				JB()->classes['jb\ajax\employer'] = new Employer();
			}
			return JB()->classes['jb\ajax\employer'];
		}


	}
}