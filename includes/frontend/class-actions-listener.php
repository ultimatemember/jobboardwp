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
		}


		/**
		 *
		 */
		function actions_listener() {
			if ( ! empty( $_POST['jb-action'] ) ) {
				switch ( $_POST['jb-action'] ) {
					case 'job-submission': {
						JB()->frontend()->forms()->flush_errors();

						if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'jb-job-submission' ) ) {
							JB()->frontend()->forms()->add_error( 'global', __( 'Security issue, Please try again', 'jobboardwp' ) );
						}

						$user_id = get_current_user_id();
						if ( ! is_user_logged_in() ) {

							$username = '';
							$password = '';
							$author_email = '';
							$author_fname = ! empty( $_POST['author_first_name'] ) ? sanitize_text_field( $_POST['author_first_name'] ) : '';
							$author_lname = ! empty( $_POST['author_last_name'] ) ? sanitize_text_field( $_POST['author_last_name'] ) : '';;

							if ( empty( $_POST['author_email'] ) ) {
								JB()->frontend()->forms()->add_error( 'author_email', __( 'Please fill email address', 'jobboardwp' ) );
							} else {
								$author_email = sanitize_email( trim( $_POST['author_email'] ) );

								if ( ! is_email( $author_email ) ) {
									JB()->frontend()->forms()->add_error( 'author_email', __( 'Wrong email address format', 'jobboardwp' ) );
								}

								if ( email_exists( $author_email ) ) {
									JB()->frontend()->forms()->add_error( 'author_email', __( 'Please use another email address', 'jobboardwp' ) );
								}
							}

							$notify = 'admin';
							if ( ! JB()->options()->get( 'account-password-email' ) ) {
								if ( empty( $_POST['author_password'] ) || empty( $_POST['author_password_confirm'] ) ) {
									if ( empty( $_POST['author_password'] ) ) {
										JB()->frontend()->forms()->add_error( 'author_password', __( 'Password is required', 'jobboardwp' ) );
									}

									if ( empty( $_POST['author_password_confirm'] ) ) {
										JB()->frontend()->forms()->add_error( 'author_password_confirm', __( 'Please confirm the password', 'jobboardwp' ) );
									}
								} else {
									$password = sanitize_text_field( trim( $_POST['author_password'] ) );
									$password_confirm = sanitize_text_field( trim( $_POST['author_password_confirm'] ) );

									if ( $password != $password_confirm ) {
										JB()->frontend()->forms()->add_error( 'author_password_confirm', __( 'Your passwords do not match', 'jobboardwp' ) );
									}
								}
							} else {
								// User is forced to set up account with email sent to them. This password will remain a secret.
								$password = wp_generate_password();
								$notify = 'both';
							}

							if ( ! JB()->options()->get( 'account-username-generate' ) ) {

								if ( empty( $_POST['author_username'] ) ) {
									JB()->frontend()->forms()->add_error( 'author_username', __( 'Username is required', 'jobboardwp' ) );
								} else {
									$username = sanitize_user( trim( $_POST['author_username'] ) );
									if ( username_exists( $username ) ) {
										JB()->frontend()->forms()->add_error( 'author_username', __( 'Please use another username', 'jobboardwp' ) );
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

							if ( ! JB()->frontend()->forms()->has_errors() ) {
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

								// Login.
								wp_signon( [
									'user_login'    => $userdata['user_login'],
									'user_password' => $userdata['user_pass'],
									'remember'      => true,
								] );
								wp_set_current_user( $user_id );

								//Notify admin or user + admin about new user registration
								wp_new_user_notification( $user_id, null, $notify );
							}
						}

						$title = '';
						$content = '';
						$app_contact = '';
						$company_name = '';

						if ( empty( $_POST['job_title'] ) ) {
							JB()->frontend()->forms()->add_error( 'job_title', __( 'Job title cannot be empty', 'jobboardwp' ) );
						} else {
							$title = sanitize_text_field( $_POST['job_title'] );
							if ( empty( $title ) ) {
								JB()->frontend()->forms()->add_error( 'job_title', __( 'Job title cannot be empty', 'jobboardwp' ) );
							}
						}

						if ( empty( $_POST['job_description'] ) ) {
							JB()->frontend()->forms()->add_error( 'job_description', __( 'Job description cannot be empty', 'jobboardwp' ) );
						} else {
							$content = sanitize_textarea_field( $_POST['job_description'] );
							if ( empty( $content ) ) {
								JB()->frontend()->forms()->add_error( 'job_description', __( 'Job description cannot be empty', 'jobboardwp' ) );
							}
						}

						if ( empty( $_POST['job_application'] ) ) {
							JB()->frontend()->forms()->add_error( 'job_application', __( 'Application contact cannot be empty', 'jobboardwp' ) );
						} else {
							$app_contact = $_POST['job_application'];

							switch ( JB()->options()->get( 'application-method' ) ) {

								case 'email':
									if ( ! is_email( $app_contact ) ) {
										JB()->frontend()->forms()->add_error( 'job_application', __( 'Job application must be an email address', 'jobboardwp' ) );
									}

									$app_contact = sanitize_email( $app_contact );
									break;
								case 'url':
									// Prefix http if needed.
									if ( ! strstr( $app_contact, 'http:' ) && ! strstr( $app_contact, 'https:' ) ) {
										$app_contact = 'http://' . $app_contact;
									}
									if ( ! filter_var( $app_contact, FILTER_VALIDATE_URL ) ) {
										JB()->frontend()->forms()->add_error( 'job_application', __( 'Job application must be an URL', 'jobboardwp' ) );
									}
									break;
								default:
									if ( ! is_email( $app_contact ) ) {
										// Prefix http if needed.
										if ( ! strstr( $app_contact, 'http:' ) && ! strstr( $app_contact, 'https:' ) ) {
											$app_contact = 'http://' . $app_contact;
										}
										if ( ! filter_var( $app_contact, FILTER_VALIDATE_URL ) ) {
											JB()->frontend()->forms()->add_error( 'job_application', __( 'Job application must be an email address or URL', 'jobboardwp' ) );
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
							JB()->frontend()->forms()->add_error( 'job_location', __( 'Job location type invalid', 'jobboardwp' ) );
						} else {
							$location_type = sanitize_text_field( $_POST['job_location_type'] );
							if ( $location_type === '0' ) {
								if ( empty( $_POST['job_location'] ) ) {
									JB()->frontend()->forms()->add_error( 'job_location', __( 'Location for onsite job is required', 'jobboardwp' ) );
								} else {
									$location = sanitize_text_field( $_POST['job_location'] );
								}
							} else {
								$location = ! empty( $_POST['job_location'] ) ? sanitize_text_field( $_POST['job_location'] ) : '';
							}
						}

						if ( empty( $_POST['company_name'] ) ) {
							JB()->frontend()->forms()->add_error( 'company_name', __( 'Company name cannot be empty', 'jobboardwp' ) );
						} else {
							$company_name = sanitize_text_field( $_POST['company_name'] );
							if ( empty( $company_name ) ) {
								JB()->frontend()->forms()->add_error( 'company_name', __( 'Company name cannot be empty', 'jobboardwp' ) );
							}
						}

						$company_website = ! empty( $_POST['company_website'] ) ? sanitize_text_field( $_POST['company_website'] ) : '';
						if ( ! empty( $company_website ) ) {
							// Prefix http if needed.
							if ( ! strstr( $company_website, 'http:' ) && ! strstr( $company_website, 'https:' ) ) {
								$company_website = 'http://' . $company_website;
							}
							if ( ! filter_var( $company_website, FILTER_VALIDATE_URL ) ) {
								JB()->frontend()->forms()->add_error( 'company_website', __( 'Company website is invalid', 'jobboardwp' ) );
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
									JB()->frontend()->forms()->add_error( 'company_twitter', __( 'Company Twitter is invalid', 'jobboardwp' ) );
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
								JB()->frontend()->forms()->add_error( 'company_facebook', __( 'Company Facebook is invalid', 'jobboardwp' ) );
							}
						}

						$company_instagram = ! empty( $_POST['company_instagram'] ) ? sanitize_text_field( $_POST['company_instagram'] ) : '';
						if ( ! empty( $company_instagram ) ) {
							$validate_company_instagram = $company_instagram;
							if ( ! strstr( $company_instagram, 'https://instagram.com/' ) ) {
								$validate_company_instagram = 'https://instagram.com/' . $company_instagram;
							}

							if ( ! filter_var( $validate_company_instagram, FILTER_VALIDATE_URL ) ) {
								JB()->frontend()->forms()->add_error( 'company_instagram', __( 'Company Instagram is invalid', 'jobboardwp' ) );
							}
						}

						$status = 'draft';
						if ( isset( $_POST['jb-job-submission-step'] ) && $_POST['jb-job-submission-step'] == 'preview' ) {
							$status = 'jb-preview';
						}

						$company_logo = '';
						if ( ! empty( $_POST['company_logo'] ) && ! empty( $_POST['company_logo_hash'] ) ) {
							if ( $_POST['company_logo_hash'] !== md5( $_POST['company_logo'] . '_jb_uploader_security_salt' ) ) {
								JB()->frontend()->forms()->add_error( 'company_logo', __( 'Something wrong with image, please re-upload', 'jobboardwp' ) );
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
						}

						if ( ! JB()->frontend()->forms()->has_errors() ) {

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

							$job_id = wp_insert_post( $job_data );

							if ( is_wp_error( $job_id ) ) {
								JB()->frontend()->forms()->add_error( 'global', __( 'Job submission issue, Please try again', 'jobboardwp' ) );
							} else {

								if ( ! empty( $company_logo ) ) {

									require_once ABSPATH . 'wp-admin/includes/image.php';
									require_once ABSPATH . 'wp-admin/includes/file.php';
									require_once ABSPATH . 'wp-admin/includes/media.php';

									$image_id = media_sideload_image( $company_logo, $job_id, null, 'id' );
									set_post_thumbnail( $job_id, $image_id );
								}

								if ( ! empty( $_POST['job_type'] ) && is_array( $_POST['job_type'] ) ) {
									$type_ids = array_map( 'absint', $_POST['job_type'] );
									wp_set_post_terms( $job_id, $type_ids, 'jb-job-type' );
								}

								if ( JB()->options()->get( 'job-categories' ) ) {
									if ( ! empty( $_POST['job_category'] ) ) {
										$categories = [ absint( $_POST['job_category'] ) ];
										wp_set_post_terms( $job_id, $categories, 'jb-job-category' );
									}
								}

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

							if ( ! JB()->frontend()->forms()->has_errors() ) {
								if ( isset( $_POST['jb-job-submission-step'] ) && $_POST['jb-job-submission-step'] == 'preview' ) {
									$url = add_query_arg( [ 'preview' => 1, 'job-id' => $job_id, 'nonce' => wp_create_nonce( 'jb-job-preview' . $job_id ) ], get_permalink() );
								} else {
									$url = add_query_arg( [ 'msg' => 'submitted' ], get_permalink() );
								}
								exit( wp_redirect( $url ) );
							}
						}

						break;
					}
					case 'job-publishing':

						if ( ! empty( $_GET['job-id'] ) ) {
							$job_id = absint( $_GET['job-id'] );

							if ( isset( $_POST['jb-job-submission-step'] ) && $_POST['jb-job-submission-step'] == 'publish' ) {
								$status =  JB()->options()->get( 'job-moderation' ) ? 'pending' : 'publish';

								wp_update_post( [
									'ID'            => $job_id,
									'post_status'   => $status,
								] );
							} else {
								wp_update_post( [
									'ID'            => $job_id,
									'post_status'   => 'draft',
								] );
							}

							if ( isset( $_POST['jb-job-submission-step'] ) && $_POST['jb-job-submission-step'] == 'publish' ) {
								if ( JB()->options()->get( 'job-moderation' ) ) {
									$url = add_query_arg( [ 'msg' => 'on-moderation' ], get_permalink() );
								} else {
									$url = add_query_arg( [ 'msg' => 'published' ], get_permalink() );
								}
							} else {
								//redirect to job's draft
								$url = JB()->common()->job()->get_edit_link( $job_id );
							}
							exit( wp_redirect( $url ) );
						}

						break;
				}
			}
		}

	}
}