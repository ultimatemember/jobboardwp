<?php
namespace jb\common;

use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\common\Mail' ) ) {

	/**
	 * Class Mail
	 *
	 * @package jb\common
	 */
	class Mail {

		/**
		 * @var bool
		 */
		private $sending = false;

		/**
		 * @return bool
		 */
		public function is_sending() {
			return $this->sending;
		}

		/**
		 * Prepare email template to send
		 *
		 * @param string $slug
		 * @param array $args
		 * @return string
		 *
		 * @since 1.0
		 */
		public function prepare_template( $slug, $args = array() ) {
			$args['slug'] = $slug;

			$emails         = JB()->config()->get( 'email_notifications' );
			$module         = ! empty( $emails[ $slug ]['module'] ) ? $emails[ $slug ]['module'] : '';
			$args['module'] = $module;

			ob_start();

			JB()->get_template_part( 'emails/base_wrapper', $args );

			$message = ob_get_clean();

			/**
			 * Filters the email notification content before sending it.
			 *
			 * Note: placeholders like {site_name}, etc. will be replaced later.
			 *
			 * @since 1.0
			 * @hook jb_email_template_content
			 *
			 * @param {string} $message Email notification content.
			 * @param {string} $slug    Email notification key.
			 * @param {array}  $args    Arguments passed to the function. There can be data to replace placeholders.
			 *
			 * @return {string} Email notification content.
			 */
			$message = apply_filters( 'jb_email_template_content', $message, $slug, $args );

			// Convert tags in email template
			return $this->replace_placeholders( $message, $args );
		}

		/**
		 * Send Email function
		 *
		 * @param string $email
		 * @param null $template
		 * @param array $args
		 * @param array $attachments
		 *
		 * @since 1.0
		 * @since 1.2.2 Added $attachments argument.
		 */
		public function send( $email, $template, $args = array(), $attachments = array() ) {
			if ( ! is_email( $email ) ) {
				return;
			}

			if ( empty( JB()->options()->get( $template . '_on' ) ) ) {
				return;
			}

			$this->sending = true;

			/**
			 * Filters the email notification placeholders.
			 *
			 * @since 1.2.8
			 * @hook jb_email_sending_placeholders
			 *
			 * @param {array}  $args     Passed into the `send()` function arguments. There can be data to replace placeholders.
			 * @param {string} $email    Recipient email address.
			 * @param {string} $template Email notification key.
			 *
			 * @return {array} Passed into the `send()` function arguments.
			 */
			$args = apply_filters( 'jb_email_sending_placeholders', $args, $email, $template );

			add_filter(
				'jb_template_locations_base_user_id_for_locale',
				static function ( $user_id ) use ( $email ) {
					$user = get_user_by( 'email', $email );
					if ( false !== $user && isset( $user->ID ) ) {
						$user_id = $user->ID;
					}

					return $user_id;
				}
			);

			/**
			 * Fires before sending email notification via JobBoardWP plugin.
			 *
			 * @since 1.1.0
			 * @hook jb_before_email_notification_sending
			 *
			 * @param {string} $email    Recipient email address.
			 * @param {string} $template Email template key.
			 * @param {array}  $args     Passed into the `send()` function arguments. There can be data to replace placeholders.
			 */
			do_action( 'jb_before_email_notification_sending', $email, $template, $args );

			/**
			 * Filters the email notification content type that is used in email header.
			 *
			 * @since 1.0
			 * @hook jb_email_template_content_type
			 *
			 * @param {string} $content_type Content type string. It's "text/plain" by default.
			 * @param {string} $template     Email notification key.
			 * @param {array}  $args         Arguments passed to the function. There can be data to replace placeholders.
			 * @param {string} $email        Recipient's email address.
			 *
			 * @return {string} Content type string.
			 */
			$content_type = apply_filters( 'jb_email_template_content_type', 'text/plain', $template, $args, $email );

			$headers  = 'From: ' . JB()->options()->get( 'mail_from' ) . ' <' . JB()->options()->get( 'mail_from_addr' ) . '>' . "\r\n";
			$headers .= "Content-Type: {$content_type}\r\n";

			/**
			 * Filters the email notification subject before sending it.
			 *
			 * Note: This filter is internally used for getting translated subject before sending email notification.
			 *
			 * @since 1.0
			 * @hook jb_email_send_subject
			 *
			 * @param {string} $subject  Email notification subject.
			 * @param {string} $template Email notification key.
			 * @param {string} $email    Recipient's email address.
			 *
			 * @return {string} Email notification subject.
			 */
			$subject = apply_filters( 'jb_email_send_subject', JB()->options()->get( $template . '_sub' ), $template, $email );
			$subject = $this->replace_placeholders( $subject, $args );

			$message = $this->prepare_template( $template, $args );

			// Send mail
			wp_mail( $email, $subject, html_entity_decode( $message ), $headers, $attachments );

			/**
			 * Fires after sending email notification via JobBoardWP plugin.
			 *
			 * @since 1.1.0
			 * @hook jb_after_email_notification_sending
			 *
			 * @param {string} $email    Recipient email address.
			 * @param {string} $template Email template key.
			 * @param {array}  $args     Passed into the `send()` function arguments. There can be data to replace placeholders.
			 */
			do_action( 'jb_after_email_notification_sending', $email, $template, $args );

			$this->sending = false;
		}

		/**
		 * Get job details for placeholder
		 *
		 * @param WP_Post $job
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_job_details( $job ) {
			global $post;

			$company_data = JB()->common()->job()->get_company_data( $job->ID );

			$temp_post = $post;
			$post      = $job; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- is needed for getting correct job content

			ob_start();
			the_content();
			$post_content = ob_get_clean();
			$post         = $temp_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- back to the original $post

			$details = __( 'Job Title:', 'jobboardwp' ) . ' ' . get_the_title( $job ) . "\n\r" .
			__( 'Description:', 'jobboardwp' ) . ' ' . $post_content . "\n\r" .
			__( 'Posted by:', 'jobboardwp' ) . ' ' . JB()->common()->job()->get_job_author( $job->ID ) . "\n\r" .
			__( 'Application Contact:', 'jobboardwp' ) . ' ' . get_post_meta( $job->ID, 'jb-application-contact', true ) . "\n\r" .
			__( 'Location:', 'jobboardwp' ) . ' ' . JB()->common()->job()->get_location( $job->ID ) . "\n\r" .
			__( 'Company name:', 'jobboardwp' ) . ' ' . $company_data['name'] . "\n\r" .
			__( 'Company website:', 'jobboardwp' ) . ' ' . $company_data['website'] . "\n\r" .
			__( 'Company tagline:', 'jobboardwp' ) . ' ' . $company_data['tagline'];

			/**
			 * Filters the job details string.
			 *
			 * @since 1.2.2
			 * @hook jb_get_mail_job_details
			 *
			 * @param {string}  $details Job details that replaces the placeholder {job_details} in email templates.
			 * @param {WP_Post} $job     Job CPT class (\WP_Post) instance.
			 *
			 * @return {string} Job details string.
			 */
			$details = apply_filters( 'jb_get_mail_job_details', $details, $job );
			return $details;
		}

		/**
		 * Replace placeholders
		 *
		 * @param string $content
		 * @param array $args
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function replace_placeholders( $content, $args ) {
			$tags = array_map(
				function ( $item ) {
					return '{' . $item . '}';
				},
				array_keys( $args )
			);

			$tags_replace = array_values( $args );

			$tags[] = '{site_url}';
			$tags[] = '{site_name}';

			/**
			 * Filters the email notification placeholders tags. You may add your custom placeholders tags here.
			 *
			 * @since 1.0
			 * @hook jb_mail_placeholders
			 *
			 * @param {array} $tags Email notification placeholders list.
			 * @param {array} $args Arguments passed to the function. There can be data to replace placeholders.
			 *
			 * @return {array} Email notification placeholders list.
			 */
			$tags = apply_filters( 'jb_mail_placeholders', $tags, $args );

			$tags_replace[] = get_bloginfo( 'url' );
			$tags_replace[] = get_bloginfo( 'blogname' );

			/**
			 * Filters the email notification replace placeholders tags. You may add your custom placeholders tags here.
			 *
			 * @since 1.0
			 * @hook jb_mail_replace_placeholders
			 *
			 * @param {array} $tags_replace Email notification replace placeholders list.
			 * @param {array} $args         Arguments passed to the function. There can be data to replace placeholders.
			 *
			 * @return {array} Email notification replace placeholders list.
			 */
			$tags_replace = apply_filters( 'jb_mail_replace_placeholders', $tags_replace, $args );

			return str_replace( $tags, $tags_replace, $content );
		}

		/**
		 * Get admin emails
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function multi_admin_email() {
			$emails = JB()->options()->get( 'admin_email' );

			$emails_array = explode( ',', $emails );
			if ( ! empty( $emails_array ) ) {
				$emails_array = array_map( 'trim', $emails_array );
			}

			return array_unique( $emails_array );
		}
	}
}
