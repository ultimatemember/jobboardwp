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
		 * @param string $key
		 *
		 * @return mixed
		 */
		function get( $key ) {
			if ( empty( $this->$key ) ) {
				call_user_func( [ &$this, 'init_' . $key ] );
			}
			return apply_filters( 'jb_config_get', $this->$key, $key );
		}


		/**
		 *
		 */
		function init_defaults() {
			$this->defaults = apply_filters( 'jb_settings_defaults', [
				'job-slug'                  => 'job',
				'job-type-slug'             => 'job-type',
				'job-category-slug'         => 'job-category',
				'job-categories'            => true,
				'job-template'              => '',
				'jobs-list-pagination'      => 10,
				'jobs-list-no-logo'         => false,
				'jobs-dashboard-pagination' => 10,
				'account-required'          => false,
				'account-creation'          => true,
				'account-username-generate' => true,
				'account-password-email'    => true,
				'account-role'              => 'jb_employer',
				'job-moderation'            => true,
				'pending-job-editing'       => true,
				'published-job-editing'     => '1',
				'job-duration'              => 30,
				'application-method'        => '',

				'disable-styles'            => false,
				'disable-fa-styles'         => false,


				'uninstall-delete-settings' => false,
			] );
		}


		/**
		 * Initialize JB custom roles list
		 */
		function init_custom_roles() {
			$this->custom_roles = apply_filters( 'jb_roles_list', [
				'jb_employer'   => __( 'Employer', 'jobboardwp' ),
			] );
		}


		/**
		 * Initialize JB roles capabilities list
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
		 *
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
		 *
		 */
		function init_permalink_options() {
			$this->permalink_options = apply_filters( 'jb_permalink_options', [
				'job-slug',
				'job-type-slug',
				'job-category-slug',
			] );
		}


		/**
		 *
		 */
		function init_core_pages() {
			$this->core_pages = apply_filters( 'jb_core_pages', [
				'jobs'                  => [
					'title'     => __( 'Jobs', 'jobboardwp' ),
					'content'   => '[jb_jobs /]'
				],
				'job-post'              => [
					'title'     => __( 'Add Job', 'jobboardwp' ),
					'content'   => '[jb_post_job /]',
				],
				'jobs-dashboard'        => [
					'title'     => __( 'Jobs Dashboard', 'jobboardwp' ),
					'content'   => '[jb_jobs_dashboard /]',
				],
				'employer-dashboard'    => [
					'title'     => __( 'Employer Dashboard', 'jobboardwp' ),
					'content'   => '[jb_employer_dashboard /]',
				],
			] );
		}


		/**
		 *
		 */
		function init_email_notifications() {
			$this->email_notifications = apply_filters( 'jb_email_notifications', [
				'new_job'   => [
					'key'               => 'new_job',
					'title'             => __( 'New job posted', 'jobboardwp' ),
					'description'       => __( 'Whether to send the admin an email when new job is posted on website', 'jobboardwp' ),
					'recipient'         => 'admin',
					'default_active'    => true
				],
			] );
		}
	}
}