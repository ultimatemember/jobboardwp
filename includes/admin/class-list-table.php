<?php
namespace jb\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\admin\List_Table' ) ) {


	/**
	 * Class List_Table
	 *
	 * @package jb\admin
	 */
	class List_Table extends \WP_Posts_List_Table {


		/**
		 * List_Table constructor.
		 *
		 * @param array $args
		 */
		function __construct( $args = [] ) {
			parent::__construct( $args );
		}


		/**
		 * Change the title column content at wp-admin Jobs page
		 *
		 * @param \WP_Post $post
		 *
		 * @since 1.0
		 */
		function column_title( $post ) {

			$can_edit_post = current_user_can( 'edit_post', $post->ID ); ?>

			<div class="jb-job-data">
				<div class="jb-job-title-company">
					<strong>

						<?php $title = _draft_or_post_title();

						if ( $can_edit_post && $post->post_status != 'trash' ) {
							printf(
								'(#%s)&nbsp;<a class="row-title" href="%s" aria-label="%s">%s</a>',
								$post->ID,
								get_edit_post_link( $post->ID ),
								/* translators: %s: Post title. */
								esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)' ), $title ) ),
								$title
							);
						} else {
							printf(
								'<span>%s</span>',
								$title
							);
						} ?>

					</strong>
					<?php echo "\n";

					$company_name = get_post_meta( $post->ID, 'jb-company-name', true );
					$company_tagline = get_post_meta( $post->ID, 'jb-company-tagline', true );
					$company_website = get_post_meta( $post->ID, 'jb-company-website', true );

					if ( ! empty( $company_website ) ) {
						printf( "<div class=\"company\"><span title=\"%s\"><a href=\"%s\">%s</a></span></div>\n", $company_tagline, $company_website, $company_name );
					} else {
						printf( "<div class=\"company\"><span title=\"%s\">%s</span></div>\n", $company_tagline, $company_name );
					} ?>

				</div>
			</div>

			<?php if ( $can_edit_post && $post->post_status != 'trash' ) {
				$lock_holder = wp_check_post_lock( $post->ID );

				if ( $lock_holder ) {
					$lock_holder   = get_userdata( $lock_holder );
					$locked_avatar = get_avatar( $lock_holder->ID, 18 );
					/* translators: %s: User's display name. */
					$locked_text = esc_html( sprintf( __( '%s is currently editing' ), $lock_holder->display_name ) );
				} else {
					$locked_avatar = '';
					$locked_text   = '';
				}

				echo '<div class="locked-info"><span class="locked-avatar">' . $locked_avatar . '</span> <span class="locked-text">' . $locked_text . "</span></div>\n";
			}

		}
	}
}