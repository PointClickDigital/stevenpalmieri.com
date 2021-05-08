<?php
/**
 *  Dashboard Changes.
 *
 * @package white-label
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Replace Default Welcome Panel with WL Panel.
 *
 * @return void
 */
function white_label_pro_enable_welcome_panel() {
	$wl_panel = white_label_get_option( 'admin_welcome_panel_content', 'white_label_dashboard', false );
	if ( ! empty( $wl_panel ) ) {
		// remove old panel.
		remove_action( 'welcome_panel', 'wp_welcome_panel' );
		// Add the New White Label Panel.
		add_action( 'welcome_panel', 'white_label_welcome_panel_content' );
	}
}

add_action( 'admin_init', 'white_label_pro_enable_welcome_panel', 90 );

/**
 * WL Welcome Panel Content.
 *
 * @return void
 */
function white_label_welcome_panel_content() {
	$wl_panel = white_label_get_option( 'admin_welcome_panel_content', 'white_label_dashboard', false );

	if ( ! empty( $wl_panel ) ) {
		echo wpautop( $wl_panel ); // phpcs:ignore
	}
}

/**
 * Remove Default Dashboard widgets
 *
 * @return void
 */
function white_label_remove_dashboard_metaboxes() {
	$wl_remove_meta = white_label_get_option( 'admin_remove_default_widgets', 'white_label_dashboard', false );

	if ( $wl_remove_meta === 'on' ) {
		remove_action( 'welcome_panel', 'wp_welcome_panel' );
		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' ); // Removes the 'incoming links' widget.
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' ); // Removes the 'plugins' widget.
		remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' ); // Removes the 'WordPress News' widget.
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' ); // Removes the secondary widget.
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' ); // Removes the 'Quick Draft' widget.
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' ); // Removes the 'Recent Drafts' widget.
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' ); // Removes the 'Activity' widget.
		remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' ); // Removes the 'At a Glance' widget.
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' ); // Removes the 'Activity' widget (since 3.8).
	}
}
add_action( 'admin_init', 'white_label_remove_dashboard_metaboxes', 50 );



/**
 * Add White Label widget to the dashboard.
 *
 * @return void
 */
function white_label_dashboard_widget_one() {

	// Add Custom Dashboard widget.
	$enable_widget = white_label_get_option( 'admin_enable_widget', 'white_label_dashboard', false );

	if ( $enable_widget !== 'on' ) {
		return;
	}

	$title = white_label_get_option( 'admin_widget_title', 'white_label_dashboard', '' );

	wp_add_dashboard_widget(
		'white_label_dashboard_widget_one',
		$title,
		'white_label_dashboard_widget_one_content'
	);

}

add_action( 'wp_dashboard_setup', 'white_label_dashboard_widget_one' );

/**
 * Widget Content.
 *
 * @return void
 */
function white_label_dashboard_widget_one_content() {

	$content = white_label_get_option( 'admin_widget_content', 'white_label_dashboard', false );

	if ( empty( $content ) ) {
		return;
	}

	echo wpautop( $content ); // phpcs:ignore
}
