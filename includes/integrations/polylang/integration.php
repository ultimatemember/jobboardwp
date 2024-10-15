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
function jb_polylang_get_languages_codes() {
	return array(
		'default' => pll_default_language( 'locale' ),
		'current' => pll_current_language( 'locale' ),
	);
}

/**
 * @param int $page_id
 *
 * @return mixed
 */
function jb_get_predefined_page_id_polylang( $page_id ) {
	$post = pll_get_post( $page_id );
	if ( $post ) {
		$page_id = $post;
	}

	return $page_id;
}
add_filter( 'jb_get_predefined_page_id', 'jb_get_predefined_page_id_polylang' );

/**
 * @param bool $condition
 * @param WP_Post $post
 * @param int $predefined_page_id
 *
 * @return bool
 */
function jb_is_predefined_page_polylang( $condition, $post, $predefined_page_id ) {
	global $polylang;

	if ( ( JB()->is_request( 'admin' ) || JB()->is_request( 'ajax' ) ) && false === pll_current_language( 'locale' ) ) {

		if ( count( pll_languages_list() ) > 0 ) {
			foreach ( pll_languages_list() as $language_code ) {
				if ( pll_current_language() === $language_code ) {
					continue;
				}

				$language = $polylang->model->get_language( $language_code );
				if ( empty( $language ) ) {
					continue;
				}

				$tr_post_id = $polylang->model->post->get( $post->ID, $language );
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
add_filter( 'jb_is_predefined_page', 'jb_is_predefined_page_polylang', 10, 3 );

/**
 * @return array
 */
function jb_admin_settings_get_pages_list_polylang() {
	global $polylang;

	// fix when "Show all languages" in wp-admin is set
	if ( false === pll_current_language( 'locale' ) ) {
		$locale        = pll_default_language( 'locale' );
		PLL()->curlang = $polylang->model->get_language( $locale );
	}

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

	$posts              = array();
	$current_lang_posts = $current_lang_query->get_posts();
	if ( ! empty( $current_lang_posts ) ) {
		$posts = array_merge( $posts, $current_lang_posts );
	}

	if ( pll_current_language() !== pll_default_language() ) {
		$default_lang_query = new WP_Query(
			array(
				'post_type'           => 'page',
				's'                   => $search_query, // the search query
				'post_status'         => 'publish', // if you don't want drafts to be returned
				'ignore_sticky_posts' => 1,
				'fields'              => 'ids',
				'lang'                => pll_default_language(),
				'posts_per_page'      => -1,
			)
		);

		$default_lang_posts = $default_lang_query->get_posts();
		if ( ! empty( $default_lang_posts ) ) {
			foreach ( $default_lang_posts as $k => $post_id ) {
				$lang_post_id = pll_get_post( $post_id, pll_current_language() );
				if ( in_array( $lang_post_id, $posts, true ) ) {
					unset( $default_lang_posts[ $k ] );
				}
			}
			$posts = array_merge( $posts, array_values( $default_lang_posts ) );
		}
	}

	$active_languages   = pll_languages_list();
	$active_langs_posts = array();
	foreach ( $active_languages as $language_code ) {
		if ( pll_current_language() === $language_code || pll_default_language() === $language_code ) {
			continue;
		}

		$active_lang_query = new WP_Query(
			array(
				'post_type'           => 'page',
				's'                   => $search_query, // the search query
				'post_status'         => 'publish', // if you don't want drafts to be returned
				'ignore_sticky_posts' => 1,
				'fields'              => 'ids',
				'lang'                => $language_code,
				'posts_per_page'      => -1,
			)
		);

		$active_lang_posts = $active_lang_query->get_posts();
		if ( ! empty( $active_lang_posts ) ) {
			foreach ( $active_lang_posts as $k => $post_id ) {
				$current_lang_post_id = pll_get_post( $post_id, pll_current_language() );
				$default_lang_post_id = pll_get_post( $post_id, pll_default_language() );
				if ( in_array( $current_lang_post_id, $posts, true ) || in_array( $default_lang_post_id, $posts, true ) ) {
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

	// you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
	$search_results = new WP_Query(
		array(
			'post_type'           => 'page',
			's'                   => $search_query, // the search query
			'post_status'         => 'publish', // if you don't want drafts to be returned
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => 10, // how much to show at once
			'paged'               => $paged,
			'orderby'             => 'title',
			'order'               => 'asc',
			'lang'                => '', // set empty language for getting posts of all languages
			'post__in'            => $posts,
		)
	);

	$posts = $search_results->get_posts();
	if ( ! empty( $posts ) ) {
		foreach ( $posts as $post ) {
			// shorten the title a little
			$title = ( mb_strlen( $post->post_title ) > 50 ) ? mb_substr( $post->post_title, 0, 49 ) . '...' : $post->post_title;

			// translators: %1$s is a post title; %2$s is a post ID.
			$title    = sprintf( __( '%1$s (ID: %2$s)', 'jobboardwp' ), $title, $post->ID );
			$return[] = array( $post->ID, esc_html( $title ) ); // array( Post ID, Post Title )
		}
	}

	$return['total_count'] = $search_results->found_posts;
	return $return;
	// phpcs:enable WordPress.Security.NonceVerification -- is verified in JB()->ajax()->settings()->get_pages_list()
}
add_filter( 'jb_admin_settings_get_pages_list', 'jb_admin_settings_get_pages_list_polylang' );

/**
 * @param false|array $pre_result
 * @param int         $page_id
 *
 * @return array
 */
function jb_admin_settings_pages_list_value_polylang( $pre_result, $page_id ) {
	$opt_value = JB()->options()->get( $page_id );

	if ( ! empty( $opt_value ) ) {
		$lang = '';
		if ( false === pll_current_language( 'locale' ) && ( JB()->is_request( 'admin' ) || JB()->is_request( 'ajax' ) ) ) {
			$lang = pll_default_language();
		}

		$post = pll_get_post( $opt_value, $lang );
		if ( $post ) {
			$opt_value = $post;
		} else {
			$post = pll_get_post( $opt_value, pll_default_language() );
			if ( $post ) {
				$opt_value = $post;
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
add_filter( 'jb_common_js_variables', 'jb_common_js_variables_polylang' );

/**
 * @param string $locale
 */
function jb_admin_init_locale_polylang( $locale ) {
	global $polylang;
	PLL()->curlang = $polylang->model->get_language( $locale );
}
add_action( 'jb_admin_init_locale', 'jb_admin_init_locale_polylang' );

/**
 * @param $columns
 *
 * @return array
 */
function jb_add_email_templates_column_polylang( $columns ) {
	global $polylang;

	if ( count( pll_languages_list() ) > 0 ) {
		$flags_column = '';
		foreach ( pll_languages_list() as $language_code ) {
			if ( pll_current_language() === $language_code ) {
				continue;
			}
			$language      = $polylang->model->get_language( $language_code );
			$flags_column .= '<span class="um-flag" style="margin:2px">' . $language->flag . '</span>';
		}

		$columns = JB()->array_insert_after( $columns, 'email', array( 'translations' => $flags_column ) );
	}

	return $columns;
}
add_filter( 'jb_email_templates_columns', 'jb_add_email_templates_column_polylang' );

function jb_emails_list_table_custom_column_content_polylang( $content, $item, $column_name ) {
	if ( 'translations' === $column_name ) {
		$html = '';

		foreach ( pll_languages_list() as $language_code ) {
			if ( pll_current_language() === $language_code ) {
				continue;
			}
			$html .= jb_polylang_get_status_html( $item['key'], $language_code );
		}

		$content = $html;
	}

	return $content;
}
add_filter( 'jb_emails_list_table_custom_column_content', 'jb_emails_list_table_custom_column_content_polylang', 10, 3 );

/**
 * @param $template
 * @param $code
 *
 * @return string
 */
function jb_polylang_get_status_html( $template, $code ) {
	global $polylang;

	$link = add_query_arg(
		array(
			'email' => $template,
			'lang'  => $code,
		)
	);

	$language     = $polylang->model->get_language( $code );
	$default_lang = pll_default_language();

	if ( $default_lang === $code ) {
		// translators: %s is a language display name
		$hint = sprintf( __( 'Edit the translation in %s', 'jobboardwp' ), $language->name );

		return sprintf(
			'<a href="%1$s" title="%2$s" class="pll_icon_edit"><span class="screen-reader-text">%3$s</span></a>',
			esc_url( $link ),
			esc_html( $hint ),
			esc_html( $hint )
		);
	}

	$template_name = JB()->get_email_template( $template );

	$current_language = pll_current_language();
	$current_language = $polylang->model->get_language( $current_language );

	PLL()->curlang = $language;

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
			function ( $item ) use ( $template_path, $blog_id ) {
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
		if ( false === strpos( $location, wp_normalize_path( DIRECTORY_SEPARATOR . $language->locale . DIRECTORY_SEPARATOR ) ) ) {
			unset( $template_locations[ $k ] );
		}
	}

	PLL()->curlang = $current_language;

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
	if ( ! empty( $template_exists ) ) {
		// translators: %s is a language display name
		$hint      = sprintf( __( 'Edit the translation in %s', 'jobboardwp' ), $language->name );
		$icon_html = sprintf(
			'<a href="%1$s" title="%2$s" class="pll_icon_edit"><span class="screen-reader-text">%3$s</span></a>',
			esc_url( $link ),
			esc_html( $hint ),
			esc_html( $hint )
		);
	} else {
		// translators: %s is a language display name
		$hint      = sprintf( __( 'Add a translation in %s', 'jobboardwp' ), $language->name );
		$icon_html = sprintf(
			'<a href="%1$s" title="%2$s" class="pll_icon_add"><span class="screen-reader-text">%3$s</span></a>',
			esc_url( $link ),
			esc_attr( $hint ),
			esc_html( $hint )
		);
	}

	return $icon_html;
}

function jb_pre_template_locations_polylang( $template_locations, $template_name, $module, $template_path ) {
	if ( 0 === strpos( $template_name, 'emails/' ) && JB()->common()->mail()->is_sending() ) {
		return $template_locations;
	}

	$language_codes = jb_polylang_get_languages_codes();

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
add_filter( 'jb_pre_template_locations_common_locale_integration', 'jb_pre_template_locations_polylang', 10, 4 );

/**
 * Adding endings to the "Subject Line" field, depending on the language.
 * @exaple job_approved_sub_de_DE
 *
 * @param array $section_fields
 * @param string $email_key
 *
 * @return array
 */
function jb_settings_change_subject_field_polylang( $section_fields, $email_key ) {
	$language_codes = jb_polylang_get_languages_codes();

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
add_filter( 'jb_settings_email_section_fields', 'jb_settings_change_subject_field_polylang', 10, 2 );

/**
 * @param string $subject
 * @param string $template
 * @param string $email
 *
 * @return string
 */
function jb_change_email_subject_polylang( $subject, $template, $email ) {
	$language_codes = jb_polylang_get_languages_codes();

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
add_filter( 'jb_email_send_subject', 'jb_change_email_subject_polylang', 10, 3 );

/**
 * @param array $template_locations
 *
 * @return array
 */
function jb_change_email_templates_locations_polylang( $template_locations ) {
	$code         = pll_current_language( 'locale' );
	$code_default = pll_default_language( 'locale' );

	if ( $code === $code_default ) {
		return $template_locations;
	}

	foreach ( $template_locations as $k => $location ) {
		if ( false === strpos( $location, wp_normalize_path( DIRECTORY_SEPARATOR . $code . DIRECTORY_SEPARATOR ) ) ) {
			unset( $template_locations[ $k ] );
		}
	}

	return $template_locations;
}
add_filter( 'jb_save_email_templates_locations', 'jb_change_email_templates_locations_polylang' );

function jb_before_email_notification_sending_polylang( $email, $template, $args ) {
	if ( 'job_approved' === $template || 'job_expiration_reminder' === $template ) {
		global $polylang;

		$current_language = pll_current_language();
		$current_language = $polylang->model->get_language( $current_language );

		$post_lang     = $polylang->model->get_language( pll_get_post_language( $args['job_id'] ) );
		PLL()->curlang = $post_lang;

		$function = static function () {
			return PLL()->curlang->locale;
		};

		add_filter( 'locale', $function );

		add_action(
			'jb_after_email_notification_sending',
			static function ( $email, $template ) use ( $current_language, $function ) {
				if ( 'job_approved' === $template || 'job_expiration_reminder' === $template ) {
					PLL()->curlang = $current_language;
					remove_filter( 'locale', $function );
				}
			},
			10,
			2
		);
	}
}
add_action( 'jb_before_email_notification_sending', 'jb_before_email_notification_sending_polylang', 10, 3 );

function jb_check_for_reminder_expired_jobs_job_ids_polylang( $job_ids, $args ) {
	$active_languages = pll_languages_list();

	$job_translations = array();
	foreach ( $active_languages as $language_code ) {
		if ( pll_current_language() === $language_code ) {
			continue;
		}
		$args['lang'] = $language_code;

		$lang_job_ids = get_posts( $args );
		if ( ! empty( $lang_job_ids ) ) {
			$job_translations[] = $lang_job_ids;
		}
	}
	$job_translations = array_merge( ...$job_translations );

	if ( ! empty( $job_translations ) ) {
		$job_ids = array_merge( $job_ids, $job_translations );
	}

	return $job_ids;
}
add_filter( 'jb_check_for_reminder_expired_jobs_job_ids', 'jb_check_for_reminder_expired_jobs_job_ids_polylang', 10, 2 );
