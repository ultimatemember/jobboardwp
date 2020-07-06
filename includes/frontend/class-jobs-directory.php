<?php namespace jb\frontend;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\frontend\Jobs_Directory' ) ) {


	/**
	 * Class Jobs_Directory
	 *
	 * @package jb\frontend
	 */
	class Jobs_Directory {


		/**
		 * @var array
		 */
		var $filters = [];


		/**
		 * @var array
		 */
		var $filter_types = [];


		/**
		 * Jobs_Directory constructor.
		 */
		function __construct() {
			add_action( 'init', [ $this, 'init_variables' ] );
			if ( empty( $this->filter_types ) || empty( $this->filters ) ) {
				$this->init_variables();
			}
		}


		/**
		 *
		 */
		function init_variables() {
			$this->filters = apply_filters( 'jb_jobs_directory_filters', [
				'job_type'  => __( 'Job Type', 'jobboardwp' ),
				'company'   => __( 'Company', 'jobboardwp' ),
			] );

			$this->filter_types = apply_filters( 'jb_jobs_directory_filter_types', [
				'job_type'  => 'select',
				'company'   => 'select',
			] );
		}


		/**
		 * @param $filter
		 *
		 * @return array|int|\WP_Error
		 */
		function get_db_values( $filter ) {
			global $wpdb;

			$values = [];

			switch ( $filter ) {
				case 'job_type':

					$values = get_terms( [
						'taxonomy'      => 'jb-job-type',
						'hide_empty'    => true,
						'fields'        => 'ids',
					] );

					break;
				case 'company':

					$values = $wpdb->get_col(
					"SELECT DISTINCT meta_value
						FROM $wpdb->postmeta
						WHERE meta_key = 'jb_company_name' AND 
							  meta_value != ''"
					);

					break;
				default:
					break;
			}

			return $values;
		}


		/**
		 * @param $filter
		 *
		 * @return array|int|\WP_Error
		 */
		function get_filter_options( $filter ) {
			global $wpdb;

			$values = [];

			switch ( $filter ) {
				case 'job_type':

					$values = get_terms( [
						'taxonomy'      => 'jb-job-type',
						'hide_empty'    => true,
						'fields'        => 'id=>name',
					] );

					break;
				case 'company':

					$values = $wpdb->get_col(
					"SELECT DISTINCT meta_value
						FROM $wpdb->postmeta
						WHERE meta_key = 'jb_company_name' AND 
							  meta_value != ''"
					);

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
		 */
		function show_filter( $filter ) {

			if ( empty( $this->filter_types[ $filter ] ) ) {
				return '';
			}

			switch ( $this->filter_types[ $filter ] ) {
				default: {

					do_action( "jb_jobs_filter_type_{$this->filter_types[ $filter ]}", $filter );

					break;
				}
				case 'select': {
					// getting value from GET line
					$filter_from_url = ! empty( $_GET[ 'jb_' . $filter ] ) ? explode( '||', sanitize_text_field( $_GET[ 'jb_' . $filter ] ) ) : [];

					$options = $this->get_filter_options( $filter );
					if ( empty( $options ) ) {
						return '';
					}

					ob_start(); ?>

					<select class="jb-s1" id="jb_jobs_filter_<?php echo esc_attr( $filter ); ?>"
							name="jb_jobs_filter_<?php echo esc_attr( $filter ); ?>"
							data-placeholder="<?php echo esc_attr( $this->filters[ $filter ] ); ?>"
							aria-label="<?php echo esc_attr( $this->filters[ $filter ] ); ?>">

						<option></option>

						<?php foreach ( $options as $k => $v ) {

							$opt = stripslashes( $v ); ?>

							<option value="<?php echo esc_attr( $opt ); ?>" data-value_label="<?php echo esc_attr( $v ); ?>"
								<?php disabled( ! empty( $filter_from_url ) && in_array( $opt, $filter_from_url ) ); ?>>
								<?php echo $v; ?>
							</option>

						<?php } ?>

					</select>

					<?php $filter = ob_get_clean();
					break;
				}
			}


			return $filter;
		}


	}
}