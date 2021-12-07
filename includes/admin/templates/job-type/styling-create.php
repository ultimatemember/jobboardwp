<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = array();

/**
 * Filters the job type meta fields in the metabox (Admin Dashboard > Add/Edit Job Type screen).
 *
 * @since 1.0
 * @hook jb_job_type_styling
 *
 * @param {array} $fields Meta fields.
 *
 * @return {array} Meta fields.
 */
$fields = apply_filters(
	'jb_job_type_styling',
	array(
		array(
			'id'          => 'jb-color',
			'type'        => 'color',
			'label'       => __( 'Tag Color', 'jobboardwp' ),
			'description' => __( 'Customize job type tag color', 'jobboardwp' ),
			'value'       => ! empty( $data['jb-color'] ) ? $data['jb-color'] : '',
		),
		array(
			'id'          => 'jb-background',
			'type'        => 'color',
			'label'       => __( 'Tag Background', 'jobboardwp' ),
			'description' => __( 'Customize job type tag background', 'jobboardwp' ),
			'value'       => ! empty( $data['jb-background'] ) ? $data['jb-background'] : '',
		),
	),
	$data,
	'create'
);

JB()->admin()->forms(
	array(
		'class'           => 'jb-styling jb-third-column',
		'prefix_id'       => '',
		'without_wrapper' => true,
		'div_line'        => true,
		'fields'          => $fields,
	)
)->display();

wp_nonce_field( basename( __FILE__ ), 'jb_job_type_styling_nonce' );
