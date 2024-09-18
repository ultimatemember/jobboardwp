<?php
namespace jb\admin;

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
		 * @param bool $display
		 * @return string
		 *
		 * @since 1.0
		 */
		public function display( $display = true ) {
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

			if ( $display ) {
				ob_get_flush();
				return '';
			}

			return ob_get_clean();
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

				} elseif ( ! empty( $data['without_label'] ) ) {

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
			} elseif ( strpos( $this->form_data['class'], 'jb-top-label' ) !== false ) {

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

			} elseif ( ! empty( $data['without_label'] ) ) {

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
				$label .= ' <span class="jb-req" title="' . esc_attr__( 'Required', 'jobboardwp' ) . '">*</span>';
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

			return "<input type=\"hidden\" $id_attr $class_attr $name_attr $data_attr $value_attr />";
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

			$disabled = ! empty( $field_data['disabled'] ) ? 'disabled' : '';

			return "<input type=\"text\" $disabled $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required />";
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

			return "<input type=\"text\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required />
					 <input type=\"hidden\" $name_loco_data_attr class=\"jb-location-autocomplete-data\" value=\"$value_data\" />";
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
			$min              = isset( $field_data['min'] ) ? ' min="' . esc_attr( $field_data['min'] ) . '"' : '';
			$max              = isset( $field_data['max'] ) ? ' max="' . esc_attr( $field_data['max'] ) . '"' : '';
			$step             = ! empty( $field_data['step'] ) ? ' step="' . esc_attr( $field_data['step'] ) . '"' : '';

			$name      = $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			return "<input type=\"number\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $min $max $step />";
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

			return "<input type=\"text\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr />";
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

			$disabled = ! empty( $field_data['disabled'] ) ? 'disabled' : '';

			$value = $this->get_field_value( $field_data );

			return "<textarea $id_attr $class_attr $name_attr $data_attr $rows $disabled>" . esc_textarea( $value ) . '</textarea>';
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
			 * @param {array} $editor_settings WP_Editor field's settings. See the all settings here https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/#parameters
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
			$mce_buttons = array_diff( $mce_buttons, array( 'alignright', 'alignleft', 'aligncenter', 'wp_adv', 'wp_more', 'fullscreen', 'formatselect', 'spellchecker', 'link' ) );
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

			$disabled = ! empty( $field_data['disabled'] ) ? 'disabled' : '';

			if ( ! empty( $field_data['options'] ) ) {
				$values    = $this->get_field_value( $field_data );
				$html      = '';
				$name_attr = ' name="' . esc_attr( $name ) . '[]" ';
				foreach ( $field_data['options'] as $optkey => $option ) {
					$id_attr = ' id="' . $id . '-' . $optkey . '" ';
					if ( is_array( $values ) && in_array( (string) $optkey, $values, true ) ) {
						$checked = 'checked';
					} else {
						$checked = '';
					}
					$html .= "<label><input $disabled type=\"checkbox\" $id_attr $name_attr $data_attr " . $checked . ' value="' . esc_attr( $optkey ) . '" />&nbsp;' . $option . '</label>';
				}
			} else {
				$value = $this->get_field_value( $field_data );
				$html  = "<input type=\"hidden\" $id_attr_hidden $name_attr value=\"0\" /><input $disabled type=\"checkbox\" $id_attr $class_attr $name_attr $data_attr " . checked( $value, true, false ) . ' value="1" />';
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

			$name     .= ( ! empty( $field_data['multi'] ) ? '[]' : '' );
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$disabled = ! empty( $field_data['disabled'] ) ? 'disabled' : '';

			$value = $this->get_field_value( $field_data );

			$options = '';
			if ( ! empty( $field_data['options'] ) ) {
				foreach ( $field_data['options'] as $key => $option ) {
					if ( ! empty( $field_data['multi'] ) ) {

						if ( ! is_array( $value ) || empty( $value ) ) {
							$value = array();
						}

						$options .= '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( (string) $key, $value, true ), true, false ) . '>' . esc_html( $option ) . '</option>';
					} else {
						$options .= '<option value="' . esc_attr( $key ) . '" ' . selected( (string) $key === $value, true, false ) . '>' . esc_html( $option ) . '</option>';
					}
				}
			}

			$hidden = '';
			if ( ! empty( $multiple ) ) {
				$hidden = "<input type=\"hidden\" $hidden_name_attr value=\"\" />";
			}
			return "$hidden<select $disabled $multiple $id_attr $name_attr $class_attr $data_attr>$options</select>";
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

			return "$hidden<select $multiple $id_attr $name_attr $class_attr $data_attr>$options</select>$button";
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

			/**
			 * Filters the media formats. Backend form fields.
			 *
			 * @hook jb_get_media_field_formats
			 * @since 1.2.3
			 *
			 * @param {array}  $field_formats  Formats. By default, array is empty. The full array is array('pdf', 'doc', 'docx', 'csv', 'txt|asc|c|cc|h|srt', 'xla|xls|xlt|xlw', 'xlsx', 'jpg|jpeg|jpe', 'png', 'gif', 'bmp', 'tiff|tif', 'webp', 'heic', 'ico', 'zip' )
			 * @param {string} $field_id       Field id.
			 *
			 * @return {string} $field_formats Formats for media upload field.
			 */
			$field_formats = apply_filters( 'jb_get_media_field_formats', array(), $field_data['id'] );
			$field_formats = str_replace( '|', ',', implode( ',', $field_formats ) );

			$field_data['action'] = 'jb_upload_media_file';

			$id = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];

			$name = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			$name = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;

			$field_id = str_replace( 'jb-', '', $field_data['id'] );

			$select_label = isset( $field_data['labels']['select'] ) ? $field_data['labels']['select'] : __( 'Select file', 'jobboardwp' );
			$change_label = isset( $field_data['labels']['change'] ) ? $field_data['labels']['change'] : __( 'Change', 'jobboardwp' );
			$remove_label = isset( $field_data['labels']['remove'] ) ? $field_data['labels']['remove'] : __( 'Remove', 'jobboardwp' );
			$cancel_label = isset( $field_data['labels']['cancel'] ) ? $field_data['labels']['cancel'] : __( 'Cancel', 'jobboardwp' );

			$wrapper_classes = array( 'jb-uploaded-wrapper', 'jb-' . $id . '-wrapper' );
			if ( ! empty( $field_data['value'] ) ) {
				$wrapper_classes = array_merge( $wrapper_classes, array( 'jb-uploaded', 'jb-' . $id . '-uploaded' ) );
			}
			$wrapper_classes = implode( ' ', $wrapper_classes );

			$uploader_classes = array( 'jb-uploader', 'jb-' . $id . '-uploader' );
			if ( ! empty( $field_data['value'] ) ) {
				$uploader_classes = array_merge( $uploader_classes, array( 'jb-uploaded', 'jb-' . $id . '-uploaded' ) );
			}
			$uploader_classes = implode( ' ', $uploader_classes );

			$value = ! empty( $field_data['value'] ) ? $field_data['value'] : '';

			/**
			 * Filters the media-type field URL. Backend form fields.
			 *
			 * @since 1.2.4
			 * @hook jb_get_media_field_url
			 *
			 * @param {string} $value     Media URL.
			 * @param {string} $field_id  Field ID.
			 *
			 * @return {string} Filtered media file URL.
			 */
			$media_url = apply_filters( 'jb_get_media_field_url', $value, $field_data['id'] );
			ob_start();
			?>

			<span class="<?php echo esc_attr( $wrapper_classes ); ?>">
				<span class="jb-uploaded-content-wrapper jb-<?php echo esc_attr( $id ); ?>-image-wrapper">
					<?php
					$output = '<a target="_blank" class="media-upload-field-preview" data-formats="' . esc_attr( $field_formats ) . '" href="' . esc_url( $media_url ) . '"><span>' . esc_html__( 'File', 'jobboardwp' ) . '</span></a>';
					/**
					 * Filters the media-type field preview HTML. Backend form fields.
					 *
					 * @since 1.2.4
					 * @hook jb_admin_preview_media_output
					 *
					 * @param {string} $output     Media field preview.
					 * @param {array}  $field_data Field data.
					 *
					 * @return {string} Filtered the media-type field preview HTML.
					 */
					echo wp_kses( apply_filters( 'jb_admin_preview_media_output', $output, $field_data ), JB()->get_allowed_html( 'templates' ) );
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
			<input type="hidden" class="jb-media-value" id="<?php echo esc_attr( $id ); ?>" data-field="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $media_url ); ?>" />
			<input type="hidden" class="jb-media-value-hash" id="<?php echo esc_attr( $field_id ); ?>_hash" name="<?php echo esc_attr( $field_id ); ?>_hash" value="" />

			<?php
			return ob_get_clean();
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
			$default_index = 'default' . $i;
			$default       = isset( $field_data[ $default_index ] ) ? $field_data[ $default_index ] : '';

			$value_index = 'value' . $i;
			if ( 'checkbox' === $field_data['type'] ) {
				$value = ( isset( $field_data[ $value_index ] ) && '' !== $field_data[ $value_index ] ) ? $field_data[ $value_index ] : $default;
			} else {
				$value = isset( $field_data[ $value_index ] ) ? $field_data[ $value_index ] : $default;
			}

			if ( ! empty( $value ) && isset( $field_data['encode'] ) ) {
				$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE );
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
