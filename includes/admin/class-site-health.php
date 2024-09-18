<?php
namespace jb\admin;

use WP_Query;

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
			add_filter( 'debug_information', array( $this, 'debug_information' ), 20 );
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
			$filled     = new WP_Query( $query_args );

			return $filled->found_posts;
		}


		/**
		 * Getting Job Categories list in the text format
		 *
		 * @return string
		 */
		private function get_categories() {
			$categories_text = __( 'None', 'jobboardwp' );

			$categories = get_terms(
				array(
					'taxonomy'   => 'jb-job-category',
					'hide_empty' => false,
				)
			);

			if ( empty( $categories ) || is_wp_error( $categories ) ) {
				return $categories_text;
			}

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


		/**
		 * Getting Job Types list in the text format
		 *
		 * @return string
		 */
		private function get_types() {
			$types_text = __( 'None', 'jobboardwp' );

			$types = get_terms(
				array(
					'taxonomy'   => 'jb-job-type',
					'hide_empty' => false,
				)
			);

			if ( empty( $types ) || is_wp_error( $types ) ) {
				return $types_text;
			}

			$types_list = array();
			$count      = count( $types );

			if ( $count > 20 ) {
				for ( $i = 0; $i < 19; $i++ ) {
					if ( get_term_meta( $types[ $i ]->term_id, 'jb-background', true ) ) {
						$color = get_term_meta( $types[ $i ]->term_id, 'jb-color', true );
					} else {
						$color = __( 'no', 'jobboardwp' );
					}
					if ( get_term_meta( $types[ $i ]->term_id, 'jb-background', true ) ) {
						$background = get_term_meta( $types[ $i ]->term_id, 'jb-background', true );
					} else {
						$background = __( 'no', 'jobboardwp' );
					}
					$types_list[] = $types[ $i ]->name . ' (ID#' . $types[ $i ]->term_id . ' | ' . __( 'color: ', 'jobboardwp' ) . $color . ' | ' . __( 'background: ', 'jobboardwp' ) . $background . ')';
				}
				$types_text = implode( ', ', $types_list ) . __( ' and more', 'jobboardwp' );
			} else {
				foreach ( $types as $type ) {
					if ( get_term_meta( $type->term_id, 'jb-background', true ) ) {
						$color = get_term_meta( $type->term_id, 'jb-color', true );
					} else {
						$color = __( 'no', 'jobboardwp' );
					}
					if ( get_term_meta( $type->term_id, 'jb-background', true ) ) {
						$background = get_term_meta( $type->term_id, 'jb-background', true );
					} else {
						$background = __( 'no', 'jobboardwp' );
					}
					$types_list[] = $type->name . ' (ID#' . $type->term_id . ' | ' . __( 'color: ', 'jobboardwp' ) . $color . ' | ' . __( 'background: ', 'jobboardwp' ) . $background . ')';
				}
				$types_text = implode( ', ', $types_list );
			}

			return $types_text;
		}


		private function get_active_modules() {
			$modules        = JB()->modules()->get_list();
			$active_modules = array();
			if ( ! empty( $modules ) ) {
				foreach ( $modules as $slug => $data ) {
					if ( JB()->modules()->is_active( $slug ) ) {
						$active_modules[ $slug ] = $data['title'];
					}
				}
			}

			return $active_modules;
		}


		/**
		 * Add our data to Site Health information.
		 *
		 * @since 1.2.1
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

			$pages_array      = array();
			$predefined_pages = JB()->config()->get( 'predefined_pages' );
			foreach ( $predefined_pages as $slug => $data ) {
				$option_key = JB()->options()->get_predefined_page_option_key( $slug );
				$wp_page_id = JB()->options()->get( $option_key );

				$pages_array[ $data['title'] ] = null !== $wp_page_id ? get_the_title( $wp_page_id ) . ' (ID#' . $wp_page_id . ') | ' . get_permalink( $wp_page_id ) : $labels['nopages'];
			}

			/**
			 * Filters Page settings array on Site Health screen.
			 *
			 * @since 1.2.1
			 * @hook jb_debug_information_pages
			 *
			 * @param {array} $pages JobBoardWP Pages list in the format: {Predefined page title} => "{Assigned WordPress Page title} (ID# {Page ID}) | {Page permalink}"
			 *
			 * @return {array} JobBoardWP Pages list.
			 */
			$pages = apply_filters( 'jb_debug_information_pages', $pages_array );

			$info['jobboardwp'] = array(
				'label'       => __( 'JobBoardWP', 'jobboardwp' ),
				'description' => __( 'This debug information about JobBoardWP plugin.', 'jobboardwp' ),
				'fields'      => array(
					'predefined-pages'        => array(
						'label' => __( 'Pages', 'jobboardwp' ),
						'value' => $pages,
					),
					'job-categories'          => array(
						'label' => __( 'Job Categories', 'jobboardwp' ),
						'value' => JB()->options()->get( 'job-categories' ) ? $labels['yes'] : $labels['no'],
					),
					'job-template'            => array(
						'label' => __( 'Job Template', 'jobboardwp' ),
						'value' => $options_categories[ JB()->options()->get( 'job-template' ) ],
					),
					'job-dateformat'          => array(
						'label' => __( 'Date format', 'jobboardwp' ),
						'value' => $options_template[ JB()->options()->get( 'job-dateformat' ) ],
					),
					'job-breadcrumbs'         => array(
						'label' => __( 'Show breadcrumbs on the job page', 'jobboardwp' ),
						'value' => JB()->options()->get( 'job-breadcrumbs' ) ? $labels['yes'] : $labels['no'],
					),
					'job-salary'              => array(
						'label' => __( 'Job Salary', 'jobboardwp' ),
						'value' => JB()->options()->get( 'job-salary' ) ? $labels['yes'] : $labels['no'],
					),
					'googlemaps-api-key'      => array(
						'label' => __( 'GoogleMaps API key', 'jobboardwp' ),
						'value' => JB()->options()->get( 'googlemaps-api-key' ) ? $labels['yes'] : $labels['no'],
					),
					'disable-structured-data' => array(
						'label' => __( 'Disable Google structured data', 'jobboardwp' ),
						'value' => JB()->options()->get( 'disable-structured-data' ) ? $labels['yes'] : $labels['no'],
					),
					'disable-logo-cache'      => array(
						'label' => __( 'Disable Google structured data', 'jobboardwp' ),
						'value' => JB()->options()->get( 'disable-company-logo-cache' ) ? $labels['yes'] : $labels['no'],
					),
					'account-required'        => array(
						'label' => __( 'Account Needed', 'jobboardwp' ),
						'value' => JB()->options()->get( 'account-required' ) ? $labels['yes'] : $labels['no'],
					),
					'account-creation'        => array(
						'label' => __( 'User Registration', 'jobboardwp' ),
						'value' => JB()->options()->get( 'account-creation' ) ? $labels['yes'] : $labels['no'],
					),
				),
			);

			if ( JB()->options()->get( 'job-salary' ) ) {
				$currency        = JB()->options()->get( 'job-salary-currency' );
				$currencies_data = JB()->config()->get( 'currencies' );
				$currency_text   = __( 'Invalid', 'jobboardwp' );
				if ( array_key_exists( $currency, $currencies_data ) ) {
					$currency_text = $currency . ' - ' . $currencies_data[ $currency ]['label'] . ' (' . $currencies_data[ $currency ]['symbol'] . ')';
				}

				$info['jobboardwp']['fields'] = JB()->array_insert_after(
					$info['jobboardwp']['fields'],
					'job-salary',
					array(
						'job-salary-currency' => array(
							'label' => __( 'Currency', 'jobboardwp' ),
							'value' => $currency_text,
						),
						'required-job-salary' => array(
							'label' => __( 'Required job salary', 'jobboardwp' ),
							'value' => JB()->options()->get( 'required-job-salary' ) ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			if ( 1 === (int) JB()->options()->get( 'account-creation' ) ) {
				$info['jobboardwp']['fields'] = array_merge(
					$info['jobboardwp']['fields'],
					array(
						'account-username-generate' => array(
							'label' => __( 'Use email addresses as usernames', 'jobboardwp' ),
							'value' => JB()->options()->get( 'account-username-generate' ) ? $labels['yes'] : $labels['no'],
						),
						'account-password-email'    => array(
							'label' => __( 'Email password link', 'jobboardwp' ),
							'value' => JB()->options()->get( 'account-password-email' ) ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			$info['jobboardwp']['fields'] = array_merge(
				$info['jobboardwp']['fields'],
				array(
					'your-details-section' => array(
						'label' => __( '"Your Details" for logged in users', 'jobboardwp' ),
						'value' => ! empty( JB()->options()->get( 'your-details-section' ) ) ? __( 'Visible with editable email, first/last name fields', 'jobboardwp' ) : __( 'Hidden', 'jobboardwp' ),
					),
					'full-name-required'   => array(
						'label' => __( 'First and Last names required', 'jobboardwp' ),
						'value' => JB()->options()->get( 'full-name-required' ) ? $labels['yes'] : $labels['no'],
					),
					'account-role'         => array(
						'label' => __( 'User Role', 'jobboardwp' ),
						'value' => $roles[ JB()->options()->get( 'account-role' ) ],
					),
					'job-moderation'       => array(
						'label' => __( 'Set submissions as Pending', 'jobboardwp' ),
						'value' => JB()->options()->get( 'job-moderation' ) ? $labels['yes'] : $labels['no'],
					),
				)
			);

			if ( 1 === (int) JB()->options()->get( 'job-moderation' ) ) {
				$info['jobboardwp']['fields'] = array_merge(
					$info['jobboardwp']['fields'],
					array(
						'pending-job-editing' => array(
							'label' => __( 'Pending Job Edits', 'jobboardwp' ),
							'value' => JB()->options()->get( 'pending-job-editing' ) ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			$info['jobboardwp']['fields'] = array_merge(
				$info['jobboardwp']['fields'],
				array(
					'published-job-editing'   => array(
						'label' => __( 'Published Job Edits', 'jobboardwp' ),
						'value' => $options_editing[ JB()->options()->get( 'published-job-editing' ) ],
					),
					'individual-job-duration' => array(
						'label' => __( 'Show individual expiry date', 'jobboardwp' ),
						'value' => JB()->options()->get( 'individual-job-duration' ) ? $labels['yes'] : $labels['no'],
					),
				)
			);

			if ( 1 !== (int) JB()->options()->get( 'individual-job-duration' ) ) {
				$info['jobboardwp']['fields'] = array_merge(
					$info['jobboardwp']['fields'],
					array(
						'job-duration' => array(
							'label' => __( 'Job duration', 'jobboardwp' ),
							'value' => JB()->options()->get( 'job-duration' ),
						),
					)
				);
			}

			$info['jobboardwp']['fields'] = array_merge(
				$info['jobboardwp']['fields'],
				array(
					'job-expiration-reminder' => array(
						'label' => __( 'Send expiration reminder to the author? ', 'jobboardwp' ),
						'value' => JB()->options()->get( 'job-expiration-reminder' ) ? $labels['yes'] : $labels['no'],
					),
				)
			);

			if ( 1 === (int) JB()->options()->get( 'job-expiration-reminder' ) ) {
				$info['jobboardwp']['fields'] = array_merge(
					$info['jobboardwp']['fields'],
					array(
						'job-expiration-reminder-time' => array(
							'label' => __( 'Reminder time for "X" days', 'jobboardwp' ),
							'value' => JB()->options()->get( 'job-expiration-reminder-time' ),
						),
					)
				);
			}

			$info['jobboardwp']['fields'] = array_merge(
				$info['jobboardwp']['fields'],
				array(
					'required-job-type'              => array(
						'label' => __( 'Required job type', 'jobboardwp' ),
						'value' => JB()->options()->get( 'required-job-type' ) ? $labels['yes'] : $labels['no'],
					),
					'application-method'             => array(
						'label' => __( 'How to apply', 'jobboardwp' ),
						'value' => $options_application[ JB()->options()->get( 'application-method' ) ],
					),
					'job-submitted-notice'           => array(
						'label' => __( 'Job submitted notice', 'jobboardwp' ),
						'value' => JB()->options()->get( 'job-submitted-notice' ),
					),
					'jobs-list-pagination'           => array(
						'label' => __( 'Jobs per page', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-pagination' ),
					),
					'jobs-list-no-logo'              => array(
						'label' => __( 'Hide Logos', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-no-logo' ) ? $labels['yes'] : $labels['no'],
					),
					'jobs-list-hide-filled'          => array(
						'label' => __( 'Hide filled jobs', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-hide-filled' ) ? $labels['yes'] : $labels['no'],
					),
					'jobs-list-hide-expired'         => array(
						'label' => __( 'Hide expired jobs', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-hide-expired' ) ? $labels['yes'] : $labels['no'],
					),
					'jobs-list-hide-search'          => array(
						'label' => __( 'Hide search field', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-hide-search' ) ? $labels['yes'] : $labels['no'],
					),
					'jobs-list-hide-location-search' => array(
						'label' => __( 'Hide location field', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-hide-location-search' ) ? $labels['yes'] : $labels['no'],
					),
					'jobs-list-hide-filters'         => array(
						'label' => __( 'Hide filters', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-hide-filters' ) ? $labels['yes'] : $labels['no'],
					),
					'jobs-list-hide-job-types'       => array(
						'label' => __( 'Hide job types', 'jobboardwp' ),
						'value' => JB()->options()->get( 'jobs-list-hide-job-types' ) ? $labels['yes'] : $labels['no'],
					),
					'disable-styles'                 => array(
						'label' => __( 'Disable styles', 'jobboardwp' ),
						'value' => JB()->options()->get( 'disable-styles' ) ? $labels['yes'] : $labels['no'],
					),
					'disable-fa-styles'              => array(
						'label' => __( 'Disable FontAwesome styles', 'jobboardwp' ),
						'value' => JB()->options()->get( 'disable-fa-styles' ) ? $labels['yes'] : $labels['no'],
					),
					'uninstall-delete-settings'      => array(
						'label' => __( 'Delete settings on uninstall', 'jobboardwp' ),
						'value' => JB()->options()->get( 'uninstall-delete-settings' ) ? $labels['yes'] : $labels['no'],
					),
					'all-jobs'                       => array(
						'label' => __( 'All publish jobs count', 'jobboardwp' ),
						'value' => $jobs_data['publish'],
					),
					'expired-jobs'                   => array(
						'label' => __( 'Expired jobs count', 'jobboardwp' ),
						'value' => $jobs_data['jb-expired'],
					),
					'filled-jobs'                    => array(
						'label' => __( 'Filled jobs count', 'jobboardwp' ),
						'value' => $this->get_filled_jobs_count(),
					),
					'categories-list'                => array(
						'label' => __( 'Jobs categories', 'jobboardwp' ),
						'value' => $this->get_categories(),
					),
					'types-list'                     => array(
						'label' => __( 'Jobs types', 'jobboardwp' ),
						'value' => $this->get_types(),
					),
					'admin_email'                    => array(
						'label' => __( 'Admin Email Address', 'jobboardwp' ),
						'value' => JB()->options()->get( 'admin_email' ),
					),
					'mail_from'                      => array(
						'label' => __( 'Mail appears from', 'jobboardwp' ),
						'value' => JB()->options()->get( 'mail_from' ),
					),
					'mail_from_addr'                 => array(
						'label' => __( 'Mail appears from address', 'jobboardwp' ),
						'value' => JB()->options()->get( 'mail_from_addr' ),
					),
				)
			);

			foreach ( JB()->config()->get( 'email_notifications' ) as $key => $email ) {
				if ( 1 === (int) JB()->options()->get( $key . '_on' ) ) {
					$info['jobboardwp']['fields'] = array_merge(
						$info['jobboardwp']['fields'],
						array(
							'email_' . $key       => array(
								'label' => $email['title'] . __( ' Subject', 'jobboardwp' ),
								'value' => JB()->options()->get( $key . '_sub' ),
							),
							'email_theme_' . $key => array(
								'label' => __( 'Template ', 'jobboardwp' ) . $email['title'] . __( ' in theme?', 'jobboardwp' ),
								'value' => '' !== locate_template( array( 'jobboardwp/emails/' . $key . '.php' ) ) ? $labels['yes'] : $labels['no'],
							),
						)
					);
				}
			}

			// Active modules
			$active_modules = $this->get_active_modules();
			if ( empty( $active_modules ) ) {
				$active_modules_text = __( 'No (0)', 'jobboardwp' );
			} else {
				$active_modules_text = implode( ', ', $this->get_active_modules() );
			}
			$info['jobboardwp']['fields'] = array_merge(
				$info['jobboardwp']['fields'],
				array(
					'jb-active-modules' => array(
						'label' => __( 'Active modules', 'jobboardwp' ),
						'value' => $active_modules_text,
					),
				)
			);

			return $info;
		}
	}
}
