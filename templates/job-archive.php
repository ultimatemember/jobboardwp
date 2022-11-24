<?php get_header(); ?>
<header class="page-header alignwide">
	<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
</header>
<div class="jb-job-categories-wrapper">
	<?php
	$tax_id = get_queried_object_id();
	$attrs = '';
	if ( 'jb-job-type' === get_queried_object()->taxonomy ) {
		$attrs = 'type="' . $tax_id . '"';
	} elseif ( 'jb-job-category' === get_queried_object()->taxonomy ) {
		$attrs = 'category="' . $tax_id . '"';
	}

	echo apply_shortcodes( "[jb_jobs " . $attrs . "]" );
	?>
</div>
<?php get_footer(); ?>
