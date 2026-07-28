<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
whp_get_shared('header'); 

$active_subtab = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'advance';

$whp_bct_confirmed  = whp_bct_is_registered();
$whp_bct_config_url = admin_url('admin.php?page=mb-wphelper-woocommerce-advance&subtab=bct');
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
.mb-wph-sub-tab.active-bct      { background: #eff2fe; color: #3858e9; font-weight: 700; }

/* ── Popup pháp lý Bộ Công Thương ── */
.whp-bct-legal-modal { background:#fff; border-radius:20px; max-width:660px; width:100%; padding:44px 44px 36px; position:relative; box-shadow:0 24px 70px rgba(15,23,42,.3); text-align:center; max-height:calc(100vh - 40px); overflow-y:auto; }
.whp-bct-legal-close { position:absolute; top:18px; right:18px; width:32px; height:32px; border-radius:50%; background:#f1f5f9; border:none; cursor:pointer; color:#64748b; display:flex; align-items:center; justify-content:center; transition:background .15s,color .15s; }
.whp-bct-legal-close:hover { background:#e2e8f0; color:#0f172a; }
.whp-bct-legal-icon-wrap { position:relative; width:128px; height:128px; margin:0 auto 22px; border-radius:50%; background:radial-gradient(circle,#eef2ff 0%,rgba(238,242,255,0) 72%); display:flex; align-items:center; justify-content:center; }
.whp-bct-legal-icon { width:72px; height:72px; border-radius:22px; background:linear-gradient(135deg,#4a67ee,#2b46d6); display:flex; align-items:center; justify-content:center; box-shadow:0 14px 28px -10px rgba(56,88,233,.55); }
.whp-bct-legal-dot { position:absolute; }
.whp-bct-legal-dot.d1 { top:10px; left:22px; width:9px; height:9px; background:#93c5fd; border-radius:3px; transform:rotate(20deg); }
.whp-bct-legal-dot.d2 { top:30px; right:8px; width:7px; height:7px; background:#fbbf24; border-radius:2px; transform:rotate(45deg); }
.whp-bct-legal-dot.d3 { bottom:14px; left:6px; width:6px; height:6px; background:#6ee7b7; border-radius:50%; }
.whp-bct-legal-dot.d4 { bottom:26px; right:18px; width:8px; height:8px; background:#c4b5fd; border-radius:2px; transform:rotate(30deg); }
.whp-bct-legal-eyebrow { display:inline-block; margin:0 0 14px; padding:5px 14px; border-radius:99px; background:#eef2ff; color:#3858e9; font-size:11.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
.whp-bct-legal-title { font-size:23px; font-weight:800; color:#0f172a; margin:0 0 14px; line-height:1.28; }
.whp-bct-legal-bar { width:46px; height:3px; border-radius:2px; background:#3858e9; margin:0 auto 20px; }
.whp-bct-legal-desc { font-size:14.5px; color:#475569; line-height:1.75; max-width:600px; margin:0 auto 22px; }
.whp-bct-legal-desc strong { color:#3858e9; }
.whp-bct-legal-info { display:flex; gap:12px; align-items:flex-start; text-align:left; background:#f8fafc; border:1px solid #e2e8f0; border-radius:13px; padding:16px 18px; margin-bottom:24px; }
.whp-bct-legal-info .ic { flex-shrink:0; width:22px; height:22px; color:#3858e9; margin-top:1px; }
.whp-bct-legal-info h3 { font-size:13.5px; font-weight:700; color:#0f172a; margin:0 0 4px; }
.whp-bct-legal-info p { font-size:12.5px; color:#64748b; margin:0; line-height:1.65; }
.whp-bct-legal-actions { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:22px; }
.whp-bct-legal-action { flex:1 1 160px; display:flex; flex-direction:column; align-items:flex-start; gap:10px; padding:16px 16px 15px; border-radius:13px; border:1.5px solid #e2e8f0; background:#fff; text-decoration:none; cursor:pointer; text-align:left; font-family:inherit; transition:border-color .15s,background .15s,transform .15s; }
.whp-bct-legal-action:hover { border-color:#3858e9; background:#f8faff; transform:translateY(-1px); }
.whp-bct-legal-action .ic { width:34px; height:34px; border-radius:9px; background:#eef2ff; color:#3858e9; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.whp-bct-legal-action .lbl { font-size:13.5px; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:5px; }
.whp-bct-legal-action .sub { font-size:11.5px; color:#94a3b8; }
.whp-bct-legal-action.is-primary { background:linear-gradient(135deg,#4a67ee,#2b46d6); border-color:transparent; }
.whp-bct-legal-action.is-primary:hover { background:linear-gradient(135deg,#3d59e0,#2440c9); }
.whp-bct-legal-action.is-primary .ic { background:rgba(255,255,255,.18); color:#fff; }
.whp-bct-legal-action.is-primary .lbl { color:#fff; }
.whp-bct-legal-action.is-primary .sub { color:rgba(255,255,255,.78); }
.whp-bct-legal-footer { display:flex; align-items:center; justify-content:center; gap:8px; padding-top:18px; border-top:1px solid #f1f5f9; font-size:12px; color:#64748b; }
.whp-bct-legal-footer svg { flex-shrink:0; color:#16a34a; }
@media (max-width:600px) {
    .whp-bct-legal-modal { padding:32px 22px 26px; border-radius:16px; }
    .whp-bct-legal-actions { flex-direction:column; }
}
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
    'bct'       => [
        'label' => __('Bộ Công Thương', 'whp'),
        'icon'  => '<path d="M12 2 3 7v2h18V7L12 2z"/><path d="M5 10v8M9 10v8M15 10v8M19 10v8"/><path d="M3 20h18"/>',
        'color' => '#3858e9',
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
        case 'bct':
            $this->whp_woocommerce_bct_content();
            break;
        case 'advance':
        default:
            $this->whp_woocommerce_advance_content();
            break;
    }
    ?>
</div>

<?php if ( $active_subtab !== 'bct' && ! $whp_bct_confirmed ) : ?>
<div id="whp-bct-modal-overlay" style="position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:100000;display:none;align-items:center;justify-content:center;padding:20px">
    <div class="whp-bct-legal-modal">
        <button type="button" id="whp-bct-modal-close" class="whp-bct-legal-close" aria-label="<?php esc_attr_e('Đóng', 'whp'); ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <div class="whp-bct-legal-icon-wrap">
            <span class="whp-bct-legal-dot d1"></span>
            <span class="whp-bct-legal-dot d2"></span>
            <span class="whp-bct-legal-dot d3"></span>
            <span class="whp-bct-legal-dot d4"></span>
            <div class="whp-bct-legal-icon">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 7v2h18V7L12 2z"/><path d="M5 10v8M9 10v8M15 10v8M19 10v8"/><path d="M3 20h18"/></svg>
            </div>
        </div>

        <span class="whp-bct-legal-eyebrow"><?php esc_html_e('Lưu ý pháp lý', 'whp'); ?></span>

        <h2 class="whp-bct-legal-title"><?php esc_html_e('Đăng ký/Thông báo Bộ Công Thương', 'whp'); ?></h2>
        <div class="whp-bct-legal-bar"></div>
        <p class="whp-bct-legal-desc">
            <?php
            echo wp_kses(
                sprintf(
                    /* translators: %s: "Bộ Công Thương" được in đậm */
                    __( 'Nếu website có chức năng bán hàng/thanh toán trực tuyến, theo Nghị định 52/2013/NĐ-CP (sửa đổi, bổ sung bởi Nghị định 85/2021/NĐ-CP) về thương mại điện tử, chủ website cần thông báo hoặc đăng ký với %s trước khi vận hành.', 'whp' ),
                    '<strong>' . esc_html__( 'Bộ Công Thương', 'whp' ) . '</strong>'
                ),
                [ 'strong' => [] ]
            );
            ?>
        </p>

        <div class="whp-bct-legal-info">
            <svg class="ic" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <div>
                <h3><?php esc_html_e('Đối tượng áp dụng', 'whp'); ?></h3>
                <p><?php esc_html_e('Áp dụng cho website có chức năng đặt hàng, thanh toán trực tuyến hoặc cung cấp dịch vụ thương mại điện tử.', 'whp'); ?></p>
            </div>
        </div>

        <div class="whp-bct-legal-actions">
            <a href="https://www.matbao.net/ten-mien/thong-bao-website-voi-bo-cong-thuong" target="_blank" rel="noopener noreferrer" class="whp-bct-legal-action">
                <span class="ic"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></span>
                <span class="lbl"><?php esc_html_e('Đăng ký bộ công thương ngay', 'whp'); ?></span>
                <span class="sub"><?php esc_html_e('Hướng dẫn đăng ký chi tiết', 'whp'); ?></span>
            </a>
            <a href="<?php echo esc_url($whp_bct_config_url); ?>" class="whp-bct-legal-action is-primary">
                <span class="ic"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 7v2h18V7L12 2z"/><path d="M5 10v8M9 10v8M15 10v8M19 10v8"/><path d="M3 20h18"/></svg></span>
                <span class="lbl"><?php esc_html_e('Đi đến Cấu hình Bộ Công Thương', 'whp'); ?> →</span>
                <span class="sub"><?php esc_html_e('Thiết lập & kiểm tra thông báo', 'whp'); ?></span>
            </a>
        </div>

        <div class="whp-bct-legal-footer">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 7v6c0 5.25 3.75 9.75 9 11 5.25-1.25 9-5.75 9-11V7l-9-5z"/><polyline points="9 12 11 14 15 10"/></svg>
            <?php esc_html_e('Thông tin của bạn được bảo mật và chỉ sử dụng cho mục đích xác thực.', 'whp'); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ( $active_subtab !== 'bct' && ! $whp_bct_confirmed ) : ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var overlay  = document.getElementById('whp-bct-modal-overlay');
    var closeBtn = document.getElementById('whp-bct-modal-close');
    var DISMISS_KEY = 'whp_bct_popup_dismissed';
    // Đưa overlay ra làm con trực tiếp của <body> để position:fixed luôn phủ đúng
    // toàn bộ viewport, tránh bị "nhốt" nếu div cha nào đó có transform/filter.
    if (overlay && overlay.parentNode !== document.body) {
        document.body.appendChild(overlay);
    }
    // Chỉ tự hiện 1 lần: nếu trước đó admin đã bấm Đóng (X)/click ra ngoài trên
    // trình duyệt này thì không tự hiện lại nữa, cho tới khi xác nhận đăng ký
    // thành công (lúc đó cờ dismissed được xoá — xem khối script bên dưới).
    var dismissed = false;
    try { dismissed = localStorage.getItem(DISMISS_KEY) === '1'; } catch (e) {}
    if (overlay && !dismissed) {
        overlay.style.display = 'flex';
    }
    function dismiss() {
        try { localStorage.setItem(DISMISS_KEY, '1'); } catch (e) {}
        if (overlay) overlay.remove();
    }
    if (overlay && closeBtn) {
        closeBtn.addEventListener('click', dismiss);
        overlay.addEventListener('click', function(e){ if (e.target === overlay) dismiss(); });
    }
});
</script>
<?php elseif ( $active_subtab !== 'bct' && $whp_bct_confirmed ) : ?>
<script>
try { localStorage.removeItem('whp_bct_popup_dismissed'); } catch (e) {}
</script>
<?php endif; ?>

<?php
whp_get_shared('footer');
?>
