<?php namespace jb\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\common\Permalinks' ) ) {


	/**
	 * Class Permalinks
	 *
	 * @package jb\common
	 */
	class Permalinks {


		/**
		 * Permalinks constructor.
		 */
		function __construct() {
		}


		/**
		 * @param string $key Pre-set page key
		 *
		 * @return string
		 */
		function get_slug( $key ) {
			$preset_page_id = $this->get_preset_page_id( $key );

			$slug = '';
			if ( $preset_page_id ) {
				$preset_page = get_post( $preset_page_id );
				if ( ! empty( $preset_page ) && ! is_wp_error( $preset_page ) ) {
					$slug = $preset_page->post_name . '/';
				}
			}

			return $slug;
		}


		/**
		 * @param string $key
		 *
		 * @return int
		 */
		function get_preset_page_id( $key ) {
			$page_id = JB()->options()->get( $key . '_page' );
			return (int) $page_id;
		}


		/**
		 * @param string $key
		 *
		 * @return false|string
		 */
		function get_preset_page_link( $key ) {
			$page_id = $this->get_preset_page_id( $key );
			return get_permalink( $page_id );
		}


		/**
		 * Are JB pages installed
		 *
		 * @return bool
		 */
		function are_pages_installed() {
			$installed = true;

			$pages = [];
			$core_pages = array_keys( JB()->config()->get( 'core_pages' ) );
			if ( ! empty( $core_pages ) ) {
				foreach ( $core_pages as $page_key ) {
					$option = JB()->options()->get( $page_key . '_page' );
					if ( ! empty( $option ) ) {
						$pages[ $page_key ] = $option;
					}
				}
			}

			if ( empty( $pages ) ) {
				$installed = false;
			} else {
				foreach ( $pages as $page_id ) {
					$page = get_post( $page_id );

					if ( ! isset( $page->ID ) ) {
						$installed = false;
						break;
					}
				}
			}

			return $installed;
		}

	}
}