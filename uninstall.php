<?php
/**
 * Uninstall JobBoardWP
 *
 */


if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


delete_option( 'jb_last_version_upgrade' );
delete_option( 'jb_first_activation_date' );
delete_option( 'jb_version' );
delete_option( 'jb_flush_rewrite_rules' );
delete_option( 'jb_hidden_admin_notices' );