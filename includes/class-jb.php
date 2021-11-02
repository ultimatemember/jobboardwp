<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'JB' ) ) {


	/**
	 * Main JB Class
	 *
	 * @class JB
	 * @version 1.0
	 */
	final class JB extends JB_Functions {


		/**
		 * @var JB the single instance of the class
		 */
		private static $instance = null;


		/**
		 * @var array all plugin's classes
		 */
		public $classes = array();


		/**
		 * Main JB Instance
		 *
		 * Ensures only one instance of JB is loaded or can be loaded.
		 *
		 * @since 1.0
		 * @static
		 * @see JB()
		 * @return JB - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->jb_construct();
			}

			return self::$instance;
		}


		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0
		 */
		public function __clone() {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- strict output
			_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'jobboardwp' ), '1.0' );
		}


		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		public function __wakeup() {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- strict output
			_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'jobboardwp' ), '1.0' );
		}


		/**
		 * JB pseudo-constructor.
		 *
		 * @since 1.0
		 */
		public function jb_construct() {
			//register autoloader for include JB classes
			spl_autoload_register( array( $this, 'jb__autoloader' ) );

			if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
				// run activation
				register_activation_hook( JB_PLUGIN, array( $this->install(), 'activation' ) );
				if ( is_multisite() && ! defined( 'DOING_AJAX' ) ) {
					add_action( 'wp_loaded', array( $this->install(), 'maybe_network_activation' ) );
				}

				// deactivation
				register_deactivation_hook( JB_PLUGIN, array( $this->common()->cron(), 'unschedule_tasks' ) );

				// init cron tasks
				$this->common()->cron()->maybe_schedule_tasks();

				// textdomain loading
				$this->localize();

				// include JB classes
				$this->includes();
			}
		}


		/**
		 * Autoload JB classes handler
		 *
		 * @since 1.0
		 *
		 * @param $class
		 */
		public function jb__autoloader( $class ) {
			if ( strpos( $class, 'jb' ) === 0 ) {
				$array                        = explode( '\\', strtolower( $class ) );
				$array[ count( $array ) - 1 ] = 'class-' . end( $array );

				if ( strpos( $class, 'jb\\' ) === 0 ) {
					$class     = implode( '\\', $array );
					$path      = str_replace( array( 'jb\\', '_', '\\' ), array( DIRECTORY_SEPARATOR, '-', DIRECTORY_SEPARATOR ), $class );
					$full_path = JB_PATH . 'includes' . $path . '.php';
				}

				if ( isset( $full_path ) && file_exists( $full_path ) ) {
					/** @noinspection PhpIncludeInspection */
					include_once $full_path;
				}
			}
		}


		/**
		 * Loading JB textdomain
		 *
		 * 'jobboardwp' by default
		 *
		 * @since 1.0
		 */
		public function localize() {
			$language_locale = ( '' !== get_locale() ) ? get_locale() : 'en_US';
			$language_locale = apply_filters( 'jb_language_locale', $language_locale );

			$language_domain = apply_filters( 'jb_language_textdomain', 'jobboardwp' );

			$language_file = WP_LANG_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $language_domain . '-' . $language_locale . '.mo';
			$language_file = apply_filters( 'jb_language_file', $language_file );

			load_textdomain( $language_domain, $language_file );
		}


		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		public function includes() {
			$this->common()->includes();

			if ( $this->is_request( 'ajax' ) ) {
				$this->ajax()->includes();
			} elseif ( $this->is_request( 'admin' ) ) {
				$this->admin()->includes();
			} elseif ( $this->is_request( 'frontend' ) ) {
				$this->frontend()->includes();
			}
		}


		/**
		 * Getting the Config class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\Config
		 */
		public function config() {
			return $this->call_class( 'jb\Config' );
		}


		/**
		 * Getting the Install class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\admin\Install()
		 */
		public function install() {
			return $this->call_class( 'jb\admin\Install' );
		}


		/**
		 * Getting the Options class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\common\Options()
		 */
		public function options() {
			return $this->call_class( 'jb\common\Options' );
		}


		/**
		 * Getting the Common class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\common\Init()
		 */
		public function common() {
			return $this->call_class( 'jb\common\Init' );
		}


		/**
		 * Getting the Admin class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\admin\Init()
		 */
		public function admin() {
			return $this->call_class( 'jb\admin\Init' );
		}


		/**
		 * Getting the Frontend class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\frontend\Init()
		 */
		public function frontend() {
			return $this->call_class( 'jb\frontend\Init' );
		}


		/**
		 * Getting the AJAX class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\ajax\Init()
		 */
		public function ajax() {
			return $this->call_class( 'jb\ajax\Init' );
		}


		/**
		 * @param string $class
		 *
		 * @return mixed
		 *
		 * @since 1.0
		 */
		private function call_class( $class ) {
			$key = strtolower( $class );

			if ( empty( $this->classes[ $key ] ) ) {
				$this->classes[ $key ] = new $class();
			}

			return $this->classes[ $key ];
		}

	}
}


/**
 * Function for calling JB methods and variables
 *
 * @since 1.0
 *
 * @return JB
 */
function JB() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return JB::instance();
}
