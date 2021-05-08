<?php
/**
 * Admin Settings.
 *
 * @package White Label
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * white_label_Admin_Settings
 *
 * Create and setup the admin options page.
 */
class white_label_Admin_Settings {
	/**
	 * Settings API.
	 *
	 * @var $settings_api white_label_Settings_Api class.
	 */
	private $settings_api;

	/**
	 * Constants.
	 *
	 * @var $conatants contains plugin setup.
	 */
	private $constants;

	/**
	 * Construct.
	 *
	 * @param object $constants contains plugin setup.
	 */
	public function __construct( $constants ) {
		// Load only on admin side.
		if ( ! is_admin() ) {
			return;
		}

			// set up the plugin config.
			$this->constants = $constants;

			// Add menus.
			add_action( 'admin_menu', array( $this, 'menu' ) );
			// Create our settings.
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			// Quick settings link.
			add_action( 'plugin_action_links_' . plugin_basename( $this->constants['file'] ), array( $this, 'action_link' ) );

	}

	/**
	 * Add plugin action links.
	 *
	 * Add a link to the settings page on the plugins.php page.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $links List of existing plugin action links.
	 * @return array         List of modified plugin action links.
	 */
	public function action_link( $links ) {
		$links = array_merge(
			array(
				'<a href="' . esc_url( admin_url( '/options-general.php?page=white-label' ) ) . '">' . __( 'Settings', 'white-label' ) . '</a>',
			),
			array(
				'<a href="' . esc_url( 'https://whitewp.com/docs/' ) . '" target="_blank">' . __( 'Documentation', 'white-label' ) . '</a>',
			),
			$links
		);
		return $links;
	}

	public function sections() {

		$sections = array(
			'white_label_general'       => array(
				'id'                    => 'white_label_general',
				'title'                 => __( 'General', 'white_label' ),
				'requires_verification' => false,
			),
			'white_label_login'         => array(
				'id'                    => 'white_label_login',
				'title'                 => __( 'Login', 'white_label' ),
				'requires_verification' => false,
			),
			'white_label_dashboard'     => array(
				'id'                    => 'white_label_dashboard',
				'title'                 => __( 'Dashboard', 'white-label' ),
				'requires_verification' => false,
			),
			'white_label_menus_plugins' => array(
				'id'                    => 'white_label_menus_plugins',
				'title'                 => __( 'Menus & Plugins', 'white-label' ),
				'requires_verification' => false,
			),
			'white_label_visual_tweaks' => array(
				'id'                    => 'white_label_visual_tweaks',
				'title'                 => __( 'Visual Tweaks', 'white-label' ),
				'requires_verification' => false,
			),
			'white_label_misc'          => array(
				'id'                    => 'white_label_misc',
				'title'                 => __( 'Misc', 'white-label' ),
				'requires_verification' => false,
			),
			'white_label_import_export' => array(
				'id'                    => 'white_label_import_export',
				'title'                 => __( 'Import & Export', 'white-label' ),
				'requires_verification' => false,
				'custom_tab'            => true,

			),
		);

		return $sections;
	}


	/**
	 * Create our settings fields, sections and sidebars.
	 *
	 * @param mixed $get_part either sections, fields or sidebars.
	 */
	public function settings( $get_part = false ) {
		// Create tabbed sections.
		$sections = $this->sections();
		// Create setting fields.
		$fields = array();
		// fields for above sections.
		$fields['white_label_general'] = array(
			array(
				'name'  => 'general_section',
				'label' => __( 'General', 'white-label' ),
				'desc'  => __( 'Quickly turn on and off all White Label settings.', 'white-label' ),
				'type'  => 'subheading',
				'class' => 'subheading',
			),
			array(
				'name'  => 'enable_white_label',
				'label' => __( 'Enable White Label', 'white-label' ),
				'desc'  => __( 'Enable White Labelling on entire site.', 'white-label' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'wl_admin_sub_section',
				'label' => __( 'White Label Administators', 'white-label' ),
				'desc'  => __( 'A White Label Administrator will bypass other rules set inside the White Label plugin. You will be able to hide sensitive menus, plugins, updates and more from all normal administrators.', 'white-label' ),
				'type'  => 'subheading',
				'class' => 'subheading',
			),
			array(
				'name'    => 'wl_administrators',
				'label'   => __( 'WL Administrators', 'white-label' ),
				'desc'    => __( 'Select which administrators should also be White Label Administrators.', 'white-label' ),
				'type'    => 'multicheck',
				'options' => white_label_get_regular_admins(),
			),

		);

		$fields['white_label_login'] = array(
			array(
				'name'  => 'login_section',
				'label' => __( 'Login Design', 'white-label' ),
				'desc'  => __( 'Customize and design the WordPress login page to suit your branding.', 'white-label' ),
				'type'  => 'subheading',
				'class' => 'subheading',
			),
			array(
				'name'        => 'business_name',
				'label'       => __( 'Business Name', 'white-label' ),
				'desc'        => __( 'Your business name will be included inside the link attribute on the login logo.', 'white-label' ),
				'placeholder' => __( 'Business Name', 'white-label' ),
				'type'        => 'text',
			),
			array(
				'name'        => 'business_url',
				'label'       => __( 'Business URL', 'white-label' ),
				'desc'        => __( 'The login logo will link to your business URL.', 'white-label' ),
				'placeholder' => __( 'https://whitewp.com/', 'white-label' ),
				'type'        => 'url',
			),
			array(
				'name'    => 'login_logo_file',
				'label'   => __( 'Login Logo image', 'white-label' ),
				'desc'    => __( 'Replaces the WordPress logo on the login screen.', 'white-label' ),
				'type'    => 'file',
				'default' => '',
				'options' => array(
					'button_label' => __( 'Choose Logo Image', 'white-label' ),
				),
			),
			array(
				'name'    => 'login_background_file',
				'label'   => __( 'Page Background Image', 'white-label' ),
				'desc'    => __( 'Choose a background image to use on the login screen.', 'white-label' ),
				'type'    => 'file',
				'default' => '',
				'options' => array(
					'button_label' => __( 'Choose Background Image', 'white-label' ),
				),
			),
			array(
				'name'    => 'login_background_color',
				'label'   => __( 'Page Background Color', 'white-label' ),
				'desc'    => __( 'Background color of the login screen if you do not have an image selected.', 'white-label' ),
				'type'    => 'color',
				'default' => '#f1f1f1',
			),
			array(
				'name'    => 'login_box_background_color',
				'label'   => __( 'Login Box Background', 'white-label' ),
				'desc'    => __( 'Background color of the login details box.', 'white-label' ),
				'type'    => 'color',
				'default' => '#fff',
			),
			array(
				'name'    => 'login_box_text_color',
				'label'   => __( 'Login Box Text Color', 'white-label' ),
				'desc'    => __( 'Change the color of the text inside the login box.', 'white-label' ),
				'type'    => 'color',
				'default' => '#444',
			),
			array(
				'name'    => 'login_text_color',
				'label'   => __( 'Link Color', 'white-label' ),
				'desc'    => __( 'Change the color of the links outside the login box.', 'white-label' ),
				'type'    => 'color',
				'default' => '#555d66',
			),
			array(
				'name'    => 'login_button_background_color',
				'label'   => __( 'Button Color', 'white-label' ),
				'desc'    => __( 'Color of the login button.', 'white-label' ),
				'type'    => 'color',
				'default' => '#007cba',
			),
			array(
				'name'    => 'login_button_font_color',
				'label'   => __( 'Button Font Color', 'white-label' ),
				'desc'    => __( 'Color of the login button text.', 'white-label' ),
				'type'    => 'color',
				'default' => '#fff',
			),
			array(
				'name'        => 'login_custom_css',
				'label'       => __( 'Custom CSS', 'white-label' ),
				'desc'        => __( 'Any CSS in this box will apply to the login screen.', 'white-label' ),
				'placeholder' => '',
				'type'        => 'textarea',
			),
			array(
				'name'  => 'login_page_template_sub',
				'label' => __( 'Login Template', 'white-label' ),
				'desc'  => __( 'Switch it up with a fresh new login page look and feel. The login template changes the layout of the login screen.', 'white-label' ),
				'type'  => 'subheading',
				'class' => 'subheading',
			),
			array(
				'name'    => 'login_page_template',
				'label'   => __( 'Login Template', 'white-label' ),
				'desc'    => __( 'Select a login screen template to use. Your above customizations will apply to any template.', 'white-label' ),
				'type'    => 'radio',
				'class'   => 'setting-with-image',
				'options' => array(
					''      => '<span class="dashicons dashicons-align-center"></span>' . __( 'Default', 'white-label' ),
					'left'  => '<span class="dashicons dashicons-align-left"></span>' . __( 'Left Login', 'white-label' ),
					'right' => '<span class="dashicons dashicons-align-right"></span>' . __( 'Right Login', 'white-label' ),
				),
			),
		);

		$fields['white_label_menus_plugins'] = array(
			array(
				'name'  => 'hidden_plugins_section',
				'label' => __( 'Hidden Plugins', 'white-label' ),
				'desc'  => __( 'Hide the selected plugins from the plugin page. Only White Label Administrators will be able to see the selected plugins & their updates in the plugins list.', 'white-label' ),
				'type'  => 'subheading',
				'class' => 'subheading',
			),
			array(
				'name'    => 'hidden_plugins',
				'label'   => __( 'Hidden Plugins', 'white-label' ),
				'desc'    => __( 'Selected plugins will be hidden from all non-White Label Administrators.', 'white-label' ),
				'type'    => 'multicheck',
				'options' => white_label_get_plugins(),
			),
			array(
				'name'  => 'hidden_sidebar_menu_sub',
				'label' => __( 'Hidden Sidebar Menus', 'white-label' ),
				'desc'  => __( 'Hide the selected sidebar menu items from non-White Label Administrators.', 'white-label' ),
				'type'  => 'subheading',
				'class' => 'subheading',
			),
			array(
				'name'    => 'hidden_sidebar_menus',
				'label'   => __( 'Hidden Sidebar Menus', 'white-label' ),
				'desc'    => __( 'Selected sidebar menus will be hidden from all non-White Label Administrators.', 'white-label' ),
				'type'    => 'nested_multicheck',
				'options' => white_label_get_sidebar_menus(),
			),
		);

		$fields['white_label_dashboard'] = array(

			array(
				'name'  => 'dashboard_section',
				'label' => __( 'Dashboard', 'white-label' ),
				'desc'  => __( 'Create your own experence in the WordPress Dashboard. You can provide users with quick links or information.', 'white-label' ),
				'type'  => 'subheading',
				'class' => 'subheading',
			),
			array(
				'name'  => 'admin_welcome_panel_content',
				'label' => __( 'Custom Welcome Panel ', 'white-label' ),
				'desc'  => __( 'Replaces the default Welcome Panel content on the dashboard.', 'white-label' ),
				'type'  => 'wysiwyg',
			),
			array(
				'name'  => 'admin_remove_default_widgets',
				'label' => __( 'Remove Dashboard Widgets', 'white-label' ),
				'desc'  => __( 'Remove all default dashboard widgets.', 'white-label' ),
				'type'  => 'checkbox',
			),

			array(
				'name'  => 'widget_section',
				'label' => __( 'Widget', 'white-label' ),
				'desc'  => __( 'Add a custom widget to the admin dashboard. Provide users with quick links or information.', 'white-label' ),
				'type'  => 'subheading',
				'class' => 'subheading',
			),
			array(
				'name'  => 'admin_enable_widget',
				'label' => __( 'Enable Widget', 'white-label' ),
				'desc'  => __( 'The custom widget will show up for all users in the admin dashboard.', 'white-label' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'admin_widget_title',
				'label' => __( 'Widget Title', 'white-label' ),
				'desc'  => __( 'The heading of your new dashboard widget.', 'white-label' ),
				'type'  => 'text',
			),
			array(
				'name'  => 'admin_widget_content',
				'label' => __( 'Widget Content', 'white-label' ),
				'desc'  => __( 'The content of your new dashboard widget.', 'white-label' ),
				'type'  => 'wysiwyg',
			),
			array(
				'name'  => 'custom_dashboard_section',
				'label' => __( 'Custom Dashboard', 'white-label' ),
				'desc'  => __( 'If you do not wish to have any widgets on the dashboard, then you can replace it with your own dashboard content.', 'white-label' ),
				'type'  => 'subheading',
				'class' => 'subheading',
			),
			array(
				'name'  => 'admin_enable_custom_dashboard',
				'label' => __( 'Enable Custom Dashboard', 'white-label' ),
				'desc'  => __( 'The custom dashboard will replace the default dashboard and all widgets.', 'white-label' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'admin_custom_dashboard_content',
				'label' => __( 'Dashboard Content', 'white-label' ),
				'desc'  => __( 'The content of your new custom dashboard.', 'white-label' ),
				'type'  => 'wysiwyg',
			),
		);

		$fields['white_label_visual_tweaks'] = array(
			array(
				'name'  => 'admin_tweaks',
				'label' => __( 'Admin Area', 'white-label' ),
				'desc'  => __( 'Customize the administrator side of WordPress.', 'white-label' ),
				'type'  => 'subheading',
				'class' => 'subheading',
			),
			array(
				'name'        => 'admin_howdy_replacment',
				'label'       => __( 'Replace Howdy in Admin Bar', 'white-label' ),
				'desc'        => __( 'For example, welcome may be more professional.', 'white-label' ),
				'placeholder' => 'Howdy',
				'type'        => 'text',
			),
			array(
				'name'        => 'admin_remove_wp_logo',
				'label'       => __( 'Remove WordPress Logo in Admin bar', 'white-label' ),
				'desc'        => __( 'Remove the tiny WordPress logo in the admin bar.', 'white-label' ),
				'placeholder' => __( 'https://whitewp.com/', 'white-label' ),
				'type'        => 'checkbox',
			),
			array(
				'name'        => 'admin_replace_wp_logo',
				'label'       => __( 'Replace WordPress Logo in Admin bar', 'white-label' ),
				'desc'        => __( 'Replace the tiny WordPress logo in the admin bar with your own.', 'white-label' ),
				'placeholder' => __( 'https://whitewp.com/', 'white-label' ),
				'type'        => 'file',
				'options'     => array(
					'button_label' => __( 'Choose mini logo', 'white-label' ),
				),
			),
			array(
				'name'  => 'admin_footer_credit',
				'label' => __( 'Admin Footer Credit', 'white-label' ),
				'desc'  => __( 'Replace the admin footer credit with your own.', 'white-label' ),
				'type'  => 'wysiwyg',
			),
			array(
				'name'  => 'admin_javascript_section',
				'label' => __( 'Admin Scripts', 'white-label' ),
				'desc'  => __( 'Run Javascript in the admin area. Great for adding a live chat for your clients to contact you.', 'white-label' ),
				'type'  => 'subheading',
				'class' => 'subheading',
			),
			array(
				'name'        => 'admin_javascript',
				'label'       => __( 'Admin Scripts', 'white-label' ),
				'desc'        => __( 'Any scripts here will only run on the administrator side of WordPress.', 'white-label' ),
				'placeholder' => '<script>...</script>',
				'type'        => 'textarea',
			),

		);

			// Create sidebar boxes.
			$sidebars = array(
				'feature_request' => array(
					'id'      => 'feature_request',
					'title'   => __( 'Feature Request', 'white-label' ),
					'content' => __( 'Got a great idea or are you missing something essential for your business? Let us know <a href="https://whitewp.com/support/" target="_blank">whitewp.com/support/</a> ', 'white-label' ),
				),
				'support'         => array(
					'id'      => 'support',
					'title'   => __( 'Documentation', 'white-label' ),
					'content' => __( 'Our <a href="https://whitewp.com/docs/" target="_blank">documentation</a> has detailed information on the features and white labelling. <br /> You can also say <em>hello</em> to us via <a href="https://whitewp.com/support/" target="_blank">support</a>.', 'white-label' ),
				),
			);

			$settings = array(
				'sections' => $sections,
				'fields'   => $fields,
				'sidebars' => $sidebars,
			);

			$settings = apply_filters( 'white_label_admin_settings', $settings );

			if ( $get_part ) {
				return $settings[ $get_part ];
			}

			return $settings;
	}

	/**
	 * Set the admin settings page.
	 */
	public function register_settings() {

		global $pagenow;

		// Make sure we are on a settings page. We can't check for specific at admin_init.
		if ( $pagenow === 'options-general.php' || $pagenow === 'options.php' ) {

			$this->settings_api = new white_label_Settings_Api( $this->constants );
			// Set the admin page.
			$this->settings_api->set_sections( $this->settings( 'sections' ) );
			$this->settings_api->set_fields( $this->settings( 'fields' ) );
			$this->settings_api->set_sidebar( $this->settings( 'sidebars' ) );
			// initialize settings.
			$this->settings_api->admin_init();

		}
	}

	/**
	 * Display the plugin page
	 */
	public function plugin_page() {

		$cb_image = $this->svg_logo_full();

		echo '<div class="wrap white-label-admin">';
		echo '<div class="conblock-settings-header">';
		echo '<div style="max-width: 45px;
		display: inline-block;
		margin-top: 15px;
		position: absolute;
		margin-left: 8px;">' . $cb_image . '</div>'; // phpcs:ignore
		echo '<h2 style="display: inline-block;
		position: relative;
		left: 52px;
		margin-bottom:30px;
		margin-top: 15px;
		visibility: hidden;
		font-size: 20px;">White Label</h2>';
		echo '</div>';

		$this->settings_api->show_navigation();
		$this->settings_api->show_sidebar();
		$this->settings_api->show_forms();
		echo '</div>';
	}

	/**
	 * Register a custom menu page.
	 */
	public function menu() {

		if ( ! white_label_is_wl_admin() ) {
			return;
		}

		$parent      = 'options-general.php';
		$plugin_name = __( 'White Label', 'white-label' );
		$permissions = 'manage_options';
		$slug        = 'white-label';
		$callback    = array( $this, 'plugin_page' );
		$priority    = 100;

		add_submenu_page(
			$parent,
			$plugin_name,
			$plugin_name,
			$permissions,
			$slug,
			$callback,
			$priority
		);
	}

	/**
	 * SVG logo tag.
	 */
	private function svg_logo_tag() {

		return '<svg width="30" height="30" viewBox="0 0 70 70" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path fill-rule="evenodd" clip-rule="evenodd" d="M68.2916 37.194L35.2531 4.20301C34.0848 3.03192 32.5118 2.04089 30.5432 1.22354C28.569 0.406903 26.7691 0 25.1351 0H5.91497C4.31147 0 2.927 0.58696 1.75592 1.75521C0.584125 2.927 0 4.31147 0 5.91427V25.1351C0 26.7684 0.406903 28.5682 1.22425 30.5404C2.04089 32.5118 3.03476 34.0678 4.20371 35.2091L37.2415 68.2916C38.38 69.4293 39.7673 70 41.3977 70C43.0012 70 44.4027 69.4293 45.6043 68.2916L68.2916 45.5568C69.4293 44.4162 70 43.0317 70 41.4006C70 39.7971 69.4293 38.3963 68.2916 37.194ZM18.9685 18.9678C17.8108 20.1225 16.4179 20.6989 14.7839 20.6989C13.1534 20.6989 11.7605 20.1225 10.6029 18.9678C9.44808 17.8108 8.87246 16.4179 8.87246 14.7867C8.87246 13.1527 9.44808 11.7598 10.6029 10.6057C11.7605 9.44808 13.1534 8.87175 14.7839 8.87175C16.4179 8.87175 17.8108 9.44808 18.9685 10.6057C20.1232 11.7598 20.6989 13.1527 20.6989 14.7867C20.6989 16.4179 20.1232 17.8108 18.9685 18.9678Z" fill="#0052CC"/>
		</svg>
';
	}

	/**
	 * SVG logo tag.
	 */
	private function svg_logo_full() {
			return '<svg width="180" viewBox="0 0 558 88" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M85.7635 46.7098L44.2723 5.27831C42.8052 3.80761 40.8297 2.56304 38.3575 1.53658C35.8781 0.511005 33.6177 0 31.5657 0L7.42828 0C5.41452 0 3.67586 0.73713 2.20516 2.20427C0.733569 3.67586 0 5.41452 0 7.42738L0 31.5657C0 33.6169 0.511006 35.8772 1.53747 38.3539C2.56304 40.8297 3.81117 42.7838 5.2792 44.2171L46.7695 85.7635C48.1992 87.1923 49.9414 87.909 51.989 87.909C54.0028 87.909 55.7628 87.1923 57.2718 85.7635L85.7635 57.2121C87.1923 55.7797 87.909 54.0411 87.909 51.9926C87.909 49.9788 87.1923 48.2197 85.7635 46.7098ZM23.8214 23.8205C22.3676 25.2707 20.6183 25.9945 18.5662 25.9945C16.5187 25.9945 14.7693 25.2707 13.3155 23.8205C11.8653 22.3676 11.1424 20.6183 11.1424 18.5698C11.1424 16.5178 11.8653 14.7684 13.3155 13.3191C14.7693 11.8653 16.5187 11.1415 18.5662 11.1415C20.6183 11.1415 22.3676 11.8653 23.8214 13.3191C25.2716 14.7684 25.9945 16.5178 25.9945 18.5698C25.9945 20.6183 25.2716 22.3676 23.8214 23.8205Z" fill="#0052CC"/>
			<path d="M119.781 66.9385C119.544 66.4157 119.306 65.9167 119.068 65.4414C118.831 64.9661 118.593 64.5384 118.355 64.1582C117.785 63.0176 117.286 61.9958 116.858 61.0928C116.431 60.1898 116.027 59.1442 115.646 57.9561C115.789 57.1481 116.027 55.7936 116.359 53.8926C116.74 51.9915 117.167 49.9242 117.643 47.6904C118.118 45.4092 118.617 43.1042 119.14 40.7754C119.71 38.4466 120.28 36.4505 120.851 34.7871C121.373 33.3138 121.944 31.7454 122.562 30.082C123.227 28.4186 123.94 26.8978 124.7 25.5195C125.508 24.0938 126.364 22.8818 127.267 21.8838C128.17 20.8857 129.144 20.2917 130.189 20.1016C130.475 20.4818 130.784 20.7907 131.116 21.0283C131.449 21.2184 131.758 21.361 132.043 21.4561C132.851 22.5967 133.635 23.571 134.396 24.3789C135.156 25.1393 136.059 25.8997 137.104 26.6602C136.819 28.0859 136.392 29.6781 135.821 31.4365C135.299 33.1475 134.728 34.8822 134.11 36.6406C133.54 38.3991 132.97 40.11 132.399 41.7734C131.829 43.3893 131.378 44.7913 131.045 45.9795C130.76 46.93 130.475 48.0231 130.189 49.2588C129.952 50.3044 129.69 51.5638 129.405 53.0371C129.168 54.5104 128.93 56.1263 128.692 57.8848C129.358 57.6947 130.094 57.3145 130.902 56.7441C131.758 56.1738 132.542 55.5798 133.255 54.9619C134.11 54.249 134.942 53.4886 135.75 52.6807C136.843 51.5876 137.77 50.5895 138.53 49.6865C139.291 48.7835 139.98 47.8568 140.598 46.9062C141.263 45.9082 141.928 44.8389 142.594 43.6982C143.307 42.5101 144.138 41.1081 145.089 39.4922C145.944 38.1139 146.824 36.6406 147.727 35.0723C148.677 33.5039 149.628 32.0068 150.578 30.5811C151.529 29.1553 152.455 27.8483 153.358 26.6602C154.261 25.4245 155.093 24.474 155.854 23.8086C156.376 24.3314 156.97 24.8779 157.636 25.4482C158.349 26.0186 159.109 26.4225 159.917 26.6602C160.25 27.1829 160.559 27.6582 160.844 28.0859C161.176 28.4661 161.485 28.8464 161.771 29.2266C162.151 29.7493 162.483 30.2246 162.769 30.6523C163.101 31.0801 163.386 31.5553 163.624 32.0781C163.576 32.4583 163.481 32.9574 163.339 33.5752C163.244 34.193 163.125 34.8822 162.982 35.6426C162.84 36.3555 162.674 37.0921 162.483 37.8525C162.341 38.613 162.198 39.3496 162.056 40.0625C161.58 42.2012 161.224 43.8171 160.986 44.9102C160.749 46.0033 160.582 46.8587 160.487 47.4766C160.392 48.0944 160.321 48.6172 160.273 49.0449C160.273 49.4727 160.273 50.0667 160.273 50.8271C160.273 51.3975 160.321 51.9678 160.416 52.5381C160.511 53.0609 160.606 53.6074 160.701 54.1777C160.844 54.7005 161.081 55.1995 161.414 55.6748L164.836 53.0371C165.786 52.3242 166.689 51.3262 167.545 50.043C168.448 48.7122 169.303 47.2152 170.111 45.5518C170.967 43.8408 171.799 42.0111 172.606 40.0625C173.414 38.0664 174.222 36.0228 175.03 33.9316C175.601 32.4108 176.171 30.9137 176.741 29.4404C177.359 27.9196 177.977 26.4938 178.595 25.1631C179.213 23.7848 179.807 22.5492 180.377 21.4561C180.995 20.3154 181.589 19.3649 182.159 18.6045C183.442 19.4124 184.535 20.2204 185.438 21.0283C186.389 21.8363 187.197 22.7393 187.862 23.7373C188.575 24.6878 189.122 25.8047 189.502 27.0879C189.93 28.3236 190.239 29.7969 190.429 31.5078C189.383 33.5039 188.409 35.5475 187.506 37.6387C186.603 39.7298 185.7 41.8447 184.797 43.9834C183.656 46.7399 182.468 49.4964 181.232 52.2529C180.044 54.9619 178.714 57.5521 177.24 60.0234C175.767 62.4948 174.104 64.776 172.25 66.8672C170.444 68.9108 168.353 70.6455 165.977 72.0713C165.311 71.9287 164.741 71.8574 164.266 71.8574C163.743 71.8574 163.291 71.9287 162.911 72.0713C162.578 72.2139 162.27 72.404 161.984 72.6416C160.036 71.7386 158.467 70.4079 157.279 68.6494C156.091 66.8434 154.927 64.8473 153.786 62.6611C153.644 62.3284 153.477 62.0195 153.287 61.7344C153.145 61.4017 152.978 61.069 152.788 60.7363L151.576 58.5264L150.863 57.1719L149.865 58.0986L148.368 59.4531L147.513 60.3086C146.039 61.6868 144.542 63.0413 143.021 64.3721C141.501 65.6553 139.909 66.8434 138.245 67.9365C136.582 68.9821 134.799 69.8376 132.898 70.5029C131.045 71.1683 129.049 71.5247 126.91 71.5723C126.53 71.3822 126.174 71.2158 125.841 71.0732C125.556 70.9307 125.271 70.7881 124.985 70.6455C123.892 70.1227 122.989 69.6237 122.276 69.1484C121.563 68.6732 120.732 67.9365 119.781 66.9385Z" fill="#0052CC"/>
			<path d="M223.293 65.085C223.388 66.3206 223.245 67.3424 222.865 68.1504C222.485 68.9583 221.962 69.6475 221.297 70.2178C220.632 70.7881 219.871 71.2396 219.016 71.5723C218.16 71.9049 217.328 72.2139 216.521 72.499C216.14 72.2614 215.76 72.0238 215.38 71.7861C215.047 71.501 214.643 71.2871 214.168 71.1445C213.55 70.0039 212.956 69.0059 212.386 68.1504C211.815 67.2474 211.554 66.2256 211.602 65.085C211.602 65.0374 211.625 64.776 211.673 64.3008C211.768 63.8255 211.863 63.3027 211.958 62.7324C212.101 62.1621 212.196 61.6393 212.243 61.1641C212.338 60.6413 212.386 60.3561 212.386 60.3086C211.293 60.4036 210.152 60.6413 208.964 61.0215C207.776 61.4017 206.611 61.8532 205.471 62.376C204.378 62.8988 203.332 63.4216 202.334 63.9443C201.383 64.4671 200.599 64.9186 199.981 65.2988C199.981 65.4414 199.886 65.7979 199.696 66.3682C199.506 66.8909 199.269 67.4613 198.983 68.0791C198.698 68.6494 198.389 69.1722 198.057 69.6475C197.724 70.1227 197.391 70.3604 197.059 70.3604C197.106 70.5029 197.177 70.6455 197.272 70.7881C197.368 70.9307 197.368 71.1208 197.272 71.3584C196.607 71.5485 196.013 71.7861 195.49 72.0713C194.967 72.404 194.421 72.6654 193.851 72.8555C193.28 73.0931 192.662 73.2119 191.997 73.2119C191.332 73.2119 190.548 72.9743 189.645 72.499C189.645 72.2139 189.526 71.9762 189.288 71.7861C189.05 71.5485 188.789 71.3584 188.504 71.2158C188.266 71.0257 188.052 70.8831 187.862 70.7881C187.672 70.6455 187.577 70.5267 187.577 70.4316C187.34 70.4792 187.244 70.3128 187.292 69.9326C187.34 69.5524 187.53 69.4574 187.862 69.6475C187.34 68.9821 187.007 68.4118 186.864 67.9365C186.769 67.4137 186.508 66.7721 186.08 66.0117C186.175 65.9167 186.27 65.7503 186.365 65.5127C186.413 65.2751 186.46 65.0612 186.508 64.8711C186.555 64.681 186.627 64.5384 186.722 64.4434C186.817 64.3483 186.936 64.3721 187.078 64.5146C186.983 64.0869 186.983 63.7305 187.078 63.4453C187.173 63.1602 187.268 62.8988 187.363 62.6611C187.458 62.4235 187.53 62.1859 187.577 61.9482C187.625 61.7106 187.553 61.4492 187.363 61.1641C188.124 60.1185 188.623 59.0016 188.86 57.8135C189.098 56.5778 189.241 55.4609 189.288 54.4629C189.621 54.1302 189.906 53.6549 190.144 53.0371C190.429 52.4193 190.643 51.8014 190.785 51.1836C190.928 50.5182 190.975 49.9004 190.928 49.3301C190.928 48.7122 190.856 48.2132 190.714 47.833C190.761 47.5954 191.023 47.5241 191.498 47.6191C191.783 46.0508 191.973 44.2923 192.068 42.3438C192.211 40.3477 192.425 38.4704 192.71 36.7119C192.995 34.9059 193.47 33.3376 194.136 32.0068C194.801 30.6286 195.87 29.7256 197.344 29.2979C197.629 30.2959 197.985 31.1751 198.413 31.9355C198.888 32.696 199.387 33.4326 199.91 34.1455C200.48 34.8109 201.027 35.5 201.55 36.2129C202.12 36.9258 202.643 37.7337 203.118 38.6367C203.071 39.5397 202.976 40.5853 202.833 41.7734C202.69 42.9616 202.5 44.1735 202.263 45.4092C202.073 46.6449 201.859 47.833 201.621 48.9736C201.431 50.1143 201.241 51.0885 201.051 51.8965C201.098 52.1341 201.217 52.2767 201.407 52.3242C201.645 52.3717 201.692 52.5856 201.55 52.9658C202.69 52.6807 203.879 52.4193 205.114 52.1816C206.35 51.944 207.586 51.6826 208.821 51.3975C210.057 51.1123 211.245 50.8034 212.386 50.4707C213.574 50.0905 214.667 49.639 215.665 49.1162C215.903 47.2627 216.212 45.4092 216.592 43.5557C216.972 41.7021 217.02 39.8011 216.734 37.8525C216.972 37.6624 217.162 37.4248 217.305 37.1396C217.447 36.8545 217.685 36.6644 218.018 36.5693C217.78 35.6663 217.685 34.7158 217.732 33.7178C217.78 32.7197 217.946 31.7692 218.231 30.8662C218.564 29.9632 219.016 29.2028 219.586 28.585C220.204 27.9196 220.94 27.4919 221.796 27.3018C222.319 28.0622 222.77 28.8939 223.15 29.7969C223.531 30.6523 223.911 31.5316 224.291 32.4346C224.719 33.29 225.146 34.1217 225.574 34.9297C226.049 35.6901 226.644 36.3555 227.356 36.9258C227.499 38.304 227.594 39.7298 227.642 41.2031C227.737 42.6764 227.808 44.1735 227.855 45.6943C227.475 47.5003 227.048 49.2588 226.572 50.9697C226.145 52.6331 225.836 54.4154 225.646 56.3164C225.313 56.6966 225.051 57.1243 224.861 57.5996C224.671 58.0749 224.481 58.5739 224.291 59.0967C224.101 59.5719 223.887 60.0472 223.649 60.5225C223.412 60.9502 223.127 61.3304 222.794 61.6631C222.984 62.2334 223.008 62.7799 222.865 63.3027C222.77 63.778 222.913 64.3721 223.293 65.085Z" fill="#0052CC"/>
			<path d="M244.395 67.0098C244.632 68.4355 244.537 69.5524 244.109 70.3604C243.729 71.1683 243.159 71.7624 242.398 72.1426C241.686 72.5703 240.854 72.8079 239.903 72.8555C238.953 72.903 238.05 72.8555 237.194 72.7129C236.719 72.0475 236.291 71.2158 235.911 70.2178C235.531 69.2197 235.174 68.1979 234.842 67.1523C234.509 66.0592 234.2 64.9899 233.915 63.9443C233.677 62.8988 233.464 61.972 233.273 61.1641C233.511 60.5938 233.654 59.9521 233.701 59.2393C233.796 58.4788 233.844 57.7184 233.844 56.958C233.891 56.1976 233.915 55.4372 233.915 54.6768C233.963 53.8688 234.034 53.1084 234.129 52.3955C234.462 50.3994 234.937 48.4984 235.555 46.6924C236.22 44.8864 236.743 43.0091 237.123 41.0605C237.218 40.6328 237.171 40.2288 236.98 39.8486C236.838 39.4684 236.814 39.112 236.909 38.7793C236.957 38.5892 237.099 38.4704 237.337 38.4229C237.575 38.3278 237.717 38.1615 237.765 37.9238C237.907 37.401 237.931 36.9733 237.836 36.6406C237.788 36.3079 237.883 35.904 238.121 35.4287C238.359 35.0485 238.644 34.502 238.977 33.7891C239.357 33.0286 239.499 32.2445 239.404 31.4365C239.832 31.1514 240.236 30.8187 240.616 30.4385C240.996 30.0583 241.377 29.7256 241.757 29.4404C242.185 29.1077 242.636 28.8226 243.111 28.585C243.634 28.3473 244.252 28.2285 244.965 28.2285C245.345 28.7038 245.583 29.2266 245.678 29.7969C245.82 30.3672 245.939 30.9375 246.034 31.5078C246.177 32.0781 246.367 32.6247 246.604 33.1475C246.842 33.6227 247.246 34.0029 247.816 34.2881C247.816 34.6683 247.816 35.1436 247.816 35.7139C247.816 36.2842 247.84 36.8545 247.888 37.4248C247.935 37.9476 248.03 38.4466 248.173 38.9219C248.363 39.3496 248.648 39.6348 249.028 39.7773C248.173 42.0586 247.365 44.3161 246.604 46.5498C245.892 48.7835 245.298 51.041 244.822 53.3223C244.347 55.556 244.038 57.8135 243.896 60.0947C243.8 62.376 243.967 64.681 244.395 67.0098Z" fill="#0052CC"/>
			<path d="M293.228 37.71C292.039 37.71 290.756 37.805 289.378 37.9951C288 38.1377 286.645 38.304 285.314 38.4941C284.364 38.6367 283.437 38.7555 282.534 38.8506C281.679 38.9456 280.895 39.0169 280.182 39.0645C279.849 40.11 279.516 41.0368 279.184 41.8447C278.851 42.6527 278.494 43.4368 278.114 44.1973C277.734 45.0052 277.378 45.8369 277.045 46.6924C276.76 47.5003 276.475 48.3796 276.189 49.3301C275.714 50.0905 275.31 50.8034 274.978 51.4688C274.692 52.0866 274.455 52.7995 274.265 53.6074C273.694 54.3203 273.267 55.0807 272.981 55.8887C272.696 56.6491 272.482 57.3145 272.34 57.8848L272.055 58.7402C271.77 59.0254 271.603 59.263 271.556 59.4531C271.508 59.5957 271.389 59.7383 271.199 59.8809C271.152 60.4036 270.985 60.9264 270.7 61.4492C270.605 61.6868 270.534 61.9245 270.486 62.1621C270.439 62.3522 270.415 62.4948 270.415 62.5898C270.415 63.0651 270.296 63.374 270.059 63.5166C270.106 63.7542 270.106 64.0156 270.059 64.3008C270.011 64.5384 269.964 64.776 269.916 65.0137C269.773 65.584 269.726 66.0355 269.773 66.3682C269.916 66.5583 270.035 66.7246 270.13 66.8672C270.272 67.0098 270.368 67.1999 270.415 67.4375C270.7 67.6276 270.914 67.7464 271.057 67.7939C271.247 67.9365 271.532 68.1029 271.912 68.293L272.554 69.0059C272.791 70.3841 272.577 71.4772 271.912 72.2852C271.199 73.1882 269.987 73.6396 268.276 73.6396H267.421C267.183 73.402 267.041 73.2119 266.993 73.0693C266.613 72.9268 266.304 72.8079 266.066 72.7129C265.829 72.6178 265.615 72.4277 265.425 72.1426C264.807 71.8574 264.308 71.6198 263.928 71.4297L263.286 71.0732C263.286 70.5505 263.286 70.2653 263.286 70.2178C262.953 69.79 262.692 69.3148 262.502 68.792C262.359 68.2692 262.241 67.7464 262.146 67.2236C262.05 66.7959 261.932 66.3919 261.789 66.0117C261.694 65.6315 261.551 65.2988 261.361 65.0137C261.266 62.2572 261.528 59.9284 262.146 58.0273C262.763 56.1263 263.405 54.2966 264.07 52.5381C264.451 51.5876 264.831 50.637 265.211 49.6865C265.591 48.6885 265.924 47.6667 266.209 46.6211L266.851 45.9082C267.183 45.1478 267.445 44.5299 267.635 44.0547C267.872 43.5319 268.11 43.0091 268.348 42.4863C268.49 42.1536 268.609 41.8447 268.704 41.5596C268.847 41.2269 268.965 40.9417 269.061 40.7041C268.775 40.7041 268.514 40.7279 268.276 40.7754C268.039 40.7754 267.801 40.7992 267.563 40.8467C267.278 40.8942 267.017 40.9417 266.779 40.9893C266.542 40.9893 266.304 40.9893 266.066 40.9893C265.734 40.9893 265.496 40.9655 265.354 40.918C264.831 41.1556 264.26 41.2744 263.643 41.2744H262.93C262.787 41.2744 262.645 41.2744 262.502 41.2744C262.407 41.2269 262.312 41.2031 262.217 41.2031C261.742 41.2031 261.361 41.2744 261.076 41.417C260.506 41.417 259.959 41.417 259.437 41.417H258.724C258.629 41.417 258.51 41.4408 258.367 41.4883C258.225 41.4883 258.106 41.4883 258.011 41.4883C257.678 41.4883 257.322 41.4645 256.941 41.417C256.561 41.3219 256.157 41.1556 255.729 40.918C255.159 40.1576 254.779 39.7536 254.589 39.7061L253.947 38.8506C253.9 38.5654 253.828 38.304 253.733 38.0664C253.638 37.7812 253.543 37.4723 253.448 37.1396C253.258 36.6644 253.068 36.1654 252.878 35.6426C252.735 35.0723 252.664 34.4069 252.664 33.6465C252.712 33.2663 252.807 32.9811 252.949 32.791C253.092 32.6009 253.211 32.4346 253.306 32.292L253.52 31.2227L254.446 31.2939H256.442C257.963 31.2939 259.508 31.2464 261.076 31.1514C262.645 31.0088 264.26 30.8662 265.924 30.7236C266.874 30.6286 267.825 30.5335 268.775 30.4385C269.773 30.3434 270.795 30.2484 271.841 30.1533C272.031 29.7256 272.221 29.3216 272.411 28.9414L273.124 28.585C273.979 28.5374 274.574 28.6562 274.906 28.9414C275.286 29.179 275.595 29.4167 275.833 29.6543C276.879 29.5592 277.924 29.4642 278.97 29.3691C280.063 29.2266 281.156 29.0602 282.249 28.8701C283.865 28.6325 285.481 28.4186 287.097 28.2285C288.76 28.0384 290.352 27.9434 291.873 27.9434C292.538 27.9434 293.18 27.9671 293.798 28.0146C294.416 28.0622 294.986 28.1335 295.509 28.2285L296.222 28.7275C296.364 29.0127 296.554 29.2741 296.792 29.5117C297.077 29.7493 297.362 29.987 297.647 30.2246C298.218 30.7474 298.788 31.3652 299.358 32.0781C299.929 32.791 300.143 33.8366 300 35.2148C299.62 35.7852 299.335 36.1178 299.145 36.2129L298.503 37.0684L297.79 36.9971C297.505 36.9971 297.243 37.0446 297.006 37.1396C296.768 37.1872 296.531 37.2585 296.293 37.3535C296.103 37.4486 295.913 37.5436 295.723 37.6387C295.533 37.6862 295.319 37.7337 295.081 37.7812C294.653 37.7812 294.297 37.7812 294.012 37.7812C293.774 37.7337 293.513 37.71 293.228 37.71Z" fill="#0052CC"/>
			<path d="M327.446 31.8643C327.494 32.1969 327.494 32.4583 327.446 32.6484C327.446 32.791 327.423 32.9336 327.375 33.0762C327.375 33.2188 327.375 33.3851 327.375 33.5752C327.375 33.7178 327.423 33.9554 327.518 34.2881C326.52 35.904 325.355 37.0208 324.024 37.6387C322.741 38.209 321.339 38.6367 319.818 38.9219C318.298 39.1595 316.729 39.4209 315.113 39.7061C313.545 39.9912 311.977 40.609 310.408 41.5596C309.79 41.3219 309.22 41.3219 308.697 41.5596C308.222 41.7972 307.509 41.8685 306.559 41.7734C306.273 42.0586 305.917 42.2725 305.489 42.415C305.062 42.5101 304.634 42.6289 304.206 42.7715C304.396 43.1992 304.467 43.6507 304.42 44.126C304.42 44.5537 304.491 44.9102 304.634 45.1953C306.297 45.3379 308.127 45.3141 310.123 45.124C312.167 44.9339 314.163 44.8151 316.111 44.7676C316.159 45.0052 316.135 45.1715 316.04 45.2666C315.945 45.3141 315.921 45.4329 315.969 45.623C316.301 45.8607 316.634 46.1696 316.967 46.5498C317.299 46.8825 317.632 47.2152 317.965 47.5479C318.298 47.8805 318.63 48.1657 318.963 48.4033C319.343 48.5934 319.771 48.6647 320.246 48.6172C320.436 48.9974 320.555 49.4251 320.603 49.9004C320.698 50.3281 320.65 50.7796 320.46 51.2549C319.7 51.7777 318.939 52.3242 318.179 52.8945C317.418 53.4173 316.61 53.8926 315.755 54.3203C314.947 54.748 314.068 55.1045 313.117 55.3896C312.214 55.6273 311.192 55.7223 310.052 55.6748C309.814 55.6748 309.624 55.6748 309.481 55.6748C309.386 55.6748 309.244 55.6035 309.054 55.4609C308.674 55.6986 308.127 55.7699 307.414 55.6748C306.749 55.5798 306.226 55.651 305.846 55.8887C305.323 55.651 304.681 55.556 303.921 55.6035C303.208 55.6035 302.566 55.5085 301.996 55.3184C301.236 56.3164 300.547 57.3857 299.929 58.5264C299.311 59.667 298.764 60.8789 298.289 62.1621C299.905 62.3997 301.236 62.4948 302.281 62.4473C303.374 62.3997 304.396 62.3284 305.347 62.2334C306.297 62.0908 307.271 61.972 308.27 61.877C309.268 61.7344 310.527 61.6868 312.048 61.7344C312.998 61.2116 314.115 60.8789 315.398 60.7363C316.729 60.5462 317.941 60.2611 319.034 59.8809C319.414 60.0234 319.747 60.2848 320.032 60.665C320.317 60.9977 320.579 61.3304 320.816 61.6631C321.102 61.9482 321.387 62.2096 321.672 62.4473C321.957 62.6849 322.313 62.7799 322.741 62.7324C322.931 62.9225 323.05 63.1602 323.098 63.4453C323.193 63.6829 323.264 63.9206 323.312 64.1582C323.359 64.3958 323.43 64.6097 323.525 64.7998C323.668 64.9424 323.882 64.9899 324.167 64.9424C323.454 66.5107 322.622 67.7227 321.672 68.5781C320.769 69.3861 319.723 70.0039 318.535 70.4316C317.347 70.8594 316.016 71.1921 314.543 71.4297C313.117 71.6673 311.525 71.9525 309.767 72.2852C309.339 72.3802 308.982 72.3564 308.697 72.2139C308.412 72.1188 308.079 72.0713 307.699 72.0713C306.606 72.3089 305.299 72.5228 303.778 72.7129C302.257 72.903 300.713 72.9743 299.145 72.9268C297.576 72.8792 296.079 72.6654 294.653 72.2852C293.228 71.9525 292.063 71.3346 291.16 70.4316C290.78 70.099 290.519 69.7188 290.376 69.291C290.281 68.8158 289.972 68.4593 289.449 68.2217C289.402 67.6038 289.307 66.9147 289.164 66.1543C289.021 65.3939 288.879 64.6097 288.736 63.8018C288.594 62.9463 288.475 62.1146 288.38 61.3066C288.332 60.4987 288.38 59.762 288.522 59.0967C288.665 58.3838 288.95 57.7422 289.378 57.1719C289.853 56.6016 290.02 55.9837 289.877 55.3184C290.115 55.2708 290.257 55.152 290.305 54.9619C290.352 54.7243 290.542 54.6292 290.875 54.6768C291.16 53.8213 291.469 53.0133 291.802 52.2529C292.134 51.4925 292.467 50.7559 292.8 50.043C293.18 49.3301 293.536 48.6172 293.869 47.9043C294.249 47.1439 294.606 46.3122 294.938 45.4092C294.273 44.7913 293.774 44.126 293.441 43.4131C293.156 42.6527 292.942 41.916 292.8 41.2031C292.657 40.4427 292.538 39.6823 292.443 38.9219C292.348 38.1139 292.182 37.306 291.944 36.498C292.42 35.7852 292.99 35.2148 293.655 34.7871C294.368 34.3118 295.129 33.9316 295.937 33.6465C296.744 33.3613 297.624 33.1237 298.574 32.9336C299.525 32.7435 300.523 32.5296 301.568 32.292C303.469 31.6742 305.157 31.1989 306.63 30.8662C308.151 30.5335 309.624 30.2721 311.05 30.082C312.523 29.8444 314.044 29.6305 315.612 29.4404C317.181 29.2503 318.963 28.9652 320.959 28.585C321.387 29.0127 321.791 29.2266 322.171 29.2266C322.361 28.7988 322.575 28.68 322.812 28.8701C323.098 29.0127 323.216 28.8701 323.169 28.4424C323.644 29.2503 324.238 29.9395 324.951 30.5098C325.712 31.0326 326.543 31.484 327.446 31.8643Z" fill="#0052CC"/>
			<path d="M356.817 69.3623C356.627 69.1722 356.461 68.9583 356.318 68.7207C356.176 68.4355 356.057 68.0791 355.962 67.6514C355.297 66.8434 354.774 65.9167 354.394 64.8711C354.013 63.8255 353.752 62.7562 353.609 61.6631C353.467 60.5225 353.443 59.3818 353.538 58.2412C353.633 57.1006 353.799 56.0312 354.037 55.0332C354.18 54.5579 354.299 54.0589 354.394 53.5361C354.631 52.6331 354.869 51.7539 355.106 50.8984C355.344 49.9954 355.701 49.2588 356.176 48.6885C355.986 48.1657 356.009 47.5954 356.247 46.9775C356.485 46.3597 356.722 45.7894 356.96 45.2666C357.055 45.124 357.126 45.0052 357.174 44.9102C357.221 44.7676 357.269 44.6488 357.316 44.5537C357.887 42.8903 358.433 41.2982 358.956 39.7773C359.526 38.209 360.073 36.7119 360.596 35.2861C361.546 32.6722 362.449 30.1058 363.305 27.5869C364.208 25.068 365.016 22.3828 365.729 19.5312C366.109 19.2461 366.441 19.056 366.727 18.9609C366.822 18.9134 366.94 18.8659 367.083 18.8184C367.226 18.7708 367.392 18.7471 367.582 18.7471C368.58 19.2699 369.744 19.7689 371.075 20.2441C372.453 20.6719 373.618 20.8857 374.568 20.8857L375.78 21.9551C376.398 22.668 376.921 23.4284 377.349 24.2363C377.824 24.9967 377.966 25.9948 377.776 27.2305C377.491 27.6582 377.254 27.9196 377.063 28.0146C376.778 29.1077 376.469 30.082 376.137 30.9375C375.804 31.7454 375.471 32.5296 375.139 33.29C374.758 34.1455 374.402 35.001 374.069 35.8564C373.737 36.7119 373.452 37.7575 373.214 38.9932C372.929 39.3258 372.739 39.611 372.644 39.8486C372.596 40.0863 372.525 40.3239 372.43 40.5615C372.335 40.8467 372.216 41.1556 372.073 41.4883C371.931 41.7734 371.717 42.0348 371.432 42.2725C371.242 43.7458 370.956 44.9577 370.576 45.9082C370.196 46.8112 369.816 47.7142 369.436 48.6172C368.77 50.2806 368.152 51.944 367.582 53.6074C367.012 55.2233 366.679 57.0768 366.584 59.168C367.154 59.0254 367.677 58.9541 368.152 58.9541C368.77 58.9541 369.364 59.0492 369.935 59.2393C371.646 58.3838 373.499 57.861 375.495 57.6709C377.539 57.4808 379.559 57.362 381.555 57.3145C383.218 57.3145 384.763 57.2432 386.188 57.1006C386.664 57.4333 386.973 57.6234 387.115 57.6709C387.258 57.6709 387.495 57.6709 387.828 57.6709C387.971 57.6709 388.113 57.6709 388.256 57.6709C388.398 57.6709 388.589 57.6947 388.826 57.7422C389.492 58.4076 390.133 58.8828 390.751 59.168C391.369 59.4056 392.034 59.6432 392.747 59.8809C393.365 60.1185 393.959 60.3799 394.529 60.665C395.147 60.9027 395.741 61.2354 396.312 61.6631L396.953 65.2988C397.333 65.8216 397.5 66.1781 397.452 66.3682C397.452 66.7484 397.357 67.0811 397.167 67.3662L397.31 68.293C396.407 68.4831 395.812 68.6969 395.527 68.9346C395.242 69.1247 394.933 69.3861 394.601 69.7188C394.078 69.7663 393.579 69.79 393.104 69.79C392.153 69.79 391.179 69.9326 390.181 70.2178C388.755 69.695 387.187 69.4336 385.476 69.4336C384.81 69.4336 384.121 69.4574 383.408 69.5049C382.743 69.5524 382.077 69.6237 381.412 69.7188C380.699 69.8138 379.986 69.9089 379.273 70.0039C378.561 70.0514 377.848 70.0752 377.135 70.0752C376.564 70.1702 376.16 70.2415 375.923 70.2891C375.685 70.3366 375.448 70.4079 375.21 70.5029C373.594 71.0257 371.836 71.5485 369.935 72.0713C368.081 72.5941 366.204 72.8555 364.303 72.8555C361.499 72.8555 359.241 72.1426 357.53 70.7168C357.34 70.2891 357.198 70.0039 357.103 69.8613C357.007 69.7188 356.912 69.5524 356.817 69.3623Z" fill="#0052CC"/>
			<path d="M436.661 66.0117C436.376 66.8197 435.948 67.6514 435.378 68.5068C434.855 69.3148 434.214 70.0514 433.453 70.7168C432.74 71.3346 431.932 71.8574 431.029 72.2852C430.126 72.7129 429.176 72.9505 428.178 72.998C427.56 72.8555 427.132 72.6891 426.895 72.499C426.704 72.3564 426.491 72.2139 426.253 72.0713C426.11 71.9762 425.968 71.9049 425.825 71.8574C425.73 71.7624 425.635 71.6911 425.54 71.6436L424.685 71.2871L424.97 70.3604C425.065 69.9801 425.041 69.695 424.898 69.5049C424.756 69.2673 424.471 68.9583 424.043 68.5781C423.9 68.4355 423.758 68.293 423.615 68.1504C423.473 68.0078 423.354 67.8652 423.259 67.7227L422.047 66.3682L423.972 65.8691C425.16 65.5365 425.944 65.1087 426.324 64.5859C426.752 64.0632 426.918 63.1839 426.823 61.9482C425.873 61.7581 425.065 61.7344 424.399 61.877C423.782 62.0195 423.14 62.1859 422.475 62.376C421.619 62.6136 420.74 62.8275 419.837 63.0176C418.981 63.1602 418.031 63.2314 416.985 63.2314C416.605 63.2314 416.249 63.2314 415.916 63.2314C415.583 63.1839 415.227 63.1364 414.847 63.0889L414.134 62.9463C414.039 62.4235 413.944 62.1383 413.849 62.0908C413.754 61.9958 413.659 61.9007 413.563 61.8057C413.326 61.568 413.064 61.2591 412.779 60.8789C412.542 60.4512 412.494 59.9284 412.637 59.3105C412.399 58.8828 412.257 58.4551 412.209 58.0273C411.924 58.5977 411.71 59.1917 411.567 59.8096L411.282 60.5225L410.569 60.665C410.474 60.7126 410.403 60.8314 410.355 61.0215C410.308 61.1641 410.284 61.2829 410.284 61.3779L409.928 62.5898C409.833 62.6849 409.738 62.7799 409.643 62.875C409.548 62.9225 409.452 62.9938 409.357 63.0889C409.262 63.5166 409.144 63.9443 409.001 64.3721C408.858 64.7998 408.716 65.2038 408.573 65.584C408.336 66.2018 408.122 66.8197 407.932 67.4375C407.789 68.0078 407.742 68.5781 407.789 69.1484L407.504 69.9326C407.266 70.2178 407.005 70.4554 406.72 70.6455C406.435 70.7881 406.149 70.9307 405.864 71.0732C405.769 71.1683 405.627 71.2633 405.437 71.3584C405.294 71.4059 405.175 71.4534 405.08 71.501L404.866 72.2852L404.011 72.4277C403.488 72.5228 403.108 72.5941 402.87 72.6416C402.68 72.6891 402.442 72.7129 402.157 72.7129L401.444 72.6416C400.922 72.2139 400.518 71.8812 400.232 71.6436C399.947 71.3584 399.615 71.097 399.234 70.8594C398.807 70.2891 398.569 69.8613 398.521 69.5762C398.474 69.291 398.426 69.0296 398.379 68.792C398.284 68.5068 398.236 68.2692 398.236 68.0791C398.189 67.889 398.118 67.7464 398.022 67.6514L397.096 66.7959L397.88 66.1543L397.951 64.9424C398.902 62.5186 399.9 60.1898 400.945 57.9561C402.038 55.7223 403.155 53.4411 404.296 51.1123C404.676 50.3519 405.033 49.5915 405.365 48.8311C405.745 48.0706 406.126 47.3102 406.506 46.5498C406.458 46.4072 406.435 46.2409 406.435 46.0508C406.435 45.8607 406.482 45.6468 406.577 45.4092C407.1 44.6012 407.528 43.8883 407.86 43.2705C408.193 42.6051 408.526 41.9398 408.858 41.2744C409.239 40.4665 409.619 39.6823 409.999 38.9219C410.427 38.1615 410.95 37.3773 411.567 36.5693L412.708 36.0703C412.851 36.0228 413.017 35.9753 413.207 35.9277C413.397 35.8327 413.611 35.7852 413.849 35.7852L414.918 35.9277C416.249 35.3099 417.413 34.4544 418.411 33.3613C419.409 32.2207 420.431 30.8662 421.477 29.2979L422.189 28.2998L423.116 28.8701C423.354 29.0127 423.687 29.1553 424.114 29.2979C424.542 29.3929 424.993 29.5117 425.469 29.6543C426.324 29.8444 427.132 30.0583 427.893 30.2959C428.653 30.5335 429.271 30.89 429.746 31.3652C430.031 32.1257 430.198 32.696 430.245 33.0762C430.293 33.4564 430.364 33.8366 430.459 34.2168C430.602 35.0247 430.72 35.8327 430.815 36.6406C430.958 37.4486 431.053 38.3516 431.101 39.3496C431.338 40.1576 431.528 40.9655 431.671 41.7734C431.861 42.5339 432.027 43.318 432.17 44.126C432.408 45.2666 432.645 46.3835 432.883 47.4766C433.12 48.5221 433.429 49.5202 433.81 50.4707C434.19 50.4707 434.427 50.4945 434.522 50.542C434.76 50.4469 434.998 50.3757 435.235 50.3281C435.521 50.2806 435.829 50.2331 436.162 50.1855L436.946 50.1143L437.231 50.8271C437.279 51.0648 437.517 51.3024 437.944 51.54C438.562 51.5876 439.014 51.7301 439.299 51.9678C439.584 52.1579 439.845 52.3717 440.083 52.6094C440.273 52.7995 440.463 52.9421 440.653 53.0371L441.723 53.4648L441.152 54.5342C440.677 55.3896 439.988 56.2214 439.085 57.0293C438.229 57.7897 437.255 58.4313 436.162 58.9541C436.21 59.2393 436.233 59.5244 436.233 59.8096C436.281 60.0472 436.352 60.3086 436.447 60.5938C436.59 61.4017 436.709 62.2334 436.804 63.0889C436.899 63.9443 436.899 64.8236 436.804 65.7266L436.661 66.0117ZM416.059 53.6787C417.484 53.5837 418.768 53.4886 419.908 53.3936C421.096 53.251 422.403 53.0371 423.829 52.752C423.496 51.3262 423.14 49.8529 422.76 48.332C422.38 46.7637 421.976 45.2191 421.548 43.6982L421.12 44.4111C420.17 45.4092 419.243 46.526 418.34 47.7617C417.484 48.9974 416.748 50.1618 416.13 51.2549C415.845 51.6826 415.56 52.1104 415.274 52.5381C414.989 52.9658 414.728 53.3936 414.49 53.8213C414.775 53.7738 415.037 53.75 415.274 53.75C415.56 53.75 415.821 53.7262 416.059 53.6787Z" fill="#0052CC"/>
			<path d="M479.078 60.3799C479.363 60.5225 479.53 60.7601 479.577 61.0928C479.625 61.4255 479.625 61.6868 479.577 61.877C478.722 62.9701 478.128 63.7305 477.795 64.1582C477.462 64.5859 477.225 64.9186 477.082 65.1562C476.939 65.3464 476.773 65.5127 476.583 65.6553C476.44 65.7979 476.084 66.1068 475.514 66.582C473.375 67.8652 471.165 69.0059 468.884 70.0039C466.65 71.002 464.298 71.6911 461.826 72.0713C461.446 72.5941 460.709 72.9505 459.616 73.1406C458.571 73.3783 457.596 73.5921 456.693 73.7822C456.503 73.6872 456.384 73.5446 456.337 73.3545C456.337 73.2119 456.242 73.0931 456.052 72.998C456.004 73.4733 455.79 73.7109 455.41 73.7109C455.03 73.7585 454.555 73.6634 453.984 73.4258C453.462 73.2357 452.891 72.9505 452.273 72.5703C451.703 72.2376 451.204 71.9287 450.776 71.6436C450.539 72.0238 450.277 72.404 449.992 72.7842C449.755 73.1644 449.446 73.4258 449.065 73.5684C448.543 73.4733 448.139 73.402 447.854 73.3545C447.616 73.307 447.378 73.402 447.141 73.6396C446.855 73.4971 446.618 73.3307 446.428 73.1406C446.285 72.9505 446.119 72.7604 445.929 72.5703C445.786 72.4277 445.596 72.2852 445.358 72.1426C445.168 72 444.883 71.8812 444.503 71.7861C444.598 71.596 444.55 71.3109 444.36 70.9307C444.17 70.5029 443.885 70.2415 443.505 70.1465C443.695 69.8138 443.6 69.4098 443.22 68.9346C442.887 68.4118 442.626 67.8415 442.436 67.2236C442.531 66.7008 442.602 66.1305 442.649 65.5127C442.697 64.8949 442.982 64.5384 443.505 64.4434C443.457 64.1107 443.386 63.8255 443.291 63.5879C443.196 63.3503 443.101 63.1126 443.006 62.875C443.339 62.5898 443.576 62.2572 443.719 61.877C443.861 61.4492 443.98 61.0215 444.075 60.5938C444.17 60.166 444.289 59.7383 444.432 59.3105C444.574 58.8828 444.836 58.5264 445.216 58.2412C445.644 56.8154 445.976 55.7699 446.214 55.1045C446.452 54.3916 446.618 53.7025 446.713 53.0371C446.855 52.3717 446.998 51.6589 447.141 50.8984C447.331 50.138 447.64 49.1875 448.067 48.0469C448.02 47.334 448.091 46.7161 448.281 46.1934C448.471 45.623 448.661 45.0765 448.852 44.5537C449.089 43.9834 449.279 43.4131 449.422 42.8428C449.564 42.2249 449.541 41.512 449.351 40.7041C448.685 40.7516 448.21 40.9893 447.925 41.417C447.64 41.7972 447.45 42.32 447.354 42.9854C446.974 43.3656 446.499 43.5557 445.929 43.5557C445.406 43.5081 444.931 43.5557 444.503 43.6982C442.84 43.0329 441.461 42.0824 440.368 40.8467C439.275 39.611 438.396 38.1615 437.73 36.498C438.158 35.4049 438.8 34.502 439.655 33.7891C440.511 33.0286 441.485 32.4108 442.578 31.9355C443.671 31.4128 444.836 31.0088 446.071 30.7236C447.307 30.391 448.495 30.082 449.636 29.7969C451.489 29.4642 453.343 29.084 455.196 28.6562C457.05 28.2285 459.046 28.0859 461.185 28.2285C461.47 28.0859 461.802 28.0146 462.183 28.0146C462.61 27.9671 463.038 27.9434 463.466 27.9434C463.894 27.9434 464.298 27.9434 464.678 27.9434C465.105 27.8958 465.462 27.8008 465.747 27.6582C465.985 27.9434 466.317 28.1335 466.745 28.2285C467.22 28.276 467.719 28.4899 468.242 28.8701C468.812 28.4424 469.288 28.3473 469.668 28.585C470.096 28.8226 470.476 29.084 470.809 29.3691C471.141 29.6543 471.45 29.9157 471.735 30.1533C472.068 30.391 472.401 30.5098 472.733 30.5098C473.066 30.9375 473.47 31.3415 473.945 31.7217C474.468 32.1019 474.943 32.5059 475.371 32.9336C475.846 33.3138 476.227 33.7415 476.512 34.2168C476.844 34.6921 477.011 35.2386 477.011 35.8564C476.963 36.0941 477.035 36.2367 477.225 36.2842C477.415 36.3317 477.557 36.4268 477.652 36.5693C477.7 38.0426 477.462 39.3258 476.939 40.4189C476.464 41.4645 475.846 42.4388 475.086 43.3418C474.373 44.2448 473.565 45.1003 472.662 45.9082C471.807 46.7161 471.022 47.5716 470.31 48.4746C470.785 49.0924 471.331 49.639 471.949 50.1143C472.567 50.5895 473.185 51.0648 473.803 51.54C474.468 52.0153 475.086 52.5143 475.656 53.0371C476.227 53.5599 476.702 54.1777 477.082 54.8906C477.177 54.9857 477.296 55.0807 477.438 55.1758C477.581 55.2233 477.724 55.2946 477.866 55.3896C478.009 55.8649 478.199 56.3639 478.437 56.8867C478.674 57.362 478.864 57.8372 479.007 58.3125C479.197 58.7402 479.316 59.1442 479.363 59.5244C479.411 59.8571 479.316 60.1423 479.078 60.3799ZM466.531 36.5693C464.107 36.4743 461.684 36.6882 459.26 37.2109C456.883 37.7337 454.887 38.4704 453.271 39.4209C454.079 40.0863 454.887 40.6803 455.695 41.2031C456.551 41.7259 457.216 42.4388 457.691 43.3418C459.545 42.5814 461.185 41.6309 462.61 40.4902C464.036 39.3021 465.343 37.9951 466.531 36.5693ZM467.458 59.3105C467.601 58.8353 467.862 58.4551 468.242 58.1699C468.622 57.8848 469.003 57.6234 469.383 57.3857C469.763 57.1006 470.096 56.8154 470.381 56.5303C470.666 56.1976 470.785 55.7461 470.737 55.1758C469.216 54.3678 467.814 53.8926 466.531 53.75C465.296 53.5599 464.107 53.5599 462.967 53.75C461.874 53.8926 460.804 54.1302 459.759 54.4629C458.761 54.7956 457.739 55.0332 456.693 55.1758C456.171 56.554 455.624 58.0511 455.054 59.667C454.483 61.2829 453.937 62.8512 453.414 64.3721C454.84 64.182 456.147 63.8968 457.335 63.5166C458.523 63.0889 459.664 62.6374 460.757 62.1621C461.897 61.6868 462.991 61.2116 464.036 60.7363C465.129 60.2135 466.27 59.7383 467.458 59.3105Z" fill="#0052CC"/>
			<path d="M521.566 31.8643C521.614 32.1969 521.614 32.4583 521.566 32.6484C521.566 32.791 521.543 32.9336 521.495 33.0762C521.495 33.2188 521.495 33.3851 521.495 33.5752C521.495 33.7178 521.543 33.9554 521.638 34.2881C520.64 35.904 519.475 37.0208 518.145 37.6387C516.861 38.209 515.459 38.6367 513.938 38.9219C512.418 39.1595 510.849 39.4209 509.233 39.7061C507.665 39.9912 506.097 40.609 504.528 41.5596C503.91 41.3219 503.34 41.3219 502.817 41.5596C502.342 41.7972 501.629 41.8685 500.679 41.7734C500.394 42.0586 500.037 42.2725 499.609 42.415C499.182 42.5101 498.754 42.6289 498.326 42.7715C498.516 43.1992 498.588 43.6507 498.54 44.126C498.54 44.5537 498.611 44.9102 498.754 45.1953C500.417 45.3379 502.247 45.3141 504.243 45.124C506.287 44.9339 508.283 44.8151 510.231 44.7676C510.279 45.0052 510.255 45.1715 510.16 45.2666C510.065 45.3141 510.041 45.4329 510.089 45.623C510.422 45.8607 510.754 46.1696 511.087 46.5498C511.42 46.8825 511.752 47.2152 512.085 47.5479C512.418 47.8805 512.75 48.1657 513.083 48.4033C513.463 48.5934 513.891 48.6647 514.366 48.6172C514.556 48.9974 514.675 49.4251 514.723 49.9004C514.818 50.3281 514.77 50.7796 514.58 51.2549C513.82 51.7777 513.059 52.3242 512.299 52.8945C511.538 53.4173 510.73 53.8926 509.875 54.3203C509.067 54.748 508.188 55.1045 507.237 55.3896C506.334 55.6273 505.312 55.7223 504.172 55.6748C503.934 55.6748 503.744 55.6748 503.602 55.6748C503.507 55.6748 503.364 55.6035 503.174 55.4609C502.794 55.6986 502.247 55.7699 501.534 55.6748C500.869 55.5798 500.346 55.651 499.966 55.8887C499.443 55.651 498.801 55.556 498.041 55.6035C497.328 55.6035 496.687 55.5085 496.116 55.3184C495.356 56.3164 494.667 57.3857 494.049 58.5264C493.431 59.667 492.884 60.8789 492.409 62.1621C494.025 62.3997 495.356 62.4948 496.401 62.4473C497.494 62.3997 498.516 62.3284 499.467 62.2334C500.417 62.0908 501.392 61.972 502.39 61.877C503.388 61.7344 504.647 61.6868 506.168 61.7344C507.118 61.2116 508.235 60.8789 509.519 60.7363C510.849 60.5462 512.061 60.2611 513.154 59.8809C513.535 60.0234 513.867 60.2848 514.152 60.665C514.438 60.9977 514.699 61.3304 514.937 61.6631C515.222 61.9482 515.507 62.2096 515.792 62.4473C516.077 62.6849 516.434 62.7799 516.861 62.7324C517.051 62.9225 517.17 63.1602 517.218 63.4453C517.313 63.6829 517.384 63.9206 517.432 64.1582C517.479 64.3958 517.55 64.6097 517.646 64.7998C517.788 64.9424 518.002 64.9899 518.287 64.9424C517.574 66.5107 516.743 67.7227 515.792 68.5781C514.889 69.3861 513.843 70.0039 512.655 70.4316C511.467 70.8594 510.136 71.1921 508.663 71.4297C507.237 71.6673 505.645 71.9525 503.887 72.2852C503.459 72.3802 503.103 72.3564 502.817 72.2139C502.532 72.1188 502.2 72.0713 501.819 72.0713C500.726 72.3089 499.419 72.5228 497.898 72.7129C496.378 72.903 494.833 72.9743 493.265 72.9268C491.696 72.8792 490.199 72.6654 488.773 72.2852C487.348 71.9525 486.183 71.3346 485.28 70.4316C484.9 70.099 484.639 69.7188 484.496 69.291C484.401 68.8158 484.092 68.4593 483.569 68.2217C483.522 67.6038 483.427 66.9147 483.284 66.1543C483.142 65.3939 482.999 64.6097 482.856 63.8018C482.714 62.9463 482.595 62.1146 482.5 61.3066C482.452 60.4987 482.5 59.762 482.643 59.0967C482.785 58.3838 483.07 57.7422 483.498 57.1719C483.973 56.6016 484.14 55.9837 483.997 55.3184C484.235 55.2708 484.377 55.152 484.425 54.9619C484.472 54.7243 484.662 54.6292 484.995 54.6768C485.28 53.8213 485.589 53.0133 485.922 52.2529C486.255 51.4925 486.587 50.7559 486.92 50.043C487.3 49.3301 487.657 48.6172 487.989 47.9043C488.369 47.1439 488.726 46.3122 489.059 45.4092C488.393 44.7913 487.894 44.126 487.562 43.4131C487.276 42.6527 487.062 41.916 486.92 41.2031C486.777 40.4427 486.659 39.6823 486.563 38.9219C486.468 38.1139 486.302 37.306 486.064 36.498C486.54 35.7852 487.11 35.2148 487.775 34.7871C488.488 34.3118 489.249 33.9316 490.057 33.6465C490.865 33.3613 491.744 33.1237 492.694 32.9336C493.645 32.7435 494.643 32.5296 495.688 32.292C497.59 31.6742 499.277 31.1989 500.75 30.8662C502.271 30.5335 503.744 30.2721 505.17 30.082C506.643 29.8444 508.164 29.6305 509.732 29.4404C511.301 29.2503 513.083 28.9652 515.079 28.585C515.507 29.0127 515.911 29.2266 516.291 29.2266C516.481 28.7988 516.695 28.68 516.933 28.8701C517.218 29.0127 517.337 28.8701 517.289 28.4424C517.764 29.2503 518.358 29.9395 519.071 30.5098C519.832 31.0326 520.663 31.484 521.566 31.8643Z" fill="#0052CC"/>
			<path d="M538.676 71.2158C537.345 71.6436 535.896 72.0713 534.327 72.499C532.759 72.9268 531.19 73.1406 529.622 73.1406C527.246 73.1406 525.368 72.5466 523.99 71.3584C523.753 70.8356 523.539 70.4792 523.349 70.2891C523.206 70.099 523.063 69.9089 522.921 69.7188C522.826 69.4811 522.755 69.1722 522.707 68.792C522.137 68.1266 521.685 67.3662 521.353 66.5107C521.02 65.6077 520.782 64.7048 520.64 63.8018C520.545 62.8512 520.521 61.9007 520.568 60.9502C520.663 59.9997 520.83 59.0967 521.067 58.2412C521.162 58.0511 521.21 57.861 521.21 57.6709C521.257 57.4808 521.305 57.2669 521.353 57.0293C521.543 56.2689 521.733 55.5322 521.923 54.8193C522.16 54.1064 522.469 53.4886 522.85 52.9658C522.66 52.5381 522.66 52.0628 522.85 51.54C523.04 51.0173 523.254 50.542 523.491 50.1143C524.157 48.3083 524.703 46.7874 525.131 45.5518C525.606 44.2686 526.081 43.0091 526.557 41.7734C527.365 39.6348 528.125 37.5199 528.838 35.4287C529.551 33.3376 530.192 31.0801 530.763 28.6562C531.143 28.4186 531.428 28.2523 531.618 28.1572C531.903 28.0622 532.141 27.9909 532.331 27.9434C533.187 28.3711 534.185 28.7988 535.325 29.2266C536.466 29.6068 537.416 29.7969 538.177 29.7969C538.557 30.0345 538.795 30.2246 538.89 30.3672C538.985 30.4622 539.08 30.5573 539.175 30.6523C539.698 31.2702 540.149 31.9118 540.529 32.5771C540.91 33.195 541.028 34.0267 540.886 35.0723C540.601 35.5 540.387 35.7139 540.244 35.7139C540.007 36.6644 539.745 37.4723 539.46 38.1377C539.222 38.8031 538.961 39.4684 538.676 40.1338C538.343 40.8467 538.034 41.5596 537.749 42.2725C537.464 42.9854 537.226 43.8646 537.036 44.9102C536.846 45.1478 536.703 45.3617 536.608 45.5518C536.561 45.7419 536.49 45.9557 536.395 46.1934C536.299 46.431 536.181 46.6924 536.038 46.9775C535.943 47.2152 535.777 47.429 535.539 47.6191C535.396 48.8548 535.159 49.8529 534.826 50.6133C534.541 51.3737 534.232 52.1341 533.899 52.8945C533.329 54.2728 532.806 55.651 532.331 57.0293C531.903 58.4076 531.642 59.9759 531.547 61.7344C531.785 61.6393 531.998 61.5918 532.188 61.5918C532.426 61.5918 532.64 61.5918 532.83 61.5918C533.258 61.5918 533.757 61.6631 534.327 61.8057C535.753 61.0452 537.298 60.5938 538.961 60.4512C540.672 60.3086 542.359 60.2135 544.022 60.166C544.688 60.166 545.353 60.166 546.019 60.166C546.684 60.166 547.302 60.1185 547.872 60.0234C548.3 60.2611 548.561 60.4036 548.656 60.4512C548.799 60.4512 548.989 60.4512 549.227 60.4512C549.559 60.4512 549.844 60.4749 550.082 60.5225C550.652 61.0452 551.199 61.4255 551.722 61.6631C552.244 61.9007 552.791 62.1146 553.361 62.3047C553.884 62.4948 554.383 62.7087 554.858 62.9463C555.381 63.1364 555.88 63.4216 556.355 63.8018L556.854 66.8672C557.187 67.2949 557.33 67.5801 557.282 67.7227C557.282 68.1029 557.187 68.388 556.997 68.5781L557.14 69.3623C556.427 69.5049 555.928 69.6712 555.643 69.8613C555.405 70.0514 555.167 70.2653 554.93 70.5029C554.549 70.5505 554.264 70.5742 554.074 70.5742C553.932 70.5742 553.789 70.5742 553.646 70.5742C552.981 70.5742 552.173 70.693 551.223 70.9307C549.939 70.5029 548.609 70.2891 547.23 70.2891C546.708 70.2891 546.161 70.3128 545.591 70.3604C545.021 70.4079 544.45 70.4554 543.88 70.5029C543.31 70.598 542.715 70.6693 542.098 70.7168C541.527 70.7643 540.933 70.7881 540.315 70.7881L538.676 71.2158Z" fill="#0052CC"/>
			</svg>
			';
	}
}
