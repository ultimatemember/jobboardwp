<?php namespace jb\common;

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
		 * Job constructor.
		 */
		public function __construct() {
		}


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
				?>

				<div class="jb-job-type" <?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- strict output ?>>
					<?php echo esc_html( $type->name ); ?>
				</div>

				<?php
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

			$expiry_date = date_i18n( get_option( 'date_format' ), strtotime( $expiry_date ) );

			return $expiry_date;
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
			$datetime = get_post_time( 'c', false, $job_id, true );
			return $datetime;
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
			$location_data = ! empty( $location_data ) ? $location_data : '';

			return $location_data;
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
			} elseif ( empty( $location ) ) {
				return __( 'Anywhere', 'jobboardwp' );
			}

			return $location;
		}


		/**
		 * Get location link
		 *
		 * @param $location
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_location_link( $location ) {
			if ( empty( $location ) ) {
				return '';
			}
			$location = '<a href="https://maps.google.com/maps?q=' . rawurlencode( wp_strip_all_tags( $location ) ) . '&zoom=14&size=512x512&maptype=roadmap&sensor=false" target="_blank">' . $location . '</a>';
			return $location;
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
				/** @noinspection HtmlUnknownTarget */
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

			$company_data = apply_filters(
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

			return $company_data;
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
					$image        = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
					$company_logo = isset( $image[0] ) ? $image[0] : '';
				}

				return $company_logo;
			} else {
				$company_logo = get_the_post_thumbnail( $job_id, 'thumbnail', array( 'class' => 'jb-job-company-logo' ) );

				if ( ! empty( $company_logo ) ) {
					$company_logo = '<div class="jb-job-company-logo-wrapper">' . $company_logo . '</div>';
				} else {
					$company_logo = '';
				}

				return $company_logo;
			}
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
				return $can_applied;
			}

			if ( ! $this->is_filled( $job_id ) && ! in_array( $job->post_status, array( 'jb-preview', 'jb-expired' ), true ) ) {
				$can_applied = true;
			}

			$can_applied = apply_filters( 'jb_can_applied_job', $can_applied, $job_id );

			return $can_applied;
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

			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
			$response = apply_filters( 'jb-job-raw-data', $response, $job_id );

			return $response;
		}


		/**
		 * Get job actions
		 *
		 * @param int|\WP_Post $job
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

			if ( in_array( $job->post_status, array( 'jb-expired' ), true ) ) {
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

			if ( in_array( $job->post_status, array( 'pending' ), true ) ) {
				if ( JB()->options()->get( 'pending-job-editing' ) ) {
					$actions['edit'] = array(
						'href'  => $this->get_edit_link( $job->ID ),
						'title' => __( 'Edit', 'jobboardwp' ),
					);
				}
			}

			if ( in_array( $job->post_status, array( 'publish' ), true ) ) {
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
			$post_job_page = JB()->common()->permalinks()->get_preset_page_link( 'job-post' );
			return add_query_arg(
				array(
					'jb-preview' => 1,
					'job-id'     => $job_id,
					'nonce'      => wp_create_nonce( 'jb-job-preview' . $job_id ),
				),
				$post_job_page
			);
		}


		/**
		 * Get job edit link
		 *
		 * @param $job_id
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_edit_link( $job_id ) {
			$post_job_page = JB()->common()->permalinks()->get_preset_page_link( 'job-post' );
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
		 * @param int|\WP_Post $job
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
			$data['@context']   = 'http://schema.org/';
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
				$data['employmentType'] = implode( ', ', $employment_types );
			}

			$logo    = JB()->common()->job()->get_logo( $job->ID, true );
			$company = JB()->common()->job()->get_company_data( $job->ID );

			$data['hiringOrganization']          = array();
			$data['hiringOrganization']['@type'] = 'Organization';
			$data['hiringOrganization']['name']  = $company['name'];

			$company_website = $company['website'];
			if ( $company_website ) {
				$data['hiringOrganization']['sameAs'] = $company_website;
				$data['hiringOrganization']['url']    = $company_website;
			}

			if ( $logo ) {
				$data['hiringOrganization']['logo'] = $logo;
			}

			$data['identifier']          = array();
			$data['identifier']['@type'] = 'PropertyValue';
			$data['identifier']['name']  = $company['name'];
			$data['identifier']['value'] = get_the_guid( $job );

			$location = JB()->common()->job()->get_location( $job->ID, true );
			if ( ! empty( $location ) ) {
				$data['jobLocation']            = array();
				$data['jobLocation']['@type']   = 'Place';
				$data['jobLocation']['address'] = $this->get_structured_location( $job );
				if ( empty( $data['jobLocation']['address'] ) ) {
					$data['jobLocation']['address'] = $location;
				}
			}

			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
			return apply_filters( 'jb-job-structured-data', $data, $job );
		}


		/**
		 * Gets the job location data.
		 *
		 * @see http://schema.org/PostalAddress
		 *
		 * @param \WP_Post $job
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
					$address[ $schema_key ] = $value;
				}
			}

			// No address parts were found.
			if ( 1 === count( $address ) ) {
				$address = false;
			}

			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
			return apply_filters( 'jb-job-location-structured-data', $address, $job );
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

			if ( ! isset( $prefix ) ) {
				return array();
			}

			$dir = JB()->theme_templates;

			/** @var $wp_filesystem \WP_Filesystem_Base */
			global $wp_filesystem;

			if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once ABSPATH . 'wp-admin/includes/file.php';

				$credentials = request_filesystem_credentials( site_url() );
				\WP_Filesystem( $credentials );
			}

			$templates = array();
			if ( $wp_filesystem->is_dir( $dir ) ) {
				$handle = @opendir( $dir );
				// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition -- reading folder's content here
				while ( false !== ( $filename = readdir( $handle ) ) ) {
					if ( '.' === $filename || '..' === $filename || 'emails' === $filename ) {
						continue;
					}
					$clean_filename = $this->get_template_name( $filename );

					$source  = $wp_filesystem->get_contents( $dir . DIRECTORY_SEPARATOR . $filename );
					$tokens  = @\token_get_all( $source );
					$comment = array(
						T_COMMENT, // All comments since PHP5
						T_DOC_COMMENT, // PHPDoc comments
					);
					foreach ( $tokens as $token ) {
						if ( in_array( $token[0], $comment, true ) && strstr( $token[1], '/* ' . $prefix . ' Template:' ) ) {
							$txt = $token[1];
							$txt = str_replace( '/* ' . $prefix . ' Template: ', '', $txt );
							$txt = str_replace( ' */', '', $txt );

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
		 * @param $file
		 *
		 * @return mixed|string
		 *
		 * @since 1.0
		 */
		public function get_template_name( $file ) {
			$file = basename( $file );
			$file = preg_replace( '/\\.[^.\\s]{3,4}$/', '', $file );
			return $file;
		}


		/**
		 * Maintenance task to expire jobs.
		 *
		 * @since 1.0
		 */
		public function check_for_expired_jobs() {
			// Change status to expired.
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
							'compare' => '<',
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

					do_action( 'jb_job_is_expired', $job_id );
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
	}
}
