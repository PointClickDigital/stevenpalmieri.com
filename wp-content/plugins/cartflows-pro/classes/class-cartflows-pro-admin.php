<?php
/**
 * Cartflows Admin.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Admin.
 */
class Cartflows_Pro_Admin {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

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
	 * Constructor
	 */
	public function __construct() {

		// Offer settings and saving.
		add_action( 'cartflows_after_general_settings', array( $this, 'render_offer_setting' ) );
		add_action( 'cartflows_admin_settings_save', array( $this, 'save_offer_settings' ) );

		add_action( 'cartflows_global_admin_scripts', array( $this, 'global_scripts' ) );
		add_action( 'cartflows_admin_meta_scripts', array( $this, 'meta_scripts' ) );
		add_filter( 'cartflows_licence_args', array( $this, 'licence_args' ) );
		add_action( 'cartflows_after_settings_fields', array( $this, 'add_settings_fields' ) );
		add_filter( 'cartflows_common_settings_default', array( $this, 'set_default_settings' ) );

		// Change String of Offer Item Meta.
		add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'change_order_item_meta_title' ), 20, 3 );

		// Hide Order Bump Metadata from the order list.
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'custom_woocommerce_hidden_order_itemmeta' ), 10, 1 );

		/* Add pro version class to body */
		add_action( 'admin_body_class', array( $this, 'add_admin_pro_body_class' ) );

		add_action( 'cartflows_step_left_content', array( $this, 'add_step_left_content' ), 10, 2 );

		add_filter( 'cartflows_admin_js_localize', array( $this, 'wcf_localize_tags' ) );

		add_filter( 'cartflows_google_analytics_settings_default', array( $this, 'add_google_analytics_pro_events' ) );

		add_action( 'cartflows_register_general_settings', array( $this, 'add_license_debug_metabox' ) );

		$this->include_refund_order_section();

		add_filter( 'get_user_option_meta-box-order_' . CARTFLOWS_STEP_POST_TYPE, array( $this, 'metabox_order' ) );
	}

	/**
	 * Metabox fixed orders.
	 *
	 * @param  array $pro_metabox_order Metabox orders.
	 * @return array
	 */
	public function metabox_order( $pro_metabox_order ) {

		if ( isset( $pro_metabox_order['side'] ) ) {

			$pro_metabox_order['side'] = str_replace(
				array(
					'wcf-offer-settings',
				),
				'',
				$pro_metabox_order['side']
			);
		}

		return $pro_metabox_order;
	}

	/**
	 * Renders the admin settings content.
	 *
	 * @since 1.5.5
	 *
	 * @return void
	 */
	public function render_offer_setting() {

		include_once CARTFLOWS_PRO_DIR . 'includes/admin/offer-settings.php';
	}

	/**
	 * Offer Global Setting options.
	 *
	 * @since 1.0.0
	 */
	public function save_offer_settings() {

		if ( isset( $_POST['cartflows-offer-global-settings-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cartflows-offer-global-settings-nonce'] ) ), 'cartflows-offer-global-settings' ) ) {

			$url          = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$new_settings = array();

			if ( isset( $_POST['_cartflows_offer_global_settings'] ) ) {
				// Loop through the input and sanitize each of the values.
				$new_settings = Cartflows_Pro_Admin_Helper::sanitize_form_inputs( wp_unslash( $_POST['_cartflows_offer_global_settings'] ) ); //phpcs:ignore
			}

			Cartflows_Helper::update_admin_settings_option( '_cartflows_offer_global_settings', $new_settings, false );

			$query = array(
				'message' => 'saved',
			);

			$redirect_to = add_query_arg( $query, $url );

			wp_safe_redirect( $redirect_to );
			exit;
		} // End if statement.
	}

	/**
	 * License arguments for Rest API Request.
	 *
	 * @param  array $defaults License arguments.
	 * @return array           License arguments.
	 */
	public function licence_args( $defaults ) {

		$data = get_option( 'wc_am_client_cartflows_api_key', array() );

		$licence_key = isset( $data['api_key'] ) ? esc_attr( $data['api_key'] ) : '';

		$args = array(
			'request'     => 'status',
			'product_id'  => CARTFLOWS_PRO_PRODUCT_TITLE,
			'instance'    => CartFlows_Pro_Licence::get_instance()->wc_am_instance_id,
			'object'      => CartFlows_Pro_Licence::get_instance()->wc_am_domain,
			'licence_key' => $licence_key,
		);

		return apply_filters( 'cartflows_pro_licence_args', wp_parse_args( $args, $defaults ) );
	}

	/**
	 * Redirect to thank page if upsell not exists
	 *
	 * Global Admin Styles.
	 *
	 * @since 1.0.0
	 */
	public function global_scripts() {
		// Styles.
		wp_enqueue_style( 'cartflows-pro-global-admin', CARTFLOWS_PRO_URL . 'admin/assets/css/global-admin.css', array(), CARTFLOWS_PRO_VER );
		// Script.
		wp_enqueue_script( 'cartflows-pro-global-admin', CARTFLOWS_PRO_URL . 'admin/assets/js/global-admin.js', array( 'jquery' ), CARTFLOWS_PRO_VER, false );

	}

	/**
	 * Redirect to thank page if upsell not exists
	 *
	 * Global Admin Scripts.
	 *
	 * @since 1.0.0
	 */
	public function meta_scripts() {

		wp_enqueue_style(
			'wcf-pro-admin-meta',
			CARTFLOWS_PRO_URL . 'admin/meta-assets/css/admin-edit.css',
			array(),
			CARTFLOWS_PRO_VER
		);

		wp_enqueue_script(
			'wcf-pro-admin-meta',
			CARTFLOWS_PRO_URL . 'admin/meta-assets/js/admin-edit.js',
			array( 'jquery' ),
			CARTFLOWS_PRO_VER,
			true
		);
	}

	/**
	 * Add setting fields in admin section
	 *
	 * @param array $settings settings array.
	 * @since 1.0.0
	 */
	public function add_settings_fields( $settings ) {

		if ( ! wcf_pro()->is_woo_active ) {
			return;
		}

		echo Cartflows_Admin_Fields::checkobox_field(
			array(
				'id'    => 'wcf_paypal_reference_transactions',
				'name'  => '_cartflows_common[paypal_reference_transactions]',
				'title' => __( 'Enable PayPal Reference Transactions', 'cartflows-pro' ),
				'value' => $settings['paypal_reference_transactions'],
			)
		);
	}

	/**
	 * Set default options for settings.
	 *
	 * @param array $settings settings data.
	 * @since 1.0.0
	 */
	public function set_default_settings( $settings ) {

		$settings['paypal_reference_transactions'] = 'disable';

		return $settings;
	}

	/**
	 * Hide order meta-data from order list backend.
	 *
	 * @param array $arr order meta data.
	 * @return array
	 * @since 1.0.0
	 */
	public function custom_woocommerce_hidden_order_itemmeta( $arr ) {
		$arr[] = '_cartflows_step_id';
		return $arr;
	}

	/**
	 * Changing a meta title
	 *
	 * @param  string        $key  The meta key.
	 * @param  WC_Meta_Data  $meta The meta object.
	 * @param  WC_Order_Item $item The order item object.
	 * @return string        The title.
	 */
	public function change_order_item_meta_title( $key, $meta, $item ) {

		if ( '_cartflows_upsell' === $meta->key ) {
			$key = __( 'Upsell Offer', 'cartflows-pro' );
		} elseif ( '_cartflows_downsell' === $meta->key ) {
			$key = __( 'Downsell Offer', 'cartflows-pro' );
		}

		return $key;
	}

	/**
	 * Admin body classes.
	 *
	 * Body classes to be added to <body> tag in admin page
	 *
	 * @param String $classes body classes returned from the filter.
	 * @return String body classes to be added to <body> tag in admin page
	 */
	public static function add_admin_pro_body_class( $classes ) {

		$classes .= ' cartflows-pro-' . CARTFLOWS_PRO_VER;

		return $classes;
	}

	/**
	 * Changing a meta title
	 *
	 * @param  int    $step_id  The step ID.
	 * @param  string $step_term_slug The step term slug.
	 */
	public function add_step_left_content( $step_id, $step_term_slug ) {

		$next_steps = '';
		$yes_step   = '';
		$no_step    = '';

		if ( 'upsell' === $step_term_slug || 'downsell' === $step_term_slug ) {

			$wcf_step_obj = wcf_pro_get_step( $step_id );
			$step_id      = intval( $step_id );
			$flow_id      = $wcf_step_obj->get_flow_id();
			$flow_steps   = $wcf_step_obj->get_flow_steps();
			$control_step = $wcf_step_obj->get_control_step();

			if ( 'upsell' === $step_term_slug ) {
				$next_yes_steps = wcf_pro()->flow->get_next_step_id_for_upsell_accepted( $wcf_step_obj, $flow_steps, $step_id, $control_step );
				$next_no_steps  = wcf_pro()->flow->get_next_step_id_for_upsell_rejected( $wcf_step_obj, $flow_steps, $step_id, $control_step );
			}

			if ( 'downsell' === $step_term_slug ) {
				$next_yes_steps = wcf_pro()->flow->get_next_step_id_for_downsell_accepted( $wcf_step_obj, $flow_steps, $step_id, $control_step );
				$next_no_steps  = wcf_pro()->flow->get_next_step_id_for_downsell_rejected( $wcf_step_obj, $flow_steps, $step_id, $control_step );
			}

			if ( false !== get_post_status( get_post_meta( $step_id, 'wcf-yes-next-step', true ) ) ) {

				$yes_step = get_post_meta( $step_id, 'wcf-yes-next-step', true );
			}

			if ( false !== get_post_status( get_post_meta( $step_id, 'wcf-yes-next-step', true ) ) ) {

				$no_step = get_post_meta( $step_id, 'wcf-no-next-step', true );
			}

			if ( ! empty( $next_yes_steps ) && false !== get_post_status( $next_yes_steps ) ) {

				$yes_label = __( 'YES : ', 'cartflows-pro' ) . get_the_title( $next_yes_steps );
			} else {
				$yes_label = __( 'YES : Step not Found', 'cartflows-pro' );
			}

			$next_steps = '<span class="wcf-flow-badge wcf-conditional-badge wcf-yes-next-badge" data-yes-step="' . $yes_step . '">' . $yes_label . '</span>';

			if ( ! empty( $next_no_steps ) && false !== get_post_status( $next_no_steps ) ) {

				$no_label = __( 'No : ', 'cartflows-pro' ) . get_the_title( $next_no_steps );
			} else {
				$no_label = __( 'No : Step not Found', 'cartflows-pro' );
			}

			$next_steps .= '<span class="wcf-flow-badge wcf-conditional-badge wcf-no-next-badge" data-no-step="' . $no_step . '">' . $no_label . '</span>';

			echo '<div class="wcf-badges">' . $next_steps . '</div>';

		}
	}

	/**
	 * Localize variables in admin.
	 *
	 * @param array $vars variables.
	 */
	public function wcf_localize_tags( $vars ) {

		$localize_tags = array(
			'add_yes_label'   => __( 'YES : ', 'cartflows-pro' ),
			'add_no_label'    => __( 'No : ', 'cartflows-pro' ),
			'not_found_label' => __( 'Step not Found', 'cartflows-pro' ),

		);

		$vars = array_merge( $vars, $localize_tags );

		return $vars;
	}

	/**
	 * Google anytics settings.
	 *
	 * @param array $google_analytics_settings_data settings.
	 */
	public function add_google_analytics_pro_events( $google_analytics_settings_data ) {

		$google_analytics_settings_data['enable_bump_order_add_to_cart'] = 'disable';

		return $google_analytics_settings_data;
	}

	/**
	 * License Debug Log.
	 */
	public function add_license_debug_metabox() {

		$debug_on = ( isset( $_GET['debug'] ) ) ? sanitize_text_field( wp_unslash( $_GET['debug'] ) ) : 'false'; //phpcs:ignore

		if ( 'true' === $debug_on ) {

			include CARTFLOWS_PRO_DIR . 'admin/views/html-license-debug-log.php';
		}
	}

	/**
	 * Offer refund.
	 */
	public function include_refund_order_section() {
		/* Refund */
		include_once CARTFLOWS_PRO_DIR . 'classes/class-cartflows-pro-refund.php';
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Admin' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Admin::get_instance();
