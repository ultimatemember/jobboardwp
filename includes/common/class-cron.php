<?php namespace jb\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'jb\common\Cron' ) ) {


	/**
	 * Class Cron
	 *
	 * @package jb\common
	 */
	class Cron {


		/**
		 * Cron constructor.
		 */
		function __construct() {
			add_action( 'jb_check_for_expired_jobs', [ JB()->common()->job(), 'check_for_expired_jobs' ] );
			add_action( 'jb_check_for_reminder_expired_jobs', [ JB()->common()->job(), 'check_for_reminder_expired_jobs' ] );
			add_action( 'jb_delete_old_previews', [ JB()->common()->job(), 'delete_old_previews' ] );
		}


		/**
		 * Clear scheduled tasks
		 *
		 * @since 1.0
		 */
		function unschedule_tasks() {
			wp_clear_scheduled_hook( 'jb_check_for_expired_jobs' );
			wp_clear_scheduled_hook( 'jb_check_for_reminder_expired_jobs' );
			wp_clear_scheduled_hook( 'jb_delete_old_previews' );
		}


		/**
		 * Maybe create scheduled events
		 *
		 * @since 1.0
		 */
		function maybe_schedule_tasks() {
			if ( ! wp_next_scheduled( 'jb_check_for_expired_jobs' ) ) {
				wp_schedule_event( time(), 'hourly', 'jb_check_for_expired_jobs' );
			}
			if ( ! wp_next_scheduled( 'jb_check_for_reminder_expired_jobs' ) ) {
				wp_schedule_event( time(), 'hourly', 'jb_check_for_reminder_expired_jobs' );
			}
			if ( ! wp_next_scheduled( 'jb_delete_old_previews' ) ) {
				wp_schedule_event( time(), 'daily', 'jb_delete_old_previews' );
			}
		}

	}
}