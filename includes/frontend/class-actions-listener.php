<?php namespace jb\frontend;


if ( ! defined( 'ABSPATH' ) ) exit;


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
		function __construct() {
			add_action( 'wp_loaded', [ $this, 'actions_listener' ], 10 );
			add_filter( 'jb_job_submitted_data', [ $this, 'add_location_data' ], 10, 2 );
		}


		/**
		 * Parse and save location data
		 *
		 * @param array $job_data
		 * @param array $posting_form
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		function add_location_data( $job_data, $posting_form ) {
			$key = JB()->options()->get( 'googlemaps-api-key' );
			if ( empty( $key ) || empty( $_POST['job_location_data'] ) ) {
				return $job_data;
			}

			$location_data = json_decode( stripslashes( $_POST['job_location_data'] ) );

			$job_data['meta_input']['jb-location-raw-data'] = $location_data;
			$job_data['meta_input']['jb-location-lat']               = sanitize_text_field( $location_data->geometry->location->lat );
			$job_data['meta_input']['jb-location-long']              = sanitize_text_field( $location_data->geometry->location->lng );
			$job_data['meta_input']['jb-location-formatted-address'] = sanitize_text_field( $location_data->formatted_address );

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
		}


		/**
		 * Handle posting job form and maybe create user if the form data is proper
		 * $_POST validation on form submission
		 *
		 * @return int|\WP_Error
		 *
		 * @since 1.0
		 */
		function maybe_create_user() {
			/**
			 * @var $posting_form \jb\frontend\Forms
			 */
			global $posting_form;

			$user_id = get_current_user_id();

			if ( ! is_user_logged_in() ) {
				$username = '';
				$password = '';
				$author_email = '';
				$author_fname = ! empty( $_POST['author_first_name'] ) ? sanitize_text_field( $_POST['author_first_name'] ) : '';
				$author_lname = ! empty( $_POST['author_last_name'] ) ? sanitize_text_field( $_POST['author_last_name'] ) : '';;

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
					$author_email = sanitize_email( trim( $_POST['author_email'] ) );

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
						$password = sanitize_text_field( trim( $_POST['author_password'] ) );
						$password_confirm = sanitize_text_field( trim( $_POST['author_password_confirm'] ) );

						if ( $password != $password_confirm ) {
							$posting_form->add_error( 'author_password_confirm', __( 'Your passwords do not match', 'jobboardwp' ) );
						}
					}
				} else {
					// User is forced to set up account with email sent to them. This password will remain a secret.
					$password = wp_generate_password();
					$notify = 'both';
				}

				if ( ! JB()->options()->get( 'account-username-generate' ) ) {

					if ( empty( $_POST['author_username'] ) ) {
						$posting_form->add_error( 'author_username', __( 'Username is required', 'jobboardwp' ) );
					} else {
						$username = sanitize_user( trim( $_POST['author_username'] ) );
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
						$append ++;
					}
				}

				if ( ! $posting_form->has_errors() ) {
					// Create account.
					$userdata = [
						'user_login'    => $username,
						'user_pass'     => $password,
						'user_email'    => $author_email,
						'role'          => JB()->options()->get( 'account-role' ),
						'first_name'    => $author_fname,
						'last_name'     => $author_lname,
					];
					$userdata = apply_filters( 'jb_job_submission_create_account_data', $userdata );

					$user_id = wp_insert_user( $userdata );

					// Login here
					add_action( 'set_logged_in_cookie', [ $this, 'update_global_login_cookie' ] );
					wp_set_auth_cookie( $user_id, true, is_ssl() );
					wp_set_current_user( $user_id );
					remove_action( 'set_logged_in_cookie', [ $this, 'update_global_login_cookie' ] );

					//Notify admin or user + admin about new user registration
					wp_new_user_notification( $user_id, null, $notify );
				}
			} else {
				if ( JB()->options()->get( 'your-details-section' ) == '1' ) {
					$author_fname = ! empty( $_POST['author_first_name'] ) ? sanitize_text_field( $_POST['author_first_name'] ) : '';
					$author_lname = ! empty( $_POST['author_last_name'] ) ? sanitize_text_field( $_POST['author_last_name'] ) : '';;

					$current_userdata = get_userdata( $user_id );
					$last_email = $current_userdata->user_email;

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
						$author_email = sanitize_email( trim( $_POST['author_email'] ) );

						if ( $last_email != $author_email ) {
							if ( ! is_email( $author_email ) ) {
								$posting_form->add_error( 'author_email', __( 'Wrong email address format', 'jobboardwp' ) );
							}

							if ( email_exists( $author_email ) ) {
								$posting_form->add_error( 'author_email', __( 'Please use another email address', 'jobboardwp' ) );
							}
						}
					}

					if ( ! $posting_form->has_errors() ) {
						// Create account.
						$userdata = [
							'ID'            => $user_id,
							'user_email'    => $author_email,
							'first_name'    => $author_fname,
							'last_name'     => $author_lname,
						];
						$userdata = apply_filters( 'jb_job_submission_update_account_data', $userdata );

						wp_update_user( $userdata );
					}
				}
			}

			return $user_id;
		}



		/**
		 * Allows for immediate access to the logged in cookie after mid-request login.
		 *
		 * @param string $logged_in_cookie Logged in cookie.
		 *
		 * @since 1.0
		 */
		function update_global_login_cookie( $logged_in_cookie ) {
			$_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
		}


		/**
		 * Main frontend action listener
		 *
		 * @since 1.0
		 */
		function actions_listener() {
			if ( ! empty( $_POST['jb-action'] ) ) {
				switch ( $_POST['jb-action'] ) {
					case 'job-submission': {

						global $posting_form;

						$posting_form = JB()->frontend()->forms( [ 'id' => 'jb-job-submission', ] );

						$posting_form->flush_errors();

						if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'jb-job-submission' ) ) {
							$posting_form->add_error( 'global', __( 'Security issue, Please try again', 'jobboardwp' ) );
						}

						if ( ! isset( $_POST['jb-job-submission-step'] ) ||
						     ( $_POST['jb-job-submission-step'] == 'draft' && ! is_user_logged_in() &&
						       ! JB()->options()->get( 'account-creation' ) && ! JB()->options()->get( 'account-required' ) )
							) {
							$posting_form->add_error( 'global', __( 'You cannot save draft jobs, Please try again', 'jobboardwp' ) );
						}

						// register user if it's needed
						$user_id = $this->maybe_create_user();

						// handle job details fields
						$title = '';
						$content = '';
						$app_contact = '';
						$company_name = '';

						if ( empty( $_POST['job_title'] ) ) {
							$posting_form->add_error( 'job_title', __( 'Job title cannot be empty', 'jobboardwp' ) );
						} else {
							$title = sanitize_text_field( $_POST['job_title'] );
							if ( empty( $title ) ) {
								$posting_form->add_error( 'job_title', __( 'Job title cannot be empty', 'jobboardwp' ) );
							}
						}

						if ( empty( $_POST['job_description'] ) ) {
							$posting_form->add_error( 'job_description', __( 'Job description cannot be empty', 'jobboardwp' ) );
						} else {
							$content = sanitize_textarea_field( $_POST['job_description'] );
							if ( empty( $content ) ) {
								$posting_form->add_error( 'job_description', __( 'Job description cannot be empty', 'jobboardwp' ) );
							}
						}

						if ( empty( $_POST['job_application'] ) ) {
							$posting_form->add_error( 'job_application', __( 'Application contact cannot be empty', 'jobboardwp' ) );
						} else {
							$app_contact = $_POST['job_application'];

							switch ( JB()->options()->get( 'application-method' ) ) {

								case 'email':
									if ( ! is_email( $app_contact ) ) {
										$posting_form->add_error( 'job_application', __( 'Job application must be an email address', 'jobboardwp' ) );
									}

									$app_contact = sanitize_email( $app_contact );
									break;
								case 'url':
									// Prefix http if needed.
									if ( ! strstr( $app_contact, 'http:' ) && ! strstr( $app_contact, 'https:' ) ) {
										$app_contact = 'http://' . $app_contact;
									}
									if ( ! filter_var( $app_contact, FILTER_VALIDATE_URL ) ) {
										$posting_form->add_error( 'job_application', __( 'Job application must be an URL', 'jobboardwp' ) );
									}
									break;
								default:
									if ( ! is_email( $app_contact ) ) {
										// Prefix http if needed.
										if ( ! strstr( $app_contact, 'http:' ) && ! strstr( $app_contact, 'https:' ) ) {
											$app_contact = 'http://' . $app_contact;
										}
										if ( ! filter_var( $app_contact, FILTER_VALIDATE_URL ) ) {
											$posting_form->add_error( 'job_application', __( 'Job application must be an email address or URL', 'jobboardwp' ) );
										}
									} else {
										$app_contact = sanitize_email( $app_contact );
									}

									break;
							}
						}

						$location_type = '0';
						$location = '';
						if ( ! isset( $_POST['job_location_type'] ) ) {
							$posting_form->add_error( 'job_location', __( 'Job location type invalid', 'jobboardwp' ) );
						} else {
							$location_type = sanitize_text_field( $_POST['job_location_type'] );
							if ( $location_type === '0' ) {
								if ( empty( $_POST['job_location'] ) ) {
									$posting_form->add_error( 'job_location', __( 'Location for onsite job is required', 'jobboardwp' ) );
								} else {
									$location = sanitize_text_field( $_POST['job_location'] );
								}
							} else {
								$location = ! empty( $_POST['job_location'] ) ? sanitize_text_field( $_POST['job_location'] ) : '';
							}
						}


						if ( JB()->options()->get( 'required-job-type' ) ) {
							if ( ! isset( $_POST['job_type'] ) || empty( $_POST['job_type'] ) ) {
								$posting_form->add_error( 'job_type', __( 'Job type is required', 'jobboardwp' ) );
							}
						}

						// handle company details
						if ( empty( $_POST['company_name'] ) ) {
							$posting_form->add_error( 'company_name', __( 'Company name cannot be empty', 'jobboardwp' ) );
						} else {
							$company_name = sanitize_text_field( $_POST['company_name'] );
							if ( empty( $company_name ) ) {
								$posting_form->add_error( 'company_name', __( 'Company name cannot be empty', 'jobboardwp' ) );
							}
						}

						$company_website = ! empty( $_POST['company_website'] ) ? sanitize_text_field( $_POST['company_website'] ) : '';
						if ( ! empty( $company_website ) ) {
							// Prefix http if needed.
							if ( ! strstr( $company_website, 'http:' ) && ! strstr( $company_website, 'https:' ) ) {
								$company_website = 'http://' . $company_website;
							}
							if ( ! filter_var( $company_website, FILTER_VALIDATE_URL ) ) {
								$posting_form->add_error( 'company_website', __( 'Company website is invalid', 'jobboardwp' ) );
							}
						}

						$company_tagline = ! empty( $_POST['company_tagline'] ) ? sanitize_text_field( $_POST['company_tagline'] ) : '';

						$company_twitter = ! empty( $_POST['company_twitter'] ) ? sanitize_text_field( $_POST['company_twitter'] ) : '';
						if ( ! empty( $company_twitter ) ) {
							if ( 0 === strpos( $company_twitter, '@' ) ) {
								$company_twitter = substr( $company_twitter, 1 );
							}

							if ( ! empty( $company_twitter ) ) {

								$validate_company_twitter = $company_twitter;
								if ( ! strstr( $company_twitter, 'https://twitter.com/' ) ) {
									$validate_company_twitter = 'https://twitter.com/' . $company_twitter;
								}

								if ( ! filter_var( $validate_company_twitter, FILTER_VALIDATE_URL ) ) {
									$posting_form->add_error( 'company_twitter', __( 'Company Twitter is invalid', 'jobboardwp' ) );
								}
							}
						}

						$company_facebook = ! empty( $_POST['company_facebook'] ) ? sanitize_text_field( $_POST['company_facebook'] ) : '';
						if ( ! empty( $company_facebook ) ) {
							$validate_company_facebook = $company_facebook;
							if ( ! strstr( $company_facebook, 'https://facebook.com/' ) ) {
								$validate_company_facebook = 'https://facebook.com/' . $company_facebook;
							}

							if ( ! filter_var( $validate_company_facebook, FILTER_VALIDATE_URL ) ) {
								$posting_form->add_error( 'company_facebook', __( 'Company Facebook is invalid', 'jobboardwp' ) );
							}
						}

						$company_instagram = ! empty( $_POST['company_instagram'] ) ? sanitize_text_field( $_POST['company_instagram'] ) : '';
						if ( ! empty( $company_instagram ) ) {
							$validate_company_instagram = $company_instagram;
							if ( ! strstr( $company_instagram, 'https://instagram.com/' ) ) {
								$validate_company_instagram = 'https://instagram.com/' . $company_instagram;
							}

							if ( ! filter_var( $validate_company_instagram, FILTER_VALIDATE_URL ) ) {
								$posting_form->add_error( 'company_instagram', __( 'Company Instagram is invalid', 'jobboardwp' ) );
							}
						}

						$status = 'draft';
						if ( isset( $_POST['jb-job-submission-step'] ) && $_POST['jb-job-submission-step'] == 'preview' ) {
							$status = 'jb-preview';
						}

						$company_logo = '';
						if ( ! empty( $_POST['company_logo'] ) && ! empty( $_POST['company_logo_hash'] ) ) {
							if ( $_POST['company_logo_hash'] !== md5( $_POST['company_logo'] . '_jb_uploader_security_salt' ) ) {
								$posting_form->add_error( 'company_logo', __( 'Something wrong with image, please re-upload', 'jobboardwp' ) );
							} else {
								$company_logo_temp = $_POST['company_logo'];

								$logos_dir = JB()->common()->filesystem()->get_upload_dir( 'jobboardwp/logos', 'allow' );
								$logos_url = JB()->common()->filesystem()->get_upload_url( 'jobboardwp/logos' );

								$type = wp_check_filetype( $company_logo_temp );
								$newname = $logos_dir . DIRECTORY_SEPARATOR . $user_id . '.' . $type['ext'];
								if ( file_exists( JB()->common()->filesystem()->temp_upload_dir . DIRECTORY_SEPARATOR . $company_logo_temp ) &&
								     rename( JB()->common()->filesystem()->temp_upload_dir . DIRECTORY_SEPARATOR . $company_logo_temp, $newname ) ) {
									$company_logo = $logos_url . '/' . $user_id . '.' . $type['ext'];
								}
							}
						} elseif ( ! empty( $_POST['company_logo'] ) ) {
							$company_logo_post = ! empty( $_POST['company_logo'] ) ? sanitize_text_field( $_POST['company_logo'] ) : '';

							if ( ! filter_var( $company_logo_post, FILTER_VALIDATE_URL ) ) {

								$posting_form->add_error( 'company_logo', __( 'Wrong image URL', 'jobboardwp' ) );

							} else {

								$type = wp_check_filetype( $company_logo_post );
								$logos_url = JB()->common()->filesystem()->get_upload_url( 'jobboardwp/logos' );

								if ( $company_logo_post != $logos_url . '/' . $user_id . '.' . $type['ext'] ) {

									if ( empty( $_GET['job-id'] ) ) {

										$posting_form->add_error( 'company_logo', __( 'Wrong image URL', 'jobboardwp' ) );

									} else {

										$attachment_id = get_post_thumbnail_id( absint( $_GET['job-id'] ) );
										if ( ! $attachment_id ) {

											$posting_form->add_error( 'company_logo', __( 'Wrong image URL', 'jobboardwp' ) );

										} else {

											$image = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );

											if ( ! isset( $image[0] ) || $company_logo_post != $image[0] ) {
												$posting_form->add_error( 'company_logo', __( 'Wrong image URL', 'jobboardwp' ) );
											} else {
												$company_logo = $company_logo_post;
											}

										}
									}

								} else {
									$company_logo = $company_logo_post;
								}

							}
						}

						if ( ! $posting_form->has_errors() ) {

							$expiry = JB()->common()->job()->calculate_expiry();

							$job_data = [
								'post_type'         => 'jb-job',
								'post_author'       => $user_id,
								'post_parent'       => 0,
								'post_title'        => $title,
								'post_content'      => $content,
								'post_status'       => $status,
								'meta_input'        => [
									'jb-location-type'          => $location_type,
									'jb-location'               => $location,
									'jb-application-contact'    => $app_contact,
									'jb-company-name'           => $company_name,
									'jb-company-website'        => $company_website,
									'jb-company-tagline'        => $company_tagline,
									'jb-company-twitter'        => $company_twitter,
									'jb-company-facebook'       => $company_facebook,
									'jb-company-instagram'      => $company_instagram,
									'jb-expiry-date'            => $expiry,
								],
							];

							$job_data = apply_filters( 'jb_job_submitted_data', $job_data, $posting_form );

							if ( ! empty( $_GET['job-id'] ) ) {
								$job_id = absint( $_GET['job-id'] );
								$job = get_post( $job_id );
								if ( is_wp_error( $job ) || empty( $job ) ) {
									$posting_form->add_error( 'global', __( 'Wrong job', 'jobboardwp' ) );
								} else {
									$job_data['ID'] = $job_id;
									wp_update_post( $job_data );
									if ( $job->post_status == 'pending' ) {
										update_post_meta( $job_id, 'jb-had-pending', true );
									}
								}
							} else {
								$job_id = wp_insert_post( $job_data );
							}

							if ( is_wp_error( $job_id ) ) {
								$posting_form->add_error( 'global', __( 'Job submission issue, Please try again', 'jobboardwp' ) );
							} else {

								if ( ! empty( $company_logo ) ) {

									require_once ABSPATH . 'wp-admin/includes/image.php';
									require_once ABSPATH . 'wp-admin/includes/file.php';
									require_once ABSPATH . 'wp-admin/includes/media.php';

									$image_id = media_sideload_image( $company_logo, $job_id, null, 'id' );
									set_post_thumbnail( $job_id, $image_id );
								}

								if ( ! empty( $_POST['job_type'] ) ) {
									if ( is_array( $_POST['job_type'] ) ) {
										$type_ids = array_map( 'absint', $_POST['job_type'] );
									} else {
										$type_ids = [ absint( $_POST['job_type'] ) ];
									}
									wp_set_post_terms( $job_id, $type_ids, 'jb-job-type' );
								}

								if ( JB()->options()->get( 'job-categories' ) && ! empty( $_POST['job_category'] ) ) {
									$categories = [ absint( $_POST['job_category'] ) ];
									wp_set_post_terms( $job_id, $categories, 'jb-job-category' );
								}

								if ( empty( $_GET['job-id'] ) ) {
									JB()->common()->user()->set_company_data( [
										'name'      => $company_name,
										'website'   => $company_website,
										'tagline'   => $company_tagline,
										'twitter'   => $company_twitter,
										'facebook'  => $company_facebook,
										'instagram' => $company_instagram,
										'logo'      => $company_logo,
									] );
								}
							}

							if ( ! $posting_form->has_errors() ) {

								if ( isset( $_POST['jb-job-submission-step'] ) && $_POST['jb-job-submission-step'] == 'preview' ) {
									// redirect user to the preview page
									$url = JB()->common()->job()->get_preview_link( $job_id );
								} else {
									// redirect to empty form and let the user know about draft job is created
									$url = add_query_arg( [ 'msg' => 'draft' ], JB()->common()->permalinks()->get_preset_page_link( 'job-post' ) );
								}
								exit( wp_redirect( $url ) );
							}
						}

						break;
					}

					case 'job-publishing':

						$preview_form = JB()->frontend()->forms( [ 'id' => 'jb-job-submission', ] );
						$preview_form->flush_errors();

						if ( empty( $_GET['job-id'] ) ) {
							$preview_form->add_error( 'global', __( 'Wrong job', 'jobboardwp' ) );
						}

						if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'jb-job-publishing' ) ) {
							$preview_form->add_error( 'global', __( 'Security issue, Please try again', 'jobboardwp' ) );
						}

						if ( ! isset( $_POST['jb-job-submission-step'] ) ) {
							$preview_form->add_error( 'global', __( 'Wrong action, Please try again', 'jobboardwp' ) );
						}

						$job_id = absint( $_GET['job-id'] );
						$job = get_post( $job_id );
						if ( is_wp_error( $job ) || empty( $job ) ) {
							$preview_form->add_error( 'global', __( 'Wrong job', 'jobboardwp' ) );
						}

						if ( $_POST['jb-job-submission-step'] == 'publish' ) {

							$is_edited = get_post_meta( $job_id, 'jb-last-edit-date', true );
							$was_pending = get_post_meta( $job_id, 'jb-had-pending', true );

							if ( ! empty( $is_edited ) && JB()->options()->get( 'published-job-editing' ) == '0' ) {
								$preview_form->add_error( 'global', __( 'Security action, Please try again.', 'jobboardwp' ) );
							}

							if ( ! empty( $is_edited ) ) {
								if ( JB()->options()->get( 'published-job-editing' ) == '2' ) {
									$status = 'publish';
									if ( ! empty( $was_pending ) ) {
										$status = 'pending';
									}
								} elseif ( JB()->options()->get( 'published-job-editing' ) == '1' ) {
									$status = 'pending';
								}
							} else {
								$status = JB()->options()->get( 'job-moderation' ) ? 'pending' : 'publish';
							}

							if ( ! $preview_form->has_errors() ) {
								wp_update_post( [
									'ID'            => $job_id,
									'post_status'   => $status,
								] );

								update_post_meta( $job_id, 'jb-last-edit-date', time() );

								if ( ! empty( $is_edited ) ) {
									$emails = JB()->common()->mail()->multi_admin_email();
									if ( ! empty( $emails ) ) {
										foreach ( $emails as $email ) {
											JB()->common()->mail()->send( $email, 'job_edited', [
												'job_id'            => $job_id,
												'job_title'         => $job->post_title,
												'job_details'       => JB()->common()->mail()->get_job_details( $job ),
												'view_job_url'      => get_permalink( $job ),
												'approve_job_url'   => add_query_arg( [
													'jb_adm_action' => 'approve_job',
													'job-id'        => $job_id,
													'nonce'         => wp_create_nonce( 'jb-approve-job' . $job_id ),
												], admin_url() ),
												'trash_job_url'     => get_delete_post_link( $job_id ),
											] );
										}
									}
								} else {
									$emails = JB()->common()->mail()->multi_admin_email();
									if ( ! empty( $emails ) ) {
										foreach ( $emails as $email ) {
											JB()->common()->mail()->send( $email, 'job_submitted', [
												'job_id'            => $job_id,
												'job_details'       => JB()->common()->mail()->get_job_details( $job ),
												'view_job_url'      => get_permalink( $job ),
												'approve_job_url'   => add_query_arg( [
													'jb_adm_action' => 'approve_job',
													'job-id'        => $job_id,
													'nonce'         => wp_create_nonce( 'jb-approve-job' . $job_id ),
												], admin_url() ),
												'trash_job_url'     => get_delete_post_link( $job_id ),
											] );
										}
									}
								}

								$job_post_page_url = JB()->common()->permalinks()->get_preset_page_link( 'job-post' );
								if ( empty( $is_edited ) && JB()->options()->get( 'job-moderation' ) ) {
									$url = add_query_arg( [ 'msg' => 'on-moderation' ], $job_post_page_url );
								} elseif ( ! empty( $is_edited ) && JB()->options()->get( 'published-job-editing' ) == '1' ) {
									$url = add_query_arg( [ 'msg' => 'on-moderation' ], $job_post_page_url );
								} else {
									$url = add_query_arg( [ 'msg' => 'published', 'published-id' => $job_id ], $job_post_page_url );
								}

								exit( wp_redirect( $url ) );
							}
						} elseif ( $_POST['jb-job-submission-step'] == 'draft' ) {

							if ( ! $preview_form->has_errors() ) {
								wp_update_post( [
									'ID'            => $job_id,
									'post_status'   => 'draft',
								] );

								//redirect to job's draft
								$url = JB()->common()->job()->get_edit_link( $job_id );
								exit( wp_redirect( $url ) );
							}

						}

						break;
				}
			}
		}

	}
}