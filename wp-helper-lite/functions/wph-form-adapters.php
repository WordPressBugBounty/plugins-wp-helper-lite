<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── SHARED: extract name/email/phone từ field array ─────────────────────────

function wph_fm_extract_contact( $fields ) {
    $name_keys  = array( 'your-name','name','ho-ten','fullname','full-name','ho_ten','ten','first-name','last-name','fname','lname' );
    $email_keys = array( 'your-email','email','e-mail','email-address' );
    $phone_keys = array( 'your-phone','phone','tel','sdt','so-dien-thoai','dien-thoai','mobile','so_dt','phone-number' );

    $name  = '';
    $email = '';
    $phone = '';

    foreach ( $fields as $key => $value ) {
        $k = strtolower( trim( $key ) );
        $v = is_array( $value ) ? implode( ', ', $value ) : (string) $value;
        if ( empty( $name )  && in_array( $k, $name_keys,  true ) ) $name  = $v;
        if ( empty( $email ) && in_array( $k, $email_keys, true ) ) $email = $v;
        if ( empty( $phone ) && in_array( $k, $phone_keys, true ) ) $phone = $v;
    }

    // Fallback: find by value pattern if key not matched
    if ( empty( $email ) ) {
        foreach ( $fields as $v ) {
            $v = is_array( $v ) ? implode( ' ', $v ) : $v;
            if ( filter_var( trim( $v ), FILTER_VALIDATE_EMAIL ) ) { $email = $v; break; }
        }
    }
    if ( empty( $phone ) ) {
        foreach ( $fields as $v ) {
            $v = is_array( $v ) ? implode( ' ', $v ) : $v;
            if ( preg_match( '/^[\+\d\s\-\(\)]{8,15}$/', trim( $v ) ) ) { $phone = $v; break; }
        }
    }

    return array( 'name' => $name, 'email' => $email, 'phone' => $phone );
}

function wph_fm_get_referer() {
    return isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_url( $_SERVER['HTTP_REFERER'] ) : '';
}

function wph_fm_should_save( $plugin_key ) {
    $settings = get_option( 'wph_fm_settings', array() );
    $enabled  = $settings['plugins'] ?? array();
    // Nếu chưa cấu hình → mặc định bật tất cả
    if ( empty( $enabled ) ) return true;
    return ! empty( $enabled[ $plugin_key ] );
}

// ─── CONTACT FORM 7 ──────────────────────────────────────────────────────────

add_action( 'wpcf7_mail_sent', 'wph_fm_adapter_cf7' );
function wph_fm_adapter_cf7( $contact_form ) {
    if ( ! wph_fm_should_save( 'cf7' ) ) return;

    $submission = WPCF7_Submission::get_instance();
    if ( ! $submission ) return;

    $posted  = $submission->get_posted_data();
    $fields  = array();
    foreach ( $posted as $key => $value ) {
        if ( strpos( $key, '_wpcf7' ) === 0 ) continue;
        $fields[ $key ] = is_array( $value ) ? implode( ', ', $value ) : $value;
    }

    $contact = wph_fm_extract_contact( $fields );

    wph_fm_save_submission( array(
        'form_plugin'    => 'cf7',
        'form_id'        => (string) $contact_form->id(),
        'form_title'     => $contact_form->title(),
        'customer_name'  => $contact['name'],
        'customer_email' => $contact['email'],
        'customer_phone' => $contact['phone'],
        'submission_url' => $submission->get_meta( 'url' ) ?: ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '' ),
        'referrer'       => wph_fm_get_referer(),
        'fields'         => $fields,
    ) );
}

// ─── WPFORMS ─────────────────────────────────────────────────────────────────

add_action( 'wpforms_process_complete', 'wph_fm_adapter_wpforms', 10, 4 );
function wph_fm_adapter_wpforms( $fields, $entry, $form_data, $entry_id ) {
    if ( ! wph_fm_should_save( 'wpforms' ) ) return;

    $flat = array();
    foreach ( $fields as $field ) {
        $key         = sanitize_key( $field['name'] ?? $field['id'] );
        $flat[ $key ] = $field['value'] ?? '';
    }

    $contact = wph_fm_extract_contact( $flat );

    wph_fm_save_submission( array(
        'form_plugin'    => 'wpforms',
        'form_id'        => (string) ( $form_data['id'] ?? '' ),
        'form_title'     => $form_data['settings']['form_title'] ?? '',
        'customer_name'  => $contact['name'],
        'customer_email' => $contact['email'],
        'customer_phone' => $contact['phone'],
        'submission_url' => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
        'referrer'       => wph_fm_get_referer(),
        'fields'         => $flat,
    ) );
}

// ─── GRAVITY FORMS ───────────────────────────────────────────────────────────

add_action( 'gform_after_submission', 'wph_fm_adapter_gf', 10, 2 );
function wph_fm_adapter_gf( $entry, $form ) {
    if ( ! wph_fm_should_save( 'gf' ) ) return;

    $flat = array();
    foreach ( $form['fields'] as $field ) {
        $key         = sanitize_key( $field->label );
        $flat[ $key ] = rgar( $entry, (string) $field->id ) ?: '';
    }

    $contact = wph_fm_extract_contact( $flat );

    wph_fm_save_submission( array(
        'form_plugin'    => 'gf',
        'form_id'        => (string) $form['id'],
        'form_title'     => $form['title'],
        'customer_name'  => $entry['3'] ?? $contact['name'],  // GF field 3 = Name (common)
        'customer_email' => $entry['2'] ?? $contact['email'],
        'customer_phone' => $contact['phone'],
        'submission_url' => $entry['source_url'] ?? '',
        'referrer'       => wph_fm_get_referer(),
        'fields'         => $flat,
    ) );
}

// ─── NINJA FORMS ─────────────────────────────────────────────────────────────

add_action( 'ninja_forms_after_submission', 'wph_fm_adapter_nf' );
function wph_fm_adapter_nf( $form_data ) {
    if ( ! wph_fm_should_save( 'nf' ) ) return;

    $flat = array();
    foreach ( $form_data['fields'] as $field ) {
        $key         = sanitize_key( $field['settings']['label'] ?? $field['id'] );
        $flat[ $key ] = $field['value'] ?? '';
    }

    $contact = wph_fm_extract_contact( $flat );

    wph_fm_save_submission( array(
        'form_plugin'    => 'nf',
        'form_id'        => (string) ( $form_data['form_id'] ?? '' ),
        'form_title'     => $form_data['settings']['title'] ?? '',
        'customer_name'  => $contact['name'],
        'customer_email' => $contact['email'],
        'customer_phone' => $contact['phone'],
        'submission_url' => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
        'referrer'       => wph_fm_get_referer(),
        'fields'         => $flat,
    ) );
}

// ─── FLUENT FORMS ────────────────────────────────────────────────────────────

add_action( 'fluentform_submission_inserted', 'wph_fm_adapter_ff', 10, 3 );
function wph_fm_adapter_ff( $entry_id, $form_data, $form ) {
    if ( ! wph_fm_should_save( 'ff' ) ) return;

    $flat = array();
    if ( is_string( $form_data ) ) $form_data = json_decode( $form_data, true );
    if ( is_array( $form_data ) ) {
        foreach ( $form_data as $key => $value ) {
            $flat[ $key ] = is_array( $value ) ? implode( ', ', $value ) : $value;
        }
    }

    $contact = wph_fm_extract_contact( $flat );

    wph_fm_save_submission( array(
        'form_plugin'    => 'ff',
        'form_id'        => (string) ( $form->id ?? '' ),
        'form_title'     => $form->title ?? '',
        'customer_name'  => $contact['name'],
        'customer_email' => $contact['email'],
        'customer_phone' => $contact['phone'],
        'submission_url' => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
        'referrer'       => wph_fm_get_referer(),
        'fields'         => $flat,
    ) );
}

// ─── FORMIDABLE FORMS ────────────────────────────────────────────────────────

add_action( 'frm_after_create_entry', 'wph_fm_adapter_frm', 30, 2 );
function wph_fm_adapter_frm( $entry_id, $form_id ) {
    if ( ! wph_fm_should_save( 'frm' ) ) return;
    if ( ! class_exists( 'FrmEntry' ) || ! class_exists( 'FrmForm' ) ) return;

    $entry = FrmEntry::getOne( $entry_id, true );
    $form  = FrmForm::getOne( $form_id );
    if ( ! $entry || ! $form ) return;

    $flat = array();
    if ( ! empty( $entry->metas ) ) {
        foreach ( $entry->metas as $field_id => $value ) {
            $field       = FrmField::getOne( $field_id );
            $key         = $field ? sanitize_key( $field->name ) : 'field_' . $field_id;
            $flat[ $key ] = is_array( $value ) ? implode( ', ', $value ) : $value;
        }
    }

    $contact = wph_fm_extract_contact( $flat );

    wph_fm_save_submission( array(
        'form_plugin'    => 'frm',
        'form_id'        => (string) $form_id,
        'form_title'     => $form->name ?? '',
        'customer_name'  => $contact['name'],
        'customer_email' => $contact['email'],
        'customer_phone' => $contact['phone'],
        'submission_url' => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
        'referrer'       => wph_fm_get_referer(),
        'fields'         => $flat,
    ) );
}

// ─── WS FORM ─────────────────────────────────────────────────────────────────

add_action( 'wsf_submit_post_after', 'wph_fm_adapter_wsf', 10, 2 );
function wph_fm_adapter_wsf( $form, $submit ) {
    if ( ! wph_fm_should_save( 'wsf' ) ) return;

    $flat = array();
    if ( ! empty( $submit->meta ) ) {
        foreach ( $submit->meta as $key => $value ) {
            $flat[ $key ] = is_array( $value ) ? implode( ', ', $value ) : $value;
        }
    }

    $contact = wph_fm_extract_contact( $flat );

    wph_fm_save_submission( array(
        'form_plugin'    => 'wsf',
        'form_id'        => (string) ( $form->id ?? '' ),
        'form_title'     => $form->label ?? '',
        'customer_name'  => $contact['name'],
        'customer_email' => $contact['email'],
        'customer_phone' => $contact['phone'],
        'submission_url' => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
        'referrer'       => wph_fm_get_referer(),
        'fields'         => $flat,
    ) );
}
