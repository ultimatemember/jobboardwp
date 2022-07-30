<?php
/**
 * Widget "JobBoardWP - Recent Jobs".
 *
 * @package jb\widgets
 */

namespace jb\widgets;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Recent_Jobs
 */
class Recent_Jobs extends \WP_Widget {

	/**
	 * Widget constructor.
	 */
	public function __construct() {
		parent::__construct(
			'jb_recent_jobs',
			__( 'JobBoardWP - Recent Jobs', 'jobboardwp' ),
			array(
				'classname'   => 'jb-recent-jobs',
				'description' => __( 'JobBoardWP - Recent Jobs', 'jobboardwp' ),
			)
		);
	}

	/**
	 * Echoes the widget content.
	 *
	 * @uses  The "Recent Jobs" shortcode
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title', 'before_widget' and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		echo wp_kses_post( $args['before_widget'] );

		$title = array_key_exists( 'title', $instance ) ? $instance['title'] : '';
		if ( ! empty( $title ) ) {
			$title = apply_filters( 'widget_title', $title );
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		echo JB()->frontend()->shortcodes()->recent_jobs( $instance );

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {

		$default = array(
			'title'         => __( 'Recent Jobs', 'jobboardwp' ),
			'number'        => 5,
			'category'      => '',
			'job_type'      => '',
			'location_type' => '',
			'orderby'       => 'date',
			'order'         => 'desc',
			'no_logo'       => JB()->options()->get( 'jobs-list-no-logo' ),
			'no_job_types'  => JB()->options()->get( 'jobs-list-hide-job-types' ),
		);

		$args = wp_parse_args( $instance, $default );


		if ( JB()->options()->get( 'job-categories' ) ) {
			$categories = get_terms(
				array(
					'taxonomy'   => 'jb-job-category',
					'hide_empty' => false,
				)
			);
		}

		$types = get_terms(
			array(
				'taxonomy'   => 'jb-job-type',
				'hide_empty' => false,
			)
		);
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'jobboardwp' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $args['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of jobs to show', 'jobboardwp' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" min="1" max="99" value="<?php echo esc_attr( $args['number'] ); ?>" />
		</p>

		<?php if ( ! empty( $categories ) ) { ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>"><?php esc_html_e( 'Job Category', 'jobboardwp' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>">
				<option value="" <?php selected( empty( $args['category'] ) ); ?>><?php esc_html_e( '~ All categories ~', 'jobboardwp' ); ?></option>

				<?php foreach ( $categories as $jbc ) { ?>
				<option value="<?php echo esc_attr( $jbc->term_id ); ?>" <?php selected( $jbc->term_id, $args['category'] ); ?>><?php echo esc_html( $jbc->name ); ?></option>
				<?php } ?>

			</select>
		</p>
		<?php } ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'job_type' ) ); ?>"><?php esc_html_e( 'Job Type', 'jobboardwp' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'job_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'job_type' ) ); ?>">
				<option value="" <?php selected( empty( $args['job_type'] ) ); ?>><?php esc_html_e( '~ All types ~', 'jobboardwp' ); ?></option>

				<?php foreach ( $types as $jbt ) { ?>
				<option value="<?php echo esc_attr( $jbt->term_id ); ?>" <?php selected( $jbt->term_id, $args['job_type'] ); ?>><?php echo esc_html( $jbt->name ); ?></option>
				<?php } ?>

			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'location_type' ) ); ?>"><?php esc_html_e( 'Location Type', 'jobboardwp' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'location_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'location_type' ) ); ?>">
				<option value="" <?php selected( empty( $args['location_type'] ) ); ?>><?php esc_html_e( '~ Any location ~', 'jobboardwp' ); ?></option>
				<option value="1" <?php selected( ! empty( $args['location_type'] ) ); ?>><?php esc_html_e( 'Remote', 'jobboardwp' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php esc_html_e( 'Order by', 'jobboardwp' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
				<option value="expiry_date" <?php selected( 'expiry_date', $args['orderby'] ); ?>><?php esc_html_e( 'Closing date', 'jobboardwp' ); ?></option>
				<option value="date" <?php selected( 'date', $args['orderby'] ); ?>><?php esc_html_e( 'Posting date', 'jobboardwp' ); ?></option>
				<option value="rand" <?php selected( 'rand', $args['orderby'] ); ?>><?php esc_html_e( 'Random', 'jobboardwp' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"><?php esc_html_e( 'Order', 'jobboardwp' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
				<option value="asc" <?php selected( 'asc', $args['order'] ); ?>><?php esc_html_e( 'ASC', 'jobboardwp' ); ?></option>
				<option value="desc" <?php selected( 'desc', $args['order'] ); ?>><?php esc_html_e( 'DESC', 'jobboardwp' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'no_logo' ) ); ?>"><?php esc_html_e( 'Hide Logos', 'jobboardwp' ); ?></label>
			<?php if ( JB()->options()->get( 'jobs-list-no-logo' ) ) { ?>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'no_logo_disabled' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'no_logo_disabled' ) ); ?>" type="checkbox" value="1" checked disabled />
				<input id="<?php echo esc_attr( $this->get_field_id( 'no_logo' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'no_logo' ) ); ?>" type="hidden" value="1" />
			<?php } else { ?>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'no_logo' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'no_logo' ) ); ?>" type="checkbox" value="1" <?php checked( $args['no_logo'] ); ?> />
			<?php } ?>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'no_job_types' ) ); ?>"><?php esc_html_e( 'Hide job types', 'jobboardwp' ); ?></label>
			<?php if ( JB()->options()->get( 'jobs-list-hide-job-types' ) ) { ?>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'no_job_types_disabled' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'no_job_types_disabled' ) ); ?>" type="checkbox" value="1" checked disabled />
				<input id="<?php echo esc_attr( $this->get_field_id( 'no_job_types' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'no_job_types' ) ); ?>" type="hidden" value="1" />
			<?php } else { ?>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'no_job_types' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'no_job_types' ) ); ?>" type="checkbox" value="1" <?php checked( $args['no_job_types'] ); ?> />
			<?php } ?>
		</p>

		<p style="font-size: 13px;"><?php esc_html_e( 'You may manage basic settings', 'jobboardwp' ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=jb-settings&section=jobs' ) ); ?>" title="<?php esc_attr_e( 'Jobs List', 'jobboardwp' ); ?>" target="_blank"><?php esc_html_e( 'here', 'jobboardwp' ); ?></a></p>

		<?php
	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * @param array $new_instance New settings for this instance.
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']         = empty( $new_instance['title'] ) ? '' : sanitize_text_field( $new_instance['title'] );
		$instance['number']        = empty( $new_instance['number'] ) ? 5 : absint( $new_instance['number'] );
		$instance['category']      = empty( $new_instance['category'] ) ? '' : absint( $new_instance['category'] );
		$instance['job_type']      = empty( $new_instance['job_type'] ) ? '' : absint( $new_instance['job_type'] );
		$instance['location_type'] = empty( $new_instance['location_type'] ) ? '' : 1;
		$instance['orderby']       = empty( $new_instance['orderby'] ) ? 'date' : sanitize_key( $new_instance['orderby'] );
		$instance['order']         = empty( $new_instance['order'] ) ? 'desc' : sanitize_key( $new_instance['order'] );
		$instance['no_logo']       = empty( $new_instance['no_logo'] ) ? 0 : 1;
		$instance['no_job_types']  = empty( $new_instance['no_job_types'] ) ? 0 : 1;

		return $instance;
	}

}
