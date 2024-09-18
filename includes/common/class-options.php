<?php
namespace jb\common;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		public $options = array();

		/**
		 * Returns options key
		 *
		 * @param string $option
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_key( $option ) {
			/**
			 * Filters the option name with which it is saved in wp_options table.
			 *
			 * @since 1.0
			 * @hook jb_options_key
			 *
			 * @param {string} $option_name Option name. By default, it's a simplified option key with 'jb_' prefix.
			 * @param {string} $option      Simplified option key without prefix.
			 *
			 * @return {string} Option name.
			 */
			return apply_filters( 'jb_options_key', "jb_{$option}", $option );
		}

		/**
		 * Get JB option value
		 *
		 * @param string $option_id
		 * @param mixed  $default_value
		 *
		 * @return mixed
		 *
		 * @since 1.0
		 */
		public function get( $option_id, $default_value = false ) {
			if ( isset( $this->options[ $option_id ] ) ) {
				$value = $this->options[ $option_id ];
			} else {
				$value = get_option( $this->get_key( $option_id ), $default_value );
			}

			/**
			 * Filters the option value.
			 *
			 * @since 1.0
			 * @hook jb_options_get_{$option_id}
			 *
			 * @param {mixed}  $value         Option name. By default, it's a simplified option key with 'jb_' prefix.
			 * @param {string} $option_id     Simplified option key without prefix.
			 * @param {string} $default_value Default option value passed to a function.
			 *
			 * @return {mixed} Option value.
			 */
			return apply_filters( "jb_options_get_{$option_id}", $value, $option_id, $default_value );
		}

		/**
		 * Add JB option value
		 *
		 * @param string $option_id
		 * @param mixed $value
		 *
		 * @since 1.0
		 */
		public function add( $option_id, $value ) {
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
		public function update( $option_id, $value ) {
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
		public function delete( $option_id ) {
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
		public function get_default( $option_id ) {
			$settings_defaults = JB()->config()->get( 'defaults' );
			if ( ! isset( $settings_defaults[ $option_id ] ) ) {
				return false;
			}

			return $settings_defaults[ $option_id ];
		}

		/**
		 * Get predefined page option key
		 *
		 * @param string $slug
		 *
		 * @return string
		 */
		public function get_predefined_page_option_key( $slug ) {
			/**
			 * Filters the predefined page simplified option key.
			 *
			 * @since 1.0
			 * @hook jb_predefined_page_option_key
			 *
			 * @param {string} $key  Simplified option key. By default, it's a page slug with '_page' suffix.
			 * @param {string} $slug The predefined page slug. E.g. 'jon-dashboard'.
			 *
			 * @return {string} Simplified option key.
			 */
			return apply_filters( 'jb_predefined_page_option_key', "{$slug}_page" );
		}
	}
}
