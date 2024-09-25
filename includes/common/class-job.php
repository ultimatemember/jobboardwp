<?php
namespace jb\common;

use WP_Filesystem_Base;
use WP_Post;
use function WP_Filesystem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\common\Job' ) ) {

	/**
	 * Class Job
	 *
	 * @package jb\common
	 */
	class Job {

		/**
		 * Render job types layout
		 *
		 * @param int $job_id
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function display_types( $job_id ) {
			$types = wp_get_post_terms(
				$job_id,
				'jb-job-type',
				array(
					'orderby' => 'name',
					'order'   => 'ASC',
				)
			);

			if ( empty( $types ) || is_wp_error( $types ) ) {
				return '';
			}

			ob_start();

			foreach ( $types as $type ) {
				$term_color      = get_term_meta( $type->term_id, 'jb-color', true );
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
				}

				echo wp_kses( '<div class="jb-job-type" ' . $attr . '>' . esc_html( $type->name ) . '</div>', JB()->get_allowed_html( 'templates' ) );
			}

			return ob_get_clean();
		}

		/**
		 * Calculates and returns the job expiry date.
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function calculate_expiry() {
			$duration = absint( JB()->options()->get( 'job-duration' ) );

			if ( ! empty( $duration ) ) {
				return gmdate( 'Y-m-d', strtotime( "+{$duration} days" ) );
			}

			return '';
		}

		/**
		 * Returns the job expiry date.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_expiry_date( $job_id ) {
			$expiry_date = get_post_meta( $job_id, 'jb-expiry-date', true );
			if ( empty( $expiry_date ) ) {
				return '';
			}

			return date_i18n( get_option( 'date_format' ), strtotime( $expiry_date ) );
		}

		/**
		 * Returns the job's raw expiry date.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_expiry_date_raw( $job_id ) {
			$expiry_date = get_post_meta( $job_id, 'jb-expiry-date', true );
			if ( empty( $expiry_date ) ) {
				return '';
			}

			return $expiry_date;
		}

		/**
		 * Returns the job posted date.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_posted_date( $job_id ) {
			$posted_date = '';

			if ( JB()->is_request( 'admin' ) && ! JB()->is_request( 'ajax' ) ) {
				$posted_date = get_post_time( get_option( 'date_format' ), false, $job_id, true );
			} else {
				$dateformat = JB()->options()->get( 'job-dateformat' );
				if ( 'relative' === $dateformat ) {
					$posted_date = human_time_diff( get_post_timestamp( $job_id ) );
				} elseif ( 'default' === $dateformat ) {
					$posted_date = get_post_time( get_option( 'date_format' ), false, $job_id, true );
				}
			}

			return $posted_date;
		}

		/**
		 * Returns the job expiry date.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_html_datetime( $job_id ) {
			return get_post_time( 'c', false, $job_id, true );
		}

		/**
		 * Returns the job category.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 *
		 * @since 1.1.1
		 */
		public function get_job_category( $job_id ) {
			if ( ! JB()->options()->get( 'job-categories' ) ) {
				return '';
			}

			$terms = get_the_terms( $job_id, 'jb-job-category' );

			if ( empty( $terms ) ) {
				return '';
			}

			return '<i class="fas fa-list-alt"></i><a href="' . esc_url( get_term_link( $terms[0]->term_id, 'jb-job-category' ) ) . '">' . esc_html( $terms[0]->name ) . '</a>';
		}

		/**
		 * Returns the job author.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_job_author( $job_id ) {
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
		 *
		 * @since 1.0
		 */
		public function get_location_type( $job_id, $raw = false ) {
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
		 * Returns the job location data.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_location_data( $job_id ) {
			$location_data = get_post_meta( $job_id, 'jb-location-raw-data', true );
			return ! empty( $location_data ) ? $location_data : '';
		}

		/**
		 * Returns the job location.
		 *
		 * @param int $job_id Job post ID
		 * @param bool $raw RAW or formatted location
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_location( $job_id, $raw = false ) {
			$location = get_post_meta( $job_id, 'jb-location', true );
			if ( $raw ) {
				return $location;
			}

			$location_type = get_post_meta( $job_id, 'jb-location-type', true );

			if ( '1' === $location_type && empty( $location ) ) {
				return __( 'Remote', 'jobboardwp' );
			}

			if ( empty( $location ) ) {
				return __( 'Anywhere', 'jobboardwp' );
			}

			return $location;
		}

		/**
		 * Get location link
		 *
		 * @param int $job_id
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_location_link( $job_id ) {
			$location_raw = JB()->common()->job()->get_location( $job_id, true );
			$type_raw     = $this->get_location_type( $job_id, true );
			$type         = $this->get_location_type( $job_id );

			if ( '1' === $type_raw ) {
				if ( empty( $location_raw ) ) {
					return esc_html__( 'Remote', 'jobboardwp' );
				}

				$location = JB()->common()->job()->get_location( $job_id );
				if ( empty( $location ) ) {
					return '';
				}
				$location = '<a href="https://maps.google.com/maps?q=' . rawurlencode( wp_strip_all_tags( $location ) ) . '&zoom=14&size=512x512&maptype=roadmap&sensor=false" target="_blank">' . $location . '</a>';
				// translators: %1$s is a location type; %2$s is a location.
				return sprintf( __( '%1$s (%2$s)', 'jobboardwp' ), $type, $location );
			}

			if ( empty( $location_raw ) ) {
				return esc_html__( 'Anywhere', 'jobboardwp' );
			}

			$location = JB()->common()->job()->get_location( $job_id );
			if ( empty( $location ) ) {
				return '';
			}

			return '<a href="https://maps.google.com/maps?q=' . rawurlencode( wp_strip_all_tags( $location ) ) . '&zoom=14&size=512x512&maptype=roadmap&sensor=false" target="_blank">' . $location . '</a>';
		}

		/**
		 * Returns the job company.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_company( $job_id ) {
			$company_name    = get_post_meta( $job_id, 'jb-company-name', true );
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
		 * Build job's company data
		 *
		 * @param int $job_id
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function get_company_data( $job_id ) {
			$company_name      = get_post_meta( $job_id, 'jb-company-name', true );
			$company_website   = get_post_meta( $job_id, 'jb-company-website', true );
			$company_tagline   = get_post_meta( $job_id, 'jb-company-tagline', true );
			$company_twitter   = get_post_meta( $job_id, 'jb-company-twitter', true );
			$company_facebook  = get_post_meta( $job_id, 'jb-company-facebook', true );
			$company_instagram = get_post_meta( $job_id, 'jb-company-instagram', true );

			/**
			 * Filters the company data.
			 *
			 * @since 1.1.0
			 * @hook jb-job-company-data
			 *
			 * @param {array} $company_data Job's company data.
			 * @param {int}   $job_id       Job ID passed into the function.
			 *
			 * @return {array} Maybe modified job's company data.
			 */
			return apply_filters(
				'jb-job-company-data', // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
				array(
					'name'      => $company_name,
					'website'   => $company_website,
					'tagline'   => $company_tagline,
					'twitter'   => $company_twitter,
					'facebook'  => $company_facebook,
					'instagram' => $company_instagram,
				),
				$job_id
			);
		}

		/**
		 * Get job logo
		 *
		 * @param int $job_id
		 * @param bool $raw
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_logo( $job_id, $raw = false ) {
			if ( $raw ) {
				$company_logo = '';

				$attachment_id = get_post_thumbnail_id( $job_id );
				if ( $attachment_id ) {
					$image        = wp_get_attachment_image_src( $attachment_id );
					$company_logo = isset( $image[0] ) ? $image[0] : '';
				}

				/**
				 * Filters the job logo.
				 *
				 * @since 1.2.2
				 * @hook jb_job_logo
				 *
				 * @param {string} $logo   Job logo.
				 * @param {int}    $job_id Job ID.
				 * @param {bool}   $raw    Context for getting job logo. If `true` getting RAW link to the logo.
				 *
				 * @return {array} Job logo.
				 */
				return apply_filters( 'jb_job_logo', $company_logo, $job_id, $raw );
			}

			$company_logo = get_the_post_thumbnail( $job_id, 'thumbnail', array( 'class' => 'jb-job-company-logo' ) );

			if ( ! empty( $company_logo ) ) {
				$company_logo = '<div class="jb-job-company-logo-wrapper">' . $company_logo . '</div>';
			} else {
				$company_logo = '';
			}

			/** This filter is documented in includes/common/class-job.php */
			return apply_filters( 'jb_job_logo', $company_logo, $job_id, $raw );
		}

		/**
		 * Returns the job status.
		 *
		 * @param int $job_id Job post ID
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_status( $job_id ) {
			$job = get_post( $job_id );

			if ( empty( $job->post_status ) ) {
				return '';
			}

			if ( 'jb-preview' === $job->post_status ) {
				$job->post_status = 'draft';
			}

			$post_status = get_post_status_object( $job->post_status );
			if ( null === $post_status ) {
				return '';
			}

			return ! empty( $post_status->label ) ? $post_status->label : '';
		}

		/**
		 * Is job filled?
		 *
		 * @param int $job_id
		 *
		 * @return bool
		 *
		 * @since 1.0
		 */
		public function is_filled( $job_id ) {
			$filled = get_post_meta( $job_id, 'jb-is-filled', true );
			return (bool) $filled;
		}

		/**
		 * Is job featured?
		 *
		 * @param int $job_id
		 *
		 * @return bool
		 *
		 * @since 1.2.4
		 */
		public function is_featured( $job_id ) {
			$featured = get_post_meta( $job_id, 'jb-is-featured', true );
			return (bool) $featured;
		}

		/**
		 * Is job expired?
		 *
		 * @param int $job_id
		 *
		 * @return bool
		 *
		 * @since 1.0
		 */
		public function is_expired( $job_id ) {
			$job = get_post( $job_id );

			if ( empty( $job ) || is_wp_error( $job ) ) {
				return false;
			}

			if ( 'jb-expired' === $job->post_status ) {
				return true;
			}

			return false;
		}

		/**
		 * Can job be applied?
		 *
		 * @param int $job_id
		 *
		 * @return bool
		 *
		 * @since 1.0
		 */
		public function can_applied( $job_id ) {
			$job = get_post( $job_id );

			$can_applied = false;
			if ( empty( $job ) || is_wp_error( $job ) ) {
				return false;
			}

			if ( ! $this->is_filled( $job_id ) && ! in_array( $job->post_status, array( 'jb-preview', 'jb-expired' ), true ) ) {
				$can_applied = true;
			}

			/**
			 * Filters the ability of the job can be applied.
			 *
			 * @since 1.0
			 * @hook jb_can_applied_job
			 *
			 * @param {bool} $can_applied Can a job be applied? Set it to the `true` if a job can be applied.
			 * @param {int}  $job_id      Job ID passed into the function.
			 *
			 * @return {bool} Can a job be applied?
			 */
			return apply_filters( 'jb_can_applied_job', $can_applied, $job_id );
		}

		/**
		 * Getting formatted job salary.
		 *
		 * @param int $job_id Job ID.
		 *
		 * @return string Formatted salary string. Empty string in the case when invalid salary data in meta
		 */
		public function get_formatted_salary( $job_id ) {
			$amount_output = '';
			if ( ! JB()->options()->get( 'job-salary' ) ) {
				return $amount_output;
			}

			$salary_type = get_post_meta( $job_id, 'jb-salary-type', true );
			if ( '' === $salary_type ) {
				return $amount_output;
			}

			$currency         = JB()->options()->get( 'job-salary-currency' );
			$currency_symbols = JB()->config()->get( 'currencies' );
			$currency_symbol  = $currency_symbols[ $currency ]['symbol'];

			$salary_amount_type = get_post_meta( $job_id, 'jb-salary-amount-type', true );
			if ( 'numeric' === $salary_amount_type ) {
				$salary_amount = get_post_meta( $job_id, 'jb-salary-amount', true );
				if ( empty( $salary_amount ) ) {
					return $amount_output;
				}

				$amount_output = sprintf( JB()->get_job_salary_format(), $currency_symbol, $salary_amount );
			} else {
				$salary_min_amount = get_post_meta( $job_id, 'jb-salary-min-amount', true );
				$salary_max_amount = get_post_meta( $job_id, 'jb-salary-max-amount', true );
				if ( empty( $salary_min_amount ) && empty( $salary_max_amount ) ) {
					return $amount_output;
				}

				if ( empty( $salary_min_amount ) && ! empty( $salary_max_amount ) ) {
					$amount = sprintf( JB()->get_job_salary_format(), $currency_symbol, $salary_max_amount );

					// translators: %s is maximum job salary amount.
					$amount_output = sprintf( __( 'Up to %s', 'jobboardwp' ), $amount );
				} elseif ( ! empty( $salary_min_amount ) && empty( $salary_max_amount ) ) {
					$amount = sprintf( JB()->get_job_salary_format(), $currency_symbol, $salary_min_amount );

					// translators: %s is minimum job salary amount.
					$amount_output = sprintf( __( 'Starts from %s', 'jobboardwp' ), $amount );
				} else {
					$amount_output = sprintf( JB()->get_job_salary_format(), $currency_symbol, $salary_min_amount . '-' . $salary_max_amount );
				}
			}

			if ( 'recurring' === $salary_type ) {
				$salary_period = get_post_meta( $job_id, 'jb-salary-period', true );
				if ( empty( $salary_period ) ) {
					return $amount_output;
				}

				// translators: %1$s is a job's salary amount or range; %2$s is a job's salary period.
				$amount_output = sprintf( __( '%1$s per %2$s', 'jobboardwp' ), $amount_output, $salary_period );
			}

			return $amount_output;
		}

		/**
		 * @return int
		 */
		public function get_maximum_salary() {
			global $wpdb;
			$max_values = $wpdb->get_results(
				"SELECT DISTINCT meta_value
				FROM {$wpdb->postmeta}
				WHERE meta_key = 'jb-salary-max-amount' OR
				      meta_key = 'jb-salary-amount'",
				ARRAY_A
			);

			$max_value = 0;
			foreach ( $max_values as $value ) {
				if ( null !== $value['meta_value'] && ( 0 === $max_value || $max_value < $value['meta_value'] ) ) {
					$max_value = absint( $value['meta_value'] );
				}
			}

			return $max_value;
		}

		/**
		 * Get job RAW data
		 *
		 * @param int $job_id
		 *
		 * @return array|bool
		 *
		 * @since 1.0
		 */
		public function get_raw_data( $job_id ) {
			$job = get_post( $job_id );

			if ( empty( $job ) || is_wp_error( $job ) ) {
				return false;
			}

			$company_name      = get_post_meta( $job_id, 'jb-company-name', true );
			$company_website   = get_post_meta( $job_id, 'jb-company-website', true );
			$company_tagline   = get_post_meta( $job_id, 'jb-company-tagline', true );
			$company_twitter   = get_post_meta( $job_id, 'jb-company-twitter', true );
			$company_facebook  = get_post_meta( $job_id, 'jb-company-facebook', true );
			$company_instagram = get_post_meta( $job_id, 'jb-company-instagram', true );

			$company_logo  = '';
			$attachment_id = get_post_thumbnail_id( $job_id );
			if ( $attachment_id ) {
				$image        = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
				$company_logo = isset( $image[0] ) ? $image[0] : '';
			}

			$types = wp_get_post_terms(
				$job_id,
				'jb-job-type',
				array(
					'orderby' => 'name',
					'order'   => 'ASC',
					'fields'  => 'ids',
				)
			);

			if ( empty( $types ) || is_wp_error( $types ) ) {
				$job_types = array();
			} else {
				$job_types = $types;
			}

			$response = array(
				'title'             => $job->post_title,
				'description'       => $job->post_content,
				'type'              => $job_types,
				'location'          => $this->get_location( $job_id, true ),
				'location_type'     => $this->get_location_type( $job_id, true ),
				'location_data'     => $this->get_location_data( $job_id ),
				'app_contact'       => get_post_meta( $job_id, 'jb-application-contact', true ),
				'expires'           => get_post_meta( $job_id, 'jb-expiry-date', true ),
				'company_name'      => $company_name,
				'company_website'   => $company_website,
				'company_tagline'   => $company_tagline,
				'company_twitter'   => $company_twitter,
				'company_facebook'  => $company_facebook,
				'company_instagram' => $company_instagram,
				'company_logo'      => $company_logo,
			);

			if ( JB()->options()->get( 'job-categories' ) ) {
				$categories = wp_get_post_terms(
					$job_id,
					'jb-job-category',
					array(
						'orderby' => 'name',
						'order'   => 'ASC',
						'fields'  => 'ids',
					)
				);

				if ( empty( $categories ) || is_wp_error( $categories ) ) {
					$job_categories = array();
				} else {
					$job_categories = $categories;
				}

				$response['category'] = $job_categories;
			}

			if ( JB()->options()->get( 'job-salary' ) ) {
				if ( get_post_meta( $job_id, 'jb-salary-type', true ) ) {
					$response['salary_type'] = get_post_meta( $job_id, 'jb-salary-type', true );
				}
				if ( get_post_meta( $job_id, 'jb-salary-amount-type', true ) ) {
					$response['salary_amount_type'] = get_post_meta( $job_id, 'jb-salary-amount-type', true );
				}
				if ( get_post_meta( $job_id, 'jb-salary-amount', true ) ) {
					$response['salary_amount'] = get_post_meta( $job_id, 'jb-salary-amount', true );
				}
				if ( get_post_meta( $job_id, 'jb-salary-min-amount', true ) ) {
					$response['salary_min_amount'] = get_post_meta( $job_id, 'jb-salary-min-amount', true );
				}
				if ( get_post_meta( $job_id, 'jb-salary-max-amount', true ) ) {
					$response['salary_max_amount'] = get_post_meta( $job_id, 'jb-salary-max-amount', true );
				}
				if ( get_post_meta( $job_id, 'jb-salary-period', true ) ) {
					$response['salary_period'] = get_post_meta( $job_id, 'jb-salary-period', true );
				}
			}

			/**
			 * Filters the job raw data.
			 *
			 * @since 1.0
			 * @hook jb-job-raw-data
			 *
			 * @param {array} $response Job's raw data.
			 * @param {int}   $job_id   Job ID passed into the function.
			 *
			 * @return {array} Job data in raw format.
			 */
			return apply_filters( 'jb-job-raw-data', $response, $job_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		}

		/**
		 * Get job actions
		 *
		 * @param int|WP_Post|array $job
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function get_actions( $job ) {
			if ( is_numeric( $job ) ) {
				$job = get_post( $job );
			}

			$actions = array();

			if ( 'jb-expired' === $job->post_status ) {
				$actions = array_merge(
					$actions,
					array(
						'edit'   => array(
							'href'  => $this->get_edit_link( $job->ID ),
							'title' => __( 'Submit again', 'jobboardwp' ),
						),
						'delete' => array(
							'title' => __( 'Delete', 'jobboardwp' ),
						),
					)
				);
			}

			if ( in_array( $job->post_status, array( 'draft', 'jb-preview' ), true ) ) {
				$actions = array_merge(
					$actions,
					array(
						'edit'   => array(
							'href'  => $this->get_edit_link( $job->ID ),
							'title' => __( 'Continue submission', 'jobboardwp' ),
						),
						'delete' => array(
							'title' => __( 'Delete', 'jobboardwp' ),
						),
					)
				);
			}

			if ( 'pending' === $job->post_status && JB()->options()->get( 'pending-job-editing' ) ) {
				$actions['edit'] = array(
					'href'  => $this->get_edit_link( $job->ID ),
					'title' => __( 'Edit', 'jobboardwp' ),
				);
			}

			if ( 'publish' === $job->post_status ) {
				if ( 0 !== (int) JB()->options()->get( 'published-job-editing' ) ) {
					$actions['edit'] = array(
						'href'  => $this->get_edit_link( $job->ID ),
						'title' => __( 'Edit', 'jobboardwp' ),
					);
				}

				if ( ! $this->is_filled( $job->ID ) ) {
					$actions['fill'] = array(
						'title' => __( 'Mark as filled', 'jobboardwp' ),
					);
				} else {
					$actions['un-fill'] = array(
						'title' => __( 'Mark as un-filled', 'jobboardwp' ),
					);
				}

				$actions['delete'] = array(
					'title' => __( 'Delete', 'jobboardwp' ),
				);
			}

			return $actions;
		}

		/**
		 * Get job preview link
		 *
		 * @param $job_id
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_preview_link( $job_id ) {
			$current_url = JB()->get_current_url( true );

			return add_query_arg(
				array(
					'jb-preview' => 1,
					'job-id'     => $job_id,
					'nonce'      => wp_create_nonce( 'jb-job-preview' . $job_id ),
				),
				$current_url
			);
		}

		/**
		 * Get job edit link
		 *
		 * @param int         $job_id
		 * @param null|string $base_url
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_edit_link( $job_id, $base_url = null ) {
			if ( empty( $base_url ) ) {
				$post_job_page = JB()->common()->permalinks()->get_predefined_page_link( 'job-post' );
			} else {
				$post_job_page = $base_url;
			}

			return add_query_arg(
				array(
					'job-id' => $job_id,
					'nonce'  => wp_create_nonce( 'jb-job-draft' . $job_id ),
				),
				$post_job_page
			);
		}

		/**
		 * Get job's structured data for schema.org
		 *
		 * @param int|WP_Post|array $job
		 *
		 * @return array|bool
		 *
		 * @since 1.0
		 */
		public function get_structured_data( $job ) {
			if ( is_numeric( $job ) ) {
				$job = get_post( $job );
			}

			$data               = array();
			$data['@context']   = 'https://schema.org/';
			$data['@type']      = 'JobPosting';
			$data['datePosted'] = get_post_time( 'c', false, $job );

			$job_expires = get_post_meta( $job->ID, 'jb-expiry-date', true );
			if ( ! empty( $job_expires ) ) {
				$data['validThrough'] = gmdate( 'c', strtotime( $job_expires ) );
			}

			$data['title']       = wp_strip_all_tags( get_the_title( $job->ID ) );
			$data['description'] = get_the_content( $job->ID );

			$types = wp_get_post_terms(
				$job->ID,
				'jb-job-type',
				array(
					'orderby' => 'name',
					'order'   => 'ASC',
				)
			);

			if ( ! empty( $types ) && ! is_wp_error( $types ) ) {
				$employment_types = array();
				foreach ( $types as $type ) {
					$employment_types[] = $type->name;
				}
				$data['employmentType'] = esc_html( implode( ', ', $employment_types ) );
			}

			$logo    = JB()->common()->job()->get_logo( $job->ID, true );
			$company = JB()->common()->job()->get_company_data( $job->ID );

			$data['hiringOrganization']          = array();
			$data['hiringOrganization']['@type'] = 'Organization';
			$data['hiringOrganization']['name']  = esc_html( $company['name'] );

			$company_website = $company['website'];
			if ( $company_website ) {
				$data['hiringOrganization']['sameAs'] = esc_url_raw( $company_website );
				$data['hiringOrganization']['url']    = esc_url_raw( $company_website );
			}

			if ( $logo ) {
				$data['hiringOrganization']['logo'] = esc_url_raw( $logo );
			}

			$data['identifier']          = array();
			$data['identifier']['@type'] = 'PropertyValue';
			$data['identifier']['name']  = esc_html( $company['name'] );
			$data['identifier']['value'] = get_the_guid( $job );

			$location = JB()->common()->job()->get_location( $job->ID, true );
			if ( ! empty( $location ) ) {
				$data['jobLocation']            = array();
				$data['jobLocation']['@type']   = 'Place';
				$data['jobLocation']['address'] = $this->get_structured_location( $job );
				if ( empty( $data['jobLocation']['address'] ) ) {
					$data['jobLocation']['address'] = esc_html( $location );
				}
			}

			if ( JB()->options()->get( 'job-salary' ) ) {
				$salary_type = get_post_meta( $job->ID, 'jb-salary-type', true );
				if ( '' !== $salary_type ) {
					$salary_amount_type = get_post_meta( $job->ID, 'jb-salary-amount-type', true );
					$currency           = JB()->options()->get( 'job-salary-currency' );

					$data['baseSalary']             = array();
					$data['baseSalary']['@type']    = 'MonetaryAmount';
					$data['baseSalary']['currency'] = $currency;

					$data['baseSalary']['value']['@type'] = 'QuantitativeValue';
					if ( 'numeric' === $salary_amount_type ) {
						$salary_amount = get_post_meta( $job->ID, 'jb-salary-amount', true );
						if ( ! empty( $salary_amount ) ) {
							$data['baseSalary']['value']['value'] = number_format( $salary_amount, 2, '.', '' );
						}
					} else {
						$salary_min_amount = get_post_meta( $job->ID, 'jb-salary-min-amount', true );
						$salary_max_amount = get_post_meta( $job->ID, 'jb-salary-max-amount', true );
						if ( '' !== $salary_min_amount ) {
							$data['baseSalary']['value']['maxValue'] = number_format( $salary_max_amount, 2, '.', '' );
						}
						if ( '' !== $salary_max_amount ) {
							$data['baseSalary']['value']['maxValue'] = number_format( $salary_max_amount, 2, '.', '' );
						}
					}

					if ( 'recurring' === $salary_type ) {
						$salary_period = get_post_meta( $job->ID, 'jb-salary-period', true );
						if ( ! empty( $salary_period ) ) {
							$data['baseSalary']['value']['unitText'] = strtoupper( $salary_period );
						}
					}
				}
			}

			/**
			 * Filters the job structured data.
			 *
			 * @since 1.1.0
			 * @hook jb-job-structured-data
			 *
			 * @param {array}   $data Job's structured data.
			 * @param {WP_Post} $job  Job post object.
			 *
			 * @return {array} Job data in raw format.
			 */
			return apply_filters( 'jb-job-structured-data', $data, $job ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		}

		/**
		 * Gets the job location data.
		 *
		 * @see https://schema.org/PostalAddress
		 *
		 * @param WP_Post $job
		 * @return array|bool
		 *
		 * @since 1.0
		 */
		public function get_structured_location( $job ) {
			$address = array(
				'@type' => 'PostalAddress',
			);

			$mapping = array(
				'addressLocality' => 'city',
				'addressRegion'   => 'state-short',
				'addressCountry'  => 'country-short',
			);
			foreach ( $mapping as $schema_key => $meta_key ) {
				$value = get_post_meta( $job->ID, 'jb-location-' . $meta_key, true );

				if ( ! empty( $value ) ) {
					$address[ $schema_key ] = esc_html( $value );
				}
			}

			// No address parts were found.
			if ( 1 === count( $address ) ) {
				$address = false;
			}

			/**
			 * Filters the job location structured data.
			 *
			 * @since 1.0
			 * @hook jb-job-location-structured-data
			 *
			 * @param {array}   $address Job location structured data.
			 * @param {WP_Post} $job  Job post object.
			 *
			 * @return {array} Job data in raw format.
			 */
			return apply_filters( 'jb-job-location-structured-data', $address, $job ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		}

		/**
		 * Get Templates
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function get_templates() {
			// phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
			$prefix = 'Job';

			$dir = JB()->theme_templates;

			global $wp_filesystem;

			if ( ! $wp_filesystem instanceof WP_Filesystem_Base ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';

				$credentials = request_filesystem_credentials( site_url() );
				WP_Filesystem( $credentials );
			}

			$templates = array();
			if ( $wp_filesystem->is_dir( $dir ) ) {
				$handle = @opendir( $dir );
				// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition -- reading folder's content here
				while ( false !== ( $filename = readdir( $handle ) ) ) {
					if ( '.' === $filename || '..' === $filename ) {
						continue;
					}

					// show only root *.php files inside templates dir for getting Job templates
					if ( is_dir( wp_normalize_path( $dir . DIRECTORY_SEPARATOR . $filename ) ) ) {
						continue;
					}

					$clean_filename = $this->get_template_name( $filename );

					$source  = $wp_filesystem->get_contents( wp_normalize_path( $dir . DIRECTORY_SEPARATOR . $filename ) );
					$tokens  = @\token_get_all( $source );
					$comment = array(
						T_COMMENT, // All comments since PHP5
						T_DOC_COMMENT, // PHPDoc comments
					);
					foreach ( $tokens as $token ) {
						if ( in_array( $token[0], $comment, true ) && false !== strpos( $token[1], '/* ' . $prefix . ' Template:' ) ) {
							$txt = $token[1];
							$txt = str_replace( array( '/* ' . $prefix . ' Template: ', ' */' ), '', $txt );

							$templates[ $clean_filename ] = $txt;
						}
					}
				}
				closedir( $handle );

				asort( $templates );
			}

			return $templates;
			// phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged
		}

		/**
		 * Get File Name without path and extension
		 *
		 * @param string $file
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_template_name( $file ) {
			$file = basename( $file );
			return preg_replace( '/\\.[^.\\s]{3,4}$/', '', $file );
		}

		/**
		 * Maintenance task to expire jobs.
		 *
		 * @since 1.0
		 */
		public function check_for_expired_jobs() {
			// Change status to expire.
			$job_ids = get_posts(
				array(
					'post_type'      => 'jb-job',
					'post_status'    => 'publish',
					'fields'         => 'ids',
					'posts_per_page' => -1,
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'key'     => 'jb-expiry-date',
							'value'   => gmdate( 'Y-m-d' ),
							'compare' => '<=',
						),
						array(
							'key'     => 'jb-expiry-date',
							'value'   => '',
							'compare' => '!=',
						),
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
					),
				)
			);

			if ( $job_ids ) {
				foreach ( $job_ids as $job_id ) {
					$job_data                = array();
					$job_data['ID']          = $job_id;
					$job_data['post_status'] = 'jb-expired';
					wp_update_post( $job_data );

					/**
					 * Fires after Job has been expired.
					 *
					 * @since 1.1.0
					 * @hook jb_job_is_expired
					 *
					 * @param {int} $job_id Job ID.
					 */
					do_action( 'jb_job_is_expired', $job_id );
				}
			}

			// Delete old expired jobs.
			/**
			 * Set whether we should delete expired jobs after a certain amount of time.
			 *
			 * @since 1.0
			 * @hook jb_cron_delete_expired_jobs
			 *
			 * @param {bool} $address Whether we should delete expired jobs after a certain amount of time. Defaults to false.
			 *
			 * @return {bool} Delete expired jobs if set to true.
			 */
			if ( apply_filters( 'jb_cron_delete_expired_jobs', false ) ) {
				/**
				 * Filters days to preserve expired job listings before deleting them.
				 *
				 * @since 1.0
				 * @hook jb_cron_delete_expired_jobs_days
				 *
				 * @param {int} $delete_expired_jobs_days Number of days to preserve expired job posts before deleting them. Defaults to 30 days.
				 *
				 * @return {int} Number of days to preserve expired job.
				 */
				$delete_expired_jobs_days = apply_filters( 'jb_cron_delete_expired_jobs_days', 30 );

				$job_ids = get_posts(
					array(
						'post_type'      => 'jb-job',
						'post_status'    => 'jb-expired',
						'fields'         => 'ids',
						'date_query'     => array(
							array(
								'column' => 'post_modified',
								'before' => gmdate( 'Y-m-d', strtotime( '-' . $delete_expired_jobs_days . ' days' ) ),
							),
						),
						'posts_per_page' => -1,
					)
				);

				if ( $job_ids ) {
					foreach ( $job_ids as $job_id ) {
						wp_trash_post( $job_id );
					}
				}
			}
		}

		/**
		 * Maintenance task to send expiration reminders jobs.
		 *
		 * @since 1.0
		 */
		public function check_for_reminder_expired_jobs() {
			$duration = JB()->options()->get( 'job-duration' );
			$reminder = JB()->options()->get( 'job-expiration-reminder' );
			$days     = absint( JB()->options()->get( 'job-expiration-reminder-time' ) );

			if ( ! empty( $duration ) && ! empty( $reminder ) && ! empty( $days ) && $days < $duration ) {
				$time    = gmdate( 'Y-m-d', strtotime( '+' . $days . ' days' ) );
				$args    = array(
					'post_type'      => 'jb-job',
					'post_status'    => 'publish',
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'key'     => 'jb-is-expiration-reminded',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => 'jb-expiry-date',
							'value'   => $time,
							'compare' => '<=',
						),
						array(
							'key'     => 'jb-expiry-date',
							'value'   => '',
							'compare' => '!=',
						),
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
					),
					'fields'         => 'ids',
					'posts_per_page' => - 1,
				);
				$job_ids = get_posts( $args );

				/**
				 * Filters the Job IDs for reminder about expired jobs.
				 *
				 * @since 1.1.0
				 * @hook jb_check_for_reminder_expired_jobs_job_ids
				 *
				 * @param {array} $job_ids Job IDs.
				 * @param {array} $args    \WP_Query arguments.
				 *
				 * @return {array} Filtered Job IDs.
				 */
				$job_ids = apply_filters( 'jb_check_for_reminder_expired_jobs_job_ids', $job_ids, $args );
				$job_ids = array_unique( $job_ids );

				if ( ! empty( $job_ids ) && ! is_wp_error( $job_ids ) ) {
					$wp_timezone = wp_timezone();

					foreach ( $job_ids as $job_id ) {
						// when debug then endless email about expiration for checking the email content and subject
						$debug = ( defined( 'JB_CRON_DEBUG' ) && JB_CRON_DEBUG );
						if ( ! $debug ) {
							update_post_meta( $job_id, 'jb-is-expiration-reminded', true );
						}

						$author_id = get_post_field( 'post_author', $job_id );
						if ( empty( $author_id ) ) {
							continue;
						}

						$time = $this->get_expiry_date_raw( $job_id );
						if ( empty( $time ) || '0000-00-00' === $time ) {
							continue;
						}

						$datetime = date_create_immutable_from_format( 'Y-m-d', $time, $wp_timezone );
						if ( false === $datetime ) {
							continue;
						}

						$origin   = current_datetime();
						$target   = $datetime->setTimezone( $wp_timezone );
						$interval = $origin->diff( $target );

						$user = get_userdata( $author_id );
						JB()->common()->mail()->send(
							$user->user_email,
							'job_expiration_reminder',
							array(
								'job_id'              => $job_id,
								'job_title'           => get_the_title( $job_id ),
								'job_author'          => $user->display_name,
								'job_expiration_days' => $interval->format( '%a' ),
								'view_job_url'        => get_permalink( $job_id ),
							)
						);
					}
				}
			}
		}

		/**
		 * Deletes old previewed jobs to keep the DB clean.
		 *
		 * @since 1.0
		 */
		public function delete_old_previews() {
			// Delete old jobs stuck in preview.
			$job_ids = get_posts(
				array(
					'post_type'      => 'jb-job',
					'post_status'    => 'jb-preview',
					'fields'         => 'ids',
					'date_query'     => array(
						array(
							'column' => 'post_modified',
							'before' => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
						),
					),
					'posts_per_page' => -1,
				)
			);

			if ( ! empty( $job_ids ) && ! is_wp_error( $job_ids ) ) {
				foreach ( $job_ids as $job_id ) {
					wp_delete_post( $job_id, true );
				}
			}
		}

		/**
		 * Make location data secured after the response from GoogleMaps API
		 *
		 * @param $data
		 *
		 * @return array|mixed|object
		 */
		public function sanitize_location_data( $data ) {
			return $this->map_deep( $data, array( $this, 'sanitize_location_data_cb' ) );
		}

		/**
		 * See the function's reference documented in wp-includes/formatting.php -> map_deep()
		 * Passed $key for getting different sanitizing in the callback
		 *
		 * @param $value
		 * @param $callback
		 * @param null|string $key
		 *
		 * @return array|mixed|object
		 */
		public function map_deep( $value, $callback, $key = null ) {
			$temp_value = array();
			if ( is_array( $value ) ) {
				foreach ( $value as $index => $item ) {
					$index_sanitized                = is_string( $index ) ? sanitize_key( $index ) : $index;
					$temp_value[ $index_sanitized ] = $this->map_deep( $item, $callback, $index_sanitized );
				}
				$value = $temp_value;
			} elseif ( is_object( $value ) ) {
				$temp_value  = (object) $temp_value;
				$object_vars = get_object_vars( $value );
				foreach ( $object_vars as $property_name => $property_value ) {
					$property_name_sanitized              = is_string( $property_name ) ? sanitize_key( $property_name ) : $property_name;
					$temp_value->$property_name_sanitized = $this->map_deep( $property_value, $callback, $property_name_sanitized );
				}
				$value = $temp_value;
			} else {
				$value = call_user_func( $callback, $value, $key );
			}

			return $value;
		}

		/**
		 * Sanitize Location Data response
		 *
		 * @param mixed $value
		 * @param null|string $key
		 *
		 * @return float|int|string
		 */
		public function sanitize_location_data_cb( $value, $key = null ) {
			if ( is_numeric( $value ) ) {
				if ( is_int( $value ) ) {
					$value = (int) $value;
				} elseif ( is_float( $value ) ) {
					$value = (float) $value;
				}
			} elseif ( is_string( $value ) ) {
				if ( isset( $key ) && 'adr_address' === $key ) {
					$value = wp_kses_post( $value );
				} else {
					$value = sanitize_text_field( $value );
				}
			}

			return $value;
		}

		/**
		 * Validate URL string
		 *
		 * @param string $url
		 *
		 * @return bool
		 *
		 * @since 1.1.0
		 */
		public function validate_url( $url ) {
			$regex  = '((https?)\:\/\/)?';
			$regex .= '([a-z0-9-.]*)\.([a-z]{2,3})';
			$regex .= '(\/([a-z0-9+\$_-]\.?)+)*\/?';
			$regex .= '(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?';

			if ( preg_match( "/^$regex$/i", $url ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Recursive function for building categories tree
		 *
		 * @param array $terms
		 * @param array $children Terms hierarchy
		 * @param int $parent_id
		 * @param int $level
		 *
		 * @return array
		 */
		public function prepare_categories_options( $terms, $children, $parent_id = 0, $level = 0 ) {
			$structured_terms = array();

			foreach ( $terms as $key => $term ) {
				if ( (int) $term->parent !== $parent_id ) {
					continue;
				}

				$term->level = $level;

				$structured_terms[] = array( $term );

				unset( $terms[ $key ] );

				if ( isset( $children[ $term->term_id ] ) ) {
					$structured_terms[] = $this->prepare_categories_options( array_values( $terms ), $children, $term->term_id, $level + 1 );
				}
			}

			$structured_terms = array_merge( ...$structured_terms );

			return array_values( $structured_terms );
		}

		/**
		 * @param WP_Post|array $job
		 *
		 * @return bool
		 */
		public function approve_job( $job ) {
			if ( 'pending' !== $job->post_status ) {
				return false;
			}

			$job_id = $job->ID;

			$args = array(
				'ID'          => $job_id,
				'post_status' => 'publish',
			);

			// a fix for restored from trash pending jobs
			if ( 0 === strpos( $job->post_name, '__trashed' ) ) {
				$args['post_name'] = sanitize_title( $job->post_title );
			}

			wp_update_post( $args );

			delete_post_meta( $job_id, 'jb-had-pending' );

			$job  = get_post( $job_id );
			$user = get_userdata( $job->post_author );
			if ( ! empty( $user ) && ! is_wp_error( $user ) ) {
				$email_args = array(
					'job_id'       => $job_id,
					'job_title'    => $job->post_title,
					'job_author'   => $user->display_name,
					'view_job_url' => get_permalink( $job ),
				);
				JB()->common()->mail()->send( $user->user_email, 'job_approved', $email_args );
			}

			/**
			 * Fires after Job has been approved.
			 *
			 * @since 1.1.0
			 * @hook jb_job_is_approved
			 *
			 * @param {int}     $post_id Post ID.
			 * @param {WP_Post} $post    The post object.
			 *
			 * @example <caption>Updates job post meta after approving.</caption>
			 * function my_custom_jb_job_is_approved( $job_id, $job ) {
			 *     update_post_meta( $job_id, 'set_some_meta_key_after_approve', true );
			 * }
			 * add_action( 'jb_job_is_approved', 'my_custom_jb_job_is_approved', 10, 2 );
			 */
			do_action( 'jb_job_is_approved', $job_id, $job );

			return true;
		}
	}
}
