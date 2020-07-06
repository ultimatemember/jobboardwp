<?php if ( ! defined( 'ABSPATH' ) ) exit;

global $wp_query; ?>

<?php echo JB()->common()->mail()->get_email_template( $wp_query->query_vars['jb_email_content']['slug'], $wp_query->query_vars['jb_email_content'] ); ?>