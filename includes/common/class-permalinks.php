<?php namespace jb\common;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


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
		public function __construct() {
			add_action( 'wp_login_failed', array( &$this, 'login_failed' ) );
			add_filter( 'authenticate', array( &$this, 'verify_username_password' ), 1, 3 );
		}


		/**
		 * Verifies username and password. Redirects visitor
		 * to the login page with login empty status if
		 * eather username or password is empty.
		 *
		 * @param mixed $user
		 * @param string $username
		 * @param string $password
		 *
		 * @return \WP_Error
		 *
		 * @since 1.0
		 */
		public function verify_username_password( $user, $username, $password ) {
			//use WP native function for fill $_SERVER variables by correct values
			wp_fix_server_vars();

			if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
				$postid = url_to_postid( $_SERVER['HTTP_REFERER'] );

				if ( ! empty( $postid ) && $postid === $this->get_preset_page_id( 'job-post' ) ) {
					if ( null === $user && ( '' === $username || '' === $password ) ) {
						return new \WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>: Invalid username, email address or incorrect password.' ) );
					}
				}
			}

			return $user;
		}


		/**
		 * Redirects visitor to the login page with login
		 * failed status.
		 *
		 * @return void
		 *
		 * @since 1.0
		 */
		public function login_failed() {
			//use WP native function for fill $_SERVER variables by correct values
			wp_fix_server_vars();

			if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
				$postid = url_to_postid( $_SERVER['HTTP_REFERER'] );

				if ( ! empty( $postid ) && $postid === $this->get_preset_page_id( 'job-post' ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification
					if ( ! empty( $_GET['redirect_to'] ) && esc_url_raw( $_GET['redirect_to'] ) === $_SERVER['HTTP_REFERER'] ) {
						return;
					}

					$logout_link = add_query_arg( array( 'login' => 'failed' ), $this->get_preset_page_link( 'job-post' ) );
					wp_safe_redirect( $logout_link );
					exit;
				}
			}
		}


		/**
		 * Get page slug
		 *
		 * @param string $key Pre-set page key
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_slug( $key ) {
			$preset_page_id = $this->get_preset_page_id( $key );

			$slug = '';
			if ( $preset_page_id ) {
				$preset_page = get_post( $preset_page_id );
				if ( ! empty( $preset_page ) && ! is_wp_error( $preset_page ) ) {
					$slug = $preset_page->post_name;
				}
			}

			return $slug;
		}


		/**
		 * Get preset page ID
		 *
		 * @param string $key
		 *
		 * @return int
		 *
		 * @since 1.0
		 */
		public function get_preset_page_id( $key ) {
			$page_id = JB()->options()->get( $key . '_page' );
			return (int) $page_id;
		}


		/**
		 * Get preset page link
		 *
		 * @param string $key
		 *
		 * @return false|string
		 *
		 * @since 1.0
		 */
		public function get_preset_page_link( $key ) {
			$page_id = $this->get_preset_page_id( $key );
			return get_permalink( $page_id );
		}


		/**
		 * Are JB pages installed
		 *
		 * @return bool
		 *
		 * @since 1.0
		 */
		public function are_pages_installed() {
			$installed = true;

			$pages      = array();
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
