<?php
/**
 *  Hide Plugins & Menus in the admin area from none White Label Admins.
 *
 * @package white-label
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Hide Plugins from the plugin page.
 *
 * @return void
 */
function white_label_hide_plugins() {

	// Exit If it's WL Admin.
	if ( white_label_is_wl_admin() ) {
		return;
	}

	$hidden_plugins = white_label_get_option( 'hidden_plugins', 'white_label_menus_plugins', false );

	// Exit if no settings.
	if ( empty( $hidden_plugins ) ) {
		return;
	}

	global $wp_list_table;

	$all_plugins = $wp_list_table->items;
	// Check each plugin name.
	foreach ( $all_plugins as $plugin_key => $val ) {
		if ( in_array( $plugin_key, $hidden_plugins, true ) ) {
			unset( $wp_list_table->items[ $plugin_key ] );
		}
	}
}

add_action( 'pre_current_active_plugins', 'white_label_hide_plugins', 999 );

/**
 * Hide plugin updates from the transient & updates page.
 *
 * @param array $value transient updates array.
 *
 * @return array $value update information.
 */
function white_label_hide_plugin_updates( $value ) {

	// Exit if it's WL Admin.
	if ( white_label_is_wl_admin() ) {
		return $value;
	}

	$hidden_plugins = white_label_get_option( 'hidden_plugins', 'white_label_menus_plugins', false );

	if ( ! empty( $hidden_plugins ) ) {
		// Hide each plugin update.
		foreach ( $hidden_plugins  as $plugin ) {
			if ( isset( $value->response[ $plugin ] ) ) {
				unset( $value->response[ $plugin ] ); // E.g 'akismet/akismet.php'.
			}
		}
	}

	return $value;
}
add_filter( 'site_transient_update_plugins', 'white_label_hide_plugin_updates' );

/**
 * Hide sidebar parent menus.
 *
 * @return void
 */
function white_label_remove_menu_page() {
	// Exit early if WL admin.
	if ( white_label_is_wl_admin() ) {
		return;
	}

	$hidden_menu_items = white_label_get_option( 'hidden_sidebar_menus', 'white_label_menus_plugins', false );

	if ( empty( $hidden_menu_items ) || empty( $hidden_menu_items['parents'] ) || ! is_array( $hidden_menu_items['parents'] ) ) {
		return;
	}

	// Get all menus.
	global $menu;

	if ( $menu && is_array( $menu ) ) {
		foreach ( $hidden_menu_items['parents'] as $item ) {
			// Support for removing VC Bakery parent menu.
			if ( $item === 'vc-welcome' || $item === 'vc-general' ) {
				remove_menu_page( 'vc-general' );
				remove_menu_page( 'vc-welcome' );
				continue;
			}
			remove_menu_page( $item );
		}
	}
}

add_action( 'admin_menu', 'white_label_remove_menu_page', 9999 );

/**
 * Remove Submenus from parent menus.
 *
 * @return void
 */
function white_label_remove_sub_menus() {

	// Exit early if WL admin.
	if ( white_label_is_wl_admin() ) {
		return;
	}

	$hidden_menu_items = white_label_get_option( 'hidden_sidebar_menus', 'white_label_menus_plugins', false );

	if ( empty( $hidden_menu_items['children'] ) ) {
		return;
	}

	foreach ( $hidden_menu_items['children'] as $parent_key => $childen_array ) {

		foreach ( $childen_array as $child ) {
			$submenu_list = explode( '_whitelabel_', $child );
			$main_menu = $submenu_list[0];
			$main_submenu = $submenu_list[1];
			white_label_remove_submenu_page( $main_menu, $main_submenu );
		}
	}
}

add_action( 'admin_menu', 'white_label_remove_sub_menus', 99999 );


/**
 * Remove an admin submenu.
 *
 * @global array $submenu
 *
 * @param string $parent_slug    The slug for the parent menu.
 * @param string $submenu_slug The slug of the submenu.
 * @return array|bool The removed submenu on success, false if not found.
 */
function white_label_remove_submenu_page( $parent_slug, $submenu_slug ) {
	global $submenu;

	if ( ! isset( $submenu[ $parent_slug ] ) || ! is_array( $submenu[ $parent_slug ] ) ) {
		return false;
	}

	foreach ( $submenu[ $parent_slug ] as $i => $item ) {

		$submenu_item = remove_query_arg( 'return', $item[2] );
		$submenu_item = sanitize_title( $submenu_item );

		if ( $submenu_slug === $submenu_item ) {
			unset( $submenu[ $parent_slug ][ $i ] );
		} else if ( $submenu_slug === $item[2] ) {
			// Fallback to none sanitized name.
			unset( $submenu[ $parent_slug ][ $i ] );
		}
	}
	return false;
}
