<?php
function whp_ajax_sendmail_popup()
{
    $data = $_POST['data'];

    $email = sanitize_text_field($data[0]['value']);

    $content = whp_get_option('whp_popup_mail_template');
    $content = str_replace(['{email}'], $email, $content);
    $mail_from = whp_get_option('whp_smtp_email');
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $result = wp_mail($mail_from, 'Thông báo thành viên đăng ký mới', $content, $headers);
    if ($result) {
        wp_send_json(['status' => 200]);
    } else {
        wp_send_json(['status' => 500]);
    }
}
add_action('wp_ajax_whp_ajax_sendmail_popup', 'whp_ajax_sendmail_popup');
add_action('wp_ajax_nopriv_whp_ajax_sendmail_popup', 'whp_ajax_sendmail_popup');



function whp_ajax_table_sidebar_admin()
{

    $data = $_POST['data'];
    wp_send_json(['status' => 200]);
}
add_action('wp_ajax_whp_ajax_table_sidebar_admin', 'whp_ajax_table_sidebar_admin');
