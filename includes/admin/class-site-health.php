<?php
namespace jb\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\admin\Site_Health' ) ) {


	/**
	 * Class Site_Health
	 *
	 * @package jb\admin
	 */
	class Site_Health {


		/**
		 * Site_Health constructor.
		 */
		public function __construct() {
			add_filter( 'debug_information', array( $this, 'debug_information' ), 20, 1 );
		}


		private function get_roles() {
			global $wp_roles;

			$roles = array();
			if ( ! empty( $wp_roles ) ) {
				$roles = $wp_roles->role_names;
			}

			return $roles;
		}


		private function get_filled_jobs_count() {
			$query_args = array(
				'post_type'      => 'jb-job',
				'post_status'    => array( 'publish' ),
				'posts_per_page' => -1,
				'order'          => 'DESC',
				'meta_query'     => array(
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
				),
			);
			$filled     = new \WP_Query( $query_args );

			return $filled->found_posts;
		}


		private function get_categories() {
			$categories = get_terms(
				'jb-job-category',
				array(
					'hide_empty' => false,
				)
			);

			$categories_list = array();
			$count           = count( $categories );

			if ( $count > 20 ) {
				for ( $i = 0; $i < 19; $i++ ) {
					$categories_list[] = $categories[ $i ]->name . ' (ID#' . $categories[ $i ]->term_id . ')';
				}
				$categories_text = implode( ', ', $categories_list ) . __( ' and more', 'jobboardwp' );
			} else {
				foreach ( $categories as $category ) {
					$categories_list[] = $category->name . ' (ID#' . $category->term_id . ')';
				}
				$categories_text = implode( ', ', $categories_list );
			}

			return $categories_text;
		}


		private function get_types() {
			$categories = get_terms(
				'jb-job-type',
				array(
					'hide_empty' => false,
				)
			);

			$categories_list = array();
			$count           = count( $categories );

			if ( $count > 20 ) {
				for ( $i = 0; $i < 19; $i++ ) {
					if ( get_term_meta( $categories[ $i ]->term_id, 'jb-background', true ) ) {
						$color = get_term_meta( $categories[ $i ]->term_id, 'jb-color', true );
					} else {
						$color = __( 'no', 'jobboardwp' );
					}
					if ( get_term_meta( $categories[ $i ]->term_id, 'jb-background', true ) ) {
						$background = get_term_meta( $categories[ $i ]->term_id, 'jb-background', true );
					} else {
						$background = __( 'no', 'jobboardwp' );
					}
					$categories_list[] = $categories[ $i ]->name . ' (ID#' . $categories[ $i ]->term_id . ' | ' . __( 'color: ', 'jobboardwp' ) . $color . ' | ' . __( 'background: ', 'jobboardwp' ) . $background . ')';
				}
				$categories_text = implode( ', ', $categories_list ) . __( ' and more', 'jobboardwp' );
			} else {
				foreach ( $categories as $category ) {
					if ( get_term_meta( $category->term_id, 'jb-background', true ) ) {
						$color = get_term_meta( $category->term_id, 'jb-color', true );
					} else {
						$color = __( 'no', 'jobboardwp' );
					}
					if ( get_term_meta( $category->term_id, 'jb-background', true ) ) {
						$background = get_term_meta( $category->term_id, 'jb-background', true );
					} else {
						$background = __( 'no', 'jobboardwp' );
					}
					$categories_list[] = $category->name . ' (ID#' . $category->term_id . ' | ' . __( 'color: ', 'jobboardwp' ) . $color . ' | ' . __( 'background: ', 'jobboardwp' ) . $background . ')';
				}
				$categories_text = implode( ', ', $categories_list );
			}

			return $categories_text;
		}


		/**
		 * Add our data to Site Health information.
		 *
		 * @since 3.0
		 *
		 * @param array $info The Site Health information.
		 *
		 * @return array The updated Site Health information.
		 */
		public function debug_information( $info ) {
			$labels = array(
				'yes'     => __( 'Yes', 'jobboardwp' ),
				'no'      => __( 'No', 'jobboardwp' ),
				'all'     => __( 'All', 'jobboardwp' ),
				'default' => __( 'Default', 'jobboardwp' ),
				'nopages' => __( 'No predefined page', 'jobboardwp' ),
			);

			$options_categories = array(
				''        => __( 'Wordpress native post template', 'jobboardwp' ),
				'default' => __( 'Default job template', 'jobboardwp' ),
			);

			$options_template = array(
				'relative' => __( 'Relative to the posting date (e.g., 1 hour, 1 day, 1 week ago)', 'jobboardwp' ),
				'default'  => __( 'Default date format set via WP > Settings > General', 'jobboardwp' ),
			);

			$options_editing = array(
				0 => __( 'Users cannot edit their published job listings', 'jobboardwp' ),
				1 => __( 'Users can edit their published job listings but edits require approval by admin', 'jobboardwp' ),
				2 => __( 'Users can edit their published job listing without approval by admin', 'jobboardwp' ),
			);

			$options_application = array(
				'email' => __( 'Email addresses', 'jobboardwp' ),
				'url'   => __( 'Website URL', 'jobboardwp' ),
				''      => __( 'Email address or website URL', 'jobboardwp' ),
			);

			$roles = $this->get_roles();

			$jobs_data = (array) wp_count_posts( 'jb-job' );

			// Pages settings
			$pages = apply_filters(
				'jb_debug_information_pages',
				array(
					'Jobs'           => null !== JB()->options()->get( 'jobs_page' ) ? get_the_title( JB()->options()->get( 'jobs_page' ) ) . ' (ID#' . JB()->options()->get( 'jobs_page' ) . ') | ' . get_permalink( JB()->options()->get( 'jobs_page' ) ) : $labels['nopages'],
					'Post Job'       => null !== JB()->options()->get( 'job-post_page' ) ? get_the_title( JB()->options()->get( 'job-post_page' ) ) . ' (ID#' . JB()->options()->get( 'job-post_page' ) . ') | ' . get_permalink( JB()->options()->get( 'job-post_page' ) ) : $labels['nopages'],
					'Jobs Dashboard' => null !== JB()->options()->get( 'jobs-dashboard_page' ) ? get_the_title( JB()->options()->get( 'jobs-dashboard_page' ) ) . ' (ID#' . JB()->options()->get( 'jobs-dashboard_page' ) . ') | ' . get_permalink( JB()->options()->get( 'jobs-dashboard_page' ) ) : $labels['nopages'],
				)
			);

			$info['jobboard'] = array(
				'label'       => __( 'JobBoard', 'jobboardwp' ),
				'description' => __( 'This debug information about JobBoard plugin.', 'jobboardwp' ),
				'fields'      => array(
					'jb-jobboardwp-pages'                   => array(
						'label' => __( 'Pages', 'jobboardwp' ),
						'value' => $pages,
					),
					'jb-jobboardwp-job-categories'          => array(
						'label' => __( 'Job Categories', 'jobboardwp' ),
						'value' => JB()->options()->get( 'job-categories' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-job-template'            => array(
						'label' => __( 'Job Template', 'jobboardwp' ),
						'value' => $options_categories[ JB()->options()->get( 'job-template' ) ],
					),
					'jb-jobboardwp-job-dateformat'          => array(
						'label' => __( 'Date format', 'jobboardwp' ),
						'value' => $options_template[ JB()->options()->get( 'job-dateformat' ) ],
					),
					'jb-jobboardwp-job-breadcrumbs'         => array(
						'label' => __( 'Show breadcrumbs on the job page', 'jobboardwp' ),
						'value' => JB()->options()->get( 'job-breadcrumbs' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-googlemaps-api-key'      => array(
						'label' => __( 'GoogleMaps API key', 'jobboardwp' ),
						'value' => JB()->options()->get( 'googlemaps-api-key' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-disable-structured-data' => array(
						'label' => __( 'Disable Google structured data', 'jobboardwp' ),
						'value' => JB()->options()->get( 'disable-structured-data' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-account-required'        => array(
						'label' => __( 'Account Needed', 'jobboardwp' ),
						'value' => JB()->options()->get( 'account-required' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-account-creation'        => array(
						'label' => __( 'User Registration', 'jobboardwp' ),
						'value' => JB()->options()->get( 'account-creation' ) ? $labels['yes'] : $labels['no'],
					),
				),
			);

			if ( 1 === (int) JB()->options()->get( 'account-creation' ) ) {
				$info['jobboard' ]['fields'] = array_merge(
					$info['jobboard' ]['fields'],
					array(
						'jb-jobboardwp-account-username-generate' => array(
							'label' => __( 'Use email addresses as usernames', 'jobboardwp' ),
							'value' => JB()->options()->get( 'account-username-generate' ) ? $labels['yes'] : $labels['no'],
						),
						'jb-jobboardwp-account-password-email'    => array(
							'label' => __( 'Email password link', 'jobboardwp' ),
							'value' => JB()->options()->get( 'account-password-email' ) ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			$info['jobboard' ]['fields'] = array_merge(
				$info['jobboard' ]['fields'],
				array(
					'jb-jobboardwp-your-details-section' => array(
						'label' => __( '"Your Details" for logged in users', 'jobboardwp' ),
						'value' => ! empty( JB()->options()->get( 'your-details-section' ) ) ? __( 'Visible with editable email, first/last name fields', 'jobboardwp' ) : __( 'Hidden', 'jobboardwp' ),
					),
					'jb-jobboardwp-full-name-required'   => array(
						'label' => __( 'First and Last names required', 'jobboardwp' ),
						'value' => JB()->options()->get( 'full-name-required' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-account-role'         => array(
						'label' => __( 'User Role', 'jobboardwp' ),
						'value' => $roles[ JB()->options()->get( 'account-role' ) ],
					),
					'jb-jobboardwp-job-moderation'       => array(
						'label' => __( 'Set submissions as Pending', 'jobboardwp' ),
						'value' => JB()->options()->get( 'job-moderation' ) ? $labels['yes'] : $labels['no'],
					),
				)
			);

			if ( 1 === (int) JB()->options()->get( 'job-moderation' ) ) {
				$info['jobboard' ]['fields'] = array_merge(
					$info['jobboard' ]['fields'],
					array(
						'jb-jobboardwp-pending-job-editing' => array(
							'label' => __( 'Pending Job Edits', 'jobboardwp' ),
							'value' => JB()->options()->get( 'pending-job-editing' ) ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			$info['jobboard' ]['fields'] = array_merge(
				$info['jobboard' ]['fields'],
				array(
					'jb-jobboardwp-published-job-editing'   => array(
						'label' => __( 'Published Job Edits', 'jobboardwp' ),
						'value' => $options_editing[ JB()->options()->get( 'published-job-editing' ) ],
					),
					'jb-jobboardwp-individual-job-duration' => array(
						'label' => __( 'Show individual expiry date', 'jobboardwp' ),
						'value' => JB()->options()->get( 'individual-job-duration' ) ? $labels['yes'] : $labels['no'],
					),
				)
			);

			if ( 1 !== (int) JB()->options()->get( 'individual-job-duration' ) ) {
				$info['jobboard' ]['fields'] = array_merge(
					$info['jobboard' ]['fields'],
					array(
						'jb-jobboardwp-job-duration' => array(
							'label' => __( 'Job duration', 'jobboardwp' ),
							'value' => JB()->options()->get( 'job-duration' ),
						),
					)
				);
			}

			$info['jobboard' ]['fields'] = array_merge(
				$info['jobboard' ]['fields'],
				array(
					'jb-jobboardwp-job-expiration-reminder' => array(
						'label' => __( 'Send expiration reminder to the author? ', 'jobboardwp' ),
						'value' => JB()->options()->get( 'job-expiration-reminder' ) ? $labels['yes'] : $labels['no'],
					),
				)
			);

			if ( 1 === (int) JB()->options()->get( 'job-expiration-reminder' ) ) {
				$info['jobboard' ]['fields'] = array_merge(
					$info['jobboard' ]['fields'],
					array(
						'jb-jobboardwp-job-expiration-reminder-time' => array(
							'label' => __( 'Reminder time for "X" days', 'jobboardwp' ),
							'value' => JB()->options()->get( 'job-expiration-reminder-time' ),
						),
					)
				);
			}

			$info['jobboard' ]['fields'] = array_merge(
				$info['jobboard' ]['fields'],
				array(
					'jb-jobboardwp-required-job-type'              => array(
						'label' => __( 'Required job type', 'jobboardwp' ),
						'value' => JB()->options()->get( 'required-job-type' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-application-method'             => array(
						'label' => __( 'How to apply', 'jobboardwp' ),
						'value' => $options_application[ JB()->options()->get( 'application-method' ) ],
					),
					'jb-jobboardwp-job-submitted-notice'           => array(
						'label' => __( 'Job submitted notice', 'jobboardwp' ),
						'value' => stripslashes( JB()->options()->get( 'job-submitted-notice' ) ),
					),
					'jb-jobboardwp-jobs-list-pagination'           => array(
						'label' => __( 'Jobs per page', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-pagination' ),
					),
					'jb-jobboardwp-jobs-list-no-logo'              => array(
						'label' => __( 'Hide Logos', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-no-logo' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-jobs-list-hide-filled'          => array(
						'label' => __( 'Hide filled jobs', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-hide-filled' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-jobs-list-hide-expired'         => array(
						'label' => __( 'Hide expired jobs', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-hide-expired' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-jobs-list-hide-search'          => array(
						'label' => __( 'Hide search field', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-hide-search' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-jobs-list-hide-location-search' => array(
						'label' => __( 'Hide location field', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-hide-location-search' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-jobs-list-hide-filters'         => array(
						'label' => __( 'Hide filters', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-hide-filters' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-jobs-list-hide-job-types'       => array(
						'label' => __( 'Hide job types', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-hide-job-types' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-disable-styles'                 => array(
						'label' => __( 'Disable styles', 'jobboardwp' ),
						'value' => JB()->options()->get( 'disable-styles' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-disable-fa-styles'              => array(
						'label' => __( 'Disable FontAwesome styles', 'jobboardwp' ),
						'value' => JB()->options()->get( 'disable-fa-styles' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-uninstall-delete-settings'      => array(
						'label' => __( 'Delete settings on uninstall', 'jobboardwp' ),
						'value' => JB()->options()->get( 'uninstall-delete-settings' ) ? $labels['yes'] : $labels['no'],
					),
					'jb-jobboardwp-all-jobs'                       => array(
						'label' => __( 'All publish jobs count', 'jobboardwp' ),
						'value' => $jobs_data['publish'],
					),
					'jb-jobboardwp-expired-jobs'                   => array(
						'label' => __( 'Expired jobs count', 'jobboardwp' ),
						'value' => $jobs_data['jb-expired'],
					),
					'jb-jobboardwp-filled-jobs'                    => array(
						'label' => __( 'Filled jobs count', 'jobboardwp' ),
						'value' => $this->get_filled_jobs_count(),
					),
					'jb-jobboardwp-categories-list'                => array(
						'label' => __( 'Jobs categories', 'jobboardwp' ),
						'value' => $this->get_categories(),
					),
					'jb-jobboardwp-types-list'                     => array(
						'label' => __( 'Jobs types', 'jobboardwp' ),
						'value' => $this->get_types(),
					),
					'jb-jobboardwp-admin_email'                    => array(
						'label' => __( 'Admin E-mail Address', 'jobboardwp' ),
						'value' => JB()->options()->get( 'admin_email' ),
					),
					'jb-jobboardwp-mail_from'                      => array(
						'label' => __( 'Mail appears from', 'jobboardwp' ),
						'value' => stripslashes( JB()->options()->get( 'mail_from' ) ),
					),
					'jb-jobboardwp-mail_from_addr'                 => array(
						'label' => __( 'Mail appears from address', 'jobboardwp' ),
						'value' => JB()->options()->get( 'mail_from_addr' ),
					),
				)
			);

			foreach ( JB()->config()->get( 'email_notifications' ) as $key => $email ) {
				if ( 1 === (int) JB()->options()->get( $key . '_on' ) ) {
					$info['jobboard' ]['fields'] = array_merge(
						$info['jobboard' ]['fields'],
						array(
							'jb-jobboardwp-email_' . $key => array(
								'label' => $email['title'] . __( ' Subject', 'jobboardwp' ),
								'value' => JB()->options()->get( $key . '_sub'),
							),
							'jb-jobboardwp-email_theme_' . $key => array(
								'label' => __( 'Template ', 'jobboardwp' ) . $email['title'] . __( ' in theme?', 'jobboardwp' ),
								'value' => '' != locate_template( array( 'jobboardwp/emails/' . $key . '.php' ) ) ? $labels['yes'] : $labels['no'],
							),
						)
					);
				}
			}

			return $info;
		}
	}
}
