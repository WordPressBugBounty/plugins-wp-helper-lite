(function ($) {
  'use strict';

  // ── Simple email form (form_source = email) ──────────────────────────────
  $(document).on('submit', '#whp-form', function (e) {
    e.preventDefault();
    var $form = $(this);
    var $btn  = $('#whp-button-popup');
    var origText = $btn.text();

    $btn.prop('disabled', true).text('Đang gửi...');

    $.ajax({
      type: 'POST',
      url: whp_popup_ajax.url,
      data: {
        action: 'whp_ajax_sendmail_popup',
        nonce:  whp_popup_ajax.nonce,
        data:   $form.serializeArray(),
      },
      success: function (res) {
        if (res.status == 200) {
          whpShowMsg('success', '✓ Đăng ký thành công! Chúng tôi sẽ gửi ưu đãi cho bạn sớm nhất.');
          whpSetCookie('whp_popup', true, 1);
          setTimeout(whpClosePopup, 2800);
        } else {
          $btn.prop('disabled', false).text(origText);
          whpShowMsg('error', '✗ Có lỗi xảy ra, vui lòng thử lại sau.');
        }
      },
      error: function () {
        $btn.prop('disabled', false).text(origText);
        whpShowMsg('error', '✗ Không thể kết nối, vui lòng kiểm tra mạng.');
      },
    });
  });

  // ── CF7 embedded in popup ────────────────────────────────────────────────
  document.addEventListener('wpcf7mailsent', function () {
    if ($('#whp-popup').hasClass('whp-hidden')) return;
    whpEmbeddedSuccess();
  }, false);

  document.addEventListener('wpcf7mailfailed', function () {
    if ($('#whp-popup').hasClass('whp-hidden')) return;
    whpShowMsg('error', '✗ Gửi không thành công. Vui lòng kiểm tra lại thông tin.');
  }, false);

  // ── WPForms embedded in popup ────────────────────────────────────────────
  $(document).on('wpformsAjaxSubmitSuccess', function () {
    if ($('#whp-popup').hasClass('whp-hidden')) return;
    whpEmbeddedSuccess();
  });

  // ── Helpers ──────────────────────────────────────────────────────────────
  function whpEmbeddedSuccess() {
    var $modal = $('#whp-popup .modal-box');
    $modal.find('.whp-popup-header, .whp-embedded-form').fadeOut(250, function () {
      whpShowMsg('success', '✓ Gửi thành công! Chúng tôi sẽ phản hồi bạn sớm nhất.');
    });
    whpSetCookie('whp_popup', true, 1);
    setTimeout(whpClosePopup, 4000);
  }

  function whpShowMsg(type, msg) {
    var $box = $('#whp-popup .whp-result-msg');
    if (!$box.length) {
      $('#whp-popup .modal-box').append('<div class="whp-result-msg"></div>');
      $box = $('#whp-popup .whp-result-msg');
    }
    $box.removeClass('whp-result-success whp-result-error')
        .addClass('whp-result-' + type)
        .html(msg)
        .slideDown(200);
  }

  function whpClosePopup() {
    $('#whp-popup').removeClass('whp-popup').addClass('whp-hidden');
    $('#whp-popup .modal-box').removeClass('show-modal');
  }

})(jQuery);
