<?php
/**
 * Template for the company details form.
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/company-details.php
 *
 * Page: "Company Details"
 *
 * @version 1.2.6
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="jb-company-details-form-wrapper" class="jb">
	<?php
	$user_id = get_current_user_id();

	$company_details_form = JB()->frontend()->forms(
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

	/**
	 * Filters company details form arguments.
	 *
	 * @since 1.2.6
	 * @hook jb_company_details_form_args
	 *
	 * @param {array} $args Company details form arguments for init.
	 *
	 * @return {array} Filtered arguments.
	 */
	$company_details_args = apply_filters(
		'jb_company_details_form_args',
		array(
			'id'        => 'jb-company-details',
			'class'     => '',
			'prefix_id' => '',
			'fields'    => array(
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
			'hiddens'   => array(
				'jb-action' => 'company-details',
				'nonce'     => wp_create_nonce( 'jb-company-details' ),
			),
			'buttons'   => array(
				'jb-company-save' => array(
					'type'  => 'submit',
					'label' => __( 'Save', 'jobboardwp' ),
				),
			),
		)
	);

	$company_details_form->set_data( $company_details_args );
	$company_details_form->display();
	?>
</div>
