<?php
defined('ABSPATH') || exit;
$ocr_receipt_required = function_exists('whp_get_setting') && whp_get_setting('whp_aipay_ocr_enable') === '1';
?>
<!-- TRANSFER CONFIRMATION MODAL -->
<div class="whp-ty__modal" id="whp-transfer-modal" role="dialog" aria-modal="true" aria-labelledby="whp-transfer-modal-title" style="display:none">
  <div class="whp-ty__modal-box whp-ty__modal-box--transfer">
    <div class="whp-tfm-header">
      <div class="whp-tfm-header-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><path d="M15 14h2"/>
        </svg>
      </div>
      <h3 class="whp-tfm-header-title" id="whp-transfer-modal-title">Xác nhận chuyển khoản</h3>
      <p class="whp-tfm-header-desc">Điền thông tin để chúng tôi xác minh thanh toán nhanh hơn.</p>
    </div>
    <!-- Nút đóng đặt SAU header (z-order cao hơn), positioned tương đối với .whp-ty__modal-box (position:relative) -->
    <button id="whp-transfer-modal-close" type="button" aria-label="Đóng"
      style="position:absolute !important;top:20px !important;right:20px !important;z-index:10 !important;background:rgba(255,255,255,0.22) !important;color:#fff !important;border:none !important;border-radius:50% !important;width:28px !important;height:28px !important;min-width:0 !important;min-height:0 !important;padding:0 !important;margin:0 !important;display:flex !important;align-items:center !important;justify-content:center !important;font-size:18px !important;line-height:1 !important;cursor:pointer !important;box-shadow:none !important;outline:none !important;box-sizing:content-box !important;transition:background .2s !important;"
      onmouseover="this.style.setProperty('background','rgba(255,255,255,0.38)','important')"
      onmouseout="this.style.setProperty('background','rgba(255,255,255,0.22)','important')">&times;</button>
    <style>
    .whp-tfm-header {
      background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
      border-radius: 16px 16px 0 0;
      padding: 22px 22px 20px;
      text-align: center;
      margin: -24px -24px 20px;
    }
    .whp-tfm-header-icon {
      width: 52px; height: 52px;
      background: rgba(255,255,255,0.18);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 12px;
      border: 2px solid rgba(255,255,255,0.3);
    }
    .whp-tfm-header-title {
      color: #fff !important;
      font-size: 18px !important;
      font-weight: 700 !important;
      margin: 0 0 6px !important;
      letter-spacing: -0.2px;
    }
    .whp-tfm-header-desc {
      color: rgba(255,255,255,0.85) !important;
      font-size: 13px !important;
      margin: 0 !important;
      line-height: 1.5;
    }
    </style>

    <form id="whp-transfer-form" class="whp-transfer-form" novalidate data-receipt-required="<?php echo $ocr_receipt_required ? '1' : '0'; ?>">
      <div class="whp-transfer-form__row">
        <label class="whp-transfer-form__label" for="whp-tf-name">Họ tên người chuyển <span class="whp-req">*</span></label>
        <input class="whp-transfer-form__input" type="text" id="whp-tf-name" name="sender_name" placeholder="Nguyễn Văn A" required autocomplete="name">
        <div class="whp-transfer-form__error" data-for="whp-tf-name">Vui lòng nhập họ tên</div>
      </div>

      <div class="whp-transfer-form__row whp-transfer-form__row--half">
        <div>
          <label class="whp-transfer-form__label" for="whp-tf-bank">Ngân hàng <span class="whp-req">*</span></label>
          <select class="whp-transfer-form__input" id="whp-tf-bank" name="bank" required>
            <option value="">-- Chọn ngân hàng --</option>
            <?php
            $banks = [
                'Vietcombank','Techcombank','VietinBank','BIDV','Agribank',
                'MB Bank','ACB','Sacombank','VPBank','TPBank','Vietbank',
                'MSB','SeABank','OCB','VIB','SHB','HDBank','LienVietPostBank',
                'NamABank','ABBank','Kienlongbank','BaoVietBank','PBBANK','NCB',
                'PGBank','VietABank','CBBank','GPBank','DongABank','MoMo','ZaloPay','VNPay','ShopeePay','Khác',
            ];
            foreach ($banks as $b): ?>
            <option value="<?php echo esc_attr($b); ?>"><?php echo esc_html($b); ?></option>
            <?php endforeach; ?>
          </select>
          <div class="whp-transfer-form__error" data-for="whp-tf-bank">Vui lòng chọn ngân hàng</div>
        </div>
        <div>
          <label class="whp-transfer-form__label" for="whp-tf-last4">4 số cuối TK <span class="whp-req">*</span></label>
          <input class="whp-transfer-form__input" type="text" id="whp-tf-last4" name="last4" placeholder="1234" maxlength="4" pattern="\d{4}" inputmode="numeric" required>
          <div class="whp-transfer-form__error" data-for="whp-tf-last4">Nhập đúng 4 chữ số</div>
        </div>
      </div>

      <div class="whp-transfer-form__row">
        <label class="whp-transfer-form__label" for="whp-tf-receipt">
          Ảnh biên lai
          <?php if ( $ocr_receipt_required ) : ?>
          <span class="whp-req">*</span>
          <?php else : ?>
          <span class="whp-transfer-form__optional">(không bắt buộc)</span>
          <?php endif; ?>
        </label>
        <div class="whp-transfer-form__upload" id="whp-tf-upload-area">
          <input type="file" id="whp-tf-receipt" name="receipt" accept="image/*" class="whp-transfer-form__file-input">
          <div class="whp-transfer-form__upload-placeholder" id="whp-tf-upload-ph">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            <span>Nhấn để chọn ảnh hoặc kéo vào đây</span>
            <span style="font-size:12px;color:#94a3b8">JPG, PNG, WEBP — tối đa 5MB</span>
          </div>
          <div class="whp-transfer-form__upload-preview" id="whp-tf-upload-preview" style="display:none">
            <img id="whp-tf-preview-img" src="" alt="Biên lai" style="max-height:160px;border-radius:8px;object-fit:contain">
            <button type="button" class="whp-transfer-form__upload-remove" id="whp-tf-remove-receipt">&times; Xóa ảnh</button>
          </div>
          <div class="whp-transfer-form__upload-progress" id="whp-tf-upload-progress" style="display:none">
            <div class="whp-transfer-form__progress-bar"><div class="whp-transfer-form__progress-fill" id="whp-tf-progress-fill"></div></div>
            <span id="whp-tf-progress-text">Đang tải lên...</span>
          </div>
        </div>
        <div class="whp-transfer-form__error" data-for="whp-tf-receipt">Vui lòng tải lên ảnh biên lai thanh toán</div>
      </div>

      <div class="whp-transfer-form__row">
        <label class="whp-transfer-form__label" for="whp-tf-notes">Ghi chú <span class="whp-transfer-form__optional">(không bắt buộc)</span></label>
        <textarea class="whp-transfer-form__input whp-transfer-form__textarea" id="whp-tf-notes" name="notes" placeholder="Ví dụ: Tôi chuyển khoản vào lúc 14:30 ngày hôm nay..." rows="3"></textarea>
      </div>

      <div class="whp-transfer-form__actions">
        <button type="submit" class="whp-ty__btn whp-ty__btn--primary" id="whp-transfer-form-submit" style="width:100%"
                style="background:linear-gradient(90deg,var(--whp-accent),color-mix(in srgb,var(--whp-accent) 78%,#000));box-shadow:0 4px 14px color-mix(in srgb,var(--whp-accent) 35%,transparent)">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Xác nhận đã chuyển khoản
        </button>
      </div>
    </form>

    <!-- Success state (non-OCR) -->
    <div id="whp-transfer-form-success" style="display:none" class="whp-transfer-form__success">
      <div style="text-align:center;padding:24px 0">
        <div style="width:56px;height:56px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <div style="font-size:16px;font-weight:700;color:#15803d;margin-bottom:6px">Đã ghi nhận!</div>
        <div style="font-size:13.5px;color:#64748b;line-height:1.6">Chúng tôi sẽ xác minh thanh toán và cập nhật trạng thái đơn hàng sớm nhất có thể.</div>
      </div>
    </div>

    <!-- AI verify progress (shown after form saved, while AI is running) -->
    <div id="whp-ai-verify-wrap" style="display:none">
      <div class="whp-ai-vp-label">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:5px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span id="whp-ai-vp-text">Đang xác minh thanh toán bằng AI...</span>
      </div>
      <div class="whp-ai-vp-bar-wrap">
        <div class="whp-ai-vp-bar-fill" id="whp-ai-vp-fill" style="width:0%"></div>
      </div>
      <div class="whp-ai-vp-pct" id="whp-ai-vp-pct">0%</div>
    </div>

    <!-- AI success state -->
    <div id="whp-ai-success" style="display:none">
      <div class="whp-ai-result whp-ai-result--success">
        <div class="whp-ai-result__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="whp-ai-result__title">Xác minh thành công!</div>
        <div class="whp-ai-result__desc">Thanh toán của bạn đã được xác nhận. Đơn hàng đang được xử lý.</div>
        <div class="whp-ai-result__conf" id="whp-ai-success-conf"></div>
        <div class="whp-ai-manual-review-notice whp-ai-manual-review-notice--success">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          Chúng tôi đã ghi nhận và sẽ cập nhật trạng thái đơn hàng sớm nhất. Email xác nhận sẽ được gửi đến bạn ngay sau khi hoàn tất.
        </div>
      </div>
    </div>

    <!-- AI failure state -->
    <div id="whp-ai-failure" style="display:none">
      <div class="whp-ai-result whp-ai-result--fail">
        <div class="whp-ai-result__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="whp-ai-result__title">Biên lai không khớp</div>
        <div class="whp-ai-result__desc" id="whp-ai-fail-reason">Hệ thống không thể xác minh biên lai tự động.</div>
        <div class="whp-ai-result__flags" id="whp-ai-fail-flags"></div>
        <div class="whp-ai-manual-review-notice">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          Chúng tôi đã nhận được thông tin và sẽ đối chiếu lại thủ công. Đội ngũ hỗ trợ sẽ liên hệ với bạn sớm nhất để xác nhận đơn hàng.
        </div>
      </div>
    </div>

  </div>
</div>

<style>
.whp-transfer-form { display: flex; flex-direction: column; gap: 16px; margin-top: 16px; }
.whp-transfer-form__row { display: flex; flex-direction: column; gap: 5px; }
.whp-transfer-form__row--half { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.whp-transfer-form__row--half > div { min-width: 0; }
.whp-transfer-form__label { font-size: 13px; font-weight: 600; color: #374151; }
.whp-req { color: #ef4444; }
.whp-transfer-form__optional { font-weight: 400; color: #9ca3af; font-size: 12px; }
.whp-transfer-form__input {
  width: 100%; padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px;
  font-size: 14px; color: #0f172a; background: #fff; transition: border-color .15s;
  box-sizing: border-box; outline: none; font-family: inherit;
}
/* ID selector (specificity 1,1,0) beats Flatsome's input[type=text]{border-radius:0} (0,0,1) */
#whp-transfer-modal .whp-transfer-form__input { border-radius: 8px; }
/* !important needed to beat Flatsome's select overrides */
select.whp-transfer-form__input {
  -webkit-appearance: none !important;
  -moz-appearance: none !important;
  appearance: none !important;
  color: #0f172a !important;
  border: 1.5px solid #e2e8f0 !important;
  border-radius: 8px !important;
  padding-right: 34px !important;
  background-color: #fff !important;
  background-image: url("data:image/svg+xml,%3Csvg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") !important;
  background-repeat: no-repeat !important;
  background-position: right 10px center !important;
  background-size: 16px !important;
  height: 40px !important;
  cursor: pointer;
}
.whp-transfer-form__input:focus { border-color: var(--whp-accent, #6d28d9); }
.whp-transfer-form__input.whp--invalid { border-color: #ef4444; }
.whp-transfer-form__textarea { resize: vertical; min-height: 72px; }
.whp-transfer-form__error { display: none; font-size: 12px; color: #ef4444; margin-top: 3px; }
.whp-transfer-form__error.whp--show { display: block; }
.whp-transfer-form__upload {
  border: 2px dashed #e2e8f0; border-radius: 10px; padding: 20px 16px;
  text-align: center; cursor: pointer; transition: border-color .2s;
}
.whp-transfer-form__upload:hover { border-color: var(--whp-accent, #6d28d9); }
.whp-transfer-form__upload-placeholder { display: flex; flex-direction: column; align-items: center; gap: 6px; color: #64748b; font-size: 13px; pointer-events: none; }
.whp-transfer-form__file-input { position: fixed; top: -9999px; left: -9999px; opacity: 0; width: 1px; height: 1px; }
.whp-transfer-form__upload-remove { background: none; border: none; color: #ef4444; font-size: 13px; cursor: pointer; margin-top: 8px; }
.whp-transfer-form__progress-bar { height: 4px; background: #e2e8f0; border-radius: 4px; overflow: hidden; margin-bottom: 6px; }
.whp-transfer-form__progress-fill { height: 100%; background: var(--whp-accent, #6d28d9); width: 0; transition: width .3s; }
.whp-transfer-form__actions { display: flex; gap: 10px; justify-content: flex-end; padding-top: 8px; }
#whp-transfer-form-submit { flex:1; justify-content:center; font-weight:700; font-size:14.5px; padding:12px 20px; }
#whp-transfer-form-cancel { min-width:80px; }
.whp-ty__modal-box--transfer { max-width: 520px; }
.whp-ty__modal-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.whp-ty__modal-desc { font-size: 13.5px; color: #64748b; margin: 4px 0 0; }
@media (max-width: 480px) {
  .whp-transfer-form__row--half { grid-template-columns: 1fr; }
  .whp-transfer-form__actions { flex-direction: column-reverse; }
  .whp-transfer-form__actions .whp-ty__btn { width: 100%; justify-content: center; }
}

/* AI Verify Progress */
#whp-ai-verify-wrap { padding: 24px 0 8px; text-align: center; }
.whp-ai-vp-label { font-size: 13.5px; font-weight: 600; color: #374151; margin-bottom: 14px; }
.whp-ai-vp-bar-wrap { height: 8px; background: #e2e8f0; border-radius: 8px; overflow: hidden; margin: 0 auto 8px; max-width: 340px; }
.whp-ai-vp-bar-fill { height: 100%; background: linear-gradient(90deg, var(--whp-accent,#6d28d9), color-mix(in srgb,var(--whp-accent,#6d28d9) 70%,#10b981)); border-radius: 8px; transition: width .3s ease; }
.whp-ai-vp-pct { font-size: 12px; font-weight: 700; color: #94a3b8; }

/* AI Result states */
.whp-ai-result { text-align: center; padding: 20px 0 8px; }
.whp-ai-result__icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
.whp-ai-result--success .whp-ai-result__icon { background: #dcfce7; }
.whp-ai-result--fail    .whp-ai-result__icon { background: #fee2e2; }
.whp-ai-result__title { font-size: 17px; font-weight: 700; margin-bottom: 8px; }
.whp-ai-result--success .whp-ai-result__title { color: #15803d; }
.whp-ai-result--fail    .whp-ai-result__title { color: #b91c1c; }
.whp-ai-result__desc { font-size: 13.5px; color: #64748b; line-height: 1.6; margin-bottom: 10px; }
.whp-ai-result__conf { font-size: 12.5px; font-weight: 600; color: #059669; margin-top: 4px; }
.whp-ai-result__flags { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; margin: 8px 0 14px; }
.whp-ai-result__flags span { background: #fee2e2; color: #b91c1c; font-size: 11.5px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
.whp-ai-manual-review-notice {
  display: flex; align-items: flex-start; gap: 8px; text-align: left;
  background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 8px;
  padding: 11px 14px; font-size: 13px; color: #9a3412; line-height: 1.6;
  margin-top: 4px;
}
.whp-ai-manual-review-notice--success {
  background: #f0fdf4; border-color: #bbf7d0; color: #15803d;
}
</style>
<?php if ( $ocr_receipt_required ) : ?>
<script>
(function(){
  var form = document.getElementById('whp-transfer-form');
  if (!form || form.dataset.receiptRequired !== '1') return;
  var uploadArea = document.getElementById('whp-tf-upload-area');
  var receiptInput = document.getElementById('whp-tf-receipt');
  var errDiv = form.querySelector('[data-for="whp-tf-receipt"]');
  function clearErr() {
    if (errDiv) errDiv.classList.remove('whp--show');
    if (uploadArea) uploadArea.style.borderColor = '';
  }
  function showErr() {
    if (errDiv) errDiv.classList.add('whp--show');
    if (uploadArea) uploadArea.style.borderColor = '#ef4444';
  }
  if (receiptInput) {
    receiptInput.addEventListener('change', clearErr);
  }
  form.addEventListener('submit', function(e) {
    if (!receiptInput || !receiptInput.files || !receiptInput.files.length) {
      e.preventDefault();
      e.stopImmediatePropagation();
      showErr();
      if (uploadArea) uploadArea.scrollIntoView({behavior:'smooth', block:'nearest'});
      return false;
    }
    clearErr();
  }, true);
})();
</script>
<?php endif; ?>
