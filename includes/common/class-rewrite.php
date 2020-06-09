<?php namespace jb\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\common\Rewrite' ) ) {


	/**
	 * Class Rewrite
	 * @package jb\common
	 */
	class Rewrite {


		/**
		 * Rewrite constructor.
		 */
		function __construct() {
			if ( ! defined( 'DOING_AJAX' ) ) {
				add_filter( 'wp_loaded', [ $this, 'maybe_flush_rewrite_rules' ] );
			}

			add_action( 'plugins_loaded', [ $this, 'init_variables' ], 10 );
		}


		/**
		 * Init variables for permalinks
		 */
		function init_variables() {
			if ( get_option( 'permalink_structure' ) ) {
				JB()->is_permalinks = true;
			}
		}


		/**
		 * Update "flush" option for reset rules on wp_loaded hook
		 */
		function reset_rules() {
			JB()->options()->update( 'flush_rewrite_rules', true );
		}


		/**
		 * Reset Rewrite rules if need it.
		 *
		 * @return void
		 */
		function maybe_flush_rewrite_rules() {
			$flush_exists = JB()->options()->get( 'flush_rewrite_rules' );

			if ( $flush_exists ) {
				flush_rewrite_rules( false );
				JB()->options()->delete( 'flush_rewrite_rules' );
			}
		}
	}
}