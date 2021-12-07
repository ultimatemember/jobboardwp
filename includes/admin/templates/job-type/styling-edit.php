<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** This filter is documented in includes/admin/templates/job-type/styling-create.php */
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
	'edit'
);

JB()->admin()->forms(
	array(
		'class'           => 'jb-styling jb-third-column',
		'prefix_id'       => '',
		'without_wrapper' => true,
		'fields'          => $fields,
	)
)->display();
