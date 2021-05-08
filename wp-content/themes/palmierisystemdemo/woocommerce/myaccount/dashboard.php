<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$allowed_html = array(
	'a' => array(
		'href' => array(),
	),
);
?>
<h2>
<?php
printf(
/* translators: 1: user display name 2: logout url */
wp_kses( __( 'Hey %1$s, you\'re back! ', 'woocommerce' ), $allowed_html ),
'<strong>' . esc_html( $current_user->display_name ) . '</strong>',
esc_url( wc_logout_url() )
);
?>
</h2>
<iframe width="560" height="315" src="https://www.youtube.com/embed/hnp1pt8biD4" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><h3>
<p>
	<?php
	/* translators: 1: Orders URL 2: Address URL 3: Account URL. */
	$dashboard_desc = __( 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">billing address</a>, and <a href="%3$s">edit your password and account details</a>.', 'woocommerce' );
	if ( wc_shipping_enabled() ) {
		/* translators: 1: Orders URL 2: Addresses URL 3: Account URL. */
		$dashboard_desc = __( 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">shipping and billing addresses</a>, and <a href="%3$s">edit your password and account details</a>.', 'woocommerce' );
	}
	printf(
		wp_kses( $dashboard_desc, $allowed_html ),
		esc_url( wc_get_endpoint_url( 'orders' ) ),
		esc_url( wc_get_endpoint_url( 'edit-address' ) ),
		esc_url( wc_get_endpoint_url( 'edit-account' ) )
	);
	?>
</p>
<h4>Access our community <a href="https://www.facebook.com/groups/creditrepairprelitigationdisputescommunity">Facebook Group</a> here.</h4>
<?php
$div_contact = __('
<div class="acc_contact">
<span class="acc_images" >
<a href="#link to send whatsapp message"><img src="http://localhost/Sampler/wp-content/uploads/2020/12/whatsapp-icon.png"/></a>
<a href="#link to facebook profile"><img src="http://localhost/Sampler/wp-content/uploads/2020/12/fcbk-icon.png"/></a>
<a href="#link to twitter profile"><img src="http://localhost/Sampler/wp-content/uploads/2020/12/twitter-icon.png"/></a>
<a href="#link to send email"><img src="http://localhost/Sampler/wp-content/uploads/2020/12/email-icon2.png"/></a>
</span>
</div>');
