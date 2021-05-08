<?php
/**
 * Convert settings for 2.0.
 *
 * @package white-label
 */

/**
 * Mirgrate old White Label Settings over to the new framework.
 *
 * @return void
 */
function white_label_migrate_settings() {

	if ( ! is_admin() ) {
		return;
	}

	$old_settings = get_option( 'white_label_section_start', false );

	// No old settings, stop here.
	if ( $old_settings === false ) {
		return;
	}

	// Free Settings.
	$section_start            = get_option( 'white_label_section_start', false ); // enable button.
	$company_name             = get_option( 'white_label_company_name', false );
	$company_url              = get_option( 'white_label_company_url', false );
	$custom_logo              = get_option( 'white_label_custom_logo', false );
	$login_background_image   = get_option( 'white_label_login_background_image', false );
	$login_background         = get_option( 'white_label_login_background', false );
	$admin_area               = get_option( 'white_label_admin_area', false );
	$admin_bar_logo           = get_option( 'white_label_admin_bar_logo', false );
	$admin_howdy              = get_option( 'white_label_admin_howdy', false );
	$admin_footer             = get_option( 'white_label_admin_footer', false );
	$welcome_panel            = get_option( 'white_label_welcome_panel', false ); // EX PRO feature.
	$dashboard_widget_switch  = get_option( 'white_label_dashboard_widget_switch', false );
	$dashboard_widget_title   = get_option( 'white_label_dashboard_widget_title', false );
	$dashboard_widget_content = get_option( 'white_label_dashboard_widget_content', false );
	$custom_dashboard_switch  = get_option( 'white_label_custom_dashboard_switch', false );
	$custom_dashboard         = get_option( 'white_label_custom_dashboard', false );
	$live_chat                = get_option( 'white_label_live_chat', false );

	// Pro Settings.
	$super_admins              = get_option( 'white_label_super_admins', false );
	$hide_plugins              = get_option( 'white_label_hide_plugins', false );
	$admin_menu                = get_option( 'white_label_admin_menu', false );
	$pro_email_name            = get_option( 'white_label_pro_email_name', false );
	$pro_email_address         = get_option( 'white_label_pro_email_address', false );
	$update_nag                = get_option( 'white_label_update_nag', false );
	$pro_remove_dashboard_meta = get_option( 'white_label_pro_remove_dashboard_meta', false );

	// white_label_general .
	$new_general = array(
		'enable_white_label' => white_label_convert_setting( $section_start ),
		'wl_administrators'  => white_label_convert_setting( $super_admins ), // PRO.
	);

	update_option( 'white_label_general', $new_general, false );

	// white_label_login .
	$new_general = array(
		'business_name'          => white_label_convert_setting( $company_name ),
		'business_url'           => white_label_convert_setting( $company_url ),
		'login_logo_file'        => white_label_convert_setting( $custom_logo ),
		'login_background_file'  => white_label_convert_setting( $login_background_image ),
		'login_background_color' => white_label_convert_setting( $login_background ),
	);

	update_option( 'white_label_login', $new_general, false );

	// white_label_dashboard .
	$new_general = array(
		'admin_welcome_panel_content'    => white_label_convert_setting( $welcome_panel ),
		'admin_enable_widget'            => white_label_convert_setting( $dashboard_widget_switch ),
		'admin_widget_title'             => white_label_convert_setting( $dashboard_widget_title ),
		'admin_widget_content'           => white_label_convert_setting( $dashboard_widget_content ),
		'admin_enable_custom_dashboard'  => white_label_convert_setting( $custom_dashboard_switch ),
		'admin_custom_dashboard_content' => white_label_convert_setting( $custom_dashboard ),
		'admin_remove_default_widgets'   => white_label_convert_setting( $pro_remove_dashboard_meta ),
	);

	update_option( 'white_label_dashboard', $new_general, false );

	// white_label_menus_plugins .
	$new_general = array(
		'hidden_plugins'       => white_label_convert_setting( $hide_plugins ),
		'hidden_sidebar_menus' => array(
			'parents'  => white_label_convert_setting( $admin_menu ),
			'children' => array(),
		),
	);

	update_option( 'white_label_menus_plugins', $new_general, false );

	// white_label_visual_tweaks .
	$new_general = array(
		'admin_remove_wp_logo'   => white_label_convert_setting( $admin_area ),
		'admin_replace_wp_logo'  => white_label_convert_setting( $admin_bar_logo ),
		'admin_howdy_replacment' => white_label_convert_setting( $admin_howdy ),
		'admin_footer_credit'    => white_label_convert_setting( $admin_footer ),
		'admin_javascript'       => white_label_convert_setting( $live_chat ),
	);

	update_option( 'white_label_visual_tweaks', $new_general, false );

	// white_label_misc .
	$new_general = array(
		'email_from_name'    => white_label_convert_setting( $pro_email_name ),
		'email_from_address' => white_label_convert_setting( $pro_email_address ),
		'update_nags'        => white_label_convert_setting( $update_nag ),
	);

	update_option( 'white_label_misc', $new_general, false );

	// white_label_license .
	$license_key = get_option( 'white_label_license_key' );

	if ( $license_key ) {

		$new_general = array(
			'license' => array(
				'key' => $license_key,
			),
		);

		update_option( 'white_label_license', $new_general, false );

	}

	// Clean up legacy free.
	delete_option( 'white_label_section_start', false ); // enable button.
	delete_option( 'white_label_company_name', false );
	delete_option( 'white_label_company_url', false );
	delete_option( 'white_label_custom_logo', false );
	delete_option( 'white_label_login_background_image', false );
	delete_option( 'white_label_login_background', false );
	delete_option( 'white_label_admin_area', false );
	delete_option( 'white_label_admin_bar_logo', false );
	delete_option( 'white_label_admin_howdy', false );
	delete_option( 'white_label_admin_footer', false );
	delete_option( 'white_label_welcome_panel', false ); // EX PRO feature.
	delete_option( 'white_label_dashboard_widget_switch', false );
	delete_option( 'white_label_dashboard_widget_title', false );
	delete_option( 'white_label_dashboard_widget_content', false );
	delete_option( 'white_label_custom_dashboard_switch', false );
	delete_option( 'white_label_custom_dashboard', false );
	delete_option( 'white_label_live_chat', false );

	// Clean up legacy pro.
	delete_option( 'white_label_super_admins', false );
	delete_option( 'white_label_hide_plugins', false );
	delete_option( 'white_label_admin_menu', false );
	delete_option( 'white_label_pro_email_name', false );
	delete_option( 'white_label_pro_email_address', false );
	delete_option( 'white_label_update_nag', false );
	delete_option( 'white_label_pro_remove_dashboard_meta', false );

}

add_action( 'wp_loaded', 'white_label_migrate_settings', 1 );

/**
 * Convert setting value.
 *
 * @param mixed $value of a setting.
 * @return mixed settings value;
 */
function white_label_convert_setting( $value ) {

	if ( empty( $value ) || $value === 'none' ) {
		return false;
	}
	if ( is_array( $value ) ) {
		if ( $value[0] === 'on' || $value[0] === 'remove-wp-logo-admin-bar' ) {
			return 'on';
		}
	}

	if ( is_array( $value ) ) {

		$new_array = array();

		foreach ( $value as $key => $val ) {

			if ( $val === 'hide_all' ) {
				$val = 'wl_admins';
			}
			if ( $val === 'hide_some' ) {
				$val = 'wl_admins';
			}

			$new_array[ $val ] = $val;
		}

		$value = $new_array;

	}

	return $value;
}
