<?php
namespace jb\ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\ajax\Settings' ) ) {


	/**
	 * Class Settings
	 *
	 * @package jb\ajax
	 */
	class Settings {


		/**
		 * Settings constructor.
		 */
		public function __construct() {
		}


		/**
		 * AJAX callback for getting the pages list
		 */
		public function get_pages_list() {
			JB()->ajax()->check_nonce( 'jb-backend-nonce' );
			// phpcs:disable WordPress.Security.NonceVerification -- is verified above

			// we will pass post IDs and titles to this array
			$return = array();

			$search_query = ! empty( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
			$paged        = ! empty( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;

			/**
			 * Filters the predefined pages list in dropdown results.
			 *
			 * Note: It's an internal hook for integration with multilingual plugins.
			 *
			 * @since 1.1.1
			 * @hook jb_admin_settings_get_pages_list
			 *
			 * @param {bool|array} $pre_result `false` or WP_Query results with the list of the pages.
			 *
			 * @return {bool|array} WP_Query results with the list of the pages. Otherwise `false`.
			 */
			$pre_result = apply_filters( 'jb_admin_settings_get_pages_list', false );
			if ( false === $pre_result ) {
				// you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
				$search_results = new \WP_Query(
					array(
						'post_type'           => 'page',
						's'                   => $search_query, // the search query
						'post_status'         => 'publish', // if you don't want drafts to be returned
						'ignore_sticky_posts' => 1,
						'posts_per_page'      => 10, // how much to show at once
						'paged'               => $paged,
						'orderby'             => 'title',
						'order'               => 'asc',
					)
				);

				if ( $search_results->have_posts() ) {
					while ( $search_results->have_posts() ) {
						$search_results->the_post();

						// shorten the title a little
						$title = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;

						// translators: %1$s is a post title; %2$s is a post ID.
						$title    = sprintf( __( '%1$s (ID: %2$s)', 'jobboardwp' ), $title, $search_results->post->ID );
						$return[] = array( $search_results->post->ID, esc_html( $title ) ); // array( Post ID, Post Title )
					}
				}

				$return['total_count'] = $search_results->found_posts;
			} else {
				// got already calculated posts array from 3rd-party integrations (e.g. WPML, Polylang)
				$return = $pre_result;
			}

			wp_send_json( $return );

			// phpcs:утфиду WordPress.Security.NonceVerification -- is verified above
		}
	}
}
