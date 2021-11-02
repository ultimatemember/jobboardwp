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
					case 'install_core_pages':
						if ( wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'jb_install_core_pages' ) ) {
							JB()->install()->core_pages();

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
								if ( 'pending' === $job->post_status ) {
									$args = array(
										'ID'          => $job_id,
										'post_status' => 'publish',
									);

									// a fix for restored from trash pending jobs
									if ( '__trashed' === substr( $job->post_name, 0, 9 ) ) {
										$args['post_name'] = sanitize_title( $job->post_title );
									}

									wp_update_post( $args );

									delete_post_meta( $job_id, 'jb-had-pending' );

									$job  = get_post( $job_id );
									$user = get_userdata( $job->post_author );
									if ( ! empty( $user ) && ! is_wp_error( $user ) ) {
										$email_args = array(
											'job_id'       => $job_id,
											'job_title'    => $job->post_title,
											'view_job_url' => get_permalink( $job ),
										);
										JB()->common()->mail()->send( $user->user_email, 'job_approved', $email_args );
									}

									do_action( 'jb_job_is_approved', $job_id, $job );

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
