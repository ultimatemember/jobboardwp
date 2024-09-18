<?php
namespace jb\admin;

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
			add_action( 'admin_init', array( $this, 'actions_listener' ) );
			add_action( 'load-job-board_page_jb-settings', array( &$this, 'handle_modules_actions_options' ) );
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
						check_admin_referer( 'jb_install_predefined_pages', 'nonce' );

						JB()->install()->predefined_pages();

						wp_safe_redirect( add_query_arg( array( 'page' => 'jb-settings' ), admin_url( 'admin.php' ) ) );
						exit;
					case 'install_predefined_page':
						check_admin_referer( 'jb_install_predefined_page', 'nonce' );

						$page_slug = array_key_exists( 'jb_page_key', $_REQUEST ) ? sanitize_key( $_REQUEST['jb_page_key'] ) : '';
						if ( empty( $page_slug ) || ! JB()->common()->permalinks()->predefined_page_slug_exists( $page_slug ) ) {
							wp_safe_redirect( add_query_arg( array( 'page' => 'jb-settings' ), admin_url( 'admin.php' ) ) );
							exit;
						}

						JB()->install()->predefined_page( $page_slug );

						wp_safe_redirect( add_query_arg( array( 'page' => 'jb-settings' ), admin_url( 'admin.php' ) ) );
						exit;
					case 'approve_job':
						if ( ! empty( $_GET['job-id'] ) && ! empty( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'jb-approve-job' . absint( $_GET['job-id'] ) ) ) {

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
									wp_safe_redirect( add_query_arg( array( 'jb-approved' => '1' ), $referrer ) );
									exit;
								}
							}

							wp_safe_redirect( $referrer );
							exit;
						}
						break;

					case 'check_templates_version':
						check_admin_referer( 'jb_check_templates_version' );

						$templates = JB()->admin()->settings()->get_override_templates( true );
						$out_date  = false;
						foreach ( $templates as $template ) {
							if ( 0 === $template['status_code'] ) {
								$out_date = true;
								break;
							}
						}

						if ( false === $out_date ) {
							delete_option( 'jb_override_templates_outdated' );
						}

						$url = add_query_arg(
							array(
								'page' => 'jb-settings',
								'tab'  => 'override_templates',
							),
							admin_url( 'admin.php' )
						);
						wp_safe_redirect( $url );
						exit;
				}
			}
		}

		/**
		 * Handles Modules list table
		 *
		 * @since 1.2.2
		 *
		 * @uses Modules::activate() JB()->modules()->activate( $slug )
		 * @uses Modules::deactivate() JB()->modules()->deactivate( $slug )
		 * @uses Modules::flush_data() JB()->modules()->flush_data( $slug )
		 */
		public function handle_modules_actions_options() {
			if ( ! ( isset( $_GET['page'] ) && 'jb-settings' === $_GET['page'] && isset( $_GET['tab'] ) && 'modules' === $_GET['tab'] && ! isset( $_GET['section'] ) ) ) {
				return;
			}

			if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
				$redirect = remove_query_arg( array( '_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- _wp_http_referer ok
			} else {
				$redirect = get_admin_url( null, 'admin.php?page=jb-settings&tab=modules' );
			}

			if ( isset( $_GET['action'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification -- will be verified below through `check_admin_referer()`
				switch ( sanitize_key( $_GET['action'] ) ) {
					case 'activate':
						// Activate module
						$slugs = array();
						if ( isset( $_GET['slug'] ) ) {
							// single activate
							$slug = sanitize_key( $_GET['slug'] );

							if ( empty( $slug ) ) {
								wp_safe_redirect( $redirect );
								exit;
							}

							check_admin_referer( 'jb_module_activate' . $slug . get_current_user_id() );
							$slugs = array( $slug );
						} elseif ( isset( $_REQUEST['item'] ) ) {
							// bulk activate
							check_admin_referer( 'bulk-' . sanitize_key( __( 'Modules', 'jobboardwp' ) ) );
							$slugs = array_map( 'sanitize_key', $_REQUEST['item'] );
						}

						if ( ! count( $slugs ) ) {
							wp_safe_redirect( $redirect );
							exit;
						}

						$results = 0;
						foreach ( $slugs as $slug ) {
							if ( JB()->modules()->activate( $slug ) ) {
								++$results;
							}
						}

						if ( ! $results ) {
							wp_safe_redirect( $redirect );
							exit;
						}

						wp_safe_redirect( add_query_arg( 'msg', 'a', $redirect ) );
						exit;
					case 'deactivate':
						// Deactivate module
						$slugs = array();
						if ( isset( $_GET['slug'] ) ) {
							// single deactivate
							$slug = sanitize_key( $_GET['slug'] );

							if ( empty( $slug ) ) {
								wp_safe_redirect( $redirect );
								exit;
							}

							check_admin_referer( 'jb_module_deactivate' . $slug . get_current_user_id() );
							$slugs = array( $slug );
						} elseif ( isset( $_REQUEST['item'] ) ) {
							// bulk deactivate
							check_admin_referer( 'bulk-' . sanitize_key( __( 'Modules', 'jobboardwp' ) ) );
							$slugs = array_map( 'sanitize_key', $_REQUEST['item'] );
						}

						if ( ! count( $slugs ) ) {
							wp_safe_redirect( $redirect );
							exit;
						}

						$results = 0;
						foreach ( $slugs as $slug ) {
							if ( JB()->modules()->deactivate( $slug ) ) {
								++$results;
							}
						}

						if ( ! $results ) {
							wp_safe_redirect( $redirect );
							exit;
						}

						wp_safe_redirect( add_query_arg( 'msg', 'd', $redirect ) );
						exit;
					case 'flush-data':
						// Flush module's data
						$slugs = array();
						if ( isset( $_GET['slug'] ) ) {
							// single flush
							$slug = sanitize_key( $_GET['slug'] );

							if ( empty( $slug ) ) {
								wp_safe_redirect( $redirect );
								exit;
							}

							check_admin_referer( 'jb_module_flush' . $slug . get_current_user_id() );
							$slugs = array( $slug );
						} elseif ( isset( $_REQUEST['item'] ) ) {
							// bulk flush
							check_admin_referer( 'bulk-' . sanitize_key( __( 'Modules', 'jobboardwp' ) ) );
							$slugs = array_map( 'sanitize_key', $_REQUEST['item'] );
						}

						if ( ! count( $slugs ) ) {
							wp_safe_redirect( $redirect );
							exit;
						}

						$results = 0;
						foreach ( $slugs as $slug ) {
							if ( JB()->modules()->flush_data( $slug ) ) {
								++$results;
							}
						}

						if ( ! $results ) {
							wp_safe_redirect( $redirect );
							exit;
						}

						wp_safe_redirect( add_query_arg( 'msg', 'f', $redirect ) );
						exit;
				}
			}

			// Remove extra query arg
			if ( ! empty( $_GET['_wp_http_referer'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
				wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- REQUEST_URI ok
				exit;
			}
		}
	}
}
