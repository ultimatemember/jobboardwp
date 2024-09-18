<?php
namespace jb;

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
		 * @var array
		 */
		public $custom_roles;

		/**
		 * @var array
		 */
		public $all_caps;

		/**
		 * @var array
		 */
		public $capabilities_map;

		/**
		 * @var array
		 */
		public $permalink_options;

		/**
		 * @var array
		 */
		public $predefined_pages;

		/**
		 * @var array
		 */
		public $email_notifications;

		/**
		 * @var array
		 */
		public $currencies;

		/**
		 * @since 1.2.2
		 *
		 * @var array
		 */
		public $modules = array();

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
		 * Init default modules
		 */
		public function init_modules() {
			$this->modules = array();

			foreach ( $this->modules as $slug => &$data ) {
				$data['key']  = $slug;
				$data['path'] = JB_PATH . 'modules' . DIRECTORY_SEPARATOR . $slug;
				$data['url']  = JB_URL . "modules/{$slug}/";
			}
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
				'job-salary'                     => false,
				'job-salary-currency'            => 'USD',
				'job-salary-currency-pos'        => 'left',
				'job-categories'                 => true,
				'job-template'                   => '',
				'job-archive-template'           => '',
				'job-dateformat'                 => 'default',
				'job-breadcrumbs'                => false,
				'googlemaps-api-key'             => '',
				'disable-structured-data'        => false,
				'disable-company-logo-cache'     => false,
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
				'required-job-salary'            => false,
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
				'jobs'               => array(
					'title'   => __( 'Jobs', 'jobboardwp' ),
					'content' => '[jb_jobs /]',
				),
				'job-post'           => array(
					'title'   => __( 'Post Job', 'jobboardwp' ),
					'content' => '[jb_post_job /]',
				),
				'jobs-dashboard'     => array(
					'title'   => __( 'Jobs Dashboard', 'jobboardwp' ),
					'content' => '[jb_jobs_dashboard /]',
				),
				'jb-company-details' => array(
					'title'   => __( 'Company Details', 'jobboardwp' ),
					'content' => '[jb_company_details /]',
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

		/**
		 * Init currency data.
		 *
		 * @since 1.2.6
		 */
		public function init_currencies() {
			$this->currencies = array(
				'AED' => array(
					'label'  => __( 'United Arab Emirates dirham', 'jobboardwp' ),
					'symbol' => '&#x62f;.&#x625;',
				),
				'AFN' => array(
					'label'  => __( 'Afghan afghani', 'jobboardwp' ),
					'symbol' => '&#x60b;',
				),
				'ALL' => array(
					'label'  => __( 'Albanian lek', 'jobboardwp' ),
					'symbol' => 'L',
				),
				'AMD' => array(
					'label'  => __( 'Armenian dram', 'jobboardwp' ),
					'symbol' => 'AMD',
				),
				'ANG' => array(
					'label'  => __( 'Netherlands Antillean guilder', 'jobboardwp' ),
					'symbol' => '&fnof;',
				),
				'AOA' => array(
					'label'  => __( 'Angolan kwanza', 'jobboardwp' ),
					'symbol' => 'Kz',
				),
				'ARS' => array(
					'label'  => __( 'Argentine peso', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'AUD' => array(
					'label'  => __( 'Australian dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'AWG' => array(
					'label'  => __( 'Aruban florin', 'jobboardwp' ),
					'symbol' => 'Afl.',
				),
				'AZN' => array(
					'label'  => __( 'Azerbaijani manat', 'jobboardwp' ),
					'symbol' => '&#8380;',
				),
				'BAM' => array(
					'label'  => __( 'Bosnia and Herzegovina convertible mark', 'jobboardwp' ),
					'symbol' => 'KM',
				),
				'BBD' => array(
					'label'  => __( 'Barbadian dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'BDT' => array(
					'label'  => __( 'Bangladeshi taka', 'jobboardwp' ),
					'symbol' => '&#2547;&nbsp;',
				),
				'BGN' => array(
					'label'  => __( 'Bulgarian lev', 'jobboardwp' ),
					'symbol' => '&#1083;&#1074;.',
				),
				'BHD' => array(
					'label'  => __( 'Bahraini dinar', 'jobboardwp' ),
					'symbol' => '.&#x62f;.&#x628;',
				),
				'BIF' => array(
					'label'  => __( 'Burundian franc', 'jobboardwp' ),
					'symbol' => 'Fr',
				),
				'BMD' => array(
					'label'  => __( 'Bermudian dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'BND' => array(
					'label'  => __( 'Brunei dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'BOB' => array(
					'label'  => __( 'Bolivian boliviano', 'jobboardwp' ),
					'symbol' => 'Bs.',
				),
				'BRL' => array(
					'label'  => __( 'Brazilian real', 'jobboardwp' ),
					'symbol' => '&#82;&#36;',
				),
				'BSD' => array(
					'label'  => __( 'Bahamian dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'BTC' => array(
					'label'  => __( 'Bitcoin', 'jobboardwp' ),
					'symbol' => '&#3647;',
				),
				'BTN' => array(
					'label'  => __( 'Bhutanese ngultrum', 'jobboardwp' ),
					'symbol' => 'Nu.',
				),
				'BWP' => array(
					'label'  => __( 'Botswana pula', 'jobboardwp' ),
					'symbol' => 'P',
				),
				'BYR' => array(
					'label'  => __( 'Belarusian ruble (old)', 'jobboardwp' ),
					'symbol' => 'Br',
				),
				'BYN' => array(
					'label'  => __( 'Belarusian ruble', 'jobboardwp' ),
					'symbol' => 'Br',
				),
				'BZD' => array(
					'label'  => __( 'Belize dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'CAD' => array(
					'label'  => __( 'Canadian dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'CDF' => array(
					'label'  => __( 'Congolese franc', 'jobboardwp' ),
					'symbol' => 'Fr',
				),
				'CHF' => array(
					'label'  => __( 'Swiss franc', 'jobboardwp' ),
					'symbol' => '&#67;&#72;&#70;',
				),
				'CLP' => array(
					'label'  => __( 'Chilean peso', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'CNY' => array(
					'label'  => __( 'Chinese yuan', 'jobboardwp' ),
					'symbol' => '&yen;',
				),
				'COP' => array(
					'label'  => __( 'Colombian peso', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'CRC' => array(
					'label'  => __( 'Costa Rican col&oacute;n', 'jobboardwp' ),
					'symbol' => '&#x20a1;',
				),
				'CUC' => array(
					'label'  => __( 'Cuban convertible peso', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'CUP' => array(
					'label'  => __( 'Cuban peso', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'CVE' => array(
					'label'  => __( 'Cape Verdean escudo', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'CZK' => array(
					'label'  => __( 'Czech koruna', 'jobboardwp' ),
					'symbol' => '&#75;&#269;',
				),
				'DJF' => array(
					'label'  => __( 'Djiboutian franc', 'jobboardwp' ),
					'symbol' => 'Fr',
				),
				'DKK' => array(
					'label'  => __( 'Danish krone', 'jobboardwp' ),
					'symbol' => 'kr.',
				),
				'DOP' => array(
					'label'  => __( 'Dominican peso', 'jobboardwp' ),
					'symbol' => 'RD&#36;',
				),
				'DZD' => array(
					'label'  => __( 'Algerian dinar', 'jobboardwp' ),
					'symbol' => '&#x62f;.&#x62c;',
				),
				'EGP' => array(
					'label'  => __( 'Egyptian pound', 'jobboardwp' ),
					'symbol' => 'EGP',
				),
				'ERN' => array(
					'label'  => __( 'Eritrean nakfa', 'jobboardwp' ),
					'symbol' => 'Nfk',
				),
				'ETB' => array(
					'label'  => __( 'Ethiopian birr', 'jobboardwp' ),
					'symbol' => 'Br',
				),
				'EUR' => array(
					'label'  => __( 'Euro', 'jobboardwp' ),
					'symbol' => '&euro;',
				),
				'FJD' => array(
					'label'  => __( 'Fijian dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'FKP' => array(
					'label'  => __( 'Falkland Islands pound', 'jobboardwp' ),
					'symbol' => '&pound;',
				),
				'GBP' => array(
					'label'  => __( 'Pound sterling', 'jobboardwp' ),
					'symbol' => '&pound;',
				),
				'GEL' => array(
					'label'  => __( 'Georgian lari', 'jobboardwp' ),
					'symbol' => '&#x20be;',
				),
				'GGP' => array(
					'label'  => __( 'Guernsey pound', 'jobboardwp' ),
					'symbol' => '&pound;',
				),
				'GHS' => array(
					'label'  => __( 'Ghana cedi', 'jobboardwp' ),
					'symbol' => '&#x20b5;',
				),
				'GIP' => array(
					'label'  => __( 'Gibraltar pound', 'jobboardwp' ),
					'symbol' => '&pound;',
				),
				'GMD' => array(
					'label'  => __( 'Gambian dalasi', 'jobboardwp' ),
					'symbol' => 'D',
				),
				'GNF' => array(
					'label'  => __( 'Guinean franc', 'jobboardwp' ),
					'symbol' => 'Fr',
				),
				'GTQ' => array(
					'label'  => __( 'Guatemalan quetzal', 'jobboardwp' ),
					'symbol' => 'Q',
				),
				'GYD' => array(
					'label'  => __( 'Guyanese dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'HKD' => array(
					'label'  => __( 'Hong Kong dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'HNL' => array(
					'label'  => __( 'Honduran lempira', 'jobboardwp' ),
					'symbol' => 'L',
				),
				'HRK' => array(
					'label'  => __( 'Croatian kuna', 'jobboardwp' ),
					'symbol' => 'kn',
				),
				'HTG' => array(
					'label'  => __( 'Haitian gourde', 'jobboardwp' ),
					'symbol' => 'G',
				),
				'HUF' => array(
					'label'  => __( 'Hungarian forint', 'jobboardwp' ),
					'symbol' => '&#70;&#116;',
				),
				'IDR' => array(
					'label'  => __( 'Indonesian rupiah', 'jobboardwp' ),
					'symbol' => 'Rp',
				),
				'ILS' => array(
					'label'  => __( 'Israeli new shekel', 'jobboardwp' ),
					'symbol' => '&#8362;',
				),
				'IMP' => array(
					'label'  => __( 'Manx pound', 'jobboardwp' ),
					'symbol' => '&pound;',
				),
				'INR' => array(
					'label'  => __( 'Indian rupee', 'jobboardwp' ),
					'symbol' => '&#8377;',
				),
				'IQD' => array(
					'label'  => __( 'Iraqi dinar', 'jobboardwp' ),
					'symbol' => '&#x62f;.&#x639;',
				),
				'IRR' => array(
					'label'  => __( 'Iranian rial', 'jobboardwp' ),
					'symbol' => '&#xfdfc;',
				),
				'IRT' => array(
					'label'  => __( 'Iranian toman', 'jobboardwp' ),
					'symbol' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
				),
				'ISK' => array(
					'label'  => __( 'Icelandic kr&oacute;na', 'jobboardwp' ),
					'symbol' => 'kr.',
				),
				'JEP' => array(
					'label'  => __( 'Jersey pound', 'jobboardwp' ),
					'symbol' => '&pound;',
				),
				'JMD' => array(
					'label'  => __( 'Jamaican dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'JOD' => array(
					'label'  => __( 'Jordanian dinar', 'jobboardwp' ),
					'symbol' => '&#x62f;.&#x627;',
				),
				'JPY' => array(
					'label'  => __( 'Japanese yen', 'jobboardwp' ),
					'symbol' => '&yen;',
				),
				'KES' => array(
					'label'  => __( 'Kenyan shilling', 'jobboardwp' ),
					'symbol' => 'KSh',
				),
				'KGS' => array(
					'label'  => __( 'Kyrgyzstani som', 'jobboardwp' ),
					'symbol' => '&#x441;&#x43e;&#x43c;',
				),
				'KHR' => array(
					'label'  => __( 'Cambodian riel', 'jobboardwp' ),
					'symbol' => '&#x17db;',
				),
				'KMF' => array(
					'label'  => __( 'Comorian franc', 'jobboardwp' ),
					'symbol' => 'Fr',
				),
				'KPW' => array(
					'label'  => __( 'North Korean won', 'jobboardwp' ),
					'symbol' => '&#x20a9;',
				),
				'KRW' => array(
					'label'  => __( 'South Korean won', 'jobboardwp' ),
					'symbol' => '&#8361;',
				),
				'KWD' => array(
					'label'  => __( 'Kuwaiti dinar', 'jobboardwp' ),
					'symbol' => '&#x62f;.&#x643;',
				),
				'KYD' => array(
					'label'  => __( 'Cayman Islands dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'KZT' => array(
					'label'  => __( 'Kazakhstani tenge', 'jobboardwp' ),
					'symbol' => '&#8376;',
				),
				'LAK' => array(
					'label'  => __( 'Lao kip', 'jobboardwp' ),
					'symbol' => '&#8365;',
				),
				'LBP' => array(
					'label'  => __( 'Lebanese pound', 'jobboardwp' ),
					'symbol' => '&#x644;.&#x644;',
				),
				'LKR' => array(
					'label'  => __( 'Sri Lankan rupee', 'jobboardwp' ),
					'symbol' => '&#xdbb;&#xdd4;',
				),
				'LRD' => array(
					'label'  => __( 'Liberian dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'LSL' => array(
					'label'  => __( 'Lesotho loti', 'jobboardwp' ),
					'symbol' => 'L',
				),
				'LYD' => array(
					'label'  => __( 'Libyan dinar', 'jobboardwp' ),
					'symbol' => '&#x62f;.&#x644;',
				),
				'MAD' => array(
					'label'  => __( 'Moroccan dirham', 'jobboardwp' ),
					'symbol' => '&#x62f;.&#x645;.',
				),
				'MDL' => array(
					'label'  => __( 'Moldovan leu', 'jobboardwp' ),
					'symbol' => 'MDL',
				),
				'MGA' => array(
					'label'  => __( 'Malagasy ariary', 'jobboardwp' ),
					'symbol' => 'Ar',
				),
				'MKD' => array(
					'label'  => __( 'Macedonian denar', 'jobboardwp' ),
					'symbol' => '&#x434;&#x435;&#x43d;',
				),
				'MMK' => array(
					'label'  => __( 'Burmese kyat', 'jobboardwp' ),
					'symbol' => 'Ks',
				),
				'MNT' => array(
					'label'  => __( 'Mongolian t&ouml;gr&ouml;g', 'jobboardwp' ),
					'symbol' => '&#x20ae;',
				),
				'MOP' => array(
					'label'  => __( 'Macanese pataca', 'jobboardwp' ),
					'symbol' => 'P',
				),
				'MRU' => array(
					'label'  => __( 'Mauritanian ouguiya', 'jobboardwp' ),
					'symbol' => 'UM',
				),
				'MUR' => array(
					'label'  => __( 'Mauritian rupee', 'jobboardwp' ),
					'symbol' => '&#x20a8;',
				),
				'MVR' => array(
					'label'  => __( 'Maldivian rufiyaa', 'jobboardwp' ),
					'symbol' => '.&#x783;',
				),
				'MWK' => array(
					'label'  => __( 'Malawian kwacha', 'jobboardwp' ),
					'symbol' => 'MK',
				),
				'MXN' => array(
					'label'  => __( 'Mexican peso', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'MYR' => array(
					'label'  => __( 'Malaysian ringgit', 'jobboardwp' ),
					'symbol' => '&#82;&#77;',
				),
				'MZN' => array(
					'label'  => __( 'Mozambican metical', 'jobboardwp' ),
					'symbol' => 'MT',
				),
				'NAD' => array(
					'label'  => __( 'Namibian dollar', 'jobboardwp' ),
					'symbol' => 'N&#36;',
				),
				'NGN' => array(
					'label'  => __( 'Nigerian naira', 'jobboardwp' ),
					'symbol' => '&#8358;',
				),
				'NIO' => array(
					'label'  => __( 'Nicaraguan c&oacute;rdoba', 'jobboardwp' ),
					'symbol' => 'C&#36;',
				),
				'NOK' => array(
					'label'  => __( 'Norwegian krone', 'jobboardwp' ),
					'symbol' => '&#107;&#114;',
				),
				'NPR' => array(
					'label'  => __( 'Nepalese rupee', 'jobboardwp' ),
					'symbol' => '&#8360;',
				),
				'NZD' => array(
					'label'  => __( 'New Zealand dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'OMR' => array(
					'label'  => __( 'Omani rial', 'jobboardwp' ),
					'symbol' => '&#x631;.&#x639;.',
				),
				'PAB' => array(
					'label'  => __( 'Panamanian balboa', 'jobboardwp' ),
					'symbol' => 'B/.',
				),
				'PEN' => array(
					'label'  => __( 'Sol', 'jobboardwp' ),
					'symbol' => 'S/',
				),
				'PGK' => array(
					'label'  => __( 'Papua New Guinean kina', 'jobboardwp' ),
					'symbol' => 'K',
				),
				'PHP' => array(
					'label'  => __( 'Philippine peso', 'jobboardwp' ),
					'symbol' => '&#8369;',
				),
				'PKR' => array(
					'label'  => __( 'Pakistani rupee', 'jobboardwp' ),
					'symbol' => '&#8360;',
				),
				'PLN' => array(
					'label'  => __( 'Polish z&#x142;oty', 'jobboardwp' ),
					'symbol' => '&#122;&#322;',
				),
				'PRB' => array(
					'label'  => __( 'Transnistrian ruble', 'jobboardwp' ),
					'symbol' => '&#x440;.',
				),
				'PYG' => array(
					'label'  => __( 'Paraguayan guaran&iacute;', 'jobboardwp' ),
					'symbol' => '&#8370;',
				),
				'QAR' => array(
					'label'  => __( 'Qatari riyal', 'jobboardwp' ),
					'symbol' => '&#x631;.&#x642;',
				),
				'RON' => array(
					'label'  => __( 'Romanian leu', 'jobboardwp' ),
					'symbol' => 'lei',
				),
				'RSD' => array(
					'label'  => __( 'Serbian dinar', 'jobboardwp' ),
					'symbol' => '&#1088;&#1089;&#1076;',
				),
				'RUB' => array(
					'label'  => __( 'Russian ruble', 'jobboardwp' ),
					'symbol' => '&#8381;',
				),
				'RWF' => array(
					'label'  => __( 'Rwandan franc', 'jobboardwp' ),
					'symbol' => 'Fr',
				),
				'SAR' => array(
					'label'  => __( 'Saudi riyal', 'jobboardwp' ),
					'symbol' => '&#x631;.&#x633;',
				),
				'SBD' => array(
					'label'  => __( 'Solomon Islands dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),
				'SCR' => array(
					'label'  => __( 'Seychellois rupee', 'jobboardwp' ),
					'symbol' => '&#x20a8;',
				),
				'SDG' => array(
					'label'  => __( 'Sudanese pound', 'jobboardwp' ),
					'symbol' => '&#x62c;.&#x633;.',
				),
				'SEK' => array(
					'label'  => __( 'Swedish krona', 'jobboardwp' ),
					'symbol' => '&#107;&#114;',
				),
				'SGD' => array(
					'label'  => __( 'Singapore dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),

				'SHP' => array(
					'label'  => __( 'Saint Helena pound', 'jobboardwp' ),
					'symbol' => '&pound;',
				),

				'SLL' => array(
					'label'  => __( 'Sierra Leonean leone', 'jobboardwp' ),
					'symbol' => 'Le',
				),

				'SOS' => array(
					'label'  => __( 'Somali shilling', 'jobboardwp' ),
					'symbol' => 'Sh',
				),

				'SRD' => array(
					'label'  => __( 'Surinamese dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),

				'SSP' => array(
					'label'  => __( 'South Sudanese pound', 'jobboardwp' ),
					'symbol' => '&pound;',
				),

				'STN' => array(
					'label'  => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'jobboardwp' ),
					'symbol' => 'Db',
				),

				'SYP' => array(
					'label'  => __( 'Syrian pound', 'jobboardwp' ),
					'symbol' => '&#x644;.&#x633;',
				),

				'SZL' => array(
					'label'  => __( 'Swazi lilangeni', 'jobboardwp' ),
					'symbol' => 'E',
				),

				'THB' => array(
					'label'  => __( 'Thai baht', 'jobboardwp' ),
					'symbol' => '&#3647;',
				),

				'TJS' => array(
					'label'  => __( 'Tajikistani somoni', 'jobboardwp' ),
					'symbol' => '&#x405;&#x41c;',
				),

				'TMT' => array(
					'label'  => __( 'Turkmenistan manat', 'jobboardwp' ),
					'symbol' => 'm',
				),

				'TND' => array(
					'label'  => __( 'Tunisian dinar', 'jobboardwp' ),
					'symbol' => '&#x62f;.&#x62a;',
				),

				'TOP' => array(
					'label'  => __( 'Tongan pa&#x2bb;anga', 'jobboardwp' ),
					'symbol' => 'T&#36;',
				),

				'TRY' => array(
					'label'  => __( 'Turkish lira', 'jobboardwp' ),
					'symbol' => '&#8378;',
				),

				'TTD' => array(
					'label'  => __( 'Trinidad and Tobago dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),

				'TWD' => array(
					'label'  => __( 'New Taiwan dollar', 'jobboardwp' ),
					'symbol' => '&#78;&#84;&#36;',
				),

				'TZS' => array(
					'label'  => __( 'Tanzanian shilling', 'jobboardwp' ),
					'symbol' => 'Sh',
				),

				'UAH' => array(
					'label'  => __( 'Ukrainian hryvnia', 'jobboardwp' ),
					'symbol' => '&#8372;',
				),

				'UGX' => array(
					'label'  => __( 'Ugandan shilling', 'jobboardwp' ),
					'symbol' => 'UGX',
				),

				'USD' => array(
					'label'  => __( 'United States (US) dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),

				'UYU' => array(
					'label'  => __( 'Uruguayan peso', 'jobboardwp' ),
					'symbol' => '&#36;',
				),

				'UZS' => array(
					'label'  => __( 'Uzbekistani som', 'jobboardwp' ),
					'symbol' => 'UZS',
				),

				'VEF' => array(
					'label'  => __( 'Venezuelan bol&iacute;var', 'jobboardwp' ),
					'symbol' => 'Bs F',
				),

				'VES' => array(
					'label'  => __( 'Bol&iacute;var soberano', 'jobboardwp' ),
					'symbol' => 'Bs.S',
				),

				'VND' => array(
					'label'  => __( 'Vietnamese &#x111;&#x1ed3;ng', 'jobboardwp' ),
					'symbol' => '&#8363;',
				),

				'VUV' => array(
					'label'  => __( 'Vanuatu vatu', 'jobboardwp' ),
					'symbol' => 'Vt',
				),

				'WST' => array(
					'label'  => __( 'Samoan t&#x101;l&#x101;', 'jobboardwp' ),
					'symbol' => 'T',
				),

				'XAF' => array(
					'label'  => __( 'Central African CFA franc', 'jobboardwp' ),
					'symbol' => 'CFA',
				),

				'XCD' => array(
					'label'  => __( 'East Caribbean dollar', 'jobboardwp' ),
					'symbol' => '&#36;',
				),

				'XOF' => array(
					'label'  => __( 'West African CFA franc', 'jobboardwp' ),
					'symbol' => 'CFA',
				),

				'XPF' => array(
					'label'  => __( 'CFP franc', 'jobboardwp' ),
					'symbol' => 'Fr',
				),

				'YER' => array(
					'label'  => __( 'Yemeni rial', 'jobboardwp' ),
					'symbol' => '&#xfdfc;',
				),

				'ZAR' => array(
					'label'  => __( 'South African rand', 'jobboardwp' ),
					'symbol' => '&#82;',
				),

				'ZMW' => array(
					'label'  => __( 'Zambian kwacha', 'jobboardwp' ),
					'symbol' => 'ZK',
				),
			);
		}
	}
}
