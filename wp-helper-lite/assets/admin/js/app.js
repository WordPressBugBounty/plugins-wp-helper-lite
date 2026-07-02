(function ($) {
  $(document).ready(function () {
    let whp_type_filter = $("#whp_helper_type_filter").val();
    if (whp_type_filter == "taxonomy") {
      let class_show = $("#whp-group-option-taxonomy").hasClass("show");
      if (!class_show) {
        $("#whp-group-option-taxonomy").addClass("show");
      }
    }
    let checkBox = $(`.whp-setting-content-item input[type='checkbox']`);
    checkBox.change(function () {
      if ($(this).is(":checked")) {
        $(this).val("1");

        $("#" + this.id + "-content").addClass("whp-show-content");
        $("#" + this.id + "-content").removeClass("whp-hide-content");
      } else {
        $(this).val("0");
        $("#" + this.id + "-content").addClass("whp-hide-content");
        $("#" + this.id + "-content").removeClass("whp-show-content");
      }
    });

    if ($(".button-upload-qrcode").length) {
      $(".button-upload-qrcode").attr("value", "Chọn ảnh");

      // Inject stable container ONCE — never insert more elements into the DOM after this
      $(".button-upload-qrcode").after('<div id="whp-qr-area" style="margin-top:8px"></div>');
      var $qrArea = $("#whp-qr-area");

      function renderQrPreview(url) {
        if (!url) { $qrArea.empty(); return; }
        $qrArea.html(
          '<img src="' + url + '" style="width:150px;display:block;border-radius:6px;border:1px solid #e5e7eb;margin-bottom:6px">' +
          '<button type="button" class="whp-qr-delete" style="color:#dc2626;background:#fff;border:1px solid #dc2626;border-radius:4px;padding:3px 12px;cursor:pointer;font-size:12px">Xóa ảnh</button>'
        );
        $qrArea.find(".whp-qr-delete").on("click", function () {
          $(".input-image-qr").val("");
          renderQrPreview("");
        });
      }

      // Show existing image on page load
      var existingQr = $(".input-image-qr").val();
      if (existingQr) { renderQrPreview(existingQr); }

      // Create media frame ONCE, reuse on every click
      var whpQrFrame = wp.media({
        title: "Chọn ảnh QR Code",
        library: { type: "image" },
        multiple: false,
        button: { text: "Sử dụng ảnh này" },
      });
      whpQrFrame.on("select", function () {
        var sel = whpQrFrame.state().get("selection").first();
        if (!sel) return;
        var url = sel.toJSON().url;
        $(".input-image-qr").val(url);
        renderQrPreview(url);
      });

      $(".button-upload-qrcode").on("click", function (e) {
        e.preventDefault();
        whpQrFrame.open();
      });
    }
    $("#enable_contact").change(function () {
      let enable_contact = $(this).val();
      let contactSetting = $("#contact-setting");
      let securitySetting = $("#security-setting");
      if (enable_contact == "1") {
        contactSetting.fadeIn();
        securitySetting.fadeIn();
      } else {
        contactSetting.hide();
        securitySetting.hide();
      }
    });
    $("#enable_maintenance").change(function () {
      let enable_maintenance = $(this).val();
      let maintenance = $("#maintenance-setting");
      let preview = $("#whp-preview");
      if (enable_maintenance == "1") {
        maintenance.fadeIn();
        preview.removeClass("disable");
      } else {
        preview.addClass("disable");
        maintenance.hide();
      }
    });
    $("#enable_popup").change(function () {
      let enable_popup = $(this).val();
      let popup = $("#popup-setting");
      let select = $("select[name=whp_popup_type]");
      let select_value = select.val();
      if (enable_popup == "1") {
        popup.fadeIn();
        if (select_value == 0) {
          $("#whp-newsletter").removeClass("no_checked").addClass("checked");
        }
        if (select_value == 1) {
          $("#whp-banner").removeClass("no_checked").addClass("checked");
        }
      } else {
        popup.hide();
        $("#whp-newsletter").removeClass("checked").addClass("no_checked");
        $("#whp-banner").removeClass("checked").addClass("no_checked");
      }
    });
    let btnAddMorePhone = $("#btnAddMorePhone");
    btnAddMorePhone.click(function (e) {
      let numberAddMorePhone = $(this).data("number");
      numberAddMorePhone = numberAddMorePhone + 1;
      let key = "whp_contact_phone_data";
      let imageUrl = $(this).data("image-url");
      let xhtml = `
    <div class="contact-list-item" data-id = '${numberAddMorePhone}'>
        <button type = 'button' class = 'contact-remove'><img src = '${imageUrl}remove.png'></button>
        <div class="form-group">
            <label for="">Hình đại diện</label>
            <div class="form-avatar-group">
                <div class = 'form-avatar-item'>
                    <label for = 'avatar_${numberAddMorePhone}_nu'> 
                      <img src = '${imageUrl}nu.svg'>
                    </label>
                    <input type = 'radio' name = '${key}[${numberAddMorePhone}][avatar]' value = 'contact-avata-women' id = 'avatar_${numberAddMorePhone}_nu' checked >
                    Nữ
                </div>
                <div class = 'form-avatar-item'>
                  <label for = 'avatar_${numberAddMorePhone}_nam'> 
                    <img src = '${imageUrl}nam.svg'>
                  </label>
                  <input type = 'radio' name = '${key}[${numberAddMorePhone}][avatar]' value = 'contact-avata-men' id = 'avatar_${numberAddMorePhone}_nam'>
                  Nam
                </div>
                <div class = 'form-avatar-item'>
                  <label for = 'avatar_${numberAddMorePhone}_support'> 
                    <img src = '${imageUrl}24.svg'>
                  </label>
                  <input type = 'radio' name = '${key}[${numberAddMorePhone}][avatar]' value = 'contact-avata-support' id = 'avatar_${numberAddMorePhone}_support' >
                  Support 24/7
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="">Tên hiển thị</label>
            <div class="form-group">
                <input type="text" class="form-control" placeholder="Nhập tên hiển thị" name = '${key}[${numberAddMorePhone}][title]'>
            </div>
        </div>
        <div class="form-group">
            <label for="">Số điện thoại</label>
            <div class="form-group">
                <input type="text" class="form-control" placeholder="Nhập số điện thoại hiển thị" name = '${key}[${numberAddMorePhone}][phone]'>
            </div>
        </div>
    </div>`;
      let contactList = $(".contact-list");
      contactList.append(xhtml);
      $(this).data("number", numberAddMorePhone);
      handleBtnContactRemove();
    });
    //$("form").dirtyForms();
    let whp_smtp_host = $("select[name=whp_smtp_host]");

    let whpSmtpSecurity = $(`select[name='whp_smtp_security']`);
    let whp_smtp_port = $(`input[name='whp_smtp_port']`);

    whp_smtp_host.click(function () {
      let val = $(this).val();
      if (val == "smtp.office365.com") {
        whpSmtpSecurity.val("tls").change();
        whpSmtpSecurity.prop("disabled", true);
      } else {
        whpSmtpSecurity.prop("disabled", false);
      }
    });

    whpSmtpSecurity.change(function () {
      let port = $(this).find("option:selected").data("port");
      if (port) return $(`input[name='whp_smtp_port']`).val(port);
    });

    let btnShowPassword = $(".btn-show-password");
    btnShowPassword.click(function () {
      let input = $(this).prev();
      let inputType = input.attr("type");
      if (inputType == "text") {
        input.attr("type", "password");
      } else {
        input.attr("type", "text");
      }
    });
    let checkBoxChangeLoginUrl = $(
      `.whp-setting-content-item input[name='whp_security_change_login_url']`
    );
    checkBoxChangeLoginUrl.change(function () {
      let value = $(this).val();
      let input = $(this).parent().parent().find(".input-group");

      if (value == 1) return input.css("display", "flex");
      if (value == 0) return input.hide();
    });
    let btnUploadLogo = $(".button-upload-qrcode");
    $("#uploadLogo").on("click", function () {
      var frame = wp.media({
        title: "Chọn logo đăng nhập",
        library: { type: "image" },
        multiple: false,
        button: { text: "Sử dụng ảnh này" },
      });
      frame.open();
      frame.on("select", function () {
        let attachment = frame.state().get("selection").first().toJSON();
        let image_url = attachment.url;
        // Update all logo preview images (new layout classes + legacy)
        $(".mb-lte-preview-img, .mb-lte-form-logo-img, .preview-logo").attr("src", image_url);
        $(`input[name='whp_extention_custom_login_logo']`).val(image_url);
        $(`input[name='whp_popup_image_banner']`).val(image_url);
        $(`input[name='whp_maintenance_banner']`).val(image_url);
        // Update configured badge
        $("#mb-lte-configured-badge").css("display", "inline-flex");
        // Update dimensions badge
        var tmp = new Image();
        tmp.onload = function () {
          $("#mb-lte-dim").text(tmp.naturalWidth + " × " + tmp.naturalHeight + "px");
        };
        tmp.src = image_url;
      });
    });
    let btnClosePreview = $("#removeLogo, .preview-close");
    btnClosePreview.on("click", function () {
      let defaultUrl = $(this).data("default") || "";
      $(".mb-lte-preview-img, .mb-lte-form-logo-img, .preview-logo").attr("src", defaultUrl);
      $(`input[name='whp_extention_custom_login_logo']`).val("");
      $("#mb-lte-configured-badge").css("display", "none");
      $("#mb-lte-dim").text("— × —");
    });
    let enableCustomLogin = $(`input[name='whp_extention_custom_login_theme']`);
    enableCustomLogin.change(function () {
      let value = $(this).val();
      let custom_login = $("#custom_login");
      if (value == "1") {
        custom_login.fadeIn();
      } else {
        custom_login.hide();
      }
    });

    handleBtnContactRemove();
    function handleBtnContactRemove() {
      let btnContactRemove = $(".contact-remove");
      btnContactRemove.click(function () {
        let id = $(this).parent().data("id");

        let ele = $(`.contact-list-item[data-id=${id}]`);
        ele.remove();
      });
    }
  });

  $("select[name=whp_popup_type]").change(function () {
    let value = $(this).val();
    if (value == 0) {
      $("#whp-newsletter").removeClass("no_checked").addClass("checked");
      $("#whp-banner").removeClass("checked").addClass("no_checked");
    }
    if (value == 1) {
      $("#whp-banner").removeClass("no_checked").addClass("checked");
      $("#whp-newsletter").removeClass("checked").addClass("no_checked");
    }
  });
  $("#whp_helper_type_filter").change(function () {
    const type = $(this).val();

    switch (type) {
      case "price":
        $("#whp-group-option-category").removeClass("show").addClass("hide");
        $("#whp-group-option-price").removeClass("hide").addClass("show");
        $("#whp-group-option-comment").removeClass("show").addClass("hide");
        $("#whp-group-option-taxonomy").removeClass("show").addClass("hide");
        break;
      case "category":
        $("#whp-group-option-category").removeClass("show").addClass("hide");
        $("#whp-group-option-price").removeClass("show").addClass("hide");
        $("#whp-group-option-comment").removeClass("show").addClass("hide");
        $("#whp-group-option-taxonomy").removeClass("show").addClass("hide");
        break;
      case "comment":
        $("#whp-group-option-comment").removeClass("hide").addClass("show");
        $("#whp-group-option-category").removeClass("show").addClass("hide");
        $("#whp-group-option-price").removeClass("show").addClass("hide");
        $("#whp-group-option-taxonomy").removeClass("show").addClass("hide");
        break;
      case "taxonomy":
        $("#whp-group-option-taxonomy").removeClass("hide").addClass("show");
        $("#whp-group-option-category").removeClass("show").addClass("hide");
        $("#whp-group-option-price").removeClass("show").addClass("hide");
        $("#whp-group-option-comment").removeClass("show").addClass("hide");

        break;

      default:
        break;
    }
  });

  function whp_helper_type_filter(type) {
    let all_type = ["price", "category", "comment", "taxonomy"];

    all_type.forEach((element) => {
      var elt = document.getElementById("whp-group-option-" + type);

      if (type == element) {
        elt.classList.add("show");
        elt.classList.remove("hide");
      } else {
        elt.classList.add("hide");
        elt.classList.remove("show");
      }
      // if (element != type) {
      //   if (elt.classList.contains("show")) {
      //     elt.classList.remove("show");
      //   }
      //   elt.classList.add("hide");
      // } else {
      //   if (elt.classList.contains("hide")) {
      //     elt.classList.remove("hide");
      //   }

      //   elt.classList.add("show");
      // }
    });
  }
})(jQuery);
