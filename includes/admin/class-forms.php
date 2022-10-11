<?php namespace jb\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'jb\admin\Forms' ) ) {


	/**
	 * Class Forms
	 *
	 * @package jb\admin
	 */
	class Forms {


		/**
		 * @var bool|array Inited form data
		 *
		 * @since 1.0
		 */
		public $form_data;


		/**
		 * Forms constructor.
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
		 * @param bool|array $data
		 *
		 * @return self
		 */
		public function set_data( $data ) {
			$this->form_data = $data;
			return $this;
		}


		/**
		 * Render form
		 *
		 * @param bool $echo
		 * @return string
		 *
		 * @since 1.0
		 */
		public function display( $echo = true ) {
			if ( empty( $this->form_data['fields'] ) ) {
				return '';
			}

			$hidden = '';
			$fields = '';
			foreach ( $this->form_data['fields'] as $field_data ) {
				if ( empty( $field_data['type'] ) ) {
					continue;
				}

				if ( 'hidden' === $field_data['type'] ) {
					$hidden .= $this->render_hidden( $field_data );
				} else {
					$fields .= $this->render_form_row( $field_data );
				}
			}

			ob_start();

			echo wp_kses( $hidden, JB()->get_allowed_html( 'wp-admin' ) );

			if ( empty( $this->form_data['without_wrapper'] ) ) {
				$class = 'form-table jb-form-table ' . ( ! empty( $this->form_data['class'] ) ? $this->form_data['class'] : '' );
				?>

				<table class="<?php echo esc_attr( $class ); ?>">
					<tbody>
						<?php echo wp_kses( $fields, JB()->get_allowed_html( 'wp-admin' ) ); ?>
					</tbody>
				</table>

				<?php
			} else {
				echo wp_kses( $fields, JB()->get_allowed_html( 'wp-admin' ) );
			}

			if ( $echo ) {
				ob_get_flush();
				return '';
			} else {
				return ob_get_clean();
			}
		}


		/**
		 * Render form field's row
		 *
		 * @param array $data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_form_row( $data ) {
			if ( ! empty( $data['value'] ) && 'email_template' !== $data['type'] ) {
				$data['value'] = wp_unslash( $data['value'] );

				/*for multi_text*/
				if ( ! is_array( $data['value'] ) && 'wp_editor' !== $data['type'] ) {
					$data['value'] = esc_attr( $data['value'] );
				}
			}

			$conditional = ! empty( $data['conditional'] ) ? 'data-conditional="' . esc_attr( wp_json_encode( $data['conditional'] ) ) . '"' : '';
			$prefix_attr = ! empty( $this->form_data['prefix_id'] ) ? ' data-prefix="' . esc_attr( $this->form_data['prefix_id'] ) . '" ' : '';

			$type_attr = ' data-field_type="' . $data['type'] . '" ';

			$html = '';

			if ( ! empty( $this->form_data['div_line'] ) ) {

				if ( strpos( $this->form_data['class'], 'jb-top-label' ) !== false ) {

					$html .= '<div class="form-field jb-forms-line" ' . $conditional . $prefix_attr . $type_attr . '>' . $this->render_field_label( $data );

					if ( method_exists( $this, 'render_' . $data['type'] ) ) {

						$html .= call_user_func( array( &$this, 'render_' . $data['type'] ), $data );

					} else {

						$html .= $this->render_field_by_hook( $data );

					}

					if ( ! empty( $data['description'] ) ) {
						$html .= '<p class="description">' . $data['description'] . '</p>';
					}

					$html .= '</div>';

				} else {

					if ( ! empty( $data['without_label'] ) ) {

						$html .= '<div class="form-field jb-forms-line" ' . $conditional . $prefix_attr . $type_attr . '>';

						if ( method_exists( $this, 'render_' . $data['type'] ) ) {

							$html .= call_user_func( array( &$this, 'render_' . $data['type'] ), $data );

						} else {

							$html .= $this->render_field_by_hook( $data );

						}

						if ( ! empty( $data['description'] ) ) {
							$html .= '<p class="description">' . $data['description'] . '</p>';
						}

						$html .= '</div>';

					} else {

						$html .= '<div class="form-field jb-forms-line" ' . $conditional . $prefix_attr . $type_attr . '>' . $this->render_field_label( $data );

						if ( method_exists( $this, 'render_' . $data['type'] ) ) {

							$html .= call_user_func( array( &$this, 'render_' . $data['type'] ), $data );

						} else {

							$html .= $this->render_field_by_hook( $data );

						}

						if ( ! empty( $data['description'] ) ) {
							$html .= '<p class="description">' . $data['description'] . '</p>';
						}

						$html .= '</div>';

					}
				}
			} else {
				if ( strpos( $this->form_data['class'], 'jb-top-label' ) !== false ) {

					$html .= '<tr class="jb-forms-line" ' . $conditional . $prefix_attr . $type_attr . '>
					<td>' . $this->render_field_label( $data );

					if ( method_exists( $this, 'render_' . $data['type'] ) ) {

						$html .= call_user_func( array( &$this, 'render_' . $data['type'] ), $data );

					} else {

						$html .= $this->render_field_by_hook( $data );

					}

					if ( ! empty( $data['description'] ) ) {
						$html .= '<div class="clear"></div><p class="description">' . $data['description'] . '</p>';
					}

					$html .= '</td></tr>';

				} else {

					if ( ! empty( $data['without_label'] ) ) {

						$html .= '<tr class="jb-forms-line" ' . $conditional . $prefix_attr . $type_attr . '>
						<td colspan="2">';

						if ( method_exists( $this, 'render_' . $data['type'] ) ) {

							$html .= call_user_func( array( &$this, 'render_' . $data['type'] ), $data );

						} else {

							$html .= $this->render_field_by_hook( $data );

						}

						if ( ! empty( $data['description'] ) ) {
							$html .= '<div class="clear"></div><p class="description">' . $data['description'] . '</p>';
						}

						$html .= '</td></tr>';

					} else {

						$html .= '<tr class="jb-forms-line" ' . $conditional . $prefix_attr . $type_attr . '>
						<th>' . $this->render_field_label( $data ) . '</th>
						<td>';

						if ( method_exists( $this, 'render_' . $data['type'] ) ) {

							$html .= call_user_func( array( &$this, 'render_' . $data['type'] ), $data );

						} else {

							$html .= $this->render_field_by_hook( $data );

						}

						if ( ! empty( $data['description'] ) ) {
							$html .= '<div class="clear"></div><p class="description">' . $data['description'] . '</p>';
						}

						$html .= '</td></tr>';

					}
				}
			}

			return $html;
		}


		/**
		 * Render field by a hook
		 *
		 * @param array $data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_field_by_hook( $data ) {
			/**
			 * Filters the custom field content.
			 *
			 * Note: You could use this hook for getting rendered your custom field in wp-admin Forms.
			 *
			 * @since 1.1.0
			 * @hook jb_render_field_type_{$field_type}
			 *
			 * @param {string} $content   Field content. It's '' by default.
			 * @param {array}  $data      Field data.
			 * @param {array}  $form_data Admin form data.
			 * @param {object} $form      Admin form class (\jb\admin\Forms) instance.
			 *
			 * @return {string} Rendered custom field content.
			 */
			return apply_filters( 'jb_render_field_type_' . $data['type'], '', $data, $this->form_data, $this );
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

			$id       = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $data['id'];
			$for_attr = ' for="' . esc_attr( $id ) . '" ';

			$label = $data['label'];
			if ( ! empty( $data['required'] ) ) {
				$label = $label . ' <span class="jb-req" title="' . esc_attr__( 'Required', 'jobboardwp' ) . '">*</span>';
			}

			$helptip = ! empty( $data['helptip'] ) ? JB()->helptip( $data['helptip'] ) : '';

			return "<label $for_attr>$label $helptip</label>";
		}


		/**
		 * Render hidden field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_hidden( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $field_data['id'];
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class_attr = ' class="jb-forms-field ' . esc_attr( $class ) . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$name      = $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			$html = "<input type=\"hidden\" $id_attr $class_attr $name_attr $data_attr $value_attr />";

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

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $field_data['id'];
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? 'jb-' . $field_data['size'] . '-field' : 'jb-long-field';
			$class_attr = ' class="jb-forms-field ' . esc_attr( $class ) . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$key        = esc_html( $key );
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . esc_attr( $field_data['placeholder'] ) . '"' : '';

			$required = ! empty( $field_data['required'] ) ? ' required' : '';

			$name      = $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			$html = "<input type=\"text\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required />";

			return $html;
		}


		/**
		 * Render location autocomplete text
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

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . esc_attr( $field_data['placeholder'] ) . '"' : '';

			$required = ! empty( $field_data['required'] ) ? ' required' : '';

			$name      = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			$name_loco = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . '-data]' : $name . '-data';
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;

			$name_attr           = ' name="' . esc_attr( $name ) . '" ';
			$name_loco_data_attr = ' name="' . esc_attr( $name_loco ) . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			$field_data_data           = $field_data;
			$field_data_data['name']   = ( isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'] ) . '-data';
			$field_data_data['value']  = $field_data['value_data'];
			$field_data_data['encode'] = true;

			$value_data = $this->get_field_value( $field_data_data );
			$value_data = esc_attr( $value_data );

			$html = "<input type=\"text\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required />
					 <input type=\"hidden\" $name_loco_data_attr class=\"jb-location-autocomplete-data\" value=\"$value_data\" />";

			return $html;
		}


		/**
		 * Render number field
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

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $field_data['id'];
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? 'jb-' . $field_data['size'] . '-field' : 'jb-long-field';
			$class_attr = ' class="jb-forms-field ' . esc_attr( $class ) . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . esc_attr( $field_data['placeholder'] ) . '"' : '';

			$name      = $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			$html = "<input type=\"number\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr />";

			return $html;
		}


		/**
		 * Render color-picker field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_color( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $field_data['id'];
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? ' jb-' . $field_data['size'] . '-field ' : ' jb-long-field ';
			$class     .= ' jb-admin-colorpicker ';
			$class_attr = ' class="jb-forms-field ' . esc_attr( $class ) . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . $field_data['placeholder'] . '"' : '';

			$name      = $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			$html = "<input type=\"text\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr />";

			return $html;
		}


		/**
		 * Render textarea field
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

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $field_data['id'];
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? $field_data['size'] : 'jb-long-field';
			$class_attr = ' class="jb-forms-field ' . esc_attr( $class ) . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$rows = ! empty( $field_data['args']['textarea_rows'] ) ? ' rows="' . esc_attr( $field_data['args']['textarea_rows'] ) . '" ' : '';

			$name      = $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value = $this->get_field_value( $field_data );

			$html = "<textarea $id_attr $class_attr $name_attr $data_attr $rows>" . esc_textarea( $value ) . '</textarea>';

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

			$id             = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $field_data['id'];
			$id_attr        = ' id="' . esc_attr( $id ) . '" ';
			$id_attr_hidden = ' id="' . esc_attr( $id ) . '_hidden" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? $field_data['size'] : 'jb-long-field';
			$class_attr = ' class="jb-forms-field ' . esc_attr( $class ) . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$name      = $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			if ( ! empty( $field_data['options'] ) ) {
				$values    = $this->get_field_value( $field_data );
				$html      = '';
				$name_attr = ' name="' . esc_attr( $name ) . '[]" ';
				foreach ( $field_data['options'] as $optkey => $option ) {
					$id_attr = ' id="' . $id . '-' . $optkey . '" ';
					if ( in_array( (string) $optkey, $values, true ) ) {
						$checked = 'checked';
					} else {
						$checked = '';
					}
					$html .= "<label><input type=\"checkbox\" $id_attr $name_attr $data_attr " . $checked . ' value="' . esc_attr( $optkey ) . '" />&nbsp;' . $option . '</label>';
				}
			} else {
				$value = $this->get_field_value( $field_data );
				$html  = "<input type=\"hidden\" $id_attr_hidden $name_attr value=\"0\" /><input type=\"checkbox\" $id_attr $class_attr $name_attr $data_attr " . checked( $value, true, false ) . ' value="1" />';
			}

			return $html;
		}


		/**
		 * Render select
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

			$multiple = ! empty( $field_data['multi'] ) ? 'multiple' : '';

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $field_data['id'];
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? ' jb-' . $field_data['size'] . '-field' : ' jb-long-field';
			$class_attr = ' class="jb-forms-field ' . esc_attr( $class ) . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);
			$data = ! empty( $field_data['data'] ) ? array_merge( $data, $field_data['data'] ) : $data;

			$data['placeholder'] = ! empty( $data['placeholder'] ) ? $data['placeholder'] : __( 'Please select...', 'jobboardwp' );

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$name             = $field_data['id'];
			$name             = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$hidden_name_attr = ' name="' . esc_attr( $name ) . '" ';

			$name      = $name . ( ! empty( $field_data['multi'] ) ? '[]' : '' );
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value = $this->get_field_value( $field_data );

			$options = '';
			if ( ! empty( $field_data['options'] ) ) {
				foreach ( $field_data['options'] as $key => $option ) {
					if ( ! empty( $field_data['multi'] ) ) {

						if ( ! is_array( $value ) || empty( $value ) ) {
							$value = array();
						}

						$options .= '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $value, true ), true, false ) . '>' . esc_html( $option ) . '</option>';
					} else {
						$options .= '<option value="' . esc_attr( $key ) . '" ' . selected( (string) $key === $value, true, false ) . '>' . esc_html( $option ) . '</option>';
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
		 * @param $field_data
		 *
		 * @since 1.1.1
		 *
		 * @return bool|string
		 */
		public function render_page_select( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return false;
			}

			$multiple = ! empty( $field_data['multi'] ) ? 'multiple' : '';

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $field_data['id'];
			$id_attr = ' id="' . esc_attr( $id ) . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] . ' ' : ' ';
			$class     .= ! empty( $field_data['size'] ) ? 'jb-' . $field_data['size'] . '-field' : 'jb-long-field';
			$class_attr = ' class="jb-forms-field jb-pages-select2 ' . esc_attr( $class ) . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			if ( ! empty( $field_data['placeholder'] ) ) {
				$data['placeholder'] = $field_data['placeholder'];
			}

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= ' data-' . $key . '="' . esc_attr( $value ) . '" ';
			}

			$name             = $field_data['id'];
			$name             = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$hidden_name_attr = ' name="' . esc_attr( $name ) . '" ';

			$name      = $name . ( ! empty( $field_data['multi'] ) ? '[]' : '' );
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value = $this->get_field_value( $field_data );

			$options = '<option value="">' . esc_html( $data['placeholder'] ) . '</option>';
			if ( ! empty( $field_data['options'] ) ) {
				foreach ( $field_data['options'] as $key => $option ) {
					if ( ! empty( $field_data['multi'] ) ) {

						if ( ! is_array( $value ) || empty( $value ) ) {
							$value = array();
						}

						$options .= '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $value, true ), true, false ) . '>' . esc_html( $option ) . '</option>';
					} else {
						$options .= '<option value="' . esc_attr( $key ) . '" ' . selected( (string) $key === $value, true, false ) . '>' . esc_html( $option ) . '</option>';
					}
				}
			}

			$hidden = '';
			if ( ! empty( $multiple ) ) {
				$hidden = "<input type=\"hidden\" $hidden_name_attr value=\"\" />";
			}

			$button   = '';
			$page_key = str_replace( '_page', '', $field_data['id'] );

			if ( ! JB()->common()->permalinks()->get_predefined_page_id( $page_key ) ) {
				$create_page_url = add_query_arg(
					array(
						'jb_adm_action' => 'install_predefined_page',
						'jb_page_key'   => $page_key,
						'nonce'         => wp_create_nonce( 'jb_install_predefined_page' ),
					)
				);

				$button = '&nbsp;<a href="' . esc_url( $create_page_url ) . '" class="button button-primary">' . esc_html__( 'Create Default', 'jobboardwp' ) . '</a>';
			}

			$html = "$hidden<select $multiple $id_attr $name_attr $class_attr $data_attr>$options</select>$button";

			return $html;
		}


		/**
		 * Render datepicker field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
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

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= ' data-' . $key . '="' . esc_attr( $value ) . '" ';
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
		 * Render media uploader
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

			$id = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $field_data['id'];

			$class  = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class .= ! empty( $field_data['size'] ) ? $field_data['size'] : 'jb-long-field';

			$data = array(
				'field_id' => $field_data['id'] . '_url',
			);

			if ( ! empty( $field_data['default']['url'] ) ) {
				$data['default'] = esc_attr( $field_data['default']['url'] );
			}

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"" . esc_attr( $value ) . '" ';
			}

			$name = $field_data['id'];
			$name = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;

			$value = $this->get_field_value( $field_data );

			$upload_frame_title = ! empty( $field_data['upload_frame_title'] ) ? $field_data['upload_frame_title'] : __( 'Select media', 'jobboardwp' );

			$image_id        = ! empty( $value['id'] ) ? $value['id'] : '';
			$image_width     = ! empty( $value['width'] ) ? $value['width'] : '';
			$image_height    = ! empty( $value['height'] ) ? $value['height'] : '';
			$image_thumbnail = ! empty( $value['thumbnail'] ) ? $value['thumbnail'] : '';
			$image_url       = ! empty( $value['url'] ) ? $value['url'] : '';

			ob_start();
			?>

			<div class="jb-media-upload">
				<input type="hidden" class="jb-media-upload-data-id" name="<?php echo esc_attr( $name ); ?>[id]" id="<?php echo esc_attr( $id ); ?>_id" value="<?php echo esc_attr( $image_id ); ?>">
				<input type="hidden" class="jb-media-upload-data-width" name="<?php echo esc_attr( $name ); ?>[width]" id="<?php echo esc_attr( $id ); ?>_width" value="<?php echo esc_attr( $image_width ); ?>">
				<input type="hidden" class="jb-media-upload-data-height" name="<?php echo esc_attr( $name ); ?>[height]" id="<?php echo esc_attr( $id ); ?>_height" value="<?php echo esc_attr( $image_height ); ?>">
				<input type="hidden" class="jb-media-upload-data-thumbnail" name="<?php echo esc_attr( $name ); ?>[thumbnail]" id="<?php echo esc_attr( $id ); ?>_thumbnail" value="<?php echo esc_attr( $image_thumbnail ); ?>">

				<?php echo wp_kses( '<input type="hidden" class="jb-forms-field jb-media-upload-data-url ' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '[url]" id="' . esc_attr( $id ) . '_url" value="' . esc_attr( $image_url ) . '" ' . $data_attr . '>', JB()->get_allowed_html( 'wp-admin' ) ); ?>

				<?php if ( ! isset( $field_data['preview'] ) || false !== $field_data['preview'] ) { ?>
					<img src="<?php echo esc_attr( $image_url ); ?>" alt="" class="icon_preview"><div style="clear:both;"></div>
				<?php } ?>

				<?php if ( ! empty( $field_data['url'] ) ) { ?>
					<label class="screen-reader-text" for="jb-media-upload-url"><?php echo esc_attr( $this->render_field_label( $field_data ) ); ?></label>
					<input type="text" id="jb-media-upload-url" class="jb-media-upload-url" readonly value="<?php echo esc_attr( $image_url ); ?>" /><div style="clear:both;"></div>
				<?php } ?>

				<input type="button" class="jb-set-image button button-primary" value="<?php esc_attr_e( 'Select', 'jobboardwp' ); ?>" data-upload_frame="<?php echo esc_attr( $upload_frame_title ); ?>" />
				<input type="button" class="jb-clear-image button" value="<?php esc_attr_e( 'Clear', 'jobboardwp' ); ?>" />
			</div>

			<?php
			$html = ob_get_clean();
			return $html;
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
			$default = '';
			$default = isset( $field_data[ 'default' . $i ] ) ? $field_data[ 'default' . $i ] : $default;

			if ( 'checkbox' === $field_data['type'] ) {
				$value = ( isset( $field_data[ 'value' . $i ] ) && '' !== $field_data[ 'value' . $i ] ) ? $field_data[ 'value' . $i ] : $default;
			} else {
				$value = isset( $field_data[ 'value' . $i ] ) ? $field_data[ 'value' . $i ] : $default;
			}

			$value = is_string( $value ) ? stripslashes( $value ) : $value;

			if ( ! empty( $value ) ) {
				if ( isset( $field_data['encode'] ) ) {
					$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE );
				}
			}

			return $value;
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

			if ( empty( $field_data['options'] ) ) {
				return '';
			}

			$id = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];

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

				$html .= "<label><input type=\"radio\" $id_attr $name_attr $data_attr " . checked( $value, $optkey, false ) . ' value="' . esc_attr( $optkey ) . '" />&nbsp;' . $option . '</label>';
			}

			return $html;
		}
	}
}
