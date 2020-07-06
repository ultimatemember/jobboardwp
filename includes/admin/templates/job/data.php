<?php if ( ! defined( 'ABSPATH' ) ) exit;

global $post_id;

$location = '';
$app_contact = '';
$company_name = '';
$company_website = '';
$company_tagline = '';
$is_filled = false;
$expiry_date = '';
$location_type = '0';
$author = get_current_user_id();

$users = ['0' => __( 'Guest', 'jobboardwp' ),];
$users_query = get_users( [
	'fields' => [ 'ID', 'display_name' ],
] );

foreach ( $users_query as $user ) {
	$users[ $user->ID ] = $user->display_name;
}

if ( $post_id ) {
	$location_type = get_post_meta( $post_id, 'jb-location-type', true );
	$location = get_post_meta( $post_id, 'jb-location', true );
	$app_contact = get_post_meta( $post_id, 'jb-application-contact', true );
	$company_name = get_post_meta( $post_id, 'jb-company-name', true );
	$company_website = get_post_meta( $post_id, 'jb-company-website', true );
	$company_tagline = get_post_meta( $post_id, 'jb-company-tagline', true );
	$is_filled = get_post_meta( $post_id, 'jb-is-filled', true );

	$job = get_post( $post_id );
	$author = $job->post_author;

	$expiry_date = JB()->common()->job()->get_expiry_date( $post_id );
}

$fields = apply_filters( 'jb_job-data', [
	[
		'id'        => 'jb-author',
		'type'      => 'select',
		'label'     => __( 'Posted by', 'jobboardwp' ),
		'options'   => $users,
		'value'     => $author,
	],
	[
		'id'            => 'jb-application-contact',
		'type'          => 'text',
		'label'         => __( 'Application contact', 'jobboardwp' ),
		'description'   => __( 'It\'s required email or URL for the "application" area.', 'jobboardwp' ),
		'value'         => $app_contact,
	],
	[
		'id'        => 'jb-location-type',
		'type'      => 'select',
		'label'     => __( 'Location Type', 'jobboardwp' ),
		'options'   => [
			'0' => __( 'Onsite', 'jobboardwp' ),
			'1' => __( 'Remote', 'jobboardwp' ),
			''  => __( 'Onsite or remote', 'jobboardwp' ),
		],
		'value'     => $location_type,
		'size'      => 'small',
	],
	[
		'id'            => 'jb-location',
		'type'          => 'text',
		'label'         => __( 'Location', 'jobboardwp' ),
		'description'   => __( 'Required for onsite jobs.', 'jobboardwp' ),
		'value'         => $location,
		'required'      => true,
		'conditional'   => [ 'jb-location-type', '=', '0' ],
	],
	[
		'id'            => 'jb-location-preferred',
		'type'          => 'text',
		'label'         => __( 'Preferred Location', 'jobboardwp' ),
		'description'   => __( 'Leave this blank if location is not important.', 'jobboardwp' ),
		'value'         => $location,
		'conditional'   => [ 'jb-location-type', '!=', '0' ],
	],
	[
		'id'    => 'jb-company-name',
		'type'  => 'text',
		'label' => __( 'Company name', 'jobboardwp' ),
		'value' => $company_name,
	],
	[
		'id'    => 'jb-company-website',
		'type'  => 'text',
		'label' => __( 'Company website', 'jobboardwp' ),
		'value' => $company_website,
	],
	[
		'id'    => 'jb-company-tagline',
		'type'  => 'text',
		'label' => __( 'Company tagline', 'jobboardwp' ),
		'value' => $company_tagline,
	],
	[
		'id'            => 'jb-is-filled',
		'type'          => 'checkbox',
		'label'         => __( 'Position Filled', 'jobboardwp' ),
		'description'   => __( 'Filled listings will no longer accept applications.', 'jobboardwp' ),
		'value'         => $is_filled,
	],
	[
		'id'    => 'jb-expiry-date',
		'type'  => 'datepicker',
		'label' => __( 'Expiry Date', 'jobboardwp' ),
		'value' => $expiry_date,
		'size'  => 'small',
	],
] );

JB()->admin()->forms( [
	'class'     => 'jb-data jb-third-column',
	'prefix_id' => 'jb-job-meta',
	'fields'    => $fields,
] )->display();