<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
whp_get_shared('header'); 

$active_subtab = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'advance';
?>
<style>
.mb-wph-sub-tabs {
    display: flex;
    gap: 6px;
    background: #fff;
    padding: 8px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-bottom: 22px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    flex-wrap: wrap;
}
.mb-wph-sub-tab {
    padding: 9px 16px;
    text-decoration: none;
    color: #64748b;
    font-weight: 500;
    font-size: 13px;
    border-radius: 8px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    flex: 1;
    justify-content: center;
    white-space: nowrap;
}
.mb-wph-sub-tab:hover {
    background: #f8fafc;
    color: #0f172a;
}
.mb-wph-sub-tab svg { flex-shrink: 0; transition: color 0.2s; }

/* Accent per tab when active */
.mb-wph-sub-tab.active-advance  { background: #eff2fe; color: #3858e9; font-weight: 700; }
.mb-wph-sub-tab.active-wallet   { background: #dcfce7; color: #16a34a; font-weight: 700; }
.mb-wph-sub-tab.active-payment  { background: #eef2ff; color: #4f46e5; font-weight: 700; }
.mb-wph-sub-tab.active-ecommerce{ background: #fff7ed; color: #ea580c; font-weight: 700; }
.mb-wph-sub-tab.active-cta      { background: #f5f3ff; color: #7c3aed; font-weight: 700; }
.mb-wph-sub-tab.active-thankyou { background: #fff1f2; color: #e11d48; font-weight: 700; }
</style>

<?php
$tab_defs = [
    'advance'   => [
        'label' => __('Cửa hàng nâng cao', 'whp'),
        'icon'  => '<circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>',
        'color' => '#3858e9',
    ],
    'wallet'    => [
        'label' => __('Thanh toán ví điện tử', 'whp'),
        'icon'  => '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/><path d="M15 14h2"/>',
        'color' => '#16a34a',
    ],
    'payment'   => [
        'label' => __('Mẫu thông tin', 'whp'),
        'icon'  => '<rect x="5" y="3" width="14" height="18" rx="2"/><line x1="8" y1="8" x2="16" y2="8"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="8" y1="16" x2="12" y2="16"/>',
        'color' => '#4f46e5',
    ],
    'ecommerce' => [
        'label' => __('Liên kết sàn TMĐT', 'whp'),
        'icon'  => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'color' => '#ea580c',
    ],
    'cta'       => [
        'label' => __('Nút mua hàng (CTA)', 'whp'),
        'icon'  => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
        'color' => '#7c3aed',
    ],
    'thankyou'  => [
        'label' => __('Trang đơn hàng thành công', 'whp'),
        'icon'  => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'color' => '#e11d48',
    ],
];
?>

<div class="mb-wph-sub-tabs">
<?php foreach ($tab_defs as $key => $tab):
    $is_active = $active_subtab === $key;
    $active_class = $is_active ? "active-{$key}" : '';
    $icon_color = $is_active ? $tab['color'] : '#94a3b8';
?>
    <a href="<?php echo admin_url("admin.php?page=mb-wphelper-woocommerce-advance&subtab={$key}"); ?>"
       class="mb-wph-sub-tab <?php echo $active_class; ?>">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
             stroke="<?php echo esc_attr($icon_color); ?>"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <?php echo $tab['icon']; ?>
        </svg>
        <?php echo esc_html($tab['label']); ?>
    </a>
<?php endforeach; ?>
</div>

<div class="mb-wph-sub-tab-content">
    <?php
    switch ($active_subtab) {
        case 'wallet':
            $this->whp_woocommerce_wallet_content();
            break;
        case 'payment':
            $this->whp_woocommerce_payment_content();
            break;
        case 'ecommerce':
            $this->whp_woocommerce_ecommerce_content();
            break;
        case 'cta':
            $this->whp_woocommerce_cta_content();
            break;
        case 'thankyou':
            $this->whp_woocommerce_thankyou_content();
            break;
        case 'advance':
        default:
            $this->whp_woocommerce_advance_content();
            break;
    }
    ?>
</div>

<?php 
whp_get_shared('footer'); 
?>
