<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array
 */
function jb_wpml_get_languages_codes() {
	global $sitepress;

	return array(
		'default' => $sitepress->get_locale_from_language_code( $sitepress->get_default_language() ),
		'current' => $sitepress->get_locale_from_language_code( $sitepress->get_current_language() ),
	);
}

/**
 * @param $columns
 * @param $base_columns
 *
 * @return array
 */
function jb_admin_jobs_listtable_columns_wpml( $columns, $base_columns ) {
	if ( array_key_exists( 'icl_translations', $base_columns ) ) {
		$columns = JB()->array_insert_after( $columns, 'title', array( 'icl_translations' => $base_columns['icl_translations'] ) );
	}
	return $columns;
}
add_filter( 'jb_admin_jobs_listtable_columns', 'jb_admin_jobs_listtable_columns_wpml', 10, 2 );

/**
 * @param $classes
 *
 * @return string
 */
function jb_admin_body_class_wpml( $classes ) {
	global $pagenow;
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'jb-job' === sanitize_key( $_GET['post_type'] ) ) {
		$classes .= ' jb_icl_active ';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'jb_admin_body_class_wpml' );

/**
 * Get predefined page translation for current language
 *
 * @param int $page_id
 *
 * @return mixed
 */
function jb_get_predefined_page_id_wpml( $page_id ) {
	global $sitepress;

	return wpml_object_id_filter( $page_id, 'page', true, $sitepress->get_current_language() );
}
add_filter( 'jb_get_predefined_page_id', 'jb_get_predefined_page_id_wpml' );

/**
 * @param bool $condition
 * @param WP_Post $post
 * @param int $predefined_page_id
 *
 * @return bool
 */
function jb_is_predefined_page_wpml( $condition, $post, $predefined_page_id ) {
	global $sitepress;

	$current_language = $sitepress->get_current_language();

	if ( ( JB()->is_request( 'admin' ) || JB()->is_request( 'ajax' ) ) && 'all' === $current_language ) {
		$active_languages = $sitepress->get_active_languages();

		if ( count( $active_languages ) > 0 ) {
			foreach ( $active_languages as $language_data ) {
				$tr_post_id = wpml_object_id_filter( $post->ID, 'page', true, $language_data['code'] );
				if ( empty( $tr_post_id ) ) {
					continue;
				}

				if ( $tr_post_id === $predefined_page_id ) {
					$condition = true;
					break;
				}
			}
		}
	}

	return $condition;
}
add_filter( 'jb_is_predefined_page', 'jb_is_predefined_page_wpml', 10, 3 );

/**
 * @return array
 */
function jb_admin_settings_get_pages_list_wpml() {
	// phpcs:disable WordPress.Security.NonceVerification -- is verified in JB()->ajax()->settings()->get_pages_list()
	$return = array();

	$search_query = ! empty( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
	$paged        = ! empty( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;

	$current_lang_query = new WP_Query(
		array(
			'post_type'           => 'page',
			's'                   => $search_query, // the search query
			'post_status'         => 'publish', // if you don't want drafts to be returned
			'ignore_sticky_posts' => 1,
			'fields'              => 'ids',
			'posts_per_page'      => -1,
		)
	);

	global $sitepress;

	$code         = $sitepress->get_current_language();
	$code_default = $sitepress->get_default_language();

	$posts              = array();
	$current_lang_posts = $current_lang_query->get_posts();
	if ( ! empty( $current_lang_posts ) ) {
		$posts = array_merge( $posts, $current_lang_posts );
	}

	if ( $code !== $code_default ) {
		$sitepress->switch_lang( $code_default );

		$default_lang_query = new WP_Query(
			array(
				'post_type'           => 'page',
				's'                   => $search_query, // the search query
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

	$active_languages   = $sitepress->get_active_languages();
	$active_langs_posts = array();
	foreach ( $active_languages as $language_code ) {
		if ( $language_code['code'] === $code || $language_code['code'] === $code_default ) {
			continue;
		}

		$sitepress->switch_lang( $language_code['code'] );

		$active_lang_query = new WP_Query(
			array(
				'post_type'           => 'page',
				's'                   => $search_query, // the search query
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
			$active_langs_posts[] = array_values( $active_lang_posts );
		}
	}

	if ( ! empty( $active_langs_posts ) ) {
		$active_langs_posts = array_merge( ...$active_langs_posts );
		$posts              = array_merge( $posts, $active_langs_posts );
	}

	$sitepress->switch_lang( $code );

	// you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
	$search_results = new WP_Query(
		array(
			'post_type'           => 'page',
			's'                   => $search_query, // the search query
			'post_status'         => 'publish', // if you don't want drafts to be returned
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => 10, // how much to show at once
			'paged'               => $paged,
			'suppress_filters'    => true, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.SuppressFilters_suppress_filters -- ignore WPML default filters for languages
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
add_filter( 'jb_admin_settings_get_pages_list', 'jb_admin_settings_get_pages_list_wpml' );

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

		$current_language = $sitepress->get_current_language();
		if ( 'all' === $current_language && ( JB()->is_request( 'admin' ) || JB()->is_request( 'ajax' ) ) ) {
			$current_language = $sitepress->get_default_language();
		}

		$page_id = wpml_object_id_filter( $opt_value, 'page', true, $current_language );
		if ( $page_id ) {
			$opt_value = $page_id;
		} else {
			$page_id = wpml_object_id_filter( $opt_value, 'page', true, $sitepress->get_default_language() );
			if ( $page_id ) {
				$opt_value = $page_id;
			}
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
add_filter( 'jb_common_js_variables', 'jb_common_js_variables_wpml' );

/**
 * @param string $locale
 */
function jb_admin_init_locale_wpml( $locale ) {
	global $sitepress;
	$sitepress->switch_lang( $locale );
}
add_action( 'jb_admin_init_locale', 'jb_admin_init_locale_wpml' );

/**
 * @param $columns
 *
 * @return array
 */
function jb_add_email_templates_column_wpml( $columns ) {
	global $sitepress;

	$active_languages = $sitepress->get_active_languages();
	$current_language = $sitepress->get_current_language();
	unset( $active_languages[ $current_language ] );

	if ( count( $active_languages ) > 0 ) {
		$flags_column = '';
		foreach ( $active_languages as $language_data ) {
			$flags_column .= '<img src="' . esc_attr( $sitepress->get_flag_url( $language_data['code'] ) ) . '" width="18" height="12" alt="' . esc_attr( $language_data['display_name'] ) . '" title="' . esc_attr( $language_data['display_name'] ) . '" style="margin:2px" />';
		}

		$columns = JB()->array_insert_after( $columns, 'email', array( 'translations' => $flags_column ) );
	}

	return $columns;
}
add_filter( 'jb_email_templates_columns', 'jb_add_email_templates_column_wpml' );

/**
 * @param $content
 * @param $item
 * @param $column_name
 *
 * @return string
 */
function jb_emails_list_table_custom_column_content_wpml( $content, $item, $column_name ) {
	if ( 'translations' === $column_name ) {
		global $sitepress;

		$active_languages = $sitepress->get_active_languages();
		$current_language = $sitepress->get_current_language();
		unset( $active_languages[ $current_language ] );

		$html = '';
		foreach ( $active_languages as $language_data ) {
			$html .= jb_wpml_get_status_html( $item['key'], $language_data['code'] );
		}

		$content = $html;
	}

	return $content;
}
add_filter( 'jb_emails_list_table_custom_column_content', 'jb_emails_list_table_custom_column_content_wpml', 10, 3 );

/**
 * @param $template
 * @param $code
 *
 * @return string
 */
function jb_wpml_get_status_html( $template, $code ) {
	global $sitepress;

	$link = add_query_arg(
		array(
			'email' => $template,
			'lang'  => $code,
		)
	);

	$active_languages = $sitepress->get_active_languages();
	$translation_map  = array(
		'edit' => array(
			'icon' => 'edit_translation.png',
			// translators: %s is a language display name
			'text' => sprintf( __( 'Edit the %s translation', 'jobboardwp' ), $active_languages[ $code ]['display_name'] ),
		),
		'add'  => array(
			'icon' => 'add_translation.png',
			// translators: %s is a language display name
			'text' => sprintf( __( 'Add translation to %s', 'jobboardwp' ), $active_languages[ $code ]['display_name'] ),
		),
	);

	$default_lang = $sitepress->get_default_language();

	if ( $default_lang === $code ) {
		return jb_wpml_render_status_icon( $link, $translation_map['edit']['text'], $translation_map['edit']['icon'] );
	}

	$template_name = JB()->get_email_template( $template );

	$current_language = $sitepress->get_current_language();
	$sitepress->switch_lang( $code );

	$module        = JB()->get_email_template_module( $template );
	$template_path = JB()->template_path( $module );

	$template_locations = array(
		trailingslashit( $template_path ) . $template_name,
	);

	/** This filter is documented in includes/class-jb-functions.php */
	$template_locations = apply_filters( 'jb_pre_template_locations', $template_locations, $template_name, $module, $template_path );

	// build multisite blog_ids priority paths
	if ( is_multisite() ) {
		$blog_id = get_current_blog_id();

		$ms_template_locations = array_map(
			static function ( $item ) use ( $template_path, $blog_id ) {
				return str_replace( trailingslashit( $template_path ), trailingslashit( $template_path ) . $blog_id . '/', $item );
			},
			$template_locations
		);

		$template_locations = array_merge( $ms_template_locations, $template_locations );
	}

	/** This filter is documented in includes/class-jb-functions.php */
	$template_locations = apply_filters( 'jb_template_locations', $template_locations, $template_name, $module, $template_path );
	$template_locations = array_map( 'wp_normalize_path', $template_locations );

	foreach ( $template_locations as $k => $location ) {
		if ( false === strpos( $location, wp_normalize_path( DIRECTORY_SEPARATOR . $sitepress->get_locale_from_language_code( $code ) . DIRECTORY_SEPARATOR ) ) ) {
			unset( $template_locations[ $k ] );
		}
	}

	$sitepress->switch_lang( $current_language );

	/** This filter is documented in includes/class-jb-functions.php */
	$custom_path = apply_filters( 'jb_template_structure_custom_path', false, $template_name, $module );
	if ( false === $custom_path || ! is_dir( $custom_path ) ) {
		$template_exists = locate_template( $template_locations );
	} else {
		$template_exists = JB()->locate_template_custom_path( $template_locations, $custom_path );
	}

	// Get default template in cases:
	// 1. Conflict test constant is defined and TRUE
	// 2. There aren't any proper template in custom or theme directories
	$status = 'add';
	if ( ! empty( $template_exists ) ) {
		$status = 'edit';
	}

	return jb_wpml_render_status_icon( $link, $translation_map[ $status ]['text'], $translation_map[ $status ]['icon'] );
}

/**
 * @param $link
 * @param $text
 * @param $img
 *
 * @return string
 */
function jb_wpml_render_status_icon( $link, $text, $img ) {
	$icon_html  = '<a href="' . esc_url( $link ) . '" title="' . esc_attr( $text ) . '">';
	$icon_html .= '<img style="padding:1px;margin:2px;" border="0" src="' . esc_attr( ICL_PLUGIN_URL ) . '/res/img/' . esc_attr( $img ) . '" alt="' . esc_attr( $text ) . '" width="16" height="16" />';
	$icon_html .= '</a>';

	return $icon_html;
}

/**
 * @param array  $template_locations
 * @param string $template_name
 * @param string $module
 * @param string $template_path
 *
 * @return array
 */
function jb_pre_template_locations_wpml( $template_locations, $template_name, $module, $template_path ) {
	if ( 0 === strpos( $template_name, 'emails/' ) && JB()->common()->mail()->is_sending() ) {
		return $template_locations;
	}

	$language_codes = jb_wpml_get_languages_codes();

	if ( $language_codes['default'] !== $language_codes['current'] ) {
		$lang = $language_codes['current'];

		$ml_template_locations = array_map(
			static function ( $item ) use ( $template_path, $lang ) {
				return str_replace( trailingslashit( $template_path ), trailingslashit( $template_path ) . $lang . '/', $item );
			},
			$template_locations
		);

		$template_locations = array_merge( $ml_template_locations, $template_locations );
	}

	return $template_locations;
}
add_filter( 'jb_pre_template_locations_common_locale_integration', 'jb_pre_template_locations_wpml', 10, 4 );

/**
 * Adding endings to the "Subject Line" field, depending on the language.
 * @example job_approved_sub_de_DE
 *
 * @param array $section_fields
 * @param string $email_key
 *
 * @return array
 */
function jb_settings_change_subject_field_wpml( $section_fields, $email_key ) {
	$language_codes = jb_wpml_get_languages_codes();

	if ( $language_codes['default'] === $language_codes['current'] ) {
		return $section_fields;
	}

	$lang       = '_' . $language_codes['current'];
	$option_key = $email_key . '_sub' . $lang;
	$value      = JB()->options()->get( $option_key );

	$section_fields[2]['id']    = $option_key;
	$section_fields[2]['value'] = ! empty( $value ) ? $value : JB()->options()->get( $email_key . '_sub' );

	return $section_fields;
}
add_filter( 'jb_settings_email_section_fields', 'jb_settings_change_subject_field_wpml', 10, 2 );


/**
 * @param string $subject
 * @param string $template
 * @param string $email
 *
 * @return string
 */
function jb_change_email_subject_wpml( $subject, $template, $email ) {
	$language_codes = jb_wpml_get_languages_codes();

	$current_locale = $language_codes['current'];
	$user_obj       = get_user_by( 'email', $email );
	if ( false !== $user_obj ) {
		$current_locale = get_user_locale( $user_obj->ID );
	}

	if ( $language_codes['default'] === $current_locale ) {
		return $subject;
	}

	$lang  = '_' . $current_locale;
	$value = JB()->options()->get( $template . '_sub' . $lang );

	return ! empty( $value ) ? $value : $subject;
}
add_filter( 'jb_email_send_subject', 'jb_change_email_subject_wpml', 10, 3 );

/**
 * @param array $template_locations
 *
 * @return array
 */
function jb_change_email_templates_locations_wpml( $template_locations ) {
	global $sitepress;

	$code         = $sitepress->get_current_language();
	$code_default = $sitepress->get_default_language();

	if ( $code === $code_default ) {
		return $template_locations;
	}

	$locale = $sitepress->get_locale_from_language_code( $code );
	foreach ( $template_locations as $k => $location ) {
		if ( false === strpos( $location, wp_normalize_path( DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR ) ) ) {
			unset( $template_locations[ $k ] );
		}
	}

	return $template_locations;
}
add_filter( 'jb_save_email_templates_locations', 'jb_change_email_templates_locations_wpml', 10, 1 );


function jb_before_email_notification_sending_wpml( $email, $template, $args ) {
	if ( 'job_approved' === $template || 'job_expiration_reminder' === $template ) {
		global $sitepress;

		$current_language = $sitepress->get_current_language();

		$post_lang = $sitepress->get_language_for_element( $args['job_id'], 'post_jb-job' );
		$sitepress->switch_lang( $post_lang );

		$function = static function () {
			global $sitepress;
			$locale_lang_code = $sitepress->get_current_language();

			return $sitepress->get_locale( $locale_lang_code );
		};

		add_filter( 'locale', $function );

		add_action(
			'jb_after_email_notification_sending',
			static function ( $email, $template ) use ( $current_language, $function ) {
				if ( 'job_approved' === $template || 'job_expiration_reminder' === $template ) {
					global $sitepress;
					$sitepress->switch_lang( $current_language );
					remove_filter( 'locale', $function );
				}
			},
			10,
			2
		);
	}
}
add_action( 'jb_before_email_notification_sending', 'jb_before_email_notification_sending_wpml', 10, 3 );

function jb_check_for_reminder_expired_jobs_job_ids_wpml( $job_ids, $args ) {
	global $sitepress;

	$code = $sitepress->get_current_language();

	$job_translations = array();
	$active_languages = $sitepress->get_active_languages();
	foreach ( $active_languages as $language_code ) {
		if ( $language_code['code'] === $code ) {
			continue;
		}

		$sitepress->switch_lang( $language_code['code'] );

		$lang_job_ids = get_posts( $args );
		if ( ! empty( $lang_job_ids ) ) {
			$job_translations[] = $lang_job_ids;
		}
	}

	$job_translations = array_merge( ...$job_translations );
	if ( ! empty( $job_translations ) ) {
		$job_ids = array_merge( $job_ids, $job_translations );
	}

	$sitepress->switch_lang( $code );

	return $job_ids;
}
add_filter( 'jb_check_for_reminder_expired_jobs_job_ids', 'jb_check_for_reminder_expired_jobs_job_ids_wpml', 10, 2 );
