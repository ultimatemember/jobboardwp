<?php namespace jb;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


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
		public $defaults;


		/**
		 * @var
		 */
		public $custom_roles;


		/**
		 * @var
		 */
		public $all_caps;


		/**
		 * @var
		 */
		public $capabilities_map;


		/**
		 * @var
		 */
		public $permalink_options;


		/**
		 * @var
		 */
		public $predefined_pages;


		/**
		 * @var
		 */
		public $email_notifications;


		/**
		 * Config constructor.
		 */
		public function __construct() {
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
		public function get( $key ) {
			if ( empty( $this->$key ) ) {
				call_user_func( array( &$this, 'init_' . $key ) );
			}
			/**
			 * Filters the variable before getting it from the config.
			 *
			 * @since 1.1.0
			 * @hook jb_config_get
			 *
			 * @param {mixed}  $data The predefined data in config.
			 * @param {string} $key  The predefined data key. E.g. 'predefined_pages'.
			 *
			 * @return {mixed} Prepared config data.
			 */
			return apply_filters( 'jb_config_get', $this->$key, $key );
		}


		/**
		 * Init plugin defaults
		 *
		 * @since 1.0
		 */
		public function init_defaults() {
			$this->defaults = array(
				'job-slug'                       => 'job',
				'job-type-slug'                  => 'job-type',
				'job-category-slug'              => 'job-category',
				'job-categories'                 => true,
				'job-template'                   => '',
				'job-dateformat'                 => 'default',
				'job-breadcrumbs'                => false,
				'googlemaps-api-key'             => '',
				'disable-structured-data'        => false,
				'jobs-list-pagination'           => 10,
				'jobs-list-no-logo'              => false,
				'jobs-list-hide-filled'          => false,
				'jobs-list-hide-expired'         => false,
				'jobs-list-hide-search'          => false,
				'jobs-list-hide-location-search' => false,
				'jobs-list-hide-filters'         => false,
				'jobs-list-hide-job-types'       => false,
				'jobs-dashboard-pagination'      => 10,
				'account-required'               => false,
				'account-creation'               => true,
				'account-username-generate'      => true,
				'account-password-email'         => true,
				'full-name-required'             => true,
				'your-details-section'           => false,
				'account-role'                   => 'jb_employer',
				'job-moderation'                 => true,
				'pending-job-editing'            => true,
				'published-job-editing'          => 1,
				'individual-job-duration'        => false,
				'job-duration'                   => 30,
				'job-expiration-reminder'        => false,
				'job-expiration-reminder-time'   => '',
				'required-job-type'              => true,
				'application-method'             => '',
				'job-submitted-notice'           => __( 'Thank you for submitting your job. It will be appear on the website once approved.', 'jobboardwp' ),
				'disable-styles'                 => false,
				'disable-fa-styles'              => false,
				'admin_email'                    => get_bloginfo( 'admin_email' ),
				'mail_from'                      => get_bloginfo( 'name' ),
				'mail_from_addr'                 => get_bloginfo( 'admin_email' ),
				'uninstall-delete-settings'      => false,
			);

			foreach ( $this->get( 'email_notifications' ) as $key => $notification ) {
				$this->defaults[ $key . '_on' ]  = ! empty( $notification['default_active'] );
				$this->defaults[ $key . '_sub' ] = $notification['subject'];
			}

			foreach ( $this->get( 'predefined_pages' ) as $slug => $array ) {
				$this->defaults[ JB()->options()->get_predefined_page_option_key( $slug ) ] = '';
			}
		}


		/**
		 * Initialize JB custom roles list
		 *
		 * @since 1.0
		 */
		public function init_custom_roles() {
			$this->custom_roles = array(
				'jb_employer' => __( 'Employer', 'jobboardwp' ),
			);
		}


		/**
		 * Initialize JB roles capabilities list
		 *
		 * @since 1.0
		 */
		public function init_capabilities_map() {
			$this->capabilities_map = array(
				'administrator' => array(
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
				),
				'jb_employer'   => array(
					'edit_jb-job',
					'read_jb-job',
					'delete_jb-job',
				),
			);
		}


		/**
		 * Initialize JB custom capabilities
		 *
		 * @since 1.0
		 */
		public function init_all_caps() {
			$this->all_caps = array(
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
			);
		}


		/**
		 * Initialize JB permalink options
		 *
		 * @since 1.0
		 */
		public function init_permalink_options() {
			$this->permalink_options = array(
				'job-slug',
				'job-type-slug',
				'job-category-slug',
			);
		}


		/**
		 * Initialize JB predefined pages
		 *
		 * @since 1.0
		 */
		public function init_predefined_pages() {
			$this->predefined_pages = array(
				'jobs'           => array(
					'title'   => __( 'Jobs', 'jobboardwp' ),
					'content' => '[jb_jobs /]',
				),
				'job-post'       => array(
					'title'   => __( 'Post Job', 'jobboardwp' ),
					'content' => '[jb_post_job /]',
				),
				'jobs-dashboard' => array(
					'title'   => __( 'Jobs Dashboard', 'jobboardwp' ),
					'content' => '[jb_jobs_dashboard /]',
				),
			);
		}


		/**
		 * Initialize JB email notifications
		 *
		 * @since 1.0
		 */
		public function init_email_notifications() {
			$this->email_notifications = array(
				'job_submitted'           => array(
					'key'            => 'job_submitted',
					'title'          => __( 'Job submitted', 'jobboardwp' ),
					'subject'        => __( 'New Job Submission - {site_name}', 'jobboardwp' ),
					'description'    => __( 'Whether to send the admin an email when new job is posted on website.', 'jobboardwp' ),
					'recipient'      => 'admin',
					'default_active' => true,
				),
				'job_approved'            => array(
					'key'            => 'job_approved',
					'title'          => __( 'Job listing approved', 'jobboardwp' ),
					'subject'        => __( 'Job listing is now live - {site_name}', 'jobboardwp' ),
					'description'    => __( 'Whether to send the job\'s author an email when job is approved.', 'jobboardwp' ),
					'recipient'      => 'user',
					'default_active' => true,
				),
				'job_edited'              => array(
					'key'            => 'job_edited',
					'title'          => __( 'Job has been edited', 'jobboardwp' ),
					'subject'        => __( 'A job listing has been edited - {site_name}', 'jobboardwp' ),
					'description'    => __( 'Whether to send the admin an email when new job is edited on website.', 'jobboardwp' ),
					'recipient'      => 'admin',
					'default_active' => true,
				),
				'job_expiration_reminder' => array(
					'key'            => 'job_expiration_reminder',
					'title'          => __( 'Job expiration reminder', 'jobboardwp' ),
					'subject'        => __( 'Your job will expire in {job_expiration_days} days - {site_name}', 'jobboardwp' ),
					'description'    => __( 'Whether to send the job\'s author an email before job is expired.', 'jobboardwp' ),
					'recipient'      => 'user',
					'default_active' => true,
				),
			);
		}
	}
}
