<?php
/**
 * Template for the single job archive
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/job-archive.php
 *
 * Page: "Job's Archive"
 *
 * @version 1.2.3
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<header class="page-header alignwide">
	<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
</header>

<div class="entry-content <?php echo esc_attr( get_queried_object()->taxonomy . '-wrapper' ); ?>">
	<?php
	$tax_id = get_queried_object_id();
	$attrs  = '';
	if ( 'jb-job-type' === get_queried_object()->taxonomy ) {
		$shortcode = '[jb_jobs type="' . $tax_id . '"]';
	} elseif ( 'jb-job-category' === get_queried_object()->taxonomy ) {
		$shortcode = '[jb_jobs category="' . $tax_id . '"]';
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode content output, early escaped
	echo apply_shortcodes( wp_filter_content_tags( prepend_attachment( shortcode_unautop( wpautop( wptexturize( do_blocks( $shortcode ) ) ) ) ) ) );
	?>
</div>

<?php get_footer(); ?>
