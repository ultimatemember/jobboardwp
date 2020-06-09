<?php namespace jb\ajax;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\ajax\Jobs' ) ) {


	/**
	 * Class Jobs
	 *
	 * @package jb\ajax
	 */
	class Jobs {


		/**
		 * @var int
		 */
		var $jobs_per_page;


		/**
		 * @var array
		 */
		var $query_args = [];


		var $search = '';


		var $company_name_meta = '';


		/**
		 * Jobs constructor.
		 */
		function __construct() {
			add_action( 'wp_loaded', [ $this, 'init_variables' ], 10 );
		}


		/**
		 * Init variables
		 */
		function init_variables() {
			$this->jobs_per_page = JB()->options()->get( 'jobs-list-pagination' );
		}


		function change_where_posts( $where, $query ) {
			if ( ! empty( $_POST['search'] ) ) {
				$from = '/' . preg_quote( $this->search, '/' ) . '/';
				$where = preg_replace( $from, '', $where, 1 );
			}
			return $where;
		}


		function set_search( $search, $query ) {
			$this->search = $search;
			return $search;
		}


		/**
		 * Change mySQL meta query join attribute
		 * for search only by UM user meta fields and WP core fields in WP Users table
		 *
		 * @param array $sql Array containing the query's JOIN and WHERE clauses.
		 * @param $queries
		 * @param $type
		 * @param $primary_table
		 * @param $primary_id_column
		 * @param \WP_Query $context
		 *
		 * @return mixed
		 */
		function change_meta_sql( $sql, $queries, $type, $primary_table, $primary_id_column, $context ) {
			if ( ! empty( $_POST['search'] ) ) {
				global $wpdb;
				$search = trim( stripslashes( $_POST['search'] ) );
				if ( ! empty( $search ) ) {

					$meta_value = '%' . $wpdb->esc_like( $search ) . '%';
					$search_meta      = $wpdb->prepare( '%s', $meta_value );

					preg_match(
						"/\(\s(.*).meta_key = \'jb-company-name\'[^\)]/im",
						$sql['where'],
						$join_matches
					);

					$from = '/' . preg_quote( ' AND ', '/' ) . '/';
					$search_query = preg_replace( $from, ' OR ', $this->search, 1 );


					if ( isset( $join_matches[1] ) ) {
						$meta_join_for_search = trim( $join_matches[1] );

						$this->company_name_meta = $meta_join_for_search;

						preg_match(
							"/\( (" . $meta_join_for_search . ".meta_key = 'jb-company-name' AND " . $meta_join_for_search . ".meta_value LIKE " . $search_meta . ") \)/im",
							$sql['where'],
							$join_matches
						);

						$sql['where'] = preg_replace(
							"/\( (" . $meta_join_for_search . ".meta_key = 'jb-company-name' AND " . $meta_join_for_search . ".meta_value LIKE " . $search_meta . ") \)/im",
							"( $1 " . $search_query . " )",
							$sql['where'],
							1
						);

					}

				}
			}

			return $sql;
		}


		/**
		 * @param string $search_orderby
		 * @param \WP_Query $query
		 *
		 * @return string
		 */
		function relevance_search( $search_orderby, $query ) {
			global $wpdb;

			$search_orderby = '';

			$search = trim( stripslashes( $_POST['search'] ) );
			$meta_value = '%' . $wpdb->esc_like( $search ) . '%';

			// Sentence match in 'post_title'.
			if ( $meta_value ) {
				$search_orderby .= $wpdb->prepare( "WHEN {$wpdb->posts}.post_title LIKE %s THEN 1 ", $meta_value );
				$search_orderby .= $wpdb->prepare( "WHEN {$this->company_name_meta}.meta_value LIKE %s THEN 2 ", $meta_value );
				$search_orderby .= $wpdb->prepare( "WHEN {$wpdb->posts}.post_content LIKE %s THEN 3 ", $meta_value );
			}

			if ( $search_orderby ) {
				$search_orderby = '(CASE ' . $search_orderby . 'ELSE 4 END)';
			}

			return $search_orderby;
		}


		/**
		 *
		 *
		 */
		function get_jobs() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );

			global $wpdb;
			// Prepare for BIG SELECT query
			$wpdb->query( 'SET SQL_BIG_SELECTS=1' );

			/**
			 * Handle pagination
			 *
			 */
			$paged = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

			$query_args = [
				'posts_per_page'    => $this->jobs_per_page,
				'offset'            => $this->jobs_per_page * ( $paged - 1 ),
				'orderby'           => 'date',
				'order'             => 'DESC',
				'post_type'         => 'jb-job',
				'post_status'       => [ 'publish' ],
			];

			if ( ! empty( $_POST['search'] ) ) {
				$search = trim( stripslashes( $_POST['search'] ) );
				if ( ! empty( $search ) ) {
					$query_args['s'] = $search;

					if ( ! isset( $query_args['meta_query'] ) ) {
						$query_args['meta_query'] = [];
					}

					$query_args['meta_query'] = array_merge( $query_args['meta_query'], [
						'relation'  => 'AND',
						[
							'key'       => 'jb-company-name',
							'value'     => $search,
							'compare'   => 'LIKE',
						],
					] );
				}
			}

			if ( ! empty( $_POST['location'] ) ) {
				$location = trim( stripslashes( $_POST['location'] ) );
				if ( ! empty( $location ) ) {

					if ( ! isset( $query_args['meta_query'] ) ) {
						$query_args['meta_query'] = [];
					}

					$query_args['meta_query'] = array_merge( $query_args['meta_query'], [
						'relation'  => 'AND',
						[
							'key'       => 'jb-location',
							'value'     => $location,
							'compare'   => 'LIKE',
						],
					] );
				}
			}

			$remote_only = ( isset( $_POST['remote_only'] ) && '1' == $_POST['remote_only'] );
			if ( $remote_only ) {

				if ( ! isset( $query_args['meta_query'] ) ) {
					$query_args['meta_query'] = [];
				}

				$query_args['meta_query'] = array_merge( $query_args['meta_query'], [
					'relation'  => 'AND',
					[
						'key'       => 'jb-location-type',
						'value'     => '1',
						'compare'   => '=',
					],
				] );
			}

			add_filter( 'get_meta_sql', array( &$this, 'change_meta_sql' ), 10, 6 );
			add_filter( 'posts_search', array( &$this, 'set_search' ), 10, 2 );
			add_filter( 'posts_where', array( &$this, 'change_where_posts' ), 10, 2 );

			add_filter( 'posts_search_orderby', array( &$this, 'relevance_search' ), 10, 2 );

			$get_posts = new \WP_Query;
			$jobs_query = $get_posts->query( $query_args );

			remove_filter( 'posts_where', array( &$this, 'change_where_posts' ), 10 );
			remove_filter( 'posts_search', array( &$this, 'set_search' ), 10 );
			remove_filter( 'get_meta_sql', array( &$this, 'change_meta_sql' ), 10 );
			remove_filter( 'posts_search_orderby', array( &$this, 'relevance_search' ), 10 );

			$jobs = [];
			if ( ! empty( $jobs_query ) ) {
				foreach ( $jobs_query as $job_post ) {

					$job_company_data = JB()->common()->job()->get_company_data( $job_post->ID );
					$location = JB()->common()->job()->get_location( $job_post->ID, true );

					$data_types = [];
					$types = wp_get_post_terms( $job_post->ID, 'jb-job-type', [
						'orderby'   => 'name',
						'order'     => 'ASC',
					] );;
					foreach ( $types as $type ) {
						$data_types[] = [
							'name'      => $type->name,
							'color'     => get_term_meta( $type->term_id, 'jb-color', true ),
							'bg_color'  => get_term_meta( $type->term_id, 'jb-background', true ),
						];
					}

					$jobs[] = [
						'title'     => $job_post->post_title,
						'permalink' => get_permalink( $job_post ),
						'date'      => JB()->common()->job()->get_posted_date( $job_post->ID, true ),
						'expires'   => JB()->common()->job()->get_expiry_date( $job_post->ID ),
						'company'   => [
							'name'      => $job_company_data['name'],
							'website'   => $job_company_data['website'],
							'tagline'   => $job_company_data['tagline'],
							'twitter'   => $job_company_data['twitter'],
							'facebook'  => $job_company_data['facebook'],
							'instagram' => $job_company_data['instagram'],
						],
						'logo'      => JB()->options()->get( 'jobs-list-no-logo' ) ? '' : JB()->common()->job()->get_logo( $job_post->ID ),
						'location'  => JB()->common()->job()->get_location_link( $location ),
						'types'     => $data_types,
					];
				}
			}

			$response = apply_filters( 'jb_jobs_list_response', [
				'pagination'    => $this->calculate_pagination( $get_posts->found_posts ),
				'jobs'          => $jobs,
			] );


			wp_send_json_success( $response );



			do_action( 'um_member_directory_before_query' );

			// Prepare default user query values
			$this->query_args = array(
				'fields'        => 'ids',
				'number'        => 0,
				'meta_query'    => array(
					'relation' => 'AND'
				),
			);

			// handle pagination options
			$this->pagination_options();

			// handle general search line
			$this->general_search();

			$this->query_args = apply_filters( 'um_prepare_user_query_args', $this->query_args );

			//unset empty meta_query attribute
			if ( isset( $this->query_args['meta_query']['relation'] ) && count( $this->query_args['meta_query'] ) == 1 ) {
				unset( $this->query_args['meta_query'] );
			}

			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_user_before_query
			 * @description Action before users query on member directory
			 * @input_vars
			 * [{"var":"$query_args","type":"array","desc":"Query arguments"},
			 * {"var":"$md_class","type":"um\core\Member_Directory","desc":"Member Directory class"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_user_before_query', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_action( 'um_user_before_query', 'my_user_before_query', 10, 1 );
			 * function my_user_before_query( $query_args ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_user_before_query', $this->query_args, $this );

			add_filter( 'get_meta_sql', array( &$this, 'change_meta_sql' ), 10, 6 );

			add_filter( 'pre_user_query', array( &$this, 'pagination_changes' ), 10, 1 );

			$user_query = new \WP_User_Query( $this->query_args );

			remove_filter( 'pre_user_query', array( &$this, 'pagination_changes' ), 10 );

			remove_filter( 'get_meta_sql', array( &$this, 'change_meta_sql' ), 10 );

			/**
			 * UM hook
			 *
			 * @type action
			 * @title um_user_after_query
			 * @description Action before users query on member directory
			 * @input_vars
			 * [{"var":"$query_args","type":"array","desc":"Query arguments"},
			 * {"var":"$user_query","type":"array","desc":"User Query"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'um_user_after_query', 'function_name', 10, 2 );
			 * @example
			 * <?php
			 * add_action( 'um_user_after_query', 'my_user_after_query', 10, 2 );
			 * function my_user_after_query( $query_args, $user_query ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action( 'um_user_after_query', $this->query_args, $user_query );

			$pagination_data = $this->calculate_pagination( $directory_data, $user_query->total_users );

			$user_ids = ! empty( $user_query->results ) ? array_unique( $user_query->results ) : array();
		}


		/**
		 *
		 */
		function get_employer_jobs() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );

			$employer = get_current_user_id();

			/**
			 * Handle pagination
			 *
			 */
//			$paged = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

			$get_posts = new \WP_Query;
			$jobs_query = $get_posts->query( [
				'author'        => $employer,
//				'number'        => $this->jobs_per_page,
//				'paged'         => $paged,
				'orderby'       => 'date',
				'order'         => 'DESC',
				'post_type'     => 'jb-job',
				'post_status'   => [ 'publish', 'draft', 'pending', 'jb-preview', 'jb-expired' ],
			] );

			$jobs = [];
			if ( ! empty( $jobs_query ) ) {
				foreach ( $jobs_query as $job_post ) {
					if ( $job_post->post_status != 'publish' ) {
						$status_label = JB()->common()->job()->get_status( $job_post->ID );
						$status = $job_post->post_status == 'jb-preview' ? 'draft' : $job_post->post_status;
					} else {
						$status_label = JB()->common()->job()->is_filled( $job_post->ID ) ? __( 'Filled', 'jobboardwp' ) : __( 'Not-filled', 'jobboardwp' );
						$status = JB()->common()->job()->is_filled( $job_post->ID ) ? 'filled' : 'not-filled';
					}

					$jobs[] = [
						'title'             => $job_post->post_title,
						'permalink'         => get_permalink( $job_post ),
						'is_published'      => $job_post->post_status == 'publish',
						'status_label'      => $status_label,
						'status'            => $status,
						'date'              => JB()->common()->job()->get_posted_date( $job_post->ID ),
						'expires'           => JB()->common()->job()->get_expiry_date( $job_post->ID ),
						'actions'           => JB()->common()->job()->get_actions( $job_post->ID ),
					];
				}
			}

			$response = apply_filters( 'jb_job_dashboard_response', [
//				'pagination'    => $this->calculate_pagination( $get_posts->post_count ),
				'jobs'          => $jobs,
			] );

			wp_send_json_success( $response );
		}


		/**
		 * Get data array for pagination
		 *
		 * @param int $total_jobs
		 *
		 * @return array
		 */
		function calculate_pagination( $total_jobs ) {

			$current_page = ! empty( $_POST['page'] ) ? $_POST['page'] : 1;

			$total_pages = ceil( $total_jobs / $this->jobs_per_page );

			if ( ! empty( $total_pages ) ) {
				$index1 = 0 - ( $current_page - 2 ) + 1;
				$to = $current_page + 2;
				if ( $index1 > 0 ) {
					$to += $index1;
				}

				$index2 = $total_pages - ( $current_page + 2 );
				$from = $current_page - 2;
				if ( $index2 < 0 ) {
					$from += $index2;
				}

				$pages_to_show = range(
					( $from > 0 ) ? $from : 1,
					( $to <= $total_pages ) ? $to : $total_pages
				);
			}

			return apply_filters( 'jb_jobs_dashboard_calculate_pagination_result', [
				'pages_to_show' => ( ! empty( $pages_to_show ) && count( $pages_to_show ) > 1 ) ? array_values( $pages_to_show ) : [],
				'current_page'  => $current_page,
				'total_pages'   => $total_pages,
				'total_jobs'    => $total_jobs,
			] );
		}
	}
}