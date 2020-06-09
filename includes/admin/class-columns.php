<?php
namespace jb\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\admin\Columns' ) ) {


	/**
	 * Class Columns
	 *
	 * @package jb\admin
	 */
	class Columns {


		/**
		 * Columns constructor.
		 */
		function __construct() {
			add_filter( 'display_post_states', [ &$this, 'add_display_post_states' ], 10, 2 );


			add_filter( 'manage_edit-jb-job_columns', [ &$this, 'job_columns' ] );
			add_action( 'manage_jb-job_posts_custom_column', [ &$this, 'job_columns_content' ], 10, 3 );

			add_filter( 'views_edit-jb-job', [ &$this, 'replace_list_table' ], 10, 1 );
			add_filter( 'post_row_actions', [ &$this, 'remove_quick_edit' ] , 10, 2 );
		}


		/**
		 * Extends WP_Posts_List_Table class via JB class
		 * to change list table view
		 *
		 * @param $views
		 *
		 * @return mixed
		 */
		function replace_list_table( $views ) {
			global $wp_list_table;
			$wp_list_table = new List_Table();

			return $views;
		}


		/**
		 * @param array $actions
		 * @param \WP_Post $post
		 *
		 * @return array
		 */
		function remove_quick_edit( $actions, $post ) {
			if ( $post->post_type == 'jb-job' ) {
				unset( $actions['inline hide-if-no-js'] );
			}
			return $actions;
		}


		/**
		 * Add a post display state for special Job Board pages in the page list table.
		 *
		 * @param array $post_states An array of post display states.
		 * @param \WP_Post $post The current post object.
		 *
		 * @return mixed
		 */
		function add_display_post_states( $post_states, $post ) {
			if ( $post->post_type == 'page' ) {
				foreach ( JB()->config()->get( 'core_pages' ) as $page_key => $page_value ) {
					if ( JB()->options()->get( $page_key . '_page' ) == $post->ID ) {
						$post_states[ 'jb_page_' . $page_key ] = sprintf( __( 'Job Board %s', 'jobboardwp' ), $page_value['title'] );
					}
				}
			}

			return $post_states;
		}


		/**
		 * Custom columns for JB Job
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		function job_columns( $columns ) {
			$additional_columns = [
				'title'     => __( '(#ID) Position', 'jobboardwp' ),
				'type'      => __( 'Type', 'jobboardwp' ),
				'location'  => __( 'Location', 'jobboardwp' ),
				'status'    => __( 'Status', 'jobboardwp' ),
				'posted'    => __( 'Posted', 'jobboardwp' ),
				'expires'   => __( 'Expires', 'jobboardwp' ),
				'category'  => __( 'Category', 'jobboardwp' ),
				'filled'    => __( 'Filled?', 'jobboardwp' ),
			];

			if ( ! JB()->options()->get( 'job-categories' ) ) {
				unset( $additional_columns['category'] );
			}

			return $additional_columns;
		}


		/**
		 * Display custom columns for Forum
		 *
		 * @param string $column_name
		 * @param int $id
		 */
		function job_columns_content( $column_name, $id ) {
			switch ( $column_name ) {
				case 'location':
					$type = JB()->common()->job()->get_location_type( $id );
					$location = JB()->common()->job()->get_location( $id );
					printf( __( '%s (%s)', 'jobboardwp' ), $type, $location );
					break;
				case 'status':
					echo JB()->common()->job()->get_status( $id );
					break;
				case 'type':
					//echo '<div class="jb-job-types">' . JB()->common()->job()->display_types( $id ) . '</div>';
					$terms = wp_get_post_terms(
						$id,
						'jb-job-type',
						[
							'orderby'   => 'name',
							'order'     => 'ASC',
							'fields'    => 'names'
						]
					);

					if ( ! empty( $terms ) ) {
						echo implode( ', ', $terms );
					}
					break;
				case 'category':
					$terms = wp_get_post_terms(
						$id,
						'jb-job-category',
						[
							'orderby'   => 'name',
							'order'     => 'ASC',
							'fields'    => 'names'
						]
					);

					if ( ! empty( $terms ) ) {
						echo implode( ',', $terms );
					}
					break;
				case 'posted':
					$posted = JB()->common()->job()->get_posted_date( $id );
					$author = JB()->common()->job()->get_job_author( $id );
					printf( __( '%s <br />by <a href="" title="Filter by author">%s</a>', 'jobboardwp' ), $posted, $author );
					break;
				case 'expires':
					$expiry = JB()->common()->job()->get_expiry_date( $id );
					if ( ! empty( $expiry ) ) {
						echo $expiry;
					} else {
						echo '&ndash;';
					}
					break;
				case 'filled':
					if ( JB()->common()->job()->is_filled( $id ) ) {
						echo '&#10004;';
					} else {
						echo '&ndash;';
					}
					break;
			}
		}
	}
}