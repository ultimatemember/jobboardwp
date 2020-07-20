<?php namespace jb\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\common\Options' ) ) {


	/**
	 * Class Options
	 * @package jb\common
	 */
	class Options {


		/**
		 * @var array
		 *
		 * @since 1.0
		 */
		var $options = [];


		/**
		 * Options constructor.
		 */
		function __construct() {
		}


		/**
		 * Returns options key
		 *
		 * @param string $option
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		function get_key( $option ) {
			return apply_filters( 'jb_options_key', "jb_{$option}", $option );
		}


		/**
		 * Get JB option value
		 *
		 * @param string $option_id
		 * @param mixed $default
		 *
		 * @return mixed
		 *
		 * @since 1.0
		 */
		function get( $option_id, $default = false ) {
			if ( isset( $this->options[ $option_id ] ) ) {
				$value = $this->options[ $option_id ];
			} else {
				$value = get_option( $this->get_key( $option_id ), $default );
			}

			return apply_filters( "jb_options_get_{$option_id}", $value, $option_id, $default );
		}


		/**
		 * Add JB option value
		 *
		 * @param string $option_id
		 * @param mixed $value
		 *
		 * @since 1.0
		 */
		function add( $option_id, $value ) {
			if ( ! isset( $this->options[ $option_id ] ) ) {
				$this->options[ $option_id ] = $value;
			}
			add_option( $this->get_key( $option_id ), $value );
		}


		/**
		 * Update JB option value
		 *
		 * @param string $option_id
		 * @param mixed $value
		 *
		 * @since 1.0
		 */
		function update( $option_id, $value ) {
			$this->options[ $option_id ] = $value;
			update_option( $this->get_key( $option_id ), $value );
		}


		/**
		 * Delete JB option
		 *
		 * @param string $option_id
		 *
		 * @since 1.0
		 */
		function delete( $option_id ) {
			if ( isset( $this->options[ $option_id ] ) ) {
				unset( $this->options[ $option_id ] );
			}

			delete_option( $this->get_key( $option_id ) );
		}


		/**
		 * Get JB option default value
		 *
		 * @param string $option_id
		 * @return mixed
		 *
		 * @since 1.0
		 */
		function get_default( $option_id ) {
			$settings_defaults = JB()->config()->get( 'defaults' );
			if ( ! isset( $settings_defaults[ $option_id ] ) ) {
				return false;
			}

			return $settings_defaults[ $option_id ];
		}
	}
}