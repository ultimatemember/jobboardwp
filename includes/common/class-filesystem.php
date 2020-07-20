<?php
namespace jb\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\common\Filesystem' ) ) {


	/**
	 * Class Filesystem
	 *
	 * @package jb\common
	 */
	class Filesystem {


		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		var $upload_dir = '';


		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		var $upload_url = '';


		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		var $temp_upload_dir = '';


		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		var $temp_upload_url = '';


		/**
		 * Filesystem constructor.
		 */
		function __construct() {

			$this->init_paths();

		}


		/**
		 * Init uploading URL and directory
		 *
		 * @since 1.0
		 */
		function init_paths() {
			$this->temp_upload_dir = $this->get_upload_dir( 'jobboardwp/temp', 'allow' );
			$this->temp_upload_url = $this->get_upload_url( 'jobboardwp/temp' );
		}


		/**
		 * Function for recursively delete all files and folders in current folder
		 *
		 * @param string $dir
		 *
		 * @return bool
		 *
		 * @since 1.0
		 */
		function recursive_delete_files( $dir ) {
			if ( is_dir( $dir ) ) {
				$files = scandir( $dir );
				foreach ( $files as $file ) {
					if ( $file != '.' && $file != '..' ) {
						$this->recursive_delete_files( $dir . DIRECTORY_SEPARATOR . $file );
					}
				}
				rmdir( $dir );
				return true;
			} elseif( file_exists( $dir ) ) {
				unlink( $dir );
				return true;
			}
			return false;
		}


		/**
		 * Remove all files, which are older then 24 hours
		 *
		 * @since 1.0
		 */
		function clear_temp_dir() {
			$maxFileAge = 24 * 3600; // Temp file age in seconds

			if ( ! is_dir( $this->temp_upload_dir ) || ! $dir = opendir( $this->temp_upload_dir ) ) {
				return;
			}

			while ( ( $file = readdir( $dir ) ) !== false ) {

				if ( $file == '.' || $file == '..' ) {
					continue;
				}

				$tmpfilePath = $this->temp_upload_dir . DIRECTORY_SEPARATOR . $file;

				// Remove temp file if it is older than the max age and is not the current file
				if ( filemtime( $tmpfilePath ) < time() - $maxFileAge ) {
					@unlink( $tmpfilePath );
				}
			}
			closedir( $dir );
		}


		/**
		 * Get upload dir of plugin
		 *
		 * @param string $dir
		 * @param string $dir_access
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		function get_upload_dir( $dir = '', $dir_access = '' ) {

			if ( empty( $this->upload_dir ) ) {
				$uploads            = wp_upload_dir();
				$this->upload_dir   = str_replace( '/', DIRECTORY_SEPARATOR, $uploads['basedir'] . DIRECTORY_SEPARATOR );
			}

			$dir = str_replace( '/', DIRECTORY_SEPARATOR, $dir );

			//check and create folder
			if ( ! empty( $dir ) ) {
				$folders = explode( DIRECTORY_SEPARATOR, $dir );
				$cur_folder = '';
				foreach ( $folders as $folder ) {
					$prev_dir = $cur_folder;
					$cur_folder .= $folder . DIRECTORY_SEPARATOR;
					if ( ! is_dir( $this->upload_dir . $cur_folder ) && wp_is_writable( $this->upload_dir . $prev_dir ) ) {
						mkdir( $this->upload_dir . $cur_folder, 0777 );
						if ( $dir_access == 'deny' ) {
							$htp = fopen( $this->upload_dir . $cur_folder . DIRECTORY_SEPARATOR . '.htaccess', 'w' );
							fputs( $htp, 'deny from all' ); // $file being the .htpasswd file
						} elseif ( $dir_access == 'allow' ) {
							$htp = fopen( $this->upload_dir . $cur_folder . DIRECTORY_SEPARATOR . '.htaccess', 'w' );
							fputs( $htp, 'allow from all' ); // $file being the .htpasswd file
						}
					}
				}
			}

			//return dir path
			return $this->upload_dir . $dir;
		}


		/**
		 * Get upload url of plugin
		 *
		 * @param string $url
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		function get_upload_url( $url = '' ) {
			if ( empty( $this->upload_url ) ) {
				$uploads            = wp_upload_dir();
				$this->upload_url   = $uploads['baseurl'] . '/';
			}

			//return dir path
			return $this->upload_url . $url;
		}


		/**
		 * Format Bytes
		 *
		 * @param int $size
		 * @param int $precision
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		function format_bytes( $size, $precision = 1 ) {
			if ( is_numeric( $size ) ) {
				$base = log( $size, 1024 );
				$suffixes = [ '', 'kb', 'MB', 'GB', 'TB' ];
				$computed_size = round( pow( 1024, $base - floor( $base ) ), $precision );
				$unit = $suffixes[ absint( floor( $base ) ) ];

				return $computed_size . ' ' . $unit;
			}

			return '';
		}

	}
}