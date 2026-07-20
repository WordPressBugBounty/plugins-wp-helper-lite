/**
 * WP Helper Lite — Thank You Page
 * Handles: countdown, copy buttons, transfer form modal, support modal, invoice print.
 * Requires: jQuery (provided by WooCommerce), window.whpThankyou config.
 */
jQuery(function ($) {
    'use strict';

    if (!window.whpThankyou) return;

    var cfg = window.whpThankyou;

    /* =========================================================
     * 1. COUNTDOWN TIMER
     * ========================================================= */
    var countdownInterval = null;
    if (cfg.expire_at > 0) {
        countdownInterval = setInterval(function () {
            var now       = Math.floor(Date.now() / 1000);
            var remaining = cfg.expire_at - now;

            if (remaining <= 0) {
                clearInterval(countdownInterval);
                $('#whp-ty-countdown').hide();
                $('#whp-ty-expired-msg').show();
                $.post(cfg.ajax_url, {
                    action   : 'whp_cancel_order_expired',
                    nonce    : cfg.cancel_nonce,
                    order_id : cfg.order_id
                }).always(function () {
                    setTimeout(function () { location.reload(); }, 1500);
                });
                return;
            }

            var hours = Math.floor(remaining / 3600);
            var mins  = Math.floor((remaining % 3600) / 60);
            var secs  = remaining % 60;
            function pad(n) { return n < 10 ? '0' + n : String(n); }
            $('#whp-ty-countdown-display').text(pad(hours) + ':' + pad(mins) + ':' + pad(secs));
            if (remaining < 300) {
                $('.whp-ty__countdown').addClass('whp-ty__countdown--warning');
            }
        }, 1000);
    }

    /* =========================================================
     * 2. COPY BUTTONS
     * ========================================================= */
    $(document).on('click', '.whp-ty-copy-btn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var copyText = $btn.data('copy');
        if (!copyText) return;
        var originalHtml = $btn.html();
        function onCopySuccess() {
            $btn.addClass('whp-ty__copy-btn--done').text('✓ Đã sao chép');
            setTimeout(function () {
                $btn.removeClass('whp-ty__copy-btn--done').html(originalHtml);
            }, 2000);
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(String(copyText)).then(onCopySuccess, function () {
                execCommandCopy(copyText, onCopySuccess);
            });
        } else {
            execCommandCopy(copyText, onCopySuccess);
        }
    });

    function execCommandCopy(text, callback) {
        var $ta = $('<textarea>').val(text).css({position:'fixed',top:0,left:0,opacity:0}).appendTo('body');
        $ta[0].select();
        try { document.execCommand('copy'); if (typeof callback === 'function') callback(); } catch (err) {}
        $ta.remove();
    }

    /* =========================================================
     * 3. TRANSFER FORM MODAL
     * ========================================================= */
    var $transferModal = $('#whp-transfer-modal');
    var $transferForm  = $('#whp-transfer-form');
    var receiptUrl     = '';
    var isUploading    = false;
    // Xác nhận chuyển khoản thành công rồi mới cần reload khi đóng popup — để đồng bộ
    // toàn bộ trang (badge trạng thái, Tình trạng đơn hàng...) theo dữ liệu server mới nhất,
    // thay vì tự cập nhật tay từng phần tử một (dễ sót, như badge trạng thái ở đầu trang).
    var _transferJustConfirmed = false;

    var SUBMIT_ICON = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> ';

    function openTransferModal() {
        if ($transferModal.parent()[0] !== document.body) {
            $(document.body).append($transferModal);
        }
        // Reset form to clean state on every open
        _transferJustConfirmed = false;
        $('#whp-transfer-form-success').hide();
        $('#whp-ai-verify-wrap').hide();
        $('#whp-ai-success').hide();
        $('#whp-ai-failure').hide();
        $transferForm.show();
        $transferForm[0].reset();
        $transferForm.find('.whp--invalid').removeClass('whp--invalid');
        $transferForm.find('.whp--show').removeClass('whp--show');
        $('#whp-transfer-form-submit').prop('disabled', false)
            .html(SUBMIT_ICON + 'Xác nhận đã chuyển khoản');
        receiptUrl = '';
        isUploading = false;
        $previewWrap.hide();
        $uploadPh.show();
        $previewImg.attr('src', '');

        $transferModal.css('display', 'flex');
        $('html, body').css('overflow', 'hidden');
        $('#whp-tf-name').focus();
    }
    function closeTransferModal() {
        $transferModal.css('display', 'none');
        $('html, body').css('overflow', '');
        if (_transferJustConfirmed) {
            window.location.reload();
        }
    }

    $('#whp-ty-transfer-btn').on('click', openTransferModal);
    $('#whp-transfer-modal-close, #whp-transfer-form-cancel').on('click', closeTransferModal);
    $transferModal.on('click', function (e) {
        if ($(e.target).is('#whp-transfer-modal')) closeTransferModal();
    });

    // Receipt image upload
    var $uploadArea    = $('#whp-tf-upload-area');
    var $fileInput     = $('#whp-tf-receipt');
    var $previewImg    = $('#whp-tf-preview-img');
    var $previewWrap   = $('#whp-tf-upload-preview');
    var $uploadPh      = $('#whp-tf-upload-ph');
    var $progressWrap  = $('#whp-tf-upload-progress');
    var $progressFill  = $('#whp-tf-progress-fill');
    var $progressText  = $('#whp-tf-progress-text');

    $uploadArea.on('click', function (e) {
        // Skip if click came from file input itself (it bubbles up and would recurse) or the remove button
        if ($(e.target).is($fileInput) || $(e.target).is('#whp-tf-remove-receipt')) return;
        $fileInput[0].click();
    });
    $uploadArea.on('dragover dragenter', function (e) {
        e.preventDefault();
        $(this).css('border-color', 'var(--whp-accent, #6d28d9)');
    });
    $uploadArea.on('dragleave drop', function (e) {
        e.preventDefault();
        $(this).css('border-color', '');
        if (e.type === 'drop') {
            var files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                try { var dt = new DataTransfer(); dt.items.add(files[0]); $fileInput[0].files = dt.files; } catch(ex) {}
                handleReceiptFile(files[0]);
            }
        }
    });
    $fileInput.on('change', function () {
        if (this.files && this.files[0]) handleReceiptFile(this.files[0]);
    });
    $('#whp-tf-remove-receipt').on('click', function (e) {
        e.stopPropagation();
        receiptUrl = '';
        $fileInput.val('');
        $previewWrap.hide();
        $uploadPh.show();
    });

    function handleReceiptFile(file) {
        var reader = new FileReader();
        reader.onload = function (ev) {
            $previewImg.attr('src', ev.target.result);
            $uploadPh.hide();
            $previewWrap.show();
        };
        reader.readAsDataURL(file);

        // Upload to server
        isUploading = true;
        $progressWrap.show();
        $progressFill.css('width', '0');
        $progressText.text('Đang tải lên...');

        var fd = new FormData();
        fd.append('action',   'whp_upload_receipt');
        fd.append('nonce',    cfg.transfer_nonce);
        fd.append('order_id', cfg.order_id);
        fd.append('receipt',  file);

        $.ajax({
            url         : cfg.ajax_url,
            type        : 'POST',
            data        : fd,
            processData : false,
            contentType : false,
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function (ev) {
                    if (ev.lengthComputable) {
                        var pct = Math.round((ev.loaded / ev.total) * 100);
                        $progressFill.css('width', pct + '%');
                        $progressText.text(pct + '%');
                    }
                });
                return xhr;
            },
            success: function (res) {
                isUploading = false;
                $progressWrap.hide();
                if (res && res.success) {
                    receiptUrl = res.data.url;
                    $progressText.text('Tải lên thành công');
                } else {
                    $progressText.text('Lỗi tải lên');
                    receiptUrl = '';
                }
            },
            error: function () {
                isUploading = false;
                $progressWrap.hide();
                $progressText.text('Lỗi tải lên');
                receiptUrl = '';
            }
        });
    }

    // Transfer form validation & submit
    $transferForm.on('submit', function (e) {
        e.preventDefault();

        var senderName = $('#whp-tf-name').val().trim();
        var bank       = $('#whp-tf-bank').val();
        var last4      = $('#whp-tf-last4').val().replace(/\D/g, '');
        var notes      = $('#whp-tf-notes').val().trim();
        var valid      = true;

        // Validate required fields
        if (!senderName) {
            valid = false;
            $('#whp-tf-name').addClass('whp--invalid');
            $('[data-for="whp-tf-name"]').addClass('whp--show');
        } else {
            $('#whp-tf-name').removeClass('whp--invalid');
            $('[data-for="whp-tf-name"]').removeClass('whp--show');
        }
        if (!bank) {
            valid = false;
            $('#whp-tf-bank').addClass('whp--invalid');
            $('[data-for="whp-tf-bank"]').addClass('whp--show');
        } else {
            $('#whp-tf-bank').removeClass('whp--invalid');
            $('[data-for="whp-tf-bank"]').removeClass('whp--show');
        }
        if (last4.length !== 4) {
            valid = false;
            $('#whp-tf-last4').addClass('whp--invalid');
            $('[data-for="whp-tf-last4"]').addClass('whp--show');
        } else {
            $('#whp-tf-last4').removeClass('whp--invalid');
            $('[data-for="whp-tf-last4"]').removeClass('whp--show');
        }
        // Ảnh biên lai bắt buộc khi OCR bật: check receiptUrl (đã upload xong lên server),
        // không chỉ file đã chọn — vì upload có thể fail sau khi chọn file.
        var receiptRequired = $transferForm.attr('data-receipt-required') === '1';
        if (receiptRequired && !receiptUrl) {
            valid = false;
            $uploadArea.css('border-color', '#ef4444');
            $('[data-for="whp-tf-receipt"]').addClass('whp--show');
        } else {
            $uploadArea.css('border-color', '');
            $('[data-for="whp-tf-receipt"]').removeClass('whp--show');
        }
        if (!valid) return;

        if (isUploading) {
            $progressText.text('Đang tải ảnh biên lai lên, vui lòng đợi...');
            return;
        }

        var $btn = $('#whp-transfer-form-submit');
        var $generalError = $('#whp-tf-general-error');
        $btn.prop('disabled', true).text('Đang gửi...');
        $generalError.removeClass('whp--show').text('');

        $.post(cfg.ajax_url, {
            action       : 'whp_confirm_transfer',
            nonce        : cfg.transfer_nonce,
            order_id     : cfg.order_id,
            sender_name  : senderName,
            bank         : bank,
            last4        : last4,
            notes        : notes,
            receipt_url  : receiptUrl
        }, function (res) {
            if (res && res.success) {
                // Cập nhật UI ngoài modal (timeline, banner, nút)
                onTransferConfirmed();

                // OCR bật + có ảnh biên lai → chạy AI verify
                if (cfg.ocr_active && receiptUrl) {
                    $transferForm.hide();
                    runAiVerify($btn);
                } else {
                    showTransferSuccess();
                }
            } else {
                var errMsg = (res && res.data && res.data.message) ? res.data.message : 'Có lỗi xảy ra, vui lòng thử lại.';
                $generalError.text(errMsg).addClass('whp--show');
                $btn.prop('disabled', false).html(SUBMIT_ICON + 'Xác nhận đã chuyển khoản');
            }
        }, 'json').fail(function () {
            $generalError.text('Kết nối thất bại, vui lòng thử lại.').addClass('whp--show');
            $btn.prop('disabled', false).html(SUBMIT_ICON + 'Xác nhận đã chuyển khoản');
        });
    });

    // Cập nhật UI ngoài modal sau khi server lưu thành công
    function onTransferConfirmed() {
        _transferJustConfirmed = true;
        $('#whp-ty-transfer-btn').hide();
        $('#whp-ty-transfer-success').show();
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        $('#whp-ty-countdown').slideUp(400);
        var $steps = $('.whp-ty__sh-step');
        if ($steps.length >= 3) {
            $steps.eq(1).removeClass('is-current is-pending').addClass('is-done').find('.whp-ty__sh-dot').html('');
            $steps.eq(2).removeClass('is-pending').addClass('is-current').find('.whp-ty__sh-dot').html('3');
        }
        $('#whp-ty-onhold-banner').slideDown(400);
    }

    // Success thường (không có AI)
    function showTransferSuccess() {
        $transferForm.hide();
        $('#whp-transfer-form-success').show();
        setTimeout(closeTransferModal, 2500);
    }

    // Chạy AI verify với thanh %
    function runAiVerify($btn) {
        var $wrap = $('#whp-ai-verify-wrap');
        var $fill = $('#whp-ai-vp-fill');
        var $pct  = $('#whp-ai-vp-pct');
        var $txt  = $('#whp-ai-vp-text');

        $wrap.show();
        var progress = 0;
        var aiTimer = setInterval(function () {
            var inc = progress < 40 ? 3 : progress < 70 ? 1.2 : 0.35;
            if (progress < 92) {
                progress = Math.min(92, progress + inc);
                $fill.css('width', Math.round(progress) + '%');
                $pct.text(Math.round(progress) + '%');
            }
        }, 250);

        $.post(cfg.ajax_url, {
            action   : 'wpaap_frontend_ai_verify',
            nonce    : cfg.transfer_nonce,
            order_id : cfg.order_id,
        }, function (res) {
            clearInterval(aiTimer);
            $fill.css('width', '100%');
            $pct.text('100%');
            $txt.text('Hoàn tất!');

            setTimeout(function () {
                $wrap.hide();
                var data = (res && res.data) ? res.data : {};
                if (res && res.success && data.verdict === 'valid') {
                    showAiSuccess(data);
                } else if (res && res.success) {
                    showAiFailure(data);
                } else {
                    // AI không xác minh được → thông báo sẽ xem xét thủ công
                    showAiFailure({verdict_reason: 'Không thể xác minh tự động. Thông tin chuyển khoản đã được ghi nhận và sẽ được xem xét thủ công trong thời gian sớm nhất.'});
                }
            }, 600);
        }, 'json').fail(function () {
            clearInterval(aiTimer);
            $wrap.hide();
            showTransferSuccess(); // fallback nếu AI không trả lời
        });
    }

    function showAiSuccess(data) {
        var conf = typeof data.confidence !== 'undefined'
            ? data.confidence
            : Math.round((1 - (data.risk_score || 0)) * 100);
        $('#whp-ai-success-conf').text('Độ tin cậy: ' + conf + '%');
        $('#whp-ai-success').show();
        setTimeout(closeTransferModal, 3500);
    }

    function showAiFailure(data) {
        var reason = data.verdict_reason || 'Hệ thống không thể xác minh biên lai tự động.';
        $('#whp-ai-fail-reason').text(reason);
        var flags = data.risk_flags || [];
        if (flags.length) {
            var flagHtml = flags.map(function (f) { return '<span>⚠ ' + f + '</span>'; }).join('');
            $('#whp-ai-fail-flags').html(flagHtml).show();
        } else {
            $('#whp-ai-fail-flags').hide();
        }
        $('#whp-ai-failure').show();
    }

    /* =========================================================
     * 4. AUTO AI VERIFY ON PAGE LOAD
     * Triggered when: OCR active + receipt exists + no AI result yet + order is on-hold
     * ========================================================= */
    var $pageAiPending = $('#whp-ty-ai-pending');
    if (cfg.ocr_active && cfg.has_receipt && !cfg.ai_result && cfg.order_status === 'on-hold') {
        $pageAiPending.show();
        var pageProgress = 0;
        var pageAiTimer = setInterval(function () {
            var inc = pageProgress < 40 ? 3 : pageProgress < 70 ? 1.2 : 0.35;
            if (pageProgress < 92) {
                pageProgress = Math.min(92, pageProgress + inc);
                $('#whp-ty-ai-pending-pct').text(Math.round(pageProgress) + '%');
            }
        }, 250);

        $.post(cfg.ajax_url, {
            action   : 'wpaap_frontend_ai_verify',
            nonce    : cfg.transfer_nonce,
            order_id : cfg.order_id,
        }, function (res) {
            clearInterval(pageAiTimer);
            $pageAiPending.hide();
            var data = (res && res.data) ? res.data : {};
            var $wrap = $('#whp-ty-ai-result-wrap');
            if (res && res.success && data.verdict === 'valid') {
                var conf = typeof data.confidence !== 'undefined'
                    ? data.confidence
                    : Math.round((1 - (data.risk_score || 0)) * 100);
                $wrap.html(
                    '<div class="whp-ty-ai-result whp-ty-ai-result--ok">' +
                    '<div class="whp-ty-ai-result__icon" style="background:#dcfce7">' +
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' +
                    '</div><div class="whp-ty-ai-result__body">' +
                    '<div class="whp-ty-ai-result__title" style="color:#15803d">AI đã xác minh thanh toán</div>' +
                    '<div class="whp-ty-ai-result__sub">Độ tin cậy: ' + conf + '%</div>' +
                    '</div></div>'
                );
            } else if (res && res.success) {
                var reason = data.verdict_reason || 'Biên lai cần được kiểm tra thêm.';
                $wrap.html(
                    '<div class="whp-ty-ai-result whp-ty-ai-result--fail">' +
                    '<div class="whp-ty-ai-result__icon" style="background:#fee2e2">' +
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
                    '</div><div class="whp-ty-ai-result__body">' +
                    '<div class="whp-ty-ai-result__title" style="color:#b91c1c">Biên lai cần xem lại</div>' +
                    '<div class="whp-ty-ai-result__sub">' + $('<span>').text(reason).html() + '</div>' +
                    '</div></div>'
                );
            }
            // if error/fail → just hide the pending block, leave wrap empty
        }, 'json').fail(function () {
            clearInterval(pageAiTimer);
            $pageAiPending.hide();
        });
    }

    /* =========================================================
     * 5. SUPPORT MODAL
     * ========================================================= */
    var $supportModal = $('#whp-ty-modal');
    function closeSupport() {
        $supportModal.css('display', 'none');
        $('html, body').css('overflow', '');
    }
    $('#whp-ty-support-btn').on('click', function () {
        if ($supportModal.parent()[0] !== document.body) {
            $(document.body).append($supportModal);
        }
        $supportModal.css('display', 'flex');
        $('html, body').css('overflow', 'hidden');
    });
    $('#whp-ty-modal-close').on('click', closeSupport);
    $(document).on('click', '#whp-ty-modal', function (e) {
        if ($(e.target).is('#whp-ty-modal')) closeSupport();
    });

    /* =========================================================
     * 6. INVOICE PRINT
     * ========================================================= */
    $('#whp-ty-invoice-btn').on('click', function () {
        var printTitle = $(this).data('print-title') || document.title;
        var origTitle  = document.title;
        document.title = printTitle;
        window.print();
        setTimeout(function () { document.title = origTitle; }, 1000);
    });

}); // end jQuery ready
