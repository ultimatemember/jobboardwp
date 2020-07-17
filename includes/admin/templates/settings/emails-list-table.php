<?php if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/**
 * Class JB_Emails_List_Table
 */
class JB_Emails_List_Table extends WP_List_Table {


	/**
	 * @var string
	 */
	var $no_items_message = '';


	/**
	 * @var array
	 */
	var $sortable_columns = [];


	/**
	 * @var string
	 */
	var $default_sorting_field = '';


	/**
	 * @var array
	 */
	var $actions = [];


	/**
	 * @var array
	 */
	var $bulk_actions = [];


	/**
	 * @var array
	 */
	var $columns = [];


	/**
	 * JB_Emails_List_Table constructor.
	 *
	 * @param array $args
	 */
	function __construct( $args = [] ){
		$args = wp_parse_args( $args, [
			'singular'  => __( 'item', 'jobboardwp' ),
			'plural'    => __( 'items', 'jobboardwp' ),
			'ajax'      => false
		] );

		$this->no_items_message = $args['plural'] . ' ' . __( 'not found.', 'jobboardwp' );

		parent::__construct( $args );
	}


	/**
	 * @param callable $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	function __call( $name, $arguments ) {
		return call_user_func_array( [ $this, $name ], $arguments );
	}


	/**
	 *
	 */
	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = [];
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];
	}


	/**
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	function column_default( $item, $column_name ) {
		if( isset( $item[ $column_name ] ) ) {
			return $item[ $column_name ];
		} else {
			return '';
		}
	}


	/**
	 *
	 */
	function no_items() {
		echo $this->no_items_message;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	function set_sortable_columns( $args = [] ) {
		$return_args = [];
		foreach ( $args as $k => $val ) {
			if ( is_numeric( $k ) ) {
				$return_args[ $val ] = [ $val, $val == $this->default_sorting_field ];
			} elseif ( is_string( $k ) ) {
				$return_args[ $k ] = [ $val, $k == $this->default_sorting_field ];
			} else {
				continue;
			}
		}
		$this->sortable_columns = $return_args;
		return $this;
	}


	/**
	 * @return array
	 */
	function get_sortable_columns() {
		return $this->sortable_columns;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	function set_columns( $args = [] ) {
		if ( count( $this->bulk_actions ) ) {
			$args = array_merge( [ 'cb' => '<input type="checkbox" />' ], $args );
		}
		$this->columns = $args;

		return $this;
	}


	/**
	 * @return array
	 */
	function get_columns() {
		return $this->columns;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	function set_actions( $args = [] ) {
		$this->actions = $args;
		return $this;
	}


	/**
	 * @return array
	 */
	function get_actions() {
		return $this->actions;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	function set_bulk_actions( $args = [] ) {
		$this->bulk_actions = $args;
		return $this;
	}


	/**
	 * @return array
	 */
	function get_bulk_actions() {
		return $this->bulk_actions;
	}


	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_email( $item ) {
		$active = JB()->options()->get( $item['key'] . '_on' );

		return '<span class="dashicons jb-notification-status ' . ( ! empty( $active ) ? 'jb-notification-is-active dashicons-yes' : 'dashicons-no-alt' ) . '"></span><a href="' . add_query_arg( [ 'email' => $item['key'] ] ) . '"><strong>'. $item['title'] . '</strong></a>';
	}


	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_recipients( $item ) {
		if ( $item['recipient'] == 'admin' ) {
			return JB()->options()->get( 'admin_email' );
		} else {
			return __( 'Member', 'jobboardwp' );
		}
	}


	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_configure( $item ) {
		return '<a class="button jb-email-configure" href="' . add_query_arg( [ 'email' => $item['key'] ] ) . '"><span class="dashicons dashicons-admin-generic"></span></a>';
	}


	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_icl_translations( $item ) {
		return JB()->external_integrations()->wpml_column_content( $item );
	}


	/**
	 * @param array $attr
	 */
	function jb_set_pagination_args( $attr = [] ) {
		$this->set_pagination_args( $attr );
	}
}


$ListTable = new JB_Emails_List_Table( [
	'singular'  => __( 'Email Notification', 'jobboardwp' ),
	'plural'    => __( 'Email Notifications', 'jobboardwp' ),
	'ajax'      => false,
] );

$per_page   = 20;
$paged      = $ListTable->get_pagenum();

$columns = apply_filters( 'jb_email_templates_columns', [
	'email'         => __( 'Email', 'jobboardwp' ),
	'recipients'    => __( 'Recipient(s)', 'jobboardwp' ),
	'configure'     => '',
] );

$ListTable->set_columns( $columns );

$emails = JB()->config()->get( 'email_notifications' );

$ListTable->prepare_items();
$ListTable->items = $emails;
$ListTable->jb_set_pagination_args( [ 'total_items' => count( $emails ), 'per_page' => $per_page, ] ); ?>

<form action="" method="get" name="jb-settings-emails" id="jb-settings-emails">
	<input type="hidden" name="page" value="jb-settings" />
	<input type="hidden" name="tab" value="email" />
	<?php if ( ! empty( $_GET['section'] ) ) { ?>
		<input type="hidden" name="section" value="<?php echo esc_attr( $_GET['section'] ) ?>" />
	<?php }

	$ListTable->display(); ?>
</form>