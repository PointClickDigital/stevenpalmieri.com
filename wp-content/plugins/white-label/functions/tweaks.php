<?php
/**
 *  Visual Tweaks.
 *
 * @package white-label
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Remove the WP Logo in Admin Bar.
 *
 * @param mixed $wp_admin_bar admin bar menus.
 *
 * @return void
 */
function white_label_remove_wp_logo( $wp_admin_bar ) {

	$admin_remove_wp_logo = white_label_get_option( 'admin_remove_wp_logo', 'white_label_visual_tweaks', false );
	$wl_admin_logo = white_label_get_option( 'admin_replace_wp_logo', 'white_label_visual_tweaks', false );

	// Remove WordPress Logo in Admin Bar.
	if ( $admin_remove_wp_logo === 'on' || $wl_admin_logo ) {

		if ( ! $wl_admin_logo ) {
			$wp_admin_bar->remove_node( 'wp-logo' );
		}

		$wp_admin_bar->remove_menu( 'about' );
		$wp_admin_bar->remove_menu( 'wporg' );
		$wp_admin_bar->remove_menu( 'documentation' );
		$wp_admin_bar->remove_menu( 'support-forums' );
		$wp_admin_bar->remove_menu( 'feedback' );
	}

	// $hidden_items = white_label_get_option( 'hidden_admin_bar_items', 'white_label_menus_plugins', false );
	// if ( empty( $hidden_items ) ) {
	// return;
	// }
	// foreach ( $hidden_items as $value ) {
	// $wp_admin_bar->remove_node( $value );
	// }
}

add_action( 'admin_bar_menu', 'white_label_remove_wp_logo', 999 );


/**
 * Replace Howdy usename with any text.
 *
 * @param mixed $wp_admin_bar admin bar menus.
 *
 * @return void
 */
function white_label_change_howdy( $wp_admin_bar ) {

	$white_label_admin_howdy = white_label_get_option( 'admin_howdy_replacment', 'white_label_visual_tweaks', false );

	if ( ! empty( $white_label_admin_howdy ) ) {

		$wl_get_howdy = $wp_admin_bar->get_node( 'my-account' );

		$wl_replacement = preg_replace( '/^[^,]*,\s*/', $white_label_admin_howdy . ' ', $wl_get_howdy->title );
		$wp_admin_bar->add_node(
			array(
				'id'    => 'my-account',
				'title' => $wl_replacement,
			)
		);
	}
}
add_filter( 'admin_bar_menu', 'white_label_change_howdy', 50 );



/**
 * Replace admin footer text
 *
 * @param string $default default footer text.
 *
 * @return string
 */
function white_label_admin_footer_credit( $default ) {

	$new_footer = white_label_get_option( 'admin_footer_credit', 'white_label_visual_tweaks', false );

	if ( ! empty( $new_footer ) ) {
		return $new_footer;
	}
	return $default;
}

add_filter( 'admin_footer_text', 'white_label_admin_footer_credit', 100 );

/**
 * Add JS Scripts to the admin area.
 *
 * @return void
 */
function white_label_live_chat() {

	$white_label_live_chat = white_label_get_option( 'admin_javascript', 'white_label_visual_tweaks', false );

	if ( ! empty( $white_label_live_chat ) ) {
		echo $white_label_live_chat;
	}
}

add_action( 'admin_print_footer_scripts', 'white_label_live_chat' );

/**
 * Replace admin bar logo with a custom one.
 *
 * @return void
 */
function white_label_admin_bar_logo() {

	$wl_admin_logo = white_label_get_option( 'admin_replace_wp_logo', 'white_label_visual_tweaks', false );

	if ( $wl_admin_logo ) {
		echo '
<style type="text/css">
#wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
background-image: url(' . esc_url( $wl_admin_logo ) . ') !important;
background-position: center;
color:rgba(0, 0, 0, 0);
background-size:cover;
}
#wpadminbar #wp-admin-bar-wp-logo.hover > .ab-item .ab-icon {
background-position: center;
background-size:cover;
}
</style>
';
	}
}
add_action( 'wp_before_admin_bar_render', 'white_label_admin_bar_logo', 90 );
