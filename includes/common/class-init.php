<?php
namespace jb\common;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\common\Init' ) ) {


	/**
	 * Class Init
	 *
	 * @package jb\common
	 */
	class Init {


		/**
		 * Init constructor.
		 */
		public function __construct() {
			add_action( 'jb_core_loaded', array( JB()->modules(), 'load_modules' ), 1 );
			add_action( 'plugins_loaded', array( $this, 'init_template_path' ), 10 );
		}


		/**
		 * Init variables for getting templates
		 *
		 * @since 1.0
		 */
		public function init_template_path() {
			JB()->templates_path  = JB_PATH . 'templates' . DIRECTORY_SEPARATOR;
			JB()->theme_templates = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'jobboardwp' . DIRECTORY_SEPARATOR;
		}


		/**
		 * Common plugin includes
		 *
		 * @since 1.0
		 */
		public function includes() {
			$this->cpt();
			$this->rewrite();
			$this->mail();
			$this->cron();
			$this->blocks();
		}


		/**
		 * @since 1.0
		 *
		 * @return Cron
		 */
		public function cron() {
			if ( empty( JB()->classes['jb\common\cron'] ) ) {
				JB()->classes['jb\common\cron'] = new Cron();
			}
			return JB()->classes['jb\common\cron'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Rewrite
		 */
		public function rewrite() {
			if ( empty( JB()->classes['jb\common\rewrite'] ) ) {
				JB()->classes['jb\common\rewrite'] = new Rewrite();
			}

			return JB()->classes['jb\common\rewrite'];
		}


		/**
		 * @since 1.0
		 *
		 * @return CPT
		 */
		public function cpt() {
			if ( empty( JB()->classes['jb\common\cpt'] ) ) {
				JB()->classes['jb\common\cpt'] = new CPT();
			}

			return JB()->classes['jb\common\cpt'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Mail
		 */
		public function mail() {
			if ( empty( JB()->classes['jb\common\mail'] ) ) {
				JB()->classes['jb\common\mail'] = new Mail();
			}

			return JB()->classes['jb\common\mail'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Job
		 */
		public function job() {
			if ( empty( JB()->classes['jb\common\job'] ) ) {
				JB()->classes['jb\common\job'] = new Job();
			}

			return JB()->classes['jb\common\job'];
		}


		/**
		 * @since 1.0
		 *
		 * @return User
		 */
		public function user() {
			if ( empty( JB()->classes['jb\common\user'] ) ) {
				JB()->classes['jb\common\user'] = new User();
			}

			return JB()->classes['jb\common\user'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Filesystem
		 */
		public function filesystem() {
			if ( empty( JB()->classes['jb\common\filesystem'] ) ) {
				JB()->classes['jb\common\filesystem'] = new Filesystem();
			}

			return JB()->classes['jb\common\filesystem'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Permalinks
		 */
		public function permalinks() {
			if ( empty( JB()->classes['jb\common\permalinks'] ) ) {
				JB()->classes['jb\common\permalinks'] = new Permalinks();
			}

			return JB()->classes['jb\common\permalinks'];
		}


		/**
		 * @since 1.2.1
		 *
		 * @return Blocks
		 */
		public function blocks() {
			if ( empty( JB()->classes['jb\common\blocks'] ) ) {
				JB()->classes['jb\common\blocks'] = new Blocks();
			}
			return JB()->classes['jb\common\blocks'];
		}
	}
}
