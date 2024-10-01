jQuery("#whp-popup form").submit(function (e) {
  e.preventDefault();
  let data = jQuery(this).serializeArray();
  jQuery.ajax({
    type: "POST",
    url: whp_popup_ajax["url"],
    data: {
      action: "whp_ajax_sendmail_popup",
      data: data,
    },
    success: function (response) {
      if (response["status"] == 200) {
        setCookie("whp_popup", true, 1);
        jQuery("#whp-popup").removeClass("whp-popup").addClass("whp-hidden");
        alert("Bạn đã đăng ký nhận tin thành công");
      }
    },
  });
});
