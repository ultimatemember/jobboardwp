<?php
namespace jb\common;

use WP_Filesystem_Base;
use function WP_Filesystem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\common\Filesystem' ) ) {

	/**
	 * Class Filesystem
	 *
	 * @package jb\common
	 */
	class Filesystem {

		/**
		 * @var array
		 *
		 * @since 1.0
		 */
		public $upload_dir = array();

		/**
		 * @var array
		 *
		 * @since 1.0
		 */
		public $upload_url = array();

		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		public $temp_upload_dir = '';

		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		public $temp_upload_url = '';

		/**
		 * Filesystem constructor.
		 */
		public function __construct() {
			$this->init_paths();
		}

		/**
		 * Init uploading URL and directory
		 *
		 * @since 1.0
		 */
		public function init_paths() {
			$this->temp_upload_dir = $this->get_upload_dir( 'jobboardwp/temp' );
			$this->temp_upload_url = $this->get_upload_url( 'jobboardwp/temp' );
		}

		/**
		 * Remove all files, which are older than 24 hours
		 *
		 * @since 1.0
		 */
		public function clear_temp_dir() {
			global $wp_filesystem;

			if ( ! $wp_filesystem instanceof WP_Filesystem_Base ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';

				$credentials = request_filesystem_credentials( site_url() );
				WP_Filesystem( $credentials );
			}

			/**
			 * Filters the maximum file age in the temp folder. By default, it's 24 hours.
			 *
			 * @since 1.0
			 * @hook jb_filesystem_max_file_age
			 *
			 * @param {int} $file_age Temp file age in seconds.
			 *
			 * @return {int} Temp file age in seconds.
			 */
			$file_age = apply_filters( 'jb_filesystem_max_file_age', 24 * 3600 ); // Temp file age in seconds

			if ( ! $wp_filesystem->is_dir( $this->temp_upload_dir ) ) {
				return;
			}

			// phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
			$dir = @opendir( $this->temp_upload_dir );
			if ( ! $dir ) {
				return;
			}

			// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition -- reading folder's content here
			while ( false !== ( $file = readdir( $dir ) ) ) {
				if ( '.' === $file || '..' === $file ) {
					continue;
				}

				$filepath = wp_normalize_path( $this->temp_upload_dir . DIRECTORY_SEPARATOR . $file );

				// Remove temp file if it is older than the max age and is not the current file
				if ( $wp_filesystem->mtime( $filepath ) < time() - $file_age ) {
					$wp_filesystem->delete( $filepath );
				}
			}

			@closedir( $dir );
			// phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged
		}

		/**
		 * Get upload dir of plugin
		 *
		 * @param string   $dir
		 * @param int|null $blog_id
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_upload_dir( $dir = '', $blog_id = null ) {
			global $wp_filesystem;
			// if you need to fix this issue on the localhost
			// https://stackoverflow.com/questions/30688431/wordpress-needs-the-ftp-credentials-to-update-plugins
			// Please add define('FS_METHOD', 'direct'); to avoid question about FTP.
			if ( ! $wp_filesystem instanceof WP_Filesystem_Base ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';

				$credentials = request_filesystem_credentials( site_url() );
				WP_Filesystem( $credentials );
			}

			if ( ! $blog_id ) {
				$blog_id = get_current_blog_id();
			} elseif ( is_multisite() ) {
				switch_to_blog( $blog_id );
			}

			if ( empty( $this->upload_dir[ $blog_id ] ) ) {
				$uploads = wp_upload_dir();
				if ( ! empty( $uploads['error'] ) ) {
					return '';
				}

				$this->upload_dir[ $blog_id ] = $uploads['basedir'];
			}

			$upload_dir = wp_normalize_path( trailingslashit( $this->upload_dir[ $blog_id ] ) . untrailingslashit( $dir ) );

			if ( ! $wp_filesystem->is_dir( $upload_dir ) ) {
				wp_mkdir_p( $upload_dir );
			}

			if ( is_multisite() ) {
				restore_current_blog();
			}

			//return dir path
			return $upload_dir;
		}

		/**
		 * Get upload url of plugin
		 *
		 * @param string $url
		 * @param int|null $blog_id
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_upload_url( $url = '', $blog_id = null ) {
			if ( ! $blog_id ) {
				$blog_id = get_current_blog_id();
			} elseif ( is_multisite() ) {
				switch_to_blog( $blog_id );
			}

			if ( empty( $this->upload_url[ $blog_id ] ) ) {
				$uploads = wp_upload_dir();
				if ( ! empty( $uploads['error'] ) ) {
					return '';
				}

				$this->upload_url[ $blog_id ] = $uploads['baseurl'];
			}

			$upload_url = trailingslashit( $this->upload_url[ $blog_id ] ) . untrailingslashit( $url );

			if ( is_multisite() ) {
				restore_current_blog();
			}

			//return dir path
			return $upload_url;
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
		public function format_bytes( $size, $precision = 1 ) {
			if ( is_numeric( $size ) ) {
				$base     = log( $size, 1024 );
				$suffixes = array( '', 'kb', 'MB', 'GB', 'TB' );

				$computed_size = round( 1024 ** ( $base - floor( $base ) ), $precision );
				$unit          = $suffixes[ absint( floor( $base ) ) ];

				return $computed_size . ' ' . $unit;
			}

			return '';
		}
	}
}
