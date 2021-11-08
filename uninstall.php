<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'jb_last_version_upgrade' );
delete_option( 'jb_first_activation_date' );
delete_option( 'jb_version' );
delete_option( 'jb_flush_rewrite_rules' );
delete_option( 'jb_hidden_admin_notices' );

if ( JB()->options()->get( 'uninstall-delete-settings' ) ) {

	//remove core settings
	$settings_defaults = JB()->config()->get( 'defaults' );
	foreach ( $settings_defaults as $k => $v ) {
		JB()->options()->delete( $k );
	}

	//remove core pages
	foreach ( JB()->config()->get( 'core_pages' ) as $slug => $array ) {
		$page_id = JB()->options()->get( $slug . '_page' );
		if ( ! empty( $page_id ) ) {
			wp_delete_post( $page_id, true );
		}
	}

	//delete UM Custom Post Types posts
	$jb_posts = get_posts(
		array(
			'post_type'   => array(
				'jb-job',
			),
			'post_status' => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ),
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
        WHERE meta_key LIKE 'jb_%'"
	);

	// remove uploads
	$jb_dir = JB()->common()->filesystem()->get_upload_dir( 'jobboardwp' );
	if ( file_exists( $jb_dir ) ) {
		rmdir( $jb_dir );
	}
}
