<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JB_Versions_List_Table
 */
class JB_Versions_List_Table extends WP_List_Table {

	/**
	 * @var string
	 */
	public $no_items_message = '';

	/**
	 * @var array
	 */
	public $columns = array();

	/**
	 * JB_Versions_List_Table constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'singular' => __( 'item', 'jobboardwp' ),
				'plural'   => __( 'items', 'jobboardwp' ),
				'ajax'     => false,
			)
		);

		$this->no_items_message = $args['plural'] . ' ' . __( 'not found.', 'jobboardwp' );

		parent::__construct( $args );
	}

	/**
	 * @param callable $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		return call_user_func_array( array( $this, $name ), $arguments );
	}

	/**
	 *
	 */
	public function prepare_items() {
		$screen = $this->screen;

		$columns               = $this->get_columns();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, array(), $sortable );

		$templates = get_option( 'jb_template_statuses', array() );
		$templates = is_array( $templates ) ? $templates : array();

		@uasort(
			$templates,
			function ( $a, $b ) {
				if ( strtolower( $a['status_code'] ) === strtolower( $b['status_code'] ) ) {
					return 0;
				}
				return ( strtolower( $a['status_code'] ) < strtolower( $b['status_code'] ) ) ? -1 : 1;
			}
		);

		$per_page = $this->get_items_per_page( str_replace( '-', '_', $screen->id . '_per_page' ), 999 );
		$paged    = $this->get_pagenum();

		$this->items = array_slice( $templates, ( $paged - 1 ) * $per_page, $per_page );

		$this->set_pagination_args(
			array(
				'total_items' => count( $templates ),
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		if ( isset( $item[ $column_name ] ) ) {
			return $item[ $column_name ];
		} else {
			return '';
		}
	}

	/**
	 *
	 */
	public function no_items() {
		echo $this->no_items_message;
	}

	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	public function set_columns( $args = array() ) {
		$this->columns = $args;
		return $this;
	}

	/**
	 * @return array
	 */
	public function get_columns() {
		return $this->columns;
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function column_template( $item ) {
		$output  = esc_html__( 'Core path - ', 'jobboardwp' );
		$output .= $item['core_file'] . '<br>';
		$output .= esc_html__( 'Theme path - ', 'jobboardwp' );
		$output .= $item['theme_file'];

		return $output;
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function column_core_version( $item ) {
		return $item['core_version'];
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function column_theme_version( $item ) {
		return $item['theme_version'] ? $item['theme_version'] : '-';
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function column_status( $item ) {
		$icon = 1 === $item['status_code'] ? 'jb-notification-is-active dashicons-yes' : 'dashicons-no-alt';
		return $item['status'] . ' <span class="dashicons jb-notification-status ' . esc_attr( $icon ) . '"></span>';
	}
}

$list_table = new JB_Versions_List_Table(
	array(
		'singular' => __( 'Template', 'jobboardwp' ),
		'plural'   => __( 'Templates', 'jobboardwp' ),
		'ajax'     => false,
	)
);

/**
 * JB hook
 *
 * @type filter
 * @title jb_versions_templates_columns
 * @description Version Templates List Table columns
 * @input_vars
 * [{"var":"$columns","type":"array","desc":"Columns"}]
 * @change_log
 * ["Since: 2.0"]
 * @usage add_filter( 'jb_versions_templates_columns', 'function_name', 10, 1 );
 * @example
 * <?php
 * add_filter( 'jb_versions_templates_columns', 'jb_versions_templates_columns', 10, 1 );
 * function jb_versions_templates_columns( $columns ) {
 *     // your code here
 *     $columns['my-custom-column'] = 'My Custom Column';
 *     return $columns;
 * }
 * ?>
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
