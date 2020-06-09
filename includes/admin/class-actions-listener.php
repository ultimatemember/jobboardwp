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
		 *
		 */
		function actions_listener() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( ! empty( $_REQUEST['jb_adm_action'] ) ) {
				switch ( $_REQUEST['jb_adm_action'] ) {
					case 'install_core_pages': {
						JB()->install()->core_pages();

//						if ( JB()->permalinks()->are_pages_installed() ) {
//							JB()->admin()->notices()->dismiss( 'wrong_pages' );
//						}

						$url = add_query_arg( [ 'page' => 'jb-settings' ], admin_url( 'admin.php' ) );
						exit( wp_redirect( $url ) );

						break;
					}
				}
			}
		}

	}
}