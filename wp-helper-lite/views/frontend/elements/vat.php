<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<style>
.mbwc-vat-wrap {
    font-family: inherit;
    margin: 20px 0;
    /* Force full-row inside WooCommerce flex/float field wrapper */
    width: 100%;
    flex-basis: 100%;
    clear: both;
    order: 99;
    box-sizing: border-box;
}

/* Toggle row */
.mbwc-vat-toggle-row {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    user-select: none;
    padding: 12px 16px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    transition: background 0.2s ease, border-color 0.2s ease;
}

.mbwc-vat-toggle-row:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.mbwc-vat-toggle-row input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}

.mbwc-vat-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}

.mbwc-vat-slider {
    position: absolute;
    inset: 0;
    background: #d1d5db;
    border-radius: 34px;
    transition: background 0.25s ease;
    pointer-events: none;
}

.mbwc-vat-slider::before {
    content: "";
    position: absolute;
    width: 18px;
    height: 18px;
    left: 3px;
    bottom: 3px;
    background: #ffffff;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    transition: transform 0.25s ease;
}

.mbwc-vat-toggle-row.is-checked .mbwc-vat-slider {
    background: #22c55e;
}

.mbwc-vat-toggle-row.is-checked .mbwc-vat-slider::before {
    transform: translateX(20px);
}

.mbwc-vat-label-text {
    font-size: 14.5px;
    font-weight: 600;
    color: #374151;
    line-height: 1.4;
}

/* Collapsible fields section */
.mbwc-vat-fields {
    display: none;
    margin-top: 10px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    width: 100%;
    box-sizing: border-box;
}

.mbwc-vat-fields.is-open {
    display: block;
}

.mbwc-vat-section-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    border-bottom: 1px solid #f3f4f6;
    background: #f9fafb;
}

.mbwc-vat-section-header svg {
    width: 16px;
    height: 16px;
    color: #6b7280;
    flex-shrink: 0;
}

.mbwc-vat-section-header span {
    font-size: 13px;
    font-weight: 600;
    color: #4b5563;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.mbwc-vat-form-body {
    padding: 16px;
}

/* 2-column grid then full-width row */
.mbwc-vat-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 12px;
}

.mbwc-vat-row:last-child {
    margin-bottom: 0;
}

.mbwc-vat-field {
    flex: 1 1 calc(50% - 6px);
    min-width: 140px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.mbwc-vat-field.mbwc-vat-full {
    flex: 1 1 100%;
}

.mbwc-vat-field > label {
    font-size: 13px;
    font-weight: 500;
    color: #4b5563;
    margin: 0;
    line-height: 1.4;
}

.mbwc-vat-field > label abbr.required {
    text-decoration: none;
    color: #ef4444;
    margin-left: 2px;
    font-style: normal;
}

.mbwc-vat-field input.input-text {
    width: 100%;
    box-sizing: border-box;
    padding: 9px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    color: #1f2937;
    background: #fff;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    margin: 0;
}

.mbwc-vat-field input.input-text:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
}

.mbwc-vat-field input.input-text::placeholder {
    color: #9ca3af;
}

@media (max-width: 600px) {
    .mbwc-vat-field {
        flex: 1 1 100%;
    }
}
</style>

<div class="mbwc-vat-wrap">
    <label class="mbwc-vat-toggle-row" id="mbwc-vat-toggle-row">
        <span class="mbwc-vat-switch" aria-hidden="true">
            <input
                class="mb_hpwc_invoice_vat_input"
                type="checkbox"
                name="mb_hpwc_invoice_vat_input"
                value="1"
                id="mbwc_vat_checkbox"
                autocomplete="off"
            >
            <span class="mbwc-vat-slider"></span>
        </span>
        <span class="mbwc-vat-label-text">Xuất hóa đơn VAT</span>
    </label>

    <div class="mbwc-vat-fields" id="mbwc-vat-fields" aria-hidden="true">
        <div class="mbwc-vat-section-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1z"/>
                <line x1="8" y1="8" x2="16" y2="8"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
                <line x1="8" y1="16" x2="12" y2="16"/>
            </svg>
            <span>Thông tin xuất hóa đơn</span>
        </div>

        <div class="mbwc-vat-form-body">
            <div class="mbwc-vat-row">
                <div class="mbwc-vat-field" id="billing_vat_company_field">
                    <label for="billing_vat_company">
                        Tên công ty&nbsp;<abbr class="required" title="bắt buộc">*</abbr>
                    </label>
                    <input
                        type="text"
                        class="input-text"
                        name="billing_vat_company"
                        id="billing_vat_company"
                        placeholder="VD: Công ty TNHH ABC"
                        value="<?php echo esc_attr( isset( $_POST['billing_vat_company'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_vat_company'] ) ) : '' ); ?>"
                        autocomplete="organization"
                    >
                </div>

                <div class="mbwc-vat-field" id="billing_vat_tax_code_field">
                    <label for="billing_vat_tax_code">
                        Mã số thuế&nbsp;<abbr class="required" title="bắt buộc">*</abbr>
                    </label>
                    <input
                        type="text"
                        class="input-text"
                        name="billing_vat_tax_code"
                        id="billing_vat_tax_code"
                        placeholder="VD: 0123456789"
                        value="<?php echo esc_attr( isset( $_POST['billing_vat_tax_code'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_vat_tax_code'] ) ) : '' ); ?>"
                    >
                </div>
            </div>

            <div class="mbwc-vat-row">
                <div class="mbwc-vat-field mbwc-vat-full" id="billing_vat_company_address_field">
                    <label for="billing_vat_company_address">
                        Địa chỉ&nbsp;<abbr class="required" title="bắt buộc">*</abbr>
                    </label>
                    <span class="woocommerce-input-wrapper">
                        <input
                            type="text"
                            class="input-text"
                            name="billing_vat_company_address"
                            id="billing_vat_company_address"
                            placeholder="VD: 123 Đường Lê Lợi, Q.1, TP.HCM"
                            value="<?php echo esc_attr( isset( $_POST['billing_vat_company_address'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_vat_company_address'] ) ) : '' ); ?>"
                            autocomplete="street-address"
                        >
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function mbwcVatToggle() {
    var checkbox = document.getElementById('mbwc_vat_checkbox');
    var fields   = document.getElementById('mbwc-vat-fields');
    var row      = document.getElementById('mbwc-vat-toggle-row');

    if (!checkbox || !fields || !row) return;

    var STORAGE_KEY = 'mbwc_vat_checked';

    function applyState(checked) {
        checkbox.checked = !!checked;
        if (checked) {
            fields.classList.add('is-open');
            fields.setAttribute('aria-hidden', 'false');
            row.classList.add('is-checked');
        } else {
            fields.classList.remove('is-open');
            fields.setAttribute('aria-hidden', 'true');
            row.classList.remove('is-checked');
        }
    }

    // Restore from sessionStorage so WC AJAX checkout refresh doesn't collapse the panel
    var stored = sessionStorage.getItem(STORAGE_KEY);
    applyState(checkbox.checked || stored === '1');

    checkbox.addEventListener('change', function () {
        sessionStorage.setItem(STORAGE_KEY, this.checked ? '1' : '0');
        applyState(this.checked);
    });
}());
</script>
