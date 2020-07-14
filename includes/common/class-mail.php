<?php
namespace jb\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\common\Mail' ) ) {


	/**
	 * Class Mail
	 *
	 * @package jb\common
	 */
	class Mail {


		/**
		 * @var array
		 */
		var $path_by_slug = [];


		/**
		 * Mail constructor.
		 */
		function __construct() {
			add_action( 'init', [ $this, 'init_paths' ] );
		}


		/**
		 * Init paths for email notifications
		 */
		function init_paths() {
			$this->path_by_slug = apply_filters( 'jb_email_templates_extends', $this->path_by_slug );
		}


		/**
		 * Check blog ID on multisite, return '' if single site
		 *
		 * @return string
		 */
		function get_blog_id() {
			$blog_id = '';
			if ( is_multisite() ) {
				$blog_id = DIRECTORY_SEPARATOR . get_current_blog_id();
			}

			return $blog_id;
		}


		/**
		 * Locate a template and return the path for inclusion.
		 *
		 * @param string $template_name
		 * @return string
		 */
		function locate_template( $template_name ) {
			// check if there is template at theme folder
			$blog_id = $this->get_blog_id();

			//get template file from current blog ID folder
			$template = locate_template( [
				trailingslashit( 'jobboardwp' . DIRECTORY_SEPARATOR . 'emails' . $blog_id ) . $template_name . '.php'
			] );

			//if there isn't template at theme folder for current blog ID get template file from theme folder
			if ( is_multisite() && ! $template ) {
				$template = locate_template( [
					trailingslashit( 'jobboardwp' . DIRECTORY_SEPARATOR . 'emails' ) . $template_name . '.php'
				] );
			}

			//if there isn't template at theme folder get template file from plugin dir
			if ( ! $template ) {
				$path = ! empty( $this->path_by_slug[ $template_name ] ) ? $this->path_by_slug[ $template_name ] : jb_path . 'templates' . DIRECTORY_SEPARATOR . 'emails';
				$template = trailingslashit( $path ) . $template_name . '.php';
			}

			return apply_filters( 'jb_locate_email_template', $template, $template_name );
		}


		/**
		 * @param $slug
		 * @param $args
		 *
		 * @return bool|string
		 */
		function get_template( $slug, $args = [] ) {
			$located = $this->locate_template( $slug );

			$located = apply_filters( 'jb_email_template_path', $located, $slug, $args );

			if ( ! file_exists( $located ) ) {
				_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
				return false;
			}

			ob_start();

			do_action( 'jb_before_email_template_part', $slug, $located, $args );

			include( $located );

			do_action( 'jb_after_email_template_part', $slug, $located, $args );

			return ob_get_clean();
		}


		/**
		 * Method returns expected path for template
		 *
		 * @access public
		 *
		 * @param string $location
		 * @param string $template_name
		 *
		 * @return string
		 */
		function get_template_file( $location, $template_name ) {
			$template_name_file = $this->get_template_filename( $template_name );

			$template_path = '';
			switch( $location ) {
				case 'theme':
					//save email template in blog ID folder if we use multisite
					$blog_id = $this->get_blog_id();

					$template_path = trailingslashit( get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'jobboardwp' . DIRECTORY_SEPARATOR . 'emails' . $blog_id ). $template_name_file . '.php';
					break;
				case 'plugin':
					$path = ! empty( $this->path_by_slug[ $template_name ] ) ? $this->path_by_slug[ $template_name ] : jb_path . 'templates' . DIRECTORY_SEPARATOR . 'emails';
					$template_path = trailingslashit( $path ) . $template_name . '.php';
					break;
			}

			return $template_path;
		}


		/**
		 * @param string $template_name
		 *
		 * @return string
		 */
		function get_template_filename( $template_name ) {
			return apply_filters( 'jb_change_email_template_file', $template_name );
		}


		/**
		 * Ajax copy template to the theme
		 *
		 * @param string $template
		 * @return bool
		 */
		function copy_template( $template ) {

			$in_theme = $this->template_in_theme( $template );
			if ( $in_theme ) {
				return false;
			}

			$plugin_template_path = $this->get_template_file( 'plugin', $template );
			$theme_template_path = $this->get_template_file( 'theme', $template );

			$temp_path = str_replace( trailingslashit( get_stylesheet_directory() ), '', $theme_template_path );
			$temp_path = str_replace( '/', DIRECTORY_SEPARATOR, $temp_path );
			$folders = explode( DIRECTORY_SEPARATOR, $temp_path );
			$folders = array_splice( $folders, 0, count( $folders ) - 1 );
			$cur_folder = '';
			$theme_dir = trailingslashit( get_stylesheet_directory() );

			foreach ( $folders as $folder ) {
				$prev_dir = $cur_folder;
				$cur_folder .= $folder . DIRECTORY_SEPARATOR;
				if ( ! is_dir( $theme_dir . $cur_folder ) && wp_is_writable( $theme_dir . $prev_dir ) ) {
					mkdir( $theme_dir . $cur_folder, 0777 );
				}
			}

			if ( file_exists( $plugin_template_path ) && copy( $plugin_template_path, $theme_template_path ) ) {
				return true;
			} else {
				return false;
			}
		}


		/**
		 * Locate a template and return the path for inclusion.
		 *
		 * @access public
		 * @param string $template_name
		 * @return string
		 */
		function template_in_theme( $template_name ) {
			$template_name_file = $this->get_template_filename( $template_name );

			$blog_id = $this->get_blog_id();

			// check if there is template at theme blog ID folder
			$template = locate_template( [
				trailingslashit( 'jobboardwp' . DIRECTORY_SEPARATOR . 'emails' . $blog_id ) . $template_name_file . '.php'
			] );

			// Return what we found.
			return ! $template ? false : true;
		}


		/**
		 * @param $slug
		 * @param $args
		 * @return bool|string
		 */
		function get_email_template( $slug, $args = [] ) {
			$located = $this->locate_template( $slug );

			if ( ! file_exists( $located ) ) {
				_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
				return false;
			}

			ob_start();

			include( $located );

			return ob_get_clean();
		}


		/**
		 * Prepare email template to send
		 *
		 * @param $slug
		 * @param $args
		 * @return mixed|string
		 */
		function prepare_template( $slug, $args = [] ) {
			global $wp_query;

			$args['slug'] = $slug;
			$wp_query->query_vars['jb_email_content'] = $args;

			ob_start();

			JB()->get_template_part( 'emails/base_wrapper' );

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
		 */
		function send( $email, $template, $args = [] ) {
			if ( ! is_email( $email ) ) {
				return;
			}

			if ( JB()->options()->get( $template . '_on' ) != 1 ) {
				return;
			}

			$attachments = null;
			//$content_type = apply_filters( 'jb_email_template_content_type', 'text/html', $template, $args, $email );
			$content_type = apply_filters( 'jb_email_template_content_type', 'text/plain', $template, $args, $email );

			$headers = 'From: '. JB()->options()->get( 'mail_from' ) .' <'. JB()->options()->get( 'mail_from_addr' ) .'>' . "\r\n";
			$headers .= "Content-Type: {$content_type}\r\n";

			$subject = apply_filters( 'jb_email_send_subject', JB()->options()->get( $template . '_sub' ), $template, $email );
			$subject = $this->replace_placeholders( $subject, $args );

			$message = $this->prepare_template( $template, $args );

			// Send mail
			wp_mail( $email, $subject, html_entity_decode( $message ), $headers, $attachments );
		}


		/**
		 * @param \WP_Post $job
		 *
		 * @return string
		 */
		function get_job_details( $job ) {
			$company_data = JB()->common()->job()->get_company_data( $job->ID );

			$details = __( 'Job Title:', 'jobboardwp' ) . ' ' . $job->post_title . "\n\r" .
			__( 'Description:', 'jobboardwp' ) . ' ' . $job->post_content . "\n\r" .
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
		 * @param $content
		 * @param $args
		 *
		 * @return mixed
		 */
		function replace_placeholders( $content, $args ) {
			$tags = array_map( function( $item ) {
				return '{' . $item . '}';
			}, array_keys( $args ) );

			$tags_replace = array_values( $args );

			$tags[] = '{site_url}';
			$tags[] = '{site_name}';
			$tags_replace[] = get_bloginfo( 'url' );
			$tags_replace[] = get_bloginfo( 'blogname' );


//			{view_job_url}
//			{approve_job_url}
//			{trash_job_url}
//			{job_title}
//			{view_job_url}

			$tags = apply_filters( 'jb-mail-placeholders', $tags, $args );
			$tags_replace = apply_filters( 'jb-mail-replace-placeholders', $tags_replace, $args );

			$content = str_replace( $tags, $tags_replace, $content );
			return $content;
		}


		/**
		 * Get admin e-mails
		 *
		 * @return array
		 */
		function multi_admin_email() {
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