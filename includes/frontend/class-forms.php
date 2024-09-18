<?php
namespace jb\frontend;

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
		 * @var bool|array
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
			'datepicker',
			'radio',
			'checkbox',
			'textarea',
			'number',
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
		 * @return self
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
		 * @param bool $display
		 * @return string
		 *
		 * @since 1.0
		 */
		public function display( $display = true ) {
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
			} elseif ( ! empty( $this->form_data['sections'] ) ) {
				foreach ( $this->form_data['sections'] as $section_key => $section_data ) {
					$section_data['key'] = $section_key;
					$fields             .= $this->render_section( $section_data );
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

			/**
			 * Filters the state when JobBoardWP form opening tag <form> must be moved to the 3rd-party handler.
			 *
			 * Note: It's used internally for displaying "My Details" section on the Job Posting form.
			 *
			 * @since 1.0
			 * @hook jb_forms_move_form_tag
			 *
			 * @param {bool} $move_form_tag Whether we should move the form opening tag <form>. Defaults to false.
			 *
			 * @return {bool} If true, the form opening tag <form> must be displayed in the 3rd-party callback.
			 */
			$move_form_tag = apply_filters( 'jb_forms_move_form_tag', false );

			if ( ! $move_form_tag ) {
				echo wp_kses( '<form action="' . esc_attr( $action ) . '" method="' . esc_attr( $method ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" class="jb-form" ' . $data_attr . '>', JB()->get_allowed_html( 'templates' ) );
			}

			echo wp_kses( $fields . $hidden . '<div class="jb-form-buttons-section">' . $buttons . '</div>', JB()->get_allowed_html( 'templates' ) );

			/**
			 * Fires in the form footer before closing tag in the form.
			 * This hook may be used to display custom content in the form footer.
			 *
			 * Note: For checking the form on where you need to add content - use $form_data['id']
			 *
			 * @since 1.2.2
			 * @hook jb_after_form_fields
			 *
			 * @param {array} $form_data JB Form data.
			 */
			do_action( 'jb_after_form_fields', $this->form_data );
			?>

			</form>

			<?php
			remove_all_filters( 'jb_forms_move_form_tag' );

			if ( $display ) {
				ob_get_flush();
				return '';
			}

			return ob_get_clean();
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

			$default_index = 'default' . $i;
			$default       = isset( $field_data[ $default_index ] ) ? $field_data[ $default_index ] : '';

			$value_index = 'value' . $i;
			if ( 'checkbox' === $field_data['type'] ) {
				$value = ( isset( $field_data[ $value_index ] ) && '' !== $field_data[ $value_index ] ) ? $field_data[ $value_index ] : $default;
			} else {
				$value = isset( $field_data[ $value_index ] ) ? $field_data[ $value_index ] : $default;
			}

			$name = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			if ( ! empty( $this->form_data['prefix_id'] ) ) {
				if ( isset( $_POST[ $this->form_data['prefix_id'] ][ $name ] ) ) {
					if ( is_array( $_POST[ $this->form_data['prefix_id'] ][ $name ] ) ) {
						$value = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_POST[ $this->form_data['prefix_id'] ][ $name ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- operate with value as array and array_map function
					} else {
						$value = sanitize_text_field( wp_unslash( $_POST[ $this->form_data['prefix_id'] ][ $name ] ) );
					}
				}
			} elseif ( isset( $_POST[ $name ] ) ) {
				if ( is_array( $_POST[ $name ] ) ) {
					$value = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_POST[ $name ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- operate with value as array and array_map function
				} else {
					$value = sanitize_text_field( wp_unslash( $_POST[ $name ] ) );
				}
			}

			if ( ! empty( $value ) ) {
				if ( ! empty( $this->form_data['prefix_id'] ) ) {
					if ( isset( $field_data['encode'] ) && ! isset( $_POST[ $this->form_data['prefix_id'] ][ $name ] ) ) {
						$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE );
					}
				} elseif ( isset( $field_data['encode'] ) && ! isset( $_POST[ $name ] ) ) {
					$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE );
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

			$conditional = ! empty( $data['conditional'] ) ? 'data-conditional="' . esc_attr( wp_json_encode( $data['conditional'] ) ) . '"' : '';
			$required    = ! empty( $data['required'] ) ? 'data-required="required"' : '';

			ob_start();
			?>

			<div class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>" <?php echo $conditional; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  -- already escaped above ?> <?php echo $required; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  -- already escaped above ?>>
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
			return ob_get_clean();
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

			/**
			 * Filters the section content before its render.
			 *
			 * @since 1.0
			 * @hook jb_forms_before_render_section
			 *
			 * @param {string}         $html         Default HTML before the section render start. It's <h3> title by default.
			 * @param {array}          $section_data Section data.
			 * @param {array}          $form_data    Frontend form data.
			 *
			 * @return {string} Custom HTML before the rendered section.
			 */
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
			$class = isset( $data['class'] ) ? $data['class'] : array();
			$class = is_array( $class ) ? $class : array( $class );

			$classes   = array_merge( array( 'jb-form-button' ), $class );
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

			return "<input type=\"hidden\" $id_attr $name_attr $data_attr $value_attr />";
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

			$label = $data['label'];

			/**
			 * Filters the condition for disabling the "required" star in the form field label.
			 *
			 * @since 1.0
			 * @hook jb_frontend_forms_required_star_disabled
			 *
			 * @param {bool} $disable_star Whether we should disable the "required" star in the form field label. Defaults to false.
			 *
			 * @return {bool} If true, the "required" star will be hidden.
			 */
			$disable_star = apply_filters( 'jb_frontend_forms_required_star_disabled', false );
			if ( ! empty( $data['required'] ) && ! $disable_star ) {
				$label .= '<span class="jb-req" title="' . esc_attr__( 'Required', 'jobboardwp' ) . '">*</span>';
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

			$value_array = explode( '/', $field_data['value'] );

			$wrapper_classes = array( 'jb-uploaded-wrapper', 'jb-' . $id . '-wrapper' );

			// check if a file uploaded
			if ( ! empty( $field_data['value'] ) && ! empty( end( $value_array ) ) ) {
				$wrapper_classes = array_merge( $wrapper_classes, array( 'jb-uploaded', 'jb-' . $id . '-uploaded' ) );
			}
			$wrapper_classes = implode( ' ', $wrapper_classes );

			$img_style = $thumb_crop ? 'style="object-fit: cover;"' : '';

			$uploader_classes = array( 'jb-uploader', 'jb-' . $id . '-uploader' );
			// check if a file uploaded
			if ( ! empty( $field_data['value'] ) && ! empty( end( $value_array ) ) ) {
				$uploader_classes = array_merge( $uploader_classes, array( 'jb-uploaded', 'jb-' . $id . '-uploaded' ) );
			}
			$uploader_classes = implode( ' ', $uploader_classes );

			$styles = 'width: ' . $thumb_w . 'px; height: ' . $thumb_h . 'px; display: block;';

			/**
			 * Filters the preview style.
			 *
			 * @hook jb_upload_wrapper_styles
			 * @since 1.2.3
			 *
			 * @param {string} $styles      Styles
			 * @param {array}  $field_data  Field data.
			 *
			 * @return {string} Styles attribute
			 */
			$styles = apply_filters( 'jb_upload_wrapper_styles', $styles, $field_data );

			if ( 'jb-upload-company-logo' === $field_data['action'] ) {
				$value = ! empty( $field_data['value'] ) ? $field_data['value'] : '';
			} elseif ( count( $value_array ) > 1 && ! empty( end( $value_array ) ) ) {
				// check if $field_data['value'] is a full path or only name
				$value = end( $value_array );
			} else {
				$value = ! empty( $field_data['value'] ) ? $field_data['value'] : '';
			}

			ob_start();
			?>

			<span class="<?php echo esc_attr( $wrapper_classes ); ?>">
				<span class="jb-uploaded-content-wrapper jb-<?php echo esc_attr( $id ); ?>-image-wrapper" style="<?php echo esc_attr( $styles ); ?>">
					<?php
					if ( JB()->options()->get( 'disable-company-logo-cache' ) ) {
						$field_data['value'] = add_query_arg( array( 't' => time() ), $field_data['value'] );
					}
					$output = '<img src="' . ( ! empty( $field_data['value'] ) ? esc_url( $field_data['value'] ) : '' ) . '" alt="' . esc_attr( $img_alt ) . '" ' . $img_style . ' />';
					/**
					 * Filters the preview media output.
					 *
					 * @hook jb_preview_media_output
					 * @since 1.2.2
					 *
					 * @param {string} $output      Media output.
					 * @param {array}  $field_data  Field data.
					 *
					 * @return {string} Filtered media output.
					 */
					$output = apply_filters( 'jb_preview_media_output', $output, $field_data );
					echo wp_kses( $output, JB()->get_allowed_html( 'templates' ) );
					?>
				</span>
				<a class="jb-cancel-change-media" href="#"><?php echo esc_html( $cancel_label ); ?></a>
				<a class="jb-change-media" href="#"><?php echo esc_html( $change_label ); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;
				<a class="jb-clear-media" href="#"><?php echo esc_html( $remove_label ); ?></a>
			</span>

			<span class="<?php echo esc_attr( $uploader_classes ); ?>">
				<span id="jb_<?php echo esc_attr( $id ); ?>_filelist" class="jb-uploader-dropzone">
					<span><?php esc_html_e( 'Drop file to upload', 'jobboardwp' ); ?></span>
					<span><?php esc_html_e( 'or', 'jobboardwp' ); ?></span>
					<span class="jb-select-media-button-wrapper">
						<input type="button" class="jb-select-media" data-action="<?php echo esc_attr( $field_data['action'] ); ?>" id="jb_<?php echo esc_attr( $id ); ?>_plupload" value="<?php echo esc_attr( $select_label ); ?>" />
					</span>
				</span>

				<span id="jb-<?php echo esc_attr( $id ); ?>-errorlist" class="jb-uploader-errorlist"></span>
			</span>
			<input type="hidden" class="jb-media-value" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			<input type="hidden" class="jb-media-value-hash" id="<?php echo esc_attr( $id ); ?>_hash" name="<?php echo esc_attr( $name ); ?>_hash" value="" />

			<?php
			return ob_get_clean();
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

			return "<input type=\"text\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required />";
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
		public function render_number( $field_data ) {
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
			$min              = isset( $field_data['min'] ) ? ' min="' . esc_attr( $field_data['min'] ) . '"' : '';
			$max              = isset( $field_data['max'] ) ? ' max="' . esc_attr( $field_data['max'] ) . '"' : '';
			$step             = ! empty( $field_data['step'] ) ? ' step="' . esc_attr( $field_data['step'] ) . '"' : '';

			$name      = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			return "<input type=\"number\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required $min $max $step />";
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
		public function render_textarea( $field_data ) {
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

			$value = esc_textarea( $this->get_field_value( $field_data ) );

			return "<textarea $id_attr $class_attr $name_attr $data_attr $placeholder_attr $required >$value</textarea>";
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

			if ( empty( $field_data['ignore_predefined_options'] ) && ! isset( $field_data['options'] ) ) {
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
			$name            .= ( ! empty( $field_data['multi'] ) ? '[]' : '' );
			$name_attr        = ' name="' . esc_attr( $name ) . '" ';

			$value = $this->get_field_value( $field_data );
			if ( ! empty( $field_data['multi'] ) ) {
				if ( ! is_array( $value ) || empty( $value ) ) {
					$value = array();
				}

				$value = array_map( 'strval', $value );
			}

			if ( ! empty( $field_data['ignore_predefined_options'] ) ) {
				$added_values = $value;
			}

			$options = '';
			if ( ! empty( $field_data['options'] ) ) {
				foreach ( $field_data['options'] as $key => $option ) {
					if ( isset( $added_values ) && in_array( (string) $key, $value, true ) ) {
						unset( $added_values[ array_search( (string) $key, $added_values, true ) ] );
					}

					if ( ! empty( $field_data['multi'] ) ) {
						if ( is_array( $value ) ) {
							$selected = selected( in_array( (string) $key, $value, true ), true, false );
						} else {
							$selected = selected( $value, $key, false );
						}

						$options .= '<option value="' . esc_attr( $key ) . '" ' . $selected . '>' . esc_html( $option ) . '</option>';
					} else {
						$options .= '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $option ) . '</option>';
					}
				}
			}

			if ( ! empty( $added_values ) ) {
				foreach ( $added_values as $option ) {
					$options .= '<option value="' . esc_attr( $option ) . '" selected>' . esc_html( $option ) . '</option>';
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
		 * Render radio
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_radio( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			if ( ! isset( $field_data['options'] ) ) {
				return '';
			}

			$id = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? $field_data['size'] : ' jb-long-field';
			$class_attr = ' class="jb-forms-field' . esc_attr( $class ) . '" ';

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

				$html .= "<label><input type=\"radio\" $id_attr $class_attr $name_attr $data_attr " . checked( $value, $optkey, false ) . ' value="' . esc_attr( $optkey ) . '" />&nbsp;' . esc_html( $option ) . '</label>';
			}

			return $html;
		}

		/**
		 * Render checkbox
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_checkbox( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			if ( ! isset( $field_data['options'] ) ) {
				return '';
			}

			$id = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? $field_data['size'] : ' jb-long-field';
			$class_attr = ' class="jb-forms-field' . esc_attr( $class ) . '" ';

			$data = array( 'field_id' => $field_data['id'] );

			$data_attr = '';
			foreach ( $data as $key => $val ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $val ) . '" ';
			}

			$name      = $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '[]" ';

			$value = $this->get_field_value( $field_data );

			$html = '';
			foreach ( $field_data['options'] as $optkey => $option ) {
				$id_attr = ' id="' . $id . '-' . $optkey . '" ';

				if ( is_array( $value ) ) {
					$checked = checked( in_array( (string) $optkey, $value, true ), true, false );
				} else {
					$checked = checked( $value, $optkey, false );
				}
				$html .= "<label><input type=\"checkbox\" $id_attr $class_attr $name_attr $data_attr $checked value=\"" . esc_attr( $optkey ) . '" />&nbsp;' . esc_html( $option ) . '</label>';
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

			$name = $field_data['id'];
			$name = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;

			$value = $this->get_field_value( $field_data );

			add_filter( 'mce_buttons', array( $this, 'filter_mce_buttons' ), 10, 2 );

			add_action(
				'after_wp_tiny_mce',
				static function ( $settings ) {
					if ( isset( $settings['_job_description']['plugins'] ) && false !== strpos( $settings['_job_description']['plugins'], 'wplink' ) ) {
						?>
						<script>
							jQuery("#link-selector > .howto, #link-selector > #search-panel").remove();
						</script>
						<?php
					}
				}
			);

			/**
			 * Filters the WP_Editor options.
			 *
			 * @since 1.0
			 * @hook jb_content_editor_options
			 *
			 * @param {array} $editor_settings WP_Editor field's settings. See all settings here https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/#parameters
			 * @param {array} $field_data      Frontend form's field data.
			 *
			 * @return {array} WP_Editor field's settings.
			 */
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
				),
				$field_data
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
			/**
			 * Filters the WP_Editor MCE buttons list.
			 *
			 * @since 1.0
			 * @hook jb_rich_text_editor_buttons
			 *
			 * @param {array}  $mce_buttons TinyMCE buttons. See the list of buttons here https://developer.wordpress.org/reference/hooks/mce_buttons/
			 * @param {string} $editor_id   WP_Editor ID.
			 * @param {object} $form        Frontend form class (\jb\frontend\Forms) instance.
			 *
			 * @return {array} TinyMCE buttons.
			 */
			return apply_filters( 'jb_rich_text_editor_buttons', $mce_buttons, $editor_id, $this );
		}

		/**
		 * Render datepicker field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.1.1
		 */
		public function render_datepicker( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $field_data['id'];
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? 'jb-' . $field_data['size'] . '-field' : 'jb-long-field';
			$class_attr = ' class="jb-forms-field jb-datepicker ' . esc_attr( $class ) . '" ';

			$data = array( 'field_id' => $field_data['id'] );

			$data_attr = '';
			foreach ( $data as $key => $val ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $val ) . '" ';
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . esc_attr( $field_data['placeholder'] ) . '"' : '';

			$name      = $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$hidden_value      = $this->get_field_value( $field_data );
			$hidden_value_attr = ' value="' . esc_attr( $hidden_value ) . '" ';

			$value      = ! empty( $hidden_value ) ? date_i18n( get_option( 'date_format' ), strtotime( $hidden_value ) ) : '';
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			$html = "<input type=\"text\" $id_attr $class_attr $data_attr $value_attr $placeholder_attr /><input type=\"hidden\" class=\"jb-datepicker-default-format\" $name_attr $hidden_value_attr />";

			return $html;
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
				/**
				 * Filters the frontend form global errors.
				 *
				 * @since 1.0
				 * @hook jb_form_global_error
				 *
				 * @param {string} $text Global error text.
				 *
				 * @return {string} Custom singular global error.
				 */
				$this->errors['global'][] = apply_filters( 'jb_form_global_error', $text );
			} elseif ( ! isset( $this->errors[ $field ] ) ) {
				/**
				 * Filters the frontend form error related to the field.
				 *
				 * @since 1.0
				 * @hook jb_form_error
				 *
				 * @param {string} $text  Error text.
				 * @param {string} $field Field ID. E.g. 'company_name', etc.
				 *
				 * @return {string} Error text.
				 */
				$this->errors[ $field ] = apply_filters( 'jb_form_error', $text, $field );
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
			/**
			 * Filters the frontend form notices based on the notice key.
			 *
			 * @since 1.0
			 * @hook jb_form_notice
			 *
			 * @param {string} $text Notice text.
			 * @param {string} $key  Notice key. E.g. 'on-moderation', etc.
			 *
			 * @return {string} Notice text.
			 */
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
