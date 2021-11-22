<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * @param int $page_id
 *
 * @return mixed
 */
function jb_get_predefined_page_id_translatepress( $page_id ) {
	// just empty method, but works properly
	return $page_id;
}
add_filter( 'jb_get_predefined_page_id', 'jb_get_predefined_page_id_translatepress', 10, 1 );


/**
 * @param array $variables
 *
 * @return array
 */
function jb_common_js_variables_translatepress( $variables ) {
	$variables['locale'] = get_locale();
	return $variables;
}
add_filter( 'jb_common_js_variables', 'jb_common_js_variables_translatepress', 10, 1 );
