<?php namespace jb\common;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\common\Blocks' ) ) {


	/**
	 * Class Blocks
	 *
	 * @package jb\common
	 */
	class Blocks {


		/**
		 * Job constructor.
		 */
		public function __construct() {
			add_action( 'init', array( &$this, 'block_editor_render' ), 11 );
			add_filter( 'allowed_block_types_all', array( &$this, 'jb_allowed_block_types' ), 10, 2 );
		}


		public function block_editor_render() {
			$blocks = array(
				'jb-block/jb-job-post'             => array(
					'editor_script'   => 'jb_admin_blocks_shortcodes',
					'render_callback' => array( $this, 'jb_job_post_render' ),
				),
				'jb-block/jb-job'                  => array(
					'editor_script'   => 'jb_admin_blocks_shortcodes',
					'render_callback' => array( $this, 'jb_single_job_render' ),
					'attributes'      => array(
						'job_id' => array(
							'type' => 'string',
						),
					),
				),
				'jb-block/jb-jobs-dashboard'       => array(
					'editor_script'   => 'jb_admin_blocks_shortcodes',
					'render_callback' => array( $this, 'jb_jobs_dashboard_render' ),
				),
				'jb-block/jb-jobs-categories-list' => array(
					'editor_script'   => 'jb_admin_blocks_shortcodes',
					'render_callback' => array( $this, 'jb_jobs_categories_list_render' ),
				),
				'jb-block/jb-jobs-list'            => array(
					'editor_script'   => 'jb_admin_blocks_shortcodes',
					'render_callback' => array( $this, 'jb_jobs_list_render' ),
					'attributes'      => array(
						'user_id'              => array(
							'type' => 'string',
						),
						'per_page'             => array(
							'type'    => 'string',
							'default' => JB()->options()->get( 'jobs-list-pagination' ),
						),
						'no_logo'              => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-no-logo' ),
						),
						'hide_filled'          => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-filled' ),
						),
						'hide_expired'         => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-expired' ),
						),
						'hide_search'          => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-search' ),
						),
						'hide_location_search' => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-location-search' ),
						),
						'hide_filters'         => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-filters' ),
						),
						'hide_job_types'       => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-job-types' ),
						),
						'no_jobs_text'         => array(
							'type' => 'string',
						),
						'no_job_search_text'   => array(
							'type' => 'string',
						),
						'load_more_text'       => array(
							'type' => 'string',
						),
						'category'             => array(
							'type' => 'string',
						),
						'type'                 => array(
							'type' => 'string',
						),
						'orderby'              => array(
							'default' => 'date',
						),
						'order'                => array(
							'type'    => 'string',
							'default' => 'DESC',
						),
						'filled_only'          => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'isLoading'            => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'content'              => array(
							'type' => 'string',
						),
					),
				),
				'jb-block/jb-recent-jobs'          => array(
					'editor_script'   => 'jb_admin_blocks_shortcodes',
					'render_callback' => array( $this, 'jb_recent_jobs_render' ),
					'attributes'      => array(
						'number'       => array(
							'type'    => 'number',
							'default' => 5,
						),
						'no_logo'      => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-no-logo' ),
						),
						'hide_filled'  => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-filled' ),
						),
						'no_job_types' => array(
							'type'    => 'boolean',
							'default' => JB()->options()->get( 'jobs-list-hide-job-types' ),
						),
						'remote_only'  => array(
							'type'    => 'boolean',
							'default' => 0,
						),
						'orderby'      => array(
							'default' => 'date',
							'type'    => 'string',
						),
						'type'         => array(
							'type'    => 'string',
							'default' => '',
						),
						'category'     => array(
							'type'    => 'string',
							'default' => '',
						),
					),
				),
			);

			foreach ( $blocks as $block_type => $block_data ) {
				register_block_type( $block_type, $block_data );
			}
		}


		public function jb_job_post_render( $atts ) {
			$shortcode = '[jb_post_job]';

			return apply_shortcodes( $shortcode );
		}


		public function jb_jobs_dashboard_render() {
			$shortcode = '[jb_jobs_dashboard]';

			return apply_shortcodes( $shortcode );
		}


		public function jb_jobs_categories_list_render() {
			$shortcode = '[jb_job_categories_list]';

			return apply_shortcodes( $shortcode );
		}


		public function jb_jobs_list_render( $atts ) {
			$shortcode = '[jb_jobs ';

			if ( isset( $atts['user_id'] ) && '' !== $atts['user_id'] ) {
				$shortcode .= ' employer-id="' . $atts['user_id'] . '"';
			}

			if ( $atts['per_page'] ) {
				$shortcode .= ' per-page="' . $atts['per_page'] . '"';
			}

			$shortcode .= ' no-logo="' . $atts['no_logo'] . '"';

			$shortcode .= ' hide-filled="' . $atts['hide_filled'] . '"';

			$shortcode .= ' hide-expired="' . $atts['hide_expired'] . '"';

			$shortcode .= ' hide-search="' . $atts['hide_search'] . '"';

			$shortcode .= ' hide-location-search="' . $atts['hide_location_search'] . '"';

			$shortcode .= ' hide-filters="' . $atts['hide_filters'] . '"';

			$shortcode .= ' hide-job-types="' . $atts['hide_job_types'] . '"';

			if ( isset( $atts['no_jobs_text'] ) && '' !== $atts['no_jobs_text'] ) {
				$shortcode .= ' no-jobs-text="' . $atts['no_jobs_text'] . '"';
			}

			if ( isset( $atts['no_job_search_text'] ) && '' !== $atts['no_job_search_text'] ) {
				$shortcode .= ' no-jobs-search-text="' . $atts['no_job_search_text'] . '"';
			}

			if ( isset( $atts['load_more_text'] ) && '' !== $atts['load_more_text'] ) {
				$shortcode .= ' load-more-text="' . $atts['load_more_text'] . '"';
			}

			if ( isset( $atts['category'] ) && '' !== $atts['category'] ) {
				$shortcode .= ' category="' . $atts['category'] . '"';
			}

			if ( isset( $atts['type'] ) && '' !== $atts['type'] ) {
				$shortcode .= ' type="' . $atts['type'] . '"';
			}

			if ( $atts['orderby'] ) {
				$shortcode .= ' orderby="' . $atts['orderby'] . '"';
			}

			if ( $atts['order'] ) {
				$shortcode .= ' order="' . $atts['order'] . '"';
			}

			$shortcode .= ' filled-only="' . $atts['filled_only'] . '"';

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}


		public function jb_single_job_render( $atts ) {
			$shortcode = '[jb_job';

			if ( $atts['job_id'] ) {
				$shortcode .= ' id="' . $atts['job_id'] . '"';
			}

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}


		public function jb_recent_jobs_render( $atts ) {
			$shortcode = '[jb_recent_jobs';

			if ( $atts['number'] ) {
				$shortcode .= ' number="' . $atts['number'] . '"';
			}

			$shortcode .= ' no_logo="' . $atts['no_logo'] . '"';

			if ( $atts['type'] ) {
				$shortcode .= ' type="' . $atts['type'] . '"';
			}

			if ( $atts['category'] ) {
				$shortcode .= ' category="' . $atts['category'] . '"';
			}

			$shortcode .= ' remote_only="' . $atts['remote_only'] . '"';

			if ( $atts['orderby'] ) {
				$shortcode .= ' orderby="' . $atts['orderby'] . '"';
			}

			$shortcode .= ' hide_filled="' . $atts['hide_filled'] . '"';

			$shortcode .= ' no_job_types="' . $atts['no_job_types'] . '"';

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}


		public function jb_allowed_block_types( $allowed_block_types, $block_editor_context ) {
			if ( 'core/edit-widgets' === $block_editor_context->name ) {
				$block_registry         = \WP_Block_Type_Registry::get_instance();
				$registered_block_types = $block_registry->get_all_registered();
				unset( $registered_block_types['jb-block/jb-job-post'] );
				unset( $registered_block_types['jb-block/jb-job'] );
				return array_keys( $registered_block_types );
			}

			return $allowed_block_types;
		}
	}
}
