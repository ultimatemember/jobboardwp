<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- todo changing email notifications keys
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

echo wp_kses( JB()->common()->mail()->get_email_template( $wp_query->query_vars['jb_email_content']['slug'] ), JB()->get_allowed_html() );
