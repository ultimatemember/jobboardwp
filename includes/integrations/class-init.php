<?php
namespace jb\integrations;

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

			add_filter( 'jb_pre_template_locations', array( &$this, 'pre_template_locations_common_locale' ), 10, 4 );
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
		 * Email notifications integration with `get_user_locale()`
		 *
		 * @since 1.1.1
		 * @since 1.2.2 Added $module argument.
		 *
		 * @param array  $template_locations
		 * @param string $template_name
		 * @param string $module
		 * @param string $template_path
		 *
		 * @return array
		 */
		public function pre_template_locations_common_locale( $template_locations, $template_name, $module, $template_path ) {
			// make pre templates locations array to avoid the conflicts between different locales when multilingual plugins are integrated
			// e.g. "jobboardwp/ru_RU(user locale)/uk(WPML)/emails/job_approved.php"
			// must be the next priority:
			//
			// jobboardwp/{user locale}/emails/job_approved.php
			// jobboardwp/{site locale}/emails/job_approved.php
			$template_locations_pre = $template_locations;

			/**
			 * Filters the template locations array for WP native `locate_template()` function.
			 *
			 * Note: Internal JobBoardWP hook for getting individual multilingual location in the common integration function.
			 *
			 * @since 1.1.1
			 * @since 1.2.2 Added $module argument.
			 * @hook jb_pre_template_locations_common_locale_integration
			 *
			 * @param {array}  $template_locations Template locations array for WP native `locate_template()` function.
			 * @param {string} $template_name      Template name.
			 * @param {string} $module             Module slug. (default: '').
			 * @param {string} $template_path      Template path. (default: '').
			 *
			 * @return {array} An array for WP native `locate_template()` function with paths where we need to search for the $template_name.
			 */
			$template_locations = apply_filters( 'jb_pre_template_locations_common_locale_integration', $template_locations, $template_name, $module, $template_path );

			// use the user_locale only for email notifications templates
			if ( 0 === strpos( $template_name, 'emails/' ) && JB()->common()->mail()->is_sending() ) {
				/**
				 * Filters the user ID for getting it locale when getting individual multilingual template's location in the common integration function.
				 *
				 * Note: Internal JobBoardWP hook for getting individual multilingual location related to email templates in the common integration function.
				 *
				 * @since 1.2.0
				 * @hook jb_template_locations_base_user_id_for_locale
				 *
				 * @param {int}    $user_id       Current User ID.
				 * @param {string} $template_name Template name.
				 *
				 * @return {int} User ID for getting current recipient locale.
				 */
				$base_user_id        = apply_filters( 'jb_template_locations_base_user_id_for_locale', get_current_user_id(), $template_name );
				$current_user_locale = get_user_locale( $base_user_id );

				// todo skip duplications e.g. "jobboardwp/ru_RU/uk/emails/job_approved.php" when current language = uk, but user locale is ru_RU. Must be only "jobboardwp/ru_RU/emails/job_approved.php" in this case
				$locale_template_locations = array_map(
					function ( $item ) use ( $template_path, $current_user_locale ) {
						return str_replace( trailingslashit( $template_path ), trailingslashit( $template_path ) . $current_user_locale . '/', $item );
					},
					$template_locations_pre
				);

				$template_locations = array_merge( $locale_template_locations, $template_locations );
			}

			return $template_locations;
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
			return defined( 'WEGLOT_VERSION' ) && function_exists( 'weglot_get_current_language' );
		}
	}
}
