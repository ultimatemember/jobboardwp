<?php
namespace jb\frontend;

use WP_Error;
use WP_Filesystem_Base;
use function WP_Filesystem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\frontend\Actions_Listener' ) ) {

	/**
	 * Class Actions_Listener
	 *
	 * @package jb\frontend
	 */
	class Actions_Listener {

		/**
		 * Actions_Listener constructor.
		 */
		public function __construct() {
			add_action( 'wp_loaded', array( $this, 'actions_listener' ) );
			add_filter( 'jb_job_submitted_data', array( $this, 'add_location_data' ) );
		}

		/**
		 * Parse and save location data
		 *
		 * @param array $job_data
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function add_location_data( $job_data ) {
			// phpcs:disable WordPress.Security.NonceVerification -- already verified here
			$key = JB()->options()->get( 'googlemaps-api-key' );
			if ( empty( $key ) || empty( $_POST['job_location_data'] ) ) {
				return $job_data;
			}

			$location_data = json_decode( wp_unslash( $_POST['job_location_data'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized just below
			$location_data = JB()->common()->job()->sanitize_location_data( $location_data );

			$job_data['meta_input']['jb-location-raw-data'] = $location_data;
			if ( isset( $location_data->geometry, $location_data->geometry->location ) ) {
				if ( isset( $location_data->geometry->location->lat ) ) {
					$job_data['meta_input']['jb-location-lat'] = sanitize_text_field( $location_data->geometry->location->lat );
				}
				if ( isset( $location_data->geometry->location->lng ) ) {
					$job_data['meta_input']['jb-location-long'] = sanitize_text_field( $location_data->geometry->location->lng );
				}
			}
			if ( isset( $location_data->formatted_address ) ) {
				$job_data['meta_input']['jb-location-formatted-address'] = sanitize_text_field( $location_data->formatted_address );
			}

			if ( ! empty( $location_data->address_components ) ) {
				$address_data = $location_data->address_components;

				foreach ( $address_data as $data ) {
					switch ( $data->types[0] ) {
						case 'sublocality_level_1':
						case 'locality':
						case 'postal_town':
							$job_data['meta_input']['jb-location-city'] = sanitize_text_field( $data->long_name );
							break;
						case 'administrative_area_level_1':
						case 'administrative_area_level_2':
							$job_data['meta_input']['jb-location-state-short'] = sanitize_text_field( $data->short_name );
							$job_data['meta_input']['jb-location-state-long']  = sanitize_text_field( $data->long_name );
							break;
						case 'country':
							$job_data['meta_input']['jb-location-country-short'] = sanitize_text_field( $data->short_name );
							$job_data['meta_input']['jb-location-country-long']  = sanitize_text_field( $data->long_name );
							break;
					}
				}
			}

			return $job_data;
			// phpcs:enable WordPress.Security.NonceVerification -- already verified here
		}

		/**
		 * Handle posting a job form and maybe create user if the form data is proper
		 * $_POST validation on form submission
		 *
		 * @return int|WP_Error
		 *
		 * @since 1.0
		 */
		public function maybe_create_user() {
			// phpcs:disable WordPress.Security.NonceVerification -- already verified here
			/**
			 * @var $posting_form Forms
			 */
			global $posting_form;

			$user_id = get_current_user_id();

			if ( ! is_user_logged_in() ) {
				$user_id = 0;

				/**
				 * Fires just before trying to create user if it doesn't exist when job submission.
				 *
				 * @since 1.2.2
				 * @hook jb_job_create_user_validation
				 *
				 * @param {object} $posting_form Frontend form class (\jb\frontend\Forms) instance.
				 * @param {int}    $user_id      Current user ID or 0 if guest.
				 */
				do_action( 'jb_job_create_user_validation', $posting_form, $user_id );

				if ( JB()->options()->get( 'account-required' ) ) {
					$username     = '';
					$password     = '';
					$author_email = '';
					$author_fname = ! empty( $_POST['author_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['author_first_name'] ) ) : '';
					$author_lname = ! empty( $_POST['author_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['author_last_name'] ) ) : '';

					if ( JB()->options()->get( 'full-name-required' ) ) {
						if ( empty( $author_fname ) ) {
							$posting_form->add_error( 'author_first_name', __( 'Please fill the first name field.', 'jobboardwp' ) );
						}

						if ( empty( $author_lname ) ) {
							$posting_form->add_error( 'author_last_name', __( 'Please fill the last name field.', 'jobboardwp' ) );
						}
					}

					if ( empty( $_POST['author_email'] ) ) {
						$posting_form->add_error( 'author_email', __( 'Please fill email address', 'jobboardwp' ) );
					} else {
						$author_email = trim( sanitize_email( wp_unslash( $_POST['author_email'] ) ) );

						if ( ! is_email( $author_email ) ) {
							$posting_form->add_error( 'author_email', __( 'Wrong email address format', 'jobboardwp' ) );
						}

						if ( email_exists( $author_email ) ) {
							$posting_form->add_error( 'author_email', __( 'Please use another email address', 'jobboardwp' ) );
						}
					}

					$notify = 'admin';
					if ( ! JB()->options()->get( 'account-password-email' ) ) {
						if ( empty( $_POST['author_password'] ) || empty( $_POST['author_password_confirm'] ) ) {
							if ( empty( $_POST['author_password'] ) ) {
								$posting_form->add_error( 'author_password', __( 'Password is required', 'jobboardwp' ) );
							}

							if ( empty( $_POST['author_password_confirm'] ) ) {
								$posting_form->add_error( 'author_password_confirm', __( 'Please confirm the password', 'jobboardwp' ) );
							}
						} else {
							$password         = trim( $_POST['author_password'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- don't sanitize pass
							$password_confirm = trim( $_POST['author_password_confirm'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- don't sanitize pass
							$min_length       = 8;
							$max_length       = 30;

							if ( $password !== $password_confirm ) {
								$posting_form->add_error( 'author_password_confirm', __( 'Your passwords do not match', 'jobboardwp' ) );
							}

							if ( mb_strlen( $password ) < $min_length ) {
								$posting_form->add_error( 'author_password', __( 'Your password must contain at least 8 characters', 'jobboardwp' ) );
							}

							if ( mb_strlen( $password ) > $max_length ) {
								$posting_form->add_error( 'author_password', __( 'Your password must contain less than 30 characters', 'jobboardwp' ) );
							}

							$pattern = '/^(?=.*\d)(?=.*[A-Z]).{8,20}$/';
							if ( ! preg_match( $pattern, $password ) ) {
								$posting_form->add_error( 'author_password', __( 'Your password must contain at least one lowercase letter, one capital letter and one number', 'jobboardwp' ) );
							}
						}
					} else {
						// User is forced to set up account with email sent to them. This password will remain a secret.
						$password = wp_generate_password();
						$notify   = 'both';

						/**
						 * Filter change email recipients - admin or both.
						 *
						 * @since 1.2.7
						 * @hook jb_job_email_notify
						 *
						 * @param {string}     $notify recipients.
						 *
						 * @return {string}    $notify new recipients.
						 *
						 * @example <caption>Set email recipients.</caption>
						 * function my_jb_job_email_notify( $notify ) {
						 *     $notify = 'admin';
						 *
						 *     return $notify;
						 * }
						 * add_filter( 'jb_job_email_notify', 'my_jb_job_email_notify', 10, 1 );
						 */
						$notify = apply_filters( 'jb_job_email_notify', $notify );
					}

					if ( ! JB()->options()->get( 'account-username-generate' ) ) {
						if ( empty( $_POST['author_username'] ) ) {
							$posting_form->add_error( 'author_username', __( 'Username is required', 'jobboardwp' ) );
						} else {
							$username = trim( sanitize_user( wp_unslash( $_POST['author_username'] ) ) );
							if ( username_exists( $username ) ) {
								$posting_form->add_error( 'author_username', __( 'Please use another username', 'jobboardwp' ) );
							}
						}
					} else {
						$username = sanitize_user( current( explode( '@', $author_email ) ), true );

						// Ensure username is unique.
						$append     = 1;
						$o_username = $username;

						while ( username_exists( $username ) ) {
							$username = $o_username . $append;
							++$append;
						}
					}

					if ( ! $posting_form->has_errors() ) {
						// Create account.
						$userdata = array(
							'user_login' => $username,
							'user_pass'  => $password,
							'user_email' => $author_email,
							'role'       => JB()->options()->get( 'account-role' ),
							'first_name' => $author_fname,
							'last_name'  => $author_lname,
						);
						/**
						 * Filters the userdata after creating user account when posting a new job.
						 *
						 * Note: Validation already passed!
						 *
						 * @since 1.0
						 * @hook jb_job_submission_create_account_data
						 *
						 * @param {array} $userdata Userdata based on the submitted form. See the list of all arguments https://developer.wordpress.org/reference/functions/wp_insert_user/#parameters
						 *
						 * @return {array} Userdata.
						 */
						$userdata = apply_filters( 'jb_job_submission_create_account_data', $userdata );

						$user_id = wp_insert_user( $userdata );

						if ( ! is_wp_error( $user_id ) ) {
							/**
							 * Fires after creating a user account when posting a new job.
							 *
							 * @since 1.1.0
							 * @hook jb_job_submission_after_create_account
							 *
							 * @param {int} $user_id Created User ID.
							 */
							do_action( 'jb_job_submission_after_create_account', $user_id );
						}

						// Login here
						add_action( 'set_logged_in_cookie', array( $this, 'update_global_login_cookie' ) );
						wp_set_auth_cookie( $user_id, true, is_ssl() );
						wp_set_current_user( $user_id );
						remove_action( 'set_logged_in_cookie', array( $this, 'update_global_login_cookie' ) );

						//Notify admin or user + admin about new user registration
						wp_new_user_notification( $user_id, null, $notify );
					}
				} elseif ( ( ! empty( $_POST['author_email'] ) && JB()->options()->get( 'account-username-generate' ) ) || ( ! empty( $_POST['author_email'] ) && ! empty( $_POST['author_username'] ) && ! JB()->options()->get( 'account-username-generate' ) ) ) {
					$author_email = trim( sanitize_email( wp_unslash( $_POST['author_email'] ) ) );

					if ( ! is_email( $author_email ) ) {
						$posting_form->add_error( 'author_email', __( 'Wrong email address format', 'jobboardwp' ) );
					}

					if ( email_exists( $author_email ) ) {
						$posting_form->add_error( 'author_email', __( 'Please use another email address', 'jobboardwp' ) );
					}

					$username     = '';
					$password     = '';
					$author_fname = ! empty( $_POST['author_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['author_first_name'] ) ) : '';
					$author_lname = ! empty( $_POST['author_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['author_last_name'] ) ) : '';

					if ( JB()->options()->get( 'full-name-required' ) ) {
						if ( empty( $author_fname ) ) {
							$posting_form->add_error( 'author_first_name', __( 'Please fill the first name field.', 'jobboardwp' ) );
						}

						if ( empty( $author_lname ) ) {
							$posting_form->add_error( 'author_last_name', __( 'Please fill the last name field.', 'jobboardwp' ) );
						}
					}

					$notify = 'admin';
					if ( ! JB()->options()->get( 'account-password-email' ) ) {
						if ( empty( $_POST['author_password'] ) || empty( $_POST['author_password_confirm'] ) ) {
							if ( empty( $_POST['author_password'] ) ) {
								$posting_form->add_error( 'author_password', __( 'Password is required', 'jobboardwp' ) );
							}

							if ( empty( $_POST['author_password_confirm'] ) ) {
								$posting_form->add_error( 'author_password_confirm', __( 'Please confirm the password', 'jobboardwp' ) );
							}
						} else {
							$password         = trim( $_POST['author_password'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- don't sanitize pass
							$password_confirm = trim( $_POST['author_password_confirm'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- don't sanitize pass

							if ( $password !== $password_confirm ) {
								$posting_form->add_error( 'author_password_confirm', __( 'Your passwords do not match', 'jobboardwp' ) );
							}
						}
					} else {
						// User is forced to set up account with email sent to them. This password will remain a secret.
						$password = wp_generate_password();
						$notify   = 'both';

						/** This filter is documented in includes/frontend/class-actions-listener.php */
						$notify = apply_filters( 'jb_job_email_notify', $notify );
					}

					if ( ! JB()->options()->get( 'account-username-generate' ) ) {
						if ( ! empty( $_POST['author_username'] ) ) {
							$username = trim( sanitize_user( wp_unslash( $_POST['author_username'] ) ) );
							if ( username_exists( $username ) ) {
								$posting_form->add_error( 'author_username', __( 'Please use another username', 'jobboardwp' ) );
							}
						}
					} else {
						$username = sanitize_user( current( explode( '@', $author_email ) ), true );

						// Ensure username is unique.
						$append     = 1;
						$o_username = $username;

						while ( username_exists( $username ) ) {
							$username = $o_username . $append;
							++$append;
						}
					}

					if ( ! $posting_form->has_errors() ) {
						// Create account.
						$userdata = array(
							'user_login' => $username,
							'user_pass'  => $password,
							'user_email' => $author_email,
							'role'       => JB()->options()->get( 'account-role' ),
							'first_name' => $author_fname,
							'last_name'  => $author_lname,
						);
						/** This action is documented in includes/frontend/class-actions-listener.php */
						$userdata = apply_filters( 'jb_job_submission_create_account_data', $userdata );

						$user_id = wp_insert_user( $userdata );

						if ( ! is_wp_error( $user_id ) ) {
							/** This action is documented in includes/frontend/class-actions-listener.php */
							do_action( 'jb_job_submission_after_create_account', $user_id );
						}

						// Login here
						add_action( 'set_logged_in_cookie', array( $this, 'update_global_login_cookie' ) );
						wp_set_auth_cookie( $user_id, true, is_ssl() );
						wp_set_current_user( $user_id );
						remove_action( 'set_logged_in_cookie', array( $this, 'update_global_login_cookie' ) );

						//Notify admin or user + admin about new user registration
						wp_new_user_notification( $user_id, null, $notify );
					}
				}
			} else {
				$your_details_enabled = JB()->options()->get( 'your-details-section' );
				if ( ! empty( $your_details_enabled ) ) {
					$author_email = '';
					$author_fname = ! empty( $_POST['author_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['author_first_name'] ) ) : '';
					$author_lname = ! empty( $_POST['author_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['author_last_name'] ) ) : '';

					$current_userdata = get_userdata( $user_id );
					$last_email       = $current_userdata->user_email;

					if ( JB()->options()->get( 'full-name-required' ) ) {
						if ( empty( $author_fname ) ) {
							$posting_form->add_error( 'author_first_name', __( 'Please fill the first name field.', 'jobboardwp' ) );
						}

						if ( empty( $author_lname ) ) {
							$posting_form->add_error( 'author_last_name', __( 'Please fill the last name field.', 'jobboardwp' ) );
						}
					}

					if ( empty( $_POST['author_email'] ) ) {
						$posting_form->add_error( 'author_email', __( 'Please fill email address', 'jobboardwp' ) );
					} else {
						$author_email = trim( sanitize_email( wp_unslash( $_POST['author_email'] ) ) );

						if ( $last_email !== $author_email ) {
							if ( ! is_email( $author_email ) ) {
								$posting_form->add_error( 'author_email', __( 'Wrong email address format', 'jobboardwp' ) );
							}

							if ( email_exists( $author_email ) ) {
								$posting_form->add_error( 'author_email', __( 'Please use another email address', 'jobboardwp' ) );
							}
						}
					}

					if ( ! $posting_form->has_errors() ) {
						// Update account.
						$userdata = array(
							'ID'         => $user_id,
							'user_email' => $author_email,
							'first_name' => $author_fname,
							'last_name'  => $author_lname,
						);

						/**
						 * Filters the userdata after updating user account when posting a new job.
						 *
						 * Note: Validation already passed!
						 *
						 * @since 1.0
						 * @hook jb_job_submission_update_account_data
						 *
						 * @param {array} $userdata Userdata based on the submitted form. See the list of all arguments https://developer.wordpress.org/reference/functions/wp_insert_user/#parameters
						 *
						 * @return {array} Userdata.
						 */
						$userdata = apply_filters( 'jb_job_submission_update_account_data', $userdata );

						wp_update_user( $userdata );
					}
				}
			}

			return $user_id;
			// phpcs:enable WordPress.Security.NonceVerification -- already verified here
		}

		/**
		 * Allows for immediate access to the logged in cookie after mid-request login.
		 *
		 * @param string $logged_in_cookie Logged in cookie.
		 *
		 * @since 1.0
		 */
		public function update_global_login_cookie( $logged_in_cookie ) {
			$_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
		}

		/**
		 * Main frontend action listener
		 *
		 * @since 1.0
		 */
		public function actions_listener() {
			global $wp_filesystem;

			if ( ! empty( $_POST['jb-action'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification -- there is nonce verification for each case below
				switch ( sanitize_key( $_POST['jb-action'] ) ) {
					case 'job-submission':
						global $posting_form;

						$posting_form = JB()->frontend()->forms( array( 'id' => 'jb-job-submission' ) );

						$posting_form->flush_errors();

						if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'jb-job-submission' ) ) {
							$posting_form->add_error( 'global', __( 'Security issue, Please try again', 'jobboardwp' ) );
						}

						if ( ! isset( $_POST['jb-job-submission-step'] ) || ( 'draft' === sanitize_key( wp_unslash( $_POST['jb-job-submission-step'] ) ) && ! is_user_logged_in() && ! JB()->options()->get( 'account-creation' ) && ! JB()->options()->get( 'account-required' ) ) ) {
							$posting_form->add_error( 'global', __( 'You cannot save draft jobs, Please try again', 'jobboardwp' ) );
						}

						// register user if it's needed
						$user_id = $this->maybe_create_user();

						$nonce_action = '';
						if ( empty( $user_id ) ) {
							if ( isset( $_COOKIE['jb-guest-job-posting'] ) ) {
								$nonce_action = 'jb-guest-job-posting' . sanitize_text_field( wp_unslash( $_COOKIE['jb-guest-job-posting'] ) );
							} else {
								$uniqid = uniqid();
								JB()->setcookie( 'jb-guest-job-posting', $uniqid, time() + HOUR_IN_SECONDS );
								$nonce_action = 'jb-guest-job-posting' . $uniqid;
							}
						}

						$is_edited = false;
						if ( ! empty( $_GET['job-id'] ) ) {
							$job_id = absint( $_GET['job-id'] );
							$job    = get_post( $job_id );
							if ( ! empty( $job ) && ! is_wp_error( $job ) ) {
								$is_edited = true;

								if ( ! empty( $user_id ) ) {
									if ( ! user_can( $user_id, 'edit_post', $job_id ) && absint( $job->post_author ) !== absint( $user_id ) ) {
										$posting_form->add_error( 'global', __( 'Security action, Please try again with another job.', 'jobboardwp' ) );
									}
								} else {
									$job_guest_nonce = get_post_meta( $job_id, 'jb-guest-nonce', true );
									if ( empty( $job_guest_nonce ) || ! wp_verify_nonce( $job_guest_nonce, $nonce_action ) ) {
										$posting_form->add_error( 'global', __( 'Security action, Please try again with another job.', 'jobboardwp' ) );
									}
								}
							}
						}

						// handle job details fields
						$title        = '';
						$content      = '';
						$app_contact  = '';
						$company_name = '';

						if ( empty( $_POST['job_title'] ) ) {
							$posting_form->add_error( 'job_title', __( 'Job title cannot be empty', 'jobboardwp' ) );
						} else {
							$title = sanitize_text_field( wp_unslash( $_POST['job_title'] ) );
							if ( empty( $title ) ) {
								$posting_form->add_error( 'job_title', __( 'Job title cannot be empty', 'jobboardwp' ) );
							}
						}

						if ( empty( $_POST['job_description'] ) ) {
							$posting_form->add_error( 'job_description', __( 'Job description cannot be empty', 'jobboardwp' ) );
						} else {
							$content = wp_kses_post( wp_unslash( $_POST['job_description'] ) );
							if ( empty( $content ) ) {
								$posting_form->add_error( 'job_description', __( 'Job description cannot be empty', 'jobboardwp' ) );
							}
						}

						if ( empty( $_POST['job_application'] ) ) {
							$posting_form->add_error( 'job_application', __( 'Application contact cannot be empty', 'jobboardwp' ) );
						} else {
							switch ( JB()->options()->get( 'application-method' ) ) {
								case 'email':
									$app_contact = sanitize_email( wp_unslash( $_POST['job_application'] ) );
									if ( ! is_email( $app_contact ) ) {
										$posting_form->add_error( 'job_application', __( 'Job application must be an email address', 'jobboardwp' ) );
									}
									break;
								case 'url':
									$app_contact = sanitize_text_field( wp_unslash( $_POST['job_application'] ) );
									// Prefix http if needed.
									if ( false === strpos( $app_contact, 'http:' ) && false === strpos( $app_contact, 'https:' ) ) {
										$app_contact = 'https://' . $app_contact;
									}
									if ( is_email( $app_contact ) || ! JB()->common()->job()->validate_url( $app_contact ) ) {
										$posting_form->add_error( 'job_application', __( 'Job application must be an URL', 'jobboardwp' ) );
									}
									break;
								default:
									$app_contact = sanitize_email( wp_unslash( $_POST['job_application'] ) );
									if ( ! is_email( $app_contact ) ) {
										$app_contact = sanitize_text_field( wp_unslash( $_POST['job_application'] ) );
										// Prefix http if needed.
										if ( false === strpos( $app_contact, 'http:' ) && false === strpos( $app_contact, 'https:' ) ) {
											$app_contact = 'https://' . $app_contact;
										}
										if ( ! JB()->common()->job()->validate_url( $app_contact ) ) {
											$posting_form->add_error( 'job_application', __( 'Job application must be an email address or URL', 'jobboardwp' ) );
										}
									}
									break;
							}
						}

						$location_type = '0';
						$location      = '';
						if ( ! isset( $_POST['job_location_type'] ) ) {
							$posting_form->add_error( 'job_location', __( 'Job location type invalid', 'jobboardwp' ) );
						} else {
							$location_type = sanitize_text_field( wp_unslash( $_POST['job_location_type'] ) );
							if ( '0' === $location_type ) {
								if ( empty( $_POST['job_location'] ) ) {
									$posting_form->add_error( 'job_location', __( 'Location for onsite job is required', 'jobboardwp' ) );
								} else {
									$location = sanitize_text_field( wp_unslash( $_POST['job_location'] ) );
								}
							} else {
								$location = ! empty( $_POST['job_location'] ) ? sanitize_text_field( wp_unslash( $_POST['job_location'] ) ) : '';
							}
						}

						if ( empty( $_POST['job_type'] ) && JB()->options()->get( 'required-job-type' ) ) {
							$posting_form->add_error( 'job_type', __( 'Job type is required', 'jobboardwp' ) );
						}

						// handle company details
						if ( empty( $_POST['company_name'] ) ) {
							$posting_form->add_error( 'company_name', __( 'Company name cannot be empty', 'jobboardwp' ) );
						} else {
							$company_name = sanitize_text_field( wp_unslash( $_POST['company_name'] ) );
							if ( empty( $company_name ) ) {
								$posting_form->add_error( 'company_name', __( 'Company name cannot be empty', 'jobboardwp' ) );
							}
						}

						$company_website = ! empty( $_POST['company_website'] ) ? sanitize_text_field( wp_unslash( $_POST['company_website'] ) ) : '';
						if ( ! empty( $company_website ) ) {
							// Prefix http if needed.
							if ( false === strpos( $company_website, 'http:' ) && false === strpos( $company_website, 'https:' ) ) {
								$company_website = 'https://' . $company_website;
							}
							if ( ! filter_var( $company_website, FILTER_VALIDATE_URL ) ) {
								$posting_form->add_error( 'company_website', __( 'Company website is invalid', 'jobboardwp' ) );
							}
						}

						$company_tagline = ! empty( $_POST['company_tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['company_tagline'] ) ) : '';

						$company_twitter = ! empty( $_POST['company_twitter'] ) ? sanitize_text_field( wp_unslash( $_POST['company_twitter'] ) ) : '';
						if ( ! empty( $company_twitter ) ) {
							if ( 0 === strpos( $company_twitter, '@' ) ) {
								$company_twitter = substr( $company_twitter, 1 );
							}

							if ( ! empty( $company_twitter ) ) {

								$validate_company_twitter = $company_twitter;
								if ( false === strpos( $company_twitter, 'https://twitter.com/' ) ) {
									$validate_company_twitter = 'https://twitter.com/' . $company_twitter;
								}

								if ( ! filter_var( $validate_company_twitter, FILTER_VALIDATE_URL ) ) {
									$posting_form->add_error( 'company_twitter', __( 'Company Twitter is invalid', 'jobboardwp' ) );
								}
							}
						}

						$company_facebook = ! empty( $_POST['company_facebook'] ) ? sanitize_text_field( wp_unslash( $_POST['company_facebook'] ) ) : '';
						if ( ! empty( $company_facebook ) ) {
							$validate_company_facebook = $company_facebook;
							if ( false === strpos( $company_facebook, 'https://facebook.com/' ) ) {
								$validate_company_facebook = 'https://facebook.com/' . $company_facebook;
							}

							if ( ! filter_var( $validate_company_facebook, FILTER_VALIDATE_URL ) ) {
								$posting_form->add_error( 'company_facebook', __( 'Company Facebook is invalid', 'jobboardwp' ) );
							}
						}

						$company_instagram = ! empty( $_POST['company_instagram'] ) ? sanitize_text_field( wp_unslash( $_POST['company_instagram'] ) ) : '';
						if ( ! empty( $company_instagram ) ) {
							$validate_company_instagram = $company_instagram;
							if ( false === strpos( $company_instagram, 'https://instagram.com/' ) ) {
								$validate_company_instagram = 'https://instagram.com/' . $company_instagram;
							}

							if ( ! filter_var( $validate_company_instagram, FILTER_VALIDATE_URL ) ) {
								$posting_form->add_error( 'company_instagram', __( 'Company Instagram is invalid', 'jobboardwp' ) );
							}
						}

						$status = 'draft';
						if ( isset( $_POST['jb-job-submission-step'] ) && 'preview' === sanitize_key( wp_unslash( $_POST['jb-job-submission-step'] ) ) ) {
							$status = 'jb-preview';
						}

						$company_logo   = '';
						$set_attachment = false;
						if ( ! empty( $_POST['company_logo'] ) && ! empty( $_POST['company_logo_hash'] ) ) {
							// new company logo has been uploaded, so we need to update current user logo
							if ( md5( sanitize_file_name( wp_unslash( $_POST['company_logo'] ) ) . '_jb_uploader_security_salt' ) !== sanitize_key( wp_unslash( $_POST['company_logo_hash'] ) ) ) {
								// invalid salt for company logo, it's for the security enhancements
								$posting_form->add_error( 'company_logo', __( 'Something wrong with image, please re-upload', 'jobboardwp' ) );
							} else {
								if ( ! $wp_filesystem instanceof WP_Filesystem_Base ) {
									require_once ABSPATH . 'wp-admin/includes/file.php';

									$credentials = request_filesystem_credentials( site_url() );
									WP_Filesystem( $credentials );
								}

								$company_logo_temp = sanitize_file_name( wp_unslash( $_POST['company_logo'] ) );

								if ( is_multisite() ) {
									$main_blog = get_network()->site_id;

									$current_blog_url = get_bloginfo( 'url' );
									switch_to_blog( $main_blog );
									$main_blog_url = get_bloginfo( 'url' );
									restore_current_blog();

									$logos_dir = JB()->common()->filesystem()->get_upload_dir( 'jobboardwp/logos', $main_blog );

									$logos_url = JB()->common()->filesystem()->get_upload_url( 'jobboardwp/logos', $main_blog );
									if ( $current_blog_url !== $main_blog_url ) {
										$logos_url = str_replace( $current_blog_url, $main_blog_url, $logos_url );
									}
								} else {
									$logos_dir = JB()->common()->filesystem()->get_upload_dir( 'jobboardwp/logos' );
									$logos_url = JB()->common()->filesystem()->get_upload_url( 'jobboardwp/logos' );
								}

								// replace the company logo inside user logos dir to the uploaded to the temp upload folder image
								$type    = wp_check_filetype( $company_logo_temp );
								$newname = wp_normalize_path( $logos_dir . DIRECTORY_SEPARATOR . $user_id . '.' . $type['ext'] );
								$oldname = wp_normalize_path( JB()->common()->filesystem()->temp_upload_dir . DIRECTORY_SEPARATOR . $company_logo_temp );

								if ( ! $is_edited ) {
									// change Company data for the employer only in case if the posting new job, not while editing existed job
									if ( file_exists( $oldname ) && $wp_filesystem->move( $oldname, $newname, true ) ) {
										$company_logo   = trailingslashit( $logos_url ) . $user_id . '.' . $type['ext'];
										$set_attachment = true;
									}
								} else {
									$company_logo   = trailingslashit( JB()->common()->filesystem()->temp_upload_url ) . $company_logo_temp;
									$set_attachment = true;
								}
							}
						} elseif ( ! empty( $_POST['company_logo'] ) ) {
							// post a job with regular company logo that hasn't been changed when posting a job
							$company_logo_post = sanitize_text_field( wp_unslash( $_POST['company_logo'] ) );

							if ( ! filter_var( $company_logo_post, FILTER_VALIDATE_URL ) ) {
								// company logo must be a URL
								$posting_form->add_error( 'company_logo', __( 'Wrong image URL. Invalid URL', 'jobboardwp' ) );

							} else {

								$type = wp_check_filetype( $company_logo_post );
								if ( is_multisite() ) {
									$main_blog = get_network()->site_id;

									$current_blog_url = get_bloginfo( 'url' );
									switch_to_blog( $main_blog );
									$main_blog_url = get_bloginfo( 'url' );
									restore_current_blog();

									$logos_url = JB()->common()->filesystem()->get_upload_url( 'jobboardwp/logos', $main_blog );
									if ( $current_blog_url !== $main_blog_url ) {
										$logos_url = str_replace( $current_blog_url, $main_blog_url, $logos_url );
									}
								} else {
									$logos_url = JB()->common()->filesystem()->get_upload_url( 'jobboardwp/logos' );
								}

								// check if the company logo gets from the job post attachment
								if ( trailingslashit( $logos_url ) . $user_id . '.' . $type['ext'] !== $company_logo_post ) {

									if ( empty( $_GET['job-id'] ) ) {

										$posting_form->add_error( 'company_logo', __( 'Wrong image URL. Invalid job', 'jobboardwp' ) );

									} else {
										// case when job has own thumbnail
										$attachment_id = get_post_thumbnail_id( absint( $_GET['job-id'] ) );
										if ( ! $attachment_id ) {

											$posting_form->add_error( 'company_logo', __( 'Wrong image URL. Invalid attachment ID', 'jobboardwp' ) );

										} else {

											$image = wp_get_attachment_image_src( $attachment_id );

											if ( ! isset( $image[0] ) || $company_logo_post !== $image[0] ) {
												$posting_form->add_error( 'company_logo', __( 'Wrong image URL. Invalid attachment path', 'jobboardwp' ) );
											} else {
												$company_logo = $company_logo_post;
											}
										}
									}
								} else {
									// case when we get the company logo from the employer image
									$company_logo   = $company_logo_post;
									$set_attachment = true;
								}
							}
						}

						if ( JB()->options()->get( 'job-salary' ) ) {
							if ( empty( $_POST['job_salary_type'] ) && JB()->options()->get( 'required-job-salary' ) ) {
								$posting_form->add_error( 'job_salary_type', __( 'Job salary type is required', 'jobboardwp' ) );
							} else {
								$salary_type = sanitize_key( wp_unslash( $_POST['job_salary_type'] ) );
								if ( '' !== $salary_type ) {
									if ( empty( $_POST['job_salary_amount_type'] ) ) {
										$posting_form->add_error( 'job_salary_amount_type', __( 'Job salary type is required', 'jobboardwp' ) );
									} else {
										$salary_amount_type = sanitize_key( wp_unslash( $_POST['job_salary_amount_type'] ) );
										if ( 'numeric' === $salary_amount_type ) {
											if ( empty( $_POST['job_salary_amount'] ) ) {
												$posting_form->add_error( 'job_salary_amount', __( 'Job salary amount is required and must be more than 0', 'jobboardwp' ) );
											} elseif ( ! is_numeric( $_POST['job_salary_amount'] ) ) {
												$posting_form->add_error( 'job_salary_amount', __( 'Job salary amount must be numeric', 'jobboardwp' ) );
											} else {
												$job_amount = absint( $_POST['job_salary_amount'] );
											}
										} elseif ( empty( $_POST['job_salary_min_amount'] ) && empty( $_POST['job_salary_max_amount'] ) ) {
											$posting_form->add_error( 'job_salary_min_amount', __( 'Job salary amount is required', 'jobboardwp' ) );
											$posting_form->add_error( 'job_salary_max_amount', __( 'Job salary amount is required', 'jobboardwp' ) );
										} else {
											if ( ! is_numeric( $_POST['job_salary_min_amount'] ) ) {
												$posting_form->add_error( 'job_salary_min_amount', __( 'Job salary amount must be numeric', 'jobboardwp' ) );
											} elseif ( 0 !== absint( $_POST['job_salary_max_amount'] ) && absint( $_POST['job_salary_min_amount'] ) >= absint( $_POST['job_salary_max_amount'] ) ) {
												$posting_form->add_error( 'job_salary_min_amount', __( 'Job minimum salary must be lower than maximum salary', 'jobboardwp' ) );
											} else {
												$job_min_amount = absint( $_POST['job_salary_min_amount'] );
											}

											if ( ! is_numeric( $_POST['job_salary_max_amount'] ) ) {
												$posting_form->add_error( 'job_salary_max_amount', __( 'Job salary amount must be numeric', 'jobboardwp' ) );
											} elseif ( 0 !== absint( $_POST['job_salary_max_amount'] ) && absint( $_POST['job_salary_max_amount'] ) <= absint( $_POST['job_salary_min_amount'] ) ) {
												$posting_form->add_error( 'job_salary_max_amount', __( 'Job maximum salary must be higher than minimum salary', 'jobboardwp' ) );
											} else {
												$job_max_amount = absint( $_POST['job_salary_max_amount'] );
											}
										}
									}
									if ( 'recurring' === $salary_type ) {
										if ( empty( $_POST['job_salary_period'] ) ) {
											$posting_form->add_error( 'job_salary_period', __( 'Job salary period is required', 'jobboardwp' ) );
										} else {
											$job_period = sanitize_key( wp_unslash( $_POST['job_salary_period'] ) );
										}
									}
								}
							}
						}

						/**
						 * Fires after JobBoardWP native job submission validations are completed.
						 *
						 * Note: Use this hook for adding custom validations to your Job Post form.
						 *
						 * @since 1.1.0
						 * @hook jb-job-submission-validation
						 *
						 * @param {object} $posting_form Frontend form class (\jb\frontend\Forms) instance.
						 * @param {int}    $user_id      Job author ID.
						 */
						do_action( 'jb-job-submission-validation', $posting_form, $user_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

						if ( ! $posting_form->has_errors() ) {

							if ( JB()->options()->get( 'individual-job-duration' ) ) {
								/** This filter is documented in includes/admin/class-metabox.php */
								$expiry = apply_filters( 'jb_default_individual_expiry', '' );
								$expiry = ! empty( $_POST['job_expire'] ) ? gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['job_expire'] ) ) ) ) : $expiry;
							} else {
								$expiry = JB()->common()->job()->calculate_expiry();
							}

							if ( $is_edited && ! empty( $_GET['job-id'] ) ) {
								// Reset expiration reminder marker as soon as expiration date is changed.
								$job_id     = absint( $_GET['job-id'] );
								$old_expiry = get_post_meta( $job_id, 'jb-expiry-date', true );
								if ( $expiry !== $old_expiry ) {
									delete_post_meta( $job_id, 'jb-is-expiration-reminded' );
								}
							}

							$meta_input = array(
								'jb-location-type'       => $location_type,
								'jb-location'            => $location,
								'jb-application-contact' => $app_contact,
								'jb-company-name'        => $company_name,
								'jb-company-website'     => $company_website,
								'jb-company-tagline'     => $company_tagline,
								'jb-company-twitter'     => $company_twitter,
								'jb-company-facebook'    => $company_facebook,
								'jb-company-instagram'   => $company_instagram,
								'jb-expiry-date'         => $expiry,
							);

							if ( JB()->options()->get( 'job-salary' ) ) {
								$skip_meta_update = array();
								if ( empty( $salary_type ) ) {
									$skip_meta_update = array_merge(
										$skip_meta_update,
										array(
											'jb-salary-type',
											'jb-salary-amount-type',
											'jb-salary-amount',
											'jb-salary-min-amount',
											'jb-salary-max-amount',
											'jb-salary-period',
										)
									);
								} elseif ( 'fixed' === $salary_type ) {
									$skip_meta_update = array_merge(
										$skip_meta_update,
										array(
											'jb-salary-period',
										)
									);
								}

								if ( ! empty( $salary_amount_type ) ) {
									if ( 'numeric' === $salary_amount_type ) {
										$skip_meta_update = array_merge(
											$skip_meta_update,
											array(
												'jb-salary-min-amount',
												'jb-salary-max-amount',
											)
										);
									} elseif ( 'range' === $salary_amount_type ) {
										$skip_meta_update = array_merge(
											$skip_meta_update,
											array(
												'jb-salary-amount',
											)
										);
									}
								}

								if ( $is_edited && ! empty( $_GET['job-id'] ) ) {
									$job_id = absint( $_GET['job-id'] );
									if ( empty( $salary_type ) ) {
										delete_post_meta( $job_id, 'jb-salary-type' );
										delete_post_meta( $job_id, 'jb-salary-amount-type' );
										delete_post_meta( $job_id, 'jb-salary-amount' );
										delete_post_meta( $job_id, 'jb-salary-min-amount' );
										delete_post_meta( $job_id, 'jb-salary-max-amount' );
										delete_post_meta( $job_id, 'jb-salary-period' );
									} elseif ( 'fixed' === $salary_type ) {
										delete_post_meta( $job_id, 'jb-salary-period' );
									}

									if ( ! empty( $salary_amount_type ) ) {
										if ( 'numeric' === $salary_amount_type ) {
											delete_post_meta( $job_id, 'jb-salary-min-amount' );
											delete_post_meta( $job_id, 'jb-salary-max-amount' );
										} elseif ( 'range' === $salary_amount_type ) {
											delete_post_meta( $job_id, 'jb-salary-amount' );
										}
									}
								}

								if ( ! empty( $salary_type ) ) {
									$meta_input['jb-salary-type'] = $salary_type;
								}
								if ( ! empty( $salary_amount_type ) ) {
									$meta_input['jb-salary-amount-type'] = $salary_amount_type;
								}
								if ( ! empty( $job_amount ) ) {
									$meta_input['jb-salary-amount'] = $job_amount;
								}
								if ( isset( $job_min_amount ) ) {
									$meta_input['jb-salary-min-amount'] = $job_min_amount;
								}
								if ( isset( $job_max_amount ) ) {
									$meta_input['jb-salary-max-amount'] = $job_max_amount;
								}
								if ( ! empty( $job_period ) ) {
									$meta_input['jb-salary-period'] = $job_period;
								}

								foreach ( $meta_input as $metakey => $metavalue ) {
									if ( in_array( $metakey, $skip_meta_update, true ) ) {
										unset( $meta_input[ $metakey ] );
									}
								}
							}

							// make guest jobs secure per each wp_nonce(unique per session)
							if ( empty( $user_id ) ) {
								$meta_input['jb-guest-nonce'] = wp_create_nonce( $nonce_action );
							}

							$job_data = array(
								'post_type'    => 'jb-job',
								'post_author'  => $user_id,
								'post_parent'  => 0,
								'post_title'   => $title,
								'post_content' => $content,
								'post_status'  => $status,
								'meta_input'   => $meta_input,
							);

							/**
							 * Filters the job post data after when posting a new job or update existed.
							 *
							 * Note: Validation already passed!
							 *
							 * @since 1.0
							 * @hook jb_job_submitted_data
							 *
							 * @param {array}  $job_data     Job post data. See the list of all arguments https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters
							 * @param {object} $posting_form Frontend form class (\jb\frontend\Forms) instance.
							 *
							 * @return {array} Job post data.
							 */
							$job_data = apply_filters( 'jb_job_submitted_data', $job_data, $posting_form );

							if ( ! empty( $_GET['job-id'] ) ) {
								$job_id = absint( $_GET['job-id'] );
								$job    = get_post( $job_id );
								if ( empty( $job ) || is_wp_error( $job ) ) {
									$posting_form->add_error( 'global', __( 'Wrong job', 'jobboardwp' ) );
								} else {
									$job_data['ID'] = $job_id;
									wp_update_post( $job_data );
									if ( 'pending' === $job->post_status ) {
										update_post_meta( $job_id, 'jb-had-pending', true );
									}
								}
							} else {
								$job_id = wp_insert_post( $job_data );
							}

							if ( is_wp_error( $job_id ) ) {
								$posting_form->add_error( 'global', __( 'Job submission issue, Please try again', 'jobboardwp' ) );
							} else {

								/**
								 * Fires after submitted job data and pass validation through frontend job posting form.
								 *
								 * @since 1.2.3
								 * @hook jb_after_job_submitted_successfully
								 *
								 * @param {int}  $job_id    Job's ID.
								 * @param {bool} $is_edited New job or edit process.
								 */
								do_action( 'jb_after_job_submitted_successfully', $job_id, $is_edited );

								// $company_logo must be an image URL
								if ( ! empty( $company_logo ) ) {
									if ( $set_attachment ) {
										require_once ABSPATH . 'wp-admin/includes/image.php';
										require_once ABSPATH . 'wp-admin/includes/file.php';
										require_once ABSPATH . 'wp-admin/includes/media.php';

										$image_id = media_sideload_image( $company_logo, $job_id, null, 'id' );
										set_post_thumbnail( $job_id, $image_id );
									}
								} elseif ( $is_edited ) {
									if ( has_post_thumbnail( $job_id ) ) {
										$thumbnail_id = get_post_thumbnail_id( $job_id );
										if ( $thumbnail_id ) {
											wp_delete_attachment( $thumbnail_id, true );
										}
									}
								}

								$type_ids = '';
								if ( ! empty( $_POST['job_type'] ) ) {
									if ( is_array( $_POST['job_type'] ) ) {
										$type_ids = array_map( 'absint', $_POST['job_type'] );
									} else {
										$type_ids = array( absint( $_POST['job_type'] ) );
									}
								}
								wp_set_post_terms( $job_id, $type_ids, 'jb-job-type' );

								if ( JB()->options()->get( 'job-categories' ) ) {
									$categories = '';
									if ( ! empty( $_POST['job_category'] ) ) {
										$categories = array( absint( $_POST['job_category'] ) );
									}
									wp_set_post_terms( $job_id, $categories, 'jb-job-category' );
								}

								if ( ! $is_edited ) {
									/**
									 * Filters the company data meta for the current user only when posting a new job.
									 *
									 * Note: Job post is created on this moment. Validation already passed!
									 *
									 * @since 1.0
									 * @hook jb-save-job-user-company-data
									 *
									 * @param {array} $company_data Company data for the user meta.
									 * @param {int}   $job_id       Created Job postID.
									 *
									 * @return {array} Company data.
									 */
									$company_data = apply_filters(
										'jb-save-job-user-company-data', // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
										array(
											'name'      => $company_name,
											'website'   => $company_website,
											'tagline'   => $company_tagline,
											'twitter'   => $company_twitter,
											'facebook'  => $company_facebook,
											'instagram' => $company_instagram,
											'logo'      => $company_logo,
										),
										$job_id
									);

									JB()->common()->user()->set_company_data( $company_data );
								}
							}

							if ( ! $posting_form->has_errors() ) {
								if ( isset( $_POST['jb-job-submission-step'] ) && 'preview' === sanitize_key( $_POST['jb-job-submission-step'] ) ) {
									// redirect user to the preview page
									$url = JB()->common()->job()->get_preview_link( $job_id );
								} else {
									// redirect to an empty form and let the user know about a draft job is created
									$url = add_query_arg( array( 'msg' => 'draft' ), JB()->common()->permalinks()->get_predefined_page_link( 'job-post' ) );
								}
								wp_safe_redirect( $url );
								exit;
							}
						}

						break;
					case 'job-publishing':
						$preview_form = JB()->frontend()->forms( array( 'id' => 'jb-job-submission' ) );
						$preview_form->flush_errors();

						if ( empty( $_GET['job-id'] ) ) {
							$preview_form->add_error( 'global', __( 'Wrong job', 'jobboardwp' ) );
						}

						if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'jb-job-publishing' ) ) {
							$preview_form->add_error( 'global', __( 'Security issue, Please try again', 'jobboardwp' ) );
						}

						if ( ! isset( $_POST['jb-job-submission-step'] ) ) {
							$preview_form->add_error( 'global', __( 'Wrong action, Please try again', 'jobboardwp' ) );
						}

						$job_id = absint( $_GET['job-id'] );
						$job    = get_post( $job_id );
						if ( empty( $job ) || is_wp_error( $job ) ) {
							$preview_form->add_error( 'global', __( 'Wrong job', 'jobboardwp' ) );
						}

						if ( is_user_logged_in() ) {
							if ( ! current_user_can( 'edit_post', $job_id ) && absint( $job->post_author ) !== get_current_user_id() ) {
								$preview_form->add_error( 'global', __( 'Security action, Please try again with another job.', 'jobboardwp' ) );
							}
						} elseif ( ! isset( $_COOKIE['jb-guest-job-posting'] ) ) {
							$preview_form->add_error( 'global', __( 'Security action, Please try again with another job.', 'jobboardwp' ) );
						} else {
							$nonce_action    = 'jb-guest-job-posting' . sanitize_text_field( wp_unslash( $_COOKIE['jb-guest-job-posting'] ) );
							$job_guest_nonce = get_post_meta( $job_id, 'jb-guest-nonce', true );
							if ( empty( $job_guest_nonce ) || ! wp_verify_nonce( $job_guest_nonce, $nonce_action ) ) {
								$preview_form->add_error( 'global', __( 'Security action, Please try again with another job.', 'jobboardwp' ) );
							}
						}

						if ( 'publish' === sanitize_key( $_POST['jb-job-submission-step'] ) ) {

							$is_edited   = get_post_meta( $job_id, 'jb-last-edit-date', true );
							$was_pending = get_post_meta( $job_id, 'jb-had-pending', true );

							if ( ! empty( $is_edited ) && 0 === (int) JB()->options()->get( 'published-job-editing' ) ) {
								$preview_form->add_error( 'global', __( 'Security action, Please try again.', 'jobboardwp' ) );
							}

							$status = 'publish';
							if ( ! empty( $is_edited ) ) {
								if ( ! current_user_can( 'manage_options' ) ) {
									if ( 2 === (int) JB()->options()->get( 'published-job-editing' ) ) {
										if ( ! empty( $was_pending ) ) {
											$status = 'pending';
										}
									} elseif ( 1 === (int) JB()->options()->get( 'published-job-editing' ) ) {
										$status = 'pending';
									}
								}
							} else {
								$status = ( JB()->options()->get( 'job-moderation' ) && ! current_user_can( 'manage_options' ) ) ? 'pending' : 'publish';
							}

							if ( ! $preview_form->has_errors() ) {
								wp_update_post(
									array(
										'ID'          => $job_id,
										'post_status' => $status,
									)
								);

								update_post_meta( $job_id, 'jb-last-edit-date', time() );

								$emails = JB()->common()->mail()->multi_admin_email();

								if ( ! empty( $emails ) ) {
									$approve_job_nonce = wp_create_nonce( 'jb-approve-job' . $job_id );
									$approve_job_url   = add_query_arg(
										array(
											'jb_adm_action' => 'approve_job',
											'job-id'        => $job_id, // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
											'nonce'         => $approve_job_nonce, // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
										),
										admin_url()
									);
									global $current_user;
									$user_obj = $current_user;

									$template = ! empty( $is_edited ) ? 'job_edited' : 'job_submitted';

									$email_args = array(
										'job_id'          => $job_id,
										'job_title'       => $job->post_title,
										'job_details'     => JB()->common()->mail()->get_job_details( $job ),
										'view_job_url'    => get_permalink( $job ),
										'approve_job_url' => $approve_job_url,
										'edit_job_url'    => get_edit_post_link( $job_id ),
									);

									foreach ( $emails as $email ) {
										$user         = get_user_by( 'email', $email );
										$current_user = $user; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- is needed for getting correct job links in email content
										$author       = get_userdata( $job->post_author );

										$email_args['job_author'] = ! empty( $author ) ? $author->display_name : __( 'Guest', 'jobboardwp' );

										JB()->common()->mail()->send( $email, $template, $email_args );
									}
									$current_user = $user_obj; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- is needed for getting correct job links in email content
								}

								if ( ! empty( $is_edited ) ) {
									/**
									 * Fires after Job has been edited.
									 *
									 * @since 1.1.0
									 * @hook jb_job_edited
									 *
									 * @param {int}     $job_id Job post ID.
									 * @param {WP_Post} $job    Job post object.
									 */
									do_action( 'jb_job_edited', $job_id, $job );
								} else {
									/**
									 * Fires after Job has been published.
									 *
									 * @since 1.1.0
									 * @hook jb_job_published
									 *
									 * @param {int}     $job_id Job post ID.
									 * @param {WP_Post} $job    Job post object.
									 */
									do_action( 'jb_job_published', $job_id, $job );
								}

								$job_post_page_url = JB()->get_current_url( true );
								if ( empty( $is_edited ) && ! current_user_can( 'manage_options' ) && JB()->options()->get( 'job-moderation' ) ) {
									$url = add_query_arg( array( 'msg' => 'on-moderation' ), $job_post_page_url );
								} elseif ( ! empty( $is_edited ) && ! current_user_can( 'manage_options' ) && 1 === (int) JB()->options()->get( 'published-job-editing' ) ) {
									$url = add_query_arg( array( 'msg' => 'on-moderation' ), $job_post_page_url );
								} elseif ( ! empty( $is_edited ) && ! empty( $was_pending ) && ! current_user_can( 'manage_options' ) && 2 === (int) JB()->options()->get( 'published-job-editing' ) ) {
									$url = add_query_arg( array( 'msg' => 'on-moderation' ), $job_post_page_url );
								} else {
									$url = add_query_arg(
										array(
											'msg'          => 'published',
											'published-id' => $job_id,
										),
										$job_post_page_url
									);
								}

								JB()->setcookie( 'jb-guest-job-posting', false );

								wp_safe_redirect( $url );
								exit;
							}
						} elseif ( 'draft' === sanitize_key( wp_unslash( $_POST['jb-job-submission-step'] ) ) ) {
							if ( ! $preview_form->has_errors() ) {
								wp_update_post(
									array(
										'ID'          => $job_id,
										'post_status' => 'draft',
									)
								);

								//redirect to job's draft
								$url = JB()->common()->job()->get_edit_link( $job_id, JB()->get_current_url( true ) );
								wp_safe_redirect( $url );
								exit;
							}
						}

						break;
					case 'company-details':
						global $posting_form;

						$user_id = get_current_user_id();

						$posting_form = JB()->frontend()->forms( array( 'id' => 'jb-company-details' ) );

						$posting_form->flush_errors();

						if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'jb-company-details' ) ) {
							$posting_form->add_error( 'global', __( 'Security issue, Please try again', 'jobboardwp' ) );
						}

						// handle company details
						$company_name = '';
						if ( empty( $_POST['company_name'] ) ) {
							$posting_form->add_error( 'company_name', __( 'Company name cannot be empty', 'jobboardwp' ) );
						} else {
							$company_name = sanitize_text_field( wp_unslash( $_POST['company_name'] ) );
							if ( empty( $company_name ) ) {
								$posting_form->add_error( 'company_name', __( 'Company name cannot be empty', 'jobboardwp' ) );
							}
						}

						$company_website = ! empty( $_POST['company_website'] ) ? sanitize_text_field( wp_unslash( $_POST['company_website'] ) ) : '';
						if ( ! empty( $company_website ) ) {
							// Prefix http if needed.
							if ( false === strpos( $company_website, 'http:' ) && false === strpos( $company_website, 'https:' ) ) {
								$company_website = 'https://' . $company_website;
							}
							if ( ! filter_var( $company_website, FILTER_VALIDATE_URL ) ) {
								$posting_form->add_error( 'company_website', __( 'Company website is invalid', 'jobboardwp' ) );
							}
						}

						$company_tagline = ! empty( $_POST['company_tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['company_tagline'] ) ) : '';

						$company_twitter = ! empty( $_POST['company_twitter'] ) ? sanitize_text_field( wp_unslash( $_POST['company_twitter'] ) ) : '';
						if ( ! empty( $company_twitter ) ) {
							if ( 0 === strpos( $company_twitter, '@' ) ) {
								$company_twitter = substr( $company_twitter, 1 );
							}

							if ( ! empty( $company_twitter ) ) {

								$validate_company_twitter = $company_twitter;
								if ( false === strpos( $company_twitter, 'https://twitter.com/' ) ) {
									$validate_company_twitter = 'https://twitter.com/' . $company_twitter;
								}

								if ( ! filter_var( $validate_company_twitter, FILTER_VALIDATE_URL ) ) {
									$posting_form->add_error( 'company_twitter', __( 'Company Twitter is invalid', 'jobboardwp' ) );
								}
							}
						}

						$company_facebook = ! empty( $_POST['company_facebook'] ) ? sanitize_text_field( wp_unslash( $_POST['company_facebook'] ) ) : '';
						if ( ! empty( $company_facebook ) ) {
							$validate_company_facebook = $company_facebook;
							if ( false === strpos( $company_facebook, 'https://facebook.com/' ) ) {
								$validate_company_facebook = 'https://facebook.com/' . $company_facebook;
							}

							if ( ! filter_var( $validate_company_facebook, FILTER_VALIDATE_URL ) ) {
								$posting_form->add_error( 'company_facebook', __( 'Company Facebook is invalid', 'jobboardwp' ) );
							}
						}

						$company_instagram = ! empty( $_POST['company_instagram'] ) ? sanitize_text_field( wp_unslash( $_POST['company_instagram'] ) ) : '';
						if ( ! empty( $company_instagram ) ) {
							$validate_company_instagram = $company_instagram;
							if ( false === strpos( $company_instagram, 'https://instagram.com/' ) ) {
								$validate_company_instagram = 'https://instagram.com/' . $company_instagram;
							}

							if ( ! filter_var( $validate_company_instagram, FILTER_VALIDATE_URL ) ) {
								$posting_form->add_error( 'company_instagram', __( 'Company Instagram is invalid', 'jobboardwp' ) );
							}
						}

						$company_logo = '';
						if ( ! empty( $_POST['company_logo'] ) && ! empty( $_POST['company_logo_hash'] ) ) {
							// The new company logo has been uploaded, so we need to update the current user logo
							if ( md5( sanitize_file_name( wp_unslash( $_POST['company_logo'] ) ) . '_jb_uploader_security_salt' ) !== sanitize_key( wp_unslash( $_POST['company_logo_hash'] ) ) ) {
								// invalid salt for company logo, it's for the security enhancements
								$posting_form->add_error( 'company_logo', __( 'Something wrong with image, please re-upload', 'jobboardwp' ) );
							} else {
								if ( ! $wp_filesystem instanceof WP_Filesystem_Base ) {
									require_once ABSPATH . 'wp-admin/includes/file.php';

									$credentials = request_filesystem_credentials( site_url() );
									WP_Filesystem( $credentials );
								}

								$company_logo_temp = sanitize_file_name( wp_unslash( $_POST['company_logo'] ) );

								if ( is_multisite() ) {
									$main_blog = get_network()->site_id;

									$current_blog_url = get_bloginfo( 'url' );
									switch_to_blog( $main_blog );
									$main_blog_url = get_bloginfo( 'url' );
									restore_current_blog();

									$logos_dir = JB()->common()->filesystem()->get_upload_dir( 'jobboardwp/logos', $main_blog );

									$logos_url = JB()->common()->filesystem()->get_upload_url( 'jobboardwp/logos', $main_blog );
									if ( $current_blog_url !== $main_blog_url ) {
										$logos_url = str_replace( $current_blog_url, $main_blog_url, $logos_url );
									}
								} else {
									$logos_dir = JB()->common()->filesystem()->get_upload_dir( 'jobboardwp/logos' );
									$logos_url = JB()->common()->filesystem()->get_upload_url( 'jobboardwp/logos' );
								}

								// replace the company logo inside user logos dir to the uploaded to the temp upload folder image
								$type    = wp_check_filetype( $company_logo_temp );
								$newname = wp_normalize_path( $logos_dir . DIRECTORY_SEPARATOR . $user_id . '.' . $type['ext'] );
								$oldname = wp_normalize_path( JB()->common()->filesystem()->temp_upload_dir . DIRECTORY_SEPARATOR . $company_logo_temp );

								if ( file_exists( $oldname ) && $wp_filesystem->move( $oldname, $newname, true ) ) {
									$company_logo = trailingslashit( $logos_url ) . $user_id . '.' . $type['ext'];
								}
							}
						} elseif ( ! empty( $_POST['company_logo'] ) ) {
							// post a job with a regular company logo that hasn't been changed when posting a job
							$company_logo_post = sanitize_text_field( wp_unslash( $_POST['company_logo'] ) );

							if ( ! filter_var( $company_logo_post, FILTER_VALIDATE_URL ) ) {
								// company logo must be a URL
								$posting_form->add_error( 'company_logo', __( 'Wrong image URL. Invalid URL', 'jobboardwp' ) );

							} else {

								$type = wp_check_filetype( $company_logo_post );
								if ( is_multisite() ) {
									$main_blog = get_network()->site_id;

									$current_blog_url = get_bloginfo( 'url' );
									switch_to_blog( $main_blog );
									$main_blog_url = get_bloginfo( 'url' );
									restore_current_blog();

									$logos_url = JB()->common()->filesystem()->get_upload_url( 'jobboardwp/logos', $main_blog );
									if ( $current_blog_url !== $main_blog_url ) {
										$logos_url = str_replace( $current_blog_url, $main_blog_url, $logos_url );
									}
								} else {
									$logos_url = JB()->common()->filesystem()->get_upload_url( 'jobboardwp/logos' );
								}

								// check if the company logo gets from the job post attachment
								if ( trailingslashit( $logos_url ) . $user_id . '.' . $type['ext'] !== $company_logo_post ) {

									if ( empty( $_GET['job-id'] ) ) {

										$posting_form->add_error( 'company_logo', __( 'Wrong image URL. Invalid job', 'jobboardwp' ) );

									} else {
										// case when does a job have own thumbnail
										$attachment_id = get_post_thumbnail_id( absint( $_GET['job-id'] ) );
										if ( ! $attachment_id ) {

											$posting_form->add_error( 'company_logo', __( 'Wrong image URL. Invalid attachment ID', 'jobboardwp' ) );

										} else {

											$image = wp_get_attachment_image_src( $attachment_id );

											if ( ! isset( $image[0] ) || $company_logo_post !== $image[0] ) {
												$posting_form->add_error( 'company_logo', __( 'Wrong image URL. Invalid attachment path', 'jobboardwp' ) );
											} else {
												$company_logo = $company_logo_post;
											}
										}
									}
								} else {
									// case when we get the company logo from the employer image
									$company_logo = $company_logo_post;
								}
							}
						}

						/**
						 * Fires after JobBoardWP native company details validations are completed.
						 *
						 * Note: Use this hook for adding custom validations to your Company Details.
						 *
						 * @since 1.2.6
						 * @hook jb_company_details_validation
						 *
						 * @param {object} $posting_form Frontend form class (\jb\frontend\Forms) instance.
						 * @param {int}    $user_id      Job author ID.
						 */
						do_action( 'jb_company_details_validation', $posting_form, $user_id );

						if ( ! $posting_form->has_errors() ) {
							update_user_meta( $user_id, 'jb_company_name', $company_name );
							update_user_meta( $user_id, 'jb_company_website', $company_website );
							update_user_meta( $user_id, 'jb_company_tagline', $company_tagline );
							update_user_meta( $user_id, 'jb_company_twitter', $company_twitter );
							update_user_meta( $user_id, 'jb_company_facebook', $company_facebook );
							update_user_meta( $user_id, 'jb_company_instagram', $company_instagram );
							update_user_meta( $user_id, 'jb_company_logo', $company_logo );

							$company_details_page_url = JB()->common()->permalinks()->get_predefined_page_link( 'jb-company-details' );
							$company_details_page_url = add_query_arg(
								array( 'msg' => 'updated' ),
								$company_details_page_url
							);
							wp_safe_redirect( $company_details_page_url );
							exit;
						}

						break;
				}
			}
		}
	}
}
