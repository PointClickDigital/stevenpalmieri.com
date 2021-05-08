<?php
add_filter('mla_matrix_data_end', 'mla_aelia_convert', 99, 1);
function mla_aelia_convert($matrix_data) {

  if ( $matrix_data['args']['context'] == 'woocommerce' ) :

    $order_id = $matrix_data['args']['reference'];
    $order = new WC_Order($order_id);

    foreach ($matrix_data['referrals'] as $key => $referral_data) :

      $current_amount = $referral_data['referral_total'];

      $converted_total = apply_filters('wc_aelia_cs_convert', $current_amount, $order->get_currency(), affwp_get_currency());

      $matrix_data['referrals'][$key]['referral_total'] = $converted_total;
      array_push($matrix_data['referrals'][$key]['log'], 'Converted with Aelia: ' . $converted_total);

    endforeach;

  endif;

  return $matrix_data;

}