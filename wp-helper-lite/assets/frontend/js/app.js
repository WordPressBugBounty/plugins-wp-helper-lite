(function ($) {
  $(document).ready(function () {
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
    let cookie_pop_up = getCookie("whp_popup");
    if (!cookie_pop_up) {
      if (document.location.pathname.length <= 1) {
        $("#whp-popup").addClass("whp-popup").removeClass("whp-hidden");
        $(".modal-box").toggleClass("show-modal");
        $(".start-btn").toggleClass("show-modal");
        $(".fa-times").click(function () {
          setCookie("whp_popup", true, 1);
          $("#whp-popup").removeClass("whp-popup").addClass("hidden");
          $(".modal-box").toggleClass("show-modal");
          $(".start-btn").toggleClass("show-modal");
        });
      }
    }
  });
})(jQuery);
