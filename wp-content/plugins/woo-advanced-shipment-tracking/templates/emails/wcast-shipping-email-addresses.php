<?php
/**
 * Email Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-addresses.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.5.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align = is_rtl() ? 'right' : 'left';
$address    = $order->get_formatted_billing_address();
$shipping   = $order->get_formatted_shipping_address();
if( !empty($shipping) ){ ?>
	<table id="addresses" cellspacing="0" cellpadding="0" style="width: 48%;display:inline-block;vertical-align: top; margin-bottom: 20px; padding:0;border: 0;margin-right: 15px;" border="0">
		<tr>				
			<td style="text-align:<?php echo esc_attr( $text_align ); ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; padding:0;" valign="top" width="50%">				
				<h2 style="padding-bottom: 5px;margin-bottom: 0;border:0;"><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></h2>
				<address class="address" style="padding: 0;border:0;margin: 5px 0 0 0;font-size: 14px;font-style: normal;vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;line-height: 20px;color: #4e4e4e;"><?php echo wp_kses_post( $shipping ); ?></address>			
			</td>		
		</tr>
	</table>
<?php } ?>
