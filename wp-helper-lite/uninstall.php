<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Drop custom tables
$tables = array(
    $wpdb->prefix . 'wph_spam_logs',
    $wpdb->prefix . 'wph_captcha_logs',
    $wpdb->prefix . 'wph_email_logs',
    $wpdb->prefix . 'wph_form_submissions',
    $wpdb->prefix . 'ai_jobs',
    $wpdb->prefix . 'ai_job_logs',
);

foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS `" . esc_sql( $table ) . "`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- DROP TABLE IF EXISTS cannot be prepared; table name sanitised with esc_sql()
}

// Delete all options
$options = array(
    'whp_setting',
    'whp_smtp_active',
    'whp_smtp_email',
    'whp_smtp_from_name',
    'whp_smtp_host',
    'whp_smtp_port',
    'whp_smtp_security',
    'whp_maintenance_active',
    'whp_maintenance_template',
    'whp_contact_active',
    'whp_popup_mail_template',
    'wpaap_core_connected',
    'wpaap_default_ai_model',
    'wpaap_pexels_api_key',
    'wpaap_pixabay_api_key',
    'wpaap_provider_connected_anthropic',
    'wpaap_provider_connected_google',
    'wpaap_provider_connected_openai',
    'wpaap_token_usage_logs',
    'wph_captcha_log_settings',
    'wph_captcha_settings',
    'wph_el_settings',
    'wph_fm_settings',
    'wph_spam_filter_settings',
    'wph_spam_log_settings',
    'connectors_anthropic_api_key',
    'connectors_gemini_api_key',
    'connectors_google_api_key',
    'connectors_openai_api_key',
);

foreach ( $options as $option ) {
    delete_option( $option );
}

// Delete dynamic options (wpaap_provider_connected_*, wpaap_tokens_used_*, wpaap_*_model)
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpaap_%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $wpdb->options is a core WP property, always safe
