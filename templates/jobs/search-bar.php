<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis
// There are "undefined" variables here because they're defined in the code that includes this file as a template.

// phpcs:disable WordPress.Security.NonceVerification -- getting value from GET line
?>

<div class="jb-jobs-header">

	<?php /** @noinspection PhpUndefinedVariableInspection */ do_action( 'jb_jobs_head_before', $jb_jobs_search_bar ); ?>

	<?php if ( ! $jb_jobs_search_bar['hide-search'] || ! $jb_jobs_search_bar['hide-location-search'] ) { ?>

		<div class="jb-jobs-header-row jb-jobs-search-row">
			<div class="jb-jobs-search-line">

				<?php
				if ( ! $jb_jobs_search_bar['hide-search'] ) {
					$search_from_url = ! empty( $_GET['jb-search'] ) ? stripslashes( sanitize_text_field( $_GET['jb-search'] ) ) : '';
					?>

					<label>
						<span><?php esc_html_e( 'Find Jobs:', 'jobboardwp' ); ?></span>
						<input type="search" class="jb-search-line" placeholder="<?php esc_attr_e( 'Job title, keywords, or company', 'jobboardwp' ); ?>" value="<?php echo esc_attr( $search_from_url ); ?>" aria-label="<?php esc_attr_e( 'Find Jobs by title', 'jobboardwp' ); ?>" />
					</label>

					<?php
				}

				if ( ! $jb_jobs_search_bar['hide-location-search'] ) {
					$search_from_url2 = ! empty( $_GET['jb-location-search'] ) ? stripslashes( sanitize_text_field( $_GET['jb-location-search'] ) ) : '';

					$classes = array( 'jb-search-location' );
					$key     = JB()->options()->get( 'googlemaps-api-key' );
					if ( ! empty( $key ) ) {
						$classes[] = 'jb-location-autocomplete';
					}
					?>

					<label>
						<span><?php esc_html_e( 'Find Jobs:', 'jobboardwp' ); ?></span>
						<input type="search" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" placeholder="<?php esc_attr_e( 'City, State or Country', 'jobboardwp' ); ?>" value="<?php echo esc_attr( $search_from_url2 ); ?>" aria-label="<?php esc_attr_e( 'Find Jobs by location', 'jobboardwp' ); ?>" />
						<?php
						if ( ! empty( $key ) ) {

							$search_location_city          = ! empty( $_GET['jb-location-search-city'] ) ? stripslashes( sanitize_text_field( $_GET['jb-location-search-city'] ) ) : '';
							$search_location_state_short   = ! empty( $_GET['jb-location-search-state-short'] ) ? stripslashes( sanitize_text_field( $_GET['jb-location-search-state-short'] ) ) : '';
							$search_location_state_long    = ! empty( $_GET['jb-location-search-state-long'] ) ? stripslashes( sanitize_text_field( $_GET['jb-location-search-state-long'] ) ) : '';
							$search_location_country_short = ! empty( $_GET['jb-location-search-country-short'] ) ? stripslashes( sanitize_text_field( $_GET['jb-location-search-country-short'] ) ) : '';
							$search_location_country_long  = ! empty( $_GET['jb-location-search-country-long'] ) ? stripslashes( sanitize_text_field( $_GET['jb-location-search-country-long'] ) ) : '';
							?>

							<input type="hidden" class="jb-location-autocomplete-data jb-location-city" value="<?php echo esc_attr( $search_location_city ); ?>" />
							<input type="hidden" class="jb-location-autocomplete-data jb-location-state-short" value="<?php echo esc_attr( $search_location_state_short ); ?>" />
							<input type="hidden" class="jb-location-autocomplete-data jb-location-state-long" value="<?php echo esc_attr( $search_location_state_long ); ?>" />
							<input type="hidden" class="jb-location-autocomplete-data jb-location-country-short" value="<?php echo esc_attr( $search_location_country_short ); ?>" />
							<input type="hidden" class="jb-location-autocomplete-data jb-location-country-long" value="<?php echo esc_attr( $search_location_country_long ); ?>" />
						<?php } ?>
					</label>
				<?php } ?>

				<input type="button" class="jb-do-search jb-button" value="<?php esc_attr_e( 'Find Jobs', 'jobboardwp' ); ?>" />
			</div>
		</div>

		<?php
	}

	if ( ! $jb_jobs_search_bar['hide-filters'] ) {

		$is_remote = ! empty( $_GET['jb-is-remote'] );
		$job_type  = ! empty( $_GET['jb-job-type'] ) ? sanitize_text_field( $_GET['jb-job-type'] ) : '';
		?>

		<div class="jb-jobs-header-row jb-jobs-filters-row">
			<label>
				<input type="checkbox" class="jb-only-remote" value="1" <?php checked( $is_remote ); ?> />&nbsp;<?php esc_attr_e( 'Show only remote jobs', 'jobboardwp' ); ?>
			</label>

			<?php
			$types = get_terms(
				array(
					'taxonomy'   => 'jb-job-type',
					'hide_empty' => false,
				)
			);

			if ( ! empty( $types ) ) {
				?>

				<label>
					<select class="jb-job-type-filter">
						<option value="" <?php selected( $job_type, '' ); ?>><?php esc_attr_e( 'Select job type', 'jobboardwp' ); ?></option>
						<?php foreach ( $types as $t ) { ?>
							<option value="<?php echo esc_attr( $t->term_id ); ?>" <?php selected( $job_type, $t->term_id ); ?>><?php echo esc_html( $t->name ); ?></option>
						<?php } ?>
					</select>
				</label>

				<?php
			}

			if ( JB()->options()->get( 'job-categories' ) ) {
				if ( empty( $jb_jobs_search_bar['category'] ) ) {
					$job_category = ! empty( $_GET['jb-job-category'] ) ? sanitize_text_field( $_GET['jb-job-category'] ) : '';
					$categories   = get_terms(
						array(
							'taxonomy'   => 'jb-job-category',
							'hide_empty' => false,
						)
					);
					if ( ! empty( $categories ) ) {
						?>
						<label>
							<select class="jb-job-category-filter">
								<option value="" <?php selected( $job_category, '' ); ?>><?php esc_attr_e( 'Select job category', 'jobboardwp' ); ?></option>
								<?php foreach ( $categories as $category ) { ?>
									<option value="<?php echo esc_attr( $category->term_id ); ?>" <?php selected( $job_category, $category->term_id ); ?>><?php echo esc_html( $category->name ); ?></option>
								<?php } ?>
							</select>
						</label>
						<?php
					}
				}
			}
			?>
		</div>

		<?php
	}

	do_action( 'jb_jobs_head_after', $jb_jobs_search_bar );
	?>
</div>
