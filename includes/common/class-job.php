<?php namespace jb\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\common\Job' ) ) {


	/**
	 * Class Job
	 *
	 * @package jb\common
	 */
	class Job {


		/**
		 * Job constructor.
		 */
		function __construct() {

		}


		/**
		 * @param int $job_id
		 *
		 * @return string
		 */
		function display_types( $job_id ) {

			$types = wp_get_post_terms( $job_id, 'jb-job-type', [
				'orderby'   => 'name',
				'order'     => 'ASC',
			] );

			if ( empty( $types ) || is_wp_error( $types ) ) {
				return '';
			}

			ob_start();

			foreach ( $types as $type ) {
				$term_color = get_term_meta( $type->term_id, 'jb-color', true );
				$term_background = get_term_meta( $type->term_id, 'jb-background', true );

				$attr = '';
				if ( ! empty( $term_color ) || ! empty( $term_background ) ) {
					$attr .= 'style="';
					if ( ! empty( $term_color ) ) {
						$attr .= 'color:' . esc_attr( $term_color ) . ';';
					}
					if ( ! empty( $term_background ) ) {
						$attr .= 'background:' . esc_attr( $term_background ) . ';';
					}
					$attr .= '"';
				} ?>

				<div class="jb-job-type" <?php echo $attr ?>>
					<?php echo $type->name ?>
				</div>

			<?php }

			return ob_get_clean();
		}


		/**
		 * Calculates and returns the job expiry date.
		 *
		 * @return string
		 */
		function calculate_expiry() {
			$duration = absint( JB()->options()->get( 'job-duration' ) );

			if ( $duration ) {
				return date( 'Y-m-d', strtotime( "+{$duration} days", current_time( 'timestamp' ) ) );
			}

			return '';
		}


		/**
		 * Returns the job expiry date.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 */
		function get_expiry_date( $job_id ) {
			$expiry_date = get_post_meta( $job_id, 'jb-expiry-date', true );
			if ( empty( $expiry_date ) ) {
				return '';
			}

			$expiry_date = date_i18n( get_option( 'date_format' ), strtotime( $expiry_date ) );

			return $expiry_date;
		}


		/**
		 * Returns the job expiry date.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 */
		function get_posted_date( $job_id, $diff = false ) {
			$job = get_post( $job_id );

			if ( $diff ) {
				$posted_date = JB()->time_diff( strtotime( $job->post_date ) );
			} else {
				$posted_date = date_i18n( get_option( 'date_format' ), strtotime( $job->post_date ) );
			}

			return $posted_date;
		}


		/**
		 * Returns the job expiry date.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 */
		function get_job_author( $job_id ) {
			$job = get_post( $job_id );

			$author = get_userdata( $job->post_author );

			if ( empty( $author ) ) {
				return __( 'Guest', 'jobboardwp' );
			}

			return $author->display_name;
		}


		/**
		 * Returns the job location type.
		 *
		 * @param int $job_id Job post ID
		 * @param bool $raw RAW or formatted location
		 *
		 * @return string
		 */
		function get_location_type( $job_id, $raw = false ) {
			$location = get_post_meta( $job_id, 'jb-location-type', true );
			if ( $raw ) {
				return $location;
			}

			switch ( $location ) {
				case '':
					$location = __( 'Onsite or remote', 'jobboardwp' );
					break;
				case '0':
					$location = __( 'Onsite', 'jobboardwp' );
					break;
				case '1':
					$location = __( 'Remote', 'jobboardwp' );
					break;
			}

			return $location;
		}


		/**
		 * Returns the job location.
		 *
		 * @param int $job_id Job post ID
		 * @param bool $raw RAW or formatted location
		 *
		 * @return string
		 */
		function get_location( $job_id, $raw = false ) {
			$location = get_post_meta( $job_id, 'jb-location', true );
			if ( $raw ) {
				return $location;
			}

			if ( empty( $location ) ) {
				return __( 'Anywhere', 'jobboardwp' );
			}

			$location = $this->get_location_link( $location );
			return $location;
		}


		/**
		 * @param $location
		 *
		 * @return string
		 */
		function get_location_link( $location ) {
			if ( empty( $location ) ) {
				return '';
			}
			$location = '<a href="https://maps.google.com/maps?q=' . rawurlencode( wp_strip_all_tags( $location ) ) . '&zoom=14&size=512x512&maptype=roadmap&sensor=false" target="_blank">' . $location . '</a>';
			return $location;
		}

		/**
		 * Returns the job expiry date.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 */
		function get_company( $job_id ) {
			$company_name = get_post_meta( $job_id, 'jb-company-name', true );
			$company_website = get_post_meta( $job_id, 'jb-company-website', true );
			$company_tagline = get_post_meta( $job_id, 'jb-company-tagline', true );

			if ( empty( $company_name ) ) {
				return '';
			}

			if ( ! empty( $company_website ) ) {
				$company = sprintf( '<span title="%s"><a href="%s">%s</a></span>', $company_tagline, $company_website, $company_name );
			} else {
				$company = sprintf( '<span title="%s">%s</span>', $company_tagline, $company_name );
			}

			return $company;
		}


		/**
		 * @param $job_id
		 *
		 * @return array
		 */
		function get_company_data( $job_id ) {
			$company_name = get_post_meta( $job_id, 'jb-company-name', true );
			$company_website = get_post_meta( $job_id, 'jb-company-website', true );
			$company_tagline = get_post_meta( $job_id, 'jb-company-tagline', true );
			$company_twitter = get_post_meta( $job_id, 'jb-company-twitter', true );
			$company_facebook = get_post_meta( $job_id, 'jb-company-facebook', true );
			$company_instagram = get_post_meta( $job_id, 'jb-company-instagram', true );

			return [
				'name'      => $company_name,
				'website'   => $company_website,
				'tagline'   => $company_tagline,
				'twitter'   => $company_twitter,
				'facebook'  => $company_facebook,
				'instagram' => $company_instagram,
			];
		}


		/**
		 * @param $job_id
		 *
		 * @return string
		 */
		function get_logo( $job_id ) {
			$company_logo = get_the_post_thumbnail( $job_id, 'thumbnail', [ 'class' => 'jb-job-company-logo' ] );
			if ( ! empty( $company_logo ) ) {
				$company_logo = '<div class="jb-job-company-logo-wrapper">' . $company_logo . '</div>';
			} else {
				$company_logo = '';
			}

			return $company_logo;
		}


		/**
		 * Returns the job expiry date.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 */
		function get_status( $job_id ) {
			$job = get_post( $job_id );

			if ( empty( $job->post_status ) ) {
				return '';
			}

			if ( $job->post_status == 'jb-preview' ) {
				$job->post_status = 'draft';
			}

			$post_status = get_post_status_object( $job->post_status );
			return ! empty( $post_status->label ) ? $post_status->label : '';
		}


		/**
		 * @param int $job_id
		 *
		 * @return bool
		 */
		function is_filled( $job_id ) {
			$filled = get_post_meta( $job_id, 'jb-is-filled', true );
			return (bool) $filled;
		}


		/**
		 * @param int $job_id
		 *
		 * @return bool
		 */
		function can_applied( $job_id ) {
			$job = get_post( $job_id );

			if ( empty( $job ) || is_wp_error( $job ) ) {
				return false;
			}

			if ( ! $this->is_filled( $job_id ) && ! in_array( $job->post_status, [ 'jb-preview', 'jb-expired' ] ) ) {
				return true;
			}

			return false;
		}


		/**
		 * @param int $job_id
		 *
		 * @return array|bool
		 */
		function get_raw_data( $job_id ) {
			$job = get_post( $job_id );

			if ( empty( $job ) || is_wp_error( $job ) ) {
				return false;
			}

			$company_name = get_post_meta( $job_id, 'jb-company-name', true );
			$company_website = get_post_meta( $job_id, 'jb-company-website', true );
			$company_tagline = get_post_meta( $job_id, 'jb-company-tagline', true );
			$company_twitter = get_post_meta( $job_id, 'jb-company-twitter', true );
			$company_facebook = get_post_meta( $job_id, 'jb-company-facebook', true );
			$company_instagram = get_post_meta( $job_id, 'jb-company-instagram', true );

			$company_logo = '';
			$attachment_id = get_post_thumbnail_id( $job_id );
			if ( $attachment_id ) {
				$image = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
				$company_logo = isset( $image[0] ) ? $image[0] : '';
			}

			$types = wp_get_post_terms( $job_id, 'jb-job-type', [
				'orderby'   => 'name',
				'order'     => 'ASC',
				'fields'    => 'ids',
			] );

			if ( empty( $types ) || is_wp_error( $types ) ) {
				$job_types = [];
			} else {
				$job_types = $types;
			}

			$categories = wp_get_post_terms( $job_id, 'jb-job-category', [
				'orderby'   => 'name',
				'order'     => 'ASC',
				'fields'    => 'ids',
			] );


			if ( empty( $categories ) || is_wp_error( $categories ) ) {
				$job_categories = [];
			} else {
				$job_categories = $categories;
			}

			return [
				'title'             => $job->post_title,
				'description'       => $job->post_content,
				'type'              => $job_types,
				'category'          => $job_categories,
				'location'          => $this->get_location( $job_id, true ),
				'location_type'     => $this->get_location_type( $job_id, true ),
				'app_contact'       => get_post_meta( $job_id, 'jb-application-contact', true ),
				'company_name'      => $company_name,
				'company_website'   => $company_website,
				'company_tagline'   => $company_tagline,
				'company_twitter'   => $company_twitter,
				'company_facebook'  => $company_facebook,
				'company_instagram' => $company_instagram,
				'company_logo'      => $company_logo,
			];
		}


		/**
		 * @param int|\WP_Post $job
		 *
		 * @return array
		 */
		function get_actions( $job ) {
			if ( is_numeric( $job ) ) {
				$job = get_post( $job );
			}

			$actions = [];

			if ( in_array( $job->post_status, [ 'draft', 'jb-preview' ] ) ) {
				$actions['edit'] = [
					'href'  => $this->get_edit_link( $job->ID ),
					'title' => __( 'Continue submission', 'jobboardwp' ),
				];
				$actions['delete'] = [
					'href'  => $this->get_delete_link( $job->ID ),
					'title' => __( 'Delete', 'jobboardwp' ),
				];
			}

			if ( in_array( $job->post_status, [ 'publish' ] ) ) {
				$actions['edit'] = [
					'href'  => $this->get_edit_link( $job->ID ),
					'title' => __( 'Edit', 'jobboardwp' ),
				];

				if ( ! $this->is_filled( $job->ID ) ) {
					$actions['fill'] = [
						'href'  => $this->get_filled_link( $job->ID ),
						'title' => __( 'Mark as filled', 'jobboardwp' ),
					];
				} else {
					$actions['un-fill'] = [
						'href'  => $this->get_unfilled_link( $job->ID ),
						'title' => __( 'Mark as un-filled', 'jobboardwp' ),
					];
				}

				$actions['delete'] = [
					'href'  => $this->get_delete_link( $job->ID ),
					'title' => __( 'Delete', 'jobboardwp' ),
				];
			}

			return $actions;
		}


		/**
		 * @param $job_id
		 *
		 * @return string
		 */
		function get_edit_link( $job_id ) {
			$post_job_page = JB()->permalinks()->get_preset_page_link( 'job-post' );
			return add_query_arg( [ 'job-id' => $job_id, 'nonce' => wp_create_nonce( 'jb-job-draft' . $job_id ) ], $post_job_page );
		}


		/**
		 * @param $job_id
		 *
		 * @return string
		 */
		function get_filled_link( $job_id ) {
			$jobs_page = JB()->permalinks()->get_preset_page_link( 'jobs-dashboard' );
			return add_query_arg( [ 'action' => 'filled', 'job-id' => $job_id, 'nonce' => wp_create_nonce( 'jb-job-fill' . $job_id ) ], $jobs_page );
		}


		/**
		 * @param $job_id
		 *
		 * @return string
		 */
		function get_unfilled_link( $job_id ) {
			$jobs_page = JB()->permalinks()->get_preset_page_link( 'jobs-dashboard' );
			return add_query_arg( [ 'action' => 'un-filled', 'job-id' => $job_id, 'nonce' => wp_create_nonce( 'jb-job-unfill' . $job_id ) ], $jobs_page );
		}


		/**
		 * @param $job_id
		 *
		 * @return string
		 */
		function get_delete_link( $job_id ) {
			$jobs_page = JB()->permalinks()->get_preset_page_link( 'jobs-dashboard' );
			return add_query_arg( [ 'action' => 'delete', 'job-id' => $job_id, 'nonce' => wp_create_nonce( 'jb-job-delete' . $job_id ) ], $jobs_page );
		}


		/**
		 * @param int|\WP_Post $job
		 *
		 * @return array|bool
		 */
		function get_structured_data( $job ) {
			if ( is_numeric( $job ) ) {
				$job = get_post( $job );
			}

			$data = [];


			return apply_filters( 'jb-job-structured-data', $data, $job );
		}


		/**
		 * Get Templates
		 *
		 * @return mixed
		 */
		function get_templates() {

			$prefix = 'Job';

			if ( ! isset( $prefix ) ) {
				return array();
			}

			$dir = JB()->theme_templates;

			$templates = array();
			if ( is_dir( $dir ) ) {
				$handle = opendir( $dir );
				while ( false !== ( $filename = readdir( $handle ) ) ) {
					if ( $filename != '.' && $filename != '..' ) {

						$clean_filename = $this->get_template_name( $filename );

						$source = file_get_contents( $dir . DIRECTORY_SEPARATOR . $filename );
						$tokens = token_get_all( $source );
						$comment = array(
							T_COMMENT, // All comments since PHP5
							T_DOC_COMMENT, // PHPDoc comments
						);
						foreach ( $tokens as $token ) {
							if ( in_array( $token[0], $comment ) && strstr( $token[1], '/* ' . $prefix . ' Template:' ) ) {
								$txt = $token[1];
								$txt = str_replace('/* ' . $prefix . ' Template: ', '', $txt );
								$txt = str_replace(' */', '', $txt );
								$templates[ $clean_filename ] = $txt;
							}
						}
					}
				}
				closedir( $handle );

				asort( $templates );
			}

			return $templates;
		}


		/**
		 * Get File Name without path and extension
		 *
		 * @param $file
		 *
		 * @return mixed|string
		 */
		function get_template_name( $file ) {
			$file = basename( $file );
			$file = preg_replace( '/\\.[^.\\s]{3,4}$/', '', $file );
			return $file;
		}


		/**
		 * Maintenance task to expire jobs.
		 */
		function check_for_expired_jobs() {
			// Change status to expired.
			$job_ids = get_posts(
				[
					'post_type'      => 'jb-job',
					'post_status'    => 'publish',
					'fields'         => 'ids',
					'posts_per_page' => -1,
					'meta_query'     => [
						'relation' => 'AND',
						[
							'key'     => 'jb-expiry-date',
							'value'   => date( 'Y-m-d', current_time( 'timestamp' ) ),
							'compare' => '<',
						],
					],
				]
			);

			if ( $job_ids ) {
				foreach ( $job_ids as $job_id ) {
					$job_data                = [];
					$job_data['ID']          = $job_id;
					$job_data['post_status'] = 'jb-expired';
					wp_update_post( $job_data );
				}
			}

			// Delete old expired jobs.

			/**
			 * Set whether or not we should delete expired jobs after a certain amount of time.
			 *
			 * @since 1.0.0
			 *
			 * @param bool $delete_expired_jobs Whether we should delete expired jobs after a certain amount of time. Defaults to false.
			 */
			if ( apply_filters( 'jb_cron_delete_expired_jobs', false ) ) {
				/**
				 * Days to preserve expired job listings before deleting them.
				 *
				 * @since 1.0.0
				 *
				 * @param int $delete_expired_jobs_days Number of days to preserve expired job listings before deleting them.
				 */
				$delete_expired_jobs_days = apply_filters( 'jb_cron_delete_expired_jobs_days', 30 );

				$job_ids = get_posts(
					[
						'post_type'      => 'jb-job',
						'post_status'    => 'jb-expired',
						'fields'         => 'ids',
						'date_query'     => [
							[
								'column' => 'post_modified',
								'before' => date( 'Y-m-d', strtotime( '-' . $delete_expired_jobs_days . ' days', current_time( 'timestamp' ) ) ),
							],
						],
						'posts_per_page' => -1,
					]
				);

				if ( $job_ids ) {
					foreach ( $job_ids as $job_id ) {
						wp_trash_post( $job_id );
					}
				}
			}
		}

	}
}