<?php

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;

add_action('affwp_portal_views_registry_init', 'affwp_mla_register_views');
function affwp_mla_register_views($registry_instance) {

  $sections = array(
    'mla-table' => array(
      'wrapper' => false,
      'priority' => 5,
      'columns' => array(
        'header' => 3,
        'content' => 3,
      ),
    )
  );

  $controls = array(

    new Controls\Wrapper_Control(array(
//    'id' => 'affwp-mla-table', // Wrapper_Control IDs get hard-coded by core, so you can skip it here.
      'view_id' => 'mla-tab-1', // View ID had incorrect value: affwp-mla-table
      'section' => 'wrapper',
    )),

    new Controls\Table_Control(array(
      'id' => 'affwp-mla-table', // Control IDs have to be globally unique. Formerly was also used for your Wrapper_Control.
      'view_id' => 'mla-tab-1', // View ID had incorrect value: affwp-mla-table
      'section' => 'mla-table', // Section ID had incorrect value: affwp-mla-table
      'args' => array(
        'schema' => array(
          'table_name' => 'affwp-mla-table',
          'page_count_callback' => function ($args) {
            $number = isset($args['number']) ? $args['number'] : 20;

            $count = affiliate_wp()->affiliates->payouts->count(array(
              'affiliate_id' => $args['affiliate_id'],
              'status' => array('processing', 'paid'),
            ));

            return absint(ceil($count / $number));
          },
          'data_callback' => function ($args) {
            $args['status'] = array('processing', 'paid');

            return affiliate_wp()->affiliates->payouts->get_payouts($args);
          },
          'schema' => array(
            'date' => array(
              'title' => __('Date', 'affiliatewp-affiliate-portal'),
              'priority' => 5,
              'cell' => function (\AffWP\Affiliate\Payout $row, $table_control_id) {
                return new Controls\Text_Control(array(
                  'id' => "{$table_control_id}_date",
                  'args' => array(
                    'text' => $row->date_i18n()
                  ),
                ));
              },
            ),
            'test' => array(
              'title' => __('Test', 'affiliatewp-affiliate-portal'),
              'priority' => 7,
              'cell' => function ($row, $table_control_id) {
                return new Controls\Text_Control(array(
                  'id' => "{$table_control_id}_test",
                  'args' => array(
                    'text' => ""
                  ),
                ));
              },
            ),
            'amount' => array(
              'title' => __('Amount', 'affiliatewp-affiliate-portal'),
              'priority' => 10,
              'cell' => function (\AffWP\Affiliate\Payout $row, $table_control_id) {
                return new Controls\Text_Control(array(
                  'id' => "{$table_control_id}_amount",
                  'args' => array(
                    'text' => affwp_currency_filter(affwp_format_amount($row->amount))
                  ),
                ));
              },
            ),
            'payout_method' => array(
              'title' => __('Payout Method', 'affiliatewp-affiliate-portal'),
              'priority' => 15,
              'cell' => function (\AffWP\Affiliate\Payout $row, $table_control_id) {
                $payout_method = $row->payout_method;

                return new Controls\Text_Control(array(
                  'id' => "{$table_control_id}_payout_method",
                  'args' => array(
                    'text' => affwp_get_payout_method_label($payout_method),
                  ),
                ));
              },
            ),
            'status' => array(
              'title' => __('Status', 'affiliatewp-affiliate-portal'),
              'priority' => 20,
              'cell' => function (\AffWP\Affiliate\Payout $row, $table_control_id) {
                switch ($row->status) {
                  case 'paid':
                    $type = 'approved';
                    break;
                  case 'failed':
                    $type = 'rejected';
                    break;
                  default:
                    $type = 'pending';
                }

                return new Controls\Status_Control(array(
                  'id' => "{$table_control_id}_status",
                  'args' => array(
                    'type' => $type,
                    'label' => affwp_get_payout_status_label($row->status),
                  ),
                ));
              },
            ),
          ),
        ),
      ),
    )),

  );

  $registry_instance->register_view('mla-tab-1', array(
    'label' => __('MLA Tab 1', 'affiliatewp-affiliate-portal'),
    'icon' => 'cash',
    'sections' => $sections,
    'controls' => $controls,
  ));

}