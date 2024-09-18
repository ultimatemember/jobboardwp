<?php
namespace jb\admin;

use WP_Post;
use WP_Term;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\admin\Metabox' ) ) {

	/**
	 * Class Metabox
	 *
	 * @package jb\admin
	 */
	class Metabox {

		/**
		 * @var array
		 *
		 * @since 1.0
		 */
		public $nonce = array();

		/**
		 * Metabox constructor.
		 */
		public function __construct() {
			add_action( 'load-post.php', array( &$this, 'add_metabox' ), 9 );
			add_action( 'load-post-new.php', array( &$this, 'add_metabox' ), 9 );

			add_action( 'jb-job-type_add_form_fields', array( &$this, 'job_type_create' ) );
			add_action( 'jb-job-type_edit_form_fields', array( &$this, 'job_type_edit' ) );
			add_action( 'create_jb-job-type', array( &$this, 'save_job_type_meta' ) );
			add_action( 'edited_jb-job-type', array( &$this, 'save_job_type_meta' ) );
		}

		/**
		 * Add custom fields on Job Type Create form
		 *
		 * @since 1.0
		 */
		public function job_type_create() {
			include_once JB()->admin()->templates_path . 'job-type' . DIRECTORY_SEPARATOR . 'styling-create.php';

			wp_nonce_field( basename( __FILE__ ), 'jb_job_type_styling_nonce' );
		}

		/**
		 * Add custom fields on Job Type Edit form
		 *
		 * @param WP_Term $term
		 *
		 * @since 1.0
		 */
		public function job_type_edit( $term ) {
			$term_id = $term->term_id;

			$data                  = array();
			$data['jb-color']      = get_term_meta( $term_id, 'jb-color', true );
			$data['jb-background'] = get_term_meta( $term_id, 'jb-background', true );

			include_once JB()->admin()->templates_path . 'job-type' . DIRECTORY_SEPARATOR . 'styling-edit.php';

			wp_nonce_field( basename( __FILE__ ), 'jb_job_type_styling_nonce' );
		}

		/**
		 * Save custom data for Job Type
		 *
		 * @param int $term_id
		 *
		 * @since 1.0
		 */
		public function save_job_type_meta( $term_id ) {
			// validate nonce
			if ( ! isset( $_REQUEST['jb_job_type_styling_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['jb_job_type_styling_nonce'] ), basename( __FILE__ ) ) ) {
				return;
			}

			// validate user
			$term     = get_term( $term_id );
			$taxonomy = get_taxonomy( $term->taxonomy );

			if ( ! current_user_can( $taxonomy->cap->edit_terms, $term_id ) ) {
				return;
			}

			if ( ! empty( $_REQUEST['jb-color'] ) ) {
				update_term_meta( $term_id, 'jb-color', sanitize_hex_color( wp_unslash( $_REQUEST['jb-color'] ) ) );
			} else {
				delete_term_meta( $term_id, 'jb-color' );
			}

			if ( ! empty( $_REQUEST['jb-background'] ) ) {
				update_term_meta( $term_id, 'jb-background', sanitize_hex_color( wp_unslash( $_REQUEST['jb-background'] ) ) );
			} else {
				delete_term_meta( $term_id, 'jb-background' );
			}
		}

		/**
		 * Checking CPT screen
		 *
		 * @since 1.0
		 */
		public function add_metabox() {
			global $current_screen;

			if ( 'jb-job' === $current_screen->id && current_user_can( 'edit_jb-jobs' ) ) {
				add_action( 'add_meta_boxes', array( &$this, 'add_metabox_job' ) );
				add_action( 'save_post', array( &$this, 'save_metabox_job' ), 10, 2 );
			}
		}

		/**
		 * Load a form metabox
		 *
		 * @param object $job Not used.
		 * @param array $box
		 *
		 * @since 1.0
		 */
		public function load_metabox_job( /** @noinspection PhpUnusedParameterInspection */$job, $box ) {
			$metabox = str_replace( 'jb-job-', '', $box['id'] );

			include_once JB()->admin()->templates_path . 'job' . DIRECTORY_SEPARATOR . $metabox . '.php';

			if ( empty( $this->nonce['job'] ) ) {
				$this->nonce['job'] = true;
				wp_nonce_field( basename( __FILE__ ), 'jb_job_save_metabox_nonce' );
			}
		}

		/**
		 * Add form metabox
		 *
		 * @since 1.0
		 */
		public function add_metabox_job() {
			add_meta_box( 'jb-job-data', __( 'Job Data', 'jobboardwp' ), array( &$this, 'load_metabox_job' ), 'jb-job', 'normal', 'core' );
		}

		/**
		 * Save job metabox
		 *
		 * @param int $post_id
		 * @param WP_Post $post
		 *
		 * @since 1.0
		 */
		public function save_metabox_job( $post_id, $post ) {
			// validate nonce
			if ( ! isset( $_POST['jb_job_save_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['jb_job_save_metabox_nonce'] ), basename( __FILE__ ) ) ) {
				return;
			}

			// validate post type
			if ( 'jb-job' !== $post->post_type ) {
				return;
			}

			// validate post type object
			$post_type = get_post_type_object( $post->post_type );
			if ( null === $post_type ) {
				return;
			}

			// validate user
			if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
				return;
			}

			// location validation
			/**
			 * Filters the checking job location type.
			 *
			 * @since 1.2.2
			 * @hook jb_location_type_disable
			 *
			 * @param {bool} $disable Checking job location type. Set to true if disable job location type.
			 *
			 * @return {bool} Login form visibility.
			 */
			$location_type_disable = apply_filters( 'jb_location_type_disable', false );
			if ( false === (bool) $location_type_disable ) {
				if ( ! isset( $_POST['jb-job-meta']['jb-location-type'] ) ) {
					return;
				}
				if ( empty( $_POST['jb-job-meta']['jb-location'] ) && sanitize_text_field( wp_unslash( $_POST['jb-job-meta']['jb-location-type'] ) ) === '0' ) {
					return;
				}
			}

			$sanitize_map = array(
				'jb-author'              => 'absint',
				'jb-application-contact' => 'text',
				'jb-job-type'            => 'absint',
				'jb-job-category'        => 'absint',
				'jb-location-type'       => 'text',
				'jb-location'            => 'text',
				'jb-location-preferred'  => 'text',
				'jb-company-name'        => 'text',
				'jb-company-website'     => 'text',
				'jb-company-tagline'     => 'text',
				'jb-company-twitter'     => 'text',
				'jb-company-facebook'    => 'text',
				'jb-company-instagram'   => 'text',
				'jb-is-filled'           => 'bool',
				'jb-expiry-date'         => 'text',
				'jb-is-featured'         => 'bool',
				'jb-featured-order'      => 'absint',
				'jb-salary-type'         => 'text',
				'jb-salary-amount-type'  => 'text',
				'jb-salary-amount'       => 'absint',
				'jb-salary-min-amount'   => 'absint',
				'jb-salary-max-amount'   => 'absint',
				'jb-salary-period'       => 'text',
			);

			$current_time = time();

			// merge preferred location into location
			if ( isset( $_POST['jb-job-meta']['jb-location-type'] ) && '0' !== sanitize_text_field( wp_unslash( $_POST['jb-job-meta']['jb-location-type'] ) ) ) {
				if ( isset( $_POST['jb-job-meta']['jb-location-preferred'] ) ) {
					$_POST['jb-job-meta']['jb-location'] = wp_unslash( $_POST['jb-job-meta']['jb-location-preferred'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below
					unset( $_POST['jb-job-meta']['jb-location-preferred'] );
				}
				if ( isset( $_POST['jb-job-meta']['jb-location-preferred-data'] ) ) {
					$_POST['jb-job-meta']['jb-location-data'] = wp_unslash( $_POST['jb-job-meta']['jb-location-preferred-data'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below
					unset( $_POST['jb-job-meta']['jb-location-preferred-data'] );
				}
			}

			if ( empty( $_POST['jb-job-meta']['jb-is-featured'] ) ) {
				unset( $_POST['jb-job-meta']['jb-featured-order'] );
			} elseif ( empty( $_POST['jb-job-meta']['jb-featured-order'] ) ) {
				// workaround if user set featured option but doesn't the order
				$_POST['jb-job-meta']['jb-featured-order'] = 1;
			}

			$skip_meta_update = array();

			//save metadata
			foreach ( $_POST['jb-job-meta'] as $k => $v ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitized below
				if ( 0 === strpos( $k, 'jb-' ) ) {
					if ( in_array( $k, $skip_meta_update, true ) ) {
						continue;
					}

					$meta_key = sanitize_key( $k );

					if ( isset( $sanitize_map[ $meta_key ] ) ) {
						switch ( $sanitize_map[ $meta_key ] ) {
							case 'bool':
								$v = (bool) $v;
								break;
							case 'text':
								$v = sanitize_text_field( wp_unslash( $v ) );
								break;
							case 'absint':
								if ( is_array( $v ) ) {
									$v = array_map( 'absint', $v );
								} else {
									$v = absint( $v );
								}
								break;
						}
					}

					if ( 'jb-job-type' === $meta_key ) {
						if ( ! is_array( $v ) ) {
							$v = ! empty( $v ) ? array( $v ) : '';
						}
						wp_set_post_terms( $post_id, $v, 'jb-job-type' );
						continue;
					}

					if ( JB()->options()->get( 'job-categories' ) ) {
						if ( 'jb-job-category' === $meta_key ) {
							if ( ! is_array( $v ) ) {
								$v = ! empty( $v ) ? array( $v ) : '';
							}
							wp_set_post_terms( $post_id, $v, 'jb-job-category' );
							continue;
						}
					}

					if ( 'jb-author' === $meta_key ) {
						global $wpdb;
						$wpdb->update( $wpdb->posts, array( 'post_author' => $v ), array( 'ID' => $post_id ), array( '%d' ), array( '%d' ) );
						continue;
					}

					if ( 'jb-is-filled' === $meta_key ) {
						if ( ! empty( $v ) ) {
							if ( ! JB()->common()->job()->is_filled( $post_id ) ) {
								/** This action is documented in includes/ajax/class-jobs.php */
								do_action( 'jb_fill_job', $post_id, $post );
							}
						} elseif ( JB()->common()->job()->is_filled( $post_id ) ) {
							/** This action is documented in includes/ajax/class-jobs.php */
							do_action( 'jb_unfill_job', $post_id, $post );
						}
					}

					if ( 'jb-expiry-date' === $meta_key ) {
						if ( empty( $v ) ) {
							if ( JB()->options()->get( 'individual-job-duration' ) ) {
								/**
								 * Filters the default individual job expiration date.
								 *
								 * Note: It works only in case if the expiration date has been empty in the posting form.
								 *
								 * @since 1.1.0
								 * @hook jb_default_individual_expiry
								 *
								 * @param {string} $expiration_date Default Job expiration date. It's '' by default and job is unexpired.
								 *
								 * @return {string} Job expiration date.
								 */
								$v = apply_filters( 'jb_default_individual_expiry', '' );
							} else {
								$v = JB()->common()->job()->calculate_expiry();
							}
						} else {
							$date = strtotime( $v, $current_time );
							$v    = gmdate( 'Y-m-d', $date );
							if ( $current_time >= $date ) {
								global $wpdb;
								$wpdb->update( $wpdb->posts, array( 'post_status' => 'jb-expired' ), array( 'ID' => $post_id ), array( '%s' ), array( '%d' ) );
								/** This action is documented in includes/common/class-job.php */
								do_action( 'jb_job_is_expired', $post_id );
							}
						}
					}

					if ( 'jb-location-data' === $k ) {
						$v = json_decode( wp_unslash( $v ) );
						$v = JB()->common()->job()->sanitize_location_data( $v );

						update_post_meta( $post_id, 'jb-location-raw-data', $v );

						if ( isset( $v->geometry, $v->geometry->location ) ) {
							if ( isset( $v->geometry->location->lat ) ) {
								update_post_meta( $post_id, 'jb-location-lat', sanitize_text_field( $v->geometry->location->lat ) );
							}
							if ( isset( $v->geometry->location->lng ) ) {
								update_post_meta( $post_id, 'jb-location-long', sanitize_text_field( $v->geometry->location->lng ) );
							}
						}
						if ( isset( $v->formatted_address ) ) {
							update_post_meta( $post_id, 'jb-location-formatted-address', sanitize_text_field( $v->formatted_address ) );
						}

						if ( ! empty( $v->address_components ) ) {
							$address_data = $v->address_components;

							foreach ( $address_data as $data ) {
								switch ( $data->types[0] ) {
									case 'sublocality_level_1':
									case 'locality':
									case 'postal_town':
										update_post_meta( $post_id, 'jb-location-city', sanitize_text_field( $data->long_name ) );
										break;
									case 'administrative_area_level_1':
									case 'administrative_area_level_2':
										update_post_meta( $post_id, 'jb-location-state-short', sanitize_text_field( $data->short_name ) );
										update_post_meta( $post_id, 'jb-location-state-long', sanitize_text_field( $data->long_name ) );
										break;
									case 'country':
										update_post_meta( $post_id, 'jb-location-country-short', sanitize_text_field( $data->short_name ) );
										update_post_meta( $post_id, 'jb-location-country-long', sanitize_text_field( $data->long_name ) );
										break;
								}
							}
						}

						continue;
					}

					// Flush salary data if it isn't supported by the current salary type.
					if ( 'jb-salary-type' === $k ) {
						if ( empty( $v ) ) {
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
							delete_post_meta( $post_id, 'jb-salary-type' );
							delete_post_meta( $post_id, 'jb-salary-amount-type' );
							delete_post_meta( $post_id, 'jb-salary-amount' );
							delete_post_meta( $post_id, 'jb-salary-min-amount' );
							delete_post_meta( $post_id, 'jb-salary-max-amount' );
							delete_post_meta( $post_id, 'jb-salary-period' );
							continue;
						} elseif ( 'fixed' === $v ) {
							$skip_meta_update = array_merge(
								$skip_meta_update,
								array(
									'jb-salary-period',
								)
							);
							delete_post_meta( $post_id, 'jb-salary-period' );
						}
					}

					update_post_meta( $post_id, $k, $v );

					// Flush salary data if it isn't supported by the current salary type.
					if ( 'jb-salary-amount-type' === $k ) {
						if ( 'numeric' === $v ) {
							$skip_meta_update = array_merge(
								$skip_meta_update,
								array(
									'jb-salary-min-amount',
									'jb-salary-max-amount',
								)
							);

							delete_post_meta( $post_id, 'jb-salary-min-amount' );
							delete_post_meta( $post_id, 'jb-salary-max-amount' );
						} elseif ( 'range' === $v ) {
							$skip_meta_update = array_merge(
								$skip_meta_update,
								array(
									'jb-salary-amount',
								)
							);
							delete_post_meta( $post_id, 'jb-salary-amount' );
						}
					}

					// Flush featured order in case when the job isn't featured.
					if ( 'jb-is-featured' === $k && empty( $v ) ) {
						delete_post_meta( $post_id, 'jb-featured-order' );
					}
				}
			}

			update_post_meta( $post_id, 'jb-last-edit-date', $current_time );

			/**
			 * Fires after job submission and pass validation through wp-admin.
			 *
			 * @since 1.2.3
			 * @hook jb_job_after_save_metabox
			 *
			 * @param {string} $post_id Job's ID.
			 * @param {object} $post    Post object.
			 */
			do_action( 'jb_job_after_save_metabox', $post_id, $post );
		}
	}
}
