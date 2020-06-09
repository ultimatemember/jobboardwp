<?php if ( ! defined( 'ABSPATH' ) ) exit;

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
], $data, 'edit' );

JB()->admin()->forms( [
	'class'             => 'jb-styling jb-third-column',
	'prefix_id'         => '',
	'without_wrapper'   => true,
	'fields'            => $fields
] )->display();