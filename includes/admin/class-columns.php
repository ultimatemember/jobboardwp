<?php
namespace jb\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\admin\Columns' ) ) {


	/**
	 * Class Columns
	 *
	 * @package jb\admin
	 */
	class Columns {


		/**
		 * Columns constructor.
		 */
		public function __construct() {
			add_filter( 'display_post_states', array( &$this, 'add_display_post_states' ), 10, 2 );

			add_action( 'restrict_manage_posts', array( $this, 'display_jobs_meta_filters' ) );

			add_filter( 'manage_edit-jb-job_columns', array( &$this, 'job_columns' ) );
			add_action( 'manage_jb-job_posts_custom_column', array( &$this, 'job_columns_content' ), 10, 3 );
			add_filter( 'manage_edit-jb-job_sortable_columns', array( $this, 'sortable_columns' ) );
			add_filter( 'bulk_actions-edit-jb-job', array( &$this, 'remove_from_bulk_actions' ), 10, 1 );
			add_filter( 'handle_bulk_actions-edit-jb-job', array( &$this, 'custom_bulk_action_handler' ), 10, 3 );

			add_action( 'admin_notices', array( &$this, 'after_bulk_action_notice' ) );

			add_filter( 'views_edit-jb-job', array( &$this, 'replace_list_table' ), 10, 1 );
			add_filter( 'post_row_actions', array( &$this, 'remove_quick_edit' ), 10, 2 );

			add_filter( 'request', array( $this, 'sort_columns' ) );
			add_action( 'parse_query', array( $this, 'filter_meta' ) );
		}


		/**
		 * Add a post display state for special Job Board pages in the page list table.
		 *
		 * @param array $post_states An array of post display states.
		 * @param \WP_Post $post The current post object.
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function add_display_post_states( $post_states, $post ) {
			if ( 'page' === $post->post_type ) {
				foreach ( JB()->config()->get( 'predefined_pages' ) as $slug => $page_value ) {
					if ( JB()->common()->permalinks()->is_predefined_page( $slug, $post ) ) {
						// translators: %s is a pre-defined page title.
						$post_states[ 'jb_page_' . $slug ] = sprintf( __( 'JB %s', 'jobboardwp' ), $page_value['title'] );
					}
				}
			}

			return $post_states;
		}


		/**
		 * Output dropdowns for filters based on post meta.
		 *
		 * @since 1.0
		 */
		public function display_jobs_meta_filters() {
			global $typenow;

			// Only add the filters for job_listings.
			if ( 'jb-job' !== $typenow ) {
				return;
			}

			if ( JB()->options()->get( 'job-categories' ) ) {
				$categories = get_terms(
					array(
						'taxonomy'   => 'jb-job-category',
						'hide_empty' => false,
					)
				);

				if ( ! empty( $categories ) ) {
					$selected_cat = isset( $_GET['jb-job-category'] ) ? sanitize_text_field( wp_unslash( $_GET['jb-job-category'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

					$dropdown_options = array(
						'selected'          => $selected_cat,
						'name'              => 'jb-job-category',
						'taxonomy'          => 'jb-job-category',
						'show_option_all'   => get_taxonomy( 'jb-job-category' )->labels->all_items,
						'hide_empty'        => false,
						'hierarchical'      => 1,
						'show_count'        => 0,
						'orderby'           => 'name',
						'value_field'       => 'slug',
						'option_none_value' => '',
					);

					echo '<label class="screen-reader-text" for="jb-job-category">' . esc_html__( 'Filter by job category', 'jobboardwp' ) . '</label>';
					wp_dropdown_categories( $dropdown_options );
				}
			}

			$types = get_terms(
				array(
					'taxonomy'   => 'jb-job-type',
					'hide_empty' => false,
				)
			);

			if ( ! empty( $types ) ) {
				$selected_cat = isset( $_GET['jb-job-type'] ) ? sanitize_text_field( wp_unslash( $_GET['jb-job-type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				$dropdown_options = array(
					'selected'          => $selected_cat,
					'name'              => 'jb-job-type',
					'taxonomy'          => 'jb-job-type',
					'show_option_all'   => get_taxonomy( 'jb-job-type' )->labels->all_items,
					'hide_empty'        => false,
					'hierarchical'      => 1,
					'show_count'        => 0,
					'orderby'           => 'name',
					'value_field'       => 'slug',
					'option_none_value' => '',
				);

				echo '<label class="screen-reader-text" for="jb-job-type">' . esc_html__( 'Filter by job type', 'jobboardwp' ) . '</label>';
				wp_dropdown_categories( $dropdown_options );
			}

			$selected = isset( $_GET['jb-is-filled'] ) ? sanitize_text_field( wp_unslash( $_GET['jb-is-filled'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			$options = array(
				''  => __( 'Select Filled', 'jobboardwp' ),
				'1' => __( 'Filled', 'jobboardwp' ),
				'0' => __( 'Not Filled', 'jobboardwp' ),
			); ?>

			<label class="screen-reader-text" for="dropdown_jb-is-filled"><?php esc_html_e( 'Filter by filled type', 'jobboardwp' ); ?></label>
			<select name="jb-is-filled" id="dropdown_jb-is-filled">
				<?php foreach ( $options as $k => $v ) { ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $k, $selected ); ?>>
						<?php echo esc_html( $v ); ?>
					</option>
				<?php } ?>
			</select>
			<?php
			$selected = isset( $_GET['jb-is-featured'] ) ? sanitize_text_field( wp_unslash( $_GET['jb-is-featured'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			$featured_options = array(
				''  => __( 'Select Featured', 'jobboardwp' ),
				'1' => __( 'Featured', 'jobboardwp' ),
				'0' => __( 'Not Featured', 'jobboardwp' ),
			);
			?>
			<label class="screen-reader-text" for="dropdown_jb-is-featured"><?php esc_html_e( 'Filter by featured jobs', 'jobboardwp' ); ?></label>
			<select name="jb-is-featured" id="dropdown_jb-is-featured">
				<?php foreach ( $featured_options as $k => $v ) { ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $k, $selected ); ?>>
						<?php echo esc_html( $v ); ?>
					</option>
				<?php } ?>
			</select>
			<?php
		}


		/**
		 * Added admin notice after bulk approve or delete job posts
		 *
		 * @since 1.0
		 */
		public function after_bulk_action_notice() {
			if ( ! empty( $_REQUEST['jb-approved'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$approved_count = absint( $_REQUEST['jb-approved'] ); // phpcs:ignore WordPress.Security.NonceVerification
				?>
				<div class="jb-admin-notice notice updated fade">
					<p>
						<?php
						echo esc_html(
							sprintf(
								// translators: %s is the count of approved jobs.
								_n( '%s job is approved.', '%s jobs are approved.', $approved_count, 'jobboardwp' ),
								$approved_count
							)
						);
						?>
					</p>
				</div>
				<?php
			} elseif ( ! empty( $_REQUEST['jb-deleted'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$deleted_count = absint( $_REQUEST['jb-deleted'] ); // phpcs:ignore WordPress.Security.NonceVerification
				?>
				<div class="jb-admin-notice notice updated fade">
					<p>
						<?php
						echo esc_html(
							sprintf(
								// translators: %s is the count of deleted jobs.
								_n( '%s job is deleted.', '%s jobs are deleted.', $deleted_count, 'jobboardwp' ),
								$deleted_count
							)
						);
						?>
					</p>
				</div>
				<?php
			}
		}


		/**
		 * Handler for the bulk approve
		 *
		 * @param string $redirect_to
		 * @param string $doaction
		 * @param array $post_ids
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function custom_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
			if ( 'jb-approve' === $doaction ) {
				$app_ids = array();
				foreach ( $post_ids as $post_id ) {
					$post = get_post( $post_id );
					if ( ! JB()->common()->job()->approve_job( $post ) ) {
						continue;
					}

					$app_ids[] = $post_id;
				}
				$redirect_to = add_query_arg( 'jb-approved', count( $app_ids ), remove_query_arg( 'jb-deleted', $redirect_to ) );
			} elseif ( 'jb-delete' === $doaction ) {
				foreach ( $post_ids as $post_id ) {
					wp_delete_post( $post_id, true );
				}
				$redirect_to = add_query_arg( 'jb-deleted', count( $post_ids ), remove_query_arg( 'jb-approved', $redirect_to ) );
			}

			return $redirect_to;
		}


		/**
		 * Changed jobs bulk actions
		 *
		 * @param array $actions
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function remove_from_bulk_actions( $actions ) {
			unset( $actions['edit'] );

			$actions = array( 'jb-approve' => __( 'Approve', 'jobboardwp' ) ) + $actions;
			$actions = $actions + array( 'jb-delete' => __( 'Delete permanently', 'jobboardwp' ) );
			return $actions;
		}


		/**
		 * Extends WP_Posts_List_Table class via JB class
		 * to change list table view
		 *
		 * @param $views
		 *
		 * @return mixed
		 *
		 * @since 1.0
		 */
		public function replace_list_table( $views ) {
			global $wp_list_table;

			$total_items = $wp_list_table->get_pagination_arg( 'total_items' );
			$total_pages = $wp_list_table->get_pagination_arg( 'total_pages' );
			$per_page    = $wp_list_table->get_pagination_arg( 'per_page' );

			$wp_list_table = new List_Table(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

			$wp_list_table->public_set_pagination_args(
				array(
					'total_items' => $total_items,
					'total_pages' => $total_pages,
					'per_page'    => $per_page,
				)
			);

			return $views;
		}


		/**
		 * Remove ability to job's quick edit
		 *
		 * @param array $actions
		 * @param \WP_Post $post
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function remove_quick_edit( $actions, $post ) {
			if ( 'jb-job' === $post->post_type ) {
				unset( $actions['inline hide-if-no-js'] );

				if ( 'pending' === $post->post_status ) {
					$url = add_query_arg(
						array(
							'jb_adm_action' => 'approve_job',
							'job-id'        => $post->ID,
							'nonce'         => wp_create_nonce( 'jb-approve-job' . $post->ID ),
						),
						admin_url()
					);
					// translators: %s is a job title.
					$actions['jb-approve'] = '<a href="' . esc_url( $url ) . '" aria-label="' . esc_attr( sprintf( __( 'Approve %s', 'jobboardwp' ), $post->post_title ) ) . '">' . __( 'Approve', 'jobboardwp' ) . '</a>';
				}
			}
			return $actions;
		}


		/**
		 * Custom columns for JB Job
		 *
		 * @param array $columns
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function job_columns( $columns ) {
			$additional_columns = array();
			if ( isset( $columns['cb'] ) ) {
				$additional_columns['cb'] = $columns['cb'];
			}

			$additional_columns = array_merge(
				$additional_columns,
				array(
					'title'    => __( '(#ID) Position', 'jobboardwp' ),
					'status'   => __( 'Status', 'jobboardwp' ),
					'location' => __( 'Location', 'jobboardwp' ),
					'filled'   => __( 'Filled', 'jobboardwp' ),
					'featured' => __( 'Featured', 'jobboardwp' ),
					'type'     => __( 'Type', 'jobboardwp' ),
					'category' => __( 'Category', 'jobboardwp' ),
					'posted'   => __( 'Posted', 'jobboardwp' ),
					'expires'  => __( 'Expires', 'jobboardwp' ),
				)
			);

			if ( ! JB()->options()->get( 'job-categories' ) ) {
				unset( $additional_columns['category'] );
			}

			/**
			 * Filters the Jobs ListTable columns on wp-admin JobBoardWP > Jobs screen.
			 *
			 * @since 1.1.0
			 * @hook jb_admin_jobs_listtable_columns
			 *
			 * @param {array} $additional_columns Customized ListTable columns. There are all columns related to JobBoardWP.
			 * @param {array} $columns            Default ListTable columns.
			 *
			 * @return {array} Jobs ListTable columns.
			 */
			return apply_filters( 'jb_admin_jobs_listtable_columns', $additional_columns, $columns );
		}


		/**
		 * Display custom columns for Jobs
		 *
		 * @param string $column_name
		 * @param int $id
		 *
		 * @since 1.0
		 */
		public function job_columns_content( $column_name, $id ) {
			switch ( $column_name ) {
				case 'location':
					$type     = JB()->common()->job()->get_location_type( $id );
					$type_raw = JB()->common()->job()->get_location_type( $id, true );

					switch ( $type_raw ) {
						case '0':
							$location = JB()->common()->job()->get_location_link( $id );
							// translators: %1$s is a location type; %2$s is a location.
							echo wp_kses( sprintf( __( '%1$s (%2$s)', 'jobboardwp' ), $type, $location ), JB()->get_allowed_html( 'wp-admin' ) );
							break;
						case '1':
							echo wp_kses( JB()->common()->job()->get_location_link( $id ), JB()->get_allowed_html( 'wp-admin' ) );
							break;
						case '':
							$location = JB()->common()->job()->get_location( $id );
							echo wp_kses( $location, JB()->get_allowed_html( 'wp-admin' ) );
							break;
					}

					break;
				case 'status':
					$job = get_post( $id );
					if ( ! empty( $job->post_status ) ) {
						$post_status = get_post_status_object( $job->post_status );
						if ( ! empty( $post_status->label ) ) {
							echo esc_html( $post_status->label );
						}
					}
					echo '';
					break;
				case 'type':
					$terms = wp_get_post_terms(
						$id,
						'jb-job-type',
						array(
							'orderby' => 'name',
							'order'   => 'ASC',
							'fields'  => 'names',
						)
					);

					if ( ! empty( $terms ) ) {
						echo esc_html( implode( ', ', $terms ) );
					}
					break;
				case 'category':
					$terms = wp_get_post_terms(
						$id,
						'jb-job-category',
						array(
							'orderby' => 'name',
							'order'   => 'ASC',
							'fields'  => 'names',
						)
					);

					if ( ! empty( $terms ) ) {
						echo esc_html( implode( ', ', $terms ) );
					}
					break;
				case 'posted':
					$posted = JB()->common()->job()->get_posted_date( $id );
					$author = JB()->common()->job()->get_job_author( $id );

					$post        = get_post( $id );
					$filter_link = add_query_arg( 'author', $post->post_author );
					ob_start();
					?>
					<?php echo esc_html( $posted ); ?>
					<br /><?php esc_html_e( 'by ', 'jobboardwp' ); ?>
					<a href="<?php echo esc_attr( $filter_link ); ?>" title="<?php esc_attr_e( 'Filter by author', 'jobboardwp' ); ?>">
						<?php echo esc_html( $author ); ?>
					</a>
					<?php
					ob_end_flush();
					break;
				case 'expires':
					$expiry = JB()->common()->job()->get_expiry_date( $id );
					if ( ! empty( $expiry ) ) {
						echo esc_html( $expiry );
					} else {
						echo '&ndash;';
					}
					break;
				case 'filled':
					if ( JB()->common()->job()->is_filled( $id ) ) {
						echo '&#10004;';
					} else {
						echo '&ndash;';
					}
					break;
				case 'featured':
					if ( JB()->common()->job()->is_featured( $id ) ) {
						echo '&#10004;';
					} else {
						echo '&ndash;';
					}
					break;
			}
		}


		/**
		 * Added sortable columns
		 *
		 * @param array $columns
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function sortable_columns( $columns ) {
			$custom = array(
				'posted'  => 'date',
				'expires' => 'jb-expires',
			);
			return wp_parse_args( $custom, $columns );
		}


		/**
		 * Sorts the admin listing of Job Listings by updating the main query in the request.
		 *
		 * @param array $vars Variables with sort arguments.
		 * @return array
		 *
		 * @since 1.0
		 */
		public function sort_columns( $vars ) {
			if ( isset( $vars['orderby'] ) ) {
				if ( 'jb-expires' === $vars['orderby'] ) {
					$vars = array_merge(
						$vars,
						array(
							'meta_key' => 'jb-expiry-date',
							'orderby'  => 'meta_value',
						)
					);
				}
			}
			return $vars;
		}


		/**
		 * Filters by meta fields.
		 *
		 * @param \WP_Query $wp
		 *
		 * @since 1.0
		 */
		public function filter_meta( $wp ) {
			global $pagenow;

			if ( 'edit.php' !== $pagenow || empty( $wp->query_vars['post_type'] ) || 'jb-job' !== $wp->query_vars['post_type'] ) {
				return;
			}

			if ( isset( $_GET['author'] ) && 0 === absint( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- just get author ID
				$users = get_users(
					array(
						'fields' => 'ids',
					)
				);
				$wp->set( 'author__not_in', $users );
			}

			$is_filled  = isset( $_GET['jb-is-filled'] ) && '' !== $_GET['jb-is-filled'] ? (bool) $_GET['jb-is-filled'] : ''; // phpcs:ignore WordPress.Security.NonceVerification -- just get filled status
			$meta_query = $wp->get( 'meta_query' );
			if ( ! is_array( $meta_query ) ) {
				$meta_query = array();
			}

			// Filter on _filled meta.
			if ( '' !== $is_filled ) {
				if ( $is_filled ) {
					$meta_query[] = array(
						'relation' => 'OR',
						array(
							'key'   => 'jb-is-filled',
							'value' => true,
						),
						array(
							'key'   => 'jb-is-filled',
							'value' => 1,
						),
					);
				} else {
					$meta_query[] = array(
						'relation' => 'OR',
						array(
							'key'   => 'jb-is-filled',
							'value' => false,
						),
						array(
							'key'   => 'jb-is-filled',
							'value' => 0,
						),
						array(
							'key'     => 'jb-is-filled',
							'compare' => 'NOT EXISTS',
						),
					);
				}
			}

			// Filter on featured meta.
			$is_featured = isset( $_GET['jb-is-featured'] ) && '' !== $_GET['jb-is-featured'] ? (bool) $_GET['jb-is-featured'] : ''; // phpcs:ignore WordPress.Security.NonceVerification -- just get filled status
			if ( '' !== $is_featured ) {
				if ( $is_featured ) {
					$meta_query[] = array(
						'relation' => 'OR',
						array(
							'key'   => 'jb-is-featured',
							'value' => true,
						),
						array(
							'key'   => 'jb-is-featured',
							'value' => 1,
						),
					);
				} else {
					$meta_query[] = array(
						'relation' => 'OR',
						array(
							'key'   => 'jb-is-featured',
							'value' => false,
						),
						array(
							'key'   => 'jb-is-featured',
							'value' => 0,
						),
						array(
							'key'     => 'jb-is-featured',
							'compare' => 'NOT EXISTS',
						),
					);
				}
			}

			// Set new meta query.
			if ( ! empty( $meta_query ) ) {
				$wp->set( 'meta_query', $meta_query );
			}
		}
	}
}
