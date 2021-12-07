<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$list_table = new jb\admin\Emails_List_Table(
	array(
		'singular' => __( 'Email Notification', 'jobboardwp' ),
		'plural'   => __( 'Email Notifications', 'jobboardwp' ),
		'ajax'     => false,
	)
);

/**
 * Filters the columns of the ListTable on the JobBoardWP > Settings > Email screen.
 *
 * @since 1.1.0
 * @hook jb_email_templates_columns
 *
 * @param {array} $columns Email ListTable columns.
 *
 * @return {array} Email ListTable columns.
 */
$columns = apply_filters(
	'jb_email_templates_columns',
	array(
		'email'      => __( 'Email', 'jobboardwp' ),
		'recipients' => __( 'Recipient(s)', 'jobboardwp' ),
		'configure'  => '',
	)
);

$list_table->set_columns( $columns );
$list_table->prepare_items();
?>

<form action="" method="get" name="jb-settings-emails" id="jb-settings-emails">
	<input type="hidden" name="page" value="jb-settings" />
	<input type="hidden" name="tab" value="email" />

	<?php $list_table->display(); ?>
</form>
