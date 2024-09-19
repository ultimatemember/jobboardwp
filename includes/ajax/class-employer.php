<?php
namespace jb\ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\ajax\Employer' ) ) {

	/**
	 * Class Employer
	 *
	 * @package jb\ajax
	 */
	class Employer {

		/**
		 * Generate unique filename
		 *
		 * @param string $dir
		 * @param string $name
		 * @param string $ext
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function unique_filename( /** @noinspection PhpUnusedParameterInspection */$dir, $name, $ext ) {
			$hashed = hash( 'ripemd160', time() . wp_rand( 10, 1000 ) );
			return "company_logo_{$hashed}{$ext}";
		}

		/**
		 * Uploading Logo AJAX process
		 *
		 * @since 1.0
		 */
		public function upload_logo() {
			check_ajax_referer( 'jb-frontend-nonce', 'nonce' );

			$files = array();

			$chunk  = ! empty( $_REQUEST['chunk'] ) ? absint( $_REQUEST['chunk'] ) : 0;
			$chunks = ! empty( $_REQUEST['chunks'] ) ? absint( $_REQUEST['chunks'] ) : 0;

			// Get a file name
			if ( isset( $_REQUEST['name'] ) ) {
				$filename = sanitize_file_name( wp_unslash( $_REQUEST['name'] ) );
			} elseif ( ! empty( $_FILES['file']['name'] ) ) {
				$filename = sanitize_file_name( $_FILES['file']['name'] );
			} else {
				$filename = uniqid( 'file_' );
			}

			/**
			 * Filters the MIME-types of the images that can be uploaded as Company Logo.
			 *
			 * @since 1.0
			 * @hook jb_uploading_logo_mime_types
			 *
			 * @param {array} $mime_types MIME types.
			 *
			 * @return {array} MIME types.
			 */
			$mimes = apply_filters(
				'jb_uploading_logo_mime_types',
				array(
					'jpg|jpeg|jpe' => 'image/jpeg',
					'gif'          => 'image/gif',
					'png'          => 'image/png',
					'bmp'          => 'image/bmp',
					'tiff|tif'     => 'image/tiff',
					'ico'          => 'image/x-icon',
					'webp'         => 'image/webp',
					'heic'         => 'image/heic',
				)
			);

			$image_type = wp_check_filetype( $filename, $mimes );
			if ( ! $image_type['ext'] ) {
				wp_send_json(
					array(
						'OK'   => 0,
						'info' => __( 'Wrong filetype.', 'jobboardwp' ),
					)
				);
			}

			JB()->common()->filesystem()->clear_temp_dir();

			if ( empty( $_FILES ) || ! empty( $_FILES['file']['error'] ) ) {
				wp_send_json(
					array(
						'OK'   => 0,
						'info' => __( 'Failed to move uploaded file.', 'jobboardwp' ),
					)
				);
			}

			// Uploader for the chunks
			if ( $chunks ) {

				if ( isset( $_COOKIE['jb-logo-upload'] ) && $chunks > 1 ) {
					$unique_name = sanitize_file_name( wp_unslash( $_COOKIE['jb-logo-upload'] ) );
					$filepath    = JB()->common()->filesystem()->temp_upload_dir . DIRECTORY_SEPARATOR . $unique_name;

					$image_type = wp_check_filetype( $unique_name, $mimes );
					if ( ! $image_type['ext'] ) {
						wp_send_json(
							array(
								'OK'   => 0,
								'info' => __( 'Wrong filetype.', 'jobboardwp' ),
							)
						);
					}
				} else {
					$unique_name = wp_unique_filename( JB()->common()->filesystem()->temp_upload_dir, $filename, array( &$this, 'unique_filename' ) );
					$filepath    = JB()->common()->filesystem()->temp_upload_dir . DIRECTORY_SEPARATOR . $unique_name;
					if ( $chunks > 1 ) {
						JB()->setcookie( 'jb-logo-upload', $unique_name );
					}
				}

				// phpcs:disable WordPress.WP.AlternativeFunctions -- for directly fopen, fwrite, fread, fclose functions using
				// phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged -- for silenced fopen, fwrite, fread, fclose functions running

				// Open temp file
				$out = @fopen( "{$filepath}.part", 0 === $chunk ? 'wb' : 'ab' );

				if ( $out && ! empty( $_FILES['file']['tmp_name'] ) ) {
					$tmp_name = $_FILES['file']['tmp_name']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitized below in sanitize.

					// Read binary input stream and append it to temp file
					$in = @fopen( $tmp_name, 'rb' );

					if ( $in ) {
						// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition -- reading buffer here
						while ( $buff = fread( $in, 4096 ) ) {
							fwrite( $out, $buff );
						}
					} else {
						wp_send_json(
							array(
								'OK'   => 0,
								'info' => __( 'Failed to open input stream.', 'jobboardwp' ),
							)
						);
					}

					fclose( $in );
					fclose( $out );
					unlink( $tmp_name );

				} else {

					wp_send_json(
						array(
							'OK'   => 0,
							'info' => __( 'Failed to open output stream.', 'jobboardwp' ),
						)
					);

				}

				// phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged

				// Check if file has been uploaded
				if ( $chunk === $chunks - 1 ) {
					// Strip the temp .part suffix off
					rename( "{$filepath}.part", $filepath );
					// phpcs:enable WordPress.WP.AlternativeFunctions

					$fileinfo                = ! empty( $_FILES['file'] ) ? wp_unslash( $_FILES['file'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- don't need to sanitize
					$fileinfo['file']        = $filepath;
					$fileinfo['name_loaded'] = $filename;
					$fileinfo['name_saved']  = wp_basename( $fileinfo['file'] );
					$fileinfo['hash']        = md5( $fileinfo['name_saved'] . '_jb_uploader_security_salt' );
					$fileinfo['path']        = JB()->common()->filesystem()->temp_upload_dir . DIRECTORY_SEPARATOR . $fileinfo['name_saved'];
					$fileinfo['url']         = JB()->common()->filesystem()->temp_upload_url . '/' . $fileinfo['name_saved'];
					$fileinfo['size']        = filesize( $fileinfo['file'] );
					$fileinfo['size_format'] = size_format( $fileinfo['size'] );
					$fileinfo['time']        = gmdate( 'Y-m-d H:i:s', filemtime( $fileinfo['file'] ) );

					$files[] = $fileinfo;

					JB()->setcookie( 'jb-logo-upload', false );

				} else {

					wp_send_json(
						array(
							'OK'   => 1,
							'info' => __( 'Upload successful.', 'jobboardwp' ),
						)
					);

				}
			}

			wp_send_json_success( $files );
		}
	}
}
