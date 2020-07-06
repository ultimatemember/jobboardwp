<?php
/*
Plugin Name: JobBoardWP
Plugin URI: https://jobboardwp.com/
Description: A full-featured, powerful job board plugin for WordPress
Version: 1.0.0
Author: JobBoardWP
Author URI: https://jobboardwp.com/
Text Domain: jobboardwp
Domain Path: /languages
*/

defined( 'ABSPATH' ) || exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
$plugin_data = get_plugin_data( __FILE__ );

define( 'jb_url', plugin_dir_url( __FILE__ ) );
define( 'jb_path', plugin_dir_path( __FILE__ ) );
define( 'jb_plugin', plugin_basename( __FILE__ ) );
define( 'jb_author', $plugin_data['AuthorName'] );
define( 'jb_version', $plugin_data['Version'] );
define( 'jb_plugin_name', $plugin_data['Name'] );

require_once 'includes/class-functions.php';
require_once 'includes/class-init.php';

//run
JB();