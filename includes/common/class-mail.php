<?php namespace jb\common;

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
		 * Mail constructor.
		 */
		public function __construct() {
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

			ob_start();

			JB()->get_template_part( 'emails/base_wrapper', $args );

			$message = ob_get_clean();

			$message = apply_filters( 'jb_email_template_content', $message, $slug, $args );

			// Convert tags in email template
			$message = $this->replace_placeholders( $message, $args );
			return $message;
		}


		/**
		 * Send Email function
		 *
		 * @param string $email
		 * @param null $template
		 * @param array $args
		 *
		 * @since 1.0
		 */
		public function send( $email, $template, $args = array() ) {
			if ( ! is_email( $email ) ) {
				return;
			}

			if ( empty( JB()->options()->get( $template . '_on' ) ) ) {
				return;
			}

			do_action( 'jb_before_email_notification_sending', $email, $template, $args );

			$attachments  = null;
			$content_type = apply_filters( 'jb_email_template_content_type', 'text/plain', $template, $args, $email );

			$headers  = 'From: ' . JB()->options()->get( 'mail_from' ) . ' <' . JB()->options()->get( 'mail_from_addr' ) . '>' . "\r\n";
			$headers .= "Content-Type: {$content_type}\r\n";

			$subject = apply_filters( 'jb_email_send_subject', JB()->options()->get( $template . '_sub' ), $template, $email );
			$subject = $this->replace_placeholders( $subject, $args );

			$message = $this->prepare_template( $template, $args );

			// Send mail
			wp_mail( $email, $subject, html_entity_decode( $message ), $headers, $attachments );

			do_action( 'jb_after_email_notification_sending', $email, $template, $args );
		}


		/**
		 * Get job details for placeholder
		 *
		 * @param \WP_Post $job
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
				function( $item ) {
					return '{' . $item . '}';
				},
				array_keys( $args )
			);

			$tags_replace = array_values( $args );

			$tags[] = '{site_url}';
			$tags[] = '{site_name}';
			$tags   = apply_filters( 'jb-mail-placeholders', $tags, $args ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

			$tags_replace[] = get_bloginfo( 'url' );
			$tags_replace[] = get_bloginfo( 'blogname' );
			$tags_replace   = apply_filters( 'jb-mail-replace-placeholders', $tags_replace, $args ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

			$content = str_replace( $tags, $tags_replace, $content );
			return $content;
		}


		/**
		 * Get admin e-mails
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

			$emails_array = array_unique( $emails_array );
			return $emails_array;
		}
	}
}
