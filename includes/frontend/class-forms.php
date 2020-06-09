<?php
namespace jb\frontend;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\frontend\Forms' ) ) {


	/**
	 * Class Forms
	 *
	 * @package jb\frontend
	 */
	class Forms {


		/**
		 * @var array
		 */
		var $errors = [];


		var $notices = [];


		/**
		 * @var bool
		 */
		var $form_data;


		/**
		 * Forms constructor.
		 *
		 * @param bool $form_data
		 */
		function __construct( $form_data = false ) {
			if ( $form_data ) {
				$this->form_data = $form_data;
			}
		}


		/**
		 * Set Form Data
		 *
		 * @param $data
		 *
		 * @return $this
		 */
		function set_data( $data ) {
			$this->form_data = $data;
			return $this;
		}


		/**
		 * Render form
		 *
		 *
		 * @param bool $echo
		 * @return string
		 */
		function display( $echo = true ) {

			if ( empty( $this->form_data['fields'] ) ) {
				return '';
			}

			$hidden = '';
			$fields = '';
			foreach ( $this->form_data['fields'] as $field_data ) {
				if ( empty( $field_data['type'] ) ) {
					continue;
				}

				if ( 'hidden' == $field_data['type'] ) {
					$hidden .= $this->render_hidden( $field_data );
				} else {
					$fields .= $this->render_form_row( $field_data );
				}
			}

			ob_start();

			echo $hidden;

			$class = 'form-table jb-form-table ' . ( ! empty( $this->form_data['class'] ) ? $this->form_data['class'] : '' );
			$class_attr = ' class="' . $class . '" '; ?>

            <form action="" method="post" name="jb-job-submission" id="jb-job-submission">

			<table <?php echo $class_attr ?>>
				<tbody><?php echo $fields; ?></tbody>
			</table>

			<?php if ( $echo ) {
				ob_get_flush();
				return '';
			} else {
				return ob_get_clean();
			}
		}


		/**
		 * @param string $field
		 * @param string $text
		 */
		function add_error( $field, $text ) {
			if ( $field === 'global' ) {
				if ( ! isset( $this->errors['global'] ) ) {
					$this->errors['global'] = [];
				}
				$this->errors['global'][] = apply_filters( 'jb_form_global_error', $text );
			} else {
				if ( ! isset( $this->errors[ $field ] ) ) {
					$this->errors[ $field ] = apply_filters( 'jb_form_error', $text, $field );
				}
			}
		}


		/**
		 * @param string $text
		 */
		function add_notice( $text, $key ) {
			$this->notices[ $key ] = apply_filters( 'jb_form_notice', $text, $key );
		}


		/**
		 * If a form has error
		 *
		 * @param  string  $field
		 * @return boolean
		 */
		function has_error( $field ) {
			return ! empty( $this->errors[ $field ] ) || ! empty( $this->errors[ $field ] );
		}


		/**
		 * If a form has errors
		 *
		 * @return boolean
		 */
		function has_errors() {
			return ! empty( $this->errors );
		}


		/**
		 * If a form has notices
		 *
		 * @return boolean
		 */
		function has_notices() {
			return ! empty( $this->notices );
		}


		/**
		 * Flush errors
		 */
		function flush_errors() {
			$this->errors = [];
		}


		/**
		 * Flush notices
		 */
		function flush_notices() {
			$this->errors = [];
		}


		/**
		 * @param string $field
		 *
		 * @return array
		 */
		function get_errors( $field ) {
			return ! empty( $this->errors[ $field ] ) ? $this->errors[ $field ] : [];
		}


		/**
		 *
		 * @return array
		 */
		function get_notices() {
			return ! empty( $this->notices ) ? $this->notices : [];
		}


		/**
		 * @param array $mce_buttons
		 * @param int $editor_id
		 *
		 * @return array
		 */
		function filter_mce_buttons( $mce_buttons, $editor_id ) {
			$mce_buttons = array_diff( $mce_buttons, [ 'alignright', 'alignleft', 'aligncenter', 'wp_adv', 'wp_more', 'fullscreen', 'formatselect', 'spellchecker' ] );
			$mce_buttons = apply_filters( 'jb_rich_text_editor_buttons', $mce_buttons, $editor_id, $this );

			return $mce_buttons;
		}


		/**
		 * @param string $content
		 */
		function render_editor( $content = '' ) {
			add_filter( 'mce_buttons', [ $this, 'filter_mce_buttons' ], 10, 2 );

			add_action( 'after_wp_tiny_mce', function( $settings ) {
				if ( isset( $settings['jb_job_description']['plugins'] ) && false !== strpos( $settings['jb_job_description']['plugins'], 'wplink' ) ) {
					echo '<style>
						#link-selector > .howto, #link-selector > #search-panel { display:none; }
					</style>';
				}
			} );

			$editor_settings = apply_filters( 'jb_content_editor_options', [
				'textarea_name' => 'job_description',
				'wpautop'       => true,
				'editor_height' => 145,
				'media_buttons' => false,
				'quicktags'     => false,
				'tinymce'       => [
					'init_instance_callback' => "function (editor) {
													editor.on( 'keyup paste mouseover', function (e) {
													var content = editor.getContent( { format: 'html' } ).trim();
													var textarea = jQuery( '#' + editor.id ); 
													textarea.val( content ).trigger( 'keyup' ).trigger( 'keypress' ).trigger( 'keydown' ).trigger( 'change' ).trigger( 'paste' ).trigger( 'mouseover' );
												});}"
				],
			] );

			wp_editor( $content, 'jb_job_description', $editor_settings );

			remove_filter( 'mce_buttons', [ $this, 'filter_mce_buttons' ], 10 );
		}
	}
}