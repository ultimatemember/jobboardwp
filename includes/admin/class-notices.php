<?php
namespace jb\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\admin\Notices' ) ) {


	/**
	 * Class Notices
	 *
	 * @package jb\admin
	 */
	class Notices {


		/**
		 * Notices list
		 *
		 * @var array
		 */
		var $list = [];


		/**
		 * Notices constructor.
		 */
		function __construct() {
			add_action( 'admin_init', [ &$this, 'create_list' ], 10 );
			add_action( 'admin_notices', [ &$this, 'render' ], 1 );
		}


		/**
		 *
		 */
		function create_list() {
			$this->install_core_page_notice();
			do_action( 'jb_admin_create_notices' );
		}


		/**
		 * Render all admin notices
		 */
		function render() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$admin_notices = $this->get_admin_notices();

			$hidden = JB()->options()->get( 'hidden_admin_notices', [] );

			uasort( $admin_notices, [ &$this, 'notice_priority_sort' ] );

			foreach ( $admin_notices as $key => $admin_notice ) {
				if ( empty( $hidden ) || ! in_array( $key, $hidden ) ) {
					$this->display( $key );
				}
			}

			do_action( 'jb_admin_after_main_notices' );
		}


		/**
		 * @return array
		 */
		function get_admin_notices() {
			return $this->list;
		}


		/**
		 * @param $admin_notices
		 */
		function set_admin_notices( $admin_notices ) {
			$this->list = $admin_notices;
		}


		/**
		 * @param $a
		 * @param $b
		 *
		 * @return mixed
		 */
		function notice_priority_sort( $a, $b ) {
			if ( $a['priority'] == $b['priority'] ) {
				return 0;
			}
			return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
		}


		/**
		 * Add notice to JB notices array
		 *
		 * @param string $key
		 * @param array $data
		 * @param int $priority
		 */
		function add( $key, $data, $priority = 10 ) {
			$admin_notices = $this->get_admin_notices();

			if ( empty( $admin_notices[ $key ] ) ) {
				$admin_notices[ $key ] = array_merge( $data, [ 'priority' => $priority ] );
				$this->set_admin_notices( $admin_notices );
			}
		}


		/**
		 * Remove notice from JB notices array
		 *
		 * @param string $key
		 */
		function remove_notice( $key ) {
			$admin_notices = $this->get_admin_notices();

			if ( ! empty( $admin_notices[ $key ] ) ) {
				unset( $admin_notices[ $key ] );
				$this->set_admin_notices( $admin_notices );
			}
		}


		/**
		 * Display single admin notice
		 *
		 * @param string $key
		 * @param bool $echo
		 *
		 * @return string
		 */
		function display( $key, $echo = true ) {
			$admin_notices = $this->get_admin_notices();

			if ( empty( $admin_notices[ $key ] ) ) {
				return '';
			}

			$notice_data = $admin_notices[ $key ];

			$class = ! empty( $notice_data['class'] ) ? $notice_data['class'] : 'updated';
			if ( ! empty( $admin_notices[ $key ]['dismissible'] ) ) {
				$class .= ' is-dismissible';
			}

			$message = ! empty( $notice_data['message'] ) ? $notice_data['message'] : '';

			ob_start();

			printf( '<div class="jb-admin-notice notice %s" data-key="%s">%s</div>',
				esc_attr( $class ),
				esc_attr( $key ),
				$message
			);

			$notice = ob_get_clean();

			if ( $echo ) {
				echo $notice;
				return '';
			} else {
				return $notice;
			}
		}


		/**
		 * Dismiss notices by key
		 *
		 * @param string $key
		 */
		function dismiss( $key ) {
			$hidden_notices = JB()->options()->get( 'hidden_admin_notices', [] );
			$hidden_notices[] = $key;
			JB()->options()->update( 'hidden_admin_notices', array_unique( $hidden_notices ) );
		}


		/**
		 * Regarding page setup
		 */
		function install_core_page_notice() {
			if ( JB()->permalinks()->are_pages_installed() || ! current_user_can( 'manage_options' ) ) {
				return;
			}

			ob_start(); ?>

			<p>
				<?php printf( __( 'To add job board functionality to your website %s needs to create several front-end pages.', 'jobboardwp' ), jb_plugin_name ); ?>
			</p>
			<p>
				<a href="<?php echo esc_attr( add_query_arg( 'jb_adm_action', 'install_core_pages' ) ); ?>" class="button button-primary">
					<?php _e( 'Create Pages', 'jobboardwp' ) ?>
				</a>
				&nbsp;
				<a href="javascript:void(0);" class="button-secondary jb_secondary_dimiss">
					<?php _e( 'No thanks', 'jobboardwp' ) ?>
				</a>
			</p>

			<?php $message = ob_get_clean();

			$this->add( 'wrong_pages', [
				'class'         => 'updated',
				'message'       => $message,
				'dismissible'   => true
			], 20 );
		}
	}
}