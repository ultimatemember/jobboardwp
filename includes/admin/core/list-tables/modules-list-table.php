<?php if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/**
 * Class JB_Modules_List_Table
 */
class JB_Modules_List_Table extends WP_List_Table {


	/**
	 * @var string
	 */
	public $no_items_message = '';


	/**
	 * @var array
	 */
	public $actions = array();


	/**
	 * @var array
	 */
	public $bulk_actions = array();


	/**
	 * @var array
	 */
	public $columns = array();


	/**
	 * JB_Roles_List_Table constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'singular' => __( 'item', 'jobboardwp' ),
			'plural'   => __( 'items', 'jobboardwp' ),
			'ajax'     => false,
		) );

		$this->no_items_message = $args['plural'] . ' ' . __( 'not found.', 'jobboardwp' );

		parent::__construct( $args );
	}


	/**
	 * @param callable $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
//	function __call( $name, $arguments ) {
//		return call_user_func_array( array( $this, $name ), $arguments );
//	}


	/**
	 *
	 */
	public function prepare_items() {
		$screen = $this->screen;

		$columns               = $this->get_columns();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, array(), $sortable );

		$modules = JB()->modules()->get_list();

		@uasort($modules, function ( $a, $b ) {
			if ( strtolower( $a['title'] ) == strtolower( $b['title'] ) ) {
				return 0;
			}
			return ( strtolower( $a['title'] ) < strtolower( $b['title'] ) ) ? -1 : 1;
		});

		$per_page = $this->get_items_per_page( str_replace( '-', '_', $screen->id . '_per_page' ), 999 );
		$paged    = $this->get_pagenJB();

		$this->items = array_slice( $modules, ( $paged - 1 ) * $per_page, $per_page );

		$this->set_pagination_args( array(
			'total_items' => count( $modules ),
			'per_page'    => $per_page,
		) );
	}


	/**
	 * Generates content for a single row of the table.
	 *
	 * @since 3.1.0
	 *
	 * @param object|array $item The current item
	 */
	public function single_row( $item ) {
		$is_active   = JB()->modules()->is_active( $item['key'] );
		$is_disabled = JB()->modules()->is_disabled( $item['key'] );


		$class = $is_disabled ? 'disabled ' : 'enabled ';
		$class .= $is_active ? 'active' : 'inactive';

		echo sprintf( '<tr class="%s">', esc_attr( $class ) );
		$this->single_row_columns( $item );
		echo '</tr>';
	}


	public function single_row_columns( $item ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "$column_name column-$column_name";
			if ( $primary === $column_name ) {
				$classes .= ' has-row-actions column-primary';
			}

			if ( in_array( $column_name, $hidden, true ) ) {
				$classes .= ' hidden';
			}

			// Comments column uses HTML in the display name with screen reader text.
			// Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
			$data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';

			$attributes = "class='$classes'";

			if ( 'cb' === $column_name ) {
				echo '<th scope="row" class="check-column">';
				echo $this->column_cb( $item );
				echo '</th>';
			} elseif ( method_exists( $this, '_column_' . $column_name ) ) {
				echo call_user_func(
					array( $this, '_column_' . $column_name ),
					$item,
					$classes,
					$data,
					$primary
				);
			} elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo "<td $attributes>";
				echo call_user_func( array( $this, 'column_' . $column_name ), $item );
				echo $this->handle_row_actions( $item, $column_name, $primary );
				echo '</td>';
			} else {
				echo "<td $attributes>";
				echo $this->column_default( $item, $column_name );
				echo $this->handle_row_actions( $item, $column_name, $primary );
				echo '</td>';
			}
		}
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
		if ( count( $this->bulk_actions ) ) {
			$args = array_merge( array( 'cb' => '<input type="checkbox" />' ), $args );
		}
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
	 * @param array $args
	 *
	 * @return $this
	 */
	public function set_actions( $args = array() ) {
		$this->actions = $args;
		return $this;
	}


	/**
	 * @return array
	 */
	public function get_actions() {
		return $this->actions;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	public function set_bulk_actions( $args = array() ) {
		$this->bulk_actions = $args;
		return $this;
	}


	/**
	 * @return array
	 */
	public function get_bulk_actions() {
		return $this->bulk_actions;
	}


	public function get_table_classes() {
		return array( 'widefat', $this->_args['plural'] );
	}


	/**
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="item[]" value="%s" />', $item['key'] );
	}


	/**
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_type( $item ) {
		$type = '';
		switch ( $item['type'] ) {
		case 'free':
			$type = __( 'Free', 'jobboardwp' );
			break;
		case 'pro':
			$type = __( 'Pro', 'jobboardwp' );
			break;
		case 'premium':
			$type = __( 'Premium', 'jobboardwp' );
			break;
		}

		return $type;
	}


	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function column_module_title( $item ) {
		$actions = array();


		if ( JB()->modules()->can_activate( $item['key'] ) ) {
			$actions['activate'] = '<a href="admin.php?page=jb-settings&tab=modules&action=activate&slug=' . esc_attr( $item['key'] ) . '&_wpnonce=' . wp_create_nonce( 'jb_module_activate' . $item['key'] . get_current_user_id() ) . '">' . __( 'Activate', 'jobboardwp' ). '</a>';
		}

		$module_data = JB()->modules()->get_data( $item['key'] );

		if ( array_key_exists( 'docs_url', $module_data ) ) {
			$actions['docs'] = '<a href="' . esc_url_raw( $module_data['docs_url'] ) . '" target="_blank">' . __( 'Documentation', 'jobboardwp' ). '</a>';
		}

		if ( JB()->modules()->has_settings_section( $item['key'] ) ) {
			$actions['settings'] = '<a href="admin.php?page=jb-settings&tab=modules&section=' . esc_attr( $item['key'] ) . '">' . __( 'Settings', 'jobboardwp' ) . '</a>';
		}

		if ( JB()->modules()->can_deactivate( $item['key'] ) ) {
			$actions['deactivate'] = '<a href="admin.php?page=jb-settings&tab=modules&action=deactivate&slug=' . esc_attr( $item['key'] ) . '&_wpnonce=' . wp_create_nonce( 'jb_module_deactivate' . $item['key'] . get_current_user_id() ) . '" class="delete">' . __( 'Deactivate', 'jobboardwp' ). '</a>';
		}

		if ( JB()->modules()->can_flush( $item['key'] ) ) {
			$actions['flush-data'] = '<a href="admin.php?page=jb-settings&tab=modules&action=flush-data&slug=' . esc_attr( $item['key'] ) . '&_wpnonce=' . wp_create_nonce( 'jb_module_flush' . $item['key'] . get_current_user_id() ) . '" class="delete">' . __( 'Flush data', 'jobboardwp' ). '</a>';
		}

		$actions = apply_filters( 'jb_module_list_table_actions', $actions, $item['key'] );

		$column_content = sprintf('<div class="jb-module-data-wrapper"><div class="jb-module-title-wrapper">%1$s %2$s</div></div>', '<strong>' . esc_html( $item['title'] ) . '</strong>', $this->row_actions( $actions, true ) );

		return $column_content;
	}
}


$ListTable = new JB_Modules_List_Table( array(
	'singular' => __( 'Module', 'jobboardwp' ),
	'plural'   => __( 'Modules', 'jobboardwp' ),
	'ajax'     => false,
) );

$bulk_actions = array();

$bulk_actions = array(
	'activate'   => __( 'Activate', 'jobboardwp' ),
	'deactivate' => __( 'Deactivate', 'jobboardwp' ),
	'flush-data' => __( 'Flush module data', 'jobboardwp' ),
);


$ListTable->set_bulk_actions( $bulk_actions );

$ListTable->set_columns( array(
	'module_title' => __( 'Module', 'jobboardwp' ),
	'type'         => __( 'Type', 'jobboardwp' ),
	'description'  => __( 'Description', 'jobboardwp' ),
) );

$ListTable->prepare_items();

if ( ! empty( $_GET['msg'] ) ) {
	switch( sanitize_key( $_GET['msg'] ) ) {
	case 'a':
		echo '<div class="clear"></div><div id="message" class="updated fade"><p>' . __( 'Module <strong>activated</strong> successfully.', 'jobboardwp' ) . '</p></div>';
		break;
	case 'd':
		echo '<div class="clear"></div><div id="message" class="updated fade"><p>' . __( 'Module <strong>deactivated</strong> successfully.', 'jobboardwp' ) . '</p></div>';
		break;
	case 'f':
		echo '<div class="clear"></div><div id="message" class="updated fade"><p>' . __( 'Module\'s data is <strong>flushed</strong> successfully.', 'jobboardwp' ) . '</p></div>';
		break;
	}
} ?>

<div class="clear"></div>

<?php ob_start(); ?>

<div id="jb-plan">
	<p><?php esc_html_e( 'You are using the free version of JobBoardWP. With this you have access to the modules below. Upgrade to JobBoardWP Pro to get access to the pro modules.', 'jobboardwp' ); ?></p>
	<p><?php echo wp_kses( sprintf( __( 'Click <a href="%s" target="_blank">here</a> to view our different plans for JobBoardWP Pro.', 'jobboardwp' ), 'https://jobboardwp.com/pricing/' ), array( 'a' => array( 'href' => array(), 'target' => true ) ) ); ?></p>
</div>

<?php
$same_page_license = ob_get_clean();
$same_page_license = apply_filters( 'jb_modules_page_same_page_license', $same_page_license );

echo $same_page_license;
?>

<form action="" method="get" name="jb-modules" id="jb-modules">
	<input type="hidden" name="page" value="jb-settings" />
	<input type="hidden" name="tab" value="modules" />
	<?php $ListTable->display(); ?>
</form>
