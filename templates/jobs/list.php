<?php if ( ! defined( 'ABSPATH' ) ) exit;

JB()->get_template_part( 'js/jobs-list', $jb_jobs_list ); ?>

<div class="jb-jobs-wrapper"></div>

<div class="jb-jobs-pagination-box">
	<a href="javascript:void(0);" class="jb-load-more-jobs">
		<?php echo esc_html( $jb_jobs_list['load-more-text'] ) ?>
	</a>
</div>