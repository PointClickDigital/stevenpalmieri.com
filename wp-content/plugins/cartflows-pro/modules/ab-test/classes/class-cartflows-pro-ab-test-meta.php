<?php
/**
 * Base Offer meta.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Meta Boxes setup
 */
class Cartflows_Pro_Ab_Test_Meta {


	/**
	 * Instance
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Meta Option
	 *
	 * @var $meta_option
	 */
	private static $meta_option = null;

	/**
	 * Template Type
	 *
	 * @var $template_type
	 */
	private static $template_type = null;

	/**
	 * Initiator
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

		add_filter( 'cartflows_step_actions', array( $this, 'add_ab_test_action' ), 10, 4 );
		add_filter( 'cartflows_admin_js_localize', array( $this, 'localize_vars' ) );

		/* Localized vars for CartFlows New UI */
		add_filter( 'cartflows_react_admin_localize', array( $this, 'localize_vars' ) );

		/* Ajax Calls */
		add_action( 'wp_ajax_cartflows_delete_ab_test_step', array( $this, 'delete_ab_test_variation' ) );
		add_action( 'wp_ajax_cartflows_archive_ab_test_step', array( $this, 'archive_ab_test_variation' ) );
		add_action( 'wp_ajax_cartflows_create_ab_test_variation', array( $this, 'create_ab_test_variation' ) );
		add_action( 'wp_ajax_cartflows_start_ab_test', array( $this, 'start_ab_test' ) );
		add_action( 'wp_ajax_cartflows_declare_ab_test_winner', array( $this, 'declare_ab_test_winner' ) );
		add_action( 'wp_ajax_cartflows_save_ab_test_settings', array( $this, 'save_ab_test_settings' ) );
		add_action( 'wp_ajax_cartflows_clone_ab_test_step', array( $this, 'clone_ab_test_variation' ) );

		add_action( 'wp_ajax_cartflows_restore_archive_ab_test_variation', array( $this, 'restore_archive_ab_test_variation' ) );
		add_action( 'wp_ajax_cartflows_delete_archive_ab_test_variation', array( $this, 'delete_archive_ab_test_variation' ) );

		/* Meta box */
		add_action( 'add_meta_boxes', array( $this, 'add_ab_test_step_note' ) );
		add_action( 'save_post', array( $this, 'wcf_save_note' ), 1, 2 );
	}

	/**
	 * Add metabox.
	 */
	public function add_ab_test_step_note() {

		if ( ! wcf()->utils->is_step_post_type() ) {
			return;
		}

		$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;

		$ab_test = get_post_meta( $post_id, 'wcf-ab-test', true );

		if ( $post_id && $ab_test ) {
			add_meta_box(
				'wcf-ab-test-step-note',                // Id.
				__( 'Step Note', 'cartflows-pro' ), // Title.
				array( $this, 'step_note_markup' ),      // Callback.
				wcf()->utils->get_step_post_type(),                 // Post_type.
				'side',                               // Context.
				'high'                                  // Priority.
			);
		}
	}

	/**
	 * Add text area.
	 *
	 * @param array $post post.
	 */
	public function step_note_markup( $post ) {

		$value = get_post_meta( $post->ID, 'wcf-step-note', true );
		?>
		<textarea name="wcf-step-note" style="width: 100%;"> <?php echo $value; ?></textarea>
		<?php
	}

	/**
	 * Save metabox.
	 *
	 * @param int   $id post id.
	 * @param array $post post.
	 */
	public function wcf_save_note( $id, $post ) {

		if ( isset( $_POST['wcf-step-note'] ) ) {

			$note = sanitize_text_field( wp_unslash( $_POST['wcf-step-note'] ) );

			update_post_meta( $id, 'wcf-step-note', $note );
		}
	}

	/**
	 * Add Ab test button to step actions
	 *
	 * @param array $actions Button actions.
	 * @param int   $step_id Current step id.
	 * @param bool  $ab_test_ui Is ab test enabled.
	 * @param array $args    Ab test args.
	 */
	public function add_ab_test_action( $actions, $step_id, $ab_test_ui, $args ) {

		$actions['ab-test'] = array(
			'link'    => '#',
			'class'   => 'wcf-step-abtest wcf-action-button wp-ui-text-highlight',
			'tooltip' => esc_html__( 'A/B Test', 'cartflows-pro' ),
			'icon'    => 'dashicons-forms',
			'label'   => esc_html__( 'A/B Test', 'cartflows-pro' ),
			'show'    => true,
			'attr'    => array(
				'data-id' => $step_id,
			),
		);

		$actions['ab-test-archive'] = array(
			'link'    => '#',
			'class'   => 'wcf-ab-test-step-archive wcf-action-button wp-ui-text-highlight',
			'tooltip' => esc_html__( 'Archive', 'cartflows-pro' ),
			'icon'    => 'dashicons-archive',
			'label'   => esc_html__( 'Archive', 'cartflows-pro' ),
			'show'    => true,
			'ab-test' => true,
			'attr'    => array(
				'data-id' => $step_id,
			),
		);

		$actions['ab-test-winner'] = array(
			'link'    => '#',
			'class'   => 'wcf-declare-winner wcf-action-button wp-ui-text-highlight',
			'tooltip' => esc_html__( 'Declare as Winner', 'cartflows-pro' ),
			'icon'    => 'dashicons-yes',
			'label'   => esc_html__( 'Declare as Winner', 'cartflows-pro' ),
			'show'    => true,
			'ab-test' => true,
			'attr'    => array(
				'data-id' => $step_id,
			),
		);

		if ( $ab_test_ui ) {

			$control_id = $args['control_id'];

			// Clone replace.
			$actions['clone']['class'] = str_replace( 'wcf-step-clone', 'wcf-ab-test-step-clone', $actions['clone']['class'] );
			$actions['clone']['link']  = '#';
			$actions['clone']['attr']  = array(
				'data-id'         => $step_id,
				'data-control-id' => $control_id,
			);

			// Delete replace.
			$actions['delete']['class'] = str_replace( 'wcf-step-delete', 'wcf-ab-test-step-delete', $actions['delete']['class'] );

			if ( $args['ab_test_variations_count'] < 3 ) {
				unset( $actions['delete'] );
				unset( $actions['ab-test-archive'] );
			}
		}

		return $actions;
	}

	/**
	 * Localize variables in admin
	 *
	 * @param array $vars variables.
	 */
	public function localize_vars( $vars ) {

		$ajax_actions = array(
			'wcf_create_ab_test_variation',
			'wcf_start_ab_test',
			'wcf_declare_ab_test_winner',
			'wcf_save_ab_test_settings',
			'wcf_delete_ab_test_step',
			'wcf_archive_ab_test_step',
			'wcf_clone_ab_test_step',
			'wcf_restore_archive_ab_test_variation',
			'wcf_delete_archive_ab_test_variation',
		);

		foreach ( $ajax_actions as $action ) {

			$vars[ $action . '_nonce' ] = wp_create_nonce( str_replace( '_', '-', $action ) );
		}

		return $vars;
	}

	/**
	 * Delete variation and archive it.
	 *
	 * @param int  $flow_id Flow id.
	 * @param int  $step_id Step id.
	 * @param bool $delete_data Delete step and it's meta.
	 */
	public function delete_variation( $flow_id, $step_id, $delete_data = false ) {

		$flow_steps   = get_post_meta( $flow_id, 'wcf-steps', true );
		$control_step = get_post_meta( $step_id, 'wcf-control-step', true );
		$control_step = empty( $control_step ) ? $step_id : intval( $control_step );

		foreach ( $flow_steps as $index => $data ) {

			if ( intval( $data['id'] ) === $control_step ) {

				$all_variations      = $flow_steps[ $index ]['ab-test-variations'];
				$archived_variations = $flow_steps[ $index ]['ab-test-archived-variations'];

				if ( ! is_array( $archived_variations ) ) {
					$archived_variations = array();
				}

				if ( $control_step === $step_id && isset( $all_variations[1] ) ) {

					$step_to_update = $all_variations[1]['id'];

					$flow_steps[ $index ] = array(
						'id'    => intval( $step_to_update ),
						'title' => get_the_title( $step_to_update ),
						'type'  => get_post_meta( $step_to_update, 'wcf-step-type', true ),
					);

					foreach ( $all_variations as $v_index => $v_data ) {

						if ( $step_to_update !== $v_data['id'] ) {
							update_post_meta( $v_data['id'], 'wcf-control-step', $step_to_update );
						}
					}

					delete_post_meta( $step_to_update, 'wcf-control-step' );
				}

				$current_time = current_time( 'Y-m-d H:i:s' );
				/* Add to archived list */
				$step_to_archive_data = array(
					'id'       => $step_id,
					'title'    => get_the_title( $step_id ),
					'note'     => get_post_meta( $step_id, 'wcf-step-note', true ),
					'deleted'  => $delete_data,
					'raw_date' => $current_time,
					'date'     => date( 'M d, Y', strtotime( $current_time ) ), //phpcs:ignore
				);

				if ( $delete_data ) {
					$archived_variations[] = $step_to_archive_data;
				} else {
					array_unshift( $archived_variations, $step_to_archive_data );
				}

				/* Delete the item from variation */
				if ( count( $all_variations ) > 1 ) {

					foreach ( $all_variations as $v_index => $v_data ) {

						if ( $step_id === $v_data['id'] ) {
							array_splice( $all_variations, $v_index, 1 );
						}
					}

					/* Update traffic after variation deleted */
					$traffic = array( 50, 50 );

					foreach ( $all_variations as $v_index => $v_data ) {

						if ( isset( $traffic[ $v_index ] ) ) {
							$all_variations[ $v_index ]['traffic'] = intval( $traffic[ $v_index ] );
						} else {
							$all_variations[ $v_index ]['traffic'] = 0;
						}
					}
				}

				$flow_steps[ $index ]['ab-test']                     = true;
				$flow_steps[ $index ]['ab-test-ui']                  = true;
				$flow_steps[ $index ]['ab-test-start']               = false;
				$flow_steps[ $index ]['ab-test-variations']          = $all_variations;
				$flow_steps[ $index ]['ab-test-archived-variations'] = $archived_variations;

				break;
			}
		}

		/* Set index order properly */
		$flow_steps = array_merge( $flow_steps );

		/* Update latest data */
		update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

		if ( $delete_data ) {
			/* Delete step */
			wp_delete_post( $step_id, true );
		}
	}

	/**
	 * Delete ab test delete.
	 *
	 * @return void
	 */
	public function delete_ab_test_variation() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-delete-ab-test-step', 'security' );

		if ( isset( $_POST['post_id'] ) && isset( $_POST['step_id'] ) ) {
			$flow_id = intval( $_POST['post_id'] );
			$step_id = intval( $_POST['step_id'] );
		}

		$result = array(
			'status' => false,
			'reload' => false,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Step not deleted for flow - %s', 'cartflows-pro' ), $flow_id ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		$this->delete_variation( $flow_id, $step_id, true );

		$result = array(
			'status' => true,
			'reload' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Step deleted for flow - %s', 'cartflows-pro' ), $flow_id ),
		);

		wp_send_json( $result );
	}

	/**
	 * Delete ab test delete.
	 *
	 * @return void
	 */
	public function archive_ab_test_variation() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-archive-ab-test-step', 'security' );

		if ( isset( $_POST['post_id'] ) && isset( $_POST['step_id'] ) ) {
			$flow_id = intval( $_POST['post_id'] );
			$step_id = intval( $_POST['step_id'] );
		}

		$result = array(
			'status' => false,
			'reload' => false,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Can\'t archive this step - %1$s. Flow - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		$this->delete_variation( $flow_id, $step_id, false );

		$result = array(
			'status' => true,
			'reload' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Step - %1$s archived. Flow - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
		);

		wp_send_json( $result );
	}

	/**
	 * Create variation for current step
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function create_ab_test_variation() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-create-ab-test-variation', 'security' );

		if ( isset( $_POST['flow_id'] ) && isset( $_POST['step_id'] ) ) {
			$flow_id = intval( $_POST['flow_id'] );
			$step_id = intval( $_POST['step_id'] );
		}
		$result = array(
			'status' => false,
			/* translators: %s step id */
			'text'   => sprintf( __( 'Can\'t create a variation for this step - %s', 'cartflows-pro' ), $step_id ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		/* Enable abtest for step */
		update_post_meta( $step_id, 'wcf-ab-test', true );

		// Step - Clone step as a variation.
		$new_step_id = wcf_pro()->utils->clone_step( $step_id );

		if ( $new_step_id ) {

			// Step - Add control step as parent.
			update_post_meta( $new_step_id, 'wcf-control-step', $step_id );

			// Flow - Add ab test variations array.
			$show_variations = array(
				array(
					'id'      => $step_id,
					'traffic' => 50,
				),
				array(
					'id'      => $new_step_id,
					'traffic' => 50,
				),
			);

			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

			foreach ( $flow_steps as $index => $step_data ) {

				if ( $step_data['id'] === $step_id ) {
					$flow_steps[ $index ]['ab-test']            = true;
					$flow_steps[ $index ]['ab-test-ui']         = true;
					$flow_steps[ $index ]['ab-test-variations'] = $show_variations;
					$flow_steps[ $index ]['ab-test-start']      = false;

					$archived_variations = array();

					if ( isset( $flow_steps[ $index ]['ab-test-archived-variations'] ) ) {
						$archived_variations = $flow_steps[ $index ]['ab-test-archived-variations'];
					}

					$flow_steps[ $index ]['ab-test-archived-variations'] = $archived_variations;
				}
			}

			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			$result = array(
				'status' => true,
				/* translators: %s flow id */
				'text'   => sprintf( __( 'Variation created for step - %s', 'cartflows-pro' ), $step_id ),
			);
		}

		wp_send_json( $result );
	}

	/**
	 * Start split test for current variation
	 *
	 * @since x.x.x
	 *
	 * @return void
	 */
	public function start_ab_test() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-start-ab-test', 'security' );

		if ( isset( $_POST['flow_id'] ) && isset( $_POST['step_id'] ) ) {
			$flow_id = intval( $_POST['flow_id'] );
			$step_id = intval( $_POST['step_id'] );
		}
		$result = array(
			'status' => false,
			'text'   => __( 'Can\'t start a split test', 'cartflows-pro' ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		$success_text = __( 'Stop Split Test', 'cartflows-pro' );
		$start_test   = true;
		$flow_steps   = get_post_meta( $flow_id, 'wcf-steps', true );

		foreach ( $flow_steps as $index => $step_data ) {

			if ( intval( $step_data['id'] ) === $step_id ) {

				if ( isset( $flow_steps[ $index ]['ab-test-start'] ) ) {

					if ( $flow_steps[ $index ]['ab-test-start'] ) {
						$flow_steps[ $index ]['ab-test-start'] = false;
						$start_test                            = false;
						$success_text                          = __( 'Start Split Test', 'cartflows-pro' );
					} else {
						$flow_steps[ $index ]['ab-test-start'] = true;
						$start_test                            = true;
					}
				} else {
					$flow_steps[ $index ]['ab-test-start'] = true;
					$start_test                            = true;
				}
			}
		}

		update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

		$result = array(
			'status' => true,
			'start'  => $start_test,
			'text'   => $success_text,
		);

		wp_send_json( $result );
	}

	/**
	 * Declare ab test winner
	 *
	 * @since x.x.x
	 *
	 * @return void
	 */
	public function declare_ab_test_winner() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-declare-ab-test-winner', 'security' );

		if ( isset( $_POST['flow_id'] ) && isset( $_POST['step_id'] ) ) {
			$flow_id = intval( $_POST['flow_id'] );
			$step_id = intval( $_POST['step_id'] );
		}

		$result = array(
			'status' => false,
			/* translators: %s step id */
			'text'   => sprintf( __( 'Can\'t update the winner for this step - %s', 'cartflows-pro' ), $step_id ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

		if ( ! is_array( $flow_steps ) ) {
			wp_send_json( $result );
		}

		$control_step = get_post_meta( $step_id, 'wcf-control-step', true );
		$control_step = empty( $control_step ) ? $step_id : intval( $control_step );

		foreach ( $flow_steps as $index => $data ) {

			if ( intval( $data['id'] ) === $control_step ) {

				$all_variations      = $flow_steps[ $index ]['ab-test-variations'];
				$archived_variations = $flow_steps[ $index ]['ab-test-archived-variations'];

				if ( ! is_array( $archived_variations ) ) {
					$archived_variations = array();
				}

				/* Update winner step */
				$flow_steps[ $index ] = array(
					'id'    => intval( $step_id ),
					'title' => get_the_title( $step_id ),
					'type'  => get_post_meta( $step_id, 'wcf-step-type', true ),
				);

				/* Remove step ab test trace */
				delete_post_meta( $step_id, 'wcf-ab-test' );
				delete_post_meta( $step_id, 'wcf-control-step' );

				/* Archive all rest of variation */
				$current_time = current_time( 'Y-m-d H:i:s' );

				/* Add rest of variation to archived list */
				foreach ( $all_variations as $v_index => $v_data ) {

					if ( $step_id !== $v_data['id'] ) {

						$step_to_archive_data = array(
							'id'       => $v_data['id'],
							'title'    => get_the_title( $v_data['id'] ),
							'note'     => get_post_meta( $v_data['id'], 'wcf-step-note', true ),
							'deleted'  => false,
							'raw_date' => $current_time,
							'date'     => date( 'M d, Y', strtotime( $current_time ) ), //phpcs:ignore
						);

						array_unshift( $archived_variations, $step_to_archive_data );
					}
				}

				/* Keep ab step data for analytics */
				$flow_steps[ $index ]['ab-test']                     = true;
				$flow_steps[ $index ]['ab-test-ui']                  = false;
				$flow_steps[ $index ]['ab-test-start']               = false;
				$flow_steps[ $index ]['ab-test-variations']          = array();
				$flow_steps[ $index ]['ab-test-archived-variations'] = $archived_variations;

				break;
			}
		}

		/* Set index order properly */
		$flow_steps = array_merge( $flow_steps );

		/* Update latest data */
		update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

		$result = array(
			'status' => true,
			/* translators: %s step id */
			'text'   => sprintf( __( 'Winner updated for this step - %s', 'cartflows-pro' ), $step_id ),
		);

		wp_send_json( $result );
	}

	/**
	 * Create variation for current step
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function clone_ab_test_variation() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-clone-ab-test-step', 'security' );

		if ( isset( $_POST['post_id'] ) && isset( $_POST['step_id'] ) && isset( $_POST['control_id'] ) ) {
			$flow_id    = intval( $_POST['post_id'] );
			$step_id    = intval( $_POST['step_id'] );
			$control_id = intval( $_POST['control_id'] );
		}

		$result = array(
			'status' => false,
			'reload' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Can\'t clone this step - %1$s. Flow - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
		);

		if ( ! $flow_id || ! $step_id || ! $control_id ) {
			wp_send_json( $result );
		}

		// Step - Clone step as a variation.
		$new_step_id = wcf_pro()->utils->clone_step( $step_id );

		if ( $new_step_id ) {

			// Step - Add control step as parent.
			update_post_meta( $new_step_id, 'wcf-control-step', $control_id );

			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

			foreach ( $flow_steps as $index => $step_data ) {

				if ( $step_data['id'] === $control_id ) {

					$variations = $flow_steps[ $index ]['ab-test-variations'];

					if ( ! is_array( $variations ) ) {
						$variations = array();
					}

					// Flow - Add ab test variations array.
					$variations[] = array(
						'id'      => $new_step_id,
						'traffic' => 0,
					);

					$flow_steps[ $index ]['ab-test-variations'] = $variations;
				}
			}

			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			$result = array(
				'status' => true,
				'reload' => true,
				/* translators: %s flow id */
				'text'   => sprintf( __( 'Step - %1$s archived. Flow - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
			);
		}

		wp_send_json( $result );
	}

	/**
	 * Save ab test settings.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function save_ab_test_settings() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-save-ab-test-settings', 'security' );

		if ( isset( $_POST['flow_id'] ) && isset( $_POST['step_id'] ) ) {
			$flow_id = intval( $_POST['flow_id'] );
			$step_id = intval( $_POST['step_id'] );
		}

		$result = array(
			'status' => false,
			/* translators: %s step id */
			'text'   => sprintf( __( 'Can\'t update A/B test settings for this step - %s', 'cartflows-pro' ), $step_id ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		$form_data = array();

		if ( isset( $_POST['form_data'] ) ) {

			$form_raw_data = sanitize_text_field( wp_unslash( $_POST['form_data'] ) );

			parse_str( $form_raw_data, $form_data );

			$form_data = wcf_clean( $form_data );
		}

		if ( ! empty( $form_data ) ) {

			$settings   = isset( $form_data['wcf_ab_settings'] ) ? $form_data['wcf_ab_settings'] : array();
			$traffic    = isset( $settings['traffic'] ) ? $settings['traffic'] : array();
			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

			foreach ( $flow_steps as $index => $step_data ) {

				if ( $step_data['id'] === $step_id ) {

					if ( isset( $step_data['ab-test-variations'] ) ) {

						$all_variations = $step_data['ab-test-variations'];

						foreach ( $all_variations as $var_key => $var_data ) {

							$var_id = intval( $var_data['id'] );

							if ( isset( $traffic[ $var_id ] ) && isset( $traffic[ $var_id ]['value'] ) ) {
								$all_variations[ $var_key ]['traffic'] = intval( $traffic[ $var_id ]['value'] );
							}
						}

						$flow_steps[ $index ]['ab-test-variations'] = $all_variations;
					}
				}
			}

			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			$result = array(
				'status' => true,
				/* translators: %s step id */
				'text'   => sprintf( __( 'A/B test settings updated for this step - %s', 'cartflows-pro' ), $step_id ),
			);
		}

		wp_send_json( $result );
	}

	/**
	 * Restore archived variation.
	 *
	 * @return void
	 */
	public function restore_archive_ab_test_variation() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-restore-archive-ab-test-variation', 'security' );

		if ( isset( $_POST['flow_id'] ) && isset( $_POST['step_id'] ) && isset( $_POST['control_id'] ) ) {
			$flow_id      = intval( $_POST['flow_id'] );
			$step_id      = intval( $_POST['step_id'] );
			$control_step = intval( $_POST['control_id'] );
		}

		$result = array(
			'status' => false,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Can\'t restore this variation - %1$s. Flow - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
		);

		if ( ! $flow_id || ! $step_id || ! $control_step ) {
			wp_send_json( $result );
		}

		$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

		// Update control id to step.
		update_post_meta( $step_id, 'wcf-control-step', $control_step );

		foreach ( $flow_steps as $index => $data ) {

			if ( intval( $data['id'] ) === $control_step ) {

				$all_variations      = $flow_steps[ $index ]['ab-test-variations'];
				$archived_variations = $flow_steps[ $index ]['ab-test-archived-variations'];

				/* Add to variation list list */
				$new_variation = array(
					'id'      => $step_id,
					'traffic' => 0,
				);

				$all_variations[] = $new_variation;

				/* Delete the item from archived list */
				foreach ( $archived_variations as $v_index => $v_data ) {

					if ( $step_id === $v_data['id'] ) {
						array_splice( $archived_variations, $v_index, 1 );
						break;
					}
				}

				$flow_steps[ $index ]['ab-test-variations']          = $all_variations;
				$flow_steps[ $index ]['ab-test-archived-variations'] = $archived_variations;

				break;
			}
		}

		/* Set index order properly */
		$flow_steps = array_merge( $flow_steps );

		/* Update latest data */
		update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

		$result = array(
			'status' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Variation - %1$s restored. Flow  - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
		);

		wp_send_json( $result );
	}

	/**
	 * Delete archived variation.
	 *
	 * @return void
	 */
	public function delete_archive_ab_test_variation() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-delete-archive-ab-test-variation', 'security' );

		if ( isset( $_POST['flow_id'] ) && isset( $_POST['step_id'] ) && isset( $_POST['control_id'] ) ) {
			$flow_id      = intval( $_POST['flow_id'] );
			$step_id      = intval( $_POST['step_id'] );
			$control_step = intval( $_POST['control_id'] );
		}

		$result = array(
			'status' => false,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Can\'t delete this variation - %1$s. Flow - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
		);

		if ( ! $flow_id || ! $step_id || ! $control_step ) {
			wp_send_json( $result );
		}

		$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

		foreach ( $flow_steps as $index => $data ) {

			if ( intval( $data['id'] ) === $control_step ) {

				$archived_variations = $flow_steps[ $index ]['ab-test-archived-variations'];

				/* Mark as deleted, update time in the archived list */
				foreach ( $archived_variations as $v_index => $v_data ) {

					if ( $step_id === $v_data['id'] ) {

						$archived_variations[ $v_index ]['deleted'] = true;

						$current_time = current_time( 'Y-m-d H:i:s' );

						$archived_variations[ $v_index ]['raw_date'] = $current_time;
						$archived_variations[ $v_index ]['date']     = date( 'M d, Y', strtotime( $current_time ) ); //phpcs:ignore
						break;
					}
				}

				$flow_steps[ $index ]['ab-test-archived-variations'] = $archived_variations;
				break;
			}
		}

		/* Set index order properly */
		$flow_steps = array_merge( $flow_steps );

		/* Update latest data */
		update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

		/* Delete step */
		wp_delete_post( $step_id, true );

		$result = array(
			'status' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Variation - %1$s deleted. Flow  - %2$s', 'cartflows-pro' ), $step_id, $flow_id ),
		);

		wp_send_json( $result );
	}
}


/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Ab_Test_Meta::get_instance();
