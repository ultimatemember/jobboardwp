<?php
namespace jb;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\Config' ) ) {


	/**
	 * Class Config
	 *
	 * @package jb
	 */
	class Config {


		/**
		 * @var array
		 */
		var $defaults;


		/**
		 * @var
		 */
		var $custom_roles;


		var $all_caps;


		var $capabilities_map;


		var $permalink_options;


		var $core_pages;


		var $email_notifications;


		/**
		 * Config constructor.
		 */
		function __construct() {
		}


		/**
		 * Get variable from config
		 *
		 * @param string $key
		 *
		 * @return mixed
		 *
		 * @since 1.0
		 */
		function get( $key ) {
			if ( empty( $this->$key ) ) {
				call_user_func( [ &$this, 'init_' . $key ] );
			}
			return apply_filters( 'jb_config_get', $this->$key, $key );
		}


		/**
		 * Init plugin defaults
		 *
		 * @since 1.0
		 */
		function init_defaults() {
			$this->defaults = apply_filters( 'jb_settings_defaults', [
				'job-slug'                          => 'job',
				'job-type-slug'                     => 'job-type',
				'job-category-slug'                 => 'job-category',
				'job-categories'                    => true,
				'job-breadcrumbs'                   => false,
				'job-template'                      => '',
				'job-dateformat'                    => 'default',
				'googlemaps-api-key'                => '',
				'jobs-list-pagination'              => 10,
				'jobs-list-no-logo'                 => false,
				'jobs-list-hide-filled'             => false,
				'jobs-list-hide-expired'            => false,
				'jobs-list-hide-search'             => false,
				'jobs-list-hide-location-search'    => false,
				'jobs-list-hide-filters'            => false,
				'jobs-list-hide-job-types'          => false,
				'jobs-dashboard-pagination'         => 10,
				'account-required'                  => false,
				'account-creation'                  => true,
				'account-username-generate'         => true,
				'account-password-email'            => true,
				'full-name-required'                => true,
				'your-details-section'                => '0',
				'account-role'                      => 'jb_employer',
				'job-moderation'                    => true,
				'pending-job-editing'               => true,
				'published-job-editing'             => '1',
				'job-duration'                      => 30,
				'required-job-type'                 => true,
				'application-method'                => '',
				'job-submitted-notice'              => __( 'Thank you for submitting your job. It will be appear on the website once approved.', 'jobboardwp' ),

				'disable-styles'                    => false,
				'disable-fa-styles'                 => false,

				'admin_email'                       => get_bloginfo( 'admin_email' ),
				'mail_from'                         => get_bloginfo( 'name' ),
				'mail_from_addr'                    => get_bloginfo( 'admin_email' ),

				'uninstall-delete-settings'         => false,
			] );

			foreach ( $this->get( 'email_notifications' ) as $key => $notification ) {
				$this->defaults[ $key . '_on' ] = ! empty( $notification['default_active'] );
				$this->defaults[ $key . '_sub' ] = $notification['subject'];
			}
		}


		/**
		 * Initialize JB custom roles list
		 *
		 * @since 1.0
		 */
		function init_custom_roles() {
			$this->custom_roles = apply_filters( 'jb_roles_list', [
				'jb_employer'   => __( 'Employer', 'jobboardwp' ),
			] );
		}


		/**
		 * Initialize JB roles capabilities list
		 *
		 * @since 1.0
		 */
		function init_capabilities_map() {
			$this->capabilities_map = apply_filters( 'jb_roles_capabilities_list', [
				'administrator'     => [
					'edit_jb-job',
					'read_jb-job',
					'delete_jb-job',
					'edit_jb-jobs',
					'edit_others_jb-jobs',
					'publish_jb-jobs',
					'read_private_jb-jobs',
					'delete_jb-jobs',
					'delete_private_jb-jobs',
					'delete_published_jb-jobs',
					'delete_others_jb-jobs',
					'edit_private_jb-jobs',
					'edit_published_jb-jobs',
					'create_jb-jobs',

					'manage_jb-job-types',
					'edit_jb-job-types',
					'delete_jb-job-types',
					'edit_jb-job-types',

					'manage_jb-job-categories',
					'edit_jb-job-categories',
					'delete_jb-job-categories',
					'edit_jb-job-categories',
				],

				'jb_employer'      => [
					'edit_jb-job',
					'read_jb-job',
					'delete_jb-job',
				],
			] );
		}


		/**
		 * Initialize JB custom capabilities
		 *
		 * @since 1.0
		 */
		function init_all_caps() {
			$this->all_caps = apply_filters( 'jb_all_caps_list', [
				'edit_jb-job',
				'read_jb-job',
				'delete_jb-job',
				'edit_jb-jobs',
				'edit_others_jb-jobs',
				'publish_jb-jobs',
				'read_private_jb-jobs',
				'delete_jb-jobs',
				'delete_private_jb-jobs',
				'delete_published_jb-jobs',
				'delete_others_jb-jobs',
				'edit_private_jb-jobs',
				'edit_published_jb-jobs',
				'create_jb-jobs',

				'manage_jb-job-types',
				'edit_jb-job-types',
				'delete_jb-job-types',
				'edit_jb-job-types',

				'manage_jb-job-categories',
				'edit_jb-job-categories',
				'delete_jb-job-categories',
				'edit_jb-job-categories',
			] );
		}


		/**
		 * Initialize JB permalink options
		 *
		 * @since 1.0
		 */
		function init_permalink_options() {
			$this->permalink_options = apply_filters( 'jb_permalink_options', [
				'job-slug',
				'job-type-slug',
				'job-category-slug',
			] );
		}


		/**
		 * Initialize JB core pages
		 *
		 * @since 1.0
		 */
		function init_core_pages() {
			$this->core_pages = apply_filters( 'jb_core_pages', [
				'jobs'              => [
					'title'     => __( 'Jobs', 'jobboardwp' ),
					'content'   => '[jb_jobs /]'
				],
				'job-post'          => [
					'title'     => __( 'Post Job', 'jobboardwp' ),
					'content'   => '[jb_post_job /]',
				],
				'jobs-dashboard'    => [
					'title'     => __( 'Jobs Dashboard', 'jobboardwp' ),
					'content'   => '[jb_jobs_dashboard /]',
				],
			] );
		}


		/**
		 * Initialize JB email notifications
		 *
		 * @since 1.0
		 */
		function init_email_notifications() {
			$this->email_notifications = apply_filters( 'jb_email_notifications', [
				'job_submitted' => [
					'key'               => 'job_submitted',
					'title'             => __( 'Job submitted', 'jobboardwp' ),
					'subject'           => __( 'New Job Submission - {site_name}', 'jobboardwp' ),
					'description'       => __( 'Whether to send the admin an email when new job is posted on website.', 'jobboardwp' ),
					'recipient'         => 'admin',
					'default_active'    => true,
				],
				'job_approved'  => [
					'key'               => 'job_approved',
					'title'             => __( 'Job listing approved', 'jobboardwp' ),
					'subject'           => __( 'Job listing is now live - {site_name}', 'jobboardwp' ),
					'description'       => __( 'Whether to send the job\'s author an email when job is approved.', 'jobboardwp' ),
					'recipient'         => 'user',
					'default_active'    => true,
				],
				'job_edited'    => [
					'key'               => 'job_edited',
					'title'             => __( 'Job has been edited', 'jobboardwp' ),
					'subject'           => __( 'A job listing has been edited - {site_name}', 'jobboardwp' ),
					'description'       => __( 'Whether to send the admin an email when new job is edited on website.', 'jobboardwp' ),
					'recipient'         => 'admin',
					'default_active'    => true,
				],
			] );
		}
	}
}