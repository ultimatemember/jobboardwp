<?php namespace jb\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\admin\Settings' ) ) {


	/**
	 * Class Settings
	 *
	 * @package jb\admin
	 */
	class Settings {


		/**
		 * @var array
		 */
		var $config;


		/**
		 * Settings constructor.
		 */
		function __construct() {
			add_action( 'current_screen', [ $this, 'conditional_includes' ] );
			add_action( 'admin_init', [ $this, 'permalinks_save' ] );

			add_action( 'jb_before_settings_email__content', [ $this, 'email_templates_list_table' ], 10 );
			add_filter( 'jb_section_fields', [ $this, 'email_template_fields' ], 10, 3 );

			add_action( 'init', [ $this, 'init' ], 10 );

			add_action( 'admin_init', [ $this, 'save_settings' ], 10 );
		}


		/**
		 * Handler for settings forms
		 * when "Save Settings" button click
		 *
		 */
		function save_settings() {
			if ( ! isset( $_POST['jb-settings-action'] ) || 'save' !== $_POST['jb-settings-action'] ) {
				return;
			}

			if ( empty( $_POST['jb_options'] ) ) {
				return;
			}

			$nonce = ! empty( $_POST['__jbnonce'] ) ? $_POST['__jbnonce'] : '';
			if ( ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'jb-settings-nonce' ) ) ||
				 ! current_user_can( 'manage_options' ) ) {

				// This nonce is not valid.
				wp_die( 'Security Check', 'jobboardwp' );
			}

			do_action( 'jb_settings_before_save' );

			$settings = apply_filters( 'jb_change_settings_before_save', $_POST['jb_options'] );

			foreach ( $settings as $key => $value ) {
				JB()->options()->update( $key, $value );
			}

			do_action( 'jb_settings_save' );

			//redirect after save settings
			$arg = [
				'page' => 'jb-settings',
			];
			if ( ! empty( $_GET['tab'] ) ) {
				$arg['tab'] = $_GET['tab'];
			}
			if ( ! empty( $_GET['section'] ) ) {
				$arg['section'] = $_GET['section'];
			}

			wp_redirect( add_query_arg( $arg, admin_url( 'admin.php' ) ) );
			exit;
		}


		/**
		 * Set JB Settings
		 */
		function init() {
			$pages = get_posts(
				[
					'post_type'         => 'page',
					'post_status'       => 'publish',
					'posts_per_page'    => -1,
					'fields'            => [ 'ID', 'post_title', ]
				]
			);
			$page_options = [ '' => __( '(None)', 'jobboardwp' ), ];
			if ( ! empty( $pages ) ) {
				foreach ( $pages as $page ) {
					$page_options[ $page->ID ] = $page->post_title;
				}
			}

			$general_pages_fields = [];
			foreach ( JB()->config()->get( 'core_pages' ) as $page_id => $page ) {
				$page_title = ! empty( $page['title'] ) ? $page['title'] : '';

				$general_pages_fields[] = [
					'id'            => $page_id . '_page',
					'type'          => 'select',
					'label'         => sprintf( __( '%s page', 'jobboardwp' ), $page_title ),
					'options'       => $page_options,
					'placeholder'   => __( 'Choose a page...', 'jobboardwp' ),
					'size'          => 'small',
				];
			}

			$job_templates = [
				''          => __( 'Wordpress native post template', 'jobboardwp' ),
				'default'   => __( 'Default job template', 'jobboardwp' )
			];

			$custom_templates = JB()->common()->job()->get_templates();
			if ( count( $custom_templates ) ) {
				$job_templates = array_merge( $job_templates, $custom_templates );
			}

			global $wp_roles;

			$roles = [];
			if ( ! empty( $wp_roles ) ) {
				$roles = $wp_roles->role_names;
			}

			$this->config = apply_filters( 'jb_settings', [
				'general'   => [
					'title'     => __( 'General', 'jobboardwp' ),
					'sections'  => [
						'pages' => [
							'title'     => __( 'Pages', 'jobboardwp' ),
							'fields'    => $general_pages_fields,
						],
						'job'  => [
							'title'     => __( 'Job', 'jobboardwp' ),
							'fields'    => [
								[
									'id'        => 'job-categories',
									'type'      => 'checkbox',
									'label'     => __( 'Job Categories', 'jobboardwp' ),
									'helptip'   => __( 'Enable categories for jobs.', 'jobboardwp' ),
								],
								[
									'id'        => 'job-template',
									'type'      => 'select',
									'options'   => $job_templates,
									'label'     => __( 'Job Template', 'jobboardwp' ),
									'helptip'   => __( 'Select which template you would like applied to the job CPT.', 'jobboardwp' ),
									'size'      => 'medium',
								],
								[
									'id'        => 'job-dateformat',
									'type'      => 'select',
									'options'   => [
										'relative' => __( 'Relative to the posting date (e.g., 1 hour, 1 day, 1 week ago)', 'jobboardwp' ),
										'default'  => __( 'Default date format set via WP > Settings > General', 'jobboardwp' ),
									],
									'label'     => __( 'Date format', 'jobboardwp' ),
									'helptip'   => __( 'Select the date format used for jobs on the front-end.', 'jobboardwp' ),
								],
							],
						],
						'job_submission'  => [
							'title'     => __( 'Job Submission', 'jobboardwp' ),
							'fields'    => [
								[
									'id'        => 'account-required',
									'type'      => 'checkbox',
									'label'     => __( 'Account Required', 'jobboardwp' ),
									'helptip'   => __( 'Limits job listing submissions to registered, logged-in users.', 'jobboardwp' ),
								],
								[
									'id'        => 'account-creation',
									'type'      => 'checkbox',
									'label'     => __( 'Account Creation', 'jobboardwp' ),
									'helptip'   => __( 'Includes account creation on the listing submission form, to allow non-registered users to create an account and submit a job listing simultaneously.', 'jobboardwp' ),
								],
								[
									'id'            => 'account-username-generate',
									'type'          => 'checkbox',
									'label'         => __( 'Generate usernames from email addresses', 'jobboardwp' ),
									'helptip'       => __( 'Automatically generates usernames for new accounts from the registrant\'s email address. If this is not enabled, a "username" field will display instead.', 'jobboardwp' ),
									'conditional'   => [ 'account-creation', '=', '1' ],
								],
								[
									'id'            => 'account-password-email',
									'type'          => 'checkbox',
									'label'         => __( 'Email new users a link to set a password', 'jobboardwp' ),
									'helptip'       => __( 'Sends an email to the user with their username and a link to set their password. If this is not enabled, a "password" field will display instead, and their email address won\'t be verified.', 'jobboardwp' ),
									'conditional'   => [ 'account-creation', '=', '1' ],
								],
								[
									'id'        => 'full-name-required',
									'type'      => 'checkbox',
									'label'     => __( 'First and Last names required', 'jobboardwp' ),
									'helptip'   => __( 'Make the first and last name fields required.', 'jobboardwp' ),
								],
								[
									'id'            => 'my-details-section',
									'type'          => 'select',
									'label'         => __( 'Logged in users "My Details" section', 'jobboardwp' ),
									'options'       => [
										'0' => __( 'Hidden', 'jobboardwp' ),
										'1' => __( 'Visible with editable email, first/last name fields', 'jobboardwp' ),
									],
									'helptip'   => __( 'Whether to display the "My Details" section for logged in users.', 'jobboardwp' ),
									'size'          => 'medium',
								],
								[
									'id'            => 'account-role',
									'type'          => 'select',
									'label'         => __( 'Account Role', 'jobboardwp' ),
									'options'       => $roles,
									'helptip'       => __( 'Any new accounts created during submission will have this role.', 'jobboardwp' ),
									'size'          => 'small',
									'conditional'   => [ 'account-creation', '=', '1' ],
								],
								[
									'id'        => 'job-moderation',
									'type'      => 'checkbox',
									'label'     => __( 'Moderate New Listings', 'jobboardwp' ),
									'helptip'   => __( 'Sets all new submissions to "pending." They will not appear on your site until an admin approves them.', 'jobboardwp' ),
								],
								[
									'id'            => 'pending-job-editing',
									'type'          => 'checkbox',
									'label'         => __( 'Allow Pending Edits', 'jobboardwp' ),
									'helptip'       => __( 'Users can continue to edit pending jobs until they are approved by an admin.', 'jobboardwp' ),
									'conditional'   => [ 'job-moderation', '=', '1' ],
								],
								[
									'id'            => 'published-job-editing',
									'type'          => 'select',
									'label'         => __( 'Allow Published Edits', 'jobboardwp' ),
									'options'       => [
										'0' => __( 'Users cannot edit', 'jobboardwp' ),
										'1' => __( 'Users can edit without admin approval', 'jobboardwp' ),
										'2' => __( 'Users can edit, but edits require admin approval', 'jobboardwp' ),
									],
									'helptip'       => __( 'Choose whether published job can be edited and if edits require admin approval.', 'jobboardwp' ),
									'size'          => 'medium',
								],
								[
									'id'        => 'job-duration',
									'type'      => 'text',
									'label'     => __( 'Job duration', 'jobboardwp' ),
									'helptip'   => __( 'Jobs will display for the set number of days, then expire. Leave this field blank if you don\'t want jobs to have an expiration date.', 'jobboardwp' ),
									'size'      => 'small',
								],
								[
									'id'            => 'required-job-type',
									'type'          => 'checkbox',
									'label'         => __( 'Required job type', 'jobboardwp' ),
									'helptip'       => __( 'Job type is required.', 'jobboardwp' ),
								],
								[
									'id'            => 'application-method',
									'type'          => 'select',
									'label'         => __( 'Application Method', 'jobboardwp' ),
									'options'       => [
										''      => __( 'Email address or website URL', 'jobboardwp' ),
										'email' => __( 'Email addresses only', 'jobboardwp' ),
										'url'   => __( 'Website URLs only', 'jobboardwp' ),
									],
									'helptip'       => __( 'Choose the application method employers will need to provide. Specify URL or email address only, or allow employers to choose which they prefer.', 'jobboardwp' ),
									'size'          => 'small',
								],
								[
									'id'        => 'job-submitted-notice',
									'type'      => 'text',
									'label'     => __( 'Job successfully submitted notice', 'jobboardwp' ),
									'helptip'   => __( 'Visible when job is successfully submitted.', 'jobboardwp' ),
									'size'      => 'long',
								],
							],
						],
						'jobs'  => [
							'title'     => __( 'Jobs List', 'jobboardwp' ),
							'fields'    => [
								[
									'id'        => 'jobs-list-pagination',
									'type'      => 'number',
									'label'     => __( 'Jobs per page', 'jobboardwp' ),
									'helptip'   => __( 'Number of jobs to display per page.', 'jobboardwp' ),
									'size'      => 'small',
								],
								[
									'id'        => 'jobs-list-no-logo',
									'type'      => 'checkbox',
									'label'     => __( 'Hide Logos', 'jobboardwp' ),
									'helptip'   => __( 'Hide jobs logo on list page', 'jobboardwp' ),
								],
								[
									'id'        => 'jobs-list-hide-filled',
									'type'      => 'checkbox',
									'label'     => __( 'Hide filled jobs', 'jobboardwp' ),
									'helptip'   => __( 'Hide filled jobs on list page', 'jobboardwp' ),
								],
								[
									'id'        => 'jobs-list-hide-expired',
									'type'      => 'checkbox',
									'label'     => __( 'Hide expired jobs', 'jobboardwp' ),
									'helptip'   => __( 'Hide expired jobs in job archives/search', 'jobboardwp' ),
								],
								[
									'id'        => 'jobs-list-hide-search',
									'type'      => 'checkbox',
									'label'     => __( 'Hide search', 'jobboardwp' ),
									'helptip'   => __( 'Hide search field in the jobs list.', 'jobboardwp' ),
								],
								[
									'id'        => 'jobs-list-hide-location-search',
									'type'      => 'checkbox',
									'label'     => __( 'Hide search by the location', 'jobboardwp' ),
									'helptip'   => __( 'Hide search by the location field in the jobs list.', 'jobboardwp' ),
								],
								[
									'id'        => 'jobs-list-hide-filters',
									'type'      => 'checkbox',
									'label'     => __( 'Hide filters', 'jobboardwp' ),
									'helptip'   => __( 'Hide search filters in the jobs list.', 'jobboardwp' ),
								],
								[
									'id'        => 'jobs-list-hide-job-types',
									'type'      => 'checkbox',
									'label'     => __( 'Hide job types', 'jobboardwp' ),
									'helptip'   => __( 'Hide job types in the jobs list row.', 'jobboardwp' ),
								],
							],
						],
					]
				],
				'email'     => [
					'title'     => __( 'Email', 'jobboardwp' ),
					'fields'    => [
						[
							'id'        => 'admin_email',
							'type'      => 'text',
							'label'     => __( 'Admin E-mail Address', 'jobboardwp' ),
							'helptip'   => __( 'e.g. admin@companyname.com', 'jobboardwp' ),
						],
						[
							'id'        => 'mail_from',
							'type'      => 'text',
							'label'     => __( 'Mail appears from', 'jobboardwp' ),
							'helptip'   => __( 'e.g. Site Name', 'jobboardwp' ),
						],
						[
							'id'        => 'mail_from_addr',
							'type'      => 'text',
							'label'     => __( 'Mail appears from address', 'jobboardwp' ),
							'helptip'   => __( 'e.g. admin@companyname.com', 'jobboardwp' ),
						],
					],
				],
				'styles'      => [
					'title'     => __( 'Styles', 'jobboardwp' ),
					'fields'    => [
						[
							'id'        => 'disable-styles',
							'type'      => 'checkbox',
							'label'     => __( 'Disable styles', 'jobboardwp' ),
							'helptip'   => __( 'Check this to disable all included styling of buttons, and all other elements.', 'jobboardwp' ),
						],
						[
							'id'        => 'disable-fa-styles',
							'type'      => 'checkbox',
							'label'     => __( 'Disable FontAwesome styles', 'jobboardwp' ),
							'helptip'   => __( 'To avoid duplicates if you have enqueued FontAwesome styles you could disable it.', 'jobboardwp' ),
						],
					],
				],
				'misc'      => [
					'title'     => __( 'Misc', 'jobboardwp' ),
					'fields'    => [
						[
							'id'        => 'uninstall-delete-settings',
							'type'      => 'checkbox',
							'label'     => __( 'Delete settings on uninstall', 'jobboardwp' ),
							'helptip'   => __( 'Once removed, this data cannot be restored.', 'jobboardwp' ),
						],
					],
				],
			] );
		}


		/**
		 * Display Email Notifications Templates List
		 */
		function email_templates_list_table() {
			$email_key = empty( $_GET['email'] ) ? '' : urldecode( $_GET['email'] );
			$email_notifications = JB()->config()->get( 'email_notifications' );

			if ( empty( $email_key ) || empty( $email_notifications[ $email_key ] ) ) {
				include_once JB()->admin()->templates_path . 'settings' . DIRECTORY_SEPARATOR . 'emails-list-table.php';
			}
		}


		/**
		 * Edit email template fields
		 *
		 * @param array $fields
		 * @param string $tab
		 * @param string $section
		 *
		 * @return array
		 */
		function email_template_fields( $fields, $tab, $section ) {
			if ( 'email' !== $tab ) {
				return $fields;
			}

			$email_key = empty( $_GET['email'] ) ? '' : urldecode( $_GET['email'] );
			$email_notifications = JB()->config()->get( 'email_notifications' );
			if ( empty( $email_key ) || empty( $email_notifications[ $email_key ] ) ) {
				return $fields;
			}

			//$in_theme = UM()->mail()->template_in_theme( $email_key );
			$in_theme = false;

			$fields = apply_filters( 'jb_settings_email_section_fields', [
				[
					'id'    => 'jb_email_template',
					'type'  => 'hidden',
					'value' => $email_key,
				],
				[
					'id'        => $email_key . '_on',
					'type'      => 'checkbox',
					'label'     => $email_notifications[ $email_key ]['title'],
					'helptip'   => $email_notifications[ $email_key ]['description'],
				],
				[
					'id'            => $email_key . '_sub',
					'type'          => 'text',
					'label'         => __( 'Subject Line', 'jobboardwp' ),
					'helptip'       => __( 'This is the subject line of the e-mail', 'jobboardwp' ),
					'conditional'   => [ $email_key . '_on', '=', '1' ],
				],
				[
					'id'            => $email_key,
					'type'          => 'email_template',
					'label'         => __( 'Message Body', 'jobboardwp' ),
					'helptip'       => __( 'This is the content of the e-mail', 'jobboardwp' ),
					'value'         => JB()->common()->mail()->get_template( $email_key ),
					'in_theme'      => $in_theme,
					'conditional'   => [ $email_key . '_on', '=', '1' ],
				],
			], $email_key );

			return $fields;
		}


		/**
		 * Include admin files conditionally.
		 */
		function conditional_includes() {
			$screen = get_current_screen();
			if ( ! $screen ) {
				return;
			}
			switch ( $screen->id ) {
				case 'options-permalink':

					add_settings_field(
						JB()->options()->get_key( 'job-slug' ),
						__( 'Job base', 'jobboardwp' ),
						[ $this, 'job_base_slug_input' ],
						'permalink',
						'optional'
					);
					add_settings_field(
						JB()->options()->get_key( 'job-type-slug' ),
						__( 'Job type base', 'jobboardwp' ),
						[ $this, 'job_type_slug_input' ],
						'permalink',
						'optional'
					);

					if ( JB()->options()->get( 'job-categories' ) ) {
						add_settings_field(
							JB()->options()->get_key( 'job-category-slug' ),
							__( 'Job category base', 'jobboardwp' ),
							[ $this, 'job_category_slug_input' ],
							'permalink',
							'optional'
						);
					}
					break;
			}
		}


		/**
		 * Show a slug input box for job post type slug.
		 */
		function job_base_slug_input() {
			$defaults = JB()->config()->get( 'defaults' );
			?>
			<input name="<?php echo esc_attr( JB()->options()->get_key( 'job-slug' ) ) ?>" type="text" class="regular-text code" value="<?php echo esc_attr( JB()->options()->get( 'job-slug' ) ); ?>" placeholder="<?php echo esc_attr( $defaults['job-slug'] ); ?>" />
			<?php
		}


		/**
		 * Show a slug input box for job type slug.
		 */
		function job_type_slug_input() {
			$defaults = JB()->config()->get( 'defaults' );
			?>
			<input name="<?php echo esc_attr( JB()->options()->get_key( 'job-type-slug' ) ) ?>" type="text" class="regular-text code" value="<?php echo esc_attr( JB()->options()->get( 'job-type-slug' ) ); ?>" placeholder=""<?php echo esc_attr( $defaults['job-type-slug'] ); ?>" />
			<?php
		}


		/**
		 * Show a slug input box for job category slug.
		 */
		function job_category_slug_input() {
			$defaults = JB()->config()->get( 'defaults' );
			?>
			<input name="<?php echo esc_attr( JB()->options()->get_key( 'job-category-slug' ) ) ?>" type="text" class="regular-text code" value="<?php echo esc_attr( JB()->options()->get( 'job-category-slug' ) ); ?>" placeholder=""<?php echo esc_attr( $defaults['job-category-slug'] ); ?>" />
			<?php
		}


		/**
		 * Save permalinks handler
		 */
		function permalinks_save() {
			if ( ! isset( $_POST['permalink_structure'] ) ) {
				// We must not be saving permalinks.
				return;
			}

			$job_base_key = JB()->options()->get_key( 'job-slug' );
			$job_type_base_key = JB()->options()->get_key( 'job-type-slug' );

			$job_base = isset( $_POST[ $job_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $job_base_key ] ) ) : '';
			$job_type_base = isset( $_POST[ $job_type_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $job_type_base_key ] ) ) : '';

			JB()->options()->update( 'job-slug', $job_base );
			JB()->options()->update( 'job-type-slug', $job_type_base );

			if ( JB()->options()->get( 'job-categories' ) ) {
				$job_category_base_key = JB()->options()->get_key( 'job-category-slug' );
				$job_category_base = isset( $_POST[ $job_category_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $job_category_base_key ] ) ) : '';
				JB()->options()->update( 'job-category-slug', $job_category_base );
			}
		}


		/**
		 * Generate pages tabs
		 *
		 * @param string $page
		 *
		 * @return string
		 */
		function tabs_menu( $page = 'settings' ) {
			switch( $page ) {
				case 'settings': {
					$current_tab = empty( $_GET['tab'] ) ? '' : urldecode( $_GET['tab'] );
					if ( empty( $current_tab ) ) {
						$all_tabs = array_keys( $this->config );
						$current_tab = $all_tabs[0];
					}

					$i = 0;
					$tabs = '';
					foreach ( $this->config as $slug => $tab ) {
						if ( empty( $tab['fields'] ) && empty( $tab['sections'] ) ) {
							continue;
						}

						$link_args = [
							'page' => 'jb-settings',
						];
						if ( ! empty( $i ) ) {
							$link_args['tab'] = $slug;
						}

						$tab_link = add_query_arg(
							$link_args,
							admin_url( 'admin.php' )
						);

						$active = ( $current_tab == $slug ) ? 'nav-tab-active' : '';
						$tabs .= sprintf( "<a href=\"%s\" class=\"nav-tab %s\">%s</a>",
							$tab_link,
							$active,
							$tab['title']
						);

						$i++;
					}
					break;
				}
				default: {
					$tabs = apply_filters( 'jb_generate_tabs_menu_' . $page, '' );
					break;
				}
			}

			return '<h2 class="nav-tab-wrapper jb-nav-tab-wrapper">' . $tabs . '</h2>';
		}


		/**
		 * Generate sub-tabs
		 *
		 * @param string $tab
		 *
		 * @return string
		 */
		function subtabs_menu( $tab = '' ) {
			if ( empty( $tab ) ) {
				$all_tabs = array_keys( $this->config );
				$tab = $all_tabs[0];
			}

			if ( empty( $this->config[ $tab ]['sections'] ) ) {
				return '';
			}

			$current_tab = empty( $_GET['tab'] ) ? '' : urldecode( $_GET['tab'] );

			$current_subtab = empty( $_GET['section'] ) ? '' : urldecode( $_GET['section'] );
			if ( empty( $current_subtab ) ) {
				$sections = array_keys( $this->config[ $tab ]['sections'] );
				$current_subtab = $sections[0];
			}

			$i = 0;
			$subtabs = '';
			foreach ( $this->config[ $tab ]['sections'] as $slug => $subtab ) {

				$custom_section = $this->section_is_custom( $current_tab, $slug );

				if ( ! $custom_section && empty( $subtab['fields'] ) ) {
					continue;
				}

				$link_args = [
					'page' => 'jb-settings',
				];
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

				$active = ( $current_subtab == $slug ) ? 'current' : '';

				$subtabs .= sprintf( "<a href=\"%s\" class=\"%s\">%s</a> | ",
					$tab_link,
					$active,
					$subtab['title']
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
		 */
		function display_section( $current_tab, $current_subtab ) {
			$fields = $this->get_settings( $current_tab, $current_subtab );

			if ( ! $fields ) {
				return '';
			}

			return JB()->admin()->forms( [
				'class'     => 'jb-options-' . $current_tab . '-' . $current_subtab . ' jb-third-column',
				'prefix_id' => 'jb_options',
				'fields'    => $fields,
			] )->display( false );
		}


		/**
		 * Get settings section
		 *
		 * @param string $tab
		 * @param string $section
		 * @param bool $assoc Return Associated array
		 *
		 * @return bool|array
		 */
		function get_settings( $tab = '', $section = '', $assoc = false ) {
			if ( empty( $tab ) ) {
				$tabs = array_keys( $this->config );
				$tab = $tabs[0];
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
					$section = $sections[0];
				}

				if ( isset( $this->config[ $tab ]['sections'] ) && ! isset( $this->config[ $tab ]['sections'][ $section ] ) ) {
					return false;
				}

				$fields = $this->config[ $tab ]['sections'][ $section ]['fields'];
			} else {
				$fields = $this->config[ $tab ]['fields'];
			}

			$fields = apply_filters( 'jb_section_fields', $fields, $tab, $section );

			$assoc_fields = [];
			foreach ( $fields as &$data ) {
				if ( ! isset( $data['value'] ) ) {
					$data['value'] = JB()->options()->get( $data['id'] );
				}

				if ( $assoc ) {
					$assoc_fields[ $data['id'] ] = $data;
				}
			}

			return $assoc ? $assoc_fields : $fields;
		}


		/**
		 * @param string $current_tab
		 * @param string $current_subtab
		 *
		 * @return bool
		 */
		function section_is_custom( $current_tab, $current_subtab ) {
			$custom_section = in_array( $current_tab, apply_filters( 'jb_settings_custom_tabs', [] ) )
							  || in_array( $current_subtab, apply_filters( 'jb_settings_custom_subtabs', [], $current_tab ) );
			return $custom_section;
		}

	}
}