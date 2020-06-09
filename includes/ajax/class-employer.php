<?php namespace jb\ajax;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\ajax\Employer' ) ) {


	/**
	 * Class Employer
	 *
	 * @package jb\ajax
	 */
	class Employer {


		/**
		 * Employer constructor.
		 */
		function __construct() {
		}


		/**
		 * @param string $dir
		 * @param string $name
		 * @param string $ext
		 *
		 * @return string
		 */
		function unique_filename( $dir, $name, $ext ) {
			$hashed = hash('ripemd160', time() . mt_rand( 10, 1000 ) );
			$name = "company_logo_{$hashed}{$ext}";

			return $name;
		}


		/**
		 * Uploading Logo AJAX process
		 */
		function upload_logo() {
			$nonce = isset( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : '';
			if ( ! wp_verify_nonce( $nonce, 'jb-frontend-nonce' ) ) {
				wp_send_json( [
					'OK'    => 0,
					'info'  => __( 'Wrong nonce.', 'jobboardwp' ),
				] );
			}

			$files = [];

			$chunk = filter_input( 0, 'chunk' );
			$chunks = filter_input( 0, 'chunks' );

			// Get a file name
			if ( isset( $_REQUEST['name'] ) ) {
				$fileName = $_REQUEST['name'];
			} elseif ( ! empty( $_FILES ) ) {
				$fileName = $_FILES['file']['name'];
			} else {
				$fileName = uniqid( 'file_' );
			}

			$mimes = [
				'jpg|jpeg|jpe'  => 'image/jpeg',
				'gif'           => 'image/gif',
				'png'           => 'image/png',
				'bmp'           => 'image/bmp',
				'tiff|tif'      => 'image/tiff',
				'ico'           => 'image/x-icon',
			];

			$image_type = wp_check_filetype( $fileName, $mimes );
			if ( ! $image_type['ext'] ) {
				wp_send_json( [
					'OK'    => 0,
					'info'  => __( 'Wrong filetype.', 'jobboardwp' ),
				] );
			}

			JB()->common()->filesystem()->clear_temp_dir();

			if ( empty( $_FILES ) || $_FILES['file']['error'] ) {
				wp_send_json( [
					'OK'    => 0,
					'info'  => __( 'Failed to move uploaded file.', 'jobboardwp' ),
				] );
			}

			// Uploader for the chunks
			if ( $chunks ) {

				if ( isset( $_COOKIE['jb-logo-upload'] ) ) {
					$unique_name = $_COOKIE['jb-logo-upload'];
					$filePath = JB()->common()->filesystem()->temp_upload_dir . DIRECTORY_SEPARATOR . $unique_name;
				} else {
					$unique_name = wp_unique_filename( JB()->common()->filesystem()->temp_upload_dir, $fileName, [ &$this, 'unique_filename' ] );
					$filePath = JB()->common()->filesystem()->temp_upload_dir . DIRECTORY_SEPARATOR . $unique_name;
					if ( $chunks > 1 ) {
						JB()->setcookie( 'jb-logo-upload', $unique_name );
					}
				}

				// Open temp file
				$out = @fopen( "{$filePath}.part", $chunk == 0 ? 'wb' : 'ab' );

				if ( $out ) {

					// Read binary input stream and append it to temp file
					$in = @fopen( $_FILES['file']['tmp_name'], 'rb' );

					if ( $in ) {
						while ( $buff = fread( $in, 4096 ) ) {
							fwrite( $out, $buff );
						}
					} else {
						wp_send_json( [
							'OK'    => 0,
							'info'  => __( 'Failed to open input stream.', 'jobboardwp' ),
						] );
					}

					fclose( $in );
					fclose( $out );
					unlink( $_FILES['file']['tmp_name'] );

				} else {

					wp_send_json( [
						'OK'    => 0,
						'info'  => __( 'Failed to open output stream.', 'jobboardwp' ),
					] );

				}

				// Check if file has been uploaded
				if ( $chunk == $chunks - 1 ) {
					// Strip the temp .part suffix off
					rename( "{$filePath}.part", $filePath ); // Strip the temp .part suffix off

					$fileinfo = $_FILES['file'];
					$fileinfo['file'] = $filePath;
					$fileinfo['name_loaded'] = $fileName;
					$fileinfo['name_saved'] = wp_basename( $fileinfo['file'] );
					$fileinfo['hash'] = md5( $fileinfo['name_saved'] . '_jb_uploader_security_salt' );
					$fileinfo['path'] = JB()->common()->filesystem()->temp_upload_dir . DIRECTORY_SEPARATOR . $fileinfo['name_saved'];
					$fileinfo['url'] = JB()->common()->filesystem()->temp_upload_url . DIRECTORY_SEPARATOR . $fileinfo['name_saved'];
					$fileinfo['size'] = filesize( $fileinfo['file'] );
					$fileinfo['size_format'] = size_format( $fileinfo['size'] );
					$fileinfo['time'] = date( 'Y-m-d H:i:s', filemtime( $fileinfo['file'] ) );
					$files[] = $fileinfo;

					JB()->setcookie( 'jb-logo-upload', false );

				} else {

					wp_send_json( [
						'OK'    => 1,
						'info'  => __( 'Upload successful.', 'jobboardwp' ),
					] );

				}
			}

			wp_send_json_success( $files );
		}


	}
}