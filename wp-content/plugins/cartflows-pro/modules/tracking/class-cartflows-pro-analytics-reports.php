<?php
/**
 * Flow
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Analytics reports class.
 */
class Cartflows_Pro_Analytics_Reports {

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 * Flow orders
	 *
	 * @var array flow_orders
	 */
	private static $flow_orders = array();

	/**
	 * Flow gross sell
	 *
	 * @var int flow_gross
	 */
	private static $flow_gross = 0;

	/**
	 * Flow visits
	 *
	 * @var array flow_visits
	 */
	private static $flow_visits = array();

	/**
	 * Steps data
	 *
	 * @var array step_data
	 */
	private static $step_data = array();

	/**
	 * Earnings for flow
	 *
	 * @var array flow_earnings
	 */
	private static $flow_earnings = array();

	/**
	 * Report interval
	 *
	 * @var int report_interval
	 */
	private static $report_interval = 30;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor function that initializes required actions and hooks
	 */
	public function __construct() {

		$this->load_analytics();
	}

	/**
	 *
	 * Analytics
	 *
	 * @return void
	 */
	public function load_analytics() {

		add_action( 'cartflows_add_flow_metabox', array( $this, 'add_analytics_metabox' ) );

		$flow_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;

		$analytics = wcf()->options->get_flow_meta_value( $flow_id, 'wcf-enable-analytics' );

		if ( 'no' === $analytics ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'load_analytics_scripts' ), 20 );
		add_action( 'wp_ajax_cartflows_set_visit_data', array( $this, 'set_visits_data' ) );
		add_action( 'admin_footer', array( $this, 'render_analytics_stat' ) );
		add_action( 'wp_ajax_wcf_reset_flow_analytics', array( $this, 'reset_flow_analytics' ) );
		add_filter( 'cartflows_admin_js_localize', array( $this, 'add_localize_vars_for_analytics' ), 10, 1 );
	}

	/**
	 *
	 * Localize variables for analytics.
	 *
	 * @param array $localize vars.
	 */
	public function add_localize_vars_for_analytics( $localize ) {
		$localize['wcf_reset_analytics_nonce']   = wp_create_nonce( 'wcf_reset_flow_analytics' );
		$localize['confirm_msg_for_analytics']   = __( 'Are you sure you want to reset the analytics for this flow?', 'cartflows-pro' );
		$localize['succesful_msg_for_analytics'] = __( 'Flow analytics data has been reset successfully.', 'cartflows-pro' );

		$flow_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;

		$localize['flow_id'] = $flow_id;

		return $localize;
	}

	/**
	 *
	 * Reset ANalytics.
	 *
	 * @return void
	 */
	public function reset_flow_analytics() {

		check_ajax_referer( 'wcf_reset_flow_analytics', 'security' );
		global $wpdb;
		$visit_db       = $wpdb->prefix . CARTFLOWS_PRO_VISITS_TABLE;
		$visits_meta_db = $wpdb->prefix . CARTFLOWS_PRO_VISITS_META_TABLE;
		$flow_id        = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;

		if ( 0 !== $flow_id ) {
			$get_steps = wcf()->flow->get_steps( $flow_id );
			$step_ids  = implode( ',', wp_list_pluck( $get_steps, 'id' ) );

			$wpdb->query( "DELETE FROM {$visit_db} WHERE step_id IN(" . $step_ids . ')' ); //phpcs:ignore

			$reset_date = current_time( 'Y-m-d H:i:s' );

			update_post_meta( $flow_id, 'wcf-analytics-reset-date', $reset_date );

			wp_send_json_success( true );
		}
		wp_send_json_error( false );
	}
	/**
	 *
	 * Add Analytics Metabox
	 *
	 * @return void
	 */
	public function add_analytics_metabox() {
		add_meta_box(
			'wcf-analytics-settings',                    // Id.
			__( 'Analytics', 'cartflows-pro' ), // Title.
			array( $this, 'analytics_metabox_markup' ),      // Callback.
			CARTFLOWS_FLOW_POST_TYPE,               // Post_type.
			'side',                               // Context.
			'high'                                  // Priority.
		);
	}

	/**
	 * Analytics Metabox Markup
	 *
	 * @param array $post post data.
	 * @return void
	 */
	public function analytics_metabox_markup( $post ) {
		?>
		<div class="wcf-flow-sandbox-table wcf-general-metabox-wrap widefat">
			<div class="wcf-flow-enable-analytics">
				<?php

					$meta_intance = Cartflows_Flow_Meta::get_instance();
					$meta_data    = $meta_intance->get_current_post_meta( $post->ID );
				foreach ( $meta_data as $key => $value ) {
					$options[ $key ] = $meta_data[ $key ]['default'];
				}

					echo wcf()->meta->get_checkbox_field(
						array(
							'name'  => 'wcf-enable-analytics',
							'value' => $options['wcf-enable-analytics'],
							'after' => esc_html__( 'Enable Flow Analytics', 'cartflows-pro' ),
						)
					);
				?>
			</div>
			<div class="wcf-flow-sandbox-table-container">
				<?php

					echo wcf()->meta->get_description_field(
						array(
							'name'    => 'wcf-analytics-note',
							'content' => esc_html__( 'Analytics offers data that helps you understand how your flows are performing.', 'cartflows-pro' ),
						)
					);
				?>
					<div class="button-wrap">
					<?php
						$this->setup_analytics_button();
						$this->reset_analytics_button();
					?>
					</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render analytics display button beside title.
	 */
	public function setup_analytics_button() {

		if ( ! Cartflows_Admin::is_flow_edit_admin() ) {
			return;
		}

		$reports_btn_markup          = '<style>.wrap{ position:relative;}</style>';
		$reports_btn_markup         .= "<div class='wcf-reports-button-wrap'>";
			$reports_btn_markup     .= "<button class='wcf-trigger-reports-popup button button-secondary'>";
				$reports_btn_markup .= esc_html__( 'View Analytics', 'cartflows-pro' );
			$reports_btn_markup     .= '</button>';
		$reports_btn_markup         .= '</div>';

		echo $reports_btn_markup;

	}

	/**
	 *
	 * Add Analytics reset button.
	 *
	 * @return void
	 */
	public function reset_analytics_button() {

		if ( ! Cartflows_Admin::is_flow_edit_admin() ) {
			return;
		}

		$reset_btn_markup          = '<style>.wrap{ position:relative;}</style>';
		$reset_btn_markup         .= "<div class='wcf-reset-button-wrap'>";
			$reset_btn_markup     .= "<button class='wcf-reset-analytics button button-secondary' id='wcf-reset-analytics-button' data-process='" . esc_html__( 'Resetting', 'cartflows-pro' ) . "'>";
				$reset_btn_markup .= esc_html__( 'Reset Analytics', 'cartflows-pro' );
			$reset_btn_markup     .= '</button>';
		$reset_btn_markup         .= '</div>';

		echo $reset_btn_markup;

	}

	/**
	 * Set visits data for later use in analytics.
	 */
	public function set_visits_data() {

		$flow_id  = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : 0;
		$earning  = $this->get_earnings( $flow_id );
		$visits   = $this->fetch_visits( $flow_id );
		$all_data = $this->visits_map( $flow_id, $visits, $earning );

		$response = array(
			'revenue'   => $earning,
			'all_steps' => $all_data,
		);

		wp_send_json_success( $response );
	}

	/**
	 * Display analytics stat table.
	 */
	public function render_analytics_stat() {

		if ( ! Cartflows_Admin::is_flow_edit_admin() ) {
			return;
		}

		$currency_symbol = '';

		if ( wcf_pro()->is_woo_active ) {

			if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
				$currency_symbol = get_woocommerce_currency_symbol();
			}
		}

		include CARTFLOWS_TRACKING_DIR . '/view/analytics-template.php';

		include CARTFLOWS_TRACKING_DIR . '/view/analytics-popup.php';
	}

	/**
	 * Visits map.
	 *
	 * @param int   $flow_id flow id.
	 * @param array $visits visits data.
	 * @param array $earning earning data.
	 * @return array
	 */
	public function visits_map( $flow_id, $visits, $earning ) {

		$visits_map = array();

		foreach ( $visits as $v_in => $v_data ) {

			$step_id                = $v_data->step_id;
			$v_data_array           = (array) $v_data;
			$visits_map[ $step_id ] = $v_data_array;
			$step_type              = wcf()->utils->get_step_type( $step_id );

			$visits_map[ $step_id ]['revenue']         = 0;
			$visits_map[ $step_id ]['title']           = get_the_title( $step_id );
			$visits_map[ $step_id ]['note']            = get_post_meta( $step_id, 'wcf-step-note', true );
			$visits_map[ $step_id ]['conversion_rate'] = 0;

			// Set conversion rate.
			$conversions  = intval( $v_data_array['conversions'] );
			$total_visits = intval( $v_data_array['total_visits'] );

			if ( $total_visits > 0 ) {

				$conversion_rate = $conversions / intval( $v_data_array['total_visits'] ) * 100;

				$visits_map[ $step_id ]['conversion_rate'] = number_format( (float) $conversion_rate, 2, '.', '' );
			}

			switch ( $step_type ) {

				case 'checkout':
					$visits_map[ $step_id ]['revenue'] = 0;

					if ( isset( $earning['checkout'][ $step_id ] ) ) {
						$visits_map[ $step_id ]['revenue'] = $earning['checkout'][ $step_id ];
					}
					break;
				case 'upsell':
				case 'downsell':
					$visits_map[ $step_id ]['revenue'] = 0;

					if ( isset( $earning['offer'][ $step_id ] ) ) {
						$visits_map[ $step_id ]['revenue'] = $earning['offer'][ $step_id ];
					}
					break;
			}

			$visits_map[ $step_id ]['revenue'] = number_format( (float) $visits_map[ $step_id ]['revenue'], 2, '.', '' );
		}

		$all_steps = wcf()->flow->get_steps( $flow_id );

		foreach ( $all_steps as $in => $step_data ) {

			$step_id = $step_data['id'];

			if ( isset( $visits_map[ $step_id ] ) ) {

				$all_steps[ $in ]['visits'] = $visits_map[ $step_id ];

				if ( isset( $step_data['ab-test'] ) ) {

					$ab_total_visits  = 0;
					$ab_unique_visits = 0;
					$ab_conversions   = 0;
					$ab_revenue       = 0;

					// If ab test true but ab test ui is off and variations are empty.
					if ( isset( $step_data['ab-test-variations'] ) && ! empty( $step_data['ab-test-variations'] ) ) {

						$variations = $step_data['ab-test-variations'];

						foreach ( $variations as $v_in => $v_data ) {

							$v_id = $v_data['id'];

							if ( isset( $visits_map[ $v_id ] ) ) {

								$all_steps[ $in ]['visits-ab'][ $v_id ] = $visits_map[ $v_id ];

								$ab_total_visits  = $ab_total_visits + intval( $visits_map[ $v_id ]['total_visits'] );
								$ab_unique_visits = $ab_unique_visits + intval( $visits_map[ $v_id ]['unique_visits'] );
								$ab_conversions   = $ab_conversions + intval( $visits_map[ $v_id ]['conversions'] );
								$ab_revenue       = $ab_revenue + $visits_map[ $v_id ]['revenue'];

							}
						}
					} else {
						$ab_total_visits  = $all_steps[ $in ]['visits']['total_visits'];
						$ab_unique_visits = $all_steps[ $in ]['visits']['unique_visits'];
						$ab_conversions   = $all_steps[ $in ]['visits']['conversions'];
						$ab_revenue       = $all_steps[ $in ]['visits']['revenue'];

						$all_steps[ $in ]['visits-ab'][ $step_id ] = $visits_map[ $step_id ];
					}

					if ( isset( $step_data['ab-test-archived-variations'] ) && ! empty( $step_data['ab-test-archived-variations'] ) ) {

						/* Add archived variations */
						$archived_variations = $step_data['ab-test-archived-variations'];

						foreach ( $archived_variations as $v_in => $v_data ) {

							$v_id = $v_data['id'];

							if ( isset( $visits_map[ $v_id ] ) ) {

								$all_steps[ $in ]['visits-ab-archived'][ $v_id ]          = $visits_map[ $v_id ];
								$all_steps[ $in ]['visits-ab-archived'][ $v_id ]['title'] = $v_data['title'];

								if ( $v_data['deleted'] ) {
									$all_steps[ $in ]['visits-ab-archived'][ $v_id ]['archived_date'] = '(Deleted on ' . $v_data['date'] . ')';
								} else {
									$all_steps[ $in ]['visits-ab-archived'][ $v_id ]['archived_date'] = '(Archived on ' . $v_data['date'] . ')';
								}

								$all_steps[ $in ]['visits-ab-archived'][ $v_id ]['note'] = isset( $v_data['note'] ) ? $v_data['note'] : '';

								$ab_total_visits  = $ab_total_visits + intval( $visits_map[ $v_id ]['total_visits'] );
								$ab_unique_visits = $ab_unique_visits + intval( $visits_map[ $v_id ]['unique_visits'] );
								$ab_conversions   = $ab_conversions + intval( $visits_map[ $v_id ]['conversions'] );
								$ab_revenue       = $ab_revenue + $visits_map[ $v_id ]['revenue'];
							}
						}
					}

					// Add total count to main step.
					$all_steps[ $in ]['visits']['total_visits']  = $ab_total_visits;
					$all_steps[ $in ]['visits']['unique_visits'] = $ab_unique_visits;
					$all_steps[ $in ]['visits']['conversions']   = $ab_conversions;
					$all_steps[ $in ]['visits']['revenue']       = number_format( (float) $ab_revenue, 2, '.', '' );

					// Calculate total conversion count and set to main step.
					$total_conversion_rate = 0;

					if ( $ab_total_visits > 0 ) {
						$total_conversion_rate = $ab_conversions / $ab_total_visits * 100;
						$total_conversion_rate = number_format( (float) $total_conversion_rate, 2, '.', '' );
					}

					$all_steps[ $in ]['visits']['conversion_rate'] = $total_conversion_rate;
				}
			}
		}

		return $all_steps;
	}

	/**
	 * Fetch total visits.
	 *
	 * @param integer $flow_id flow_id.
	 * @return array|object|null
	 */
	public function fetch_visits( $flow_id ) {

		global $wpdb;

		$visit_db      = $wpdb->prefix . CARTFLOWS_PRO_VISITS_TABLE;
		$visit_meta_db = $wpdb->prefix . CARTFLOWS_PRO_VISITS_META_TABLE;

		$start_date = filter_input( INPUT_POST, 'date_from', FILTER_SANITIZE_STRING );
		$end_date   = filter_input( INPUT_POST, 'date_to', FILTER_SANITIZE_STRING );
		$start_date = $start_date ? $start_date : date( 'Y-m-d' ); //phpcs:ignore
		$end_date   = $end_date ? $end_date : date( 'Y-m-d' ); //phpcs:ignore
		$start_date = date( 'Y-m-d H:i:s', strtotime( $start_date . '00:00:00' ) ); //phpcs:ignore
		$end_date   = date( 'Y-m-d H:i:s', strtotime( $end_date . '23:59:59' ) ); //phpcs:ignore

		// Need to look into date format later.
		$analytics_reset_date = wcf()->options->get_flow_meta_value( $flow_id, 'wcf-analytics-reset-date' );

		if ( $analytics_reset_date > $start_date ) {
			$start_date = $analytics_reset_date;
		}

		$steps     = wcf()->flow->get_steps( $flow_id );
		$all_steps = array();

		foreach ( $steps as $s_key => $s_data ) {

			if ( isset( $s_data['ab-test'] ) ) {

				if ( isset( $s_data['ab-test-variations'] ) && ! empty( $s_data['ab-test-variations'] ) ) {

					foreach ( $s_data['ab-test-variations'] as $v_key => $v_data ) {

						$all_steps[] = $v_data['id'];
					}
				} else {
					$all_steps[] = $s_data['id'];
				}

				if ( isset( $s_data['ab-test-archived-variations'] ) && ! empty( $s_data['ab-test-archived-variations'] ) ) {

					foreach ( $s_data['ab-test-archived-variations'] as $av_key => $av_data ) {

						$all_steps[] = $av_data['id'];
					}
				}
			} else {
				$all_steps[] = $s_data['id'];
			}
		}

		$step_ids = implode( ', ', $all_steps );

		// phpcs:disable
		$query = $wpdb->prepare(
			"SELECT step_id,
			 COUNT( DISTINCT( $visit_db.id ) ) AS total_visits,
			 COUNT( DISTINCT( CASE WHEN visit_type = 'new' 
			 THEN $visit_db.id ELSE NULL END ) ) AS unique_visits,
			 COUNT( CASE WHEN $visit_meta_db.meta_key = 'conversion' 
			 AND $visit_meta_db.meta_value = 'yes' 
			 THEN step_id ELSE NULL END ) AS conversions 
			 FROM $visit_db INNER JOIN $visit_meta_db ON $visit_db.id = $visit_meta_db.visit_id
			 WHERE step_id IN ( $step_ids ) 
			 AND ( date_visited BETWEEN %s AND %s ) 
			 GROUP BY step_id
			 ORDER BY NULL",//phpcs:ignore
			$start_date,
			$end_date
		);
		// phpcs:enable
		$visits = $wpdb->get_results( $query ); //phpcs:ignore

		$visited_steps     = wp_list_pluck( (array) $visits, 'step_id' );
		$non_visited_steps = array_diff( $all_steps, $visited_steps );

		// Non visited steps.
		if ( $non_visited_steps ) {

			$non_visit = array(
				'step_id'       => 0,
				'total_visits'  => 0,
				'unique_visits' => 0,
				'conversions'   => 0,
				'revenue'       => 0,
			);

			foreach ( $non_visited_steps as $non_visited_step ) {

				$non_visit['step_id'] = $non_visited_step;
				array_push( $visits, (object) $non_visit );

			}
		}

		$step_ids_array = wp_list_pluck( (array) $steps, 'id' );
		usort(
			$visits,
			function ( $a, $b ) use ( $all_steps ) {
				return array_search( intval( $a->step_id ), $all_steps, true ) - array_search( intval( $b->step_id ), $all_steps, true );

			}
		);

		// phpcs:enable
		return $visits;
	}

	/**
	 * Calculate earning.
	 *
	 * @param integer $flow_id flow_id.
	 * @return array
	 */
	public function get_earnings( $flow_id ) {

		$orders                   = $this->get_orders_by_flow( $flow_id );
		$gross_sale               = 0;
		$checkout_total           = 0;
		$avg_order_value          = 0;
		$total_bump_offer_earning = 0;
		$checkout_earnings        = array();
		$offer_earnings           = array();
		$order_count              = 0;

		if ( ! empty( $orders ) ) {

			foreach ( $orders as $order ) {

				$order_id    = $order->ID;
				$order       = wc_get_order( $order_id );
				$order_total = $order->get_total();
				if ( ! $order->has_status( 'cancelled' ) ) {
					$gross_sale    += (float) $order_total;
					$checkout_total = (float) $order_total;
				}
				$bump_product_id      = $order->get_meta( '_wcf_bump_product' );
				$bump_offer_earnings  = 0;
				$separate_offer_order = $order->get_meta( '_cartflows_parent_flow_id' );
				$checkout_id          = $order->get_meta( '_wcf_checkout_id' );

				if ( empty( $separate_offer_order ) ) {

					$order_count++;

					foreach ( $order->get_items() as $item_id => $item_data ) {

						$item_product_id = $item_data->get_product_id();
						$item_total      = $item_data->get_total();
						$is_upsell       = wc_get_order_item_meta( $item_id, '_cartflows_upsell', true );
						$is_downsell     = wc_get_order_item_meta( $item_id, '_cartflows_downsell', true );
						$offer_step_id   = wc_get_order_item_meta( $item_id, '_cartflows_step_id', true );

						if ( 'yes' === $is_upsell ) {
							$checkout_total -= $item_total;

							if ( ! isset( $offer_earnings[ $offer_step_id ] ) ) {
								$offer_earnings[ $offer_step_id ] = 0;
							}
							$offer_earnings[ $offer_step_id ] += number_format( (float) $item_total, 2, '.', '' );
						}

						if ( 'yes' === $is_downsell ) {
							$checkout_total -= $item_total;

							if ( ! isset( $offer_earnings[ $offer_step_id ] ) ) {
								$offer_earnings[ $offer_step_id ] = 0;
							}

							$offer_earnings[ $offer_step_id ] += number_format( (float) $item_total, 2, '.', '' );
						}

						if ( $item_product_id == $bump_product_id ) {
							$bump_offer_earnings += $item_total;
							$checkout_total      -= $item_total;
						}
					}
				} else {

					$is_offer      = $order->get_meta( '_cartflows_offer' );
					$offer_step_id = $order->get_meta( '_cartflows_offer_step_id', true );

					if ( 'yes' === $is_offer ) {
						$checkout_total -= $order_total;

						if ( ! isset( $offer_earnings[ $offer_step_id ] ) ) {
							$offer_earnings[ $offer_step_id ] = 0;
						}

						$offer_earnings[ $offer_step_id ] += number_format( (float) $order_total, 2, '.', '' );
					}
				}

				$total_bump_offer_earning += $bump_offer_earnings;

				if ( ! isset( $checkout_earnings[ $checkout_id ] ) ) {
					$checkout_earnings[ $checkout_id ] = 0;
				}

				$checkout_earnings[ $checkout_id ] = $checkout_earnings[ $checkout_id ] + $checkout_total;
			}

			if ( 0 !== $order_count ) {
				$avg_order_value = $gross_sale / $order_count;
			}
		}

		$all_earning_data = array(
			'avg_order_value' => number_format( (float) $avg_order_value, 2, '.', '' ),
			'gross_sale'      => number_format( (float) $gross_sale, 2, '.', '' ),
			'checkout_sale'   => number_format( (float) $checkout_total, 2, '.', '' ),
			'offer'           => $offer_earnings,
			'checkout'        => $checkout_earnings,
			'bump_offer'      => number_format( (float) $total_bump_offer_earning, 2, '.', '' ),
		);

		return $all_earning_data;
	}

	/**
	 * Load analytics scripts.
	 */
	public function load_analytics_scripts() {

		if ( Cartflows_Admin::is_flow_edit_admin() ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-style' );
			wp_enqueue_script( 'cartflows-analytics-admin', CARTFLOWS_TRACKING_URL . 'assets/js/analytics-admin.js', array( 'jquery' ), CARTFLOWS_VER, true );
		}
	}


	/**
	 * Prepare where items for query.
	 *
	 * @param array $conditions conditions to prepare WHERE query.
	 * @return string
	 */
	protected function get_items_query_where( $conditions ) {

		global $wpdb;

		$where_conditions = array();
		$where_values     = array();

		foreach ( $conditions as $key => $condition ) {

			if ( false !== stripos( $key, 'IN' ) ) {
				$where_conditions[] = $key . '( %s )';
			} else {
				$where_conditions[] = $key . '= %s';
			}

			$where_values[] = $condition;
		}

		if ( ! empty( $where_conditions ) ) {
			// @codingStandardsIgnoreStart
			return $wpdb->prepare( 'WHERE 1 = 1 AND ' . implode( ' AND ', $where_conditions ), $where_values );
			// @codingStandardsIgnoreEnd
		} else {
			return '';
		}
	}


	/**
	 * Get orders data for flow.
	 *
	 * @param int $flow_id flow id.
	 * @return int
	 */
	public function get_orders_by_flow( $flow_id ) {

		global $wpdb;

		$start_date = filter_input( INPUT_POST, 'date_from', FILTER_SANITIZE_STRING );
		$end_date   = filter_input( INPUT_POST, 'date_to', FILTER_SANITIZE_STRING );
		$start_date = $start_date ? $start_date : date( 'Y-m-d' ); //phpcs:ignore
		$end_date   = $end_date ? $end_date : date( 'Y-m-d' ); //phpcs:ignore
		$start_date = date( 'Y-m-d H:i:s', strtotime( $start_date . '00:00:00' ) ); //phpcs:ignore
		$end_date   = date( 'Y-m-d H:i:s', strtotime( $end_date . '23:59:59' ) ); //phpcs:ignore

		$analytics_reset_date = wcf()->options->get_flow_meta_value( $flow_id, 'wcf-analytics-reset-date' );

		if ( $analytics_reset_date > $start_date ) {
			$start_date = $analytics_reset_date;
		}

		$conditions = array(
			'tb1.post_type' => 'shop_order',
		);

		$where  = $this->get_items_query_where( $conditions );
		$where .= " AND ( tb1.post_date BETWEEN '" . $start_date . "' AND '" . $end_date . "' )";
		$where .= " AND ( ( tb2.meta_key = '_wcf_flow_id' AND tb2.meta_value = $flow_id ) OR ( tb2.meta_key = '_cartflows_parent_flow_id' AND tb2.meta_value = $flow_id ) )";
		$where .= " AND tb1.post_status IN ( 'wc-completed', 'wc-processing', 'wc-cancelled' )";

		$query = 'SELECT tb1.ID, DATE( tb1.post_date ) date FROM ' . $wpdb->prefix . 'posts tb1 
		INNER JOIN ' . $wpdb->prefix . 'postmeta tb2
		ON tb1.ID = tb2.post_id 
		' . $where;

		// @codingStandardsIgnoreStart
		$orders = $wpdb->get_results( $query );
		// @codingStandardsIgnoreEnd

		self::$flow_orders = $orders;

		return $orders;
	}
}

Cartflows_Pro_Analytics_Reports::get_instance();
