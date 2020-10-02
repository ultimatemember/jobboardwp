<?php if ( ! defined( 'ABSPATH' ) ) exit;


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
		protected static $instance = null;


		/**
		 * @var array all plugin's classes
		 */
		public $classes = [];


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
		static public function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->_jb_construct();
			}

			return self::$instance;
		}


		/**
		 * Create plugin classes
		 *
		 * @since 1.0
		 * @see JB()
		 *
		 * @param $name
		 * @param array $params
		 * @return mixed
		 */
		public function __call( $name, array $params ) {
			if ( empty( $this->classes[ $name ] ) ) {
				$this->classes[ $name ] = apply_filters( 'jb_call_object_' . $name, false );
			}

			return $this->classes[ $name ];
		}


		/**
		 * Cloning is forbidden.
		 * @since 1.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'jobboardwp' ), '1.0' );
		}


		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'jobboardwp' ), '1.0' );
		}


		/**
		 * JB constructor.
		 *
		 * @since 1.0
		 */
		function __construct() {
			parent::__construct();
		}


		/**
		 * JB pseudo-constructor.
		 *
		 * @since 1.0
		 */
		function _jb_construct() {
			//register autoloader for include JB classes
			spl_autoload_register( [ $this, 'jb__autoloader' ] );

			if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
				// run activation
				register_activation_hook( jb_plugin, [ $this->install(), 'activation' ] );
				if ( is_multisite() && ! defined( 'DOING_AJAX' ) ) {
					add_action( 'wp_loaded', [ $this->install(), 'maybe_network_activation' ] );
				}

				// deactivation
				register_deactivation_hook( jb_plugin, [ $this->common()->cron(), 'unschedule_tasks' ] );

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
		function jb__autoloader( $class ) {
			if ( strpos( $class, 'jb' ) === 0 ) {

				$array = explode( '\\', strtolower( $class ) );
				$array[ count( $array ) - 1 ] = 'class-'. end( $array );

				if ( strpos( $class, 'jb\\' ) === 0 ) {
					$class = implode( '\\', $array );
					$path = str_replace( [ 'jb\\', '_', '\\' ], [ DIRECTORY_SEPARATOR, '-', DIRECTORY_SEPARATOR ], $class );
					$full_path =  jb_path . 'includes' . $path . '.php';
				}

				if ( isset( $full_path ) && file_exists( $full_path ) ) {
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
		function localize() {
			$language_locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
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
		function config() {
			if ( empty( $this->classes['jb\config'] ) ) {
				$this->classes['jb\config'] = new jb\Config();
			}

			return $this->classes['jb\config'];
		}


		/**
		 * Getting the Install class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\admin\Install()
		 */
		function install() {
			if ( empty( $this->classes['jb\admin\install'] ) ) {
				$this->classes['jb\admin\install'] = new jb\admin\Install();
			}
			return $this->classes['jb\admin\install'];
		}


		/**
		 * Getting the Options class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\common\Options()
		 */
		function options() {
			if ( empty( $this->classes['jb\common\options'] ) ) {
				$this->classes['jb\common\options'] = new jb\common\Options();
			}
			return $this->classes['jb\common\options'];
		}


		/**
		 * Getting the Common class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\common\Common()
		 */
		function common() {
			if ( empty( $this->classes['jb\common\common'] ) ) {
				$this->classes['jb\common\common'] = new jb\common\Common();
			}
			return $this->classes['jb\common\common'];
		}


		/**
		 * Getting the Admin class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\admin\Common()
		 */
		function admin() {
			if ( empty( $this->classes['jb\admin\common'] ) ) {
				$this->classes['jb\admin\common'] = new jb\admin\Common();
			}
			return $this->classes['jb\admin\common'];
		}


		/**
		 * Getting the Frontend class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\frontend\Common()
		 */
		function frontend() {
			if ( empty( $this->classes['jb\frontend\common'] ) ) {
				$this->classes['jb\frontend\common'] = new jb\frontend\Common();
			}
			return $this->classes['jb\frontend\common'];
		}


		/**
		 * Getting the AJAX class instance
		 *
		 * @since 1.0
		 *
		 * @return jb\ajax\Common()
		 */
		function ajax() {
			if ( empty( $this->classes['jb\ajax\common'] ) ) {
				$this->classes['jb\ajax\common'] = new jb\ajax\Common();
			}
			return $this->classes['jb\ajax\common'];
		}


		/**
		 * Function for add classes to $this->classes
		 * for run using JB()
		 *
		 * @since 1.0
		 *
		 * @param string $class_name
		 * @param bool $instance
		 */
		public function set_class( $class_name, $instance = false ) {
			if ( empty( $this->classes[ $class_name ] ) ) {
				$class = 'JB_' . $class_name;
				$this->classes[ $class_name ] = $instance ? $class::instance() : new $class;
			}
		}


		/**
		 * @param string $class
		 *
		 * @return stdClass
		 *
		 * @since 1.0
		 */
		function call_class( $class ) {
			$key = strtolower( $class );

			if ( empty( $this->classes[ $key ] ) ) {
				$this->classes[ $key ] = new $class;
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
function JB() {
	return JB::instance();
}