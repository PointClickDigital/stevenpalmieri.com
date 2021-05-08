<?php
// Run rules after auto grouping
// Auto grouping
add_action('affwp_g_after_auto_grouping', 'affwp_g_after_auto_grouping', 10, 1);
function affwp_g_after_auto_grouping($affiliate_id) {

    $user_id = affwp_get_affiliate_user_id($affiliate_id);

    affiliate_wp_group_switcher();

    require_once plugin_dir_path(__FILE__) . 'plugin_core/class-base.php';
    $gs_base = new AFFWP_GS_Base();

    // Run some rules
    // PMP level change
    if ( function_exists('pmpro_getMembershipLevelForUser') ) :
        $membership_level = pmpro_getMembershipLevelForUser($user_id);
        $level_id = $membership_level->ID;

        $gs_base->hooks->pmpro_after_change_membership_level($level_id, $user_id, TRUE);

        //affiliate_wp_group_switcher()->base->hooks->pmpro_after_change_membership_level($level_id, $user_id, TRUE);

    endif;

    // Woo Memberships - Is active member of a plan
    if ( class_exists('WC_Memberships') ) :

        $gs_base->hooks->affwp_insert_affiliate_woo_memberships_is_member($affiliate_id);

        //affiliate_wp_group_switcher()->base->hooks->affwp_insert_affiliate_woo_memberships_is_member($affiliate_id);

    endif;

}

?>