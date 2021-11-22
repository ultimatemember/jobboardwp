<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Get predefined page translation for current language
 *
 * @param int $page_id
 *
 * @return mixed
 */
function jb_get_predefined_page_id_wpml( $page_id ) {
	global $sitepress;

	$page_id = wpml_object_id_filter( $page_id, 'page', true, $sitepress->get_current_language() );

	return $page_id;
}
add_filter( 'jb_get_predefined_page_id', 'jb_get_predefined_page_id_wpml', 10, 1 );


/**
 * @return array
 */
function jb_admin_settings_get_pages_list_wpml() {
	// phpcs:disable WordPress.Security.NonceVerification -- is verified in JB()->ajax()->settings()->get_pages_list()
	$return = array();

	$current_lang_query = new \WP_Query(
		array(
			'post_type'           => 'page',
			's'                   => sanitize_text_field( $_GET['search'] ), // the search query
			'post_status'         => 'publish', // if you don't want drafts to be returned
			'ignore_sticky_posts' => 1,
			'fields'              => 'ids',
			'posts_per_page'      => -1,
		)
	);

	global $sitepress;

	$code         = $sitepress->get_current_language();
	$code_default = $sitepress->get_default_language();

	$posts = array();
	$current_lang_posts = $current_lang_query->get_posts();
	if ( ! empty( $current_lang_posts ) ) {
		$posts = array_merge( $posts, $current_lang_posts );
	}

	if ( $code !== $code_default ) {
		$sitepress->switch_lang( $code_default );

		$default_lang_query = new \WP_Query(
			array(
				'post_type'           => 'page',
				's'                   => sanitize_text_field( $_GET['search'] ), // the search query
				'post_status'         => 'publish', // if you don't want drafts to be returned
				'ignore_sticky_posts' => 1,
				'fields'              => 'ids',
				'posts_per_page'      => -1,
			)
		);

		$default_lang_posts = $default_lang_query->get_posts();
		if ( ! empty( $default_lang_posts ) ) {
			foreach ( $default_lang_posts as $k => $post_id ) {
				$lang_post_id = wpml_object_id_filter( $post_id, 'page', true, $code );
				if ( $lang_post_id && in_array( $lang_post_id, $posts, true ) ) {
					unset( $default_lang_posts[ $k ] );
				}
			}
			$posts = array_merge( $posts, array_values( $default_lang_posts ) );
		}
	}

	$active_languages = $sitepress->get_active_languages();

	foreach ( $active_languages as $language_code ) {
		if ( $language_code['code'] === $code || $language_code['code'] === $code_default ) {
			continue;
		}

		$sitepress->switch_lang( $language_code['code'] );

		$active_lang_query = new \WP_Query(
			array(
				'post_type'           => 'page',
				's'                   => sanitize_text_field( $_GET['search'] ), // the search query
				'post_status'         => 'publish', // if you don't want drafts to be returned
				'ignore_sticky_posts' => 1,
				'fields'              => 'ids',
				'posts_per_page'      => -1,
			)
		);

		$active_lang_posts = $active_lang_query->get_posts();
		if ( ! empty( $active_lang_posts ) ) {
			foreach ( $active_lang_posts as $k => $post_id ) {
				$current_lang_post_id = wpml_object_id_filter( $post_id, 'page', true, $code );
				$default_lang_post_id = wpml_object_id_filter( $post_id, 'page', true, $code_default );

				if ( ( $current_lang_post_id && in_array( $current_lang_post_id, $posts, true ) ) || ( $default_lang_post_id && in_array( $default_lang_post_id, $posts, true ) ) ) {
					unset( $active_lang_posts[ $k ] );
				}
			}
			$posts = array_merge( $posts, array_values( $active_lang_posts ) );
		}
	}

	$sitepress->switch_lang( $code );

	// you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
	$search_results = new \WP_Query(
		array(
			'post_type'           => 'page',
			's'                   => sanitize_text_field( $_GET['search'] ), // the search query
			'post_status'         => 'publish', // if you don't want drafts to be returned
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => 10, // how much to show at once
			'paged'               => absint( $_GET['page'] ),
			'suppress_filters'    => true, // ignore WPML default filters for languages
			'orderby'             => 'title',
			'order'               => 'asc',
			'post__in'            => $posts,
		)
	);

	$posts = $search_results->get_posts();

	if ( ! empty( $posts ) ) {
		foreach ( $posts as $post ) {
			// shorten the title a little
			$title = ( mb_strlen( $post->post_title ) > 50 ) ? mb_substr( $post->post_title, 0, 49 ) . '...' : $post->post_title;

			// translators: %1$s is a post title; %2$s is a post ID.
			$title    = sprintf( __( '%1$s (ID: %2$s)', 'jobboardwp' ), esc_html( $title ), $post->ID );
			$return[] = array( $post->ID, $title ); // array( Post ID, Post Title )
		}
	}

	$return['total_count'] = $search_results->found_posts;
	return $return;
	// phpcs:enable WordPress.Security.NonceVerification -- is verified in JB()->ajax()->settings()->get_pages_list()
}
add_filter( 'jb_admin_settings_get_pages_list', 'jb_admin_settings_get_pages_list_wpml', 10 );


/**
 * @param false $pre_result
 * @param int $page_id
 *
 * @return array
 */
function jb_admin_settings_pages_list_value_wpml( $pre_result, $page_id ) {
	$opt_value = JB()->options()->get( $page_id );

	if ( ! empty( $opt_value ) ) {
		global $sitepress;

		$page_id = wpml_object_id_filter( $opt_value, 'page', true, $sitepress->get_current_language() );
		if ( $page_id ) {
			$opt_value = $page_id;
		}

		$title = get_the_title( $opt_value );
		$title = ( mb_strlen( $title ) > 50 ) ? mb_substr( $title, 0, 49 ) . '...' : $title;
		// translators: %1$s is a post title; %2$s is a post ID.
		$title = sprintf( __( '%1$s (ID: %2$s)', 'jobboardwp' ), $title, $opt_value );

		$pre_result               = array( $opt_value => $title );
		$pre_result['page_value'] = $opt_value;
	}

	return $pre_result;
}
add_filter( 'jb_admin_settings_pages_list_value', 'jb_admin_settings_pages_list_value_wpml', 10, 2 );


/**
 * @param array $variables
 *
 * @return array
 */
function jb_common_js_variables_wpml( $variables ) {
	global $sitepress;

	$variables['locale'] = $sitepress->get_current_language();
	return $variables;
}
add_filter( 'jb_common_js_variables', 'jb_common_js_variables_wpml', 10, 1 );


/**
 * @param string $locale
 */
function jb_admin_init_locale_wpml( $locale ) {
	global $sitepress;
	$sitepress->switch_lang( $locale );
}
add_action( 'jb_admin_init_locale', 'jb_admin_init_locale_wpml', 10, 1 );
