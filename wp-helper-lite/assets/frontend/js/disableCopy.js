function AddOriginalLink() {
  var e,
    t = document.getElementsByTagName("body")[0];
  e = window.getSelection();
  var n =
      "Nguá»“n : <a href='" +
      document.location.href +
      "'>" +
      document.location.href +
      "</a>",
    o = document.createElement("div");
  (o.style.position = "absolute"),
    (o.style.left = "-99999px"),
    t.appendChild(o),
    (o.innerHTML = n),
    e.selectAllChildren(o),
    window.setTimeout(function () {
      t.removeChild(o);
    }, 0);
}
(window.onload = function () {
  function e(e) {
    return (
      e.stopPropagation
        ? e.stopPropagation()
        : window.event && (window.event.cancelBubble = !0),
      e.preventDefault(),
      !1
    );
  }
  document.addEventListener(
    "contextmenu",
    function (e) {
      e.preventDefault();
    },
    !1
  ),
    document.addEventListener(
      "keydown",
      function (t) {
        t.ctrlKey && t.shiftKey && 73 == t.keyCode && e(t),
          t.ctrlKey && t.shiftKey && 74 == t.keyCode && e(t),
          83 == t.keyCode &&
            (navigator.platform.match("Mac") ? t.metaKey : t.ctrlKey) &&
            e(t),
          t.ctrlKey && 85 == t.keyCode && e(t),
          123 == event.keyCode && e(t),
          !t.ctrlKey ||
            ("p" != t.key &&
              16 != t.charCode &&
              112 != t.charCode &&
              80 != t.keyCode) ||
            ((t.cancelBubble = !0),
            t.preventDefault(),
            t.stopImmediatePropagation());
      },
      !1
    );
}),
  (document.oncopy = AddOriginalLink),
  document.addEventListener(
    "contextmenu",
    function (e) {
      e.preventDefault();
    },
    !1
  );
