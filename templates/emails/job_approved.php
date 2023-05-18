<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- todo changing email notifications keys
/**
 * Template for the job approved email template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/emails/job_approved.php
 *
 * @version 1.2.6
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

Hello,

Your job listing {job_title} has been approved and is now live on {site_name}.

View job: {view_job_url}
