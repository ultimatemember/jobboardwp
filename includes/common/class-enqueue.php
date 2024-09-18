<?php
namespace jb\common;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\common\Enqueue' ) ) {

	/**
	 * Class Enqueue
	 *
	 * @package jb\common
	 */
	class Enqueue {

		/**
		 * @var array JS URLs
		 *
		 * @since 1.0
		 */
		public $js_url = array();

		/**
		 * @var array CSS URLs
		 *
		 * @since 1.0
		 */
		public $css_url = array();

		/**
		 * @var array assets URLs
		 *
		 * @since 1.0
		 */
		public $url = array();

		/**
		 * @var array Google Autocomplete locales
		 *
		 * @since 1.0
		 */
		public $g_locales = array();

		/**
		 * @var string FontAwesome version
		 *
		 * @since 1.0
		 */
		public $fa_version = '5.13.0';

		public $common_localize = array();

		/**
		 * Enqueue constructor.
		 */
		public function __construct() {
			$this->url['common']     = JB_URL . 'assets/common/';
			$this->js_url['common']  = JB_URL . 'assets/common/js/';
			$this->css_url['common'] = JB_URL . 'assets/common/css/';

			// see all locales here https://developers.google.com/maps/faq#languagesupport
			$this->g_locales = array(
				'af'     => __( 'Afrikaans', 'jobboardwp' ),
				'sq'     => __( 'Albanian', 'jobboardwp' ),
				'am'     => __( 'Amharic', 'jobboardwp' ),
				'ar'     => __( 'Arabic', 'jobboardwp' ),
				'hy'     => __( 'Armenian', 'jobboardwp' ),
				'az'     => __( 'Azerbaijani', 'jobboardwp' ),
				'eu'     => __( 'Basque', 'jobboardwp' ),
				'be'     => __( 'Belarusian', 'jobboardwp' ),
				'bn'     => __( 'Bengali', 'jobboardwp' ),
				'bs'     => __( 'Bosnian', 'jobboardwp' ),
				'my'     => __( 'Burmese', 'jobboardwp' ),
				'ca'     => __( 'Catalan', 'jobboardwp' ),
				'zh'     => __( 'Chinese', 'jobboardwp' ),
				'zh-CN'  => __( 'Chinese (Simplified)', 'jobboardwp' ),
				'zh-HK'  => __( 'Chinese (Hong Kong)', 'jobboardwp' ),
				'zh-TW'  => __( 'Chinese (Traditional)', 'jobboardwp' ),
				'hr'     => __( 'Croatian', 'jobboardwp' ),
				'cs'     => __( 'Czech', 'jobboardwp' ),
				'da'     => __( 'Danish', 'jobboardwp' ),
				'nl'     => __( 'Dutch', 'jobboardwp' ),
				'en'     => __( 'English', 'jobboardwp' ),
				'en-AU'  => __( 'English (Australian)', 'jobboardwp' ),
				'en-GB'  => __( 'English (Great Britain)', 'jobboardwp' ),
				'et'     => __( 'Estonian', 'jobboardwp' ),
				'fa'     => __( 'Farsi', 'jobboardwp' ),
				'fi'     => __( 'Finnish', 'jobboardwp' ),
				'fil'    => __( 'Filipino', 'jobboardwp' ),
				'fr'     => __( 'French', 'jobboardwp' ),
				'fr-CA'  => __( 'French (Canada)', 'jobboardwp' ),
				'gl'     => __( 'Galician', 'jobboardwp' ),
				'ka'     => __( 'Kartuli', 'jobboardwp' ),
				'de'     => __( 'German', 'jobboardwp' ),
				'el'     => __( 'Greek', 'jobboardwp' ),
				'gu'     => __( 'Gujarati', 'jobboardwp' ),
				'iw'     => __( 'Hebrew', 'jobboardwp' ),
				'hi'     => __( 'Hindi', 'jobboardwp' ),
				'hu'     => __( 'Hungarian', 'jobboardwp' ),
				'is'     => __( 'Icelandic', 'jobboardwp' ),
				'id'     => __( 'Indonesian', 'jobboardwp' ),
				'it'     => __( 'Italian', 'jobboardwp' ),
				'ja'     => __( 'Japanese', 'jobboardwp' ),
				'kn'     => __( 'Kannada', 'jobboardwp' ),
				'kk'     => __( 'Kazakh', 'jobboardwp' ),
				'km'     => __( 'Khmer', 'jobboardwp' ),
				'ko'     => __( 'Korean', 'jobboardwp' ),
				'ky'     => __( 'Kyrgyz', 'jobboardwp' ),
				'lo'     => __( 'Lao', 'jobboardwp' ),
				'lv'     => __( 'Latvian', 'jobboardwp' ),
				'lt'     => __( 'Lithuanian', 'jobboardwp' ),
				'mk'     => __( 'Macedonian', 'jobboardwp' ),
				'ms'     => __( 'Malay', 'jobboardwp' ),
				'ml'     => __( 'Malayalam', 'jobboardwp' ),
				'mr'     => __( 'Marathi', 'jobboardwp' ),
				'mn'     => __( 'Mongolian', 'jobboardwp' ),
				'ne'     => __( 'Nepali', 'jobboardwp' ),
				'no'     => __( 'Norwegian', 'jobboardwp' ),
				'pl'     => __( 'Polish', 'jobboardwp' ),
				'pt'     => __( 'Portuguese', 'jobboardwp' ),
				'pt-BR'  => __( 'Portuguese (Brazil)', 'jobboardwp' ),
				'pt-PT'  => __( 'Portuguese (Portugal)', 'jobboardwp' ),
				'pa'     => __( 'Punjabi', 'jobboardwp' ),
				'ro'     => __( 'Romanian', 'jobboardwp' ),
				'ru'     => __( 'Russian', 'jobboardwp' ),
				'sr'     => __( 'Serbian', 'jobboardwp' ),
				'si'     => __( 'Sinhalese', 'jobboardwp' ),
				'sk'     => __( 'Slovak', 'jobboardwp' ),
				'sl'     => __( 'Slovenian', 'jobboardwp' ),
				'es'     => __( 'Spanish', 'jobboardwp' ),
				'es-419' => __( 'Spanish (Latin America)', 'jobboardwp' ),
				'sw'     => __( 'Swahili', 'jobboardwp' ),
				'sv'     => __( 'Swedish', 'jobboardwp' ),
				'ta'     => __( 'Tamil', 'jobboardwp' ),
				'te'     => __( 'Telugu', 'jobboardwp' ),
				'th'     => __( 'Thai', 'jobboardwp' ),
				'tr'     => __( 'Turkish', 'jobboardwp' ),
				'uk'     => __( 'Ukrainian', 'jobboardwp' ),
				'ur'     => __( 'Urdu', 'jobboardwp' ),
				'uz'     => __( 'Uzbek', 'jobboardwp' ),
				'vi'     => __( 'Vietnamese', 'jobboardwp' ),
				'zu'     => __( 'Zulu', 'jobboardwp' ),
			);

			add_action( 'plugins_loaded', array( $this, 'init_variables' ), 10 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'common_libs' ), 9 );
			add_action( 'wp_enqueue_scripts', array( &$this, 'common_libs' ), 9 );

			add_filter( 'jb_frontend_common_styles_deps', array( &$this, 'extends_styles' ), 10, 1 );

			global $wp_version;
			if ( version_compare( $wp_version, '5.8', '>=' ) ) {
				add_filter( 'block_categories_all', array( &$this, 'blocks_category' ), 10, 1 );
			} else {
				add_filter( 'block_categories', array( &$this, 'blocks_category' ), 10, 1 );
			}

			$this->css_url['admin']   = JB_URL . 'assets/admin/css/';
			$this->js_url['admin']    = JB_URL . 'assets/admin/js/';
			$this->js_url['frontend'] = JB_URL . 'assets/frontend/js/';
			add_action( 'enqueue_block_assets', array( &$this, 'block_editor' ), 11 );
		}

		/**
		 * Add Gutenberg category for JobBoardWP shortcodes
		 *
		 * @param array $categories
		 *
		 * @return array
		 */
		public function blocks_category( $categories ) {
			return array_merge(
				$categories,
				array(
					array(
						'slug'  => 'jb-blocks',
						'title' => __( 'JobBoardWP', 'jobboardwp' ),
					),
				)
			);
		}

		/**
		 * Enqueue Gutenberg Block Editor assets
		 */
		public function block_editor() {
			global $current_screen;

			// todo enqueue scripts|styles for block editor properly

			wp_register_style( 'jb_admin_blocks_shortcodes', $this->css_url['admin'] . 'blocks' . JB()->scrips_prefix . '.css', array(), JB_VERSION );
			wp_enqueue_style( 'jb_admin_blocks_shortcodes' );
			wp_register_script( 'jb_admin_blocks_shortcodes', $this->js_url['admin'] . 'blocks' . JB()->scrips_prefix . '.js', array( 'wp-i18n', 'wp-blocks', 'wp-components' ), JB_VERSION, true );

			wp_set_script_translations( 'jb_admin_blocks_shortcodes', 'jobboardwp' );

			$jb_options = array();
			if ( isset( $current_screen->id ) && 'jb-job' === $current_screen->id ) {
				$jb_options['exclude_blocks'] = 1;
			} elseif ( isset( $current_screen->id ) && 'widgets' === $current_screen->id ) {
				$jb_options['exclude_blocks'] = 2;
			} else {
				$jb_options['exclude_blocks'] = 0;
			}

			wp_localize_script( 'jb_admin_blocks_shortcodes', 'jb_blocks_options', $jb_options );

			wp_enqueue_script( 'jb_admin_blocks_shortcodes' );

			// render blocks
			wp_enqueue_style( 'jb-common' );
			wp_enqueue_style( 'jb-jobs-widget' );
			wp_enqueue_style( 'jb-font-awesome' );
			wp_enqueue_style( 'jb-job' );
			wp_enqueue_style( 'jb-forms-preview' );
			wp_enqueue_style( 'jb-job-categories' );
			wp_enqueue_style( 'jb-jobs-dashboard' );
			wp_enqueue_style( 'jb-jobs' );

			if ( isset( $current_screen->id ) ) {
				wp_register_script( 'jb-front-global', $this->js_url['frontend'] . 'global' . JB()->scrips_prefix . '.js', array( 'jb-helptip' ), JB_VERSION, true );
				wp_enqueue_script( 'jb-helptip' );
				wp_localize_script(
					'jb-front-global',
					'jb_front_data',
					array(
						'nonce' => wp_create_nonce( 'jb-frontend-nonce' ),
					)
				);
			}

			wp_enqueue_script( 'jb-front-global' );
			wp_enqueue_script( 'jb-job-categories' );
			wp_enqueue_script( 'jb-dropdown' );
			wp_enqueue_script( 'jb-jobs-dashboard' );
			wp_enqueue_script( 'jb-jobs' );
			wp_enqueue_script( 'jb-post-job' );
		}

		/**
		 * Getting current Google Autocomplete locale
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_g_locale() {
			$locale  = get_locale();
			$locales = array_keys( $this->g_locales );
			if ( ! in_array( $locale, $locales, true ) ) {
				$locale = str_replace( '_', '-', $locale );
				if ( ! in_array( $locale, $locales, true ) ) {
					$locale = explode( '-', $locale );
					if ( isset( $locale[1] ) ) {
						$locale = $locale[1];
					}
				}
			}

			return $locale;
		}

		/**
		 * Init variables for enqueue scripts
		 *
		 * @since 1.0
		 */
		public function init_variables() {
			JB()->scrips_prefix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			/**
			 * Filters the data array that needs to be localized inside wp-admin or frontend global JS.
			 *
			 * @since 1.1.0
			 * @hook jb_common_js_variables
			 *
			 * @param {array} $localize_data Array with some data for JS.
			 *
			 * @return {array} Data for localize in JS.
			 */
			$this->common_localize = apply_filters(
				'jb_common_js_variables',
				array(
					'locale' => get_locale(),
				)
			);
		}

		/**
		 * It checks if jquery-ui is enabled.
		 *
		 * @return bool
		 */
		public function is_jquery_ui_enabled() {
			/**
			 * Filters the jquery-ui styles in styles queue.
			 *
			 * Note: Set to `true` if you need to disable jquery-ui scripts.
			 *
			 * @since 1.2.3
			 * @hook jb_disable_jquery_ui
			 *
			 * @param {bool} $disable_jquery_ui Disable jquery-ui styles.
			 *
			 * @return {bool} Disable jquery-ui styles.
			 */
			$disable_jquery_ui = apply_filters( 'jb_disable_jquery_ui', false );
			return ! $disable_jquery_ui;
		}

		/**
		 * Register common JS/CSS libraries
		 *
		 * @since 1.0
		 */
		public function common_libs() {
			if ( $this->is_jquery_ui_enabled() ) {
				wp_register_style( 'jquery-ui', $this->url['common'] . 'libs/jquery-ui/jquery-ui' . JB()->scrips_prefix . '.css', array(), '1.12.1' );
			}

			if ( ! JB()->options()->get( 'disable-fa-styles' ) ) {
				wp_register_style( 'jb-far', $this->url['common'] . 'libs/fontawesome/css/regular' . JB()->scrips_prefix . '.css', array(), $this->fa_version );
				wp_register_style( 'jb-fas', $this->url['common'] . 'libs/fontawesome/css/solid' . JB()->scrips_prefix . '.css', array(), $this->fa_version );
				wp_register_style( 'jb-fab', $this->url['common'] . 'libs/fontawesome/css/brands' . JB()->scrips_prefix . '.css', array(), $this->fa_version );
				wp_register_style( 'jb-fa', $this->url['common'] . 'libs/fontawesome/css/v4-shims' . JB()->scrips_prefix . '.css', array(), $this->fa_version );
				wp_register_style( 'jb-font-awesome', $this->url['common'] . 'libs/fontawesome/css/fontawesome' . JB()->scrips_prefix . '.css', array( 'jb-fa', 'jb-far', 'jb-fas', 'jb-fab' ), $this->fa_version );
			}
		}

		/**
		 * Add FontAwesome style to dependencies if it's not disabled
		 *
		 * @param array $styles
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function extends_styles( $styles ) {
			if ( JB()->options()->get( 'disable-fa-styles' ) ) {
				return $styles;
			}

			$styles[] = 'jb-font-awesome';
			return $styles;
		}
	}
}
