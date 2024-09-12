<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array
 * @throws Exception
 */
function jb_weglot_get_languages_codes() {
	$default = weglot_get_original_language();
	$current = weglot_get_current_language();

	$languages_map = array(
		'af'      => 'af_ZA',
		'ar'      => 'ar',
		'az'      => 'az',
		'be'      => 'be_BY',
		'bg'      => 'bg_BG',
		'bn'      => 'bn_BD',
		'bs'      => 'bs_BA',
		'ca'      => 'ca',
		'cs'      => 'cs_CZ',
		'cy'      => 'cy_GB',
		'da'      => 'da_DK',
		'de'      => 'de_DE',
		'el'      => 'el',
		'en'      => 'en_US',
		'eo'      => 'eo_UY',
		'es'      => 'es_ES',
		'et'      => 'et',
		'eu'      => 'eu_ES',
		'fa'      => 'fa_IR',
		'fi'      => 'fi',
		'fo'      => 'fo_FO',
		'fr'      => 'fr_FR',
		'ga'      => 'ga_IE',
		'gl'      => 'gl_ES',
		'he'      => 'he_IL',
		'hi'      => 'hi_IN',
		'hr'      => 'hr',
		'hu'      => 'hu_HU',
		'hy'      => 'hy_AM',
		'id'      => 'id_ID',
		'is'      => 'is_IS',
		'it'      => 'it_IT',
		'ja'      => 'ja',
		'ka'      => 'ge_GE',
		'km'      => 'km_KH',
		'ko'      => 'ko_KR',
		'ku'      => 'ckb',
		'lt'      => 'lt_LT',
		'lv'      => 'lv_LV',
		'mg'      => 'mg_MG',
		'mk'      => 'mk_MK',
		'mn'      => 'mn_MN',
		'ms'      => 'ms_MY',
		'mt'      => 'mt_MT',
		'nb'      => 'nb_NO',
		'ne'      => 'ne',
		'no'      => 'nb_NO',
		'nn'      => 'nn_NO',
		'ni'      => 'ni_ID',
		'nl'      => 'nl_NL',
		'pa'      => 'pa_IN',
		'pl'      => 'pl_PL',
		'pt-br'   => 'pt_BR',
		'pt-pt'   => 'pt_PT',
		'qu'      => 'quz_PE',
		'ro'      => 'ro_RO',
		'ru'      => 'ru_RU',
		'si'      => 'si_LK',
		'sk'      => 'sk_SK',
		'sl'      => 'sl_SI',
		'so'      => 'so_SO',
		'sq'      => 'sq_AL',
		'sr'      => 'sr_RS',
		'su'      => 'su_ID',
		'sv'      => 'sv_SE',
		'ta'      => 'ta_IN',
		'tg'      => 'tg_TJ',
		'th'      => 'th',
		'tr'      => 'tr_TR',
		'ug'      => 'ug_CN',
		'uk'      => 'uk',
		'ur'      => 'ur',
		'uz'      => 'uz_UZ',
		'vi'      => 'vi_VN',
		'zh-hans' => 'zh_CN',
		'zh-hant' => 'zh_TW',
	);

	$default = array_key_exists( $default, $languages_map ) ? $languages_map[ $default ] : $default;
	$current = array_key_exists( $current, $languages_map ) ? $languages_map[ $current ] : $current;

	return array(
		'default' => $default,
		'current' => $current,
	);
}

/**
 * @param int $page_id
 *
 * @return int
 */
function jb_get_predefined_page_id_weglot( $page_id ) {
	// just empty method, but works properly
	return $page_id;
}
add_filter( 'jb_get_predefined_page_id', 'jb_get_predefined_page_id_weglot' );

/**
 * @param array $variables
 *
 * @return array
 * @throws Exception
 */
function jb_common_js_variables_weglot( $variables ) {
	$codes = jb_weglot_get_languages_codes();

	$variables['locale'] = $codes['current'];
	return $variables;
}
add_filter( 'jb_common_js_variables', 'jb_common_js_variables_weglot' );

/**
 * @since 1.1.1
 * @since 1.2.2 Added $module argument.
 *
 * @param array  $template_locations
 * @param string $template_name
 * @param string $module
 * @param string $template_path
 *
 * @return array
 * @throws Exception
 */
function jb_pre_template_locations_weglot( $template_locations, $template_name, $module, $template_path ) {
	if ( JB()->common()->mail()->is_sending() && 0 === strpos( $template_name, 'emails/' ) ) {
		return $template_locations;
	}

	$language_codes = jb_weglot_get_languages_codes();

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
add_filter( 'jb_pre_template_locations_common_locale_integration', 'jb_pre_template_locations_weglot', 10, 4 );
