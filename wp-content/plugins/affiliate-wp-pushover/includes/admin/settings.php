<?php

class AffiliateWP_Pushover_Admin {

	public function __construct() {
		add_filter( 'affwp_settings_tabs',           array( $this, 'setting_tab'       ) );
		add_action( 'admin_init',                    array( $this, 'register_settings' ) );
	}

	public function setting_tab( $tabs ) {
		$tabs['pushover'] = __( 'Pushover', 'affiliate-wp-pushover' );
		return $tabs;
	}

	public function register_settings() {

		add_settings_section(
			'affwp_settings_pushover',
			__return_null(),
			'__return_false',
			'affwp_settings_pushover'
		);

		add_settings_field(
			'affwp_settings[pushover_app_key]',
			__( 'Pushover Application Key', 'affiliate-wp-pushover' ),
			array( $this, 'pushover' ),
			'affwp_settings_pushover',
			'affwp_settings_pushover'
		);

	}

	public function pushover() {
		$settings = get_option( 'affwp_settings' );
		$pushover_app_key = isset( $settings['pushover_app_key'] ) ? $settings['pushover_app_key'] : '';
?>
		<form id="affiliatewp-pushover-form">
			<input type="text" size="50" name="affwp_settings[pushover_app_key]" value="<?php echo $pushover_app_key; ?>" placeholder="Enter Application Key" />
		</form>
<?php
	}

}

new AffiliateWP_Pushover_Admin;
