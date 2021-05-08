<?php

/**
 * Get a specific option from White Label settings.
 *
 * @param [type]  $option id of option in settings tab.
 * @param boolean $section id of setting tab.
 * @param string  $default default if nothing is found.
 * @return mixed
 */
function white_label_get_option( $option, $section, $default = '' ) {

	$options = wp_cache_get( $section, 'white_label_options' );

	if ( $options === false ) {
		$options = get_option( $section, 'none' );

		if ( $options === 'none' ) {
			return $default;
		}

		wp_cache_add( $section, $options, 'white_label_options' );
	}

	if ( isset( $options[ $option ] ) ) {
		return $options[ $option ];
	}
	return $default;
}

/**
 * Admin Login Preview.
 *
 * @return void
 */
function white_label_login_preview() {

	$login_logo_file        = white_label_get_option( 'login_logo_file', 'white_label_login', false );
	$login_background_file  = white_label_get_option( 'login_background_file', 'white_label_login', false );
	$login_color_background = white_label_get_option( 'login_background_color', 'white_label_login', '#f1f1f1' );

	// Only once a logo is set.
	if ( ! $login_logo_file ) {
		// use default WP logo.
		$login_logo_file = admin_url( '/images/wordpress-logo.svg' );
	}

	$preview_text = __( 'Preview Login Page', 'white-label' );

	$style = 'background-image: url(' . $login_background_file . ');
	background-color:' . $login_color_background . ';
    background-position: center;
	background-size: cover;
	height: 200px;
	border: 4px solid white;
	display: flex;
	align-items: center;
	justify-content: center;
	';

	$logo = '<img style="
	max-width: 180px;
	max-height: 180px;
    display: block;
	margin: 0 auto;"
	src="' . $login_logo_file . '"/>';

	$preview_link = get_site_url( null, '/wp-login.php' );

	$output = '<div style="background: white;
    margin-left: 25px;
    margin-bottom: 20px;" class="white_label-preview-box">

	<div style="%s" class="white-label-preview">%s</div>
	<div style="padding: 14px;">';

	$output .= '
	<a href="' . $preview_link . '" target="_blank" style="text-align:center;display:block; margin: 0 auto; width:80%%;" class="button-secondary">%s <span style="padding:3px;" class="dashicons dashicons-migrate"></span></a>
	</div>

	</div>';

	// Echo the preview on our admin page.
	echo sprintf( $output, $style, $logo, $preview_text ); // phpcs:ignore
}

add_action( 'white_label_above_settings_sidebars', 'white_label_login_preview' );
/**
 * Display the import and export forms in our settings import/export tab.
 *
 * @return void
 */
function white_label_import_export_html() {

	$import_export = new white_label_Import_Export_Options();
	$import_export->display_html();
}

add_action( 'white_label_settings_tab_white_label_import_export', 'white_label_import_export_html' );

/**
 * Create an array of all administrator.
 *
 * @return array admin_id => name + details.
 */
function white_label_get_regular_admins() {

	$cached_admins = wp_cache_get( 'admins', 'white_label_settings' );

	if ( $cached_admins !== false ) {
		return $cached_admins;
	}

	$admins = array();

	$args = array(
		'role'    => 'Administrator',
		'orderby' => 'user_nicename',
		'order'   => 'ASC',
	);

	$blogusers = get_users( $args );

	$you_text = __( 'You', 'white-label' );

	if ( $blogusers && is_array( $blogusers ) ) {
		// Array of WP_User objects.
		foreach ( $blogusers as $user ) {

			$current_user_indicator = $user->ID === get_current_user_id() ? '<span style="background-color:red;padding: 1px 7px;color: white;font-weight: 500;border-radius: 7px;font-size: 11px;">' . $you_text . '</span>' : '';

			$admins [ $user->ID ] = '<b>' . $user->user_nicename . "</b><i> ($user->user_email</i>) " . $current_user_indicator;
		}
	}

	// Just enough to prevent duplicate queries.
	wp_cache_set( 'admins', $admins, 'white_label_settings', 60 );

	return $admins;
}

/**
 * Create an array of all plugins.
 *
 * @return array plugin_base => Nice name
 */
function white_label_get_plugins() {

	$all_plugins = get_plugins();

	$plugins_formatted = array();

	if ( $all_plugins && is_array( $all_plugins ) ) {
		foreach ( $all_plugins as  $key => $value ) {
			$plugins_formatted[ $key ] = $value['Name'];
		}
	}

	return $plugins_formatted;
}

/**
 * Create an array of all sidebar menu itmes.
 *
 * @return array slug => name.
 */
function white_label_get_sidebar_menus() {

	global $menu;

	$selectable_items = array();

	if ( ! empty( $menu ) && is_array( $menu ) ) {

		foreach ( $menu as $menu_entry ) {

			$menu_name = ! empty( $menu_entry[0] ) ? $menu_entry[0] : $menu_entry[2];

			$parent_slug = $menu_entry[2];

			$selectable_items[ $parent_slug ] = array(
				'name'     => esc_html( strip_tags( $menu_name ) ),
				'submenus' => white_label_get_submenus( $parent_slug ),
			);

		}
	}

	return $selectable_items;
}

/**
 * Undocumented function
 *
 * @param string $parent_slug Parent menu slug.
 * @return array
 */
function white_label_get_submenus( $parent_slug ) {

	global $submenu;

	$submenu_array = array();

	if ( ! empty( $submenu[ $parent_slug ] ) && is_array( $submenu[ $parent_slug ] ) ) {

		foreach ( $submenu[ $parent_slug ] as $key => $menu ) {
			if ( ! empty( $menu ) && is_array( $menu ) ) {

				$submenu_item = remove_query_arg( 'return', $menu[2] );
				$submenu_key = sanitize_title( $submenu_item );

				$slug = $parent_slug . '_whitelabel_' . $submenu_key;
				$name = $menu[0];

				// Add the submenu to the array.
				$submenu_array[ $slug ] = esc_html( strip_tags( $name ) );
			}
		}
	}

	return $submenu_array ? $submenu_array : false;
}
