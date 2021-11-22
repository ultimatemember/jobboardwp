<?php namespace jb\integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\integrations\Init' ) ) {


	/**
	 * Class Init
	 *
	 * @package jb\integrations
	 */
	class Init {


		/**
		 * Init constructor.
		 */
		public function __construct() {
			// running before all plugins_loaded callbacks in JobBoardWP.
			add_action( 'plugins_loaded', array( &$this, 'plugins_loaded' ), 9 );
		}


		/**
		 *
		 */
		public function plugins_loaded() {
			if ( $this->is_wpml_active() ) {
				require_once 'wpml/integration.php';
			}

			if ( $this->is_polylang_active() ) {
				require_once 'polylang/integration.php';
			}

			if ( $this->is_translatepress_active() ) {
				require_once 'translatepress/integration.php';
			}

			if ( $this->is_weglot_active() ) {
				require_once 'weglot/integration.php';
			}
		}


		/**
		 * Check if WPML is active
		 *
		 * @return bool
		 */
		public function is_wpml_active() {
			if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
				global $sitepress;
				return $sitepress->is_setup_complete();
			}

			return false;
		}


		/**
		 * Check if Polylang is active
		 *
		 * @return bool
		 */
		public function is_polylang_active() {
			if ( defined( 'POLYLANG_VERSION' ) ) {
				global $polylang;
				return is_object( $polylang );
			}

			return false;
		}


		/**
		 * Check if TranslatePress is active
		 *
		 * @return bool
		 */
		public function is_translatepress_active() {
			return defined( 'TRP_PLUGIN_VERSION' ) && class_exists( '\TRP_Translate_Press' );
		}


		/**
		 * Check if Weglot is active
		 *
		 * @return bool
		 */
		public function is_weglot_active() {
			return defined( 'WEGLOT_VERSION' );
		}
	}
}
