<?php namespace jb\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\frontend\Forms' ) ) {


	/**
	 * Class Forms
	 *
	 * @package jb\frontend
	 */
	class Forms {


		/**
		 * @var bool
		 *
		 * @since 1.0
		 */
		public $form_data;


		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		public $error_class = 'jb-form-error-row';


		/**
		 * @var array
		 */
		public $errors = array();


		/**
		 * @var array
		 */
		public $notices = array();


		/**
		 * @var array
		 *
		 * @since 1.0
		 */
		public $types = array(
			'text',
			'password',
			'hidden',
			'select',
			'wp_editor',
			'conditional_radio',
			'media',
			'label',
		);


		/**
		 * Forms constructor.
		 *
		 * @param bool $form_data
		 */
		public function __construct( $form_data = false ) {
			if ( $form_data ) {
				$this->form_data = $form_data;
			}
		}


		/**
		 * Set Form Data
		 *
		 * @param array $data
		 *
		 * @return $this
		 *
		 * @since 1.0
		 */
		public function set_data( $data ) {
			$this->form_data = $data;
			return $this;
		}


		/**
		 * Render form
		 *
		 *
		 * @param bool $echo
		 * @return string
		 *
		 * @since 1.0
		 */
		public function display( $echo = true ) {
			if ( empty( $this->form_data['fields'] ) && empty( $this->form_data['sections'] ) && empty( $this->form_data['hiddens'] ) ) {
				return '';
			}

			$id     = isset( $this->form_data['id'] ) ? $this->form_data['id'] : 'jb-frontend-form-' . uniqid();
			$name   = isset( $this->form_data['name'] ) ? $this->form_data['name'] : $id;
			$action = isset( $this->form_data['action'] ) ? $this->form_data['action'] : '';
			$method = isset( $this->form_data['method'] ) ? $this->form_data['method'] : 'post';

			$data_attrs = isset( $this->form_data['data'] ) ? $this->form_data['data'] : array();
			$data_attr  = '';
			foreach ( $data_attrs as $key => $val ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $val ) . '" ';
			}

			$hidden = '';
			if ( ! empty( $this->form_data['hiddens'] ) ) {
				foreach ( $this->form_data['hiddens'] as $field_id => $value ) {
					$hidden .= $this->render_hidden( $field_id, $value );
				}
			}

			$fields = '';
			if ( ! empty( $this->form_data['fields'] ) ) {
				foreach ( $this->form_data['fields'] as $data ) {
					if ( ! $this->validate_type( $data ) ) {
						continue;
					}

					$fields .= $this->render_form_row( $data );
				}
			} else {
				if ( ! empty( $this->form_data['sections'] ) ) {
					foreach ( $this->form_data['sections'] as $section_key => $section_data ) {
						$section_data['key'] = $section_key;
						$fields             .= $this->render_section( $section_data );
					}
				}
			}

			$buttons = '';
			if ( ! empty( $this->form_data['buttons'] ) ) {
				foreach ( $this->form_data['buttons'] as $field_id => $data ) {
					$buttons .= $this->render_button( $field_id, $data );
				}
			}

			ob_start();

			if ( $this->has_notices() ) {
				foreach ( $this->get_notices() as $notice ) {
					?>
					<span class="jb-frontend-form-notice"><?php echo wp_kses( $notice, JB()->get_allowed_html( 'templates' ) ); ?></span>
					<?php
				}
			}

			if ( $this->has_error( 'global' ) ) {
				foreach ( $this->get_error( 'global' ) as $error ) {
					?>
					<span class="jb-frontend-form-error"><?php echo wp_kses( $error, JB()->get_allowed_html( 'templates' ) ); ?></span>
					<?php
				}
			}

			$move_form_tag = apply_filters( 'jb_forms_move_form_tag', false );

			if ( ! $move_form_tag ) {
				echo wp_kses( '<form action="' . esc_attr( $action ) . '" method="' . esc_attr( $method ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" class="jb-form" ' . $data_attr . '>', JB()->get_allowed_html( 'templates' ) );
			}

			echo wp_kses( $fields . $hidden . '<div class="jb-form-buttons-section">' . $buttons . '</div>', JB()->get_allowed_html( 'templates' ) );
			?>

			</form>

			<?php
			remove_all_filters( 'jb_forms_move_form_tag' );

			if ( $echo ) {
				ob_get_flush();
				return '';
			} else {
				return ob_get_clean();
			}
		}


		/**
		 * Validate type of the field
		 *
		 * @param array $data
		 *
		 * @return bool
		 *
		 * @since 1.0
		 */
		public function validate_type( $data ) {
			return ( ! empty( $data['type'] ) && in_array( $data['type'], $this->types, true ) );
		}


		/**
		 * Get field value
		 *
		 * @param array $field_data
		 * @param string $i
		 * @return string|array
		 *
		 * @since 1.0
		 */
		public function get_field_value( $field_data, $i = '' ) {
			// phpcs:disable WordPress.Security.NonceVerification -- there is already verified
			$default = '';
			$default = isset( $field_data[ 'default' . $i ] ) ? $field_data[ 'default' . $i ] : $default;

			if ( 'checkbox' === $field_data['type'] ) {
				$value = ( isset( $field_data[ 'value' . $i ] ) && '' !== $field_data[ 'value' . $i ] ) ? $field_data[ 'value' . $i ] : $default;
			} else {
				$value = isset( $field_data[ 'value' . $i ] ) ? $field_data[ 'value' . $i ] : $default;
			}

			$name = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			if ( ! empty( $this->form_data['prefix_id'] ) ) {
				$value = isset( $_POST[ $this->form_data['prefix_id'] ][ $name ] ) ? sanitize_text_field( $_POST[ $this->form_data['prefix_id'] ][ $name ] ) : $value;
			} else {
				$value = isset( $_POST[ $name ] ) ? sanitize_text_field( $_POST[ $name ] ) : $value;
			}

			$value = is_string( $value ) ? stripslashes( $value ) : $value;

			if ( ! empty( $value ) ) {
				if ( ! empty( $this->form_data['prefix_id'] ) ) {
					if ( isset( $field_data['encode'] ) && ! isset( $_POST[ $this->form_data['prefix_id'] ][ $name ] ) ) {
						$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE );
					}
				} else {
					if ( isset( $field_data['encode'] ) && ! isset( $_POST[ $name ] ) ) {
						$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE );
					}
				}
			}

			return $value;
			// phpcs:enable WordPress.Security.NonceVerification -- there is already verified
		}


		/**
		 * Render form row
		 *
		 * @param array $data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_form_row( $data ) {
			if ( empty( $data['id'] ) ) {
				return '';
			}

			if ( ! $this->validate_type( $data ) ) {
				return '';
			}

			$field_html = '';
			if ( method_exists( $this, 'render_' . $data['type'] ) ) {
				$field_html = call_user_func( array( &$this, 'render_' . $data['type'] ), $data );
			}

			if ( empty( $field_html ) ) {
				return '';
			}

			$row_classes = array( 'jb-form-row', 'jb-field-' . $data['type'] . '-type' );
			if ( $this->has_error( $data['id'] ) ) {
				$row_classes[] = $this->error_class;
			}

			ob_start();
			?>

			<div class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>">
				<?php echo wp_kses( $this->render_field_label( $data ), JB()->get_allowed_html( 'templates' ) ); ?>

				<span class="jb-form-field-content">
					<?php echo wp_kses( $field_html, JB()->get_allowed_html( 'templates' ) ); ?>

					<?php if ( $this->has_error( $data['id'] ) ) { ?>
						<span class="jb-form-field-error">
							<?php echo wp_kses( $this->get_error( $data['id'] ), JB()->get_allowed_html( 'templates' ) ); ?>
						</span>
					<?php } ?>

				</span>
			</div>

			<?php
			$html = ob_get_clean();
			return $html;
		}


		/**
		 * Render form section
		 *
		 * @param array $data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_section( $data ) {
			$html = '';

			if ( ! empty( $data['title'] ) ) {
				$html .= '<h3 class="jb-form-section-title">' . $data['title'] . '</h3>';
			}

			$html = apply_filters( 'jb_forms_before_render_section', $html, $data, $this->form_data );

			if ( ! empty( $data['wrap_fields'] ) ) {
				$strict = ! empty( $data['strict_wrap_attrs'] ) ? $data['strict_wrap_attrs'] : '';

				$html .= '<div class="jb-form-section-fields-wrapper" data-key="' . esc_attr( $data['key'] ) . '"' . $strict . '>';
			}

			if ( ! empty( $data['fields'] ) ) {
				foreach ( $data['fields'] as $fields_data ) {
					if ( ! $this->validate_type( $fields_data ) ) {
						continue;
					}

					$html .= $this->render_form_row( $fields_data );
				}
			}

			if ( ! empty( $data['wrap_fields'] ) ) {
				$html .= '</div>';
			}

			return $html;
		}


		/**
		 * Render field label
		 *
		 * @param array $data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_label( $data ) {
			return '<p>' . $data['label'] . '</p>';
		}


		/**
		 * Render button
		 *
		 * @param string $id
		 * @param array $data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_button( $id, $data ) {
			$type  = isset( $data['type'] ) ? $data['type'] : 'submit';
			$name  = isset( $data['name'] ) ? $data['name'] : $id;
			$label = isset( $data['label'] ) ? $data['label'] : __( 'Submit', 'jobboardwp' );

			$classes   = array( 'jb-form-button' );
			$classes[] = 'jb-form-button-' . $type;

			$data = isset( $data['data'] ) ? $data['data'] : array();

			$data_attr = '';
			foreach ( $data as $key => $val ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $val ) . '" ';
			}

			ob_start();
			?>

			<label class="screen-reader-text" for="jb-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>

			<?php
			echo wp_kses( '<input id="jb-' . esc_attr( $name ) . '" type="' . esc_attr( $type ) . '" value="' . esc_attr( $label ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '" name="' . esc_attr( $name ) . '" ' . $data_attr . ' />', JB()->get_allowed_html( 'templates' ) );
			return ob_get_clean();
		}


		/**
		 * Render hidden field
		 *
		 * @param string $id
		 * @param string $value
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_hidden( $id, $value ) {
			if ( empty( $value ) ) {
				return '';
			}

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $id;
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$data = array( 'field_id' => $id );

			$data_attr = '';
			foreach ( $data as $key => $val ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $val ) . '" ';
			}

			$name      = $id;
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			$html = "<input type=\"hidden\" $id_attr $name_attr $data_attr $value_attr />";

			return $html;
		}


		/**
		 * Render field label
		 *
		 * @param array $data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_field_label( $data ) {
			if ( empty( $data['label'] ) ) {
				return '';
			}

			if ( 'label' === $data['type'] ) {
				return '';
			}

			$id       = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $data['id'];
			$for_attr = ' for="' . $id . '" ';

			$label        = $data['label'];
			$disable_star = apply_filters( 'jb_frontend_forms_required_star_disabled', false );
			if ( ! empty( $data['required'] ) && ! $disable_star ) {
				$label = $label . '<span class="jb-req" title="' . esc_attr__( 'Required', 'jobboardwp' ) . '">*</span>';
			}

			$helptip = ! empty( $data['helptip'] ) ? ' ' . JB()->helptip( $data['helptip'] ) : '';

			return "<label $for_attr class=\"jb-form-row-label\">{$label}{$helptip}</label>";
		}


		/**
		 * Render media uploader field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_media( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			if ( empty( $field_data['action'] ) ) {
				return '';
			}

			$thumb_w    = get_option( 'thumbnail_size_w' );
			$thumb_h    = get_option( 'thumbnail_size_h' );
			$thumb_crop = get_option( 'thumbnail_crop', false );

			$id = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];

			$name = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			$name = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;

			$img_alt      = isset( $field_data['labels']['img_alt'] ) ? $field_data['labels']['img_alt'] : __( 'Selected image', 'jobboardwp' );
			$select_label = isset( $field_data['labels']['select'] ) ? $field_data['labels']['select'] : __( 'Select file', 'jobboardwp' );
			$change_label = isset( $field_data['labels']['change'] ) ? $field_data['labels']['change'] : __( 'Change', 'jobboardwp' );
			$remove_label = isset( $field_data['labels']['remove'] ) ? $field_data['labels']['remove'] : __( 'Remove', 'jobboardwp' );
			$cancel_label = isset( $field_data['labels']['cancel'] ) ? $field_data['labels']['cancel'] : __( 'Cancel', 'jobboardwp' );

			$wrapper_classes = array( 'jb-uploaded-wrapper', 'jb-' . $id . '-wrapper' );
			if ( ! empty( $field_data['value'] ) ) {
				$wrapper_classes = array_merge( $wrapper_classes, array( 'jb-uploaded', 'jb-' . $id . '-uploaded' ) );
			}
			$wrapper_classes = implode( ' ', $wrapper_classes );

			$img_style = $thumb_crop ? 'style="object-fit: cover;"' : '';

			$uploader_classes = array( 'jb-uploader', 'jb-' . $id . '-uploader' );
			if ( ! empty( $field_data['value'] ) ) {
				$uploader_classes = array_merge( $uploader_classes, array( 'jb-uploaded', 'jb-' . $id . '-uploaded' ) );
			}
			$uploader_classes = implode( ' ', $uploader_classes );

			$value = ! empty( $field_data['value'] ) ? $field_data['value'] : '';

			ob_start();
			?>

			<span class="<?php echo esc_attr( $wrapper_classes ); ?>">
				<span class="jb-uploaded-content-wrapper jb-<?php echo esc_attr( $id ); ?>-image-wrapper" style="width: <?php echo esc_attr( $thumb_w ); ?>px;height: <?php echo esc_attr( $thumb_h ); ?>px;">
					<?php echo wp_kses( '<img src="' . ( ! empty( $field_data['value'] ) ? esc_url( $field_data['value'] ) : '' ) . '" alt="' . esc_attr( $img_alt ) . '" ' . $img_style . ' />', JB()->get_allowed_html( 'templates' ) ); ?>
				</span>
				<a class="jb-cancel-change-media" href="javascript:void(0);"><?php echo esc_html( $cancel_label ); ?></a>
				<a class="jb-change-media" href="javascript:void(0);"><?php echo esc_html( $change_label ); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;
				<a class="jb-clear-media" href="javascript:void(0);"><?php echo esc_html( $remove_label ); ?></a>
			</span>

			<span class="<?php echo esc_attr( $uploader_classes ); ?>">
				<span id="jb_<?php echo esc_attr( $id ); ?>_filelist" class="jb-uploader-dropzone">
					<span><?php esc_html_e( 'Drop file to upload', 'jobboardwp' ); ?></span>
					<span><?php esc_html_e( 'or', 'jobboardwp' ); ?></span>
					<input type="button" class="jb-select-media" data-action="<?php echo esc_attr( $field_data['action'] ); ?>" id="jb_<?php echo esc_attr( $id ); ?>_plupload" value="<?php echo esc_attr( $select_label ); ?>" />
				</span>

				<span id="jb-<?php echo esc_attr( $id ); ?>-errorlist" class="jb-uploader-errorlist"></span>
			</span>
			<input type="hidden" class="jb-media-value" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			<input type="hidden" class="jb-media-value-hash" id="<?php echo esc_attr( $id ); ?>_hash" name="<?php echo esc_attr( $name ); ?>_hash" value="" />

			<?php
			$html = ob_get_clean();
			return $html;
		}


		/**
		 * Render text field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_text( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? 'jb-' . $field_data['size'] . '-field' : 'jb-long-field';
			$class_attr = ' class="jb-forms-field ' . esc_attr( $class ) . '" ';

			$data = array( 'field_id' => $field_data['id'] );

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . $field_data['placeholder'] . '"' : '';
			$required         = ! empty( $field_data['required'] ) ? ' required' : '';

			$name      = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			$html = "<input type=\"text\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required />";

			return $html;
		}


		/**
		 * Render location autocomplete field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_location_autocomplete( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? 'jb-' . $field_data['size'] . '-field' : 'jb-long-field';
			$class_attr = ' class="jb-forms-field jb-location-autocomplete ' . esc_attr( $class ) . '" ';

			$data = array( 'field_id' => $field_data['id'] );

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . $field_data['placeholder'] . '"' : '';
			$required         = ! empty( $field_data['required'] ) ? ' required' : '';

			$name                = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			$name                = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr           = ' name="' . esc_attr( $name ) . '" ';
			$name_loco_data_attr = ' name="' . esc_attr( $name ) . '_data" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			$field_data_data           = $field_data;
			$field_data_data['name']   = $name . '_data';
			$field_data_data['value']  = $field_data['value_data'];
			$field_data_data['encode'] = true;

			$value_data = $this->get_field_value( $field_data_data );
			$value_data = esc_attr( $value_data );

			$html = "<input type=\"text\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required />
					 <input type=\"hidden\" $name_loco_data_attr class=\"jb-location-autocomplete-data\" value=\"$value_data\" />";

			return $html;
		}


		/**
		 * Render password field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_password( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? 'jb-' . $field_data['size'] . '-field' : 'jb-long-field';
			$class_attr = ' class="jb-forms-field ' . esc_attr( $class ) . '" ';

			$data = array( 'field_id' => $field_data['id'] );

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . $field_data['placeholder'] . '"' : '';
			$required         = ! empty( $field_data['required'] ) ? ' required' : '';

			$name      = $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			$html = "<input type=\"password\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required />";

			return $html;
		}


		/**
		 * Render dropdown field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_select( $field_data ) {

			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			if ( empty( $field_data['options'] ) ) {
				return '';
			}

			$multiple = ! empty( $field_data['multi'] ) ? 'multiple' : '';

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$class      = ! empty( $field_data['class'] ) ? ' ' . $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? ' jb-' . $field_data['size'] . '-field' : ' jb-long-field';
			$class_attr = ' class="jb-forms-field' . esc_attr( $class ) . '" ';

			$data = array( 'field_id' => $field_data['id'] );
			$data = ! empty( $field_data['data'] ) ? array_merge( $data, $field_data['data'] ) : $data;

			$data['placeholder'] = ! empty( $data['placeholder'] ) ? $data['placeholder'] : __( 'Please select...', 'jobboardwp' );

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$name             = $field_data['id'];
			$name             = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$hidden_name_attr = ' name="' . esc_attr( $name ) . '" ';
			$name             = $name . ( ! empty( $field_data['multi'] ) ? '[]' : '' );
			$name_attr        = ' name="' . esc_attr( $name ) . '" ';

			$value = $this->get_field_value( $field_data );

			$options = '';
			if ( ! empty( $field_data['options'] ) ) {
				foreach ( $field_data['options'] as $key => $option ) {
					if ( ! empty( $field_data['multi'] ) ) {

						if ( ! is_array( $value ) || empty( $value ) ) {
							$value = array();
						}

						$value = array_map( 'strval', $value );

						$options .= '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( (string) $key, $value, true ), true, false ) . '>' . esc_html( $option ) . '</option>';
					} else {
						$options .= '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $option ) . '</option>';
					}
				}
			}

			$hidden = '';
			if ( ! empty( $multiple ) ) {
				$hidden = "<input type=\"hidden\" $hidden_name_attr value=\"\" />";
			}
			$html = "$hidden<select $multiple $id_attr $name_attr $class_attr $data_attr>$options</select>";

			return $html;
		}


		/**
		 * Render conditional radio
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_conditional_radio( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			if ( empty( $field_data['options'] ) ) {
				return '';
			}

			if ( empty( $field_data['condition_sections'] ) ) {
				return '';
			}

			$id = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? $field_data['size'] : ' jb-long-field';
			$class_attr = ' class="jb-forms-field jb-forms-condition-option' . esc_attr( $class ) . '" ';

			$data = array( 'field_id' => $field_data['id'] );

			$data_attr = '';
			foreach ( $data as $key => $val ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $val ) . '" ';
			}

			$name      = $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value = $this->get_field_value( $field_data );

			$html = '';
			foreach ( $field_data['options'] as $optkey => $option ) {
				$id_attr = ' id="' . $id . '-' . $optkey . '" ';

				$html .= "<label><input type=\"radio\" $id_attr $class_attr $name_attr $data_attr " . checked( $value, $optkey, false ) . ' value="' . esc_attr( $optkey ) . '" />&nbsp;' . $option . '</label>';

				$cond_html = '';
				if ( ! empty( $field_data['condition_sections'][ $optkey ] ) ) {
					foreach ( $field_data['condition_sections'][ $optkey ] as $section_field ) {
						$cond_html .= call_user_func( array( &$this, 'render_' . $section_field['type'] ), $section_field );
					}
				}

				if ( ! empty( $cond_html ) ) {
					$html .= '<span data-visible-if="' . esc_attr( $optkey ) . '">' . $cond_html . '</span>';
				}
			}

			return $html;
		}


		/**
		 * Render WP Editor field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_wp_editor( $field_data ) {

			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $field_data['id'];

			$data = array( 'field_id' => $field_data['id'] );

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= ' data-' . $key . '="' . esc_attr( $value ) . '" ';
			}

			$name = $field_data['id'];
			$name = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;

			$value = $this->get_field_value( $field_data );

			add_filter( 'mce_buttons', array( $this, 'filter_mce_buttons' ), 10, 2 );

			add_action(
				'after_wp_tiny_mce',
				function( $settings ) {
					if ( isset( $settings['jb_job_description']['plugins'] ) && false !== strpos( $settings['jb_job_description']['plugins'], 'wplink' ) ) {
						echo '<style>
							#link-selector > .howto, #link-selector > #search-panel { display:none; }
						</style>';
					}
				}
			);

			$editor_settings = apply_filters(
				'jb_content_editor_options',
				array(
					'textarea_name' => $name,
					'wpautop'       => true,
					'editor_height' => 145,
					'media_buttons' => false,
					'quicktags'     => false,
					'editor_css'    => '<style> .mce-top-part button { background-color: rgba(0,0,0,0.0) !important; } </style>',
					'tinymce'       => array(
						'init_instance_callback' => "function (editor) {
														editor.on( 'keyup paste mouseover', function (e) {
														var content = editor.getContent( { format: 'html' } ).trim();
														var textarea = jQuery( '#' + editor.id ); 
														textarea.val( content ).trigger( 'keyup' ).trigger( 'keypress' ).trigger( 'keydown' ).trigger( 'change' ).trigger( 'paste' ).trigger( 'mouseover' );
													});}",
					),
				)
			);

			ob_start();

			wp_editor( $value, $id, $editor_settings );

			$editor_contents = ob_get_clean();

			remove_filter( 'mce_buttons', array( $this, 'filter_mce_buttons' ), 10 );

			return $editor_contents;
		}


		/**
		 * Remove unusable MCE button for JB WP Editors
		 *
		 * @param array $mce_buttons
		 * @param int $editor_id
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function filter_mce_buttons( $mce_buttons, $editor_id ) {
			$mce_buttons = array_diff( $mce_buttons, array( 'alignright', 'alignleft', 'aligncenter', 'wp_adv', 'wp_more', 'fullscreen', 'formatselect', 'spellchecker' ) );
			$mce_buttons = apply_filters( 'jb_rich_text_editor_buttons', $mce_buttons, $editor_id, $this );

			return $mce_buttons;
		}


		/**
		 * Add form error
		 *
		 * @param string $field
		 * @param string $text
		 *
		 * @since 1.0
		 */
		public function add_error( $field, $text ) {
			if ( 'global' === $field ) {
				if ( ! isset( $this->errors['global'] ) ) {
					$this->errors['global'] = array();
				}
				$this->errors['global'][] = apply_filters( 'jb_form_global_error', $text );
			} else {
				if ( ! isset( $this->errors[ $field ] ) ) {
					$this->errors[ $field ] = apply_filters( 'jb_form_error', $text, $field );
				}
			}
		}


		/**
		 * Add form notice
		 *
		 * @param string $text
		 * @param string $key
		 *
		 * @since 1.0
		 */
		public function add_notice( $text, $key ) {
			$this->notices[ $key ] = apply_filters( 'jb_form_notice', $text, $key );
		}


		/**
		 * If a form has error by field key
		 *
		 * @param  string $field
		 * @return boolean
		 *
		 * @since 1.0
		 */
		public function has_error( $field ) {
			return ! empty( $this->errors[ $field ] ) || ! empty( $this->errors[ $field ] );
		}


		/**
		 * If a form has errors
		 *
		 * @return boolean
		 *
		 * @since 1.0
		 */
		public function has_errors() {
			return ! empty( $this->errors );
		}


		/**
		 * If a form has notices
		 *
		 * @return boolean
		 *
		 * @since 1.0
		 */
		public function has_notices() {
			return ! empty( $this->notices );
		}


		/**
		 * Flush errors
		 *
		 * @since 1.0
		 */
		public function flush_errors() {
			$this->errors = array();
		}


		/**
		 * Flush notices
		 *
		 * @since 1.0
		 */
		public function flush_notices() {
			$this->notices = array();
		}


		/**
		 * Get a form error by a field key
		 *
		 * @param string $field
		 *
		 * @return string|array
		 *
		 * @since 1.0
		 */
		public function get_error( $field ) {
			$default = 'global' === $field ? array() : '';
			return ! empty( $this->errors[ $field ] ) ? $this->errors[ $field ] : $default;
		}


		/**
		 * Get a form notices
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function get_notices() {
			return ! empty( $this->notices ) ? $this->notices : array();
		}
	}
}
