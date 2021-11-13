<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_tab    = empty( $_GET['tab'] ) ? '' : sanitize_key( urldecode( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
$current_subtab = empty( $_GET['section'] ) ? '' : sanitize_key( urldecode( $_GET['section'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
?>

<div id="jb-settings-wrap" class="wrap">
	<h2><?php esc_html_e( 'JobBoardWP - Settings', 'jobboardwp' ); ?></h2>

	<?php
	echo wp_kses( JB()->admin()->settings()->tabs_menu() . JB()->admin()->settings()->subtabs_menu( $current_tab ), JB()->get_allowed_html( 'wp-admin' ) );

	do_action( "jb_before_settings_{$current_tab}_{$current_subtab}_content" );

	if ( JB()->admin()->settings()->section_is_custom( $current_tab, $current_subtab ) ) {
		do_action( "jb_settings_page_{$current_tab}_{$current_subtab}_before_section" );

		$settings_section = JB()->admin()->settings()->display_section( $current_tab, $current_subtab );

		echo wp_kses( apply_filters( "jb_settings_section_{$current_tab}_{$current_subtab}_content", $settings_section ), JB()->get_allowed_html( 'wp-admin' ) );
	} else {
		?>

		<form method="post" action="" name="jb-settings-form" id="jb-settings-form">
			<input type="hidden" value="save" name="jb-settings-action" />

			<?php
			do_action( "jb_settings_page_{$current_tab}_{$current_subtab}_before_section" );

			$settings_section = JB()->admin()->settings()->display_section( $current_tab, $current_subtab );

			echo wp_kses( apply_filters( "jb_settings_section_{$current_tab}_{$current_subtab}_content", $settings_section ), JB()->get_allowed_html( 'wp-admin' ) );
			?>

			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'jobboardwp' ); ?>" />
				<?php wp_nonce_field( 'jb-settings-nonce' ); ?>
			</p>
		</form>

	<?php } ?>
</div>
