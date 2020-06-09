<?php if ( ! defined( 'ABSPATH' ) ) exit;

$not_searched = false;
$current_page = ( ! empty( $_GET['jb-page'] ) && is_numeric( $_GET['jb-page'] ) ) ? (int) $_GET['jb-page'] : 1; ?>

<div class="jb jb-jobs" data-base-post="<?php echo esc_attr( $post->ID ) ?>"
	 data-searched="<?php echo $not_searched ? '0' : '1'; ?>"
	 data-page="<?php echo esc_attr( $current_page ) ?>"
	 data-no-jobs="<?php esc_attr_e( 'No Jobs','jobboardwp' ) ?>"
	 data-no-jobs-search="<?php esc_attr_e( 'No Jobs found','jobboardwp' ) ?>">

	<div class="jb-jobs-overlay">
		<div class="jb-ajax-loading"></div>
	</div>

	<?php JB()->get_template_part( 'jobs/search-bar' );

	JB()->get_template_part( 'jobs/list' );

	do_action( 'jb_jobs_footer' ); ?>
</div>