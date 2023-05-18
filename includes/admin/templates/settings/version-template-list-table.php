<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$list_table = new jb\admin\Version_Template_List_Table(
	array(
		'singular' => __( 'Template', 'jobboardwp' ),
		'plural'   => __( 'Templates', 'jobboardwp' ),
		'ajax'     => false,
	)
);

/**
 * Filters the columns of the ListTable on the JobBoardWP > Settings > Override Templates screen.
 *
 * @since 1.2.6
 * @hook jb_versions_templates_columns
 *
 * @param {array} $columns Version Templates ListTable columns.
 *
 * @return {array} Version Templates ListTable columns.
 */
$columns = apply_filters(
	'jb_versions_templates_columns',
	array(
		'template'      => __( 'Template', 'jobboardwp' ),
		'core_version'  => __( 'Core version', 'jobboardwp' ),
		'theme_version' => __( 'Theme version', 'jobboardwp' ),
		'status'        => __( 'Status', 'jobboardwp' ),
	)
);

$list_table->set_columns( $columns );
$list_table->prepare_items();
?>

<form action="" method="get" name="jb-settings-template-versions" id="jb-settings-template-versions">
	<input type="hidden" name="page" value="jb_options" />
	<input type="hidden" name="tab" value="override_templates" />
	<?php $list_table->display(); ?>
</form>
