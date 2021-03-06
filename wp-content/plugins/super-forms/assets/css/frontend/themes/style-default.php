<?php
$s = '.super-form-'.$form_id.' ';
$rs1 = '.super-form-'.$form_id.'.super-window-first-responsiveness ';
$rs2 = '.super-form-'.$form_id.'.super-window-second-responsiveness ';
$rs3 = '.super-form-'.$form_id.'.super-window-third-responsiveness ';
$v = $settings;

// Google fonts
if( !isset( $v['font_google_fonts'] ) ) $v['font_google_fonts'] = '';
$import_fonts = '';
//$import_fonts .= "@import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');\n";
$import_fonts .= "@import url('https://fonts.googleapis.com/css2?family=PT+Sans&family=Roboto&display=swap');\n";
if($v['font_google_fonts']!=''){
    $google_fonts = explode( "\n", $v['font_google_fonts'] );  
    foreach( $google_fonts as $font ) {
        $import_fonts .= "@import url('".$font."');\n";
    }
}
// Font family
if( empty( $v['font_global_family'] ) ) $v['font_global_family'] = '"Helvetica", "Arial", sans-serif';
// Globals
if( empty( $v['font_global_size'] ) ) $v['font_global_size'] = 12;
if( empty( $v['font_global_weight'] ) ) { $v['font_global_weight'] = 'normal'; }else{ $v['font_global_weight']; }
if( empty( $v['font_global_lineheight'] ) ) { $v['font_global_lineheight'] = '1.2'; }
if( floatval($v['font_global_lineheight'])>5 ) { $v['font_global_lineheight'] = '1.2'; }
// Labels
if( empty( $v['font_label_size'] ) ) $v['font_label_size'] = 16;
if( empty( $v['font_label_weight'] ) ) { $v['font_label_weight'] = 'normal'; }else{ $v['font_label_weight']; }
if( empty( $v['font_label_lineheight'] ) ) { $v['font_label_lineheight'] = '1.2'; }
if( floatval($v['font_label_lineheight'])>5 ) { $v['font_label_lineheight'] = '1.2'; }
// Descriptions
if( empty( $v['font_description_size'] ) ) $v['font_description_size'] = 14;
if( empty( $v['font_description_weight'] ) ) { $v['font_description_weight'] = 'normal'; }else{ $v['font_description_weight']; }
if( empty( $v['font_description_lineheight'] ) ) { $v['font_description_lineheight'] = '1.2'; }
if( floatval($v['font_description_lineheight'])>5 ) { $v['font_description_lineheight'] = '1.2'; }
// Globals (mobile)
if( empty( $v['font_global_size_mobile'] ) ) $v['font_global_size_mobile'] = 16;
if( empty( $v['font_global_weight_mobile'] ) ) { $v['font_global_weight_mobile'] = 'normal'; }else{ $v['font_global_weight_mobile']; }
if( empty( $v['font_global_lineheight_mobile'] ) ) { $v['font_global_lineheight_mobile'] = '1.2'; }
if( floatval($v['font_global_lineheight_mobile'])>5 ) { $v['font_global_lineheight_mobile'] = '1.2'; }
// Labels (mobile)
if( empty( $v['font_label_size_mobile'] ) ) $v['font_label_size_mobile'] = 20;
if( empty( $v['font_label_weight_mobile'] ) ) { $v['font_label_weight_mobile'] = 'normal'; }else{ $v['font_label_weight_mobile']; }
if( empty( $v['font_label_lineheight_mobile'] ) ) { $v['font_label_lineheight_mobile'] = '1.2'; }
if( floatval($v['font_label_lineheight_mobile'])>5 ) { $v['font_label_lineheight_mobile'] = '1.2'; }
// Descriptions (mobile)
if( empty( $v['font_description_size_mobile'] ) ) $v['font_description_size_mobile'] = 16;
if( empty( $v['font_description_weight_mobile'] ) ) { $v['font_description_weight_mobile'] = 'normal'; }else{ $v['font_description_weight_mobile']; }
if( empty( $v['font_description_lineheight_mobile'] ) ) { $v['font_description_lineheight_mobile'] = '1.2'; }
if( floatval($v['font_description_lineheight_mobile'])>5 ) { $v['font_description_lineheight_mobile'] = '1.2'; }

if( !isset( $v['theme_ui_toggle_disabled_font'] ) ) $v['theme_ui_toggle_disabled_font'] = '#9c9c9c';

if( !isset( $v['theme_field_colors_placeholder'] ) ) {
    $v['theme_field_colors_placeholder'] = '';
}

// @since 2.0.0
if( !isset( $v['theme_success_msg_margin'] ) ) {
    $v['theme_success_msg_margin'] = '0px 0px 30px 0px';
}

/* @since 2.9.0 - toggle buttons */
if( !isset( $v['theme_ui_toggle_bg'] ) ) $v['theme_ui_toggle_bg'] = '#4EB1B6';
if( !isset( $v['theme_ui_toggle_font'] ) ) $v['theme_ui_toggle_font'] = '#ffffff';
if( !isset( $v['theme_ui_toggle_disabled_bg'] ) ) $v['theme_ui_toggle_disabled_bg'] = '#e4e4e4';
if( !isset( $v['theme_ui_toggle_disabled_font'] ) ) $v['theme_ui_toggle_disabled_font'] = '#9c9c9c';

/* @since 2.9.0 - keywords */
if( !isset( $v['theme_ui_keywords_bg'] ) ) $v['theme_ui_keywords_bg'] = '#4EB1B6';
if( !isset( $v['theme_ui_keywords_font'] ) ) $v['theme_ui_keywords_font'] = '#ffffff';
if( !isset( $v['theme_ui_keywords_icon'] ) ) $v['theme_ui_keywords_icon'] = '#2e8a90';
if( !isset( $v['theme_ui_keywords_icon_hover'] ) ) $v['theme_ui_keywords_icon_hover'] = '#246569';

// @since 3.0.0 - loading icon font color
if( !isset( $v['theme_ui_loading_icon_font'] ) ) $v['theme_ui_loading_icon_font'] = '#c5c5c5';

// @since 3.1.0 - checkbox/radio label colors
if( !isset( $v['theme_ui_checkbox_label'] ) ) $v['theme_ui_checkbox_label'] = $v['theme_field_colors_font'];

// @since 3.3.0 - show/hide multi-part progress bar
$extra_styles = '';
if( (isset($v['theme_multipart_progress_bar'])) && ($v['theme_multipart_progress_bar']!='true') ) {
    $extra_styles .= $s.".super-multipart-progress {
    display:none;
}\n";  
}
if( (isset($v['theme_multipart_steps'])) && ($v['theme_multipart_steps']!='true') ) {
    $extra_styles .= $s.".super-multipart-steps {
    display:none;
}\n";
}else{
    if( (isset($v['theme_multipart_steps_hide_mobile'])) && ($v['theme_multipart_steps_hide_mobile']=='true') ) {
        $extra_styles .= $rs1.".super-multipart-steps,
".$rs2.".super-multipart-steps,
".$rs3.".super-multipart-steps {
    display:none;
}\n";
    }
}

// @since 3.3.0
if( !isset( $v['theme_ui_checkbox_border'] ) ) $v['theme_ui_checkbox_border'] = '#4EB1B6';
if( !isset( $v['theme_ui_checkbox_inner'] ) ) $v['theme_ui_checkbox_inner'] = '#4EB1B6';
if( !isset( $v['theme_ui_slider_dragger'] ) ) $v['theme_ui_slider_dragger'] = '#4EB1B6';
if( !isset( $v['theme_ui_slider_track'] ) ) $v['theme_ui_slider_track'] = '#CDCDCD';
if( !isset( $v['theme_ui_quantity_bg'] ) ) $v['theme_ui_quantity_bg'] = '#4EB1B6';
if( !isset( $v['theme_ui_quantity_font'] ) ) $v['theme_ui_quantity_font'] = '#ffffff';
if( !isset( $v['theme_ui_quantity_bg_hover'] ) ) $v['theme_ui_quantity_bg_hover'] = '#7ed0d4';
if( !isset( $v['theme_ui_quantity_font_hover'] ) ) $v['theme_ui_quantity_font_hover'] = '#ffffff';

/* @since 3.7.0 - tags autosuggest field */
if( !isset( $v['theme_ui_tags_bg'] ) ) $v['theme_ui_tags_bg'] = '#4EB1B6';
if( !isset( $v['theme_ui_tags_font'] ) ) $v['theme_ui_tags_font'] = '#ffffff';
if( !isset( $v['theme_ui_tags_remove'] ) ) $v['theme_ui_tags_remove'] = '#2e8a90';
if( !isset( $v['theme_ui_tags_remove_hover'] ) ) $v['theme_ui_tags_remove_hover'] = '#246569';
if( !isset( $v['theme_ui_tags_list_bg_hover'] ) ) $v['theme_ui_tags_list_bg_hover'] = '#fdecde';

if( !isset( $v['theme_icon_color'] ) ) $v['theme_icon_color'] = '#B3DBDD';
if( !isset( $v['theme_icon_bg'] ) ) $v['theme_icon_bg'] = '#ffffff';
if( !isset( $v['theme_icon_border'] ) ) $v['theme_icon_border'] = '#cdcdcd';
if( !isset( $v['theme_icon_color_focus'] ) ) $v['theme_icon_color_focus'] = '#4EB1B6';
if( !isset( $v['theme_icon_bg_focus'] ) ) $v['theme_icon_bg_focus'] = '#ffffff';
if( !isset( $v['theme_icon_border_focus'] ) ) $v['theme_icon_border_focus'] = '#cdcdcd';

// @since 4.9.3 - Adaptive Placeholders */
if( empty( $v['theme_field_colors_placeholder'] ) ) $v['theme_field_colors_placeholder'] = '#9a9a9a';
if( empty( $v['adaptive_placeholder_focus'] ) ) $v['adaptive_placeholder_focus'] = '#4EB1B6';
if( empty( $v['adaptive_placeholder_filled'] ) ) $v['adaptive_placeholder_filled'] = '#9a9a9a';
if( empty( $v['adaptive_placeholder_bg_top_focus'] ) ) $v['adaptive_placeholder_bg_top_focus'] = '#ffffff';
if( empty( $v['adaptive_placeholder_bg_bottom_focus'] ) ) $v['adaptive_placeholder_bg_bottom_focus'] = '#ffffff';
if( empty( $v['adaptive_placeholder_bg_top_filled'] ) ) $v['adaptive_placeholder_bg_top_filled'] = '#ffffff';
if( empty( $v['adaptive_placeholder_bg_bottom_filled'] ) ) $v['adaptive_placeholder_bg_bottom_filled'] = '#ffffff';
$bottom = $v['adaptive_placeholder_bg_bottom_focus'];
$top = $v['adaptive_placeholder_bg_top_focus'];
$placeholder_bg_focus = "
".$s.".super-focus .super-adaptive-placeholder span,
".$s.".super-adaptive-positioning span {
    background: ".$bottom."; /* Old browsers */
    background: -moz-linear-gradient(top, ".$top." 50%, ".$bottom." 50%); /* FF3.6-15 */
    background: -webkit-linear-gradient(top, ".$top." 50%, ".$bottom." 50%); /* Chrome10-25,Safari5.1-6 */
    background: linear-gradient(to bottom, ".$top." 50%, ".$bottom." 50%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='".$top."', endColorstr='".$bottom."', GradientType=0 ); /* IE6-9 */
}";
$bottom = $v['adaptive_placeholder_bg_bottom_filled'];
$top = $v['adaptive_placeholder_bg_top_filled'];
$placeholder_bg_filled = "
".$s.".super-filled .super-adaptive-placeholder span {
    background: ".$bottom."; /* Old browsers */
    background: -moz-linear-gradient(top, ".$top." 50%, ".$bottom." 50%); /* FF3.6-15 */
    background: -webkit-linear-gradient(top, ".$top." 50%, ".$bottom." 50%); /* Chrome10-25,Safari5.1-6 */
    background: linear-gradient(to bottom, ".$top." 50%, ".$bottom." 50%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='".$top."', endColorstr='".$bottom."', GradientType=0 ); /* IE6-9 */
}";

return $import_fonts."

/* Font styles */
".$s.",
".$s.".super-button .super-button-name,
".$s.".super-entered-keywords > span,
".$s.".super-no-results,
".$s.".super-html-subtitle,
".$s.".super-html-content,
".$s.".super-slider .super-field-wrapper span.amount,
".$s.".super-fileupload-button,
".$s.".super-msg,
".$s.".super-radio.display-horizontal .super-field-wrapper label,
".$s.".super-checkbox.display-horizontal .super-field-wrapper label,
".$s.".super-radio.display-vertical .super-field-wrapper label,
".$s.".super-checkbox.display-vertical .super-field-wrapper label,
".$s.".super-error-msg,
".$s.".super-empty-error-msg,
".$s.".super-field .super-field-wrapper .super-shortcode-field,
".$s.".super-field .super-field-wrapper .super-keyword-filter,
".$s.".super-field .super-field-wrapper .super-autosuggest-tags,
".$s.".super-fileupload-files > div .super-fileupload-name,
".$s.".super-fileupload-files > div .super-fileupload-delete,
".$s.".super-toggle-prefix-label,
".$s.".super-toggle-suffix-label,
".$s.".super-toggle-switch,
".$s.".super-field .super-field-wrapper .super-shortcode-field,
.super-datepicker-dialog,
.super-datepicker-dialog *,
.super-datepicker-dialog .ui-datepicker-month,
.super-datepicker-dialog .ui-datepicker-year,
.ui-timepicker-wrapper.super-form-".$form_id.".super-timepicker-dialog,
".$s.".super-dropdown-ui .super-item.super-placeholder,
".$s.".super-adaptive-placeholder span {
    font-family: ".$v['font_global_family'].";
    font-size: ".$v['font_global_size']."px;
    font-weight: ".$v['font_global_weight'].";
    line-height: ".$v['font_global_lineheight'].";
    letter-spacing: 0;
}
".$s.".super-field .super-field-wrapper .super-dropdown-ui .super-item {
    font-family: ".$v['font_global_family'].";
    font-size: ".$v['font_global_size']."px;
    line-height: ".$v['font_global_lineheight'].";
    letter-spacing: 0;
}

/* Labels */
".$s.".super-field .super-label,
".$s.".super-field .super-group-title {
    font-size: ".$v['font_label_size']."px;
    font-weight: ".$v['font_label_weight'].";
    line-height: ".$v['font_label_lineheight'].";
    letter-spacing: 0;
}
/* Descriptions */
".$s.".super-field .super-description {
    font-size: ".$v['font_description_size']."px;
    font-weight: ".$v['font_description_weight'].";
    line-height: ".$v['font_description_lineheight'].";
    letter-spacing: 0;
}
/* Placeholders */
".$s."::-webkit-input-placeholder { /* WebKit browsers */
    font-family: ".$v['font_global_family'].";
    font-size: ".$v['font_global_size']."px;
    font-weight: ".$v['font_global_weight'].";
    line-height: ".$v['font_global_lineheight'].";
    letter-spacing: 0;
}
".$s.":-moz-placeholder { /* Mozilla Firefox 4 to 18 */
    font-family: ".$v['font_global_family'].";
    font-size: ".$v['font_global_size']."px;
    font-weight: ".$v['font_global_weight'].";
    line-height: ".$v['font_global_lineheight'].";
    letter-spacing: 0;
}
".$s."::-moz-placeholder { /* Mozilla Firefox 19+ */
    font-family: ".$v['font_global_family'].";
    font-size: ".$v['font_global_size']."px;
    font-weight: ".$v['font_global_weight'].";
    line-height: ".$v['font_global_lineheight'].";
    letter-spacing: 0;
}
".$s.":-ms-input-placeholder { /* Internet Explorer 10+ */
    font-family: ".$v['font_global_family'].";
    font-size: ".$v['font_global_size']."px;
    font-weight: ".$v['font_global_weight'].";
    line-height: ".$v['font_global_lineheight'].";
    letter-spacing: 0;
}

/* Mobile font styles */
".$rs1.".super-button .super-button-name,
".$rs1.".super-entered-keywords > span,
".$rs1.".super-no-results,
".$rs1.".super-html-subtitle,
".$rs1.".super-html-content,
".$rs1.".super-slider .super-field-wrapper span.amount,
".$rs1.".super-fileupload-button,
".$rs1.".super-msg,
".$rs1.".super-radio.display-horizontal .super-field-wrapper label,
".$rs1.".super-checkbox.display-horizontal .super-field-wrapper label,
".$rs1.".super-radio.display-vertical .super-field-wrapper label,
".$rs1.".super-checkbox.display-vertical .super-field-wrapper label,
".$rs1.".super-error-msg,
".$rs1.".super-empty-error-msg,
".$rs1.".super-field .super-field-wrapper .super-shortcode-field,
".$rs1.".super-field .super-field-wrapper .super-keyword-filter,
".$rs1.".super-field .super-field-wrapper .super-autosuggest-tags,
".$rs1.".super-fileupload-files > div .super-fileupload-name,
".$rs1.".super-fileupload-files > div .super-fileupload-delete,
".$rs1.".super-toggle-prefix-label,
".$rs1.".super-toggle-suffix-label,
".$rs1.".super-toggle-switch,
".$rs1.".super-field .super-field-wrapper .super-shortcode-field,
".$rs1.".super-datepicker-dialog,
".$rs1.".super-datepicker-dialog *,
".$rs1.".super-datepicker-dialog .ui-datepicker-month,
".$rs1.".super-datepicker-dialog .ui-datepicker-year,
".$rs1.".ui-timepicker-wrapper.super-form-".$form_id.".super-timepicker-dialog,
".$rs1.".super-dropdown-ui .super-item.super-placeholder,
".$rs1.".super-adaptive-placeholder span {
    font-size: ".$v['font_global_size_mobile']."px;
    font-weight: ".$v['font_global_weight_mobile'].";
    line-height: ".$v['font_global_lineheight_mobile'].";
    letter-spacing: 0;
}
".$rs1.".super-field .super-field-wrapper .super-dropdown-ui .super-item {
    font-size: ".$v['font_global_size_mobile']."px;
    line-height: ".$v['font_global_lineheight_mobile'].";
    letter-spacing: 0;
}

/* Labels */
".$rs1.".super-field .super-label,
".$rs1.".super-field .super-group-title {
    font-size: ".$v['font_label_size_mobile']."px;
    font-weight: ".$v['font_label_weight_mobile'].";
    line-height: ".$v['font_label_lineheight_mobile'].";
    letter-spacing: 0;
}
/* Descriptions */
".$rs1.".super-field .super-description {
    font-size: ".$v['font_description_size_mobile']."px;
    font-weight: ".$v['font_description_weight_mobile'].";
    line-height: ".$v['font_description_lineheight_mobile'].";
    letter-spacing: 0;
}
/* Placeholders */
".$rs1."::-webkit-input-placeholder { /* WebKit browsers */
    font-size: ".$v['font_global_size_mobile']."px;
    font-weight: ".$v['font_global_weight_mobile'].";
    line-height: ".$v['font_global_lineheight_mobile'].";
    letter-spacing: 0;
}
".$rs1.":-moz-placeholder { /* Mozilla Firefox 4 to 18 */
    font-size: ".$v['font_global_size_mobile']."px;
    font-weight: ".$v['font_global_weight_mobile'].";
    line-height: ".$v['font_global_lineheight_mobile'].";
    letter-spacing: 0;
}
".$rs1."::-moz-placeholder { /* Mozilla Firefox 19+ */
    font-size: ".$v['font_global_size_mobile']."px;
    font-weight: ".$v['font_global_weight_mobile'].";
    line-height: ".$v['font_global_lineheight_mobile'].";
    letter-spacing: 0;
}
".$rs1.":-ms-input-placeholder { /* Internet Explorer 10+ */
    font-size: ".$v['font_global_size_mobile']."px;
    font-weight: ".$v['font_global_weight_mobile'].";
    line-height: ".$v['font_global_lineheight_mobile'].";
    letter-spacing: 0;
}

/* Slider amount positioning based on font size */
".$s.".super-slider .super-field-wrapper span.amount {
    top: -".ceil($v['font_global_size']/2)."px;
}
".$rs1.".super-slider .super-field-wrapper span.amount {
    top: -".ceil($v['font_global_size_mobile']/2)."px;
}

/* Colors */

/* Dropdown placeholder */
".$s.".super-dropdown-ui .super-item.super-placeholder {
    color:".$v['theme_field_colors_placeholder'].";
}
".$s.".super-focus .super-dropdown-ui .super-item.super-placeholder {
    color:".$v['adaptive_placeholder_focus'].";
}
".$s.".super-filled .super-dropdown-ui .super-item.super-placeholder {
    color: ".$v['theme_field_colors_font'].";
}

/* @since 4.9.3 - Adaptive Placeholders */
/* Initial Color */
".$s.".super-adaptive-placeholder span {
  color:".$v['theme_field_colors_placeholder'].";
}
/* Focused Colors */
".$s.".super-focus .super-adaptive-placeholder span,
".$s.".super-adaptive-positioning span {
  color:".$v['adaptive_placeholder_focus'].";
  ".(!empty($v['adaptive_placeholder_border_focus']) ? 'border:1px solid '.$v['adaptive_placeholder_border_focus'] : 'border: 0' ).";
}
/* Filled Colors */
".$s.".super-filled .super-adaptive-placeholder span {
  color:".$v['adaptive_placeholder_filled'].";
  ".(!empty($v['adaptive_placeholder_border_filled']) ? 'border:1px solid '.$v['adaptive_placeholder_border_filled'] : 'border: 0' ).";
}
/* Filled + Focus Colors */
".$s.".super-focus.super-filled .super-adaptive-placeholder span {
    color:".$v['adaptive_placeholder_focus'].";
    ".(!empty($v['adaptive_placeholder_border_focus']) ? 'border:1px solid '.$v['adaptive_placeholder_border_focus'] : 'border: 0' ).";
}

/* Background Colors */
".$placeholder_bg_focus."
".$placeholder_bg_filled."

".$s."::-webkit-input-placeholder { /* WebKit browsers */
    color:".$v['theme_field_colors_placeholder'].";
}
".$s.":-moz-placeholder { /* Mozilla Firefox 4 to 18 */
    color:".$v['theme_field_colors_placeholder'].";
    opacity:1;
}
".$s."::-moz-placeholder { /* Mozilla Firefox 19+ */
    color:".$v['theme_field_colors_placeholder'].";
    opacity:1;
}
".$s.":-ms-input-placeholder { /* Internet Explorer 10+ */
    color:".$v['theme_field_colors_placeholder'].";
}
".$s.".super-focus ::-webkit-input-placeholder { /* WebKit browsers */
    color:".$v['adaptive_placeholder_focus'].";
}
".$s.".super-focus :-moz-placeholder { /* Mozilla Firefox 4 to 18 */
   color:".$v['adaptive_placeholder_focus'].";
   opacity:1;
}
".$s.".super-focus ::-moz-placeholder { /* Mozilla Firefox 19+ */
   color:".$v['adaptive_placeholder_focus'].";
   opacity:1;
}
".$s.".super-focus :-ms-input-placeholder { /* Internet Explorer 10+ */
   color:".$v['adaptive_placeholder_focus'].";
}

".$s.".super-field .super-label,
".$s.".super-field .super-group-title,
".$s.".super-field .super-toggle-prefix-label,
".$s.".super-field .super-toggle-suffix-label,
".$s.".super-html-title {
    color: ".$v['theme_field_label'].";
}
".$s.".super-field .super-description,
".$s.".super-html-subtitle,
".$s.".super-html-content {
    color: ".$v['theme_field_description'].";
}
".$s.".super-load-icon {
    color: ".$v['theme_ui_loading_icon_font'].";
}
".$s."input,
".$s.".super-dropdown-ui,
".$s."textarea,
".$s.".super-autosuggest-tags,
".$s.".super-field div .super-fileupload-button,
".$s.".super-stripe-base {
    color: ".$v['theme_field_colors_font'].";
    background-color: ".$v['theme_field_colors_top'].";
    border: solid 1px ".$v['theme_field_colors_border'].";
    background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0.25, ".$v['theme_field_colors_top']."), color-stop(1, ".$v['theme_field_colors_bottom']."));
    background-image: -o-linear-gradient(bottom, ".$v['theme_field_colors_top']." 25%, ".$v['theme_field_colors_bottom']." 100%);
    background-image: -moz-linear-gradient(bottom, ".$v['theme_field_colors_top']." 25%, ".$v['theme_field_colors_bottom']." 100%);
    background-image: -webkit-linear-gradient(bottom, ".$v['theme_field_colors_top']." 25%, ".$v['theme_field_colors_bottom']." 100%);
    background-image: -ms-linear-gradient(bottom, ".$v['theme_field_colors_top']." 25%, ".$v['theme_field_colors_bottom']." 100%);
    background-image: linear-gradient(to bottom, ".$v['theme_field_colors_top']." 25%, ".$v['theme_field_colors_bottom']." 100%);
}
".$s.".super-checkbox .super-field-wrapper label,
".$s.".super-radio .super-field-wrapper label {
    color: ".$v['theme_ui_checkbox_label'].";
}
".$s."input:focus,
".$s.".super-focus .super-dropdown-ui,
".$s."textarea:focus,
".$s.".super-focus .super-autosuggest-tags {
    color: ".$v['theme_field_colors_font_focus'].";
    background-color: ".$v['theme_field_colors_top_focus'].";
    border: solid 1px ".$v['theme_field_colors_border_focus'].";
    background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0.25, ".$v['theme_field_colors_top_focus']."), color-stop(1, ".$v['theme_field_colors_bottom_focus']."));
    background-image: -o-linear-gradient(bottom, ".$v['theme_field_colors_top_focus']." 25%, ".$v['theme_field_colors_bottom_focus']." 100%);
    background-image: -moz-linear-gradient(bottom, ".$v['theme_field_colors_top_focus']." 25%, ".$v['theme_field_colors_bottom_focus']." 100%);
    background-image: -webkit-linear-gradient(bottom, ".$v['theme_field_colors_top_focus']." 25%, ".$v['theme_field_colors_bottom_focus']." 100%);
    background-image: -ms-linear-gradient(bottom, ".$v['theme_field_colors_top_focus']." 25%, ".$v['theme_field_colors_bottom_focus']." 100%);
    background-image: linear-gradient(to bottom, ".$v['theme_field_colors_top_focus']." 25%, ".$v['theme_field_colors_bottom_focus']." 100%);
}
".$s.".super-radio .super-field-wrapper .super-item > .super-before,
".$s.".super-checkbox .super-field-wrapper .super-item > .super-before {
    border-color: ".$v['theme_ui_checkbox_border'].";
}
".$s.".super-radio .super-field-wrapper .super-item > .super-after,
".$s.".super-checkbox .super-field-wrapper .super-item > .super-after {
    background-color: ".$v['theme_ui_checkbox_inner'].";
}
".$s.".super-slider .super-field-wrapper .slider .dragger,
".$s.".super-slider .super-field-wrapper .slider .dragger:hover {
    background:none;
    background-color: ".$v['theme_ui_slider_dragger'].";
}
".$s.".super-slider .super-field-wrapper .slider .track {
    background-color: ".$v['theme_ui_slider_track'].";
}
".$s.".super-quantity .super-minus-button,
".$s.".super-quantity .super-plus-button {
    background-color: ".$v['theme_ui_quantity_bg'].";
    color: ".$v['theme_ui_quantity_font'].";
}
".$s.".super-quantity .super-minus-button:hover ,
".$s.".super-quantity .super-plus-button:hover {
    background-color: ".$v['theme_ui_quantity_bg_hover'].";
    color: ".$v['theme_ui_quantity_font_hover'].";
}
".$s.".super-field-wrapper .super-icon {
    color: ".$v['theme_icon_color'].";
    " . ($v['theme_icon_bg']!='' ? "background-color: ".$v['theme_icon_bg'].";" : "") . "
    " . ($v['theme_icon_border']!='' ? "border: 1px solid ".$v['theme_icon_border'].";" : "padding-top:1px;padding-left:1px;") . "
}
".$s.".super-focus .super-field-wrapper .super-icon {
    color: ".$v['theme_icon_color_focus'].";
    " . ($v['theme_icon_bg_focus']!='' ? "background-color: ".$v['theme_icon_bg_focus'].";" : "") . "
    " . ($v['theme_icon_border_focus']!='' ? "border: 1px solid ".$v['theme_icon_border_focus'].";" : "padding-top:1px;padding-left:1px;") . "
}
".$s.".super-rating .super-rating-star {
    color: ".$v['theme_rating_color'].";
    background-color: ".$v['theme_rating_bg'].";
    border: 1px solid ".$v['theme_rating_border'].";
}
".$s.".super-rating .super-rating-star.super-hover {
    color: ".$v['theme_rating_color_hover'].";
    background-color: ".$v['theme_rating_bg_hover'].";
}
".$s.".super-rating .super-rating-star.super-active {
    color: ".$v['theme_rating_color_active'].";
    background-color: ".$v['theme_rating_bg_active'].";
}
".$s.".super-multipart-progress-inner {
    border: 1px solid ".$v['theme_progress_bar_border_color'].";
    background-color: ".$v['theme_progress_bar_secondary_color'].";
}
".$s.".super-multipart-progress-bar {
    background-color: ".$v['theme_progress_bar_primary_color'].";
}
".$s.".super-multipart-steps .super-multipart-step {
    background-color: ".$v['theme_progress_step_secondary_color'].";
    border: 1px solid ".$v['theme_progress_step_border_color'].";
}
".$s.".super-multipart-steps .super-multipart-step:after {
    border-top: 1px dashed ".$v['theme_progress_step_border_color'].";
}
".$s.".super-multipart-steps .super-multipart-step.super-active {
    color: ".$v['theme_progress_step_font_color_active'].";
    background-color: ".$v['theme_progress_step_secondary_color_active'].";
    border: 1px solid ".$v['theme_progress_step_border_color_active'].";
}
".$s.".super-multipart-steps .super-multipart-step .super-multipart-step-wrapper {
    background-color: ".$v['theme_progress_step_primary_color'].";
}
".$s.".super-multipart-steps .super-multipart-step.super-active .super-multipart-step-wrapper {
    background-color: ".$v['theme_progress_step_primary_color_active'].";
}
".$s.".super-multipart-steps .super-multipart-step .super-multipart-step-count,
".$s.".super-multipart-steps .super-multipart-step .super-multipart-step-icon {
    color: ".$v['theme_progress_step_font_color'].";
}
".$s.".super-multipart-steps .super-multipart-step.super-active .super-multipart-step-count,
".$s.".super-multipart-steps .super-multipart-step.super-active .super-multipart-step-icon {
    color: ".$v['theme_progress_step_font_color_active'].";
}
".$s.".super-form.super-style-one .super-multipart-steps .super-multipart-step {
    background-color: ".$v['theme_progress_step_primary_color'].";
}
".$s.".super-form.super-style-one .super-multipart-steps .super-multipart-step.super-active {
    border-color: ".$v['theme_progress_step_primary_color_active'].";
    background-color: ".$v['theme_progress_step_primary_color_active'].";
}
".$s.".super-form.super-style-one .super-multipart-steps .super-multipart-step.super-error {
    background-color: ".$v['theme_error_font'].";
    border-color: ".$v['theme_error_font'].";
}
".$s.".super-multipart-steps .super-multipart-step.super-error:before {
    content:'\\f071';
    position: absolute;
    width: 100%;
    text-align: center;
    left: 0px;
    top: -22px;
    bottom:inherit;
    height: 20px;
    line-height: 20px;
    font-size: 14px;
    color: ".$v['theme_error_font'].";
}
".$s.".super-form.super-style-one .super-multipart-steps .super-multipart-step.super-error:before {
    color: ".$v['theme_error_font'].";
}
".$s.".super-initialized .super-multipart.super-active {
    visibility: visible;
    height: auto;
}
".$s.".super-initialized .super-multipart.super-active > * {
    opacity:1;
}
".$s.".super-error-msg,
".$s.".super-empty-error-msg {
    color: ".$v['theme_error_font'].";
}
".$s.".super-msg.super-error {
    border: 1px solid ".$v['theme_error_msg_border_color'].";
    background-color: ".$v['theme_error_msg_bg_color'].";
    color: ".$v['theme_error_msg_font_color'].";
}
".$s.".super-msg.super-error a {
    color: ".$v['theme_error_msg_font_color'].";
}
".$s.".super-msg.super-error:after {
    color: ".$v['theme_error_msg_icon_color'].";
}
".$s.".super-msg.super-success {
    border: 1px solid".$v['theme_success_msg_border_color'].";
    background-color: ".$v['theme_success_msg_bg_color'].";
    color: ".$v['theme_success_msg_font_color'].";
    margin: ".$v['theme_success_msg_margin'].";
}
".$s.".super-msg.super-success a,
".$s.".super-msg.super-success .super-close {
    color: ".$v['theme_success_msg_font_color'].";
}
".$s.".super-msg.super-success:after {
    color: ".$v['theme_success_msg_icon_color'].";
}
".$s.".super-dropdown-arrow {
    color: ".$v['theme_field_colors_font'].";
}

/* @since 2.9.0 - toggle buttons */
".$s.".super-toggle-switch {
    border-color: ".$v['theme_ui_toggle_bg'].";
}
".$s.".super-toggle-switch .super-toggle-group .super-toggle-on,
".$s.".super-toggle-switch .super-toggle-group .super-toggle-off {
    background-color: ".$v['theme_ui_toggle_bg'].";
    color: ".$v['theme_ui_toggle_font'].";
}
".$s.".super-toggle-switch:not(.super-active) {
    border-color: ".$v['theme_ui_toggle_disabled_bg'].";
}
".$s.".super-toggle-switch .super-toggle-group .super-toggle-off {
    background-color: ".$v['theme_ui_toggle_disabled_bg'].";
    color: ".$v['theme_ui_toggle_disabled_font'].";
}


/* @since 2.9.0 - keywords */
".$s.".super-entered-keywords > span {
    background-color: ".$v['theme_ui_keywords_bg'].";
    color: ".$v['theme_ui_keywords_font'].";
}
.super-entered-keywords > span:after {
    color: ".$v['theme_ui_keywords_icon'].";
}
.super-entered-keywords > span:hover:after {
    color: ".$v['theme_ui_keywords_icon_hover'].";
}
".$s.".super-button .super-button-name {
    color: ".$v['theme_button_font'].";
}
.super-visible {
    visibility:visible;
}

/* @since 3.7.0 - tags autosuggest field */
".$s.".super-field .super-field-wrapper .super-autosuggest-tags > div > span,
".$s.".super-field.super-text.super-keyword-tags.super-string-found .super-field-wrapper .super-dropdown-ui .super-item span.super-wp-tag {
    background-color: ".$v['theme_ui_tags_bg'].";
    color: ".$v['theme_ui_tags_font'].";
}
".$s.".super-field .super-field-wrapper .super-autosuggest-tags > div > span:after {
    color: ".$v['theme_ui_tags_remove'].";
}
".$s.".super-field .super-field-wrapper .super-autosuggest-tags > div > span:hover:after {
    color: ".$v['theme_ui_tags_remove_hover'].";
}
".$s.".super-field.super-text.super-keyword-tags.super-string-found .super-field-wrapper .super-dropdown-ui .super-item:hover {
    background-color: ".$v['theme_ui_tags_list_bg_hover'].";
}
".$s.".super-field.super-text .super-field-wrapper.super-overlap .super-dropdown-ui {
    border: solid 1px ".$v['theme_field_colors_border'].";
    background-color: ".$v['theme_field_colors_top'].";
    background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0.25, ".$v['theme_field_colors_top']."), color-stop(1, ".$v['theme_field_colors_bottom']."));
    background-image: -o-linear-gradient(bottom, ".$v['theme_field_colors_top']." 25%, ".$v['theme_field_colors_bottom']." 100%);
    background-image: -moz-linear-gradient(bottom, ".$v['theme_field_colors_top']." 25%, ".$v['theme_field_colors_bottom']." 100%);
    background-image: -webkit-linear-gradient(bottom, ".$v['theme_field_colors_top']." 25%, ".$v['theme_field_colors_bottom']." 100%);
    background-image: -ms-linear-gradient(bottom, ".$v['theme_field_colors_top']." 25%, ".$v['theme_field_colors_bottom']." 100%);
    background-image: linear-gradient(to bottom, ".$v['theme_field_colors_top']." 25%, ".$v['theme_field_colors_bottom']." 100%);
}
".$s.".super-field.super-text .super-field-wrapper.super-overlap .super-dropdown-ui .super-item.super-active {
    color: ".$v['theme_field_colors_font'].";
}
".$extra_styles;
