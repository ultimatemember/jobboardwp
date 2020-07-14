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


		/**
		 * @var string
		 */
		var $search = '';


		/**
		 * @var string
		 */
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


		/**
		 * @param $where
		 * @param $query
		 *
		 * @return string|string[]|null
		 */
		function change_where_posts( $where, $query ) {
			if ( ! empty( $_POST['search'] ) ) {
				$from = '/' . preg_quote( $this->search, '/' ) . '/';
				$where = preg_replace( $from, '', $where, 1 );
			}
			return $where;
		}


		/**
		 * @param $search
		 * @param $query
		 *
		 * @return mixed
		 */
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

//						preg_match(
//							"/\( (" . $meta_join_for_search . ".meta_key = 'jb-company-name' AND " . $meta_join_for_search . ".meta_value LIKE " . $search_meta . ") \)/im",
//							$sql['where'],
//							$join_matches
//						);

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

			$statuses = [ 'publish' ];
			if ( JB()->options()->get( 'jobs-list-hide-filled' ) ) {
				if ( ! isset( $query_args['meta_query'] ) ) {
					$query_args['meta_query'] = [];
				}

				$query_args['meta_query'] = array_merge( $query_args['meta_query'], [
					'relation'  => 'AND',
					[
						'relation'  => 'OR',
						[
							'key'       => 'jb-is-filled',
							'value'     => false,
						],
						[
							'key'       => 'jb-is-filled',
							'compare'   => 'NOT EXISTS',
						],
					],
				] );
			}

			if ( ! JB()->options()->get( 'jobs-list-hide-expired' ) ) {
				$statuses[] = 'jb-expired';
			}

			$query_args = [
				'orderby'           => 'date',
				'order'             => 'DESC',
				'post_type'         => 'jb-job',
				'post_status'       => $statuses,
			];

			if ( ! empty( $_POST['get_previous'] ) ) {
				// first loading with page > 1....to show the jobs above
				$query_args['posts_per_page'] = $this->jobs_per_page * $paged;
				$query_args['offset'] = 0;
			} else {
				$query_args['posts_per_page'] = $this->jobs_per_page;
				$query_args['offset'] = $this->jobs_per_page * ( $paged - 1 );
			}

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

					$location_meta_keys = [ 'jb-location' ];
					$key = JB()->options()->get( 'googlemaps-api-key' );
					if ( ! empty( $key ) ) {
						$location_meta_keys = array_merge( $location_meta_keys, [ 'jb-location-formatted-address', 'jb-location-state-long' ] );
					}

					$location_query = [];
					if ( count( $location_meta_keys ) > 1 ) {
						$location_query['relation'] = 'OR';
						foreach ( $location_meta_keys as $location_meta_key ) {
							$location_query[] = [
								'key'       => $location_meta_key,
								'value'     => $location,
								'compare'   => 'LIKE',
							];
						}
					} else {
						$location_query = [
							'key'       => $location_meta_keys[0],
							'value'     => $location,
							'compare'   => 'LIKE',
						];
					}

					$query_args['meta_query'] = array_merge( $query_args['meta_query'], [
						'relation'  => 'AND',
						$location_query,
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

			$type = ! empty( $_POST['type'] ) ? absint( $_POST['type'] ) : '';
			if ( ! empty( $type ) ) {
				$query_args['tax_query'][] = [
					'taxonomy'  => 'jb-job-type',
					'field'     => 'id',
					'terms'     => $type,
				];
			}

			if ( JB()->options()->get( 'job-categories' ) ) {
				$category = ! empty( $_POST['category'] ) ? absint( $_POST['category'] ) : '';
				if ( ! empty( $category ) ) {
					$query_args['tax_query'][] = [
						'taxonomy'  => 'jb-job-category',
						'field'     => 'id',
						'terms'     => $category,
					];
				}
			}

			add_filter( 'get_meta_sql', [ &$this, 'change_meta_sql' ], 10, 6 );
			add_filter( 'posts_search', [ &$this, 'set_search' ], 10, 2 );
			add_filter( 'posts_where', [ &$this, 'change_where_posts' ], 10, 2 );

			add_filter( 'posts_search_orderby', [ &$this, 'relevance_search' ], 10, 2 );

			$get_posts = new \WP_Query;
			$jobs_query = $get_posts->query( $query_args );

			remove_filter( 'posts_where', [ &$this, 'change_where_posts' ], 10 );
			remove_filter( 'posts_search', [ &$this, 'set_search' ], 10 );
			remove_filter( 'get_meta_sql', [ &$this, 'change_meta_sql' ], 10 );
			remove_filter( 'posts_search_orderby', [ &$this, 'relevance_search' ], 10 );

			$jobs = [];
			if ( ! empty( $jobs_query ) ) {
				foreach ( $jobs_query as $job_post ) {

					$job_company_data = JB()->common()->job()->get_company_data( $job_post->ID );

					$location = JB()->common()->job()->get_location( $job_post->ID, true );
					$location_type = get_post_meta( $job_post->ID, 'jb-location-type', true );

					if ( $location_type == '1' && empty( $location ) ) {
						$formatted_location = __( 'Remote', 'jobboardwp' );
					} elseif ( empty( $location ) ) {
						$formatted_location = __( 'Anywhere', 'jobboardwp' );
					} else {
						$formatted_location = $location;
					}

					$data_types = [];
					$types = wp_get_post_terms( $job_post->ID, 'jb-job-type', [
						'orderby'   => 'name',
						'order'     => 'ASC',
					] );
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
						'date'      => JB()->common()->job()->get_posted_date( $job_post->ID ),
						'expires'   => JB()->common()->job()->get_expiry_date( $job_post->ID ),
						'company'   => [
							'name'      => $job_company_data['name'],
							'website'   => $job_company_data['website'],
							'tagline'   => $job_company_data['tagline'],
							'twitter'   => $job_company_data['twitter'],
							'facebook'  => $job_company_data['facebook'],
							'instagram' => $job_company_data['instagram'],
						],
						'logo'      => JB()->common()->job()->get_logo( $job_post->ID ),
						'location'  => $formatted_location,
						'types'     => $data_types,
					];
				}
			}

			$response = apply_filters( 'jb_jobs_list_response', [
				'pagination'    => $this->calculate_pagination( $get_posts->found_posts ),
				'jobs'          => $jobs,
			] );


			wp_send_json_success( $response );
		}


		/**
		 *
		 */
		function delete_job() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );

			if ( empty( $_POST['job_id'] ) ) {
				wp_send_json_error( __( 'Wrong job ID.', 'jobboardwp' ) );
			}

			$job_id = absint( $_POST['job_id'] );

			$job = get_post( $job_id );
			if ( is_wp_error( $job ) || empty( $job ) ) {
				wp_send_json_error( __( 'Wrong job.', 'jobboardwp' ) );
			}

			if ( get_current_user_id() != $job->post_author ) {
				wp_send_json_error( __( 'You haven\'t ability to delete this job.', 'jobboardwp' ) );
			}

			$result = wp_delete_post( $job_id, true );
			if ( ! empty( $result ) ) {
				wp_send_json_success();
			} else {
				wp_send_json_error( __( 'Something went wrong.', 'jobboardwp' ) );
			}
		}


		/**
		 *
		 */
		function fill_job() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );

			if ( empty( $_POST['job_id'] ) ) {
				wp_send_json_error( __( 'Wrong job ID', 'jobboardwp' ) );
			}

			$job_id = absint( $_POST['job_id'] );

			$job = get_post( $job_id );
			if ( is_wp_error( $job ) || empty( $job ) ) {
				wp_send_json_error( __( 'Wrong job', 'jobboardwp' ) );
			}

			if ( get_current_user_id() != $job->post_author ) {
				wp_send_json_error( __( 'You haven\'t ability to fill this job.', 'jobboardwp' ) );
			}

			if ( JB()->common()->job()->is_filled( $job_id ) ) {
				wp_send_json_error( __( 'Job is already filled.', 'jobboardwp' ) );
			}

			update_post_meta( $job_id, 'jb-is-filled', true );

			if ( JB()->common()->job()->is_filled( $job_id ) ) {
				$job = get_post( $job_id );

				$jobs = [];
				$jobs[] = $this->get_job_data( $job );

				wp_send_json_success( [ 'jobs' => $jobs ] );
			} else {
				wp_send_json_error( __( 'Something went wrong.', 'jobboardwp' ) );
			}
		}


		/**
		 *
		 */
		function unfill_job() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );

			if ( empty( $_POST['job_id'] ) ) {
				wp_send_json_error( __( 'Wrong job ID', 'jobboardwp' ) );
			}

			$job_id = absint( $_POST['job_id'] );

			$job = get_post( $job_id );
			if ( is_wp_error( $job ) || empty( $job ) ) {
				wp_send_json_error( __( 'Wrong job', 'jobboardwp' ) );
			}

			if ( get_current_user_id() != $job->post_author ) {
				wp_send_json_error( __( 'You haven\'t ability to un-fill this job.', 'jobboardwp' ) );
			}

			if ( ! JB()->common()->job()->is_filled( $job_id ) ) {
				wp_send_json_error( __( 'Job isn\'t filled yet.', 'jobboardwp' ) );
			}

			update_post_meta( $job_id, 'jb-is-filled', false );

			if ( ! JB()->common()->job()->is_filled( $job_id ) ) {
				$job = get_post( $job_id );

				$jobs = [];
				$jobs[] = $this->get_job_data( $job );

				wp_send_json_success( [ 'jobs' => $jobs ] );
			} else {
				wp_send_json_error( __( 'Something went wrong.', 'jobboardwp' ) );
			}
		}


		/**
		 * @param $job_post
		 *
		 * @return array
		 */
		function get_job_data( $job_post ) {
			if ( $job_post->post_status != 'publish' ) {
				$status_label = JB()->common()->job()->get_status( $job_post->ID );
				$status = $job_post->post_status == 'jb-preview' ? 'draft' : $job_post->post_status;
			} else {
				$status_label = JB()->common()->job()->is_filled( $job_post->ID ) ? __( 'Filled', 'jobboardwp' ) : __( 'Not-filled', 'jobboardwp' );
				$status = JB()->common()->job()->is_filled( $job_post->ID ) ? 'filled' : 'not-filled';
			}

			return apply_filters( 'jb_job_dashboard_job_data_response', [
				'id'                => $job_post->ID,
				'title'             => $job_post->post_title,
				'permalink'         => get_permalink( $job_post ),
				'is_published'      => $job_post->post_status == 'publish',
				'status_label'      => $status_label,
				'status'            => $status,
				'date'              => JB()->common()->job()->get_posted_date( $job_post->ID ),
				'expires'           => JB()->common()->job()->get_expiry_date( $job_post->ID ),
				'actions'           => JB()->common()->job()->get_actions( $job_post->ID ),
			], $job_post );
		}


		/**
		 *
		 */
		function get_employer_jobs() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );

			$employer = get_current_user_id();

			$get_posts = new \WP_Query;
			$jobs_query = $get_posts->query( [
				'author'        => $employer,
				'orderby'       => 'date',
				'order'         => 'DESC',
				'post_type'     => 'jb-job',
				'post_status'   => [ 'publish', 'draft', 'pending', 'jb-preview', 'jb-expired' ],
			] );

			$jobs = [];
			if ( ! empty( $jobs_query ) ) {
				foreach ( $jobs_query as $job_post ) {
					$jobs[] = $this->get_job_data( $job_post );
				}
			}

			$response = apply_filters( 'jb_job_dashboard_response', [
				'jobs'  => $jobs,
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