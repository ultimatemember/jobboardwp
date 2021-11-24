<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- todo changing email notifications keys
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

JB()->get_template_part( JB()->get_email_template( $jb_emails_base_wrapper['slug'], false ) );
