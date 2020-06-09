<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'JB_Functions' ) ) {


	/**
	 * Class JB_Functions
	 */
	class JB_Functions {

		/**
		 * @var string
		 */
		var $templates_path;


		/**
		 * @var string
		 */
		var $theme_templates;


		/**
		 * @var bool CPU Links Structure
		 */
		var $is_permalinks;


		/**
		 * @var string Standard or Minified versions
		 */
		var $scrips_prefix = '';


		/**
		 * JB_Functions constructor.
		 */
		function __construct() {
		}


		/**
		 * Check frontend nonce
		 *
		 * @param bool $action
		 */
		function check_ajax_nonce( $action = false ) {
			$nonce = isset( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : '';
			$action = empty( $action ) ? 'jb-frontend-nonce' : $action;

			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_send_json_error( __( 'Wrong Frontend Nonce', 'jobboardwp' ) );
			}
		}


		/**
		 * @param $array
		 * @param $key
		 * @param $insert_array
		 *
		 * @return mixed
		 */
		function array_insert_before( $array, $key, $insert_array ) {
			$index = array_search( $key, array_keys( $array ) );
			if ( $index === false ) {
				return $array;
			}

			$array = array_slice( $array, 0, $index, true ) +
				   $insert_array +
				   array_slice( $array, $index, count( $array ) - 1, true );

			return $array;
		}


		/**
		 * @param $array
		 * @param $key
		 * @param $insert_array
		 *
		 * @return mixed
		 */
		function array_insert_after( $array, $key, $insert_array ) {
			$index = array_search( $key, array_keys( $array ) );
			if ( $index === false ) {
				return $array;
			}

			$array = array_slice( $array, 0, $index + 1, true ) +
				   $insert_array +
				   array_slice( $array, $index + 1, count( $array ) - 1, true );

			return $array;
		}


		/**
		 * What type of request is this?
		 *
		 * @param string $type String containing name of request type (ajax, frontend, cron or admin)
		 *
		 * @return bool
		 */
		public function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}

			return false;
		}


		/**
		 * @param int $size
		 * @param string $display
		 * @param bool $echo
		 *
		 * @return string
		 */
		function ajax_loader( $size, $display = 'absolute_center', $echo = true ) {
			$this->ajax_loader_styles( $size, $display );

			ob_start(); ?>

			<div class="jb-ajax-loading jb-ajax-<?php echo $size ?>"></div>

			<?php if ( $echo ) {
				ob_get_flush();
			} else {
				$content = ob_get_clean();
				return $content;
			}
			return '';
		}


		/**
		 * @param $size
		 * @param string $display
		 */
		function ajax_loader_styles( $size, $display = 'absolute_center' ) {
			if ( ! JB()->frontend()->templates()->check_preloader_css( $size, $display ) ) {
				$border = round( $size * 0.08 );
				$font_size = round( $size * 0.7 );

				$style = '';
				if ( $display == 'absolute_center' ) {
					$style = 'position:absolute;left: calc(50% - ' . $font_size . 'px);
					top: calc(50% - ' . $font_size . 'px);';
				}

				$custom_css = '.jb-ajax-loading.jb-ajax-' . $size . ' {
					border-width:' . $border . 'px;
					font-size:' . $font_size . 'px;' . $style . '
					width:' . $size . 'px;
					height:' . $size . 'px;
				}';

				wp_add_inline_style( 'jb-common', $custom_css );
			}
		}


		/**
		 * @param $page
		 *
		 * @return bool
		 */
		function is_core_page( $page ) {
			global $post;

			if ( empty( $post ) ) {
				return false;
			}

			$preset_page_id = JB()->permalinks()->get_preset_page_id( $page );
			if ( isset( $post->ID ) && ! empty( $preset_page_id ) && $post->ID == $preset_page_id ) {
				return true;
			}

			return false;
		}


		/**
		 * Get template path
		 *
		 * @param string $slug
		 * @return string
		 */
		function get_template( $slug ) {
			$file_list = $this->templates_path . "{$slug}.php";

			$theme_file = $this->theme_templates . "{$slug}.php";
			if ( file_exists( $theme_file ) ) {
				$file_list = $theme_file;
			}

			return $file_list;
		}


		/**
		 * Load template
		 *
		 * @param string $slug
		 * @param array $args
		 */
		function get_template_part( $slug, $args = [] ) {
			global $wp_query;

			$query_title = str_replace( '-', '_', sanitize_title( $slug ) );

			$wp_query->query_vars[ 'jb_' . $query_title ] = $args;

			$template = $this->get_template( $slug );

			load_template( $template, false );
		}


		/**
		 * @param $tip
		 * @param bool $allow_html
		 * @param bool $echo
		 *
		 * @return false|string
		 */
		function helptip( $tip, $allow_html = false, $echo = true ) {

			wp_enqueue_script( 'jb-helptip' );
			wp_enqueue_style( 'jb-helptip' );

			if ( $allow_html ) {
				$tip = htmlspecialchars( wp_kses( html_entity_decode( $tip ), [
					'br'     => [],
					'em'     => [],
					'strong' => [],
					'small'  => [],
					'span'   => [],
					'ul'     => [],
					'li'     => [],
					'ol'     => [],
					'p'      => [],
				] ) );

			} else {
				$tip = esc_attr( $tip );
			}

			ob_start(); ?>

			<span class="jb-helptip dashicons dashicons-editor-help" title="<?php echo $tip ?>"></span>

			<?php if ( $echo ) {
				ob_get_flush();
				return '';
			} else {
				return ob_get_clean();
			}

		}



		/**
		 * Disable page caching and set or clear cookie
		 *
		 * @param string $name
		 * @param string $value
		 * @param int $expire
		 * @param string $path
		 */
		function setcookie( $name, $value = '', $expire = 0, $path = '' ) {
			if ( empty( $value ) ) {
				$expire = time() - YEAR_IN_SECONDS;
			}
			if ( empty( $path ) ) {
				list( $path ) = explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) );
			}

			$levels = ob_get_level();
			for ( $i = 0; $i < $levels; $i++ ) {
				@ob_end_clean();
			}

			nocache_headers();
			setcookie( $name, $value, $expire, $path, COOKIE_DOMAIN, is_ssl(), true );
		}


		/**
		 * Show a cool time difference between 2 timestamps
		 *
		 * @param int $from
		 * @param string|int $to
		 *
		 * @return string
		 */
		function time_diff( $from, $to = '' ) {
			$since = '';

			if ( empty( $to ) ) {
				$to = time();
			}

			$diff = (int) abs( $to - $from );
			if ( $diff < 60 ) {

				$since = __( 'just now', 'jobboardwp' );

			} elseif ( $diff < HOUR_IN_SECONDS ) {

				$mins = round( $diff / MINUTE_IN_SECONDS );
				if ( $mins <= 1 ) {
					$mins = 1;
				}

				$since = sprintf( _n( '%s min', '%s mins', $mins, 'jobboardwp' ), $mins );

			} elseif ( $diff < DAY_IN_SECONDS && $diff >= HOUR_IN_SECONDS ) {

				$hours = round( $diff / HOUR_IN_SECONDS );
				if ( $hours <= 1 ) {
					$hours = 1;
				}

				$since = sprintf( _n( '%s hr', '%s hrs', $hours, 'jobboardwp' ), $hours );

			} elseif ( $diff < WEEK_IN_SECONDS && $diff >= DAY_IN_SECONDS ) {

				$days = round( $diff / DAY_IN_SECONDS );
				if ( $days <= 1 ) {
					$days = 1;
				}

				if ( $days == 1 ) {
					$since = sprintf( __( 'Yesterday at %s', 'jobboardwp' ), date_i18n( get_option( 'time_format' ), $from ) );
				} else {
					$since = sprintf( __( '%s at %s', 'jobboardwp' ), date_i18n( 'F d', $from ), date_i18n( get_option( 'time_format' ), $from ) );
				}

			} elseif ( $diff < 30 * DAY_IN_SECONDS && $diff >= WEEK_IN_SECONDS ) {

				$since = sprintf( __( '%s at %s', 'jobboardwp' ), date_i18n( 'F d', $from ), date_i18n( get_option( 'time_format' ), $from ) );

			} elseif ( $diff < YEAR_IN_SECONDS && $diff >= 30 * DAY_IN_SECONDS ) {

				$since = sprintf( __( '%s at %s','jobboardwp'), date_i18n( 'F d', $from ), date_i18n( get_option( 'time_format' ), $from ) );

			} elseif ( $diff >= YEAR_IN_SECONDS ) {

				$since = sprintf( __( '%s at %s', 'jobboardwp' ), date_i18n( get_option( 'date_format' ), $from ), date_i18n( get_option( 'time_format' ), $from ) );

			}

			return apply_filters( 'jb_human_time_diff', $since, $diff, $from, $to );
		}
	}
}