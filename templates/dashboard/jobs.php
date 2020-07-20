<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! is_user_logged_in() ) { ?>

	<p>
		<?php
		// translators: %s: login link
		printf( __( '<a href="%s">Sign in</a> to view your job listings.', 'jobboardwp' ), wp_login_url( get_permalink() ) ); ?>
	</p>

<?php } else {

	JB()->get_template_part( 'js/jobs-dashboard', $jb_dashboard_jobs ); ?>

	<div id="jb-job-dashboard" class="jb">

		<?php JB()->get_template_part( 'ajax-overlay', $jb_dashboard_jobs );  ?>

		<div class="jb-responsive jb-ui-m jb-ui-l jb-ui-xl">
			<div class="jb-job-dashboard-heading">
				<?php foreach ( $jb_dashboard_jobs['columns'] as $key => $title ) { ?>
					<span class="jb-job-col-<?php echo esc_attr( $key ) ?>"><?php echo $title ?></span>
				<?php } ?>
			</div>
		</div>

		<div id="jb-job-dashboard-rows"></div>
	</div>

	<?php JB()->frontend()->templates()->dropdown_menu( '.jb-job-actions-dropdown', 'click' );

}