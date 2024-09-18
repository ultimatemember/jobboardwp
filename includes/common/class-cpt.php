<?php
namespace jb\common;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\common\CPT' ) ) {

	/**
	 * Class CPT
	 * @package jb\common
	 */
	class CPT {

		/**
		 * CPT constructor.
		 */
		public function __construct() {
			add_action( 'init', array( &$this, 'create_post_types' ), 1 );
			add_action( 'init', array( &$this, 'register_post_statuses' ), 2 );

			add_action( 'admin_bar_menu', array( &$this, 'toolbar_links' ), 999, 1 );
			add_action( 'admin_bar_menu', array( &$this, 'new_cpt_links' ), 999, 1 );

			add_filter( 'request', array( &$this, 'change_feed_request' ), 10, 1 );
		}


		/**
		 * Feed request changing based on post type
		 *
		 * @param array $qv
		 *
		 * @return array
		 */
		public function change_feed_request( $qv ) {
			// phpcs:ignore WordPress.Security.NonceVerification -- verified in request
			if ( isset( $qv['feed'] ) && isset( $_GET['post_type'] ) && 'jb_job' === sanitize_key( $_GET['post_type'] ) ) {
				$qv['post_type'] = 'jb-job';
			}

			return $qv;
		}


		/**
		 * Get all JB CPT
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function get() {
			$cpt = array(
				'jb-job' => array(
					'labels'              => array(
						'name'                  => __( 'Jobs', 'jobboardwp' ),
						'singular_name'         => __( 'Job', 'jobboardwp' ),
						'menu_name'             => _x( 'Jobs', 'Admin menu name', 'jobboardwp' ),
						'add_new'               => __( 'Add New Job', 'jobboardwp' ),
						'add_new_item'          => __( 'Add New Job', 'jobboardwp' ),
						'edit'                  => __( 'Edit', 'jobboardwp' ),
						'edit_item'             => __( 'Edit Job', 'jobboardwp' ),
						'new_item'              => __( 'New Job', 'jobboardwp' ),
						'view'                  => __( 'View Job', 'jobboardwp' ),
						'view_item'             => __( 'View Job', 'jobboardwp' ),
						'search_items'          => __( 'Search Jobs', 'jobboardwp' ),
						'not_found'             => __( 'No Jobs found', 'jobboardwp' ),
						'not_found_in_trash'    => __( 'No Jobs found in trash', 'jobboardwp' ),
						'parent'                => __( 'Parent Job', 'jobboardwp' ),
						'featured_image'        => __( 'Company logo', 'jobboardwp' ),
						'set_featured_image'    => __( 'Set company logo', 'jobboardwp' ),
						'remove_featured_image' => __( 'Remove Company logo', 'jobboardwp' ),
						'use_featured_image'    => __( 'Use as Company logo', 'jobboardwp' ),
					),
					'description'         => __( 'This is where you can add new jobs.', 'jobboardwp' ),
					'public'              => true,
					'show_ui'             => true,
					'capability_type'     => 'jb-job',
					'show_in_menu'        => false,
					'map_meta_cap'        => true,
					'capabilities'        => array( 'create_posts' => 'create_jb-jobs' ),
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'hierarchical'        => false,
					'rewrite'             => array(
						'slug'       => JB()->options()->get( 'job-slug' ),
						'with_front' => false,
						'feeds'      => true,
					),
					'query_var'           => true,
					'supports'            => array( 'title', 'editor', 'author', 'thumbnail' ),
					'has_archive'         => false,
					'show_in_nav_menus'   => false,
					'show_in_rest'        => true,
					'taxonomies'          => array( 'jb-job-type', 'jb-job-category' ),
				),
			);

			/**
			 * Filters the custom post types (CPT) list that will be created when JobBoardWP is active.
			 *
			 * @since 1.0
			 * @hook jb_cpt_list
			 *
			 * @param {array} $cpt CPT data.
			 *
			 * @return {array} CPT data.
			 */
			return apply_filters( 'jb_cpt_list', $cpt );
		}


		/**
		 * Get all CPT taxonomies
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function get_taxonomies() {
			$jobs_slug = JB()->common()->permalinks()->get_slug( 'jobs' );
			$type_slug = untrailingslashit( trailingslashit( $jobs_slug ) . JB()->options()->get( 'job-type-slug' ) );

			$taxonomies = array(
				'jb-job-type' => array(
					'post_types' => array( 'jb-job' ),
					'tax_args'   => array(
						'labels'             => array(
							'name'                       => __( 'Job Types', 'jobboardwp' ),
							'singular_name'              => __( 'Job Type', 'jobboardwp' ),
							'menu_name'                  => _x( 'Job Types', 'Admin menu name', 'jobboardwp' ),
							'search_items'               => __( 'Search Job Types', 'jobboardwp' ),
							'all_items'                  => __( 'All Job Types', 'jobboardwp' ),
							'edit_item'                  => __( 'Edit Job Type', 'jobboardwp' ),
							'update_item'                => __( 'Update Job Type', 'jobboardwp' ),
							'add_new_item'               => __( 'Add New Job Type', 'jobboardwp' ),
							'new_item_name'              => __( 'New Job Type Name', 'jobboardwp' ),
							'popular_items'              => __( 'Popular Job Types', 'jobboardwp' ),
							'separate_items_with_commas' => __( 'Separate Job Types with commas', 'jobboardwp' ),
							'add_or_remove_items'        => __( 'Add or remove Job Types', 'jobboardwp' ),
							'choose_from_most_used'      => __( 'Choose from the most used Job Types', 'jobboardwp' ),
							'not_found'                  => __( 'No Job Types found', 'jobboardwp' ),
							'no_terms'                   => __( 'No Job Types', 'jobboardwp' ),
							'parent_item'                => __( 'Parent Type', 'jobboardwp' ),
							'parent_item_colon'          => __( 'Parent Type:', 'jobboardwp' ),
						),
						'hierarchical'       => true,
						'label'              => __( 'Job Types', 'jobboardwp' ),
						'show_ui'            => true,
						'show_in_quick_edit' => false,
						'meta_box_cb'        => false,
						'show_in_menu'       => false,
						'query_var'          => true,
						'capabilities'       => array(
							'manage_terms' => 'manage_jb-job-types',
							'edit_terms'   => 'edit_jb-job-types',
							'delete_terms' => 'delete_jb-job-types',
							'assign_terms' => 'edit_jb-job-types',
						),
						'rewrite'            => array(
							'slug'       => $type_slug,
							'with_front' => true,
						),
						'show_in_rest'       => true,
					),
				),
			);

			if ( JB()->options()->get( 'job-categories' ) ) {
				$category_slug = untrailingslashit( trailingslashit( $jobs_slug ) . JB()->options()->get( 'job-category-slug' ) );

				$taxonomies['jb-job-category'] = array(
					'post_types' => array( 'jb-job' ),
					'tax_args'   => array(
						'labels'             => array(
							'name'                       => __( 'Job Categories', 'jobboardwp' ),
							'singular_name'              => __( 'Job Category', 'jobboardwp' ),
							'menu_name'                  => _x( 'Job Categories', 'Admin menu name', 'jobboardwp' ),
							'search_items'               => __( 'Search Job Categories', 'jobboardwp' ),
							'all_items'                  => __( 'All Job Categories', 'jobboardwp' ),
							'edit_item'                  => __( 'Edit Job Category', 'jobboardwp' ),
							'update_item'                => __( 'Update Job Category', 'jobboardwp' ),
							'add_new_item'               => __( 'Add New Job Category', 'jobboardwp' ),
							'new_item_name'              => __( 'New Job Category Name', 'jobboardwp' ),
							'popular_items'              => __( 'Popular Job Categories', 'jobboardwp' ),
							'separate_items_with_commas' => __( 'Separate Job Categories with commas', 'jobboardwp' ),
							'add_or_remove_items'        => __( 'Add or remove Job Categories', 'jobboardwp' ),
							'choose_from_most_used'      => __( 'Choose from the most used Job Categories', 'jobboardwp' ),
							'not_found'                  => __( 'No Job Categories found', 'jobboardwp' ),
							'no_terms'                   => __( 'No Job Categories', 'jobboardwp' ),
							'parent_item'                => __( 'Parent Category', 'jobboardwp' ),
							'parent_item_colon'          => __( 'Parent Category:', 'jobboardwp' ),
						),
						'hierarchical'       => true,
						'label'              => __( 'Job Categories', 'jobboardwp' ),
						'show_ui'            => true,
						'show_in_quick_edit' => false,
						'meta_box_cb'        => false,
						'show_in_menu'       => false,
						'query_var'          => true,
						'capabilities'       => array(
							'manage_terms' => 'manage_jb-job-categories',
							'edit_terms'   => 'edit_jb-job-categories',
							'delete_terms' => 'delete_jb-job-categories',
							'assign_terms' => 'edit_jb-job-categories',
						),
						'rewrite'            => array(
							'slug'       => $category_slug,
							'with_front' => false,
						),
						'show_in_rest'       => true,
					),
				);
			}

			/**
			 * Filters the custom taxonomies list that will be created when JobBoardWP is active.
			 *
			 * @since 1.0
			 * @hook jb_taxonomies_list
			 *
			 * @param {array} $taxonomies Taxonomies data.
			 *
			 * @return {array} Taxonomies data.
			 */
			return apply_filters( 'jb_taxonomies_list', $taxonomies );
		}


		/**
		 * Get all custom post statuses
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function get_post_statuses() {
			$statuses = array(
				'jb-expired' => array(
					'label'                     => _x( 'Expired', 'post status', 'jobboardwp' ),
					'public'                    => true,
					'protected'                 => true,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					// translators: %s: posts count
					'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'jobboardwp' ),
				),
				'jb-preview' => array(
					'label'                     => _x( 'Preview', 'post status', 'jobboardwp' ),
					'public'                    => false,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => false,
					'show_in_admin_status_list' => true,
					// translators: %s: posts count
					'label_count'               => _n_noop( 'Preview <span class="count">(%s)</span>', 'Preview <span class="count">(%s)</span>', 'jobboardwp' ),
				),
			);

			/**
			 * Filters the custom post statuses list that will be initialized when JobBoardWP is active.
			 *
			 * @since 1.0
			 * @hook jb_post_statuses
			 *
			 * @param {array} $statuses Statuses data.
			 *
			 * @return {array} Statuses data.
			 */
			return apply_filters( 'jb_post_statuses', $statuses );
		}


		/**
		 * Create CPT & Taxonomies
		 *
		 * @since 1.0
		 */
		public function create_post_types() {
			$cpt = $this->get();
			foreach ( $cpt as $post_type => $args ) {
				register_post_type( $post_type, $args );
			}

			$taxonomies = $this->get_taxonomies();
			foreach ( $taxonomies as $key => $taxonomy ) {
				register_taxonomy( $key, $taxonomy['post_types'], $taxonomy['tax_args'] );
			}
		}


		/**
		 * Register Job Board statuses
		 *
		 * @since 1.0
		 */
		public function register_post_statuses() {
			$order_statuses = $this->get_post_statuses();

			foreach ( $order_statuses as $order_status => $values ) {
				register_post_status( $order_status, $values );
			}
		}


		/**
		 * Expand wp-admin bar links
		 *
		 * @param \WP_Admin_Bar $wp_admin_bar
		 *
		 * @since 1.0
		 */
		public function toolbar_links( $wp_admin_bar ) {
			global $post;

			if ( ! is_user_logged_in() ) {
				return;
			}

			if ( ! is_singular( array( 'jb-job' ) ) ) {
				return;
			}

			if ( ! current_user_can( 'edit_post', $post->ID ) ) {
				return;
			}

			$args = array(
				'id'    => 'jb_edit_job',
				'title' => '<span class="ab-icon"></span>' . __( 'Edit Job', 'jobboardwp' ),
				'href'  => get_edit_post_link(),
				'meta'  => array(
					'class' => 'jb-child-toolbar',
				),
			);

			$wp_admin_bar->add_node( $args );
		}


		/**
		 * Expand wp-admin bar links
		 *
		 * @param \WP_Admin_Bar $wp_admin_bar
		 *
		 * @since 1.0
		 */
		public function new_cpt_links( $wp_admin_bar ) {
			if ( ! is_user_logged_in() ) {
				return;
			}

			if ( ! current_user_can( 'create_jb-jobs' ) ) {
				return;
			}

			$wp_admin_bar->add_menu(
				array(
					'parent' => 'new-content',
					'id'     => 'new-jb-job',
					'title'  => __( 'Job', 'jobboardwp' ),
					'href'   => add_query_arg( array( 'post_type' => 'jb-job' ), admin_url( 'post-new.php' ) ),
				)
			);
		}
	}
}
