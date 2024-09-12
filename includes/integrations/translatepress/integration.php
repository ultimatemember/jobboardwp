<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get default and current locales.
 *
 * @since 1.1.1
 *
 * @return array
 */
function jb_translatepress_get_languages_codes() {
	$trp          = TRP_Translate_Press::get_trp_instance();
	$trp_settings = $trp->get_component( 'settings' );
	$settings     = $trp_settings->get_settings();

	$default_language = $settings['default-language'];

	return array(
		'default' => $default_language,
		'current' => get_locale(),
	);
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
add_filter( 'jb_get_predefined_page_id', 'jb_get_predefined_page_id_translatepress' );

/**
 * @param array $variables
 *
 * @return array
 */
function jb_common_js_variables_translatepress( $variables ) {
	$variables['locale'] = get_locale();
	return $variables;
}
add_filter( 'jb_common_js_variables', 'jb_common_js_variables_translatepress' );

/**
 *
 * @since 1.1.1
 * @since 1.2.2 Added $module argument.
 *
 * @param array  $template_locations
 * @param string $template_name
 * @param string $module
 * @param string $template_path
 *
 * @return array
 */
function jb_pre_template_locations_translatepress( $template_locations, $template_name, $module, $template_path ) {
	if ( 0 === strpos( $template_name, 'emails/' ) && JB()->common()->mail()->is_sending() ) {
		return $template_locations;
	}

	$language_codes = jb_translatepress_get_languages_codes();

	if ( $language_codes['default'] !== $language_codes['current'] ) {
		$lang = $language_codes['current'];

		$ml_template_locations = array_map(
			function ( $item ) use ( $template_path, $lang ) {
				return str_replace( trailingslashit( $template_path ), trailingslashit( $template_path ) . $lang . '/', $item );
			},
			$template_locations
		);

		$template_locations = array_merge( $ml_template_locations, $template_locations );
	}

	return $template_locations;
}
add_filter( 'jb_pre_template_locations_common_locale_integration', 'jb_pre_template_locations_translatepress', 10, 4 );
