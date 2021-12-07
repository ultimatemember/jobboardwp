<?php namespace jb\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\frontend\Jobs_Directory' ) ) {


	/**
	 * Class Jobs_Directory
	 *
	 * @package jb\frontend
	 */
	class Jobs_Directory {


		/**
		 * @var array
		 *
		 * @since 1.0
		 */
		public $filters = array();


		/**
		 * @var array
		 *
		 * @since 1.0
		 */
		public $filter_types = array();


		/**
		 * Jobs_Directory constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'init_variables' ) );
			if ( empty( $this->filter_types ) || empty( $this->filters ) ) {
				$this->init_variables();
			}
		}


		/**
		 * Init jobs directory variables
		 *
		 * @since 1.0
		 */
		public function init_variables() {
			/**
			 * Filters the jobs list filters.
			 *
			 * Note: The filters structure is 'filter_key' => 'filter_title'.
			 *
			 * @since 1.0
			 * @hook jb_jobs_directory_filters
			 *
			 * @param {array} $filters Jobs list filters.
			 *
			 * @return {array} Jobs list filters.
			 */
			$this->filters = apply_filters(
				'jb_jobs_directory_filters',
				array(
					'job_type' => __( 'Job Type', 'jobboardwp' ),
					'company'  => __( 'Company', 'jobboardwp' ),
				)
			);

			/**
			 * Filters the jobs list filters' types.
			 *
			 * Note: The filters structure is 'filter_key' => 'filter_type'.
			 *
			 * @since 1.0
			 * @hook jb_jobs_directory_filter_types
			 *
			 * @param {array} $filter_types Jobs list filters' types.
			 *
			 * @return {array} Jobs list filters' types.
			 */
			$this->filter_types = apply_filters(
				'jb_jobs_directory_filter_types',
				array(
					'job_type' => 'select',
					'company'  => 'select',
				)
			);
		}


		/**
		 * Get values from DB for build filter values
		 *
		 * @param string $filter
		 *
		 * @return array|int|\WP_Error
		 *
		 * @since 1.0
		 */
		public function get_filter_options( $filter ) {
			global $wpdb;

			$values = array();

			switch ( $filter ) {
				case 'job_type':
					$values = get_terms(
						array(
							'taxonomy'   => 'jb-job-type',
							'hide_empty' => true,
							'fields'     => 'id=>name',
						)
					);

					break;
				case 'company':
					$values = $wpdb->get_col( "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = 'jb_company_name' AND meta_value != ''" );
					if ( ! empty( $values ) ) {
						$values = array_combine( $values, $values );
					}

					break;
				default:
					break;
			}

			return $values;
		}


		/**
		 * Render HTML for filter
		 *
		 * @param string $filter
		 *
		 * @return string $filter
		 *
		 * @since 1.0
		 */
		public function show_filter( $filter ) {
			if ( empty( $this->filter_types[ $filter ] ) ) {
				return '';
			}

			switch ( $this->filter_types[ $filter ] ) {
				default:
					/**
					 * Fires when showing custom filter type on the jobs list.
					 *
					 * Note: $type can be 'slider', 'text', etc. 'select' is used by default and has own handler.
					 *
					 * @since 1.0
					 * @hook jb_jobs_filter_type_{$type}
					 *
					 * @param {string} $filter Filter's field key.
					 */
					do_action( "jb_jobs_filter_type_{$this->filter_types[ $filter ]}", $filter );
					break;
				case 'select':
					// phpcs:ignore WordPress.Security.NonceVerification -- getting value from GET line
					$filter_from_url = ! empty( $_GET[ 'jb_' . $filter ] ) ? explode( '||', sanitize_text_field( $_GET[ 'jb_' . $filter ] ) ) : array();

					$options = $this->get_filter_options( $filter );
					if ( empty( $options ) ) {
						return '';
					}

					ob_start();
					?>

					<label class="screen-reader-text" for="jb_jobs_filter_<?php echo esc_attr( $filter ); ?>"><?php echo esc_html( $this->filters[ $filter ] ); ?></label>
					<select class="jb-s1" id="jb_jobs_filter_<?php echo esc_attr( $filter ); ?>"
							name="jb_jobs_filter_<?php echo esc_attr( $filter ); ?>"
							data-placeholder="<?php echo esc_attr( $this->filters[ $filter ] ); ?>"
							aria-label="<?php echo esc_attr( $this->filters[ $filter ] ); ?>">

						<option></option>

						<?php
						foreach ( $options as $k => $v ) {
							$opt = stripslashes( $v );
							?>

							<option value="<?php echo esc_attr( $opt ); ?>" data-value_label="<?php echo esc_attr( $v ); ?>"
								<?php disabled( ! empty( $filter_from_url ) && in_array( $opt, $filter_from_url, true ) ); ?>>
								<?php echo esc_html( $v ); ?>
							</option>

						<?php } ?>

					</select>

					<?php
					$filter = ob_get_clean();
					break;
			}

			return $filter;
		}

	}
}
