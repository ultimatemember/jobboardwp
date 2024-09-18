<?php
/**
 * Plugin Name: JobBoardWP
 * Plugin URI: https://jobboardwp.com/
 * Description: Add a modern job board to your website. Display job listings and allow employers to submit and manage jobs all from the front-end
 * Version: 1.2.8
 * Author: JobBoardWP
 * Text Domain: jobboardwp
 * Domain Path: /languages
 *
 * @package JB
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/plugin.php';
$plugin_data = get_plugin_data( __FILE__ );

define( 'JB_URL', plugin_dir_url( __FILE__ ) );
define( 'JB_PATH', plugin_dir_path( __FILE__ ) );
define( 'JB_PLUGIN', plugin_basename( __FILE__ ) );
define( 'JB_VERSION', $plugin_data['Version'] );
define( 'JB_PLUGIN_NAME', $plugin_data['Name'] );

if ( ! defined( 'JB_CRON_DEBUG' ) ) {
	define( 'JB_CRON_DEBUG', false );
}

require_once 'includes/class-jb-functions.php';
require_once 'includes/class-jb.php';

//run
JB();
