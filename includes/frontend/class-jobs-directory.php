<?php
namespace jb\frontend;

use WP_Error;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\frontend\Jobs_Directory' ) ) {

	/**
	 * Class Jobs_Directory
	 *
	 * @package jb\frontend
	 */
	class Jobs_Directory {

		/**
		 * @var array
		 *
		 * @since 1.0
		 */
		public $filters = array();

		/**
		 * @var array
		 *
		 * @since 1.0
		 */
		public $filter_types = array();

		/**
		 * Jobs_Directory constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'init_variables' ) );
			if ( empty( $this->filter_types ) || empty( $this->filters ) ) {
				$this->init_variables();
			}

			add_action( 'pre_get_posts', array( &$this, 'jb_exclude_jobs' ), 99, 1 );
		}

		/**
		 * Init jobs directory variables
		 *
		 * @since 1.0
		 */
		public function init_variables() {
			/**
			 * Filters the jobs list filters.
			 *
			 * Note: The filters structure is 'filter_key' => 'filter_title'.
			 *
			 * @since 1.0
			 * @hook jb_jobs_directory_filters
			 *
			 * @param {array} $filters Jobs list filters.
			 *
			 * @return {array} Jobs list filters.
			 */
			$this->filters = apply_filters(
				'jb_jobs_directory_filters',
				array(
					'job_type' => __( 'Job Type', 'jobboardwp' ),
					'company'  => __( 'Company', 'jobboardwp' ),
				)
			);

			/**
			 * Filters the jobs list filters' types.
			 *
			 * Note: The filters structure is 'filter_key' => 'filter_type'.
			 *
			 * @since 1.0
			 * @hook jb_jobs_directory_filter_types
			 *
			 * @param {array} $filter_types Jobs list filters' types.
			 *
			 * @return {array} Jobs list filters' types.
			 */
			$this->filter_types = apply_filters(
				'jb_jobs_directory_filter_types',
				array(
					'job_type' => 'select',
					'company'  => 'select',
				)
			);
		}

		/**
		 * Hide filled and expired jobs from archive pages
		 *
		 * @param WP_Query $query
		 */
		public function jb_exclude_jobs( $query ) {
			if ( $query->is_main_query() ) {
				$exclude_posts = array();
				$hide_filled   = JB()->options()->get( 'jobs-list-hide-filled' );
				if ( ! empty( $hide_filled ) ) {
					if ( is_user_logged_in() ) {
						$employer = get_current_user_id();

						$args = array(
							'author__not_in' => array( $employer ),
							'post_type'      => 'jb-job',
							'post_status'    => array( 'publish', 'draft', 'pending', 'jb-preview', 'jb-expired' ),
							'posts_per_page' => -1,
							'meta_query'     => array(
								'relation' => 'OR',
								array(
									'key'   => 'jb-is-filled',
									'value' => true,
								),
								array(
									'key'   => 'jb-is-filled',
									'value' => 1,
								),
							),
							'fields'         => 'ids',
						);
					} else {
						$args = array(
							'post_type'      => 'jb-job',
							'post_status'    => array( 'publish', 'draft', 'pending', 'jb-preview', 'jb-expired' ),
							'posts_per_page' => -1,
							'meta_query'     => array(
								'relation' => 'OR',
								array(
									'key'   => 'jb-is-filled',
									'value' => true,
								),
								array(
									'key'   => 'jb-is-filled',
									'value' => 1,
								),
							),
							'fields'         => 'ids',
						);
					}
					$filled_ids    = get_posts( $args );
					$exclude_posts = array_merge( $exclude_posts, $filled_ids );
				}

				$hide_expired = JB()->options()->get( 'jobs-list-hide-expired' );
				if ( ! empty( $hide_expired ) ) {
					$expired_ids   = get_posts(
						array(
							'post_type'      => 'jb-job',
							'post_status'    => 'jb-expired',
							'fields'         => 'ids',
							'posts_per_page' => - 1,
						)
					);
					$exclude_posts = array_merge( $exclude_posts, $expired_ids );
				}

				$post__not_in = $query->get( 'post__not_in', array() );
				$query->set( 'post__not_in', array_merge( wp_parse_id_list( $post__not_in ), $exclude_posts ) );
			}
		}
	}
}
