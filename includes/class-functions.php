<?php if ( ! defined( 'ABSPATH' ) ) exit;


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
		var $templates_path;


		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		var $theme_templates;


		/**
		 * @var bool CPU Links Structure
		 *
		 * @since 1.0
		 */
		var $is_permalinks;


		/**
		 * @var string Standard or Minified versions
		 *
		 * @since 1.0
		 */
		var $scrips_prefix = '';


		/**
		 * JB_Functions constructor.
		 */
		function __construct() {
		}


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
		 * Get template path
		 *
		 * @param string $slug
		 * @return string
		 *
		 * @since 1.0
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
		 *
		 * @since 1.0
		 */
		function get_template_part( $slug, $args = [] ) {
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
		 * @param bool $allow_html
		 * @param bool $echo
		 *
		 * @return false|string
		 *
		 * @since 1.0
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
		 *
		 * @since 1.0
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
	}
}