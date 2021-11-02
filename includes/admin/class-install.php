<?php namespace jb\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\admin\Install' ) ) {


	/**
	 * Class Install
	 * @package jb\admin
	 */
	class Install {


		/**
		 * @var bool
		 */
		public $install_process = false;


		/**
		 * Install constructor.
		 */
		public function __construct() {
		}


		/**
		 * Plugin Activation
		 *
		 * @since 1.0
		 */
		public function activation() {
			$this->install_process = true;

			$this->single_site_activation();
			if ( is_multisite() ) {
				update_network_option( get_current_network_id(), 'jb_maybe_network_wide_activation', 1 );
			}

			$this->install_process = false;
		}


		/**
		 * Check if plugin is network activated make the first installation on all blogs
		 *
		 * @since 1.0
		 */
		public function maybe_network_activation() {
			$maybe_activation = get_network_option( get_current_network_id(), 'jb_maybe_network_wide_activation' );

			if ( $maybe_activation ) {

				delete_network_option( get_current_network_id(), 'jb_maybe_network_wide_activation' );

				if ( is_plugin_active_for_network( JB_PLUGIN ) ) {
					// get all blogs
					$blogs = get_sites();
					if ( ! empty( $blogs ) ) {
						foreach ( $blogs as $blog ) {
							switch_to_blog( $blog->blog_id );
							//make activation script for each sites blog
							$this->single_site_activation();
							restore_current_blog();
						}
					}
				}
			}
		}


		/**
		 * Single site plugin activation handler
		 *
		 * @since 1.0
		 */
		public function single_site_activation() {
			$version = JB()->options()->get( 'version' );
			if ( ! $version ) {
				//set first install date and set current version as last upgrade version
				JB()->options()->update( 'last_version_upgrade', JB_VERSION );
				JB()->options()->add( 'first_activation_date', time() );
			}

			if ( JB_VERSION !== $version ) {
				// update current version on first install or activation another version
				JB()->options()->update( 'version', JB_VERSION );
			}

			//set default settings
			$this->set_defaults( JB()->config()->get( 'defaults' ) );
			//create custom roles + upgrade capabilities
			$this->create_roles();

			if ( ! $version ) {
				// create default job types on the first install
				$this->create_job_types();
			}

			JB()->common()->rewrite()->reset_rules();
		}


		/**
		 * Set default JB settings
		 *
		 * @param array $defaults
		 *
		 * @since 1.0
		 */
		public function set_defaults( $defaults ) {
			if ( ! empty( $defaults ) ) {
				foreach ( $defaults as $key => $value ) {
					add_option( JB()->options()->get_key( $key ), $value );
				}
			}
		}


		/**
		 * Parse user capabilities and set the proper capabilities for roles
		 *
		 * @since 1.0
		 */
		public function create_roles() {
			global $wp_roles;

			if ( ! class_exists( '\WP_Roles' ) ) {
				return;
			}

			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- only if ! isset
			}

			$all_caps         = JB()->config()->get( 'all_caps' );
			$custom_roles     = JB()->config()->get( 'custom_roles' );
			$capabilities_map = JB()->config()->get( 'capabilities_map' );

			foreach ( $custom_roles as $role_id => $role_title ) {
				$wp_roles->remove_role( $role_id );

				if ( empty( $capabilities_map[ $role_id ] ) ) {
					$capabilities_map[ $role_id ] = array();
				}

				add_role( $role_id, $role_title, $capabilities_map[ $role_id ] );
			}

			foreach ( $capabilities_map as $role_id => $caps ) {
				foreach ( array_diff( $caps, $all_caps ) as $cap ) {
					$wp_roles->remove_cap( $role_id, $cap );
				}

				foreach ( $caps as $cap ) {
					$wp_roles->add_cap( $role_id, $cap );
				}
			}
		}


		/**
		 * Create pre-defined Job Types
		 *
		 * @since 1.0
		 */
		public function create_job_types() {
			// create post types here because on install there aren't registered CPT and terms
			JB()->common()->cpt()->create_post_types();

			$types = array(
				'full-time'  => array(
					'title'   => __( 'Full-time', 'jobboardwp' ),
					'color'   => '#0e6245',
					'bgcolor' => '#cbf4c9',
				),
				'part-time'  => array(
					'title'   => __( 'Part-time', 'jobboardwp' ),
					'color'   => '#3d4eac',
					'bgcolor' => '#d6ecff',
				),
				'internship' => array(
					'title'   => __( 'Internship', 'jobboardwp' ),
					'color'   => '#a3052a',
					'bgcolor' => '#fedce4',
				),
				'freelance'  => array(
					'title'   => __( 'Freelance', 'jobboardwp' ),
					'color'   => '#983705',
					'bgcolor' => '#f8e5b9',
				),
				'temporary'  => array(
					'title'   => __( 'Temporary', 'jobboardwp' ),
					'color'   => '#5c3eb7',
					'bgcolor' => '#e0d4ff',
				),
				'graduate'   => array(
					'title'   => __( 'Graduate', 'jobboardwp' ),
					'color'   => '#8c2a84',
					'bgcolor' => '#ffd7fc',
				),
				'volunteer'  => array(
					'title'   => __( 'Volunteer', 'jobboardwp' ),
					'color'   => '#4f566b',
					'bgcolor' => '#e3e8ee',
				),
			);

			foreach ( $types as $key => $type ) {
				$term = wp_insert_term(
					$type['title'],
					'jb-job-type',
					array(
						'description' => '',
						'parent'      => 0,
						'slug'        => $key,
					)
				);

				if ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) {
					update_term_meta( $term['term_id'], 'jb-color', $type['color'] );
					update_term_meta( $term['term_id'], 'jb-background', $type['bgcolor'] );
				}
			}
		}


		/**
		 * Install Core Pages
		 *
		 * @since 1.0
		 */
		public function core_pages() {
			foreach ( JB()->config()->get( 'core_pages' ) as $slug => $array ) {

				$page_id = JB()->options()->get( $slug . '_page' );
				if ( ! empty( $page_id ) ) {
					$page = get_post( $page_id );

					if ( isset( $page->ID ) ) {
						continue;
					}
				}

				//If page does not exist - create it
				$user_page = array(
					'post_title'     => $array['title'],
					'post_content'   => ! empty( $array['content'] ) ? $array['content'] : '',
					'post_name'      => $slug,
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'post_author'    => get_current_user_id(),
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $user_page );
				if ( empty( $post_id ) || is_wp_error( $post_id ) ) {
					continue;
				}

				JB()->options()->update( $slug . '_page', $post_id );
			}
		}

	}
}
