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
			add_action( 'create_jb-job-type', [ &$this, 'save_job_type_meta' ] );
			add_action( 'edited_jb-job-type', [ &$this, 'save_job_type_meta' ] );
		}


		/**
		 * Add custom fields on Job Type Create form
		 */
		function job_type_create() {
			include_once JB()->admin()->templates_path . 'job-type' . DIRECTORY_SEPARATOR . 'styling-create.php';

			wp_nonce_field( basename( __FILE__ ), 'jb_job_type_styling_nonce' );
		}


		/**
		 * Add custom fields on Job Type Edit form
		 *
		 * @param $term
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
		 * @param $termID
		 *
		 * @return mixed
		 */
		function save_job_type_meta( $termID ) {

			// validate nonce
			if ( ! isset( $_REQUEST['jb_job_type_styling_nonce'] ) || ! wp_verify_nonce( $_REQUEST['jb_job_type_styling_nonce'], basename( __FILE__ ) ) ) {
				return $termID;
			}

			// validate user
			$term = get_term( $termID );
			$taxonomy = get_taxonomy( $term->taxonomy );

			if ( ! current_user_can( $taxonomy->cap->edit_terms, $termID ) ) {
				return $termID;
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

			return $termID;
		}


		/**
		 * Checking CPT screen
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
		 * @param $box
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
		 */
		function add_metabox_job() {
			add_meta_box( 'jb-job-data', __( 'Job Data', 'jobboardwp' ), [ &$this, 'load_metabox_job' ], 'jb-job', 'normal', 'core' );
		}


		/**
		 * Save forum metabox
		 *
		 * @param $post_id
		 * @param $post
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
					if ( 'jb-expiry-date' == $k ) {
						$v = date( 'Y-m-d', strtotime( $v, current_time( 'timestamp' ) ) );
					}

					update_post_meta( $post_id, $k, $v );
				}
			}
		}
	}
}