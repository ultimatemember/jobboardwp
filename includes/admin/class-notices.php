<?php
namespace jb\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


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
		 *
		 * @since 1.0
		 */
		public $list = array();


		/**
		 * Notices constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( &$this, 'create_list' ), 10 );
			add_action( 'admin_notices', array( &$this, 'render' ), 1 );
		}


		/**
		 * Initialize all admin notices
		 *
		 * @since 1.0
		 */
		public function create_list() {
			$this->install_core_page_notice();
			do_action( 'jb_admin_create_notices' );
		}


		/**
		 * Render all admin notices
		 *
		 * @since 1.0
		 */
		public function render() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$admin_notices = $this->get_admin_notices();

			$hidden = JB()->options()->get( 'hidden_admin_notices', array() );

			uasort( $admin_notices, array( &$this, 'notice_priority_sort' ) );

			foreach ( $admin_notices as $key => $admin_notice ) {
				if ( empty( $hidden ) || ! in_array( $key, $hidden, true ) ) {
					$this->display( $key );
				}
			}

			do_action( 'jb_admin_after_main_notices' );
		}


		/**
		 * Getting all admin notices
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function get_admin_notices() {
			return $this->list;
		}


		/**
		 * Set admin notices variable
		 *
		 * @param array $admin_notices
		 *
		 * @since 1.0
		 */
		public function set_admin_notices( $admin_notices ) {
			$this->list = $admin_notices;
		}


		/**
		 * Sorting notices in predefined priority
		 *
		 * @param array $a
		 * @param array $b
		 *
		 * @return mixed
		 *
		 * @since 1.0
		 */
		public function notice_priority_sort( $a, $b ) {
			if ( $a['priority'] === $b['priority'] ) {
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
		 *
		 * @since 1.0
		 */
		public function add( $key, $data, $priority = 10 ) {
			$admin_notices = $this->get_admin_notices();

			if ( empty( $admin_notices[ $key ] ) ) {
				$admin_notices[ $key ] = array_merge( $data, array( 'priority' => $priority ) );
				$this->set_admin_notices( $admin_notices );
			}
		}


		/**
		 * Remove notice from JB notices array
		 *
		 * @param string $key
		 *
		 * @since 1.0
		 */
		public function remove_notice( $key ) {
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
		 *
		 * @since 1.0
		 */
		public function display( $key, $echo = true ) {
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

			printf(
				'<div class="jb-admin-notice notice %1$s" data-key="%2$s">%3$s</div>',
				esc_attr( $class ),
				esc_attr( $key ),
				$message // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped in $notice_data
			);

			$notice = ob_get_clean();

			if ( $echo ) {
				echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped
				return '';
			} else {
				return $notice;
			}
		}


		/**
		 * Dismiss notices by key
		 *
		 * @param string $key
		 *
		 * @since 1.0
		 */
		public function dismiss( $key ) {
			$hidden_notices   = JB()->options()->get( 'hidden_admin_notices', array() );
			$hidden_notices[] = $key;
			JB()->options()->update( 'hidden_admin_notices', array_unique( $hidden_notices ) );
		}


		/**
		 * Regarding page setup
		 *
		 * @since 1.0
		 */
		public function install_core_page_notice() {
			if ( JB()->common()->permalinks()->are_pages_installed() || ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$page_titles = array();
			foreach ( JB()->config()->get( 'core_pages' ) as $slug => $array ) {
				$page_titles[] = $array['title'];
			}

			$create_pages_link = add_query_arg(
				array(
					'jb_adm_action' => 'install_core_pages',
					'nonce'         => wp_create_nonce( 'jb_install_core_pages' ),
				)
			);

			ob_start(); ?>

			<p>
				<?php
				printf(
					// translators: %1$s: plugin name, %2$s: list of pre-defined pages
					esc_html__( 'To add job board functionality to your website %1$s needs to create the following pages: %2$s.', 'jobboardwp' ),
					esc_html( JB_PLUGIN_NAME ),
					esc_html( implode( ', ', $page_titles ) )
				);
				?>
			</p>
			<p>
				<a href="<?php echo esc_attr( $create_pages_link ); ?>" class="button button-primary">
					<?php esc_html_e( 'Create Pages', 'jobboardwp' ); ?>
				</a>
				&nbsp;
				<a href="javascript:void(0);" class="button-secondary jb_secondary_dismiss">
					<?php esc_html_e( 'No thanks', 'jobboardwp' ); ?>
				</a>
			</p>

			<?php
			$message = ob_get_clean();

			$this->add(
				'wrong_pages',
				array(
					'class'       => 'updated',
					'message'     => $message,
					'dismissible' => true,
				),
				20
			);
		}
	}
}
