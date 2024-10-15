<?php
namespace jb\admin;

use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\admin\List_Table' ) ) {

	/**
	 * Class List_Table
	 *
	 * @package jb\admin
	 */
	class List_Table extends \WP_Posts_List_Table {

		// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
		/**
		 * List_Table constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = array() ) {
			parent::__construct( $args );
		}
		// phpcs:enable Generic.CodeAnalysis.UselessOverridingMethod

		/**
		 * @param array $args
		 */
		public function public_set_pagination_args( $args = array() ) {
			$this->set_pagination_args( $args );
		}

		/**
		 * Change the title column content at wp-admin Jobs page
		 *
		 * @param WP_Post $post
		 *
		 * @since 1.0
		 */
		public function column_title( $post ) {
			$can_edit_post = current_user_can( 'edit_post', $post->ID );
			?>
			<div class="jb-job-data">
				<div class="jb-job-title-company">
					<strong>
						<?php
						$title = _draft_or_post_title();

						if ( $can_edit_post && 'trash' !== $post->post_status ) {
							printf(
								'(#%1$s)&nbsp;<a class="row-title" href="%2$s" aria-label="%3$s">%4$s</a>',
								esc_html( $post->ID ),
								esc_attr( get_edit_post_link( $post->ID ) ),
								// translators: %s: Post title.
								esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)', 'jobboardwp' ), $title ) ),
								$title // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  -- already escaped via `_draft_or_post_title()`
							);
						} else {
							printf(
								'<span>%s</span>',
								$title // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  -- already escaped via `_draft_or_post_title()`
							);
						}
						?>
					</strong>
					<?php
					echo "\n";

					$company_name    = get_post_meta( $post->ID, 'jb-company-name', true );
					$company_tagline = get_post_meta( $post->ID, 'jb-company-tagline', true );
					$company_website = get_post_meta( $post->ID, 'jb-company-website', true );

					if ( ! empty( $company_website ) ) {
						printf(
							'<div class="company"><span title="%1$s"><a href="%2$s">%3$s</a></span></div>' . "\n",
							esc_attr( $company_tagline ),
							esc_url( $company_website ),
							esc_html( $company_name )
						);
					} else {
						printf(
							'<div class="company"><span title="%1$s">%2$s</span></div>' . "\n",
							esc_attr( $company_tagline ),
							esc_html( $company_name )
						);
					}
					?>
				</div>
			</div>

			<?php
			if ( $can_edit_post && 'trash' !== $post->post_status ) {
				$lock_holder = wp_check_post_lock( $post->ID );

				if ( $lock_holder ) {
					$lock_holder   = get_userdata( $lock_holder );
					$locked_avatar = get_avatar( $lock_holder->ID, 18 );
					// translators: %s: User's display name.
					$locked_text = sprintf( __( '%s is currently editing', 'jobboardwp' ), $lock_holder->display_name );
				} else {
					$locked_avatar = '';
					$locked_text   = '';
				}
				?>
				<div class="locked-info">
					<span class="locked-avatar"><?php echo wp_kses( $locked_avatar, JB()->get_allowed_html( 'wp-admin' ) ); ?></span>&nbsp;
					<span class="locked-text"><?php echo esc_html( $locked_text ); ?></span>
				</div>
				<?php
				echo "\n";
			}
		}
	}
}
