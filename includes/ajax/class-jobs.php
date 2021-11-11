<?php namespace jb\ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\ajax\Jobs' ) ) {


	/**
	 * Class Jobs
	 *
	 * @package jb\ajax
	 */
	class Jobs {


		/**
		 * @var int
		 *
		 * @since 1.0
		 */
		public $jobs_per_page;


		/**
		 * @var array
		 *
		 * @since 1.0
		 */
		public $query_args = array();


		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		public $search = '';


		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		public $company_name_meta = '';


		/**
		 * Jobs constructor.
		 */
		public function __construct() {
			add_action( 'wp_loaded', array( $this, 'init_variables' ), 10 );
		}


		/**
		 * Init variables
		 *
		 * @since 1.0
		 */
		public function init_variables() {
			// phpcs:ignore WordPress.Security.NonceVerification -- already verified here
			$this->jobs_per_page = ! empty( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : JB()->options()->get( 'jobs-list-pagination' );
		}


		/**
		 * Replace 'WHERE' by the searching request
		 *
		 * @param string $where
		 * @param \WP_Query $query
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function change_where_posts( $where, /** @noinspection PhpUnusedParameterInspection */$query ) {
			// phpcs:ignore WordPress.Security.NonceVerification -- already verified here
			if ( ! empty( $_POST['search'] ) ) {
				$from  = '/' . preg_quote( $this->search, '/' ) . '/';
				$where = preg_replace( $from, '', $where, 1 );
			}
			return $where;
		}


		/**
		 * Set class search variable
		 *
		 * @param string $search
		 * @param \WP_Query $query
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function set_search( $search, /** @noinspection PhpUnusedParameterInspection */$query ) {
			$this->search = $search;
			return $search;
		}


		/**
		 * Change mySQL meta query join attribute
		 * for search by the company name
		 *
		 * @param array $sql Array containing the query's JOIN and WHERE clauses.
		 * @param $queries
		 * @param $type
		 * @param $primary_table
		 * @param $primary_id_column
		 * @param \WP_Query $context
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function change_meta_sql( $sql, /** @noinspection PhpUnusedParameterInspection */$queries, /** @noinspection PhpUnusedParameterInspection */$type, /** @noinspection PhpUnusedParameterInspection */$primary_table, /** @noinspection PhpUnusedParameterInspection */$primary_id_column, /** @noinspection PhpUnusedParameterInspection */$context ) {
			// phpcs:ignore WordPress.Security.NonceVerification -- already verified here
			if ( ! empty( $_POST['search'] ) ) {
				global $wpdb;
				$search = trim( stripslashes( sanitize_text_field( $_POST['search'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification -- already verified here
				if ( ! empty( $search ) ) {

					$meta_value  = '%' . $wpdb->esc_like( $search ) . '%';
					$search_meta = $wpdb->prepare( '%s', $meta_value );

					preg_match(
						"/\(\s(.*).meta_key = \'jb-company-name\'[^\)]/im",
						$sql['where'],
						$join_matches
					);

					$from         = '/' . preg_quote( ' AND ', '/' ) . '/';
					$search_query = preg_replace( $from, ' OR ', $this->search, 1 );

					if ( isset( $join_matches[1] ) ) {
						$meta_join_for_search = trim( $join_matches[1] );

						$this->company_name_meta = $meta_join_for_search;

						// phpcs:disable Squiz.Strings.DoubleQuoteUsage.NotRequired -- don't remove regex indentation
						$sql['where'] = preg_replace(
							"/\( (" . $meta_join_for_search . ".meta_key = 'jb-company-name' AND " . $meta_join_for_search . ".meta_value LIKE " . $search_meta . ") \)/im",
							"( $1 " . $search_query . " )",
							$sql['where'],
							1
						);
						// phpcs:enable Squiz.Strings.DoubleQuoteUsage.NotRequired -- don't remove regex indentation
					}
				}
			}

			return $sql;
		}


		/**
		 * Searching by relevance
		 *
		 * @param string $search_orderby
		 * @param \WP_Query $query
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function relevance_search( /** @noinspection PhpUnusedParameterInspection */$search_orderby, $query ) {
			global $wpdb;

			$search_orderby = '';

			// phpcs:ignore WordPress.Security.NonceVerification -- already verified here
			$search     = trim( stripslashes( sanitize_text_field( $_POST['search'] ) ) );
			$meta_value = '%' . $wpdb->esc_like( $search ) . '%';

			// Sentence match in 'post_title'.
			if ( $meta_value ) {
				$search_orderby .= $wpdb->prepare( "WHEN {$wpdb->posts}.post_title LIKE %s THEN 1 ", $meta_value );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $this->company_name_meta is static variable
				$search_orderby .= $wpdb->prepare( "WHEN {$this->company_name_meta}.meta_value LIKE %s THEN 2 ", $meta_value );
				$search_orderby .= $wpdb->prepare( "WHEN {$wpdb->posts}.post_content LIKE %s THEN 3 ", $meta_value );
			}

			if ( $search_orderby ) {
				$search_orderby = '(CASE ' . $search_orderby . 'ELSE 4 END)';
			}

			return $search_orderby;
		}


		/**
		 * AJAX response for getting jobs
		 *
		 * @since 1.0
		 */
		public function get_jobs() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );
			// phpcs:disable WordPress.Security.NonceVerification -- is verified above

			$query_args = array();

			global $wpdb;
			// Prepare for BIG SELECT query
			$wpdb->query( 'SET SQL_BIG_SELECTS=1' );

			/**
			 * Handle pagination
			 *
			 */
			$paged = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

			$employer = ! empty( $_POST['employer'] ) ? absint( $_POST['employer'] ) : '';
			if ( ! empty( $employer ) ) {
				$query_args['author'] = $employer;
			}

			$statuses = array( 'publish' );
			if ( ! empty( $_POST['filled_only'] ) ) {
				// show only filled jobs
				if ( ! isset( $query_args['meta_query'] ) ) {
					$query_args['meta_query'] = array();
				}

				$query_args['meta_query'] = array_merge(
					$query_args['meta_query'],
					array(
						'relation' => 'AND',
						array(
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
					)
				);
			} else {
				// regular logic
				if ( ! empty( $_POST['hide_filled'] ) ) {
					if ( ! isset( $query_args['meta_query'] ) ) {
						$query_args['meta_query'] = array();
					}

					$query_args['meta_query'] = array_merge(
						$query_args['meta_query'],
						array(
							'relation' => 'AND',
							array(
								'relation' => 'OR',
								array(
									'key'   => 'jb-is-filled',
									'value' => false,
								),
								array(
									'key'   => 'jb-is-filled',
									'value' => 0,
								),
								array(
									'key'     => 'jb-is-filled',
									'compare' => 'NOT EXISTS',
								),
							),
						)
					);
				}

				if ( empty( $_POST['hide_expired'] ) ) {
					$statuses[] = 'jb-expired';
				}
			}

			if ( isset( $_POST['orderby'] ) && 'title' === sanitize_key( $_POST['orderby'] ) ) {
				$orderby = 'title';
			} else {
				$orderby = 'date';
			}
			if ( isset( $_POST['order'] ) && 'ASC' === sanitize_text_field( $_POST['order'] ) ) {
				$order = 'ASC';
			} else {
				$order = 'DESC';
			}

			$query_args = array_merge(
				$query_args,
				array(
					'orderby'     => $orderby,
					'order'       => $order,
					'post_type'   => 'jb-job',
					'post_status' => $statuses,
				)
			);

			if ( ! empty( $_POST['get_previous'] ) ) {
				// first loading with page > 1....to show the jobs above
				$query_args['posts_per_page'] = $this->jobs_per_page * $paged;
				$query_args['offset']         = 0;
			} else {
				$query_args['posts_per_page'] = $this->jobs_per_page;
				$query_args['offset']         = $this->jobs_per_page * ( $paged - 1 );
			}

			if ( ! empty( $_POST['search'] ) ) {
				$search = trim( stripslashes( sanitize_text_field( $_POST['search'] ) ) );
				if ( ! empty( $search ) ) {
					$query_args['s'] = $search;

					if ( ! isset( $query_args['meta_query'] ) ) {
						$query_args['meta_query'] = array();
					}

					$query_args['meta_query'] = array_merge(
						$query_args['meta_query'],
						array(
							'relation' => 'AND',
							array(
								'key'     => 'jb-company-name',
								'value'   => $search,
								'compare' => 'LIKE',
							),
						)
					);
				}
			}

			if ( ! empty( $_POST['location'] ) ) {
				$location = trim( stripslashes( sanitize_text_field( $_POST['location'] ) ) );
				if ( ! empty( $location ) ) {
					if ( ! isset( $query_args['meta_query'] ) ) {
						$query_args['meta_query'] = array();
					}

					$query_args['meta_query'] = array_merge(
						$query_args['meta_query'],
						array(
							'relation' => 'AND',
							array(
								'relation' => 'OR',
								array(
									'key'     => 'jb-location',
									'value'   => $location,
									'compare' => 'LIKE',
								),
								array(
									'key'     => 'jb-location-preferred',
									'value'   => $location,
									'compare' => 'LIKE',
								),
							),
						)
					);
				}
			}

			$key = JB()->options()->get( 'googlemaps-api-key' );
			if ( ! empty( $key ) ) {

				$address_query = array();
				if ( ! empty( $_POST['location-city'] ) ) {
					$address_query[] = array(
						'key'     => 'jb-location-city',
						'value'   => sanitize_text_field( $_POST['location-city'] ),
						'compare' => '=',
					);
				}

				if ( ! empty( $_POST['location-state-short'] ) && ! empty( $_POST['location-state-long'] ) ) {
					$address_query[] = array(
						'relation' => 'OR',
						array(
							'key'     => 'jb-location-state-short',
							'value'   => sanitize_text_field( $_POST['location-state-short'] ),
							'compare' => '=',
						),
						array(
							'key'     => 'jb-location-state-long',
							'value'   => sanitize_text_field( $_POST['location-state-long'] ),
							'compare' => '=',
						),
					);
				}

				if ( ! empty( $_POST['location-country-short'] ) && ! empty( $_POST['location-country-long'] ) ) {
					$address_query[] = array(
						'relation' => 'OR',
						array(
							'key'     => 'jb-location-country-short',
							'value'   => sanitize_text_field( $_POST['location-country-short'] ),
							'compare' => '=',
						),
						array(
							'key'     => 'jb-location-country-long',
							'value'   => sanitize_text_field( $_POST['location-country-long'] ),
							'compare' => '=',
						),
					);
				}

				if ( ! empty( $address_query ) ) {
					$address_query['relation'] = 'AND';

					if ( ! isset( $query_args['meta_query'] ) ) {
						$query_args['meta_query'] = array();
					}

					$query_args['meta_query'] = array_merge( $query_args['meta_query'], array( $address_query ) );
				}
			}

			$remote_only = ( isset( $_POST['remote_only'] ) && (bool) $_POST['remote_only'] );
			if ( $remote_only ) {

				if ( ! isset( $query_args['meta_query'] ) ) {
					$query_args['meta_query'] = array();
				}

				$query_args['meta_query'] = array_merge(
					$query_args['meta_query'],
					array(
						'relation' => 'AND',
						array(
							'key'     => 'jb-location-type',
							'value'   => '1',
							'compare' => '=',
						),
					)
				);
			}

			$type = ! empty( $_POST['type'] ) ? absint( $_POST['type'] ) : '';
			if ( ! empty( $type ) ) {
				$query_args['tax_query'][] = array(
					'taxonomy' => 'jb-job-type',
					'field'    => 'id',
					'terms'    => $type,
				);
			}

			if ( JB()->options()->get( 'job-categories' ) ) {
				$category = ! empty( $_POST['category'] ) ? absint( $_POST['category'] ) : '';
				if ( ! empty( $category ) ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => 'jb-job-category',
						'field'    => 'id',
						'terms'    => $category,
					);
				}
			}

			add_filter( 'get_meta_sql', array( &$this, 'change_meta_sql' ), 10, 6 );
			add_filter( 'posts_search', array( &$this, 'set_search' ), 10, 2 );
			add_filter( 'posts_where', array( &$this, 'change_where_posts' ), 10, 2 );

			add_filter( 'posts_search_orderby', array( &$this, 'relevance_search' ), 10, 2 );

			$query_args = apply_filters( 'jb_get_jobs_query_args', $query_args );

			$get_posts  = new \WP_Query();
			$jobs_query = $get_posts->query( $query_args );

			remove_filter( 'posts_where', array( &$this, 'change_where_posts' ), 10 );
			remove_filter( 'posts_search', array( &$this, 'set_search' ), 10 );
			remove_filter( 'get_meta_sql', array( &$this, 'change_meta_sql' ), 10 );
			remove_filter( 'posts_search_orderby', array( &$this, 'relevance_search' ), 10 );

			$jobs = array();
			if ( ! empty( $jobs_query ) ) {
				foreach ( $jobs_query as $job_post ) {

					$job_company_data = JB()->common()->job()->get_company_data( $job_post->ID );

					$location      = JB()->common()->job()->get_location( $job_post->ID, true );
					$location_type = get_post_meta( $job_post->ID, 'jb-location-type', true );

					if ( '1' === $location_type && empty( $location ) ) {
						$formatted_location = __( 'Remote', 'jobboardwp' );
					} elseif ( empty( $location ) ) {
						$formatted_location = __( 'Anywhere', 'jobboardwp' );
					} else {
						$formatted_location = $location;
					}

					$data_types = array();
					$types      = wp_get_post_terms(
						$job_post->ID,
						'jb-job-type',
						array(
							'orderby' => 'name',
							'order'   => 'ASC',
						)
					);
					foreach ( $types as $type ) {
						$data_types[] = array(
							'name'     => $type->name,
							'color'    => get_term_meta( $type->term_id, 'jb-color', true ),
							'bg_color' => get_term_meta( $type->term_id, 'jb-background', true ),
						);
					}

					$jobs[] = apply_filters(
						'jb_jobs_job_data_response',
						array(
							'title'     => $job_post->post_title,
							'permalink' => get_permalink( $job_post ),
							'date'      => JB()->common()->job()->get_posted_date( $job_post->ID ),
							'expires'   => JB()->common()->job()->get_expiry_date( $job_post->ID ),
							'company'   => array(
								'name'      => $job_company_data['name'],
								'website'   => $job_company_data['website'],
								'tagline'   => $job_company_data['tagline'],
								'twitter'   => $job_company_data['twitter'],
								'facebook'  => $job_company_data['facebook'],
								'instagram' => $job_company_data['instagram'],
							),
							'logo'      => JB()->common()->job()->get_logo( $job_post->ID ),
							'location'  => $formatted_location,
							'types'     => $data_types,
							'actions'   => array(),
						),
						$job_post
					);
				}
			}

			$response = apply_filters(
				'jb_jobs_list_response',
				array(
					'pagination' => $this->calculate_pagination( $get_posts->found_posts ),
					'jobs'       => $jobs,
				)
			);

			wp_send_json_success( $response );
			// phpcs:enable WordPress.Security.NonceVerification -- already verified here
		}

		/**
		 * Recursive function for building categories tree
		 *
		 * @param array $terms
		 * @param array $children Terms hierarchy
		 * @param int $parent
		 * @param int $level
		 *
		 * @return array
		 */
		public function build_categories_structure( $terms, $children, $parent = 0, $level = 0 ) {
			$structured_terms = array();

			foreach ( $terms as $key => $term ) {
				if ( (int) $term->parent !== $parent ) {
					continue;
				}

				$term->level = $level;

				$structured_terms[ $key ] = $term;

				unset( $terms[ $key ] );

				if ( isset( $children[ $term->term_id ] ) ) {
					$structured_terms = array_merge( $structured_terms, $this->build_categories_structure( $terms, $children, $term->term_id, $level + 1 ) );
				}
			}

			return $structured_terms;
		}


		/**
		 * Getting Job Categories Tree
		 */
		public function get_categories() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );

			$args = apply_filters(
				'jb_get_job_categories_args',
				array(
					'taxonomy'   => 'jb-job-category',
					'hide_empty' => 0,
					'get'        => 'all',
				)
			);

			$terms = get_terms( $args );

			if ( is_taxonomy_hierarchical( 'jb-job-category' ) ) {
				$children = _get_term_hierarchy( 'jb-job-category' );

				$terms = $this->build_categories_structure( $terms, $children );

				foreach ( $terms as $key => $term ) {
					$terms[ $key ]->permalink = get_term_link( $term );
				}
			} else {
				$args = apply_filters(
					'jb_get_job_categories_args',
					array(
						'taxonomy'   => 'jb-job-category',
						'hide_empty' => 0,
						'get'        => 'all',
					)
				);

				$terms = get_terms( $args );

				foreach ( $terms as $key => $term ) {
					$terms[ $key ]->level     = 0;
					$terms[ $key ]->permalink = get_term_link( $term );
				}
			}

			$response = apply_filters(
				'jb_get_job_categories_response',
				array(
					'terms' => $terms,
					'total' => count( $terms ),
				)
			);

			wp_send_json_success( $response );
		}


		/**
		 * AJAX handler for job delete
		 *
		 * @since 1.0
		 */
		public function delete_job() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );
			// phpcs:disable WordPress.Security.NonceVerification -- already verified here

			if ( empty( $_POST['job_id'] ) ) {
				wp_send_json_error( __( 'Wrong job ID.', 'jobboardwp' ) );
			}

			$job_id = absint( $_POST['job_id'] );

			$job = get_post( $job_id );
			if ( is_wp_error( $job ) || empty( $job ) ) {
				wp_send_json_error( __( 'Wrong job.', 'jobboardwp' ) );
			}

			if ( get_current_user_id() !== (int) $job->post_author ) {
				wp_send_json_error( __( 'You haven\'t ability to delete this job.', 'jobboardwp' ) );
			}

			$result = wp_delete_post( $job_id, true );
			if ( ! empty( $result ) ) {
				do_action( 'jb-after-job-delete', $job_id, $result ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

				wp_send_json_success();
			} else {
				wp_send_json_error( __( 'Something went wrong.', 'jobboardwp' ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification -- already verified here
		}


		/**
		 * AJAX handler for making a job filled
		 *
		 * @since 1.0
		 */
		public function fill_job() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );
			// phpcs:disable WordPress.Security.NonceVerification -- already verified here
			if ( empty( $_POST['job_id'] ) ) {
				wp_send_json_error( __( 'Wrong job ID', 'jobboardwp' ) );
			}

			$job_id = absint( $_POST['job_id'] );

			$job = get_post( $job_id );
			if ( is_wp_error( $job ) || empty( $job ) ) {
				wp_send_json_error( __( 'Wrong job', 'jobboardwp' ) );
			}

			if ( get_current_user_id() !== (int) $job->post_author ) {
				wp_send_json_error( __( 'You haven\'t ability to fill this job.', 'jobboardwp' ) );
			}

			if ( JB()->common()->job()->is_filled( $job_id ) ) {
				wp_send_json_error( __( 'Job is already filled.', 'jobboardwp' ) );
			}

			update_post_meta( $job_id, 'jb-is-filled', true );

			if ( JB()->common()->job()->is_filled( $job_id ) ) {
				$job = get_post( $job_id );

				$jobs   = array();
				$jobs[] = $this->get_job_data( $job );

				do_action( 'jb_fill_job', $job_id, $job );

				wp_send_json_success( array( 'jobs' => $jobs ) );
			} else {
				wp_send_json_error( __( 'Something went wrong.', 'jobboardwp' ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification -- already verified here
		}


		/**
		 * AJAX handler for making a job unfilled
		 *
		 * @since 1.0
		 */
		public function unfill_job() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );
			// phpcs:disable WordPress.Security.NonceVerification -- already verified here
			if ( empty( $_POST['job_id'] ) ) {
				wp_send_json_error( __( 'Wrong job ID', 'jobboardwp' ) );
			}

			$job_id = absint( $_POST['job_id'] );

			$job = get_post( $job_id );
			if ( is_wp_error( $job ) || empty( $job ) ) {
				wp_send_json_error( __( 'Wrong job', 'jobboardwp' ) );
			}

			if ( get_current_user_id() !== (int) $job->post_author ) {
				wp_send_json_error( __( 'You haven\'t ability to un-fill this job.', 'jobboardwp' ) );
			}

			if ( ! JB()->common()->job()->is_filled( $job_id ) ) {
				wp_send_json_error( __( 'Job isn\'t filled yet.', 'jobboardwp' ) );
			}

			update_post_meta( $job_id, 'jb-is-filled', false );

			if ( ! JB()->common()->job()->is_filled( $job_id ) ) {
				$job = get_post( $job_id );

				$jobs   = array();
				$jobs[] = $this->get_job_data( $job );

				do_action( 'jb_unfill_job', $job_id, $job );

				wp_send_json_success( array( 'jobs' => $jobs ) );
			} else {
				wp_send_json_error( __( 'Something went wrong.', 'jobboardwp' ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification -- already verified here
		}


		/**
		 * Prepare job data for AJAX response
		 *
		 * @param \WP_Post $job_post
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function get_job_data( $job_post ) {
			if ( 'publish' !== $job_post->post_status ) {
				$status_label = JB()->common()->job()->get_status( $job_post->ID );
				$status       = 'jb-preview' === $job_post->post_status ? 'draft' : $job_post->post_status;
			} else {
				$status_label = JB()->common()->job()->is_filled( $job_post->ID ) ? __( 'Filled', 'jobboardwp' ) : __( 'Not-filled', 'jobboardwp' );
				$status       = JB()->common()->job()->is_filled( $job_post->ID ) ? 'filled' : 'not-filled';
			}

			return apply_filters(
				'jb_job_dashboard_job_data_response',
				array(
					'id'           => $job_post->ID,
					'title'        => $job_post->post_title,
					'permalink'    => get_permalink( $job_post ),
					'is_published' => 'publish' === $job_post->post_status,
					'status_label' => $status_label,
					'status'       => $status,
					'date'         => JB()->common()->job()->get_posted_date( $job_post->ID ),
					'expires'      => JB()->common()->job()->get_expiry_date( $job_post->ID ),
					'actions'      => JB()->common()->job()->get_actions( $job_post->ID ),
				),
				$job_post
			);
		}


		/**
		 * AJAX handler for getting employer's jobs
		 *
		 * @since 1.0
		 */
		public function get_employer_jobs() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );

			$employer = get_current_user_id();

			$get_posts = new \WP_Query();

			$args = array(
				'author'         => $employer,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_type'      => 'jb-job',
				'post_status'    => array( 'publish', 'draft', 'pending', 'jb-preview', 'jb-expired' ),
				'posts_per_page' => -1,
			);

			$args = apply_filters( 'jb_get_employer_jobs_args', $args );

			$jobs_query = $get_posts->query( $args );

			$jobs = array();
			if ( ! empty( $jobs_query ) ) {
				foreach ( $jobs_query as $job_post ) {
					$jobs[] = $this->get_job_data( $job_post );
				}
			}

			$response = apply_filters(
				'jb_job_dashboard_response',
				array(
					'jobs' => $jobs,
				)
			);

			wp_send_json_success( $response );
		}


		/**
		 * Get data array for pagination
		 *
		 * @param int $total_jobs
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function calculate_pagination( $total_jobs ) {
			// phpcs:disable WordPress.Security.NonceVerification -- already verified here
			$current_page = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

			$total_pages = ceil( $total_jobs / $this->jobs_per_page );

			if ( ! empty( $total_pages ) ) {
				$index1 = 0 - ( $current_page - 2 ) + 1;
				$to     = $current_page + 2;
				if ( $index1 > 0 ) {
					$to += $index1;
				}

				$index2 = $total_pages - ( $current_page + 2 );
				$from   = $current_page - 2;
				if ( $index2 < 0 ) {
					$from += $index2;
				}

				$pages_to_show = range(
					( $from > 0 ) ? $from : 1,
					( $to <= $total_pages ) ? $to : $total_pages
				);
			}

			return apply_filters(
				'jb_jobs_dashboard_calculate_pagination_result',
				array(
					'pages_to_show' => ( ! empty( $pages_to_show ) && count( $pages_to_show ) > 1 ) ? array_values( $pages_to_show ) : array(),
					'current_page'  => $current_page,
					'total_pages'   => $total_pages,
					'total_jobs'    => $total_jobs,
				)
			);
			// phpcs:enable WordPress.Security.NonceVerification -- already verified here
		}
	}
}
