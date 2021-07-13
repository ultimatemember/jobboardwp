<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

JB()->get_template_part( 'js/jobs-category-list' ); ?>
<div class="jb-jobs-category-list-wrapper">
	<?php JB()->get_template_part( 'ajax-overlay' );  ?>
	<div class="category-list-head">
		<div class="jb-row-data">
			<div class="jb-category-title"><?php echo esc_html__( 'Category', 'jobboardwp' ); ?></div>
			<div class="jb-category-count"><?php echo esc_html__( 'Jobs', 'jobboardwp' ); ?></div>
		</div>
	</div>
	<div class="category-list">

	</div>
</div>
