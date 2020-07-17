<?php namespace jb\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


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
		var $slug = 'jobboardwp';


		/**
		 * Menu constructor.
		 */
		function __construct() {
			add_action( 'admin_menu',  [ &$this, 'menu' ] );
			add_filter( 'submenu_file', [ &$this, 'remove_dashboard' ] );
			add_filter( 'admin_body_class', [ &$this, 'selected_menu' ], 10, 1 );

			add_action( 'init', [ &$this, 'wrong_settings' ], 9999 );
			add_action( 'admin_head', [ &$this, 'add_pending_count' ] );
		}



		/**
		 * Change label for admin menu item to show number of Job Listing items pending approval.
		 *
		 * @since 1.0
		 */
		public function add_pending_count() {
			global $menu;

			$pending_jobs = get_posts( [
				'post_type'     => 'jb-job',
				'post_status'   => 'pending',
				'numberposts'   => '-1',
				'fields'        => 'ids',
			] );

			if ( is_wp_error( $pending_jobs ) ) {
				return;
			}

			$pending_jobs = count( $pending_jobs );

			// No need to go further if no pending jobs, menu is not set, or is not an array.
			if ( empty( $pending_jobs ) || empty( $menu ) || ! is_array( $menu ) ) {
				return;
			}

			foreach ( $menu as $key => $menu_item ) {
				if ( $menu_item[2] === 'jobboardwp' ) {
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
		function menu() {
			add_menu_page( __( 'Job Board', 'jobboardwp' ), __( 'Job Board', 'jobboardwp' ), 'manage_options', $this->slug, '', 'dashicons-businessman', 40 );
			add_submenu_page( $this->slug, __( 'Dashboard', 'jobboardwp' ), __( 'Dashboard', 'jobboardwp' ), 'manage_options', $this->slug, '' );

			add_submenu_page( $this->slug, __( 'Jobs', 'jobboardwp' ), __( 'Jobs', 'jobboardwp' ), 'read_private_jb-jobs', 'edit.php?post_type=jb-job' );
			add_submenu_page( $this->slug, __( 'Add New', 'jobboardwp' ), __( 'Add New', 'jobboardwp' ), 'create_jb-jobs', 'post-new.php?post_type=jb-job' );
			add_submenu_page( $this->slug, __( 'Job Types', 'jobboardwp' ), __( 'Job Types', 'jobboardwp' ), 'manage_jb-job-types', 'edit-tags.php?taxonomy=jb-job-type&post_type=jb-job' );

			if ( JB()->options()->get( 'job-categories' ) ) {
				add_submenu_page( $this->slug, __( 'Job Categories', 'jobboardwp' ), __( 'Job Categories', 'jobboardwp' ), 'manage_jb-job-categories', 'edit-tags.php?taxonomy=jb-job-category&post_type=jb-job' );
			}

			add_submenu_page( $this->slug, __( 'Settings', 'jobboardwp' ), __( 'Settings', 'jobboardwp' ), 'manage_options', 'jb-settings', [ &$this, 'settings' ] );
		}



		/**
		 * Hide first submenu and replace to Jobs
		 * @param string $submenu_file
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		function remove_dashboard( $submenu_file ) {
			global $plugin_page;

			$hidden_submenus = [
				$this->slug,
			];

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
		 */
		function selected_menu( $classes ) {
			global $submenu, $pagenow;

			if ( isset( $submenu[ $this->slug ] ) ) {
				if ( isset( $_GET['post_type'] ) && 'jb-job' == $_GET['post_type'] ) {
					add_filter( 'parent_file', [ &$this, 'change_parent_file' ], 200 );
				}

				if ( 'post.php' == $pagenow && ( isset( $_GET['post'] ) && 'jb-job' == get_post_type( $_GET['post'] ) ) ) {
					add_filter( 'parent_file', [ &$this, 'change_parent_file' ], 200 );
				}

				add_filter( 'submenu_file', [ &$this, 'change_submenu_file' ], 200, 2 );
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
		function change_parent_file() {
			global $pagenow;

			if ( 'edit-tags.php' !== $pagenow && 'term.php' !== $pagenow && 'post-new.php' !== $pagenow ) {
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
		function change_submenu_file( $submenu_file, $parent_file ) {
			global $pagenow;

			if ( 'edit-tags.php' == $pagenow || 'term.php' == $pagenow || 'post-new.php' == $pagenow ) {
				if ( $parent_file == $this->slug ) {
					if ( isset( $_GET['post_type'] ) && 'jb-job' == $_GET['post_type'] &&
					     isset( $_GET['taxonomy'] ) && ( 'jb-job-type' == $_GET['taxonomy'] || 'jb-job-category' == $_GET['taxonomy'] ) ) {
						$submenu_file  = 'edit-tags.php?taxonomy=' . $_GET['taxonomy'] . '&post_type=' . $_GET['post_type'];
					} elseif ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'jb-job' == $_GET['post_type'] ) {
						$submenu_file  = 'edit.php?post_type=' . $_GET['post_type'];
					}

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
		function wrong_settings() {
			global $pagenow;

			if ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'jb-settings' == $_GET['page'] ) {
				$current_tab = empty( $_GET['tab'] ) ? '' : sanitize_key( urldecode( $_GET['tab'] ) );
				$current_subtab = empty( $_GET['section'] ) ? '' : sanitize_key( urldecode( $_GET['section'] ) );

				$settings_struct = JB()->admin()->settings()->get_settings( $current_tab, $current_subtab );
				$custom_section = JB()->admin()->settings()->section_is_custom( $current_tab, $current_subtab );

				if ( ! $custom_section && empty( $settings_struct ) ) {
					wp_redirect( add_query_arg( [ 'page' => 'jb-settings' ], admin_url( 'admin.php' ) ) );
					exit;
				} else {
					//remove extra query arg for Email list table
					$email_key = empty( $_GET['email'] ) ? '' : urldecode( $_GET['email'] );
					$email_notifications = JB()->config()->get( 'email_notifications' );

					if ( empty( $email_key ) || empty( $email_notifications[ $email_key ] ) ) {
						if ( ! empty( $_GET['_wp_http_referer'] ) ) {
							wp_redirect( remove_query_arg( [ '_wp_http_referer', '_wpnonce' ], wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
						}
					}
				}
			}
		}


		/**
		 * Settings page callback
		 *
		 * @since 1.0
		 */
		function settings() {
			include_once JB()->admin()->templates_path . 'settings' . DIRECTORY_SEPARATOR . 'settings.php';
		}
	}
}