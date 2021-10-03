<?php
namespace jb\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


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
		function __construct() {
			add_filter( 'display_post_states', [ &$this, 'add_display_post_states' ], 10, 2 );

			add_action( 'restrict_manage_posts', [ $this, 'display_jobs_meta_filters' ] );

			add_filter( 'manage_edit-jb-job_columns', [ &$this, 'job_columns' ] );
			add_action( 'manage_jb-job_posts_custom_column', [ &$this, 'job_columns_content' ], 10, 3 );
			add_filter( 'manage_edit-jb-job_sortable_columns', [ $this, 'sortable_columns' ] );
			add_filter( 'bulk_actions-edit-jb-job', [ &$this, 'remove_from_bulk_actions' ], 10, 1 );
			add_filter( 'handle_bulk_actions-edit-jb-job', [ &$this, 'custom_bulk_action_handler' ], 10, 3 );

			add_action( 'admin_notices', [ &$this, 'after_bulk_action_notice' ] );

			add_filter( 'views_edit-jb-job', [ &$this, 'replace_list_table' ], 10, 1 );
			add_filter( 'post_row_actions', [ &$this, 'remove_quick_edit' ] , 10, 2 );

			add_filter( 'request', [ $this, 'sort_columns' ] );
			add_action( 'parse_query', [ $this, 'filter_meta' ] );
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
		function add_display_post_states( $post_states, $post ) {
			if ( $post->post_type == 'page' ) {
				foreach ( JB()->config()->get( 'core_pages' ) as $page_key => $page_value ) {
					if ( JB()->options()->get( $page_key . '_page' ) == $post->ID ) {
						// translators: %s is a pre-defined page title.
						$post_states[ 'jb_page_' . $page_key ] = sprintf( __( 'JB %s', 'jobboardwp' ), $page_value['title'] );
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
		function display_jobs_meta_filters() {
			global $typenow;

			// Only add the filters for job_listings.
			if ( 'jb-job' !== $typenow ) {
				return;
			}

			if ( JB()->options()->get( 'job-categories' ) ) {
				$categories = get_terms( [
					'taxonomy'      => 'jb-job-category',
					'hide_empty'    => false,
				] );

				if ( ! empty( $categories ) ) {
					$selected_cat = isset( $_GET['jb-job-category'] ) ? sanitize_text_field( wp_unslash( $_GET['jb-job-category'] ) ) : '';

					$dropdown_options = [
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
					];

					echo '<label class="screen-reader-text" for="jb-job-category">' . __( 'Filter by job category', 'jobboardwp' ) . '</label>';
					wp_dropdown_categories( $dropdown_options );
				}
			}

			$types = get_terms( [
				'taxonomy'      => 'jb-job-type',
				'hide_empty'    => false,
			] );

			if ( ! empty( $types ) ) {
				$selected_cat = isset( $_GET['jb-job-type'] ) ? sanitize_text_field( wp_unslash( $_GET['jb-job-type'] ) ) : '';

				$dropdown_options = [
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
				];

				echo '<label class="screen-reader-text" for="jb-job-type">' . __( 'Filter by job type', 'jobboardwp' ) . '</label>';
				wp_dropdown_categories( $dropdown_options );
			}

			$selected = isset( $_GET['jb-is-filled'] ) ? sanitize_text_field( wp_unslash( $_GET['jb-is-filled'] ) ) : '';

			$options = [
				''  => __( 'Select Filled', 'jobboardwp' ),
				'1' => __( 'Filled', 'jobboardwp' ),
				'0' => __( 'Not Filled', 'jobboardwp' ),
			]; ?>

			<select name="jb-is-filled" id="dropdown_jb-is-filled">
				<?php foreach ( $options as $k => $v ) { ?>
					<option value="<?php echo esc_attr( $k ) ?>" <?php selected( $k, $selected ) ?>>
						<?php echo esc_html( $v ) ?>
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
		function after_bulk_action_notice() {
			if ( ! empty( $_REQUEST['jb-approved'] ) ) {
				$approved_count = intval( $_REQUEST['jb-approved'] );
				// translators: %s is the count of approved jobs.
				printf( '<div class="jb-admin-notice notice updated fade">' .
						_n( '<p>%s job is approved.</p>',
							'<p>%s jobs are approved.</p>',
							$approved_count,
							'jobboardwp'
						) . '</div>', $approved_count );
			} elseif ( ! empty( $_REQUEST['jb-deleted'] ) ) {
				$deleted_count = intval( $_REQUEST['jb-deleted'] );
				// translators: %s is the count of deleted jobs.
				printf( '<div class="jb-admin-notice notice updated fade">' .
						_n( '<p>%s job is deleted.</p>',
							'<p>%s jobs are deleted.</p>',
							$deleted_count,
							'jobboardwp'
						) . '</div>', $deleted_count );
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
		function custom_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
			if ( $doaction == 'jb-approve' ) {
				$app_ids = [];
				foreach ( $post_ids as $post_id ) {
					$post = get_post( $post_id );
					if ( $post->post_status != 'pending' ) {
						continue;
					}
					$app_ids[] = $post_id;

					$args = [
						'ID'            => $post_id,
						'post_status'   => 'publish',
					];

					// a fix for restored from trash pending jobs
					if ( '__trashed' === substr( $post->post_name, 0, 9 ) ) {
						$args['post_name'] = sanitize_title( $post->post_title );
					}

					wp_update_post( $args );

					delete_post_meta( $post_id, 'jb-had-pending' );

					$post = get_post( $post_id );
					$user = get_userdata( $post->post_author );
					if ( ! empty( $user ) && ! is_wp_error( $user ) ) {
						JB()->common()->mail()->send( $user->user_email, 'job_approved', [
							'job_id'        => $post_id,
							'job_title'     => $post->post_title,
							'view_job_url'  => get_permalink( $post ),
						] );
					}

					do_action( 'jb_job_is_approved', $post_id, $post );
				}
				$redirect_to = add_query_arg( 'jb-approved', count( $post_ids ), $redirect_to );
			} elseif ( $doaction == 'jb-delete' ) {
				foreach ( $post_ids as $post_id ) {
					wp_delete_post( $post_id, true );
				}
				$redirect_to = add_query_arg( 'jb-deleted', count( $post_ids ), $redirect_to );
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
		function remove_from_bulk_actions( $actions ) {
			unset( $actions['edit'] );

			$actions = [ 'jb-approve' => __( 'Approve', 'jobboardwp' ), ] + $actions;
			$actions = $actions + [ 'jb-delete' => __( 'Delete permanently', 'jobboardwp' ), ];
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
		function replace_list_table( $views ) {
			global $wp_list_table;

			$total_items = $wp_list_table->get_pagination_arg( 'total_items' );
			$total_pages = $wp_list_table->get_pagination_arg( 'total_pages' );
			$per_page = $wp_list_table->get_pagination_arg( 'per_page' );

			$wp_list_table = new List_Table();

			$wp_list_table->set_pagination_args( [
				'total_items' => $total_items,
				'total_pages' => $total_pages,
				'per_page'    => $per_page,
			] );

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
		function remove_quick_edit( $actions, $post ) {
			if ( $post->post_type == 'jb-job' ) {
				unset( $actions['inline hide-if-no-js'] );

				if ( $post->post_status == 'pending' ) {
					// translators: %s is a job title.
					$actions['jb-approve'] = '<a href="' . esc_attr( add_query_arg( ['jb_adm_action' => 'approve_job', 'job-id' => $post->ID, 'nonce' => wp_create_nonce( 'jb-approve-job' . $post->ID ) ], admin_url() ) ) . '" aria-label="' . esc_attr( sprintf( __( 'Approve %s',  'jobboardwp' ), $post->post_title ) ) . '">' . __( 'Approve',  'jobboardwp' ) . '</a>';
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
		function job_columns( $columns ) {

			$additional_columns = [];
			if ( isset( $columns['cb'] ) ) {
				$additional_columns['cb'] = $columns['cb'];
			}

			$additional_columns = array_merge( $additional_columns, [
				'title'     => __( '(#ID) Position', 'jobboardwp' ),
				'status'    => __( 'Status', 'jobboardwp' ),
				'location'  => __( 'Location', 'jobboardwp' ),
				'filled'    => __( 'Filled', 'jobboardwp' ),
				'type'      => __( 'Type', 'jobboardwp' ),
				'category'  => __( 'Category', 'jobboardwp' ),
				'posted'    => __( 'Posted', 'jobboardwp' ),
				'expires'   => __( 'Expires', 'jobboardwp' ),
			] );

			if ( ! JB()->options()->get( 'job-categories' ) ) {
				unset( $additional_columns['category'] );
			}

			return $additional_columns;
		}


		/**
		 * Display custom columns for Jobs
		 *
		 * @param string $column_name
		 * @param int $id
		 *
		 * @since 1.0
		 */
		function job_columns_content( $column_name, $id ) {
			switch ( $column_name ) {
				case 'location':
					$type = JB()->common()->job()->get_location_type( $id );
					$type_raw = JB()->common()->job()->get_location_type( $id, true );

					switch ( $type_raw ) {
						case '0': {
							$location = JB()->common()->job()->get_location_link( JB()->common()->job()->get_location( $id ) );

							// translators: %1$s is a location type; %2$s is a location.
							printf( __( '%1$s (%2$s)', 'jobboardwp' ), $type, $location );

							break;
						}
						case '1': {
							$location = JB()->common()->job()->get_location( $id );
							echo $location;
							break;
						}
						case '': {
							$location = JB()->common()->job()->get_location( $id );
							echo $location;
							break;
						}
					}

					break;
				case 'status':
					$job = get_post( $id );

					if ( ! empty( $job->post_status ) ) {
						$post_status = get_post_status_object( $job->post_status );
						echo ! empty( $post_status->label ) ? $post_status->label : '';
					}

					echo '';
					break;
				case 'type':
					//echo '<div class="jb-job-types">' . JB()->common()->job()->display_types( $id ) . '</div>';
					$terms = wp_get_post_terms(
						$id,
						'jb-job-type',
						[
							'orderby'   => 'name',
							'order'     => 'ASC',
							'fields'    => 'names'
						]
					);

					if ( ! empty( $terms ) ) {
						echo implode( ', ', $terms );
					}
					break;
				case 'category':
					$terms = wp_get_post_terms(
						$id,
						'jb-job-category',
						[
							'orderby'   => 'name',
							'order'     => 'ASC',
							'fields'    => 'names'
						]
					);

					if ( ! empty( $terms ) ) {
						echo implode( ',', $terms );
					}
					break;
				case 'posted':
					$posted = JB()->common()->job()->get_posted_date( $id );
					$author = JB()->common()->job()->get_job_author( $id );

					$post = get_post( $id );
					// translators: %1$s is a posted job date. %2$s is an author URL and %3$s is Author display name
					printf( __( '%1$s <br />by <a href="%2$s" title="Filter by author">%3$s</a>', 'jobboardwp' ), $posted, esc_url( add_query_arg( 'author', $post->post_author ) ), $author );
					break;
				case 'expires':
					$expiry = JB()->common()->job()->get_expiry_date( $id );
					if ( ! empty( $expiry ) ) {
						echo $expiry;
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
		function sortable_columns( $columns ) {
			$custom = [
				'posted'    => 'date',
				'expires'   => 'jb-expires',
			];
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
		function sort_columns( $vars ) {
			if ( isset( $vars['orderby'] ) ) {
				if ( 'jb-expires' === $vars['orderby'] ) {
					$vars = array_merge(
						$vars,
						[
							'meta_key' => 'jb-expiry-date',
							'orderby'  => 'meta_value',
						]
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
		function filter_meta( $wp ) {
			global $pagenow;

			if ( 'edit.php' !== $pagenow || empty( $wp->query_vars['post_type'] ) || 'jb-job' !== $wp->query_vars['post_type'] ) {
				return;
			}

			if ( isset( $_GET['author'] ) && '0' === $_GET['author'] ) {
				$users = get_users( [
					'fields' => 'ids',
				] );
				$wp->set( 'author__not_in', $users );
			}

			$is_filled = isset( $_GET['jb-is-filled'] ) && '' !== $_GET['jb-is-filled'] ? absint( $_GET['jb-is-filled'] ) : false;
			$meta_query = $wp->get( 'meta_query' );
			if ( ! is_array( $meta_query ) ) {
				$meta_query = [];
			}

			// Filter on _filled meta.
			if ( false !== $is_filled ) {
				$meta_query[] = [
					'key'   => 'jb-is-filled',
					'value' => $is_filled,
				];
			}

			// Set new meta query.
			if ( ! empty( $meta_query ) ) {
				$wp->set( 'meta_query', $meta_query );
			}
		}
	}
}