<?php
defined('ABSPATH') || exit;
// Only show when AI payment is NOT yet activated
if (whp_get_setting('whp_aipay_enable')) return;
?>
<div class="whp-aipay-banner">
  <div class="whp-aipay-banner__inner">
    <div class="whp-aipay-banner__icon">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96-.46 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"/>
        <path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96-.46 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"/>
      </svg>
    </div>
    <div class="whp-aipay-banner__text">
      <div class="whp-aipay-banner__title">AI Thanh toán chưa được kích hoạt</div>
      <div class="whp-aipay-banner__desc">Nâng cấp để xác minh biên lai tự động, phát hiện gian lận và đối soát ngân hàng theo thời gian thực.</div>
      <div class="whp-aipay-banner__features">
        <span class="whp-aipay-banner__feat">🔍 OCR đọc biên lai</span>
        <span class="whp-aipay-banner__feat">🛡️ AI Fraud Detection</span>
        <span class="whp-aipay-banner__feat">✅ AI Copilot xác minh</span>
        <span class="whp-aipay-banner__feat">🔗 Tích hợp Casso/SePay</span>
      </div>
    </div>
    <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment')); ?>" class="whp-aipay-banner__btn" target="_blank">
      Kích hoạt AI Thanh toán
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M7 17 17 7M17 7H7M17 7v10"/>
      </svg>
    </a>
  </div>
</div>
<style>
.whp-aipay-banner {
  background: linear-gradient(135deg,#312e81,#4f46e5);
  border-radius: 14px;
  padding: 18px 20px;
  margin-top: 16px;
  color: #fff;
}
.whp-aipay-banner__inner {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  flex-wrap: wrap;
}
.whp-aipay-banner__icon {
  width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
  background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center;
}
.whp-aipay-banner__text { flex: 1; min-width: 0; }
.whp-aipay-banner__title { font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 4px; }
.whp-aipay-banner__desc { font-size: 12.5px; color: rgba(255,255,255,0.8); line-height: 1.5; margin-bottom: 10px; }
.whp-aipay-banner__features { display: flex; flex-wrap: wrap; gap: 6px; }
.whp-aipay-banner__feat {
  font-size: 12px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
  border-radius: 20px; padding: 3px 9px; white-space: nowrap;
}
.whp-aipay-banner__btn {
  display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
  background: #fff; color: #4f46e5; font-size: 13px; font-weight: 700;
  border-radius: 8px; padding: 9px 16px; text-decoration: none;
  transition: opacity .2s; align-self: center;
}
.whp-aipay-banner__btn:hover { opacity: .88; }
@media (max-width: 560px) {
  .whp-aipay-banner__btn { width: 100%; justify-content: center; }
}
</style>
