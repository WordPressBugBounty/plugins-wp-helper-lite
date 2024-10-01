 jQuery('#whp-popup form').submit(function (e) { 
      e.preventDefault();
      let data = jQuery(this).serializeArray();
     console.log(whp_popup_ajax);
      alert(data)
      jQuery.ajax({
        type: "POST",
        url: whp_popup_ajax,
        data: {
          action: "whp_ajax_sendmail_popup",
          data: data,
        },
        dataType: "json",
        success: function (response) {
          console.log(response);
        }
      });
    });