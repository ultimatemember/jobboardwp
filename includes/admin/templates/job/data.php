<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post_id;

$gmap_key = JB()->options()->get( 'googlemaps-api-key' );

$location          = '';
$app_contact       = '';
$company_name      = '';
$company_website   = '';
$company_tagline   = '';
$company_twitter   = '';
$company_facebook  = '';
$company_instagram = '';
$is_filled         = false;
$is_featured       = false;
$featured_order    = '';
$expiry_date       = '';
$location_type     = '0';
$author            = get_current_user_id();
$job_location_data = '';
$job_type          = '';
$job_category      = '';

$salary_type        = '';
$salary_amount_type = 'numeric';
$salary_amount      = '';
$salary_min_amount  = '';
$salary_max_amount  = '';
$salary_period      = '';

$users = array(
	'0' => __( 'Guest', 'jobboardwp' ),
);

$users_query = get_users(
	array(
		'fields' => array( 'ID', 'display_name' ),
	)
);

foreach ( $users_query as $user ) {
	$users[ $user->ID ] = $user->display_name;
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

if ( $post_id ) {
	$location_type = get_post_meta( $post_id, 'jb-location-type', true );
	$location      = get_post_meta( $post_id, 'jb-location', true );

	$job_location_data = JB()->common()->job()->get_location_data( $post_id );

	$app_contact       = get_post_meta( $post_id, 'jb-application-contact', true );
	$company_name      = get_post_meta( $post_id, 'jb-company-name', true );
	$company_website   = get_post_meta( $post_id, 'jb-company-website', true );
	$company_tagline   = get_post_meta( $post_id, 'jb-company-tagline', true );
	$company_twitter   = get_post_meta( $post_id, 'jb-company-twitter', true );
	$company_facebook  = get_post_meta( $post_id, 'jb-company-facebook', true );
	$company_instagram = get_post_meta( $post_id, 'jb-company-instagram', true );
	$is_filled         = JB()->common()->job()->is_filled( $post_id );
	$is_featured       = JB()->common()->job()->is_featured( $post_id );
	$featured_order    = get_post_meta( $post_id, 'jb-featured-order', true );
	$job_type          = '';
	$job_category      = '';

	if ( JB()->options()->get( 'job-salary' ) ) {
		$salary_type        = get_post_meta( $post_id, 'jb-salary-type', true );
		$salary_amount_type = get_post_meta( $post_id, 'jb-salary-amount-type', true );
		$salary_amount      = get_post_meta( $post_id, 'jb-salary-amount', true );
		$salary_min_amount  = get_post_meta( $post_id, 'jb-salary-min-amount', true );
		$salary_max_amount  = get_post_meta( $post_id, 'jb-salary-max-amount', true );
		$salary_period      = get_post_meta( $post_id, 'jb-salary-period', true );
	}

	// workaround on the submission form because Job Type isn't multiple dropdown
	$types = wp_get_post_terms(
		$post_id,
		'jb-job-type',
		array(
			'orderby' => 'name',
			'order'   => 'ASC',
			'fields'  => 'ids',
		)
	);

	if ( empty( $types ) || is_wp_error( $types ) ) {
		$job_type = array();
	} else {
		$job_type = $types;
	}

	if ( 1 === count( $job_type ) ) {
		$job_type = $job_type[0];
	} elseif ( empty( $job_type ) ) {
		$job_type = '';
	}

	if ( JB()->options()->get( 'job-categories' ) ) {
		$categories = wp_get_post_terms(
			$post_id,
			'jb-job-category',
			array(
				'orderby' => 'name',
				'order'   => 'ASC',
				'fields'  => 'ids',
			)
		);

		if ( empty( $categories ) || is_wp_error( $categories ) ) {
			$job_category = array();
		} else {
			$job_category = $categories;
		}

		// workaround on the submission form because Job Category isn't multiple dropdown
		if ( 1 === count( $job_category ) ) {
			$job_category = $job_category[0];
		} elseif ( empty( $job_category ) ) {
			$job_category = '';
		}
	}

	$job = get_post( $post_id );

	$author = $job->post_author;

	$expiry_date = JB()->common()->job()->get_expiry_date_raw( $post_id );
}

$job_details_fields = array(
	array(
		'id'      => 'jb-author',
		'type'    => 'select',
		'label'   => __( 'Posted by', 'jobboardwp' ),
		'options' => $users,
		'value'   => $author,
	),
	array(
		'id'          => 'jb-application-contact',
		'type'        => 'text',
		'label'       => __( 'Application contact', 'jobboardwp' ),
		'description' => __( 'It\'s required email or URL for the "application" area.', 'jobboardwp' ),
		'value'       => $app_contact,
		'required'    => true,
	),
	array(
		'type'     => 'select',
		'label'    => __( 'Job Type', 'jobboardwp' ),
		'id'       => 'jb-job-type',
		'options'  => $types_options,
		'value'    => $job_type,
		'required' => ! empty( JB()->options()->get( 'required-job-type' ) ),
		'size'     => 'medium',
	),
);

if ( JB()->options()->get( 'job-categories' ) ) {
	$categories_options = array();
	$categories         = get_terms(
		array(
			'taxonomy'   => 'jb-job-category',
			'hide_empty' => false,
		)
	);

	$categories_options[''] = __( '(None)', 'jobboardwp' );

	$cat_children = _get_term_hierarchy( 'jb-job-category' );

	$categories = JB()->common()->job()->prepare_categories_options( $categories, $cat_children );
	foreach ( $categories as $category ) {
		$categories_options[ $category->term_id ] = str_repeat( '&#8211;', $category->level ) . ' ' . $category->name;
	}

	$job_details_fields = array_merge(
		$job_details_fields,
		array(
			array(
				'type'    => 'select',
				'label'   => __( 'Job Category', 'jobboardwp' ),
				'id'      => 'jb-job-category',
				'size'    => 'medium',
				'options' => $categories_options,
				'value'   => $job_category,
			),
		)
	);
}

if ( JB()->options()->get( 'individual-job-duration' ) ) {
	$expiry_description = __( 'Leave empty to make this job un-expired.', 'jobboardwp' );
} else {
	$settings_link = add_query_arg(
		array(
			'page'    => 'jb-settings',
			'section' => 'job_submission',
		),
		admin_url( 'admin.php' )
	);

	// translators: %s: link to the settings section
	$expiry_description = sprintf( __( 'If empty, then job will have an expiration date based on the <a href="%s#jb_options_job-duration">Job Duration</a> setting.', 'jobboardwp' ), $settings_link );
}

$job_details_fields = array_merge(
	$job_details_fields,
	array(
		array(
			'id'      => 'jb-location-type',
			'type'    => 'select',
			'label'   => __( 'Location Type', 'jobboardwp' ),
			'options' => array(
				'0' => __( 'Onsite', 'jobboardwp' ),
				'1' => __( 'Remote', 'jobboardwp' ),
				''  => __( 'Onsite or remote', 'jobboardwp' ),
			),
			'value'   => $location_type,
			'size'    => 'small',
		),
		array(
			'id'          => 'jb-location',
			'type'        => empty( $gmap_key ) ? 'text' : 'location_autocomplete',
			'label'       => __( 'Location', 'jobboardwp' ),
			'description' => __( 'Required for onsite jobs.', 'jobboardwp' ),
			'value'       => $location,
			'required'    => true,
			'conditional' => array( 'jb-location-type', '=', '0' ),
			'value_data'  => $job_location_data,
		),
		array(
			'id'          => 'jb-location-preferred',
			'type'        => empty( $gmap_key ) ? 'text' : 'location_autocomplete',
			'label'       => __( 'Preferred Location', 'jobboardwp' ),
			'description' => __( 'Leave this blank if location is not important.', 'jobboardwp' ),
			'value'       => $location,
			'conditional' => array( 'jb-location-type', '!=', '0' ),
			'value_data'  => $job_location_data,
		),
		array(
			'id'       => 'jb-company-name',
			'type'     => 'text',
			'label'    => __( 'Company name', 'jobboardwp' ),
			'value'    => $company_name,
			'required' => true,
		),
		array(
			'id'    => 'jb-company-website',
			'type'  => 'text',
			'label' => __( 'Company website', 'jobboardwp' ),
			'value' => $company_website,
		),
		array(
			'id'    => 'jb-company-tagline',
			'type'  => 'text',
			'label' => __( 'Company tagline', 'jobboardwp' ),
			'value' => $company_tagline,
		),
		array(
			'id'    => 'jb-company-twitter',
			'type'  => 'text',
			'label' => __( 'Company twitter', 'jobboardwp' ),
			'value' => $company_twitter,
		),
		array(
			'id'    => 'jb-company-facebook',
			'type'  => 'text',
			'label' => __( 'Company facebook', 'jobboardwp' ),
			'value' => $company_facebook,
		),
		array(
			'id'    => 'jb-company-instagram',
			'type'  => 'text',
			'label' => __( 'Company instagram', 'jobboardwp' ),
			'value' => $company_instagram,
		),
		array(
			'id'          => 'jb-is-filled',
			'type'        => 'checkbox',
			'label'       => __( 'Position Filled', 'jobboardwp' ),
			'description' => __( 'Filled listings will no longer accept applications.', 'jobboardwp' ),
			'value'       => $is_filled,
		),
		array(
			'id'          => 'jb-expiry-date',
			'type'        => 'datepicker',
			'label'       => __( 'Expiry Date', 'jobboardwp' ),
			'value'       => $expiry_date,
			'size'        => 'small',
			'description' => $expiry_description,
		),
		array(
			'id'          => 'jb-is-featured',
			'type'        => 'checkbox',
			'label'       => __( 'Is Featured?', 'jobboardwp' ),
			'value'       => $is_featured,
			'size'        => 'small',
			'description' => __( 'Add a feature option.', 'jobboardwp' ),
		),
		array(
			'id'          => 'jb-featured-order',
			'type'        => 'text',
			'label'       => __( 'Featured Order', 'jobboardwp' ),
			'value'       => $featured_order,
			'conditional' => array( 'jb-is-featured', '!=', '0' ),
		),
	)
);

if ( JB()->options()->get( 'job-salary' ) ) {
	$currency        = JB()->options()->get( 'job-salary-currency' );
	$currencies_data = JB()->config()->get( 'currencies' );
	$currency_symbol = $currencies_data[ $currency ]['symbol'];

	$job_details_fields = array_merge(
		$job_details_fields,
		array(
			array(
				'type'        => 'select',
				'label'       => __( 'Salary', 'jobboardwp' ),
				'placeholder' => __( 'Please select salary', 'jobboardwp' ),
				'size'        => 'small',
				'id'          => 'jb-salary-type',
				'options'     => array(
					''          => __( 'Not specified', 'jobboardwp' ),
					'fixed'     => __( 'Fixed', 'jobboardwp' ),
					'recurring' => __( 'Recurring', 'jobboardwp' ),
				),
				'required'    => ! empty( JB()->options()->get( 'required-job-salary' ) ),
				'value'       => $salary_type,
			),
			array(
				'type'        => 'select',
				'label'       => __( 'Salary amount type', 'jobboardwp' ),
				'placeholder' => __( 'Please select amount type', 'jobboardwp' ),
				'id'          => 'jb-salary-amount-type',
				'options'     => array(
					'numeric' => __( 'Numeric', 'jobboardwp' ),
					'range'   => __( 'Range (min-max)', 'jobboardwp' ),
				),
				'value'       => $salary_amount_type,
				'size'        => 'small',
				'conditional' => array( 'jb-salary-type', '!=', '' ),
			),
			array(
				'type'        => 'number',
				'min'         => 0,
				'step'        => 1,
				'size'        => 'small',
				'required'    => true,
				// translators: %s - Currency symbol
				'label'       => sprintf( __( 'Salary Amount %s', 'jobboardwp' ), $currency_symbol ),
				'id'          => 'jb-salary-amount',
				'value'       => $salary_amount,
				'conditional' => array( 'jb-salary-amount-type', '=', 'numeric' ),
			),
			array(
				'type'        => 'number',
				'min'         => 0,
				'step'        => 1,
				'size'        => 'small',
				'required'    => true,
				// translators: %s - Currency symbol
				'label'       => sprintf( __( 'Salary Min Amount %s', 'jobboardwp' ), $currency_symbol ),
				'description' => __( 'Set to "0" for displaying only max amount. (E.g. "Salary is up to {max_amount}")', 'jobboardwp' ),
				'id'          => 'jb-salary-min-amount',
				'value'       => $salary_min_amount,
				'conditional' => array( 'jb-salary-amount-type', '=', 'range' ),
			),
			array(
				'type'        => 'number',
				'min'         => 0,
				'step'        => 1,
				'size'        => 'small',
				'required'    => true,
				// translators: %s - Currency symbol
				'label'       => sprintf( __( 'Salary Max Amount %s', 'jobboardwp' ), $currency_symbol ),
				'description' => __( 'Set to "0" for displaying only min amount. (E.g. "Salary starts from {min_amount}")', 'jobboardwp' ),
				'id'          => 'jb-salary-max-amount',
				'value'       => $salary_max_amount,
				'conditional' => array( 'jb-salary-amount-type', '=', 'range' ),
			),
			array(
				'type'        => 'select',
				'required'    => true,
				'size'        => 'small',
				'label'       => __( 'Salary Period', 'jobboardwp' ),
				'id'          => 'jb-salary-period',
				'options'     => array(
					'hour'  => __( 'Hour', 'jobboardwp' ),
					'day'   => __( 'Day', 'jobboardwp' ),
					'week'  => __( 'Week', 'jobboardwp' ),
					'month' => __( 'Month', 'jobboardwp' ),
				),
				'value'       => $salary_period,
				'conditional' => array( 'jb-salary-type', '=', 'recurring' ),
			),
		)
	);
}

/**
 * Filters the job meta fields in the metabox (Admin Dashboard > Add/Edit Job screen).
 *
 * @since 1.0
 * @hook jb_job_details_metabox_fields
 *
 * @param {array} $fields Meta fields.
 *
 * @return {array} Meta fields.
 */
$fields = apply_filters( 'jb_job_details_metabox_fields', $job_details_fields );

JB()->admin()->forms(
	array(
		'class'     => 'jb-data jb-third-column',
		'prefix_id' => 'jb-job-meta',
		'fields'    => $fields,
	)
)->display();
