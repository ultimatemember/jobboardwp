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
		 * @var self The single instance of the class
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
		 * @return self
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
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'jobboardwp' ), '1.0' );
		}


		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'jobboardwp' ), '1.0' );
		}


		/**
		 * JB pseudo-constructor.
		 *
		 * @since 1.0
		 */
		public function jb_construct() {
			$this->define_constants();

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
		 * Define JobBoardWP Constants.
		 *
		 * @since 1.1.1
		 */
		private function define_constants() {
			$this->define( 'JB_TEMPLATE_CONFLICT_TEST', false );
		}


		/**
		 * Autoload JB classes handler
		 *
		 * @since 1.0
		 *
		 * @param string $class
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
			/**
			 * Filters the language locale before loading textdomain.
			 *
			 * @since 1.1.0
			 * @hook jb_language_locale
			 *
			 * @param {string} $language_locale Current language locale.
			 *
			 * @return {string} Maybe changed language locale.
			 */
			$language_locale = apply_filters( 'jb_language_locale', $language_locale );

			/**
			 * Filters the plugin's textdomain.
			 *
			 * @since 1.1.0
			 * @hook jb_language_textdomain
			 *
			 * @param {string} $textdomain Plugin's textdomain.
			 *
			 * @return {string} Maybe changed plugin's textdomain.
			 */
			$language_domain = apply_filters( 'jb_language_textdomain', 'jobboardwp' );

			$language_file = WP_LANG_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $language_domain . '-' . $language_locale . '.mo';
			/**
			 * Filters the path to the language file (*.mo).
			 *
			 * @since 1.1.0
			 * @hook jb_language_file
			 *
			 * @param {string} $language_file Default path to the language file.
			 *
			 * @return {string} Language file path.
			 */
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
			$this->integrations();

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
		 * @since 1.1.1
		 *
		 * @return jb\integrations\Init
		 */
		public function integrations() {
			return $this->call_class( 'jb\integrations\Init' );
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
		 * @return jb\admin\Install
		 */
		public function install() {
			return $this->call_class( 'jb\admin\Install' );
		}


		/**
		 * Getting the Options class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\common\Options
		 */
		public function options() {
			return $this->call_class( 'jb\common\Options' );
		}


		/**
		 * Getting the Common class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\common\Init
		 */
		public function common() {
			return $this->call_class( 'jb\common\Init' );
		}


		/**
		 * Getting the Admin class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\admin\Init
		 */
		public function admin() {
			return $this->call_class( 'jb\admin\Init' );
		}


		/**
		 * Getting the Frontend class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\frontend\Init
		 */
		public function frontend() {
			return $this->call_class( 'jb\frontend\Init' );
		}


		/**
		 * Getting the AJAX class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\ajax\Init
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
 * @return \JB
 */
function JB() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return JB::instance();
}
