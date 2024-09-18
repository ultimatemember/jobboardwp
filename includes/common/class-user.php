<?php
namespace jb\common;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'jb\common\User' ) ) {

	/**
	 * Class User
	 *
	 * @package jb\common
	 */
	class User {

		/**
		 * Get company data by the user
		 *
		 * @param int|null $user_id
		 * @param string|null $field
		 *
		 * @return string|array
		 *
		 * @since 1.0
		 */
		public function get_company_data( $user_id = null, $field = null ) {
			$company_data = array(
				'name'      => '',
				'website'   => '',
				'tagline'   => '',
				'twitter'   => '',
				'facebook'  => '',
				'instagram' => '',
				'logo'      => '',
			);

			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			if ( ! $user_id ) {
				return $company_data;
			}

			if ( ! empty( $field ) && in_array( $field, array_keys( $company_data ), true ) ) {
				return get_user_meta( $user_id, "jb_company_{$field}", true );
			}

			$company_data['name']      = get_user_meta( $user_id, 'jb_company_name', true );
			$company_data['website']   = get_user_meta( $user_id, 'jb_company_website', true );
			$company_data['tagline']   = get_user_meta( $user_id, 'jb_company_tagline', true );
			$company_data['twitter']   = get_user_meta( $user_id, 'jb_company_twitter', true );
			$company_data['facebook']  = get_user_meta( $user_id, 'jb_company_facebook', true );
			$company_data['instagram'] = get_user_meta( $user_id, 'jb_company_instagram', true );
			$company_data['logo']      = get_user_meta( $user_id, 'jb_company_logo', true );

			/**
			 * Filters the user's company data.
			 *
			 * @since 1.0
			 * @hook jb-user-company-data
			 *
			 * @param {array} $company_data User's company data.
			 * @param {int}   $user_id      User ID passed into the function.
			 *
			 * @return {array} Maybe modified user's company data.
			 */
			$company_data = apply_filters( 'jb-user-company-data', $company_data, $user_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
			return $company_data;
		}

		/**
		 * Set company data
		 *
		 * @param array $data
		 * @param int|null $user_id
		 *
		 * @since 1.0
		 */
		public function set_company_data( $data, $user_id = null ) {
			$company_data = wp_parse_args(
				$data,
				array(
					'name'    => '',
					'website' => '',
					'tagline' => '',
					'logo'    => '',
				)
			);

			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			if ( ! $user_id ) {
				return;
			}

			foreach ( $company_data as $key => $data_row ) {
				update_user_meta( $user_id, "jb_company_{$key}", $data_row );
			}
		}
	}
}
