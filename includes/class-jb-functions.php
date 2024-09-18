<?php
if ( ! defined( 'ABSPATH' ) ) {
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
		 * Define constant if not already set.
		 *
		 * @since 1.1.1
		 * @access protected
		 *
		 * @param string      $name  Constant name.
		 * @param string|bool $value Constant value.
		 */
		protected function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
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

		/**
		 * @param string $context
		 *
		 * @return array
		 */
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
							'min'          => true,
							'max'          => true,
							'step'         => true,
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
						'button'   => array(
							'type' => true,
						),
					);
					break;
				case 'templates':
					$allowed_html = array(
						'style'    => array(),
						'link'     => array(
							'rel'   => true,
							'href'  => true,
							'media' => true,
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
							'size'         => true,
							'min'          => true,
							'max'          => true,
							'step'         => true,
						),
						'textarea' => array(
							'cols'         => true,
							'rows'         => true,
							'disabled'     => true,
							'name'         => true,
							'readonly'     => true,
							'required'     => true,
							'autocomplete' => true,
							'placeholder'  => true,
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
				'code'   => array(),
			);

			$allowed_html = array_merge_recursive( $global_allowed, $allowed_html );
			$allowed_html = array_map( '_wp_add_global_attributes', $allowed_html );

			/**
			 * Filters the allowed HTML tags and their attributes in the late escaping before echo.
			 *
			 * Note: Please use the `wp_kses()` allowed tags structure.
			 *
			 * @since 1.1.0
			 * @hook jb_late_escaping_allowed_tags
			 *
			 * @param {array}  $allowed_html Allowed HTML tags with attributes.
			 * @param {string} $context      Function context 'wp-admin' for Admin Dashboard echo, 'templates' for the frontend.
			 *
			 * @return {array} Allowed HTML tags with attributes.
			 */
			return apply_filters( 'jb_late_escaping_allowed_tags', $allowed_html, $context );
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
			if ( empty( $path ) && isset( $_SERVER['REQUEST_URI'] ) ) {
				list( $path ) = explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- REQUEST_URI ok
			}

			$levels = ob_get_level();
			for ( $i = 0; $i < $levels; $i++ ) {
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				@ob_end_clean();
			}

			nocache_headers();
			setcookie( $name, $value, $expire, $path, COOKIE_DOMAIN, is_ssl(), true );
		}

		/**
		 * Get the current URL anywhere.
		 *
		 * @param bool $no_query_params
		 *
		 * @return mixed|void
		 */
		public function get_current_url( $no_query_params = false ) {
			//use WP native function for fill $_SERVER variables by correct values
			wp_fix_server_vars();

			$host = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : 'localhost'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- HTTP_HOST ok
			$url  = ( is_ssl() ? 'https://' : 'http://' ) . $host;
			$url .= ! empty( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- REQUEST_URI ok

			if ( true === $no_query_params ) {
				$url = strtok( $url, '?' );
			}

			/**
			 * Filters the current URL.
			 *
			 * @since 1.2.6
			 * @hook jb_get_current_url
			 *
			 * @param {string} $url             Current URL.
			 * @param {bool}   $no_query_params Set to `true` if needed clear URL without $_GET attributes. It's `false` by default.
			 *
			 * @return {string} Filtered current URL.
			 */
			return apply_filters( 'jb_get_current_url', $url, $no_query_params );
		}

		/**
		 * Easy merge arrays based on parent array key. Insert after selected key
		 *
		 * @since 1.1.1
		 *
		 * @param array $haystack
		 * @param string $key
		 * @param array $insert_array
		 *
		 * @return array
		 */
		public function array_insert_after( $haystack, $key, $insert_array ) {
			$index = array_search( $key, array_keys( $haystack ), true );
			if ( false === $index ) {
				return $haystack;
			}

			return array_slice( $haystack, 0, $index + 1, true ) + $insert_array + array_slice( $haystack, $index + 1, count( $haystack ) - 1, true );
		}

		/**
		 * Get the template path inside theme or custom path
		 *
		 * @since 1.1.1
		 * @since 1.2.2 Added $module argument.
		 * @access public
		 *
		 * @param string $module Module slug. (default: '').
		 *
		 * @return string
		 */
		public function template_path( $module = '' ) {
			$path = 'jobboardwp/';

			if ( ! empty( $module ) ) {
				$path .= "$module/";
			}

			/**
			 * Filters the template path inside theme or custom path.
			 *
			 * @since 1.1.1
			 * @since 1.2.2 Added $module argument.
			 * @hook jb_template_path
			 *
			 * @param {string} $path   JobBoardWP templates' path.
			 * @param {string} $module Module slug. (default: '').
			 *
			 * @return {string} JobBoardWP templates' path.
			 */
			return apply_filters( 'jb_template_path', $path, $module );
		}


		/**
		 * Get the default template path inside wp-content/plugins/
		 *
		 * @since 1.1.0
		 * @since 1.2.2 Added $module argument.
		 * @access public
		 *
		 * @param string $module Module slug. (default: '').
		 *
		 * @return string
		 */
		public function default_templates_path( $module = '' ) {
			$path = untrailingslashit( JB_PATH ) . '/templates/';
			if ( ! empty( $module ) ) {
				$module_data = JB()->modules()->get_data( $module );
				$path        = untrailingslashit( $module_data['path'] ) . '/templates/';
			}

			/**
			 * Filters the default template path inside `wp-content/plugins/`.
			 *
			 * @since 1.1.0
			 * @since 1.2.2 Added $module argument.
			 * @hook jb_default_template_path
			 *
			 * @param {string} $path   JobBoardWP default templates' path.
			 * @param {string} $module Module slug. (default: '').
			 *
			 * @return {string} JobBoardWP default templates' path.
			 */
			return apply_filters( 'jb_default_template_path', $path, $module );
		}


		/**
		 * Get JobBoardWP custom templates (e.g. jobs list) passing attributes and including the file.
		 *
		 * @since 1.1.0
		 * @since 1.2.2 Added $module argument.
		 *
		 * @param string $template_name Template name.
		 * @param array  $args          Arguments. (default: array).
		 * @param string $module        Module slug. (default: '').
		 * @param string $template_path Template path. (default: '').
		 * @param string $default_path  Default path. (default: '').
		 */
		public function get_template_part( $template_name, $args = array(), $module = '', $template_path = '', $default_path = '' ) {
			/**
			 * Fires just before the algorithm for getting JobBoardWP custom templates.
			 *
			 * Note: Allow 3rd party plugins or modules filter template file, arguments, module name from their side.
			 *
			 * @since 1.2.2
			 * @hook jb_change_template_part
			 *
			 * @param {string} $template_name Template name passed by reference.
			 * @param {array}  $args          Arguments passed for the template by reference.
			 * @param {string} $module        Module slug passed by reference. (default: '').
			 * @param {string} $template_path Template path passed by reference. (default: '').
			 * @param {string} $default_path  Default path passed by reference. (default: '').
			 */
			do_action_ref_array( 'jb_change_template_part', array( &$template_name, &$args, &$module, &$template_path, &$default_path ) );

			$template = $this->locate_template( $template_name, $module, $template_path, $default_path );

			/**
			 * Filters the template location.
			 *
			 * Note: Allow 3rd party plugin filter template file from their plugin.
			 *
			 * @since 1.1.1
			 * @since 1.2.2 Added $module argument.
			 * @hook jb_get_template
			 *
			 * @param {string} $template      Predefined template location. That has been found via the `JB()->locate_template()` function.
			 * @param {string} $template_name Template name.
			 * @param {array}  $args          Arguments passed for the template.
			 * @param {string} $module        Module slug. (default: '').
			 * @param {string} $template_path Template path. (default: '').
			 * @param {string} $default_path  Default path. (default: '').
			 *
			 * @return {string} Maybe a custom location for the $template_name.
			 */
			$filter_template = apply_filters( 'jb_get_template', $template, $template_name, $args, $module, $template_path, $default_path );

			if ( $filter_template !== $template ) {
				if ( ! file_exists( $filter_template ) ) {
					/* translators: %s template */
					_doing_it_wrong( __FUNCTION__, wp_kses( sprintf( __( '<code>%s</code> does not exist.', 'jobboardwp' ), $filter_template ), $this->get_allowed_html( 'templates' ) ), esc_html( JB_VERSION ) );
					return;
				}
				$template = $filter_template;
			}

			$action_args = array(
				'template_name' => $template_name,
				'template_path' => $template_path,
				'located'       => $template,
				'args'          => $args,
				'module'        => $module,
			);

			$query_title                  = str_replace( '-', '_', sanitize_title( $template_name ) );
			$args[ 'jb_' . $query_title ] = $action_args['args'];

			if ( ! empty( $args ) && is_array( $args ) ) {
				if ( isset( $args['action_args'] ) ) {
					_doing_it_wrong( __FUNCTION__, esc_html__( '`action_args` should not be overwritten when calling `jb_get_template()`.', 'jobboardwp' ), esc_html( JB_VERSION ) );
					unset( $args['action_args'] );
				}

				extract( $args, EXTR_SKIP ); // @codingStandardsIgnoreLine
			}

			/**
			 * Fires before the content of the template is displayed.
			 *
			 * @since 1.1.0
			 * @since 1.2.2 Added $module argument.
			 *
			 * @hook jb_before_template_part
			 *
			 * @param {string} $template_name Template name. E.g. 'job/info' or 'job-categories', etc. See templates folder and see more keys
			 * @param {string} $located       The path to the template from which it will be displayed. Can be default or placed in theme.
			 * @param {string} $module        Module slug. (default: '').
			 * @param {array}  $args          Arguments passed into template.
			 * @param {string} $template_path The path to template. Can be custom for 3rd-party integrations. (default: '').
			 */
			do_action( 'jb_before_template_part', $action_args['template_name'], $action_args['located'], $action_args['module'], $action_args['args'], $action_args['template_path'] );

			include $action_args['located'];

			/**
			 * Fires after the content of the template is displayed.
			 *
			 * @since 1.1.0
			 * @since 1.2.2 Added $module argument.
			 *
			 * @hook jb_after_template_part
			 *
			 * @param {string} $template_name Template name. E.g. 'job/info' or 'job-categories', etc. See templates folder and see more keys
			 * @param {string} $located       The path to the template from which it will be displayed. Can be default or placed in theme.
			 * @param {string} $module        Module slug. (default: '').
			 * @param {array}  $args          Arguments passed into template.
			 * @param {string} $template_path The path to template. Can be custom for 3rd-party integrations. (default: '').
			 */
			do_action( 'jb_after_template_part', $action_args['template_name'], $action_args['located'], $action_args['module'], $action_args['args'], $action_args['template_path'] );
		}


		/**
		 * Like get_template, but returns the HTML instead of outputting.
		 *
		 * @see get_template
		 *
		 * @since 1.1.1
		 * @since 1.2.2 Added $module argument.
		 *
		 * @param string $template_name Template name.
		 * @param array  $args          Arguments. (default: array).
		 * @param string $module        Module slug. (default: '').
		 * @param string $template_path Template path. (default: '').
		 * @param string $default_path  Default path. (default: '').
		 *
		 * @return string
		 */
		public function get_template_html( $template_name, $args = array(), $module = '', $template_path = '', $default_path = '' ) {
			ob_start();
			$this->get_template_part( $template_name, $args, $module, $template_path, $default_path );
			return ob_get_clean();
		}


		/**
		 * Locate a template and return the path for inclusion.
		 *
		 * This is the load order:
		 *
		 * yourtheme/$blog_id/$locale/$template_path/$template_name
		 * yourtheme/$blog_id/$template_path/$template_name
		 * yourtheme/$locale/$template_path/$template_name
		 * yourtheme/$template_path/$template_name
		 * $default_path/$template_name
		 *
		 * where $locale is site_locale for regular templates, but $user_locale for email templates
		 *
		 * @since 1.1.1
		 * @since 1.2.2 Added $module argument.
		 *
		 * @param string $template_name Template name.
		 * @param string $module        Module slug. (default: '').
		 * @param string $template_path Template path. (default: '').
		 * @param string $default_path  Default path. (default: '').
		 * @return string
		 */
		public function locate_template( $template_name, $module = '', $template_path = '', $default_path = '' ) {
			$template_name .= '.php';

			// path in theme
			if ( ! $template_path ) {
				$template_path = $this->template_path( $module );
			}

			$template_locations = array(
				trailingslashit( $template_path ) . $template_name,
			);

			/**
			 * Filters the template locations array for WP native `locate_template()` function.
			 *
			 * Note: Handle locations array before multisite's blog ID path will be added. JobBoardWP uses this hook for integration with multilingual plugins.
			 *
			 * @since 1.1.1
			 * @since 1.2.2 Added $module argument.
			 * @hook jb_pre_template_locations
			 *
			 * @param {array}  $template_locations Template locations array for WP native `locate_template()` function.
			 * @param {string} $template_name      Template name.
			 * @param {string} $module             Module slug. (default: '').
			 * @param {string} $template_path      Template path. (default: '').
			 *
			 * @return {array} An array for WP native `locate_template()` function with paths where we need to search for the $template_name.
			 */
			$template_locations = apply_filters( 'jb_pre_template_locations', $template_locations, $template_name, $module, $template_path );

			// build multisite blog_ids priority paths
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();

				$ms_template_locations = array_map(
					static function ( $item ) use ( $template_path, $blog_id ) {
						return str_replace( trailingslashit( $template_path ), trailingslashit( $template_path ) . $blog_id . '/', $item );
					},
					$template_locations
				);

				$template_locations = array_merge( $ms_template_locations, $template_locations );
			}

			/**
			 * Filters the template locations array for WP native `locate_template()` function.
			 *
			 * Note: Final chance for getting customized the templates locations array.
			 *
			 * @since 1.1.1
			 * @since 1.2.2 Added $module argument.
			 * @hook jb_template_locations
			 *
			 * @param {array}  $template_locations Template locations array for WP native `locate_template()` function.
			 * @param {string} $template_name      Template name.
			 * @param {string} $module             Module slug. (default: '').
			 * @param {string} $template_path      Template path. (default: '').
			 *
			 * @return {array} An array for WP native `locate_template()` function with paths where we need to search for the $template_name.
			 */
			$template_locations = apply_filters( 'jb_template_locations', $template_locations, $template_name, $module, $template_path );

			$template_locations = array_map( 'wp_normalize_path', $template_locations );

			/**
			 * Filters the custom path variable. There is possible to set your custom templates path.
			 *
			 * Note: You could use this hook for getting JobBoardWP templates stored in your own custom path (e.g. uploads/ folder).
			 * This can be used to avoid the possibility of dumping templates if you are using a theme that is constantly updated and it is not possible to create a child-theme.
			 *
			 * @since 1.1.1
			 * @since 1.2.2 Added $module argument.
			 * @hook jb_template_structure_custom_path
			 *
			 * @param {bool}   $custom_path   Custom path to JobBoardWP templates. It's `false` by default.
			 * @param {string} $template_name Template name.
			 * @param {string} $module        Module slug. (default: '').
			 *
			 * @return {bool|string} Maybe custom path to JobBoardWP templates. Otherwise false.
			 */
			$custom_path = apply_filters( 'jb_template_structure_custom_path', false, $template_name, $module );
			if ( false === $custom_path || ! is_dir( $custom_path ) ) {
				$template = locate_template( $template_locations );
			} else {
				$template = $this->locate_template_custom_path( $template_locations, $custom_path );
			}

			// Get default template in cases:
			// 1. Conflict test constant is defined and TRUE
			// 2. There aren't any proper template in custom or theme directories
			if ( ! $template || ( defined( 'JB_TEMPLATE_CONFLICT_TEST' ) && JB_TEMPLATE_CONFLICT_TEST ) ) {
				// default path in plugin
				if ( ! $default_path ) {
					$default_path = $this->default_templates_path( $module );
				}

				$template = wp_normalize_path( trailingslashit( $default_path ) . $template_name );
			}

			// Return what we found.
			/**
			 * Filters the founded template location.
			 *
			 * Note: Ignore all locate rules for the selected $template_name.
			 *
			 * @since 1.1.1
			 * @since 1.2.2 Added $module argument.
			 * @hook jb_locate_template
			 *
			 * @param {string} $template      Template full path.
			 * @param {string} $template_name Template name.
			 * @param {string} $module        Module slug. (default: '').
			 * @param {string} $template_path Template path. (default: '').
			 *
			 * @return {string} Template full path.
			 */
			return apply_filters( 'jb_locate_template', $template, $template_name, $module, $template_path );
		}


		/**
		 * Retrieve the name of the highest priority template file that exists in custom path.
		 *
		 * @since 1.1.1
		 *
		 * @param string|array $template_locations Template file(s) to search for, in order.
		 * @param string       $custom_path        Custom path to the JB templates.
		 *
		 * @return string The template filename if one is located.
		 */
		public function locate_template_custom_path( $template_locations, $custom_path ) {
			$located = '';

			foreach ( (array) $template_locations as $template_location ) {
				if ( ! $template_location ) {
					continue;
				}

				$path = wp_normalize_path( trailingslashit( $custom_path ) . $template_location );
				if ( file_exists( $path ) ) {
					$located = $path;
					break;
				}
			}

			return $located;
		}

		/**
		 * @param string $email_key
		 * @param bool $with_ext
		 *
		 * @return string
		 */
		public function get_email_template( $email_key, $with_ext = true ) {
			$template_path = $with_ext ? "emails/{$email_key}.php" : "emails/{$email_key}";
			/**
			 * Filters an email template path inside `jobboardwp/` folder.
			 *
			 * @since 1.1.1
			 * @hook jb_email_template_path
			 *
			 * @param {string} $template  Email template path.
			 * @param {string} $email_key Email notification key.
			 *
			 * @return {string} Email template path. 'emails/{$email_key}.php' by default.
			 */
			return apply_filters( 'jb_email_template_path', $template_path, $email_key );
		}

		/**
		 * Getting module slug for email notification.
		 *
		 * @since 1.2.2
		 *
		 * @param string $email_key Email Notification key.
		 *
		 * @return bool|string false                               If email key doesn't exist in the email notifications list
		 *                     '{empty_string}' or '{module_slug}' For core email notification or email notification inside the module.
		 */
		public function get_email_template_module( $email_key ) {
			$email_notifications = JB()->config()->get( 'email_notifications' );
			if ( ! array_key_exists( $email_key, $email_notifications ) ) {
				return false;
			}

			if ( ! array_key_exists( 'module', $email_notifications[ $email_key ] ) ) {
				return '';
			}

			return $email_notifications[ $email_key ]['module'];
		}

		/**
		 * Undash string. Easy operate
		 *
		 * @since 1.2.2
		 * @param string $slug
		 *
		 * @return string
		 */
		public function undash( $slug ) {
			return str_replace( '-', '_', $slug );
		}

		/**
		 * Get the price format depending on the currency position.
		 *
		 * @return string
		 */
		public function get_job_salary_format( $context = '' ) {
			$currency_pos = JB()->options()->get( 'job-salary-currency-pos' );

			switch ( $currency_pos ) {
				case 'right':
					$format = ( 'js' === $context ) ? '${salary}${symbol}' : '%2$s%1$s';
					break;
				case 'left_space':
					$format = ( 'js' === $context ) ? '${symbol} ${salary}' : '%1$s&nbsp;%2$s';
					break;
				case 'right_space':
					$format = ( 'js' === $context ) ? '${salary} ${symbol}' : '%2$s&nbsp;%1$s';
					break;
				case 'left':
				default:
					$format = ( 'js' === $context ) ? '${symbol}${salary}' : '%1$s%2$s';
					break;
			}

			/**
			 * Filters job salary format.
			 *
			 * @since 1.2.6
			 * @hook jb_job_salary_format
			 *
			 * @param {string} $format  Email template path.
			 * @param {string} $currency_pos Email notification key.
			 *
			 * @return {string} Email template path. 'emails/{$email_key}.php' by default.
			 */
			return apply_filters( 'jb_job_salary_format', $format, $currency_pos );
		}
	}
}
