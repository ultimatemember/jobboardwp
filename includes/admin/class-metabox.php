<?php
namespace jb\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


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
		var $nonce = [];


		/**
		 * Metabox constructor.
		 */
		function __construct() {
			add_action( 'load-post.php', [ &$this, 'add_metabox' ], 9 );
			add_action( 'load-post-new.php', [ &$this, 'add_metabox' ], 9 );


			add_action( 'jb-job-type_add_form_fields', [ &$this, 'job_type_create' ] );
			add_action( 'jb-job-type_edit_form_fields', [ &$this, 'job_type_edit' ] );
			add_action( 'create_jb-job-type', [ &$this, 'save_job_type_meta' ], 10, 1 );
			add_action( 'edited_jb-job-type', [ &$this, 'save_job_type_meta' ], 10, 1 );
		}


		/**
		 * Add custom fields on Job Type Create form
		 *
		 * @since 1.0
		 */
		function job_type_create() {
			include_once JB()->admin()->templates_path . 'job-type' . DIRECTORY_SEPARATOR . 'styling-create.php';

			wp_nonce_field( basename( __FILE__ ), 'jb_job_type_styling_nonce' );
		}


		/**
		 * Add custom fields on Job Type Edit form
		 *
		 * @param \WP_Term $term
		 *
		 * @since 1.0
		 */
		function job_type_edit( $term ) {
			$termID = $term->term_id;

			$data = [];
			$data['jb-color'] = get_term_meta( $termID, 'jb-color', true );
			$data['jb-background'] = get_term_meta( $termID, 'jb-background', true );

			include_once JB()->admin()->templates_path . 'job-type' . DIRECTORY_SEPARATOR . 'styling-edit.php';

			wp_nonce_field( basename( __FILE__ ), 'jb_job_type_styling_nonce' );
		}


		/**
		 * Save custom data for Job Type
		 *
		 * @param int $termID
		 *
		 * @since 1.0
		 */
		function save_job_type_meta( $termID ) {

			// validate nonce
			if ( ! isset( $_REQUEST['jb_job_type_styling_nonce'] ) || ! wp_verify_nonce( $_REQUEST['jb_job_type_styling_nonce'], basename( __FILE__ ) ) ) {
				return;
			}

			// validate user
			$term = get_term( $termID );
			$taxonomy = get_taxonomy( $term->taxonomy );

			if ( ! current_user_can( $taxonomy->cap->edit_terms, $termID ) ) {
				return;
			}

			if ( ! empty( $_REQUEST['jb-color'] ) ) {
				update_term_meta( $termID, 'jb-color', $_REQUEST['jb-color'] );
			} else {
				delete_term_meta( $termID, 'jb-color' );
			}

			if ( ! empty( $_REQUEST['jb-background'] ) ) {
				update_term_meta( $termID, 'jb-background', $_REQUEST['jb-background'] );
			} else {
				delete_term_meta( $termID, 'jb-background' );
			}
		}


		/**
		 * Checking CPT screen
		 *
		 * @since 1.0
		 */
		function add_metabox() {
			global $current_screen;

			if ( $current_screen->id == 'jb-job' && current_user_can( 'edit_jb-jobs' ) ) {

				add_action( 'add_meta_boxes', [ &$this, 'add_metabox_job' ] );
				add_action( 'save_post', [ &$this, 'save_metabox_job' ], 10, 2 );

			}
		}


		/**
		 * Load a form metabox
		 *
		 * @param $object
		 * @param array $box
		 *
		 * @since 1.0
		 */
		function load_metabox_job( $object, $box ) {
			$metabox = str_replace( 'jb-job-','', $box['id'] );

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
		function add_metabox_job() {
			add_meta_box( 'jb-job-data', __( 'Job Data', 'jobboardwp' ), [ &$this, 'load_metabox_job' ], 'jb-job', 'normal', 'core' );
		}


		/**
		 * Save job metabox
		 *
		 * @param int $post_id
		 * @param \WP_Post $post
		 *
		 * @since 1.0
		 */
		function save_metabox_job( $post_id, $post ) {
			// validate nonce
			if ( ! isset( $_POST['jb_job_save_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['jb_job_save_metabox_nonce'], basename( __FILE__ ) ) ) {
				return;
			}

			// validate post type
			if ( $post->post_type != 'jb-job' ) {
				return;
			}

			// validate user
			$post_type = get_post_type_object( $post->post_type );
			if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
				return;
			}

			// location validation
			if ( ! isset( $_POST['jb-job-meta']['jb-location-type'] ) ) {
				return;
			}
			if ( $_POST['jb-job-meta']['jb-location-type'] === '0' && empty( $_POST['jb-job-meta']['jb-location'] ) ) {
				return;
			}

			//save metadata
			foreach ( $_POST['jb-job-meta'] as $k => $v ) {
				if ( strstr( $k, 'jb-' ) ) {
					if ( 'jb-author' == $k ) {
						global $wpdb;
						$wpdb->update( $wpdb->posts, [ 'post_author' => $v ], [ 'ID' => $post_id ], [ '%d' ], [ '%d' ] );
						continue;
					}

					if ( 'jb-is-filled' == $k ) {
						if ( ! empty( $v ) ) {
							if ( ! JB()->common()->job()->is_filled( $post_id ) ) {
								do_action( 'jb_fill_job', $post_id, $post );
							}
						} else {
							if ( JB()->common()->job()->is_filled( $post_id ) ) {
								do_action( 'jb_unfill_job', $post_id, $post );
							}
						}
					}

					if ( 'jb-expiry-date' == $k ) {
						if ( empty( $v ) ) {
							$v = JB()->common()->job()->calculate_expiry();
						} else {
							$date = strtotime( $v, current_time( 'timestamp' ) );
							$v = date( 'Y-m-d', $date );
							if ( current_time( 'timestamp' ) >= $date ) {
								global $wpdb;
								$wpdb->update( $wpdb->posts, [ 'post_status' => 'jb-expired' ], [ 'ID' => $post_id ], [ '%s' ], [ '%d' ] );
								do_action( 'jb_job_is_expired', $post_id );
							}
						}
					}

					if ( 'jb-location-data' == $k ) {

						$v = json_decode( stripslashes( $v ) );

						update_post_meta( $post_id, 'jb-location-raw-data', $v );
						update_post_meta( $post_id, 'jb-location-lat', sanitize_text_field( $v->geometry->location->lat ) );
						update_post_meta( $post_id, 'jb-location-long', sanitize_text_field( $v->geometry->location->lng ) );
						update_post_meta( $post_id, 'jb-location-formatted-address', sanitize_text_field( $v->formatted_address ) );

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

					if ( $_POST['jb-job-meta']['jb-location-type'] !== '0' && 'jb-location-preferred' == $k ) {
						$k = 'jb-location';
					}

					update_post_meta( $post_id, $k, $v );
				}
			}

			update_post_meta( $post_id, 'jb-last-edit-date', time() );
		}
	}
}