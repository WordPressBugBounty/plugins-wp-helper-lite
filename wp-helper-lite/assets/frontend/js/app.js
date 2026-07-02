(function ($) {
  $(document).ready(function () {

    // ── FAQ Accordion ─────────────────────────────────────────────────────────
    $(document).on('click', '.wpaap-faq-q', function () {
      var $item = $(this).closest('.wpaap-faq-item');
      var $ans  = $item.find('.wpaap-faq-a');
      var $icon = $(this).find('.wpaap-faq-icon');
      if ( $item.hasClass('wpaap-faq-open') ) {
        $item.removeClass('wpaap-faq-open');
        $ans.slideUp(200);
        if ( $icon.length ) $icon.text('▼');
      } else {
        $item.addClass('wpaap-faq-open');
        $ans.slideDown(200);
        if ( $icon.length ) $icon.text('▲');
      }
    });

    // Toggle danh sách Hotline trong Mẫu C
    $(document).on("click", ".whp-toggle-phones-btn", function(e) {
      e.preventDefault();
      $(this).next(".whp-agents-collapse").slideToggle(250);
    });

    // Tự động mở popup ở lần đầu truy cập
    if (typeof whpGetCookie === 'function') {
      var autoOpened = whpGetCookie("whp_contact_auto_opened");
      if (!autoOpened) {
        var isAdmin = (typeof whp_popup_ajax !== 'undefined' && whp_popup_ajax.isAdmin == 1);
        if (!isAdmin) {
          setTimeout(function() {
            let btnIcon = $(".whp-contact-icon").first();
            if (btnIcon.length && !btnIcon.hasClass("only-call")) {
              let btnClose = btnIcon.find(".whp-contact-icon-close");
              let content = $(".whp-contact-content").first();
              $(".whp-contact-greeting").hide();
              btnIcon.addClass("show-close");
              btnClose.addClass("active");
              content.addClass("active");
              whpSetCookie("whp_contact_auto_opened", "1", 1);
            }
          }, 1500);
        }
      }
    }

    let btnContact = $(".whp-contact-button");
    let greetingContact = $(".whp-contact-greeting");
    let btnCloseGreeting = $(".whp-contact-close-greeting");
    btnCloseGreeting.click(function () {
      greetingContact.hide();
    });
    btnContact.click(function () {
      let btnIcon = $(this).find(".whp-contact-icon");
      let btnClose = $(this).find(".whp-contact-icon-close");
      let content = $(this).prev();
      greetingContact.hide();
      if (btnIcon.hasClass("only-call")) {
        let phone = btnIcon.data("phone");
        window.location.href = `tel:${phone}`;
      } else {
        btnIcon.toggleClass("show-close");
        btnClose.toggleClass("active");
        $(".whp-contact-content").not(content).removeClass("active");
        content.toggleClass("active");
      }
    });
    $(window).click(function (e) {
      let contactEle = $(".whp-contact");
      if (!contactEle.is(e.target) && contactEle.has(e.target).length === 0) {
        let btnIcon = $(".whp-contact-icon");
        let btnClose = $(".whp-contact-icon-close");
        btnIcon.removeClass("show-close");
        btnClose.removeClass("active");
        if ($(".whp-contact-content").hasClass("active"))
          return $(".whp-contact-content").removeClass("active");
      }
    });
  });

  $(document).ready(function () {
    if (typeof whpGetCookie !== 'function') return;
    var isAdmin = (typeof whp_popup_ajax !== 'undefined' && whp_popup_ajax.isAdmin == 1);
    var cookie_pop_up = isAdmin ? '' : whpGetCookie("whp_popup");
    if (!cookie_pop_up && $("#whp-popup").length) {
      var delay = (typeof whp_popup_ajax !== 'undefined' && !isNaN(parseInt(whp_popup_ajax.delay)))
        ? parseInt(whp_popup_ajax.delay) * 1000
        : 8000;

      function doShowPopup() {
        if (whpGetCookie("whp_popup") && !isAdmin) return;
        $("#whp-popup").addClass("whp-popup").removeClass("whp-hidden");
        $("#whp-popup .modal-box").addClass("show-modal");
        if (!isAdmin) whpSetCookie("whp_popup", true, 1);

        $("#whp-popup .fa-times, #whp-popup .whp-popup-close, #whp-popup .icon-close").on("click", function () {
          $("#whp-popup").removeClass("whp-popup").addClass("whp-hidden");
          $("#whp-popup .modal-box").removeClass("show-modal");
        });
        $(".whp-popup-background").on("click", function () {
          $("#whp-popup").removeClass("whp-popup").addClass("whp-hidden");
          $("#whp-popup .modal-box").removeClass("show-modal");
        });
      }

      if (delay === 0) {
        doShowPopup();
      } else {
        setTimeout(doShowPopup, delay);
      }
    }
  });
})(jQuery);
