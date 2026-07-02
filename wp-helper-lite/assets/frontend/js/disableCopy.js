(function () {
    function whpAddOriginalLink() {
        var e,
            t = document.getElementsByTagName("body")[0];
        e = window.getSelection();
        var n =
                "Nguồn : <a href='" +
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

    window.addEventListener("load", function () {
        function blockEvent(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            } else if (window.event) {
                window.event.cancelBubble = true;
            }
            e.preventDefault();
            return false;
        }

        document.addEventListener(
            "contextmenu",
            function (e) {
                e.preventDefault();
            },
            false
        );

        document.addEventListener(
            "keydown",
            function (e) {
                if (e.ctrlKey && e.shiftKey && e.keyCode === 73) blockEvent(e);
                if (e.ctrlKey && e.shiftKey && e.keyCode === 74) blockEvent(e);
                if (e.keyCode === 83 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) blockEvent(e);
                if (e.ctrlKey && e.keyCode === 85) blockEvent(e);
                if (e.keyCode === 123) blockEvent(e);
                if (e.ctrlKey && (e.key === "p" || e.charCode === 16 || e.charCode === 112 || e.keyCode === 80)) {
                    e.cancelBubble = true;
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            },
            false
        );
    });

    document.oncopy = whpAddOriginalLink;

    document.addEventListener(
        "contextmenu",
        function (e) {
            e.preventDefault();
        },
        false
    );
}());
