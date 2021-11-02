<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- todo changing email notifications keys
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped
echo JB()->common()->mail()->get_email_template( $wp_query->query_vars['jb_email_content']['slug'] );
