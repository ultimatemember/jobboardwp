<?php namespace jb\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


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
		function __construct() {
			add_action( 'admin_init', [ $this, 'actions_listener' ], 10 );
		}


		/**
		 * Handle wp-admin actions
		 *
		 * @since 1.0
		 */
		function actions_listener() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( ! empty( $_REQUEST['jb_adm_action'] ) ) {
				switch ( $_REQUEST['jb_adm_action'] ) {
					case 'install_core_pages': {
						JB()->install()->core_pages();

						$url = add_query_arg( [ 'page' => 'jb-settings' ], admin_url( 'admin.php' ) );
						exit( wp_redirect( $url ) );

						break;
					}
					case 'approve_job': {

						if ( ! empty( $_GET['job-id'] ) && wp_verify_nonce( $_GET['nonce'], 'jb-approve-job' . $_GET['job-id'] ) ) {

							$job_id = absint( $_GET['job-id'] );
							$job = get_post( $job_id );

							$referrer = wp_get_referer();
							if ( ! $referrer || false !== strpos( $referrer, 'jb_adm_action=approve_job' ) ) {
								$referrer = admin_url( 'edit.php?post_type=jb-job' );
							}

							if ( ! empty( $job ) && ! is_wp_error( $job ) ) {
								if ( $job->post_status == 'pending' ) {
									$args = [
										'ID'            => $job_id,
										'post_status'   => 'publish',
									];

									// a fix for restored from trash pending jobs
									if ( '__trashed' === substr( $job->post_name, 0, 9 ) ) {
										$args['post_name'] = sanitize_title( $job->post_title );
									}

									wp_update_post( $args );

									delete_post_meta( $job_id, 'jb-had-pending' );

									$job = get_post( $job_id );
									$user = get_userdata( $job->post_author );
									if ( ! empty( $user ) && ! is_wp_error( $user ) ) {
										JB()->common()->mail()->send( $user->user_email, 'job_approved', [
											'job_id'        => $job_id,
											'job_title'     => $job->post_title,
											'view_job_url'  => get_permalink( $job ),
										] );
									}

									do_action( 'jb_job_is_approved', $job_id, $job );

									$url = add_query_arg( [ 'jb-approved' => '1' ], $referrer );
									exit( wp_redirect( $url ) );
								}
							}

							exit( wp_redirect( $referrer ) );
						}

						break;
					}
				}
			}
		}

	}
}