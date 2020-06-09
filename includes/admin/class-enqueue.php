<?php namespace jb\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\admin\Enqueue' ) ) {


	/**
	 * Class Enqueue
	 *
	 * @package jb\admin
	 */
	class Enqueue extends \jb\common\Enqueue {


		/**
		 * Enqueue constructor.
		 */
		function __construct() {
			parent::__construct();

			$this->url['admin'] = jb_url . 'assets/admin/';
			$this->js_url['admin'] = jb_url . 'assets/admin/js/';
			$this->css_url['admin'] = jb_url . 'assets/admin/css/';

			add_action( 'admin_enqueue_scripts', [ &$this, 'admin_scripts' ] );
		}


		/**
		 *
		 */
		function admin_scripts() {
			wp_register_script( 'jb-global', $this->js_url['admin'] . 'global' . JB()->scrips_prefix . '.js', [ 'jquery', 'wp-util' ], jb_version, true );
			wp_register_script( 'jb-helptip', $this->js_url['common'] . 'helptip' . JB()->scrips_prefix . '.js', [ 'jquery', 'jquery-ui-tooltip' ], jb_version, true );

			wp_register_script( 'jb-forms', $this->js_url['admin'] . 'forms' . JB()->scrips_prefix . '.js', [ 'jquery', 'wp-util', 'jb-global', 'jb-helptip', 'wp-color-picker', 'jquery-ui-datepicker' ], jb_version, true );

			wp_register_style( 'jb-helptip', $this->css_url['common'] . 'helptip' . JB()->scrips_prefix . '.css', [ 'dashicons' ], jb_version );

			wp_register_style( 'jb-common', $this->css_url['admin'] . 'common' . JB()->scrips_prefix . '.css', [], jb_version );
			wp_register_style( 'jb-forms', $this->css_url['admin'] . 'forms' . JB()->scrips_prefix . '.css', [ 'jb-helptip', 'wp-color-picker', 'jquery-ui' ], jb_version );

			$localize_data = apply_filters( 'jb_admin_enqueue_localize', [
				'nonce' => wp_create_nonce( 'jb-backend-nonce' ),
			] );

			wp_localize_script( 'jb-global', 'jb_admin_data', $localize_data );
			wp_enqueue_script( 'jb-global' );

			if ( JB()->admin()->is_own_screen() ) {
				wp_enqueue_script( 'jb-forms' );

				wp_enqueue_style( 'jb-common' );
				wp_enqueue_style( 'jb-forms' );
			}
		}

	}
}