<?php
namespace jb\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\frontend\Init' ) ) {

	/**
	 * Class Init
	 *
	 * @package jb\frontend
	 */
	class Init {

		/**
		 * @return bool
		 *
		 * @since 1.0
		 */
		public function is_job_page() {
			return is_singular( array( 'jb-job' ) );
		}

		/**
		 * @since 1.0
		 */
		public function includes() {
			$this->jobs_directory();
			$this->enqueue();
			$this->templates();
			$this->shortcodes();
			$this->actions_listener();
		}

		/**
		 * @since 1.0
		 *
		 * @return Templates
		 */
		public function templates() {
			if ( empty( JB()->classes['jb\frontend\templates'] ) ) {
				JB()->classes['jb\frontend\templates'] = new Templates();
			}

			return JB()->classes['jb\frontend\templates'];
		}

		/**
		 * @since 1.0
		 *
		 * @return Shortcodes
		 */
		public function shortcodes() {
			if ( empty( JB()->classes['jb\frontend\shortcodes'] ) ) {
				JB()->classes['jb\frontend\shortcodes'] = new Shortcodes();
			}

			return JB()->classes['jb\frontend\shortcodes'];
		}

		/**
		 * @since 1.0
		 *
		 * @return Enqueue
		 */
		public function enqueue() {
			if ( empty( JB()->classes['jb\frontend\enqueue'] ) ) {
				JB()->classes['jb\frontend\enqueue'] = new Enqueue();
			}

			return JB()->classes['jb\frontend\enqueue'];
		}

		/**
		 * @since 1.0
		 *
		 * @param array|bool $data
		 *
		 * @return Forms
		 */
		public function forms( $data = false ) {
			if ( empty( JB()->classes[ 'jb\frontend\forms' . $data['id'] ] ) ) {
				JB()->classes[ 'jb\frontend\forms' . $data['id'] ] = new Forms( $data );
			}

			return JB()->classes[ 'jb\frontend\forms' . $data['id'] ];
		}

		/**
		 * @since 1.0
		 *
		 * @return Jobs_Directory
		 */
		public function jobs_directory() {
			if ( empty( JB()->classes['jb\frontend\jobs_directory'] ) ) {
				JB()->classes['jb\frontend\jobs_directory'] = new Jobs_Directory();
			}

			return JB()->classes['jb\frontend\jobs_directory'];
		}

		/**
		 * @since 1.0
		 *
		 * @return Actions_Listener
		 */
		public function actions_listener() {
			if ( empty( JB()->classes['jb\frontend\actions_listener'] ) ) {
				JB()->classes['jb\frontend\actions_listener'] = new Actions_Listener();
			}
			return JB()->classes['jb\frontend\actions_listener'];
		}
	}
}
