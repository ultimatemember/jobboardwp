<?php namespace jb\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\admin\Actions_Listener' ) ) {


	/**
	 * Class Actions_Listener
	 *
	 * @package jb\admin
	 */
	class Actions_Listener {


		/**
		 * Actions_Listener constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'actions_listener' ), 10 );
		}


		/**
		 * Handle wp-admin actions
		 *
		 * @since 1.0
		 */
		public function actions_listener() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( ! empty( $_REQUEST['jb_adm_action'] ) ) {
				switch ( sanitize_key( $_REQUEST['jb_adm_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- there is nonce verification below for each case
					case 'install_predefined_pages':
						if ( wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'jb_install_predefined_pages' ) ) {
							JB()->install()->predefined_pages();

							// phpcs:ignore WordPress.Security.SafeRedirect
							wp_redirect( add_query_arg( array( 'page' => 'jb-settings' ), admin_url( 'admin.php' ) ) );
							exit;
						}

						break;
					case 'install_predefined_page':
						if ( wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'jb_install_predefined_page' ) ) {
							$page_slug = array_key_exists( 'jb_page_key', $_REQUEST ) ? sanitize_key( $_REQUEST['jb_page_key'] ) : '';

							if ( empty( $page_slug ) || ! JB()->common()->permalinks()->predefined_page_slug_exists( $page_slug ) ) {
								// phpcs:ignore WordPress.Security.SafeRedirect
								wp_redirect( add_query_arg( array( 'page' => 'jb-settings' ), admin_url( 'admin.php' ) ) );
								exit;
							}

							JB()->install()->predefined_page( $page_slug );

							// phpcs:ignore WordPress.Security.SafeRedirect
							wp_redirect( add_query_arg( array( 'page' => 'jb-settings' ), admin_url( 'admin.php' ) ) );
							exit;
						}

						break;
					case 'approve_job':
						if ( ! empty( $_GET['job-id'] ) && wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'jb-approve-job' . absint( $_GET['job-id'] ) ) ) {

							$job_id = absint( $_GET['job-id'] );
							$job    = get_post( $job_id );

							$referrer = wp_get_referer();
							// check if $referrer is secure and contains `jb_adm_action=approve_job`
							if ( ! $referrer || false !== strpos( $referrer, 'jb_adm_action=approve_job' ) ) {
								$referrer = admin_url( 'edit.php?post_type=jb-job' );
							}

							if ( ! empty( $job ) && ! is_wp_error( $job ) ) {
								if ( JB()->common()->job()->approve_job( $job ) ) {
									// phpcs:ignore WordPress.Security.SafeRedirect
									wp_redirect( add_query_arg( array( 'jb-approved' => '1' ), $referrer ) );
									exit;
								}
							}

							// phpcs:ignore WordPress.Security.SafeRedirect
							wp_redirect( $referrer );
							exit;
						}

						break;
				}
			}
		}

	}
}
