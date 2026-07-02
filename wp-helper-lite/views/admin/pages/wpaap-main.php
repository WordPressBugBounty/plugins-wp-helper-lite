<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
whp_get_shared('header');

$active_subtab = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'dashboard';
?>
<style>
.wpaap-sub-tabs {
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
.wpaap-sub-tab {
    padding: 9px 14px;
    text-decoration: none;
    color: #64748b;
    font-weight: 500;
    font-size: 13px;
    border-radius: 8px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    white-space: nowrap;
    flex: 1;
}
.wpaap-sub-tab:hover { background: #f8fafc; color: #0f172a; }
.wpaap-sub-tab svg { flex-shrink: 0; transition: color 0.2s; }

.wpaap-sub-tab.active-dashboard   { background: #eff2fe; color: #3858e9; font-weight: 700; }
.wpaap-sub-tab.active-connection  { background: #eef2ff; color: #4f46e5; font-weight: 700; }
.wpaap-sub-tab.active-limits      { background: #ecfeff; color: #0891b2; font-weight: 700; }
.wpaap-sub-tab.active-writer      { background: #f5f3ff; color: #7c3aed; font-weight: 700; }
.wpaap-sub-tab.active-security    { background: #fef2f2; color: #dc2626; font-weight: 700; }
.wpaap-sub-tab.active-seo         { background: #f0fdf4; color: #16a34a; font-weight: 700; }
.wpaap-sub-tab.active-ai-payment  { background: #fff1f2; color: #e11d48; font-weight: 700; }
.wpaap-sub-tab.active-maintenance { background: #fffbeb; color: #d97706; font-weight: 700; }
</style>

<?php
$tab_defs = [
    'dashboard' => [
        'label' => __('AI Tổng quan', 'whp'),
        'color_active' => '#3858e9',
        'icon' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
    ],
    'connection' => [
        'label' => __('AI Kết nối', 'whp'),
        'color_active' => '#4f46e5',
        'icon' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
    ],
    'limits' => [
        'label' => __('Thống Kê Tokens AI', 'whp'),
        'color_active' => '#0891b2',
        'icon' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
    ],
    'writer' => [
        'label' => __('AI Viết Bài', 'whp'),
        'color_active' => '#7c3aed',
        'icon' => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
    ],
    'security' => [
        'label' => __('AI Bảo Mật', 'whp'),
        'color_active' => '#dc2626',
        'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    ],
    'seo' => [
        'label' => __('AI SEO', 'whp'),
        'color_active' => '#16a34a',
        'icon' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/>',
    ],
    'ai-payment' => [
        'label' => __('AI Thanh Toán', 'whp'),
        'color_active' => '#e11d48',
        'icon' => '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><path d="M15 14h2"/>',
    ],
    'maintenance' => [
        'label' => __('Trang Bảo Trì', 'whp'),
        'color_active' => '#d97706',
        'icon' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
    ],
];
?>

<div class="wpaap-sub-tabs">
<?php foreach ($tab_defs as $key => $tab):
    $is_active = $active_subtab === $key;
    $active_class = $is_active ? "active-{$key}" : '';
    $icon_color   = $is_active ? $tab['color_active'] : '#94a3b8';
?>
    <a href="<?php echo admin_url("admin.php?page=mb-wphelper-ai&subtab={$key}"); ?>"
       class="wpaap-sub-tab <?php echo $active_class; ?>">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
             stroke="<?php echo esc_attr($icon_color); ?>"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <?php echo $tab['icon']; ?>
        </svg>
        <?php echo esc_html($tab['label']); ?>
    </a>
<?php endforeach; ?>
</div>

<div class="wpaap-sub-tab-content">
    <?php
    switch ($active_subtab) {
        case 'connection':
            wpaap_connection_page_layout();
            break;
        case 'limits':
            wpaap_limits_page_layout();
            break;
        case 'writer':
            wpaap_admin_page_layout();
            break;
        case 'security':
            wpaap_security_page_layout();
            break;
        case 'seo':
            wpaap_seo_page_layout();
            break;
        case 'ai-payment':
            wpaap_ai_payment_tab_layout();
            break;
        case 'maintenance':
            wpaap_maintenance_tab_layout();
            break;
        case 'dashboard':
        default:
            wpaap_dashboard_page_layout();
            break;
    }
    ?>
</div>

<?php
whp_get_shared('footer');
?>
