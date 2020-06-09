<?php if ( ! defined( 'ABSPATH' ) ) exit;

$current_tab = empty( $_GET['tab'] ) ? '' : urldecode( $_GET['tab'] );
$current_subtab = empty( $_GET['section'] ) ? '' : urldecode( $_GET['section'] ); ?>

<div id="jb-settings-wrap" class="wrap">
	<h2><?php printf( __( '%s - Settings', 'jobboardwp' ), jb_plugin_name ) ?></h2>

	<?php echo JB()->admin()->settings()->tabs_menu() . JB()->admin()->settings()->subtabs_menu( $current_tab );

	do_action( "jb_before_settings_{$current_tab}_{$current_subtab}_content" );

	if ( JB()->admin()->settings()->section_is_custom( $current_tab, $current_subtab ) ) {

		do_action( "jb_settings_page_{$current_tab}_{$current_subtab}_before_section" );

		$settings_section = JB()->admin()->settings()->display_section( $current_tab, $current_subtab );

		echo apply_filters( "jb_settings_section_{$current_tab}_{$current_subtab}_content", $settings_section );

	} else { ?>

		<form method="post" action="" name="jb-settings-form" id="jb-settings-form">
			<input type="hidden" value="save" name="jb-settings-action" />

			<?php do_action( "jb_settings_page_{$current_tab}_{$current_subtab}_before_section" );

			$settings_section = JB()->admin()->settings()->display_section( $current_tab, $current_subtab );

			echo apply_filters( "jb_settings_section_{$current_tab}_{$current_subtab}_content", $settings_section ); ?>

			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'jobboardwp' ) ?>" />
				<input type="hidden" name="__jbnonce" value="<?php echo wp_create_nonce( 'jb-settings-nonce' ); ?>" />
			</p>
		</form>

	<?php } ?>
</div>