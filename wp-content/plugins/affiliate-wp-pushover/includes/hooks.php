<?php

/**
 * Output the Pushover User Key in the Affiliate Dashboard -> Settings
 * @param  int $affiliate_id The Affiliates ID
 * @param  int $user_id      The User ID for this affiliate
 * @return void
 */
function affwp_pushover_user_key_output( $affiliate_id, $user_id ) {
	$current_key = get_user_meta( $user_id, '_affwp_pushover_user_key', true );
	$current_key = !empty( $current_key ) ? $current_key : '';
	?>
	<p>
		<strong><?php _e( 'Receive referral notifications via Pushover', 'affiliate-wp-pushover'); ?></strong><br />
		<input type="text" size="50" name="_affwp_pushover_user_key" placeholder="<?php _e( 'Pushover User Key', 'affiliate-wp-pushover' ); ?>" value="<?php echo $current_key; ?>" />
	</p>
	<?php
}
add_action( 'affwp_affiliate_dashboard_before_submit', 'affwp_pushover_user_key_output', 10, 2 );

/**
 * Save the changes made to the Settings page of the Affiliate Dashboard
 * @param  array $data Data from the saving of the form
 * @return void
 */
function affwp_pushover_save_profile_settings( $data ) {
	if ( !isset( $_POST['_affwp_pushover_user_key'] ) ) {
		return;
	}

	$new_key = $_POST['_affwp_pushover_user_key'];

	update_user_meta( get_current_user_id(), '_affwp_pushover_user_key', $new_key );
}
add_action( 'affwp_update_affiliate_profile_settings', 'affwp_pushover_save_profile_settings', 10, 1 );

/**
 * Hooks into new accepted referrals and sends a Pushover Notification if necessary
 * @param  id     $affiliate_id The affilaites ID
 * @param  object $referral     The referral received
 * @return void
 */
function affwp_pushover_new_referral_notification( $affiliate_id, $referral ) {
	$user_id = affwp_get_affiliate_user_id( $affiliate_id );

	$user_key = get_user_meta( $user_id, '_affwp_pushover_user_key', true );
	if ( ! get_user_meta( $user_id, 'affwp_referral_notifications', true ) || empty( $user_key ) ) {
		return;
	}

	$pushover_key = get_user_meta( $user_id, '_affwp_pushover_user_key', true );
	$title        = apply_filters( 'affwp_pushover_title', sprintf( __( 'New Referral Awarded for %s', 'affiliate-wp-pushover' ), get_bloginfo( 'name' ) ) );
	$amount       = affwp_currency_filter( affwp_format_amount( $referral->amount ) );
	$message      = apply_filters( 'affwp_pushover_message', sprintf( __( 'Total Amount: %s', 'affiliate-wp-pushover' ), $amount ) );

	do_action( 'affwp_pushover_send_notification', $pushover_key, $title, $message );
}
add_action( 'affwp_referral_accepted', 'affwp_pushover_new_referral_notification', 10, 2 );

/**
 * Sends the actual notification, for extensibility purposes, this is a hookable action
 * @param  string $pushover_key The User's Pushover API Key
 * @param  string $title        The Title of the Pushover message
 * @param  string $message      The message to send
 * @return bool                 Checks if the reponse was an error, and returns the opposite
 */
function affwp_pushover_send_notification( $pushover_key = '', $title = '', $message = '' ) {
	$settings = get_option( 'affwp_settings' );
	$pushover_app_key = isset( $settings['pushover_app_key'] ) ? $settings['pushover_app_key'] : '';
	if ( empty( $pushover_app_key ) ) {
		return;
	}

	$api_args['token']   = $pushover_app_key;
	$api_args['user']    = $pushover_key;
	$api_args['title']   = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
	$api_args['message'] = html_entity_decode( $message, ENT_QUOTES, 'UTF-8' );
	$api_args['sound']   = 'cashregister';

	$api_args = apply_filters( 'affwp_pushover_api_args', $api_args );

	// Crate the request
	$body = '';
	foreach ( $api_args as $key => $value ) {
		$body .= ( $body == '' ? '' : '&' );
		$body .= urlencode( $key ) . '=' . urlencode( $value );
	}

	$req_args = array( 'body' => $body );

	$req_args['sslverify'] = apply_filters( 'affwp_pushover_sslverify', true );
	$req_args['blocking']  = apply_filters( 'affwp_pushover_blocking', false );

	// Where the magic happens
	$response = wp_remote_post( 'https://api.pushover.net/1/messages.json', $req_args );

	return !is_wp_error( $response );
}
add_action( 'affwp_pushover_send_notification', 'affwp_pushover_send_notification', 10, 3 );
