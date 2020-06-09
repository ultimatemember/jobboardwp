<?php namespace jb\ajax;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\ajax\Notices' ) ) {


	/**
	 * Class Notices
	 *
	 * @package jb\ajax
	 */
	class Notices {


		/**
		 * Notices constructor.
		 */
		function __construct() {
		}


		/**
		 * AJAX dismiss notices
		 */
		function dismiss_notice() {
			JB()->ajax()->check_nonce( 'jb-backend-nonce' );

			if ( empty( $_POST['key'] ) ) {
				wp_send_json_error( __( 'Wrong Data', 'jobboardwp' ) );
			}

			JB()->admin()->notices()->dismiss( $_POST['key'] );
			wp_send_json_success();
		}

	}
}