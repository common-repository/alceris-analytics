<?php
/*
Plugin Name: Alceris Analytics
Description: Alceris Analytics is a website analytics solution which creates insights, without using any user specific data or cookies. GDPR-compliant alternative to Google Analytics.
Author: Alceris
Version: 1.0.0

Alceris Analytics for WordPress
Copyright (C) 2022 Alceris
*/

const ALCERIS_SITE_ID_OPTION = 'alceris_site_id';
const ALCERIS_TRACK_ADMIN_OPTION = 'alceris_track_admin';

function alceris_get_site_id() {
    return get_option('alceris_site_id', '');
}

function alceris_get_admin_tracking() {
    return get_option('alceris_track_admin', '');
}

function alceris_print_js_snippet() {
    $exclude_admin = alceris_get_admin_tracking();

    if (empty($exclude_admin) && current_user_can('manage_options')) {
        return;
    }

    $site_id = alceris_get_site_id();

    if (empty($site_id)) {
        return;
    } 

    wp_print_script_tag(array(
        'src'      => 'https://alceris.com/script.js',
        'data-id'  => esc_attr($site_id),
        'defer' => true
    ));
}

function alceris_register_settings() {
    $alceris_logo_html = sprintf('<a href="https://alceris.com/" style="margin-left: 6px"><img src="%s" width="25" height="25" style="vertical-align: middle"></a>', plugins_url('alceris.png', __FILE__));

    // register page + section
    add_options_page('Alceris Analytics', 'Alceris Analytics', 'manage_options', 'alceris-analytics', 'alceris_print_settings_page');
    add_settings_section('default', "{$alceris_logo_html} Alceris Analytics", 'alceris_settings_description', 'alceris-analytics');

    // register options
    register_setting('alceris', ALCERIS_SITE_ID_OPTION, array('type' => 'string'));
    register_setting('alceris', ALCERIS_TRACK_ADMIN_OPTION, array('type' => 'string'));

    // register settings fields
    add_settings_field(ALCERIS_SITE_ID_OPTION, __('Site ID', 'alceris-analytics'), 'alceris_print_site_id_setting_field', 'alceris-analytics', 'default');
    add_settings_field(ALCERIS_TRACK_ADMIN_OPTION, __('Track Administrators', 'alceris-analytics'), 'alceris_print_admin_tracking_setting_field', 'alceris-analytics', 'default');
}

function alceris_print_settings_page() {
    echo '<div class="wrap">';
    echo sprintf('<form method="POST" action="%s">', esc_attr(admin_url('options.php')));

    settings_fields('alceris');
    do_settings_sections('alceris-analytics');
    submit_button();

    echo '</form>';
    echo '</div>';
}

function alceris_settings_description() {
    echo '<p>Alceris is a privacy focused website analytics solution which creates insights, without using any user specific data or cookies. A powerful GDPR compatible Google Analytics alternative.</p>';
    echo '<p>If you don\'t have already an account, <a href="https://alceris.com" target="_blank">click here</a> and start your free trial (no credit card or payment method required for signup).</p>';
}

function alceris_print_site_id_setting_field($args = array()) {
    $value = get_option(ALCERIS_SITE_ID_OPTION);
    echo sprintf('<input type="text" name="%s" id="%s" class="regular-text" value="%s" placeholder="Your site ID" />', ALCERIS_SITE_ID_OPTION, ALCERIS_SITE_ID_OPTION, esc_attr($value));
    echo '<p class="description">This is your site id which is used to save all tracking data. It can be found in the "Website integration" tab on the <a href="https://alceris.com/dashboard" target="_blank">dashboard</a>.</p>';
}

function alceris_print_admin_tracking_setting_field($args = array()) {
    $value = get_option(ALCERIS_TRACK_ADMIN_OPTION);
    echo sprintf('<input type="checkbox" name="%s" id="%s" value="1" %s />', ALCERIS_TRACK_ADMIN_OPTION, ALCERIS_TRACK_ADMIN_OPTION, checked(1, $value, false));
    echo '<p class="description">Track pageviews of administrator accounts</p>';
}

add_action('wp_footer', 'alceris_print_js_snippet', 50);

if (is_admin() && ! wp_doing_ajax()) {
    add_action('admin_menu', 'alceris_register_settings');
}
