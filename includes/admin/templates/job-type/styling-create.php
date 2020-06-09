<?php if ( ! defined( 'ABSPATH' ) ) exit;

$data = [];

$fields = apply_filters( 'jb_job-type-styling', [
	[
		'id'            => 'jb-color',
		'type'          => 'color',
		'label'         => __( 'Tag Color', 'jobboardwp' ),
		'description'   => __( 'Customize job type tag color', 'jobboardwp' ),
		'value'         => ! empty( $data['jb-color'] ) ? $data['jb-color'] : '',
	],
	[
		'id'            => 'jb-background',
		'type'          => 'color',
		'label'         => __( 'Tag Background', 'jobboardwp' ),
		'description'   => __( 'Customize job type tag background', 'jobboardwp' ),
		'value'         => ! empty( $data['jb-background'] ) ? $data['jb-background'] : '',
	],
], $data, 'create' );

JB()->admin()->forms( [
	'class'             => 'jb-styling jb-third-column',
	'prefix_id'         => '',
	'without_wrapper'   => true,
	'div_line'          => true,
	'fields'            => $fields
] )->display();

wp_nonce_field( basename( __FILE__ ), 'jb_job_type_styling_nonce' );