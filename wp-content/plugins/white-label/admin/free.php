<?php
/**
 * Free White Label functions.
 *
 * @package White Label
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add Pro features upsell to misc section.
 *
 * @return void
 */
function white_label_free_misc() {
	echo '<div class="white_label-subsection">
	<h1 style="font-weight:900;">Level up with White Label Pro</h1>
	<p style="font-size:19px;">Get the full setup and level up your toolkit with White Label Pro.</p>
	<ul style="list-style: initial;
    margin-left: 30px;
	font-size: 17px;">
	<li>Update nags - Hide update notifications from non-White Label Administrators</li>
	<li>Remove the admin bar on the frontend</li>
	<li>Redirect users after loggin in.</li>
	<li>Set WordPress email address & email from name</li>
	</ul>

	<a style="background: #0052cc; font-weight:700; border-color:#0052cc; color:#fff; padding:5px 10px;"
	class="button-primary" href="https://whitewp.com/" target="_blank">Upgrade</a>

	</div>';
}

add_action( 'white_label_form_bottom_white_label_misc', 'white_label_free_misc' );


/**
 * PRO options to admin settings.
 *
 * @param mixed $settings White Label Settings.
 */
function white_label_free_sidebar( $settings ) {

	if ( ! $settings ) {
		return;
	}

	$upgrade = array(
		'id'      => 'white_label_pro_upsell',
		'title'   => '<span style="color:#0052cc;"> ' . __( 'Upgrade your WP Experience', 'white-label' ) . '</span>',
		'content' => '<div style="color:#0052cc; font-weight:600;">' . __(
			'Give your clients or users a better experience with <a href="https://whitewp.com/" target="_blank">White Label Pro</a>.
			<p>
			<b><em>Included in Professional:</em></b>
			</p>
			<ul style="list-style: initial;
			margin-left: 30px;
			">
			<li>Update nags - Hide update notifications from none White Label Administrators</li>
			<li>Remove the admin bar on the frontend</li>
			<li>Redirect user after loggin in.</li>
			<li>Set WordPress email address & email from name</li>
			</ul>
			<br />
			<a style="background: #0052cc; font-weight:700; border-color:#0052cc; color:#fff; padding:5px 10px;"
class="button-primary" href="https://whitewp.com/" target="_blank">Upgrade</a>',
			'white-label'
		)
		. '</div>',
	);

		array_unshift( $settings['sidebars'], $upgrade );

		return $settings;

}
add_filter( 'white_label_admin_settings', 'white_label_free_sidebar' );

