<?php
/**
 * Template for the base wrapper email template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/emails/base_wrapper.php
 *
 * @version 1.2.6
 *
 * @var array $jb_emails_base_wrapper
 */
// phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- todo changing email notifications keys
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

JB()->get_template_part( JB()->get_email_template( $jb_emails_base_wrapper['slug'], false ), $jb_emails_base_wrapper, $jb_emails_base_wrapper['module'] );
