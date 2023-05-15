<?php
/**
 * Template for the job expiration reminder email template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/emails/job_expiration_reminder.php
 *
 * @version 1.2.6
 */
// phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- todo changing email notifications keys
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

Hello,

Your job {job_title} will expire in {job_expiration_days} days on {site_name}.

View job: {view_job_url}
