<?php
/**
 * Template for the Job's submission
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/job-submission.php
 *
 * @version 1.2.4
 *
 * @var array $jb_job_submission
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div id="jb-job-submission-form-wrapper" class="jb">

	<?php
	if ( ! is_user_logged_in() && JB()->options()->get( 'account-required' ) && ! JB()->options()->get( 'account-creation' ) ) {
		?>

		<span>
			<?php esc_html_e( 'You must sign in to create a new job.', 'jobboardwp' ); ?>
			<a class="button" href="<?php echo esc_url( wp_login_url( get_permalink(), true ) ); ?>"><?php esc_attr_e( 'Sign in', 'jobboardwp' ); ?></a>
		</span>

		<?php
	} else {

		$edit = false;
		if ( ! empty( $jb_job_submission['job'] ) ) {
			$edit = true;
		}

		$types = get_terms(
			array(
				'taxonomy'   => 'jb-job-type',
				'hide_empty' => false,
			)
		);

		$types_options = array();
		if ( empty( JB()->options()->get( 'required-job-type' ) ) ) {
			$types_options[''] = __( '(None)', 'jobboardwp' );
		}
		foreach ( $types as $t ) {
			$types_options[ $t->term_id ] = $t->name;
		}

		$categories_options = array();
		if ( JB()->options()->get( 'job-categories' ) ) {
			$categories = get_terms(
				array(
					'taxonomy'   => 'jb-job-category',
					'hide_empty' => false,
				)
			);

			$cat_children = _get_term_hierarchy( 'jb-job-category' );

			$categories = JB()->common()->job()->prepare_categories_options( $categories, $cat_children );

			if ( ! empty( $categories ) ) {
				$categories_options[''] = __( '(None)', 'jobboardwp' );
				foreach ( $categories as $category ) {
					$categories_options[ $category->term_id ] = str_repeat( '&#8211;', $category->level ) . ' ' . $category->name;
				}
			}
		}

		$job_title         = '';
		$job_location_type = '0';
		$job_location      = '';
		$job_location_data = '';
		$job_type          = '';
		$job_category      = '';
		$job_description   = '';
		$job_application   = '';
		$job_expired       = '';

		$salary_type        = '';
		$salary_amount_type = 'numeric';
		$salary_amount      = '';
		$salary_min_amount  = '';
		$salary_max_amount  = '';
		$salary_period      = '';

		$company_name      = '';
		$company_website   = '';
		$company_tagline   = '';
		$company_twitter   = '';
		$company_facebook  = '';
		$company_instagram = '';
		$company_logo      = '';

		if ( is_user_logged_in() ) {
			$c_data = JB()->common()->user()->get_company_data();

			$company_name      = $c_data['name'];
			$company_website   = $c_data['website'];
			$company_tagline   = $c_data['tagline'];
			$company_twitter   = $c_data['twitter'];
			$company_facebook  = $c_data['facebook'];
			$company_instagram = $c_data['instagram'];
			$company_logo      = $c_data['logo'];
		}

		if ( $edit ) {
			$data = JB()->common()->job()->get_raw_data( $jb_job_submission['job']->ID );

			$job_title         = $data['title'];
			$job_location_type = $data['location_type'];
			$job_location      = $data['location'];
			$job_location_data = $data['location_data'];
			$job_type          = $data['type'];

			// workaround on the submission form because Job Type isn't multiple dropdown
			if ( 1 === count( $job_type ) ) {
				$job_type = $job_type[0];
			} elseif ( empty( $job_type ) ) {
				$job_type = '';
			}

			if ( JB()->options()->get( 'job-categories' ) ) {
				$job_category = $data['category'];

				// workaround on the submission form because Job Category isn't multiple dropdown
				if ( 1 === count( $job_category ) ) {
					$job_category = $job_category[0];
				} elseif ( empty( $job_category ) ) {
					$job_category = '';
				}
			}

			$job_description = $data['description'];
			$job_application = $data['app_contact'];
			$job_expired     = $data['expires'];

			$company_name      = $data['company_name'];
			$company_website   = $data['company_website'];
			$company_tagline   = $data['company_tagline'];
			$company_twitter   = $data['company_twitter'];
			$company_facebook  = $data['company_facebook'];
			$company_instagram = $data['company_instagram'];
			$company_logo      = $data['company_logo'];

			if ( JB()->options()->get( 'job-salary' ) ) {
				$salary_type        = array_key_exists( 'salary_type', $data ) ? $data['salary_type'] : '';
				$salary_amount_type = array_key_exists( 'salary_amount_type', $data ) ? $data['salary_amount_type'] : '';
				$salary_amount      = array_key_exists( 'salary_amount', $data ) ? $data['salary_amount'] : '';
				$salary_min_amount  = array_key_exists( 'salary_min_amount', $data ) ? $data['salary_min_amount'] : '';
				$salary_max_amount  = array_key_exists( 'salary_max_amount', $data ) ? $data['salary_max_amount'] : '';
				$salary_period      = array_key_exists( 'salary_period', $data ) ? $data['salary_period'] : '';
			}
		}

		$my_details_fields = array();

		if ( is_user_logged_in() ) {

			$current_userdata = get_userdata( get_current_user_id() );

			$my_details_fields = array(
				array(
					'type'     => 'text',
					'label'    => __( 'Email', 'jobboardwp' ),
					'id'       => 'author_email',
					'required' => true,
					'value'    => $current_userdata->user_email,
				),
				array(
					'type'     => 'text',
					'label'    => __( 'First name', 'jobboardwp' ),
					'id'       => 'author_first_name',
					'required' => JB()->options()->get( 'full-name-required' ),
					'value'    => $current_userdata->first_name,
				),
				array(
					'type'     => 'text',
					'label'    => __( 'Last name', 'jobboardwp' ),
					'id'       => 'author_last_name',
					'required' => JB()->options()->get( 'full-name-required' ),
					'value'    => $current_userdata->last_name,
				),
			);
		} elseif ( JB()->options()->get( 'account-creation' ) && ! is_user_logged_in() ) {
			$my_details_fields = array(
				array(
					'type'     => 'text',
					'label'    => __( 'Email', 'jobboardwp' ),
					'id'       => 'author_email',
					'required' => JB()->options()->get( 'account-required' ),
				),
			);

			if ( ! JB()->options()->get( 'account-username-generate' ) ) {
				$my_details_fields[] = array(
					'type'     => 'text',
					'label'    => __( 'Username', 'jobboardwp' ),
					'id'       => 'author_username',
					'required' => JB()->options()->get( 'account-required' ),
				);
			}

			$my_details_fields = array_merge(
				$my_details_fields,
				array(
					array(
						'type'     => 'text',
						'label'    => __( 'First name', 'jobboardwp' ),
						'id'       => 'author_first_name',
						'required' => JB()->options()->get( 'full-name-required' ) && JB()->options()->get( 'account-required' ),
					),
					array(
						'type'     => 'text',
						'label'    => __( 'Last name', 'jobboardwp' ),
						'id'       => 'author_last_name',
						'required' => JB()->options()->get( 'full-name-required' ) && JB()->options()->get( 'account-required' ),
					),
				)
			);

			if ( ! JB()->options()->get( 'account-password-email' ) ) {
				$my_details_fields = array_merge(
					$my_details_fields,
					array(
						array(
							'type'     => 'password',
							'label'    => __( 'Password', 'jobboardwp' ),
							'id'       => 'author_password',
							'required' => JB()->options()->get( 'account-required' ),
						),
						array(
							'type'     => 'password',
							'label'    => __( 'Confirm Password', 'jobboardwp' ),
							'id'       => 'author_password_confirm',
							'required' => JB()->options()->get( 'account-required' ),
						),
					)
				);
			} else {
				$my_details_fields = array_merge(
					$my_details_fields,
					array(
						array(
							'id'    => 'auto_generate_password',
							'type'  => 'label',
							'label' => __( 'Your account details will be confirmed via email.', 'jobboardwp' ),
						),
					)
				);
			}
		}

		$buttons = array(
			'job-preview' => array(
				'type'  => 'submit',
				'label' => __( 'Preview', 'jobboardwp' ),
				'data'  => array(
					'action' => 'preview',
				),
			),
		);

		if ( is_user_logged_in() || ( JB()->options()->get( 'account-creation' ) && JB()->options()->get( 'account-required' ) ) ) {
			$buttons['job-draft'] = array(
				'type'  => 'submit',
				'label' => __( 'Save Draft', 'jobboardwp' ),
				'data'  => array(
					'action' => 'draft',
				),
			);
		}

		$app_validation = array( 'email', 'url' );
		$method         = JB()->options()->get( 'application-method' );
		if ( ! empty( $method ) ) {
			$app_validation = array( $method );
		}

		$posting = JB()->frontend()->forms(
			array(
				'id' => 'jb-job-submission',
			)
		);

		$sections = array();

		$your_details_enabled = JB()->options()->get( 'your-details-section' );
		if ( ! ( is_user_logged_in() && empty( $your_details_enabled ) ) ) {
			/**
			 * Filters HTML attributes for the My Details section.
			 *
			 * @since 1.2.2
			 * @hook jb_job_submission_strict_wrap_attrs
			 *
			 * @param {string} $strict_wrap_attrs HTML attributes for the My Details section.
			 *
			 * @return {string} HTML attributes for the My Details section. It's empty string by default.
			 */
			$strict_wrap_attrs = apply_filters( 'jb_job_submission_strict_wrap_attrs', '' );
			// phpcs:ignore WordPress.Security.NonceVerification -- getting value from GET line
			if ( isset( $_GET['login'] ) && 'failed' === sanitize_key( $_GET['login'] ) ) {
				$strict_wrap_attrs = ' style="display: none;"';
			}

			$sections['my-details'] = array(
				'title'             => __( 'Your Details', 'jobboardwp' ),
				'fields'            => $my_details_fields,
				'wrap_fields'       => true,
				'strict_wrap_attrs' => $strict_wrap_attrs,
			);
		}

		$gmap_key = JB()->options()->get( 'googlemaps-api-key' );

		$job_details_fields = array(
			array(
				'type'     => 'text',
				'label'    => __( 'Job Title', 'jobboardwp' ),
				'id'       => 'job_title',
				'required' => true,
				'value'    => $job_title,
			),
			array(
				'type'               => 'conditional_radio',
				'label'              => __( 'Job Location', 'jobboardwp' ),
				'id'                 => 'job_location_type',
				'options'            => array(
					'0' => __( 'Onsite', 'jobboardwp' ),
					'1' => __( 'Remote', 'jobboardwp' ),
					''  => __( 'Onsite or Remote', 'jobboardwp' ),
				),
				'condition_sections' => array(
					'0' => array(
						array(
							'type'        => empty( $gmap_key ) ? 'text' : 'location_autocomplete',
							'label'       => __( 'Location', 'jobboardwp' ),
							'placeholder' => __( 'City, State, or Country', 'jobboardwp' ),
							'name'        => 'job_location',
							'id'          => 'job_location-0',
							'value'       => $job_location,
							'value_data'  => $job_location_data,
							'required'    => true,
						),
					),
					'1' => array(
						array(
							'type'        => empty( $gmap_key ) ? 'text' : 'location_autocomplete',
							'label'       => __( 'Preferred Location', 'jobboardwp' ),
							'placeholder' => __( 'City, State, or Country', 'jobboardwp' ),
							'name'        => 'job_location',
							'id'          => 'job_location-1',
							'value'       => $job_location,
							'value_data'  => $job_location_data,
						),
					),
					''  => array(
						array(
							'type'        => empty( $gmap_key ) ? 'text' : 'location_autocomplete',
							'label'       => __( 'Preferred Location', 'jobboardwp' ),
							'placeholder' => __( 'City, State, or Country', 'jobboardwp' ),
							'name'        => 'job_location',
							'id'          => 'job_location-',
							'value'       => $job_location,
							'value_data'  => $job_location_data,
						),
					),
				),
				'value'              => $job_location_type,
			),
			array(
				'type'     => 'select',
				'label'    => __( 'Job Type', 'jobboardwp' ),
				'data'     => array(
					'placeholder' => __( 'Please select job type', 'jobboardwp' ),
				),
				'id'       => 'job_type',
				'class'    => ! empty( JB()->options()->get( 'required-job-type' ) ) ? 'jb-s2' : 'jb-s1',
				'options'  => $types_options,
				'value'    => $job_type,
				'required' => ! empty( JB()->options()->get( 'required-job-type' ) ),
			),
		);

		if ( JB()->options()->get( 'job-categories' ) && ! empty( $categories_options ) ) {
			$job_details_fields = array_merge(
				$job_details_fields,
				array(
					array(
						'type'    => 'select',
						'label'   => __( 'Job Category', 'jobboardwp' ),
						'data'    => array(
							'placeholder' => __( 'Please select job category', 'jobboardwp' ),
						),
						'id'      => 'job_category',
						'class'   => 'jb-s1',
						'options' => $categories_options,
						'value'   => $job_category,
					),
				)
			);
		}

		if ( JB()->options()->get( 'job-salary' ) ) {
			$currency         = JB()->options()->get( 'job-salary-currency' );
			$currency_symbols = JB()->config()->get( 'currencies' );
			$currency_symbol  = $currency_symbols[ $currency ]['symbol'];

			$job_details_fields = array_merge(
				$job_details_fields,
				array(
					array(
						'type'        => 'select',
						'label'       => __( 'Salary', 'jobboardwp' ),
						'placeholder' => __( 'Please select salary type', 'jobboardwp' ),
						'id'          => 'job_salary_type',
						'options'     => array(
							''          => __( 'Not specified', 'jobboardwp' ),
							'fixed'     => __( 'Fixed', 'jobboardwp' ),
							'recurring' => __( 'Recurring', 'jobboardwp' ),
						),
						'value'       => $salary_type,
						'required'    => ! empty( JB()->options()->get( 'required-job-salary' ) ),
					),
					array(
						'type'        => 'select',
						'label'       => __( 'Salary amount type', 'jobboardwp' ),
						'placeholder' => __( 'Please select amount type', 'jobboardwp' ),
						'id'          => 'job_salary_amount_type',
						'options'     => array(
							'numeric' => __( 'Numeric', 'jobboardwp' ),
							'range'   => __( 'Range (min-max)', 'jobboardwp' ),
						),
						'value'       => $salary_amount_type,
						'conditional' => array( 'job_salary_type', '!=', '' ),
					),
					array(
						'type'        => 'number',
						'required'    => true,
						// translators: %s - Currency symbol
						'label'       => sprintf( __( 'Salary amount %s', 'jobboardwp' ), $currency_symbol ),
						'placeholder' => __( 'Enter salary amount', 'jobboardwp' ),
						'id'          => 'job_salary_amount',
						'value'       => $salary_amount,
						'conditional' => array( 'job_salary_amount_type', '=', 'numeric' ),
						'min'         => 0,
					),
					array(
						'type'        => 'number',
						'required'    => true,
						// translators: %s - Currency symbol
						'label'       => sprintf( __( 'Salary Min Amount %s', 'jobboardwp' ), $currency_symbol ),
						'placeholder' => __( 'Enter salary min amount', 'jobboardwp' ),
						'id'          => 'job_salary_min_amount',
						'value'       => $salary_min_amount,
						'conditional' => array( 'job_salary_amount_type', '=', 'range' ),
						'min'         => 0,
					),
					array(
						'type'        => 'number',
						'required'    => true,
						// translators: %s - Currency symbol
						'label'       => sprintf( __( 'Salary Max Amount %s', 'jobboardwp' ), $currency_symbol ),
						'placeholder' => __( 'Enter salary max amount', 'jobboardwp' ),
						'id'          => 'job_salary_max_amount',
						'value'       => $salary_max_amount,
						'conditional' => array( 'job_salary_amount_type', '=', 'range' ),
						'min'         => 0,
					),
					array(
						'type'        => 'select',
						'required'    => true,
						'label'       => __( 'Salary Period', 'jobboardwp' ),
						'placeholder' => __( 'Please select salary period', 'jobboardwp' ),
						'id'          => 'job_salary_period',
						'options'     => array(
							'hour'  => __( 'Hour', 'jobboardwp' ),
							'day'   => __( 'Day', 'jobboardwp' ),
							'week'  => __( 'Week', 'jobboardwp' ),
							'month' => __( 'Month', 'jobboardwp' ),
						),
						'value'       => $salary_period,
						'conditional' => array( 'job_salary_type', '=', 'recurring' ),
					),
				)
			);
		}

		$job_application_placeholder = __( 'Enter an email address or website URL', 'jobboardwp' );
		if ( 'email' === JB()->options()->get( 'application-method' ) ) {
			$job_application_placeholder = __( 'Enter an email address', 'jobboardwp' );
		} elseif ( 'url' === JB()->options()->get( 'application-method' ) ) {
			$job_application_placeholder = __( 'Enter a website URL', 'jobboardwp' );
		}

		$job_details_fields[] = array(
			'type'     => 'wp_editor',
			'label'    => __( 'Description', 'jobboardwp' ),
			'id'       => 'job_description',
			'value'    => $job_description,
			'required' => true,
		);

		if ( JB()->options()->get( 'individual-job-duration' ) ) {
			$job_details_fields[] = array(
				'type'  => 'datepicker',
				'label' => __( 'Expired date', 'jobboardwp' ),
				'id'    => 'job_expire',
				'value' => $job_expired,
			);
		}

		$job_details_fields[] = array(
			'type'        => 'text',
			'label'       => __( 'Application Contact', 'jobboardwp' ),
			'id'          => 'job_application',
			'required'    => true,
			'value'       => $job_application,
			'placeholder' => $job_application_placeholder,
			'validation'  => $app_validation,
		);

		$sections = array_merge(
			$sections,
			array(
				'job-details'     => array(
					'title'  => __( 'Job Details', 'jobboardwp' ),
					'fields' => $job_details_fields,
				),
				'company-details' => array(
					'title'  => __( 'Company Details', 'jobboardwp' ),
					'fields' => array(
						array(
							'type'        => 'text',
							'label'       => __( 'Name', 'jobboardwp' ),
							'id'          => 'company_name',
							'required'    => true,
							'value'       => $company_name,
							'placeholder' => __( 'Enter the name of the company', 'jobboardwp' ),
						),
						array(
							'type'  => 'text',
							'label' => __( 'Website', 'jobboardwp' ),
							'id'    => 'company_website',
							'value' => $company_website,
						),
						array(
							'type'        => 'text',
							'label'       => __( 'Tagline', 'jobboardwp' ),
							'id'          => 'company_tagline',
							'value'       => $company_tagline,
							'placeholder' => __( 'Briefly describe your company', 'jobboardwp' ),
						),
						array(
							'type'  => 'text',
							'label' => __( 'Twitter username', 'jobboardwp' ),
							'id'    => 'company_twitter',
							'value' => $company_twitter,
						),
						array(
							'type'  => 'text',
							'label' => __( 'Facebook username', 'jobboardwp' ),
							'id'    => 'company_facebook',
							'value' => $company_facebook,
						),
						array(
							'type'  => 'text',
							'label' => __( 'Instagram username', 'jobboardwp' ),
							'id'    => 'company_instagram',
							'value' => $company_instagram,
						),
						array(
							'type'   => 'media',
							'label'  => __( 'Logo', 'jobboardwp' ),
							'id'     => 'company_logo',
							'labels' => array(
								'img_alt' => __( 'Company logo image', 'jobboardwp' ),
							),
							'action' => 'jb-upload-company-logo',
							'value'  => $company_logo,
						),
					),
				),
			)
		);

		/**
		 * Filters the job submission form.
		 *
		 * @since 1.2.2
		 * @hook jb_job_submission_form_args
		 *
		 * @param {array} $args Job submission form arguments. See frontend/class-form.php file for getting the list of the necessary arguments
		 *
		 * @return {array} Job submission form arguments for init.
		 */
		$posting_args = apply_filters(
			'jb_job_submission_form_args',
			array(
				'id'        => 'jb-job-submission',
				'class'     => '',
				'data'      => array(
					'account-required' => JB()->options()->get( 'account-required' ),
				),
				'prefix_id' => '',
				'sections'  => $sections,
				'hiddens'   => array(
					'jb-action'              => 'job-submission',
					'jb-job-submission-step' => 'draft||preview',
					'nonce'                  => wp_create_nonce( 'jb-job-submission' ),
				),
				'buttons'   => $buttons,
			)
		);

		$posting->set_data( $posting_args );
		$posting->display();
	}
	?>
</div>
