<?php
namespace jb\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\admin\Init' ) ) {


	/**
	 * Class Init
	 *
	 * @package jb\admin
	 */
	class Init {


		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		public $templates_path = '';


		/**
		 * Init constructor.
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'init_variables' ), 10 );
			add_action( 'admin_init', array( $this, 'init_current_locale' ), 0 );
		}


		/**
		 * Init current locale if exists.
		 */
		public function init_current_locale() {
			// phpcs:disable WordPress.Security.NonceVerification -- don't need verifying there just the information about locale from JS to AJAX handlers
			if ( ! empty( $_REQUEST['jb_current_locale'] ) ) {
				$locale = sanitize_key( $_REQUEST['jb_current_locale'] );
				/**
				 * Fires for applying current locale via 3rd-party plugins.
				 * Note: It's internal hook, but you could use it for you custom 3rd-party integrations where locale is used.
				 *
				 * @since 1.1.1
				 * @hook jb_admin_init_locale
				 *
				 * @param {string} $locale Current locale from $_REQUEST.
				 */
				do_action( 'jb_admin_init_locale', $locale );
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}


		/**
		 * Init admin variables
		 *
		 * @since 1.0
		 */
		public function init_variables() {
			$this->templates_path = JB_PATH . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
		}


		/**
		 * All admin includes in one function
		 *
		 * @since 1.0
		 */
		public function includes() {
			$this->menu();
			$this->enqueue();
			$this->settings();
			$this->notices();
			$this->actions_listener();
			$this->columns();
			$this->metabox();
			$this->site_health();
		}


		/**
		 * @since 1.0
		 *
		 * @return Menu
		 */
		public function menu() {
			if ( empty( JB()->classes['jb\admin\menu'] ) ) {
				JB()->classes['jb\admin\menu'] = new Menu();
			}
			return JB()->classes['jb\admin\menu'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Enqueue
		 */
		public function enqueue() {
			if ( empty( JB()->classes['jb\admin\enqueue'] ) ) {
				JB()->classes['jb\admin\enqueue'] = new Enqueue();
			}
			return JB()->classes['jb\admin\enqueue'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Settings
		 */
		public function settings() {
			if ( empty( JB()->classes['jb\admin\settings'] ) ) {
				JB()->classes['jb\admin\settings'] = new Settings();
			}
			return JB()->classes['jb\admin\settings'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Notices
		 */
		public function notices() {
			if ( empty( JB()->classes['jb\admin\notices'] ) ) {
				JB()->classes['jb\admin\notices'] = new Notices();
			}
			return JB()->classes['jb\admin\notices'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Actions_Listener
		 */
		public function actions_listener() {
			if ( empty( JB()->classes['jb\admin\actions_listener'] ) ) {
				JB()->classes['jb\admin\actions_listener'] = new Actions_Listener();
			}
			return JB()->classes['jb\admin\actions_listener'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Columns
		 */
		public function columns() {
			if ( empty( JB()->classes['jb\admin\columns'] ) ) {
				JB()->classes['jb\admin\columns'] = new Columns();
			}
			return JB()->classes['jb\admin\columns'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Metabox
		 */
		public function metabox() {
			if ( empty( JB()->classes['jb\admin\metabox'] ) ) {
				JB()->classes['jb\admin\metabox'] = new Metabox();
			}
			return JB()->classes['jb\admin\metabox'];
		}


		/**
		 * @since 1.2.1
		 *
		 * @return Site_Health
		 */
		public function site_health() {
			if ( empty( JB()->classes['jb\admin\site_health'] ) ) {
				JB()->classes['jb\admin\site_health'] = new Site_Health();
			}
			return JB()->classes['jb\admin\site_health'];
		}


		/**
		 * @since 1.0
		 *
		 * @param array|bool $data
		 *
		 * @return Forms
		 */
		public function forms( $data = false ) {
			if ( empty( JB()->classes[ 'jb\admin\forms' . $data['class'] ] ) ) {
				JB()->classes[ 'jb\admin\forms' . $data['class'] ] = new Forms( $data );
			}

			return JB()->classes[ 'jb\admin\forms' . $data['class'] ];
		}


		/**
		 * Check if JobBoard screen is loaded
		 *
		 * @return bool
		 *
		 * @since 1.0
		 */
		public function is_own_screen() {
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
		 * Check if current page load JobBoard CPT
		 *
		 * @return bool
		 *
		 * @since 1.0
		 */
		public function is_own_post_type() {
			$cpt = array_keys( JB()->common()->cpt()->get() );

			if ( isset( $_REQUEST['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$post_type = sanitize_key( $_REQUEST['post_type'] ); // phpcs:ignore WordPress.Security.NonceVerification
				if ( in_array( $post_type, $cpt, true ) ) {
					return true;
				}
			} elseif ( isset( $_REQUEST['action'] ) && 'edit' === sanitize_key( $_REQUEST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$post_type = get_post_type();
				if ( in_array( $post_type, $cpt, true ) ) {
					return true;
				}
			}

			return false;
		}
	}
}
