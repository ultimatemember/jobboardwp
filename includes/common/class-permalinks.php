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

				if ( ! empty( $postid ) && $postid === $this->get_predefined_page_id( 'job-post' ) ) {
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

				if ( ! empty( $postid ) && $postid === $this->get_predefined_page_id( 'job-post' ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification
					if ( ! empty( $_GET['redirect_to'] ) && esc_url_raw( $_GET['redirect_to'] ) === $_SERVER['HTTP_REFERER'] ) {
						return;
					}

					$logout_link = add_query_arg( array( 'login' => 'failed' ), $this->get_predefined_page_link( 'job-post' ) );
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
			$preset_page_id = $this->get_predefined_page_id( $key );

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
		 * @param string $slug
		 *
		 * @return bool
		 */
		public function predefined_page_slug_exists( $slug ) {
			$predefined_pages = JB()->config()->get( 'predefined_pages' );
			return array_key_exists( $slug, $predefined_pages );
		}


		/**
		 * Get predefined page ID
		 *
		 * @param string $slug
		 *
		 * @return false|int
		 *
		 * @since 1.1.1
		 */
		public function get_predefined_page_id( $slug ) {
			if ( ! $this->predefined_page_slug_exists( $slug ) ) {
				return false;
			}

			$option_key = JB()->options()->get_predefined_page_option_key( $slug );

			/**
			 * Filters the predefined page ID.
			 *
			 * Note: See all predefined pages slugs in JB()->config()->get( 'predefined_pages' );
			 * https://github.com/ultimatemember/jobboardwp/blob/master/includes/class-config.php#L248
			 *
			 * Note: JobBoardWP internally uses this hook for getting integrated with multilingual plugins that make duplicates of the pages for translations.
			 *
			 * @since 1.1.0
			 * @hook jb_get_predefined_page_id
			 *
			 * @param {string} $page_id The predefined page ID. The value obtained from options.
			 * @param {string} $slug    The predefined page slug. E.g. 'job-dashboard'.
			 *
			 * @return {string} The predefined page ID.
			 */
			$page_id = apply_filters( 'jb_get_predefined_page_id', JB()->options()->get( $option_key ), $slug );

			$page_exists = get_post( $page_id );
			if ( ! $page_exists ) {
				return false;
			}

			return (int) $page_id;
		}


		/**
		 *
		 * @param string $slug
		 * @param null|int|\WP_Post $post
		 *
		 * @return bool
		 */
		public function is_predefined_page( $slug, $post = null ) {
			// handle $post inside, just we need make $post as \WP_Post. Otherwise something is wrong and return false
			if ( ! $post ) {
				global $post;

				if ( empty( $post ) ) {
					return false;
				}
			} else {
				if ( is_numeric( $post ) ) {
					$post = get_post( $post ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- internal variable

					if ( empty( $post ) ) {
						return false;
					}
				}
			}

			if ( empty( $post->ID ) ) {
				return false;
			}

			$predefined_page_id = $this->get_predefined_page_id( $slug );
			$condition          = $post->ID === $this->get_predefined_page_id( $slug );

			return apply_filters( 'jb_is_predefined_page', $condition, $post, $predefined_page_id, $slug );
		}


		/**
		 * Get predefined page URL
		 *
		 * @param string $slug
		 *
		 * @return false|string
		 *
		 * @since 1.1.1
		 */
		public function get_predefined_page_link( $slug ) {
			$url     = false;
			$page_id = $this->get_predefined_page_id( $slug );

			if ( ! empty( $page_id ) ) {
				$url = get_permalink( $page_id );
			}

			return $url;
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

			$pages            = array();
			$predefined_pages = array_keys( JB()->config()->get( 'predefined_pages' ) );
			if ( ! empty( $predefined_pages ) ) {
				foreach ( $predefined_pages as $slug ) {
					$option = JB()->options()->get( JB()->options()->get_predefined_page_option_key( $slug ) );
					if ( ! empty( $option ) ) {
						$pages[ $slug ] = $option;
					}
				}
			}

			if ( empty( $pages ) || count( $pages ) !== count( $predefined_pages ) ) {
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


		/**
		 * Get preset page ID
		 *
		 * @deprecated 1.1.1
		 *
		 * @param string $key
		 *
		 * @return int
		 *
		 * @since 1.0
		 */
		public function get_preset_page_id( $key ) {
			_deprecated_function( __METHOD__, '1.1.1', 'JB()->common()->permalinks()->get_predefined_page_id( $slug )' );
			return $this->get_predefined_page_id( $key );
		}


		/**
		 * Get preset page link
		 *
		 * @deprecated 1.1.1
		 *
		 * @param string $key
		 *
		 * @return false|string
		 *
		 * @since 1.0
		 */
		public function get_preset_page_link( $key ) {
			_deprecated_function( __METHOD__, '1.1.1', 'JB()->common()->permalinks()->get_predefined_page_link( $slug )' );
			return $this->get_predefined_page_link( $key );
		}
	}
}
