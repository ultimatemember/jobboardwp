<?php
namespace jb\common;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\common\Rewrite' ) ) {


	/**
	 * Class Rewrite
	 * @package jb\common
	 */
	class Rewrite {


		/**
		 * Rewrite constructor.
		 */
		public function __construct() {
			if ( ! defined( 'DOING_AJAX' ) ) {
				add_filter( 'wp_loaded', array( $this, 'maybe_flush_rewrite_rules' ) );
			}

			add_action( 'plugins_loaded', array( $this, 'init_variables' ), 10 );
		}


		/**
		 * Init variables for permalinks
		 *
		 * @since 1.0
		 */
		public function init_variables() {
			if ( get_option( 'permalink_structure' ) ) {
				JB()->is_permalinks = true;
			}
		}


		/**
		 * Update "flush" option for reset rules on wp_loaded hook
		 *
		 * @since 1.0
		 */
		public function reset_rules() {
			JB()->options()->update( 'flush_rewrite_rules', true );
		}


		/**
		 * Reset Rewrite rules if need it.
		 *
		 * @return void
		 *
		 * @since 1.0
		 */
		public function maybe_flush_rewrite_rules() {
			$flush_exists = JB()->options()->get( 'flush_rewrite_rules' );

			if ( $flush_exists ) {
				flush_rewrite_rules( false );
				JB()->options()->delete( 'flush_rewrite_rules' );
			}
		}
	}
}
