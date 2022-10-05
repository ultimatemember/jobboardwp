<?php
namespace jb\widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class Recent_Jobs
 *
 * Widget "JobBoardWP - Recent Jobs".
 *
 * @package jb\widgets
 */
class Recent_Jobs extends \WP_Widget {


	/**
	 * Recent_Jobs constructor.
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
		$default = array(
			'number'       => 5,
			'type'         => array(),
			'remote_only'  => false,
			'orderby'      => 'date',
			'hide_filled'  => JB()->options()->get( 'jobs-list-hide-filled' ),
			'no_logo'      => JB()->options()->get( 'jobs-list-no-logo' ),
			'no_job_types' => JB()->options()->get( 'jobs-list-hide-job-types' ),
		);

		if ( JB()->options()->get( 'job-categories' ) ) {
			$default['category'] = array();
		}

		$shortcode_args_line = '';
		$shortcode_args      = wp_parse_args( $instance, $default );
		foreach ( $shortcode_args as $key => $arg ) {
			if ( ! in_array( $key, array( 'number', 'category', 'type', 'remote_only', 'orderby', 'hide_filled', 'no_logo', 'no_job_types' ), true ) ) {
				continue;
			}

			if ( is_array( $arg ) ) {
				$arg = implode( ',', $arg );
			} elseif ( true === $arg ) {
				$arg = 1;
			} elseif ( false === $arg ) {
				$arg = 0;
			}
			$shortcode_args_line .= " {$key}=\"{$arg}\"";
		}

		$content = apply_shortcodes( "[jb_recent_jobs{$shortcode_args_line}]" );

		if ( empty( $content ) ) {
			return;
		}

		echo wp_kses_post( $args['before_widget'] );

		$title = array_key_exists( 'title', $instance ) ? $instance['title'] : '';
		if ( ! empty( $title ) ) {
			/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
			$title = apply_filters( 'widget_title', $title );
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		echo wp_kses( $content, JB()->get_allowed_html( 'templates' ) );

		echo wp_kses_post( $args['after_widget'] );
	}


	/**
	 * Outputs the settings update form.
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$default = array(
			'title'        => __( 'Recent Jobs', 'jobboardwp' ),
			'number'       => 5,
			'type'         => array(),
			'remote_only'  => false,
			'orderby'      => 'date',
			'hide_filled'  => JB()->options()->get( 'jobs-list-hide-filled' ),
			'no_logo'      => JB()->options()->get( 'jobs-list-no-logo' ),
			'no_job_types' => JB()->options()->get( 'jobs-list-hide-job-types' ),
		);

		if ( JB()->options()->get( 'job-categories' ) ) {
			$default['category'] = array();
		}

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
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'jobboardwp' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $args['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of jobs to show:', 'jobboardwp' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" min="1" max="99" value="<?php echo esc_attr( $args['number'] ); ?>" />
		</p>

		<?php if ( ! empty( $categories ) ) { ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>"><?php esc_html_e( 'Job Category:', 'jobboardwp' ); ?></label>
				<select multiple class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>[]">
					<?php foreach ( $categories as $jbc ) { ?>
						<option value="<?php echo esc_attr( $jbc->term_id ); ?>" <?php selected( in_array( $jbc->term_id, $args['category'], true ) ); ?>><?php echo esc_html( $jbc->name ); ?></option>
					<?php } ?>
				</select>
			</p>
		<?php } ?>

		<?php if ( ! empty( $types ) ) { ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php esc_html_e( 'Job Type:', 'jobboardwp' ); ?></label>
				<select multiple class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>[]">
					<?php foreach ( $types as $jbt ) { ?>
						<option value="<?php echo esc_attr( $jbt->term_id ); ?>" <?php selected( in_array( $jbt->term_id, $args['type'], true ) ); ?>><?php echo esc_html( $jbt->name ); ?></option>
					<?php } ?>
				</select>
			</p>
		<?php } ?>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'remote_only' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'remote_only' ) ); ?>" value="1" <?php checked( $args['remote_only'] ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'remote_only' ) ); ?>"><?php esc_html_e( 'Remote only', 'jobboardwp' ); ?></label>
		</p>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'hide_filled' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_filled' ) ); ?>" value="1" <?php checked( $args['hide_filled'] ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_filled' ) ); ?>"><?php esc_html_e( 'Hide filled', 'jobboardwp' ); ?></label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php esc_html_e( 'Order by:', 'jobboardwp' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
				<option value="expiry_date" <?php selected( 'expiry_date', $args['orderby'] ); ?>><?php esc_html_e( 'Expiry date', 'jobboardwp' ); ?></option>
				<option value="date" <?php selected( 'date', $args['orderby'] ); ?>><?php esc_html_e( 'Posting date', 'jobboardwp' ); ?></option>
			</select>
		</p>

		<p>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'no_logo' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'no_logo' ) ); ?>" type="checkbox" value="1" <?php checked( $args['no_logo'] ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'no_logo' ) ); ?>"><?php esc_html_e( 'Hide logos', 'jobboardwp' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'no_job_types' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'no_job_types' ) ); ?>" value="1" <?php checked( $args['no_job_types'] ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'no_job_types' ) ); ?>"><?php esc_html_e( 'Hide job types', 'jobboardwp' ); ?></label>
		</p>

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

		$instance['title']  = empty( $new_instance['title'] ) ? '' : sanitize_text_field( $new_instance['title'] );
		$instance['number'] = empty( $new_instance['number'] ) ? 5 : absint( $new_instance['number'] );

		if ( JB()->options()->get( 'job-categories' ) ) {
			$instance['category'] = empty( $new_instance['category'] ) ? array() : array_map( 'absint', $new_instance['category'] );
		}

		$instance['type']         = empty( $new_instance['type'] ) ? array() : array_map( 'absint', $new_instance['type'] );
		$instance['remote_only']  = empty( $new_instance['remote_only'] ) ? false : true;
		$instance['orderby']      = empty( $new_instance['orderby'] ) ? 'date' : sanitize_key( $new_instance['orderby'] );
		$instance['hide_filled']  = empty( $new_instance['hide_filled'] ) ? false : true;
		$instance['no_logo']      = empty( $new_instance['no_logo'] ) ? false : true;
		$instance['no_job_types'] = empty( $new_instance['no_job_types'] ) ? false : true;

		return $instance;
	}
}
