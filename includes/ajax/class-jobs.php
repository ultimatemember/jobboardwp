<?php
namespace jb\ajax;

use WP_Query;

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
			add_action( 'wp_loaded', array( $this, 'init_variables' ) );
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
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function change_where_posts( $where ) {
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
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function set_search( $search ) {
			$this->search = $search;
			return $search;
		}

		/**
		 * Change mySQL meta query join attribute
		 * for search by the company name
		 *
		 * @param array $sql Array containing the query's JOIN and WHERE clauses.
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function change_meta_sql( $sql ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! empty( $_POST['search'] ) ) {
				global $wpdb;
				$search = sanitize_text_field( wp_unslash( $_POST['search'] ) ); // phpcs:ignore WordPress.Security.NonceVerification -- already verified here
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

						preg_match( '~(?<=\{)(.*?)(?=\})~', $search_meta, $matches, PREG_OFFSET_CAPTURE, 0 );

						// workaround for standard mySQL hashes which are used by $wpdb->prepare instead of the %symbol
						// sometimes it breaks error for strings like that wp_postmeta.meta_value LIKE '{12f209b48a89eeab33424902879d05d503f251ca8812dde03b59484a2991dc74}AMS{12f209b48a89eeab33424902879d05d503f251ca8812dde03b59484a2991dc74}'
						// {12f209b48a89eeab33424902879d05d503f251ca8812dde03b59484a2991dc74} isn't applied by the `preg_replace()` below
						if ( $matches[0][0] ) {
							$search_meta  = str_replace(
								array(
									'{' . $matches[0][0] . '}',
									'/', // it's required for line 161 - preg_replace
								),
								array(
									'#%&',
									'\/', // it's required for line 161 - preg_replace
								),
								$search_meta
							);
							$search_query = str_replace(
								array(
									'{' . $matches[0][0] . '}',
									'/', // it's required for line 161 - preg_replace
								),
								array(
									'#%&',
									'\/', // it's required for line 161 - preg_replace
								),
								$search_query
							);
							$sql['where'] = str_replace( '{' . $matches[0][0] . '}', '#%&', $sql['where'] );
						}

						// phpcs:disable Squiz.Strings.DoubleQuoteUsage.NotRequired -- don't remove regex indentation
						$sql['where'] = preg_replace(
							"/\( (" . $meta_join_for_search . ".meta_key = 'jb-company-name' AND " . $meta_join_for_search . ".meta_value LIKE " . $search_meta . ") \)/im",
							"( $1 " . $search_query . " )",
							$sql['where'],
							1
						);
						if ( $matches[0][0] && ! empty( $sql['where'] ) ) {
							$sql['where'] = str_replace( '#%&', '{' . $matches[0][0] . '}', $sql['where'] );
						}
						// phpcs:enable Squiz.Strings.DoubleQuoteUsage.NotRequired -- don't remove regex indentation
					}
				}
			}
			if ( JB()->options()->get( 'job-salary' ) ) {
				if ( ! empty( $_POST['salary'] ) ) {
					global $wpdb;
					$salary = explode( '-', wp_unslash( $_POST['salary'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below via absint per value
					$min    = absint( $salary[0] );
					$max    = absint( $salary[1] );

					$sql['join'] .= "
						LEFT JOIN $wpdb->postmeta AS jb_salary_type ON ( $wpdb->posts.ID = jb_salary_type.post_id AND jb_salary_type.meta_key = 'jb-salary-type' )
						LEFT JOIN $wpdb->postmeta AS jb_amount_type ON ( $wpdb->posts.ID = jb_amount_type.post_id AND jb_amount_type.meta_key = 'jb-salary-amount-type' )
						LEFT JOIN $wpdb->postmeta AS jb_amount ON ( $wpdb->posts.ID = jb_amount.post_id AND jb_amount.meta_key = 'jb-salary-amount' )
						LEFT JOIN $wpdb->postmeta AS jb_min_amount ON ( $wpdb->posts.ID = jb_min_amount.post_id AND jb_min_amount.meta_key = 'jb-salary-min-amount' )
						LEFT JOIN $wpdb->postmeta AS jb_max_amount ON ( $wpdb->posts.ID = jb_max_amount.post_id AND jb_max_amount.meta_key = 'jb-salary-max-amount' )
					";

					$sql['where'] .= " AND (
						(jb_salary_type.meta_key IS NOT NULL) AND
						(jb_salary_type.meta_value != '') AND
						(jb_salary_type.meta_value IN ('fixed', 'recurring') AND jb_amount_type.meta_value = 'numeric' AND jb_amount.meta_value BETWEEN $min AND $max) OR
						(jb_salary_type.meta_value IN ('fixed', 'recurring') AND jb_amount_type.meta_value = 'range' AND ( jb_min_amount.meta_value BETWEEN $min AND $max OR jb_max_amount.meta_value BETWEEN $min AND $max) )
					)";
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification

			return $sql;
		}

		/**
		 * Searching by relevance
		 *
		 * @param string $search_orderby
		 * @param WP_Query $query
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function relevance_search( /** @noinspection PhpUnusedParameterInspection */$search_orderby, $query ) {
			global $wpdb;

			$orderby_array = array();

			// phpcs:disable WordPress.Security.NonceVerification -- already verified here
			$search     = ! empty( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
			$meta_value = '%' . $wpdb->esc_like( $search ) . '%';

			// Sentence match in 'post_title'.
			$new_search_orderby = '';
			if ( $meta_value ) {
				$new_search_orderby .= $wpdb->prepare( "WHEN {$wpdb->posts}.post_title LIKE %s THEN 1 ", $meta_value );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $this->company_name_meta is static variable
				$new_search_orderby .= $wpdb->prepare( "WHEN {$this->company_name_meta}.meta_value LIKE %s THEN 2 ", $meta_value );
				$new_search_orderby .= $wpdb->prepare( "WHEN {$wpdb->posts}.post_content LIKE %s THEN 3 ", $meta_value );
			}

			$meta_clauses    = $query->meta_query->get_clauses();
			$meta_clause     = $meta_clauses['featured'];
			$orderby_array[] = "CAST({$meta_clause['alias']}.meta_value AS {$meta_clause['cast']}) DESC";

			if ( ! empty( $new_search_orderby ) ) {
				$orderby_array[] = '(CASE ' . $new_search_orderby . 'ELSE 4 END)';
			}

			if ( isset( $_POST['orderby'] ) && 'title' === sanitize_key( wp_unslash( $_POST['orderby'] ) ) ) {
				$orderby = 'post_title';
			} else {
				$orderby = 'post_date';
			}
			if ( isset( $_POST['order'] ) && 'ASC' === sanitize_text_field( wp_unslash( $_POST['order'] ) ) ) {
				$order = 'ASC';
			} else {
				$order = 'DESC';
			}
			// phpcs:enable WordPress.Security.NonceVerification -- already verified here
			$orderby_array[] = "{$wpdb->posts}.{$orderby} {$order}";

			return implode( ', ', $orderby_array );
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

			$query_args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					'featured' => array(
						'key'     => 'jb-featured-order',
						'compare' => 'NOT EXISTS',
						'type'    => 'NUMERIC',
					),
					array(
						'key'     => 'jb-featured-order',
						'compare' => 'EXISTS',
						'type'    => 'NUMERIC',
					),
				),
			);

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
					$filled_ids = get_posts( $args );

					if ( ! empty( $filled_ids ) ) {
						$post__not_in               = ! empty( $query_args['post__not_in'] ) ? $query_args['post__not_in'] : array();
						$query_args['post__not_in'] = array_merge( $post__not_in, $filled_ids );
					}
				}

				if ( empty( $_POST['hide_expired'] ) ) {
					$statuses[] = 'jb-expired';
				}
			}

			if ( isset( $_POST['orderby'] ) && 'title' === sanitize_key( wp_unslash( $_POST['orderby'] ) ) ) {
				$orderby = 'title';
			} else {
				$orderby = 'date';
			}
			if ( isset( $_POST['order'] ) && 'ASC' === sanitize_text_field( wp_unslash( $_POST['order'] ) ) ) {
				$order = 'ASC';
			} else {
				$order = 'DESC';
			}

			$query_args = array_merge(
				$query_args,
				array(
					'orderby'     => array(
						'featured' => 'DESC',
						$orderby   => $order,
					),
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
				$search = sanitize_text_field( wp_unslash( $_POST['search'] ) );
				if ( ! empty( $search ) ) {
					$query_args['s'] = $search;
					// if search there is 'posts_search_orderby' hook used and order handler is moved to `$this->relevance_search()` function
					$query_args['orderby'] = false;

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
				$location = sanitize_text_field( wp_unslash( $_POST['location'] ) );
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
						'value'   => sanitize_text_field( wp_unslash( $_POST['location-city'] ) ),
						'compare' => '=',
					);
				}

				if ( ! empty( $_POST['location-state-short'] ) && ! empty( $_POST['location-state-long'] ) ) {
					$address_query[] = array(
						'relation' => 'OR',
						array(
							'key'     => 'jb-location-state-short',
							'value'   => sanitize_text_field( wp_unslash( $_POST['location-state-short'] ) ),
							'compare' => '=',
						),
						array(
							'key'     => 'jb-location-state-long',
							'value'   => sanitize_text_field( wp_unslash( $_POST['location-state-long'] ) ),
							'compare' => '=',
						),
					);
				}

				if ( ! empty( $_POST['location-country-short'] ) && ! empty( $_POST['location-country-long'] ) ) {
					$address_query[] = array(
						'relation' => 'OR',
						array(
							'key'     => 'jb-location-country-short',
							'value'   => sanitize_text_field( wp_unslash( $_POST['location-country-short'] ) ),
							'compare' => '=',
						),
						array(
							'key'     => 'jb-location-country-long',
							'value'   => sanitize_text_field( wp_unslash( $_POST['location-country-long'] ) ),
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

			$types = array();
			if ( ! empty( $_POST['type'] ) ) {
				$types = array_map( 'absint', array_map( 'trim', explode( ',', wp_unslash( $_POST['type'] ) ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- array_map ok
			}
			if ( ! empty( $types ) ) {
				$query_args['tax_query'][] = array(
					'taxonomy' => 'jb-job-type',
					'field'    => 'id',
					'terms'    => $types,
				);
			}

			if ( JB()->options()->get( 'job-categories' ) ) {
				$categories = array();
				if ( ! empty( $_POST['category'] ) ) {
					$categories = array_map( 'absint', array_map( 'trim', explode( ',', wp_unslash( $_POST['category'] ) ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- array_map ok
				}
				if ( ! empty( $categories ) ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => 'jb-job-category',
						'field'    => 'id',
						'terms'    => $categories,
					);
				}
			}

			add_filter( 'get_meta_sql', array( &$this, 'change_meta_sql' ) );
			add_filter( 'posts_search', array( &$this, 'set_search' ) );
			add_filter( 'posts_where', array( &$this, 'change_where_posts' ) );

			add_filter( 'posts_search_orderby', array( &$this, 'relevance_search' ), 10, 2 );

			/**
			 * Filters the WP_Query arguments for getting jobs in the Job List.
			 *
			 * @since 1.0
			 * @hook jb_get_jobs_query_args
			 *
			 * @param {array} $query_args Arguments for WP_Query.
			 *
			 * @return {array} Arguments for WP_Query.
			 */
			$query_args = apply_filters( 'jb_get_jobs_query_args', $query_args );

			$get_posts  = new WP_Query();
			$jobs_query = $get_posts->query( $query_args );

			remove_filter( 'posts_where', array( &$this, 'change_where_posts' ) );
			remove_filter( 'posts_search', array( &$this, 'set_search' ) );
			remove_filter( 'get_meta_sql', array( &$this, 'change_meta_sql' ) );
			remove_filter( 'posts_search_orderby', array( &$this, 'relevance_search' ) );

			/**
			 * Fires after Jobs List query get results.
			 *
			 * @since 1.2.2
			 * @hook jb_after_get_jobs_query
			 *
			 * @param {WP_Query} $jobs_query WP_Query for getting Jobs in the list.
			 */
			do_action( 'jb_after_get_jobs_query', $jobs_query );

			$jobs = array();
			if ( ! empty( $jobs_query ) ) {
				foreach ( $jobs_query as $job_post ) {

					$job_company_data = JB()->common()->job()->get_company_data( $job_post->ID );

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

					$title = esc_html( get_the_title( $job_post ) );
					$title = ! empty( $title ) ? $title : esc_html__( '(no title)', 'jobboardwp' );

					$job_data = array(
						'title'     => $title,
						'permalink' => get_permalink( $job_post ),
						'date'      => esc_html( JB()->common()->job()->get_posted_date( $job_post->ID ) ),
						'expires'   => esc_html( JB()->common()->job()->get_expiry_date( $job_post->ID ) ),
						'company'   => array(
							'name'      => esc_html( $job_company_data['name'] ),
							'website'   => esc_url_raw( $job_company_data['website'] ),
							'tagline'   => esc_html( $job_company_data['tagline'] ),
							'twitter'   => esc_html( $job_company_data['twitter'] ),
							'facebook'  => esc_html( $job_company_data['facebook'] ),
							'instagram' => esc_html( $job_company_data['instagram'] ),
						),
						'logo'      => JB()->common()->job()->get_logo( $job_post->ID ),
						'location'  => wp_kses( JB()->common()->job()->get_location_link( $job_post->ID ), JB()->get_allowed_html( 'templates' ) ),
						'types'     => $data_types,
						'featured'  => (bool) JB()->common()->job()->is_featured( $job_post->ID ),
						'actions'   => array(),
					);

					if ( JB()->options()->get( 'job-categories' ) ) {
						$job_data['category'] = wp_kses( JB()->common()->job()->get_job_category( $job_post->ID ), JB()->get_allowed_html( 'templates' ) );
					}

					$amount_output = JB()->common()->job()->get_formatted_salary( $job_post->ID );
					if ( '' !== $amount_output ) {
						$job_data['salary'] = esc_html( $amount_output );
					}

					/**
					 * Filters the job data after getting it from WP_Query and prepare it for AJAX response. The referrer is Jobs List shortcode AJAX request.
					 *
					 * @since 1.0
					 * @hook jb_jobs_job_data_response
					 *
					 * @param {array}   $job_data Job data prepared for AJAX response.
					 * @param {WP_Post} $job_post Job Post object.
					 *
					 * @return {array} Job data prepared for AJAX response.
					 */
					$jobs[] = apply_filters( 'jb_jobs_job_data_response', $job_data, $job_post );
				}
			}

			$hide_logo      = ! empty( $_POST['no_logo'] ) ? (bool) $_POST['no_logo'] : false;
			$hide_job_types = ! empty( $_POST['hide_job_types'] ) ? (bool) $_POST['hide_job_types'] : false;

			/**
			 * Filters the AJAX response when getting jobs for the jobs list.
			 *
			 * @since 1.0
			 * @hook jb_jobs_list_response
			 *
			 * @param {array} $response AJAX response.
			 *
			 * @return {array} AJAX response.
			 */
			$response = apply_filters(
				'jb_jobs_list_response',
				array(
					'pagination'     => $this->calculate_pagination( $get_posts->found_posts ),
					'jobs'           => $jobs,
					'hide_logo'      => $hide_logo,
					'hide_job_types' => $hide_job_types,
				)
			);

			wp_send_json_success( $response );
			// phpcs:enable WordPress.Security.NonceVerification -- already verified here
		}

		public function count_jobs( $term_id ) {
			$hide_filled  = JB()->options()->get( 'jobs-list-hide-filled' );
			$hide_expired = JB()->options()->get( 'jobs-list-hide-expired' );

			$query_args = array(
				'post_type'      => 'jb-job',
				'posts_per_page' => -1,
				'meta_query'     => array(),
				'tax_query'      => array(
					array(
						'taxonomy'         => 'jb-job-category',
						'field'            => 'id',
						'terms'            => $term_id,
						'include_children' => false,
					),
				),
			);

			if ( $hide_filled && $hide_expired ) {
				$query_args['post_status'] = array( 'publish' );
				$query_args['meta_query']  = array_merge(
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
			} elseif ( $hide_filled && ! $hide_expired ) {
				$query_args['post_status'] = array(
					'publish',
					'jb-expired',
				);
				$query_args['meta_query']  = array_merge(
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
			} elseif ( ! $hide_filled && $hide_expired ) {
				$query_args['post_status'] = array( 'publish' );
			} else {
				$query_args['post_status'] = array(
					'publish',
					'jb-expired',
				);
			}

			$query = new WP_Query( $query_args );

			return $query->found_posts;
		}

		/**
		 * Getting Job Categories Tree
		 */
		public function get_categories() {
			JB()->ajax()->check_nonce( 'jb-frontend-nonce' );

			/**
			 * Filters the `get_terms()` arguments when handle AJAX request for getting Job Categories.
			 *
			 * @since 1.1.0
			 * @hook jb_get_job_categories_args
			 *
			 * @param {array} $args array of the arguments. See the list of all arguments https://developer.wordpress.org/reference/classes/wp_term_query/__construct/#parameters
			 *
			 * @return {array} `get_terms()` arguments.
			 */
			$args = apply_filters(
				'jb_get_job_categories_args',
				array(
					'taxonomy'   => 'jb-job-category',
					'hide_empty' => 0,
					'get'        => 'all',
				)
			);

			$terms = get_terms( $args );

			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				wp_send_json_error( __( 'Invalid taxonomy registration', 'jobboardwp' ) );
			}

			if ( is_taxonomy_hierarchical( 'jb-job-category' ) ) {
				$children = _get_term_hierarchy( 'jb-job-category' );

				$terms = JB()->common()->job()->prepare_categories_options( $terms, $children );

				foreach ( $terms as $key => $term ) {
					$terms[ $key ]->count     = $this->count_jobs( $term->term_id );
					$terms[ $key ]->permalink = get_term_link( $term );
				}
			} else {
				foreach ( $terms as $key => $term ) {
					$terms[ $key ]->count     = $this->count_jobs( $term->term_id );
					$terms[ $key ]->level     = 0;
					$terms[ $key ]->permalink = get_term_link( $term );
				}
			}

			/**
			 * Filters the AJAX response when getting job categories list.
			 *
			 * @since 1.1.0
			 * @hook jb_get_job_categories_response
			 *
			 * @param {array} $response AJAX response.
			 *
			 * @return {array} AJAX response.
			 */
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
				/**
				 * Fires after Job has been deleted.
				 *
				 * @since 1.1.0
				 * @hook jb-after-job-delete
				 *
				 * @param {int}     $job_id    Deleted job ID.
				 * @param {WP_Post} $post_data The deleted job's post object.
				 */
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

				/**
				 * Fires after Job has been filled.
				 *
				 * @since 1.1.0
				 * @hook jb_fill_job
				 *
				 * @param {int}     $job_id Job ID.
				 * @param {WP_Post} $job    The Job's post object.
				 */
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

				/**
				 * Fires after Job has been unfilled.
				 *
				 * @since 1.1.0
				 * @hook jb_unfill_job
				 *
				 * @param {int}     $job_id Job ID.
				 * @param {WP_Post} $job    The Job's post object.
				 */
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

			$title = esc_html( get_the_title( $job_post ) );
			$title = ! empty( $title ) ? $title : esc_html__( '(no title)', 'jobboardwp' );

			/**
			 * Filters the job data after getting it from WP_Query and prepare it for AJAX response. The referrer is Jobs Dashboard shortcode AJAX request.
			 *
			 * @since 1.0
			 * @hook jb_job_dashboard_job_data_response
			 *
			 * @param {array}   $job_data Job data prepared for AJAX response.
			 * @param {WP_Post} $job_post Job Post object.
			 *
			 * @return {array} Job data prepared for AJAX response.
			 */
			return apply_filters(
				'jb_job_dashboard_job_data_response',
				array(
					'id'           => $job_post->ID,
					'title'        => $title,
					'permalink'    => get_permalink( $job_post ),
					'is_published' => 'publish' === $job_post->post_status,
					'status_label' => $status_label,
					'status'       => $status,
					'date'         => esc_html( JB()->common()->job()->get_posted_date( $job_post->ID ) ),
					'expires'      => esc_html( JB()->common()->job()->get_expiry_date( $job_post->ID ) ),
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

			$get_posts = new WP_Query();

			$args = array(
				'author'         => $employer,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_type'      => 'jb-job',
				'post_status'    => array( 'publish', 'draft', 'pending', 'jb-preview', 'jb-expired' ),
				'posts_per_page' => -1,
			);

			/**
			 * Filters the WP_Query arguments for getting jobs in the Jobs Dashboard.
			 *
			 * @since 1.0
			 * @hook jb_get_employer_jobs_args
			 *
			 * @param {array} $args Arguments for WP_Query.
			 *
			 * @return {array} Arguments for WP_Query.
			 */
			$args = apply_filters( 'jb_get_employer_jobs_args', $args );

			$jobs_query = $get_posts->query( $args );

			$jobs = array();
			if ( ! empty( $jobs_query ) ) {
				foreach ( $jobs_query as $job_post ) {
					$jobs[] = $this->get_job_data( $job_post );
				}
			}

			/**
			 * Filters the AJAX response when getting jobs list in jobs dashboard.
			 *
			 * @since 1.1.0
			 * @hook jb_job_dashboard_response
			 *
			 * @param {array} $response AJAX response.
			 *
			 * @return {array} AJAX response.
			 */
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

			/**
			 * Filters the pagination results for the jobs list.
			 *
			 * @since 1.1.1
			 * @hook jb_jobs_list_calculate_pagination_result
			 *
			 * @param {array} $result Pagination results.
			 *
			 * @return {array} Pagination results.
			 */
			return apply_filters(
				'jb_jobs_list_calculate_pagination_result',
				array(
					'pages_to_show' => ( ! empty( $pages_to_show ) && count( $pages_to_show ) > 1 ) ? array_values( $pages_to_show ) : array(),
					'current_page'  => $current_page,
					'total_pages'   => $total_pages,
					'total_jobs'    => $total_jobs,
				)
			);
			// phpcs:enable WordPress.Security.NonceVerification -- already verified here
		}


		/**
		 * AJAX handler for validate job data on save through wp-admin editor
		 *
		 * @since 1.0
		 */
		public function validate_job() {
			JB()->ajax()->check_nonce( 'jb-backend-nonce' );

			// phpcs:disable WordPress.Security.NonceVerification -- already verified here
			if ( empty( $_POST['data'] ) ) {
				wp_send_json_error( __( 'Wrong Data', 'jobboardwp' ) );
			}

			$errors = array();

			if ( empty( $_POST['description'] ) ) {
				$errors['empty'][] = 'description';
			} else {
				$description = wp_kses_post( wp_unslash( $_POST['description'] ) );
				if ( empty( $description ) ) {
					$errors['empty'][] = 'description';
				}
			}

			if ( empty( $_POST['data']['jb-application-contact'] ) ) {
				$errors['empty'][] = 'jb-application-contact';
			} else {
				$method = JB()->options()->get( 'application-method' );
				if ( 'email' === $method ) {
					$app_contact = sanitize_email( wp_unslash( $_POST['data']['jb-application-contact'] ) );
					if ( ! is_email( $app_contact ) ) {
						$errors['wrong'][] = 'jb-application-contact';
					}
				} elseif ( 'url' === $method ) {
					$app_contact = sanitize_text_field( wp_unslash( $_POST['data']['jb-application-contact'] ) );
					if ( false === strpos( $app_contact, 'http:' ) && false === strpos( $app_contact, 'https:' ) ) {
						$app_contact = 'https://' . $app_contact;
					}

					if ( is_email( $app_contact ) || ! JB()->common()->job()->validate_url( $app_contact ) ) {
						$errors['wrong'][] = 'jb-application-contact';
					}
				} else {
					$app_contact = sanitize_email( wp_unslash( $_POST['data']['jb-application-contact'] ) );
					if ( ! is_email( $app_contact ) ) {
						$app_contact = sanitize_text_field( wp_unslash( $_POST['data']['jb-application-contact'] ) );
						// Prefix http if needed.
						if ( false === strpos( $app_contact, 'http:' ) && false === strpos( $app_contact, 'https:' ) ) {
							$app_contact = 'https://' . $app_contact;
						}
					}
					if ( ! is_email( $app_contact ) && ! JB()->common()->job()->validate_url( $app_contact ) ) {
						$errors['wrong'][] = 'jb-application-contact';
					}
				}
			}

			if ( ! isset( $_POST['data']['jb-location-type'] ) ) {
				$errors['wrong'][] = 'jb-location-type';
			} else {
				$location_type = sanitize_text_field( wp_unslash( $_POST['data']['jb-location-type'] ) );
				if ( '0' === $location_type ) {
					if ( empty( $_POST['data']['jb-location'] ) ) {
						$errors['empty'][] = 'jb-location';
					} else {
						$location = sanitize_text_field( wp_unslash( $_POST['data']['jb-location'] ) );
						if ( empty( $location ) ) {
							$errors['empty'][] = 'jb-location';
						}
					}
				}
			}

			if ( empty( $_POST['data']['jb-company-name'] ) ) {
				$errors['empty'][] = 'jb-company-name';
			} else {
				$company_name = sanitize_text_field( wp_unslash( $_POST['data']['jb-company-name'] ) );
				if ( empty( $company_name ) ) {
					$errors['empty'][] = 'jb-company-name';
				}
			}

			if ( JB()->options()->get( 'required-job-type' ) ) {
				if ( empty( $_POST['data']['jb-job-type'] ) ) {
					$errors['empty'][] = 'jb-job-type';
				} else {
					$job_type = absint( $_POST['data']['jb-job-type'] );
					if ( empty( $job_type ) ) {
						$errors['empty'][] = 'jb-job-type';
					}
				}
			}

			if ( JB()->options()->get( 'job-salary' ) ) {
				if ( empty( $_POST['data']['jb-salary-type'] ) && JB()->options()->get( 'required-job-salary' ) ) {
					$errors['empty'][] = 'jb-salary-type';
				}

				if ( ! empty( $_POST['data']['jb-salary-type'] ) ) {
					if ( empty( $_POST['data']['jb-salary-amount-type'] ) ) {
						$errors['empty'][] = 'jb-salary-amount-type';
					} elseif ( 'numeric' === $_POST['data']['jb-salary-amount-type'] ) {
						if ( empty( $_POST['data']['jb-salary-amount'] ) ) {
							$errors['empty'][] = 'jb-salary-amount';
						} elseif ( ! is_numeric( $_POST['data']['jb-salary-amount'] ) ) {
							$errors['wrong'][] = 'jb-salary-amount';
						}
					} elseif ( 'range' === $_POST['data']['jb-salary-amount-type'] ) {
						if ( empty( $_POST['data']['jb-salary-min-amount'] ) && empty( $_POST['data']['jb-salary-max-amount'] ) ) {
							$errors['empty'][] = 'jb-salary-min-amount';
						} else {
							if ( ! is_numeric( $_POST['data']['jb-salary-min-amount'] ) ) {
								$errors['wrong'][] = 'jb-salary-min-amount';
							} elseif ( 0 !== absint( $_POST['data']['jb-salary-max-amount'] ) && absint( $_POST['data']['jb-salary-min-amount'] ) >= absint( $_POST['data']['jb-salary-max-amount'] ) ) {
								$errors['wrong'][] = 'jb-salary-min-amount';
							}

							if ( ! is_numeric( $_POST['data']['jb-salary-max-amount'] ) ) {
								$errors['wrong'][] = 'jb-salary-max-amount';
							} elseif ( 0 !== absint( $_POST['data']['jb-salary-max-amount'] ) && absint( $_POST['data']['jb-salary-max-amount'] ) <= absint( $_POST['data']['jb-salary-min-amount'] ) ) {
								$errors['wrong'][] = 'jb-salary-max-amount';
							}
						}
					}

					if ( 'recurring' === $_POST['data']['jb-salary-type'] && empty( $_POST['data']['jb-salary-period'] ) ) {
						$errors['empty'][] = 'jb-salary-period';
					}
				}
			}

			/**
			 * Filters job post validation errors.
			 *
			 * Note: You may use this hook for adding your custom validations or remove existed while job saved through wp-admin editor.
			 *
			 * Format for the errors: key = 'empty' - required field is empty
			 *                        key = 'wrong' - invalid format if the field
			 *                        value = array( '{field_id}' ) - user field ID that you used for the registration the field on the form.
			 *
			 * @since 1.2.2
			 * @hook jb_ajax_job_validation_errors
			 *
			 * @param {array} $errors Errors list. If there aren't any errors it's empty array.
			 *
			 * @return {array} Errors list.
			 */
			$errors = apply_filters( 'jb_ajax_job_validation_errors', $errors );

			if ( ! empty( $errors ) ) {
				// add notice text
				$errors['notice'][] = __( 'Wrong Job\'s data', 'jobboardwp' );
				if ( empty( $description ) ) {
					$errors['notice'][] = __( ' Description is required', 'jobboardwp' );
				}
				wp_send_json_success( $errors );
			} else {
				wp_send_json_success( array( 'valid' => 1 ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}
	}
}
