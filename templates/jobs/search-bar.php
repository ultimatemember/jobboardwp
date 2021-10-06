<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="jb-jobs-header">

	<?php do_action( 'jb_jobs_head_before', $jb_jobs_search_bar );

	if ( ! $jb_jobs_search_bar['hide-search'] || ! $jb_jobs_search_bar['hide-location-search'] ) { ?>

		<div class="jb-jobs-header-row jb-jobs-search-row">
			<div class="jb-jobs-search-line">

				<?php if ( ! $jb_jobs_search_bar['hide-search'] ) {
					$search_from_url = ! empty( $_GET['jb-search'] ) ? stripslashes( $_GET['jb-search'] ) : ''; ?>

					<label>
						<span><?php _e( 'Find Jobs:', 'jobboardwp' ); ?></span>
						<input type="search" class="jb-search-line" placeholder="<?php esc_attr_e( 'Job title, keywords, or company', 'jobboardwp' ) ?>" value="<?php echo esc_attr( $search_from_url ) ?>" aria-label="<?php esc_attr_e( 'Find Jobs by title', 'jobboardwp' ) ?>" speech />
					</label>

					<?php
				}

				if ( ! $jb_jobs_search_bar['hide-location-search'] ) {
					$search_from_url2 = ! empty( $_GET['jb-location-search'] ) ? stripslashes( $_GET['jb-location-search'] ) : '';

					$classes = ['jb-search-location'];
					$key = JB()->options()->get( 'googlemaps-api-key' );
					if ( ! empty( $key ) ) {
						$classes[] = 'jb-location-autocomplete';
					} ?>

					<label>
						<span><?php _e( 'Find Jobs:', 'jobboardwp' ); ?></span>
						<input type="search" class="<?php echo esc_attr( implode( ' ', $classes ) ) ?>" placeholder="<?php esc_attr_e( 'City, State or Country', 'jobboardwp' ) ?>" value="<?php echo esc_attr( $search_from_url2 ) ?>" aria-label="<?php esc_attr_e( 'Find Jobs by location', 'jobboardwp' ) ?>" speech />
						<?php if ( ! empty( $key ) ) {

							$search_location_city = ! empty( $_GET['jb-location-search-city'] ) ? stripslashes( $_GET['jb-location-search-city'] ) : '';
							$search_location_state_short = ! empty( $_GET['jb-location-search-state-short'] ) ? stripslashes( $_GET['jb-location-search-state-short'] ) : '';
							$search_location_state_long = ! empty( $_GET['jb-location-search-state-long'] ) ? stripslashes( $_GET['jb-location-search-state-long'] ) : '';
							$search_location_country_short = ! empty( $_GET['jb-location-search-country-short'] ) ? stripslashes( $_GET['jb-location-search-country-short'] ) : '';
							$search_location_country_long = ! empty( $_GET['jb-location-search-country-long'] ) ? stripslashes( $_GET['jb-location-search-country-long'] ) : ''; ?>

							<input type="hidden" class="jb-location-autocomplete-data jb-location-city" value="<?php echo esc_attr( $search_location_city ) ?>" />
							<input type="hidden" class="jb-location-autocomplete-data jb-location-state-short" value="<?php echo esc_attr( $search_location_state_short ) ?>" />
							<input type="hidden" class="jb-location-autocomplete-data jb-location-state-long" value="<?php echo esc_attr( $search_location_state_long ) ?>" />
							<input type="hidden" class="jb-location-autocomplete-data jb-location-country-short" value="<?php echo esc_attr( $search_location_country_short ) ?>" />
							<input type="hidden" class="jb-location-autocomplete-data jb-location-country-long" value="<?php echo esc_attr( $search_location_country_long ) ?>" />
						<?php } ?>
					</label>
				<?php } ?>

				<input type="button" class="jb-do-search jb-button" value="<?php esc_attr_e( 'Find Jobs', 'jobboardwp' ); ?>" />
			</div>
		</div>

	<?php }

	if ( ! $jb_jobs_search_bar['hide-filters'] ) {

		$is_remote = ! empty( $_GET['jb-is-remote'] );
		$job_type  = ! empty( $_GET['jb-job-type'] ) ? $_GET['jb-job-type'] : ''; ?>

		<div class="jb-jobs-header-row jb-jobs-filters-row">
			<label>
				<input type="checkbox" class="jb-only-remote" value="1" <?php checked( $is_remote ) ?> />&nbsp;<?php esc_attr_e( 'Show only remote jobs', 'jobboardwp' ); ?>
			</label>

			<?php $types = get_terms( [
				'taxonomy'   => 'jb-job-type',
				'hide_empty' => false,
			] );

			if ( ! empty( $types ) ) { ?>

				<label>
					<select class="jb-job-type-filter">
						<option value="" <?php selected( $job_type, '' ) ?>><?php esc_attr_e( 'Select job type', 'jobboardwp' ); ?></option>
						<?php foreach ( $types as $type ) { ?>
							<option value="<?php echo esc_attr( $type->term_id ) ?>" <?php selected( $job_type, $type->term_id ) ?>><?php echo esc_html( $type->name ) ?></option>
						<?php } ?>
					</select>
				</label>

			<?php }

			if ( JB()->options()->get( 'job-categories' ) ) {
				$job_category = ! empty( $_GET['jb-job-category'] ) ? $_GET['jb-job-category'] : '';
				$categories = get_terms( [
					'taxonomy'   => 'jb-job-category',
					'hide_empty' => 1,
				] );

				$categories = JB()->ajax()->jobs()->sort_terms_hierarchically( $categories );

				if ( ! empty( $categories ) ) { ?>
					<label>
						<select class="jb-job-category-filter">
							<option value="" <?php selected( $job_category, '' ) ?>><?php esc_attr_e( 'Select job category', 'jobboardwp' ); ?></option>
							<?php
								echo JB()->ajax()->jobs()->output_terms_hierarchically( $categories, $job_category );
							?>
						</select>
					</label>
				<?php }
			} ?>
		</div>

	<?php }

	do_action( 'jb_jobs_head_after', $jb_jobs_search_bar ); ?>
</div>
