<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- todo changing email notifications keys
/**
 * Template for the job edited email template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/emails/job_edited.php
 *
 * @version 1.2.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

The job listing {job_title} has been edited.

{job_details}

View edited job: {view_job_url}
Approve job edit: {approve_job_url}
Edit job: {edit_job_url}
