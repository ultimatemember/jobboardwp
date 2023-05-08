<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_user_logged_in() ) { ?>
	<div id="jb-job-submission-form-wrapper" class="jb">
		<?php
		$user_id = get_current_user_id();

		$posting = JB()->frontend()->forms(
			array(
				'id' => 'jb-company-details',
			)
		);

		$company_name      = get_user_meta( $user_id, 'jb_company_name', true );
		$company_website   = get_user_meta( $user_id, 'jb_company_website', true );
		$company_tagline   = get_user_meta( $user_id, 'jb_company_tagline', true );
		$company_twitter   = get_user_meta( $user_id, 'jb_company_twitter', true );
		$company_facebook  = get_user_meta( $user_id, 'jb_company_facebook', true );
		$company_instagram = get_user_meta( $user_id, 'jb_company_instagram', true );
		$company_logo      = get_user_meta( $user_id, 'jb_company_logo', true );

		$buttons = array(
			'jb-company-save' => array(
				'type'  => 'submit',
				'label' => __( 'Save', 'jobboardwp' ),
				'data'  => array(
					'action' => 'save',
				),
			),
		);

		$sections = array(
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
		);

		$posting_args = apply_filters(
			'jb_company_details_form_args',
			array(
				'id'        => 'jb-company-details',
				'class'     => '',
				'prefix_id' => '',
				'sections'  => $sections,
				'hiddens'   => array(
					'jb-action' => 'company-details',
					'nonce'     => wp_create_nonce( 'jb-company-details' ),
				),
				'buttons'   => $buttons,
			)
		);

		$posting->set_data( $posting_args );
		$posting->display();
		?>
	</div>
	<?php
}
