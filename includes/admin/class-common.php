<?php namespace jb\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\admin\Common' ) ) {


	/**
	 * Class Common
	 *
	 * @package jb\admin
	 */
	class Common {


		/**
		 * @var string
		 */
		var $templates_path = '';


		/**
		 * Common constructor.
		 */
		function __construct() {
			add_action( 'plugins_loaded', [ $this, 'init_variables' ], 10 );
		}


		/**
		 *
		 */
		function init_variables() {
			$this->templates_path = jb_path . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
		}


		/**
		 * All admin includes in one function
		 */
		function includes() {
			$this->menu();
			$this->enqueue();
			$this->settings();
			$this->notices();
			$this->actions_listener();
			$this->columns();
			$this->metabox();
		}


		/**
		 * @since 1.0
		 *
		 * @return Menu()
		 */
		function menu() {
			if ( empty( JB()->classes['jb\admin\menu'] ) ) {
				JB()->classes['jb\admin\menu'] = new Menu();
			}
			return JB()->classes['jb\admin\menu'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Enqueue()
		 */
		function enqueue() {
			if ( empty( JB()->classes['jb\admin\enqueue'] ) ) {
				JB()->classes['jb\admin\enqueue'] = new Enqueue();
			}
			return JB()->classes['jb\admin\enqueue'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Settings()
		 */
		function settings() {
			if ( empty( JB()->classes['jb\admin\settings'] ) ) {
				JB()->classes['jb\admin\settings'] = new Settings();
			}
			return JB()->classes['jb\admin\settings'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Notices()
		 */
		function notices() {
			if ( empty( JB()->classes['jb\admin\notices'] ) ) {
				JB()->classes['jb\admin\notices'] = new Notices();
			}
			return JB()->classes['jb\admin\notices'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Actions_Listener()
		 */
		function actions_listener() {
			if ( empty( JB()->classes['jb\admin\actions_listener'] ) ) {
				JB()->classes['jb\admin\actions_listener'] = new Actions_Listener();
			}
			return JB()->classes['jb\admin\actions_listener'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Actions_Listener()
		 */
		function columns() {
			if ( empty( JB()->classes['jb\admin\columns'] ) ) {
				JB()->classes['jb\admin\columns'] = new Columns();
			}
			return JB()->classes['jb\admin\columns'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Metabox()
		 */
		function metabox() {
			if ( empty( JB()->classes['jb\admin\metabox'] ) ) {
				JB()->classes['jb\admin\metabox'] = new Metabox();
			}
			return JB()->classes['jb\admin\metabox'];
		}


		/**
		 * @since 1.0
		 *
		 * @param array|bool $data
		 *
		 * @return Forms
		 */
		function forms( $data = false ) {
			if ( empty( JB()->classes[ 'jb\admin\forms' . $data['class'] ] ) ) {
				JB()->classes['jb\admin\forms' . $data['class'] ] = new Forms( $data );
			}

			return JB()->classes['jb\admin\forms' . $data['class'] ];
		}


		/**
		 * Check if ForumWP screen is loaded
		 *
		 * @return bool
		 */
		function is_own_screen() {
			global $current_screen;
			$screen_id = $current_screen->id;

			if ( strstr( $screen_id, $this->menu()->slug ) || strstr( $screen_id, 'jb-' ) || strstr( $screen_id, 'jb_' ) ) {
				return true;
			}

			if ( $this->is_own_post_type() ) {
				return true;
			}

			return false;
		}


		/**
		 * Check if current page load ForumWP CPT
		 *
		 * @return bool
		 */
		function is_own_post_type() {
			$cpt = array_keys( JB()->common()->cpt()->get() );

			if ( isset( $_REQUEST['post_type'] ) ) {
				$post_type = $_REQUEST['post_type'];
				if ( in_array( $post_type, $cpt ) ) {
					return true;
				}
			} elseif ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) {
				$post_type = get_post_type();
				if ( in_array( $post_type, $cpt ) ) {
					return true;
				}
			}

			return false;
		}
	}
}