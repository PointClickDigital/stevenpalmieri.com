<?php
/**
 *  Login screen changes.
 *
 * @package white-label
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'login_enqueue_scripts', 'white_label_login_styles' );

/**
 * Add White Label CSS to login page.
 *
 * @return void
 */
function white_label_login_styles() {

	$login_logo_file        = white_label_get_option( 'login_logo_file', 'white_label_login', false );
	$login_background_file  = white_label_get_option( 'login_background_file', 'white_label_login', false );
	$login_background_color = white_label_get_option( 'login_background_color', 'white_label_login', '#f1f1f1' );

	$login_box_background_color    = white_label_get_option( 'login_box_background_color', 'white_label_login', '#fff' );
	$login_box_text_color          = white_label_get_option( 'login_box_text_color', 'white_label_login', '#444' );
	$login_text_color              = white_label_get_option( 'login_text_color', 'white_label_login', '#555d66' );
	$login_button_background_color = white_label_get_option( 'login_button_background_color', 'white_label_login', '#007cba' );
	$login_button_font_color       = white_label_get_option( 'login_button_font_color', 'white_label_login', '#fff' );

	$login_page_template = white_label_get_option( 'login_page_template', 'white_label_login', false );

	if ( $login_page_template === 'left' ) {
		$template_css = "
		div#login {
			background: $login_box_background_color;
			height: 100%;
			padding-left: 10%;
			padding-right: 10%;
		}
		.interim-login #login {
			padding: 15px;
		}
		@media only screen and (min-width: 650px) {
			body #login {
			background: $login_box_background_color;
			padding: 8% 60px 10px 50px;
			float: left;
			height: 100%;
			position: fixed;
			-webkit-box-shadow: 0px 0px 10px 10px rgba(0,0,0,0.35);
			-moz-box-shadow: 0px 0px 10px 10px rgba(0,0,0,0.35);
			box-shadow: 0px 0px 10px 10px rgba(0,0,0,0.35);
		}
	}";
	} elseif ( $login_page_template === 'right' ) {
		$template_css = "
		div#login {
			background: $login_box_background_color;
			height: 100%;
			padding-left: 10%;
			padding-right: 10%;
		}
		.interim-login #login {
			padding: 15px;
		}
		@media only screen and (min-width: 650px) {
		body #login {
			background: $login_box_background_color;
			padding: 8% 60px 10px 50px;
			float: right;
			right:0;
			height: 100%;
			position: fixed;
			-webkit-box-shadow:0px 0px 10px 10px rgba(0,0,0,0.35);
			-moz-box-shadow: 0px 0px 10px 10px rgba(0,0,0,0.35);
			box-shadow: 0px 0px 10px 10px rgba(0,0,0,0.35);
		}
	}";
	} else {
		$template_css = '';
	}

	?>
<style type="text/css">
/* Login Right Template */
	<?php echo $template_css; ?>

/* Login page background */
body.login {
	background: <?php echo esc_html( $login_background_color ); ?>;
	background-image: url(<?php echo esc_html( $login_background_file ); ?>);
	background-repeat: no-repeat;
	background-size: cover;
	background-position: center;
}

/* Login logo  */
#login h1 a,
.login h1 a {
	<?php echo $login_logo_file ? 'background-image: url(' . esc_html( $login_logo_file ) . ');' : ''; ?>
	max-height: 200px;
	width: 300px;
	background-size: contain;
	background-repeat: no-repeat;
	background-position: bottom;
}

/* Login box */
body.login form {
	background-color: <?php echo $login_box_background_color; ?>;
	border: <?php echo $login_box_background_color; ?>;
}

/* login box text */
body.login label {
	color: <?php echo $login_box_text_color; ?>;
}
/* Login link color */
body.login #backtoblog a, body.login #nav a {
	color: <?php echo $login_text_color; ?>;
}
/* Login button  */
input#wp-submit {
	background-color: <?php echo $login_button_background_color; ?>;
	color: <?php echo $login_button_font_color; ?>;
}
</style>
	<?php

	echo white_label_login_custom_css();

}
add_action( 'login_enqueue_scripts', 'white_label_login_styles' );

/**
 * Replace logo URL on login.
 *
 * @param string $default url.
 * @return string
 */
function white_label_login_styles_url( $default ) {

	$company_url = white_label_get_option( 'business_url', 'white_label_login', false );

	if ( ! empty( $company_url ) ) {
		return $company_url;
	}

	return $default;
}
add_filter( 'login_headerurl', 'white_label_login_styles_url', 2, 999 );

/**
 * Replace URL title on login logo.
 *
 * @param string $default title.
 * @return string
 */
function white_label_login_styles_url_title( $default ) {

	$name = white_label_get_option( 'business_name', 'white_label_login', false );
	if ( ! empty( $name ) ) {
		return $name;
	}

	return $default;

}

add_filter( 'login_headertext', 'white_label_login_styles_url_title' );

/**
 * Get custom login page CSS.
 *
 * @return string custom css.
 */
function white_label_login_custom_css() {

	$css = white_label_get_option( 'login_custom_css', 'white_label_login', false );

	if ( empty( $css ) ) {
		return '';
	}

	return '<style>' . $css . '</style>';
}
