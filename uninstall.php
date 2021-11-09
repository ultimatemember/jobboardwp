<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
if ( ! defined( 'JB_PATH' ) ) {
	define( 'JB_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'JB_URL' ) ) {
	define( 'JB_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'JB_PLUGIN' ) ) {
	define( 'JB_PLUGIN', plugin_basename( __FILE__ ) );
}

require_once 'includes/class-jb-functions.php';
require_once 'includes/class-jb.php';

if ( JB()->options()->get( 'uninstall-delete-settings' ) ) {

	//remove core settings
	$settings_defaults = JB()->config()->get( 'defaults' );
	foreach ( $settings_defaults as $k => $v ) {
		JB()->options()->delete( $k );
	}

	//delete job posts
	$jb_posts = get_posts(
		array(
			'post_type'   => 'jb-job',
			'post_status' => array( 'any' ),
			'numberposts' => -1,
		)
	);
	foreach ( $jb_posts as $jb_post ) {
		wp_delete_post( $jb_post->ID, 1 );
	}

	// remove usermeta
	global $wpdb;
	$wpdb->query(
		"DELETE 
        FROM {$wpdb->usermeta}
        WHERE meta_key LIKE 'jb_company%'"
	);

	// remove options
	$wpdb->query(
		"DELETE 
        FROM {$wpdb->options}
        WHERE option_name LIKE 'jb_%'"
	);

	// remove termmeta
	$wpdb->query(
		"DELETE 
        FROM {$wpdb->termmeta}
        WHERE meta_key LIKE 'jb-%'"
	);

	// remove term_taxonomy
	$wpdb->query(
		"DELETE 
        FROM {$wpdb->term_taxonomy}
        WHERE taxonomy LIKE 'jb-job-%'"
	);

	// remove uploads
	global $wp_filesystem;
	$jb_dir = JB()->common()->filesystem()->get_upload_dir( 'jobboardwp' );
	$wp_filesystem->delete( $jb_dir, true );
}
