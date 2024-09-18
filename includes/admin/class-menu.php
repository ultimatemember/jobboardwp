<?php
namespace jb\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\admin\Menu' ) ) {

	/**
	 * Class Menu
	 *
	 * @package jb\admin
	 */
	class Menu {

		/**
		 * @var string Main Menu slug
		 *
		 * @since 1.0
		 */
		public $slug = 'jobboardwp';

		/**
		 * Menu constructor.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( &$this, 'menu' ) );
			add_filter( 'submenu_file', array( &$this, 'remove_dashboard' ) );
			add_filter( 'admin_body_class', array( &$this, 'selected_menu' ) );

			add_action( 'init', array( &$this, 'wrong_settings' ), 9999 );
			add_action( 'admin_head', array( &$this, 'add_pending_count' ) );
		}

		/**
		 * Change label for admin menu item to show number of Job Listing items pending approval.
		 *
		 * @since 1.0
		 */
		public function add_pending_count() {
			global $menu;

			$pending_jobs = get_posts(
				array(
					'post_type'   => 'jb-job',
					'post_status' => 'pending',
					'numberposts' => -1,
					'fields'      => 'ids',
				)
			);

			if ( is_wp_error( $pending_jobs ) ) {
				return;
			}

			$pending_jobs = count( $pending_jobs );

			// No need to go further if no pending jobs, menu is not set, or is not an array.
			if ( empty( $pending_jobs ) || empty( $menu ) || ! is_array( $menu ) ) {
				return;
			}

			foreach ( $menu as $key => $menu_item ) {
				if ( 'jobboardwp' === $menu_item[2] ) {
					// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- is needed for changing the pending count
					$menu[ $key ][0] .= " <span class='awaiting-mod update-plugins count-" . esc_attr( $pending_jobs ) . "'><span class='jb-pending-count'>" . absint( number_format_i18n( $pending_jobs ) ) . '</span></span>';
					break;
				}
			}
		}

		/**
		 * Add admin menus
		 *
		 * @since 1.0
		 */
		public function menu() {
			$parent_capability = 'manage_options';
			if ( ! current_user_can( 'manage_options' ) ) {
				if ( current_user_can( 'read_private_jb-jobs' ) ) {
					$parent_capability = 'read_private_jb-jobs';
				} elseif ( current_user_can( 'create_jb-jobs' ) ) {
					$parent_capability = 'create_jb-jobs';
				} elseif ( current_user_can( 'manage_jb-job-types' ) ) {
					$parent_capability = 'manage_jb-job-types';
				}
			}
			add_menu_page( __( 'Job Board', 'jobboardwp' ), __( 'Job Board', 'jobboardwp' ), $parent_capability, $this->slug, '', 'dashicons-businessman', 40 );
			add_submenu_page( $this->slug, __( 'Dashboard', 'jobboardwp' ), __( 'Dashboard', 'jobboardwp' ), 'manage_options', $this->slug );

			add_submenu_page( $this->slug, __( 'Jobs', 'jobboardwp' ), __( 'Jobs', 'jobboardwp' ), 'read_private_jb-jobs', 'edit.php?post_type=jb-job' );
			add_submenu_page( $this->slug, __( 'Add New', 'jobboardwp' ), __( 'Add New', 'jobboardwp' ), 'create_jb-jobs', 'post-new.php?post_type=jb-job' );
			add_submenu_page( $this->slug, __( 'Job Types', 'jobboardwp' ), __( 'Job Types', 'jobboardwp' ), 'manage_jb-job-types', 'edit-tags.php?taxonomy=jb-job-type&post_type=jb-job' );

			if ( JB()->options()->get( 'job-categories' ) ) {
				add_submenu_page( $this->slug, __( 'Job Categories', 'jobboardwp' ), __( 'Job Categories', 'jobboardwp' ), 'manage_jb-job-categories', 'edit-tags.php?taxonomy=jb-job-category&post_type=jb-job' );
			}

			add_submenu_page( $this->slug, __( 'Settings', 'jobboardwp' ), __( 'Settings', 'jobboardwp' ), 'manage_options', 'jb-settings', array( &$this, 'settings' ) );
		}

		/**
		 * Hide first submenu and replace to Jobs
		 * @param string $submenu_file
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function remove_dashboard( $submenu_file ) {
			global $plugin_page;

			$hidden_submenus = array(
				$this->slug,
			);

			// Select another submenu item to highlight (optional).
			if ( $plugin_page && isset( $hidden_submenus[ $plugin_page ] ) ) {
				$submenu_file = 'edit.php?post_type=jb-job';
			}

			// Hide the submenu.
			foreach ( $hidden_submenus as $submenu ) {
				remove_submenu_page( $this->slug, $submenu );
			}

			return $submenu_file;
		}

		/**
		 * Made selected Job Board menu on Add/Edit CPT and Term Taxonomies
		 *
		 * @since 1.0
		 *
		 * @param array $classes
		 * @return array
		 */
		public function selected_menu( $classes ) {
			global $submenu, $pagenow;

			if ( isset( $submenu[ $this->slug ] ) ) {
				if ( isset( $_GET['post_type'] ) && 'jb-job' === sanitize_key( wp_unslash( $_GET['post_type'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					add_filter( 'parent_file', array( &$this, 'change_parent_file' ), 200 );
				}

				// phpcs:ignore WordPress.Security.NonceVerification
				if ( 'post.php' === $pagenow && ( isset( $_GET['post'] ) && 'jb-job' === get_post_type( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ) ) ) {
					add_filter( 'parent_file', array( &$this, 'change_parent_file' ), 200 );
				}

				add_filter( 'submenu_file', array( &$this, 'change_submenu_file' ), 200, 2 );
			}

			return $classes;
		}

		/**
		 * Return admin submenu variable for display pages
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function change_parent_file() {
			global $pagenow;

			if ( 'edit-tags.php' !== $pagenow && 'term.php' !== $pagenow && 'post-new.php' !== $pagenow ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- is needed for displaying active JobBoardWP menu
				$pagenow = 'admin.php';
			}

			return $this->slug;
		}

		/**
		 * Return admin submenu variable for display pages
		 *
		 * @param string $submenu_file
		 * @param string $parent_file
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function change_submenu_file( $submenu_file, $parent_file ) {
			global $pagenow;

			if ( 'edit-tags.php' === $pagenow || 'term.php' === $pagenow || 'post-new.php' === $pagenow ) {
				if ( $parent_file === $this->slug ) {
					$all_taxonomies = JB()->common()->cpt()->get_taxonomies();
					$all_taxonomies = array_keys( $all_taxonomies );

					// phpcs:disable WordPress.Security.NonceVerification
					if ( isset( $_GET['post_type'], $_GET['taxonomy'] ) && 'jb-job' === sanitize_key( $_GET['post_type'] ) && in_array( sanitize_key( $_GET['taxonomy'] ), $all_taxonomies, true ) ) {
						$submenu_file = 'edit-tags.php?taxonomy=' . sanitize_key( $_GET['taxonomy'] ) . '&post_type=' . sanitize_key( $_GET['post_type'] );
					} elseif ( 'post-new.php' === $pagenow && isset( $_GET['post_type'] ) && 'jb-job' === sanitize_key( $_GET['post_type'] ) ) {
						$submenu_file = 'edit.php?post_type=' . sanitize_key( $_GET['post_type'] );
					}
					// phpcs:enable WordPress.Security.NonceVerification

					// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- is needed for displaying active JobBoardWP menu
					$pagenow = 'admin.php';
				}
			}

			return $submenu_file;
		}

		/**
		 * Handle redirect if wrong settings tab is open
		 *
		 * @since 1.0
		 */
		public function wrong_settings() {
			global $pagenow;

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'jb-settings' === sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
				$current_tab    = empty( $_GET['tab'] ) ? '' : sanitize_key( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				$current_subtab = empty( $_GET['section'] ) ? '' : sanitize_key( wp_unslash( $_GET['section'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

				$settings_struct = JB()->admin()->settings()->get_settings( $current_tab, $current_subtab );
				$custom_section  = JB()->admin()->settings()->section_is_custom( $current_tab, $current_subtab );

				if ( ! $custom_section && empty( $settings_struct ) ) {
					wp_safe_redirect( add_query_arg( array( 'page' => 'jb-settings' ), admin_url( 'admin.php' ) ) );
					exit;
				}

				//remove extra query arg for Email list table
				$email_key           = empty( $_GET['email'] ) ? '' : sanitize_key( wp_unslash( $_GET['email'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				$email_notifications = JB()->config()->get( 'email_notifications' );

				if ( empty( $email_key ) || empty( $email_notifications[ $email_key ] ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification
					if ( ! empty( $_GET['_wp_http_referer'] ) && ! empty( $_SERVER['REQUEST_URI'] ) && 'email' === $current_tab ) {
						wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- REQUEST_URI ok
						exit;
					}
				}
			}
		}

		/**
		 * Settings page callback
		 *
		 * @since 1.0
		 */
		public function settings() {
			include_once JB()->admin()->templates_path . 'settings' . DIRECTORY_SEPARATOR . 'settings.php';
		}
	}
}
