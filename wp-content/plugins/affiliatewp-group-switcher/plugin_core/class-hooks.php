<?php

class AFFWP_GS_Hooks extends AFFWP_GS_Common {

  public function __construct() {

    parent::__construct();

    // Scripts & Styles
    //add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

    // Scripts & Styles Admin
    add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_admin'));

    /////// Triggers ///////

    // Referral Status Change. Used for referral count and earnings triggers
    add_action('affwp_set_referral_status', array($this, 'affwp_set_referral_status'), 10, 2);

    // Sub affiliate count

    // PMP Membership Level Change
    // After level change
    add_action('pmpro_after_change_membership_level', array($this, 'pmpro_after_change_membership_level'), 10, 2);

    // WooCommerce
    add_action('woocommerce_payment_complete', array($this, 'woocommerce_payment_complete'));

    // EDD purchase
    add_action('edd_complete_purchase', array($this, 'edd_complete_purchase'));

    // WooCommerce Memberships
    // Fired whenever a memebership is saved (used for new memberships only)
    add_action('wc_memberships_user_membership_saved', array($this, 'wc_memberships_user_membership_saved'), 10, 2);
    // Fired whenever a memebership status is updated
    add_action('wc_memberships_user_membership_status_changed', array($this, 'wc_memberships_user_membership_status_changed'), 10, 3);

  }

  // Add the required scripts and styles
  public function enqueue_scripts() {
    //wp_enqueue_style( 'affwp-group-switcher-css', plugin_dir_url(__FILE__) . 'includes/css/cs_plugin.css' );
  }

  // Add the required scripts and styles - Admin
  public function enqueue_scripts_admin() {

    // Group Switcher settings page
    if ( $this->is_settings_page() ) :
      wp_enqueue_script('affwp-gs-jquery', '//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js');
      wp_enqueue_script('affwp-gs-settings', plugin_dir_url(__FILE__) . 'includes/js/settings_page.js');

      wp_enqueue_style('affwp-gs-css', plugin_dir_url(__FILE__) . 'includes/css/cs_plugin.css');
    endif;

  }

  // Get all rules or for certain triggers
  public function get_the_rules($triggers = array()) {

    $rules = $this->plugin_setting('gs_rules');

    if ( !empty($triggers) ) :

      foreach ($rules as $key => $rule_vars) :

        if ( !in_array($rule_vars['trigger'], $triggers) ) :

          unset($rules[$key]);

        endif;

      endforeach;

    endif;

    return $rules;
  }

  // Process the affiliate's group/s change
  public function switch_affiliates_groups($vars) {

    if ( $vars['mode'] == 'reset' ) {

      reset_affiliate_groups($vars['affiliate_id'], (array)$vars['groups']);

    } elseif ( $vars['mode'] == 'add' ) {

      add_affiliate_to_group($vars['affiliate_id'], $vars['groups']);

    } elseif ( $vars['mode'] == 'remove' ) {

      remove_affiliate_from_group($vars['affiliate_id'], $vars['groups']);

    } elseif ( $vars['mode'] == 'remove_all' ) {

      reset_affiliate_groups($vars['affiliate_id'], array());

    }

  }

  ////////// Triggers ////////////

  // Referral Status Change
  public function affwp_set_referral_status($referral_id, $new_status) {

    // Get the affiliate
    $referral = affwp_get_referral($referral_id);
    $affiliate_id = $referral->affiliate_id;

    if ( !empty($affiliate_id) ) :

      // Group all like triggers
      $referral_tier_rules = $this->get_the_rules(array('referrals_tier'));

      if ( !empty($referral_tier_rules) ) :

        ///// Process the referral tiers /////

        usort($referral_tier_rules, function ($a, $b) {
          return $a['trigger_value_referrals'] - $b['trigger_value_referrals'];
        });

        // Get the affiliate's paid referral count
        $affiliate_paid_referrals = affwp_get_affiliate_referral_count($affiliate_id);

        foreach ($referral_tier_rules as $key => $referral_tier) {

          $trigger_value_referrals = $referral_tier['trigger_value_referrals'];

          if ( !empty($trigger_value_referrals) && $affiliate_paid_referrals >= $trigger_value_referrals ) :

            $referral_tier_groups['mode'] = $referral_tier['switcher_mode'];
            $referral_tier_groups['groups'] = $referral_tier['affiliate_groups'];

          endif;

        }

        if ( !empty($referral_tier_groups) ) :

          $switch_vars = array(
            'mode' => $referral_tier_groups['mode'],
            'affiliate_id' => $affiliate_id,
            'groups' => $referral_tier_groups['groups']
          );

          $this->switch_affiliates_groups($switch_vars);

        endif;

      endif;


      ///// End process the referral tiers /////

      ///// Process the earnings tiers /////

      $earnings_tier_rules = $this->get_the_rules(array('earnings_tier'));

      if ( !empty($earnings_tier_rules) ) :

        usort($earnings_tier_rules, function ($a, $b) {
          return $a['trigger_value_earnings'] - $b['trigger_value_earnings'];
        });

        // Get the affiliate's paid earnings
        $affiliate_paid_earnings = affwp_get_affiliate_earnings($affiliate_id);

        foreach ($earnings_tier_rules as $key => $earnings_tier) {

          $trigger_value_earnings = $earnings_tier['trigger_value_earnings'];

          if ( !empty($trigger_value_earnings) && $affiliate_paid_earnings >= $trigger_value_earnings ) :

            $earnings_tier_groups['mode'] = $earnings_tier['switcher_mode'];
            $earnings_tier_groups['groups'] = $earnings_tier['affiliate_groups'];

          endif;

        }

        if ( !empty($earnings_tier_groups) ) :

          $switch_vars = array(
            'mode' => $earnings_tier_groups['mode'],
            'affiliate_id' => $affiliate_id,
            'groups' => $earnings_tier_groups['groups']
          );

          $this->switch_affiliates_groups($switch_vars);

        endif;

      endif;

      ///// End process the earnings tiers /////

    endif;

  }

  // PMPRO Memberhip Level Change
  public function pmpro_after_change_membership_level($level_id, $user_id, $is_affiliate_registration = FALSE) {

    $affiliate_id = affwp_get_affiliate_id($user_id);

    if ( !empty($affiliate_id) ) :

      $rules = $this->get_the_rules(array('pmp_level_change'));

      if ( !empty($rules) ) :

        foreach ($rules as $key => $rule_vars) :

          $run_rule = TRUE;
          // Switch off rules if on registration and not allowed
          if ( $is_affiliate_registration && !$rule_vars['run_after_auto_enrol'] ) :
            $run_rule = FALSE;
          endif;

          if ( $level_id == $rule_vars['trigger_value_pmp_level'] && $run_rule ) :

            $switch_vars = array(
              'mode' => $rule_vars['switcher_mode'],
              'affiliate_id' => $affiliate_id,
              'groups' => $rule_vars['affiliate_groups']
            );

            $this->switch_affiliates_groups($switch_vars);

          endif;

        endforeach;

      endif;

    endif;

  }

  // WC Memberships Membership Created
  // $wc_memberships_membership_plan_object is a 'WC_Memberships_Membership_Plan' object
  public function wc_memberships_user_membership_saved($wc_memberships_membership_plan_object, $args) {

    $vars['user_id'] = $args['user_id'];
    $vars['plan_id'] = $wc_memberships_membership_plan_object->id;
    $vars['plan_status'] = 'active';
    $vars['context'] = 'saved';

    // Only if new membership otherwise the status changed hook fires to cover the trigger
    if ( !$args['is_update'] ) :
      $this->wc_memberships_status_change($vars);
    endif;
  }

  // WC Memberships Membership Status changed
  // $wc_membership_user_membership_object is a 'WC_Memberships_User_Membership' object
  public function wc_memberships_user_membership_status_changed($wc_membership_user_membership_object, $old_status, $new_status) {

    $vars['user_id'] = $wc_membership_user_membership_object->get_user_id();
    $vars['plan_id'] = $wc_membership_user_membership_object->get_plan_id();
    $vars['plan_status'] = $new_status;
    $vars['context'] = 'updated';

    $this->wc_memberships_status_change($vars);
  }

  // WC Memberships Membership Status rules
  //$user_membership is a 'WC_Memberships_User_Membership' object
  public function wc_memberships_status_change($vars) {

    $user_id = $vars['user_id'];
    $plan_id = $vars['plan_id'];
    $new_status = $vars['plan_status'];

    $affiliate_id = affwp_get_affiliate_id($user_id);

    if ( !empty($affiliate_id) ) :

      $rules = $this->get_the_rules(array('wcm_membership_status'));
      if ( !empty($rules) ) :

        foreach ($rules as $key => $rule_vars) :

          $run_rule = TRUE;
          $rule_plan = $rule_vars['trigger_value_wcm_membership_plan'];

          if ( ($plan_id == 'any' || $plan_id == $rule_plan) && $run_rule ) :

            $rule_status = $rule_vars['trigger_value_wcm_membership_status'];

            if (
              $rule_status == $new_status // Status match
              || ($rule_status == 'not_active' && $new_status != 'active') // Bulk Option - Not active
              || ($rule_status == 'not_expired_or_cancelled' && $new_status != 'expired' && $new_status != 'cancelled') // Bulk Option - Not Expired or Cancelled
              || ($rule_status == 'expired_or_cancelled' && ($new_status == 'expired' || $new_status == 'cancelled')) // Bulk Option - Expired or Cancelled
            ) :

              $switch_vars = array(
                'mode' => $rule_vars['switcher_mode'],
                'affiliate_id' => $affiliate_id,
                'groups' => $rule_vars['affiliate_groups']
              );

              $this->switch_affiliates_groups($switch_vars);

            endif;

          endif;

        endforeach;

      endif;

    endif;

  }

  // Woocommerce purchase
  public function woocommerce_payment_complete($order_id) {

    // Get the order variables
    $order = new WC_Order($order_id);
    $user_id = $order->get_user_id();
    $affiliate_id = affwp_get_affiliate_id($user_id);

    if ( !empty($affiliate_id) ) :

      $rules = $this->get_the_rules(array('woo_product_purchased'));

      if ( !empty($rules) ) :

        $order_items = $order->get_items();

        update_option('switcher', $order_items);

        foreach ($rules as $key => $rule_vars) :

          $trigger_product_id = $rule_vars['trigger_value_woo_product'];

          foreach ($order_items as $product) :

            //$trigger_product_id = $rule_vars['trigger_value_woo_product'];

            $product_id = $product['product_id'];
            if ( isset($product['variation_id']) ) $variation_id = $product['variation_id'];

            // Variation ordered. The trigger product can be the variation or the parent
            if ( isset($variation_id) ) :

              if ( $variation_id == $trigger_product_id || $product_id == $trigger_product_id ) :

                $switch_vars = array(
                  'mode' => $rule_vars['switcher_mode'],
                  'affiliate_id' => $affiliate_id,
                  'groups' => $rule_vars['affiliate_groups']
                );

                $this->switch_affiliates_groups($switch_vars);

              endif;

            else:

              // Simple product ordered. The trigger product must match the product ordered
              if ( $product_id == $trigger_product_id ) :

                $switch_vars = array(
                  'mode' => $rule_vars['switcher_mode'],
                  'affiliate_id' => $affiliate_id,
                  'groups' => $rule_vars['affiliate_groups']
                );

                $this->switch_affiliates_groups($switch_vars);

              endif;

            endif;

          endforeach;

        endforeach;

      endif;

    endif;

  }

  // Edd download purchased
  public function edd_complete_purchase($payment_id) {

    $payment = new EDD_Payment($payment_id);
    $user_id = $payment->user_id;
    $affiliate_id = affwp_get_affiliate_id($user_id);

    if ( !empty($affiliate_id) ) :

      $rules = $this->get_the_rules(array('edd_product_purchased'));

      if ( !empty($rules) ) :

        foreach ($rules as $key => $rule_vars) :

          $trigger_product_id = $rule_vars['trigger_value_edd_product'];
          $cart_items = $payment->cart_details;

          foreach ($cart_items as $key => $cart_item) :

            $item_id = $cart_item['id'];

            if ( $item_id == $trigger_product_id ) :

              $switch_vars = array(
                'mode' => $rule_vars['switcher_mode'],
                'affiliate_id' => $affiliate_id,
                'groups' => $rule_vars['affiliate_groups']
              );

              $this->switch_affiliates_groups($switch_vars);

            endif;

          endforeach;

        endforeach;

      endif;

    endif;

  }

  // Registering affiliate - Check if active member of a Woo Membership plan
  public function affwp_insert_affiliate_woo_memberships_is_member($affiliate_id) {

    $rules = $this->get_the_rules(array('wcm_membership_is_member'));

    //update_option( 'gs_test_0', $rules );

    if ( !empty($rules) ) :

      foreach ($rules as $key => $rule_vars) :

        $rule_plan = $rule_vars['trigger_value_wcm_membership_plan'];
        $user_id = affwp_get_affiliate_user_id($affiliate_id);

        //update_option( 'gs_test_1', wc_memberships_is_user_active_member( $user_id, $rule_plan ) );

        if ( wc_memberships_is_user_active_member($user_id, $rule_plan) ) :


          $switch_vars = array(
            'mode' => $rule_vars['switcher_mode'],
            'affiliate_id' => $affiliate_id,
            'groups' => $rule_vars['affiliate_groups']
          );

          $this->switch_affiliates_groups($switch_vars);

        endif;

      endforeach;

    endif;

  }


}  // End of class

?>