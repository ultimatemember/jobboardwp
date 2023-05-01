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
				'job-categories'                 => true,
				'job-template'                   => '',
				'job-archive-template'           => '',
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

		/**
		 * Init currency titles.
		 *
		 * @since 1.2.6
		 */
		public function init_currencies() {
			$currencies = array(
				'AED' => __( 'United Arab Emirates dirham', 'jobboardwp' ),
				'AFN' => __( 'Afghan afghani', 'jobboardwp' ),
				'ALL' => __( 'Albanian lek', 'jobboardwp' ),
				'AMD' => __( 'Armenian dram', 'jobboardwp' ),
				'ANG' => __( 'Netherlands Antillean guilder', 'jobboardwp' ),
				'AOA' => __( 'Angolan kwanza', 'jobboardwp' ),
				'ARS' => __( 'Argentine peso', 'jobboardwp' ),
				'AUD' => __( 'Australian dollar', 'jobboardwp' ),
				'AWG' => __( 'Aruban florin', 'jobboardwp' ),
				'AZN' => __( 'Azerbaijani manat', 'jobboardwp' ),
				'BAM' => __( 'Bosnia and Herzegovina convertible mark', 'jobboardwp' ),
				'BBD' => __( 'Barbadian dollar', 'jobboardwp' ),
				'BDT' => __( 'Bangladeshi taka', 'jobboardwp' ),
				'BGN' => __( 'Bulgarian lev', 'jobboardwp' ),
				'BHD' => __( 'Bahraini dinar', 'jobboardwp' ),
				'BIF' => __( 'Burundian franc', 'jobboardwp' ),
				'BMD' => __( 'Bermudian dollar', 'jobboardwp' ),
				'BND' => __( 'Brunei dollar', 'jobboardwp' ),
				'BOB' => __( 'Bolivian boliviano', 'jobboardwp' ),
				'BRL' => __( 'Brazilian real', 'jobboardwp' ),
				'BSD' => __( 'Bahamian dollar', 'jobboardwp' ),
				'BTC' => __( 'Bitcoin', 'jobboardwp' ),
				'BTN' => __( 'Bhutanese ngultrum', 'jobboardwp' ),
				'BWP' => __( 'Botswana pula', 'jobboardwp' ),
				'BYR' => __( 'Belarusian ruble (old)', 'jobboardwp' ),
				'BYN' => __( 'Belarusian ruble', 'jobboardwp' ),
				'BZD' => __( 'Belize dollar', 'jobboardwp' ),
				'CAD' => __( 'Canadian dollar', 'jobboardwp' ),
				'CDF' => __( 'Congolese franc', 'jobboardwp' ),
				'CHF' => __( 'Swiss franc', 'jobboardwp' ),
				'CLP' => __( 'Chilean peso', 'jobboardwp' ),
				'CNY' => __( 'Chinese yuan', 'jobboardwp' ),
				'COP' => __( 'Colombian peso', 'jobboardwp' ),
				'CRC' => __( 'Costa Rican col&oacute;n', 'jobboardwp' ),
				'CUC' => __( 'Cuban convertible peso', 'jobboardwp' ),
				'CUP' => __( 'Cuban peso', 'jobboardwp' ),
				'CVE' => __( 'Cape Verdean escudo', 'jobboardwp' ),
				'CZK' => __( 'Czech koruna', 'jobboardwp' ),
				'DJF' => __( 'Djiboutian franc', 'jobboardwp' ),
				'DKK' => __( 'Danish krone', 'jobboardwp' ),
				'DOP' => __( 'Dominican peso', 'jobboardwp' ),
				'DZD' => __( 'Algerian dinar', 'jobboardwp' ),
				'EGP' => __( 'Egyptian pound', 'jobboardwp' ),
				'ERN' => __( 'Eritrean nakfa', 'jobboardwp' ),
				'ETB' => __( 'Ethiopian birr', 'jobboardwp' ),
				'EUR' => __( 'Euro', 'jobboardwp' ),
				'FJD' => __( 'Fijian dollar', 'jobboardwp' ),
				'FKP' => __( 'Falkland Islands pound', 'jobboardwp' ),
				'GBP' => __( 'Pound sterling', 'jobboardwp' ),
				'GEL' => __( 'Georgian lari', 'jobboardwp' ),
				'GGP' => __( 'Guernsey pound', 'jobboardwp' ),
				'GHS' => __( 'Ghana cedi', 'jobboardwp' ),
				'GIP' => __( 'Gibraltar pound', 'jobboardwp' ),
				'GMD' => __( 'Gambian dalasi', 'jobboardwp' ),
				'GNF' => __( 'Guinean franc', 'jobboardwp' ),
				'GTQ' => __( 'Guatemalan quetzal', 'jobboardwp' ),
				'GYD' => __( 'Guyanese dollar', 'jobboardwp' ),
				'HKD' => __( 'Hong Kong dollar', 'jobboardwp' ),
				'HNL' => __( 'Honduran lempira', 'jobboardwp' ),
				'HRK' => __( 'Croatian kuna', 'jobboardwp' ),
				'HTG' => __( 'Haitian gourde', 'jobboardwp' ),
				'HUF' => __( 'Hungarian forint', 'jobboardwp' ),
				'IDR' => __( 'Indonesian rupiah', 'jobboardwp' ),
				'ILS' => __( 'Israeli new shekel', 'jobboardwp' ),
				'IMP' => __( 'Manx pound', 'jobboardwp' ),
				'INR' => __( 'Indian rupee', 'jobboardwp' ),
				'IQD' => __( 'Iraqi dinar', 'jobboardwp' ),
				'IRR' => __( 'Iranian rial', 'jobboardwp' ),
				'IRT' => __( 'Iranian toman', 'jobboardwp' ),
				'ISK' => __( 'Icelandic kr&oacute;na', 'jobboardwp' ),
				'JEP' => __( 'Jersey pound', 'jobboardwp' ),
				'JMD' => __( 'Jamaican dollar', 'jobboardwp' ),
				'JOD' => __( 'Jordanian dinar', 'jobboardwp' ),
				'JPY' => __( 'Japanese yen', 'jobboardwp' ),
				'KES' => __( 'Kenyan shilling', 'jobboardwp' ),
				'KGS' => __( 'Kyrgyzstani som', 'jobboardwp' ),
				'KHR' => __( 'Cambodian riel', 'jobboardwp' ),
				'KMF' => __( 'Comorian franc', 'jobboardwp' ),
				'KPW' => __( 'North Korean won', 'jobboardwp' ),
				'KRW' => __( 'South Korean won', 'jobboardwp' ),
				'KWD' => __( 'Kuwaiti dinar', 'jobboardwp' ),
				'KYD' => __( 'Cayman Islands dollar', 'jobboardwp' ),
				'KZT' => __( 'Kazakhstani tenge', 'jobboardwp' ),
				'LAK' => __( 'Lao kip', 'jobboardwp' ),
				'LBP' => __( 'Lebanese pound', 'jobboardwp' ),
				'LKR' => __( 'Sri Lankan rupee', 'jobboardwp' ),
				'LRD' => __( 'Liberian dollar', 'jobboardwp' ),
				'LSL' => __( 'Lesotho loti', 'jobboardwp' ),
				'LYD' => __( 'Libyan dinar', 'jobboardwp' ),
				'MAD' => __( 'Moroccan dirham', 'jobboardwp' ),
				'MDL' => __( 'Moldovan leu', 'jobboardwp' ),
				'MGA' => __( 'Malagasy ariary', 'jobboardwp' ),
				'MKD' => __( 'Macedonian denar', 'jobboardwp' ),
				'MMK' => __( 'Burmese kyat', 'jobboardwp' ),
				'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', 'jobboardwp' ),
				'MOP' => __( 'Macanese pataca', 'jobboardwp' ),
				'MRU' => __( 'Mauritanian ouguiya', 'jobboardwp' ),
				'MUR' => __( 'Mauritian rupee', 'jobboardwp' ),
				'MVR' => __( 'Maldivian rufiyaa', 'jobboardwp' ),
				'MWK' => __( 'Malawian kwacha', 'jobboardwp' ),
				'MXN' => __( 'Mexican peso', 'jobboardwp' ),
				'MYR' => __( 'Malaysian ringgit', 'jobboardwp' ),
				'MZN' => __( 'Mozambican metical', 'jobboardwp' ),
				'NAD' => __( 'Namibian dollar', 'jobboardwp' ),
				'NGN' => __( 'Nigerian naira', 'jobboardwp' ),
				'NIO' => __( 'Nicaraguan c&oacute;rdoba', 'jobboardwp' ),
				'NOK' => __( 'Norwegian krone', 'jobboardwp' ),
				'NPR' => __( 'Nepalese rupee', 'jobboardwp' ),
				'NZD' => __( 'New Zealand dollar', 'jobboardwp' ),
				'OMR' => __( 'Omani rial', 'jobboardwp' ),
				'PAB' => __( 'Panamanian balboa', 'jobboardwp' ),
				'PEN' => __( 'Sol', 'jobboardwp' ),
				'PGK' => __( 'Papua New Guinean kina', 'jobboardwp' ),
				'PHP' => __( 'Philippine peso', 'jobboardwp' ),
				'PKR' => __( 'Pakistani rupee', 'jobboardwp' ),
				'PLN' => __( 'Polish z&#x142;oty', 'jobboardwp' ),
				'PRB' => __( 'Transnistrian ruble', 'jobboardwp' ),
				'PYG' => __( 'Paraguayan guaran&iacute;', 'jobboardwp' ),
				'QAR' => __( 'Qatari riyal', 'jobboardwp' ),
				'RON' => __( 'Romanian leu', 'jobboardwp' ),
				'RSD' => __( 'Serbian dinar', 'jobboardwp' ),
				'RUB' => __( 'Russian ruble', 'jobboardwp' ),
				'RWF' => __( 'Rwandan franc', 'jobboardwp' ),
				'SAR' => __( 'Saudi riyal', 'jobboardwp' ),
				'SBD' => __( 'Solomon Islands dollar', 'jobboardwp' ),
				'SCR' => __( 'Seychellois rupee', 'jobboardwp' ),
				'SDG' => __( 'Sudanese pound', 'jobboardwp' ),
				'SEK' => __( 'Swedish krona', 'jobboardwp' ),
				'SGD' => __( 'Singapore dollar', 'jobboardwp' ),
				'SHP' => __( 'Saint Helena pound', 'jobboardwp' ),
				'SLL' => __( 'Sierra Leonean leone', 'jobboardwp' ),
				'SOS' => __( 'Somali shilling', 'jobboardwp' ),
				'SRD' => __( 'Surinamese dollar', 'jobboardwp' ),
				'SSP' => __( 'South Sudanese pound', 'jobboardwp' ),
				'STN' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'jobboardwp' ),
				'SYP' => __( 'Syrian pound', 'jobboardwp' ),
				'SZL' => __( 'Swazi lilangeni', 'jobboardwp' ),
				'THB' => __( 'Thai baht', 'jobboardwp' ),
				'TJS' => __( 'Tajikistani somoni', 'jobboardwp' ),
				'TMT' => __( 'Turkmenistan manat', 'jobboardwp' ),
				'TND' => __( 'Tunisian dinar', 'jobboardwp' ),
				'TOP' => __( 'Tongan pa&#x2bb;anga', 'jobboardwp' ),
				'TRY' => __( 'Turkish lira', 'jobboardwp' ),
				'TTD' => __( 'Trinidad and Tobago dollar', 'jobboardwp' ),
				'TWD' => __( 'New Taiwan dollar', 'jobboardwp' ),
				'TZS' => __( 'Tanzanian shilling', 'jobboardwp' ),
				'UAH' => __( 'Ukrainian hryvnia', 'jobboardwp' ),
				'UGX' => __( 'Ugandan shilling', 'jobboardwp' ),
				'USD' => __( 'United States (US) dollar', 'jobboardwp' ),
				'UYU' => __( 'Uruguayan peso', 'jobboardwp' ),
				'UZS' => __( 'Uzbekistani som', 'jobboardwp' ),
				'VEF' => __( 'Venezuelan bol&iacute;var', 'jobboardwp' ),
				'VES' => __( 'Bol&iacute;var soberano', 'jobboardwp' ),
				'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', 'jobboardwp' ),
				'VUV' => __( 'Vanuatu vatu', 'jobboardwp' ),
				'WST' => __( 'Samoan t&#x101;l&#x101;', 'jobboardwp' ),
				'XAF' => __( 'Central African CFA franc', 'jobboardwp' ),
				'XCD' => __( 'East Caribbean dollar', 'jobboardwp' ),
				'XOF' => __( 'West African CFA franc', 'jobboardwp' ),
				'XPF' => __( 'CFP franc', 'jobboardwp' ),
				'YER' => __( 'Yemeni rial', 'jobboardwp' ),
				'ZAR' => __( 'South African rand', 'jobboardwp' ),
				'ZMW' => __( 'Zambian kwacha', 'jobboardwp' ),
			);

			$currency_symbols = array(
				'AED' => '&#x62f;.&#x625;',
				'AFN' => '&#x60b;',
				'ALL' => 'L',
				'AMD' => 'AMD',
				'ANG' => '&fnof;',
				'AOA' => 'Kz',
				'ARS' => '&#36;',
				'AUD' => '&#36;',
				'AWG' => 'Afl.',
				'AZN' => '&#8380;',
				'BAM' => 'KM',
				'BBD' => '&#36;',
				'BDT' => '&#2547;&nbsp;',
				'BGN' => '&#1083;&#1074;.',
				'BHD' => '.&#x62f;.&#x628;',
				'BIF' => 'Fr',
				'BMD' => '&#36;',
				'BND' => '&#36;',
				'BOB' => 'Bs.',
				'BRL' => '&#82;&#36;',
				'BSD' => '&#36;',
				'BTC' => '&#3647;',
				'BTN' => 'Nu.',
				'BWP' => 'P',
				'BYR' => 'Br',
				'BYN' => 'Br',
				'BZD' => '&#36;',
				'CAD' => '&#36;',
				'CDF' => 'Fr',
				'CHF' => '&#67;&#72;&#70;',
				'CLP' => '&#36;',
				'CNY' => '&yen;',
				'COP' => '&#36;',
				'CRC' => '&#x20a1;',
				'CUC' => '&#36;',
				'CUP' => '&#36;',
				'CVE' => '&#36;',
				'CZK' => '&#75;&#269;',
				'DJF' => 'Fr',
				'DKK' => 'kr.',
				'DOP' => 'RD&#36;',
				'DZD' => '&#x62f;.&#x62c;',
				'EGP' => 'EGP',
				'ERN' => 'Nfk',
				'ETB' => 'Br',
				'EUR' => '&euro;',
				'FJD' => '&#36;',
				'FKP' => '&pound;',
				'GBP' => '&pound;',
				'GEL' => '&#x20be;',
				'GGP' => '&pound;',
				'GHS' => '&#x20b5;',
				'GIP' => '&pound;',
				'GMD' => 'D',
				'GNF' => 'Fr',
				'GTQ' => 'Q',
				'GYD' => '&#36;',
				'HKD' => '&#36;',
				'HNL' => 'L',
				'HRK' => 'kn',
				'HTG' => 'G',
				'HUF' => '&#70;&#116;',
				'IDR' => 'Rp',
				'ILS' => '&#8362;',
				'IMP' => '&pound;',
				'INR' => '&#8377;',
				'IQD' => '&#x62f;.&#x639;',
				'IRR' => '&#xfdfc;',
				'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
				'ISK' => 'kr.',
				'JEP' => '&pound;',
				'JMD' => '&#36;',
				'JOD' => '&#x62f;.&#x627;',
				'JPY' => '&yen;',
				'KES' => 'KSh',
				'KGS' => '&#x441;&#x43e;&#x43c;',
				'KHR' => '&#x17db;',
				'KMF' => 'Fr',
				'KPW' => '&#x20a9;',
				'KRW' => '&#8361;',
				'KWD' => '&#x62f;.&#x643;',
				'KYD' => '&#36;',
				'KZT' => '&#8376;',
				'LAK' => '&#8365;',
				'LBP' => '&#x644;.&#x644;',
				'LKR' => '&#xdbb;&#xdd4;',
				'LRD' => '&#36;',
				'LSL' => 'L',
				'LYD' => '&#x62f;.&#x644;',
				'MAD' => '&#x62f;.&#x645;.',
				'MDL' => 'MDL',
				'MGA' => 'Ar',
				'MKD' => '&#x434;&#x435;&#x43d;',
				'MMK' => 'Ks',
				'MNT' => '&#x20ae;',
				'MOP' => 'P',
				'MRU' => 'UM',
				'MUR' => '&#x20a8;',
				'MVR' => '.&#x783;',
				'MWK' => 'MK',
				'MXN' => '&#36;',
				'MYR' => '&#82;&#77;',
				'MZN' => 'MT',
				'NAD' => 'N&#36;',
				'NGN' => '&#8358;',
				'NIO' => 'C&#36;',
				'NOK' => '&#107;&#114;',
				'NPR' => '&#8360;',
				'NZD' => '&#36;',
				'OMR' => '&#x631;.&#x639;.',
				'PAB' => 'B/.',
				'PEN' => 'S/',
				'PGK' => 'K',
				'PHP' => '&#8369;',
				'PKR' => '&#8360;',
				'PLN' => '&#122;&#322;',
				'PRB' => '&#x440;.',
				'PYG' => '&#8370;',
				'QAR' => '&#x631;.&#x642;',
				'RMB' => '&yen;',
				'RON' => 'lei',
				'RSD' => '&#1088;&#1089;&#1076;',
				'RUB' => '&#8381;',
				'RWF' => 'Fr',
				'SAR' => '&#x631;.&#x633;',
				'SBD' => '&#36;',
				'SCR' => '&#x20a8;',
				'SDG' => '&#x62c;.&#x633;.',
				'SEK' => '&#107;&#114;',
				'SGD' => '&#36;',
				'SHP' => '&pound;',
				'SLL' => 'Le',
				'SOS' => 'Sh',
				'SRD' => '&#36;',
				'SSP' => '&pound;',
				'STN' => 'Db',
				'SYP' => '&#x644;.&#x633;',
				'SZL' => 'E',
				'THB' => '&#3647;',
				'TJS' => '&#x405;&#x41c;',
				'TMT' => 'm',
				'TND' => '&#x62f;.&#x62a;',
				'TOP' => 'T&#36;',
				'TRY' => '&#8378;',
				'TTD' => '&#36;',
				'TWD' => '&#78;&#84;&#36;',
				'TZS' => 'Sh',
				'UAH' => '&#8372;',
				'UGX' => 'UGX',
				'USD' => '&#36;',
				'UYU' => '&#36;',
				'UZS' => 'UZS',
				'VEF' => 'Bs F',
				'VES' => 'Bs.S',
				'VND' => '&#8363;',
				'VUV' => 'Vt',
				'WST' => 'T',
				'XAF' => 'CFA',
				'XCD' => '&#36;',
				'XOF' => 'CFA',
				'XPF' => 'Fr',
				'YER' => '&#xfdfc;',
				'ZAR' => '&#82;',
				'ZMW' => 'ZK',
			);

			$this->currencies = array_merge_recursive( $currencies, $currency_symbols );
			$this->currencies = apply_filters( 'jb_currencies', $this->currencies );
		}
	}
}
