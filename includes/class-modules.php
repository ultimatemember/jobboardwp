<?php
namespace jb;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Modules
 *
 * @package jb
 *
 * @since 1.2.1
 */
class Modules {

	/**
	 * Modules list
	 *
	 * @var array
	 */
	private $list = array();

	/**
	 * Modules constructor.
	 */
	public function __construct() {
		add_action( 'jb_core_loaded', array( &$this, 'predefined_modules' ), 0 );
	}

	/**
	 * Set modules list
	 *
	 * @usedby on `jb_core_loaded` hook for modules initialization
	 *
	 * @uses get_plugins() for getting installed plugins list
	 * @uses DIRECTORY_SEPARATOR for getting proper path to modules' directories
	 */
	public function predefined_modules() {
		$modules = JB()->config()->get( 'modules' );
		/**
		 * Filters predefined modules list.
		 *
		 * Note: It's the filter for getting the modules list, but not validate the module structure and necessary files yet!
		 *
		 * @since 1.2.2
		 * @hook jb_predefined_modules
		 *
		 * @param {array} $modules Modules list. See structure inside class-config.php file.
		 *
		 * @return {array} Modules list.
		 */
		$modules = apply_filters( 'jb_predefined_modules', $modules );

		/** This filter is documented in wp-admin/includes/class-wp-plugins-list-table.php */
		$all_plugins = apply_filters( 'all_plugins', get_plugins() );

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		foreach ( $modules as &$data ) {
			// @todo checking the proper module structure function if not proper make 'invalid' data with displaying red line in list table

			// check the module's dir
			if ( ! is_dir( $data['path'] ) ) {

				$data['disabled']    = true;
				$data['description'] = '<strong>' . __( 'Module has not been installed properly. Please check the module\'s directory and re-install it.', 'jobboardwp' ) . '</strong><br />' . $data['description'];

			} elseif ( array_key_exists( 'plugins_required', $data ) ) {
				$maybe_installed = array_intersect( array_keys( $data['plugins_required'] ), array_keys( $all_plugins ) );
				$not_installed   = array_diff( array_keys( $data['plugins_required'] ), $maybe_installed );

				$data['disabled'] = count( $not_installed ) > 0;

				if ( $data['disabled'] ) {
					$plugins_titles = array();
					foreach ( $not_installed as $plugin_slug ) {
						$plugins_titles[] = '<a href="' . esc_url( $data['plugins_required'][ $plugin_slug ]['url'] ) . '" target="_blank">' . esc_html( $data['plugins_required'][ $plugin_slug ]['name'] ) . '</a>';
					}
					$plugins_titles = '"' . implode( '", "', $plugins_titles ) . '"';
					/* translators: %s: activate notice */
					$data['description'] = '<strong>' . sprintf( _n( 'Module cannot be activated until %s plugin is installed and activated.', 'Module cannot be activated until %s plugins are installed and activated.', count( $not_installed ), 'jobboardwp' ), $plugins_titles ) . '</strong><br />' . $data['description'];
				} else {
					$maybe_activated = array_intersect( array_keys( $data['plugins_required'] ), $active_plugins );
					$not_active      = array_diff( array_keys( $data['plugins_required'] ), $maybe_activated );

					$data['disabled'] = count( $not_active ) > 0;
					if ( $data['disabled'] ) {
						$plugins_titles = array();
						foreach ( $not_active as $plugin_slug ) {
							$plugins_titles[] = '<a href="' . esc_url( $data['plugins_required'][ $plugin_slug ]['url'] ) . '" target="_blank">' . esc_html( $data['plugins_required'][ $plugin_slug ]['name'] ) . '</a>';
						}
						$plugins_titles = '"' . implode( '", "', $plugins_titles ) . '"';
						/* translators: %s: activate notice */
						$data['description'] = '<strong>' . sprintf( _n( 'Module cannot be activated until %s plugin is activated.', 'Module cannot be activated until %s plugins are activated.', count( $not_active ), 'jobboardwp' ), $plugins_titles ) . '</strong><br />' . $data['description'];
					}
				}
			}

			// set `disabled = false` by default
			if ( ! array_key_exists( 'disabled', $data ) ) {
				$data['disabled'] = false;
			}
		}
		unset( $data );
		/**
		 * Filters already validated modules list.
		 *
		 * Note: It's the filter for getting the modules list. There are only validated structure and enabled for activation modules.
		 *       You may use this filter for adding additional validation for modules.
		 *
		 * @since 1.2.2
		 * @hook jb_predefined_validated_modules
		 *
		 * @param {array} $modules Modules list.
		 *
		 * @return {array} Modules list.
		 */
		$this->list = apply_filters( 'jb_predefined_validated_modules', $modules );
	}

	/**
	 * Get list of modules
	 *
	 * @uses list
	 *
	 * @return array
	 */
	public function get_list() {
		/**
		 * Filters already formatted modules list.
		 *
		 * Note: It's the filter for getting the formatted modules list.
		 *
		 * @since 1.2.2
		 * @hook jb_formatting_modules_list
		 *
		 * @param {array} $modules Modules list.
		 *
		 * @return {array} Modules list.
		 */
		return apply_filters( 'jb_formatting_modules_list', $this->list );
	}

	/**
	 * Get module data
	 *
	 * @param string $slug Module slug
	 *
	 * @return bool|array Returns `false` if module doesn't exists
	 *
	 * @uses exists
	 */
	public function get_data( $slug ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		return $this->list[ $slug ];
	}

	/**
	 * Checking if module exists
	 *
	 * @param string $slug Module slug
	 *
	 * @return bool Returns `false` if module doesn't exists, otherwise `true`
	 */
	public function exists( $slug ) {
		return array_key_exists( $slug, $this->list );
	}

	/**
	 * Check if module is active
	 *
	 * @param string $slug Module slug
	 *
	 *
	 * @uses exists
	 * @uses is_disabled
	 * @uses JB::undash()
	 * @uses JB::options()
	 *
	 * @return bool
	 */
	public function is_active( $slug ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		if ( $this->is_disabled( $slug ) ) {
			return false;
		}

		$slug      = JB()->undash( $slug );
		$is_active = JB()->options()->get( "module_{$slug}_on" );

		return ! empty( $is_active );
	}

	/**
	 * Check if module is disabled
	 *
	 * @param string $slug Module slug
	 *
	 * @uses exists
	 * @uses get_data
	 *
	 * @return bool
	 */
	public function is_disabled( $slug ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		$data = $this->get_data( $slug );
		return ! empty( $data['disabled'] );
	}

	/**
	 * Check if current user can activate a module
	 *
	 * @param string $slug Module slug
	 *
	 * @uses exists
	 * @uses is_disabled
	 * @uses is_active
	 *
	 * @return bool
	 */
	public function can_activate( $slug ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		if ( $this->is_disabled( $slug ) ) {
			return false;
		}

		if ( $this->is_active( $slug ) ) {
			return false;
		}

		/**
		 * Filters the marker to check if the module can be activated.
		 *
		 * @since 1.2.2
		 * @hook jb_module_can_activate
		 *
		 * @param {bool}   $can_activate Marker for checking if module can be activated. It's `true` by default.
		 * @param {string} $slug Module slug.
		 *
		 * @return {bool} `true` if a module can be activated.
		 */
		return apply_filters( 'jb_module_can_activate', true, $slug );
	}

	/**
	 * Checking if current user can deactivate a module
	 *
	 * @param string $slug Module slug
	 *
	 * @uses exists
	 * @uses is_disabled
	 * @uses is_active
	 *
	 * @return bool
	 */
	public function can_deactivate( $slug ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		if ( $this->is_disabled( $slug ) ) {
			return false;
		}

		if ( ! $this->is_active( $slug ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checking if current user can flush module's data
	 *
	 * @param string $slug Module slug
	 *
	 * @uses exists
	 * @uses is_disabled
	 * @uses is_active
	 * @uses JB::undash()
	 * @uses JB::options()
	 *
	 * @return bool
	 */
	public function can_flush( $slug ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		if ( $this->is_disabled( $slug ) ) {
			return false;
		}

		if ( $this->is_active( $slug ) ) {
			return false;
		}

		if ( ! $this->is_first_installed( $slug ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checking if the module had been already first-time activated
	 * Must be reset this marker after flushing data of the module
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function is_first_installed( $slug ) {
		$slug             = JB()->undash( $slug );
		$first_activation = JB()->options()->get( "module_{$slug}_first_activation" );

		return ! empty( $first_activation );
	}

	/**
	 * Checking if the module has the settings section in wp-admin dashboard
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function has_settings_section( $slug ) {
		return ! empty( JB()->admin()->settings()->settings_structure['modules']['sections'][ $slug ] );
	}

	/**
	 * Module's activation handler
	 *
	 * @param string $slug Module's slug
	 *
	 * @uses can_activate
	 * @uses install::start()
	 * @uses JB::undash()
	 * @uses JB::options()
	 *
	 * @return bool
	 */
	public function activate( $slug ) {
		if ( ! $this->can_activate( $slug ) ) {
			return false;
		}

		$this->install( $slug )->start();

		$slug = JB()->undash( $slug );

		JB()->options()->update( "module_{$slug}_on", true );

		$first_activation = JB()->options()->get( "module_{$slug}_first_activation" );
		if ( empty( $first_activation ) ) {
			JB()->options()->update( "module_{$slug}_first_activation", time() );
		}

		return true;
	}

	/**
	 * Module's deactivation handler
	 *
	 * @param string $slug Module slug
	 *
	 * @uses can_deactivate
	 * @uses JB::undash()
	 * @uses JB::options()
	 *
	 * @return bool
	 */
	public function deactivate( $slug ) {
		if ( ! $this->can_deactivate( $slug ) ) {
			return false;
		}

		$slug = JB()->undash( $slug );

		JB()->options()->update( "module_{$slug}_on", false );

		return true;
	}

	/**
	 * Module's flushing data handler
	 *
	 * @param string $slug Module slug
	 *
	 * @uses can_flush
	 * @uses get_data
	 * @uses JB::undash()
	 * @uses JB::options()
	 *
	 * @return bool
	 */
	public function flush_data( $slug ) {
		if ( ! $this->can_flush( $slug ) ) {
			return false;
		}

		$data = $this->get_data( $slug );

		$slug = JB()->undash( $slug );
		JB()->options()->delete( "module_{$slug}_first_activation" );

		$uninstall_path = $data['path'] . DIRECTORY_SEPARATOR . 'uninstall.php';
		if ( file_exists( $uninstall_path ) ) {
			include_once $uninstall_path;
		}

		return true;
	}

	/**
	 * Load all modules
	 *
	 * @uses get_list
	 * @uses is_active
	 * @uses run
	 */
	public function load_modules() {
		$modules = $this->get_list();
		if ( empty( $modules ) ) {
			return;
		}

		foreach ( $modules as $slug => $data ) {
			if ( ! $this->is_active( $slug ) ) {
				continue;
			}

			$this->run( $slug );
		}
	}

	/**
	 * Run main class of module
	 *
	 * @param string $slug Module slug
	 *
	 * @uses JB::undash()
	 * @uses JB::call_class()
	 */
	private function run( $slug ) {
		$slug = JB()->undash( $slug );
		JB()->call_class( "jbm\\{$slug}\\Init" );
	}

	/**
	 * Installation handler for single module
	 *
	 * @param string $slug Module slug
	 *
	 * @uses JB::undash()
	 * @uses JB::call_class()
	 *
	 * @return mixed
	 */
	private function install( $slug ) {
		$slug = JB()->undash( $slug );
		return JB()->call_class( "jbm\\{$slug}\\Install" );
	}
}
