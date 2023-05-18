<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- todo changing email notifications keys
/**
 * Template for the job submitted email template
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/emails/job_submitted.php
 *
 * @version 1.2.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

A new job has been submitted:

{job_details}

View job: {view_job_url}
Approve job: {approve_job_url}
Edit job: {edit_job_url}
