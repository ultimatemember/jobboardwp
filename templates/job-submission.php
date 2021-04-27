<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="jb-job-submission-form-wrapper" class="jb">

	<?php if ( ! is_user_logged_in() && JB()->options()->get( 'account-required' ) &&
			   ! JB()->options()->get( 'account-creation' ) ) { ?>

		<span>
			<?php _e( 'You must sign in to create a new job.', 'jobboardwp' ) ?>
			<a class="button" href="<?php echo esc_url( wp_login_url( get_permalink(), true ) ) ?>"><?php esc_attr_e( 'Sign in', 'jobboardwp' ) ?></a>
		</span>

	<?php } else {

		$edit = false;
		if ( ! empty( $jb_job_submission['job'] ) ) {
			$edit = true;
		}

		$types = get_terms( [
			'taxonomy'      => 'jb-job-type',
			'hide_empty'    => false,
		] );

		$types_options = [];
		foreach ( $types as $type ) {
			$types_options[ $type->term_id ] = $type->name;
		}

		$categories_options = [];
		if ( JB()->options()->get( 'job-categories' ) ) {
			$categories = get_terms( [
				'taxonomy'      => 'jb-job-category',
				'hide_empty'    => false,
			] );

			foreach ( $categories as $category ) {
				$categories_options[ $category->term_id ] = $category->name;
			}
		}

		$job_title = '';
		$job_location_type = '0';
		$job_location = '';
		$job_location_data = '';
		$job_type = '';
		$job_category = '';
		$job_description = '';
		$job_application = '';

		$company_name = '';
		$company_website = '';
		$company_tagline = '';
		$company_twitter = '';
		$company_facebook = '';
		$company_instagram = '';
		$company_logo = '';

		if ( is_user_logged_in() ) {
			$c_data = JB()->common()->user()->get_company_data();

			$company_name = $c_data['name'];
			$company_website = $c_data['website'];
			$company_tagline = $c_data['tagline'];
			$company_twitter = $c_data['twitter'];
			$company_facebook = $c_data['facebook'];
			$company_instagram = $c_data['instagram'];
			$company_logo = $c_data['logo'];
		}

		if ( $edit ) {
			$data = JB()->common()->job()->get_raw_data( $jb_job_submission['job']->ID );

			$job_title = $data['title'];
			$job_location_type = $data['location_type'];
			$job_location = $data['location'];
			$job_location_data = $data['location_data'];
			$job_type = $data['type'];

			// workaround on the submission form because Job Type isn't multiple dropdown
			if ( count( $job_type ) == 1 ) {
				$job_type = $job_type[0];
			}

			$job_category = $data['category'];
			$job_description = $data['description'];
			$job_application = $data['app_contact'];

			$company_name = $data['company_name'];
			$company_website = $data['company_website'];
			$company_tagline = $data['company_tagline'];
			$company_twitter = $data['company_twitter'];
			$company_facebook = $data['company_facebook'];
			$company_instagram = $data['company_instagram'];
			$company_logo = $data['company_logo'];
		}

		$my_details_fields = [];

		if ( is_user_logged_in() ) {

			$current_userdata = get_userdata( get_current_user_id() );

			$my_details_fields = [
				[
					'type'      => 'text',
					'label'     => __( 'Email', 'jobboardwp' ),
					'id'        => 'author_email',
					'required'  => true,
					'value'     => $current_userdata->user_email,
				],
				[
					'type'      => 'text',
					'label'     => __( 'First name', 'jobboardwp' ),
					'id'        => 'author_first_name',
					'required'  => JB()->options()->get( 'full-name-required' ),
					'value'     => $current_userdata->first_name,
				],
				[
					'type'      => 'text',
					'label'     => __( 'Last name', 'jobboardwp' ),
					'id'        => 'author_last_name',
					'required'  => JB()->options()->get( 'full-name-required' ),
					'value'     => $current_userdata->last_name,
				],
			];
		} elseif ( JB()->options()->get( 'account-creation' ) && ! is_user_logged_in() ) {
			$my_details_fields = [
				[
					'type'      => 'text',
					'label'     => __( 'Email', 'jobboardwp' ),
					'id'        => 'author_email',
					'required'  => JB()->options()->get( 'account-required' ),
				],
			];

			if ( ! JB()->options()->get( 'account-username-generate' ) ) {
				$my_details_fields[] = [
					'type'      => 'text',
					'label'     => __( 'Username', 'jobboardwp' ),
					'id'        => 'author_username',
					'required'  => JB()->options()->get( 'account-required' ),
				];
			}

			$my_details_fields = array_merge( $my_details_fields, [
				[
					'type'      => 'text',
					'label'     => __( 'First name', 'jobboardwp' ),
					'id'        => 'author_first_name',
					'required'  => JB()->options()->get( 'full-name-required' ),
				],
				[
					'type'      => 'text',
					'label'     => __( 'Last name', 'jobboardwp' ),
					'id'        => 'author_last_name',
					'required'  => JB()->options()->get( 'full-name-required' ),
				],
			] );

			if ( ! JB()->options()->get( 'account-password-email' ) ) {
				$my_details_fields = array_merge( $my_details_fields, [
					[
						'type'      => 'password',
						'label'     => __( 'Password', 'jobboardwp' ),
						'id'        => 'author_password',
						'required'  => JB()->options()->get( 'account-required' ),
					],
					[
						'type'      => 'password',
						'label'     => __( 'Confirm Password', 'jobboardwp' ),
						'id'        => 'author_password_confirm',
						'required'  => JB()->options()->get( 'account-required' ),
					],
				] );
			} else {
				$my_details_fields = array_merge( $my_details_fields, [
					[
						'id'    => 'auto_generate_password',
						'type'  => 'label',
						'label' => __( 'Your account details will be confirmed via email.', 'jobboardwp' ),
					],
				] );
			}
		}

		$buttons = [
			'job-preview' => [
				'type'  => 'submit',
				'label' => __( 'Preview', 'jobboardwp' ),
				'data'  => [
					'action'    => 'preview',
				],
			],
		];

		if ( is_user_logged_in() || ( JB()->options()->get( 'account-creation' ) && JB()->options()->get( 'account-required' ) ) ) {
			$buttons['job-draft'] = [
				'type'  => 'submit',
				'label' => __( 'Save Draft', 'jobboardwp' ),
				'data'  => [
					'action'    => 'draft',
				],
			];
		}

		$app_validation = ['email', 'url'];
		$method = JB()->options()->get( 'application-method' );
		if ( ! empty( $method ) ) {
			$app_validation = [ $method ];
		}

		$posting = JB()->frontend()->forms( [
			'id'    => 'jb-job-submission',
		] );

		$sections = [];

		if ( ! ( is_user_logged_in() && JB()->options()->get( 'your-details-section' ) == '0' ) ) {
			$sections['my-details'] = [
				'title'         => __( 'Your Details', 'jobboardwp' ),
				'fields'        => $my_details_fields,
				'wrap_fields'   => true,
			];
		}

		$gmap_key = JB()->options()->get( 'googlemaps-api-key' );

		$sections = array_merge( $sections, [
			'job-details'       => [
				'title'     => __( 'Job Details', 'jobboardwp' ),
				'fields'    => [
					[
						'type'      => 'text',
						'label'     => __( 'Job Title', 'jobboardwp' ),
						'id'        => 'job_title',
						'required'  => true,
						'value'     => $job_title,
					],
					[
						'type'                  => 'conditional_radio',
						'label'                 => __( 'Job Location', 'jobboardwp' ),
						'id'                    => 'job_location_type',
						'options'               => [
							'0' => __( 'Onsite', 'jobboardwp' ),
							'1' => __( 'Remote', 'jobboardwp' ),
							''  => __( 'Onsite or Remote', 'jobboardwp' ),
						],
						'condition_sections'    => [
							'0' => [
								[
									'type'          => empty( $gmap_key ) ? 'text' : 'location_autocomplete',
									'label'         => __( 'Location', 'jobboardwp' ),
									'placeholder'   => __( 'City, State, or Country', 'jobboardwp' ),
									'name'          => 'job_location',
									'id'            => 'job_location-0',
									'value'         => $job_location,
									'value_data'    => $job_location_data,
									'required'      => true,
								],
							],
							'1' => [
								[
									'type'          => empty( $gmap_key ) ? 'text' : 'location_autocomplete',
									'label'         => __( 'Preferred Location', 'jobboardwp' ),
									'placeholder'   => __( 'City, State, or Country', 'jobboardwp' ),
									'name'          => 'job_location',
									'id'            => 'job_location-1',
									'value'         => $job_location,
									'value_data'    => $job_location_data,
								],
							],
							''  => [
								[
									'type'          => empty( $gmap_key ) ? 'text' : 'location_autocomplete',
									'label'         => __( 'Preferred Location', 'jobboardwp' ),
									'placeholder'   => __( 'City, State, or Country', 'jobboardwp' ),
									'name'          => 'job_location',
									'id'            => 'job_location-',
									'value'         => $job_location,
									'value_data'    => $job_location_data,
								],
							],
						],
						'value'                 => $job_location_type,
					],
					[
						'type'      => 'select',
						'label'     => __( 'Job Type', 'jobboardwp' ),
						'id'        => 'job_type',
						'class'     => 'jb-s2',
						'options'   => $types_options,
						'value'     => $job_type,
						'required'  => ! empty( JB()->options()->get( 'required-job-type' ) ) ? true : false,
					],
					[
						'type'      => 'select',
						'label'     => __( 'Job Category', 'jobboardwp' ),
						'id'        => 'job_category',
						'class'     => 'jb-s2',
						'options'   => $categories_options,
						'value'     => $job_category,
					],
					[
						'type'      => 'wp_editor',
						'label'     => __( 'Description', 'jobboardwp' ),
						'id'        => 'job_description',
						'value'     => $job_description,
						'required'  => true,
					],
					[
						'type'          => 'text',
						'label'         => __( 'Application Contact', 'jobboardwp' ),
						'id'            => 'job_application',
						'required'      => true,
						'value'         => $job_application,
						'placeholder'   => __( 'Enter an email address or website URL', 'jobboardwp' ),
						'validation'    => $app_validation,
					],
				],
			],
			'company-details'   => [
				'title'     => __( 'Company Details', 'jobboardwp' ),
				'fields'    => [
					[
						'type'          => 'text',
						'label'         => __( 'Name', 'jobboardwp' ),
						'id'            => 'company_name',
						'required'      => true,
						'value'         => $company_name,
						'placeholder'   => __( 'Enter the name of the company', 'jobboardwp' ),
					],
					[
						'type'      => 'text',
						'label'     => __( 'Website', 'jobboardwp' ),
						'id'        => 'company_website',
						'value'     => $company_website,
					],
					[
						'type'          => 'text',
						'label'         => __( 'Tagline', 'jobboardwp' ),
						'id'            => 'company_tagline',
						'value'         => $company_tagline,
						'placeholder'   => __( 'Briefly describe your company', 'jobboardwp' ),
					],
					[
						'type'      => 'text',
						'label'     => __( 'Twitter username', 'jobboardwp' ),
						'id'        => 'company_twitter',
						'value'     => $company_twitter,
					],
					[
						'type'      => 'text',
						'label'     => __( 'Facebook username', 'jobboardwp' ),
						'id'        => 'company_facebook',
						'value'     => $company_facebook,
					],
					[
						'type'      => 'text',
						'label'     => __( 'Instagram username', 'jobboardwp' ),
						'id'        => 'company_instagram',
						'value'     => $company_instagram,
					],
					[
						'type'      => 'media',
						'label'     => __( 'Logo', 'jobboardwp' ),
						'id'        => 'company_logo',
						'labels'    => [
							'img_alt'   => __( 'Company logo image', 'jobboardwp' ),
						],
						'action'    => 'jb-upload-company-logo',
						'value'     => $company_logo,
					],
				],
			],
		] );

		$posting->set_data( [
			'id'        => 'jb-job-submission',
			'class'     => '',
			'data'      => [
				'account-required'  => JB()->options()->get( 'account-required' ),
			],
			'prefix_id' => '',
			'sections'  => $sections,
			'hiddens'   => [
				'jb-action'                 => 'job-submission',
				'jb-job-submission-step'    => 'draft||preview',
				'nonce'                     => wp_create_nonce( 'jb-job-submission' ),
			],
			'buttons'   => $buttons,
		] );

		$posting->display();
	} ?>
</div>