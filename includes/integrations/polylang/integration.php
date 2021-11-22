<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * @param int $page_id
 * @param string $slug
 *
 * @return mixed
 */
function jb_get_predefined_page_id_polylang( $page_id, $slug ) {
	if ( $post = pll_get_post( $page_id ) ) {
		$page_id = $post;
	}

	return $page_id;
}
add_filter( 'jb_get_predefined_page_id', 'jb_get_predefined_page_id_polylang', 10, 2 );


/**
 * @return array
 */
function jb_admin_settings_get_pages_list_polylang() {
	$return = array();

	$current_lang_query = new \WP_Query( array(
		'post_type'           => 'page',
		's'                   => sanitize_text_field( $_GET['search'] ), // the search query
		'post_status'         => 'publish', // if you don't want drafts to be returned
		'ignore_sticky_posts' => 1,
		'fields'              => 'ids',
		'posts_per_page'      => -1,
	) );

	$posts = array();
	if ( ! empty( $current_lang_posts = $current_lang_query->get_posts() ) ) {
		$posts = array_merge( $posts, $current_lang_posts );
	}

	if ( pll_current_language() !== pll_default_language() ) {
		$default_lang_query = new \WP_Query( array(
			'post_type'           => 'page',
			's'                   => sanitize_text_field( $_GET['search'] ), // the search query
			'post_status'         => 'publish', // if you don't want drafts to be returned
			'ignore_sticky_posts' => 1,
			'fields'              => 'ids',
			'lang'                => pll_default_language(),
			'posts_per_page'      => -1,
		) );

		if ( ! empty( $default_lang_posts = $default_lang_query->get_posts() ) ) {
			foreach ( $default_lang_posts as $k => $post_id ) {
				$lang_post_id = pll_get_post( $post_id, pll_current_language() );
				if ( in_array( $lang_post_id, $posts, true ) ) {
					unset( $default_lang_posts[ $k ] );
				}
			}
			$posts = array_merge( $posts, array_values( $default_lang_posts ) );
		}
	}

	$active_languages = pll_languages_list();

	foreach ( $active_languages as $language_code ) {
		if ( $language_code === pll_current_language() || $language_code === pll_default_language() ) {
			continue;
		}

		$active_lang_query = new \WP_Query( array(
			'post_type'           => 'page',
			's'                   => sanitize_text_field( $_GET['search'] ), // the search query
			'post_status'         => 'publish', // if you don't want drafts to be returned
			'ignore_sticky_posts' => 1,
			'fields'              => 'ids',
			'lang'                => $language_code,
			'posts_per_page'      => -1,
		) );

		if ( ! empty( $active_lang_posts = $active_lang_query->get_posts() ) ) {
			foreach ( $active_lang_posts as $k => $post_id ) {
				$current_lang_post_id = pll_get_post( $post_id, pll_current_language() );
				$default_lang_post_id = pll_get_post( $post_id, pll_default_language() );
				if ( in_array( $current_lang_post_id, $posts, true ) || in_array( $default_lang_post_id, $posts, true ) ) {
					unset( $active_lang_posts[ $k ] );
				}
			}
			$posts = array_merge( $posts, array_values( $active_lang_posts ) );
		}
	}

	// you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
	$search_results = new \WP_Query( array(
		'post_type'           => 'page',
		's'                   => sanitize_text_field( $_GET['search'] ), // the search query
		'post_status'         => 'publish', // if you don't want drafts to be returned
		'ignore_sticky_posts' => 1,
		'posts_per_page'      => 10, // how much to show at once
		'paged'               => absint( $_GET['page'] ),
		'orderby'             => 'title',
		'order'               => 'asc',
		'lang'                => '', // set empty language for getting posts of all languages
		'post__in'            => $posts,
	) );
	$posts = $search_results->get_posts();

	if ( ! empty( $posts ) ) {
		foreach ( $posts as $post ) {
			// shorten the title a little
			$title = ( mb_strlen( $post->post_title ) > 50 ) ? mb_substr( $post->post_title, 0, 49 ) . '...' : $post->post_title;
			$title = sprintf( __( '%s (ID: %s)', 'jobboardwp' ), $title, $post->ID );
			$return[] = array( $post->ID, $title ); // array( Post ID, Post Title )
		}
	}

	$return['total_count'] = $search_results->found_posts;
	return $return;
}
add_filter( 'jb_admin_settings_get_pages_list', 'jb_admin_settings_get_pages_list_polylang', 10 );


/**
 * @param false $pre_result
 * @param int $page_id
 *
 * @return array
 */
function jb_admin_settings_pages_list_value_polylang( $pre_result, $page_id ) {
	if ( ! empty( $opt_value = JB()->options()->get( $page_id ) ) ) {

		if ( $post = pll_get_post( $opt_value ) ) {
			$opt_value = $post;
		}

		$title = get_the_title( $opt_value );
		$title = ( mb_strlen( $title ) > 50 ) ? mb_substr( $title, 0, 49 ) . '...' : $title;
		$title = sprintf( __( '%s (ID: %s)', 'jobboardwp' ), $title, $opt_value );

		$pre_result = array( $opt_value => $title );
		$pre_result['page_value'] = $opt_value;
	}

	return $pre_result;
}
add_filter( 'jb_admin_settings_pages_list_value', 'jb_admin_settings_pages_list_value_polylang', 10, 2 );


/**
 * @param array $variables
 *
 * @return array
 */
function jb_common_js_variables_polylang( $variables ) {
	$variables['locale'] = pll_current_language();
	return $variables;
}
add_filter( 'jb_common_js_variables', 'jb_common_js_variables_polylang', 10, 1 );


/**
 * @param string $locale
 */
function jb_admin_init_locale_polylang( $locale ) {
	global $polylang;
	PLL()->curlang = $polylang->model->get_language( $locale );
}
add_action( 'jb_admin_init_locale', 'jb_admin_init_locale_polylang', 10, 1 );
