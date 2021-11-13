<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'JB_Functions' ) ) {


	/**
	 * Class JB_Functions
	 */
	class JB_Functions {


		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		public $templates_path;


		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		public $theme_templates;


		/**
		 * @var bool CPU Links Structure
		 *
		 * @since 1.0
		 */
		public $is_permalinks;


		/**
		 * @var string Standard or Minified versions
		 *
		 * @since 1.0
		 */
		public $scrips_prefix = '';


		/**
		 * What type of request is this?
		 *
		 * @param string $type String containing name of request type (ajax, frontend, cron or admin)
		 *
		 * @return bool
		 *
		 * @since 1.0
		 */
		public function is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined( 'DOING_AJAX' );
				case 'cron':
					return defined( 'DOING_CRON' );
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}

			return false;
		}


		/**
		 * Get template path
		 *
		 * @param string $slug
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_template( $slug ) {
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
		 *
		 * @since 1.0
		 */
		public function get_template_part( $slug, $args = array() ) {
			global $wp_query;

			$query_title = str_replace( '-', '_', sanitize_title( $slug ) );

			$wp_query->query_vars[ 'jb_' . $query_title ] = $args;

			$template = $this->get_template( $slug );

			if ( file_exists( $template ) ) {
				load_template( $template, false );
			}
		}


		/**
		 * Forms labels helptips
		 *
		 * @param string $tip
		 *
		 * @return false|string
		 *
		 * @since 1.0
		 */
		public function helptip( $tip ) {
			wp_enqueue_script( 'jb-helptip' );
			wp_enqueue_style( 'jb-helptip' );

			ob_start();
			?>

			<span class="jb-helptip dashicons dashicons-editor-help" title="<?php echo esc_attr( $tip ); ?>"></span>

			<?php
			return ob_get_clean();
		}


		public function get_allowed_html( $context = '' ) {
			switch ( $context ) {
				case 'wp-admin':
					$allowed_html = array(
						'img'      => array(
							'alt'      => true,
							'align'    => true,
							'border'   => true,
							'height'   => true,
							'hspace'   => true,
							'loading'  => true,
							'longdesc' => true,
							'vspace'   => true,
							'src'      => true,
							'srcset'   => true,
							'usemap'   => true,
							'width'    => true,
						),
						'ul'       => array(),
						'li'       => array(),
						'h1'       => array(
							'align' => true,
						),
						'h2'       => array(
							'align' => true,
						),
						'h3'       => array(
							'align' => true,
						),
						'p'        => array(
							'align' => true,
							'dir'   => true,
							'lang'  => true,
						),
						'form'     => array(
							'action'         => true,
							'accept'         => true,
							'accept-charset' => true,
							'enctype'        => true,
							'method'         => true,
							'name'           => true,
							'target'         => true,
						),
						'label'    => array(
							'for' => true,
						),
						'select'   => array(
							'name'         => true,
							'multiple'     => true,
							'disabled'     => true,
							'readonly'     => true,
							'required'     => true,
							'autocomplete' => true,
						),
						'option'   => array(
							'value'    => true,
							'selected' => true,
							'disabled' => true,
						),
						'input'    => array(
							'type'         => true,
							'name'         => true,
							'value'        => true,
							'placeholder'  => true,
							'readonly'     => true,
							'disabled'     => true,
							'checked'      => true,
							'selected'     => true,
							'required'     => true,
							'autocomplete' => true,
						),
						'textarea' => array(
							'cols'         => true,
							'rows'         => true,
							'disabled'     => true,
							'name'         => true,
							'readonly'     => true,
							'required'     => true,
							'autocomplete' => true,
						),
						'table'    => array(
							'align'       => true,
							'bgcolor'     => true,
							'border'      => true,
							'cellpadding' => true,
							'cellspacing' => true,
							'dir'         => true,
							'rules'       => true,
							'summary'     => true,
							'width'       => true,
						),
						'tbody'    => array(
							'align'   => true,
							'char'    => true,
							'charoff' => true,
							'valign'  => true,
						),
						'td'       => array(
							'abbr'    => true,
							'align'   => true,
							'axis'    => true,
							'bgcolor' => true,
							'char'    => true,
							'charoff' => true,
							'colspan' => true,
							'dir'     => true,
							'headers' => true,
							'height'  => true,
							'nowrap'  => true,
							'rowspan' => true,
							'scope'   => true,
							'valign'  => true,
							'width'   => true,
						),
						'tfoot'    => array(
							'align'   => true,
							'char'    => true,
							'charoff' => true,
							'valign'  => true,
						),
						'th'       => array(
							'abbr'    => true,
							'align'   => true,
							'axis'    => true,
							'bgcolor' => true,
							'char'    => true,
							'charoff' => true,
							'colspan' => true,
							'headers' => true,
							'height'  => true,
							'nowrap'  => true,
							'rowspan' => true,
							'scope'   => true,
							'valign'  => true,
							'width'   => true,
						),
						'thead'    => array(
							'align'   => true,
							'char'    => true,
							'charoff' => true,
							'valign'  => true,
						),
						'tr'       => array(
							'align'   => true,
							'bgcolor' => true,
							'char'    => true,
							'charoff' => true,
							'valign'  => true,
						),
					);
					break;
				case 'templates':
					$allowed_html = array(
						'style'    => array(),
						'form'     => array(
							'action'         => true,
							'accept'         => true,
							'accept-charset' => true,
							'enctype'        => true,
							'method'         => true,
							'name'           => true,
							'target'         => true,
						),
						'label'    => array(
							'for' => true,
						),
						'select'   => array(
							'name'         => true,
							'multiple'     => true,
							'disabled'     => true,
							'readonly'     => true,
							'required'     => true,
							'autocomplete' => true,
						),
						'option'   => array(
							'value'    => true,
							'selected' => true,
							'disabled' => true,
						),
						'input'    => array(
							'type'         => true,
							'name'         => true,
							'value'        => true,
							'placeholder'  => true,
							'readonly'     => true,
							'disabled'     => true,
							'checked'      => true,
							'selected'     => true,
							'required'     => true,
							'autocomplete' => true,
						),
						'textarea' => array(
							'cols'         => true,
							'rows'         => true,
							'disabled'     => true,
							'name'         => true,
							'readonly'     => true,
							'required'     => true,
							'autocomplete' => true,
						),
						'img'      => array(
							'alt'      => true,
							'align'    => true,
							'border'   => true,
							'height'   => true,
							'hspace'   => true,
							'loading'  => true,
							'longdesc' => true,
							'vspace'   => true,
							'src'      => true,
							'srcset'   => true,
							'usemap'   => true,
							'width'    => true,
						),
						'h1'       => array(
							'align' => true,
						),
						'h2'       => array(
							'align' => true,
						),
						'h3'       => array(
							'align' => true,
						),
						'p'        => array(
							'align' => true,
							'dir'   => true,
							'lang'  => true,
						),
						'ul'       => array(),
						'li'       => array(),
						'time'     => array(
							'datetime' => true,
						),
					);
					break;
				case 'admin_notice':
					$allowed_html = array(
						'p'     => array(
							'align' => true,
							'dir'   => true,
							'lang'  => true,
						),
						'label' => array(
							'for' => true,
						),
					);
					break;
				default:
					$allowed_html = array();
					break;
			}

			$global_allowed = array(
				'a'      => array(
					'href'     => array(),
					'rel'      => true,
					'rev'      => true,
					'name'     => true,
					'target'   => true,
					'download' => array(
						'valueless' => 'y',
					),
				),
				'em'     => array(),
				'i'      => array(),
				'q'      => array(
					'cite' => true,
				),
				's'      => array(),
				'strike' => array(),
				'strong' => array(),
				'br'     => array(),
				'div'    => array(
					'align' => true,
					'dir'   => true,
					'lang'  => true,
				),
				'span'   => array(
					'dir'   => true,
					'align' => true,
					'lang'  => true,
				),
			);

			$allowed_html = array_merge( $global_allowed, $allowed_html );
			$allowed_html = array_map( '_wp_add_global_attributes', $allowed_html );
			$allowed_html = apply_filters( 'jb_late_escaping_allowed_tags', $allowed_html, $context );
			return $allowed_html;
		}


		/**
		 * Disable page caching and set or clear cookie
		 *
		 * @param string $name
		 * @param string $value
		 * @param int $expire
		 * @param string $path
		 *
		 * @since 1.0
		 */
		public function setcookie( $name, $value = '', $expire = 0, $path = '' ) {
			if ( empty( $value ) ) {
				$expire = time() - YEAR_IN_SECONDS;
			}
			if ( empty( $path ) ) {
				list( $path ) = explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) );
			}

			$levels = ob_get_level();
			for ( $i = 0; $i < $levels; $i++ ) {
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				@ob_end_clean();
			}

			nocache_headers();
			setcookie( $name, $value, $expire, $path, COOKIE_DOMAIN, is_ssl(), true );
		}
	}
}
