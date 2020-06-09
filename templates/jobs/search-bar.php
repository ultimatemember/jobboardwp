<?php if ( ! defined( 'ABSPATH' ) ) exit;

$search_from_url = ! empty( $_GET['jb-search'] ) ? stripslashes( $_GET['jb-search'] ) : '';
$search_from_url2 = ! empty( $_GET['jb-location-search'] ) ? stripslashes( $_GET['jb-location-search'] ) : ''; ?>

<div class="jb-jobs-header">

	<?php do_action( 'jb_jobs_head_before' ); ?>

	<div class="jb-jobs-header-row jb-jobs-search-row">
		<div class="jb-jobs-search-line">
			<label>
				<span><?php _e( 'Find Jobs:', 'jobboardwp' ); ?></span>
				<input type="search" class="jb-search-line" placeholder="<?php esc_attr_e( 'Job title, keywords, or company', 'jobboardwp' ) ?>" value="<?php echo esc_attr( $search_from_url ) ?>" aria-label="<?php esc_attr_e( 'Find Jobs by title', 'jobboardwp' ) ?>" speech />
			</label>
			<label>
				<span><?php _e( 'Find Jobs:', 'jobboardwp' ); ?></span>
				<input type="search" class="jb-search-location" placeholder="<?php esc_attr_e( 'City, State or Country', 'jobboardwp' ) ?>" value="<?php echo esc_attr( $search_from_url2 ) ?>" aria-label="<?php esc_attr_e( 'Find Jobs by location', 'jobboardwp' ) ?>" speech />
			</label>
			<input type="button" class="jb-do-search jb-button" value="<?php esc_attr_e( 'Find Jobs', 'jobboardwp' ); ?>" />
		</div>
	</div>
	<div class="jb-jobs-header-row jb-jobs-filters-row">
		<label>
			<input type="checkbox" class="jb-only-remote" value="1" />&nbsp;<?php esc_attr_e( 'Show only remote jobs', 'jobboardwp' ); ?>
		</label>
	</div>

	<?php do_action( 'jb_jobs_head_after' ); ?>
</div>