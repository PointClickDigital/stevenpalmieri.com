<?php

if (!defined('ABSPATH')) die();

function ds_ct_enqueue_parent() { wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' ); }

function ds_ct_loadjs() {

	wp_enqueue_script( 'ds-theme-script', get_stylesheet_directory_uri() . '/ds-script.js',

        array( 'jquery' )

    );

}


add_action( 'wp_enqueue_scripts', 'ds_ct_enqueue_parent' );

add_action( 'wp_enqueue_scripts', 'ds_ct_loadjs' );

add_action( 'admin_enqueue_scripts',function(){
	wp_dequeue_script( 'wcs-admin-meta-boxes-subscription' );
    wp_enqueue_script( 'admin-meta-boxes-subscription', get_stylesheet_directory_uri() . '/meta-boxes-subscription.js.js',

        array( 'jquery' )

    );
});

include('login-editor.php');



/** Require Terms and Conditions At Woocommerce Login */
 

add_action( 'woocommerce_login_form', 'pointclick_add_woo_login_terms_conditions', 20 );

function pointclick_add_woo_login_terms_conditions() {
?>
    <p>
	<input type="checkbox" value="1" class="input" id="my_extra_field" name="my_extra_field_name"/></label>
        <label for="my_extra_field">I've read and agree to the <a href="/terms-and-conditions">Terms and Conditions</a><br />
    </p>
<?php
}

function check_checkbox($user, $password)
{
    if( !isset($_POST['my_extra_field_name']) )
     {
        remove_action('authenticate', 'wp_authenticate_username_password', 20);
        $user = new WP_Error( 'denied', __("<strong>ERROR</strong>: Please agree to our terms.") );
    }

    return $user;
}
add_filter( 'wp_authenticate_user', 'check_checkbox', 10, 3 );

/** Require Terms and Conditions At Login */
 

add_action( 'login_form', 'pointclick_add_login_terms_conditions', 20 );

function pointclick_add_login_terms_conditions() {
?>
    <p>
	<input type="checkbox" value="1" class="extra.input" id="my_extra_field" name="my_extra_field_name"/></label>
        <label for="my_extra_field">I've read and agree to the <a href="/terms-and-conditions">Terms and Conditions</a><br />
    </p>
<?php
}

function check_checkbox_validation($user, $password)
{
    if( !isset($_POST['my_extra_field_name']) )
     {
        remove_action('authenticate', 'wp_authenticate_username_password', 20);
        $user = new WP_Error( 'denied', __("<strong>ERROR</strong>: Please agree to our terms.") );
    }

    return $user;
}
add_filter( 'wp_authenticate_user', 'check_checkbox_validation', 10, 3 );


/** Require Terms and Conditions At Woocommerce Registration */

add_action( 'woocommerce_register_form', 'add_terms_and_conditions_to_registration', 20 );
function add_terms_and_conditions_to_registration() {

    if ( wc_get_page_id( 'terms' ) > 0 && is_account_page() ) {
        ?>
        <p class="form-row terms wc-terms-and-conditions">
            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" <?php checked( apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['terms'] ) ), true ); ?> id="terms" /> <span><?php printf( __( 'I&rsquo;ve read and accept the <a href="%s" target="_blank" class="woocommerce-terms-and-conditions-link">terms &amp; conditions</a>', 'woocommerce' ), esc_url( wc_get_page_permalink( 'terms' ) ) ); ?></span> <span class="required">*</span>
            </label>
            <input type="hidden" name="terms-field" value="1" />
        </p>
    <?php
    }
}

// Validate required term and conditions check box
add_action( 'woocommerce_register_post', 'terms_and_conditions_validation', 20, 3 );
function terms_and_conditions_validation( $username, $email, $validation_errors ) {
    if ( ! isset( $_POST['terms'] ) )
        $validation_errors->add( 'terms_error', __( 'Terms and condition are not checked!', 'woocommerce' ) );

    return $validation_errors;
}



//default checkout state
add_filter( 'default_checkout_billing_state', 'change_default_checkout_state' );
add_filter( 'default_checkout_shipping_state', 'change_default_checkout_state' );
function change_default_checkout_state() {
    return ''; //set state code if you want to set it otherwise leave it blank.
}



//Change "Shipping" to "Shipping & Handling

add_filter( 'woocommerce_shipping_package_name', 'custom_shipping_package_name' );
function custom_shipping_package_name( $name ) {
    return 'Shipping & Handling';
}

// Change Variable Price Range 


function pcd_format_price_range( $price, $from, $to ) {
    return sprintf( '%s: %s', __( 'From', 'pcd' ), wc_price( $from ) );
} 
 
add_filter( 'woocommerce_format_price_range', 'pcd_format_price_range', 10, 3 );


/**
 * @snippet       WooCommerce User Registration Shortcode
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 4.0
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
   
add_shortcode( 'wc_reg_form_bbloomer', 'bbloomer_separate_registration_form' );
    
function bbloomer_separate_registration_form() {
   ob_start();
 
   // NOTE: THE FOLLOWING <FORM></FORM> IS COPIED FROM woocommerce\templates\myaccount\form-login.php
   // IF WOOCOMMERCE RELEASES AN UPDATE TO THAT TEMPLATE, YOU MUST CHANGE THIS ACCORDINGLY
 
   do_action( 'woocommerce_before_customer_login_form' );
 
   ?>
      <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
 
         <?php do_action( 'woocommerce_register_form_start' ); ?>
 
         <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
 
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
               <label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?> <span class="required">*</span></label>
               <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
            </p>
 
         <?php endif; ?>
 
         <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?> <span class="required">*</span></label>
            <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
         </p>
 
         <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
 
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
               <label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
               <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
            </p>
 
         <?php else : ?>
 
            <p><?php esc_html_e( 'A password will be sent to your email address.', 'woocommerce' ); ?></p>
 
         <?php endif; ?>
 
         <?php do_action( 'woocommerce_register_form' ); ?>
 
         <p class="woocommerce-FormRow form-row">
            <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
            <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Register', 'woocommerce' ); ?></button>
         </p>
 
         <?php do_action( 'woocommerce_register_form_end' ); ?>
 
      </form>
 
   <?php
     
   return ob_get_clean();
}
