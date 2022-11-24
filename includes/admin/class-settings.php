<?php namespace jb\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\admin\Settings' ) ) {


	/**
	 * Class Settings
	 *
	 * @package jb\admin
	 */
	class Settings {

		/**
		 * Settings Config.
		 *
		 * @var array
		 *
		 * @since 1.0.0
		 */
		public $config;

		/**
		 * Data array using for sanitizing setting on save.
		 *
		 * @var array
		 *
		 * @since 1.1.0
		 */
		public $sanitize_map = array();

		/**
		 * Settings constructor.
		 */
		public function __construct() {
			add_action( 'current_screen', array( $this, 'conditional_includes' ) );
			add_action( 'admin_init', array( $this, 'permalinks_save' ) );

			add_action( 'jb_before_settings_email__content', array( $this, 'email_templates_list_table' ), 10 );
			add_filter( 'jb_section_fields', array( $this, 'email_template_fields' ), 10, 2 );

			add_action( 'init', array( $this, 'init' ), 10 );

			add_action( 'admin_init', array( $this, 'save_settings' ), 10 );

			add_filter( 'jb_change_settings_before_save', array( $this, 'save_email_templates' ) );

			add_filter( 'jb_settings_custom_subtabs', array( $this, 'settings_custom_subtabs' ), 20, 2 );
			add_filter( 'jb_settings_section_modules__content', array( $this, 'settings_modules_section' ), 20 );

			add_filter( 'jb_settings', array( $this, 'sorting_modules_options' ), 9999, 1 );
		}

		/**
		 * Add "Modules > Modules" subtab if there are any registered modules. Sorting modules by the title.
		 *
		 * @since 1.2.2
		 *
		 * @hook jb_settings
		 *
		 * @param array $settings
		 *
		 * @return array
		 */
		public function sorting_modules_options( $settings ) {
			$modules = JB()->modules()->get_list();
			if ( empty( $modules ) ) {
				return $settings;
			}

			$modules = array(
				'' => array(
					'title' => __( 'Modules', 'jobboardwp' ),
				),
			);
			if ( ! empty( $settings['modules']['sections'] ) ) {
				$settings['modules']['sections'] = $modules + $settings['modules']['sections'];
			} else {
				$settings['modules']['sections'] = $modules;
			}

			return $settings;
		}

		/**
		 * Set Modules > Modules subtab as settings pages with custom content without standard settings form.
		 *
		 * @since 1.2.2
		 *
		 * @hook jb_settings_custom_subtabs
		 *
		 * @param array  $subtabs
		 * @param string $tab
		 *
		 * @return array
		 */
		public function settings_custom_subtabs( $subtabs, $tab ) {
			$modules = JB()->modules()->get_list();
			if ( empty( $modules ) ) {
				return $subtabs;
			}

			if ( 'modules' === $tab ) {
				$subtabs = array_merge( $subtabs, array( '' ) );
			}
			return $subtabs;
		}

		/**
		 * Show Modules List_table on the Modules > Modules subtab.
		 *
		 * @since 1.2.2
		 *
		 * @hook jb_settings_section_modules__content
		 */
		public function settings_modules_section() {
			$modules = JB()->modules()->get_list();
			if ( empty( $modules ) ) {
				return;
			}
			/** @noinspection PhpIncludeInspection */
			include_once JB_PATH . 'includes/admin/class-modules-list-table.php';
		}

		/**
		 * Handler for settings forms when "Save Settings" button click.
		 *
		 *
		 * @since 1.0
		 */
		public function save_settings() {
			if ( isset( $_POST['jb-settings-action'] ) ) {
				check_admin_referer( 'jb-settings-nonce' );

				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}

				if ( 'save' !== sanitize_key( $_POST['jb-settings-action'] ) || empty( $_POST['jb_options'] ) ) {
					return;
				}

				/**
				 * Fires before saving JobBoardWP settings and after security verification that there is possible to save settings.
				 *
				 * Note: Use this hook if you need to make some action before handle saving settings via wp-admin > JobBoardWP > Settings screen.
				 *
				 * @since 1.1.0
				 * @hook jb_settings_before_save
				 */
				do_action( 'jb_settings_before_save' );

				/**
				 * Filters settings array on save handler.
				 *
				 * Note: It's the first filter after verifying nonce on save settings handler.
				 *
				 * @since 1.1.0
				 * @hook jb_change_settings_before_save
				 *
				 * @param {array} $options Options array passed from $_POST. Not sanitized yet!
				 *
				 * @return {array} Job expiration date.
				 */
				$settings = apply_filters( 'jb_change_settings_before_save', $_POST['jb_options'] );

				foreach ( $settings as $key => $value ) {
					$key = sanitize_key( $key );
					if ( isset( $this->sanitize_map[ $key ] ) ) {

						if ( is_callable( $this->sanitize_map[ $key ], true, $callable_name ) ) {
							add_filter( 'jb_settings_sanitize_' . $key, $this->sanitize_map[ $key ], 10, 1 );
						}

						switch ( $this->sanitize_map[ $key ] ) {
							case 'bool':
								$value = (bool) $value;
								break;
							case 'absint':
								$value = absint( $value );
								break;
							case 'key':
								$value = sanitize_key( $value );
								break;
							case 'email':
								$value = sanitize_email( $value );
								break;
							case 'text':
								$value = sanitize_text_field( $value );
								break;
							case 'textarea':
								$value = sanitize_textarea_field( $value );
								break;
							default:
								/**
								 * Filters settings sanitizing value on save handler.
								 *
								 * Note: It's the filter for custom settings field's sanitizing. $key - is the field's 'id'
								 *
								 * @since 1.1.0
								 * @hook jb_settings_sanitize_{$key}
								 *
								 * @param {mixed} $value Options value before sanitizing
								 *
								 * @return {mixed} Maybe sanitized option value.
								 */
								$value = apply_filters( 'jb_settings_sanitize_' . $key, $value );
								break;
						}
					}

					JB()->options()->update( $key, $value );
				}

				/**
				 * Fires after saving JobBoardWP settings and before redirect to the settings screen.
				 *
				 * Note: Use this hook if you need to make some action after handle saving settings via wp-admin > JobBoardWP > Settings screen.
				 *
				 * @since 1.1.0
				 * @hook jb_settings_save
				 */
				do_action( 'jb_settings_save' );

				//redirect after save settings
				$arg = array(
					'page'   => 'jb-settings',
					'update' => 'jb_settings_updated',
				);
				if ( ! empty( $_GET['tab'] ) ) {
					$arg['tab'] = sanitize_key( $_GET['tab'] );
				}
				if ( ! empty( $_GET['section'] ) ) {
					$arg['section'] = sanitize_key( $_GET['section'] );
				}

				// phpcs:ignore WordPress.Security.SafeRedirect -- admin screen redirect
				wp_redirect( add_query_arg( $arg, admin_url( 'admin.php' ) ) );
				exit;
			}
		}

		/**
		 * Sanitize emails separated by the "," symbol and put them back to the same string but sanitized.
		 *
		 * @param string $value
		 *
		 * @return string
		 */
		public function multi_email_sanitize( $value ) {
			$emails_array = explode( ',', $value );
			if ( ! empty( $emails_array ) ) {
				$emails_array = array_map( 'sanitize_email', array_map( 'trim', $emails_array ) );
			}

			$emails_array = array_filter( array_unique( $emails_array ) );
			$value        = implode( ',', $emails_array );

			return $value;
		}

		/**
		 * Set JB Settings.
		 *
		 * @since 1.0
		 */
		public function init() {
			$general_pages_fields = array();
			foreach ( JB()->config()->get( 'predefined_pages' ) as $slug => $page ) {
				$option_key = JB()->options()->get_predefined_page_option_key( $slug );

				$options    = array();
				$page_value = '';

				/**
				 * Filters predefined value for predefined page ID.
				 *
				 * Note: It's an internal hook for integration with multilingual plugins.
				 *
				 * @since 1.1.0
				 * @hook jb_admin_settings_pages_list_value
				 *
				 * @param {bool|int} $pre_result `false` or predefined page ID from multilingual plugins option value.
				 * @param {string}   $option_key Setting key.
				 *
				 * @return {bool|int} Predefined page ID. Otherwise `false`.
				 */
				$pre_result = apply_filters( 'jb_admin_settings_pages_list_value', false, $option_key );
				if ( false === $pre_result ) {
					$opt_value = JB()->options()->get( $option_key );
					if ( ! empty( $opt_value ) ) {
						$page_exists = get_post( $opt_value );
						if ( $page_exists ) {
							$title = get_the_title( $opt_value );
							$title = ( mb_strlen( $title ) > 50 ) ? mb_substr( $title, 0, 49 ) . '...' : $title;
							// translators: %1$s is a post title; %2$s is a post ID.
							$title = sprintf( __( '%1$s (ID: %2$s)', 'jobboardwp' ), $title, $opt_value );

							$options    = array( $opt_value => $title );
							$page_value = $opt_value;
						}
					}
				} else {
					// `page_value` variable that we transfer from 3rd-party hook for getting filtered option value also
					$page_value = $pre_result['page_value'];
					unset( $pre_result['page_value'] );

					$options = $pre_result;
				}

				$page_title = ! empty( $page['title'] ) ? $page['title'] : '';

				$general_pages_fields[] = array(
					'id'          => $option_key,
					'type'        => 'page_select',
					// translators: %s: page title
					'label'       => sprintf( __( '%s page', 'jobboardwp' ), $page_title ),
					'options'     => $options,
					'value'       => $page_value,
					'placeholder' => __( 'Choose a page...', 'jobboardwp' ),
					'size'        => 'small',
				);

				$this->sanitize_map[ $option_key ] = 'absint';
			}

			$job_templates = array(
				''        => __( 'Wordpress native post template', 'jobboardwp' ),
				'default' => __( 'Default job template', 'jobboardwp' ),
			);

			$custom_templates = JB()->common()->job()->get_templates();
			if ( count( $custom_templates ) ) {
				$job_templates = array_merge( $job_templates, $custom_templates );
			}

			global $wp_roles;

			$roles = array();
			if ( ! empty( $wp_roles ) ) {
				$roles = $wp_roles->role_names;
			}

			$this->sanitize_map = array_merge(
				$this->sanitize_map,
				array(
					'job-categories'                 => 'bool',
					'job-template'                   => 'text',
					'job-categories-filter'          => 'bool',
					'job-dateformat'                 => 'text',
					'googlemaps-api-key'             => 'text',
					'account-required'               => 'bool',
					'account-creation'               => 'bool',
					'account-username-generate'      => 'bool',
					'account-password-email'         => 'bool',
					'your-details-section'           => 'bool',
					'full-name-required'             => 'bool',
					'account-role'                   => 'key',
					'job-moderation'                 => 'bool',
					'pending-job-editing'            => 'bool',
					'published-job-editing'          => 'absint',
					'individual-job-duration'        => 'bool',
					'job-duration'                   => 'text',
					'job-expiration-reminder'        => 'bool',
					'job-expiration-reminder-time'   => 'text',
					'required-job-type'              => 'bool',
					'application-method'             => 'text',
					'job-submitted-notice'           => 'text',
					'jobs-list-pagination'           => 'absint',
					'jobs-list-no-logo'              => 'bool',
					'jobs-list-hide-filled'          => 'bool',
					'jobs-list-hide-expired'         => 'bool',
					'jobs-list-hide-search'          => 'bool',
					'jobs-list-hide-location-search' => 'bool',
					'jobs-list-hide-filters'         => 'bool',
					'jobs-list-hide-job-types'       => 'bool',
					'admin_email'                    => array( &$this, 'multi_email_sanitize' ),
					'mail_from'                      => 'text',
					'mail_from_addr'                 => 'email',
					'disable-styles'                 => 'bool',
					'disable-fa-styles'              => 'bool',
					'uninstall-delete-settings'      => 'bool',
					'job_submitted'                  => 'textarea',
					'job_submitted_sub'              => 'text',
					'job_submitted_on'               => 'bool',
					'job_approved'                   => 'textarea',
					'job_approved_sub'               => 'text',
					'job_approved_on'                => 'bool',
					'job_edited'                     => 'textarea',
					'job_edited_on'                  => 'bool',
					'job_edited_sub'                 => 'text',
				)
			);
			/**
			 * Filters JobBoardWP Settings fields sanitizing type.
			 *
			 * @since 1.2.2
			 * @hook jb_settings_sanitizing_map
			 *
			 * @param {array} $sanitize_map Settings fields sanitizing type.
			 *
			 * @return {array} Settings fields sanitizing type.
			 */
			$this->sanitize_map = apply_filters( 'jb_settings_sanitizing_map', $this->sanitize_map );

			$settings = array(
				'general' => array(
					'title'    => __( 'General', 'jobboardwp' ),
					'sections' => array(
						'pages'          => array(
							'title'  => __( 'Pages', 'jobboardwp' ),
							'fields' => $general_pages_fields,
						),
						'job'            => array(
							'title'  => __( 'Job', 'jobboardwp' ),
							'fields' => array(
								array(
									'id'      => 'job-categories',
									'type'    => 'checkbox',
									'label'   => __( 'Job Categories', 'jobboardwp' ),
									'helptip' => __( 'Enable categories for jobs.', 'jobboardwp' ),
								),
								array(
									'id'      => 'job-categories-filter',
									'type'    => 'checkbox',
									'label'   => __( 'Filters on Job Categories and Types pages', 'jobboardwp' ),
									'helptip' => __( 'Enable filters on jobs categories and types pages.', 'jobboardwp' ),
								),
								array(
									'id'      => 'job-template',
									'type'    => 'select',
									'options' => $job_templates,
									'label'   => __( 'Job Template', 'jobboardwp' ),
									'helptip' => __( 'Select which template you would like applied to the job CPT.', 'jobboardwp' ),
									'size'    => 'medium',
								),
								array(
									'id'      => 'job-dateformat',
									'type'    => 'select',
									'options' => array(
										'relative' => __( 'Relative to the posting date (e.g., 1 hour, 1 day, 1 week ago)', 'jobboardwp' ),
										'default'  => __( 'Default date format set via WP > Settings > General', 'jobboardwp' ),
									),
									'label'   => __( 'Date format', 'jobboardwp' ),
									'helptip' => __( 'Select the date format used for jobs on the front-end.', 'jobboardwp' ),
								),
								array(
									'id'    => 'job-breadcrumbs',
									'type'  => 'checkbox',
									'label' => __( 'Show breadcrumbs on the job page', 'jobboardwp' ),
									'size'  => 'medium',
								),
								array(
									'id'      => 'googlemaps-api-key',
									'type'    => 'text',
									'label'   => __( 'GoogleMaps API key', 'jobboardwp' ),
									'helptip' => __( 'Enable using GoogleMaps API for getting extended data about job location.', 'jobboardwp' ),
									'size'    => 'medium',
								),
								array(
									'id'      => 'disable-structured-data',
									'type'    => 'checkbox',
									'label'   => __( 'Disable Google structured data', 'jobboardwp' ),
									'helptip' => __( 'Disable parsing an individual job page as "Google JobPosting" data by the robots.', 'jobboardwp' ),
									'size'    => 'medium',
								),
							),
						),
						'job_submission' => array(
							'title'  => __( 'Job Submission', 'jobboardwp' ),
							'fields' => array(
								array(
									'id'      => 'account-required',
									'type'    => 'checkbox',
									'label'   => __( 'Account Needed', 'jobboardwp' ),
									'helptip' => __( 'Require users to be logged-in before they can submit a job.', 'jobboardwp' ),
								),
								array(
									'id'      => 'account-creation',
									'type'    => 'checkbox',
									'label'   => __( 'User Registration', 'jobboardwp' ),
									'helptip' => __( 'Allow users to create an account when submitting a job listing.', 'jobboardwp' ),
								),
								array(
									'id'          => 'account-username-generate',
									'type'        => 'checkbox',
									'label'       => __( 'Use email addresses as usernames', 'jobboardwp' ),
									'helptip'     => __( 'Hide the username field on the submission form and set the username as the user\'s email address.', 'jobboardwp' ),
									'conditional' => array( 'account-creation', '=', '1' ),
								),
								array(
									'id'          => 'account-password-email',
									'type'        => 'checkbox',
									'label'       => __( 'Email password link', 'jobboardwp' ),
									'helptip'     => __( 'Hide password field on submission form and send users an email with a link to set password.', 'jobboardwp' ),
									'conditional' => array( 'account-creation', '=', '1' ),
								),
								array(
									'id'      => 'your-details-section',
									'type'    => 'select',
									'label'   => __( '"Your Details" for logged in users', 'jobboardwp' ),
									'options' => array(
										0 => __( 'Hidden', 'jobboardwp' ),
										1 => __( 'Visible with editable email, first/last name fields', 'jobboardwp' ),
									),
									'helptip' => __( 'Select if the "Your Details" section is shown for logged in users.', 'jobboardwp' ),
									'size'    => 'medium',
								),
								array(
									'id'          => 'full-name-required',
									'type'        => 'checkbox',
									'label'       => __( 'First and Last names required', 'jobboardwp' ),
									'helptip'     => __( 'Make the first and last name fields required.', 'jobboardwp' ),
									'conditional' => array( 'account-creation||your-details-section', '=', 1 ),
								),
								array(
									'id'          => 'account-role',
									'type'        => 'select',
									'label'       => __( 'User Role', 'jobboardwp' ),
									'options'     => $roles,
									'helptip'     => __( 'New registered users who are created during submission will be assigned this role.', 'jobboardwp' ),
									'size'        => 'small',
									'conditional' => array( 'account-creation', '=', '1' ),
								),
								array(
									'id'      => 'job-moderation',
									'type'    => 'checkbox',
									'label'   => __( 'Set submissions as Pending', 'jobboardwp' ),
									'helptip' => __( 'New job submissions will not appear on the jobs list until approved by admin.', 'jobboardwp' ),
								),
								array(
									'id'          => 'pending-job-editing',
									'type'        => 'checkbox',
									'label'       => __( 'Pending Job Edits', 'jobboardwp' ),
									'helptip'     => __( 'Allow users to edit their pending jobs until they are approved by an admin.', 'jobboardwp' ),
									'conditional' => array( 'job-moderation', '=', '1' ),
								),
								array(
									'id'      => 'published-job-editing',
									'type'    => 'select',
									'label'   => __( 'Published Job Edits', 'jobboardwp' ),
									'options' => array(
										0 => __( 'Users cannot edit their published job listings', 'jobboardwp' ),
										1 => __( 'Users can edit their published job listings but edits require approval by admin', 'jobboardwp' ),
										2 => __( 'Users can edit their published job listing without approval by admin', 'jobboardwp' ),
									),
									'helptip' => __( 'Select if users can edit their published jobs and if edits require admin approval.', 'jobboardwp' ),
									'size'    => 'medium',
								),
								array(
									'id'      => 'individual-job-duration',
									'type'    => 'checkbox',
									'label'   => __( 'Show individual expiry date', 'jobboardwp' ),
									'helptip' => __( 'Allow users to set the job expiry date on the job posting form.', 'jobboardwp' ),
								),
								array(
									'id'          => 'job-duration',
									'type'        => 'text',
									'label'       => __( 'Job duration', 'jobboardwp' ),
									'helptip'     => __( 'Set how long you want jobs to appear on the jobs list. After the set duration jobs will set to expired. If you do not want jobs to have an expiration date, leave this field blank.', 'jobboardwp' ),
									'size'        => 'small',
									'conditional' => array( 'individual-job-duration', '=', '0' ),
								),
								array(
									'id'      => 'job-expiration-reminder',
									'type'    => 'checkbox',
									'label'   => __( 'Send expiration reminder to the author?', 'jobboardwp' ),
									'helptip' => __( 'Enable notification to the job author about the job expiration.', 'jobboardwp' ),
								),
								array(
									'id'          => 'job-expiration-reminder-time',
									'type'        => 'text',
									'label'       => __( 'Reminder time for "X" days', 'jobboardwp' ),
									'helptip'     => __( 'Set the number of days before expiration when the job author receives an email.', 'jobboardwp' ),
									'description' => __( 'Job duration must be longer than "X" days.', 'jobboardwp' ),
									'conditional' => array( 'job-expiration-reminder', '=', '1' ),
									'size'        => 'small',
								),
								array(
									'id'      => 'required-job-type',
									'type'    => 'checkbox',
									'label'   => __( 'Required job type', 'jobboardwp' ),
									'helptip' => __( 'Job type is required.', 'jobboardwp' ),
								),
								array(
									'id'      => 'application-method',
									'type'    => 'select',
									'label'   => __( 'How to apply', 'jobboardwp' ),
									'options' => array(
										'email' => __( 'Email addresses', 'jobboardwp' ),
										'url'   => __( 'Website URL', 'jobboardwp' ),
										''      => __( 'Email address or website URL', 'jobboardwp' ),
									),
									'helptip' => __( 'Select whether employers have to provide an email address, website URL or either for their job listing, so job seekers can apply for the job.', 'jobboardwp' ),
									'size'    => 'small',
								),
								array(
									'id'          => 'job-submitted-notice',
									'type'        => 'text',
									'label'       => __( 'Job submitted notice', 'jobboardwp' ),
									'helptip'     => __( 'The text that appears after a job has been submitted.', 'jobboardwp' ),
									'size'        => 'long',
									'conditional' => array( 'job-moderation', '=', '1' ),
								),
							),
						),
						'jobs'           => array(
							'title'  => __( 'Jobs List', 'jobboardwp' ),
							'fields' => array(
								array(
									'id'      => 'jobs-list-pagination',
									'type'    => 'number',
									'label'   => __( 'Jobs per page', 'jobboardwp' ),
									'helptip' => __( 'How many jobs would you like to appear on initial load and after clicking load more button.', 'jobboardwp' ),
									'size'    => 'small',
								),
								array(
									'id'      => 'jobs-list-no-logo',
									'type'    => 'checkbox',
									'label'   => __( 'Hide Logos', 'jobboardwp' ),
									'helptip' => __( 'If selected company logos will not appear on the jobs list.', 'jobboardwp' ),
								),
								array(
									'id'      => 'jobs-list-hide-filled',
									'type'    => 'checkbox',
									'label'   => __( 'Hide filled jobs', 'jobboardwp' ),
									'helptip' => __( 'If selected jobs that have been marked as filled will not appear on the jobs list.', 'jobboardwp' ),
								),
								array(
									'id'      => 'jobs-list-hide-expired',
									'type'    => 'checkbox',
									'label'   => __( 'Hide expired jobs', 'jobboardwp' ),
									'helptip' => __( 'If selected jobs that have expired will not appear in jobs list or in archive/search.', 'jobboardwp' ),
								),
								array(
									'id'      => 'jobs-list-hide-search',
									'type'    => 'checkbox',
									'label'   => __( 'Hide search field', 'jobboardwp' ),
									'helptip' => __( 'If selected the search field will not be displayed on the jobs list.', 'jobboardwp' ),
								),
								array(
									'id'      => 'jobs-list-hide-location-search',
									'type'    => 'checkbox',
									'label'   => __( 'Hide location field', 'jobboardwp' ),
									'helptip' => __( 'If selected the location search field will not be displayed on the jobs list.', 'jobboardwp' ),
								),
								array(
									'id'      => 'jobs-list-hide-filters',
									'type'    => 'checkbox',
									'label'   => __( 'Hide filters', 'jobboardwp' ),
									'helptip' => __( 'If selected the filters section will not be displayed on the jobs list.', 'jobboardwp' ),
								),
								array(
									'id'      => 'jobs-list-hide-job-types',
									'type'    => 'checkbox',
									'label'   => __( 'Hide job types', 'jobboardwp' ),
									'helptip' => __( 'If selected the job types filter will not be displayed on the jobs list.', 'jobboardwp' ),
								),
							),
						),
					),
				),
				'email'   => array(
					'title'  => __( 'Email', 'jobboardwp' ),
					'fields' => array(
						array(
							'id'      => 'admin_email',
							'type'    => 'text',
							'label'   => __( 'Admin E-mail Address', 'jobboardwp' ),
							'helptip' => __( 'e.g. admin@companyname.com', 'jobboardwp' ),
						),
						array(
							'id'      => 'mail_from',
							'type'    => 'text',
							'label'   => __( 'Mail appears from', 'jobboardwp' ),
							'helptip' => __( 'e.g. Site Name', 'jobboardwp' ),
						),
						array(
							'id'      => 'mail_from_addr',
							'type'    => 'text',
							'label'   => __( 'Mail appears from address', 'jobboardwp' ),
							'helptip' => __( 'e.g. admin@companyname.com', 'jobboardwp' ),
						),
					),
				),
				'styles'  => array(
					'title'  => __( 'Styles', 'jobboardwp' ),
					'fields' => array(
						array(
							'id'      => 'disable-styles',
							'type'    => 'checkbox',
							'label'   => __( 'Disable styles', 'jobboardwp' ),
							'helptip' => __( 'Check this to disable all included styling of buttons, and all other elements.', 'jobboardwp' ),
						),
						array(
							'id'      => 'disable-fa-styles',
							'type'    => 'checkbox',
							'label'   => __( 'Disable FontAwesome styles', 'jobboardwp' ),
							'helptip' => __( 'To avoid duplicates if you have enqueued FontAwesome styles you could disable it.', 'jobboardwp' ),
						),
					),
				),
				'misc'    => array(
					'title'  => __( 'Misc', 'jobboardwp' ),
					'fields' => array(
						array(
							'id'      => 'uninstall-delete-settings',
							'type'    => 'checkbox',
							'label'   => __( 'Delete settings on uninstall', 'jobboardwp' ),
							'helptip' => __( 'Once removed, this data cannot be restored.', 'jobboardwp' ),
						),
					),
				),
			);

			$modules = JB()->modules()->get_list();
			if ( ! empty( $modules ) ) {
				$settings['modules'] = array(
					'title'  => __( 'Modules', 'jobboardwp' ),
					'fields' => array(),
				);
			}

			/**
			 * Filters JobBoardWP Settings fields.
			 *
			 * @since 1.1.0
			 * @hook jb_settings
			 *
			 * @param {array} $fields JobBoardWP Settings.
			 *
			 * @return {array} JobBoardWP Settings.
			 */
			$this->config = apply_filters( 'jb_settings', $settings );
		}

		/**
		 * Display Email Notifications Templates List
		 *
		 * @since 1.0
		 */
		public function email_templates_list_table() {
			// phpcs:ignore WordPress.Security.NonceVerification
			$email_key           = empty( $_GET['email'] ) ? '' : sanitize_key( urldecode( $_GET['email'] ) );
			$email_notifications = JB()->config()->get( 'email_notifications' );

			if ( empty( $email_key ) || empty( $email_notifications[ $email_key ] ) ) {
				/** @noinspection PhpIncludeInspection */
				include_once JB()->admin()->templates_path . 'settings' . DIRECTORY_SEPARATOR . 'emails-list-table.php';
			}
		}

		/**
		 * Edit email template fields
		 *
		 * @param array $fields
		 * @param string $tab
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function email_template_fields( $fields, $tab ) {
			if ( 'email' !== $tab ) {
				return $fields;
			}

			// phpcs:ignore WordPress.Security.NonceVerification
			$email_key           = empty( $_GET['email'] ) ? '' : sanitize_key( urldecode( $_GET['email'] ) );
			$email_notifications = JB()->config()->get( 'email_notifications' );

			if ( empty( $email_key ) || empty( $email_notifications[ $email_key ] ) ) {
				return $fields;
			}

			/**
			 * Filters JobBoardWP Settings > Email section fields.
			 *
			 * @since 1.1.0
			 * @hook jb_settings_email_section_fields
			 *
			 * @param {array}  $fields    JobBoardWP Settings > Email section fields.
			 * @param {string} $email_key Email notification key.
			 *
			 * @return {array} JobBoardWP Settings > Email section fields.
			 */
			$fields = apply_filters(
				'jb_settings_email_section_fields',
				array(
					array(
						'id'    => 'jb_email_template',
						'type'  => 'hidden',
						'value' => $email_key,
					),
					array(
						'id'      => $email_key . '_on',
						'type'    => 'checkbox',
						'label'   => $email_notifications[ $email_key ]['title'],
						'helptip' => $email_notifications[ $email_key ]['description'],
					),
					array(
						'id'          => $email_key . '_sub',
						'type'        => 'text',
						'label'       => __( 'Subject Line', 'jobboardwp' ),
						'helptip'     => __( 'This is the subject line of the e-mail', 'jobboardwp' ),
						'conditional' => array( $email_key . '_on', '=', '1' ),
					),
					array(
						'id'          => $email_key,
						'type'        => 'textarea',
						'label'       => __( 'Message Body', 'jobboardwp' ),
						'helptip'     => __( 'This is the content of the e-mail', 'jobboardwp' ),
						'value'       => JB()->get_template_html( JB()->get_email_template( $email_key, false ) ),
						'conditional' => array( $email_key . '_on', '=', '1' ),
						'args'        => array(
							'textarea_rows' => 7,
						),
					),
				),
				$email_key
			);

			return $fields;
		}

		/**
		 * Include admin files conditionally.
		 *
		 * @since 1.0
		 */
		public function conditional_includes() {
			$screen = get_current_screen();
			if ( ! $screen ) {
				return;
			}

			switch ( $screen->id ) {
				case 'options-permalink':
					add_settings_field(
						JB()->options()->get_key( 'job-slug' ),
						__( 'Job base', 'jobboardwp' ),
						array( $this, 'job_base_slug_input' ),
						'permalink',
						'optional'
					);
					add_settings_field(
						JB()->options()->get_key( 'job-type-slug' ),
						__( 'Job type base', 'jobboardwp' ),
						array( $this, 'job_type_slug_input' ),
						'permalink',
						'optional'
					);

					if ( JB()->options()->get( 'job-categories' ) ) {
						add_settings_field(
							JB()->options()->get_key( 'job-category-slug' ),
							__( 'Job category base', 'jobboardwp' ),
							array( $this, 'job_category_slug_input' ),
							'permalink',
							'optional'
						);
					}
					break;
			}
		}

		/**
		 * Show a slug input box for job post type slug.
		 *
		 * @since 1.0
		 */
		public function job_base_slug_input() {
			$defaults = JB()->config()->get( 'defaults' );
			?>
			<input name="<?php echo esc_attr( JB()->options()->get_key( 'job-slug' ) ); ?>" type="text" class="regular-text code" value="<?php echo esc_attr( JB()->options()->get( 'job-slug' ) ); ?>" placeholder="<?php echo esc_attr( $defaults['job-slug'] ); ?>" />
			<?php
		}

		/**
		 * Show a slug input box for job type slug.
		 *
		 * @since 1.0
		 */
		public function job_type_slug_input() {
			$defaults = JB()->config()->get( 'defaults' );
			?>
			<input name="<?php echo esc_attr( JB()->options()->get_key( 'job-type-slug' ) ); ?>" type="text" class="regular-text code" value="<?php echo esc_attr( JB()->options()->get( 'job-type-slug' ) ); ?>" placeholder=""<?php echo esc_attr( $defaults['job-type-slug'] ); ?>" />
			<?php
		}

		/**
		 * Show a slug input box for job category slug.
		 *
		 * @since 1.0
		 */
		public function job_category_slug_input() {
			$defaults = JB()->config()->get( 'defaults' );
			?>
			<input name="<?php echo esc_attr( JB()->options()->get_key( 'job-category-slug' ) ); ?>" type="text" class="regular-text code" value="<?php echo esc_attr( JB()->options()->get( 'job-category-slug' ) ); ?>" placeholder=""<?php echo esc_attr( $defaults['job-category-slug'] ); ?>" />
			<?php
		}

		/**
		 * Save permalinks handler
		 *
		 * @since 1.0
		 */
		public function permalinks_save() {
			if ( isset( $_POST['permalink_structure'] ) ) {
				check_admin_referer( 'update-permalink' );

				$job_base_key      = JB()->options()->get_key( 'job-slug' );
				$job_type_base_key = JB()->options()->get_key( 'job-type-slug' );

				$job_base      = isset( $_POST[ $job_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $job_base_key ] ) ) : '';
				$job_type_base = isset( $_POST[ $job_type_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $job_type_base_key ] ) ) : '';

				JB()->options()->update( 'job-slug', $job_base );
				JB()->options()->update( 'job-type-slug', $job_type_base );

				if ( JB()->options()->get( 'job-categories' ) ) {
					$job_category_base_key = JB()->options()->get_key( 'job-category-slug' );
					$job_category_base     = isset( $_POST[ $job_category_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $job_category_base_key ] ) ) : '';
					JB()->options()->update( 'job-category-slug', $job_category_base );
				}
			}
		}

		/**
		 * Generate pages tabs
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function tabs_menu() {
			// phpcs:ignore WordPress.Security.NonceVerification
			$current_tab = empty( $_GET['tab'] ) ? '' : sanitize_key( urldecode( $_GET['tab'] ) );
			if ( empty( $current_tab ) ) {
				$all_tabs    = array_keys( $this->config );
				$current_tab = $all_tabs[0];
			}

			$i    = 0;
			$tabs = '';
			foreach ( $this->config as $slug => $tab ) {
				if ( empty( $tab['fields'] ) && empty( $tab['sections'] ) ) {
					continue;
				}

				$link_args = array(
					'page' => 'jb-settings',
				);
				if ( ! empty( $i ) ) {
					$link_args['tab'] = $slug;
				}

				$tab_link = add_query_arg(
					$link_args,
					admin_url( 'admin.php' )
				);

				$active = ( $current_tab === $slug ) ? 'nav-tab-active' : '';

				/** @noinspection HtmlUnknownTarget */
				$tabs .= sprintf(
					'<a href="%s" class="nav-tab %s">%s</a>',
					esc_attr( $tab_link ),
					esc_attr( $active ),
					esc_html( $tab['title'] )
				);

				$i++;
			}

			return '<h2 class="nav-tab-wrapper jb-nav-tab-wrapper">' . $tabs . '</h2>';
		}

		/**
		 * Generate sub-tabs
		 *
		 * @param string $tab
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function subtabs_menu( $tab = '' ) {
			if ( empty( $tab ) ) {
				$all_tabs = array_keys( $this->config );
				$tab      = $all_tabs[0];
			}

			if ( empty( $this->config[ $tab ]['sections'] ) ) {
				return '';
			}

			$current_tab    = empty( $_GET['tab'] ) ? '' : sanitize_key( urldecode( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$current_subtab = empty( $_GET['section'] ) ? '' : sanitize_key( urldecode( $_GET['section'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			if ( empty( $current_subtab ) ) {
				$sections       = array_keys( $this->config[ $tab ]['sections'] );
				$current_subtab = $sections[0];
			}

			$i       = 0;
			$subtabs = '';
			foreach ( $this->config[ $tab ]['sections'] as $slug => $subtab ) {
				$custom_section = $this->section_is_custom( $current_tab, $slug );

				if ( ! $custom_section && empty( $subtab['fields'] ) ) {
					continue;
				}

				$link_args = array(
					'page' => 'jb-settings',
				);
				if ( ! empty( $current_tab ) ) {
					$link_args['tab'] = $current_tab;
				}
				if ( ! empty( $i ) ) {
					$link_args['section'] = $slug;
				}

				$tab_link = add_query_arg(
					$link_args,
					admin_url( 'admin.php' )
				);

				$active = ( $current_subtab === $slug ) ? 'current' : '';

				/** @noinspection HtmlUnknownTarget */
				$subtabs .= sprintf(
					'<a href="%s" class="%s">%s</a> | ',
					esc_attr( $tab_link ),
					esc_attr( $active ),
					esc_html( $subtab['title'] )
				);

				$i++;
			}

			return '<div><ul class="subsubsub">' . substr( $subtabs, 0, -3 ) . '</ul></div>';
		}

		/**
		 * Render settings section
		 *
		 * @param string $current_tab
		 * @param string $current_subtab
		 *
		 * @return false|string
		 *
		 * @since 1.0
		 */
		public function display_section( $current_tab, $current_subtab ) {
			$fields = $this->get_settings( $current_tab, $current_subtab );

			if ( ! $fields ) {
				return '';
			}

			return JB()->admin()->forms(
				array(
					'class'     => 'jb-options-' . $current_tab . '-' . $current_subtab . ' jb-third-column',
					'prefix_id' => 'jb_options',
					'fields'    => $fields,
				)
			)->display( false );
		}

		/**
		 * Get settings section
		 *
		 * @param string $tab
		 * @param string $section
		 * @param bool $assoc Return Associated array
		 *
		 * @return bool|array
		 *
		 * @since 1.0
		 */
		public function get_settings( $tab = '', $section = '', $assoc = false ) {
			if ( empty( $tab ) ) {
				$tabs = array_keys( $this->config );
				$tab  = $tabs[0];
			}

			if ( ! isset( $this->config[ $tab ] ) ) {
				return false;
			}

			if ( ! empty( $section ) && empty( $this->config[ $tab ]['sections'] ) ) {
				return false;
			}

			if ( ! empty( $this->config[ $tab ]['sections'] ) ) {
				if ( empty( $section ) ) {
					$sections = array_keys( $this->config[ $tab ]['sections'] );
					$section  = $sections[0];
				}

				if ( isset( $this->config[ $tab ]['sections'] ) && ! isset( $this->config[ $tab ]['sections'][ $section ] ) ) {
					return false;
				}

				if ( ! empty( $this->config[ $tab ]['sections'][ $section ]['fields'] ) ) {
					$fields = $this->config[ $tab ]['sections'][ $section ]['fields'];
				} else {
					$fields = array();
				}
			} else {
				$fields = $this->config[ $tab ]['fields'];
			}

			/**
			 * Filters JobBoardWP settings fields inside a section.
			 *
			 * @since 1.0
			 * @hook jb_section_fields
			 *
			 * @param {array}  $fields  Settings fields of the current section.
			 * @param {string} $tab     Settings tab.
			 * @param {string} $section Settings section.
			 *
			 * @return {array} Setting's section fields.
			 */
			$fields = apply_filters( 'jb_section_fields', $fields, $tab, $section );

			$assoc_fields = array();
			foreach ( $fields as &$data ) {
				if ( ! isset( $data['value'] ) && isset( $data['id'] ) ) {
					$data['value'] = JB()->options()->get( $data['id'] );
				}

				if ( $assoc ) {
					$assoc_fields[ $data['id'] ] = $data;
				}
			}

			return $assoc ? $assoc_fields : $fields;
		}

		/**
		 * Checking if the settings section is custom
		 *
		 * @param string $current_tab
		 * @param string $current_subtab
		 *
		 * @return bool
		 *
		 * @since 1.0
		 */
		public function section_is_custom( $current_tab, $current_subtab ) {
			/**
			 * Filters JobBoardWP settings custom tabs.
			 *
			 * @since 1.0
			 * @hook jb_settings_custom_tabs
			 *
			 * @param {array} $tabs Settings custom tabs. It's empty array by default.
			 *
			 * @return {array} Settings custom tabs.
			 */
			$custom_tabs = apply_filters( 'jb_settings_custom_tabs', array() );

			/**
			 * Filters JobBoardWP settings custom tabs.
			 *
			 * @since 1.0
			 * @hook jb_settings_custom_subtabs
			 *
			 * @param {array}  $subtabs Settings custom subtabs. It's empty array by default.
			 * @param {string} $tab     Settings tab.
			 *
			 * @return {array} Settings custom subtabs.
			 */
			$custom_subtabs = apply_filters( 'jb_settings_custom_subtabs', array(), $current_tab );

			$custom_section = in_array( $current_tab, $custom_tabs, true ) || in_array( $current_subtab, $custom_subtabs, true );
			return $custom_section;
		}

		/**
		 * @param $settings
		 *
		 * @global \WP_Filesystem_Base $wp_filesystem Subclass
		 *
		 * @return mixed
		 */
		public function save_email_templates( $settings ) {
			/** @var $wp_filesystem \WP_Filesystem_Base */
			global $wp_filesystem;

			if ( empty( $settings['jb_email_template'] ) ) {
				return $settings;
			}

			$template = sanitize_key( $settings['jb_email_template'] );
			$content  = stripslashes( sanitize_textarea_field( $settings[ $template ] ) );

			$template_name = JB()->get_email_template( $template );
			$module        = JB()->get_email_template_module( $template );

			$template_path = JB()->template_path( $module );

			$template_locations = array(
				trailingslashit( $template_path ) . $template_name,
			);

			/** This filter is documented in includes/class-jb-functions.php */
			$template_locations = apply_filters( 'jb_pre_template_locations', $template_locations, $template_name, $module, $template_path );

			// build multisite blog_ids priority paths
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();

				$ms_template_locations = array_map(
					function( $item ) use ( $template_path, $blog_id ) {
						return str_replace( trailingslashit( $template_path ), trailingslashit( $template_path ) . $blog_id . '/', $item );
					},
					$template_locations
				);

				$template_locations = array_merge( $ms_template_locations, $template_locations );
			}

			/** This filter is documented in includes/class-jb-functions.php */
			$template_locations = apply_filters( 'jb_template_locations', $template_locations, $template_name, $module, $template_path );
			$template_locations = array_map( 'wp_normalize_path', $template_locations );
			/**
			 * Filters the email templates locations on save handler.
			 *
			 * @since 1.1.1
			 * @since 1.2.2 Added $module argument.
			 * @hook jb_save_email_templates_locations
			 *
			 * @param {array}  $template_locations Template locations array for WP native `locate_template()` function.
			 * @param {string} $template_name      Template name.
			 * @param {string} $module             Module slug. (default: '').
			 * @param {string} $template_path      Template path. (default: '').
			 *
			 * @return {array} An array for WP native `locate_template()` function with paths where we need to search for the $template_name.
			 */
			$template_locations = apply_filters( 'jb_save_email_templates_locations', $template_locations, $template_name, $module, $template_path );

			/** This filter is documented in includes/class-jb-functions.php */
			$custom_path = apply_filters( 'jb_template_structure_custom_path', false, $template_name, $module );
			if ( false === $custom_path || ! is_dir( $custom_path ) ) {
				$template_exists = locate_template( $template_locations );
			} else {
				$template_exists = JB()->locate_template_custom_path( $template_locations, $custom_path );
			}

			if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once ABSPATH . 'wp-admin/includes/file.php';

				$credentials = request_filesystem_credentials( self_admin_url() );
				\WP_Filesystem( $credentials );
			}

			if ( empty( $template_exists ) ) {
				if ( false === $custom_path || ! is_dir( $custom_path ) ) {
					$base_dir = trailingslashit( get_stylesheet_directory() );
				} else {
					$base_dir = trailingslashit( $custom_path );
				}
				$template_exists = wp_normalize_path( $base_dir . $template_locations[0] );

				$default_template_path = wp_normalize_path( trailingslashit( JB()->default_templates_path( $module ) ) . $template_name );

				if ( file_exists( $default_template_path ) ) {
					$dirname = dirname( $template_exists );

					if ( wp_mkdir_p( $dirname ) ) {
						$wp_filesystem->copy( $default_template_path, $template_exists );
					}
				}
			}

			$result = $wp_filesystem->put_contents( $template_exists, $content );

			if ( false !== $result ) {
				unset( $settings['jb_email_template'] );
				unset( $settings[ $template ] );
			}

			return $settings;
		}
	}
}
