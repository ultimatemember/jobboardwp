<?php
namespace jb\ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		public function __construct() {
		}


		/**
		 * AJAX dismiss notices
		 */
		public function dismiss_notice() {
			JB()->ajax()->check_nonce( 'jb-backend-nonce' );
			// phpcs:disable WordPress.Security.NonceVerification -- already verified here
			if ( empty( $_POST['key'] ) ) {
				wp_send_json_error( __( 'Wrong Data', 'jobboardwp' ) );
			}

			JB()->admin()->notices()->dismiss( sanitize_key( $_POST['key'] ) );
			wp_send_json_success();
			// phpcs:enable WordPress.Security.NonceVerification -- already verified here
		}
	}
}
