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
	// create post types here because on uninstall there aren't registered CPT and terms, the same for post statuses
	JB()->common()->cpt()->create_post_types();
	JB()->common()->cpt()->register_post_statuses();

	//remove JB settings
	$settings_defaults = JB()->config()->get( 'defaults' );
	foreach ( $settings_defaults as $k => $v ) {
		JB()->options()->delete( $k );
	}

	//delete job posts
	$jb_posts = get_posts(
		array(
			'post_type'   => 'jb-job',
			'post_status' => array( 'any', 'jb-preview', 'jb-expired', 'inherit', 'trash', 'auto-draft' ),
			'numberposts' => -1,
		)
	);
	foreach ( $jb_posts as $jb_post ) {
		wp_delete_post( $jb_post->ID, true );
	}

	// remove usermeta
	global $wpdb;
	$wpdb->query(
		"DELETE
        FROM {$wpdb->usermeta}
        WHERE meta_key LIKE 'jb_company_%'"
	);

	// remove Job Category terms
	$categories = get_terms(
		array(
			'taxonomy'   => 'jb-job-category',
			'hide_empty' => false,
		)
	);
	if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
		foreach ( $categories as $category ) {
			wp_delete_term( $category->term_id, 'jb-job-category' );
		}
	}

	// remove Job Type terms
	$types = get_terms(
		array(
			'taxonomy'   => 'jb-job-type',
			'hide_empty' => false,
		)
	);
	if ( ! empty( $types ) && ! is_wp_error( $types ) ) {
		foreach ( $types as $t ) {
			wp_delete_term( $t->term_id, 'jb-job-type' );
		}
	}

	// remove uploads
	/** @var $wp_filesystem \WP_Filesystem_Base */
	global $wp_filesystem;

	if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$credentials = request_filesystem_credentials( site_url() );
		\WP_Filesystem( $credentials );
	}
	$jb_dir = JB()->common()->filesystem()->get_upload_dir( 'jobboardwp' );
	$wp_filesystem->delete( $jb_dir, true );

	// remove plugin system options
	delete_option( 'jb_last_version_upgrade' );
	delete_option( 'jb_first_activation_date' );
	delete_option( 'jb_version' );
	delete_option( 'jb_flush_rewrite_rules' );
	delete_option( 'jb_hidden_admin_notices' );
	// created via WP automatically
	delete_option( 'jb-job-category_children' );
	delete_option( 'jb-job-type_children' );
	// option for widget
	delete_option( 'widget_jb_recent_jobs' );
}
