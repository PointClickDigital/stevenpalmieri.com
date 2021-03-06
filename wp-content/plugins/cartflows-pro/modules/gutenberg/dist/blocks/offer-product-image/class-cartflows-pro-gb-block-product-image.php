<?php
/**
 * WCFPB - Offer Product Image.
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Gb_Block_Product_Image' ) ) {

	/**
	 * Class Cartflows_Pro_Gb_Block_Product_Image.
	 */
	class Cartflows_Pro_Gb_Block_Product_Image {

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

			// Activation hook.
			add_action( 'init', array( $this, 'register_blocks' ) );
		}

		/**
		 * Registers the `core/latest-posts` block on server.
		 *
		 * @since x.x.x
		 */
		public function register_blocks() {

			// Check if the register function exists.
			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}

			register_block_type(
				'wcfpb/offer-product-image',
				array(
					'attributes'      => array(
						'block_id'                   => array(
							'type' => 'string',
						),
						'classMigrate'               => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'className'                  => array(
							'type' => 'string',
						),
						// text alignment.
						'alignment'                  => array(
							'type'    => 'string',
							'default' => 'center',
						),
						// image bottom spacing.
						'image_bottom_spacing'       => array(
							'type' => 'number',
						),
						// margin.
						'topMargin'                  => array(
							'type'    => 'number',
							'default' => 0,
						),
						'bottomMargin'               => array(
							'type'    => 'number',
							'default' => 0,
						),
						// Image Border.
						'imageBorderStyle'           => array(
							'type'    => 'string',
							'default' => 'none',
						),
						'imageBorderWidth'           => array(
							'type' => 'number',
						),
						'imageBorderColor'           => array(
							'type' => 'string',
						),
						'imageBorderRadius'          => array(
							'type' => 'number',
						),
						// spacing between thumbnails.
						'spacing_between_thumbnails' => array(
							'type' => 'number',
						),
						// Thumbnail Border.
						'thumbnailBorderStyle'       => array(
							'type'    => 'string',
							'default' => 'none',
						),
						'thumbnailBorderWidth'       => array(
							'type' => 'number',
						),
						'thumbnailBorderColor'       => array(
							'type' => 'string',
						),
						'thumbnailBorderRadius'      => array(
							'type' => 'number',
						),

					),
					'render_callback' => array( $this, 'render_html' ),
				)
			);

		}

		/**
		 * Render Offer Product Image HTML.
		 *
		 * @param array $attributes Array of block attributes.
		 *
		 * @since x.x.x
		 */
		public function render_html( $attributes ) {

			$main_classes = array(
				'wp-block-wcfpb-offer-product-image',
				'cfp-block-' . $attributes['block_id'],
			);

			if ( isset( $attributes['className'] ) ) {
				$main_classes[] = $attributes['className'];
			}

			$classes = array(
				'wpcfp__offer-product-image',
			);

			ob_start();
			?>
				<div class = "<?php echo esc_attr( implode( ' ', $main_classes ) ); ?>">
					<div class = "<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
						<?php echo do_shortcode( '[cartflows_offer_product_image]' ); ?>
					</div>
				</div>
				<?php

				return ob_get_clean();
		}


	}

	/**
	 *  Prepare if class 'Cartflows_Pro_Gb_Block_Product_Image' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Gb_Block_Product_Image::get_instance();
}
