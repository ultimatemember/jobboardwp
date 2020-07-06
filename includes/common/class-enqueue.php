<?php namespace jb\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\common\Enqueue' ) ) {


	/**
	 * Class Enqueue
	 *
	 * @package jb\common
	 */
	class Enqueue {


		/**
		 * @var
		 */
		var $js_url = [];


		/**
		 * @var
		 */
		var $css_url = [];


		/**
		 * @var array
		 */
		var $url = [];


		/**
		 * @var string
		 */
		var $fa_version = '5.13.0';


		/**
		 * Enqueue constructor.
		 */
		function __construct() {
			$this->url['common'] = jb_url . 'assets/common/';
			$this->js_url['common'] = jb_url . 'assets/common/js/';
			$this->css_url['common'] = jb_url . 'assets/common/css/';

			add_action( 'plugins_loaded', [ $this, 'init_variables' ], 10 );
			add_action( 'admin_enqueue_scripts', [ &$this, 'common_libs' ], 9 );
			add_action( 'wp_enqueue_scripts', [ &$this, 'common_libs' ], 9 );

			add_filter( 'jb_frontend_common_styles_deps', [ &$this, 'extends_styles' ], 10, 1 );
		}


		/**
		 * Init variables for enqueue scripts
		 */
		function init_variables() {
			JB()->scrips_prefix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		}


		/**
		 *
		 */
		function common_libs() {
			global $wp_scripts;

			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
			wp_register_style( 'jquery-ui', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui' . JB()->scrips_prefix . '.css', [], $jquery_version );

			if ( ! JB()->options()->get( 'disable-fa-styles' ) ) {
				wp_register_style( 'jb-far', $this->url['common'] . 'libs/fontawesome/css/regular' . JB()->scrips_prefix . '.css', [], $this->fa_version );
				wp_register_style( 'jb-fas', $this->url['common'] . 'libs/fontawesome/css/solid' . JB()->scrips_prefix . '.css', [], $this->fa_version );
				wp_register_style( 'jb-fab', $this->url['common'] . 'libs/fontawesome/css/brands' . JB()->scrips_prefix . '.css', [], $this->fa_version );
				wp_register_style( 'jb-fa', $this->url['common'] . 'libs/fontawesome/css/v4-shims' . JB()->scrips_prefix . '.css', [], $this->fa_version );
				wp_register_style( 'jb-font-awesome', $this->url['common'] . 'libs/fontawesome/css/fontawesome' . JB()->scrips_prefix . '.css', [ 'jb-fa', 'jb-far', 'jb-fas', 'jb-fab' ], $this->fa_version );
			}
		}


		/**
		 * @param array $styles
		 *
		 * @return array
		 */
		function extends_styles( $styles ) {
			if ( JB()->options()->get( 'disable-fa-styles' ) ) {
				return $styles;
			}

			$styles[] = 'jb-font-awesome';
			return $styles;
		}

	}
}